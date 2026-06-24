/**
 * Pemutar live CCTV backoffice — gateway SatuPeta (play/stop) + opsional WHEP.
 */
(function (window) {
    'use strict';

    function csrfToken() {
        var el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    async function postJson(url, body) {
        var response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(body || {}),
        });
        var data = await response.json().catch(function () { return {}; });
        if (!response.ok) {
            throw new Error(data.message || ('HTTP ' + response.status));
        }
        return data;
    }

    async function startGatewayStream(startUrl, stopUrl) {
        var frame = document.getElementById('cctvLiveFrame');
        var status = document.getElementById('cctvLiveStatus');
        var state = { streamName: '' };

        function setStatus(msg, isError) {
            if (!status) return;
            status.textContent = msg;
            status.classList.toggle('text-red-600', !!isError);
            status.classList.toggle('text-slate-500', !isError);
        }

        async function stop() {
            if (!state.streamName) return;
            try {
                await postJson(stopUrl, { stream_name: state.streamName });
            } catch (e) { /* ignore */ }
            state.streamName = '';
        }

        setStatus('Menghubungkan ke kamera…');
        try {
            var result = await postJson(startUrl, {});
            if (!result.success || !result.viewer_url) {
                throw new Error(result.message || 'Gagal memulai stream.');
            }
            state.streamName = result.stream_name || '';
            if (frame) {
                frame.src = result.viewer_url;
                frame.classList.remove('hidden');
            }
            setStatus('Live — sedang menyiarkan');
        } catch (err) {
            setStatus(err.message || 'Gagal memuat stream.', true);
            throw err;
        }

        window.addEventListener('beforeunload', stop);
        window.addEventListener('pagehide', stop);

        return { stop: stop };
    }

    window.CctvLiveStream = {
        startGatewayStream: startGatewayStream,
    };
})(window);
