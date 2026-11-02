/**
 * Offline Sync & IndexedDB Manager — SMK Yapparindo PWA
 * Mendukung penyimpanan lokal roster 1 minggu, antrean outbox, dan sinkronisasi idempotent.
 */
const DB_NAME = 'yapparindo_offline_db';
const DB_VERSION = 1;

let dbInstance = null;

function openDB() {
    return new Promise((resolve, reject) => {
        if (dbInstance) {
            resolve(dbInstance);
            return;
        }

        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;

            // 1. Store untuk Cache Bundle Jadwal & Roster Siswa (1 Minggu)
            if (!db.objectStoreNames.contains('cached_bundles')) {
                db.createObjectStore('cached_bundles', { keyPath: 'user_id' });
            }

            // 2. Store untuk Antrean Outbox Sinkronisasi Offline
            if (!db.objectStoreNames.contains('outbox_queue')) {
                const outboxStore = db.createObjectStore('outbox_queue', { keyPath: 'idempotency_key' });
                outboxStore.createIndex('user_id', 'user_id', { unique: false });
                outboxStore.createIndex('status', 'status', { unique: false });
            }
        };

        request.onsuccess = (event) => {
            dbInstance = event.target.result;
            resolve(dbInstance);
        };

        request.onerror = (event) => {
            console.error('IndexedDB error:', event.target.error);
            reject(event.target.error);
        };
    });
}

// 1. Simpan Paket Jadwal & Roster 1 Minggu ke IndexedDB
async function saveOfflineBundle(userId, bundleData) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('cached_bundles', 'readwrite');
        const store = tx.objectStore('cached_bundles');
        store.put({
            user_id: userId,
            data: bundleData,
            updated_at: new Date().toISOString()
        });
        tx.oncomplete = () => resolve(true);
        tx.onerror = (e) => reject(e.target.error);
    });
}

// 2. Ambil Paket Jadwal & Roster dari IndexedDB (Isolasi Per User)
async function getOfflineBundle(userId) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('cached_bundles', 'readonly');
        const store = tx.objectStore('cached_bundles');
        const req = store.get(userId);
        req.onsuccess = () => resolve(req.result ? req.result.data : null);
        req.onerror = (e) => reject(e.target.error);
    });
}

// 3. Hapus Paket Roster Pengguna Tertentu dari IndexedDB (dijalankan saat Logout)
async function clearUserCachedBundle(userId) {
    if (!userId) return;
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('cached_bundles', 'readwrite');
        const store = tx.objectStore('cached_bundles');
        store.delete(userId);
        tx.oncomplete = () => resolve(true);
        tx.onerror = (e) => reject(e.target.error);
    });
}

// 4. Masukkan Sesi Absensi Offline ke Antrean Outbox (Isolasi Akun)
async function queueOfflineSession(userId, payload) {
    const db = await openDB();
    const idempotencyKey = payload.idempotency_key || ('ay-' + Date.now() + '-' + Math.random().toString(36).substring(2, 9));
    payload.idempotency_key = idempotencyKey;

    return new Promise((resolve, reject) => {
        const tx = db.transaction('outbox_queue', 'readwrite');
        const store = tx.objectStore('outbox_queue');
        store.put({
            idempotency_key: idempotencyKey,
            user_id: userId,
            status: 'pending',
            created_at: new Date().toISOString(),
            payload: payload
        });
        tx.oncomplete = () => resolve(idempotencyKey);
        tx.onerror = (e) => reject(e.target.error);
    });
}

// 5. Ambil Daftar Antrean Pending Milik Pengguna yang Sedang Aktif (Isolasi Akun)
async function getPendingOutboxItems(userId) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('outbox_queue', 'readonly');
        const store = tx.objectStore('outbox_queue');
        const index = store.index('user_id');
        const req = index.getAll(userId);

        req.onsuccess = () => {
            const items = req.result.filter(item => item.status === 'pending');
            resolve(items);
        };
        req.onerror = (e) => reject(e.target.error);
    });
}

// 6. Hapus Item Outbox Setelah Berhasil Disinkronkan
async function removeOutboxItem(idempotencyKey) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('outbox_queue', 'readwrite');
        const store = tx.objectStore('outbox_queue');
        store.delete(idempotencyKey);
        tx.oncomplete = () => resolve(true);
        tx.onerror = (e) => reject(e.target.error);
    });
}

// 7. Jalankan Proses Sinkronisasi Antrean ke Endpoint Server
async function syncOfflineQueue(userId) {
    if (!navigator.onLine) {
        return { success: false, message: 'Perangkat masih offline.' };
    }

    const pendingItems = await getPendingOutboxItems(userId);
    if (!pendingItems || pendingItems.length === 0) {
        return { success: true, count: 0 };
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const itemsPayload = pendingItems.map(item => item.payload);

    try {
        const response = await fetch('/guru/api/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ items: itemsPayload })
        });

        if (!response.ok) {
            throw new Error('Gagal menghubungi server sinkronisasi.');
        }

        const data = await response.json();

        // Bersihkan item yang berhasil atau berkonflik
        if (data.results) {
            for (const res of data.results) {
                if (res.status === 'synced' || res.status === 'conflict') {
                    await removeOutboxItem(res.idempotency_key);
                }
            }
        }

        return { success: true, count: pendingItems.length, data: data };
    } catch (err) {
        console.error('Sync failed:', err);
        return { success: false, error: err.message };
    }
}

// 8. Bersihkan Cache Storage yang Tidak Diperlukan
async function clearUnneededCaches() {
    if ('caches' in window) {
        try {
            const keys = await caches.keys();
            for (const key of keys) {
                if (key !== 'ay-app-shell-v2') {
                    await caches.delete(key);
                }
            }
        } catch (err) {
            console.error('Clear caches error:', err);
        }
    }
}

// 9. Intersepsi Logout untuk Konfirmasi Outbox Pending & Pembersihan Roster Lokal
async function handleLogoutConfirmation(event, form) {
    event.preventDefault();
    const userId = window.currentUserId;

    if (userId) {
        let pending = [];
        try {
            pending = await getPendingOutboxItems(userId);
        } catch (e) {
            console.error('Error fetching pending outbox on logout:', e);
        }

        if (pending && pending.length > 0) {
            const message = `Ada ${pending.length} catatan belum tersinkron. Keluar akan menyimpannya di perangkat ini sampai kamu login lagi sebagai akun yang sama.`;
            const proceed = window.confirm(message);
            if (!proceed) {
                return false; // Batal keluar
            }
        }

        // Hapus paket roster (cached_bundles) milik pengguna dari IndexedDB
        try {
            await clearUserCachedBundle(userId);
        } catch (e) {
            console.error('Error clearing cached bundle on logout:', e);
        }
    }

    // Bersihkan cache storage lama
    await clearUnneededCaches();

    // Lanjutkan submit form logout
    form.submit();
}

// Inisialisasi Event Listener Global Status Jaringan dan Logout
window.addEventListener('DOMContentLoaded', () => {
    const networkStatusBar = document.getElementById('networkStatusBar');

    function updateNetworkStatus() {
        if (!networkStatusBar) return;

        if (navigator.onLine) {
            networkStatusBar.classList.remove('bg-amber-600', 'text-white');
            networkStatusBar.classList.add('bg-emerald-600', 'text-white');
            networkStatusBar.innerHTML = '&#9679; Terhubung ke Jaringan (Online)';
            setTimeout(() => {
                networkStatusBar.classList.add('hidden');
            }, 3000);
        } else {
            networkStatusBar.classList.remove('hidden', 'bg-emerald-600');
            networkStatusBar.classList.add('bg-amber-600', 'text-white');
            networkStatusBar.innerHTML = '&#9888; Mode Luar Jaringan (Offline) — Data tersimpan aman di perangkat';
        }
    }

    window.addEventListener('online', () => {
        updateNetworkStatus();
        const currentUserId = window.currentUserId;
        if (currentUserId) {
            syncOfflineQueue(currentUserId).then(res => {
                if (res.success && res.count > 0) {
                    window.location.reload();
                }
            });
        }
    });

    window.addEventListener('offline', updateNetworkStatus);
    updateNetworkStatus();

    // Kaitkan proteksi logout pada semua form logout
    document.querySelectorAll('form[action*="logout"]').forEach((form) => {
        form.addEventListener('submit', (e) => handleLogoutConfirmation(e, form));
    });
});
