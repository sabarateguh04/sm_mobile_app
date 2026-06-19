@php
    $exportQuery = array_merge($exportQuery ?? [], ['id' => $rowId]);
@endphp
@include('_partials.export_dropdown', [
    'routeName' => $exportRouteName,
    'query' => $exportQuery,
    'routeParams' => $exportRouteParams ?? [],
    'variant' => $variant ?? 'hero',
])
