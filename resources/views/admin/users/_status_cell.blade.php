<div class="dropdown">
    <button type="button" class="inline-flex items-center gap-0.5 border-0 bg-transparent p-0 shadow-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        @if($st === 'active')
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">Aktif</span>
        @elseif($st === 'pending')
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">Pending</span>
        @elseif($st === 'blocked')
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">Diblokir</span>
        @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">{{ $user->account_status ?: '—' }}</span>
        @endif
    </button>
    <ul class="dropdown-menu shadow-lg border-0 rounded-xl py-1 min-w-auto">
        @foreach(['active' => 'Aktif', 'pending' => 'Pending', 'blocked' => 'Diblokir'] as $val => $label)
            <li>
                <form method="POST" action="{{ route('admin.users.edit-field', $user->id_usm) }}" onsubmit="return confirm(@json('Ubah status menjadi '.$label.'?'));">
                    @csrf
                    <input type="hidden" name="name" value="account_status">
                    <input type="hidden" name="value" value="{{ $val }}">
                    <button type="submit" class="dropdown-item text-[11px] py-1.5 {{ $st === $val ? 'active' : '' }}">{{ $label }}</button>
                </form>
            </li>
        @endforeach
    </ul>
</div>
