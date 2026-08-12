<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Support\OrgMaster;
use App\Support\TerritoryMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrganisasiController extends Controller
{
    protected function normalizeOrgName(string $name): string
    {
        return mb_strtoupper(trim($name), 'UTF-8');
    }

    /**
     * @return object{id_org: int|string, nameorg: string, singkatan: ?string}|null
     */
    protected function findOrgRow($id): ?object
    {
        if (! Schema::hasTable('org')) {
            return null;
        }

        $row = DB::table('org')->where('id_org', $id)->first();

        return $row ? (object) (array) $row : null;
    }

    public function index(Request $request)
    {
        if (! Schema::hasTable('org')) {
            abort(404, 'Tabel organisasi tidak tersedia.');
        }

        $perPage = max(1, (int) $request->get('per_page', 10));

        $orgQ = DB::table('org')->orderBy('nameorg');

        if ($request->filled('search')) {
            $kw = $request->search;
            $orgQ->where(function ($q) use ($kw) {
                $q->where('nameorg', 'like', "%{$kw}%");
                if (Schema::hasColumn('org', 'singkatan')) {
                    $q->orWhere('singkatan', 'like', "%{$kw}%");
                }
                $q->orWhere('id_org', 'like', "%{$kw}%");
            });
        }

        $org = $orgQ->paginate($perPage)->withQueryString();
        $suborgCounts = OrgMaster::suborgCountsForOrgRows($org->items());

        $instansiMap = DB::table('master_pemerintahan_instansi')->pluck('nama_unit', 'id');

        return view('master.organisasi.index', compact('org', 'suborgCounts', 'instansiMap'));
    }

    public function suborganisasi(Request $request, $id)
    {
        $org = $this->findOrgRow($id);
        if ($org === null) {
            abort(404);
        }

        $perPage = max(1, (int) $request->get('per_page', 10));
        $bundle = OrgMaster::paginatedSuborgListForOrg(
            $org,
            $perPage,
            $request->filled('search') ? (string) $request->search : null
        );

        $suborg = $bundle['items'];
        if (method_exists($suborg, 'withQueryString')) {
            $suborg = $suborg->withQueryString();
        }

        $usesKlResort = $bundle['uses_kl'];
        $klDaerahIds = $bundle['daerah_ids'];
        $matchedVia = $bundle['matched_via'];
        $suborgTotal = OrgMaster::countSuborgForOrgPrimaryKey($id);
        $createParentId = count($klDaerahIds) === 1 ? $klDaerahIds[0] : null;

        if (! $usesKlResort && ! Schema::hasTable('suborg') && $suborgTotal === 0) {
            abort(404, 'Tabel sub-organisasi tidak tersedia.');
        }

        return view('master.organisasi.sub', compact(
            'org',
            'suborg',
            'suborgTotal',
            'usesKlResort',
            'klDaerahIds',
            'createParentId',
            'matchedVia'
        ));
    }

    public function create()
    {
        if (! Schema::hasTable('org')) {
            abort(404);
        }

        $instansiList = DB::table('master_pemerintahan_instansi')->orderBy('nama_unit')->get();

        return view('master.organisasi.create', compact('instansiList'));
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('org')) {
            abort(404);
        }

        $rules = ['nameorg' => ['required', 'string', 'max:255'], 'instansi_id' => ['required', 'integer']];
        if (Schema::hasColumn('org', 'singkatan')) {
            $rules['singkatan'] = ['nullable', 'string', 'max:64'];
        }
        $validated = $request->validate($rules);

        $insert = [
            'id_org' => TerritoryMaster::nextTableId('org', 'id_org'),
            'nameorg' => $this->normalizeOrgName($validated['nameorg']),
            'instansi_id' => $validated['instansi_id'],
        ];
        if (Schema::hasColumn('org', 'singkatan') && ! empty($validated['singkatan'] ?? '')) {
            $insert['singkatan'] = mb_strtoupper(trim((string) $validated['singkatan']), 'UTF-8');
        }

        DB::table('org')->insert($insert);

        return redirect()->route('master.pusat.index')->with('success', 'Data organisasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $row = $this->findOrgRow($id);
        if ($row === null) {
            abort(404);
        }

        $instansiList = DB::table('master_pemerintahan_instansi')->orderBy('nama_unit')->get();

        return view('master.organisasi.edit', compact('row', 'instansiList'));
    }

    public function update(Request $request, $id)
    {
        if ($this->findOrgRow($id) === null) {
            abort(404);
        }

        $rules = ['nameorg' => ['required', 'string', 'max:255'], 'instansi_id' => ['required', 'integer']];
        if (Schema::hasColumn('org', 'singkatan')) {
            $rules['singkatan'] = ['nullable', 'string', 'max:64'];
        }
        $validated = $request->validate($rules);

        $update = [
            'nameorg' => $this->normalizeOrgName($validated['nameorg']),
            'instansi_id' => $validated['instansi_id'],
        ];
        if (Schema::hasColumn('org', 'singkatan')) {
            $update['singkatan'] = ! empty($validated['singkatan'] ?? '')
                ? mb_strtoupper(trim((string) $validated['singkatan']), 'UTF-8')
                : null;
        }

        DB::table('org')->where('id_org', $id)->update($update);

        return redirect()->route('master.pusat.index')->with('success', 'Data organisasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        if ($this->findOrgRow($id) === null) {
            abort(404);
        }

        $childCount = OrgMaster::countSuborgForOrgPrimaryKey($id);
        if ($childCount > 0) {
            return redirect()
                ->route('master.pusat.sub', $id)
                ->with('warning', 'Organisasi masih memiliki sub-organisasi. Hapus data di Sub Org (KL) terlebih dahulu.');
        }

        DB::table('org')->where('id_org', $id)->delete();

        return redirect()->route('master.pusat.index')->with('success', 'Data organisasi berhasil dihapus.');
    }
}
