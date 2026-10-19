<?php

namespace Database\Seeders;

use App\Models\DispensationType;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            ['name' => 'Admin TU', 'username' => 'admin', 'email' => 'admin@sekolah.test', 'password' => Hash::make('password123'), 'role' => 'admin', 'is_active' => true, 'email_verified_at' => now()]
        );

        $guruUser = User::updateOrCreate(
            ['username' => 'guru'],
            ['name' => 'Pak Budi', 'username' => 'guru', 'email' => 'guru@sekolah.test', 'password' => Hash::make('password123'), 'role' => 'guru', 'is_active' => true, 'email_verified_at' => now()]
        );

        $siswaUser = User::updateOrCreate(
            ['username' => 'siswa'],
            ['name' => 'Andi', 'username' => 'siswa', 'email' => 'siswa@sekolah.test', 'password' => Hash::make('password123'), 'role' => 'siswa', 'is_active' => true, 'email_verified_at' => now()]
        );

        $guru2User = User::where('username', 'guru2')->first()
            ?? User::where('username', 'piket')->first()
            ?? new User;
        $guru2User->fill([
            'name' => 'Bu Sari',
            'username' => 'guru2',
            'email' => 'guru2@sekolah.test',
            'password' => Hash::make('password123'),
            'role' => 'guru',
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        $teacher1 = Teacher::updateOrCreate(
            ['user_id' => $guruUser->id],
            ['nip' => '1980001', 'position' => 'Wali Kelas', 'is_active' => true]
        );

        $teacher2 = Teacher::updateOrCreate(
            ['user_id' => $guru2User->id],
            ['nip' => '1980002', 'position' => 'Guru', 'is_active' => true]
        );

        $class1 = SchoolClass::updateOrCreate(
            ['name' => 'XII RPL 1', 'academic_year' => '2026/2027'],
            ['grade' => 'XII', 'major' => 'RPL', 'is_active' => true]
        );

        $class2 = SchoolClass::updateOrCreate(
            ['name' => 'XI TKJ 2', 'academic_year' => '2026/2027'],
            ['grade' => 'XI', 'major' => 'TKJ', 'is_active' => true]
        );

        $students = [];
        $studentSeeds = [
            [$siswaUser, '12001'],
            [User::updateOrCreate(['username' => 'budi'], ['name' => 'Budi', 'username' => 'budi', 'email' => 'budi@sekolah.test', 'password' => Hash::make('password123'), 'role' => 'siswa', 'is_active' => true, 'email_verified_at' => now()]), '12002'],
            [User::updateOrCreate(['username' => 'citra'], ['name' => 'Citra', 'username' => 'citra', 'email' => 'citra@sekolah.test', 'password' => Hash::make('password123'), 'role' => 'siswa', 'is_active' => true, 'email_verified_at' => now()]), '12003'],
            [User::updateOrCreate(['username' => 'deni'], ['name' => 'Deni', 'username' => 'deni', 'email' => 'deni@sekolah.test', 'password' => Hash::make('password123'), 'role' => 'siswa', 'is_active' => true, 'email_verified_at' => now()]), '12004'],
        ];

        foreach ($studentSeeds as $i => [$u, $nis]) {
            $student = Student::updateOrCreate(['user_id' => $u->id], ['nis' => $nis, 'gender' => 'L', 'is_active' => true]);
            StudentEnrollment::updateOrCreate(
                ['student_id' => $student->id, 'academic_year' => '2026/2027'],
                ['class_id' => $i < 2 ? $class1->id : $class2->id, 'start_date' => '2026-07-15', 'is_active' => true]
            );
            $students[] = $student;
        }

        TeacherAssignment::updateOrCreate(
            ['teacher_id' => $teacher1->id, 'assignment_type' => 'HOMEROOM', 'class_id' => $class1->id],
            ['start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]
        );

        foreach (['Keluar', 'Masuk / Terlambat', 'Pulang', 'Kesehatan', 'Keperluan Keluarga', 'Lainnya'] as $name) {
            DispensationType::updateOrCreate(['name' => $name, 'category' => 'STUDENT'], ['is_active' => true]);
        }

        foreach (['Lomba', 'OSIS', 'Pramuka', 'PMR', 'Paskibra', 'Ekstrakurikuler', 'Seminar', 'Workshop', 'Kunjungan', 'Kegiatan Sekolah', 'Kegiatan Formal', 'Lainnya'] as $name) {
            DispensationType::updateOrCreate(['name' => $name, 'category' => 'ACTIVITY'], ['is_active' => true]);
        }
    }
}
