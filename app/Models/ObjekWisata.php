<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel `objek_wisata` (legacy: t_tempatwisata).
 * Tabel tidak punya created_at/updated_at.
 */
class ObjekWisata extends Model
{
    protected $table = 'objek_wisata';
    protected $primaryKey = 'id_tempatwisata';
    public $timestamps = false;

    protected $fillable = [
        'piket', 'kattempatwisata', 'nametempat', 'lokasi', 'koordinat',
        'kappengunjung', 'kapparkir', 'cp', 'namecp',
        'fotoobject', 'alatkamanan', 'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'kappengunjung' => 'integer',
        'kapparkir' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(Usm::class, 'piket', 'id_usm');
    }

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
}
