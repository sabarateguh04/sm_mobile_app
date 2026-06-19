<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('daerah')) {
            Schema::table('daerah', function (Blueprint $table) {
                if (!Schema::hasColumn('daerah', 'lat')) {
                    $table->string('lat')->nullable();
                }
                if (!Schema::hasColumn('daerah', 'lng')) {
                    $table->string('lng')->nullable();
                }
            });
        }

        if (Schema::hasTable('wilayah')) {
            Schema::table('wilayah', function (Blueprint $table) {
                if (!Schema::hasColumn('wilayah', 'lat')) {
                    $table->string('lat')->nullable();
                }
                if (!Schema::hasColumn('wilayah', 'lng')) {
                    $table->string('lng')->nullable();
                }
            });
        }

        if (Schema::hasTable('master_provinsi')) {
            Schema::table('master_provinsi', function (Blueprint $table) {
                if (!Schema::hasColumn('master_provinsi', 'lat')) {
                    $table->string('lat')->nullable();
                }
                if (!Schema::hasColumn('master_provinsi', 'lng')) {
                    $table->string('lng')->nullable();
                }
            });
        }

        if (Schema::hasTable('master_kabupaten_kota')) {
            Schema::table('master_kabupaten_kota', function (Blueprint $table) {
                if (!Schema::hasColumn('master_kabupaten_kota', 'lat')) {
                    $table->string('lat')->nullable();
                }
                if (!Schema::hasColumn('master_kabupaten_kota', 'lng')) {
                    $table->string('lng')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('daerah')) {
            Schema::table('daerah', function (Blueprint $table) {
                $table->dropColumn(['lat', 'lng']);
            });
        }

        if (Schema::hasTable('wilayah')) {
            Schema::table('wilayah', function (Blueprint $table) {
                $table->dropColumn(['lat', 'lng']);
            });
        }

        if (Schema::hasTable('master_provinsi')) {
            Schema::table('master_provinsi', function (Blueprint $table) {
                $table->dropColumn(['lat', 'lng']);
            });
        }

        if (Schema::hasTable('master_kabupaten_kota')) {
            Schema::table('master_kabupaten_kota', function (Blueprint $table) {
                $table->dropColumn(['lat', 'lng']);
            });
        }
    }
};
