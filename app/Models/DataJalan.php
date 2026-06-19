<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel `t_datajalan` (database sbo).
 */
class DataJalan extends Model
{
    protected $table = 't_datajalan';

    protected $primaryKey = 'id_datajalan';

    public $timestamps = false;

    protected $fillable = [
        'jenisjalan', 'namajalan', 'panjangjalan', 'piket',
        'lintasan', 'fotoobject', 'prasjal', 'tanggal', 'koordinat',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'panjangjalan' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(Usm::class, 'piket', 'id_usm');
    }

    /** URL tampil untuk satu path foto (upload storage atau URL absolut legacy SBO). */
    public static function publicUrlForFoto(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        $v = trim($path);
        if ($v === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $v)) {
            return $v;
        }

        return asset('storage/'.ltrim($v, '/'));
    }

    /**
     * Legacy SBO menyimpan beberapa foto dalam satu kolom `fotoobject` dipisah koma.
     *
     * @return list<string>
     */
    public static function publicUrlsForFoto(?string $paths): array
    {
        if (empty($paths)) {
            return [];
        }

        $urls = [];
        foreach (array_filter(array_map('trim', explode(',', $paths))) as $part) {
            $url = self::publicUrlForFoto($part);
            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    /** @return list<string> */
    public function fotoObjectUrls(): array
    {
        return self::publicUrlsForFoto($this->fotoobject);
    }

    public function fotoObjectUrl(): ?string
    {
        $urls = $this->fotoObjectUrls();

        return $urls[0] ?? null;
    }
}
