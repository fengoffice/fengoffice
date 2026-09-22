/**
 * og.ColumnManagerWindow — custom column visibility / order manager for ExtJS 2.3 GridPanels.
 *
 * Injected automatically into every grid that has at least one manageable column
 * (i.e. a column whose hideable !== false and that has a non-empty header).
 *
 * State is persisted in localStorage under the key  og.colmgr.<gridId>  so it
 * survives page reloads.  The grid's built-in ExtJS state manager keeps working
 * for column widths; this component is authoritative for order and visibility.
 */
(function () {

var STORAGE_PREFIX = 'og.colmgr.';

// ─── CSS (injected once) ──────────────────────────────────────────────────────
(function injectCss() {
    if (document.getElementById('og-colmgr-css')) return;
    var s = document.createElement('style');
    s.id = 'og-colmgr-css';
    s.textContent = [
        // ExtJS window: rounded corners, flat white header, body reset, close-button icon swap
        '.og-cmgr-win{border-radius:6px!important;overflow:hidden!important}',
        '.og-cmgr-win .x-window-tl,.og-cmgr-win .x-window-tr,.og-cmgr-win .x-window-bl,.og-cmgr-win .x-window-br{border-radius:0;background-image:none!important}',
        // flatten the ExtJS title bar — remove blue sprite images, use plain white
        '.og-cmgr-win .x-window-tl,.og-cmgr-win .x-window-tc,.og-cmgr-win .x-window-tr{',
            'background:#fff!important;background-image:none!important;border:none!important;height:auto!important;padding:0!important}',
        '.og-cmgr-win .x-window-bl,.og-cmgr-win .x-window-bc,.og-cmgr-win .x-window-br,',
        '.og-cmgr-win .x-window-ml,.og-cmgr-win .x-window-mr{',
            'background:#fff!important;background-image:none!important;border:none!important;padding:0!important}',
        '.og-cmgr-win .x-window-header{background:#fff!important;background-image:none!important;',
            'border:none!important;padding:13px 12px!important;',
            'display:flex!important;align-items:center!important;line-height:1!important}',
        '.og-cmgr-win .x-window-header-text{color:#333!important;font-size:15px!important;',
            'font-weight:600!important;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif!important;',
            'text-shadow:none!important;letter-spacing:-.1px!important;flex:1!important;order:1!important}',
        '.og-cmgr-win .x-window-mc{background:#fff!important;border-top:1px solid #e8e8e8!important;',
            'padding:0!important;margin:0!important}',
        '.og-cmgr-win .x-window-bwrap{background:#fff!important}',
        '.og-cmgr-win .x-window-body{padding:0!important;overflow:hidden!important;border:none!important}',
        // close button: expand clipped 15x15 tool box, draw × via ::before pseudo-element
        '.og-cmgr-win .x-tool-close{background-image:none!important;background:none!important;',
            'width:22px!important;height:22px!important;overflow:visible!important;float:none!important;',
            'display:flex!important;align-items:center;justify-content:center;',
            'flex-shrink:0!important;margin:0!important;order:2!important}',
        '.og-cmgr-win .x-tool-close::before{content:"\\e084";font-family:"lucide"!important;',
            'font-style:normal;font-size:18px;color:#aaa;line-height:1;',
            '-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}',
        '.og-cmgr-win .x-tool-close:hover::before{color:#444}',
        // Outer layout
        '.og-cmgr-wrap{display:flex;flex-direction:column;height:100%;overflow:hidden;',
            'background:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif}',
        '.og-cmgr-split{display:flex;flex:1;min-height:0}',
        // Panels
        '.og-cmgr-panel{flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0;',
            'border-right:1px solid #e2e2e2}',
        '.og-cmgr-panel-r{flex:0 0 280px;border-right:none}',
        // Panel headers — light grey, same tone as footer
        '.og-cmgr-phd{background:#f0f0f0;color:#555;padding:8px 12px;display:flex;',
            'align-items:center;gap:5px;flex-shrink:0;border-bottom:1px solid #e2e2e2}',
        '.og-cmgr-phd-title{font-size:12px;font-weight:600;letter-spacing:.1px}',
        '.og-cmgr-phd-count{font-size:12px;font-weight:400;color:#888}',
        '.og-cmgr-phd-hint{margin-left:auto;font-size:11px;font-weight:400;color:#aaa;letter-spacing:.1px}',
        '.og-cmgr-phd-actions{margin-left:auto;display:flex;align-items:center;gap:4px}',
        '.og-cmgr-phd-action{font-size:11px;color:#1B75BC;cursor:pointer;font-weight:400;text-decoration:none}',
        '.og-cmgr-phd-action:hover{text-decoration:underline}',
        '.og-cmgr-phd-actdiv{font-size:11px;color:#ccc}',
        // Search
        '.og-cmgr-search-wrap{padding:8px;border-bottom:1px solid #ebebeb;flex-shrink:0}',
        '.og-cmgr-search{width:100%;box-sizing:border-box;padding:6px 10px;border:1px solid #d0d0d0;',
            'border-radius:4px;font-size:12px;outline:none;color:#333;background:#fff}',
        '.og-cmgr-search:focus{border-color:#bbb}',
        // Lists
        '.og-cmgr-list{flex:1;overflow-y:auto;overflow-x:hidden}',
        // Letter dividers
        '.og-cmgr-letter{padding:3px 12px;font-size:10px;font-weight:700;color:#999;',
            'background:#f7f7f7;border-bottom:1px solid #eee;letter-spacing:.3px; display:none;}',
        // Left items
        '.og-cmgr-litem{display:flex;align-items:center;gap:10px;padding:7px 12px;cursor:pointer;',
            'user-select:none;-webkit-user-select:none;border-bottom:1px solid #f5f5f5}',
        '.og-cmgr-litem:hover{background:#f0f6ff}',
        // Checkbox
        '.og-cmgr-chk{width:15px;height:15px;border:2px solid #ccc;border-radius:3px;flex-shrink:0;',
            'display:flex;align-items:center;justify-content:center;font-size:10px;',
            'color:transparent;line-height:1;transition:background .1s,border-color .1s}',
        '.og-cmgr-litem.og-cmgr-vis .og-cmgr-chk{background:#1B75BC;border-color:#1B75BC;color:#fff}',
        // Left col name
        '.og-cmgr-lname{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;color:#333}',
        '.og-cmgr-litem.og-cmgr-vis .og-cmgr-lname{color:#1B75BC;font-weight:500}',
        // Right items
        '.og-cmgr-ritem{display:flex;align-items:center;gap:6px;padding:7px 10px;cursor:grab;',
            'border-bottom:1px solid #f0f0f0;background:#fff;user-select:none;-webkit-user-select:none}',
        '.og-cmgr-ritem:hover{background:#fafafa}',
        '.og-cmgr-ritem.og-cmgr-dragover{border-top:2px solid #1B75BC;margin-top:-1px}',
        '.og-cmgr-ritem.og-cmgr-dragging{opacity:.3}',
        '.og-cmgr-handle{color:#d0d0d0;font-size:15px;line-height:1;flex-shrink:0}',
        '.og-cmgr-num{width:18px;font-size:11px;color:#c0c0c0;flex-shrink:0;text-align:right}',
        '.og-cmgr-rname{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;color:#333}',
        '.og-cmgr-rm{color:#ccc;cursor:pointer;font-size:17px;line-height:1;flex-shrink:0;padding:0 2px}',
        '.og-cmgr-rm:hover{color:#cc3333}',
        // Nested quick-action sub-list (opt-in, e.g. the Tasks "Actions" column)
        '.og-cmgr-sub-toggle{color:#777;cursor:pointer;font-size:19px;line-height:1;flex-shrink:0;',
            'padding:2px 6px;border-radius:3px;display:flex;align-items:center;justify-content:center}',
        '.og-cmgr-sub-toggle:hover{color:#1B75BC;background:#eaf2fb}',
        '.og-cmgr-sub-list{background:#fafbfc;border-bottom:1px solid #f0f0f0}',
        '.og-cmgr-subitem{display:flex;align-items:center;gap:6px;padding:6px 10px 6px 30px;cursor:grab;',
            'border-bottom:1px solid #f2f2f2;background:#fafbfc;user-select:none;-webkit-user-select:none}',
        '.og-cmgr-subitem:last-child{border-bottom:none}',
        '.og-cmgr-subitem:hover{background:#f2f6fb}',
        '.og-cmgr-subitem.og-cmgr-dragover{border-top:2px solid #1B75BC;margin-top:-1px}',
        '.og-cmgr-subitem.og-cmgr-dragging{opacity:.3}',
        // Locked (pinned) columns: reorderable but not removable
        '.og-cmgr-lock{color:#ccc;font-size:12px;line-height:1;flex-shrink:0;padding:0 3px}',
        '.og-cmgr-litem.og-cmgr-locked{cursor:default}',
        '.og-cmgr-litem.og-cmgr-locked:hover{background:transparent}',
        '.og-cmgr-litem.og-cmgr-locked .og-cmgr-chk{background:#ccc;border-color:#ccc;color:#fff}',
        // Right panel bottom hint
        '.og-cmgr-rhint{padding:8px 12px;font-size:11px;color:#bbb;font-style:italic;',
            'border-top:1px solid #f0f0f0;flex-shrink:0;background:#fafafa}',
        // Empty state
        '.og-cmgr-empty{padding:24px 12px;font-size:12px;color:#bbb;text-align:center;font-style:italic}',
        // Toolbar button (icon only, right-aligned)
        '.og-cmgr-tbar-btn .x-btn-center em{padding:0 6px!important}',
        '.og-cmgr-tbar-btn button{background:none!important}',
		'.og-cmgr-tbar-btn .icon-settings{font-size:17px;color:#666;}',
        // Footer
        '.og-cmgr-footer{display:flex;align-items:center;padding:10px 14px;',
            'border-top:1px solid #e0e0e0;background:#f8f8f8;flex-shrink:0}',
        '.og-cmgr-footer-r{margin-left:auto;display:flex;gap:6px}'
    ].join('');
    document.head.appendChild(s);
}());

// ─── HTML encoding ────────────────────────────────────────────────────────────
function he(s) {
    return String(s || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ─── localStorage helpers ─────────────────────────────────────────────────────
function storageSave(gridId, colState) {
    try { localStorage.setItem(STORAGE_PREFIX + gridId, JSON.stringify(colState)); } catch (e) {}
}
function storageLoad(gridId) {
    try {
        var v = localStorage.getItem(STORAGE_PREFIX + gridId);
        return v ? JSON.parse(v) : null;
    } catch (e) { return null; }
}

// ─── Column model helpers ─────────────────────────────────────────────────────
function stripHtml(h) {
    return (h || '').replace(/<[^>]+>/g, '').trim();
}

var FIXED_COL_IDS = { 'checker': true, 'expand-handler': true, 'icon': true };

function getManageableCols(cm) {
    var cols = [];
    for (var i = 0; i < cm.config.length; i++) {
        var c = cm.config[i];
        if (c.hideable === false) continue;
        if (FIXED_COL_IDS[c.id]) continue;
        var hdr = stripHtml(c.header);
        if (!hdr) continue;
        cols.push({
            id:     c.id || c.dataIndex || ('col' + i),
            header: hdr,
            hidden: c.hidden === true
        });
    }
    return cols;
}

// Rebuilds cm.config in the new order.  Pass fireColumnMove=true for user-initiated
// changes (fires configchange + columnmove); omit for silent init from localStorage.
function applyStateToGrid(grid, colState, fireColumnMove) {
    var cm = grid.getColumnModel();

    // Split into fixed (non-manageable) and manageable
    var fixed  = [];
    var manMap = {};
    for (var i = 0; i < cm.config.length; i++) {
        var c = cm.config[i];
        if (c.hideable === false || FIXED_COL_IDS[c.id]) {
            fixed.push(c);
        } else if (stripHtml(c.header)) {
            manMap[c.id || c.dataIndex] = c;
        }
    }

    // Rebuild: fixed first, then manageable in saved order
    var newCfg = fixed.slice();
    var seen   = {};
    for (var j = 0; j < colState.length; j++) {
        var s   = colState[j];
        var col = manMap[s.id];
        if (col) {
            col.hidden = s.hidden === true;
            newCfg.push(col);
            seen[s.id] = true;
        }
    }
    // Append any manageable cols introduced after the state was saved
    for (var id in manMap) {
        if (manMap.hasOwnProperty(id) && !seen[id]) {
            newCfg.push(manMap[id]);
        }
    }

    if (fireColumnMove) {
        // User-initiated apply: use setConfig() so it rebuilds lookup, clears totalWidth,
        // and fires configchange — the view refreshes immediately with the new layout.
        cm.setConfig(newCfg);
        grid.fireEvent('columnmove', 0, 0);
    } else {
        // Silent init path (runs pre-render from initComponent): rebuild lookup and clear
        // totalWidth (fixes last-cell styling) but do NOT fire configchange.  Firing it
        // would trigger view.refresh() and cascade a doLayout up to the parent container —
        // corrupting sibling panels pre-rendered in the same workspace context.  Because
        // this runs before the view paints, the header row and data rows are both built
        // from this cm.config on the first render, so no refresh is needed to align them.
        cm.config = newCfg;
        cm.lookup = {};
        delete cm.totalWidth;
        for (var k = 0; k < newCfg.length; k++) {
            var ck = newCfg[k];
            if (typeof ck.id === 'undefined') { ck.id = k; } // mirror ExtJS setConfig behaviour
            cm.lookup[ck.id] = ck;                            // store object, not index
        }
    }
}

// ─── ColumnManagerWindow ──────────────────────────────────────────────────────
// Accepts either an ExtJS GridPanel (existing behaviour) or a plain options object:
//   { id, columns: [{id, header, hidden, subItems: [{key, label}]}], onApply: fn(colState), owner? }
// `subItems` is optional per-column and opt-in: only columns that supply it get a
// nested, separately-draggable mini reorder-list in the right panel. On Apply, the
// resulting order is returned as `subOrder` (array of keys) on that column's colState
// entry. Only "generic mode" (plain options object) callers use this; grid mode never
// supplies subItems, so it is entirely unaffected.
og.ColumnManagerWindow = function (source) {
    var me   = this;
    me.dragId = null;
    me._subDragKey = null;
    me._expandedSubIds = {};

    var allCols;
    if (typeof source.getColumnModel === 'function') {
        // Grid mode — existing behaviour unchanged
        me._owner    = source;
        me._sourceId = source.id;
        me._applyFn  = null;
        me._lockedIds = {};
        allCols = getManageableCols(source.getColumnModel());
    } else {
        // Generic mode — plain options object
        me._owner    = source.owner || source;
        me._sourceId = source.id;
        me._applyFn  = source.onApply;
        // lockedIds: map of colId → truthy for columns that are always visible.
        // They can be reordered but not removed (no × button, ignored by "Remove all").
        me._lockedIds = source.lockedIds || {};
        allCols      = source.columns.slice();
    }

    // Left panel: all cols sorted alphabetically
    me.leftCols = allCols.slice().sort(function (a, b) {
        return a.header.toLowerCase().localeCompare(b.header.toLowerCase());
    });

    // Right panel: currently visible cols in their display order.
    // Locked columns are always visible regardless of their stored hidden flag.
    me.rightCols = allCols.filter(function (c) { return !c.hidden || me._lockedIds[c.id]; });

    me._syncVisSet();
    me._open();
};

og.ColumnManagerWindow.prototype = {

    _syncVisSet: function () {
        var me = this;
        me.visSet = {};
        for (var i = 0; i < me.rightCols.length; i++) {
            me.visSet[me.rightCols[i].id] = true;
        }
    },

    // ── Open window ────────────────────────────────────────────────────────────
    _open: function () {
        var me  = this;
        var uid = me._sourceId.replace(/[^a-zA-Z0-9]/g, '_');
        me.uid  = uid;

        var html = [
            '<div class="og-cmgr-wrap">',
            '<div class="og-cmgr-split">',
            // Left panel — available columns
            '<div class="og-cmgr-panel">',
            '<div class="og-cmgr-phd">',
            '<span class="og-cmgr-phd-title">', he(lang('available columns')), '</span>',
            '<span class="og-cmgr-phd-count" id="ogcmgr-lc-', uid, '">(', me.leftCols.length, ')</span>',
            '<span class="og-cmgr-phd-actions">',
            '<a class="og-cmgr-phd-action" id="ogcmgr-selall-', uid, '">', he(lang('select all')), '</a>',
            '<span class="og-cmgr-phd-actdiv">|</span>',
            '<a class="og-cmgr-phd-action" id="ogcmgr-clrall-', uid, '">', he(lang('remove all')), '</a>',
            '</span>',
            '</div>',
            '<div class="og-cmgr-search-wrap">',
            '<input type="text" class="og-cmgr-search" id="ogcmgr-s-', uid,
                '" placeholder="', he(lang('search')), '&hellip;" />',
            '</div>',
            '<div class="og-cmgr-list" id="ogcmgr-ll-', uid, '"></div>',
            '</div>',
            // Right panel — visible columns
            '<div class="og-cmgr-panel og-cmgr-panel-r">',
            '<div class="og-cmgr-phd">',
            '<span class="og-cmgr-phd-title">', he(lang('visible columns')), '</span>',
            '<span class="og-cmgr-phd-count" id="ogcmgr-vc-', uid, '">(', me.rightCols.length, ')</span>',
            '<span class="og-cmgr-phd-hint">', he(lang('drag to reorder')), '</span>',
            '</div>',
            '<div class="og-cmgr-list" id="ogcmgr-rl-', uid, '"></div>',
            '<div class="og-cmgr-rhint">', he(lang('select columns hint')), '</div>',
            '</div>',
            '</div>',
            // Footer — native buttons so we can style freely
            '<div class="og-cmgr-footer">',
            '<div class="og-cmgr-footer-r">',
            '<button class="btn btn-sm" id="ogcmgr-cancel-', uid, '">', he(lang('cancel')), '</button>',
            '<button class="btn btn-sm btn-primary" id="ogcmgr-apply-', uid, '">', he(lang('apply')), '</button>',
            '</div>',
            '</div>',
            '</div>'
        ].join('');

        var win = new Ext.Window({
            title:     lang('manage columns'),
            width:     720,
            height:    500,
            modal:     true,
            resizable: true,
            closable:  true,
            plain:     true,
            cls:       'og-cmgr-win',
            html:      html,
            bodyStyle: 'padding:0;overflow:hidden'
        });

        me.win = win;
        me._owner._cmgrWinOpen = true;
        win.on('close', function () { me._owner._cmgrWinOpen = false; });
        win.show();

        document.getElementById('ogcmgr-selall-' + uid).onclick = function () { me._onSelectAll(); };
        document.getElementById('ogcmgr-clrall-' + uid).onclick = function () { me._onRemoveAll(); };
        document.getElementById('ogcmgr-cancel-' + uid).onclick = function () { win.close(); };
        document.getElementById('ogcmgr-apply-'  + uid).onclick = function () { if (me._onApply()) win.close(); };

        me._renderLeftList();
        me._renderRightList();
        me._setupSearch();
        me._setupRightDnD();
    },

    // ── Left list ──────────────────────────────────────────────────────────────
    _renderLeftList: function () {
        var me  = this;
        var el  = document.getElementById('ogcmgr-ll-' + me.uid);
        if (!el) return;

        var html   = [];
        var letter = '';

        for (var i = 0; i < me.leftCols.length; i++) {
            var col  = me.leftCols[i];
            var fst  = col.header.charAt(0).toUpperCase();
            if (fst !== letter) {
                letter = fst;
                html.push('<div class="og-cmgr-letter">' + he(letter) + '</div>');
            }
            var vis    = me.visSet[col.id] ? true : false;
            var locked = me._lockedIds[col.id] ? true : false;
            html.push(
                '<div class="og-cmgr-litem', (vis ? ' og-cmgr-vis' : ''), (locked ? ' og-cmgr-locked' : ''), '"',
                ' data-col-id="', he(col.id), '"',
                (locked ? ' title="' + he(lang('column always visible')) + '"' : ''), '>',
                '<span class="og-cmgr-chk">', (vis ? '&#10003;' : ''), '</span>',
                '<span class="og-cmgr-lname" title="', he(col.header), '">', he(col.header), '</span>',
                (locked ? '<i class="icon-lock og-cmgr-lock"></i>' : ''),
                '</div>'
            );
        }

        el.innerHTML = html.join('');

        el.onclick = function (e) {
            var t = e.target || e.srcElement;
            while (t && t !== el) {
                if (t.className && t.className.indexOf('og-cmgr-litem') !== -1) break;
                t = t.parentNode;
            }
            if (!t || t === el) return;
            var colId = t.getAttribute('data-col-id');
            if (colId) me._toggleColumn(colId);
        };
    },

    _updateLeftItem: function (colId, isVis) {
        var el = document.getElementById('ogcmgr-ll-' + this.uid);
        if (!el) return;
        var item = el.querySelector('[data-col-id="' + colId + '"]');
        if (!item) return;
        var chk = item.querySelector('.og-cmgr-chk');
        if (isVis) {
            item.className = 'og-cmgr-litem og-cmgr-vis';
            if (chk) chk.innerHTML = '&#10003;';
        } else {
            item.className = 'og-cmgr-litem';
            if (chk) chk.innerHTML = '';
        }
    },

    // ── Right list ─────────────────────────────────────────────────────────────
    _renderRightList: function () {
        var me = this;
        var el = document.getElementById('ogcmgr-rl-' + me.uid);
        if (!el) return;

        var html = [];
        if (me.rightCols.length === 0) {
            html.push('<div class="og-cmgr-empty">', he(lang('no visible columns')), '</div>');
        } else {
            for (var i = 0; i < me.rightCols.length; i++) {
                var col      = me.rightCols[i];
                var hasSub   = !!(col.subItems && col.subItems.length);
                var expanded = hasSub && !!me._expandedSubIds[col.id];
                var locked   = me._lockedIds[col.id] ? true : false;
                html.push(
                    '<div class="og-cmgr-ritem', (hasSub ? ' og-cmgr-has-sub' : ''), '" draggable="true" data-col-id="', he(col.id), '">',
                    '<span class="og-cmgr-handle">&#8801;</span>',
                    '<span class="og-cmgr-num">', (i + 1), '</span>',
                    '<span class="og-cmgr-rname" title="', he(col.header), '">', he(col.header), '</span>'
                );
                if (hasSub) {
                    html.push(
                        '<span class="og-cmgr-sub-toggle" data-toggle-id="', he(col.id), '" title="', he(lang('drag to reorder')), '">',
                        (expanded ? '&#9662;' : '&#9656;'),
                        '</span>'
                    );
                }
                html.push(
                    // Locked columns show a lock instead of the × remove button
                    (locked
                        ? '<i class="icon-lock og-cmgr-lock" title="' + he(lang('column always visible')) + '"></i>'
                        : '<span class="og-cmgr-rm" data-col-id="' + he(col.id) + '" title="' + he(lang('hide')) + '">&#215;</span>'),
                    '</div>'
                );
                if (hasSub && expanded) {
                    html.push('<div class="og-cmgr-sub-list" data-parent-id="', he(col.id), '">');
                    for (var j = 0; j < col.subItems.length; j++) {
                        var sub = col.subItems[j];
                        html.push(
                            '<div class="og-cmgr-subitem" draggable="true" data-parent-id="', he(col.id), '" data-sub-key="', he(sub.key), '">',
                            '<span class="og-cmgr-handle">&#8801;</span>',
                            '<span class="og-cmgr-num">', (j + 1), '</span>',
                            '<span class="og-cmgr-rname" title="', he(sub.label), '">', he(sub.label), '</span>',
                            '</div>'
                        );
                    }
                    html.push('</div>');
                }
            }
        }
        el.innerHTML = html.join('');

        // Update count in header
        var vc = document.getElementById('ogcmgr-vc-' + me.uid);
        if (vc) vc.innerHTML = '(' + me.rightCols.length + ')';

        // Remove-button / sub-list expand-toggle click delegation
        el.onclick = function (e) {
            var t = e.target || e.srcElement;
            if (t.className && t.className.indexOf('og-cmgr-sub-toggle') !== -1) {
                var toggleId = t.getAttribute('data-toggle-id');
                if (toggleId) me._toggleSubExpanded(toggleId);
                return;
            }
            if (t.className && t.className.indexOf('og-cmgr-rm') !== -1) {
                var colId = t.getAttribute('data-col-id');
                if (colId) me._toggleColumn(colId);
            }
        };
    },

    // ── Nested sub-list expand/collapse ───────────────────────────────────────
    _toggleSubExpanded: function (colId) {
        var me = this;
        me._expandedSubIds[colId] = !me._expandedSubIds[colId];
        me._renderRightList();
        me._setupRightDnD();
    },

    // ── Toggle visibility ──────────────────────────────────────────────────────
    _toggleColumn: function (colId) {
        var me  = this;
        if (me._lockedIds[colId]) return; // locked columns are always visible
        var vis = me.visSet[colId] ? true : false;

        if (vis) {
            me.rightCols = me.rightCols.filter(function (c) { return c.id !== colId; });
            delete me.visSet[colId];
        } else {
            // Find the col object in leftCols and append to right panel
            for (var i = 0; i < me.leftCols.length; i++) {
                if (me.leftCols[i].id === colId) {
                    me.rightCols.push(me.leftCols[i]);
                    break;
                }
            }
            me.visSet[colId] = true;
        }

        me._updateLeftItem(colId, !vis);
        me._renderRightList();
        me._setupRightDnD();
    },

    // ── Search ─────────────────────────────────────────────────────────────────
    _setupSearch: function () {
        var me    = this;
        var input = document.getElementById('ogcmgr-s-' + me.uid);
        if (!input) return;
        var fn = function () { me._filterLeft(this.value); };
        input.onkeyup  = fn;
        input.oninput  = fn;
    },

    _filterLeft: function (q) {
        var el = document.getElementById('ogcmgr-ll-' + this.uid);
        if (!el) return;
        q = (q || '').toLowerCase().trim();

        var items   = el.querySelectorAll('.og-cmgr-litem');
        var letters = el.querySelectorAll('.og-cmgr-letter');

        var i, j;
        if (!q) {
            for (i = 0; i < items.length; i++)   items[i].style.display   = '';
            for (j = 0; j < letters.length; j++)  letters[j].style.display = '';
            return;
        }

        // Show items matching query; track which letter dividers are needed
        var visLetters = {};
        for (i = 0; i < items.length; i++) {
            var nameEl = items[i].querySelector('.og-cmgr-lname');
            var name   = nameEl ? nameEl.textContent.toLowerCase() : '';
            var show   = name.indexOf(q) !== -1;
            items[i].style.display = show ? '' : 'none';
            if (show) {
                var prev = items[i].previousSibling;
                while (prev) {
                    if (prev.className && prev.className.indexOf('og-cmgr-letter') !== -1) {
                        visLetters[prev.textContent] = true;
                        break;
                    }
                    prev = prev.previousSibling;
                }
            }
        }
        for (j = 0; j < letters.length; j++) {
            letters[j].style.display = visLetters[letters[j].textContent] ? '' : 'none';
        }
    },

    // ── Right-panel drag and drop ──────────────────────────────────────────────
    _setupRightDnD: function () {
        var me = this;
        var el = document.getElementById('ogcmgr-rl-' + me.uid);
        if (!el) return;

        function clearMarkers() {
            var items = el.querySelectorAll('.og-cmgr-ritem');
            for (var i = 0; i < items.length; i++) {
                items[i].classList.remove('og-cmgr-dragover');
                items[i].classList.remove('og-cmgr-dragging');
            }
        }

        function findItem(target) {
            while (target && target !== el) {
                if (target.className && target.className.indexOf('og-cmgr-ritem') !== -1) return target;
                target = target.parentNode;
            }
            return null;
        }

        function idxOf(colId) {
            for (var i = 0; i < me.rightCols.length; i++) {
                if (me.rightCols[i].id === colId) return i;
            }
            return -1;
        }

        el.ondragstart = function (e) {
            var item = findItem(e.target);
            if (!item) { e.preventDefault(); return; }
            // Prevent dragging via the × button
            if ((e.target.className || '').indexOf('og-cmgr-rm') !== -1) {
                e.preventDefault(); return;
            }
            me.dragId = item.getAttribute('data-col-id');
            item.classList.add('og-cmgr-dragging');
            e.dataTransfer.setData('text/plain', me.dragId);
            e.dataTransfer.effectAllowed = 'move';
        };

        el.ondragover = function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            var item = findItem(e.target);
            clearMarkers();
            if (item && item.getAttribute('data-col-id') !== me.dragId) {
                item.classList.add('og-cmgr-dragover');
            }
        };

        el.ondragleave = function (e) {
            if (!el.contains(e.relatedTarget)) clearMarkers();
        };

        el.ondrop = function (e) {
            e.preventDefault();
            var targetItem = findItem(e.target);
            clearMarkers();
            if (!targetItem || !me.dragId) return;
            var targetId = targetItem.getAttribute('data-col-id');
            if (!targetId || targetId === me.dragId) return;

            var fromIdx = idxOf(me.dragId);
            var toIdx   = idxOf(targetId);
            if (fromIdx === -1 || toIdx === -1) return;

            var moved = me.rightCols.splice(fromIdx, 1)[0];
            me.rightCols.splice(toIdx, 0, moved);

            me._renderRightList();
            me._setupRightDnD();
        };

        el.ondragend = function () {
            clearMarkers();
            me.dragId = null;
        };

        // Wire up any expanded nested sub-lists (opt-in, only columns with subItems).
        var subLists = el.querySelectorAll('.og-cmgr-sub-list');
        for (var sl = 0; sl < subLists.length; sl++) {
            me._setupSubDnD(subLists[sl].getAttribute('data-parent-id'));
        }
    },

    // ── Nested sub-list drag and drop (scoped to one column's mini list) ──────
    // Mirrors _setupRightDnD's mechanics but reorders col.subItems in place and
    // stops propagation so top-level column dragging never sees these events.
    _setupSubDnD: function (parentColId) {
        var me = this;
        var subEl = document.querySelector('.og-cmgr-sub-list[data-parent-id="' + parentColId + '"]');
        if (!subEl) return;

        var col = null;
        for (var i = 0; i < me.rightCols.length; i++) {
            if (me.rightCols[i].id === parentColId) { col = me.rightCols[i]; break; }
        }
        if (!col || !col.subItems) return;

        function clearMarkers() {
            var items = subEl.querySelectorAll('.og-cmgr-subitem');
            for (var i = 0; i < items.length; i++) {
                items[i].classList.remove('og-cmgr-dragover');
                items[i].classList.remove('og-cmgr-dragging');
            }
        }

        function findItem(target) {
            while (target && target !== subEl) {
                if (target.className && target.className.indexOf('og-cmgr-subitem') !== -1) return target;
                target = target.parentNode;
            }
            return null;
        }

        function idxOf(key) {
            for (var i = 0; i < col.subItems.length; i++) {
                if (col.subItems[i].key === key) return i;
            }
            return -1;
        }

        subEl.ondragstart = function (e) {
            e.stopPropagation();
            var item = findItem(e.target);
            if (!item) { e.preventDefault(); return; }
            me._subDragKey = item.getAttribute('data-sub-key');
            item.classList.add('og-cmgr-dragging');
            e.dataTransfer.setData('text/plain', me._subDragKey);
            e.dataTransfer.effectAllowed = 'move';
        };

        subEl.ondragover = function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.dataTransfer.dropEffect = 'move';
            var item = findItem(e.target);
            clearMarkers();
            if (item && item.getAttribute('data-sub-key') !== me._subDragKey) {
                item.classList.add('og-cmgr-dragover');
            }
        };

        subEl.ondragleave = function (e) {
            if (!subEl.contains(e.relatedTarget)) clearMarkers();
        };

        subEl.ondrop = function (e) {
            e.preventDefault();
            e.stopPropagation();
            var targetItem = findItem(e.target);
            clearMarkers();
            if (!targetItem || !me._subDragKey) return;
            var targetKey = targetItem.getAttribute('data-sub-key');
            if (!targetKey || targetKey === me._subDragKey) return;

            var fromIdx = idxOf(me._subDragKey);
            var toIdx   = idxOf(targetKey);
            if (fromIdx === -1 || toIdx === -1) return;

            var moved = col.subItems.splice(fromIdx, 1)[0];
            col.subItems.splice(toIdx, 0, moved);

            me._renderRightList();
            me._setupRightDnD();
        };

        subEl.ondragend = function (e) {
            e.stopPropagation();
            clearMarkers();
            me._subDragKey = null;
        };
    },

    // ── Apply ──────────────────────────────────────────────────────────────────
    _onApply: function () {
        var me = this;

        // Guard against applying with no visible columns: warn the user and keep the window open.
        if (me.rightCols.length === 0) {
            og.msg(lang('warning'), lang('select at least one column'), 4, 'err');
            return false;
        }

        // Build full state: visible cols first (in right-panel order), hidden cols after.
        // Columns carrying subItems (opt-in nested reorder list) report their current
        // sub-order back as `subOrder` (array of keys) alongside id/hidden.
        var colState = [];
        for (var i = 0; i < me.rightCols.length; i++) {
            var visEntry = { id: me.rightCols[i].id, hidden: false };
            if (me.rightCols[i].subItems) {
                visEntry.subOrder = me.rightCols[i].subItems.map(function (s) { return s.key; });
            }
            colState.push(visEntry);
        }
        for (var j = 0; j < me.leftCols.length; j++) {
            if (!me.visSet[me.leftCols[j].id]) {
                var hidEntry = { id: me.leftCols[j].id, hidden: true };
                if (me.leftCols[j].subItems) {
                    hidEntry.subOrder = me.leftCols[j].subItems.map(function (s) { return s.key; });
                }
                colState.push(hidEntry);
            }
        }

        if (me._applyFn) {
            return me._applyFn(colState) !== false;
        }

        // Grid mode: persist to localStorage and apply to column model
        storageSave(me._owner.id, colState);
        applyStateToGrid(me._owner, colState, true);
        return true;
    },

    // ── Select all ────────────────────────────────────────────────────────────
    _onSelectAll: function () {
        var me = this;
        for (var i = 0; i < me.leftCols.length; i++) {
            var col = me.leftCols[i];
            if (!me.visSet[col.id]) {
                me.rightCols.push(col);
                me.visSet[col.id] = true;
            }
        }
        me._renderLeftList();
        me._renderRightList();
        me._setupSearch();
        me._setupRightDnD();
    },

    // ── Remove all ────────────────────────────────────────────────────────────
    _onRemoveAll: function () {
        var me    = this;
        var input = document.getElementById('ogcmgr-s-' + me.uid);
        if (input) { input.value = ''; }
        // Keep locked columns — they are always visible and cannot be removed
        me.rightCols = me.rightCols.filter(function (c) { return me._lockedIds[c.id]; });
        me._syncVisSet();
        me._renderLeftList();
        me._filterLeft('');
        me._renderRightList();
        me._setupSearch();
        me._setupRightDnD();
    }
};

// ─── GridPanel prototype overrides ───────────────────────────────────────────
var _origInitComponent = Ext.grid.GridPanel.prototype.initComponent;
var _origAfterRender   = Ext.grid.GridPanel.prototype.afterRender;
var _origApplyState    = Ext.grid.GridPanel.prototype.applyState;

Ext.override(Ext.grid.GridPanel, {

    // A guistate blob copied from the role defaults for a brand new user carries an
    // ogRoleDefaults flag (see application/helpers/role_default_list_config.php) and describes
    // the COMPLETE column layout of the grid.  Stock applyState only touches the columns the
    // state names, so columns that exist in this install but not in the shipped defaults
    // (dimension columns, custom properties, member type columns) would stay visible and pile
    // up at the end of the list.  While the flag is present, "not in the state" means hidden.
    //
    // The flag lives only in that first copy: getState() rebuilds the blob without it, so from
    // the moment the user rearranges the grid himself his own state is authoritative again and
    // columns added later behave as before (visible when show_in_lists / show_as_column is on).
    applyState: function (state) {
        _origApplyState.call(this, state);

        if (!state || state.ogRoleDefaults !== true || !Ext.isArray(state.columns)) return;

        var cm = this.colModel;
        if (!cm || !cm.config) return;

        var listed = Object.create(null);
        for (var i = 0; i < state.columns.length; i++) {
            listed[state.columns[i].id] = true;
        }

        for (var j = 0; j < cm.config.length; j++) {
            var c = cm.config[j];
            if (c.hidden || listed[c.id]) continue;
            // Never hide what the user could not bring back: structural columns (row checkbox,
            // icon, row actions) are not hideable and/or have no header to show in the manager.
            if (c.hideable === false || FIXED_COL_IDS[c.id] || !stripHtml(c.header)) continue;
            c.hidden = true;
        }
    },

    // Inject fill + gear button into the tbar config BEFORE the panel renders.
    // Doing it here avoids a post-render tbar.addFill() call, which triggers
    // doLayout() and cascades up the container tree — corrupting the body height
    // of sibling panels that are pre-rendered in the same workspace context.
    initComponent: function () {
        var grid = this;

        if (!grid.manageColumnsDisabled && grid.tbar) {
            var cmCandidate = grid.cm || grid.colModel;
            if (cmCandidate && cmCandidate.config && cmCandidate.config.length > 0) {
                if (getManageableCols(cmCandidate).length > 0) {
                    var btnId = 'og-cmgr-btn-' + (grid.id || '').replace(/[^a-zA-Z0-9]/g, '_');
                    var newItems = ['->', {
                        id:      btnId,
                        text:    '<i class="icon-settings"></i>',
                        iconCls: 'og-cmgr-tbar-btn',
                        handler: function () {
                            if (grid._cmgrWinOpen) return;
                            new og.ColumnManagerWindow(grid);
                        }
                    }];
                    if (Ext.isArray(grid.tbar)) {
                        // plain array config — Ext.Panel.initComponent converts this to
                        // a Toolbar, so just append to the array before that happens
                        grid.tbar = grid.tbar.concat(newItems);
                    } else if (grid.tbar instanceof Ext.Toolbar) {
                        // pre-created Toolbar instance — Toolbar.initComponent has already
                        // run, converting this.items → this.buttons (the deferred list that
                        // Toolbar.afterRender processes).  Calling tbar.add() now would
                        // try to render items to this.tr which doesn't exist yet.
                        // Appending to this.buttons is safe: afterRender picks it up once
                        // the toolbar's DOM is ready.
                        if (!grid.tbar.buttons) { grid.tbar.buttons = []; }
                        grid.tbar.buttons = grid.tbar.buttons.concat(newItems);
                    } else if (Ext.isArray(grid.tbar.items)) {
                        // config object whose items is still a plain array
                        grid.tbar.items = grid.tbar.items.concat(newItems);
                    }
                    grid._cmgrBtnId = btnId;

                    // Suppress the legacy "Columns" show/hide submenu from column header
                    // menus. GridView.initUI reads g.enableColumnHide (g = grid instance),
                    // so this must be set on the grid before view.init() runs in afterRender.
                    grid.enableColumnHide = false;
                }
            }
        }

        _origInitComponent.call(this);

        // Apply persisted column state (order + visibility) BEFORE the view renders.
        // The ColumnModel already exists at this point (GridPanel.initComponent created
        // it), but onRender/afterRender hasn't painted any DOM yet, so the header row and
        // the data rows are both built from the same cm.config on the very first paint.
        //
        // This MUST happen pre-render: if applied in afterRender instead, the headers are
        // already drawn in the original order, and the next async store-load refresh
        // (Ext GridView.refresh() runs with headersToo=false) re-renders only the body rows
        // in the new order — leaving headers and data misaligned until a later full refresh.
        if (grid._cmgrBtnId) {
            var savedState = storageLoad(grid.id);
            if (savedState && savedState.length > 0) {
                applyStateToGrid(grid, savedState);
            }
        }
    },

    afterRender: function () {
        _origAfterRender.call(this);
        this._cmgrInit();
    },

    _cmgrInit: function () {
        var grid = this;

        if (grid.manageColumnsDisabled) return;
        // Button was not injected (no tbar, no manageable cols, or cm not ready at initComponent time)
        if (!grid._cmgrBtnId) return;

        // Note: persisted column state is applied pre-render in initComponent (not here),
        // so the header row and data rows stay aligned on the first paint. See initComponent.

        // ── Wire up tooltip popover ───────────────────────────────────────────
        if (og.member_list_toolbar_item_popover) {
            var tbar = grid.getTopToolbar();
            if (tbar) {
                og.member_list_toolbar_item_popover('#' + tbar.id + ' #' + grid._cmgrBtnId, lang('manage columns tooltip'));
            }
        }
    }
});

}());
