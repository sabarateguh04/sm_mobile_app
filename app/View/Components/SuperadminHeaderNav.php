<?php

namespace App\View\Components;

use App\Helpers\MenuHelper;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\User as AuthenticatedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class SuperadminHeaderNav extends Component
{
    /** @return list<string> Kunci `ref` — selaras config/referensi_tables.php */
    private static function referensiRefKeys(): array
    {
        $refs = config('referensi_tables', []);

        return is_array($refs) ? array_keys($refs) : [];
    }

    /** @var array<int, array{label: string, items: array<int, array{label: string, url: string, active: bool}>, active: bool}> */
    public array $navGroups;

    public function __construct(public bool $embedded = false)
    {
        $user = Auth::user();
        $groups = $this->headerMenuGroupsForUser($user);
        $out = [];

        foreach ($groups as $g) {
            $items = [];

            foreach ($g['items'] ?? [] as $it) {
                if (! MenuHelper::allowsHeaderMenuEntry($user, is_array($it) ? $it : [])) {
                    continue;
                }

                $routeName = $it['route'] ?? null;
                $menuPath = isset($it['path']) && is_string($it['path']) ? trim($it['path']) : null;
                $query = isset($it['query']) && is_array($it['query']) ? $it['query'] : [];
                if (! $routeName && $menuPath !== null && $menuPath !== '') {
                    $routeName = MenuHelper::indexRouteNameForMenuPath($menuPath);
                }

                $url = '#';
                if (is_string($routeName) && $routeName !== '' && Route::has($routeName)) {
                    $url = route($routeName, $query);
                } elseif ($menuPath !== null && $menuPath !== '') {
                    $url = url($menuPath);
                }

                $items[] = [
                    'label' => (string) ($it['label'] ?? ''),
                    'url' => $url,
                    'active' => $this->itemIsActive(
                        is_string($routeName) && $routeName !== '' ? $routeName : null,
                        $query,
                        $menuPath
                    ),
                ];
            }

            if ($items !== []) {
                $out[] = [
                    'label' => (string) ($g['label'] ?? ''),
                    'items' => $items,
                    'active' => collect($items)->contains(fn (array $i): bool => $i['active']),
                ];
            }
        }

        if ($user && MenuHelper::isSuperAdminForLayout($user)) {
            $this->navGroups = $out;
        } elseif ($user && (MenuHelper::isOperatorForLayout($user) || MenuHelper::isExecutiveKabKotaUser($user))) {
            $this->navGroups = $this->filterExcludedHeaderGroups($out, config('operator_header_menu.excluded_groups'));
        } else {
            $this->navGroups = $out;
        }
    }

    /**
     * @param  mixed  $excludedRaw
     * @param  array<int, array<string, mixed>>  $groups
     * @return array<int, array<string, mixed>>
     */
    private function filterExcludedHeaderGroups(array $groups, mixed $excludedRaw): array
    {
        $excluded = is_array($excludedRaw) ? array_flip(array_map('strval', $excludedRaw)) : [];

        return array_values(array_filter(
            $groups,
            fn ($g): bool => is_array($g) && ! isset($excluded[(string) ($g['label'] ?? '')])
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function headerMenuGroupsForUser(?AuthenticatedUser $user): array
    {
        $groupsRaw = config('superadmin_header_menu.groups');
        $groups = is_array($groupsRaw) ? $groupsRaw : [];

        if ($user && MenuHelper::isSuperAdminForLayout($user)) {
            return $groups;
        }

        if ($user && MenuHelper::isOperatorForLayout($user)) {
            $operatorGroups = config('operator_header_menu.groups');
            $groups = is_array($operatorGroups) ? $operatorGroups : [];

            return $this->filterExcludedHeaderGroups(
                $groups,
                config('operator_header_menu.excluded_groups')
            );
        }

        if ($user && MenuHelper::isExecutiveKabKotaUser($user)) {
            $operatorGroups = config('operator_header_menu.groups');
            $groups = is_array($operatorGroups) ? $operatorGroups : [];

            return $this->filterExcludedHeaderGroups(
                $groups,
                config('operator_header_menu.excluded_groups')
            );
        }

        if ($user && MenuHelper::isExecutiveForLayout($user)) {
            $executiveGroups = config('executive_header_menu.groups');
            $groups = is_array($executiveGroups) ? $executiveGroups : [];

            return $this->filterExcludedHeaderGroups(
                $groups,
                config('executive_header_menu.excluded_groups')
            );
        }

        if ($user && MenuHelper::isAdminTerritorialForLayout($user)) {
            $groups = $this->mergeRoleHeaderMenuGroups(
                $groups,
                config('admin_header_menu.groups'),
                config('admin_header_menu.excluded_superadmin_groups')
            );
        }

        return $groups;
    }

    /**
     * @param  mixed  $roleGroupsRaw
     * @param  mixed  $excludedRaw
     * @param  array<int, array<string, mixed>>  $groups
     * @return array<int, array<string, mixed>>
     */
    private function mergeRoleHeaderMenuGroups(array $groups, mixed $roleGroupsRaw, mixed $excludedRaw): array
    {
        $roleGroups = is_array($roleGroupsRaw) ? $roleGroupsRaw : [];
        $excluded = is_array($excludedRaw) ? array_flip(array_map('strval', $excludedRaw)) : [];
        $roleLabels = array_flip(array_map(
            fn (array $g): string => (string) ($g['label'] ?? ''),
            array_filter($roleGroups, is_array(...))
        ));

        return array_merge(
            $roleGroups,
            array_values(array_filter(
                $groups,
                fn ($g): bool => is_array($g)
                    && ! isset($roleLabels[(string) ($g['label'] ?? '')])
                    && ! isset($excluded[(string) ($g['label'] ?? '')])
            ))
        );
    }

    private function itemIsActive(?string $routeName, array $query, ?string $menuPath = null): bool
    {
        if ($menuPath !== null && $menuPath !== '') {
            return $this->pathIsActive($menuPath);
        }

        if (! $routeName) {
            return false;
        }

        $current = Route::currentRouteName();
        if (! $current) {
            return false;
        }

        if ($query !== []) {
            return $current === $routeName && $this->queryMatchesMenuItem($routeName, $query);
        }

        if (str_ends_with($routeName, '.index')) {
            $prefix = substr($routeName, 0, -strlen('.index'));

            return $current === $routeName || str_starts_with($current, $prefix.'.');
        }

        return $current === $routeName;
    }

    private function pathIsActive(string $menuPath): bool
    {
        $current = trim(request()->path(), '/');
        $expected = trim($menuPath, '/');

        if (in_array($expected, ['datacctv', 'data-cctv'], true)) {
            return in_array($current, ['datacctv', 'data-cctv'], true)
                || str_starts_with($current, 'datacctv/')
                || str_starts_with($current, 'data-cctv/');
        }

        return $current === $expected || str_starts_with($current, $expected.'/');
    }

    /**
     * Selaraskan dengan query URL; untuk master referensi tanpa ?ref= pakai default 'faktor'
     * (sama dengan ReferensiController).
     */
    private function queryMatchesMenuItem(string $routeName, array $query): bool
    {
        /** @var Request $httpRequest */
        $httpRequest = request();

        foreach ($query as $key => $value) {
            $actual = $httpRequest->query((string) $key);
            if ($routeName === 'master.referensi.index' && $key === 'ref') {
                if ($actual === null || $actual === '') {
                    $actual = 'faktor';
                } elseif (! in_array((string) $actual, self::referensiRefKeys(), true)) {
                    $actual = 'faktor';
                }
            }
            if ((string) $actual !== (string) $value) {
                return false;
            }
        }

        return true;
    }

    public function render(): View|Closure|string
    {
        return view('components.superadmin-header-nav');
    }
}
