<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Helpers\MenuHelper;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PemerintahanInstansiController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        abort_unless(MenuHelper::matrixAllowsPathForUser($request->user(), 'master/pemerintahan-instansi'), 403);

        $perPage = max(1, (int) $request->get('per_page', 10));

        if (! Schema::hasTable('master_pemerintahan_instansi')) {
            return view('master.pemerintahan-instansi.index', [
                'rows' => new LengthAwarePaginator([], 0, $perPage, 1, ['path' => $request->url(), 'pageName' => 'page']),
                'tableMissing' => true,
            ]);
        }

        $q = DB::table('master_pemerintahan_instansi');

        if ($request->filled('search')) {
            $s = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
            $q->where(function ($w) use ($s) {
                $w->where('jenis', 'like', $s)
                    ->orWhere('singkatan', 'like', $s)
                    ->orWhere('nama_unit', 'like', $s)
                    ->orWhere('lokasi', 'like', $s);
            });
        }

        $rows = $q->select(['id', 'jenis', 'singkatan', 'nama_unit', 'lokasi'])
            ->orderBy('jenis')
            ->orderBy('nama_unit')
            ->paginate($perPage)
            ->withQueryString();

        return view('master.pemerintahan-instansi.index', [
            'rows' => $rows,
            'tableMissing' => false,
        ]);
    }
}
