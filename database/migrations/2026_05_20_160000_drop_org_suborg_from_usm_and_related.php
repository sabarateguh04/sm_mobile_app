<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usm')) {
            Schema::table('usm', function (Blueprint $table) {
                if (Schema::hasColumn('usm', 'org')) {
                    $table->dropColumn('org');
                }
                if (Schema::hasColumn('usm', 'suborg')) {
                    $table->dropColumn('suborg');
                }
            });
        }

        if (Schema::hasTable('interaksi')) {
            Schema::table('interaksi', function (Blueprint $table) {
                if (Schema::hasColumn('interaksi', 'org')) {
                    $table->dropColumn('org');
                }
                if (Schema::hasColumn('interaksi', 'suborg')) {
                    $table->dropColumn('suborg');
                }
            });
        }

        if (Schema::hasTable('layanan_data')) {
            Schema::table('layanan_data', function (Blueprint $table) {
                if (Schema::hasColumn('layanan_data', 'org')) {
                    $table->dropColumn('org');
                }
                if (Schema::hasColumn('layanan_data', 'suborg')) {
                    $table->dropColumn('suborg');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('usm')) {
            Schema::table('usm', function (Blueprint $table) {
                if (! Schema::hasColumn('usm', 'org')) {
                    $table->string('org')->nullable();
                }
                if (! Schema::hasColumn('usm', 'suborg')) {
                    $table->string('suborg')->nullable();
                }
            });
        }

        if (Schema::hasTable('interaksi')) {
            Schema::table('interaksi', function (Blueprint $table) {
                if (! Schema::hasColumn('interaksi', 'org')) {
                    $table->string('org', 100)->nullable();
                }
                if (! Schema::hasColumn('interaksi', 'suborg')) {
                    $table->string('suborg', 100)->nullable();
                }
            });
        }

        if (Schema::hasTable('layanan_data')) {
            Schema::table('layanan_data', function (Blueprint $table) {
                if (! Schema::hasColumn('layanan_data', 'org')) {
                    $table->string('org', 100)->nullable();
                }
                if (! Schema::hasColumn('layanan_data', 'suborg')) {
                    $table->string('suborg', 100)->nullable();
                }
            });
        }
    }
};
