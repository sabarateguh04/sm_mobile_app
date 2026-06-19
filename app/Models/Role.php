<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'role_id';
    
    protected $fillable = [
        'role_name',
    ];

    // Relationships
    public function permissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id', 'role_id');
    }

    public function users()
    {
        return $this->hasMany(Usm::class, 'user_role_id', 'role_id');
    }
}
