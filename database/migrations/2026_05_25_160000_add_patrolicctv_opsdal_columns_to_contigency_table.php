<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contigency')) {
            return;
        }

        Schema::table('contigency', function (Blueprint $table) {
            if (! Schema::hasColumn('contigency', 'id_patrolicctv')) {
                $table->unsignedInteger('id_patrolicctv')->nullable()->after('id_contigency');
                $table->unique('id_patrolicctv');
            }
            if (! Schema::hasColumn('contigency', 'sb_grp')) {
                $table->string('sb_grp', 32)->nullable()->after('id_patrolicctv');
                $table->index('sb_grp');
            }
            if (! Schema::hasColumn('contigency', 'operator_edited_by')) {
                $table->string('operator_edited_by', 100)->nullable()->after('piket');
            }
            if (! Schema::hasColumn('contigency', 'operator_edited_at')) {
                $table->timestamp('operator_edited_at')->nullable()->after('operator_edited_by');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('contigency')) {
            return;
        }

        Schema::table('contigency', function (Blueprint $table) {
            foreach (['operator_edited_at', 'operator_edited_by', 'sb_grp', 'id_patrolicctv'] as $col) {
                if (Schema::hasColumn('contigency', $col)) {
                    if ($col === 'id_patrolicctv') {
                        $table->dropUnique(['id_patrolicctv']);
                    }
                    if ($col === 'sb_grp') {
                        $table->dropIndex(['sb_grp']);
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
