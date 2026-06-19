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
        Schema::create('suborg', function (Blueprint $table) {
            $table->id('id_suborg');
            $table->foreignId('id_org')->constrained('org', 'id_org')->onDelete('cascade');
            $table->string('namesuborg');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suborg');
    }
};
