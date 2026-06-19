@php
    $renderIcon = static function (array $item, bool $filled = false): void {
        $name = \App\Helpers\MenuHelper::sidebarMaterialIcon($item['icon'] ?? null);
        $fill = $filled ? "font-variation-settings: 'FILL' 1;" : '';
        echo '<span class="material-symbols-outlined text-[22px] shrink-0 group-hover:scale-105 transition-transform" style="'.$fill.'">'.$name.'</span>';
    };

    $isSuperadmin = \App\Helpers\MenuHelper::isSuperAdminForLayout(auth()->user());
    /** Lonceng notifikasi hanya di menu Opsdal (semua role operasional; bukan SuperAdmin config). */
    $opsdalMenuPath = 'group_opsdal';
@endphp

<div class="sidebar-menu space-y-0.5">
    @foreach($menuItems as $item)
        @php
            $collapseId = 'menu-' . md5(($item['path'] ?? '') . '|' . ($item['label'] ?? ''));
            $isActive = \App\Helpers\MenuHelper::isNavItemActive($item);
            $hasSubmenu = isset($item['submenu']) && ! empty($item['submenu']);
            $isDisabled = ! empty($item['disabled']);
            $parentActiveClass = $isActive
                ? 'text-primary bg-primary/10 font-semibold'
                : 'text-on-surface-variant hover:text-primary hover:bg-slate-50';
            $leafActiveClass = $isActive
                ? 'bg-primary text-on-primary font-semibold active-tab'
                : 'text-on-surface-variant hover:text-primary hover:bg-slate-50';
        @endphp

        @if($hasSubmenu)
            <div class="sidebar-menu-group">
                <button type="button"
                        class="sidebar-menu-trigger w-full group flex items-center gap-2.5 px-3 py-2.5 transition-all rounded-xl border-0 bg-transparent text-left {{ $parentActiveClass }}"
                        data-bs-toggle="collapse"
                        data-bs-target="#{{ $collapseId }}"
                        aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                    @php $renderIcon($item, $isActive); @endphp
                    <span class="sidebar-menu-label text-label-md font-medium flex-1">{{ $item['label'] }}</span>
                    @if(($item['path'] ?? '') === $opsdalMenuPath && ! $isSuperadmin)
                        <div class="relative opsdal-bell-container shrink-0" onclick="event.stopPropagation();">
                            <span class="btn btn-sm p-0 text-slate-400 hover:text-slate-600 relative opsdal-bell-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi Opsdal">
                                <span class="material-symbols-outlined text-[18px] opsdal-bell-icon">notifications</span>
                                <span class="absolute -top-0.5 -right-0.5 flex h-2 w-2 opsdal-bell-badge" style="display: none;">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500 border border-white"></span>
                                </span>
                            </span>
                            <ul class="dropdown-menu dropdown-menu-end shadow opsdal-notif-list text-xs p-1" style="width: 280px; max-height: 350px; overflow-y: auto; z-index: 1050; background-color: #ffffff !important; color: #1e293b !important;">
                            </ul>
                        </div>
                    @endif
                    <span class="material-symbols-outlined text-[18px] text-slate-400 transition-transform duration-200 sidebar-chevron shrink-0 {{ $isActive ? 'rotate-180' : '' }}">expand_more</span>
                </button>
                <div class="collapse {{ $isActive ? 'show' : '' }} mt-0.5" id="{{ $collapseId }}">
                    <div class="ml-3 pl-3 border-l border-slate-200/80 space-y-0.5 py-1">
                        @foreach($item['submenu'] as $subItem)
                            @php
                                $subIsActive = \App\Helpers\MenuHelper::isNavItemActive($subItem);
                                $subIsDisabled = ! empty($subItem['disabled']);
                                $subActiveClass = $subIsActive
                                    ? 'bg-primary/10 text-primary font-bold'
                                    : 'text-slate-500 hover:text-primary hover:bg-slate-50';
                            @endphp
                            @if($subIsDisabled)
                                <span class="flex items-center gap-2.5 px-3 py-2 text-slate-300 rounded-xl cursor-not-allowed min-w-0" title="Segera hadir">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                                    <span class="sidebar-menu-label text-label-md font-medium flex-1">{{ $subItem['label'] }}</span>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 bg-slate-100 text-slate-400 rounded-md uppercase shrink-0">Soon</span>
                                </span>
                            @else
                                <a class="flex items-center gap-2.5 px-3 py-2 transition-all rounded-xl text-decoration-none min-w-0 {{ $subActiveClass }}"
                                   href="{{ \App\Helpers\MenuHelper::getMenuItemUrl($subItem) }}">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $subIsActive ? 'bg-primary ring-2 ring-primary/20' : 'bg-slate-300' }}"></span>
                                    <span class="sidebar-menu-label text-label-md {{ $subIsActive ? 'font-bold' : 'font-medium' }}">{{ $subItem['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @elseif($isDisabled)
            <span class="group flex items-center gap-2.5 px-3 py-2.5 text-slate-300 rounded-xl cursor-not-allowed min-w-0" title="Segera hadir">
                @php $renderIcon($item, false); @endphp
                <span class="sidebar-menu-label text-label-md font-medium flex-1">{{ $item['label'] }}</span>
                <span class="text-[9px] font-bold px-1.5 py-0.5 bg-slate-100 text-slate-400 rounded-md uppercase shrink-0">Soon</span>
            </span>
        @else
            <a class="group flex items-center gap-2.5 px-3 py-2.5 transition-all rounded-xl text-decoration-none min-w-0 {{ $leafActiveClass }}"
               href="{{ \App\Helpers\MenuHelper::getMenuItemUrl($item) }}">
                @php $renderIcon($item, $isActive); @endphp
                <span class="sidebar-menu-label text-label-md font-medium flex-1">{{ $item['label'] }}</span>
            </a>
        @endif
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.sidebar-menu-group .collapse').forEach(function (collapse) {
            const trigger = document.querySelector('[data-bs-target="#' + collapse.id + '"]');
            const chevron = trigger?.querySelector('.sidebar-chevron');
            if (!chevron) return;

            collapse.addEventListener('show.bs.collapse', function () {
                chevron.classList.add('rotate-180');
            });
            collapse.addEventListener('hide.bs.collapse', function () {
                chevron.classList.remove('rotate-180');
            });
        });

        // Lonceng hanya dirender di menu Opsdal (.opsdal-bell-container); menu lain tanpa lonceng.
        const bellContainers = document.querySelectorAll('.opsdal-bell-container');
        const OPSDAL_ALERT_SOURCES = ['patrolicctv', 'layanan_masyarakat', 'contigency', 'trafficstuck', 'turjagwali'];

        if (bellContainers.length > 0) {
            let lastLayananId = localStorage.getItem('sbo_opsdal_notif_layanan') || 0;
            let lastCctvId = localStorage.getItem('sbo_opsdal_notif_cctv') || 0;
            let lastTurjagwaliId = localStorage.getItem('sbo_opsdal_notif_turjagwali') || 0;
            let lastContigencyId = localStorage.getItem('sbo_opsdal_notif_contigency') || 0;
            let lastTrafficstuckId = localStorage.getItem('sbo_opsdal_notif_trafficstuck') || 0;
            let notifItems = [];
            try {
                notifItems = JSON.parse(localStorage.getItem('sbo_opsdal_notif_items') || '[]')
                    .filter(item => OPSDAL_ALERT_SOURCES.includes(item.source));
            } catch(e) {
                notifItems = [];
            }

            let audioCtx = null;
            function playBeep() {
                try {
                    if (!audioCtx) {
                        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    if (audioCtx.state === 'suspended') {
                        audioCtx.resume();
                    }
                    const oscillator = audioCtx.createOscillator();
                    const gainNode = audioCtx.createGain();
                    oscillator.connect(gainNode);
                    gainNode.connect(audioCtx.destination);
                    oscillator.type = 'sine';
                    oscillator.frequency.value = 800;
                    gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);
                    oscillator.start(audioCtx.currentTime);
                    oscillator.stop(audioCtx.currentTime + 0.5);
                } catch(e) {
                    console.error('playBeep error', e);
                }
            }

            function relevantNotifs() {
                return notifItems
                    .map((item, idx) => ({ item, idx }))
                    .filter(({ item }) => OPSDAL_ALERT_SOURCES.includes(item.source));
            }

            function setPassiveState(iconEl, badgeEl, btnEl) {
                if (iconEl) {
                    iconEl.textContent = 'notifications';
                    iconEl.classList.remove('animate-pulse');
                }
                if (badgeEl) badgeEl.style.display = 'none';
                if (btnEl) {
                    btnEl.classList.remove('text-red-500', 'hover:text-red-600');
                    btnEl.classList.add('text-slate-400', 'hover:text-slate-600');
                }
            }

            function setActiveState(iconEl, badgeEl, btnEl) {
                if (iconEl) {
                    iconEl.textContent = 'notifications_active';
                    iconEl.classList.add('animate-pulse');
                }
                if (badgeEl) badgeEl.style.display = 'flex';
                if (btnEl) {
                    btnEl.classList.remove('text-slate-400', 'hover:text-slate-600');
                    btnEl.classList.add('text-red-500', 'hover:text-red-600');
                }
            }

            function renderNotifs() {
                bellContainers.forEach(container => {
                    const listEl = container.querySelector('.opsdal-notif-list');
                    const iconEl = container.querySelector('.opsdal-bell-icon');
                    const badgeEl = container.querySelector('.opsdal-bell-badge');
                    const btnEl = container.querySelector('.opsdal-bell-btn');

                    if (!listEl) return;
                    listEl.innerHTML = '';

                    const alerts = relevantNotifs();

                    if (alerts.length === 0) {
                        setPassiveState(iconEl, badgeEl, btnEl);
                        listEl.innerHTML = `<li class="px-3 py-2 text-center text-slate-400">Tidak ada notifikasi baru</li>`;
                        return;
                    }

                    setActiveState(iconEl, badgeEl, btnEl);

                    const header = document.createElement('li');
                    header.innerHTML = `<div class="px-3 py-2 font-bold text-slate-700 border-b mb-1 flex justify-between items-center" style="color: #1e293b !important; border-color: #f1f5f9 !important;">
                        <span>Notifikasi Opsdal (${alerts.length})</span>
                        <button class="btn btn-sm btn-link p-0 text-[10px] text-slate-400 hover:text-slate-600" style="color: #0041c8 !important; text-decoration: none;" id="opsdal-notif-clearAll">Clear</button>
                    </div>`;
                    listEl.appendChild(header);

                    alerts.forEach(({ item, idx }) => {
                        const li = document.createElement('li');
                        li.innerHTML = `
                            <a class="dropdown-item py-2 px-3 text-wrap border-b border-slate-50 last:border-0 hover:bg-slate-50 opsdal-notif-item-link" href="${item.url}" data-idx="${idx}">
                                <div class="font-semibold text-primary mb-0.5" style="color: #0041c8 !important;">${item.label}</div>
                                <div class="text-slate-500 mb-1" style="font-size: 11px; color: #475569 !important;">${item.desc}</div>
                                <div class="text-slate-400" style="font-size: 9px; color: #94a3b8 !important;">${item.time}</div>
                            </a>
                        `;
                        listEl.appendChild(li);
                    });

                    const clearBtn = listEl.querySelector('#opsdal-notif-clearAll');
                    if (clearBtn) {
                        clearBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            notifItems = notifItems.filter(item => !OPSDAL_ALERT_SOURCES.includes(item.source));
                            localStorage.setItem('sbo_opsdal_notif_items', JSON.stringify(notifItems));
                            renderNotifs();
                        });
                    }

                    listEl.querySelectorAll('.opsdal-notif-item-link').forEach(link => {
                        link.addEventListener('click', function() {
                            const idx = parseInt(this.getAttribute('data-idx'));
                            if (!isNaN(idx)) {
                                notifItems.splice(idx, 1);
                                localStorage.setItem('sbo_opsdal_notif_items', JSON.stringify(notifItems));
                                renderNotifs();
                            }
                        });
                    });
                });
            }

            function fetchNotifs() {
                const wasInitialized = (lastLayananId > 0 || lastCctvId > 0 || lastTurjagwaliId > 0 || lastContigencyId > 0 || lastTrafficstuckId > 0);

                fetch(`/api/opsdal-notifications?last_layanan_id=${lastLayananId}&last_cctv_id=${lastCctvId}&last_turjagwali_id=${lastTurjagwaliId}&last_contigency_id=${lastContigencyId}&last_trafficstuck_id=${lastTrafficstuckId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    const incoming = (data.items || []).filter(item => OPSDAL_ALERT_SOURCES.includes(item.source));

                    if (incoming.length > 0) {
                        if (wasInitialized) {
                            playBeep();
                            localStorage.setItem('sbo_opsdal_notif_opened', 'false');
                        }

                        let combined = [...incoming, ...notifItems];
                        const uniqueMap = {};
                        notifItems = combined.filter(item => {
                            const key = item.id && item.source ? `${item.source}-${item.id}` : (item.url || item.desc);
                            if (uniqueMap[key]) return false;
                            uniqueMap[key] = true;
                            return OPSDAL_ALERT_SOURCES.includes(item.source);
                        }).slice(0, 20);

                        localStorage.setItem('sbo_opsdal_notif_items', JSON.stringify(notifItems));

                        lastLayananId = Math.max(lastLayananId, data.max_layanan_id || 0);
                        lastCctvId = Math.max(lastCctvId, data.max_cctv_id || 0);
                        lastTurjagwaliId = Math.max(lastTurjagwaliId, data.max_turjagwali_id || 0);
                        lastContigencyId = Math.max(lastContigencyId, data.max_contigency_id || 0);
                        lastTrafficstuckId = Math.max(lastTrafficstuckId, data.max_trafficstuck_id || 0);

                        localStorage.setItem('sbo_opsdal_notif_layanan', lastLayananId);
                        localStorage.setItem('sbo_opsdal_notif_cctv', lastCctvId);
                        localStorage.setItem('sbo_opsdal_notif_turjagwali', lastTurjagwaliId);
                        localStorage.setItem('sbo_opsdal_notif_contigency', lastContigencyId);
                        localStorage.setItem('sbo_opsdal_notif_trafficstuck', lastTrafficstuckId);
                    }

                    renderNotifs();
                })
                .catch(e => console.error('Opsdal notif error', e));
            }

            renderNotifs();

            setTimeout(fetchNotifs, 2000);
            setInterval(fetchNotifs, 10000);

            setInterval(function() {
                let currentItems = [];
                try {
                    currentItems = JSON.parse(localStorage.getItem('sbo_opsdal_notif_items') || '[]')
                        .filter(item => OPSDAL_ALERT_SOURCES.includes(item.source));
                } catch(e) {}
                if (localStorage.getItem('sbo_opsdal_notif_opened') === 'false' && currentItems.length > 0) {
                    playBeep();
                }
            }, 10000);

            document.body.addEventListener('click', function initAudio(e) {
                if (e.target.closest('.opsdal-bell-btn')) {
                    localStorage.setItem('sbo_opsdal_notif_opened', 'true');
                    renderNotifs();
                }
                try {
                    if (!audioCtx) {
                        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    if (audioCtx.state === 'suspended') {
                        audioCtx.resume();
                    }
                } catch(err) {}
            });
        }
    });
</script>
