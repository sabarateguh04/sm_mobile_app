<?php

namespace App\Providers;

use App\Support\NumericCaptcha;
use App\Support\RoleMutationPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/satupeta_helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Blade::if('canMutateOperationalRow', function (object $row, ?string $table = null): bool {
            return RoleMutationPolicy::canMutateOperationalRow(Auth::user(), $row, $table);
        });

        Blade::if('canMutatePersonilRow', function (object $row): bool {
            return RoleMutationPolicy::canMutatePersonilRow(Auth::user(), $row);
        });

        View::composer(['auth.login', 'auth.register'], function (\Illuminate\View\View $view): void {
            $key = $view->name() === 'auth.login'
                ? NumericCaptcha::SESSION_LOGIN
                : NumericCaptcha::SESSION_REGISTER;
            $pair = NumericCaptcha::refresh($key);
            $view->with([
                'captchaLeft' => $pair['a'],
                'captchaRight' => $pair['b'],
            ]);
        });

        View::composer(['media.analytic.create', 'media.analytic.edit'], function (\Illuminate\View\View $view): void {
            if (! array_key_exists('sentimenOptions', $view->getData())) {
                $view->with('sentimenOptions', ['Positif', 'Negatif', 'Netral']);
            }
        });

        View::composer('layouts.app', function (\Illuminate\View\View $view): void {
            $user = Auth::user();
            $view->with([
                'sboViewOnly' => RoleMutationPolicy::deniesAllMutations($user),
            ]);
        });
    }
}
