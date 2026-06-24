<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasHierarchicalFilter;

class Usm extends Authenticatable
{
    use Notifiable, HasHierarchicalFilter;

    protected $table = 'usm';
    protected $primaryKey = 'id_usm';

    /**
     * Database `sbo` -> tabel `usm` tidak punya created_at / updated_at.
     */
    public $timestamps = false;

    protected $fillable = [
        'username',
        'email',
        'password',
        'nama',
        'nrp_nip_nik',
        'induk_pemerintahan',
        'pemerintahan_daerah',
        'organisasi_daerah',
        'unit_kerja',
        'kategori_user',
        'daerah',
        'wilayah',
        'notelp',
        'fotoprof',
        'account_status',
        'user_role_id',
        'email_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'login_session_key',
        'password_reset_key',
    ];

    protected $casts = [
        'password_expire_date' => 'datetime',
    ];

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class, 'user_role_id', 'role_id');
    }

    /**
     * Label role untuk tampilan UI (bukan relasi `role()`).
     */
    public function getRoleLabelAttribute(): string
    {
        $role = $this->relationLoaded('role')
            ? $this->getRelation('role')
            : $this->role()->first();

        $name = trim((string) ($role?->role_name ?? ''));

        return $name !== '' ? $name : 'Pengguna';
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [
            'username' => $this->username,
            'nama' => $this->nama,
            'role_id' => $this->user_role_id,
        ];
    }
}
