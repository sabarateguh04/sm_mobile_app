<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel `turjagwali` di dump sbo lawas sering tidak punya kolom yang dipakai join/filter aplikasi.
     */
    public function up(): void
    {
        if (! Schema::hasTable('turjagwali')) {
            return;
        }

        Schema::table('turjagwali', function (Blueprint $table) {
            if (! Schema::hasColumn('turjagwali', 'id_profileposko')) {
                $table->unsignedBigInteger('id_profileposko')->nullable();
            }
            if (! Schema::hasColumn('turjagwali', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('turjagwali', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        //
    }
};
