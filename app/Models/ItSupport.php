<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItSupport extends Model
{
    protected $table = 'itsupport';
    protected $primaryKey = 'id_itsupport';
    public $timestamps = false;

    protected $fillable = [
        'piket', 'tanggal', 'namedevice', 'owner', 'tingkatgangguan',
        'ketsingkat', 'fotokondisi', 'katdevice', 'lokasidevice', 'statusticket',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function fotoUrl(): ?string
    {
        if (empty($this->fotokondisi)) {
            return null;
        }
        if (preg_match('#^https?://#i', $this->fotokondisi)) {
            return $this->fotokondisi;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        return $disk->url($this->fotokondisi);
    }
}
