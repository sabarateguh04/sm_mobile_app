@php
    $routeName = $routeName ?? null;
    $query = $query ?? request()->except('page');
    $routeParams = $routeParams ?? [];
    $variant = $variant ?? 'compact';
@endphp
@if($routeName && Route::has($routeName))
    @if($variant === 'hero')
        <div class="dropdown shrink-0">
            <button type="button" class="flex items-center gap-1.5 px-3 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-xl text-label-md h-[42px]" data-bs-toggle="dropdown" title="Export">
                <span class="material-symbols-outlined text-[18px]">download</span><span>Export</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @foreach(['excel' => 'Excel', 'csv' => 'CSV', 'pdf' => 'PDF'] as $fmt => $label)
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route($routeName, array_merge($routeParams, ['format' => $fmt])) }}?{{ http_build_query($query) }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="dropdown">
            <button type="button" class="w-8 h-8 flex items-center justify-center hover:bg-slate-50 border border-slate-100 rounded-lg text-slate-500 transition-colors bg-white" data-bs-toggle="dropdown" title="Export">
                <span class="material-symbols-outlined text-[17px]">download</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @foreach(['excel' => 'Excel', 'csv' => 'CSV', 'pdf' => 'PDF'] as $fmt => $label)
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route($routeName, array_merge($routeParams, ['format' => $fmt])) }}?{{ http_build_query($query) }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif
@endif
