<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempatIbadah extends Model
{
    protected $table = 'tempatibadah';
    protected $primaryKey = 'id_tempatibadah';
    public $timestamps = false;

    protected $fillable = [
        'piket', 'tanggal', 'kattempat', 'namatempatibadah', 'lokasi', 'koordinat',
        'kappengunjung', 'kapparkir', 'alatkeamanan', 'cp', 'namecp', 'fotoobject',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'kappengunjung' => 'integer',
        'kapparkir' => 'integer',
    ];

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
