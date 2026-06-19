<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom kepegawaian & struktur pemerintahan untuk tabel usm.
     */
    public function up(): void
    {
        if (! Schema::hasTable('usm')) {
            return;
        }

        Schema::table('usm', function (Blueprint $table) {
            if (! Schema::hasColumn('usm', 'nrp_nip_nik')) {
                $table->string('nrp_nip_nik', 191)->nullable();
            }
            if (! Schema::hasColumn('usm', 'induk_pemerintahan')) {
                $table->string('induk_pemerintahan', 255)->nullable();
            }
            if (! Schema::hasColumn('usm', 'pemerintahan_daerah')) {
                $table->string('pemerintahan_daerah', 255)->nullable();
            }
            if (! Schema::hasColumn('usm', 'unit_kerja')) {
                $table->string('unit_kerja', 255)->nullable();
            }
            if (! Schema::hasColumn('usm', 'kategori_user')) {
                $table->enum('kategori_user', ['organik', 'non_organik'])->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('usm')) {
            return;
        }

        $cols = collect([
            'nrp_nip_nik',
            'induk_pemerintahan',
            'pemerintahan_daerah',
            'unit_kerja',
            'kategori_user',
        ])->filter(fn (string $c) => Schema::hasColumn('usm', $c))->values()->all();

        if ($cols !== []) {
            Schema::table('usm', function (Blueprint $table) use ($cols) {
                $table->dropColumn($cols);
            });
        }
    }
};
