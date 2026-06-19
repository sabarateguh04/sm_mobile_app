@if(($user->kategori_user ?? '') === 'organik')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">Organik</span>
@elseif(($user->kategori_user ?? '') === 'non_organik')
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">Non-Organik</span>
@else
    <span class="text-[11px] text-slate-400">—</span>
@endif
