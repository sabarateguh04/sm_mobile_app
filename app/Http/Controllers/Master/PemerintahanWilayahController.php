<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Helpers\MenuHelper;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PemerintahanWilayahController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        abort_unless(MenuHelper::matrixAllowsPathForUser($request->user(), 'master/pemerintahan-wilayah'), 403);

        $perPage = max(1, (int) $request->get('per_page', 10));

        if (! Schema::hasTable('master_pemerintahan_wilayah')) {
            return view('master.pemerintahan-wilayah.index', [
                'rows' => new LengthAwarePaginator([], 0, $perPage, 1, ['path' => $request->url(), 'pageName' => 'page']),
                'tableMissing' => true,
            ]);
        }

        $q = DB::table('master_pemerintahan_wilayah');

        if ($request->filled('search')) {
            $s = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
            $q->where(function ($w) use ($s) {
                $w->where('tingkat', 'like', $s)
                    ->orWhere('kode_wilayah', 'like', $s)
                    ->orWhere('nama', 'like', $s)
                    ->orWhere('wilayah_induk', 'like', $s);
            });
        }

        $rows = $q->select(['id', 'tingkat', 'kode_wilayah as kode', 'nama', 'wilayah_induk'])
            ->orderBy('tingkat')
            ->orderBy('kode_wilayah')
            ->paginate($perPage)
            ->withQueryString();

        return view('master.pemerintahan-wilayah.index', [
            'rows' => $rows,
            'tableMissing' => false,
        ]);
    }
}
