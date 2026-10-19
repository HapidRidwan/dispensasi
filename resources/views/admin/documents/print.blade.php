<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->document_number }}</title>
    <style>
        body { color: #111827; font: 14px Arial, sans-serif; margin: 32px auto; max-width: 900px; padding: 0 24px; }
        h1, h2, p { margin: 0; }
        h1 { font-size: 20px; text-align: center; text-transform: uppercase; }
        .muted { color: #4b5563; }
        .document-header { border-bottom: 2px solid #111827; margin-bottom: 24px; padding-bottom: 16px; }
        .document-number { margin-top: 8px; text-align: center; }
        .details { border-collapse: collapse; margin-top: 24px; width: 100%; }
        .details th, .details td { padding: 8px; text-align: left; vertical-align: top; }
        .details th { width: 180px; }
        .signatures { display: grid; gap: 24px 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: 40px; }
        .signature { min-height: 160px; text-align: center; }
        .signature-space { height: 86px; }
        .signature-name { font-weight: 600; text-decoration: underline; }
        .actions { margin-bottom: 24px; text-align: right; }
        .actions a, .actions button { background: #1d4ed8; border: 0; border-radius: 6px; color: white; cursor: pointer; display: inline-block; padding: 10px 14px; text-decoration: none; }
        @media print {
            body { margin: 0; max-width: none; padding: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <a href="{{ route('admin.documents.show', $document) }}">Kembali</a>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <header class="document-header">
        <h1>Surat Dispensasi</h1>
        <p class="document-number">Nomor: {{ $document->document_number }}</p>
    </header>

    <h2>{{ $document->dispensation->title }}</h2>
    <table class="details">
        <tbody>
            <tr><th>Jenis</th><td>{{ $document->dispensation->other_type ?: $document->dispensation->type?->name }}</td></tr>
            <tr><th>Tanggal</th><td>{{ $document->dispensation->date_start?->format('d M Y') }}{{ $document->dispensation->date_end ? ' — '.$document->dispensation->date_end->format('d M Y') : '' }}</td></tr>
            <tr><th>Waktu</th><td>{{ $document->dispensation->time_start ?? '-' }}{{ $document->dispensation->time_end ? ' — '.$document->dispensation->time_end : '' }}</td></tr>
            <tr><th>Lokasi</th><td>{{ $document->dispensation->location ?? '-' }}</td></tr>
            <tr><th>Keperluan</th><td>{{ $document->dispensation->reason }}</td></tr>
            <tr>
                <th>Peserta</th>
                <td>
                    @forelse($document->dispensation->recipients as $recipient)
                        <div>{{ $recipient->student->user->name }}</div>
                    @empty
                        -
                    @endforelse
                </td>
            </tr>
        </tbody>
    </table>

    <section class="signatures">
        @foreach($document->dispensation->signature_details ?? [] as $signatory)
            <div class="signature">
                <p>{{ $signatory['position'] }}</p>
                <div class="signature-space"></div>
                <p class="signature-name">{{ $signatory['name'] }}</p>
            </div>
        @endforeach
        @foreach($document->dispensation->responsibles as $responsible)
            <div class="signature">
                <p>{{ str_replace('_', ' ', $responsible->responsibility_type) }}</p>
                <div class="signature-space"></div>
                <p class="signature-name">{{ $responsible->teacher->user->name }}</p>
            </div>
        @endforeach
    </section>
</body>
</html>
