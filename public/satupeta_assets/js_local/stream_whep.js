var debounceTimerIn = null;
var debounceTimerOut = null;
var activeIndexes = { 'in': -1, 'out': -1 }; // State index slide aktif
const activeConnections = { 'in': null, 'out': null }; // RTCPeerConnection instances

// Konfigurasi URL Stream
const WHEP_URL = (id) => `${global_url_stream_go}transcode/${id}/whep`;
const RESOLVE_URL = `${global_url_stream_go}resolve-path`;
const TRANSCODE_URL = (id) => `${global_url_stream_go}transcode/${id}`;
const LOG_PREFIX = (groupKey) => `[WHEP ${groupKey.toUpperCase()}]`;

/**
 * Membersihkan koneksi RTCPeerConnection yang sedang aktif.
 * @param {string} groupKey 'in' atau 'out'
 */
function cleanupPC(groupKey) {
    const pc = activeConnections[groupKey];
    if (pc) {
        try { pc.getSenders().forEach(s => s.track && s.track.stop()); } catch (e) {}
        try { pc.close(); } catch (e) {}
        activeConnections[groupKey] = null;
        console.log(`${LOG_PREFIX(groupKey)} Cleaned up previous connection.`);
    }
}

/**
 * Mengambil konfigurasi ICE Servers dari endpoint.
 * @param {string} endpoint URL WHEP.
 * @returns {Array<RTCIceServer>}
 */
async function getICE(endpoint) {
    try {
        const r = await fetch(endpoint, { method: "OPTIONS" });
        const link = r.headers.get("Link");
        if (!link) return [{ urls: "stun:stun.l.google.com:19302" }];
        const urls = link.split(",").map(s => {
            const m = s.match(/<([^>]+)>/);
            return m ? { urls: m[1] } : null;
        }).filter(Boolean);
        return urls.length ? urls : [{ urls: "stun:stun.l.google.com:19302" }];
    } catch (e) {
        return [{ urls: "stun:stun.l.google.com:19302" }];
    }
}

/**
 * Memulai transcoder di server.
 * @param {string} id ID kamera.
 * @returns {Promise<boolean>}
 */
async function startTranscoder(id) {
    try {
        await fetch(RESOLVE_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                path: TRANSCODE_URL(id),
                quality: "hd"
            })
        });
        return true;
    } catch (e) {
        console.warn(`${LOG_PREFIX('Global')} Transcoder trigger failed for ID ${id}`, e);
        return false;
    }
}

/**
 * Menunggu hingga endpoint WHEP siap.
 * @param {string} id ID kamera.
 * @returns {Promise<boolean>}
 */
async function waitCameraReady(id) {
    const url = WHEP_URL(id);
    for (let i = 0; i < 20; i++) {
        try {
            const r = await fetch(url, { method: "OPTIONS" });
            if (r.ok) return true;
        } catch (e) {}
        await new Promise(r => setTimeout(r, 300));
    }
    return false;
}

/**
 * Memulai pemutaran stream WHEP.
 * @param {string} groupKey 'in' atau 'out'.
 * @param {string} endpoint URL WHEP.
 * @param {HTMLVideoElement} videoEl Elemen video.
 * @param {number} requestIndex Index slide saat request dimulai.
 */
async function playWHEP(groupKey, endpoint, videoEl, requestIndex) {
    if (activeIndexes[groupKey] !== requestIndex) {
        console.log(`${LOG_PREFIX(groupKey)} Aborted Play: Index Mismatch.`);
        return; 
    }

    const ice = await getICE(endpoint);
    const pc = new RTCPeerConnection({ iceServers: ice });

    // Cek lagi setelah async call. Jika user sudah ganti slide, abort.
    if (activeIndexes[groupKey] !== requestIndex) {
        pc.close();
        return; 
    }
    
    // Simpan PC aktif. Cleanup akan menghapus ini.
    activeConnections[groupKey] = pc;

    const stream = new MediaStream();
    videoEl.srcObject = stream;

    pc.ontrack = (ev) => { stream.addTrack(ev.track); };
    pc.addTransceiver("video", { direction: "recvonly" });

    // Handle connection state changes
    pc.oniceconnectionstatechange = () => {
        console.log(`${LOG_PREFIX(groupKey)} ICE State: ${pc.iceConnectionState}`);
        if (pc.iceConnectionState === 'failed' || pc.iceConnectionState === 'disconnected') {
            // Re-attempting or indicating failure might be needed here.
        }
    };
    
    try {
        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        
        // Wait ICE gathering
        await new Promise(resolve => {
            if (pc.iceGatheringState === "complete") return resolve();
            const fn = () => {
                if (pc.iceGatheringState === "complete" || pc.iceConnectionState === 'failed') {
                    pc.removeEventListener("icegatheringstatechange", fn);
                    resolve();
                }
            };
            pc.addEventListener("icegatheringstatechange", fn);
            setTimeout(resolve, 1500);
        });

        // POST Offer
        const resp = await fetch(endpoint, {
            method: "POST",
            headers: { "Content-Type": "application/sdp" },
            body: pc.localDescription.sdp
        });

        if (!resp.ok) {
            throw new Error(`WHEP Negotiation Failed: ${resp.status}`);
        }

        const ansSDP = await resp.text();
        await pc.setRemoteDescription({ type: "answer", sdp: ansSDP });
        console.log(`${LOG_PREFIX(groupKey)} Stream playing.`);

    } catch (e) {
        console.error(`${LOG_PREFIX(groupKey)} Failed to play stream:`, e);
        // Clear broken connection
        if (activeConnections[groupKey] === pc) window.cleanupPC(groupKey);
    }
}

/**
 * Fungsi utama
 * @param {string} groupKey 'in' atau 'out'.
 * @param {string} camId ID kamera.
 * @param {HTMLVideoElement} videoEl Elemen video.
 * @param {number} requestIndex Index slide saat request dimulai (untuk debounce/cleanup).
 */
async function loadStream(groupKey, camId, videoEl, requestIndex) {
    
    // Tampilkan loading visual
    videoEl.poster = `${global_url_ci}my/img_/loading.gif`; // Tidak perlu jika sudah dihandle di dashboard
    
    console.log(`${LOG_PREFIX(groupKey)} Starting load for ID ${camId} (Request Index: ${requestIndex})`);
    
    // Step 1: Transcoder
    const transcodeOk = await startTranscoder(camId);
    
    // Cek Early Exit: Jika user sudah geser slide sebelum step ini selesai
    if (activeIndexes[groupKey] !== requestIndex) {
        console.log(`${LOG_PREFIX(groupKey)} Aborted Transcode: Index Mismatch.`);
        return; 
    }
    if (!transcodeOk) {
        console.error(`${LOG_PREFIX(groupKey)} Transcoder failed to start.`);
        return;
    }

    // Step 2: Wait Ready
    const ready = await waitCameraReady(camId);
    
    // Cek Early Exit: Jika user sudah geser slide sebelum step ini selesai
    if (activeIndexes[groupKey] !== requestIndex) {
        console.log(`${LOG_PREFIX(groupKey)} Aborted Wait Ready: Index Mismatch.`);
        return; 
    }
    if (!ready) {
        console.error(`${LOG_PREFIX(groupKey)} Camera not ready (Timeout).`);
        return;
    }

    // Step 3: Play
    videoEl.autoplay = true; 
    videoEl.muted = true;
    
    await playWHEP(groupKey, WHEP_URL(camId), videoEl, requestIndex);
};


// CODE UNTUK STREAM CLICK CTV DI PETA
let pc = null;
async function loadCamera(cameraId, videoEl, pc_ref) {
    let pc = pc_ref;

    try {
        // Step 1: trigger transcoder
        await startTranscoder(cameraId);

        // Step 2: wait until WHEP endpoint is alive
        await waitCameraReady(cameraId);

        // setStatus(statusEl, `connecting ${cameraId}`);
        const endpoint = WHEP_URL(cameraId);
        
        // Step 3: play WebRTC camera stream
        pc = cleanupPC(pc); // Pastikan koneksi lama di-cleanup

        const ice = await getICE(endpoint);

        pc = new RTCPeerConnection({ iceServers: ice });

        const stream = new MediaStream();
        videoEl.srcObject = stream;

        pc.ontrack = (ev) => {
            stream.addTrack(ev.track);
        };
        
        // Cek status koneksi WebRTC
        pc.oniceconnectionstatechange = () => {
            // setStatus(statusEl, `ICE: ${pc.iceConnectionState}`);
        };

        pc.addTransceiver("video", { direction:"recvonly" });
        // pc.addTransceiver("audio", { direction:"recvonly" });

        // Create offer
        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        await waitICEGathering(pc);

        // POST offer -> answer
        const resp = await fetch(endpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/sdp"
            },
            body: pc.localDescription.sdp
        });

        if (!resp.ok) {
            // setStatus(statusEl, `error ${resp.status}`);
            console.log(`error ${resp.status}`);
            return pc;
        }

        const ansSDP = await resp.text();
        await pc.setRemoteDescription({ type:"answer", sdp: ansSDP });
        
        // setStatus(statusEl, `playing ${cameraId}`);
        return pc;

    } catch(e) {
        console.error(`Error loading camera ${cameraId}:`, e);
        // setStatus(statusEl, `failed to play ${cameraId}`);
        return cleanupPC(pc);
    }
}

function waitICEGathering(pc) {
return new Promise(resolve => {
    if (pc.iceGatheringState === "complete") return resolve();
    const fn = () => {
        if (pc.iceGatheringState === "complete") {
            pc.removeEventListener("icegatheringstatechange", fn);
            resolve();
        }
    };
    pc.addEventListener("icegatheringstatechange", fn);
    setTimeout(resolve, 2000); // safety
});
}