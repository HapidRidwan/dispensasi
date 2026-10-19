<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class DataImportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_imports_students_using_human_readable_excel_headers(): void
    {
        $admin = $this->admin();
        $class = SchoolClass::create([
            'name' => 'XII RPL 1',
            'grade' => 'XII',
            'major' => 'RPL',
            'academic_year' => '2026/2027',
            'is_active' => true,
        ]);
        $upload = $this->xlsxUpload([
            ['Nama', 'Username', 'Password', 'Email', 'NIS', 'NISN', 'Jenis Kelamin', 'Telepon', 'Alamat', 'Kelas', 'Tahun Ajaran'],
            ['Siswa Impor', 'siswa.impor', 'password123', 'siswa.impor@sekolah.test', '99123', '0099123', 'P', '0812345678', 'Jl. Sekolah', 'XII RPL 1', '2026/2027'],
        ]);

        $response = $this->actingAs($admin)->post(route('admin.data-import.store'), [
            'dataset' => 'students',
            'file' => $upload,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', '1 baris data berhasil diimpor.');
        $this->assertDatabaseHas('users', [
            'name' => 'Siswa Impor',
            'username' => 'siswa.impor',
            'role' => User::ROLE_SISWA,
        ]);

        $student = Student::where('nis', '99123')->firstOrFail();
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year' => '2026/2027',
        ]);
    }

    public function test_invalid_student_row_rejects_the_whole_import(): void
    {
        $admin = $this->admin();
        $upload = $this->xlsxUpload([
            ['Nama', 'Username', 'Password', 'NIS'],
            ['Siswa Valid', 'siswa.valid', 'password123', '99124'],
            ['Siswa Tidak Valid', 'siswa.invalid', 'short', '99125'],
        ]);

        $response = $this->actingAs($admin)->post(route('admin.data-import.store'), [
            'dataset' => 'students',
            'file' => $upload,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('users', ['username' => 'siswa.valid']);
        $this->assertDatabaseMissing('students', ['nis' => '99124']);
    }

    public function test_non_admin_cannot_download_import_template(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_SISWA]);

        $this->actingAs($student)
            ->get(route('admin.data-import.templates', 'students'))
            ->assertForbidden();
    }

    public function test_admin_can_download_dataset_template(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)
            ->get(route('admin.data-import.templates', 'students'));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=template-siswa.xlsx');
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'two_factor_secret' => 'test-secret',
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function xlsxUpload(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');

        $temporaryPath = tempnam(sys_get_temp_dir(), 'data-import-');
        $xlsxPath = $temporaryPath.'.xlsx';
        unlink($temporaryPath);

        try {
            (new Xlsx($spreadsheet))->save($xlsxPath);
            $content = file_get_contents($xlsxPath);
        } finally {
            if (file_exists($xlsxPath)) {
                unlink($xlsxPath);
            }
        }

        if ($content === false) {
            throw new \RuntimeException('Unable to read generated Excel fixture.');
        }

        return UploadedFile::fake()->createWithContent('import.xlsx', $content);
    }
}
