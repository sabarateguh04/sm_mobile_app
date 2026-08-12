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
    /**
     * Pastikan tabel ada dengan kolom hierarki lengkap.
     */
    private function ensureTable(): void
    {
        if (! Schema::hasTable('master_pemerintahan_instansi')) {
            Schema::create('master_pemerintahan_instansi', function ($table) {
                $table->id();
                $table->string('nama_unit');
                $table->string('singkatan', 64)->nullable();
                $table->string('jenis', 128)->nullable();
                $table->string('lokasi', 128)->nullable();
                $table->string('organisasi')->nullable();
                $table->string('sub_org')->nullable();
                $table->string('unit_kerja')->nullable();
                $table->timestamps();
            });
        } else {
            // Tambah kolom hierarki jika belum ada (untuk server produksi)
            if (! Schema::hasColumn('master_pemerintahan_instansi', 'organisasi')) {
                Schema::table('master_pemerintahan_instansi', function ($table) {
                    $table->string('organisasi')->nullable()->after('lokasi');
                });
            }
            if (! Schema::hasColumn('master_pemerintahan_instansi', 'sub_org')) {
                Schema::table('master_pemerintahan_instansi', function ($table) {
                    $table->string('sub_org')->nullable()->after('organisasi');
                });
            }
            if (! Schema::hasColumn('master_pemerintahan_instansi', 'unit_kerja')) {
                Schema::table('master_pemerintahan_instansi', function ($table) {
                    $table->string('unit_kerja')->nullable()->after('sub_org');
                });
            }
        }
    }

    /**
     * Ambil daftar Instansi unik dari kolom nama_unit (untuk filter dropdown).
     */
    private function instansiList(): \Illuminate\Support\Collection
    {
        return DB::table('master_pemerintahan_instansi')
            ->orderBy('nama_unit')
            ->whereNotNull('nama_unit')
            ->where('nama_unit', '!=', '')
            ->pluck('nama_unit')
            ->unique()
            ->values();
    }

    /**
     * Ambil daftar Organisasi unik yang terkait dengan instansi tertentu.
     */
    private function organisasiList(?string $instansi = null): \Illuminate\Support\Collection
    {
        $q = DB::table('master_pemerintahan_instansi')
            ->whereNotNull('organisasi')
            ->where('organisasi', '!=', '');

        if ($instansi) {
            $q->where('nama_unit', $instansi);
        }

        return $q->orderBy('organisasi')->pluck('organisasi')->unique()->values();
    }

    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        abort_unless(MenuHelper::matrixAllowsPathForUser($request->user(), 'master/pemerintahan-instansi'), 403);

        $this->ensureTable();

        $perPage = max(1, (int) $request->get('per_page', 10));

        $q = DB::table('master_pemerintahan_instansi');

        if ($request->filled('search')) {
            $s = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
            $q->where(function ($w) use ($s) {
                $w->where('nama_unit', 'like', $s)
                    ->orWhere('singkatan', 'like', $s)
                    ->orWhere('jenis', 'like', $s)
                    ->orWhere('lokasi', 'like', $s)
                    ->orWhere('organisasi', 'like', $s)
                    ->orWhere('sub_org', 'like', $s)
                    ->orWhere('unit_kerja', 'like', $s);
            });
        }

        $rows = $q->orderBy('nama_unit')
            ->paginate($perPage)
            ->withQueryString();

        return view('master.pemerintahan-instansi.index', [
            'rows' => $rows,
            'tableMissing' => false,
        ]);
    }

    public function create()
    {
        $this->ensureTable();
        $instansiList = $this->instansiList();
        return view('master.pemerintahan-instansi.create', compact('instansiList'));
    }

    public function store(Request $request)
    {
        $this->ensureTable();

        $validated = $request->validate([
            'nama_unit' => 'required|string|max:255',
            'organisasi' => 'nullable|string|max:255',
            'sub_org'    => 'nullable|string|max:255',
            'unit_kerja' => 'nullable|string|max:255',
        ]);

        $data = [
            'nama_unit'  => mb_strtoupper(trim($validated['nama_unit']), 'UTF-8'),
            'organisasi' => !empty($validated['organisasi']) ? mb_strtoupper(trim($validated['organisasi']), 'UTF-8') : null,
            'sub_org'    => !empty($validated['sub_org']) ? mb_strtoupper(trim($validated['sub_org']), 'UTF-8') : null,
            'unit_kerja' => !empty($validated['unit_kerja']) ? mb_strtoupper(trim($validated['unit_kerja']), 'UTF-8') : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('master_pemerintahan_instansi')->insert($data);

        return redirect()->route('master.pemerintahan-instansi.index')
            ->with('success', 'Data instansi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $this->ensureTable();

        $row = DB::table('master_pemerintahan_instansi')->where('id', $id)->first();
        if ($row === null) {
            abort(404);
        }

        $instansiList = $this->instansiList();
        return view('master.pemerintahan-instansi.edit', compact('row', 'instansiList'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureTable();

        $row = DB::table('master_pemerintahan_instansi')->where('id', $id)->first();
        if ($row === null) {
            abort(404);
        }

        $validated = $request->validate([
            'nama_unit' => 'required|string|max:255',
            'organisasi' => 'nullable|string|max:255',
            'sub_org'    => 'nullable|string|max:255',
            'unit_kerja' => 'nullable|string|max:255',
        ]);

        DB::table('master_pemerintahan_instansi')->where('id', $id)->update([
            'nama_unit'  => mb_strtoupper(trim($validated['nama_unit']), 'UTF-8'),
            'organisasi' => !empty($validated['organisasi']) ? mb_strtoupper(trim($validated['organisasi']), 'UTF-8') : null,
            'sub_org'    => !empty($validated['sub_org']) ? mb_strtoupper(trim($validated['sub_org']), 'UTF-8') : null,
            'unit_kerja' => !empty($validated['unit_kerja']) ? mb_strtoupper(trim($validated['unit_kerja']), 'UTF-8') : null,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.pemerintahan-instansi.index')
            ->with('success', 'Data instansi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->ensureTable();

        $row = DB::table('master_pemerintahan_instansi')->where('id', $id)->first();
        if ($row === null) {
            abort(404);
        }

        DB::table('master_pemerintahan_instansi')->where('id', $id)->delete();

        return redirect()->route('master.pemerintahan-instansi.index')
            ->with('success', 'Data instansi berhasil dihapus.');
    }

    /**
     * API: Ambil daftar Organisasi berdasarkan Instansi (untuk dropdown AJAX).
     */
    public function apiOrganisasiByInstansi(Request $request): \Illuminate\Http\JsonResponse
    {
        $instansi = trim((string) $request->get('instansi', ''));
        $list = $this->organisasiList($instansi ?: null);
        return response()->json($list);
    }

    /**
     * API: Ambil daftar Sub Org berdasarkan Instansi + Organisasi.
     */
    public function apiSubOrgByOrganisasi(Request $request): \Illuminate\Http\JsonResponse
    {
        $instansi = trim((string) $request->get('instansi', ''));
        $organisasi = trim((string) $request->get('organisasi', ''));

        $q = DB::table('master_pemerintahan_instansi')
            ->whereNotNull('sub_org')->where('sub_org', '!=', '');
        if ($instansi) $q->where('nama_unit', $instansi);
        if ($organisasi) $q->where('organisasi', $organisasi);

        return response()->json($q->pluck('sub_org')->unique()->sort()->values());
    }

    /**
     * API: Ambil daftar Unit Kerja berdasarkan Instansi + Organisasi + Sub Org.
     */
    public function apiUnitKerjaBySubOrg(Request $request): \Illuminate\Http\JsonResponse
    {
        $instansi = trim((string) $request->get('instansi', ''));
        $organisasi = trim((string) $request->get('organisasi', ''));
        $subOrg = trim((string) $request->get('sub_org', ''));

        $q = DB::table('master_pemerintahan_instansi')
            ->whereNotNull('unit_kerja')->where('unit_kerja', '!=', '');
        if ($instansi) $q->where('nama_unit', $instansi);
        if ($organisasi) $q->where('organisasi', $organisasi);
        if ($subOrg) $q->where('sub_org', $subOrg);

        return response()->json($q->pluck('unit_kerja')->unique()->sort()->values());
    }
}
