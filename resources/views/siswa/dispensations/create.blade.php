<x-app-layout>
    @php
        $dispensationTypes = \App\Models\DispensationType::where('category', 'STUDENT')->where('is_active', true)->get();
        $otherTypeId = $dispensationTypes->first(fn ($type) => mb_strtolower(trim($type->name)) === 'lainnya')?->id;
        $isOtherTypeSelected = $otherTypeId && (int) old('type_id') === $otherTypeId;
    @endphp
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-ink">Ajukan Dispensasi</h2>
            <p class="text-sm text-muted">Isi data dispensasi Anda.</p>
        </div>

        <form method="POST" action="{{ route('siswa.dispensations.store') }}" enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-surface border border-line rounded-xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-ink">Data Dispensasi</h3>
                <div x-data="{ isOtherType: @js($isOtherTypeSelected) }">
                    <x-ui.select label="Jenis Dispensasi" name="type_id" required :error="$errors->first('type_id')" @change="isOtherType = $event.target.selectedOptions[0].dataset.other === 'true'">
                        <option value="">Pilih jenis dispensasi</option>
                        @foreach($dispensationTypes as $type)
                            <option value="{{ $type->id }}" data-other="{{ mb_strtolower(trim($type->name)) === 'lainnya' ? 'true' : 'false' }}" @selected(old('type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <div x-show="isOtherType" x-cloak>
                        <x-ui.input class="mt-3" label="Nama kategori lainnya" name="other_type" :value="old('other_type')" x-bind:required="isOtherType" x-bind:disabled="!isOtherType" :error="$errors->first('other_type')" />
                    </div>
                </div>
                <x-ui.input label="Judul" name="title" required :error="$errors->first('title')" />
                <div class="grid sm:grid-cols-2 gap-4">
                    <x-ui.input label="Tanggal Mulai" name="date_start" type="date" required :error="$errors->first('date_start')" />
                    <x-ui.input label="Tanggal Selesai" name="date_end" type="date" :error="$errors->first('date_end')" />
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <x-ui.input label="Waktu Mulai" name="time_start" type="time" />
                    <x-ui.input label="Waktu Selesai" name="time_end" type="time" />
                </div>
                <x-ui.input label="Lokasi" name="location" />
                <x-ui.input label="Tujuan" name="destination" />
                <x-ui.input label="Alasan" name="reason" required :error="$errors->first('reason')" />
            </div>

            <div class="bg-surface border border-line rounded-xl p-5 space-y-4 h-fit">
                <x-signatory-picker :signatories="$schoolSignatories" />
                <h3 class="text-sm font-semibold text-ink">Bukti / Lampiran</h3>
                <x-ui.input label="File" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" help="PDF, JPG, PNG, DOC. Maks 5MB." :error="$errors->first('file')" />
                <x-ui.button type="submit" class="w-full">Kirim Pengajuan</x-ui.button>
            </div>
        </form>
    </div>
</x-app-layout>
