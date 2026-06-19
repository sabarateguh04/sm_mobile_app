<div class="dropdown">
    <button type="button" class="inline-flex items-center gap-0.5 border-0 bg-transparent p-0 shadow-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        @if($user->role)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary border border-primary/20" title="{{ $roleDescriptions[$user->user_role_id] ?? '' }}">{{ $user->role->role_name }}</span>
        @elseif($user->user_role_id)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/60" title="Role ID tidak ada di master">ID {{ $user->user_role_id }}</span>
        @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">—</span>
        @endif
    </button>
    <ul class="dropdown-menu shadow-lg border-0 rounded-xl py-1" style="max-height: 250px; overflow-y: auto;">
        @foreach($rolesForFilter ?? [] as $r)
            <li>
                <form method="POST" action="{{ route('admin.users.edit-field', $user->id_usm) }}" onsubmit="return confirm(@json('Ubah role menjadi '.$r->role_name.'?'));">
                    @csrf
                    <input type="hidden" name="name" value="user_role_id">
                    <input type="hidden" name="value" value="{{ $r->role_id }}">
                    <button type="submit" class="dropdown-item text-[11px] py-1.5 {{ $user->user_role_id == $r->role_id ? 'active' : '' }}">{{ $r->role_name }}</button>
                </form>
            </li>
        @endforeach
    </ul>
</div>
