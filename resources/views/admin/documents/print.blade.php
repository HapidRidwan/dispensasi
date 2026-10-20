<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->document_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { background: #e5e7eb; color: #111827; font: 14px "Times New Roman", serif; margin: 0; }
        h1, h2, p { margin: 0; }
        .toolbar { background: #fff; border-bottom: 1px solid #d1d5db; display: grid; gap: 16px; margin: 0 auto 24px; max-width: 900px; padding: 16px 20px; }
        .toolbar-heading { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .toolbar-title { font: 600 16px Arial, sans-serif; }
        .toolbar-description, .field-label, .field-help, .field-error { font: 12px Arial, sans-serif; }
        .toolbar-description, .field-help { color: #4b5563; }
        .toolbar-fields { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .field { display: grid; gap: 6px; }
        .field-label { font-weight: 600; }
        .field input[type="text"], .field input[type="file"] { border: 1px solid #9ca3af; border-radius: 4px; font: 13px Arial, sans-serif; min-width: 0; padding: 8px; width: 100%; }
        .field-error { color: #b91c1c; min-height: 14px; }
        .toolbar-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .toolbar a, .toolbar button { background: #1d4ed8; border: 0; border-radius: 4px; color: #fff; cursor: pointer; font: 13px Arial, sans-serif; padding: 9px 13px; text-decoration: none; }
        .toolbar a { background: #4b5563; }
        .paper { background: #fff; margin: 0 auto 24px; max-width: 210mm; min-height: 297mm; padding: 18mm 20mm; }
        .letterhead { align-items: center; display: grid; gap: 12px; grid-template-columns: 76px minmax(0, 1fr) 76px; padding-bottom: 10px; text-align: center; }
        .letterhead-logo { height: 70px; object-fit: contain; width: 70px; }
        .letterhead-name { font-size: 15px; font-weight: 700; line-height: 1.2; text-transform: uppercase; }
        .letterhead-school { font-size: 17px; font-weight: 700; line-height: 1.25; text-transform: uppercase; }
        .letterhead-unit { font-size: 12px; font-weight: 700; line-height: 1.3; text-transform: uppercase; }
        .letterhead-address { font-size: 10px; line-height: 1.25; margin-top: 3px; }
        .letterhead-rule { border-bottom: 3px double #111827; height: 5px; }
        .letter-date { margin: 22px 0 14px; text-align: right; }
        .metadata { border-collapse: collapse; margin-bottom: 18px; }
        .metadata td { padding: 2px 6px 2px 0; vertical-align: top; }
        .metadata td:first-child { min-width: 82px; }
        .metadata td:nth-child(2) { padding-right: 8px; }
        .recipient { line-height: 1.5; margin-bottom: 18px; }
        .subject { font-weight: 700; text-decoration: underline; }
        .body-copy { line-height: 1.5; margin: 0 0 12px; text-align: justify; text-indent: 36px; }
        .event-details { border-collapse: collapse; margin: 0 0 14px 36px; }
        .event-details td { padding: 2px 8px 2px 0; vertical-align: top; }
        .event-details td:first-child { min-width: 105px; }
        .participant-list { margin: 0; padding-left: 24px; }
        .participant-list li { padding: 1px 0; }
        .signatures { break-inside: avoid; display: grid; gap: 22px 30px; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: 34px; }
        .signature { break-inside: avoid; line-height: 1.35; min-height: 112px; text-align: center; }
        .signature-space { height: 62px; }
        .signature-name { font-weight: 700; text-decoration: underline; }
        @media (max-width: 700px) {
            .toolbar { margin-bottom: 12px; }
            .toolbar-fields { grid-template-columns: 1fr; }
            .paper { min-height: 0; padding: 24px 20px; }
            .letterhead { gap: 6px; grid-template-columns: 48px minmax(0, 1fr) 48px; }
            .letterhead-logo { height: 46px; width: 46px; }
            .letterhead-name { font-size: 11px; }
            .letterhead-school { font-size: 12px; }
            .letterhead-unit { font-size: 9px; }
            .letterhead-address { font-size: 8px; }
        }
        @media print {
            @page { size: A4; margin: 18mm 20mm; }
            body { background: #fff; }
            .toolbar { display: none; }
            .paper { margin: 0; max-width: none; min-height: 0; padding: 0; }
        }
    </style>
</head>
<body>
    @php
        $dispensation = $document->dispensation;
        $signatureDetails = $dispensation->signature_details ?? [];
        $responsibles = $dispensation->responsibles;
    @endphp

    <section class="toolbar" aria-label="Pengaturan cetak surat">
        <div class="toolbar-heading">
            <div>
                <h1 class="toolbar-title">Pengaturan kop surat</h1>
                <p class="toolbar-description">Perubahan logo dan identitas hanya berlaku pada tampilan cetak ini.</p>
            </div>
            <div class="toolbar-actions">
                <a href="{{ route('admin.documents.show', $document) }}">Kembali</a>
                <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
            </div>
        </div>
        <div class="toolbar-fields">
            <label class="field">
                <span class="field-label">Logo kiri (default yayasan.png)</span>
                <input type="file" accept="image/png,image/jpeg,image/webp" data-logo-input="left-logo">
                <span class="field-help">Pilih logo yayasan atau organisasi. Maksimal 2 MB.</span>
                <span class="field-error" data-logo-error="left-logo" role="status"></span>
            </label>
            <label class="field">
                <span class="field-label">Logo kanan (opsional)</span>
                <input type="file" accept="image/png,image/jpeg,image/webp" data-logo-input="right-logo">
                <span class="field-help">Pilih logo sekolah atau unit organisasi. Maksimal 2 MB.</span>
                <span class="field-error" data-logo-error="right-logo" role="status"></span>
            </label>
            <label class="field">
                <span class="field-label">Nama yayasan / organisasi</span>
                <input type="text" value="YAYASAN PENDIDIKAN SUMEDANG (YPS)" data-letterhead-input="letterhead-name">
            </label>
            <label class="field">
                <span class="field-label">Nama sekolah / unit</span>
                <input type="text" value="SMK INFORMATIKA SUMEDANG" data-letterhead-input="letterhead-school">
            </label>
            <label class="field">
                <span class="field-label">Nama organisasi / bagian</span>
                <input type="text" value="ORGANISASI SISWA INTRA SEKOLAH (OSIS)" data-letterhead-input="letterhead-unit">
            </label>
            <label class="field">
                <span class="field-label">Alamat dan kontak</span>
                <input type="text" value="Jl. Angkrek Situ No. 19, Sumedang Utara, 45353" data-letterhead-input="letterhead-address">
            </label>
        </div>
    </section>

    <main class="paper">
        <header class="letterhead">
            <img class="letterhead-logo" id="left-logo" src="{{ asset('images/icon/yayasan.png') }}" alt="Logo yayasan">
            <div>
                <p class="letterhead-name" id="letterhead-name">YAYASAN PENDIDIKAN SUMEDANG (YPS)</p>
                <p class="letterhead-school" id="letterhead-school">SMK INFORMATIKA SUMEDANG</p>
                <p class="letterhead-unit" id="letterhead-unit">ORGANISASI SISWA INTRA SEKOLAH (OSIS)</p>
                <p class="letterhead-address" id="letterhead-address">Jl. Angkrek Situ No. 19, Sumedang Utara, 45353</p>
            </div>
            <img class="letterhead-logo" id="right-logo" alt="Logo sekolah atau organisasi" hidden>
        </header>
        <div class="letterhead-rule"></div>

        <p class="letter-date">Sumedang, {{ $document->generated_at?->locale('id')->translatedFormat('d F Y') }}</p>

        <table class="metadata">
            <tbody>
                <tr><td>Nomor</td><td>:</td><td>{{ $document->document_number }}</td></tr>
                <tr><td>Lampiran</td><td>:</td><td>-</td></tr>
                <tr><td>Perihal</td><td>:</td><td class="subject">Dispensasi Kegiatan</td></tr>
            </tbody>
        </table>

        <div class="recipient">
            <p>Yth. Bapak/Ibu Guru / Pihak Terkait</p>
            <p>di tempat</p>
        </div>

        <p class="body-copy">Dengan hormat,</p>
        <p class="body-copy">
            Dalam rangka mengikuti kegiatan <strong>{{ $dispensation->title }}</strong>, kami memohon izin dispensasi bagi peserta didik berikut untuk mengikuti kegiatan tersebut:
        </p>

        <ol class="participant-list">
            @forelse($dispensation->recipients as $recipient)
                <li>{{ $recipient->student->user->name }}</li>
            @empty
                <li>{{ $dispensation->applicant->name }}</li>
            @endforelse
        </ol>

        <table class="event-details">
            <tbody>
                <tr>
                    <td>Hari / tanggal</td><td>:</td>
                    <td>{{ $dispensation->date_start?->locale('id')->translatedFormat('l, d F Y') }}{{ $dispensation->date_end ? ' s.d. '.$dispensation->date_end->locale('id')->translatedFormat('l, d F Y') : '' }}</td>
                </tr>
                <tr><td>Waktu</td><td>:</td><td>{{ $dispensation->time_start ?? '-' }}{{ $dispensation->time_end ? ' – '.$dispensation->time_end : '' }}</td></tr>
                <tr><td>Tempat</td><td>:</td><td>{{ $dispensation->location ?: $dispensation->destination ?: '-' }}</td></tr>
                <tr><td>Keperluan</td><td>:</td><td>{{ $dispensation->reason }}</td></tr>
            </tbody>
        </table>

        <p class="body-copy">
            Demikian surat dispensasi ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.
        </p>

        @if(count($signatureDetails) > 0 || $responsibles->isNotEmpty())
            <section class="signatures" aria-label="Tanda tangan">
                @foreach($signatureDetails as $signatory)
                    <div class="signature">
                        <p>{{ $signatory['position'] }}</p>
                        <div class="signature-space"></div>
                        <p class="signature-name">{{ $signatory['name'] }}</p>
                    </div>
                @endforeach
                @foreach($responsibles as $responsible)
                    <div class="signature">
                        <p>{{ str_replace('_', ' ', $responsible->responsibility_type) }}</p>
                        <div class="signature-space"></div>
                        <p class="signature-name">{{ $responsible->teacher->user->name }}</p>
                    </div>
                @endforeach
            </section>
        @endif
    </main>

    <script>
        document.querySelectorAll('[data-letterhead-input]').forEach((input) => {
            input.addEventListener('input', () => {
                const target = document.getElementById(input.dataset.letterheadInput);
                target.textContent = input.value.trim();
            });
        });

        document.querySelectorAll('[data-logo-input]').forEach((input) => {
            input.addEventListener('change', () => {
                const targetId = input.dataset.logoInput;
                const image = document.getElementById(targetId);
                const error = document.querySelector(`[data-logo-error="${targetId}"]`);
                const file = input.files[0];

                error.textContent = '';
                if (!file) {
                    if (targetId === 'right-logo') {
                        image.removeAttribute('src');
                        image.hidden = true;
                    } else {
                        image.src = @json(asset('images/icon/yayasan.png'));
                    }

                    return;
                }

                if (!['image/png', 'image/jpeg', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
                    input.value = '';
                    error.textContent = 'Pilih gambar PNG, JPG, atau WEBP berukuran maksimal 2 MB.';

                    return;
                }

                const reader = new FileReader();
                reader.addEventListener('load', () => {
                    image.src = reader.result;
                    image.hidden = false;
                });
                reader.addEventListener('error', () => {
                    error.textContent = 'Logo tidak dapat dibaca. Silakan pilih file gambar lain.';
                });
                reader.readAsDataURL(file);
            });
        });
    </script>
</body>
</html>
