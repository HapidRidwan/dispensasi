@props(['signatories'])

<div class="bg-surface border border-line rounded-xl p-5 space-y-3">
    <div>
        <h3 class="text-sm font-semibold text-ink">Penandatangan Dokumen</h3>
        <p class="text-xs text-muted">Pilih satu atau lebih nama. Daftar dikelola admin.</p>
    </div>

    @forelse($signatories as $signatory)
        <label class="flex items-start gap-3 rounded-lg border border-line px-3 py-2 text-sm text-ink">
            <input
                type="checkbox"
                name="signatory_ids[]"
                value="{{ $signatory->id }}"
                @checked(in_array($signatory->id, (array) old('signatory_ids', [])))
                class="mt-0.5 rounded border-line text-primary focus:ring-primary/30"
            >
            <span>{{ $signatory->position }} — {{ $signatory->name }}</span>
        </label>
    @empty
        <p class="text-sm text-muted">Belum ada nama penandatangan. Hubungi admin untuk menambahkan daftar.</p>
    @endforelse

    @error('signatory_ids')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
    @error('signatory_ids.*')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
