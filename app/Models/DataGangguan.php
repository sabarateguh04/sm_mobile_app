<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel `data_gangguan` (legacy: datagangguan).
 * Tabel tidak punya created_at/updated_at.
 */
class DataGangguan extends Model
{
    protected $table = 'data_gangguan';
    protected $primaryKey = 'id_datagangguan';
    public $timestamps = false;

    protected $fillable = [
        'tanggal', 'piket', 'fungsijalan', 'namajalan', 'kerawanan',
        'faktor', 'penyebab', 'levelgangguan', 'teamsurvayor',
        'fotolokasi', 'koordinat',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(Usm::class, 'piket', 'id_usm');
    }

    public function getLatLngAttribute(): ?array
    {
        if (empty($this->koordinat)) return null;
        $parts = array_map('trim', explode(',', $this->koordinat));
        if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) return null;
        return [(float) $parts[0], (float) $parts[1]];
    }

    /** URL tampil untuk foto (upload storage atau URL absolut legacy). */
    public static function publicUrlForFoto(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        $v = trim($path);
        if (preg_match('#^https?://#i', $v)) {
            return $v;
        }

        return asset('storage/'.ltrim($v, '/'));
    }

    public function fotoLokasiUrl(): ?string
    {
        return self::publicUrlForFoto($this->fotolokasi);
    }
}
