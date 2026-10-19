<?php

namespace Tests\Feature\Admin;

use App\Models\Dispensation;
use App\Models\DispensationType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_filters_dispensations_for_daily_weekly_monthly_and_yearly_windows(): void
    {
        $this->travelTo('2026-10-18 10:00:00');
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $student = Student::where('nis', '12001')->firstOrFail();
        $type = DispensationType::where('category', DispensationType::CATEGORY_STUDENT)->firstOrFail();
        $this->createDispensation($student, $type, 'Hari ini', '2026-10-18');
        $this->createDispensation($student, $type, 'Awal pekan', '2026-10-12');
        $this->createDispensation($student, $type, 'Dalam bulan', '2026-02-15');
        $this->createDispensation($student, $type, 'Dalam tahun', '2024-05-01');
        $this->createDispensation($student, $type, 'Di luar tahun', '2021-12-31');

        $this->actingAs($admin)->get('/admin/dashboard?period=day&date=2026-10-18')
            ->assertOk()
            ->assertSee('Hari ini')
            ->assertSee('Awal pekan')
            ->assertDontSee('Dalam bulan');

        $this->get('/admin/dashboard?period=week&date=2026-10-18')
            ->assertOk()
            ->assertSee('Awal pekan')
            ->assertDontSee('Dalam bulan');

        $this->get('/admin/dashboard?period=month&date=2026-10-18')
            ->assertOk()
            ->assertSee('Dalam bulan')
            ->assertDontSee('Di luar tahun');

        $this->get('/admin/dashboard?period=year&date=2026-10-18')
            ->assertOk()
            ->assertSee('Dalam tahun')
            ->assertDontSee('Di luar tahun');
    }

    public function test_admin_can_print_and_download_the_filtered_dispensation_list(): void
    {
        $this->travelTo('2026-10-18 10:00:00');
        $this->seed();
        $admin = User::where('email', 'admin@sekolah.test')->firstOrFail();
        $admin->update(['two_factor_secret' => 'test-secret', 'two_factor_confirmed_at' => now()]);
        $student = Student::where('nis', '12001')->firstOrFail();
        $type = DispensationType::where('category', DispensationType::CATEGORY_STUDENT)->firstOrFail();
        $this->createDispensation($student, $type, 'Laporan Cetak', '2026-10-18');

        $this->actingAs($admin)->get('/admin/dashboard/print?period=day&date=2026-10-18')
            ->assertOk()
            ->assertSee('Laporan Cetak')
            ->assertSee('Andi')
            ->assertSee('window.print()');

        $response = $this->get('/admin/dashboard/export?period=day&date=2026-10-18');
        $response->assertDownload('laporan-dispensasi-day-2026-10-05-2026-10-18.xlsx');

        $exportPath = tempnam(sys_get_temp_dir(), 'dispensation-report-');
        if ($exportPath === false) {
            $this->fail('Unable to create a temporary file for the XLSX test.');
        }

        try {
            file_put_contents($exportPath, $response->streamedContent());
            $workbook = IOFactory::load($exportPath);

            $this->assertSame('Laporan Cetak', $workbook->getActiveSheet()->getCell('C2')->getValue());
            $this->assertSame('2026-10-18', $workbook->getActiveSheet()->getCell('A2')->getValue());
            $this->assertSame('Andi', $workbook->getActiveSheet()->getCell('E2')->getValue());
        } finally {
            unlink($exportPath);
        }
    }

    private function createDispensation(Student $student, DispensationType $type, string $title, string $date): void
    {
        $dispensation = Dispensation::create([
            'applicant_user_id' => $student->user_id,
            'type_id' => $type->id,
            'title' => $title,
            'reason' => 'Alasan dispensasi.',
            'date_start' => $date,
            'status' => Dispensation::STATUS_APPROVED,
        ]);
        $dispensation->recipients()->create([
            'student_id' => $student->id,
            'status' => 'APPROVED',
        ]);
    }
}
