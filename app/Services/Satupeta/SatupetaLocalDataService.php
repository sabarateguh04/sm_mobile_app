<?php

namespace App\Services\Satupeta;

use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Data peta dari DB lokal (logika CI Pemetaan.php / Peta.php + tabel sbo).
 */
class SatupetaLocalDataService
{
    private const RAWAN_ID_MAP = [
        2 => ['Rawan Laka'],
        3 => ['Rawan Macet'],
        5 => ['Rawan Tindak Pidana'],
        6 => ['Rawan Banjir'],
        7 => ['Rawan Longsor'],
        9 => ['Rawan Pohon Tumbang'],
    ];

    public function responseFor(Request $request, string $path): ?Response
    {
        $path = trim($path, '/');

        $payload = match ($path) {
            'provinsiPolda' => $this->provinsiPolda($request),
            'kotaPolres' => $this->kotaPolres($request),
            'sm_master_data/kerawanan' => $this->kerawanan($request),
            'data_collecting/black_trouble_spot' => $this->blackTroubleSpot($request),
            default => str_starts_with($path, 'data_collecting/wilayah_rawan')
                ? $this->wilayahRawan($request)
                : null,
        };

        if ($payload === null) {
            return null;
        }

        return response()
            ->json($payload)
            ->header('X-Satupeta-Source', 'local-db');
    }

    public function provinsiPolda(Request $request): array
    {
        $db = $this->db();

        if ($this->hasTable($db, 'tbl_provinsi')) {
            return $this->provinsiFromTblProvinsi($db, $request);
        }

        if ($this->hasTable($db, 'master_provinsi')) {
            return $this->provinsiFromMaster($db, $request);
        }

        return ['status' => true, 'data' => []];
    }

    public function kotaPolres(Request $request): array
    {
        $db = $this->db();

        if ($this->hasTable($db, 'tbl_kota')) {
            return $this->kotaFromTblKota($db, $request);
        }

        if ($this->hasTable($db, 'master_kabupaten_kota')) {
            return $this->kotaFromMaster($db, $request);
        }

        return ['status' => true, 'data' => []];
    }

    public function kerawanan(Request $request): array
    {
        $db = $this->db();
        $items = [];

        if ($this->hasTable($db, 'tmc_data_rawan')) {
            $statuses = $db->table('tmc_data_rawan')
                ->whereNotNull('status')
                ->where('status', '!=', '')
                ->distinct()
                ->orderBy('status')
                ->pluck('status');

            foreach ($statuses as $i => $status) {
                $items[] = $this->kerawananRow($i + 1, (string) $status);
            }
        } elseif ($this->hasTable($db, 'data_gangguan')) {
            $statuses = $db->table('data_gangguan')
                ->whereNotNull('kerawanan')
                ->where('kerawanan', '!=', '')
                ->distinct()
                ->orderBy('kerawanan')
                ->pluck('kerawanan');

            foreach ($statuses as $i => $status) {
                $items[] = $this->kerawananRow($i + 1, (string) $status);
            }
        }

        return ['status' => true, 'data' => $items];
    }

    public function blackTroubleSpot(Request $request): array
    {
        $db = $this->db();
        $status = strtolower((string) $request->query('status', 'black spot'));

        if ($this->hasTable($db, 'tmc_data_statusjalan')) {
            return [
                'status' => true,
                'data' => $this->spotsFromTmcStatusJalan($db, $request, $status),
            ];
        }

        if ($this->hasTable($db, 'data_gangguan')) {
            return [
                'status' => true,
                'data' => $this->spotsFromDataGangguan($db, $request, $status),
            ];
        }

        return ['status' => true, 'data' => []];
    }

    public function wilayahRawan(Request $request): array
    {
        $db = $this->db();
        $jenisId = (int) $request->query('jenis_kerawanan_id', 0);
        $labels = self::RAWAN_ID_MAP[$jenisId] ?? [];

        if ($this->hasTable($db, 'tmc_data_rawan') && $labels !== []) {
            $query = $db->table('tmc_data_rawan')->where(function ($q) use ($labels) {
                foreach ($labels as $label) {
                    $q->orWhere('status', 'like', '%' . $label . '%');
                }
            });
            $this->applyTerritoryFilters($query, $request, 'polda', 'polres');
            $this->applyDateFilters($query, $request, 'tgl');

            $data = $query->get()->map(fn ($row, $i) => $this->mapTmcRawanRow($row, $i + 1, $labels[0]))->values()->all();

            return ['status' => true, 'data' => $data];
        }

        if ($this->hasTable($db, 'data_gangguan') && $labels !== []) {
            $query = $db->table('data_gangguan')->where(function ($q) use ($labels) {
                foreach ($labels as $label) {
                    $q->orWhere('kerawanan', 'like', '%' . $label . '%');
                }
            });
            $this->applyDateFilters($query, $request, 'tanggal');

            $data = $query->get()
                ->map(fn ($row, $i) => $this->mapGangguanRow($row, $i + 1, $labels[0]))
                ->values()
                ->all();

            return ['status' => true, 'data' => $data];
        }

        return ['status' => true, 'data' => []];
    }

    private function provinsiFromTblProvinsi(Connection $db, Request $request): array
    {
        $query = $db->table('tbl_provinsi as p')
            ->select('p.prov_id', 'p.prov_name', 'p.polygon');

        if ($request->filled('prov_name')) {
            $query->where('p.prov_name', $request->query('prov_name'));
        }
        if ($request->filled('prov_id')) {
            $query->where('p.prov_id', $request->query('prov_id'));
        }

        $polda = $this->defaultPolda($db);

        $data = $query->orderBy('p.prov_name')->get()->map(function ($row) use ($polda) {
            return [
                'prov_id' => (int) $row->prov_id,
                'prov_name' => $row->prov_name,
                'polda_id' => (string) $polda['id'],
                'polda' => $polda['nama'],
                'polygon' => $this->decodePolygon($row->polygon ?? null),
            ];
        })->values()->all();

        return ['status' => true, 'data' => $data];
    }

    private function provinsiFromMaster(Connection $db, Request $request): array
    {
        $query = $db->table('master_provinsi')->select('id', 'nama');

        if ($request->filled('prov_name')) {
            $query->where('nama', 'like', '%' . $request->query('prov_name') . '%');
        }
        if ($request->filled('prov_id')) {
            $query->where('id', $request->query('prov_id'));
        }

        $polda = $this->defaultPolda($db);

        $data = $query->orderBy('nama')->get()->map(function ($row) use ($polda) {
            return [
                'prov_id' => (int) $row->id,
                'prov_name' => $row->nama,
                'polda_id' => (string) $polda['id'],
                'polda' => $polda['nama'],
                'polygon' => null,
            ];
        })->values()->all();

        return ['status' => true, 'data' => $data];
    }

    private function kotaFromTblKota(Connection $db, Request $request): array
    {
        $query = $db->table('tbl_kota as k')
            ->select('k.city_id', 'k.city_name', 'k.prov_id', 'k.polygon', 'k.polres_id', 'k.polres', 'k.polda_id', 'k.polda');

        if ($request->filled('prov_id')) {
            $query->where('k.prov_id', $request->query('prov_id'));
        }

        $data = $query->orderBy('k.city_name')->get()->map(function ($row) {
            return [
                'city_id' => (int) $row->city_id,
                'city_name' => $row->city_name,
                'prov_id' => (int) $row->prov_id,
                'polda_id' => (string) ($row->polda_id ?? ''),
                'polda' => $row->polda ?? '',
                'polres_id' => (string) ($row->polres_id ?? ''),
                'polres' => $row->polres ?? '',
                'polygon' => $this->decodePolygon($row->polygon ?? null),
            ];
        })->values()->all();

        return ['status' => true, 'data' => $data];
    }

    private function kotaFromMaster(Connection $db, Request $request): array
    {
        $query = $db->table('master_kabupaten_kota as k')
            ->join('master_provinsi as p', 'p.id', '=', 'k.master_provinsi_id')
            ->select('k.id as city_id', 'k.nama as city_name', 'k.master_provinsi_id as prov_id', 'p.nama as prov_name');

        if ($request->filled('prov_id')) {
            $query->where('k.master_provinsi_id', $request->query('prov_id'));
        }

        $polda = $this->defaultPolda($db);
        $polresList = $this->polresForPolda($db, (int) $polda['id']);
        $defaultPolres = $polresList[0] ?? ['id' => '1', 'nama' => 'POLRES'];

        $data = $query->orderBy('k.nama')->get()->map(function ($row, $index) use ($polda, $polresList, $defaultPolres) {
            $polres = $polresList[$index % max(count($polresList), 1)] ?? $defaultPolres;

            return [
                'city_id' => (int) $row->city_id,
                'city_name' => $row->city_name,
                'prov_id' => (int) $row->prov_id,
                'polda_id' => (string) $polda['id'],
                'polda' => $polda['nama'],
                'polres_id' => (string) $polres['id'],
                'polres' => $polres['nama'],
                'polygon' => null,
            ];
        })->values()->all();

        return ['status' => true, 'data' => $data];
    }

    private function spotsFromTmcStatusJalan(Connection $db, Request $request, string $status): array
    {
        $statusLabel = str_contains($status, 'trouble') ? 'Trouble Spot' : 'Black Spot';
        $query = $db->table('tmc_data_statusjalan')->where('status', $statusLabel);
        $this->applyTerritoryFilters($query, $request, 'polda', 'polres');
        $this->applyDateFilters($query, $request, 'tgl');

        return $query->get()->map(function ($row, $i) use ($statusLabel) {
            return [
                'rowid' => $row->rowid ?? ($i + 1),
                'id' => $row->rowid ?? ($i + 1),
                'lat' => (float) $row->lat,
                'lng' => (float) $row->lng,
                'status' => $statusLabel,
                'status_b_t_s' => 'Aktif',
                'nama_jalan_s' => $row->jalan ?? '',
                'namajalan' => $row->jalan ?? '',
                'lokasi' => $row->jalan ?? '',
                'polda_id' => $row->polda ?? '',
                'polres_id' => $row->polres ?? '',
            ];
        })->values()->all();
    }

    private function spotsFromDataGangguan(Connection $db, Request $request, string $status): array
    {
        $query = $db->table('data_gangguan');

        if (str_contains($status, 'trouble')) {
            $query->where(function ($q) {
                $q->where('kerawanan', 'like', '%Trouble%')
                    ->orWhere('levelgangguan', 'like', '%Trouble%');
            });
        } else {
            $query->where('kerawanan', 'like', '%Black Spot%');
        }

        $this->applyDateFilters($query, $request, 'tanggal');

        return $query->orderByDesc('id_datagangguan')->get()
            ->map(function ($row, $i) use ($status) {
                $coords = $this->parseKoordinat($row->koordinat ?? null)
                    ?? $this->fallbackCoords((int) ($row->id_datagangguan ?? ($i + 1)));

                return [
                    'rowid' => $row->id_datagangguan ?? ($i + 1),
                    'id' => $row->id_datagangguan ?? ($i + 1),
                    'lat' => $coords['lat'],
                    'lng' => $coords['lng'],
                    'status' => str_contains($status, 'trouble') ? 'trouble spot' : 'black spot',
                    'status_b_t_s' => 'Aktif',
                    'nama_jalan_s' => $row->namajalan ?? '',
                    'namajalan' => $row->namajalan ?? '',
                    'lokasi' => $row->fungsijalan ?? '',
                    'kerawanan' => $row->kerawanan ?? '',
                ];
            })
            ->values()
            ->all();
    }

    private function mapTmcRawanRow(object $row, int $id, string $kerawanan): array
    {
        return [
            'rowid' => $row->rowid ?? $id,
            'id' => $row->rowid ?? $id,
            'lat' => (float) $row->lat,
            'lng' => (float) $row->lng,
            'kerawanan' => $kerawanan,
            'namajalan' => $row->jalan ?? '',
            'nama_jalan_s' => $row->jalan ?? '',
            'polda_id' => $row->polda ?? '',
            'polres_id' => $row->polres ?? '',
        ];
    }

    private function mapGangguanRow(object $row, int $id, string $kerawanan): array
    {
        $coords = $this->parseKoordinat($row->koordinat ?? null)
            ?? $this->fallbackCoords((int) ($row->id_datagangguan ?? $id));

        return [
            'rowid' => $row->id_datagangguan ?? $id,
            'id' => $row->id_datagangguan ?? $id,
            'lat' => $coords['lat'],
            'lng' => $coords['lng'],
            'kerawanan' => $row->kerawanan ?? $kerawanan,
            'namajalan' => $row->namajalan ?? '',
            'nama_jalan_s' => $row->namajalan ?? '',
            'lokasi' => $row->fungsijalan ?? '',
        ];
    }

    private function kerawananRow(int $rowid, string $label): array
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $label));

        return [
            'rowid' => $rowid,
            'kategori' => $label,
            'kerawanan' => $label,
            'icon' => null,
            'url_icon' => './my/satupeta/' . trim($slug, '-') . '.png',
        ];
    }

    private function defaultPolda(Connection $db): array
    {
        $fromSession = session('polda');
        if ($fromSession && $this->hasTable($db, 'master_kl_daerah')) {
            $row = $db->table('master_kl_daerah')->where('id', $fromSession)->first();
            if ($row) {
                return ['id' => (int) $row->id, 'nama' => $row->nama];
            }
        }

        $fromEnv = config('satupeta.polda_id');
        if ($fromEnv && $this->hasTable($db, 'master_kl_daerah')) {
            $row = $db->table('master_kl_daerah')->where('id', $fromEnv)->first();
            if ($row) {
                return ['id' => (int) $row->id, 'nama' => $row->nama];
            }
        }

        if ($this->hasTable($db, 'master_kl_daerah')) {
            $row = $db->table('master_kl_daerah')->orderBy('id')->first();
            if ($row) {
                return ['id' => (int) $row->id, 'nama' => $row->nama];
            }
        }

        return ['id' => 1, 'nama' => 'POLDA'];
    }

    private function polresForPolda(Connection $db, int $poldaId): array
    {
        if (!$this->hasTable($db, 'master_kl_resort')) {
            return [['id' => '1', 'nama' => 'POLRES']];
        }

        return $db->table('master_kl_resort')
            ->where('parent_id', $poldaId)
            ->orderBy('nama')
            ->get()
            ->map(fn ($row) => ['id' => (int) $row->id, 'nama' => $row->nama])
            ->all();
    }

    private function applyTerritoryFilters($query, Request $request, string $poldaCol, string $polresCol): void
    {
        if ($request->filled('polda_id')) {
            $query->where($poldaCol, $request->query('polda_id'));
        }
        if ($request->filled('polres_id')) {
            $query->where($polresCol, $request->query('polres_id'));
        }
    }

    private function applyDateFilters($query, Request $request, string $dateCol): void
    {
        if ($request->filled('tgl_start')) {
            $query->where($dateCol, '>=', $request->query('tgl_start'));
        }
        if ($request->filled('tgl_end')) {
            $query->where($dateCol, '<=', $request->query('tgl_end'));
        }
    }

    private function decodePolygon(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function parseKoordinat(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $parts = array_map('trim', explode(',', $value));
        if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
            return null;
        }

        return ['lat' => (float) $parts[0], 'lng' => (float) $parts[1]];
    }

    private function fallbackCoords(int $seed): array
    {
        $lat = (float) (session('lat') ?: config('satupeta.lat_center'));
        $lng = (float) (session('lng') ?: config('satupeta.lng_center'));
        $n = $seed % 20;

        return [
            'lat' => $lat + ($n * 0.002),
            'lng' => $lng + ($n * 0.002),
        ];
    }

    private function hasTable(Connection $db, string $table): bool
    {
        return Schema::connection($db->getName())->hasTable($table);
    }

    private function db(): Connection
    {
        foreach ($this->connectionCandidates() as $name) {
            $schema = Schema::connection($name);
            if ($schema->hasTable('data_gangguan')
                || $schema->hasTable('tmc_data_statusjalan')
                || $schema->hasTable('tbl_provinsi')
                || $schema->hasTable('master_provinsi')) {
                return DB::connection($name);
            }
        }

        return DB::connection('satupeta');
    }

    private function connectionCandidates(): array
    {
        $list = ['satupeta', config('database.default', 'mysql')];

        return array_values(array_unique($list));
    }
}
