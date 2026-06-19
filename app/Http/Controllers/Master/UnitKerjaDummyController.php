<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Helpers\MenuHelper;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UnitKerjaDummyController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        abort_unless(MenuHelper::matrixAllowsPathForUser($request->user(), 'master/unit-kerja'), 403);

        $perPage = max(1, (int) $request->get('per_page', 10));

        if (! Schema::hasTable('master_unit_kerja')) {
            return view('master.unit-kerja.index', [
                'rows' => new LengthAwarePaginator([], 0, $perPage, 1, ['path' => $request->url(), 'pageName' => 'page']),
                'tableMissing' => true,
            ]);
        }

        $q = DB::table('master_unit_kerja');

        if ($request->filled('search')) {
            $s = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
            $q->where(function ($w) use ($s) {
                $w->where('kode_unit_kerja', 'like', $s)
                    ->orWhere('nama_unit_kerja', 'like', $s)
                    ->orWhere('instansi_placeholder', 'like', $s)
                    ->orWhere('penanggung_jawab', 'like', $s);
            });
        }

        $rows = $q->select([
            'id',
            'kode_unit_kerja as kode',
            'nama_unit_kerja',
            'instansi_placeholder',
            'penanggung_jawab',
        ])
            ->orderBy('kode_unit_kerja')
            ->paginate($perPage)
            ->withQueryString();

        return view('master.unit-kerja.index', [
            'rows' => $rows,
            'tableMissing' => false,
        ]);
    }
}
