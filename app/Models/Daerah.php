<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Daerah extends Model
{
    protected $table = 'daerah';
    protected $primaryKey = 'id_daerah';
    
    protected $fillable = [
        'namadaerah',
    ];

    // Relationships
    public function wilayahs()
    {
        return $this->hasMany(Wilayah::class, 'id_daerah', 'id_daerah');
    }
}
