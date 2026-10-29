/**
 * Test Runner untuk Validasi Fase 2 (Migration & Seeder Security)
 * Menjalankan uji unit secara independen dan in-memory tanpa koneksi ke database Supabase.
 */

import fs from 'fs';
import path from 'path';

let passed = 0;
let failed = 0;

function assert(condition, message) {
    if (condition) {
        console.log(`  ✓ PASS: ${message}`);
        passed++;
    } else {
        console.error(`  ✗ FAIL: ${message}`);
        failed++;
    }
}

console.log('========================================================');
console.log('Menjalankan Pengujian Fase 2 (In-Memory / Static Checks)');
console.log('========================================================\n');

// 1. Pengujian File Migration
console.log('1. Uji Definisi Tabel Fondasi Migration (DATABASE_DESIGN.md):');
const migrationsDir = path.resolve('database/migrations');
const migrationFiles = fs.readdirSync(migrationsDir).filter(f => f.endsWith('.php'));

let allMigrationContent = '';
for (const file of migrationFiles) {
    allMigrationContent += fs.readFileSync(path.join(migrationsDir, file), 'utf8') + '\n';
}

const expectedTables = [
    'users',
    'teachers',
    'academic_years',
    'classes',
    'students',
    'subjects',
    'teaching_assignments',
    'lesson_sessions',
    'lesson_journals',
    'student_attendance',
    'notifications',
];

for (const table of expectedTables) {
    const regex = new RegExp(`Schema::create\\(\\s*['"]${table}['"]`);
    assert(regex.test(allMigrationContent), `Tabel fondasi '${table}' didefinisikan dalam migration`);
}

const forbiddenTables = ['daily_agendas', 'staff_attendance'];
for (const table of forbiddenTables) {
    const regex = new RegExp(`Schema::create\\(\\s*['"]${table}['"]`);
    assert(!regex.test(allMigrationContent), `Tabel non-fondasi '${table}' TIDAK ADA di Fase 2`);
}

console.log('\n2. Uji Keamanan Seeder (SyntheticDataSeeder.php):');
const seederPath = path.resolve('database/seeders/SyntheticDataSeeder.php');
const seederContent = fs.readFileSync(seederPath, 'utf8');

assert(!seederContent.includes('Admin_Yapparindo_2026!'), 'Tidak ada password admin hardcoded');
assert(!seederContent.includes('Guru_Sintetis_2026!'), 'Tidak ada password guru hardcoded');
assert(!seederContent.includes('@yapparindo.sch.id'), 'Tidak ada domain email riil sekolah');
assert(seederContent.includes('@example.test'), 'Menggunakan domain placeholder @example.test');
assert(
    /if\s*\(\s*empty\(\s*\$adminPassword\s*\)\s*\)\s*\{\s*throw\s+new\s+\\RuntimeException/.test(seederContent),
    'Seeder throw RuntimeException jika ADMIN_INITIAL_PASSWORD kosong'
);
assert(
    seederContent.includes('Str::random') || seederContent.includes('random'),
    'Password guru dibuat secara acak saat seed'
);

console.log('\n3. Uji Keamanan Repositori & Secret (.gitignore & .env):');
const gitignorePath = path.resolve('.gitignore');
const gitignoreContent = fs.readFileSync(gitignorePath, 'utf8');

assert(gitignoreContent.includes('.env'), '.gitignore mengecualikan file .env');
assert(gitignoreContent.includes('.env.*'), '.gitignore mengecualikan varian .env.*');
assert(gitignoreContent.includes('/vendor/'), '.gitignore mengecualikan vendor/');
assert(gitignoreContent.includes('/node_modules/'), '.gitignore mengecualikan node_modules/');
assert(!fs.existsSync(path.resolve('.env')), 'File .env riil TIDAK ADA di repositori');
assert(fs.existsSync(path.resolve('.env.example')), 'Template .env.example tersedia');

console.log('\n========================================================');
console.log(`Total Hasil: ${passed} Passed, ${failed} Failed`);
console.log('========================================================');

if (failed > 0) {
    process.exit(1);
}

