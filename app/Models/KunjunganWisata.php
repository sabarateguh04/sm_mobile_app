<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KunjunganWisata extends Model
{
    protected $table = 'kunjunganwisata';
    protected $primaryKey = 'id_kunjunganwisata';
    public $timestamps = false;

    protected $fillable = [
        'piket', 'tanggal', 'fotogiat', 'katwisata', 'namawisata',
        'koresponden', 'parkirr2', 'parkirr4', 'domestic',
        'mancanegara', 'jmlhpengunjung', 'jmlhkbm',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'parkirr2' => 'integer',
        'parkirr4' => 'integer',
        'domestic' => 'integer',
        'mancanegara' => 'integer',
        'jmlhpengunjung' => 'integer',
        'jmlhkbm' => 'integer',
    ];

    public function fotoUrl(): ?string
    {
        return self::resolveFotoUrl($this->fotogiat);
    }

    public static function resolveFotoUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        return $disk->url($path);
    }
}
