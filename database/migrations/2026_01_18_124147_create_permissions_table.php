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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id('permission_id');
            $table->foreignId('role_id')->constrained('roles', 'id')->onDelete('cascade');
            $table->string('page_name'); // controller name
            $table->string('action_name'); // action name (list, view, add, edit, delete)
            $table->timestamps();
            
            // Unique constraint: one role can't have duplicate permission
            $table->unique(['role_id', 'page_name', 'action_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
