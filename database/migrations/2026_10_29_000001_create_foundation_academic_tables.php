<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tahun Ajaran & Semester
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50); // Contoh: "2026/2027"
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });

        // 2. Profil Guru (terikat ke akun users)
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nip', 30)->nullable()->unique();
            $table->string('name', 150);
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('phone', 20)->nullable();
            $table->enum('status', ['tetap', 'kontrak', 'honor'])->default('kontrak');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 3. Rombongan Belajar / Kelas
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50); // Contoh: "X TKJ 1", "XI AKL 2"
            $table->enum('grade_level', ['X', 'XI', 'XII'])->index();
            $table->string('major', 50)->nullable(); // Program Keahlian: TKJ, AKL, dll.
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Data Siswa
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('nisn', 20)->nullable()->unique();
            $table->string('nis', 20)->nullable();
            $table->string('name', 150);
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 5. Mata Pelajaran
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable()->unique();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Penugasan Mengajar Guru (Teacher + Class + Subject + Academic Year)
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Constraint unik: guru tidak boleh ditugaskan dobel pada kelas, mapel, dan tahun ajaran yang sama
            $table->unique(
                ['teacher_id', 'class_id', 'subject_id', 'academic_year_id'],
                'unique_teaching_assignment'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('students');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('academic_years');
    }
};

