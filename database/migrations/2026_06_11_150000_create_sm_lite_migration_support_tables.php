<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sm_sync_watermarks')) {
            Schema::create('sm_sync_watermarks', function (Blueprint $table) {
                $table->string('sync_key', 100)->primary();
                $table->unsignedBigInteger('last_id')->default(0);
                $table->timestamp('last_timestamp')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! Schema::hasTable('sm_sync_batches')) {
            Schema::create('sm_sync_batches', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('batch_key', 64)->unique();
                $table->string('direction', 64);
                $table->string('module', 64);
                $table->string('status', 32);
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('success_rows')->default(0);
                $table->unsignedInteger('failed_rows')->default(0);
                $table->unsignedInteger('retried_rows')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->string('error_message', 2000)->nullable();
                $table->longText('meta')->nullable();
                $table->timestamps();
                $table->index(['direction', 'module', 'status']);
            });
        }

        if (! Schema::hasTable('sm_sync_dead_letters')) {
            Schema::create('sm_sync_dead_letters', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('batch_key', 64)->index();
                $table->string('direction', 64);
                $table->string('module', 64);
                $table->string('source_table', 64);
                $table->string('source_pk', 100);
                $table->longText('payload');
                $table->string('error_message', 2000);
                $table->timestamp('failed_at')->nullable();
                $table->timestamp('replayed_at')->nullable();
                $table->timestamps();
                $table->index(['direction', 'module', 'failed_at']);
            });
        }

        if (! Schema::hasTable('traffic_counting_staging')) {
            Schema::create('traffic_counting_staging', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('source_system', 32)->default('dcc');
                $table->unsignedBigInteger('source_id')->nullable();
                $table->date('tanggal')->nullable();
                $table->time('waktu')->nullable();
                $table->string('lokasi', 150)->nullable();
                $table->string('gerbangkota', 100)->nullable();
                $table->string('araharus', 100)->nullable();
                $table->string('jenisjalan', 100)->nullable();
                $table->unsignedInteger('r2')->default(0);
                $table->unsignedInteger('r4')->default(0);
                $table->unsignedInteger('r6')->default(0);
                $table->unsignedInteger('r6_plus')->default(0);
                $table->unsignedInteger('jumlah')->default(0);
                $table->string('volume_class', 8)->default('R2');
                $table->string('koordinat', 100)->nullable();
                $table->string('fotosit', 255)->nullable();
                $table->text('ketcb')->nullable();
                $table->string('status', 32)->default('ready');
                $table->string('batch_key', 64)->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
                $table->unique(['source_system', 'source_id']);
                $table->index(['status', 'id']);
            });
        }

        if (! Schema::hasTable('dwh_traffic_sampling')) {
            Schema::create('dwh_traffic_sampling', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('source_record_id')->unique();
                $table->date('event_date')->nullable();
                $table->time('event_time')->nullable();
                $table->string('lokasi', 150)->nullable();
                $table->string('sb_grp', 32)->nullable();
                $table->string('faktor', 100)->nullable();
                $table->string('penyebab', 100)->nullable();
                $table->string('volume_class', 8)->nullable();
                $table->unsignedInteger('volume_r2')->default(0);
                $table->unsignedInteger('volume_r4')->default(0);
                $table->unsignedInteger('volume_r6')->default(0);
                $table->unsignedInteger('volume_r6_plus')->default(0);
                $table->unsignedInteger('total_volume')->default(0);
                $table->string('batch_key', 64)->nullable();
                $table->timestamp('sync_timestamp')->nullable();
                $table->timestamps();
                $table->index(['event_date', 'sb_grp']);
            });
        }

        if (! Schema::hasTable('sm_cutover_controls')) {
            Schema::create('sm_cutover_controls', function (Blueprint $table) {
                $table->string('module', 64)->primary();
                $table->boolean('legacy_write_frozen')->default(false);
                $table->boolean('smlite_primary')->default(false);
                $table->timestamp('effective_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->string('notes', 255)->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (Schema::hasTable('trafficstuck') && ! Schema::hasColumn('trafficstuck', 'id_analytic_staging')) {
            Schema::table('trafficstuck', function (Blueprint $table) {
                $table->unsignedBigInteger('id_analytic_staging')->nullable()->after('id_trafficdalkot');
                $table->unique('id_analytic_staging');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('trafficstuck') && Schema::hasColumn('trafficstuck', 'id_analytic_staging')) {
            Schema::table('trafficstuck', function (Blueprint $table) {
                $table->dropUnique(['id_analytic_staging']);
                $table->dropColumn('id_analytic_staging');
            });
        }

        Schema::dropIfExists('sm_cutover_controls');
        Schema::dropIfExists('dwh_traffic_sampling');
        Schema::dropIfExists('traffic_counting_staging');
        Schema::dropIfExists('sm_sync_dead_letters');
        Schema::dropIfExists('sm_sync_batches');
        Schema::dropIfExists('sm_sync_watermarks');
    }
};
