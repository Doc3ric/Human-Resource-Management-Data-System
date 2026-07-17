{{--
    Enhancement Spec Sec. 1 — Collapsible & Sortable Columns (Active-Filter-Safe).

    Three independent state objects, none of which resets another:
    - active_filters:       already server-side GET params, untouched by this partial.
    - column_visibility:    persisted server-side per user, per tab (user_table_preferences table),
                             toggled via the "Show Columns" dropdown this partial renders. Never
                             triggers a page reload. localStorage is used only as an instant-paint
                             cache while the server value loads, then reconciled.
    - sort_column/direction: server-side GET params ("sort"/"direction"), applied via plain links that
                             preserve every other current query param (built server-side per column).

    Usage: `@include('partials.table-column-controls', ['tabKey' => 'all-data'])` once per page,
    right before the `<table>`. Then add `data-col="x"` to every `<th>` (and, if it should be
    collapsible, the matching `<td data-col="x">` in the row template) sharing the same "x" key.
--}}
<div class="tcc-wrap" style="display:flex; justify-content:flex-end; margin-bottom:8px;">
    <div class="tcc-dropdown" style="position:relative;">
        <button type="button" onclick="document.getElementById('tcc-panel-{{ $tabKey }}').classList.toggle('open')"
            class="tcc-toggle-btn" style="display:flex; align-items:center; gap:6px; padding:6px 12px; border:1px solid #d1d5db; border-radius:8px; background:#fff; font-size:12.5px; font-weight:600; color:#374151; cursor:pointer;">
            <i class="bi bi-layout-three-columns"></i> Show Columns
        </button>
        <div id="tcc-panel-{{ $tabKey }}" class="tcc-panel" style="display:none; position:absolute; right:0; top:calc(100% + 4px); background:#fff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,0.08); padding:6px; min-width:180px; z-index:40; max-height:280px; overflow-y:auto;"></div>
    </div>
</div>

<style>
    .tcc-panel.open { display:block !important; }
    .tcc-panel label { display:flex; align-items:center; gap:8px; padding:5px 8px; font-size:12.5px; color:#374151; cursor:pointer; border-radius:6px; white-space:nowrap; }
    .tcc-panel label:hover { background:#f3f4f6; }
    th[data-sort] { cursor:pointer; user-select:none; }
    th[data-sort]:hover { background:#f3f4f6; }
    th[data-sort] .tcc-sort-icon { font-size:10px; margin-left:3px; opacity:0.5; }
    th[data-sort].tcc-active .tcc-sort-icon { opacity:1; }
</style>

<script>
(function () {
    const tabKey = {{ Js::from($tabKey) }};
    const storageKey = 'col_visibility_' + tabKey; // instant-paint cache only; server is authoritative
    const prefUrl = {{ Js::from(url('table-preferences/' . $tabKey)) }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function initColumnControls() {
        const ths = Array.from(document.querySelectorAll('th[data-col]'));
        if (ths.length === 0) return;

        let hidden = [];
        try { hidden = JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch (e) { hidden = []; }

        function elementsFor(col) {
            return document.querySelectorAll('[data-col="' + col + '"]');
        }

        function applyVisibility() {
            ths.forEach(th => {
                const col = th.dataset.col;
                const isHidden = hidden.includes(col);
                elementsFor(col).forEach(el => { el.style.display = isHidden ? 'none' : ''; });
            });
        }

        function renderPanel() {
            const panel = document.getElementById('tcc-panel-' + tabKey);
            if (!panel) return;
            panel.innerHTML = ths.map(th => {
                const col = th.dataset.col;
                const label = th.dataset.label || th.textContent.trim();
                const checked = hidden.includes(col) ? '' : 'checked';
                return '<label><input type="checkbox" ' + checked + ' onchange="window.__tccToggle_' + tabKey + '(\'' + col + '\')"> ' + label + '</label>';
            }).join('');
        }

        function persist() {
            localStorage.setItem(storageKey, JSON.stringify(hidden));
            if (!csrfToken) return;
            fetch(prefUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ hidden: hidden }),
            }).catch(() => {}); // best-effort — localStorage already has it as a fallback
        }

        function toggleColumn(col) {
            hidden = hidden.includes(col) ? hidden.filter(c => c !== col) : hidden.concat([col]);
            applyVisibility();
            renderPanel();
            persist();
        }
        window['__tccToggle_' + tabKey] = toggleColumn;

        applyVisibility();
        renderPanel();

        // Reconcile with the server's per-user value once it arrives — the
        // localStorage read above just avoids a flash of all-columns-visible
        // while this request is in flight.
        fetch(prefUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data || !Array.isArray(data.hidden)) return;
                hidden = data.hidden;
                applyVisibility();
                renderPanel();
                localStorage.setItem(storageKey, JSON.stringify(hidden));
            })
            .catch(() => {});

        const panel = document.getElementById('tcc-panel-' + tabKey);
        document.addEventListener('click', function (e) {
            if (panel && !panel.contains(e.target) && !e.target.closest('.tcc-toggle-btn')) {
                panel.classList.remove('open');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initColumnControls);
    } else {
        initColumnControls();
    }
})();
</script>
