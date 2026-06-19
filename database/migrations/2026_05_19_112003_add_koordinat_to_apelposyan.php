<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apelposyan', function (Blueprint $table) {
            if (! Schema::hasColumn('apelposyan', 'koordinat')) {
                $table->string('koordinat', 100)->nullable()->after('fotogiat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('apelposyan', function (Blueprint $table) {
            $table->dropColumn('koordinat');
        });
    }
};
