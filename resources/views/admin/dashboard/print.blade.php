<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Dispensasi - {{ $periodLabel }}</title>
    <style>
        body { color: #172033; font-family: Arial, sans-serif; margin: 24px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        p { color: #475569; font-size: 12px; margin-top: 0; }
        button { margin-bottom: 16px; padding: 8px 14px; }
        table { border-collapse: collapse; font-size: 11px; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 7px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; }
        @media print {
            body { margin: 0; }
            button { display: none; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <button type="button" onclick="window.print()">Cetak</button>
    <h1>Daftar Dispensasi</h1>
    <p>{{ $periodLabel }}: {{ $start->locale('id')->translatedFormat('d M Y') }} – {{ $end->locale('id')->translatedFormat('d M Y') }}</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Judul / Kegiatan</th>
                <th>Jenis</th>
                <th>Nama Siswa / NIS</th>
                <th>Pengaju</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dispensations as $dispensation)
                <tr>
                    <td>{{ $dispensation->date_start->format('d-m-Y') }}{{ $dispensation->date_end ? ' – '.$dispensation->date_end->format('d-m-Y') : '' }}</td>
                    <td>{{ $dispensation->title }}</td>
                    <td>{{ $dispensation->other_type ?: $dispensation->type->name }}</td>
                    <td>
                        @forelse($dispensation->recipients as $recipient)
                            {{ $recipient->student->user->name }} ({{ $recipient->student->nis }})@if(! $loop->last), @endif
                        @empty
                            —
                        @endforelse
                    </td>
                    <td>{{ $dispensation->applicant->name }}</td>
                    <td>{{ $dispensation->status }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada dispensasi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
