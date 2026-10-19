<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispensation;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $context = $this->reportContext($request);
        $dispensations = $this->dispensationsQuery($context['start'], $context['end'])
            ->with(['applicant', 'type', 'recipients.student.user'])
            ->orderByDesc('date_start')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.dashboard.index', [
            ...$context,
            'dispensations' => $dispensations,
            'pending' => Dispensation::whereIn('status', ['SUBMITTED', 'TEACHER_VERIFIED', 'ADMIN_REVIEW'])->count(),
            'approved' => Dispensation::whereIn('status', ['APPROVED', 'PDF_GENERATED', 'WAITING_ENDORSEMENT', 'FINALIZED', 'COMPLETED'])->count(),
            'students' => \App\Models\Student::count(),
        ]);
    }

    public function print(Request $request): View
    {
        $context = $this->reportContext($request);
        $dispensations = $this->dispensationsQuery($context['start'], $context['end'])
            ->with(['applicant', 'type', 'recipients.student.user'])
            ->orderBy('date_start')
            ->orderBy('id')
            ->get();

        return view('admin.dashboard.print', [
            ...$context,
            'dispensations' => $dispensations,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $context = $this->reportContext($request);
        $dispensations = $this->dispensationsQuery($context['start'], $context['end'])
            ->with(['applicant', 'type', 'recipients.student.user'])
            ->orderBy('date_start')
            ->orderBy('id');
        $filename = 'laporan-dispensasi-'.$context['period'].'-'.$context['start']->toDateString().'-'.$context['end']->toDateString().'.xlsx';

        return response()->streamDownload(function () use ($dispensations, $context): void {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Dispensasi');
            $headings = ['Tanggal Mulai', 'Tanggal Selesai', 'Judul', 'Jenis', 'Nama Siswa', 'NIS', 'Pengaju', 'Tujuan', 'Status'];
            $sheet->fromArray($headings, null, 'A1');
            $sheet->getStyle('A1:I1')->getFont()->setBold(true);
            $sheet->freezePane('A2');

            $row = 2;
            foreach ($dispensations->lazy(250) as $dispensation) {
                $recipients = $dispensation->recipients;
                $studentNames = $recipients->map(fn ($recipient) => $recipient->student?->user?->name)->filter()->implode(', ');
                $studentNumbers = $recipients->map(fn ($recipient) => $recipient->student?->nis)->filter()->implode(', ');
                $values = [
                    $dispensation->date_start?->format('Y-m-d') ?? '',
                    $dispensation->date_end?->format('Y-m-d') ?? '',
                    $dispensation->title,
                    $dispensation->other_type ?: $dispensation->type?->name,
                    $studentNames,
                    $studentNumbers,
                    $dispensation->applicant?->name,
                    $dispensation->destination,
                    $dispensation->status,
                ];

                foreach ($values as $column => $value) {
                    $sheet->setCellValueExplicit(
                        chr(65 + $column).$row,
                        (string) ($value ?? ''),
                        DataType::TYPE_STRING,
                    );
                }

                $row++;
            }

            foreach (range('A', 'I') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @return array{
     *     period: string,
     *     periodLabel: string,
     *     selectedDate: string,
     *     start: CarbonImmutable,
     *     end: CarbonImmutable,
     *     total: int,
     *     trend: Collection<int, array{label: string, total: int, percentage: int}>
     * }
     */
    private function reportContext(Request $request): array
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:day,week,month,year'],
            'date' => ['nullable', 'date'],
        ]);
        $period = $validated['period'] ?? 'day';
        $selectedDate = CarbonImmutable::parse($validated['date'] ?? today()->toDateString());
        $start = match ($period) {
            'day' => $selectedDate->startOfDay()->subDays(13),
            'week' => $selectedDate->startOfWeek()->subWeeks(11),
            'month' => $selectedDate->startOfMonth()->subMonths(11),
            'year' => $selectedDate->startOfYear()->subYears(4),
        };
        $end = match ($period) {
            'day' => $selectedDate->endOfDay(),
            'week' => $selectedDate->endOfWeek(),
            'month' => $selectedDate->endOfMonth(),
            'year' => $selectedDate->endOfYear(),
        };

        $countsByDate = $this->dispensationsQuery($start, $end)
            ->selectRaw('DATE(date_start) as period_date, COUNT(*) as total')
            ->groupBy('period_date')
            ->pluck('total', 'period_date');
        $buckets = [];
        $step = match ($period) {
            'day' => '1 day',
            'week' => '1 week',
            'month' => '1 month',
            'year' => '1 year',
        };
        $cursor = match ($period) {
            'day' => $start->startOfDay(),
            'week' => $start->startOfWeek(),
            'month' => $start->startOfMonth(),
            'year' => $start->startOfYear(),
        };
        $lastBucket = match ($period) {
            'day' => $end->startOfDay(),
            'week' => $end->startOfWeek(),
            'month' => $end->startOfMonth(),
            'year' => $end->startOfYear(),
        };

        foreach (CarbonPeriod::create($cursor, $step, $lastBucket) as $bucketDate) {
            $key = match ($period) {
                'day', 'week' => $bucketDate->format('Y-m-d'),
                'month' => $bucketDate->format('Y-m'),
                'year' => $bucketDate->format('Y'),
            };
            $buckets[$key] = [
                'label' => match ($period) {
                    'day', 'week' => $bucketDate->locale('id')->translatedFormat('d M'),
                    'month' => $bucketDate->locale('id')->translatedFormat('M Y'),
                    'year' => $bucketDate->format('Y'),
                },
                'total' => 0,
            ];
        }

        foreach ($countsByDate as $date => $total) {
            $date = CarbonImmutable::parse($date);
            $key = match ($period) {
                'day' => $date->format('Y-m-d'),
                'week' => $date->startOfWeek()->format('Y-m-d'),
                'month' => $date->format('Y-m'),
                'year' => $date->format('Y'),
            };

            if (array_key_exists($key, $buckets)) {
                $buckets[$key]['total'] += (int) $total;
            }
        }

        $maximum = max(1, ...array_column($buckets, 'total'));
        $trend = collect($buckets)->map(fn (array $bucket) => [
            ...$bucket,
            'percentage' => (int) round(($bucket['total'] / $maximum) * 100),
        ])->values();
        $periodName = [
            'day' => 'Harian',
            'week' => 'Mingguan',
            'month' => 'Bulanan',
            'year' => 'Tahunan',
        ][$period];
        $range = $period === 'day' ? '14 hari' : match ($period) {
            'week' => '12 minggu',
            'month' => '12 bulan',
            'year' => '5 tahun',
        };

        return [
            'period' => $period,
            'periodLabel' => $periodName.' · '.$range.' terakhir',
            'selectedDate' => $selectedDate->toDateString(),
            'start' => $start,
            'end' => $end,
            'total' => array_sum(array_column($buckets, 'total')),
            'trend' => $trend,
        ];
    }

    private function dispensationsQuery(CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return Dispensation::query()
            ->where('date_start', '>=', $start->toDateString())
            ->where('date_start', '<', $end->addDay()->toDateString());
    }
}
