<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Inventaris TIK — tabel `dataperangkat` (database sbo).
 */
class DataPerangkat extends Model
{
    protected $table = 'dataperangkat';

    protected $primaryKey = 'id_perangkat';

    public $timestamps = false;

    protected $fillable = [
        'piket',
        'tanggal',
        'katperangkat',
        'nameperangkat',
        'lokasi',
        'owner',
        'qtty',
        'supportfor',
        'fotoperangkat',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'piket' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(Usm::class, 'piket', 'id_usm');
    }

    /** URL foto pertama (legacy bisa CSV URL absolut atau path storage). */
    public function fotoUrl(): ?string
    {
        if (empty($this->fotoperangkat)) {
            return null;
        }

        $first = trim(explode(',', (string) $this->fotoperangkat)[0]);
        if ($first === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $first)) {
            return $first;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url($first);
    }
}
