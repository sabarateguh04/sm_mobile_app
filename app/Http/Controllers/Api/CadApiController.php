<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Usm;
use App\Services\FcmService;

class CadApiController extends Controller
{
    public function __construct()
    {
        $this->ensureTablesExist();
    }

    /**
     * Dynamically create CAD tables if they do not exist.
     */
    private function ensureTablesExist()
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
     * GET /api/cad/my-task
     */
    public function myTask(Request $request)
    {
        $request->validate([
            'userId' => 'required|integer'
        ]);

        $userId = $request->input('userId');

        $tasks = DB::table('tbl_cad_task' . ' as t')
            ->join('tbl_cad_assignment as a', 't.id', '=', 'a.task_id')
            ->where('a.user_id', $userId)
            ->select('t.*', 'a.status as assignment_status', 'a.keterangan as assignment_keterangan')
            ->orderBy('t.created_at', 'desc')
            ->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    /**
     * GET /api/cad/task-detail
     */
    public function taskDetail(Request $request)
    {
        $request->validate([
            'taskId' => 'required|integer'
        ]);

        $taskId = $request->input('taskId');

        $task = DB::table('tbl_cad_task')->where('id', $taskId)->first();

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task tidak ditemukan.'
            ], 404);
        }

        $assignments = DB::table('tbl_cad_assignment as a')
            ->leftJoin('usm as u', 'a.user_id', '=', 'u.id_usm')
            ->where('a.task_id', $taskId)
            ->select('a.user_id', 'a.status', 'u.nama', 'u.username')
            ->get();

        $photos = DB::table('tbl_cad_photo')
            ->where('task_id', $taskId)
            ->select('id', 'url')
            ->get();

        $taskData = (array) $task;
        $taskData['assignments'] = $assignments;
        $taskData['photos'] = $photos;

        return response()->json($taskData);
    }

    /**
     * POST /api/cad/terima-task
     */
    public function terimaTask(Request $request)
    {
        $request->validate([
            'taskId' => 'required|integer',
            'userId' => 'required|integer'
        ]);

        $taskId = $request->input('taskId');
        $userId = $request->input('userId');

        // Check if assignment exists
        $assignment = DB::table('tbl_cad_assignment')
            ->where('task_id', $taskId)
            ->where('user_id', $userId)
            ->first();

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Penugasan tidak ditemukan.'
            ], 404);
        }

        DB::transaction(function () use ($taskId, $userId) {
            DB::table('tbl_cad_assignment')
                ->where('task_id', $taskId)
                ->where('user_id', $userId)
                ->update([
                    'status' => 'DITERIMA',
                    'updated_at' => now()
                ]);

            $logExists = DB::table('tbl_cad_assignment_log')
                ->where('task_id', $taskId)
                ->where('user_id', $userId)
                ->where('status', 'DITERIMA')
                ->exists();

            if (!$logExists) {
                DB::table('tbl_cad_assignment_log')->insert([
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'status' => 'DITERIMA',
                    'created_at' => now()
                ]);
            }

            DB::table('tbl_cad_task')
                ->where('id', $taskId)
                ->update([
                    'status' => 'DITERIMA',
                    'updated_at' => now()
                ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Task berhasil diterima.'
        ]);
    }

    /**
     * POST /api/cad/menuju
     */
    public function menujuTkp(Request $request)
    {
        $request->validate([
            'taskId' => 'required|integer',
            'userId' => 'required|integer'
        ]);

        $taskId = $request->input('taskId');
        $userId = $request->input('userId');

        $assignment = DB::table('tbl_cad_assignment')
            ->where('task_id', $taskId)
            ->where('user_id', $userId)
            ->first();

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Penugasan tidak ditemukan.'
            ], 404);
        }

        DB::transaction(function () use ($taskId, $userId) {
            DB::table('tbl_cad_assignment')
                ->where('task_id', $taskId)
                ->where('user_id', $userId)
                ->update([
                    'status' => 'MENUJU',
                    'updated_at' => now()
                ]);

            $logExists = DB::table('tbl_cad_assignment_log')
                ->where('task_id', $taskId)
                ->where('user_id', $userId)
                ->where('status', 'MENUJU')
                ->exists();

            if (!$logExists) {
                DB::table('tbl_cad_assignment_log')->insert([
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'status' => 'MENUJU',
                    'created_at' => now()
                ]);
            }

            DB::table('tbl_cad_task')
                ->where('id', $taskId)
                ->update([
                    'status' => 'MENUJU',
                    'updated_at' => now()
                ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Petugas sedang menuju TKP.'
        ]);
    }

    /**
     * POST /api/cad/tiba
     */
    public function tibaTkp(Request $request)
    {
        $request->validate([
            'taskId' => 'required|integer',
            'userId' => 'required|integer'
        ]);

        $taskId = $request->input('taskId');
        $userId = $request->input('userId');

        $assignment = DB::table('tbl_cad_assignment')
            ->where('task_id', $taskId)
            ->where('user_id', $userId)
            ->first();

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Penugasan tidak ditemukan.'
            ], 404);
        }

        DB::transaction(function () use ($taskId, $userId) {
            DB::table('tbl_cad_assignment')
                ->where('task_id', $taskId)
                ->where('user_id', $userId)
                ->update([
                    'status' => 'TIBA',
                    'updated_at' => now()
                ]);

            $logExists = DB::table('tbl_cad_assignment_log')
                ->where('task_id', $taskId)
                ->where('user_id', $userId)
                ->where('status', 'TIBA')
                ->exists();

            if (!$logExists) {
                DB::table('tbl_cad_assignment_log')->insert([
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'status' => 'TIBA',
                    'created_at' => now()
                ]);
            }

            DB::table('tbl_cad_task')
                ->where('id', $taskId)
                ->update([
                    'status' => 'TIBA',
                    'updated_at' => now()
                ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Petugas telah tiba di TKP.'
        ]);
    }

    /**
     * POST /api/cad/selesai
     */
    public function selesaiTask(Request $request)
    {
        $request->validate([
            'taskId' => 'required|integer',
            'userId' => 'required|integer',
            'keterangan' => 'nullable|string',
            'photos' => 'nullable'
        ]);

        $taskId = $request->input('taskId');
        $userId = $request->input('userId');
        $keterangan = $request->input('keterangan', '');
        $photosInput = $request->input('photos', '[]');

        $assignment = DB::table('tbl_cad_assignment')
            ->where('task_id', $taskId)
            ->where('user_id', $userId)
            ->first();

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Penugasan tidak ditemukan.'
            ], 404);
        }

        // Parse photos
        $photos = [];
        if (is_string($photosInput)) {
            $decoded = json_decode($photosInput, true);
            if (is_array($decoded)) {
                $photos = $decoded;
            }
        } elseif (is_array($photosInput)) {
            $photos = $photosInput;
        }

        DB::transaction(function () use ($taskId, $userId, $keterangan, $photos) {
            DB::table('tbl_cad_assignment')
                ->where('task_id', $taskId)
                ->where('user_id', $userId)
                ->update([
                    'status' => 'SELESAI',
                    'keterangan' => $keterangan,
                    'waktu_selesai' => now(),
                    'updated_at' => now()
                ]);

            $logExists = DB::table('tbl_cad_assignment_log')
                ->where('task_id', $taskId)
                ->where('user_id', $userId)
                ->where('status', 'SELESAI')
                ->exists();

            if (!$logExists) {
                DB::table('tbl_cad_assignment_log')->insert([
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'status' => 'SELESAI',
                    'created_at' => now()
                ]);
            }

            $hasPhotos = count($photos) > 0;

            DB::table('tbl_cad_task')
                ->where('id', $taskId)
                ->update([
                    'status' => 'SELESAI',
                    'has_bukti' => $hasPhotos ? 1 : 0,
                    'updated_at' => now()
                ]);

            foreach ($photos as $url) {
                DB::table('tbl_cad_photo')->insert([
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'url' => $url,
                    'created_at' => now()
                ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan selesai ditangani.'
        ]);
    }

    /**
     * POST /api/cad/upload-photo
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Simpan langsung ke folder public fisik (bukan symlink) untuk menghindari 403 di aaPanel/Nginx
            $file->move(public_path('storage/cad_photos'), $filename);
            
            $url = asset('storage/cad_photos/' . $filename);
            
            return response()->json([
                'status' => 'success',
                'url' => $url
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal upload foto.'
        ], 400);
    }

    /**
     * GET /api/cad/stats
     */
    public function stats(Request $request)
    {
        $request->validate([
            'userId' => 'required|integer'
        ]);

        $userId = $request->input('userId');

        $totalTasks = DB::table('tbl_cad_assignment')->where('user_id', $userId)->count();
        $completedTasks = DB::table('tbl_cad_assignment')->where('user_id', $userId)->where('status', 'SELESAI')->count();
        $onProcessTasks = DB::table('tbl_cad_assignment')
                            ->where('user_id', $userId)
                            ->whereIn('status', ['DITERIMA', 'MENUJU', 'TIBA'])
                            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total' => $totalTasks,
                'completed' => $completedTasks,
                'on_process' => $onProcessTasks
            ]
        ]);
    }

    /**
     * GET /api/backoffice/dashboard
     */
    public function boDashboard()
    {
        $totalTasks = DB::table('tbl_cad_task')->count();
        $newTasks = DB::table('tbl_cad_task')->where('status', 'NEW')->count();
        $onProcessTasks = DB::table('tbl_cad_task')->whereIn('status', ['DITERIMA', 'MENUJU', 'TIBA'])->count();
        $completedTasks = DB::table('tbl_cad_task')->where('status', 'SELESAI')->count();
        $closedTasks = DB::table('tbl_cad_task')->where('status', 'CLOSED')->count();

        // Count officer statuses from petugas_tracking
        $onlineOfficers = DB::table('petugas_tracking')->where('status', 'ONLINE')->count();
        $onDutyOfficers = DB::table('petugas_tracking')->where('status', 'BERTUGAS')->count();
        $offlineOfficers = DB::table('petugas_tracking')->where('status', 'OFFLINE')->count();

        return response()->json([
            'tasks' => [
                'total' => $totalTasks,
                'new' => $newTasks,
                'on_process' => $onProcessTasks,
                'completed' => $completedTasks,
                'closed' => $closedTasks,
            ],
            'officers' => [
                'online' => $onlineOfficers,
                'on_duty' => $onDutyOfficers,
                'offline' => $offlineOfficers,
                'total_tracked' => $onlineOfficers + $onDutyOfficers + $offlineOfficers
            ]
        ]);
    }

    /**
     * GET /api/backoffice/list-petugas
     */
    public function boListPetugas(Request $request)
    {
        $status = $request->input('status');

        $query = DB::table('usm as u')
            ->leftJoin('petugas_tracking as t', 'u.id_usm', '=', 't.user_id')
            ->select('u.id_usm as userId', 'u.username', 'u.nama', 't.lat', 't.lng', 't.status', 't.last_updated');

        if ($status) {
            $query->where('t.status', strtoupper($status));
        }

        $officers = $query->get();

        return response()->json($officers);
    }

    /**
     * GET /api/backoffice/all-task
     */
    public function boAllTask(Request $request)
    {
        $status = $request->input('status');
        $priority = $request->input('priority');
        $limit = $request->input('limit', 20);

        $query = DB::table('tbl_cad_task');

        if ($status) {
            $query->where('status', strtoupper($status));
        }

        if ($priority) {
            $query->where('priority', strtoupper($priority));
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate($limit);

        return response()->json($tasks);
    }

    /**
     * POST /api/backoffice/create-task
     */
    public function boCreateTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'address' => 'required|string|max:300',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'priority' => 'nullable|string|in:HIGH,MEDIUM,LOW,high,medium,low',
            'pelapor' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
            'created_by' => 'nullable|integer',
            'assign_to' => 'required'
        ]);

        $title = $request->input('title');
        $address = $request->input('address');
        $lat = $request->input('latitude', 0);
        $lng = $request->input('longitude', 0);
        $priority = strtoupper($request->input('priority', 'MEDIUM'));
        $pelapor = $request->input('pelapor', 'Warga');
        $keterangan = $request->input('keterangan');
        $createdBy = $request->input('created_by');
        $assignToInput = $request->input('assign_to');

        // Parse assign_to
        $assignTo = [];
        if (is_string($assignToInput)) {
            $decoded = json_decode($assignToInput, true);
            if (is_array($decoded)) {
                $assignTo = $decoded;
            }
        } elseif (is_array($assignToInput)) {
            $assignTo = $assignToInput;
        }

        $taskId = DB::transaction(function () use ($title, $address, $lat, $lng, $priority, $pelapor, $keterangan, $createdBy, $assignTo) {
            $taskId = DB::table('tbl_cad_task')->insertGetId([
                'title' => $title,
                'address' => $address,
                'latitude' => $lat,
                'longitude' => $lng,
                'priority' => $priority,
                'status' => 'NEW',
                'pelapor' => $pelapor,
                'keterangan' => $keterangan,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            foreach ($assignTo as $userId) {
                DB::table('tbl_cad_assignment')->insert([
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'status' => 'ASSIGNED',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('tbl_cad_assignment_log')->insert([
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'status' => 'ASSIGNED',
                    'created_at' => now()
                ]);
            }

            return $taskId;
        });

        // Kirim Notifikasi FCM
        if (!empty($assignTo)) {
            \App\Services\FcmService::send(
                $assignTo,
                'Tugas Baru CAD',
                "Anda mendapat tugas baru: {$title}",
                [
                    'type' => 'new_task',
                    'taskId' => $taskId,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Task berhasil dibuat dan ditugaskan.',
            'taskId' => $taskId
        ]);
    }

    /**
     * POST /api/backoffice/assign-task
     */
    public function boAssignTask(Request $request)
    {
        $request->validate([
            'taskId' => 'required|integer',
            'user_ids' => 'required'
        ]);

        $taskId = $request->input('taskId');
        $userIdsInput = $request->input('user_ids');

        // Parse user_ids
        $userIds = [];
        if (is_string($userIdsInput)) {
            $decoded = json_decode($userIdsInput, true);
            if (is_array($decoded)) {
                $userIds = $decoded;
            }
        } elseif (is_array($userIdsInput)) {
            $userIds = $userIdsInput;
        }

        $task = DB::table('tbl_cad_task')->where('id', $taskId)->first();
        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task tidak ditemukan.'
            ], 404);
        }

        DB::transaction(function () use ($taskId, $userIds) {
            foreach ($userIds as $userId) {
                // Skip if already assigned
                $exists = DB::table('tbl_cad_assignment')
                    ->where('task_id', $taskId)
                    ->where('user_id', $userId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('tbl_cad_assignment')->insert([
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'status' => 'ASSIGNED',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('tbl_cad_assignment_log')->insert([
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'status' => 'ASSIGNED',
                    'created_at' => now()
                ]);
            }
        });

        // Kirim Notifikasi FCM
        if (!empty($userIds)) {
            \App\Services\FcmService::send(
                $userIds,
                'Tugas Tambahan CAD',
                "Anda ditugaskan pada tugas tambahan: {$task->title}",
                [
                    'type' => 'new_task',
                    'taskId' => $taskId,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Petugas tambahan berhasil ditugaskan.'
        ]);
    }

    /**
     * POST /api/backoffice/close-task
     */
    public function boCloseTask(Request $request)
    {
        $request->validate([
            'taskId' => 'required|integer',
            'closed_by' => 'required|integer',
            'catatan' => 'nullable|string',
            'force' => 'nullable'
        ]);

        $taskId = $request->input('taskId');
        $closedBy = $request->input('closed_by');
        $catatan = $request->input('catatan', 'Task telah diselesaikan');
        $force = $request->input('force');

        $isForce = ($force === 'true' || $force === true || $force === 1 || $force === '1');

        $task = DB::table('tbl_cad_task')->where('id', $taskId)->first();
        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task tidak ditemukan.'
            ], 404);
        }

        if ($task->status !== 'SELESAI' && !$isForce) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task belum diselesaikan oleh petugas di lapangan. Gunakan force=true untuk memaksa tutup.'
            ], 400);
        }

        DB::transaction(function () use ($taskId, $closedBy, $catatan) {
            DB::table('tbl_cad_task')
                ->where('id', $taskId)
                ->update([
                    'status' => 'CLOSED',
                    'closed_by' => $closedBy,
                    'closed_at' => now(),
                    'updated_at' => now()
                ]);

            DB::table('tbl_cad_close_log')->insert([
                'task_id' => $taskId,
                'closed_by' => $closedBy,
                'catatan' => $catatan,
                'created_at' => now()
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Task berhasil ditutup.'
        ]);
    }

    /**
     * GET /health
     */
    public function health()
    {
        return response()->json([
            'status' => 'UP',
            'timestamp' => now()->toIso8601String()
        ]);
    }
}
