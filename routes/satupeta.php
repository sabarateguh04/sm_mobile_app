<?php

use App\Http\Controllers\Satupeta\ApiDahuaController;
use App\Http\Controllers\Satupeta\ApiIndicarController;
use App\Http\Controllers\Satupeta\ApiIntanController;
use App\Http\Controllers\Satupeta\ApiSstController;
use App\Http\Controllers\Satupeta\PemetaanController;
use App\Http\Controllers\Satupeta\PemetaanNewController;
use App\Http\Controllers\Satupeta\SatupetaApiProxyController;
use App\Http\Controllers\Satupeta\SatupetaAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SatuPeta Routes — Laravel (bukan CI). Login wajib sebelum peta.
|--------------------------------------------------------------------------
*/

Route::middleware('web')->group(function () {

    // Peta baru Laravel — langsung tanpa login/SSO
    Route::any('api/satupeta/{path?}', [SatupetaApiProxyController::class, 'proxy'])
        ->where('path', '.*')
        ->name('satupeta.api_proxy');

    Route::get('PemetaanNew', [PemetaanNewController::class, 'index'])->name('satupeta.pemetaan_new');

    Route::prefix('PemetaanNew')->group(function () {
        Route::get('api/geojson/cctv', [PemetaanNewController::class, 'getGeoJsonCctv']);
        Route::get('api/detail/cctv/{id}', [PemetaanNewController::class, 'getDetailCctv']);
        Route::post('get_kategori_kml', [PemetaanNewController::class, 'getKategoriKml']);
        Route::match(['get', 'post'], 'get_kml', [PemetaanNewController::class, 'getKml']);
        Route::post('dt_rengiat', [PemetaanNewController::class, 'dtRengiat']);
        Route::match(['get', 'post'], 'r_token/{bearer?}/{out?}', [PemetaanNewController::class, 'rToken']);
    });

    // Peta lama CI — redirect ke PemetaanNew Laravel
    Route::redirect('Pemetaan', '/PemetaanNew', 301);
    Route::redirect('/satupeta-map', '/PemetaanNew', 301);

    Route::prefix('Pemetaan')->group(function () {
        Route::get('get_kategori_lokasi', [PemetaanController::class, 'getKategoriLokasi']);
        Route::get('get_poi', [PemetaanController::class, 'getPoi']);
        Route::match(['get', 'post'], 'get_titik', [PemetaanController::class, 'getTitik']);
        Route::post('get_cctv', [PemetaanController::class, 'getCctv']);
        Route::match(['get', 'post'], 'get_rawan', [PemetaanController::class, 'getRawan']);
        Route::match(['get', 'post'], 'get_rengiat', [PemetaanController::class, 'getRengiat']);
        Route::post('get_kategori_kml', [PemetaanController::class, 'getKategoriKml']);
        Route::match(['get', 'post'], 'get_kml', [PemetaanController::class, 'getKml']);
        Route::get('get_vip', [PemetaanController::class, 'getVip']);
        Route::get('heat_laka', [PemetaanController::class, 'heatLaka']);
    });

    Route::prefix('Api_dahua')->group(function () {
        Route::match(['get', 'post'], 'traffic_flow2', [ApiDahuaController::class, 'trafficFlow2']);
        Route::get('traffic_flow_cctv', [ApiDahuaController::class, 'trafficFlowCctv']);
        Route::get('traffic_category_cctv', [ApiDahuaController::class, 'trafficCategoryCctv']);
        Route::get('average_speed_cctv', [ApiDahuaController::class, 'averageSpeedCctv']);
        Route::get('total_pelanggaran', [ApiDahuaController::class, 'totalPelanggaran']);
    });

    Route::prefix('Api_indicar')->group(function () {
        Route::get('get_token', [ApiIndicarController::class, 'getToken']);
    });

    $sstRoutes = function () {
        Route::get('get_analitik', [ApiSstController::class, 'getAnalitik']);
        Route::get('get_camera', [ApiSstController::class, 'getCamera']);
        Route::get('upd_cctv', [ApiSstController::class, 'updCctv']);
        Route::get('get_dashanalitik_by_cam', [ApiSstController::class, 'getDashanalitikByCam']);
    };

    Route::prefix('Api_sst')->group($sstRoutes);
    Route::prefix('PemetaanNew/Api_sst')->group($sstRoutes);
    Route::prefix('Pemetaan/Api_sst')->group($sstRoutes);

    $intanVipRoutes = function () {
        Route::match(['get', 'post'], 'get_rengiat_vip_by_id', [ApiIntanController::class, 'getRengiatVipById']);
        Route::match(['get', 'post'], 'get_rengiat_vip_detail', [ApiIntanController::class, 'getRengiatVipDetail']);
    };

    Route::prefix('API/intan/API')->group($intanVipRoutes);
    Route::prefix('PemetaanNew/API/intan/API')->group($intanVipRoutes);
    Route::prefix('Pemetaan/API/intan/API')->group($intanVipRoutes);

    Route::prefix('Auth')->name('satupeta.auth.')->group(function () {
        Route::get('/', [SatupetaAuthController::class, 'index'])->name('login');
        Route::post('proses_login', [SatupetaAuthController::class, 'prosesLogin'])->name('proses_login');
        Route::post('proses_login_api', [SatupetaAuthController::class, 'prosesLoginApi'])->name('proses_login_api');
        Route::get('proses_login_sso/{kode?}/{bearer?}', [SatupetaAuthController::class, 'prosesLoginSso'])->name('proses_login_sso');
        Route::get('login_sso/{kode?}/{bearer?}/{url_n?}', [SatupetaAuthController::class, 'loginSso'])->name('login_sso');
        Route::get('logout', [SatupetaAuthController::class, 'logout'])->name('logout');
        Route::get('logout_sso/{code?}/{bearer?}', [SatupetaAuthController::class, 'logoutSso'])->name('logout_sso');
        Route::get('logout_sso_sm/{code?}', [SatupetaAuthController::class, 'logoutSsoSm'])->name('logout_sso_sm');
        Route::match(['get', 'post'], 'r_token/{bearer?}/{out?}', [SatupetaAuthController::class, 'rToken'])->name('r_token');
    });

    $satupetaMyAssets = function (string $path) {
        $file = public_path('satupeta_assets/' . $path);
        if (! is_file($file)) {
            abort(404);
        }

        return response()->file($file);
    };

    Route::get('my/{path}', $satupetaMyAssets)->where('path', '.*')->name('satupeta.my_assets');
    Route::get('PemetaanNew/my/{path}', $satupetaMyAssets)->where('path', '.*');
    Route::get('Pemetaan/my/{path}', $satupetaMyAssets)->where('path', '.*');
});
