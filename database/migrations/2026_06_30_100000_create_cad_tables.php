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
        if (!Schema::hasTable('tbl_cad_task')) {
            Schema::create('tbl_cad_task', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 200);
                $table->string('address', 300);
                $table->double('latitude')->default(0);
                $table->double('longitude')->default(0);
                $table->enum('priority', ['HIGH', 'MEDIUM', 'LOW'])->default('MEDIUM');
                $table->string('status', 30)->default('NEW');
                $table->string('pelapor', 100)->nullable();
                $table->text('keterangan')->nullable();
                $table->integer('created_by')->nullable();
                $table->integer('closed_by')->nullable();
                $table->datetime('closed_at')->nullable();
                $table->tinyInteger('has_bukti')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tbl_cad_assignment')) {
            Schema::create('tbl_cad_assignment', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('task_id');
                $table->integer('user_id');
                $table->string('status', 30)->default('ASSIGNED');
                $table->text('keterangan')->nullable();
                $table->datetime('waktu_selesai')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tbl_cad_assignment_log')) {
            Schema::create('tbl_cad_assignment_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('task_id');
                $table->integer('user_id');
                $table->string('status', 30);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('tbl_cad_close_log')) {
            Schema::create('tbl_cad_close_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('task_id');
                $table->integer('closed_by');
                $table->text('catatan')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('tbl_cad_photo')) {
            Schema::create('tbl_cad_photo', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('task_id');
                $table->integer('user_id')->nullable();
                $table->string('url', 500);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('tbl_location_log_mobile')) {
            Schema::create('tbl_location_log_mobile', function (Blueprint $table) {
                $table->bigIncrements('log_id');
                $table->unsignedInteger('user_id');
                $table->decimal('lat', 10, 7);
                $table->decimal('lng', 10, 7);
                $table->date('ctddate');
                $table->time('ctdtime');
                $table->datetime('datetime');
                $table->enum('status', ['ONLINE', 'BERTUGAS', 'OFFLINE']);
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_location_log_mobile');
        Schema::dropIfExists('tbl_cad_photo');
        Schema::dropIfExists('tbl_cad_close_log');
        Schema::dropIfExists('tbl_cad_assignment_log');
        Schema::dropIfExists('tbl_cad_assignment');
        Schema::dropIfExists('tbl_cad_task');
    }
};
