@php
    $inputBase = 'w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all';
    $selectBase = $inputBase . ' appearance-none pr-8';
    $labelClass = 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5';
    $errClass = 'text-[11px] text-red-600 font-medium mt-0.5 block';
    $invalidBorder = ' border-red-300 bg-red-50';
    $isCreate = empty($data);
    $hasGpsCol = \Illuminate\Support\Facades\Schema::hasColumn('trafficdalkot', 'koordinat');
    $latVal = old('lat');
    $lngVal = old('lng');
    if ($latVal === null && $lngVal === null && ! empty(optional($data ?? null)->koordinat ?? '') && str_contains((string) optional($data ?? null)->koordinat, ',')) {
        [$latVal, $lngVal] = array_map('trim', explode(',', (string) optional($data ?? null)->koordinat, 2));
    }
    $waktuVal = old('waktu');
    if ($waktuVal === null) {
        $waktuVal = optional($data ?? null)->waktu
            ? substr((string) optional($data ?? null)->waktu, 0, 5)
            : \Carbon\Carbon::now('Asia/Jakarta')->format('H:i');
    }
    $formSuffix = $isCreate ? 'create' : 'edit';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>

    <div>
        <label for="waktu" class="{{ $labelClass }}">Waktu</label>
        <div class="flex gap-2">
            <input type="time" name="waktu" id="waktu"
                class="{{ $inputBase }} flex-1 {{ $errors->has('waktu') ? $invalidBorder : '' }}"
                value="{{ $waktuVal }}">
            <button type="button" id="btn_fetch_analytic" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[12px] font-bold border border-slate-200 transition-colors whitespace-nowrap flex items-center gap-1" title="Tarik data dari CCTV">
                <span class="material-symbols-outlined text-[16px]">sync</span>
            </button>
        </div>
        @error('waktu')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="jenisjalan" class="{{ $labelClass }}">Jenis Jalan <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="jenisjalan" id="jenisjalan" required class="{{ $selectBase }}{{ $errors->has('jenisjalan') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($jenisJalanList as $j)
                    <option value="{{ $j }}" {{ old('jenisjalan', optional($data ?? null)->jenisjalan ?? '') == $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('jenisjalan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <x-sbo-form-divider icon="route" title="Lokasi & arah" class="sm:col-span-2 lg:col-span-3 !pt-0 !border-t-0" />

    <div>
        <label for="gerbangkota" class="{{ $labelClass }}">Gerbang Kota <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="gerbangkota" id="gerbangkota" required class="{{ $selectBase }}{{ $errors->has('gerbangkota') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($gerbangKotaList ?? ['UTARA', 'SELATAN', 'TIMUR', 'BARAT'] as $g)
                    <option value="{{ $g }}" {{ old('gerbangkota', optional($data ?? null)->gerbangkota ?? '') == $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('gerbangkota')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="araharus" class="{{ $labelClass }}">Arah Arus <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="araharus" id="araharus" required class="{{ $selectBase }}{{ $errors->has('araharus') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($arahArusList ?? ['Masuk', 'Keluar'] as $a)
                    <option value="{{ $a }}" {{ old('araharus', optional($data ?? null)->araharus ?? '') == $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('araharus')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div class="sm:col-span-2 lg:col-span-1">
        <label for="namajalan" class="{{ $labelClass }}">Nama Jalan <span class="text-red-500">*</span></label>
        <input type="text" name="namajalan" id="namajalan" required maxlength="100"
            class="{{ $inputBase }}{{ $errors->has('namajalan') ? $invalidBorder : '' }}"
            value="{{ old('namajalan', optional($data ?? null)->namajalan ?? '') }}" placeholder="Nama ruas / gerbang"
            data-sbo-jalan-ac>
        @error('namajalan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <x-sbo-form-divider icon="directions_car" title="Volume kendaraan" class="sm:col-span-2 lg:col-span-3" />

    <div>
        <label for="traffics_r2" class="{{ $labelClass }}">R2 <span class="text-red-500">*</span></label>
        <input type="number" min="0" name="r2" id="traffics_r2" required
            class="{{ $inputBase }}{{ $errors->has('r2') ? $invalidBorder : '' }}"
            value="{{ old('r2', optional($data ?? null)->r2 ?? 0) }}">
        @error('r2')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="traffics_r4" class="{{ $labelClass }}">R4 <span class="text-red-500">*</span></label>
        <input type="number" min="0" name="r4" id="traffics_r4" required
            class="{{ $inputBase }}{{ $errors->has('r4') ? $invalidBorder : '' }}"
            value="{{ old('r4', optional($data ?? null)->r4 ?? 0) }}">
        @error('r4')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="traffics_r6" class="{{ $labelClass }}">R6 <span class="text-red-500">*</span></label>
        <input type="number" min="0" name="r6" id="traffics_r6" required
            class="{{ $inputBase }}{{ $errors->has('r6') ? $invalidBorder : '' }}"
            value="{{ old('r6', optional($data ?? null)->r6 ?? 0) }}">
        @error('r6')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="traffics_r6_plus" class="{{ $labelClass }}">R6+</label>
        <input type="number" min="0" name="r6_plus" id="traffics_r6_plus"
            class="{{ $inputBase }}{{ $errors->has('r6_plus') ? $invalidBorder : '' }}"
            value="{{ old('r6_plus', optional($data ?? null)->r6_plus ?? 0) }}">
        @error('r6_plus')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div class="sm:col-span-2 lg:col-span-3">
        <label for="traffics_jumlah_preview" class="{{ $labelClass }}">Jumlah (otomatis)</label>
        <input type="text" id="traffics_jumlah_preview" readonly
            class="{{ $inputBase }} bg-slate-100 text-slate-600 cursor-default"
            value="{{ old('jumlah', optional($data ?? null)->jumlah ?? 0) }}">
        <p class="text-[10px] text-slate-400 mt-0.5 m-0">R2 + R4 + R6 + R6+</p>
    </div>

    <div class="sm:col-span-2 lg:col-span-3">
        <label for="ketcb" class="{{ $labelClass }}">Keterangan CB</label>
        <textarea name="ketcb" id="ketcb" rows="2"
            class="{{ $inputBase }} resize-y min-h-[64px]{{ $errors->has('ketcb') ? $invalidBorder : '' }}"
            placeholder="Keterangan kondisi lalu lintas">{{ old('ketcb', optional($data ?? null)->ketcb ?? '') }}</textarea>
        @error('ketcb')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    @if($hasGpsCol)
        <x-sbo-form-divider icon="my_location" title="Koordinat GPS" class="sm:col-span-2 lg:col-span-3" />
        <div class="sm:col-span-2 lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label for="traffics_lat" class="{{ $labelClass }}">Latitude</label>
                <input type="number" step="any" name="lat" id="traffics_lat"
                    class="{{ $inputBase }}{{ $errors->has('lat') ? $invalidBorder : '' }}"
                    value="{{ $latVal }}" placeholder="-7.xxx" autocomplete="off">
            </div>
            <div>
                <label for="traffics_lng" class="{{ $labelClass }}">Longitude</label>
                <input type="number" step="any" name="lng" id="traffics_lng"
                    class="{{ $inputBase }}{{ $errors->has('lng') ? $invalidBorder : '' }}"
                    value="{{ $lngVal }}" placeholder="110.xxx" autocomplete="off">
            </div>
            <div class="flex items-end">
                <button type="button" id="btn_gps_traffics_form"
                    class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                    data-sbo-gps-lat="traffics_lat"
                    data-sbo-gps-lng="traffics_lng"
                    data-sbo-gps-status="gps_status_traffics_form">
                    <span class="material-symbols-outlined text-[16px]">my_location</span>
                    <span>Ambil GPS</span>
                </button>
            </div>
            <span class="sr-only" id="gps_status_traffics_form"></span>
            @error('lat')<span class="{{ $errClass }} sm:col-span-3">{{ $message }}</span>@enderror
            @error('lng')<span class="{{ $errClass }} sm:col-span-3">{{ $message }}</span>@enderror
        </div>
    @endif

    <div class="sm:col-span-2 lg:col-span-3">
        <label for="fotosit_file" class="{{ $labelClass }}">Foto Situasi</label>
        @if(! empty(optional($data ?? null)->fotosit ?? ''))
            @php
                $__foto = trim(explode(',', (string) optional($data ?? null)->fotosit, 2)[0]);
                $__fotoUrl = preg_match('#^https?://#i', $__foto) ? $__foto : asset(ltrim($__foto, '/'));
            @endphp
            <a href="{{ $__fotoUrl }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                <img src="{{ $__fotoUrl }}" alt="Foto situasi" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
            </a>
        @endif
        <label for="fotosit_file_{{ $formSuffix }}"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('fotosit_file') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="fotosit_file" id="fotosit_file_{{ $formSuffix }}" class="visually-hidden"
            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" capture="environment"
            data-sbo-media-preview
            @if($hasGpsCol) data-sbo-foto-gps data-sbo-foto-gps-lat="traffics_lat" data-sbo-foto-gps-lng="traffics_lng" data-sbo-foto-gps-coord-status="gps_status_traffics_form" @endif
            data-sbo-preview-img="#traffics_foto_preview_{{ $formSuffix }}"
            data-sbo-preview-wrap="#traffics_foto_preview_wrap_{{ $formSuffix }}"
            data-sbo-max-mb="8">
        @error('fotosit_file')<span class="{{ $errClass }}">{{ $message }}</span>@enderror

        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="traffics_foto_preview_wrap_{{ $formSuffix }}">
            <img id="traffics_foto_preview_{{ $formSuffix }}" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>
    </div>
</div>

<div id="analytic_preview_container" class="mt-5 d-none">
    <x-sbo-form-divider icon="table_chart" title="Data CCTV Terkait" class="!border-t-0 !pt-0" />
    <div class="overflow-x-auto border border-slate-200 rounded-xl bg-white">
        <table class="w-full text-left text-[11px] text-slate-600">
            <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px]">
                <tr>
                    <th class="px-3 py-2 border-b border-slate-200">Jam</th>
                    <th class="px-3 py-2 border-b border-slate-200">Gerbang</th>
                    <th class="px-3 py-2 border-b border-slate-200">Arah</th>
                    <th class="px-3 py-2 border-b border-slate-200">Jalan / CCTV</th>
                    <th class="px-3 py-2 border-b border-slate-200 text-center">R2</th>
                    <th class="px-3 py-2 border-b border-slate-200 text-center">R4</th>
                    <th class="px-3 py-2 border-b border-slate-200 text-center">R6+</th>
                    <th class="px-3 py-2 border-b border-slate-200 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="analytic_preview_tbody" class="divide-y divide-slate-100">
                <!-- Rows will be populated by JS -->
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function updateJumlah() {
        var r2 = parseInt(document.getElementById('traffics_r2')?.value || '0', 10) || 0;
        var r4 = parseInt(document.getElementById('traffics_r4')?.value || '0', 10) || 0;
        var r6 = parseInt(document.getElementById('traffics_r6')?.value || '0', 10) || 0;
        var r6Plus = parseInt(document.getElementById('traffics_r6_plus')?.value || '0', 10) || 0;
        var el = document.getElementById('traffics_jumlah_preview');
        if (el) {
            el.value = r2 + r4 + r6 + r6Plus;
        }
    }
    ['traffics_r2', 'traffics_r4', 'traffics_r6', 'traffics_r6_plus'].forEach(function (id) {
        document.getElementById(id)?.addEventListener('input', updateJumlah);
    });
    updateJumlah();

    var fetchBtn = document.getElementById('btn_fetch_analytic');
    var previewContainer = document.getElementById('analytic_preview_container');
    var previewTbody = document.getElementById('analytic_preview_tbody');

    if (fetchBtn) {
        fetchBtn.addEventListener('click', function () {
            var dateInput = document.querySelector('input[name="tanggal"]');
            var tglVal = dateInput ? dateInput.value : '';
            var waktu = document.getElementById('waktu').value;
            var gerbangkota = document.getElementById('gerbangkota').value;

            if (!tglVal || !waktu) {
                alert('Pilih tanggal dan waktu terlebih dahulu untuk menarik data dari integrasi CCTV.');
                return;
            }

            var originalHtml = fetchBtn.innerHTML;
            fetchBtn.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-spin">sync</span>';
            fetchBtn.disabled = true;
            
            previewContainer.classList.add('d-none');
            previewTbody.innerHTML = '';

            var qs = '?tanggal=' + encodeURIComponent(tglVal) + '&waktu=' + encodeURIComponent(waktu);

            fetch('{{ route("api.fetch-analytic") }}' + qs)
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    fetchBtn.innerHTML = originalHtml;
                    fetchBtn.disabled = false;
                    
                    if (res.found && res.items && res.items.length > 0) {
                        previewContainer.classList.remove('d-none');
                        
                        res.items.forEach(function(item, index) {
                            var tr = document.createElement('tr');
                            tr.className = 'hover:bg-slate-50';
                            
                            var tdJam = document.createElement('td'); tdJam.className = 'px-3 py-2 font-medium text-slate-800'; tdJam.textContent = item.jam;
                            var tdGerbang = document.createElement('td'); tdGerbang.className = 'px-3 py-2'; tdGerbang.textContent = item.category || '—';
                            var tdArah = document.createElement('td'); tdArah.className = 'px-3 py-2'; tdArah.textContent = item.status_cctv || '—';
                            
                            var tdJalan = document.createElement('td'); tdJalan.className = 'px-3 py-2'; 
                            tdJalan.innerHTML = '<span class="block text-slate-800 font-medium">' + (item.keterangan || '—') + '</span><span class="block text-[10px] text-slate-400">' + (item.nama_cctv || '') + '</span>';
                            
                            var tdR2 = document.createElement('td'); tdR2.className = 'px-3 py-2 text-center'; tdR2.textContent = item.motor;
                            var tdR4 = document.createElement('td'); tdR4.className = 'px-3 py-2 text-center'; tdR4.textContent = item.minibus;
                            var tdR6 = document.createElement('td'); tdR6.className = 'px-3 py-2 text-center font-bold text-slate-700'; tdR6.textContent = item.bus_truck;
                            
                            var tdAksi = document.createElement('td'); tdAksi.className = 'px-3 py-2 text-center';
                            var btnGunakan = document.createElement('button');
                            btnGunakan.type = 'button';
                            btnGunakan.className = 'px-2 py-1 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 rounded text-[10px] font-bold';
                            btnGunakan.textContent = 'Gunakan';
                            btnGunakan.onclick = function() {
                                if (document.getElementById('traffics_r2')) document.getElementById('traffics_r2').value = item.motor;
                                if (document.getElementById('traffics_r4')) document.getElementById('traffics_r4').value = item.minibus;
                                if (document.getElementById('traffics_r6')) document.getElementById('traffics_r6').value = item.bus_truck;
                                if (document.getElementById('traffics_r6_plus')) document.getElementById('traffics_r6_plus').value = 0;
                                if (item.status_cctv && document.getElementById('araharus')) document.getElementById('araharus').value = item.status_cctv;
                                if (item.keterangan && document.getElementById('jenisjalan')) document.getElementById('jenisjalan').value = item.keterangan;
                                if (item.nama_cctv && document.getElementById('namajalan')) document.getElementById('namajalan').value = item.nama_cctv;
                                if (item.category && document.getElementById('gerbangkota')) document.getElementById('gerbangkota').value = item.category;
                                updateJumlah();
                                alert('Data terpilih berhasil dimasukkan ke dalam form.');
                            };
                            tdAksi.appendChild(btnGunakan);
                            
                            tr.appendChild(tdJam);
                            tr.appendChild(tdGerbang);
                            tr.appendChild(tdArah);
                            tr.appendChild(tdJalan);
                            tr.appendChild(tdR2);
                            tr.appendChild(tdR4);
                            tr.appendChild(tdR6);
                            tr.appendChild(tdAksi);
                            
                            previewTbody.appendChild(tr);
                        });
                        
                    } else {
                        console.log("DEBUG INFO:", res.debug);
                        var debugStr = res.debug ? JSON.stringify(res.debug, null, 2) : 'Tidak ada debug info';
                        alert((res.message || 'Data tidak ditemukan di database integrasi CCTV.') + '\n\nDEBUG:\n' + debugStr);
                    }
                })
                .catch(function(err) {
                    fetchBtn.innerHTML = originalHtml;
                    fetchBtn.disabled = false;
                    alert('Terjadi kesalahan jaringan saat mengambil data dari server.');
                });
        });
    }
});
</script>
@endpush
