<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('interaksi') || ! Schema::hasColumn('interaksi', 'id_usm')) {
            return;
        }

        if (Schema::hasColumn('interaksi', 'piket')) {
            DB::table('interaksi')
                ->whereNull('id_usm')
                ->whereNotNull('piket')
                ->where('piket', '>', 0)
                ->update(['id_usm' => DB::raw('piket')]);
        }
    }

    public function down(): void
    {
        // Data backfill — tidak di-rollback.
    }
};
