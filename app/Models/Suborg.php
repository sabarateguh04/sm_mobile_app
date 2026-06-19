<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suborg extends Model
{
    protected $table = 'suborg';
    protected $primaryKey = 'id_suborg';
    
    protected $fillable = [
        'id_org',
        'pem_daerah',
        'namesuborg',
    ];

    // Relationships
    public function org()
    {
        return $this->belongsTo(Org::class, 'id_org', 'id_org');
    }
}
