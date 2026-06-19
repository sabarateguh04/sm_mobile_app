const HOST = Object.freeze({
    bali: "https://stream.elingbali.com",
    solo: "https://stream.elingsolo.com",
    yogya: "https://stream.elingjogja.com",
    bandung: "https://stream.elingbandung.com",
});

const AUTH = "Basic " + btoa("viewer:password123");
let pc = null;

function getHost(region) {
    const host = HOST[region?.toLowerCase()];
    if (!host) {
        console.error(`Region '${region}' tidak ditemukan!`);
        return null;
    }
    return host;
}

async function loadCamera(id, region) {
    const host = getHost(region);

    const videoEl = document.getElementById("video-" + id);

    if (!host) return; // stop jika region salah

    const WHEP = `${host}/transcode/${id}/whep`;
    const RESOLVE = `${host}/resolve-path`;

    // console.log("videoEl:", videoEl);
    // console.log("region:", region);
    // console.log("host:", host);
    // console.log("whep:", WHEP);
    // console.log("resolve:", RESOLVE);

    // Step 1: trigger transcoder
    await startTranscoder(id, RESOLVE);

    // Step 2: wait until WHEP endpoint is alive
    await waitCameraReady(id, WHEP);

    // Step 3: play WebRTC camera stream
    await playWHEP(WHEP, videoEl);
}

async function startTranscoder(id, url) {
    try {
        await fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                path: "http://localhost:15000/transcode/" + id,
                quality: "hd",
            }),
        });
    } catch (e) {
        console.warn("resolve-path failed", e);
    }
}

async function waitCameraReady(id, url) {
    while (true) {
        try {
            const r = await fetch(url, {
                method: "OPTIONS",
                headers: { Authorization: AUTH },
            });
            if (r.ok) return; // ready
        } catch (e) {}
        await sleep(300);
    }
}

async function playWHEP(endpoint, videoEl) {
    cleanupPC();

    console.log("videoEl : ", videoEl);

    const ice = await getICE(endpoint);

    pc = new RTCPeerConnection({ iceServers: ice });

    const stream = new MediaStream();
    videoEl.srcObject = stream;

    pc.ontrack = (ev) => {
        stream.addTrack(ev.track);
    };

    pc.addTransceiver("video", { direction: "recvonly" });
    //   pc.addTransceiver("audio", { direction:"recvonly" });

    // Create offer
    const offer = await pc.createOffer();
    await pc.setLocalDescription(offer);
    await waitICEGathering(pc);

    // POST offer -> answer
    const resp = await fetch(endpoint, {
        method: "POST",
        headers: {
            "Content-Type": "application/sdp",
            Authorization: AUTH,
        },
        body: pc.localDescription.sdp,
    });

    if (!resp.ok) {
        setStatus("error " + resp.status);
        return;
    }

    const ansSDP = await resp.text();
    await pc.setRemoteDescription({ type: "answer", sdp: ansSDP });
}

function cleanupPC() {
    if (!pc) return;
    try {
        pc.getSenders().forEach((s) => s.track && s.track.stop());
    } catch (e) {}
    try {
        pc.close();
    } catch (e) {}
    pc = null;
}

async function getICE(endpoint) {
    try {
        const r = await fetch(endpoint, { method: "OPTIONS", headers: { Authorization: AUTH } });
        const link = r.headers.get("Link");
        if (!link) return [{ urls: "stun:stun.l.google.com:19302" }];

        const urls = link
            .split(",")
            .map((s) => {
                const m = s.match(/<([^>]+)>/);
                return m ? { urls: m[1] } : null;
            })
            .filter(Boolean);

        return urls.length ? urls : [{ urls: "stun:stun.l.google.com:19302" }];
    } catch (e) {
        return [{ urls: "stun:stun.l.google.com:19302" }];
    }
}

function waitICEGathering(pc) {
    return new Promise((resolve) => {
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

function sleep(ms) {
    return new Promise((r) => setTimeout(r, ms));
}
