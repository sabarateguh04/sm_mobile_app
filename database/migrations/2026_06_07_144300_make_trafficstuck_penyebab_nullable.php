<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trafficstuck')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE `trafficstuck` MODIFY `penyebab` VARCHAR(100) NULL');
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
    }
};
