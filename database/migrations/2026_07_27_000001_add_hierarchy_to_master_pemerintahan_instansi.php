<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_pemerintahan_instansi', function (Blueprint $table) {
            if (! Schema::hasColumn('master_pemerintahan_instansi', 'organisasi')) {
                $table->string('organisasi')->nullable()->after('lokasi');
            }
            if (! Schema::hasColumn('master_pemerintahan_instansi', 'sub_org')) {
                $table->string('sub_org')->nullable()->after('organisasi');
            }
            if (! Schema::hasColumn('master_pemerintahan_instansi', 'unit_kerja')) {
                $table->string('unit_kerja')->nullable()->after('sub_org');
            }
        });
    }

    public function down(): void
    {
        Schema::table('master_pemerintahan_instansi', function (Blueprint $table) {
            $table->dropColumnIfExists(['organisasi', 'sub_org', 'unit_kerja']);
        });
    }
};
