<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DataFaskes extends Model
{
    protected $table = 'data_faskes';

    protected $primaryKey = 'id_faskes';

    public $timestamps = false;

    protected $fillable = [
        'piket', 'tanggal', 'katfaskes', 'namafaskes', 'lokasi', 'koordinat',
        'fasilitas', 'namecp', 'cp', 'telpambulance', 'telpigd', 'fotoobject',
    ];

    protected $casts = [
        'tanggal' => 'date',
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
        $disk = Storage::disk('public');

        return $disk->url($this->fotoobject);
    }
}
