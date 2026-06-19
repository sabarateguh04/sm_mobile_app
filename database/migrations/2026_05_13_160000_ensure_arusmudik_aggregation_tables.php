<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair: aggregation tables can be missing when migrations are already recorded
     * (e.g. DB restore, manual schema drift). Legacy `sbo` uses INT for `arusmudik.id_arusmudik`
     * and `usm.id_usm`, not BIGINT.
     */
    public function up(): void
    {
        if (Schema::hasTable('arusmudik') && ! Schema::hasColumn('arusmudik', 'id_profileposko')) {
            Schema::table('arusmudik', function (Blueprint $table) {
                $table->unsignedBigInteger('id_profileposko')->nullable()->after('id_arusmudik');
            });
        }

        if ($this->shouldRebuildDaArusmudik()) {
            Schema::dropIfExists('da_arusmudik');
            Schema::create('da_arusmudik', function (Blueprint $table) {
                $table->id('id_da_arusmudik');
                $table->integer('id_arusmudik')->comment('FK ke arusmudik');
                $table->string('daerah')->comment('Daerah untuk filter');
                $table->string('wilayah')->nullable()->comment('Wilayah');
                $table->string('katops')->nullable()->comment('Kategori Ops dari profilposko');
                $table->string('jenisops')->nullable()->comment('Jenis Ops dari profilposko');
                $table->date('tanggal')->nullable()->comment('Tanggal dari arusmudik');
                $table->string('namaposko')->nullable()->comment('Nama Posko dari profilposko');
                $table->unsignedBigInteger('arusmudik_id_profileposko')->nullable()->comment('ID Profile Posko dari tabel arusmudik');
                $table->timestamps();

                $table->foreign('id_arusmudik')->references('id_arusmudik')->on('arusmudik')->onDelete('cascade');

                $table->index('daerah');
                $table->index('wilayah');
                $table->index('id_arusmudik');
            });
        }

        if (! Schema::hasTable('wil_arusmudik')) {
            // Legacy sbo may define these names as VIEWs; hasTable() ignores views so CREATE would fail.
            DB::statement('DROP VIEW IF EXISTS wil_arusmudik');
            Schema::create('wil_arusmudik', function (Blueprint $table) {
                $table->id('id_wil_arusmudik');
                $table->integer('id_arusmudik')->comment('FK ke arusmudik');
                $table->string('wilayah')->comment('Wilayah untuk filter');
                $table->unsignedBigInteger('id_profileposko')->nullable()->comment('Profile Posko');
                $table->unsignedBigInteger('arusmudik_id_profileposko')->nullable()->comment('ID Profile Posko dari tabel arusmudik');
                $table->date('tanggal')->nullable()->comment('Tanggal dari arusmudik');
                $table->integer('piket')->nullable()->comment('ID Piket dari arusmudik');
                $table->string('usm_nama')->nullable()->comment('Nama Piket dari usm');
                $table->timestamps();

                $table->foreign('id_arusmudik')->references('id_arusmudik')->on('arusmudik')->onDelete('cascade');
                $table->foreign('id_profileposko')->references('id_profilposko')->on('profilposko')->nullOnDelete();
                $table->foreign('piket')->references('id_usm')->on('usm')->nullOnDelete();

                $table->index('wilayah');
                $table->index('id_arusmudik');
            });
        }

        if (! Schema::hasTable('nas_arusmudik')) {
            DB::statement('DROP VIEW IF EXISTS nas_arusmudik');
            Schema::create('nas_arusmudik', function (Blueprint $table) {
                $table->id('id_nas_arusmudik');
                $table->integer('id_arusmudik')->comment('FK ke arusmudik');
                $table->string('daerah')->nullable()->comment('Daerah');
                $table->string('wilayah')->nullable()->comment('Wilayah');
                $table->unsignedBigInteger('id_profileposko')->nullable()->comment('Profile Posko');
                $table->unsignedBigInteger('arusmudik_id_profileposko')->nullable()->comment('ID Profile Posko dari tabel arusmudik');
                $table->string('statarus')->nullable()->comment('Status Arus dari arusmudik');
                $table->date('tanggal')->nullable()->comment('Tanggal dari arusmudik');
                $table->integer('piket')->nullable()->comment('ID Piket dari arusmudik');
                $table->string('usm_nama')->nullable()->comment('Nama Piket dari usm');
                $table->timestamps();

                $table->foreign('id_arusmudik')->references('id_arusmudik')->on('arusmudik')->onDelete('cascade');
                $table->foreign('id_profileposko')->references('id_profilposko')->on('profilposko')->nullOnDelete();
                $table->foreign('piket')->references('id_usm')->on('usm')->nullOnDelete();

                $table->index('daerah');
                $table->index('wilayah');
                $table->index('id_arusmudik');
            });
        }
    }

    protected function shouldRebuildDaArusmudik(): bool
    {
        if (! Schema::hasTable('da_arusmudik')) {
            return true;
        }

        $count = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_TYPE = ?',
            ['da_arusmudik', 'FOREIGN KEY']
        );

        return ! $count || (int) $count->c === 0;
    }

    public function down(): void
    {
    }
};
