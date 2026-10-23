/**
 * drawing.js
 *
 * This module holds the rendering logic for groups and tasks
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */

// true while a manual "load next" or "load all" is in progress; suppresses scroll auto-loading
ogTasks.manualGroupLoading = false;

// Default relative order of the 6 quick actions in the merged actions column
// (icon row left-to-right, and "..." overflow popover top-to-bottom). Overridden by
// og.config.tasks_columns_config.actionsOrder once the user reorders them via the
// Columns modal's nested sub-list.
ogTasks.DEFAULT_ACTIONS_ORDER = ['add_sub_task', 'edit', 'mark_as_started', 'complete', 'quick_time', 'time'];

//************************************
//*		Main function
//************************************

ogTasks.draw = function () {
	
	// reset list selections in cache and current tasks
	ogTasks.resetSelection();

    //first load the groups from server
    if (!ogTasks.Groups.loaded) {
		const GROUPS_CACHE_TTL_MS = 5 * 60 * 1000;
		const contextChanged = ogTasks.previousContext !== undefined && ogTasks.previousContext !== og.contextManager.plainContext();
		const cacheExpired = ogTasks.Groups.loadedAt && (Date.now() - ogTasks.Groups.loadedAt) > GROUPS_CACHE_TTL_MS;
		const useCachedGroups = !contextChanged && !cacheExpired && ogTasks.Groups.length > 0;

		if (!useCachedGroups) {
			ogTasks.viewingTaskId = null;
			ogTasks.previousContext = undefined;
			ogTasks.resetPaginationVariables();
			if (ogTasks.Groups) ogTasks.Groups.loadedAt = undefined;
		}
    	// If returning from a task view and groups are still in memory, skip the server fetch:
    	// re-render from cached data and refresh only the viewed task asynchronously.
    	if (useCachedGroups && ogTasks.viewingTaskId && ogTasks.Groups.length > 0) {
    		var taskId = ogTasks.viewingTaskId;
    		ogTasks.viewingTaskId = null;
    		ogTasks.Groups.loaded = true; // allow rendering to fall through
    		setTimeout(function() { ogTasks.UpdateTask(taskId, true); }, 0);
    		// fall through to re-render all groups from in-memory data
    		// (savedScrollTop and savedExpandedSubtasks were captured in onTaskLinkClick)
    	} else {
    		ogTasks.resetPaginationVariables();
    		ogTasks.getGroups();
    		return;
    	}
    }
    ogTasks.Groups.loaded = false;
    ogTasks.LevelMultiplier = 20;

    if (typeof ogTasks.userPreferences.showTasksListAsGantt != 'undefined' && ogTasks.userPreferences.showTasksListAsGantt) {
        return;
    }

    var start = new Date();

    for (var i = 0; i < this.Tasks.length; i++)
        this.Tasks[i].divInfo = [];

    var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
    var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');

    if (!bottomToolbar || !topToolbar) return;

    var displayCriteria = bottomToolbar.getDisplayCriteria();
    var drawOptions = topToolbar.getDrawOptions();

    //Drawing
    var sb = new StringBuffer();
    var header_html = ogTasks.newTaskFormTopList();
    if ($("#ogTasksPanelColNamesThead").length == 0) {
	    sb.append(header_html);
    }

    //Draw all groups
    var first_group_to_draw_index = -1;
    for (var i = 0; i < this.Groups.length; i++) {
    	var gr_html = this.drawGroup(displayCriteria, drawOptions, this.Groups[i]);
        if (gr_html) {
        	if (first_group_to_draw_index == -1) {
        		first_group_to_draw_index = i;
        	}
        	sb.append(gr_html);
        }
    }

    //Message
    if (this.Groups.length == 0 && ogTasks.groupsPaginationOffset == 0) {
        var context_names = og.contextManager.getActiveContextNames();
        if (context_names.length == 0) context_names.push(lang('all'));

        sb.append('<tr id="no_tasks_info"><td colspan="10">' +
            '<div class="inner-message">' + lang('no tasks to display', '"' + context_names.join('", "') + '"') + '</div>' +
            '</td></tr>');
    }
    if (this.Groups.length == 0) {
    	ogTasks.allGroupsLoaded = true;
    }
    /*
    if (this.Groups.length >= ogTasks.groupsPaginationCount) {
    	sb.append('</div><div id="tasksPanelGroupsPagination" class="tasks-group-pagination-link-container">' +
    			'<a href="#" onclick="ogTasks.loadMoreGroups();return false;">' +
    			lang('load more task groups') +
    			'</a></div>');
    }*/

    // Track scroll position via listener so it's captured before the panel hides.
    var taskContentScrollEl = document.getElementById('tasksPanelContent');
    if (taskContentScrollEl && !taskContentScrollEl._ogScrollSaveAttached) {
        taskContentScrollEl._ogScrollSaveAttached = true;
        taskContentScrollEl.addEventListener('scroll', function () {
            ogTasks.lastScrollTop = this.scrollTop;
        }, { passive: true });
    }

    var container = document.getElementById('tasksPanelContainer');
    if (container) {
    	if (ogTasks.groupsPaginationOffset == 0) {
    		container.innerHTML = '';
    	}
        // container.innerHTML += sb.toString();
        // Use insertAdjacentHTML so existing DOM nodes (and their jQuery data / ogColSynced
        // markers) are preserved.  innerHTML += serialises + re-parses ALL existing nodes,
        // wiping jQuery's data store and losing the ogColSynced flag on every already-synced
        // row.  That causes _syncTdsOnly to double-permute those rows on the next
        // finalizeColHeaderFeatures() call, producing misaligned columns after scroll loads.
        container.insertAdjacentHTML('beforeend', sb.toString());
    }
    ogTasks.initColHeaderFeatures();
    if (this.Groups.length != 0 && first_group_to_draw_index !== -1) {
        ogTasks.drawAllGroupsTasks(first_group_to_draw_index);
    }

    ogTasks.updateGroupPaginationButtons();
    ogTasks.initDragDrop();

    // Initialize inline cell editing (delegated, safe to call multiple times)
    if (ogTasks.InlineCellEditor) {
        ogTasks.InlineCellEditor.init();
    }

    // Initialize member hover cards (delegated, safe to call multiple times)
    if (ogTasks.MemberHoverCard) {
        ogTasks.MemberHoverCard.init();
    }

    // Initialize assignee hover cards (delegated, safe to call multiple times)
    if (ogTasks.AssigneeHoverCard) {
        ogTasks.AssigneeHoverCard.init();
    }
}

/**
 * Rebuilds the positional colResizable width string (localStorage["tasksPanelContainer"])
 * from the ID-keyed width map (localStorage["tasksPanelContainer_colIds"]) for the current
 * visible <th> order.  Must be called after a column reorder and before initColResize() so
 * that colResizable restores the correct width for each column in its new position.
 * Without this, colResizable's count-based reconciliation never fires (count hasn't changed),
 * and it would apply widths by slot index — giving every column its neighbour's old width.
 */
ogTasks._rebuildPositionalWidths = function () {
    // Expected tasksPanelContainer format (written by colResizable + this function):
    //   col1Width;col2Width;...;colNWidth;total;tableWidth
    // The last token is the overall table width managed by colResizable; we preserve it
    // verbatim so that the table container does not resize on a column reorder.
    try {
        var idData = localStorage && localStorage["tasksPanelContainer_colIds"];
        if (!idData) return;
        var idMap = JSON.parse(idData);
        var storedData = localStorage && localStorage["tasksPanelContainer"];
        var tableWidth = "0";
        if (storedData) {
            var storedParts = storedData.split(";");
            // Minimum valid string has at least the total and tableWidth tokens (length >= 2).
            if (storedParts.length >= 2) {
                tableWidth = storedParts[storedParts.length - 1];
            } else {
                console.warn('_rebuildPositionalWidths: unexpected tasksPanelContainer format, tableWidth reset to 0. Value was: ' + storedData);
            }
        }
        var newParts = [];
        var total = 0;
        $("#tasksPanelContainer thead tr th:visible").each(function () {
            var colId = ($(this).attr('class') || '').split(' ')[0];
            var w = (colId && idMap[colId] !== undefined) ? Math.round(idMap[colId]) : Math.round($(this).width());
            w = Math.max(w || 50, 30);
            newParts.push(w);
            total += w;
        });
        localStorage["tasksPanelContainer"] = newParts.join(";") + ";" + total + ";" + tableWidth;
    } catch (e) {
        console.error('_rebuildPositionalWidths: failed to rebuild column widths', e);
    }
};

/**
 * Reads the saved column order from localStorage.
 * @returns {Array|null} Array of column IDs in saved order, or null if not set.
 */
ogTasks.loadColOrder = function () {
    try {
        var raw = localStorage["tasksPanelContainer_colOrder"];
        return raw ? JSON.parse(raw) : null;
    } catch (e) { return null; }
};

/**
 * Persists the current column order to localStorage keyed by "tasksPanelContainer_colOrder".
 * @param {Array} [colIds] Optional array of column IDs; if omitted, reads from current DOM order.
 */
ogTasks.saveColOrder = function (colIds) {
    try {
        if (!colIds) {
            colIds = [];
            $("#ogTasksPanelColNames th").each(function () {
                colIds.push(($(this).attr('class') || '').split(' ')[0]);
            });
        }
        localStorage["tasksPanelContainer_colOrder"] = JSON.stringify(colIds);
    } catch (e) {}
};

/**
 * Applies the saved column order to the DOM after a full redraw (col + th + td elements).
 * task_actions (the leftmost checkbox/expander column) is always pinned first.
 * task_quick_actions (the merged quick-actions + "..." overflow column) is always
 * visible but freely repositionable, both via header drag (see initColDragDrop)
 * and via the Columns modal — its slot in the saved order is honored like any
 * other column.
 * New columns not yet in the saved order are appended before the locked-last columns.
 * Columns removed since the order was saved are silently skipped.
 */
ogTasks.applyColOrder = function () {
    var LOCKED_FIRST = ['task_actions'];
    var LOCKED_LAST  = [];

    var savedOrder = ogTasks.loadColOrder();
    if (!savedOrder || !savedOrder.length) return;

    // Defensive: drop the now-removed task_btn_actions id from any pre-merge saved
    // order so it never lingers in bookkeeping (task_quick_actions absorbed it).
    savedOrder = savedOrder.filter(function (id) { return id !== 'task_btn_actions'; });

    var $ths = $("#ogTasksPanelColNames th");
    if ($ths.length === 0) return;

    var currentColIds = [];
    $ths.each(function () {
        currentColIds.push(($(this).attr('class') || '').split(' ')[0]);
    });

    // Object.create(null) prevents prototype pollution: if any id were "__proto__" or
    // "constructor", assigning to a plain {} would modify inherited prototype properties.
    var currentSet = Object.create(null), savedSet = Object.create(null);
    currentColIds.forEach(function (id) { currentSet[id] = true; });
    savedOrder.forEach(function (id) { savedSet[id] = true; });

    // Reorderable columns from saved order (excluding locked and removed)
    var middle = [];
    savedOrder.forEach(function (id) {
        if (currentSet[id] && LOCKED_FIRST.indexOf(id) === -1 && LOCKED_LAST.indexOf(id) === -1) {
            middle.push(id);
        }
    });
    // New columns (present now but not in saved order) go after the saved ones, before locked-last
    currentColIds.forEach(function (id) {
        if (!savedSet[id] && LOCKED_FIRST.indexOf(id) === -1 && LOCKED_LAST.indexOf(id) === -1) {
            middle.push(id);
        }
    });

    var targetOrder = [];
    LOCKED_FIRST.forEach(function (id) { if (currentSet[id]) targetOrder.push(id); });
    middle.forEach(function (id) { targetOrder.push(id); });
    LOCKED_LAST.forEach(function (id) { if (currentSet[id]) targetOrder.push(id); });

    var changed = targetOrder.some(function (id, i) { return id !== currentColIds[i]; });
    if (!changed) return;

    ogTasks._reorderAllColumns(targetOrder, currentColIds);
};

/**
 * Reorders col, th, and td elements in the DOM to match targetOrder.
 * @param {Array} targetOrder Array of column IDs in the desired order.
 * @param {Array} oldOrder    Array of column IDs reflecting the current DOM order.
 */
ogTasks._reorderAllColumns = function (targetOrder, oldOrder) {
    // Persist the positional mapping so applyColOrderToRow can replay it on
    // individual rows redrawn later (e.g. after inline edit via reDrawTask).
    try {
        localStorage['tasksPanelContainer_colMapping'] = JSON.stringify(
            {target: targetOrder, source: oldOrder}
        );
    } catch (e) {}

    var srcIndices = targetOrder.map(function (id) { return oldOrder.indexOf(id); });
    var numCols = targetOrder.length;

    // Reorder <col> elements (direct children of table, no colgroup wrapper)
    var $cols = $("#tasksPanelContainer > col");
    if ($cols.length) {
        var cols = $cols.toArray();
        var $thead = $("#tasksPanelContainer > thead").first();
        $cols.detach();
        srcIndices.forEach(function (i) {
            if (i >= 0 && cols[i]) $(cols[i]).insertBefore($thead);
        });
    }

    // Reorder <th> elements
    var $theadTr = $("#ogTasksPanelColNames");
    var ths = $theadTr.children('th').toArray();
    $theadTr.empty();
    srcIndices.forEach(function (i) { if (i >= 0 && ths[i]) $theadTr.append(ths[i]); });

    // Reorder <td> elements in each tbody row; skip group-header rows (single td with colspan)
    var tOrderKey = targetOrder.join(',');
    $("#tasksPanelContainer tbody tr").each(function () {
        var $tds = $(this).children('td');
        if ($tds.length !== numCols) return;
        var tds = $tds.toArray();
        var $tr = $(this);
        $tr.empty();
        srcIndices.forEach(function (i) { if (i >= 0 && tds[i]) $tr.append(tds[i]); });
        // Use a DOM attribute instead of jQuery data so the marker survives any
        // future innerHTML operations that would wipe jQuery's data store.
        this.setAttribute('data-og-col-synced', tOrderKey);
    });
};

/**
 * Reorders the <td> elements of a single <tr> using the stored positional mapping
 * saved by _reorderAllColumns / _syncDataColumnsToOrder.
 *
 * WHY positional (not class-based):
 *   Most <td> elements in the task row template have NO identifying CSS class
 *   (e.g. task_actions, assigned_to render as bare <td>).  A class-based map
 *   collapses all unclassed cells under the key '' and produces a corrupted map,
 *   causing columns to be dropped or shifted left.
 *
 *   _reorderAllColumns already uses position indices successfully for the full
 *   redraw.  We reuse the same {target, source} mapping it saves to localStorage
 *   and apply only the td reorder to the single newly redrawn row.
 *
 * @param {jQuery} $tr  The task <tr> to fix (group-header rows are skipped automatically).
 */
ogTasks.applyColOrderToRow = function ($tr) {
    if (!$tr || !$tr.length) return;

    var mapData;
    try {
        mapData = JSON.parse(localStorage['tasksPanelContainer_colMapping'] || 'null');
    } catch (e) { mapData = null; }

    // No column reorder has been applied yet — nothing to do.
    if (!mapData || !mapData.target || !mapData.source) return;

    var srcIndices = mapData.target.map(function (id) {
        return mapData.source.indexOf(id);
    });
    var numCols = mapData.target.length;

    var $tds = $tr.children('td');
    // Skip group-header rows (single <td> with colspan) and mismatched rows.
    if ($tds.length !== numCols) return;

    var tds = $tds.toArray();
    $tr.empty();
    srcIndices.forEach(function (i) {
        if (i >= 0 && tds[i]) $tr.append(tds[i]);
    });
};

/**
 * Reorders only col and td elements (not th) to match newOrder.
 * Used after a jQuery UI sortable drag completes, when th elements are already in the new order.
 * @param {Array} newOrder Array of column IDs in the desired order.
 * @param {Array} oldOrder Array of column IDs reflecting the pre-drag DOM order.
 */
ogTasks._syncDataColumnsToOrder = function (newOrder, oldOrder) {
    // Keep the stored mapping in sync so applyColOrderToRow stays correct.
    try {
        localStorage['tasksPanelContainer_colMapping'] = JSON.stringify(
            {target: newOrder, source: oldOrder}
        );
    } catch (e) {}

    var srcIndices = newOrder.map(function (id) { return oldOrder.indexOf(id); });
    var numCols = newOrder.length;

    // Reorder <col> elements
    var $cols = $("#tasksPanelContainer > col");
    if ($cols.length) {
        var cols = $cols.toArray();
        var $thead = $("#tasksPanelContainer > thead").first();
        $cols.detach();
        srcIndices.forEach(function (i) {
            if (i >= 0 && cols[i]) $(cols[i]).insertBefore($thead);
        });
    }

    // Reorder <td> elements; skip group-header rows (single td with colspan)
    var tOrderKey = newOrder.join(',');
    $("#tasksPanelContainer tbody tr").each(function () {
        var $tds = $(this).children('td');
        if ($tds.length !== numCols) return;
        var tds = $tds.toArray();
        var $tr = $(this);
        $tr.empty();
        srcIndices.forEach(function (i) { if (i >= 0 && tds[i]) $tr.append(tds[i]); });
        this.setAttribute('data-og-col-synced', tOrderKey);
    });
};

/**
 * Initialises jQuery UI Sortable on the task-list column header row so that users can
 * drag column headers to reorder them.  The first and last utility columns are locked.
 * After a successful drag the new order is persisted to localStorage and colResizable
 * is re-initialised so widths continue to work correctly.
 */
ogTasks.initColDragDrop = function () {
    var $theadTr = $("#ogTasksPanelColNames");
    if ($theadTr.length === 0) return;
    try { $theadTr.sortable("destroy"); } catch (e) {}

    var preDragColIds = null;

    $theadTr.sortable({
        items: "th:not(.task_actions)",
        axis: "x",
        cursor: "grabbing",
        opacity: 0.7,
        tolerance: "pointer",
        placeholder: "tasks-col-drag-placeholder",
        forcePlaceholderSize: true,
        /**
         * Capture the pre-drag column order for use in the update callback.
         * We deliberately avoid reading the live DOM here: by the time "start" fires
         * jQuery UI has already replaced the dragged <th> with a placeholder
         * <th class="tasks-col-drag-placeholder"> and moved the original element,
         * so iterating the DOM would produce a corrupted list.
         * Instead we use the saved localStorage order (which always reflects the
         * actual <col> element order), falling back to the default tasks_list_cols
         * order when no drag has been done yet.
         */
        start: function (_event, ui) {
            preDragColIds = ogTasks.loadColOrder();
            if (!preDragColIds && ogTasks.TasksList && ogTasks.TasksList.tasks_list_cols) {
                preDragColIds = ogTasks.TasksList.tasks_list_cols.map(function (col) { return col.id; });
            }
            ui.placeholder.width(ui.item.outerWidth());
        },
        /**
         * After jQuery UI has moved the dragged th to its new position, sync the
         * corresponding col and td elements, persist the new order, then reinitialise
         * column resizing.
         */
        update: function () {
            if (!preDragColIds) return;
            // Read final th order; exclude any stray placeholder that may not yet be removed.
            var newColIds = [];
            $("#ogTasksPanelColNames th").each(function () {
                var colId = ($(this).attr('class') || '').split(' ')[0];
                if (colId !== 'tasks-col-drag-placeholder') {
                    newColIds.push(colId);
                }
            });
            ogTasks._syncDataColumnsToOrder(newColIds, preDragColIds);
            ogTasks._rebindActionPopovers();
            // Do NOT call saveColWidthsById() here: the DOM is in a transitional state
            // after jQuery UI's sortable move, and reading $(th).width() at this moment
            // may return the adjacent <col>'s width rather than the th's own stored width,
            // corrupting the id map.  The id map saved by the last initColResize() is still
            // accurate for every column ID, so _rebuildPositionalWidths() can use it directly.
            // initColResize() will call saveColWidthsById() again once widths are settled.
            ogTasks.saveColOrder(newColIds);
            ogTasks._rebuildPositionalWidths();
            ogTasks.initColResize();
            preDragColIds = null;
        }
    });
};

/**
 * Reorders only the td elements in each task row to match targetOrder.
 * Used after async row rendering, when col and th are already in the correct order
 * but the freshly-rendered td elements are still in the default template order.
 * @param {Array} targetOrder  Desired column ID order (matches current th order).
 * @param {Array} tdOldOrder   Column ID order the td elements were rendered in (default).
 */
ogTasks._syncTdsOnly = function (targetOrder, tdOldOrder) {
    var srcIndices = targetOrder.map(function (id) { return tdOldOrder.indexOf(id); });
    var numCols = targetOrder.length;
    // Key representing the target order; used to skip rows already synced to this order
    // so that a second call (e.g. on scroll-triggered lazy load of more groups) does not
    // re-apply the transformation to rows that are already in the correct order.
    // NOTE: We use a DOM attribute (data-og-col-synced) rather than jQuery data() so the
    // marker survives any innerHTML operations that would wipe jQuery's data store.
    var targetOrderKey = targetOrder.join(',');

    $("#tasksPanelContainer tbody tr").each(function () {
        var $tds = $(this).children('td');
        if ($tds.length !== numCols) return; // skip group-header rows (single td with colspan)
        // Skip rows that have already been reordered to this exact column order.
        if (this.getAttribute('data-og-col-synced') === targetOrderKey) return;
        var tds = $tds.toArray();
        var $tr = $(this);
        $tr.empty();
        srcIndices.forEach(function (i) { if (i >= 0 && tds[i]) $tr.append(tds[i]); });
        this.setAttribute('data-og-col-synced', targetOrderKey);
    });
};

/**
 * Re-binds the "..." overflow-actions and "working on" popovers for every row.
 *
 * MUST be called after any operation that empties and re-appends <td> elements to
 * reorder columns (_syncTdsOnly, _syncDataColumnsToOrder, _reorderAllColumns).
 * jQuery's .empty() strips all bound data — including the Bootstrap popover
 * plugin's own internal state (`.data('bs.popover')`) — from every descendant it
 * removes, even though the raw <button> DOM node itself is preserved and
 * re-inserted afterward. Without this, "..." silently becomes a dead button (and
 * the working-on-users hover popover stops working) the moment a column is
 * reordered, since og.initPopoverBtns() was only ever called once, right after
 * the initial render.
 */
ogTasks._rebindActionPopovers = function () {
    if (typeof og === 'undefined' || typeof og.initPopoverBtns !== 'function') return;
    var btns = $("#tasksPanelContainer .tasksActionsBtn").toArray();
    if (btns.length) og.initPopoverBtns(btns);
};

/**
 * Reorders td elements in a given set of <tr> rows to match the current column header order.
 * Rows freshly inserted from the Handlebars template always have td in the default
 * tasks_list_cols order; call this after inserting them if columns have been reordered.
 * @param {jQuery} $rows  jQuery set of <tr> elements to fix up.
 */
ogTasks._syncRowTds = function ($rows) {
    if (!ogTasks.TasksList || !ogTasks.TasksList.tasks_list_cols) return;
    var defaultColIds = ogTasks.TasksList.tasks_list_cols.map(function (col) { return col.id; });
    var currentThColIds = [];
    $("#ogTasksPanelColNames th").each(function () {
        currentThColIds.push(($(this).attr('class') || '').split(' ')[0]);
    });
    // Both arrays must agree on column count before we can safely build srcIndices.
    // A length mismatch means a column was toggled between renders; srcIndices would
    // contain -1 entries that silently drop td slots, corrupting the row.
    if (currentThColIds.length !== defaultColIds.length) {
        console.warn('_syncRowTds: column count mismatch (header=' + currentThColIds.length +
            ', default=' + defaultColIds.length + '); skipping td reorder');
        return;
    }
    var differs = currentThColIds.some(function (id, i) { return id !== defaultColIds[i]; });
    if (!differs) return;
    var srcIndices = currentThColIds.map(function (id) { return defaultColIds.indexOf(id); });
    // Rows are rendered in defaultColIds order, so compare against that length.
    var numCols = defaultColIds.length;
    var targetOrderKey = currentThColIds.join(',');
    $rows.each(function () {
        var $tds = $(this).children('td');
        if ($tds.length !== numCols) return;
        var tds = $tds.toArray();
        var $tr = $(this);
        $tr.empty();
        srcIndices.forEach(function (i) { if (i >= 0 && tds[i]) $tr.append(tds[i]); });
        this.setAttribute('data-og-col-synced', targetOrderKey);
    });
};

/**
 * Applies saved column order, then initialises column resizing and column drag-and-drop.
 * Called at draw() time (line 89), before async row rendering begins.
 * Reorders col and th to the saved order; no td elements exist yet.
 *
 * The server config must be seeded into localStorage BEFORE applyColOrder() reads it.  On a
 * user's first render localStorage is still empty, so seeding it later (initColResize() does
 * it too) left the list in template order and, worse, the debounced save that follows wrote
 * that template order back to the server — overwriting the role defaults for good.
 */
ogTasks.initColHeaderFeatures = function () {
    ogTasks._applyServerColumnsConfig();
    ogTasks.applyColOrder();
    ogTasks.initColResize();
    ogTasks.initColDragDrop();
};

/**
 * Called after all async task rows have been rendered (line 566 / drawGroupNextTask finish).
 * At this point col and th are already in the saved order from initColHeaderFeatures(), but
 * the freshly-rendered td elements are still in the default template order.
 * Syncs only the td elements to match the current th order, then re-inits resize and drag-drop.
 */
ogTasks.finalizeColHeaderFeatures = function () {
    if (ogTasks.TasksList && ogTasks.TasksList.tasks_list_cols) {
        // Default template order: the order tasks_list_cols was built in this render cycle.
        var defaultColIds = ogTasks.TasksList.tasks_list_cols.map(function (col) { return col.id; });
        var currentThColIds = [];
        $("#ogTasksPanelColNames th").each(function () {
            currentThColIds.push(($(this).attr('class') || '').split(' ')[0]);
        });
        // Only sync if the header order differs from the template render order.
        var differs = currentThColIds.some(function (id, i) { return id !== defaultColIds[i]; });
        if (differs) {
            ogTasks._syncTdsOnly(currentThColIds, defaultColIds);
            ogTasks._rebindActionPopovers();
        }
    }
    ogTasks.initColResize();
    ogTasks.initColDragDrop();
};

/**
 * Seeds localStorage from the server-saved column config so the existing resize logic
 * picks it up naturally. Server is authoritative for cross-device sync.
 *
 * The flag is set to false by new_list_tasks.php on each list reload so that a fresh
 * server config (e.g. saved from another browser) is picked up, while still guarding
 * against the multiple initColResize() calls that happen within a single render.
 * Without the flag, the drag-drop handler's initColResize() call would overwrite the
 * newly-saved colOrder with the old server value before the debounced save fires.
 */
ogTasks._serverColumnsConfigApplied = false;
ogTasks._applyServerColumnsConfig = function () {
    if (ogTasks._serverColumnsConfigApplied) return;
    try {
        var cfg = og.config && og.config.tasks_columns_config;
        // If cfg is null (no saved config yet for this user), leave the flag false so
        // the next initColResize() call can retry once the config is available.
        if (!cfg) return;
        if (typeof cfg === 'string') cfg = JSON.parse(cfg);
        if (cfg.colIds) localStorage["tasksPanelContainer_colIds"] = JSON.stringify(cfg.colIds);
        if (cfg.colPos) localStorage["tasksPanelContainer"] = cfg.colPos;
        if (cfg.colOrder) localStorage["tasksPanelContainer_colOrder"] = JSON.stringify(cfg.colOrder);
        ogTasks._serverColumnsConfigApplied = true;
    } catch (e) {}
};

/**
 * Schedules a debounced save of column config to the server.
 * Coalesces rapid resize events into a single request fired 1.5 s after the last change.
 */
ogTasks._saveColumnsConfigTimer = null;
ogTasks._skipNextScheduledSave = false;
ogTasks._scheduleSaveColumnsConfig = function () {
    clearTimeout(ogTasks._saveColumnsConfigTimer);
    ogTasks._saveColumnsConfigTimer = setTimeout(function () {
        if (ogTasks._skipNextScheduledSave) {
			ogTasks._skipNextScheduledSave = false; return;
		}
        try {
            var colIds = JSON.parse(localStorage["tasksPanelContainer_colIds"] || '{}');
            // Capture ALL th elements (visible and hidden) in DOM order — same as saveColOrder().
            // Using only Object.keys(colIds) would give visible-only columns, which corrupts
            // the drag-drop handler: preDragColIds would be missing hidden column IDs, making
            // srcIndices contain -1 entries that silently drop those td cells from every row.
            var colOrder = [];
            $("#tasksPanelContainer thead tr th").each(function () {
                var colId = ($(this).attr('class') || '').split(' ')[0];
                if (colId) colOrder.push(colId);
            });
            // Include the current quick-actions order so a resize/drag-triggered save
            // (this function) never overwrites the actionsOrder saved by the Columns
            // modal — both write to the same server-side config blob, so any payload
            // missing a key here would erase it. Read the in-memory value the modal
            // keeps in sync (og.config.tasks_columns_config), not just the one set at
            // Apply time, so this stays correct even long after the modal has closed.
            var actionsOrder = (og.config.tasks_columns_config && og.config.tasks_columns_config.actionsOrder)
                || (ogTasks.userPreferences && ogTasks.userPreferences.actionsOrder)
                || ogTasks.DEFAULT_ACTIONS_ORDER;
            var config = {
                colIds: colIds,
                colPos: localStorage["tasksPanelContainer"] || '',
                colOrder: colOrder.length ? colOrder : null,
                actionsOrder: actionsOrder
            };
            og.openLink(og.getUrl('task', 'save_tasks_columns_config'), {
                hideLoading: true,
                post: { config: JSON.stringify(config) }
            });
        } catch (e) {}
    }, 1500);
};

/**
 * Persists current column widths to localStorage keyed by column ID (first CSS class on each th).
 * This ID-based map is used to reassign widths correctly when columns are added or removed,
 * avoiding the positional shift that would occur with colResizable's plain indexed storage.
 */
ogTasks.saveColWidthsById = function () {
    try {
        var widths = {};
        $("#tasksPanelContainer thead tr th:visible").each(function () {
            var colId = ($(this).attr('class') || '').split(' ')[0];
            if (colId) {
                widths[colId] = $(this).width();
            }
        });
        localStorage["tasksPanelContainer_colIds"] = JSON.stringify(widths);
        ogTasks._scheduleSaveColumnsConfig();
    } catch (e) {}
};

ogTasks.initColResize = function () {
    // Seed localStorage from server config on first call (server is authoritative for cross-device sync).
    ogTasks._applyServerColumnsConfig();

    // If the table has no visible headers yet (e.g. draw() wiped innerHTML before re-appending
    // the thead on a redraw), bail out immediately.  Reconciliation or saveColWidthsById running
    // against an empty th set would corrupt the stored data with ";0;<tableWidth>".
    if ($("#tasksPanelContainer thead tr th:visible").length === 0) return;

    $("#tasksPanelContainer").colResizable({disable: true});//remove previous colResize

    // colResizable stores widths positionally: "w1;w2;...;wN;total;tableWidth" (N+2 entries).
    // When a column is added or removed the count changes, making the positional mapping wrong.
    // If we have an ID-keyed backup, rebuild the positional string so each column gets its own
    // stored width (new columns fall back to their natural DOM width).
    try {
        var storedData = localStorage && localStorage["tasksPanelContainer"];
        var idData = localStorage && localStorage["tasksPanelContainer_colIds"];
        if (storedData && idData) {
            var storedParts = storedData.split(";");
            var currentCols = $("#tasksPanelContainer thead tr th:visible");
            var storedColCount = storedParts.length - 2; // non-fixed format: N+2 entries
            if (storedColCount !== currentCols.length) {
                var idMap = JSON.parse(idData);
                var newParts = [];
                var total = 0;
                currentCols.each(function () {
                    var colId = ($(this).attr('class') || '').split(' ')[0];
                    var w = (colId && idMap[colId] !== undefined) ? Math.round(idMap[colId]) : Math.round($(this).width());
                    w = Math.max(w || 50, 30); // Ensure minimum width and handle NaN
                    newParts.push(w);
                    total += w;
                });
                var tableWidth = storedParts[storedParts.length - 1];
                localStorage["tasksPanelContainer"] = newParts.join(";") + ";" + total + ";" + tableWidth;
            }
        } else if (storedData) {
            // No ID map yet (first load after this feature was deployed, or storage was
            // partially cleared).  Bootstrap a best-effort ID map by pairing the stored
            // positional widths with the current visible columns in order, then use the
            // normal reconciliation path so the positional string is never wiped.
            var storedParts = storedData.split(";");
            var currentCols = $("#tasksPanelContainer thead tr th:visible");
            var storedN = storedParts.length - 2; // N+2 non-fixed format
            var bootstrapMap = {};
            currentCols.each(function (i) {
                var colId = ($(this).attr('class') || '').split(' ')[0];
                if (!colId) return;
                bootstrapMap[colId] = (i < storedN)
                    ? (parseInt(storedParts[i]) || 50)
                    : Math.max($(this).width() || 50, 30);
            });
            localStorage["tasksPanelContainer_colIds"] = JSON.stringify(bootstrapMap);
            if (storedN !== currentCols.length) {
                var newParts = [];
                var total = 0;
                currentCols.each(function () {
                    var colId = ($(this).attr('class') || '').split(' ')[0];
                    var w = (colId && bootstrapMap[colId] !== undefined) ? Math.round(bootstrapMap[colId]) : Math.round($(this).width());
                    w = Math.max(w || 50, 30);
                    newParts.push(w);
                    total += w;
                });
                var tableWidth = storedParts[storedParts.length - 1];
                localStorage["tasksPanelContainer"] = newParts.join(";") + ";" + total + ";" + tableWidth;
            }
        }
    } catch (e) {}

    $("#tasksPanelContainer").colResizable({
        fixed: false,
        minWidth: 50,
        postbackSafe: true,
        disable: false,
		liveDrag: true,
		resizeMode: "fit",
        onResize: function () {
            ogTasks.saveColWidthsById();
        }
    });

    // Save ID-based widths after colResizable has applied postbackSafe restore to the DOM
    ogTasks.saveColWidthsById();

	// Fix jcolResizable bug that doesn't allow the user to select text inside the cells
	document.addEventListener('selectstart', function (e) {
		if ($(e.target).closest('#tasksPanelContainer td, #tasksPanelContainer th')) {
			e.stopPropagation();
			return true;
		}
	}, true);
}
//ogTasks.toggleSubtasksShow = false;
ogTasks.toggleSubtasks = function (taskId, groupId, not_expand) {
    
    var expander = document.getElementById('ogTasksPanelFixedExpanderT' + taskId + 'G' + groupId);
    var task = this.getTask(taskId);
    // Scope to this group and include level-1 rows: a subtask can appear at root when its
    // parent is in another group or filtered out. The old data-level!='1' check missed those
    // rows and caused drawSubtasks to append a duplicate on every expand.
    var $children = $('#ogTasksPanelGroup' + groupId + ' [data-parent-id="' + taskId + '"]');
    if ($children.length) {
        
        task.isExpanded = !task.isExpanded;
        if (task.isExpanded && not_expand != true) {
            $children.show();
        } else {
            $children.hide();
            if (task.subtasksIds && task.subtasksIds.length > 0) {
                task.subtasksIds.forEach(function (value) {
                    ogTasks.toggleSubtasks(value, groupId, true);
                });
            }
        }
    } else {
        var loadKey = ogTasks._subtasksLoadKey(taskId, groupId);
        if (ogTasks._subtasksLoadInFlight[loadKey]) {
            if (expander) {
                expander.className = "og-task-expander toggle_expanded";
            }
            return;
        }
        // No subtask rows in this group. toggleSubtasksShow and isExpanded may be stale
        // (e.g. after a DOM rebuild on filter change or return from task view) — reset them
        // so the fetch fires and the expander ends up in the correct expanded state.
        task.toggleSubtasksShow = false;
        if (task.subtasksIds.length > 0 && not_expand != true) {
            task.isExpanded = true;
            og.getSubTasksAndDraw(task, groupId);
        }
    }
    if (expander) {
        expander.className = "og-task-expander " + ((task.isExpanded) ? 'toggle_expanded' : 'toggle_collapsed');
    }
}

ogTasks.loadAllDescriptions = function (task_ids) {
    ogTasks.all_descriptions_loaded = false;
    og.openLink(og.getUrl('task', 'get_task_descriptions'), {
        hideLoading: true,
        scope: this,
        method: 'POST',
        post: {ids: task_ids.join(',')},
        callback: function (success, data) {
            for (i = 0; i < ogTasks.Tasks.length; i++) {
                var task = ogTasks.Tasks[i];
                if (data.descriptions['t' + task.id]) {
                    task.description = data.descriptions['t' + task.id];
                }
            }
            ogTasks.all_descriptions_loaded = true;
        }
    });
}

//************************************
//*		Draw group
//************************************
ogTasks.drawGroup = function (displayCriteria, drawOptions, group) {
	if ($("#ogTasksPanelGroup" + group.group_id).length > 0) return '';
    group.view = [];
    if (displayCriteria.group_by == 'milestone') {
        var sb = new StringBuffer();
        var milestone = this.getMilestone(group.group_id);
        if (milestone) {
            if (milestone.isUrgent) {
                group.group_icon = 'ico-urgent-milestone';
            }

            if (milestone.completedById) {
                var user = this.getUser(milestone.completedById, true);
                var tooltip = '';
                if (user) {
                    var time = new Date(milestone.completedOn * 1000);
                    var now = new Date();
                    var timeFormatted = time.getYear() != now.getYear() ? time.dateFormat('M j, Y') : time.dateFormat('M j');
                    tooltip = lang('completed by name on', og.clean(user.name), timeFormatted).replace(/'\''/g, '\\\'');
                }
                group.group_name = "<a href='#' style='text-decoration:line-through' class='internalLink' onclick='og.openLink(\"" + og.getUrl('milestone', 'view', {id: group.group_id}) + "\")' title='" + tooltip + "'>" + og.clean(group.group_name) + '</a>';
            } else {
                group.group_name = "<a href='#' class='internalLink' onclick='og.openLink(\"" + og.getUrl('milestone', 'view', {id: group.group_id}) + "\")'>" + og.clean(group.group_name) + '</a>';
            }

            //Due date
            var date = new Date();
            date.setTime((milestone.dueDate + date.getTimezoneOffset() * 60) * 1000);
            var now = new Date();
            var dateFormatted = date.getYear() != now.getYear() ? date.dateFormat('M j, Y') : date.dateFormat('M j');

            css_class = "milestone-date";
            if (milestone.completedById > 0) {
                css_class = "milestone-date milestone-completed";
            } else {
                if ((date < now))
                    css_class = "milestone-date milestone-delayed";
            }
            group.view.push({
                id: 'ogTasksPanelMileGroupDate' + group.group_id,
                text: lang('due') + ':&nbsp;' + dateFormatted,
                css_class: css_class
            });

            //Percent complete
            group.view.push({
                id: 'ogTasksPanelCompleteBar' + group.group_id,
                text: this.drawMilestoneCompleteBar(group),
                css_class: "group-milestone-complete-bar"
            });

        }
    }

    //Member Path
    var mem_path = "";
    var mpath = Ext.util.JSON.decode(group.group_memPath);
    if (mpath) mem_path = og.getEmptyCrumbHtml(mpath, ".task-breadcrumb-container");

    //get template for the row
    var source = $("#task-list-group-template").html();
    //compile the template
    var template = Handlebars.compile(source);

    //template data
    var data = {
        group: group,
        mem_path: mem_path,
        cols_total: ogTasks.TasksList.tasks_list_cols.length
    }

    //instantiate the template
    var html = template(data);

    html = html + ogTasks.newTaskGroupTotals(group);

    return html;
}

ogTasks.newTaskGroupTotals = function (group) {
    //get template for the row
    var source = $("#task-list-group-totals-template").html();
    //compile the template
    var template = Handlebars.compile(source);

    var total_cols = [];

    for (var i = 0; i < ogTasks.TasksList.tasks_list_cols.length; i++) {
        var col = ogTasks.TasksList.tasks_list_cols[i];
        if (col.id == "task_name") {
            var total = lang('total') + ':';
        } else {
            var total = group[col.group_total_field];
        }

        total_cols.push({id: col.id, text: total});
    }

    var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
    var drawOptions = topToolbar.getDrawOptions();
    drawOptions.groupId = group.group_id;

    if (group.total_tasks_loaded < group.root_total) {
        drawOptions.showMore = true;
    } else {
        drawOptions.showMore = false;
    }

    //template data
    var data = {
        draw_options: drawOptions,
        group: group,
        total_cols: total_cols
    }
    if (ogTasks.additional_task_list_columns) {
        data.additional_task_list_columns = ogTasks.additional_task_list_columns;
    }

    //instantiate the template
    var html = template(data);

    return html;
}


ogTasks.drawMilestoneCompleteBar = function (group) {
    var html = '';
    var milestone = this.getMilestone(group.group_id);
    if (!milestone) return html;
    var complete = 0;
    var completedTasks = parseInt(milestone.completedTasks);
    var totalTasks = parseInt(milestone.totalTasks);
    var tasks = this.flattenTasks(group.group_tasks);
    for (var i = 0; i < tasks.length; i++) {
        var t = tasks[i];
        if (t.milestoneId == group.group_id) {
            completedTasks += (t.status == 1 && (t.statusOnCreate == 0)) ? parseInt(1) : parseInt(0);
            completedTasks -= (t.status == 0 && (t.statusOnCreate == 1)) ? parseInt(1) : parseInt(0);
            totalTasks = (t.isCreatedClientSide) ? totalTasks + parseInt(1) : totalTasks + parseInt(0);
        }
    }
    if (totalTasks > 0)
        complete = ((100 * completedTasks) / totalTasks);
    html += "<table><tr><td style='padding-left:15px;padding-top:5px'>" +
        "<table style='height:7px;width:50px'><tr><td style='height:7px;width:" + (complete) + "%;background-color:#6C2'></td><td style='width:" + (100 - complete) + "%;background-color:#DDD'></td></tr></table>" +
        "</td><td style='padding-left:3px;line-height:12px'><span style='font-size:8px;color:#AAA'>(" + completedTasks + '/' + totalTasks + ")</span></td></tr></table>";

    return html;
}

ogTasks.minutesToHoursAndMinutes = function (minutes) {
    var total_estimate_split = Math.round(minutes * 100 / 60) / 100;
    var total_estimate = (total_estimate_split + '').split(".");
    var hours_estimate = total_estimate[0] + " " + lang('hours');
    var minutes_estimate = "";
    if (total_estimate[1]) {
        if (total_estimate[1].length == 1) {
            minutes_estimate = ", " + Math.round(((total_estimate[1] * 60) / 10)) + " " + lang('minutes');
        } else {
            minutes_estimate = ", " + Math.round(((total_estimate[1] * 60) / 100)) + " " + lang('minutes');
        }
        var format_total_estimate = hours_estimate + minutes_estimate;
    } else {
        var format_total_estimate = hours_estimate;
    }
    return format_total_estimate;
}

ogTasks.drawGroupActions = function (group) {
    var html = '<a id="ogTasksPanelGroupSoloOn' + group.group_id + '" style="margin-right:15px;display:' + (group.solo ? "none" : "inline") + '" href="#" class="internalLink" onClick="ogTasks.hideShowGroups(\'' + group.group_id + '\')" title="' + lang('hide other groups') + '">' + (lang('hide others')) + '</a>' +
        '<a id="ogTasksPanelGroupSoloOff' + group.group_id + '" style="display:' + (group.solo ? "inline" : "none") + ';margin-right:15px;" href="#" class="internalLink" onClick="ogTasks.hideShowGroups(\'' + group.group_id + '\')" title="' + lang('show all groups') + '">' + (lang('show all')) + '</a>' +
        '<a href="#" class="internalLink ogTasksGroupAction ico-print" style="margin-right:15px;" onClick="ogTasks.printGroup(\'' + group.group_id + '\')" title="' + lang('print this group') + '">' + (lang('print')) + '</a>';
    if (ogTasks.userPermissions.can_add) {
        html += '<a href="#" class="internalLink ogTasksGroupAction ico-add" onClick="ogTasks.drawAddNewTaskForm(\'' + group.group_id + '\', 0, 0, 0, 0, \'task list - add task to group\')" title="' + lang('add a new task to this group') + '">' + (lang('add task')) + '</a>';
    }
    return html;
}


ogTasks.hideShowGroups = function (group_id) {
    var group = this.getGroup(group_id);
    if (group) {
        var soloOn = document.getElementById('ogTasksPanelGroupSoloOn' + group_id);
        var soloOff = document.getElementById('ogTasksPanelGroupSoloOff' + group_id);
        group.solo = !group.solo;

        soloOn.style.display = group.solo ? 'none' : 'inline';
        soloOff.style.display = group.solo ? 'inline' : 'none';

        for (var i = 0; i < this.Groups.length; i++) {
            if (this.Groups[i].group_id != group_id) {
                var groupEl = document.getElementById('ogTasksPanelGroup' + this.Groups[i].group_id);
                if (groupEl)
                    groupEl.style.display = group.solo ? 'none' : 'block';
            }
        }

        if (group.solo)
            this.expandGroup(group_id);
        else
            this.collapseGroup(group_id);
    }
}


ogTasks.expandGroup = function (group_id) {
    var div = document.getElementById('ogTasksGroupExpandTasks' + group_id);
    var divLink = document.getElementById('ogTasksGroupExpandTasksTitle' + group_id);
    if (div) {
        var group = this.getGroup(group_id);
        group.isExpanded = true;
        var html = '';
        var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
        var displayCriteria = bottomToolbar.getDisplayCriteria();
        var drawOptions = topToolbar.getDrawOptions();
        for (var i = og.noOfTasks; i < group.group_tasks.length; i++)
            html += this.drawTask(group.group_tasks[i], drawOptions, displayCriteria, group.group_id, 1);
        div.innerHTML = html;
        divLink.style.display = 'none';
        ogTasks.expandedGroups.push(group.group_id);

        //init action btns
        var btns = $("#ogTasksGroupExpandTasks" + group_id + " .tasksActionsBtn").toArray();
        og.initPopoverBtns(btns);

        //init breadcrumbs
        og.eventManager.fireEvent('replace all empty breadcrumb', null);

        /*		if (drawOptions.show_workspaces)
                    og.showWsPaths('ogTasksGroupExpandTasks' + group_id);*/
    }
}


ogTasks.collapseGroup = function (group_id) {
    var div = document.getElementById('ogTasksGroupExpandTasks' + group_id);
    var divLink = document.getElementById('ogTasksGroupExpandTasksTitle' + group_id);
    if (div) {
        var group = this.getGroup(group_id);
        group.isExpanded = false;
        div.innerHTML = '';
        divLink.style.display = 'block';
    }
}

ogTasks.expandCollapseAllTasksGroup = function (group_id) {
    var group = this.getGroup(group_id);
    if (group) {
        var expander = document.getElementById('ogTasksPanelGroupExpanderG' + group_id);
        if (group.alltasks_collapsed) {
            group.alltasks_collapsed = false;
            if (expander) expander.className = 'og-task-expander toggle_expanded';
            if (group.tasksDrawn === false) {
                // Tasks were deferred - inject them now. They arrive visible, so use
                // slideDown instead of slideToggle (slideToggle would collapse them).
                ogTasks._renderGroupTasks(group);
                group.tasksDrawn = true;
                og.eventManager.fireEvent('replace all empty breadcrumb', null);
                $("#ogTasksPanelGroup" + group.group_id + " .task-list-row").slideDown();
            } else {
                $("#ogTasksPanelGroup" + group.group_id + " .task-list-row").slideToggle();
            }
        } else {
            group.alltasks_collapsed = true;
            if (expander) expander.className = 'og-task-expander toggle_collapsed';
            $("#ogTasksPanelGroup" + group.group_id + " .task-list-row").slideToggle();
        }
    }
}


ogTasks.expandAllGroups = function () {
    var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
    var topToolbar    = Ext.getCmp('tasksPanelTopToolbarObject');
    var displayCriteria = bottomToolbar ? bottomToolbar.getDisplayCriteria() : null;
    var drawOptions     = topToolbar    ? topToolbar.getDrawOptions()        : null;

    for (var i = 0; i < ogTasks.Groups.length; i++) {
        var group = ogTasks.Groups[i];
        if (!group.alltasks_collapsed) continue;
        var expander = document.getElementById('ogTasksPanelGroupExpanderG' + group.group_id);
        group.alltasks_collapsed = false;
        if (expander) expander.className = 'og-task-expander toggle_expanded';
        if (group.tasksDrawn === false) {
            ogTasks._renderGroupTasks(group, drawOptions, displayCriteria);
            group.tasksDrawn = true;
        }
        $("#ogTasksPanelGroup" + group.group_id + " .task-list-row").slideDown();
    }
    og.eventManager.fireEvent('replace all empty breadcrumb', null);
};

ogTasks.collapseAllGroups = function () {
    for (var i = 0; i < ogTasks.Groups.length; i++) {
        var group = ogTasks.Groups[i];
        if (group.alltasks_collapsed) continue;
        var expander = document.getElementById('ogTasksPanelGroupExpanderG' + group.group_id);
        group.alltasks_collapsed = true;
        if (expander) expander.className = 'og-task-expander toggle_collapsed';
        $("#ogTasksPanelGroup" + group.group_id + " .task-list-row").slideUp();
    }
    $("#tasksPanelContent").scrollTop(0);
};

// Kept for backwards compatibility: expands all if all collapsed, collapses all otherwise.
ogTasks.expandCollapseAllGroups = function () {
    if (ogTasks.areAllGroupsCollapsed()) {
        ogTasks.expandAllGroups();
    } else {
        ogTasks.collapseAllGroups();
    }
}



ogTasks.areAllGroupsCollapsed = function () {
    if (ogTasks.Groups.length === 0) return false;
    for (var i = 0; i < ogTasks.Groups.length; i++) {
        if (!ogTasks.Groups[i].alltasks_collapsed) return false;
    }
    return true;
}

ogTasks.collapseAllLoadedGroups = function () {
    for (var i = 0; i < ogTasks.Groups.length; i++) {
        ogTasks.Groups[i].alltasks_collapsed = true;
        var expander = document.getElementById('ogTasksPanelGroupExpanderG' + ogTasks.Groups[i].group_id);
        if (expander) expander.className = 'og-task-expander toggle_collapsed';
    }
}

ogTasks.loadNextGroups = function () {
    if (ogTasks.allGroupsLoaded || ogTasks.isLoadingGroups) return;
    // Always use the user preference count so this button is not affected by any
    // inflated groupsPaginationCount set during state restoration.
    ogTasks.groupsPaginationCount = ogTasks.userPreferences.groupsPaginationCount;
    ogTasks.manualGroupLoading = true;
    var listenerId = og.eventManager.addListener('after ogTasks.Groups list completely loaded', function () {
        og.eventManager.removeListener(listenerId);
        ogTasks.manualGroupLoading = false;
        ogTasks.updateGroupPaginationButtons();
    });
    ogTasks.loadMoreGroups();
}

// remainingDepth tracks how many more batch loads are allowed in this "load all" run.
// It starts at 500 (enough for any realistic dataset) and decrements by 1 on each
// recursive call. This serves two purposes:
//   1. Prevents an infinite loop if allGroupsLoaded never becomes true due to a bug.
//   2. Ensures manualGroupLoading is always reset and the pagination buttons are
//      restored even if the server repeatedly returns incomplete responses.
// Each call loads one page of groups (groupsPaginationCount items), so 500 iterations
// would cover 500 * groupsPaginationCount tasks before the guard kicks in.
ogTasks.loadAllGroups = function (remainingDepth) {
    if (typeof remainingDepth === 'undefined') remainingDepth = 500;
    if (ogTasks.allGroupsLoaded || ogTasks.isLoadingGroups) return;
    if (remainingDepth <= 0) {
        // Safety net: stop the chain, re-enable scroll auto-loading and show the buttons.
        ogTasks.manualGroupLoading = false;
        ogTasks.updateGroupPaginationButtons();
        return;
    }
    ogTasks.manualGroupLoading = true;
    var listenerId = og.eventManager.addListener('after ogTasks.Groups list completely loaded', function () {
        og.eventManager.removeListener(listenerId);
        if (!ogTasks.allGroupsLoaded) {
            // isLoadingGroups is still true here; wait for task drawing to finish before retrying
            var breadcrumbListenerId = og.eventManager.addListener('replace all empty breadcrumb', function () {
                og.eventManager.removeListener(breadcrumbListenerId);
                setTimeout(function () {
                    ogTasks.loadAllGroups(remainingDepth - 1);
                }, 0);
            });
        } else {
            ogTasks.manualGroupLoading = false;
            ogTasks.updateGroupPaginationButtons();
        }
    });
    ogTasks.loadMoreGroups();
}

ogTasks.updateGroupPaginationButtons = function () {
    var existing = document.getElementById('tasksPanelGroupsPagination');
    if (existing) existing.parentNode.removeChild(existing);
    if (ogTasks.allGroupsLoaded) return;
    if (ogTasks.isLoadingGroups) return;
    var tbody = document.createElement('tbody');
    tbody.id = 'tasksPanelGroupsPagination';
    var tr = document.createElement('tr');
    var td = document.createElement('td');
    var thCount = document.querySelectorAll('#ogTasksPanelColNamesThead th').length;
    td.colSpan = thCount || (ogTasks.TasksList ? ogTasks.TasksList.tasks_list_cols.length : 1);
    td.className = 'tasks-group-pagination-link-container';
    var remaining = (ogTasks.totalGroupsCount > 0) ? ogTasks.totalGroupsCount - ogTasks.Groups.length : 0;
	if (remaining <= 0) return;
    var loadAllLabel = lang('show all groups') + (remaining > 0 ? ' (' + remaining + ')' : '');
    var buttons_html =
        (remaining > ogTasks.userPreferences.groupsPaginationCount
            ? '<button class="btn btn-sm btn-secondary-outline" onclick="ogTasks.loadNextGroups()">' + lang('show next n groups', ogTasks.userPreferences.groupsPaginationCount) + '</button>'
            : '') +
        '<button class="btn btn-sm btn-secondary-outline" onclick="ogTasks.loadAllGroups()">' + loadAllLabel + '</button>';
    td.innerHTML = buttons_html;
    tr.appendChild(td);
    tbody.appendChild(tr);
    var container = document.getElementById('tasksPanelContainer');
    if (container) container.appendChild(tbody);
}


ogTasks.drawAddTask = function (id_subtask, group_id, level) {
    //Draw indentation
    // FIXME: quick add task
    var padding = (15 * (level + 1)) + ogTasks.LevelMultiplier;
    return '<div class="ogTasksTaskRow" style="padding-left:' + padding + 'px">' +
        '</div>';

}


//************************************
//*		Main functions
//************************************

ogTasks.drawGroupTasks = function (group) {
    var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
    var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');

    var displayCriteria = bottomToolbar.getDisplayCriteria();
    var drawOptions = topToolbar.getDrawOptions();
    group.isExpanded = ogTasks.expandedGroups.indexOf(group.group_id) > -1;

    if (group.alltasks_collapsed) {
        // Defer rendering for collapsed groups: tasks are injected on first expand,
        // avoiding DOM work for content the user cannot see.
        group.tasksDrawn = false;
        // The template always renders the expander as toggle_expanded; correct it now.
        var expander = document.getElementById('ogTasksPanelGroupExpanderG' + group.group_id);
        if (expander) expander.className = 'og-task-expander toggle_collapsed';
        ogTasks._completeGroupDraw();
        return;
    }

    // Visible group: build the full group HTML as a string and inject in one DOM
    // operation, then yield to the browser before drawing the next group.
    ogTasks._renderGroupTasks(group, drawOptions, displayCriteria);
    group.tasksDrawn = true;
    setTimeout(function () { ogTasks._completeGroupDraw(); }, 0);
};

// Builds all task rows for a group as a single HTML string and injects them in
// one DOM operation.  Clocks and popover buttons are initialised once per group
// instead of once per task row, which is significantly faster for large groups.
// drawOptions and displayCriteria are optional; fetched from toolbars when omitted.
ogTasks._renderGroupTasks = function (group, drawOptions, displayCriteria) {
    
    if (!drawOptions || !displayCriteria) {
        var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        var topToolbar    = Ext.getCmp('tasksPanelTopToolbarObject');
        if (!bottomToolbar || !topToolbar) return;
        displayCriteria = bottomToolbar.getDisplayCriteria();
        drawOptions     = topToolbar.getDrawOptions();
    }

    var sb = new StringBuffer();
    for (var i = 0; i < group.group_tasks_order.length; i++) {
        var task_id = group.group_tasks_order[i];
        var html = ogTasks.drawTask(group.group_tasks[task_id], drawOptions, displayCriteria, group.group_id, 1, undefined, true);
        if (html) sb.append(html);
    }
    var groupEl = document.getElementById('ogTasksPanelGroup' + group.group_id);
    if (groupEl) groupEl.insertAdjacentHTML('beforeend', sb.toString());

    // Sync new task rows to the current column order immediately after insertion,
    // before the next browser repaint (which happens after the setTimeout(0) in
    // drawGroupTasks). Without this, rows are visible in the default template order
    // while <col>/<th> are already in the saved reordered layout — causing columns
    // (especially additional ones like tasksShowInvoicingStatus) to appear to
    // jump/shift when finalizeColHeaderFeatures() eventually reorders all TDs.
    if (typeof ogTasks._syncRowTds === 'function') {
        var $newRows = $();
        for (var i = 0; i < group.group_tasks_order.length; i++) {
            var $row = $('#ogTasksPanelTask' + group.group_tasks_order[i] + 'G' + group.group_id);
            if ($row.length) $newRows = $newRows.add($row);
        }
        if ($newRows.length) ogTasks._syncRowTds($newRows);
    }

    // Init clocks and popover buttons scoped to this group only.
    var groupJq = $('#ogTasksPanelGroup' + group.group_id);
    groupJq.find('.og-timeslot-work-started span').each(function () {
        var clockId = this.id.replace('timespan', '');
        og.startClock(clockId, parseInt($('#' + clockId + 'user_start_time').val()));
    });
    og.initPopoverBtns(groupJq.find('.tasksActionsBtn').toArray());
};

// Advances to the next group in the draw queue, or fires the completion events
// when all groups are done.  Called by drawGroupTasks after each group finishes
// (immediately for collapsed/deferred groups, via setTimeout for visible ones).
ogTasks._completeGroupDraw = function () {
    ++ogTasks.Groups.group_interval_iteration;
    var group = ogTasks.Groups[ogTasks.Groups.group_interval_iteration];

    if (typeof group !== 'undefined') {
        ogTasks.drawGroupTasks(group);
    } else if (ogTasks.Groups.group_interval_iteration === ogTasks.Groups.length) {
        ogTasks.finalizeColHeaderFeatures();
        og.eventManager.fireEvent('replace all empty breadcrumb', null);
        ogTasks.isLoadingGroups = false;
        ogTasks.updateGroupPaginationButtons();
        if (ogTasks.savedScrollTop > 0) {
            (function (target) {
                setTimeout(function () {
                    var taskContentEl = document.getElementById('tasksPanelContent');
                    if (taskContentEl) taskContentEl.scrollTop = target;
                }, 0);
            })(ogTasks.savedScrollTop);
            ogTasks.savedScrollTop = 0;
        }
        // Re-expand tasks whose lazily-loaded subtasks were open before the reload.
        var toExpand = ogTasks.savedExpandedSubtasks;
        ogTasks.savedExpandedSubtasks = {};
        for (var tid in toExpand) {
            var task = ogTasks.getTask(parseInt(tid, 10));
            if (!task) continue;
            // Reset so toggleSubtasks triggers the AJAX re-load instead of silently skipping
            // (task objects reused from cache still have toggleSubtasksShow=true from the previous expand).
            // Also reset isExpanded to false so the toggle inside toggleSubtasks flips it to true,
            // leaving isExpanded=true after the restore — required for onTaskLinkClick to capture
            // these tasks correctly on the next navigation.
            task.toggleSubtasksShow = false;
            task.isExpanded = false;
            var gids = toExpand[tid];
            for (var gi = 0; gi < gids.length; gi++) {
                ogTasks.toggleSubtasks(parseInt(tid, 10), gids[gi]);
            }
        }
        var task_content_div = $('#tasksPanelContent').get(0);
        if (!ogTasks.allGroupsLoaded && task_content_div && task_content_div.scrollHeight <= task_content_div.clientHeight) {
            ogTasks.loadMoreGroups();
        }
    }
};

ogTasks.drawAllGroupsTasks = function (first_group_to_draw_index) {
    og.loading();

    if (!first_group_to_draw_index || first_group_to_draw_index < 0) {
    	ogTasks.Groups.group_interval_iteration = 0;
    } else {
    	ogTasks.Groups.group_interval_iteration = first_group_to_draw_index;
    }

    var group = ogTasks.Groups[ogTasks.Groups.group_interval_iteration];

    ogTasks.drawGroupTasks(group);

    og.hideLoading();
};

ogTasks.drawTask = function (task, drawOptions, displayCriteria, group_id, level, target, returnHtml) {
    if (!task) return;
    //Draw indentation
    var pos = (task.divInfo && !isNaN(task.divInfo.length)) ? task.divInfo.length : 0;
    task.divInfo[pos] = {
        group_id: group_id,
        drawOptions: drawOptions,
        displayCriteria: displayCriteria,
        group_id: group_id,
        level: level
    };

    var html = this.drawTaskRow(task, drawOptions, displayCriteria, group_id, level, returnHtml);

    if (typeof returnHtml != 'undefined') {
        return html;
    } else if (typeof target != 'undefined') {
        $(target).append(html);
    } else {
        $("#ogTasksPanelGroup" + group_id).append(html);
    }
}

ogTasks.removeTaskFromView = function (task) {
    $("[id^='ogTasksPanelTask" + task.id + "']").each(function (index) {
        $(this).remove();
    });

    //parent
    if (task.parentId > 0) {
        var parent = ogTasksCache.getTask(task.parentId);
        if (typeof parent != "undefined" && parent.subtasksIds.length == 0) {
            ogTasks.reDrawTask(parent);
        }
    }
}

ogTasks.reDrawTask = function (task) {
    //ogTasks.drawTask
    var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
    var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
    var displayCriteria = bottomToolbar.getDisplayCriteria();
    var drawOptions = topToolbar.getDrawOptions();

    //parent
    if (drawOptions.show_subtasks_structure) {
        if (task.parentId > 0) {
            var parent = ogTasksCache.getTask(task.parentId);
            if (typeof parent != 'undefined') {
                //if is not rendered and is subtask?
                $("[id^='ogTasksPanelSubtasksT" + parent.id + "']").each(function (index) {
                    var group_id = $(this).attr('id');
                    var remove = "ogTasksPanelSubtasksT" + task.id + "G";
                    group_id = group_id.replace(remove, "");

                    if ($("#ogTasksPanelTask" + task.id + "G" + group_id).length == 0) {
                        var html = ogTasks.drawTask(task, drawOptions, displayCriteria, group_id, $(this).attr("data-level"), null, true);
                        $(this).append(html);
                        //init action btns
                        var btns = $("#ogTasksPanelTask" + task.id + "G" + group_id + " .tasksActionsBtn").toArray();
                        og.initPopoverBtns(btns);
                    }
                });
                //update parent task
                $("[id^='ogTasksPanelTask" + parent.id + "']").each(function (index) {
                    var group_id = $(this).attr('id');
                    var remove = "ogTasksPanelTask" + parent.id + "G";
                    group_id = group_id.replace(remove, "");

                    var html = ogTasks.drawTask(parent, drawOptions, displayCriteria, group_id, $(this).attr("data-level"), null, true);
                    $("#ogTasksPanelTask" + parent.id + "G" + group_id).replaceWith(html);
                    //init action btns
                    var btns = $("#ogTasksPanelTask" + parent.id + "G" + group_id + " .tasksActionsBtn").toArray();
                    og.initPopoverBtns(btns);
                });
            }
        } else {
            //if is not rendered redraw all groups from server
            if ($("[id^='ogTasksPanelTask" + task.id + "']").length == 0) {
                ogTasks.Groups.loaded = false;
                ogTasks.draw();
                return;
            }
        }
    } else {
        //if is not rendered redraw all groups from server
        if ($("[id^='ogTasksPanelTask" + task.id + "']").length == 0) {
            ogTasks.Groups.loaded = false;
            ogTasks.draw();
            return;
        }
    }

    //update this task rows
    $("[id^='ogTasksPanelTask" + task.id + "']").each(function (index) {
        var group_id = $(this).attr('id');
        var remove = "ogTasksPanelTask" + task.id + "G";
        group_id = group_id.replace(remove, "");

        var html = ogTasks.drawTask(task, drawOptions, displayCriteria, group_id, $(this).attr("data-level"), null, true);
        $("#ogTasksPanelTask" + task.id + "G" + group_id).replaceWith(html);
        //init action btns
        var btns = $("#ogTasksPanelTask" + task.id + "G" + group_id + " .tasksActionsBtn").toArray();
        og.initPopoverBtns(btns);
    });

    //start all clocks on the list
    var clocks = $(".og-timeslot-work-started span");

    for (i = 0; i < clocks.length; i++) {
        var clockId = clocks[i].id;
        clockId = clockId.replace("timespan", "");
        var user_start_time_string = $("#" + clockId + "user_start_time + span").html();

        // Get seconds from user_start_time_string
        if(user_start_time_string != "") {
            var time_array = user_start_time_string.split(':');
            var seconds = parseInt(time_array[0] * 60 * 60) + parseInt(time_array[1] * 60) + parseInt(time_array[2]);
        } else {
            var seconds = 0;
        }
        og.startClock(clockId, seconds);
    }

    og.eventManager.fireEvent('replace all empty breadcrumb', null);

    // draw parent task's elbows
    if (task.parentId > 0) {
    	ogTasks.drawElbows(task.parentId);
    }

    // Re-apply column order to every row that was just redrawn.
    // reDrawTask() renders rows via the Handlebars template (default tasks_list_cols order).
    // If the user has reordered columns we must sync the fresh rows to match the header order.
    // This must live here (not only in drawTaskRowAfterEdit) because getGroupsForTask() can
    // trigger a second async reDrawTask() call via addTaskToGroup(), which would overwrite
    // the sync applied by drawTaskRowAfterEdit before this fix was added.
    if (typeof ogTasks._syncRowTds === 'function') {
        ogTasks._syncRowTds($("[id^='ogTasksPanelTask" + task.id + "']"));
        if (drawOptions.show_subtasks_structure && task.parentId > 0) {
            var parentForSync = ogTasksCache.getTask(task.parentId);
            if (parentForSync) {
                ogTasks._syncRowTds($("[id^='ogTasksPanelTask" + parentForSync.id + "']"));
            }
        }
    }

    // Restore the inline-edit action bar if it was sitting on a cell in the
    // row that was just replaced.  Without this, the user would need to wiggle
    // the mouse to re-trigger mouseenter on the fresh cell — especially
    // noticeable on the second reDrawTask() fired by the getGroupsForTask()
    // async callback (addTaskToGroup path).
    if (ogTasks.InlineCellEditor && typeof ogTasks.InlineCellEditor.onRowRedrawn === 'function') {
        ogTasks.InlineCellEditor.onRowRedrawn(task.id);
    }
}

ogTasks.drawTaskRow = function (task, drawOptions, displayCriteria, group_id, level, returnHtml) {
    var sb = new StringBuffer();
    var tgId = "T" + task.id + 'G' + group_id;
    
    // check if task has been already rendered in this group, dont check this if only the html is to return and update the current row container
    if (!returnHtml && $("#tasksPanel"+ og.genid +" #ogTasksPanelTask"+ task.id +"G"+ group_id).length > 0) {
    	return;
    }

    //checkbox container class by priority
    var priorityColor = "priority-default";
    if (typeof task.priority != 'undefined') {
        priorityColor = "priority-" + task.priority;
    }

    //subtask expander
    var showSubtasksExpander = false;
    var subtasksExpander = "toggle_collapsed";
    if (drawOptions.show_subtasks_structure && task.subtasksIds.length > 0) {
        showSubtasksExpander = true;
        if (task.isExpanded) {
            subtasksExpander = "toggle_expanded";
        }

    }


    //Draw the Assigned user
    var assignedTo = false;
    if (task.assignedToId) {
        assignedTo = og.allUsers[task.assignedToId];
    }

    //Draw the Assigned user
    var assignedBy = false;
    if (task.assignedById) {
        assignedBy = og.allUsers[task.assignedById];
    }

    //Draw the task name
    taskName = task.title;
    var tooltip = '';
    //if is completed
    if (task.status > 0) {
        var user = this.getUser(task.completedById, true);
        if (user) {
            var time = new Date(task.completedOn * 1000);
            var now = new Date();
            var timeFormatted = time.getYear() != now.getYear() ? time.dateFormat('M j, Y') : time.dateFormat('M j');
            tooltip = lang('completed by name on', og.clean(user.name), timeFormatted).replace(/'\''/g, '\\\'');
        }
    }

    //Member Path
    mem_path = "";
    var mpath = typeof(task.memPath)=='string' ? Ext.util.JSON.decode(task.memPath) : {};

    if (mpath) mem_path = og.getEmptyCrumbHtml(mpath, ".task-breadcrumb-container", og.breadcrumbs_skipped_dimensions);

    //dimesions breadcrumbs
    var dim_classification = new Array();
    //remove empty values from show_dimension_cols
    drawOptions.show_dimension_cols = drawOptions.show_dimension_cols.filter(function (x) { return (x != '') });
    for (var x = 0; x < drawOptions.show_dimension_cols.length; x++) {
        did = drawOptions.show_dimension_cols[x];
        var dim_mpath = {};
        var exclude_parents_path = false;

        if (!isNaN(did)) {
            dim_mpath[did] = mpath[did];
        }

        if (ogTasks.override_task_dim_col_value && ogTasks.override_task_dim_col_value.length > 0) {
            for (var i = 0; i < ogTasks.override_task_dim_col_value.length; i++) {
                var fn = ogTasks.override_task_dim_col_value[i];
                if (typeof(fn) == 'function') {
                    var result = fn.call(null, did, mpath);
                    if (result) {
                        dim_mpath = result.dim_mpath;
                        exclude_parents_path = result.exclude_parents_path;
                    }
                }
            }
        }

        if (isNaN(did)) {
            for (z in dim_mpath) {
                did = z;
                break;
            }
        }
        // ignore disabled dimensions
        if (og.config.enabled_dimensions.indexOf(did+"") == -1) {
        	continue;
        }

        var dim_mem_path = "";
        if (typeof mpath[did] != "undefined") {
            dim_mem_path = og.getEmptyCrumbHtml(dim_mpath, ".task-breadcrumb-container", null, null, exclude_parents_path,true);
        }

        var key = 'lp_dim_' + did + '_show_as_column';
        if (og.preferences['listing_preferences'][key]) {
            dim_classification.push(
                {
                    id:          'task_clasification_dim_' + drawOptions.show_dimension_cols[x],
                    dim_id:      did,   // numeric dimension ID, used by ICE breadcrumb dialog
                    dim_mem_path: dim_mem_path
                }
            );
        }
    }

    //Dates
    var now = new Date();
    var start_date = '';
    var start_date_overdue = false;
    task.already_started = false;
    if (task.startDate) {
        var date = new Date(task.startDate * 1000);
        date = new Date(Date.parse(date.toUTCString().slice(0, -4)));
        var hm_format = task.useStartTime ? (og.preferences['time_format_use_24'] == 1 ? ' <br> G:i' : ' <br> g:i A') : '';
        start_date = date.dateFormat(og.preferences['date_format'] + hm_format);
        if (date < now) task.already_started = true;
    }
    var due_date = '';
    var due_date_late = false;
    if (task.dueDate) {
        var date = new Date((task.dueDate) * 1000);
        date = new Date(Date.parse(date.toUTCString().slice(0, -4)));
        var hm_format = task.useDueTime ? (og.preferences['time_format_use_24'] == 1 ? ' <br> G:i' : ' <br> g:i A') : '';
        due_date = date.dateFormat(og.preferences['date_format'] + hm_format);
        if (task.status == 0 && date < now) {
            due_date_late = true;
        }
    }

    //Draw time tracking
    // Computed unconditionally (not gated on drawOptions.show_time) because the
    // resulting state now also decides which "time" entries feed the taskActions
    // array below — needed for a correct "..." overflow menu even when the live
    // clock column itself is hidden from the visible row.
    var userIsWorking = false;
    var userPaused = false;
    var userStartTime = 0;
    var userState = 'started';
    var userPausedTime = '';
    var workingOnUsers = new Array();
    var showWorkingOnUsers = false;
    //is working
    if (task.workingOnIds) {
        var ids = (task.workingOnIds + ' ').split(',');
        for (var i = 0; i < ids.length; i++) {
            if (this.currentUser && ids[i] == this.currentUser.id) {
                userIsWorking = true;
                userStartTime = task.workingOnTimes[i];
                var pauses = (task.workingOnPauses + ' ').split(',');
                userPaused = pauses[i] == 1;
                if (userPaused) {
                    userState = 'paused';
                    userPausedTime = og.calculateTimeForClock(new Date(), userStartTime);
                }
            } else {
                var usrId = parseInt(ids[i]);
                workingOnUsers.push(og.allUsers[usrId]);
                showWorkingOnUsers = true;
            }
        }
    }
    //task actions
    var taskActions = new Array();

    // Mirrors the old Handlebars {{#unless_or task.is_parent task.prevent_add_time_to_parent_task}}
    // guard: unless_or(a,b) renders its body unless BOTH a and b are true, so time-tracking
    // actions are hidden only when the task is a parent AND explicitly flagged to prevent it.
    // Once the task's worked time has reached/exceeded its estimated hours
    // (task.estimated_hours_limit_reached, set in advanced_core_task_info_additional_data),
    // actions that would ADD new time are shown disabled (not hidden) instead — see act_disabled
    // below. Stopping/pausing/cancelling an already-running timer is never blocked by this.
    var canAddTimeToTask = !(task.is_parent && task.prevent_add_time_to_parent_task);
    var estimatedHoursLimitReached = !!task.estimated_hours_limit_reached;

    taskActions.push({
        act_order_key: 'add_sub_task',
        act_collapsed: !drawOptions.show_quick_add_sub_tasks,
        act_onclick: "ogTasks.drawAddNewTaskForm",
        act_onclick_param: [{param_val: "'" + group_id + "',"}, {param_val: task.id + ","}, {param_val: level+ ","}, {param_val: "'',"}, {param_val: "0,"}, {param_val: "'task list - line add subtask'"}],
        act_text: lang('add subtask'),
        act_id: "ogTasksPanelExpander" + tgId,
        act_class: "add-subtask-link",
        act_icon: "list-plus"
    });
    taskActions.push({
        act_order_key: 'edit',
        act_collapsed: !drawOptions.show_quick_edit,
        act_onclick: "ogTasks.drawEditTaskForm",
        act_onclick_param: [{param_val: task.id + ","}, {param_val: "'" + group_id + "'"}],
        act_text: lang('edit'),
        act_class: "edit",
        act_icon: "pencil-line"
    });

    if (task.mark_as_started) {
        taskActions.push({
            act_order_key: 'mark_as_started',
            act_collapsed: !drawOptions.show_quick_mark_as_started,
            act_onclick: "ogTasks.ToggleChangeMarkAsStarted",
            act_onclick_param: [{param_val: task.id}],
            act_text: lang('unmark as started this task'),
            act_class: "undo",
            act_icon: "undo-2"
        });
    } else {
        taskActions.push({
            act_order_key: 'mark_as_started',
            act_collapsed: !drawOptions.show_quick_mark_as_started,
            act_onclick: "ogTasks.ToggleChangeMarkAsStarted",
            act_onclick_param: [{param_val: task.id}],
            act_text: lang('mark as started this task'),
            act_class: "start",
            act_icon: "play"
        });
    }

    if (task.status) {
        taskActions.push({
            act_order_key: 'complete',
            act_collapsed: !drawOptions.show_quick_complete,
            act_onclick: "ogTasks.ToggleCompleteStatus",
            act_onclick_param: [{param_val: task.id + ","}, {param_val: task.status}],
            act_text: lang('reopen this task'),
            act_class: "reopen",
            act_icon: "refresh-cw"
        });
    } else {
        taskActions.push({
            act_order_key: 'complete',
            act_collapsed: !drawOptions.show_quick_complete,
            act_onclick: "ogTasks.ToggleCompleteStatus",
            act_onclick_param: [{param_val: task.id + ","}, {param_val: task.status}],
            act_text: lang('complete this task'),
            act_class: "complete",
            act_icon: "check"
        });
    }

    if (canAddTimeToTask) {
        taskActions.push({
            act_order_key: 'quick_time',
            act_collapsed: !drawOptions.show_time_quick,
            act_disabled: estimatedHoursLimitReached,
            act_onclick: estimatedHoursLimitReached ? "ogTasks.doNothing" : "ogTasks.AddWorkTime",
            act_onclick_param: estimatedHoursLimitReached ? [] : [{param_val: "[" + task.id + "]"}],
            act_text: estimatedHoursLimitReached ? lang('cannot add time task estimated hours reached') : lang('add work'),
            act_class: "time",
            act_icon: "clock-plus"
        });
    }

    if (canAddTimeToTask) {
        if (userIsWorking) {
            taskActions.push({
                act_order_key: 'time',
                act_collapsed: !drawOptions.show_time,
                act_hide_from_row: true,
                act_onclick: "ogTasks.closeTimeslot",
                act_onclick_param: [{param_val: "[" + task.id + "]"}],
                act_text: lang('close_work'),
                act_class: "stop",
                act_icon: "circle-stop"
            });
            if (userPaused) {
                taskActions.push({
                    act_order_key: 'time',
                    act_collapsed: !drawOptions.show_time,
                    act_hide_from_row: true,
                    act_onclick: "ogTasks.executeAction",
                    act_onclick_param: [{param_val: "\"resume_work\",[" + task.id + "]"}],
                    act_text: lang('pause_work'),
                    act_class: "play",
                    act_icon: "circle-play"
                });
            } else {
                taskActions.push({
                    act_order_key: 'time',
                    act_collapsed: !drawOptions.show_time,
                    act_hide_from_row: true,
                    act_onclick: "ogTasks.executeAction",
                    act_onclick_param: [{param_val: "\"pause_work\",[" + task.id + "]"}],
                    act_text: lang('pause_work'),
                    act_class: "pause",
                    act_icon: "circle-pause"
                });
            }
            taskActions.push({
                act_order_key: 'time',
                act_collapsed: !drawOptions.show_time,
                act_hide_from_row: true,
                act_onclick: "ogTasks.executeAction",
                act_onclick_param: [{param_val: "\"cancel_work\",[" + task.id + "]"}],
                act_text: lang('discard_work'),
                act_class: "cancel",
                act_icon: "circle-x"
            });
        } else if (task.canAddTimeslots) {
            taskActions.push({
                act_order_key: 'time',
                act_collapsed: !drawOptions.show_time,
                act_hide_from_row: true,
                act_disabled: estimatedHoursLimitReached,
                act_onclick: estimatedHoursLimitReached ? "ogTasks.doNothing" : "ogTasks.executeAction",
                act_onclick_param: estimatedHoursLimitReached ? [] : [{param_val: "'start_work',[" + task.id + "],'','#tasksPanelContainer'"}],
                act_text: estimatedHoursLimitReached ? lang('cannot add time task estimated hours reached') : lang('start_work'),
                act_class: "play",
                act_icon: "timer"
            });
        }
    }

    // Sort by the user's configured quick-action order (persisted alongside column
    // config); this single sort drives both the visible icon order and the "..."
    // popover order. An explicit push-index tiebreaker keeps the relative order of
    // the multiple 'time' entries stable regardless of JS engine sort stability.
    var actionsOrder = (og.config && og.config.tasks_columns_config && og.config.tasks_columns_config.actionsOrder)
        || ogTasks.DEFAULT_ACTIONS_ORDER;
    var actionsOrderIndex = {};
    for (var aoi = 0; aoi < actionsOrder.length; aoi++) { actionsOrderIndex[actionsOrder[aoi]] = aoi; }
    for (var tai = 0; tai < taskActions.length; tai++) { taskActions[tai]._pushIdx = tai; }
    taskActions.sort(function (a, b) {
        var ai = actionsOrderIndex.hasOwnProperty(a.act_order_key) ? actionsOrderIndex[a.act_order_key] : 999;
        var bi = actionsOrderIndex.hasOwnProperty(b.act_order_key) ? actionsOrderIndex[b.act_order_key] : 999;
        return ai !== bi ? ai - bi : a._pushIdx - b._pushIdx;
    });

    //mark the last collapsed action with a bool
    for (var i = taskActions.length; i > 0; i--) {
        if (taskActions[i - 1].act_collapsed) {
            taskActions[i - 1].act_last = true;
            break;
        }
    }

    var collapsed_actions = 0;
    var show_quick_actions_container = false;
    for (var i = taskActions.length; i > 0; i--) {
        if (!taskActions[i - 1].act_collapsed) {
            show_quick_actions_container = true;
        } else {
            collapsed_actions++;
        }
    }

    var show_actions_popover_button = collapsed_actions > 0;

    //updating waiting tasks
    waiting_tasks = task.dependants;


    var row_total_cols = [];
    for (var key in ogTasks.TotalCols) {
        var row_field = ogTasks.TotalCols[key].row_field;
        var color = '#888';
        if (row_field == 'worked_time_string' && task.TimeEstimate != '0' && parseInt(task.TimeEstimate) < parseInt(task.worked_time)) {
            color = '#f00'; 
        }
        row_total_cols.push({text: task[row_field], color: color});
    }

    // dimension columns
    var row_dim_cols = [];
    for (did in og.dimensions_info) {
        if (isNaN(did)) continue;
        var key = 'lp_dim_' + did + '_show_as_column';
        if (og.preferences['listing_preferences'][key]) {

        }
    }

    //get template for the row
    if (typeof ogTasks.task_list_row_template == "undefined") {
        var source = $("#task-list-row-template").html();
        //compile the template
        var template = Handlebars.compile(source);
        ogTasks.task_list_row_template = template;
    }

    var action_trigger = "focus";
    var is_safari = navigator.userAgent ? navigator.userAgent.indexOf("Safari") > -1 : false;
    var is_safari_vendor = navigator.vendor ? navigator.vendor.indexOf("Apple") > -1 : false;
    if (is_safari && is_safari_vendor) {
        action_trigger = "click";
    }

    start_date_overdue = task.already_started && !task.mark_as_started;
    //template data
    var data = {
        task: task,
        task_actions: taskActions,
        action_trigger: action_trigger,
        show_quick_actions_container: show_quick_actions_container,
        show_actions_popover_button: show_actions_popover_button,
        can_add_timeslots: task.canAddTimeslots,
        genid: og.genid,
        start_date: start_date,
        due_date: due_date,
        due_date_late: due_date_late,
        draw_options: drawOptions,
        subtasksExpander: subtasksExpander,
        showSubtasksExpander: showSubtasksExpander,
        priorityColor: priorityColor,
        tgId: tgId,
        group_id: group_id,
        assigned_to_show_name: og.config.tasks_show_assigned_to_name,
        assigned_to: assignedTo,
        assigned_by: assignedBy,
        task_id: task.id,
        view_url: og.getUrl('task', 'view', {id: task.id}),
        task_name: taskName,
        tool_tip: tooltip,
        mem_path: mem_path,
        dim_classification: dim_classification,
        percent_completed_bar: ogTasks.buildTaskPercentCompletedBar(task),
        level: level,
        user_is_working: userIsWorking,
        user_paused: userPaused,
        user_paused_time: userPausedTime,
        user_state: userState,
        user_start_time: userStartTime,
        working_on_users: workingOnUsers,
        show_working_on_users: showWorkingOnUsers,
        row_total_cols: row_total_cols,
        start_date_overdue: start_date_overdue
    }

    if (ogTasks.additional_task_list_columns) {
        data.additional_task_list_columns = [];
        for (var i = 0; i < ogTasks.additional_task_list_columns.length; i++) {
            var col = ogTasks.additional_task_list_columns[i];
            if (!drawOptions[col.id]) continue; // skip columns not enabled in current view
            data.additional_task_list_columns.push({
                id: col.id,
                cls: col.cls ? col.cls : '',
                html: task.additional_data[col.id] ? task.additional_data[col.id].html : '<td class="' + col.id + '"></td>'
            });
        }
    }

    // Ensure task.custom_properties contains an entry for EVERY enabled CP —
    // even those with no value on this task — so the template loop
    // {{#each task.custom_properties}} + {{#if (isTasksColumnCPVisible id)}}
    // renders exactly as many <td> elements as tasks_list_cols has TH entries.
    // Without this, tasks missing a CP value produce fewer TDs than headers,
    // breaking the _syncRowTds count check and leaving the row in default order.
    if (ogTasks.custom_properties) {
        var taskCpMap = {};
        if (task.custom_properties) {
            for (var _k = 0; _k < task.custom_properties.length; _k++) {
                taskCpMap[task.custom_properties[_k].id] = task.custom_properties[_k];
            }
        }
        var paddedCps = [];
        for (var _cpIdx in ogTasks.custom_properties) {
            var _cpDef = ogTasks.custom_properties[_cpIdx];
            if (typeof _cpDef !== 'object') continue;
            if (ogTasks.userPreferences['tasksShowCP_' + _cpDef.id] != 1) continue;
            paddedCps.push(taskCpMap[_cpDef.id] || {id: _cpDef.id, value: ''});
        }
        // Use a shallow task copy so we don't mutate the cached task object.
        data.task = $.extend({}, task, {custom_properties: paddedCps});
    }

    //instantiate the template
    var html = ogTasks.task_list_row_template(data);

    sb.append(html);
    return sb.toString();
}

ogTasks.closeTimeslot = function (tId,callback) {
    if (og.config.tasks_show_description_on_time_forms) {
        //get template
        var source = $("#small-task-timespan-template").html();
        //compile the template
        var template = Handlebars.compile(source);

        //template data
        var data = {
            taskId: tId,
            genid: og.genid
        }

        //instantiate the template
        var html = template(data);

        var modal_params = {
            'escClose': true,
            'overlayClose': true,
            'minWidth': 400,
            'minHeight': 200,
            'closeHTML': '<a id="ogTasksPanelAT_close_link" class="modal-close modal-close-img"></a>'
        };


        $.modal(html, modal_params);

        $("#small-task-timespan-modal-form" + og.genid).submit(function (event) {
            var parameters = [];
            var form_params = $(this).serializeArray();

            for (i = 0; i < form_params.length; i++) {
                parameters[form_params[i].name] = form_params[i].value;
            }

            ogTasks.executeActionFinal("close_work", tId, parameters['timeslot[description]'],callback);

            ogTasks.closeModal();

            event.preventDefault();
        });
    } else {
        ogTasks.executeActionFinal("close_work", tId,'',callback);
    }
}

ogTasks.drawSubtasks = function (params) {
    try {
        var task = ogTasksCache.getTask(params.task_id);
        var group_id = params.group_id;

        var $task_view = $('#ogTasksPanelTask' + task.id + 'G' + group_id);
        if ($task_view.length === 0) {
            // Parent task is not in the DOM (e.g. filtered out). Subtasks are already
            // rendered at root level by the main draw loop — nothing to expand here.
            return;
        }
        var subtasks_container_id = 'SubtasksT' + task.id + 'G' + group_id;
        var level = parseInt($task_view.attr("data-level")) + ogTasks.LevelMultiplier;

        var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
        var displayCriteria = bottomToolbar.getDisplayCriteria();
        var drawOptions = topToolbar.getDrawOptions();

        var $newRows = $();
        var $group = $('#ogTasksPanelGroup' + group_id);
        for (var i = 0; i < task.subtasksIds.length; i++) {
            var subtask = ogTasks.getTask(task.subtasksIds[i]);
            if (!subtask) continue;
            // Subtask may already be in the list at root level (parent in another group / filter edge case).
            if ($('#ogTasksPanelTask' + subtask.id + 'G' + group_id).length > 0) {
                continue;
            }
            var subtask_row = ogTasks.drawTask(subtask, drawOptions, displayCriteria, group_id, level, null, 1);
            subtask_row = $(subtask_row).attr("class", $task_view.attr("class"));

            $last_child = $group.find('[data-parent-id="' + task.id + '"]').last();
            if ($last_child.length > 0) {
                $last_child.after(subtask_row);
            } else {
                $task_view.after(subtask_row);
            }

            $newRows = $newRows.add(subtask_row);
        }
        // Rows are rendered in the default column order; fix td order if columns were reordered.
        ogTasks._syncRowTds($newRows);
        var btns = $group.find('[data-parent-id="' + task.id + '"] .tasksActionsBtn').toArray();
        og.initPopoverBtns(btns);

        og.eventManager.fireEvent('replace all empty breadcrumb', null);
        ogTasks.drawElbows(params.task_id);
    } finally {
        ogTasks._finishSubtasksLoad(params);
    }
}

/**
 * This function refreshh the elbows of task passed in parameter and his father
 * @param task_id
 */
ogTasks.refresElbows = function (task_id) {
    var task_parent_id = $("[data-task-id='" + task_id + "']").first().attr("data-parent-id");
    var task_grand_id = $("[data-task-id='" + task_parent_id + "']").first().attr("data-parent-id");
    ogTasks.drawElbows(task_grand_id);//for my father
    ogTasks.drawElbows(task_parent_id);//for me
}

/**
 * Function that will remove all the childs taks with recursivity
 * Also makes the toggle of the parent task id passed is not collapsed
 * Then is gonna click again to the toggle to retrive again the task and will draw correctly the elbows
 * @param parent_id
 */
ogTasks.removeTaskToRefreshElbows = function (parent_id) {
    $("[data-parent-id=" + parent_id + "]").each(function (index,element) {
        var $element = $(element);
        var current_task_id = $element.attr("data-task-id");
        //check if the element have childs
        if ($("[data-parent-id=" + current_task_id + "]").length > 0) {
            ogTasks.removeTaskToRefreshElbows(current_task_id);
        }
        $element.remove();
    });
    var $toggle = $("[data-task-toggle="+parent_id+"]").first();
    $toggle.removeClass("toggle_expanded").addClass("toggle_collapsed");
    $toggle.click();
    $toggle.removeClass("toggle_collapsed").addClass("toggle_expanded");
}

/**
 * This fucntion that work all the elbows for the childs of the task
 * @param parentTaskId
 */
ogTasks.drawElbows = function (parentTaskId) {
    var task_level = parseInt($("[data-parent-id='" + parentTaskId + "']").attr("data-level"));
    var $parent = $("[data-task-id='" + parentTaskId + "']").first();
    var margin_of_level = task_level / 2;
    var count_of_eblow_lines = 0;
    var margin_elbow_line = 0;
    if (task_level <= 21) {//this mean is the first pass of elbows and we need it without margin
        margin_of_level = 0;
    } else {
        //this mean that we had displayed a 2 sublevels
        count_of_eblow_lines = parseInt((task_level - ogTasks.LevelMultiplier) / ogTasks.LevelMultiplier);
        var elbows_line_parent = $parent.find(".task-elbow-line").length;
        if ($parent.find("[data-elbow-type]").hasClass("task-elbow-end")) {
            //check if my father is the last element, because of that we dont draw the last elbow line
            count_of_eblow_lines = elbows_line_parent;
            if (elbows_line_parent > 0) {
                margin_elbow_line = parseInt($parent.find(".task-elbow-line").css('marginLeft').replace("px", ""));
            }
        } else {
            //check i don`t have more elbows line than my myfather
            if (elbows_line_parent == 0) {
                if ($parent.find(".task-elbow").length > 0) {
                    margin_elbow_line = parseInt($parent.find(".task-elbow").css('marginLeft').replace("px", ""));
                }
                count_of_eblow_lines = 1
            } else {
                count_of_eblow_lines = elbows_line_parent + 1;
            }
        }
    }
    //here we clean all the elbowls line in the parentTaskid to not duplicated class
    $("[data-parent-id='" + parentTaskId + "'][data-elbow-line-container]").removeClass("task-elbow");
  //  $("[data-parent-id='" + parentTaskId + "'][data-elbow-line-container]").removeClass("task-elbow-end");
    $("[data-parent-id='" + parentTaskId + "']").each(function (index, value) {
        var $value = $(value);
        for (var i = count_of_eblow_lines; i > 0; i--) {
            var margin_line = (((i - 1) * ogTasks.LevelMultiplier) + margin_elbow_line);
            if (i == count_of_eblow_lines) {
                //this is the last to show align to the right line so we have to take the same margin as the elbow or elbow-end of my parent
                var my_parent = $("[data-task-id=" + $value.attr('data-parent-id') + "]").first();
                if (my_parent.find(".task-elbow").length > 0) {
                    margin_line = parseInt(my_parent.find(".task-elbow").css('marginLeft').replace("px", ""));
                } else {
					var elbow = my_parent.find(".task-elbow-line").last().css('marginLeft');
					if (elbow) {
						margin_line = parseInt(elbow.replace("px", ""));
					}
                }
            }
            $value.find("[data-elbow-line-container]").prepend("<span class='task-elbow-line' style='margin-left: " + margin_line + "px;'></span>");

        }
        $value.find("[data-elbow-type]").addClass("task-elbow").css("marginLeft", parseInt(margin_of_level));
        if (margin_of_level > 0) {
            $value.find("[data-task-span-name]").css("marginLeft", parseInt(margin_of_level) + 25);
        }
    });
    
    $('.tasks-panel-group').each(function (i,v){
        var rowAux = $(v).find("[data-parent-id='" + parentTaskId + "'].task-list-row-template:last");
        rowAux.find("[data-elbow-type]").removeClass("task-elbow").addClass("task-elbow-end").css("marginLeft", parseInt(margin_of_level));
    });
    //$("[data-parent-id='" + parentTaskId + "'].task-list-row-template:last").find("[data-elbow-type]").removeClass("task-elbow").addClass("task-elbow-end").css("marginLeft", parseInt(margin_of_level));
}


ogTasks.ToggleCompleteStatus = function (task_id, status) {
    var related = false;
    if (status == 0) {
        var task = ogTasks.getTask(task_id);
        for (var j = 0; j < task.subtasks.length; j++) {
            if (task.subtasks[j].status == 0) {
                related = true;
            }
            if (related) {
                break;
            }
        }
    }

    if (related) {
        this.dialog = new og.TaskCompletePopUp(task_id);
        this.dialog.setTitle(lang('do complete'));
        this.dialog.show();
    } else {
        ogTasks.ToggleCompleteStatusOk(task_id, status, '');
    }
}

ogTasks.ToggleCompleteStatusOk = function (task_id, status, opt) {
    var action = (status == 0) ? 'complete_task' : 'open_task';
    og.openLink(og.getUrl('task', action, {id: task_id, quick: true, options: opt, req_channel: 'task list - line ' + action}), {
        callback: function (success, data) {
            if (success || !data.errorCode) {
                if (data.task) {
                    //Set task data
                    var task = ogTasksCache.addTasks(data.task);

                    //update dependants
                    if (task.status) {
                        ogTasks.updateDependantTasks(task.id, false);
                    } else {
                        ogTasks.updateDependantTasks(task.id, true);
                    }

                    ogTasks.UpdateTask(task.id, false);
                } else {
                    ogTasks.UpdateTask(task_id, true);
                }

                if (data.more_tasks) {
                    for (var j = 0; j < data.more_tasks.length; j++) {
                        ogTasks.drawTaskRowAfterEdit({'task': data.more_tasks[j]});
                    }
                }
                ogTasks.refreshGroupsTotals();
            }
        },
        scope: this
    });
}

ogTasks.ToggleChangeMarkAsStarted = function (task_id) {

    og.openLink(og.getUrl('task', "change_mark_as_started", {id: task_id, quick: true, req_channel: 'task list - line mark as started'}), {
        callback: function (success, data) {
            if (!success || data.errorCode) {

            } else {
                if (data.task) {
                    //Set task data
                    var task = ogTasksCache.addTasks(data.task);

                    ogTasks.UpdateTask(task.id, false);
                } else {
                    ogTasks.UpdateTask(task_id, true);
                }

                ogTasks.refreshGroupsTotals();
            }
        },
        scope: this
    });
}

ogTasks.loadTimeslotUsers = function (genid, task_id) {

    og.openLink(og.getUrl('timeslot', 'get_users_for_timeslot', {task_id: task_id}), {
        callback: function (success, data) {
            if (data.users && data.users.length > 0) {
                for (var i = 0; i < data.users.length; i++) {
                    var u = data.users[i];
                    var sel = u.id == og.loggedUser.id ? 'selected="selected"' : '';
                    $('#' + genid + 'tsUser').append('<option value="' + u.id + '" ' + sel + '>' + u.name + '</option>');
                }
                $('#' + genid + 'tsUserContainer').show();

            } else {
                $('#' + genid + 'tsUser').remove();
                $('#' + genid + 'tsUserContainer').append('<input type="hidden" name="timeslot[contact_id]" value="' + og.loggedUser.id + '" />');
            }
        }
    });

}

// No-op used as the click handler for disabled task actions (e.g. "add work" once
// the task's estimated hours have been reached) so the icon stays visible but inert.
ogTasks.doNothing = function () {
    return false;
}

ogTasks.AddWorkTime = function (task_id) {
    og.render_modal_form('', {
        c: 'time',
        a: 'add',
        params: {object_id: task_id, contact_id: og.loggedUser.id, dont_reload: 1, req_channel: 'task list - add worked hours'}
    });
    return;
}


ogTasks.readTask = function (task_id, isUnRead) {
    var task = ogTasks.getTask(task_id);
    if (!isUnRead) {
        og.openLink(
            og.getUrl('task', 'multi_task_action'),
            {
                method: 'POST', post: {ids: task_id, action: 'markasread', req_channel: 'task list - mark as read'}, callback: function (success, data) {
                    if (!success || data.errorCode) {
                    } else {
                        var td = document.getElementById('ogTasksPanelMarkasTd' + task_id);
                        td.innerHTML = "<div title=\"" + lang('mark as unread') + "\" id=\"readunreadtask" + task_id + "\" class=\"db-ico ico-read\" onclick=\"ogTasks.readTask(" + task_id + ",true)\" />";
                        task.isRead = true;
                    }
                }
            }
        );
    } else {
        og.openLink(
            og.getUrl('task', 'multi_task_action'),
            {
                method: 'POST', post: {ids: task_id, action: 'markasunread', req_channel: 'task list - mark as unread'}, callback: function (success, data) {
                    if (!success || data.errorCode) {
                    } else {
                        var td = document.getElementById('ogTasksPanelMarkasTd' + task_id);
                        td.innerHTML = "<div title=\"" + lang('mark as read') + "\" id=\"readunreadtask" + task_id + "\" class=\"db-ico ico-unread\" onclick=\"ogTasks.readTask(" + task_id + ",false)\" />";
                        task.isRead = false;
                    }
                }
            }
        );
    }
}
//this is executed only when click on buttons bar and not when edit via modal the task
//in all the buttons doesnt send never from_server in true but i leave because is my first day and also in a find usages appear some lines with pass true
ogTasks.UpdateTask = function (task_id, from_server) {
    if (typeof from_server != 'undefined' && from_server) {
        og.openLink(og.getUrl('task', 'get_task_data', {id: task_id, task_info: true}), {
            callback: function (success, data) {
                if (!success || data.errorCode) {

                } else {
					// update groups cache
					ogTasks.updateTaskDataInGroups(data.task);

                    //Set task data
                    ogTasks.drawTaskRowAfterEdit(data);
                }
            },
            scope: this
        });
    } else {
        var task = ogTasksCache.getTask(task_id);
		ogTasks.updateTaskDataInGroups(task);
        ogTasks.reDrawTask(task);
        ogTasks.refresElbows(task_id);
    }
}

ogTasks.buildTaskPercentCompletedBar = function (task) {
    let color_cls = 'task-percent-completed-';
    const pct = task.percentCompleted;

    if (pct < 25) color_cls += '0';
    else if (pct < 50) color_cls += '25';
    else if (pct < 75) color_cls += '50';
    else if (pct < 100) color_cls += '75';
    else if (pct === 100) color_cls += '100';
    else color_cls += 'more-estimate';

    const percent_complete = Math.min(pct, 100);

    const html = `
        <div class="task-progress-container">
            <div class="task-progress-track">
                <div class="task-progress-fill ${color_cls}" style="--target-width: ${percent_complete}%;"></div>
            </div>
            <span class="percent_num">
                ${percent_complete}%
            </span>
        </div>
    `;

    return html;
};


ogTasks.UpdateDependants = function (task, complete, prev_status) {
    var deps = this.getDependencyCount(task.id);
    if (deps) {
        var dependants = deps.dependants.split(',');
        for (var i = 0; i < dependants.length; i++) {
            var dependant_id = dependants[i];
            var dc = this.getDependencyCount(dependant_id);
            if (dc) {
                if (complete) {
                    dc.count -= 1;
                    this.UpdateTask(dependant_id);
                } else {
                    // Reopen: add 1 and reopen parents
                    if (prev_status == 1) {
                        dc.count += 1;
                        var dep = this.getTask(dependant_id);
                        dep.status = 0;
                        this.UpdateTask(dependant_id);
                        this.UpdateDependants(dep, false);
                    }
                }
            }
        }
    }
}

ogTasks.initTasksList = function () {
    var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
    var drawOptions = topToolbar.getDrawOptions();

    var tasks_list_cols = [];

    //actions
    tasks_list_cols.push(
        {
            id: 'task_actions',
            col_width: '70px'
        }
    );

    //assigned by
    if (drawOptions.show_by) {
        tasks_list_cols.push(
            {
                id: 'task_assigned_by_id',
                title: lang('by uppercase'),
                group_total_field: '',
                data: 'data-resizable=1',
                row_field: 'assignedById',
                col_width: '30px'
            }
        );
    }

    //assigned to
    if (drawOptions.show_assigned_to) {
        tasks_list_cols.push(
            {
                id: 'task_assigned_to_id',
                title: lang('to'),
                group_total_field: '',
                data: 'data-resizable=1',
                row_field: 'assignedToId',
                col_width: '30px'
            }
        );
    }

    //task name
    tasks_list_cols.push(
        {
            id: 'task_name',
            title: lang('task'),
            data: 'data-resizable=1',
            group_total_field: '',
            row_field: 'title',
            col_width: 'auto'
        }
    );

    //clasification
    if (drawOptions.show_classification) {
        tasks_list_cols.push(
            {
                id: 'task_clasification',
                title: lang('classified under'),
                data: 'data-resizable=1',
                group_total_field: '',
                row_field: 'memPath',
                col_width: 'auto'
            }
        );
    }

    //dimesions breadcrumbs
    for (x in drawOptions.show_dimension_cols) {
        did = drawOptions.show_dimension_cols[x];
        ot_id = null;
        if (typeof(did) == 'function') continue;

        if (isNaN(did) && did.indexOf("-") != -1) {
            exp = did.split("-");
            did = exp[0];
            if (!isNaN(ot_id)) ot_id = exp[1];
        }
        if (did == 0 || !og.dimensions_info[did]) continue;
        if (og.config.enabled_dimensions.indexOf(did + "") == -1) continue;

        var key = 'lp_dim_' + did + '_show_as_column';
        if (og.preferences['listing_preferences'][key]) {
        	var col_name = og.dimensions_info[did].name;
        	if (ot_id) {
        		col_name = og.objectTypes[ot_id].c_name_plural ? og.objectTypes[ot_id].c_name_plural : lang(og.objectTypes[ot_id].name + 's');
        	}
            tasks_list_cols.push(
                {
                    id: 'task_clasification' + drawOptions.show_dimension_cols[x],
                    css_class: 'task_clasification',
                    title: col_name,
                    data: 'data-resizable=1',
                    group_total_field: '',
                    col_width: 'auto'
                }
            );
        }
    }

    //percent complete bar
    if (drawOptions.show_percent_completed_bar) {
        tasks_list_cols.push(
            {
                id: 'task_completed_bar',
                title: lang('completed'),
                group_total_field: '',
                row_field: 'ogTasks.buildTaskPercentCompletedBar(task)',
                col_width: '100px'
            }
        );
    }

    //start date
    if (drawOptions.show_start_dates) {
        var start_date_title = (ogTasks.task_gb_options_names && ogTasks.task_gb_options_names['start_date'] && ogTasks.task_gb_options_names['start_date'] != lang('start date')) ? ogTasks.task_gb_options_names['start_date'] : lang('start m');
        tasks_list_cols.push(
            {
                id: 'task_start_date',
                title: start_date_title,
                group_total_field: '',
                row_field: 'startDate',
                col_width: '100px',
                data: 'data-resizable=1'
            }
        );
    }

    //due date
    if (drawOptions.show_end_dates) {
        var due_date_title = (ogTasks.task_gb_options_names && ogTasks.task_gb_options_names['due_date'] && ogTasks.task_gb_options_names['due_date'] != lang('due date')) ? ogTasks.task_gb_options_names['due_date'] : lang('due m');
        tasks_list_cols.push(
            {
                id: 'task_due_date',
                title: due_date_title,
                group_total_field: '',
                row_field: 'dueDate',
                col_width: '100px',
                data: 'data-resizable=1'
            }
        );
    }

    //time estimated
    if (drawOptions.show_time_estimates) {
        tasks_list_cols.push(
            {
                id: 'task_estimated',
                title: lang('estimated'),
                group_total_field: 'estimatedTime',
                row_field: 'estimatedTime',
                col_width: '100px',
                data: 'data-resizable=1'
            }
        );
    }

    //total time estimated
    if (drawOptions.show_total_time_estimates) {
        tasks_list_cols.push(
            {
                id: 'task_total_estimated',
                title: lang('total estimated'),
                group_total_field: 'totalEstimatedTime',
                row_field: 'totalTimeEstimateString',
                col_width: '100px',
                data: 'data-resizable=1'
            }
        );
    }

    //time pending
    if (drawOptions.show_time_pending) {
        tasks_list_cols.push(
            {
                id: 'task_pending',
                title: lang('pending'),
                group_total_field: 'pending_time_string',
                row_field: 'pending_time_string',
                col_width: '100px',
                data: 'data-resizable=1'
            }
        );
    }

    //time worked
    if (drawOptions.show_time_worked) {
        tasks_list_cols.push(
            {
                id: 'task_worked',
                title: lang('worked'),
                group_total_field: 'worked_time_string',
                row_field: 'worked_time_string',
                col_width: '100px',
                data: 'data-resizable=1'
            }
        );
    }

    //time worked
    if (drawOptions.show_total_time_worked) {
        tasks_list_cols.push(
            {
                id: 'task_total_worked',
                title: lang('total worked'),
                group_total_field: 'overall_worked_time_string',
                row_field: 'overall_worked_time_string',
                col_width: '100px',
                data: 'data-resizable=1'
            }
        );
    }

    // Remaining time
    if(drawOptions.show_remaining_time) {
        tasks_list_cols.push(
            {
                id: 'task_remaining',
                title: lang('remaining time'),
                group_total_field: 'remaining_time_string',
                row_field: 'remaining_time_string',
                col_width: '100px',
                data: 'data-resizable=1'
            }
        );
    }

    // Total remaining time
    if(drawOptions.show_total_remaining_time) {
        tasks_list_cols.push(
            {
                id: 'task_total_remaining',
                title: lang('total remaining time'),
                group_total_field: 'total_remaining_time_string',
                row_field: 'total_remaining_time_string',
                col_width: '100px',
                data: 'data-resizable=1'
            }
        );
    }

    // additional columns
    if (ogTasks.additional_task_list_columns) {
        for (var i = 0; i < ogTasks.additional_task_list_columns.length; i++) {
            var col = ogTasks.additional_task_list_columns[i];
            var field = col.row_field ? col.row_field : col.id;
            var total_field = col.group_total_field ? col.group_total_field : '';
            var width = col.width ? col.width : '100px';

            if (drawOptions[col.id]) {
                tasks_list_cols.push({
                    id: col.id,
                    title: col.name,
                    data: col.data ?  col.data : '',
                    group_total_field: total_field,
                    row_field: field,
                    col_width: width
                });
            }
        }
    }

    //previous tasks
    if (drawOptions.show_previous_pending_tasks) {
        tasks_list_cols.push(
            {
                id: 'task_previous',
                title: lang('previous tasks'),
                group_total_field: '',
                row_field: 'previous_tasks_total',
                col_width: '100px'
            }
        );
    }


    // custom properties
    for (x in ogTasks.custom_properties) {
        var cp = ogTasks.custom_properties[x];
        if (typeof(cp) == 'object' && ogTasks.userPreferences['tasksShowCP_' + cp.id] == 1) {
            tasks_list_cols.push({
                id: 'cp_' + cp.id,
                css_class: '',
                title: cp.name,
                data: 'data-resizable=1',
                group_total_field: '',
                col_width: 'auto'
            });
        }
    }

    //quick actions + overflow menu (merged single column)
    // Auto-size the column to how many icons can actually appear per the current
    // "Show" settings, instead of a fixed guess: ~26px per icon slot (18px icon +
    // 4px horizontal padding each side, per .task-action-icon in tasks.css). "time"
    // reserves up to 3 slots since an actively-worked task shows stop/pause-resume/
    // cancel simultaneously; the "..." overflow button is reserved unconditionally
    // since it's common for at least one quick action to be hidden.
    var actionIconSlots =
        (drawOptions.show_quick_add_sub_tasks ? 1 : 0) +
        (drawOptions.show_quick_edit ? 1 : 0) +
        (drawOptions.show_quick_mark_as_started ? 1 : 0) +
        (drawOptions.show_quick_complete ? 1 : 0) +
        (drawOptions.show_time_quick ? 1 : 0) +
        (drawOptions.show_time ? 3 : 0) +
        1; // "..." overflow button
    var actionsColWidth = Math.max(70, actionIconSlots * 26 + 10) + 'px';

    tasks_list_cols.push(
        {
            id: 'task_quick_actions',
            title: lang('actions'),
            col_width: actionsColWidth
        }
    );

    ogTasks.TasksList.tasks_list_cols = tasks_list_cols;
}

ogTasks.newTaskFormTopList = function () {
    ogTasks.initTasksList();
    var title_cols = [];

    //title_cols.push({text:group[row_field], cssclass:'task-date-container'});

    //get template for the row
    var source = $("#task-list-col-names-template").html();
    //compile the template
    var template = Handlebars.compile(source);

    var add_btn_position = 'task_quick_actions';
    for (var i = 0; i < ogTasks.TasksList.tasks_list_cols.length; i++) {
        var col = ogTasks.TasksList.tasks_list_cols[i];
        if (ogTasks.TasksList.tasks_list_cols[i].id == 'task_name') {
            add_btn_position = ogTasks.TasksList.tasks_list_cols[i + 1].id;
        }
    }

    //template data
    var data = {
        tasks_list_cols: ogTasks.TasksList.tasks_list_cols,
        add_btn_position: add_btn_position,
        genid: og.genid
    }

    //instantiate the template
    var html = template(data);

    return html;
}


/*******************************************************/
/**************** DRAG & DROP FUNCTIONS ****************/
/*******************************************************/
ogTasks.initDragDrop = function () {

    $(".tasks-panel-group").sortable({
        connectWith: ".tasks-panel-group-droppable:not(.ui-sortable-helper)",
        stop: function (event, object, c, d) {
            ogTasks.processTaskDrop(event, object);
            $(".dragging").removeClass("dragging");

            var add_trs = $(object.item).children('.task-list-row');
            for (var i = 0; i < add_trs.length; i++) {
                var tr = add_trs[i];
                $(tr).remove();
                $(object.item).after(tr);
            }
        },
        helper: function (e, item) {
            var ids_str = ogTasks.getSelectedIds() + '';
            var selected_ids = ids_str.split(',');
            $(item).addClass('dragging');

            var html = $(item).clone(); // Clone original item to not manipulate DOM directly
            var processed_ids = [item[0].id];

            for (var i = 0; i < selected_ids.length; i++) {
                var sel_task = ogTasks.getTask(selected_ids[i]);
                if (sel_task && sel_task.divInfo && sel_task.divInfo[0]) {
                    var sel_el_id = "ogTasksPanelTask" + sel_task.id + "G" + sel_task.divInfo[0].group_id;
                    var sel_el = $("#" + sel_el_id);
                    if (sel_el.length > 0 && processed_ids.indexOf(sel_el[0].id) == -1 && sel_el[0] !== item[0]) {
                        $(sel_el).addClass('dragging');

                        html.append(sel_el[0]);
                        processed_ids.push(sel_el[0].id);
                    }
                }
            }

            return html;
        },
        handle: ".ddhandle",
        cursor: "move",
        dropOnEmpty: false,
        placeholder: "tasks-dd-placeholder"
    });
    $(".tasks-panel-group").disableSelection();
};

ogTasks.processTaskDrop = function (event, object) {
    if (object.item.length > 0 && object.item[0].parentNode && object.item[0].parentNode.id) {

        var from_group_id = event.target.id.replace("ogTasksPanelGroup", "");
        var to_group_id = object.item[0].parentNode.id.replace("ogTasksPanelGroup", "");

        var task_id = object.item[0].id.replace("ogTasksPanelTask", "");
        var gpos = task_id.indexOf("G");
        if (gpos >= 0) {
            task_id = task_id.substring(0, gpos);
        }

        var ids_str = ogTasks.getSelectedIds() + '';
        ids_str += (ids_str == '' ? '' : ',') + task_id;
        var task_ids = ids_str.split(',');

        var valid_dropzone = $(event.srcElement).closest(".tasks-panel-group-droppable");

        // check if dropped to a dimension tree node
        var member_id = null;
        var dimension_id = null;
        if (event.srcElement.hasAttribute("ext:tree-node-id")) {
            var node_id = $(event.srcElement).attr("ext:tree-node-id");
            if (!isNaN(node_id)) {
                member_id = node_id;
            }
            dimension_id = parseInt($(event.srcElement).closest(".x-panel.x-tree").attr('id').replace('dimension-panel-', ''));

        } else if (event.srcElement.id.indexOf("extdd-") >= 0 // dropped in the text of the tree node
            || $(event.srcElement).hasClass("x-tree-icon") // dropped in the emtpy space that identates a tree node
            || $(event.srcElement).hasClass("x-tree-elbow") // dropped in the elbow icon before a tree node
            || $(event.srcElement).hasClass("x-tree-node-icon") // dropped in the member icon
            || $(event.srcElement).hasClass("ico-edit")) { // dropped in the member edit icon
            
            var node_id = $(event.srcElement).closest(".x-tree-node-el").attr("ext:tree-node-id");
            if (!isNaN(node_id)) {
                member_id = node_id;
            }
            dimension_id = parseInt($(event.srcElement).closest(".x-panel.x-tree").attr('id').replace('dimension-panel-', ''));
        }

        // classify task
        if (dimension_id != null && !isNaN(dimension_id)) {
            // ensure that task is not moved to another group
            ogTasks.cancelDrop();

            ogTasks.classifyTasks(task_ids, member_id, dimension_id, from_group_id);

        } else {
            // valid dropzone and source group != to group
            if (valid_dropzone.length > 0 && from_group_id != to_group_id) {

                ogTasks.changeTasksGroup(task_ids, from_group_id, to_group_id);

            } else {
                // Invalid dropzone
                ogTasks.cancelDrop();
            }
        }
    }
}

ogTasks.cancelDrop = function () {
    try {
        $(".tasks-panel-group").sortable("cancel");
    } catch (e) {
        // try/catch to avoid js crash, this error does not need to be handled
    }
}

ogTasks.changeTasksGroup = function (task_ids, from_group_id, to_group_id) {

    var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
    var filters = bottomToolbar.getFilters();
    var displayCriteria = bottomToolbar.getDisplayCriteria();

    if (displayCriteria.group_by.indexOf('dimension_') >= 0) {
        // grouping by dimension -> classify selected tasks
        var dimension_id = displayCriteria.group_by.replace('dimension_', '');
        ogTasks.classifyTasks(task_ids, to_group_id, dimension_id, from_group_id);

    } else {
        // Possible grouping: 'nothing','milestone','priority','assigned_to','due_date','start_date','created_on','created_by','completed_on','completed_by','status'
        // Do not modify if grouping by 'created_on' or 'created_by' or 'completed_on' or 'completed_by'

        switch (displayCriteria.group_by) {
            case 'assigned_to':
            case 'priority':
            case 'status':
            case 'milestone':
                ogTasks.editTasksAttribute(task_ids, displayCriteria.group_by, to_group_id, from_group_id);
                break;

            case 'due_date':
            case 'start_date':
                // TODO: prompt exact date and update tasks
                //var date_value = '';
                //ogTasks.editTasksAttribute(task_ids, displayCriteria.group_by, date_value, from_group_id);
                ogTasks.promptDate(task_ids, displayCriteria.group_by, to_group_id, from_group_id);
                break;

            default:
                // Invalid grop group
                ogTasks.cancelDrop();
                break;
        }
    }
}

ogTasks.promptDate = function (task_ids, attribute, to_group_id, from_group_id) {
    var extid = Ext.id();
    var title = (attribute == 'due_date' ? lang('new due date') : lang('new start date'));
    var description = (attribute == 'due_date' ? lang('new due date desc') : lang('new start date desc'));

    var source = $("#change-tasks-date").html();
    var template = Handlebars.compile(source);
    var html = template({genid: extid, attribute: attribute, title: title, description: description});

    var modal_params = {
        'escClose': true,
        'overlayClose': true,
        'minWidth': 400,
        'minHeight': 200,
        'closeHTML': '<a id="ogTasksPanelAT_close_link" class="modal-close modal-close-img"></a>',
        'onClose': function (dialog) {
            ogTasks.cancelDrop();
            $.modal.close();
        },
        'onShow': function (dialog) {

            var dtp = new og.DateField({
                renderTo: extid + '_date_picker_container',
                name: 'new_date_value',
                emptyText: og.preferences.date_format_tip,
                id: extid + '_date_picker',
                value: ''
            });
        }

    };
    $.modal(html, modal_params);

    $("#change-tasks-date-modal-form-" + extid).submit(function (event) {
        var date_picker = Ext.getCmp(extid + "_date_picker");
        var dp_value = date_picker.getValue();
        var date_value = dp_value ? dp_value.format(og.preferences.date_format) : '';
        ogTasks.editTasksAttribute(task_ids, attribute, date_value, from_group_id);
        $.modal.close();
        return false;
    });
}

ogTasks.editTasksAttribute = function (task_ids, attribute, new_value, from_group_id) {

    var params = {
        task_ids: Ext.util.JSON.encode(task_ids),
        attribute: attribute,
        new_value: new_value,
		req_channel: 'task list - edit attribute '+attribute
    };
    og.openLink(og.getUrl('task', 'edit_tasks_attribute'), {
        method: 'POST',
        post: params,
        callback: function (success, data) {
            for (var x = 0; x < task_ids.length; x++) {
                var t = ogTasks.getTask(task_ids[x]);
                if (t) {
                    $("#ogTasksPanelTask" + t.id + "G" + from_group_id).remove();
                    ogTasks.UpdateTask(t.id, true);
                }
            }
        }
    });
}

ogTasks.classifyTasks = function (task_ids, member_id, dimension_id, from_group_id) {
    var multiple_sel = task_ids.length > 1;
    var is_classified_in_dim = false;

    // check if there is any selected task classified in dimension_id
    for (var i = 0; i < task_ids.length; i++) {
        var task = ogTasks.getTask(task_ids[i]);
        var classification = Ext.util.JSON.decode(task.memPath);
        var task_classified_in_dim = classification && typeof(classification[dimension_id]) == 'object';
        if (task_classified_in_dim) {
            is_classified_in_dim = true;
        }
    }
    var rm_prev = 0;

	// this fn will be executed after classification
	var after_classification_fn = function(ids, mem_id) {
		// redraw the tasks list from scratch, because if we update each task 
		// it will trigger the group totals recalculation once per task, and that causes performance issues
		ogTasks.draw();
	}

	// find member type id
	var member_type_id = 0;
	var mem_array = og.getMemberFromTrees(dimension_id, member_id);
	if (mem_array && mem_array.length > 0) {
		member_type_id = mem_array[0].ot;
	}
	
	// get tasks object type
	var tasks_type = og.get_object_type_by_name('task');

	// check if classification in this type of member can be multiple to show the popup or not
	var allows_multiple_classification = og.dimension_object_type_contents[dimension_id] &&
		og.dimension_object_type_contents[dimension_id][member_type_id] &&
		og.dimension_object_type_contents[dimension_id][member_type_id][tasks_type.id] &&
		og.dimension_object_type_contents[dimension_id][member_type_id][tasks_type.id].multiple;

    // if there are tasks classified in dimension_id => check if remove previous members of dimension_id
    if (is_classified_in_dim && allows_multiple_classification && !isNaN(member_id) && member_id > 0) {

        if (og.preferences['drag_drop_prompt'] == 'prompt') {

			// ask the user if the prev classification must be kept or removed, then call the classification fn
			og.drag_drop_classification_keep_or_move_prompt('', null, task_ids, member_id, null, true, after_classification_fn);

		} else {
			// before calling classification fn decide if prev classification must be kept or removed
			if (og.preferences['drag_drop_prompt'] == 'move') {
				var rm_prev = 1;
			} else if (og.preferences['drag_drop_prompt'] == 'keep') {
				var rm_prev = 0;
			}

			// no prompt needed => directly call the classification fn
			og.call_add_objects_to_member(null, task_ids, member_id, null, true, rm_prev, after_classification_fn);
		}
		
    } else {
		
		if (isNaN(member_id) || member_id == null) {
			// unclassify the tasks
			og.call_add_objects_to_member(null, task_ids, null, null, true, rm_prev, after_classification_fn, dimension_id);

		} else {
			// no prompt needed => directly call the classification fn
			og.call_add_objects_to_member(null, task_ids, member_id, null, true, true, after_classification_fn);
		}
	}
}


ogTasks.createDimensionColumnMenuItems = function (did, option_name, ignore_listing_preferences) {
    var menu_items = [];

    var key = 'lp_dim_' + did + '_show_as_column';
    if (ignore_listing_preferences || og.preferences['listing_preferences'][key]) {

        if (og.dimensions_info[did]) {
            var general_item = ogTasks.createDimensionColumnMenuItem(did, og.dimensions_info[did].name, did, option_name);
            menu_items.push(general_item);
        }

        if (ogTasks.list_dimension_column_hooks) {
            for (var j = 0; j < ogTasks.list_dimension_column_hooks.length; j++) {
                var fn = ogTasks.list_dimension_column_hooks[j];
                if (typeof(fn) == 'function') {
                    more_items = fn.call(null, did, option_name);
                    if (more_items && more_items.length > 0) {
                        menu_items = menu_items.concat(more_items);
                    }
                }
            }
        }

        if (ogTasks.userPreferences.showDimensionCols.indexOf(did) != -1) {
            og.breadcrumbs_skipped_dimensions[did] = did.toString();
        } else {
            og.breadcrumbs_skipped_dimensions[did] = 0;
        }
    }

    return menu_items;
}


ogTasks.createDimensionColumnMenuItem = function (did, label, menu_key, option_name) {

    if (!option_name) option_name = 'tasksShowDimensionCols';

    var checked = ogTasks.userPreferences.showDimensionCols.indexOf(menu_key) != -1;
    if (option_name.indexOf("gantt") == 0) {
        checked = ogTasks.ganttPreferences.ganttShowDimensionCols.indexOf(menu_key) != -1;
    }

    var menu_config = {
        option_name: option_name,
        text: label,
        value: menu_key,
        checked: checked,
        hideOnClick: false,
        checkHandler: function () {
            if (this.option_name.indexOf("gantt") == 0) {
                var dim_index = ogTasks.ganttPreferences.ganttShowDimensionCols.indexOf(this.value);
                if (dim_index != -1) {
                    ogTasks.ganttPreferences.ganttShowDimensionCols.splice(dim_index, 1);
                } else {
                    ogTasks.ganttPreferences.ganttShowDimensionCols.push(this.value);
                }
            } else {
                var dim_index = ogTasks.userPreferences.showDimensionCols.indexOf(this.value);
                if (dim_index != -1) {
                    ogTasks.userPreferences.showDimensionCols.splice(dim_index, 1);
                } else {
                    ogTasks.userPreferences.showDimensionCols.push(this.value);
                }
            }

            var opt_val = ogTasks.userPreferences.showDimensionCols.toString();
            if (this.option_name.indexOf("gantt") == 0) {
                checked = ogTasks.ganttPreferences.ganttShowDimensionCols.toString();
            }

            var url = og.getUrl('account', 'update_user_preference', {name: this.option_name, value: opt_val});
            //og.openLink(url, {hideLoading: true});

            if (this.value.indexOf("-") == -1 && this.option_name.indexOf("gantt") == -1) {
                var d = this.value.toString();
                if (ogTasks.userPreferences.showDimensionCols.indexOf(d) != -1) {
                    og.breadcrumbs_skipped_dimensions[d] = d;
                } else {
                    og.breadcrumbs_skipped_dimensions[d] = 0;
                }
            }
            
            //var tp = Ext.getCmp("tasks-panel");
            //if (tp) tp.reset();
            ogTasksMakeRequestAndReloadWithTimeout(url);
        }
    };

    return menu_config;
}

// ── Member hover card ──────────────────────────────────────────────────────
// Shows a floating card with member custom-property data when the user hovers
// a .real-breadcrumb span inside the tasks panel.
ogTasks.MemberHoverCard = (function ($) {

    var _$card    = null;   // the floating card DOM element
    var _cache    = {};     // member_id → html string (or '' when no data)
    var _pending  = {};     // member_id → true while an AJAX request is in flight
    var _showTimer  = null; // debounce timer before showing
    var _hideTimer  = null; // debounce timer before hiding
    var _$titleSpan = null; // span whose native title was suppressed while card is visible
    var SHOW_DELAY  = 300;  // ms to wait before showing after hover
    var HIDE_DELAY  = 150;  // ms to wait before hiding after leave

    // Suppress the span's native tooltip while the card is visible — the card
    // already shows the member path, so the browser tooltip would just duplicate it.
    function _suppressTitle($span) {
        _restoreTitle();
        var t = $span.attr('title');
        if (t == null) return;
        $span.data('mhc-original-title', t).removeAttr('title');
        _$titleSpan = $span;
    }

    function _restoreTitle() {
        if (!_$titleSpan) return;
        var t = _$titleSpan.data('mhc-original-title');
        if (t != null) _$titleSpan.attr('title', t).removeData('mhc-original-title');
        _$titleSpan = null;
    }

    function _getOrCreate$card() {
        if (!_$card || !_$card.parent().length) {
            _$card = $('<div class="member-hover-card"></div>').hide().appendTo('body');
            _$card.on('mouseenter', function () {
                clearTimeout(_hideTimer);
            });
            _$card.on('mouseleave', function () {
                _scheduleHide();
            });
        }
        return _$card;
    }

    function _extractMemberId($span) {
        var cls = $span.attr('class') || '';
        var m = cls.match(/\bbread-crumb-(\d+)\b/);
        return m ? m[1] : null;
    }

    function _positionCard($anchor) {
        var $c    = _getOrCreate$card();
        var rect  = $anchor[0].getBoundingClientRect();
        var winW  = window.innerWidth;
        var winH  = window.innerHeight;
        var cardW = $c.outerWidth() || 280;
        var cardH = $c.outerHeight() || 200;
        var top   = rect.bottom + 6;
        var left  = rect.left;
        // flip above if not enough space below
        if (top + cardH > winH - 10) top = rect.top - cardH - 6;
        // keep within right edge
        if (left + cardW > winW - 10) left = winW - cardW - 10;
        if (left < 6) left = 6;
        $c.css({ top: top, left: left });
    }

    function _renderCard(member) {
        if (!member || !member.groups || !member.groups.length) return '';
        var body = '';
        for (var gi = 0; gi < member.groups.length; gi++) {
            var g = member.groups[gi];
            if (!g.properties || !g.properties.length) continue;
            var groupRows = '';
            for (var pi = 0; pi < g.properties.length; pi++) {
                var p = g.properties[pi];
                if (!p.value && p.value !== 0) continue;
                groupRows += '<div class="mhc-row">' +
                    '<span class="mhc-label">' + og.clean(p.label) + '</span>' +
                    '<span class="mhc-value">' + og.clean(p.value) + '</span>' +
                '</div>';
            }
            if (!groupRows) continue; // skip group entirely if all values are empty
            if (g.name) {
                body += '<div class="mhc-group-name">' + og.clean(g.name) + '</div>';
            }
            body += groupRows;
        }
        if (!body) return '';
        var editUrl = og.getUrl('member', 'edit', {id: member.id});

        var editLink = '<a href="#" class="mhc-edit-link" ' +
            'onclick="og.disableEventPropagation(event); ogTasks.MemberHoverCard.hide(); og.render_modal_form(\'\', {url:\'' + editUrl + '\'});">' +
            og.clean(lang('edit name', member.name)) + '</a>';
        return '<div class="mhc-header">' +
                   '<span class="mhc-header-name">' + og.clean(member.name) + '</span>' +
               '</div>' +
               '<div class="mhc-body">' + body + '</div>' +
               '<div class="mhc-footer">' + editLink + '</div>';
    }

    function _showCard($span, memberId) {
        var $c = _getOrCreate$card();
        if (_cache[memberId] === undefined) {
            // Not yet cached — fetch and then show
            if (_pending[memberId]) return; // already fetching
            _pending[memberId] = true;
            $c.data('current-member', memberId);
            og.openLink(og.getUrl('dimension', 'get_member_properties_data', {member_id: memberId}), {
                hideLoading: true,
                callback: function (success, data) {
                    var member = (success && data && data.member) ? data.member : null;
                    _cache[memberId] = member;
                    delete _pending[memberId];
                    // Only show if the cursor is still on this span
                    if (_$card && _$card.data('current-member') == memberId) {
                        var html = _renderCard(member);
                        if (html) {
                            $c.html(html).show();
                            _positionCard($span);
                            _suppressTitle($span);
                        }
                    }
                }
            });
            return;
        }

        var html = _renderCard(_cache[memberId]);
        if (!html) return; // no properties to show
        $c.data('current-member', memberId).html(html);
        _positionCard($span);
        $c.show();
        _suppressTitle($span);
    }

    function _scheduleHide() {
        clearTimeout(_hideTimer);
        _hideTimer = setTimeout(function () {
            var $c = _getOrCreate$card();
            $c.hide().data('current-member', null);
            _restoreTitle();
        }, HIDE_DELAY);
    }

    function init() {
        if ($(document).data('mhc-bound')) return;
        $(document).data('mhc-bound', true);

        $(document).on('mouseenter', '#tasksPanelContainer .real-breadcrumb', function () {
            var $span    = $(this);
            var memberId = _extractMemberId($span);
            if (!memberId) return;
            clearTimeout(_hideTimer);
            clearTimeout(_showTimer);
            _showTimer = setTimeout(function () {
                _showCard($span, memberId);
            }, SHOW_DELAY);
        });

        $(document).on('mouseleave', '#tasksPanelContainer .real-breadcrumb', function () {
            clearTimeout(_showTimer);
            _scheduleHide();
        });

        // Clicking a breadcrumb applies that member's filter and redraws the
        // list, so the hovered span is gone before mouseleave can fire and the
        // card would stay open. Any click outside the card closes it — the
        // card's own links (edit member) keep working.
        // hide() also clears the pending show timer and the card's
        // current-member marker, so a fetch still in flight won't pop the card
        // open after the redraw.
        $(document).on('mousedown', function (e) {
            if ($(e.target).closest('.member-hover-card').length) return;
            hide();
        });
    }

    function hide() {
        clearTimeout(_showTimer);
        clearTimeout(_hideTimer);
        if (_$card) _$card.hide().data('current-member', null);
        _restoreTitle();
    }

    return { init: init, hide: hide };

}(jQuery));
// ── END Member hover card ──────────────────────────────────────────────────

// ── Assignee hover card ──────────────────────────────────────────────────
ogTasks.AssigneeHoverCard = (function ($) {

    var _$card    = null;
    var _showTimer = null;
    var _hideTimer = null;
    var _$titleEl  = null;
    var SHOW_DELAY = 80;
    var HIDE_DELAY = 150;

    function _suppressTitle($el) {
        _restoreTitle();
        var t = $el.attr('title');
        if (t == null) return;
        $el.data('ahc-original-title', t).removeAttr('title');
        _$titleEl = $el;
    }

    function _restoreTitle() {
        if (!_$titleEl) return;
        var t = _$titleEl.data('ahc-original-title');
        if (t != null) _$titleEl.attr('title', t).removeData('ahc-original-title');
        _$titleEl = null;
    }

    function _getOrCreate$card() {
        if (!_$card || !_$card.parent().length) {
            _$card = $('<div class="member-hover-card mhc-compact"></div>').hide().appendTo('body');
            _$card.on('mouseenter', function () {
                clearTimeout(_hideTimer);
            });
            _$card.on('mouseleave', function () {
                _scheduleHide();
            });
        }
        return _$card;
    }

    function _positionCard($anchor) {
        var $c    = _getOrCreate$card();
        var rect  = $anchor[0].getBoundingClientRect();
        var winW  = window.innerWidth;
        var winH  = window.innerHeight;
        var cardW = $c.outerWidth() || 160;
        var cardH = $c.outerHeight() || 40;
        var top   = rect.bottom + 6;
        var left  = rect.left;
        // flip above if not enough space below
        if (top + cardH > winH - 10) top = rect.top - cardH - 6;
        // keep within right edge
        if (left + cardW > winW - 10) left = winW - cardW - 10;
        if (left < 6) left = 6;
        $c.css({ top: top, left: left });
    }

    function _renderCard(user) {
        if (!user || !user.name) return '';
        return '<div class="mhc-compact-row">' +
                   '<img class="mhc-compact-avatar" src="' + user.img_url + '" alt="" />' +
                   '<span class="mhc-compact-name">' + og.clean(user.name) + '</span>' +
               '</div>';
    }

    function _showCard($el, userId) {
        var user = og.allUsers ? og.allUsers[userId] : null;
        var html = _renderCard(user);
        if (!html) return; // no data to show, leave native title alone

        var $c = _getOrCreate$card();
        $c.html(html).show();
        _positionCard($el);
        _suppressTitle($el);
    }

    function _scheduleHide() {
        clearTimeout(_hideTimer);
        _hideTimer = setTimeout(function () {
            _getOrCreate$card().hide();
            _restoreTitle();
        }, HIDE_DELAY);
    }

    function init() {
        if ($(document).data('ahc-bound')) return;
        $(document).data('ahc-bound', true);

        $(document).on('mouseenter', '#tasksPanelContainer .assignee-hover-target', function () {
            var $el    = $(this);
            var userId = $el.attr('data-assignee-id');
            if (!userId) return;
            clearTimeout(_hideTimer);
            clearTimeout(_showTimer);
            _showTimer = setTimeout(function () {
                _showCard($el, userId);
            }, SHOW_DELAY);
        });

        $(document).on('mouseleave', '#tasksPanelContainer .assignee-hover-target', function () {
            clearTimeout(_showTimer);
            _scheduleHide();
        });

        // Same as the member card: a click can redraw the row under the cursor,
        // and mouseleave never fires on the removed element.
        $(document).on('mousedown', function (e) {
            if ($(e.target).closest('.member-hover-card').length) return;
            hide();
        });
    }

    function hide() {
        clearTimeout(_showTimer);
        clearTimeout(_hideTimer);
        if (_$card) _$card.hide();
        _restoreTitle();
    }

    return { init: init, hide: hide };

}(jQuery));
// ── END Assignee hover card ──────────────────────────────────────────────
