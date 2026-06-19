@php
    $formRow = $data ?? null;
    $selectedWilayahInitial = old('wilayah', optional($formRow)->wilayah ?? ($defaultWilayah ?? ''));
@endphp
<script>
(function () {
    var byDaerah = @json($wilayahByDaerahJson ?? []);
    var daerahEl = document.getElementById('personilDaerah');
    var wilayahEl = document.getElementById('personilWilayah');
    if (!daerahEl || !wilayahEl || daerahEl.tagName !== 'SELECT' || wilayahEl.tagName !== 'SELECT') return;
    var selectedWilayah = @json($selectedWilayahInitial);

    function refillWilayah(resetSelection) {
        var d = daerahEl.value;
        wilayahEl.innerHTML = '';
        var opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = d ? '— Pilih Polres —' : '— Pilih Polda dulu —';
        wilayahEl.appendChild(opt0);
        if (d && byDaerah[d]) {
            (byDaerah[d] || []).forEach(function (item) {
                var name = typeof item === 'string' ? item : (item && item.nama ? item.nama : '');
                if (!name) return;
                var o = document.createElement('option');
                o.value = name;
                o.textContent = name.toUpperCase();
                wilayahEl.appendChild(o);
            });
        }
            if (!resetSelection && selectedWilayah) {
                wilayahEl.value = selectedWilayah;
                if (wilayahEl.value !== selectedWilayah) {
                    var legacy = document.createElement('option');
                    legacy.value = selectedWilayah;
                    legacy.textContent = selectedWilayah + ' (tidak di master)';
                    wilayahEl.appendChild(legacy);
                    wilayahEl.value = selectedWilayah;
                }
            }
            if (window.jQuery && $(wilayahEl).hasClass('select2-hidden-accessible')) {
                $(wilayahEl).trigger('change.select2');
            }
        }
        
        if (window.jQuery) {
            $(daerahEl).on('change', function () {
                selectedWilayah = '';
                refillWilayah(true);
            });
        } else {
            daerahEl.addEventListener('change', function () {
                selectedWilayah = '';
                refillWilayah(true);
            });
        }
        refillWilayah(false);
})();
</script>
