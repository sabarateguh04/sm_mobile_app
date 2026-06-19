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
        Schema::create('usm', function (Blueprint $table) {
            $table->id('id_usm');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('nama');
            $table->string('daerah')->nullable();
            $table->string('wilayah')->nullable();
            $table->string('org')->nullable();
            $table->string('suborg')->nullable();
            $table->string('notelp')->nullable();
            $table->string('fotoprof')->nullable();
            $table->enum('account_status', ['active', 'pending', 'blocked'])->default('pending');
            $table->foreignId('user_role_id')->nullable()->constrained('roles', 'id')->nullOnDelete();
            $table->string('login_session_key')->nullable();
            $table->dateTime('password_expire_date')->nullable();
            $table->string('password_reset_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usm');
    }
};
