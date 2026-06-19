/**
 * Pratinjau aman untuk input file gambar/video — dipakai bersama oleh semua form (layouts/app).
 *
 * Tambahkan pada <input type="file">:
 *   data-sbo-media-preview
 *   data-sbo-preview-img="#idGambar"           (wajib kecuali hanya video)
 *   data-sbo-preview-video="#idVideo"          (opsional, untuk accept video/*)
 *   data-sbo-preview-wrap="#idWrapper"         (opsional; pakai untuk menyembunyikan seluruh blok pratinjau)
 *   data-sbo-max-mb="8"                        (opsional; tidak membuat pratinjau jika lebih besar — tidak mengganti validasi server)
 *
 * API: window.SBOFormMediaPreview.init(container?) untuk konten dinamis / AJAX.
 */
(function (global) {
    'use strict';

    function querySel(sel) {
        if (!sel || typeof sel !== 'string') {
            return null;
        }
        try {
            return document.querySelector(sel);
        } catch (e) {
            return null;
        }
    }

    function revokeStored(input) {
        var url = input.dataset.sboBlobUrl;
        if (url) {
            try {
                URL.revokeObjectURL(url);
            } catch (e) {
                /* noop */
            }
            delete input.dataset.sboBlobUrl;
        }
    }

    function guessKind(file) {
        var type = (file.type || '').toLowerCase();
        if (type.indexOf('video/') === 0) {
            return 'video';
        }
        if (type.indexOf('image/') === 0) {
            return 'image';
        }
        var parts = (file.name || '').split('.');
        var ext = parts.length > 1 ? parts.pop().toLowerCase() : '';
        if (['mp4', 'webm', 'ogg', 'ogv', 'mov', 'm4v', 'mkv'].indexOf(ext) !== -1) {
            return 'video';
        }
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif', 'heic', 'heif'].indexOf(ext) !== -1) {
            return 'image';
        }
        return 'unknown';
    }

    function bindInput(input) {
        if (!input || input.nodeName !== 'INPUT' || input.type !== 'file') {
            return;
        }
        if (input.dataset.sboMediaPreviewBound === '1') {
            return;
        }
        if (!input.hasAttribute('data-sbo-media-preview')) {
            return;
        }

        input.dataset.sboMediaPreviewBound = '1';

        var imgSel = input.getAttribute('data-sbo-preview-img');
        var vidSel = input.getAttribute('data-sbo-preview-video');
        var wrapSel = input.getAttribute('data-sbo-preview-wrap');

        var imgEl = imgSel ? querySel(imgSel) : null;
        var vidEl = vidSel ? querySel(vidSel) : null;
        var wrapEl = wrapSel ? querySel(wrapSel) : null;

        function hideAll() {
            revokeStored(input);
            if (imgEl) {
                imgEl.removeAttribute('src');
                imgEl.classList.add('d-none');
            }
            if (vidEl) {
                try {
                    vidEl.pause();
                } catch (e) {
                    /* noop */
                }
                vidEl.removeAttribute('src');
                try {
                    vidEl.load();
                } catch (e2) {
                    /* noop */
                }
                vidEl.classList.add('d-none');
            }
            if (wrapEl) {
                wrapEl.classList.add('d-none');
            }
        }

        function showImage(url) {
            if (vidEl) {
                try {
                    vidEl.pause();
                } catch (e) {
                    /* noop */
                }
                vidEl.removeAttribute('src');
                vidEl.classList.add('d-none');
            }
            if (!imgEl) {
                return;
            }
            imgEl.src = url;
            imgEl.classList.remove('d-none');
            if (wrapEl) {
                wrapEl.classList.remove('d-none');
            }
        }

        function showVideo(url) {
            if (imgEl) {
                imgEl.removeAttribute('src');
                imgEl.classList.add('d-none');
            }
            if (!vidEl) {
                return;
            }
            vidEl.src = url;
            vidEl.classList.remove('d-none');
            if (wrapEl) {
                wrapEl.classList.remove('d-none');
            }
            try {
                vidEl.load();
            } catch (e) {
                /* noop */
            }
        }

        function applyFile(file) {
            hideAll();

            if (!file) {
                return;
            }

            var maxMb = parseFloat(String(input.getAttribute('data-sbo-max-mb') || ''), 10);
            if (!isNaN(maxMb) && maxMb > 0 && file.size > maxMb * 1024 * 1024) {
                return;
            }

            var kind = guessKind(file);
            var url = URL.createObjectURL(file);
            input.dataset.sboBlobUrl = url;

            if (kind === 'video') {
                if (vidEl) {
                    showVideo(url);
                }
                return;
            }

            if (kind === 'image' || kind === 'unknown') {
                if (imgEl) {
                    showImage(url);
                }
            }
        }

        input.addEventListener('change', function () {
            applyFile(this.files && this.files[0]);
        });

        var form = input.closest('form');
        if (form) {
            form.addEventListener('reset', function () {
                window.setTimeout(function () {
                    applyFile(input.files && input.files[0]);
                }, 0);
            });
        }

        window.addEventListener('pagehide', function () {
            revokeStored(input);
        });
    }

    function init(scope) {
        var root = scope && scope.querySelectorAll ? scope : document;
        root.querySelectorAll('input[type="file"][data-sbo-media-preview]').forEach(bindInput);
    }

    global.SBOFormMediaPreview = { init: init };

    function boot() {
        init(document);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(typeof window !== 'undefined' ? window : this);
