<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Org extends Model
{
    protected $table = 'org';
    protected $primaryKey = 'id_org';
    
    protected $fillable = [
        'nameorg',
    ];

    // Relationships
    public function suborgs()
    {
        return $this->hasMany(Suborg::class, 'id_org', 'id_org');
    }
}
