<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['contigency', 'trafficstuck'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (! Schema::hasColumn($tableName, 'source_module')) {
                    $table->string('source_module', 50)->nullable()->after('sb_grp');
                }
                if (! Schema::hasColumn($tableName, 'source_id')) {
                    $table->unsignedBigInteger('source_id')->nullable()->after('source_module');
                }
                if (! Schema::hasColumn($tableName, 'status_konfirmasi_opsdal')) {
                    $table->string('status_konfirmasi_opsdal', 32)->nullable()->after('source_id');
                }
                if (! Schema::hasColumn($tableName, 'opsdal_confirmed_by')) {
                    $table->unsignedBigInteger('opsdal_confirmed_by')->nullable()->after('status_konfirmasi_opsdal');
                }
                if (! Schema::hasColumn($tableName, 'opsdal_confirmed_at')) {
                    $table->timestamp('opsdal_confirmed_at')->nullable()->after('opsdal_confirmed_by');
                }
                if (! Schema::hasColumn($tableName, 'opsdal_confirmation_note')) {
                    $table->text('opsdal_confirmation_note')->nullable()->after('opsdal_confirmed_at');
                }
            });

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->index(['status_konfirmasi_opsdal'], $tableName.'_opsdal_status_idx');
                $table->index(['source_module', 'source_id'], $tableName.'_source_idx');
            });
        }
    }

    public function down(): void
    {
        foreach (['contigency', 'trafficstuck'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                foreach ([$tableName.'_opsdal_status_idx', $tableName.'_source_idx'] as $idx) {
                    try {
                        $table->dropIndex($idx);
                    } catch (\Throwable) {
                    }
                }

                foreach ([
                    'opsdal_confirmation_note',
                    'opsdal_confirmed_at',
                    'opsdal_confirmed_by',
                    'status_konfirmasi_opsdal',
                    'source_id',
                    'source_module',
                ] as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
