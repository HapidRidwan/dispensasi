<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6 lg:space-y-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-primary">Ringkasan sekolah</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-ink sm:text-3xl">Dashboard Admin</h1>
            <p class="mt-1.5 text-sm text-muted">Analitik dan daftar dispensasi menurut tanggal dispensasi.</p>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <x-cards.stat-card label="Pengajuan Menunggu" :value="$pending" />
            <x-cards.stat-card label="Disetujui" :value="$approved" />
            <x-cards.stat-card label="Total Siswa" :value="$students" />
            <x-cards.stat-card label="Dalam Periode" :value="$total" />
        </div>

        <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
            <div class="border-b border-line px-5 py-4 sm:px-6">
                <h2 class="text-base font-semibold tracking-tight text-ink">Analitik Dispensasi</h2>
                <p class="mt-1 text-xs text-muted">{{ $periodLabel }} · {{ $start->locale('id')->translatedFormat('d M Y') }} – {{ $end->locale('id')->translatedFormat('d M Y') }}</p>
            </div>
            <div class="space-y-5 p-5 sm:p-6">
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-end gap-3 print:hidden">
                    <div>
                        <label for="period" class="block text-sm font-medium text-ink">Tampilan</label>
                        <select id="period" name="period" class="mt-1 rounded-lg border-line text-sm">
                            <option value="day" @selected($period === 'day')>Harian (14 hari)</option>
                            <option value="week" @selected($period === 'week')>Mingguan (12 minggu)</option>
                            <option value="month" @selected($period === 'month')>Bulanan (12 bulan)</option>
                            <option value="year" @selected($period === 'year')>Tahunan (5 tahun)</option>
                        </select>
                    </div>
                    <x-ui.input label="Acuan tanggal" name="date" type="date" :value="$selectedDate" />
                    <x-ui.button type="submit">Terapkan</x-ui.button>
                    <a href="{{ route('admin.dashboard.print', ['period' => $period, 'date' => $selectedDate]) }}" target="_blank" class="inline-flex items-center justify-center rounded-lg border border-line bg-surface px-4 py-2 text-sm font-medium text-ink hover:bg-background">Cetak daftar</a>
                    <a href="{{ route('admin.dashboard.export', ['period' => $period, 'date' => $selectedDate]) }}" class="inline-flex items-center justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Unduh Excel</a>
                </form>

                <div class="space-y-2" role="img" aria-label="Grafik jumlah dispensasi {{ strtolower($periodLabel) }}">
                    @foreach($trend as $point)
                        <div class="grid grid-cols-[4.5rem_minmax(0,1fr)_2.5rem] items-center gap-3 text-xs">
                            <span class="text-right text-muted">{{ $point['label'] }}</span>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-primary" style="width: {{ $point['percentage'] }}%"></div>
                            </div>
                            <span class="text-right font-medium text-ink">{{ $point['total'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-ink">Daftar Dispensasi</h2>
                    <p class="mt-1 text-xs text-muted">{{ $total }} pengajuan pada rentang tanggal di atas.</p>
                </div>
                <a href="{{ route('admin.dispensations.index') }}" class="text-sm font-medium text-primary hover:text-primary-dark">Kelola semua pengajuan</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="border-b border-line bg-slate-50/70 text-left text-[11px] font-semibold uppercase tracking-[0.1em] text-muted">
                            <th class="px-5 py-3.5 font-medium">Tanggal</th>
                            <th class="px-5 py-3.5 font-medium">Judul / Kegiatan</th>
                            <th class="px-5 py-3.5 font-medium">Jenis</th>
                            <th class="px-5 py-3.5 font-medium">Siswa</th>
                            <th class="px-5 py-3.5 font-medium">Pengaju</th>
                            <th class="px-5 py-3.5 font-medium">Status</th>
                            <th class="px-5 py-3.5 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line/80">
                        @forelse($dispensations as $dispensation)
                            <tr class="align-top">
                                <td class="whitespace-nowrap px-5 py-4 text-muted">{{ $dispensation->date_start->format('d M Y') }}</td>
                                <td class="px-5 py-4 font-medium text-ink">{{ $dispensation->title }}</td>
                                <td class="px-5 py-4 text-muted">{{ $dispensation->other_type ?: $dispensation->type->name }}</td>
                                <td class="px-5 py-4 text-muted">
                                    @forelse($dispensation->recipients as $recipient)
                                        <div>{{ $recipient->student->user->name }} <span class="text-xs">({{ $recipient->student->nis }})</span></div>
                                    @empty
                                        —
                                    @endforelse
                                </td>
                                <td class="px-5 py-4 text-muted">{{ $dispensation->applicant->name }}</td>
                                <td class="px-5 py-4"><x-ui.badge :status="$dispensation->status">{{ $dispensation->status }}</x-ui.badge></td>
                                <td class="whitespace-nowrap px-5 py-4 text-right"><a href="{{ route('admin.dispensations.show', $dispensation) }}" class="font-medium text-primary hover:text-primary-dark">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><x-ui.empty-state title="Tidak ada dispensasi pada periode ini" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-line px-5 py-3">{{ $dispensations->links() }}</div>
        </section>
    </div>
</x-app-layout>
