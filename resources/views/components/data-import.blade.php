@props(['dataset', 'title', 'description'])

<section id="import-{{ $dataset }}" class="rounded-2xl border border-blue-100 bg-blue-50/60 p-4 sm:p-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="max-w-xl">
            <h3 class="text-sm font-semibold text-ink">{{ $title }}</h3>
            <p class="mt-1 text-sm text-muted">{{ $description }}</p>
            <p class="mt-2 text-xs leading-relaxed text-muted">Gunakan template aplikasi. Kolom Excel dipetakan berdasarkan judul kolom, bukan nama tabel database. Data diimpor sebagai satu transaksi; jika ada baris tidak valid, tidak ada data yang disimpan.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row lg:flex-shrink-0">
            <a href="{{ route('admin.data-import.templates', $dataset) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-line bg-white px-4 py-2.5 text-sm font-medium text-primary shadow-sm transition hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3"/>
                </svg>
                Unduh template
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.data-import.store') }}" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3 border-t border-blue-100 pt-4 sm:flex-row sm:items-end">
        @csrf
        <input type="hidden" name="dataset" value="{{ $dataset }}">
        <div class="min-w-0 flex-1">
            <label for="file-{{ $dataset }}" class="mb-1.5 block text-xs font-medium text-ink">File Excel (.xlsx, maksimal 10 MB)</label>
            <input id="file-{{ $dataset }}" type="file" name="file" accept=".xlsx" required class="block w-full rounded-xl border border-line bg-white text-sm text-muted file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2.5 file:text-sm file:font-medium file:text-ink hover:file:bg-blue-50">
            @error('file')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            @error('dataset')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <x-ui.button type="submit" class="shrink-0">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 16V4m0 0L8 8m4-4 4 4M5 14v5h14v-5"/>
            </svg>
            Import data
        </x-ui.button>
    </form>
</section>
