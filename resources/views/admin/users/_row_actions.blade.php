<div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
    <a href="{{ route('admin.users.show', $user->id_usm) }}"
       class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none"
       title="Detail">
        <span class="material-symbols-outlined text-[20px]">visibility</span>
    </a>
    @if(empty($executiveUserManagementReadOnly))
        @if($st === 'pending')
            <form method="POST" action="{{ route('admin.users.verify', $user->id_usm) }}" class="inline m-0"
                  onsubmit="return confirm(@json('Verifikasi dan aktifkan akun '.($user->nama ?: $user->username).'?'));">
                @csrf
                <button type="submit" class="w-8 h-8 flex items-center justify-center text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors border-0 bg-transparent p-0" title="Verifikasi">
                    <span class="material-symbols-outlined text-[20px]">verified</span>
                </button>
            </form>
        @endif
        <a href="{{ route('admin.users.edit', $user->id_usm) }}"
           class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none"
           title="Edit">
            <span class="material-symbols-outlined text-[20px]">edit</span>
        </a>
        @if(\App\Helpers\MenuHelper::isPersonilBoOperatorUser($user))
            <a href="{{ route('personil.manager.edit', $user->id_usm) }}"
               class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none"
               title="Kuat Personil BO">
                <span class="material-symbols-outlined text-[20px]">groups</span>
            </a>
        @endif
        <form action="{{ route('admin.users.destroy', $user->id_usm) }}" method="POST" class="inline m-0"
              onsubmit="return confirm(@json('Yakin hapus user '.($user->nama ?: $user->username).'?'));">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-error hover:bg-error-container/20 rounded-lg transition-colors border-0 bg-transparent p-0" title="Hapus">
                <span class="material-symbols-outlined text-[20px]">delete</span>
            </button>
        </form>
    @endif
</div>
