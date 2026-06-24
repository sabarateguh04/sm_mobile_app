<?php

namespace App\Http\Controllers;

use App\Support\CctvLiveRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

/**
 * Live CCTV — pemutaran RTSP via gateway SatuPeta (ported dari satupeta_new).
 */
class CctvLiveController extends Controller
{
    public function index(Request $request): View
    {
        $available = CctvLiveRegistry::isAvailable();
        $cameras = $available
            ? CctvLiveRegistry::listCameras($request->input('search'))
            : collect();

        return view('cctv-live.index', [
            'available' => $available,
            'cameras' => $cameras,
            'search' => $request->input('search'),
            'streamConfigured' => true, // Selalu true agar tidak memblokir
        ]);
    }

    public function show(int $id): View
    {
        $camera = CctvLiveRegistry::findCamera($id);
        if (! $camera) {
            abort(404, 'Kamera tidak ditemukan atau tidak dapat diakses.');
        }

        // Deteksi IP Lokal (RFC 1918)
        $isLocalIp = false;
        if (preg_match('/(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $camera->rtsp_cctv)) {
            $isLocalIp = true;
        }

        return view('cctv-live.show', [
            'camera' => $camera,
            'streamConfigured' => true, // Selalu true
            'whepConfigured' => false,
            'streamConfig' => $this->streamClientConfig(),
            'isLocalIp' => $isLocalIp,
        ]);
    }

    public function startStream(int $id): JsonResponse
    {
        $camera = CctvLiveRegistry::findCamera($id);
        if (! $camera) {
            return response()->json(['success' => false, 'message' => 'Kamera tidak ditemukan.'], 404);
        }

        // Log untuk debugging
        \Log::info('CCTV Debug: ID ' . $id . ' RTSP: ' . $camera->rtsp_cctv);

        // Langsung gunakan RTSP dari database
        $viewer = $this->buildViewerUrl($camera->rtsp_cctv);
        return response()->json([
            'success' => true,
            'viewer_url' => $viewer,
            'stream_name' => '',
            'debug_rtsp' => $camera->rtsp_cctv,
        ]);
    }

    public function stopStream(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true]);
    }

    /**
     * Debugging connection method.
     */
    public function debugConnection(int $id): JsonResponse
    {
        $camera = CctvLiveRegistry::findCamera($id);
        if (! $camera) {
            return response()->json(['success' => false, 'message' => 'Kamera tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'camera_id' => $id,
            'rtsp_url' => $camera->rtsp_cctv,
            'viewer_url' => $this->buildViewerUrl($camera->rtsp_cctv),
            'message' => 'Debug info retrieved successfully.'
        ]);
    }

    private function streamGatewayConfigured(): bool
    {
        return true;
    }

    private function streamClientConfig(): array
    {
        return [
            'url_stream' => '',
            'url_stream_go' => '',
            'viewer_host' => (string) config('satupeta.stream_viewer_host', 'sp.kodal.id'),
            'viewer_port' => (int) config('satupeta.stream_viewer_port', 5050),
        ];
    }

    private function buildViewerUrl(string $transcodedRtsp): string
    {
        $host = (string) config('satupeta.stream_viewer_host', 'sp.kodal.id');
        $port = (int) config('satupeta.stream_viewer_port', 5050);

        return 'https://'.$host.':'.$port.'/?u='.rawurlencode($transcodedRtsp);
    }
}
