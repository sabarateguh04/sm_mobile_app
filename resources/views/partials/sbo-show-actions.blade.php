@props([
    'row',
    'table' => null,
    'backRoute',
    'editRoute' => null,
    'destroyRoute' => null,
    'confirmDelete' => 'Yakin hapus data ini?',
    'destroyExtra' => '',
])
@php
    use App\Support\RoleMutationPolicy;
    $canMutate = RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $row, $table ?? null);
    $showEdit = $canMutate && ! empty($editRoute);
    $showDelete = $canMutate && ! empty($destroyRoute);
@endphp
@if($showEdit || $showDelete)
    <div class="dropdown d-inline-block">
        <button type="button"
                class="btn btn-sm btn-light border shadow-sm px-2"
                data-bs-toggle="dropdown"
                data-bs-boundary="viewport"
                data-bs-popper-config='{"strategy":"fixed"}'
                aria-expanded="false"
                title="Aksi">
            <i class="fas fa-ellipsis-v"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
                <a class="dropdown-item" href="{{ $backRoute }}">
                    <i class="fas fa-arrow-left me-2 text-secondary"></i>Kembali
                </a>
            </li>
            @if($showEdit)
                <li>
                    <a class="dropdown-item" href="{{ $editRoute }}">
                        <i class="fas fa-edit me-2 text-warning"></i>Edit
                    </a>
                </li>
            @endif
            @if($showDelete)
                @if($showEdit)
                    <li><hr class="dropdown-divider my-1"></li>
                @endif
                <li>
                    <form action="{{ $destroyRoute }}"
                          method="POST"
                          onsubmit="return confirm(@json($confirmDelete));">
                        @csrf
                        @method('DELETE')
                        {!! $destroyExtra !!}
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-trash me-2"></i>Hapus
                        </button>
                    </form>
                </li>
            @endif
        </ul>
    </div>
@else
    <a href="{{ $backRoute }}" class="btn btn-secondary shadow-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
@endif
