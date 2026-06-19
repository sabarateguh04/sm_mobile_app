<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel `pos_pelayanan` (legacy: posyan) — Posko Pelayanan.
 * Tabel tidak punya created_at/updated_at.
 */
class Posyan extends Model
{
    protected $table = 'pos_pelayanan';
    protected $primaryKey = 'id_posyan';
    public $timestamps = false;

    protected $fillable = [
        'tanggal', 'piket', 'jenisops', 'nameops', 'sasaranops',
        'namaposyan', 'lokasiposyan', 'koordinat', 'sarprasposyan',
        'fotoposyan', 'tahunops',
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
}
