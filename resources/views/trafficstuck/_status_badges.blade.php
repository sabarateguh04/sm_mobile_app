@php
    $fromPatrol = ! empty($item->id_patrolicctv ?? null);
    $fromDalkot = ! empty($item->id_trafficdalkot ?? null);
    $editedAt = $item->operator_edited_at ?? null;
    $editorName = trim((string) ($item->operator_editor_nama ?? ''));
    $editorLabel = $editorName !== '' ? $editorName : 'Op #'.($item->operator_edited_by ?? '-');
    $isEdited = ! empty($editedAt);
    $opsdalStatus = trim((string) ($item->status_konfirmasi_opsdal ?? ''));
    $opsdalNote = trim((string) ($item->opsdal_confirmation_note ?? ''));
    $fromLayanan = ($item->source_module ?? '') === 'layanan_masyarakat';
    $showHandledBadge = $isEdited && $opsdalStatus !== 'terkonfirmasi';
    $editedMeta = $showHandledBadge ? ($editorLabel.' · '.\Carbon\Carbon::parse($editedAt)->format('d/m/Y H:i')) : '';
@endphp
<div class="flex flex-col gap-1 min-w-0">
    <div class="flex flex-wrap items-center gap-1.5">
        @if($fromLayanan)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary border border-primary/20">
                <span class="material-symbols-outlined text-[12px]" style="font-variation-settings: 'FILL' 1;">support_agent</span>
                Dari Layanan
            </span>
        @endif

        @if($opsdalStatus !== '')
            @if($opsdalStatus === 'terkonfirmasi')
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                    <span class="material-symbols-outlined text-[12px]" style="font-variation-settings: 'FILL' 1;">verified</span>
                    Terkonfirmasi
                </span>
            @elseif($opsdalStatus === 'ditolak')
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200/60">
                    <span class="material-symbols-outlined text-[12px]" style="font-variation-settings: 'FILL' 1;">cancel</span>
                    OPSDAL Ditolak
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                    <span class="material-symbols-outlined text-[12px]" style="font-variation-settings: 'FILL' 1;">hourglass_top</span>
                    Menunggu OPSDAL
                </span>
            @endif
        @endif

        @if($showHandledBadge)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60" title="Sudah ditangani operator di Kodal Perlambatan">
                <span class="material-symbols-outlined text-[12px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                Ditangani
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200/60" title="Belum ditangani lewat form Edit Kodal">
                Baru
            </span>
            @if($fromPatrol)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200/60" title="Salinan otomatis dari Patroli CCTV">Patroli</span>
            @elseif($fromDalkot)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15" title="Salinan otomatis dari Traffic Dalkot">Dalkot</span>
            @elseif(! $fromLayanan)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-50 text-slate-500 border border-slate-200/60" title="Input manual">Manual</span>
            @endif
        @endif
    </div>

    @if($opsdalStatus === 'ditolak' && $opsdalNote !== '')
        <span class="text-[9px] text-red-400 leading-tight {{ ($expanded ?? false) ? '' : 'truncate max-w-[150px]' }}" title="{{ $opsdalNote }}">
            {{ $opsdalNote }}
        </span>
    @endif

    @if($editedMeta !== '')
        <span class="text-[9px] text-slate-400 leading-tight {{ ($expanded ?? false) ? '' : 'truncate max-w-[170px]' }}" title="{{ $editedMeta }}">
            {{ $editedMeta }}
        </span>
    @endif
</div>
