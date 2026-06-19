<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Inventaris CCTV — tabel `datacctv` atau kolom inventaris di `patrolicctv`.
 */
class DataCctv extends Model
{
    protected $table = 'datacctv';

    protected $primaryKey = 'id_datacctv';

    public $timestamps = false;

    protected $fillable = [
        'piket',
        'owner',
        'lokasi',
        'ipaddres',
        'brand',
        'fungsicctv',
        'tanggal',
        'koordinat',
        'fotofisik',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(Usm::class, 'piket', 'id_usm');
    }

    public function fotoUrl(): ?string
    {
        $path = $this->fotofisik ?? $this->fotosit ?? null;
        if (empty($path)) {
            return null;
        }

        $first = trim(explode(',', (string) $path)[0]);
        if ($first === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $first)) {
            return $first;
        }

        if (str_starts_with($first, '/storage/')) {
            return $first;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url($first);
    }
}
