<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Helpers\HierarchicalHelper;
use App\Helpers\MenuHelper;
use App\Models\Usm;
use Carbon\Carbon;
use App\Services\FcmService;

class OpsdalCadController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Pastikan user punya akses matriks menu & tabel CAD tersedia.
     */
    private function authorizeCadAccess(Request $request): void
    {
        abort_unless(
            MenuHelper::matrixAllowsPathForUser($request->user(), 'opsdal/cad'),
            403,
            'Anda tidak memiliki akses ke modul Kodal CAD Dispatch.'
        );
    }

    /**
     * @return array{ready: bool, message: string|null}
     */
    private function cadSchemaStatus(): array
    {
        $required = [
            'tbl_cad_task',
            'tbl_cad_assignment',
            'tbl_cad_assignment_log',
            'tbl_cad_close_log',
        ];

        foreach ($required as $table) {
            if (! Schema::hasTable($table)) {
                return [
                    'ready' => false,
                    'message' => "Tabel `{$table}` belum ada. Jalankan `php artisan migrate` pada server.",
                ];
            }
        }

        return ['ready' => true, 'message' => null];
    }

    /**
     * Display a listing of the CAD tasks.
     */
    private function getPageTitle()
    {
        $user = auth()->user();
        $roleName = strtolower($user ? ($user->roleLabel ?? '') : '');
        if (str_contains($roleName, 'operator') || str_contains($roleName, 'opr')) {
            return 'Kodal CAD Dispatch';
        }
        return 'Report CAD Dispatch';
    }

    public function index(Request $request)
    {
        $this->authorizeCadAccess($request);

        $schema = $this->cadSchemaStatus();
        if (! $schema['ready']) {
            return view('opsdal.cad.index', [
                'pageTitle' => $this->getPageTitle(),
                'tasks' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'stats' => ['total' => 0, 'new' => 0, 'process' => 0, 'completed' => 0],
                'onlineOfficers' => collect(),
                'schemaWarning' => $schema['message'],
            ]);
        }
        $status = $request->input('status');
        $priority = $request->input('priority');
        $search = $request->input('search');

        $query = DB::table('tbl_cad_task as t')
            ->leftJoin('usm as creator', 't.created_by', '=', 'creator.id_usm')
            ->select('t.*', 'creator.nama as creator_name');

        // Apply territory scope
        $user = Auth::user();
        if ($user && !HierarchicalHelper::viewerBypassesTerritoryScope()) {
            $userDaerah = trim((string) ($user->daerah ?? ''));
            $userWilayah = trim((string) ($user->wilayah ?? ''));

            if (!empty($userDaerah)) {
                $query->where(function ($q) use ($userDaerah, $userWilayah) {
                    $q->where('creator.daerah', 'LIKE', '%' . $userDaerah . '%');
                    if (!empty($userWilayah)) {
                        $q->where('creator.wilayah', 'LIKE', '%' . $userWilayah . '%');
                    }
                    
                    $q->orWhereExists(function ($sub) use ($userDaerah, $userWilayah) {
                        $sub->select(DB::raw(1))
                            ->from('tbl_cad_assignment as a')
                            ->join('usm as officer', 'a.user_id', '=', 'officer.id_usm')
                            ->whereColumn('a.task_id', 't.id')
                            ->where('officer.daerah', 'LIKE', '%' . $userDaerah . '%');
                        if (!empty($userWilayah)) {
                            $sub->where('officer.wilayah', 'LIKE', '%' . $userWilayah . '%');
                        }
                    });
                });
            }
        }

        // Apply filters
        if (!empty($status)) {
            $query->where('t.status', strtoupper($status));
        }
        if (!empty($priority)) {
            $query->where('t.priority', strtoupper($priority));
        }
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('t.title', 'like', "%{$search}%")
                  ->orWhere('t.address', 'like', "%{$search}%")
                  ->orWhere('t.pelapor', 'like', "%{$search}%");
            });
        }

        $tasks = $query->orderBy('t.created_at', 'desc')->paginate(15);

        // Fetch assigned officers for each task
        foreach ($tasks as $task) {
            $task->assignments = DB::table('tbl_cad_assignment as a')
                ->join('usm as u', 'a.user_id', '=', 'u.id_usm')
                ->where('a.task_id', $task->id)
                ->select('u.id_usm', 'u.nama', 'u.username', 'u.fotoprof', 'a.status as assignment_status')
                ->get();
        }

        // Calculate statistics based on filtered query
        $statsQuery = DB::table('tbl_cad_task as t')
            ->leftJoin('usm as creator', 't.created_by', '=', 'creator.id_usm');
        
        if ($user && !HierarchicalHelper::viewerBypassesTerritoryScope()) {
            $userDaerah = trim((string) ($user->daerah ?? ''));
            $userWilayah = trim((string) ($user->wilayah ?? ''));
            if (!empty($userDaerah)) {
                $statsQuery->where(function ($q) use ($userDaerah, $userWilayah) {
                    $q->where('creator.daerah', 'LIKE', '%' . $userDaerah . '%');
                    if (!empty($userWilayah)) {
                        $q->where('creator.wilayah', 'LIKE', '%' . $userWilayah . '%');
                    }
                    $q->orWhereExists(function ($sub) use ($userDaerah, $userWilayah) {
                        $sub->select(DB::raw(1))
                            ->from('tbl_cad_assignment as a')
                            ->join('usm as officer', 'a.user_id', '=', 'officer.id_usm')
                            ->whereColumn('a.task_id', 't.id')
                            ->where('officer.daerah', 'LIKE', '%' . $userDaerah . '%');
                        if (!empty($userWilayah)) {
                            $sub->where('officer.wilayah', 'LIKE', '%' . $userWilayah . '%');
                        }
                    });
                });
            }
        }

        $statsCounts = (clone $statsQuery)
            ->select(DB::raw('status, COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $cadStats = [
            'total' => $statsCounts->sum(),
            'new' => $statsCounts['NEW'] ?? 0,
            'process' => ($statsCounts['DITERIMA'] ?? 0) + ($statsCounts['MENUJU'] ?? 0) + ($statsCounts['TIBA'] ?? 0),
            'completed' => ($statsCounts['SELESAI'] ?? 0) + ($statsCounts['CLOSED'] ?? 0),
        ];

        // Fetch online/active officers to populate assignment options
        $officersQuery = DB::table('usm as u')
            ->join('petugas_tracking as t', 'u.id_usm', '=', 't.user_id')
            ->whereIn('t.status', ['ONLINE', 'BERTUGAS'])
            ->select('u.id_usm', 'u.nama', 'u.username', 't.status as tracking_status', 'u.daerah', 'u.wilayah');

        if ($user && !HierarchicalHelper::viewerBypassesTerritoryScope()) {
            $userDaerah = trim((string) ($user->daerah ?? ''));
            $userWilayah = trim((string) ($user->wilayah ?? ''));
            if (!empty($userDaerah)) {
                $officersQuery->where('u.daerah', 'LIKE', '%' . $userDaerah . '%');
                if (!empty($userWilayah)) {
                    $officersQuery->where('u.wilayah', 'LIKE', '%' . $userWilayah . '%');
                }
            }
        }

        $onlineOfficers = $officersQuery->get();

        // ---- Reporting SM-Dispatch Table (Aktivitas Petugas) ----
        $isNasional = empty($user->daerah) || HierarchicalHelper::viewerBypassesTerritoryScope();
        $isProvinsi = !empty($user->daerah) && empty($user->wilayah);
        $officerReports = null;

        if ($isNasional || $isProvinsi) {
            $reportDate = $request->input('report_date', now()->toDateString());
            $filterPolda = $request->input('polda');
            $filterPolres = $request->input('polres');

            $reportQuery = DB::table('usm as u')
                ->select(
                    'u.nama',
                    'u.daerah as polda',
                    'u.wilayah as polres',
                    DB::raw("(SELECT MIN(ctdtime) FROM tbl_location_log_mobile WHERE user_id = u.id_usm AND ctddate = ? AND status IN ('ONLINE', 'BERTUGAS')) as jam_enable"),
                    DB::raw("(SELECT MAX(ctdtime) FROM tbl_location_log_mobile WHERE user_id = u.id_usm AND ctddate = ? AND status = 'OFFLINE') as jam_disable")
                )
                ->whereNotNull('u.daerah')
                ->where('u.daerah', '!=', '');

            $reportQuery->addBinding($reportDate, 'select');
            $reportQuery->addBinding($reportDate, 'select');

            // Apply territory restrictions based on user level
            if ($user && !HierarchicalHelper::viewerBypassesTerritoryScope()) {
                $userDaerah = trim((string) ($user->daerah ?? ''));
                $userWilayah = trim((string) ($user->wilayah ?? ''));
                if (!empty($userDaerah)) {
                    $reportQuery->where('u.daerah', 'LIKE', '%' . $userDaerah . '%');
                    if (!empty($userWilayah)) {
                        $reportQuery->where('u.wilayah', 'LIKE', '%' . $userWilayah . '%');
                    }
                }
            }

            // Apply requested filters
            if ($isNasional && !empty($filterPolda)) {
                $reportQuery->where('u.daerah', 'LIKE', '%' . $filterPolda . '%');
            }
            if (($isNasional || $isProvinsi) && !empty($filterPolres)) {
                $reportQuery->where('u.wilayah', 'LIKE', '%' . $filterPolres . '%');
            }

            $officerReports = $reportQuery->orderBy('u.daerah')->orderBy('u.wilayah')->orderBy('u.nama')->paginate(20, ['*'], 'report_page');
        }

        return view('opsdal.cad.index', [
            'pageTitle' => $this->getPageTitle(),
            'tasks' => $tasks,
            'stats' => $cadStats,
            'onlineOfficers' => $onlineOfficers,
            'officerReports' => $officerReports,
        ]);
    }

    /**
     * Show detail of a CAD task.
     */
    public function show(Request $request, $id)
    {
        $this->authorizeCadAccess($request);

        $schema = $this->cadSchemaStatus();
        if (! $schema['ready']) {
            abort(503, $schema['message']);
        }

        $task = DB::table('tbl_cad_task as t')
            ->leftJoin('usm as creator', 't.created_by', '=', 'creator.id_usm')
            ->where('t.id', $id)
            ->select('t.*', 'creator.nama as creator_name')
            ->first();

        if (!$task) {
            abort(404, 'Tugas tidak ditemukan.');
        }

        // Get assignments
        $assignments = DB::table('tbl_cad_assignment as a')
            ->join('usm as u', 'a.user_id', '=', 'u.id_usm')
            ->leftJoin('petugas_tracking as tr', 'u.id_usm', '=', 'tr.user_id')
            ->where('a.task_id', $id)
            ->select('u.id_usm', 'u.nama', 'u.username', 'u.fotoprof', 'u.notelp', 'a.status as assignment_status', 'a.keterangan as assignment_note', 'tr.lat as tracking_lat', 'tr.lng as tracking_lng', 'tr.status as tracking_status')
            ->get();

        // Get logs
        $logs = DB::table('tbl_cad_assignment_log as l')
            ->join('usm as u', 'l.user_id', '=', 'u.id_usm')
            ->where('l.task_id', $id)
            ->select('l.*', 'u.nama as user_name')
            ->orderBy('l.created_at', 'asc')
            ->get();

        // Get photos
        $photos = DB::table('tbl_cad_photo')
            ->where('task_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Get close log if closed
        $closeLog = null;
        if ($task->status === 'CLOSED') {
            $closeLog = DB::table('tbl_cad_close_log as c')
                ->join('usm as u', 'c.closed_by', '=', 'u.id_usm')
                ->where('c.task_id', $id)
                ->select('c.*', 'u.nama as closed_by_name')
                ->first();
        }

        // Fetch online/active officers in case dispatcher wants to assign more
        $user = Auth::user();
        $officersQuery = DB::table('usm as u')
            ->join('petugas_tracking as t', 'u.id_usm', '=', 't.user_id')
            ->whereIn('t.status', ['ONLINE', 'BERTUGAS'])
            ->select('u.id_usm', 'u.nama', 'u.username', 't.status as tracking_status');

        if ($user && !HierarchicalHelper::viewerBypassesTerritoryScope()) {
            $userDaerah = trim((string) ($user->daerah ?? ''));
            $userWilayah = trim((string) ($user->wilayah ?? ''));
            if (!empty($userDaerah)) {
                $officersQuery->where('u.daerah', 'LIKE', '%' . $userDaerah . '%');
                if (!empty($userWilayah)) {
                    $officersQuery->where('u.wilayah', 'LIKE', '%' . $userWilayah . '%');
                }
            }
        }
        $onlineOfficers = $officersQuery->get();

        return view('opsdal.cad.show', [
            'pageTitle' => 'Detail ' . $this->getPageTitle(),
            'task' => $task,
            'assignments' => $assignments,
            'logs' => $logs,
            'photos' => $photos,
            'closeLog' => $closeLog,
            'onlineOfficers' => $onlineOfficers,
        ]);
    }

    /**
     * Create a new CAD task from the backoffice.
     */
    public function store(Request $request)
    {
        $this->authorizeCadAccess($request);

        $schema = $this->cadSchemaStatus();
        if (! $schema['ready']) {
            return redirect()->back()->with('error', $schema['message']);
        }

        $request->validate([
            'title' => 'required|string|max:200',
            'address' => 'required|string|max:300',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'priority' => 'required|string|in:HIGH,MEDIUM,LOW',
            'pelapor' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
            'assign_to' => 'required|array',
            'assign_to.*' => 'integer'
        ]);

        DB::beginTransaction();
        try {
            $taskId = DB::table('tbl_cad_task')->insertGetId([
                'title' => $request->title,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'priority' => $request->priority,
                'status' => 'NEW',
                'pelapor' => $request->pelapor ?? 'Pusat Komando',
                'keterangan' => $request->keterangan,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->assign_to as $userId) {
                DB::table('tbl_cad_assignment')->insert([
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'status' => 'ASSIGNED',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('tbl_cad_assignment_log')->insert([
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'status' => 'ASSIGNED',
                    'created_at' => now(),
                ]);

                // Update officer status to BERTUGAS in tracking
                DB::table('petugas_tracking')
                    ->where('user_id', $userId)
                    ->update(['status' => 'BERTUGAS', 'updated_at' => now()]);
            }

            DB::commit();

            // Kirim Notifikasi FCM
            if (!empty($request->assign_to)) {
                \App\Services\FcmService::send(
                    $request->assign_to,
                    'Tugas Baru CAD',
                    "Anda mendapat tugas baru: {$request->title}",
                    [
                        'type' => 'cad_task',
                        'taskId' => $taskId,
                    ]
                );
            }

            return redirect()->route('opsdal.cad.index')->with('success', 'Tugas CAD berhasil dibuat dan ditugaskan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal membuat tugas: ' . $e->getMessage());
        }
    }

    /**
     * Test push notification FCM.
     */
    public function testFcm(Request $request)
    {
        $this->authorizeCadAccess($request);

        $request->validate([
            'test_assign_to' => 'required|array',
            'test_assign_to.*' => 'integer',
            'test_message' => 'required|string|max:200'
        ]);

        $userIds = $request->test_assign_to;
        $message = $request->test_message;

        try {
            $success = \App\Services\FcmService::send(
                $userIds,
                'Uji Coba Notifikasi',
                $message,
                [
                    'type' => 'test_notif',
                    'timestamp' => now()->timestamp,
                ]
            );

            if ($success) {
                return redirect()->route('opsdal.cad.index')->with('success', 'Uji coba notifikasi FCM berhasil dikirim ke perangkat tujuan.');
            } else {
                return redirect()->back()->with('error', 'Gagal mengirim notifikasi uji coba. Cek log server.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Assign more officers to an existing task.
     */
    public function assign(Request $request, $id)
    {
        $this->authorizeCadAccess($request);

        $schema = $this->cadSchemaStatus();
        if (! $schema['ready']) {
            return redirect()->back()->with('error', $schema['message']);
        }

        $request->validate([
            'assign_to' => 'required|array',
            'assign_to.*' => 'integer'
        ]);

        DB::beginTransaction();
        try {
            $task = DB::table('tbl_cad_task')->where('id', $id)->first();
            if (!$task) {
                return redirect()->back()->with('error', 'Tugas tidak ditemukan.');
            }

            foreach ($request->assign_to as $userId) {
                // Check if already assigned
                $exists = DB::table('tbl_cad_assignment')
                    ->where('task_id', $id)
                    ->where('user_id', $userId)
                    ->exists();

                if (!$exists) {
                    DB::table('tbl_cad_assignment')->insert([
                        'task_id' => $id,
                        'user_id' => $userId,
                        'status' => 'ASSIGNED',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('tbl_cad_assignment_log')->insert([
                        'task_id' => $id,
                        'user_id' => $userId,
                        'status' => 'ASSIGNED',
                        'created_at' => now(),
                    ]);

                    // Update officer status to BERTUGAS in tracking
                    DB::table('petugas_tracking')
                        ->where('user_id', $userId)
                        ->update(['status' => 'BERTUGAS', 'updated_at' => now()]);
                }
            }

            // Update task status to at least DITERIMA if it was NEW
            if ($task->status === 'NEW') {
                DB::table('tbl_cad_task')->where('id', $id)->update([
                    'status' => 'DITERIMA',
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            // Kirim Notifikasi FCM
            if (!empty($request->assign_to)) {
                \App\Services\FcmService::send(
                    $request->assign_to,
                    'Tugas Tambahan CAD',
                    "Anda ditugaskan pada tugas tambahan: {$task->title}",
                    [
                        'type' => 'cad_task',
                        'taskId' => $id,
                    ]
                );
            }

            return redirect()->route('opsdal.cad.show', $id)->with('success', 'Petugas berhasil ditambahkan ke tugas.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menugaskan petugas: ' . $e->getMessage());
        }
    }

    /**
     * Close a CAD task.
     */
    public function close(Request $request, $id)
    {
        $this->authorizeCadAccess($request);

        $schema = $this->cadSchemaStatus();
        if (! $schema['ready']) {
            return redirect()->back()->with('error', $schema['message']);
        }

        $request->validate([
            'catatan' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            DB::table('tbl_cad_task')->where('id', $id)->update([
                'status' => 'CLOSED',
                'closed_by' => Auth::id(),
                'closed_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('tbl_cad_close_log')->insert([
                'task_id' => $id,
                'closed_by' => Auth::id(),
                'catatan' => $request->catatan,
                'created_at' => now(),
            ]);

            // Release all assigned officers back to ONLINE
            $assignments = DB::table('tbl_cad_assignment')->where('task_id', $id)->get();
            foreach ($assignments as $assignment) {
                DB::table('tbl_cad_assignment')->where('id', $assignment->id)->update([
                    'status' => 'CLOSED',
                    'waktu_selesai' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('petugas_tracking')
                    ->where('user_id', $assignment->user_id)
                    ->update(['status' => 'ONLINE', 'updated_at' => now()]);
            }

            DB::commit();
            return redirect()->route('opsdal.cad.show', $id)->with('success', 'Tugas CAD berhasil ditutup.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menutup tugas: ' . $e->getMessage());
        }
    }
}
