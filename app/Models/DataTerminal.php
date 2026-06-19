<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel `data_terminal` (legacy: dataterminal).
 * Tabel tidak punya created_at/updated_at.
 */
class DataTerminal extends Model
{
    protected $table = 'data_terminal';
    protected $primaryKey = 'id_terminal';
    public $timestamps = false;

    protected $fillable = [
        'piket', 'jenisterminal', 'namaterminal', 'cp', 'fasilitas',
        'jalan', 'namecp', 'koordinat', 'fotoobject', 'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(Usm::class, 'piket', 'id_usm');
    }

    /**
     * Kembalikan URL foto yang dapat ditampilkan di browser.
     * Mendukung path storage lokal maupun URL eksternal legacy.
     */
    public function fotoUrl(): ?string
    {
        if (empty($this->fotoobject)) {
            return null;
        }
        if (preg_match('#^https?://#i', $this->fotoobject)) {
            return $this->fotoobject;
        }
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        return $disk->url($this->fotoobject);
    }

    /**
     * Parse koordinat "lat,lng" jadi array [lat, lng].
     */
    public function getLatLngAttribute(): ?array
    {
        if (empty($this->koordinat)) return null;
        $parts = array_map('trim', explode(',', $this->koordinat));
        if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) return null;
        return [(float) $parts[0], (float) $parts[1]];
    }
}
