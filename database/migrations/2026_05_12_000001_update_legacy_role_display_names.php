<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyelaraskan kolom roles.role_name dengan label bisnis (Super Admin, Executive, dll.).
 *
 * Catatan: DB lawas memakai roles.role_id; instalasi fresh Spatie memakai roles.id —
 * migrasi mendeteksi kolom PK yang ada. Nilai PK diasumsikan sama seperti matriks sbo (1–16).
 */
return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    protected array $labels = [
        1 => 'Super Admin',
        2 => 'Executive 1',
        3 => 'Executive 2 — Daerah',
        4 => 'Executive 2 — Wilayah',
        5 => 'Admin Back Office · FO',
        8 => 'Admin Back Office · IT',
        9 => 'Admin Back Office · Administrator',
        11 => 'Operator',
        14 => 'Executive 2 — Ops Daerah',
        15 => 'Executive 1 — Ops Nasional',
        16 => 'Admin Back Office',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasColumn('roles', 'role_name')) {
            return;
        }

        $pk = Schema::hasColumn('roles', 'role_id') ? 'role_id' : (Schema::hasColumn('roles', 'id') ? 'id' : null);
        if ($pk === null) {
            return;
        }

        foreach ($this->labels as $id => $roleName) {
            DB::table('roles')->where($pk, $id)->update(['role_name' => $roleName]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasColumn('roles', 'role_name')) {
            return;
        }

        $pk = Schema::hasColumn('roles', 'role_id') ? 'role_id' : (Schema::hasColumn('roles', 'id') ? 'id' : null);
        if ($pk === null) {
            return;
        }

        $revert = [
            1 => 'SA',
            2 => 'Wasdalnas',
            3 => 'Wasdalda',
            4 => 'Wasdalwil',
            5 => 'Backoffice',
            8 => 'wasdaltik',
            9 => 'Administrator',
            11 => 'Posyan',
            14 => 'Wasdalopsda',
            15 => 'Wasdalopsnas',
            16 => 'Adminbo',
        ];

        foreach ($revert as $id => $roleName) {
            DB::table('roles')->where($pk, $id)->update(['role_name' => $roleName]);
        }
    }
};
