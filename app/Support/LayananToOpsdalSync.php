<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LayananToOpsdalSync
{
    public static function syncFromLayananId(int $layananId): void
    {
        if ($layananId <= 0) {
            return;
        }

        $layanan = DB::table('layanan_masyarakat')
            ->where('id_interaksi', $layananId)
            ->first();

        if (! $layanan) {
            return;
        }

        $category = static::normalizeText($layanan->kategori ?? '');
        $tema = static::normalizeText($layanan->tema ?? '');
        $keterangan = static::normalizeText($layanan->keterangan ?? '');
        $haystack = trim($category.' '.$tema.' '.$keterangan);
        if ($haystack === '') {
            return;
        }

        $isAduan = str_contains($category, 'aduan') || str_contains($haystack, 'aduan');
        $isTraffic = str_contains($haystack, 'macet')
            || str_contains($haystack, 'kemacetan')
            || str_contains($haystack, 'perlambatan');
        $isContigency = str_contains($haystack, 'kecelakaan')
            || str_contains($haystack, 'kebakaran')
            || str_contains($haystack, 'bencana');

        if ($isContigency || ($isAduan && ! $isTraffic)) {
            static::createContigencyPendingFromLayanan($layanan);

            return;
        }

        if ($isTraffic) {
            static::createTrafficstuckPendingFromLayanan($layanan);
        }
    }

    protected static function normalizeText(mixed $value): string
    {
        return mb_strtolower(trim((string) $value), 'UTF-8');
    }

    protected static function createContigencyPendingFromLayanan(object $layanan): void
    {
        if (! Schema::hasTable('contigency')) {
            return;
        }

        $exists = DB::table('contigency')
            ->where('source_module', 'layanan_masyarakat')
            ->where('source_id', (int) $layanan->id_interaksi)
            ->exists();

        if ($exists) {
            return;
        }

        $ownerId = (int) ($layanan->id_usm ?? 0);
        if ($ownerId <= 0) {
            $ownerId = RoleMutationPolicy::resolveActorUserId(Auth::user());
        }
        $foto = trim((string) ($layanan->foto ?? ''));
        $lokasi = static::resolveLayananLocation($layanan);
        $sourceNote = 'Sumber: Layanan Masyarakat #'.((int) $layanan->id_interaksi);
        $rawKet = trim((string) ($layanan->keterangan ?? ''));
        $ketcb = $sourceNote.($rawKet !== '' ? "\n".$rawKet : '');

        $data = [
            'tanggal' => $layanan->tanggal ?? now()->toDateString(),
            'piket' => (string) max(0, $ownerId),
            'lokasi' => $lokasi,
            'koordinat' => (string) ($layanan->koordinat ?? ''),
            'jeniscontigency' => (string) ($layanan->kategori ?? 'KECELAKAAN'),
            'faktor' => '-',
            'penyebab' => '-',
            'korbanmd' => 0,
            'korbanlb' => 0,
            'korbanlr' => 0,
            'kermat' => 0,
            'petugastptkp' => '-',
            'ketcb' => $ketcb,
            'jamperistiwa' => now()->format('H:i:s'),
            'sb_grp' => 'opsdal',
            'source_module' => 'layanan_masyarakat',
            'source_id' => (int) $layanan->id_interaksi,
            'status_konfirmasi_opsdal' => 'menunggu',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if ($foto !== '') {
            $data['fotosit'] = mb_substr($foto, 0, 255);
        }

        static::insertLegacyRow('contigency', 'id_contigency', $data);
    }

    protected static function createTrafficstuckPendingFromLayanan(object $layanan): void
    {
        if (! Schema::hasTable('trafficstuck')) {
            return;
        }

        $exists = DB::table('trafficstuck')
            ->where('source_module', 'layanan_masyarakat')
            ->where('source_id', (int) $layanan->id_interaksi)
            ->exists();

        if ($exists) {
            return;
        }

        $ownerId = (int) ($layanan->id_usm ?? 0);
        if ($ownerId <= 0) {
            $ownerId = RoleMutationPolicy::resolveActorUserId(Auth::user());
        }
        $foto = trim((string) ($layanan->foto ?? ''));
        $lokasi = static::resolveLayananLocation($layanan);
        $sourceNote = 'Sumber: Layanan Masyarakat #'.((int) $layanan->id_interaksi);
        $rawKet = trim((string) ($layanan->keterangan ?? ''));
        $ketcb = $sourceNote.($rawKet !== '' ? "\n".$rawKet : '');

        $data = [
            'tanggal' => $layanan->tanggal ?? now()->toDateString(),
            'piket' => (string) max(0, $ownerId),
            'lokasi' => $lokasi,
            'koordinat' => (string) ($layanan->koordinat ?? ''),
            'faktor' => (string) ($layanan->kategori ?? 'KEMACETAN'),
            'penyebab' => '-',
            'durasi' => '-',
            'timurai' => '-',
            'ketcb' => $ketcb,
            'jamperistiwa' => now()->format('H:i:s'),
            'sb_grp' => 'opsdal',
            'source_module' => 'layanan_masyarakat',
            'source_id' => (int) $layanan->id_interaksi,
            'status_konfirmasi_opsdal' => 'menunggu',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if ($foto !== '') {
            $data['fotosituasi'] = mb_substr($foto, 0, 255);
        }

        static::insertLegacyRow('trafficstuck', 'id_trafficstuck', $data);
    }

    protected static function resolveLayananLocation(object $layanan): string
    {
        $lokasi = trim((string) ($layanan->lokasi ?? ''));
        if ($lokasi !== '') {
            return mb_substr($lokasi, 0, 255);
        }

        $tema = trim((string) ($layanan->tema ?? ''));
        if ($tema !== '') {
            return mb_substr($tema, 0, 255);
        }

        $ket = trim((string) ($layanan->keterangan ?? ''));
        if ($ket !== '') {
            return mb_substr($ket, 0, 255);
        }

        return 'Lokasi dari Layanan Masyarakat';
    }

    protected static function insertLegacyRow(string $tableName, string $primaryKey, array $data): void
    {
        try {
            DB::table($tableName)->insertGetId($data, $primaryKey);

            return;
        } catch (QueryException $e) {
            if (! str_contains($e->getMessage(), "doesn't have a default value")) {
                throw $e;
            }
        }

        $nextId = (int) DB::table($tableName)->max($primaryKey) + 1;
        $data[$primaryKey] = $nextId;
        DB::table($tableName)->insert($data);
    }
}
