<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Usm;
use App\Services\UsmPasswordVerifier;

class PetugasTrackingController extends Controller
{
    public function __construct()
    {
        $this->ensureTableExists();
    }

    /**
     * Pastikan tabel petugas_tracking ada di database.
     */
    private function ensureTableExists()
    {
        if (!Schema::hasTable('petugas_tracking')) {
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
     * POST /api/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = trim((string) $request->username);
        $password = (string) $request->password;

        $user = Usm::where('username', $username)->first()
            ?? Usm::where('email', $username)->first();

        if (!$user || !UsmPasswordVerifier::verify($user, $password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username atau password salah.'
            ], 401);
        }

        // Cek status akun
        $status = strtolower(trim((string) ($user->account_status ?? '')));
        if ($status === 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda sedang dalam proses verifikasi (pending).'
            ], 403);
        }

        $blocked = in_array($status, ['blocked', 'inactive', 'suspend', 'suspended', 'banned'], true);
        if ($blocked) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun dinonaktifkan. Hubungi administrator.'
            ], 403);
        }

        return response()->json([
            'userId' => (int) $user->id_usm,
            'username' => $user->username,
            'nama' => $user->nama,
        ]);
    }

    /**
     * GET /api/profile
     */
    public function profile(Request $request)
    {
        $request->validate([
            'userId' => 'required|integer',
        ]);

        $userId = $request->input('userId');
        $user = Usm::where('id_usm', $userId)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Petugas tidak ditemukan.'
            ], 404);
        }

        $tracking = DB::table('petugas_tracking')->where('user_id', $userId)->first();

        $url_image = null;
        if ($user->fotoprof) {
            if (filter_var($user->fotoprof, FILTER_VALIDATE_URL)) {
                $url_image = $user->fotoprof;
            } else {
                $url_image = asset('storage/' . $user->fotoprof);
            }
        } else {
            $url_image = asset('images/default-profile.png');
        }

        return response()->json([
            'userId' => (int) $user->id_usm,
            'nama' => $user->nama,
            'url_image' => $url_image,
            'lat' => $tracking ? (float) $tracking->lat : null,
            'lng' => $tracking ? (float) $tracking->lng : null,
            'status' => $tracking ? strtoupper($tracking->status) : 'OFFLINE',
            'last_updated' => $tracking && $tracking->last_updated ? $tracking->last_updated : null,
        ]);
    }

    /**
     * POST /api/update-location
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'userId' => 'required|integer',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'ctddate' => 'required|date_format:Y-m-d',
            'ctdtime' => 'required|date_format:H:i:s',
            'status' => 'required|string|in:ONLINE,BERTUGAS,OFFLINE,online,bertugas,offline',
        ]);

        $userId = $request->input('userId');
        
        // Pastikan user ada
        if (!Usm::where('id_usm', $userId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Petugas tidak ditemukan.'
            ], 404);
        }

        $status = strtoupper($request->input('status'));
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $ctddate = $request->input('ctddate');
        $ctdtime = $request->input('ctdtime');

        DB::table('petugas_tracking')->updateOrInsert(
            ['user_id' => $userId],
            [
                'lat' => $lat,
                'lng' => $lng,
                'status' => $status,
                'ctddate' => $ctddate,
                'ctdtime' => $ctdtime,
                'last_updated' => $ctddate . ' ' . $ctdtime,
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'ket' => 'data berhasil di update'
        ]);
    }

    /**
     * POST /api/update-profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'userId' => 'required|integer',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'status' => 'required|string|in:ONLINE,BERTUGAS,OFFLINE,online,bertugas,offline',
            'last_updated' => 'required|date_format:Y-m-d H:i:s',
        ]);

        $userId = $request->input('userId');
        
        // Pastikan user ada
        if (!Usm::where('id_usm', $userId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Petugas tidak ditemukan.'
            ], 404);
        }

        $status = strtoupper($request->input('status'));
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $last_updated = $request->input('last_updated');
        
        try {
            $parsed = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $last_updated);
            $ctddate = $parsed->toDateString();
            $ctdtime = $parsed->toTimeString();
        } catch (\Exception $e) {
            $ctddate = now()->toDateString();
            $ctdtime = now()->toTimeString();
        }

        DB::table('petugas_tracking')->updateOrInsert(
            ['user_id' => $userId],
            [
                'lat' => $lat,
                'lng' => $lng,
                'status' => $status,
                'ctddate' => $ctddate,
                'ctdtime' => $ctdtime,
                'last_updated' => $last_updated,
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'ket' => 'data berhasil di update'
        ]);
    }
}
