<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Akun di /admin/users harus bisa login: isi status kosong & pending jadi active.
     * Status blocked / inactive tetap (login tetap ditolak di LoginController).
     */
    public function up(): void
    {
        if (! Schema::hasTable('usm') || ! Schema::hasColumn('usm', 'account_status')) {
            return;
        }

        DB::table('usm')->whereNull('account_status')->update(['account_status' => 'active']);
        DB::table('usm')->where('account_status', '')->update(['account_status' => 'active']);
        DB::table('usm')->whereRaw('LOWER(TRIM(account_status)) = ?', ['pending'])->update(['account_status' => 'active']);
    }

    public function down(): void
    {
        //
    }
};
