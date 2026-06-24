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
        if (! Schema::hasTable('petugas_tracking')) {
            Schema::create('petugas_tracking', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->unique();
                $table->double('lat', 10, 8)->nullable();
                $table->double('lng', 11, 8)->nullable();
                $table->string('status', 32)->default('OFFLINE');
                $table->date('ctddate')->nullable();
                $table->time('ctdtime')->nullable();
                $table->timestamp('last_updated')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petugas_tracking');
    }
};
