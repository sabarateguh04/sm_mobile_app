<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Models\ItSupport;
use App\Traits\ExportsIndexQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ItSupportController extends BaseController
{
    use ExportsIndexQuery;

    private const TINGKAT = ['Rendah', 'Sedang', 'Tinggi'];
    private const STATUS   = ['Open', 'In Progress', 'Closed'];
    private const KATDEVICE = ['PC / Laptop', 'Printer', 'Server', 'Jaringan / Router', 'CCTV', 'UPS / Power', 'Software / Aplikasi', 'Lainnya'];

    public function index(Request $request)
    {
        $query = DB::table('itsupport as i')
            ->leftJoin('usm', 'usm.id_usm', '=', 'i.piket')
            ->select('i.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah');

        $user = Auth::user();
        if ($user && (int) $user->user_role_id !== 1) {
            HierarchicalHelper::applyTerritoryScopeForTable($query, 'itsupport', 'i');
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('i.namedevice', 'like', "%{$s}%")
                  ->orWhere('i.owner', 'like', "%{$s}%")
                  ->orWhere('i.katdevice', 'like', "%{$s}%")
                  ->orWhere('i.lokasidevice', 'like', "%{$s}%")
                  ->orWhere('i.ketsingkat', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('i.statusticket', $request->status);
        }

        $data = $query->orderByDesc('i.tanggal')->paginate($request->get('per_page', 20))->withQueryString();

        $statuses = DB::table('itsupport')->select('statusticket')->distinct()->whereNotNull('statusticket')->pluck('statusticket');

        return view('itsupport.index', [
            'pageTitle' => 'IT Support / Tiket',
            'data' => $data,
            'statuses' => $statuses,
            'sboViewOnly' => \App\Support\RoleMutationPolicy::deniesAllMutations(\Illuminate\Support\Facades\Auth::user()),
        ]);
    }

    public function export(Request $request, string $format = 'excel')
    {
        return $this->exportIndexQuery(
            $request,
            $format,
            fn (Request $req) => $this->buildIndexQuery($req),
            'itsupport',
            'IT Support / Tiket',
            'i.id_itsupport'
        );
    }

    protected function buildIndexQuery(Request $request)
    {
        $query = DB::table('itsupport as i')
            ->leftJoin('usm', 'usm.id_usm', '=', 'i.piket')
            ->select('i.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah');

        $user = Auth::user();
        if ($user && (int) $user->user_role_id !== 1) {
            HierarchicalHelper::applyTerritoryScopeForTable($query, 'itsupport', 'i');
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('i.namedevice', 'like', "%{$s}%")
                  ->orWhere('i.owner', 'like', "%{$s}%")
                  ->orWhere('i.katdevice', 'like', "%{$s}%")
                  ->orWhere('i.lokasidevice', 'like', "%{$s}%")
                  ->orWhere('i.ketsingkat', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('i.statusticket', $request->status);
        }

        return $query->orderByDesc('i.tanggal');
    }

    /** @return list<string> */
    protected function katDeviceOptions(): array
    {
        if (Schema::hasTable('t_katperangkat')) {
            $fromDb = DB::table('t_katperangkat')->orderBy('namekatperangkat')->pluck('namekatperangkat')->filter()->values()->all();
            if ($fromDb !== []) {
                return $fromDb;
            }
        }

        return self::KATDEVICE;
    }

    public function create()
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        return view('itsupport.create', [
            'pageTitle' => 'Tambah Tiket IT Support',
            'tingkatOptions' => self::TINGKAT,
            'statusOptions'  => self::STATUS,
            'katOptions'     => $this->katDeviceOptions(),
        ]);
    }

    public function store(Request $request)
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        $validated = $this->validateInput($request);
        $validated['piket'] = (string) Auth::id();

        if ($request->hasFile('foto_kondisi')) {
            $validated['fotokondisi'] = $request->file('foto_kondisi')->store('itsupport_foto', 'public');
        }
        unset($validated['foto_kondisi']);

        ItSupport::create($validated);

        return redirect()->route('itsupport.index')->with('success', 'Tiket IT Support berhasil ditambahkan.');
    }

    public function show(string|int $id)
    {
        $data = DB::table('itsupport as i')
            ->leftJoin('usm', 'usm.id_usm', '=', 'i.piket')
            ->select('i.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah')
            ->where('i.id_itsupport', $id)
            ->first();

        abort_unless($data, 404, 'Data tidak ditemukan');

        return view('itsupport.show', [
            'pageTitle' => 'Detail Tiket IT Support',
            'data' => $data,
            'canMutateRecord' => \App\Support\RoleMutationPolicy::canMutateOperationalRow(Auth::user(), $data, 'itsupport'),
        ]);
    }

    public function edit(string|int $id)
    {
        $data = ItSupport::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'itsupport');

        return view('itsupport.edit', [
            'pageTitle' => 'Edit Tiket IT Support',
            'data'           => $data,
            'tingkatOptions' => self::TINGKAT,
            'statusOptions'  => self::STATUS,
            'katOptions'     => $this->katDeviceOptions(),
        ]);
    }

    public function update(Request $request, string|int $id)
    {
        $data = ItSupport::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'itsupport');
        $validated = $this->validateInput($request);

        if ($request->hasFile('foto_kondisi')) {
            $this->deleteStoredFoto($data->fotokondisi);
            $validated['fotokondisi'] = $request->file('foto_kondisi')->store('itsupport_foto', 'public');
        }
        unset($validated['foto_kondisi']);

        $data->update($validated);

        return redirect()->route('itsupport.index')->with('success', 'Tiket IT Support berhasil diperbarui.');
    }

    public function destroy(string|int $id)
    {
        $data = ItSupport::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'itsupport');
        $this->deleteStoredFoto($data->fotokondisi);
        $data->delete();

        return redirect()->route('itsupport.index')->with('success', 'Tiket IT Support berhasil dihapus.');
    }

    private function validateInput(Request $request): array
    {
        return $request->validate([
            'tanggal'         => 'required|date',
            'namedevice'      => 'nullable|string|max:100',
            'owner'           => 'required|string|max:100',
            'katdevice'       => 'required|string|max:100',
            'lokasidevice'    => 'required|string|max:100',
            'tingkatgangguan' => 'required|string|max:100',
            'statusticket'    => 'required|string|max:100',
            'ketsingkat'      => 'nullable|string',
            'foto_kondisi'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:8192'],
        ]);
    }

    private function deleteStoredFoto(?string $path): void
    {
        if (empty($path) || preg_match('#^https?://#i', $path)) {
            return;
        }
        Storage::disk('public')->delete($path);
    }
}
