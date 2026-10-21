@php
    $student = $dispensation->recipients->first()?->student;
    $class = $student?->enrollments->first()?->class?->name ?? '-';
    $picketTeacher = $dispensation->picketAssignment?->teacher?->user?->name;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keterangan Izin - {{ $student?->user?->name ?? $dispensation->title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e8ebed; color: #24282b; font-family: Arial, Helvetica, sans-serif; }
        .toolbar { display: flex; justify-content: center; padding: 16px; }
        .toolbar button { border: 0; border-radius: 4px; background: #174a64; color: white; cursor: pointer; font-size: 14px; padding: 10px 18px; }
        .paper { width: 148mm; min-height: 210mm; margin: 0 auto 20px; padding: 10mm 11mm; background: white; box-shadow: 0 3px 18px #17212b24; }
        .school-header { display: grid; grid-template-columns: 22mm 1fr 22mm; align-items: center; gap: 4mm; border-bottom: 2px solid #4b5357; padding-bottom: 3mm; text-align: center; }
        .school-header img { width: 20mm; height: 20mm; object-fit: contain; }
        .school-header p { margin: 0; font-size: 9pt; line-height: 1.35; }
        .school-header strong { display: block; margin: 1mm 0; font-size: 12pt; }
        .school-header .school-name { font-size: 10pt; font-weight: 700; }
        .address { padding: 2mm 0; border-bottom: 1px solid #8b9295; text-align: center; font-size: 8pt; }
        h1 { margin: 8mm 0 1mm; text-align: center; font-size: 13pt; }
        .subtitle { margin: 0 0 7mm; text-align: center; font-size: 10pt; }
        .selected { font-weight: 700; }
        .not-selected { text-decoration: line-through; }
        .intro, .closing { font-size: 9.5pt; line-height: 1.5; }
        .fields { display: grid; grid-template-columns: 30mm 4mm 1fr; row-gap: 3mm; margin: 3mm 0 12mm; font-size: 10pt; }
        .value { min-height: 6mm; border-bottom: 1px dotted #73797c; font-weight: 600; overflow-wrap: anywhere; }
        .signature-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18mm; margin-top: 9mm; text-align: center; font-size: 9pt; }
        .signature { min-height: 34mm; }
        .signature-name { margin-top: 22mm; border-top: 1px solid #565b5e; padding-top: 1mm; }
        @page { size: A5 portrait; margin: 0; }
        @media print {
            body { background: white; }
            .toolbar { display: none; }
            .paper { width: 148mm; min-height: 210mm; margin: 0; box-shadow: none; }
        }
        @media screen and (max-width: 600px) {
            .paper { width: min(100%, 148mm); min-height: 0; padding: 7mm; }
            .school-header { grid-template-columns: 15mm 1fr 15mm; gap: 2mm; }
            .school-header img { width: 14mm; height: 14mm; }
            .school-header strong { font-size: 10pt; }
            .signature-row { gap: 8mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Cetak Kartu Izin</button></div>
    <main class="paper">
        <header class="school-header">
            <img src="{{ asset('images/icon/icon.png') }}" alt="Logo sekolah">
            <div>
                <p>YAYASAN PENDIDIKAN SUMEDANG</p>
                <strong>SEKOLAH MENENGAH KEJURUAN</strong>
                <p class="school-name">SMK INFORMATIKA SUMEDANG</p>
            </div>
            <img src="{{ asset('images/icon/yayasan.png') }}" alt="Logo yayasan">
        </header>
        <div class="address">Jalan Angkrek Situ No. 19 Sumedang 45323 · Telp. (0261) 202757</div>

        <h1>KETERANGAN IZIN</h1>
        <p class="subtitle"><span class="{{ $dispensation->permit_direction === 'KELUAR' ? 'selected' : 'not-selected' }}">KELUAR</span> / <span class="{{ $dispensation->permit_direction === 'MASUK' ? 'selected' : 'not-selected' }}">MASUK</span> KELAS</p>

        <p class="intro">Memberikan izin kepada:</p>
        <div class="fields">
            <span>Nama</span><span>:</span><span class="value">{{ $student?->user?->name ?? '-' }}</span>
            <span>Kelas</span><span>:</span><span class="value">{{ $class }}</span>
            <span>Hari, tanggal</span><span>:</span><span class="value">{{ ucfirst($dispensation->date_start->locale('id')->translatedFormat('l, d F Y')) }}</span>
            <span>Jam</span><span>:</span><span class="value">{{ $dispensation->time_start ? substr($dispensation->time_start, 0, 5) : '-' }}{{ $dispensation->time_end ? ' - '.substr($dispensation->time_end, 0, 5) : '' }}</span>
            <span>Alasan</span><span>:</span><span class="value">{{ $dispensation->reason }}</span>
        </div>

        <p class="closing">Demikian keterangan ini untuk digunakan sebagaimana mestinya.</p>
        <div class="signature-row">
            <div class="signature">
                <div>Security,</div>
                <div class="signature-name">&nbsp;</div>
            </div>
            <div class="signature">
                <div>Sumedang, {{ $dispensation->date_start->locale('id')->translatedFormat('d F Y') }}</div>
                <div>Guru Piket,</div>
                <div class="signature-name">{{ $picketTeacher ?? '( ................................ )' }}</div>
            </div>
        </div>
    </main>
</body>
</html>