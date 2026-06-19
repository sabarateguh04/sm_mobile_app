{{--
  Aksi baris CRUD — menu titik tiga; Edit/Hapus hanya jika user berhak mutasi baris.
  @param object $row
  @param string|null $table
  @param string $showRoute
  @param string|null $editRoute
  @param string|null $destroyRoute
  @param string|null $destroyExtra
  @param string|null $confirmDelete
--}}
@php
    use App\Support\RoleMutationPolicy;
    $canMutate = RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $row, $table ?? null);
    $showEdit = $canMutate && ! empty($editRoute);
    $showDelete = $canMutate && ! empty($destroyRoute);
@endphp
<div class="dropdown">
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
            <a class="dropdown-item" href="{{ $showRoute }}">
                <i class="fas fa-eye me-2 text-info"></i>Detail
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
                      onsubmit="return confirm(@json($confirmDelete ?? 'Yakin hapus data ini?'));">
                    @csrf
                    @method('DELETE')
                    {!! $destroyExtra ?? '' !!}
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-trash me-2"></i>Hapus
                    </button>
                </form>
            </li>
        @endif
    </ul>
</div>
