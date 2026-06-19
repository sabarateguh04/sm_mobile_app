{{-- Menu horizontal topbar: IT Asset Management, Data Collecting, Config SOTK, dll. --}}
@if(count($navGroups) > 0)
<nav class="topbar-mega-nav {{ $embedded ? 'w-full min-w-0' : 'border-t border-slate-100/80 bg-slate-50/60 px-3 lg:px-6' }}" aria-label="Menu cepat">
    <div id="topbar-mega-scroll" class="flex items-center gap-0.5 overflow-x-auto custom-scrollbar min-w-0 {{ $embedded ? 'justify-center lg:justify-start' : 'py-1' }}">
        @foreach($navGroups as $gi => $group)
            <div class="relative shrink-0 topbar-mega-item">
                <button type="button"
                        class="topbar-mega-btn flex items-center gap-0.5 px-2.5 py-1.5 rounded-lg text-[11px] font-bold whitespace-nowrap transition-colors border-0 bg-transparent {{ !empty($group['active']) ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 hover:text-primary' }}"
                        data-mega-menu="mega-menu-{{ $gi }}"
                        aria-expanded="false"
                        aria-haspopup="true">
                    <span>{{ $group['label'] }}</span>
                    <span class="material-symbols-outlined text-[14px] leading-none topbar-mega-chevron transition-transform duration-200">expand_more</span>
                </button>
            </div>
        @endforeach
    </div>
</nav>

{{-- Panel dropdown di luar header agar tidak terpengaruh backdrop-filter / overflow --}}
@foreach($navGroups as $gi => $group)
    <div id="mega-menu-{{ $gi }}"
         class="topbar-mega-panel hidden min-w-[12rem] max-w-[16rem] max-h-[70vh] overflow-y-auto bg-white border border-slate-200/80 rounded-xl shadow-[0_8px_30px_rgba(15,23,42,0.12)] py-1.5"
         role="menu"
         data-mega-panel>
        @foreach($group['items'] as $item)
            <a role="menuitem"
               class="block mx-1.5 px-3 py-2 rounded-lg text-[11px] font-semibold text-decoration-none transition-colors {{ !empty($item['active']) ? 'bg-primary/10 text-primary font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-primary' }}"
               href="{{ $item['url'] }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
@endforeach

<script>
(function () {
    var openItem = null;

    function initTopbarMegaMenus() {
        var items = [];
        document.querySelectorAll('[data-mega-menu]').forEach(function (btn) {
            var panel = document.getElementById(btn.getAttribute('data-mega-menu'));
            if (panel) items.push({ btn: btn, panel: panel });
        });
        if (!items.length) return;

        items.forEach(function (item) {
            document.body.appendChild(item.panel);
            item.panel.style.position = 'fixed';
            item.panel.style.zIndex = '1060';
        });

        function closeAll() {
            items.forEach(function (item) {
                item.panel.classList.add('hidden');
                item.btn.setAttribute('aria-expanded', 'false');
                item.btn.classList.remove('is-open');
                var chevron = item.btn.querySelector('.topbar-mega-chevron');
                if (chevron) chevron.classList.remove('rotate-180');
            });
            openItem = null;
        }

        function positionPanel(btn, panel) {
            panel.classList.remove('hidden');
            panel.style.visibility = 'hidden';
            panel.style.left = '0';
            panel.style.top = '0';

            var rect = btn.getBoundingClientRect();
            var pw = panel.offsetWidth;
            var ph = panel.offsetHeight;
            var gap = 6;
            var pad = 8;

            var top = rect.bottom + gap;
            var left = rect.left;

            if (left + pw > window.innerWidth - pad) {
                left = Math.max(pad, window.innerWidth - pw - pad);
            }
            if (top + ph > window.innerHeight - pad) {
                top = Math.max(pad, rect.top - ph - gap);
            }
            if (left < pad) left = pad;

            panel.style.top = top + 'px';
            panel.style.left = left + 'px';
            panel.style.visibility = 'visible';
        }

        function openPanel(item) {
            closeAll();
            positionPanel(item.btn, item.panel);
            item.btn.setAttribute('aria-expanded', 'true');
            item.btn.classList.add('is-open');
            var chevron = item.btn.querySelector('.topbar-mega-chevron');
            if (chevron) chevron.classList.add('rotate-180');
            openItem = item;
        }

        items.forEach(function (item) {
            item.btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (openItem && openItem.panel === item.panel) {
                    closeAll();
                    return;
                }
                openPanel(item);
            });
            item.panel.addEventListener('click', function (e) { e.stopPropagation(); });
        });

        document.addEventListener('click', closeAll);

        window.addEventListener('resize', function () {
            if (openItem) positionPanel(openItem.btn, openItem.panel);
        });

        var scrollEl = document.getElementById('topbar-mega-scroll');
        if (scrollEl) {
            scrollEl.addEventListener('scroll', function () {
                if (openItem) positionPanel(openItem.btn, openItem.panel);
            }, { passive: true });
        }

        window.addEventListener('scroll', function () {
            if (openItem) positionPanel(openItem.btn, openItem.panel);
        }, true);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAll();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTopbarMegaMenus);
    } else {
        initTopbarMegaMenus();
    }
})();
</script>
@endif
