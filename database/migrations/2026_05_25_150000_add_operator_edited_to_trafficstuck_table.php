<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trafficstuck')) {
            return;
        }

        Schema::table('trafficstuck', function (Blueprint $table) {
            if (! Schema::hasColumn('trafficstuck', 'operator_edited_by')) {
                $table->string('operator_edited_by', 100)->nullable()->after('piket');
            }
            if (! Schema::hasColumn('trafficstuck', 'operator_edited_at')) {
                $table->timestamp('operator_edited_at')->nullable()->after('operator_edited_by');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('trafficstuck')) {
            return;
        }

        Schema::table('trafficstuck', function (Blueprint $table) {
            if (Schema::hasColumn('trafficstuck', 'operator_edited_at')) {
                $table->dropColumn('operator_edited_at');
            }
            if (Schema::hasColumn('trafficstuck', 'operator_edited_by')) {
                $table->dropColumn('operator_edited_by');
            }
        });
    }
};
