<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyntheticDataSeeder extends Seeder
{
    /**
     * Run the database seeds with synthetic test data.
     * PERINGATAN: Hanya berisi data tiruan (sintetis), tidak berisi data riil sekolah.
     */
    public function run(): void
    {
        // 1. Password Admin: Wajib dari environment variable, tidak ada default hardcoded
        $adminPassword = env('ADMIN_INITIAL_PASSWORD');
        if (empty($adminPassword)) {
            throw new \RuntimeException('ADMIN_INITIAL_PASSWORD belum diset di environment. Seeder dihentikan demi keamanan.');
        }

        $adminUser = DB::table('users')->insertGetId([
            'name' => 'Administrator Sekolah',
            'email' => 'admin@example.test',
            'username' => 'admin',
            'password' => Hash::make($adminPassword),
            'role' => 'admin',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Tahun Ajaran Aktif (Sintetis)
        $academicYearId = DB::table('academic_years')->insertGetId([
            'name' => '2026/2027',
            'semester' => 'ganjil',
            'start_date' => '2026-07-15',
            'end_date' => '2026-12-20',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Kelas Contoh (Sintetis)
        $classXTkj = DB::table('classes')->insertGetId([
            'name' => 'X TKJ 1',
            'grade_level' => 'X',
            'major' => 'Teknik Komputer dan Jaringan',
            'academic_year_id' => $academicYearId,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classXiTkj = DB::table('classes')->insertGetId([
            'name' => 'XI TKJ 1',
            'grade_level' => 'XI',
            'major' => 'Teknik Komputer dan Jaringan',
            'academic_year_id' => $academicYearId,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Guru Contoh (Sintetis)
        $teachersData = [
            [
                'name' => 'Budi Santoso, S.Pd.',
                'username' => 'guru.budi',
                'email' => 'budi.santoso@example.test',
                'nip' => '198501012010011001',
                'gender' => 'L',
                'status' => 'tetap',
            ],
            [
                'name' => 'Siti Rahmawati, S.Kom.',
                'username' => 'guru.siti',
                'email' => 'siti.rahmawati@example.test',
                'nip' => '199002022015022002',
                'gender' => 'P',
                'status' => 'kontrak',
            ],
            [
                'name' => 'I Made Sukadana, S.T.',
                'username' => 'guru.sukadana',
                'email' => 'made.sukadana@example.test',
                'nip' => '199203032018031003',
                'gender' => 'L',
                'status' => 'honor',
            ],
        ];

        $teacherIds = [];
        foreach ($teachersData as $t) {
            $randomTeacherPassword = Str::random(16);
            if (isset($this->command)) {
                $this->command->info("Akun guru {$t['username']} dibuat dengan password sementara: {$randomTeacherPassword}");
            } else {
                echo "Akun guru {$t['username']} dibuat dengan password sementara: {$randomTeacherPassword}\n";
            }

            $userId = DB::table('users')->insertGetId([
                'name' => $t['name'],
                'email' => $t['email'],
                'username' => $t['username'],
                'password' => Hash::make($randomTeacherPassword),
                'role' => 'teacher',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $teacherId = DB::table('teachers')->insertGetId([
                'user_id' => $userId,
                'nip' => $t['nip'],
                'name' => $t['name'],
                'gender' => $t['gender'],
                'status' => $t['status'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $teacherIds[$t['username']] = $teacherId;
        }

        // 5. Mata Pelajaran Contoh (Sintetis)
        $subjectMtk = DB::table('subjects')->insertGetId([
            'code' => 'MTK-X',
            'name' => 'Matematika',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $subjectDkj = DB::table('subjects')->insertGetId([
            'code' => 'DKJ-X',
            'name' => 'Dasar Komputer dan Jaringan',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. Penugasan Mengajar Contoh (Sintetis)
        DB::table('teaching_assignments')->insert([
            [
                'teacher_id' => $teacherIds['guru.budi'],
                'class_id' => $classXTkj,
                'subject_id' => $subjectMtk,
                'academic_year_id' => $academicYearId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'teacher_id' => $teacherIds['guru.siti'],
                'class_id' => $classXTkj,
                'subject_id' => $subjectDkj,
                'academic_year_id' => $academicYearId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 7. Siswa Contoh (Sintetis)
        $studentsX = [
            ['name' => 'Agus Pratama', 'gender' => 'L', 'nisn' => '0081234561', 'nis' => '260101'],
            ['name' => 'Dewi Ayu Lestari', 'gender' => 'P', 'nisn' => '0081234562', 'nis' => '260102'],
            ['name' => 'Fajar Nugraha', 'gender' => 'L', 'nisn' => '0081234563', 'nis' => '260103'],
            ['name' => 'Kadek Aditya', 'gender' => 'L', 'nisn' => '0081234564', 'nis' => '260104'],
            ['name' => 'Ni Wayan Sari', 'gender' => 'P', 'nisn' => '0081234565', 'nis' => '260105'],
        ];

        foreach ($studentsX as $s) {
            DB::table('students')->insert([
                'class_id' => $classXTkj,
                'nisn' => $s['nisn'],
                'nis' => $s['nis'],
                'name' => $s['name'],
                'gender' => $s['gender'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

