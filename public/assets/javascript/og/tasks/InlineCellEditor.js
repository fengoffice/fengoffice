/**
 * InlineCellEditor.js
 *
 * Inline editing for the Tasks data table.
 *
 * Interaction model:
 *   - Hovering a cell that has data-editable reveals a floating action bar
 *     on the right edge with a pencil icon (and room for future actions).
 *   - Clicking the pencil activates the inline editor for that cell.
 *   - Cells whose content is a .task-breadcrumb-container (classification /
 *     dimension paths) open a read-only role="dialog" popover instead, since
 *     member editing is not yet supported inline.
 *
 * Saves via the existing quick_edit_task endpoint and refreshes
 * through the existing drawTaskRowAfterEdit() pipeline.
 *
 * No new dependencies — plain jQuery + og.openLink.
 */

ogTasks.InlineCellEditor = (function ($) {

    // ── Configuration ──────────────────────────────────────────────────
    // Custom property types that are fundamentally unsupported inline.
    // display_member_property was here historically, but the normal edit form
    // lets users override the value at the task level, so it is editable inline
    // when the CP is marked as editable (handled by the server-side check).
    var NON_EDITABLE_CP_TYPES = [];

    // Dimension codes whose classification cells are blocked from inline editing
    // because changes would have side-effects on billable/financial fields that
    // require the full task form (normal edit) to handle correctly.
    var NON_EDITABLE_DIM_CODES = ['hour_types'];

    // ── Helpers ────────────────────────────────────────────────────────

    /**
     * Look up a CP's metadata object from ogTasks.custom_properties by its id.
     * Returns the matching entry (with id, type, is_required, is_in_form, etc.)
     * or null when not found.
     */
    function _findCpMeta(cpId) {
        if (!ogTasks.custom_properties) return null;
        for (var _i = 0; _i < ogTasks.custom_properties.length; _i++) {
            var _cp = ogTasks.custom_properties[_i];
            if (_cp && String(_cp.id) === String(cpId)) return _cp;
        }
        return null;
    }

    function _canInlineEditCp(cpMeta) {
        if (!cpMeta) {
            return false;
        }
        if (cpMeta.can_inline_edit === false || cpMeta.can_inline_edit === 0 || cpMeta.can_inline_edit === '0') {
            return false;
        }
        if (cpMeta.is_in_form === false || cpMeta.is_in_form === 0 || cpMeta.is_in_form === '0') {
            return false;
        }
        var isCalculated = cpMeta.is_calculated === true || cpMeta.is_calculated === 1 || cpMeta.is_calculated === '1';
        var isEditable = !(cpMeta.is_editable === false || cpMeta.is_editable === 0 || cpMeta.is_editable === '0');
        if (isCalculated && !isEditable) {
            return false;
        }
        if (NON_EDITABLE_CP_TYPES.indexOf(cpMeta.type) >= 0) {
            return false;
        }
        return true;
    }

    /**
     * Returns true when a custom property is part of the given task's subtype form,
     * and therefore editable inline. Mirrors the rule the task form applies
     * (object_subtypes_object_form_custom_prop_extra_conditions):
     *
     *   - A CP whose object_subtype_id is 0 is common to every subtype; one bound to
     *     a specific subtype only belongs to tasks of that subtype.
     *   - A task with no subtype (otype 0) only shows common CPs.
     *   - Even a matching CP can be explicitly disabled for a subtype via the
     *     object_subtype_properties table — surfaced here as cp_disabled_by_subtype.
     *
     * CPs that aren't in the form would save nothing, so they must not be editable.
     */
    function _cpAppliesToTaskSubtype(cpMeta, task) {
        if (!cpMeta) {
            return false;
        }
        var taskSubtype = (task && task.otype) ? parseInt(task.otype, 10) : 0;
        var cpSubtype   = (cpMeta.object_subtype_id != null) ? parseInt(cpMeta.object_subtype_id, 10) : 0;

        // Task with no subtype sees only common (subtype-0) custom properties.
        if (taskSubtype === 0) {
            return cpSubtype === 0;
        }
        // CP bound to a different specific subtype is not in this task's form.
        if (cpSubtype !== 0 && cpSubtype !== taskSubtype) {
            return false;
        }
        // Common CP or one bound to this subtype: in the form unless explicitly
        // disabled for this subtype in object_subtype_properties (osp).
        var disabledMap = ogTasks.cp_disabled_by_subtype || {};
        var disabledForSubtype = disabledMap[taskSubtype] || disabledMap[String(taskSubtype)];
        var cpId = cpMeta.id != null ? parseInt(cpMeta.id, 10) : null;
        var isDisabled = !!(disabledForSubtype && cpId != null &&
            (disabledForSubtype[cpId] || disabledForSubtype[String(cpId)]));
        return !isDisabled;
    }

    // ── State ──────────────────────────────────────────────────────────
    var _activeCell   = null;
    var _origHtml     = null;
    var _taskId       = null;
    var _field        = null;
    var _saving       = false;
    var _activeEditor = null;  // editor resolved in _activate(), reused in _commit()
    var _initialPost  = null;  // editor.serialize() snapshot taken right after create(), before any user input
    var _blurTimer    = null;  // timeout id from blur handler (cancelled on new activation)
    var _selectObserver = null; // MutationObserver re-sizing async-populated <select> editors

    // Single action bar element, shared across all cells
    var _$actionBar   = null;
    var _$hoverCell   = null;

    // Last known mouse position, kept current by a mousemove listener.
    // Used in onRowRedrawn() to restore the action bar after a row is replaced
    // without the mouse physically moving (so no new mouseenter fires).
    var _mouseX = 0;
    var _mouseY = 0;

    // ── Plugin editor registry ─────────────────────────────────────────
    var _customEditors = {};

    // ── Public ─────────────────────────────────────────────────────────

    function init() {
        // Guard on document (not $container) so it survives panel redraws that
        // replace container.innerHTML while keeping the container element itself.
        if ($(document).data('ice-bound')) return;
        $(document).data('ice-bound', true);

        // ── Floating action bar ────────────────────────────────────────

        _$actionBar = $(
            '<div class="ice-action-bar">' +
                '<button type="button" class="ice-action-btn ice-action-edit" title="' + lang('edit') + '">' +
                    '<i class="icon-pencil-line"></i>' +
                '</button>' +
            '</div>'
        ).hide().appendTo('body');

        // Delegate on document so handlers survive container innerHTML replacements
        // (e.g. after og.memberTreeExternalClick triggers a full panel redraw).
        $(document).on('mouseenter.ice', '#tasksPanelContainer td[data-editable]', function () {
            var $td = $(this);
            if ($td.hasClass('ice-active') || $td.hasClass('ice-saving')) {
                return;
            }

            var fld = $td.attr('data-editable');

            // Check if this is a non-editable dimension cell
            if (fld === 'dim_classification') {
                var dimId = parseInt($td.attr('data-dim-id'), 10);
                if (dimId) {
                    var dimInfo = og.dimensions_info && og.dimensions_info[dimId];

                    // Only allow dimensions linked to the task object type AND manageable.
                    // PHP computes editableDimensionIds via dimension_object_type_contents
                    // filtered by is_manageable=1.  Columns like "Status" (project_phases)
                    // are project-level dimensions absent from that set.
                    var editableDimIds = (ogTasks.userPreferences && ogTasks.userPreferences.editableDimensionIds) || [];

                    if (editableDimIds.indexOf(dimId) === -1) {
                        return;
                    }
                    // Additionally block specific codes with known financial side-effects.
                    if (og.enabled_dimensions_by_code) {
                        for (var _di = 0; _di < NON_EDITABLE_DIM_CODES.length; _di++) {
                            if (parseInt(og.enabled_dimensions_by_code[NON_EDITABLE_DIM_CODES[_di]], 10) === dimId) {
                                return;
                            }
                        }
                    }
                }
            }

            // Check if this is a non-editable custom property
            if (fld && fld.indexOf('cp_') === 0) {
                var cpId = fld.substring(3);
                var cpMeta = _findCpMeta(cpId);

                // Block CPs that the normal task form would render as absent/read-only.
                if (!_canInlineEditCp(cpMeta)) {
                    return;
                }

                // Block CPs that don't exist for THIS row's task subtype — the field
                // isn't part of the form for that subtype, so editing it saves nothing.
                var hoverTaskId = $td.closest('tr[data-task-id]').attr('data-task-id');
                var hoverTask   = hoverTaskId ? ogTasksCache.getTask(hoverTaskId) : null;
                if (!_cpAppliesToTaskSubtype(cpMeta, hoverTask)) {
                    return;
                }
            }

            _$hoverCell = $td;
            _$actionBar.appendTo($td).show();
        });

        // IMPORTANT: mouseleave fires on ANY removed TD when replaceWith() detaches it,
        // not only when the mouse physically leaves. Without the identity check below,
        // a stale mouseleave from the OLD cell fires AFTER onRowRedrawn() has already
        // placed the action bar on the NEW cell — wiping it and breaking subsequent edits.
        // Fix: only react when the cell leaving IS the one we are currently tracking.
        $(document).on('mouseleave.ice', '#tasksPanelContainer td[data-editable]', function () {
            if (_$hoverCell && this !== _$hoverCell[0]) {
                return;
            }
            _$actionBar.hide().appendTo('body');
            _$hoverCell = null;
        });

        // ── Pencil click — bound at document level (capture phase) ───
        //
        // Using _$actionBar.on(delegation) was unreliable: ExtJS sortable
        // handlers and other table-level listeners call stopPropagation(),
        // silently swallowing the event before it bubbles back up to
        // _$actionBar.  Capture-phase fires BEFORE any bubbling-phase
        // stopPropagation and cannot be blocked.
        document.addEventListener('click', function (e) {
            // Match clicks on the button itself or the <i> icon inside it.
            var btn = e.target.closest
                ? e.target.closest('.ice-action-edit')
                : $(e.target).closest('.ice-action-edit')[0];
            if (!btn) return;

            e.stopPropagation();
            e.preventDefault();

            // Capture _$hoverCell before any DOM manipulation that could
            // fire a synchronous mouseleave and null it.
            var $td = _$hoverCell;
            _$actionBar.hide().appendTo('body');
            _$hoverCell = null;

            if (!$td || !$td.length) return;

            // Guard: TD detached (panel redrawn between mouseenter and click).
            if (!$.contains(document.documentElement, $td[0])) return;

            if ($td.find('.task-breadcrumb-container').length) {
                _showBreadcrumbDialog($td);
            } else {
                _activate($td);
            }
        }, true /* capture phase */);

        // ── Track mouse position for onRowRedrawn() ───────────────────
        // replaceWith() causes mouseleave → _$hoverCell = null, but the browser
        // does NOT fire mouseenter on the newly-inserted element.  We track the
        // cursor so onRowRedrawn() can call elementFromPoint and restore the bar.
        $(document).on('mousemove.ice', function (e) {
            _mouseX = e.clientX;
            _mouseY = e.clientY;
        });

        // ── Keyboard shortcuts while an editor is active ───────────────
        $(document).on('keydown.ice', function (e) {
            if (!_activeCell) return;
            if (e.key === 'Escape') {
                _cancel();
                e.preventDefault();
            } else if (e.key === 'Enter') {
                if (e.shiftKey) return;
                _commit();
                e.preventDefault();
            } else if (e.key === 'Tab') {
                // Capture _activeCell before _commit() — some code paths inside
                // _commit() call _reset() synchronously (e.g. value unchanged),
                // which sets _activeCell = null before we can read it.
                var $current = _activeCell;
                _commit();
                e.preventDefault();
                // Skip CP cells that don't exist for this row's subtype so Tab
                // doesn't dead-end on a cell that _activate() would refuse to open.
                var $tabTask = ogTasksCache.getTask($current.closest('tr[data-task-id]').attr('data-task-id'));
                var $candidates = e.shiftKey
                    ? $current.prevAll('td[data-editable]')
                    : $current.nextAll('td[data-editable]');
                var $next = $();
                $candidates.each(function () {
                    var f = $(this).attr('data-editable');
                    if (f && f.indexOf('cp_') === 0 && !_cpAppliesToTaskSubtype(_findCpMeta(f.substring(3)), $tabTask)) {
                        return; // skip: CP not applicable to this subtype
                    }
                    $next = $(this);
                    return false; // first applicable cell wins
                });
                if ($next.length) {
                    setTimeout(function () { _activate($next); }, 80);
                }
            }
        });
    }

    function registerEditor(fieldName, def) {
        _customEditors[fieldName] = def;
    }

    // ── Private — breadcrumb dialog ────────────────────────────────────

    // Heuristic plural detection for the dialog title
    function _isPluralLabel(label) {
        var words = $.trim(label || '').split(/\s+/);
        var last  = (words[words.length - 1] || '').toLowerCase();
        return /s$/.test(last) && !/ss$/.test(last);
    }

    // Builds the localized, HTML-safe dialog title for a column label:
    //   plural   → "Select Companies"   (lang('select plural', label))
    //   singular → "Select a Version"   (lang('select singular', label))
    function _selectTitle(label) {
        var safe = $('<span>').text(label).html();
        return _isPluralLabel(label)
            ? lang('select plural', safe)
            : lang('select singular', safe);
    }

    // Positions the breadcrumb dialog relative to its trigger cell.
    //
    // Default placement is just below the cell.  When the cell sits near the
    // bottom of the viewport (common for the last rows of a long table) there
    // isn't enough room below, so the dialog flips to open *above* the cell when
    // that side has more space.
    function _positionDialog($dialog, $td) {
        var rect   = $td[0].getBoundingClientRect();
        var margin = 8;
        var vh     = window.innerHeight;
        var vw     = window.innerWidth;

        var $header = $dialog.find('.ice-breadcrumb-dialog-header');
        var $body   = $dialog.find('.ice-breadcrumb-dialog-body');
        var headerH = $header.outerHeight() || 32;
        var bodyMax = parseInt($body.css('max-height'), 10) || 320;

        var spaceBelow = vh - rect.bottom - margin;
        var spaceAbove = rect.top - margin;

        // Flip upward only when the full dialog can't fit below but above is roomier.
        var neededH = headerH + bodyMax;
        var openUp  = (spaceBelow < neededH) && (spaceAbove > spaceBelow);

        // Shrink the body to the room available on the chosen side (never below a
        // usable minimum, never above its natural CSS cap).
        var avail   = openUp ? spaceAbove : spaceBelow;
        var bodyCap = Math.max(80, Math.min(bodyMax, avail - headerH));
        $body.css('max-height', bodyCap + 'px');

        var dialogH = headerH + bodyCap;
        var dialogW = $dialog.outerWidth() || 280;

        var top = openUp ? (rect.top - dialogH - 4) : (rect.bottom + 4);
        top = Math.max(margin, Math.min(top, vh - dialogH - margin));

        var left = Math.max(margin, Math.min(rect.left, vw - dialogW - margin));

        $dialog.css({ top: top, left: left });
    }

    function _showBreadcrumbDialog($td) {
        // Remove any existing dialog
        $('#ice-breadcrumb-dialog').remove();
        $(document).off('click.ice-breadcrumb keydown.ice-breadcrumb');

        // Resolve column label by positional index.
        // _syncRowTds keeps TDs and THs in the same column order, so
        // $td.index() → matching <th> is always correct — even when multiple
        // cells share the same data-editable value (e.g. all dim_classification
        // cells share "dim_classification", so a class selector returns nothing).
        var $th      = $('#ogTasksPanelColNames th').eq($td.index());
        var colLabel = $th.length
            ? $.trim($th.text())
            : ($td.attr('data-editable') || '').replace(/^(cp_|task_|dim_)/i, '').replace(/_/g, ' ');

        var $dialog = $(
            '<div id="ice-breadcrumb-dialog" role="dialog" aria-modal="true" class="ice-breadcrumb-dialog">' +
                '<div class="ice-breadcrumb-dialog-header">' +
                    '<span>' + _selectTitle(colLabel) + '</span>' +
                    '<button type="button" class="ice-breadcrumb-close" aria-label="' + lang('close') + '">&times;</button>' +
                '</div>' +
                '<div class="ice-breadcrumb-dialog-body">' +
                    '<div class="ice-dim-loading">' + lang('loading') + '</div>' +
                '</div>' +
            '</div>'
        );

        $('body').append($dialog);

        // Position now that the dialog is in the DOM and measurable. Opens below
        // the cell by default, flips above for rows near the bottom of the
        // viewport, and caps the body height so content stays fully visible.
        _positionDialog($dialog, $td);

        $dialog.find('.ice-breadcrumb-close').trigger('focus');

        // Shared close helper — removes the dialog and unbinds document handlers.
        // _outsideHandler is stored so _doClose() can remove the capture-phase
        // listener regardless of how the dialog is closed.
        var _outsideHandler = null;

        function _doClose() {
            $dialog.remove();
            $(document).off('keydown.ice-breadcrumb');
            if (_outsideHandler) {
                document.removeEventListener('click', _outsideHandler, true);
                _outsideHandler = null;
            }
        }

        // ── Load dimension members ───────────────────────────────────────
        var dimId        = parseInt($td.attr('data-dim-id'), 10) || 0;
        var taskId       = $td.closest('tr[data-task-id]').attr('data-task-id');
        var task   = taskId ? ogTasksCache.getTask(taskId) : null;
        // task.members is the flat array of all member IDs belonging to this task
        var taskMemberIds = (task && task.members)
            ? task.members.map(function (id) { return parseInt(id, 10); })
            : [];

        // Extract currently-displayed members from the cell DOM BEFORE the AJAX call.
        // These are ground-truth for what is already selected in this dimension — they
        // come directly from the rendered breadcrumb HTML so they're always correct even
        // when the server search results don't include the member (e.g. beyond limit=200).
        // Each .real-breadcrumb span carries the member ID in its class ("bread-crumb-N")
        // and the display name in its title attribute (or inner link text).
        var cellMemberSeed = [];
        $td.find('.real-breadcrumb').each(function () {
            var cls  = this.className;
            var m    = cls.match(/\bbread-crumb-(\d+)\b/);
            if (!m) return;
            var mid  = parseInt(m[1], 10);
            var name = $(this).attr('title') || $(this).find('a').first().text() || ('Member ' + mid);
            var cm   = cls.match(/\bog-wsname-color-(\d+)\b/);
            cellMemberSeed.push({ id: mid, name: $.trim(name), color: cm ? parseInt(cm[1], 10) : 0, parent: 0 });
        });

        if (!dimId) {
            // No dimension ID: fall back to showing the raw breadcrumb HTML
            var $raw = $td.find('.task-breadcrumb-container').clone();
            $dialog.find('.ice-breadcrumb-dialog-body').html($raw);
        } else {
            og.openLink(og.getUrl('dimension', 'search_dimension_members_tree'), {
                hideLoading: true,
                post: {
                    dimension_id:            dimId,
                    limit:                   200,
                    random:                  1,    // required: endpoint only runs when query or random is truthy
                    ignore_context_filters:  1,    // note: plural — matches PHP parameter name
                    content_object_type_id:  ogTasks.tasks_object_type_id  // for is_multiple lookup
                },
                callback: function (success, data) {
                    if (!$.contains(document.body, $dialog[0])) return;

                    var $body = $dialog.find('.ice-breadcrumb-dialog-body');

                    if (!success || !data) {
                        $body.html('<p class="ice-dim-empty">' + lang('error') + '</p>');
                        return;
                    }

                    // Normalise: response.members can be an array or a keyed object
                    var raw = data.members || [];
                    var allMembers = Array.isArray(raw) ? raw : Object.values(raw);
                    // Strip group separators (id ≤ 0) produced by the search endpoint
                    allMembers = allMembers.filter(function (m) { return m && parseInt(m.id, 10) > 0; });

                    // true when fo_dimension_object_type_contents.is_multiple = 1
                    var isMultiple = !!(data && data.is_multiple);

                    // Inject any cell-seed members that the search didn't return
                    // (e.g. the selected member is beyond the limit=200 cut-off).
                    // Without this, _rebuildChips() can't render the chip because it
                    // looks up each selectedId in allMembers and skips missing ones.
                    var dimMemberIdSet = {};
                    allMembers.forEach(function (m) { dimMemberIdSet[parseInt(m.id, 10)] = true; });
                    cellMemberSeed.forEach(function (seed) {
                        if (!dimMemberIdSet[seed.id]) {
                            allMembers.push(seed);
                            dimMemberIdSet[seed.id] = true;
                        }
                    });

                    // Seed selectedIds from the cell DOM (ground truth) rather than
                    // filtering taskMemberIds against search results — the filter fails
                    // when the selected member is not in the search results.
                    var dimMemberIds = allMembers.map(function (m) { return parseInt(m.id, 10); });
                    var selectedIds  = cellMemberSeed.map(function (s) { return s.id; });
                    // Also pick up any taskMemberIds that are in allMembers but weren't
                    // reflected in the cell (e.g. stale DOM after a concurrent edit).
                    taskMemberIds.forEach(function (id) {
                        if (dimMemberIdSet[id] && selectedIds.indexOf(id) === -1) {
                            selectedIds.push(id);
                        }
                    });

                    var isSaving   = false;
                    var _collapsed = {}; // mid → true when subtree is hidden (browsing mode, no search)
                    // Search mode: children of a matched member aren't loaded until the
                    // user expands it. _searchExpanded tracks which matched/ancestor
                    // members the user has manually expanded to reveal ALL their children
                    // (matching or not) — mirrors MemberTreeAjax's expandnode behaviour.
                    // _childrenFetched avoids re-requesting a member's children once loaded.
                    var _searchExpanded  = {};
                    var _childrenFetched = {};

                    // Sends newIds to the server immediately, then redraws the row.
                    function _saveSelection(newIds, onDone) {
                        if (isSaving) return;
                        isSaving = true;
                        $dialog.find('.ice-dim-selector').addClass('ice-dim-saving');
                        og.openLink(og.getUrl('task', 'quick_edit_task', {id: taskId}), {
                            method: 'POST',
                            post: {
                                'task[remove_from_dimension]': dimId,
                                'task[members]':               JSON.stringify(newIds)
                            },
                            hideLoading: true,
                            callback: function (ok, resp) {
                                isSaving = false;
                                $dialog.find('.ice-dim-selector').removeClass('ice-dim-saving');
                                if (ok && !resp.errorCode) {
                                    selectedIds = newIds.slice();
                                    ogTasks.drawTaskRowAfterEdit(resp);
                                    if (onDone) onDone(true);
                                } else {
                                    og.err((resp && resp.errorMessage) || lang('error edit task'));
                                    if (onDone) onDone(false);
                                }
                            }
                        });
                    }

                    // ── DOM helpers ──────────────────────────────────────

                    function _rebuildChips() {
                        var $chips = $dialog.find('.ice-dim-chips');
                        $chips.empty();
                        // The counter + clear bar only exists for multi-select fields
                        // with at least one selection; single-select never shows it.
                        var $selBar = $dialog.find('.ice-dim-selection-bar');
                        $selBar.toggle(isMultiple && selectedIds.length > 0);
                        $selBar.find('.ice-dim-selection-count').text(lang('n selected', selectedIds.length));
                        // Render in selectedIds order so chips don't shift position
                        // when a new item is added. Each × button gets its own
                        // closure-bound click handler so mid is never read from the
                        // DOM — eliminating any delegation mismatch.
                        selectedIds.forEach(function (chipMid) {
                            var m = null;
                            for (var i = 0; i < allMembers.length; i++) {
                                if (parseInt(allMembers[i].id, 10) === chipMid) { m = allMembers[i]; break; }
                            }
                            if (!m) return;
                            var color    = parseInt(m.color, 10) || 0;
                            var rawName  = m.name || m.display_name || m.text || '';
                            var name     = $('<s>').text(rawName).html();
                            var $btn  = $('<button type="button" class="ice-dim-chip-remove" title="' + lang('remove') + '"><i class="icon-circle-x"></i></button>');
                            $btn.on('click', (function (mid) {
                                return function (e) {
                                    e.stopPropagation();
                                    var newIds = selectedIds.filter(function (id) { return id !== mid; });
                                    _saveSelection(newIds, function () {
                                        _rebuildChips();
                                        _rebuildList($sel.find('.ice-dim-filter').val());
                                    });
                                };
                            }(chipMid)));
                            var $chip = $('<span class="og-wsname-color-' + color + ' ice-dim-chip" data-mid="' + m.id + '">');
                            $chip.append($('<span>').attr('title', rawName).html(name)).append($btn);
                            $chips.append($chip);
                        });
                    }

                    // Returns allMembers sorted by depth-first tree traversal.
                    // Each member's `parent` field (0 = root or parent not in set)
                    // is used to build parent→children buckets; each bucket is
                    // sorted alphabetically before visiting.
                    function _treeOrder(members) {
                        var allIds     = {};
                        var childrenOf = {};
                        members.forEach(function (m) {
                            allIds[parseInt(m.id, 10)] = true;
                        });
                        members.forEach(function (m) {
                            var pid = parseInt(m.parent, 10) || 0;
                            // treat as root when parent is absent from this response
                            if (!allIds[pid]) pid = 0;
                            if (!childrenOf[pid]) childrenOf[pid] = [];
                            childrenOf[pid].push(m);
                        });
                        Object.keys(childrenOf).forEach(function (pid) {
                            childrenOf[pid].sort(function (a, b) {
                                return (a.name || '').localeCompare(b.name || '');
                            });
                        });
                        var result  = [];
                        var visited = {};
                        function visit(pid) {
                            (childrenOf[pid] || []).forEach(function (m) {
                                var mid = parseInt(m.id, 10);
                                if (visited[mid]) return;
                                visited[mid] = true;
                                result.push(m);
                                visit(mid);
                            });
                        }
                        visit(0);
                        // fallback: include anything not reachable from root 0
                        members.forEach(function (m) {
                            var mid = parseInt(m.id, 10);
                            if (!visited[mid]) { visited[mid] = true; result.push(m); }
                        });
                        return result;
                    }

                    // Returns an object {mid: true} for every member that has
                    // at least one child inside the current allMembers set.
                    function _buildChildSet(members) {
                        var allIds   = {};
                        var hasChild = {};
                        members.forEach(function (m) { allIds[parseInt(m.id, 10)] = true; });
                        members.forEach(function (m) {
                            var pid = parseInt(m.parent, 10) || 0;
                            if (allIds[pid]) hasChild[pid] = true;
                        });
                        return hasChild;
                    }

                    // When searching, decides which members are eligible to render:
                    // matches, their ancestor chain (for hierarchy context — same as
                    // the server's `parents=true` behaviour), and every child of a
                    // member the user has manually expanded via _searchExpanded
                    // (which may include non-matching siblings, fetched on demand).
                    // Returns {keep: {mid:true}, childrenOf: {parentMid: [members]}}.
                    function _computeSearchKeepSet(needle) {
                        var byId = {};
                        allMembers.forEach(function (m) { byId[parseInt(m.id, 10)] = m; });

                        var keep = {};
                        allMembers.forEach(function (m) {
                            var name = m.name || m.display_name || m.text || '';
                            if (name.toLowerCase().indexOf(needle) === -1) return;
                            var mid = parseInt(m.id, 10);
                            keep[mid] = true;
                            var cur = m;
                            var guard = 0;
                            while (cur && guard++ < 50) {
                                var pid = parseInt(cur.parent, 10) || 0;
                                if (!pid || !byId[pid] || keep[pid]) break;
                                keep[pid] = true;
                                cur = byId[pid];
                            }
                        });

                        var childrenOf = {};
                        allMembers.forEach(function (m) {
                            var pid = parseInt(m.parent, 10) || 0;
                            if (!childrenOf[pid]) childrenOf[pid] = [];
                            childrenOf[pid].push(m);
                        });

                        // Fixed-point: a manually-expanded member reveals its direct
                        // children; if one of those children is itself expanded, its
                        // own children get revealed on the next pass, and so on.
                        var changed = true;
                        var iterations = 0;
                        while (changed && iterations++ < 20) {
                            changed = false;
                            Object.keys(_searchExpanded).forEach(function (midStr) {
                                var mid = parseInt(midStr, 10);
                                if (!_searchExpanded[mid] || !keep[mid]) return;
                                (childrenOf[mid] || []).forEach(function (child) {
                                    var cid = parseInt(child.id, 10);
                                    if (!keep[cid]) { keep[cid] = true; changed = true; }
                                });
                            });
                        }

                        return { keep: keep, childrenOf: childrenOf };
                    }

                    // Recursively hide every rendered child of `mid`.
                    function _hideSubtree($list, mid) {
                        $list.find('li[data-parent-mid="' + mid + '"]').each(function () {
                            $(this).hide();
                            _hideSubtree($list, parseInt($(this).attr('data-mid'), 10));
                        });
                    }

                    // Show all items, then re-hide subtrees for every collapsed node.
                    function _applyCollapsedState($list) {
                        $list.find('li.ice-dim-list-item').show();
                        Object.keys(_collapsed).forEach(function (mid) {
                            if (_collapsed[parseInt(mid, 10)]) {
                                _hideSubtree($list, parseInt(mid, 10));
                            }
                        });
                    }

                    function _rebuildList(filterText) {
                        var $list    = $dialog.find('.ice-dim-list');
                        $list.empty();
                        var needle    = (filterText || '').toLowerCase();
                        var ordered   = _treeOrder(allMembers);
                        var hasChild  = _buildChildSet(allMembers); // fallback for members without `expandable`
                        var searchRes = needle ? _computeSearchKeepSet(needle) : null;
                        var shown     = 0;
                        ordered.forEach(function (m) {
                            var mid = parseInt(m.id, 10);
                            if (searchRes && !searchRes.keep[mid]) return;
                            var name   = m.name || m.display_name || m.text || '';
                            var color  = parseInt(m.color,  10) || 0;
                            var depth  = parseInt(m.depth,  10) || 0;
                            var pid    = parseInt(m.parent, 10) || 0;
                            var isSel  = selectedIds.indexOf(mid) !== -1;
                            var safeN  = $('<s>').text(name).html();
                            var pl     = depth * 10;
                            // Whether this member has children at all: prefer the
                            // server-provided flag (accurate even when a matched
                            // member's non-matching children haven't been fetched yet).
                            var expandable = (typeof m.expandable !== 'undefined')
                                ? !!m.expandable
                                : !!hasChild[mid];
                            // Open/closed chevron: in search mode, "open" means at
                            // least one child is currently rendered underneath it;
                            // in browsing mode it follows the plain _collapsed flag.
                            var isOpen = searchRes
                                ? (searchRes.childrenOf[mid] || []).some(function (c) {
                                      return searchRes.keep[parseInt(c.id, 10)];
                                  })
                                : !_collapsed[mid];
                            var toggleHtml = expandable
                                ? '<button type="button" class="ice-dim-toggle' +
                                      (isOpen ? ' is-open' : '') + '"></button>'
                                : '<span class="ice-dim-toggle-placeholder"></span>';
                            $list.append(
                                '<li class="ice-dim-list-item' + (isSel ? ' is-selected' : '') + '"' +
                                    ' data-mid="' + mid + '" data-parent-mid="' + pid + '"' +
                                    ' style="padding-left:' + pl + 'px">' +
                                    toggleHtml +
                                    '<span class="member-color-dot og-wsname-color-' + color + '"></span>' +
                                    safeN +
                                    // Rendered on EVERY row (CSS shows it only when
                                    // selected) so its reserved width keeps the text
                                    // from shifting when toggling selection.
                                    '<i class="icon-check ice-dim-check"></i>' +
                                '</li>'
                            );
                            shown++;
                        });
                        if (shown === 0) {
                            $list.append('<li class="ice-dim-empty">' + lang('no objects to display') + '</li>');
                        }
                        // When filtering, visibility is already resolved by the keep
                        // set above; when browsing the full tree, respect _collapsed.
                        if (!needle) _applyCollapsedState($list);
                    }

                    // ── Build dialog body ────────────────────────────────
                    var labelCol = $('<span>').text($.trim(colLabel)).html();
                    // Chips render INSIDE the search field (between the magnifier
                    // icon and the text input): .ice-dim-chips is display:contents
                    // so each chip participates directly in the field's flex wrap.
                    var $sel = $(
                        '<div class="ice-dim-selector">' +
                            '<div class="ice-dim-search">' +
                                '<i class="icon-search"></i>' +
                                '<span class="ice-dim-chips"></span>' +
                                '<input type="text" class="ice-dim-filter" placeholder="' + lang('search placeholder', labelCol) + '">' +
                                '<button type="button" class="ice-dim-search-clear" title="' + lang('clear') + '"><i class="icon-x"></i></button>' +
                            '</div>' +
                            // Selection bar: "N selected" counter + clear-all link.
                            // Only rendered visible for multi-select with selections
                            // (toggled in _rebuildChips).
                            '<div class="ice-dim-selection-bar">' +
                                '<span class="ice-dim-selection-count"></span>' +
                                '<button type="button" class="ice-dim-clear-all">' + lang('clear') + '</button>' +
                            '</div>' +
                            '<ul class="ice-dim-list"></ul>' +
                        '</div>'
                    );
                    $body.html($sel);
                    _rebuildChips();
                    _rebuildList('');
                    // Scroll the list so at least the first selected member is
                    // visible on open (roughly centered). Manual scrollTop math —
                    // scrollIntoView() could also scroll the page behind the dialog.
                    var $firstSel = $sel.find('.ice-dim-list-item.is-selected').first();
                    if ($firstSel.length) {
                        var listEl  = $sel.find('.ice-dim-list')[0];
                        var itemTop = $firstSel[0].getBoundingClientRect().top
                                    - listEl.getBoundingClientRect().top;
                        listEl.scrollTop = itemTop - (listEl.clientHeight - $firstSel[0].offsetHeight) / 2;
                    }
                    // Focus the filter as soon as the selector hits the DOM so the
                    // user can start typing right away (before this, focus sat on
                    // the close button because the input didn't exist yet).
                    $sel.find('.ice-dim-filter').trigger('focus');

                    // ── Filter input ─────────────────────────────────────
                    // The initial AJAX call returns at most `limit` random members.
                    // Fire a server search (debounced) — same endpoint and
                    // parameter shape as MemberTreeAjax.filterTree
                    var _searchSeq   = 0;
                    var _searchTimer = null;

                    $sel.on('input.ice', '.ice-dim-filter', function () {
                        var text = $(this).val();
                        clearTimeout(_searchTimer);

                        // Empty filter → just rebuild from the local cache.
                        if (!text || !text.trim()) {
                            _rebuildList('');
                            return;
                        }

                        _searchTimer = setTimeout(function () {
                            var thisSeq = ++_searchSeq;
                            og.openLink(og.getUrl('dimension', 'search_dimension_members_tree'), {
                                hideLoading: true,
                                hideErrors:  true,
                                post: {
                                    dimension_id:           dimId,
                                    query:                  text,
                                    ignore_context_filters: 1,
                                    content_object_type_id: ogTasks.tasks_object_type_id,
                                    time:                   thisSeq
                                },
                                callback: function (success, data) {
                                    // Dialog may have closed while the AJAX was in flight.
                                    if (!$.contains(document.body, $dialog[0])) return;
                                    if (!success || !data) return;
                                    // Stale-response guard.
                                    if (parseInt(data.time, 10) !== _searchSeq) return;

                                    var raw     = data.members || [];
                                    var fetched = Array.isArray(raw) ? raw : Object.values(raw);
                                    fetched.forEach(function (m) {
                                        if (!m) return;
                                        var mid = parseInt(m.id, 10);
                                        if (mid <= 0) return; // group separators
                                        if (!dimMemberIdSet[mid]) {
                                            allMembers.push(m);
                                            dimMemberIdSet[mid] = true;
                                        }
                                    });
                                    _rebuildList(text);
                                }
                            });
                        }, 300);
                    });

                    // ── Search clear (×) button ───────────────────────────
                    $sel.on('click.ice', '.ice-dim-search-clear', function () {
                        var $f = $sel.find('.ice-dim-filter');
                        if ($f.val()) {
                            $f.val('');
                            _rebuildList('');
                        }
                        $f.trigger('focus');
                    });

                    // ── Click anywhere on the field → focus the text input ──
                    // Chip remove buttons stopPropagation(), so removing a chip
                    // never steals focus through this handler.
                    $sel.on('click.ice', '.ice-dim-search', function () {
                        $sel.find('.ice-dim-filter').trigger('focus');
                    });

                    // ── Remove all selections (multi-select only) ─────────
                    $sel.on('click.ice', '.ice-dim-clear-all', function () {
                        if (!isMultiple || selectedIds.length === 0) return;
                        _saveSelection([], function () {
                            _rebuildChips();
                            _rebuildList($sel.find('.ice-dim-filter').val());
                        });
                    });

                    // ── Toggle collapse / expand ──────────────────────────
                    $sel.on('click.ice', '.ice-dim-toggle', function (e) {
                        e.stopPropagation(); // don't fire list-item selection
                        var mid    = parseInt($(this).closest('li').attr('data-mid'), 10);
                        var needle = $.trim($sel.find('.ice-dim-filter').val());

                        if (!needle) {
                            // Browsing mode: every child is already loaded locally —
                            // just show/hide the subtree.
                            _collapsed[mid] = !_collapsed[mid];
                            $(this).toggleClass('is-open', !_collapsed[mid]);
                            _applyCollapsedState($dialog.find('.ice-dim-list'));
                            return;
                        }

                        // Search mode: reveal (or re-hide) ALL of this member's
                        // children, including ones that didn't match the query —
                        // mirroring the member-selector's expand-arrow behaviour.
                        if (_searchExpanded[mid]) {
                            delete _searchExpanded[mid];
                            _rebuildList(needle);
                            return;
                        }
                        _searchExpanded[mid] = true;

                        if (_childrenFetched[mid]) {
                            _rebuildList(needle);
                            return;
                        }
                        _childrenFetched[mid] = true;

                        og.openLink(og.getUrl('dimension', 'get_member_childs', {
                            member:                 mid,
                            ignore_context_filters: 1,
                            limit:                  500
                        }), {
                            hideLoading: true,
                            hideErrors:  true,
                            callback: function (success, data) {
                                if (!$.contains(document.body, $dialog[0])) return;
                                if (success && data && data.members) {
                                    var raw     = data.members;
                                    var fetched = Array.isArray(raw) ? raw : Object.values(raw);
                                    fetched.forEach(function (cm) {
                                        if (!cm) return;
                                        var cid = parseInt(cm.id, 10);
                                        if (cid > 0 && !dimMemberIdSet[cid]) {
                                            allMembers.push(cm);
                                            dimMemberIdSet[cid] = true;
                                        }
                                    });
                                }
                                _rebuildList($sel.find('.ice-dim-filter').val());
                            }
                        });
                    });

                    // ── Click list item — save immediately ────────────────
                    // Multiple: toggle and stay open.
                    // Single:   pick, save, then close.
                    $sel.on('click.ice', '.ice-dim-list-item', function () {
                        var mid   = parseInt($(this).attr('data-mid'), 10);
                        if (!mid) return;
                        var isSel = selectedIds.indexOf(mid) !== -1;
                        if (isMultiple) {
                            var newIds = isSel
                                ? selectedIds.filter(function (id) { return id !== mid; })
                                : selectedIds.concat([mid]);
                            _saveSelection(newIds, function () {
                                _rebuildChips();
                                // Clear the search text after a pick — same convention
                                // as the standard member selector (MemberTreeAjax),
                                // which resets its filter on every node click.
                                $sel.find('.ice-dim-filter').val('');
                                _rebuildList('');
                            });
                        } else {
                            var newIds = isSel ? [] : [mid];
                            _saveSelection(newIds, function (ok) {
                                if (ok) _doClose();
                            });
                        }
                    });
                }
            });
        }

        // ── Close handlers ───────────────────────────────────────────────
        // X button → just close
        $dialog.find('.ice-breadcrumb-close').on('click', function () {
            _doClose();
        });

        setTimeout(function () {
            // Click outside → close. Bound in capture phase so it fires even
            // when ExtJS table handlers call stopPropagation() in the bubble phase.
            _outsideHandler = function (e) {
                if (!$(e.target).closest('#ice-breadcrumb-dialog').length) {
                    _doClose();
                }
            };
            document.addEventListener('click', _outsideHandler, true);

            // Escape → close
            $(document).on('keydown.ice-breadcrumb', function (e) {
                if (e.key === 'Escape') { _doClose(); }
            });
        }, 50);
    }

    // ── Private — editor lifecycle ─────────────────────────────────────

    function _activate($td) {
        // If a blur timer is pending on the current editor, cancel it.
        // _activate will call _commit() synchronously below if an old editor
        // is still open, so the deferred blur commit must not fire later.
        if (_blurTimer) {
            clearTimeout(_blurTimer);
            _blurTimer = null;
        }
        if (_activeCell && _activeCell[0] !== $td[0]) {
            _commit();
        }

        var tId = $td.closest('tr[data-task-id]').attr('data-task-id');
        var fld = $td.attr('data-editable');

        if (!tId || !fld) {
            return;
        }

        var task = ogTasksCache.getTask(tId);
        if (!task) {
            return;
        }

        // Dimension / breadcrumb cells are read-only in the text-editor sense;
        // they open a member-picker dialog instead.  Route here so that Tab
        // navigation (and any other _activate() caller) behaves the same as
        // a pencil-click on those cells.
        if ($td.find('.task-breadcrumb-container').length) {
            _showBreadcrumbDialog($td);
            return;
        }

        // Skip CP fields that don't exist for this task's subtype. Returns before
        // any state is set, so Tab navigation simply lands on the next editable
        // cell rather than opening an editor that can never save.
        if (fld.indexOf('cp_') === 0 && !_cpAppliesToTaskSubtype(_findCpMeta(fld.substring(3)), task)) {
            return;
        }

        _taskId     = tId;
        _field      = fld;
        _origHtml   = $td.html();
        _activeCell = $td;
        $td.addClass('ice-active');

        var editor = _customEditors[fld] || _builtinEditors[fld];

        if (!editor && fld.indexOf('cp_') === 0) {
            editor = _makeCPEditor(fld.substring(3));
            if (!editor) {
                $td.removeClass('ice-active');
                _activeCell = null;
                return;
            }
        }

        if (!editor) {
            editor = _builtinEditors._text;
        }

        // Cache the resolved editor so _commit() can reuse it without rebuilding.
        _activeEditor = editor;

        var $input = editor.create($td, task);
        if (!$input) {
            $td.removeClass('ice-active');
            _activeCell = null;
            return;
        }

        // Capture the cell's content width BEFORE emptying it. Overlay (absolute)
        // select editors are taken out of flow, so the column would collapse
        // without pinning the cell to this width (see the select block below).
        var _cellContentW = Math.ceil($td.width());
        $td.empty().append($input);

        // Snapshot what the editor would post right now, before the user touches
        // it. _commit() compares against this so blurring away from an untouched
        // cell (e.g. click in, click out) is a no-op instead of a save round-trip
        // — which also redraws the row and re-resolves its groups. Works for every
        // editor type (CPs included), unlike the per-field checks in _changed().
        // Uses the same input lookup as _commit() so both serialize the same control.
        var $snapshotInput = $td.find('input, select, textarea').first();
        _initialPost = $snapshotInput.length ? editor.serialize($snapshotInput, task) : null;

        // If create() returned a wrapper element (e.g. the date field's
        // <span class="ice-date-wrapper">) resolve the real focusable child so
        // that focus, text-selection, and the blur→commit handler all wire to
        // the actual <input>, not the non-focusable wrapper span.
        var $focusTarget = ($input.is('input, select, textarea'))
            ? $input
            : $input.find('input:not(.ice-date-hidden), select, textarea').first();
        if (!$focusTarget.length) $focusTarget = $input; // safe fallback

        // Async selects are revealed (and focused) later by _revealAsyncSelect once
        // their options load. Focusing now would be undone when that flow hides the
        // control with visibility:hidden — and the resulting blur, once the blur→
        // commit handler below is wired, would immediately close the editor.
        var _isAsyncSelect = $focusTarget.is('select') && $focusTarget.hasClass('ice-async-select');
        if (!_isAsyncSelect) {
            $focusTarget.focus();
            if ($focusTarget[0] && typeof $focusTarget[0].select === 'function' && $focusTarget.is('input[type="text"]')) {
                $focusTarget[0].select();
            }
        }

        // ── Fit <select> editors to their content (not the column) ─────
        // Dropdowns float over the table (position:absolute, see tasks.css) and
        // get a content-based width so long options stay readable in narrow
        // columns — without widening the column or shifting the layout. Every
        // other editor type keeps the default in-flow width:100%.
        if ($focusTarget.is('select')) {
            // The control is out of flow, so pin the cell to its pre-edit width
            // to stop the now-empty column from collapsing and shifting the table.
            $td.css('min-width', _cellContentW + 'px');

            if (_selectObserver) {
                _selectObserver.disconnect();
                _selectObserver = null;
            }

            if ($focusTarget.hasClass('ice-async-select')) {
                // assigned_to / contact CPs fill their <option>s asynchronously
                // after create() returns. Sizing now would fit the lone placeholder
                // option, then resize when the real list arrives — a visible width
                // change either way (grow, or shrink if we pre-reserved). Instead
                // keep the control hidden behind a small loading spinner and reveal
                // it ONCE, already content-fitted, the moment its options finish
                // loading. The user only ever sees the final, tight size.
                _revealAsyncSelect($focusTarget, $td);
            } else {
                // Synchronous selects (priority, list, boolean) have every option
                // up front — fit to content immediately, no resize.
                _autoSizeSelect($focusTarget, $td);
            }
        }

        // ── Floating ok / cancel bar (all editor types) ────────────
        // Saves only on explicit ✓ (or Enter). Blur-away cancels the edit.
        var $bar = $(
            '<span class="ice-edit-actions">' +
                '<button type="button" class="ice-edit-ok" title="' + lang('ok') + '"><i class="icon-check"></i></button>' +
                '<button type="button" class="ice-edit-cancel"  title="' + lang('cancel')  + '"><i class="icon-x"></i></button>' +
            '</span>'
        );
        $td.append($bar);

        // mousedown preventDefault keeps the input focused so blur does NOT
        // fire when the user clicks one of the two action buttons.
        $bar.on('mousedown.ice', function (e) { e.preventDefault(); });
        $bar.on('click.ice', '.ice-edit-ok', function () { _commit(); });
        $bar.on('click.ice', '.ice-edit-cancel',  function () { _cancel(); });

        // If the editor declares a propagation warning (e.g. 'display_member_property'
        // CPs that write through to the dimension's member), surface it next to the
        // action bar — same message the normal task form shows.
        if (editor.warningMsg) {
            $('<div class="ice-cp-mfio-warning"></div>').text(editor.warningMsg).appendTo($td);
        }

        // Blur = clicked/tabbed away without confirming → commit (save).
        // The 200 ms delay lets a button mousedown+click complete first,
        // so clicking ✓ or ✕ doesn't also trigger a commit/cancel race.
        $focusTarget.on('blur.ice', function () {
            _blurTimer = setTimeout(function () {
                _blurTimer = null;
                if (_activeCell && _activeCell[0] === $td[0] && !_saving) {
                    _commit();
                }
            }, 200);
        });
    }

    function _commit() {
        if (!_activeCell || _saving) {
            return;
        }

        var $td      = _activeCell;
        var fld      = _field;
        var tId      = _taskId;
        // Capture origHtml now: _origHtml is module-level and may be clobbered
        // if the user activates a different cell before this AJAX callback fires.
        var origHtml = _origHtml;
        var task     = ogTasksCache.getTask(tId);

        var editor = _customEditors[fld]
            || _builtinEditors[fld]
            || _activeEditor
            || _builtinEditors._text;

        var $input  = $td.find('input, select, textarea').first();
        var postData = editor.serialize($input, task);

        if (!postData) {
            _cancel();
            return;
        }

        // Client-side required-CP check. Server is still authoritative (see
        // do_quick_edit_task) — this just blocks the round-trip for a cleaner UX.
        if (fld && fld.indexOf('cp_') === 0) {
            var cpId = fld.substring(3);
            var cpMeta = _findCpMeta(cpId);
            if (cpMeta && cpMeta.is_required) {
                var postKey = 'task[custom_property_values][' + cpId + ']';
                var v = postData[postKey];
                var isEmpty = (v === null || v === undefined)
                           || (typeof v === 'string' && $.trim(v) === '')
                           || ($.isArray(v) && v.length === 0);
                if (isEmpty) {
                    og.err(lang('value cannot be empty', cpMeta.name || ''));
                    $input.focus();
                    return; // keep editor open so the user can correct
                }
            }
        }

        if (!_changed(postData, task, fld)) {
            $td.html(_origHtml);
            $td.removeClass('ice-active');
            _reset();
            return;
        }

        _saving = true;
        $td.addClass('ice-saving');

        og.openLink(og.getUrl('task', 'quick_edit_task', {id: tId}), {
            method: 'POST',
            post: postData,
            hideLoading: true,
            // og.processResponse already shows og.err() for errorCode != 0; suppress
            // it so our callback below is the single source of the error toast.
            hideErrors: true,
            callback: function (success, data) {
                _saving = false;

                // The row may have been redrawn (replaceWith / innerHTML) while this
                // AJAX was in flight.  Query the live DOM instead of relying on the
                // stale $td reference, which may now point to a detached element.
                var $liveCells = $('#tasksPanelContainer tr[data-task-id="' + tId + '"] td[data-editable="' + fld + '"]');

                if ($liveCells.length) {
                    $liveCells.removeClass('ice-saving');
                } else if ($.contains(document.documentElement, $td[0])) {
                    $td.removeClass('ice-saving');
                }

                if (success && !data.errorCode) {
                    // Guard: only reset global ICE state if this save's cell is still the
                    // active cell.  If the user activated a different cell while this AJAX
                    // was in flight, _reset() would break that new editor.
                    if (!_activeCell || _activeCell[0] === $td[0]) {
                        _reset();
                    }
                    ogTasks.drawTaskRowAfterEdit(data);
                } else {
                    // Restore original HTML to every visible instance of this cell (a task
                    // may appear in multiple groups).  Fall back to the old $td only when
                    // it is still attached to the document.
                    if ($liveCells.length) {
                        $liveCells.html(origHtml).removeClass('ice-active');
                    } else if ($.contains(document.documentElement, $td[0])) {
                        $td.html(origHtml);
                        $td.removeClass('ice-active');
                    }
                    // Same guard as the success path. Without it, an error here would
                    // wipe the state of whatever cell the user has already moved on to.
                    // Typical case: a start>due validation error — the user clicks the
                    // due-date cell to fix it (which fires this save via _activate ->
                    // _commit and opens the due editor); an unconditional _reset() would
                    // then nuke that due editor and silently swallow its next save.
                    if (!_activeCell || _activeCell[0] === $td[0]) {
                        _reset();
                    }
                    var msg = (data && data.errorMessage) ? data.errorMessage : lang('error edit task');
                    og.err(msg);
                }
            },
            scope: this
        });
    }

    function _cancel() {
        if (!_activeCell) return;
        _activeCell.html(_origHtml);
        _activeCell.removeClass('ice-active');
        _reset();
    }

    function _reset() {
        if (_blurTimer) {
            clearTimeout(_blurTimer);
            _blurTimer = null;
        }
        if (_selectObserver) {
            _selectObserver.disconnect();
            _selectObserver = null;
        }
        // Remove the min-width pinned for overlay select editors (no-op for other
        // editor types / cells that weren't pinned). On the save path the row is
        // redrawn with a fresh cell, so this only matters for cancel/unchanged.
        if (_activeCell) {
            _activeCell.css('min-width', '');
        }
        _activeCell   = null;
        _origHtml     = null;
        _taskId       = null;
        _field        = null;
        _activeEditor = null;
        _initialPost  = null;
    }

    // ── Change detection ───────────────────────────────────────────────

    // Shallow key/value comparison of two serialize() results. Values are compared
    // as strings so a numeric id vs. its string form don't read as a change.
    function _samePost(a, b) {
        if (!a || !b) return false;
        var ka = Object.keys(a), kb = Object.keys(b);
        if (ka.length !== kb.length) return false;
        for (var i = 0; i < ka.length; i++) {
            var k = ka[i];
            if (!Object.prototype.hasOwnProperty.call(b, k)) return false;
            var va = a[k], vb = b[k];
            if ($.isArray(va) || $.isArray(vb)) {
                if (JSON.stringify(va) !== JSON.stringify(vb)) return false;
            } else if (String(va == null ? '' : va) !== String(vb == null ? '' : vb)) {
                return false;
            }
        }
        return true;
    }

    function _changed(postData, task, field) {
        // Untouched editor: what we'd post is exactly what we would have posted the
        // moment it opened. Editor-agnostic, so it covers CPs and plugin editors
        // that the per-field cases below don't know about.
        if (_initialPost && _samePost(postData, _initialPost)) {
            return false;
        }
        switch (field) {
            case 'task_name':
                return postData['task[name]'] !== task.title;
            case 'assigned_to':
                return parseInt(postData['task[assigned_to_contact_id]']) !== (task.assignedToId || 0);
            case 'priority':
                return parseInt(postData['task[priority]']) !== task.priority;
            // case 'percent_completed':
            //     return parseInt(postData['task[percent_completed]']) !== task.percentCompleted;
            case 'due_date':
                var newDue = postData['task[task_due_date]'];
                if (newDue === '') return !!task.dueDate;
                return newDue !== _isoToServerDate(_tsToISODate(task.dueDate));
            case 'start_date':
                var newStart = postData['task[task_start_date]'];
                if (newStart === '') return !!task.startDate;
                return newStart !== _isoToServerDate(_tsToISODate(task.startDate));
            default:
                return true;
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────

    function _tsToISODate(ts) {
        if (!ts) return '';
        var d    = new Date(ts * 1000);
        var yyyy = d.getUTCFullYear();
        var mm   = ('0' + (d.getUTCMonth() + 1)).slice(-2);
        var dd   = ('0' + d.getUTCDate()).slice(-2);
        return yyyy + '-' + mm + '-' + dd;
    }

    function _isoToServerDate(isoStr) {
        if (!isoStr) return '';
        var parts = isoStr.split('-');
        var yyyy = parts[0], mm = parts[1], dd = parts[2];
        var fmt = (og.preferences && og.preferences['date_format']) || 'm/d/Y';
        return fmt
            .replace('Y', yyyy)
            .replace('y', yyyy.substring(2))
            .replace('m', mm)
            .replace('n', parseInt(mm).toString())
            .replace('d', dd)
            .replace('j', parseInt(dd).toString());
    }

    // Inverse of _isoToServerDate: parse a system-format date string (e.g. "15/01/2024"
    // when date_format is "d/m/Y") back to ISO "yyyy-mm-dd".  Used when raw_value for a
    // date CP comes from the DB in system format.
    function _serverDateToISO(dateStr) {
        if (!dateStr) return '';
        // Fast path: already in ISO / Y-m-d format (the DB always stores dates this way
        // after normalization through getDateValue()).  No conversion needed.
        if (/^\d{4}-\d{2}-\d{2}/.test(dateStr)) {
            return dateStr.substring(0, 10);
        }
        // Legacy path: system-format date string (e.g. "15/01/2024") stored by
        // older full-form saves that didn't normalize to Y-m-d.
        var fmt = (og.preferences && og.preferences['date_format']) || 'm/d/Y';
        // Build a regex from the format template, recording which groups map to which part.
        var groups = [];
        var regexStr = fmt.replace(/./g, function (ch) {
            if (ch === 'Y') { groups.push('Y'); return '(\\d{4})'; }
            if (ch === 'y') { groups.push('y'); return '(\\d{2})'; }
            if (ch === 'm') { groups.push('m'); return '(\\d{1,2})'; }
            if (ch === 'n') { groups.push('n'); return '(\\d{1,2})'; }
            if (ch === 'd') { groups.push('d'); return '(\\d{1,2})'; }
            if (ch === 'j') { groups.push('j'); return '(\\d{1,2})'; }
            // Escape regex meta-characters in separators (e.g. '/')
            return ch.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        });
        var m = dateStr.match(new RegExp('^' + regexStr + '$'));
        if (!m) return '';
        var yyyy = '', mm = '', dd = '';
        for (var i = 0; i < groups.length; i++) {
            var v = m[i + 1];
            if (groups[i] === 'Y') { yyyy = v; }
            if (groups[i] === 'y') { yyyy = '20' + v; }
            if (groups[i] === 'm' || groups[i] === 'n') { mm = ('0' + parseInt(v, 10)).slice(-2); }
            if (groups[i] === 'd' || groups[i] === 'j') { dd = ('0' + parseInt(v, 10)).slice(-2); }
        }
        if (!yyyy || !mm || !dd) return '';
        return yyyy + '-' + mm + '-' + dd;
    }

    function _placeholder() {
        return (og.preferences && og.preferences['date_format_tip']) || 'mm/dd/yyyy';
    }

    // ── Number input step ──────────────────────────────────────────────
    //
    // Build a `step` attribute string for a given decimal precision:
    //   0 -> "1", 1 -> "0.1", 2 -> "0.01", 3 -> "0.001"
    function _stepForDecimals(decimals) {
        var d = parseInt(decimals, 10);
        if (isNaN(d) || d <= 0) return '1';
        return '0.' + new Array(d).join('0') + '1';
    }

    // Infer step from a value's decimal places: 0.005 -> "0.001", 0.5 -> "0.1",
    // 1 -> "1". An empty value carries no precision info, so stay permissive ("any").
    function _stepForValue(value) {
        var s = (value == null ? '' : String(value)).trim();
        if (s === '') return 'any';
        var dot = s.indexOf('.');
        if (dot === -1) return '1';
        return _stepForDecimals(s.length - dot - 1);
    }

    // Resolve the step for a numeric/amount CP input: prefer the CP's configured
    // decimal_digits, fall back to inferring from the current value.
    function _cpNumericStep(cpMeta, value) {
        if (cpMeta && cpMeta.decimal_digits != null && String(cpMeta.decimal_digits) !== '') {
            return _stepForDecimals(cpMeta.decimal_digits);
        }
        return _stepForValue(value);
    }

    // ── Content-based sizing for <select> editors ──────────────────────
    //
    // The shared `.ice-input { width: 100% }` rule constrains every editor to
    // the column width.  For dropdowns this clips the options so long values
    // can't be read in narrow columns.  Measure the widest option text and
    // widen the <select> (and therefore its native options popup, which is at
    // least as wide as the control) to fit it — bounded by:
    //   min = the column width   (never shrink below the cell)
    //   max = half the panel width, capped at 480px (never cover the grid)
    //
    // Applied as an inline width so it overrides the stylesheet rule, and
    // re-runnable so async-populated selects (assigned_to, contact CPs) resize
    // once their options arrive (see the MutationObserver wired in _activate).
    function _autoSizeSelect($select, $td) {
        if (!$select || !$select.length || !$select.is('select')) return;

        var minWidth = Math.ceil($td.outerWidth()) || 0;

        var $panel  = $('#tasksPanelContainer');
        var panelW  = $panel.length ? $panel.width() : $(window).width();
        var maxWidth = Math.max(minWidth, Math.min(480, Math.round(panelW * 0.5)));

        // Off-screen meter inheriting the select's font so text metrics match.
        var $meter = $('<span class="ice-select-meter"></span>')
            .css({
                position:   'absolute',
                top:        '-9999px',
                left:       '-9999px',
                visibility: 'hidden',
                whiteSpace: 'nowrap',
                fontFamily: $select.css('font-family'),
                fontSize:   $select.css('font-size'),
                fontWeight: $select.css('font-weight')
            })
            .appendTo('body');

        var widest = 0;
        $select.find('option').each(function () {
            $meter.text($(this).text());
            widest = Math.max(widest, $meter.outerWidth());
        });
        $meter.remove();

        // Room for the dropdown arrow + horizontal padding + border.
        var desired = widest + 36;
        var finalW  = Math.max(minWidth, Math.min(maxWidth, desired));

        $select.css('width', finalW + 'px');
    }

    // ── Reveal-once sizing for async <select> editors ──────────────────
    //
    // assigned_to / contact / user CPs return a <select> holding only the current
    // value, then fill the real option list in via an AJAX callback (which ends by
    // removing the transient .ice-loading class). Sizing the control before that
    // arrives would fit the placeholder and force a second resize when the list
    // lands — the width jump the user sees.
    //
    // Instead we hide the control behind a small loading spinner and reveal it
    // exactly once, already content-fitted, as soon as loading finishes. From the
    // first frame the user sees it, the dropdown is at its final, tight size —
    // never wider than its content and never resized.
    function _revealAsyncSelect($select, $td) {
        $select.css('visibility', 'hidden');
        var $spinner = $('<span class="ice-async-loading"><i class="icon-loader-circle"></i></span>');
        $td.append($spinner);

        var revealed = false;
        function reveal() {
            // Guard: loading still in progress, or already revealed, or the user
            // moved on to a different cell (which tore this editor down).
            if (revealed || $select.hasClass('ice-loading')) return;
            if (!_activeCell || _activeCell[0] !== $td[0]) return;
            revealed = true;
            if (_selectObserver) {
                _selectObserver.disconnect();
                _selectObserver = null;
            }
            $spinner.remove();
            _autoSizeSelect($select, $td);
            $select.css('visibility', '');
            $select.focus();
        }

        if (window.MutationObserver) {
            // Watch for the options being populated (childList) and for .ice-loading
            // being dropped (attributes) — the latter fires on BOTH the success and
            // the error path, so a failed fetch reveals the control promptly too.
            _selectObserver = new MutationObserver(reveal);
            _selectObserver.observe($select[0], {
                childList:        true,
                attributes:       true,
                attributeFilter: ['class']
            });
        }

        // Fast / cached responses may have resolved before the observer attached;
        // and if MutationObserver is unavailable this is the only path. The timeout
        // is also a safety net so the spinner can never get stuck.
        setTimeout(reveal, 0);
        setTimeout(reveal, 4000);
    }

    // ── Date field with calendar trigger ───────────────────────────────
    //
    // Returns a plain <input type="text"> pre-filled in the system date format.
    // A calendar icon button is appended to $td on the next tick (after _activate()
    // has finished its empty+append cycle).  Clicking the icon calls showPicker()
    // on a hidden <input type="date">, which opens the native browser date picker
    // without ever blurring the text input (mousedown.preventDefault keeps focus).
    // When the user picks a date, 'change' converts it back to system format in the
    // text input.  serialize() passes the text value straight through — the server's
    // getDateValue() / DateTimeValueLib::dateFromFormatAndString() already expects
    // the system format string.
    function _makeDateField($td, isoValue) {
        // Return a wrapper <span> so all date-field elements are appended
        // atomically by _activate()'s $td.empty().append($input) — no setTimeout
        // needed and no risk of duplicate elements.
        //
        // _activate() detects non-input wrappers and resolves the real <input>
        // for focus / blur wiring (see $focusTarget logic there).
        var $text = $('<input type="text" class="ice-input ice-date-text">')
            .val(_isoToServerDate(isoValue))
            .attr('placeholder', _placeholder());

        var $hidden  = $('<input type="date" class="ice-date-hidden" tabindex="-1">').val(isoValue);
        // Use an <i> icon embedded inside the text field (not a separate button).
        // It is absolutely positioned over the right edge of the input by CSS.
        var $trigger = $('<i class="icon-calendar ice-date-trigger">');

        // mousedown.preventDefault keeps focus on $text so the blur→commit
        // timer never fires while the native picker is open.
        $trigger.on('mousedown.ice', function (e) { e.preventDefault(); });
        $trigger.on('click.ice', function (e) {
            e.preventDefault();
            e.stopPropagation();
            try {
                $hidden[0].showPicker();
            } catch (ex) {
                // Fallback for browsers without showPicker() (older Safari).
                $hidden.focus();
            }
        });

        // User picked a date → convert ISO → system format in the text input.
        // $text was never blurred (mousedown.preventDefault), so no refocus needed.
        $hidden.on('change.ice', function () {
            var iso = $hidden.val();
            if (iso) $text.val(_isoToServerDate(iso));
        });

        return $('<span class="ice-date-wrapper">').append($text).append($trigger).append($hidden);
    }

    // Look up a CP definition by id in the global ogTasks.custom_properties
    // catalog populated by TaskController::listing (see do_quick_edit_task
    // sibling code that emits id/code/name/type/is_required/is_multiple_values).
    // Used by _commit() (required check) and _makeCPEditor() (numeric branch).
    function _findCpMeta(cpId) {
        if (!ogTasks.custom_properties) {
            return null;
        }
        var list = ogTasks.custom_properties;
        for (var i = 0; i < list.length; i++) {
            if (list[i] && list[i].id == cpId) {
                return list[i];
            }
        }
        return null;
    }

    // ── Built-in editors ───────────────────────────────────────────────

    var _builtinEditors = {

        _text: {
            create: function ($td) {
                return $('<input type="text" class="ice-input">').val($.trim($td.text()));
            },
            serialize: function ($input) {
                return {'task[name]': $input.val()};
            }
        },

        task_name: {
            create: function ($td, task) {
                return $('<input type="text" class="ice-input">').val(task.title);
            },
            serialize: function ($input) {
                var v = $.trim($input.val());
                return v ? {'task[name]': v} : null;
            }
        },

        due_date: {
            create: function ($td, task) {
                return _makeDateField($td, _tsToISODate(task.dueDate));
            },
            serialize: function ($input) {
                return {'task[task_due_date]': $input.val()};
            }
        },

        start_date: {
            create: function ($td, task) {
                return _makeDateField($td, _tsToISODate(task.startDate));
            },
            serialize: function ($input) {
                return {'task[task_start_date]': $input.val()};
            }
        },

        assigned_to: {
            create: function ($td, task) {
                var currentId   = task.assignedToId || 0;
                var currentName = task.assignedToName || '';
                var $sel = $('<select class="ice-input">');

                if (currentId > 0 && currentName) {
                    $sel.append($('<option>').val(currentId).text(og.clean(currentName)).prop('selected', true));
                } else {
                    $sel.append($('<option>').val('0').text(lang('unassigned')));
                }
                // ice-async-select marks this control for the reveal-once flow in
                // _activate (hidden + spinner until its options load, then shown at
                // its final size). ice-loading is the transient state the callback
                // clears when the option list arrives, which triggers the reveal.
                $sel.addClass('ice-loading ice-async-select');

                var params = {};
                if (task.memberIds && task.memberIds.length > 0) {
                    params.member_ids = task.memberIds.join(',');
                } else if (og.contextManager && og.contextManager.plainContext) {
                    params.context = og.contextManager.plainContext();
                }

                og.openLink(og.getUrl('task', 'allowed_users_to_assign', params), {
                    hideLoading: true,
                    callback: function (success, data) {
                        if (!success || !data || !data.companies) {
                            $sel.removeClass('ice-loading');
                            return;
                        }
                        var prev = $sel.val();
                        $sel.empty();
                        $sel.append($('<option>').val('0').text(lang('unassigned')));

                        var allUsers = [];
                        for (var i = 0; i < data.companies.length; i++) {
                            var comp = data.companies[i];
                            if (!comp || !comp.users) continue;
                            for (var j = 0; j < comp.users.length; j++) {
                                if (comp.users[j] && comp.users[j].id) allUsers.push(comp.users[j]);
                            }
                        }
                        allUsers.sort(function (a, b) {
                            return (a.name || '').toLowerCase().localeCompare((b.name || '').toLowerCase());
                        });

                        var foundCurrent = false;
                        for (var k = 0; k < allUsers.length; k++) {
                            var u = allUsers[k];
                            var isSelected = u.id == currentId;
                            if (isSelected) foundCurrent = true;
                            var label = og.clean(u.name) + (u.companyName ? ' (' + og.clean(u.companyName) + ')' : '');
                            $sel.append($('<option>').val(u.id).text(label).prop('selected', isSelected));
                        }

                        if (currentId > 0 && !foundCurrent && currentName) {
                            $sel.append($('<option>').val(currentId).text(og.clean(currentName)).prop('selected', true));
                        }
                        if (prev && prev !== currentId.toString()) $sel.val(prev);

                        $sel.removeClass('ice-loading');
                    }
                });

                return $sel;
            },
            serialize: function ($input) {
                return {'task[assigned_to_contact_id]': $input.val()};
            }
        },

        priority: {
            create: function ($td, task) {
                var opts = [
                    {v: 100, l: lang('urgent priority') },
                    {v: 200, l: lang('highest priority')},
                    {v: 300, l: lang('high priority')   },
                    {v: 400, l: lang('normal priority') },
                    {v: 500, l: lang('low priority')    },
                    {v: 600, l: lang('lowest priority') }
                ];
                var $sel = $('<select class="ice-input">');
                for (var i = 0; i < opts.length; i++) {
                    $sel.append($('<option>').val(opts[i].v).text(opts[i].l).prop('selected', task.priority == opts[i].v));
                }
                return $sel;
            },
            serialize: function ($input) {
                return {'task[priority]': $input.val()};
            }
        },

        // percent_completed: {
        //     create: function ($td, task) {
        //         if (task.is_parent) return null;
        //         return $('<input type="number" class="ice-input" min="0" max="100" step="5">').val(task.percentCompleted || 0);
        //     },
        //     serialize: function ($input) {
        //         var v = Math.min(100, Math.max(0, parseInt($input.val()) || 0));
        //         return {'task[percent_completed]': v, 'task[is_manual_percent_completed]': 1};
        //     }
        // }
    };

    // ── Custom property editor factory ─────────────────────────────────

    function _makeCPEditor(cpId) {
        // Resolve the CP's type at factory scope so both create() and serialize()
        // see it (serialize() needs it for the numeric/amount validation).
        // task-level fields like raw_value still live inside create() since they
        // vary per task.
        var cpMeta = _findCpMeta(cpId);
        var cpType = cpMeta ? cpMeta.type : null;

        // For 'display_member_property' CPs with edition allowed, route the editor by
        // the underlying member CP type (boolean → select, date → date picker, etc.).
        // The server resolves the reference and exposes resolved_type / resolved_values
        // / warning_msg on the CP meta when applicable.
        var isDisplayMemberProp = (cpType === 'display_member_property');
        var effectiveType = (isDisplayMemberProp && cpMeta && cpMeta.resolved_type)
            ? cpMeta.resolved_type
            : cpType;
        var warningMsg = (isDisplayMemberProp && cpMeta && cpMeta.warning_msg)
            ? cpMeta.warning_msg
            : null;
        var resolvedValues = (cpMeta && cpMeta.resolved_values) ? cpMeta.resolved_values : null;

        if (!_canInlineEditCp(cpMeta)) {
            return null;
        }

        return {
            // Exposed to _activate() which appends it to the cell alongside the action bar
            // (no wrapping on the editor itself — keeps width/positioning logic intact).
            warningMsg: warningMsg,
            create: function ($td, task) {
                var displayVal = '', rawVal = null;
                if (task.custom_properties) {
                    for (var i = 0; i < task.custom_properties.length; i++) {
                        var cp = task.custom_properties[i];
                        if (cp && cp.id == cpId) {
                            // Prefer the authoritative type from task data, but only
                            // when it is not a transitive display_member_property — for
                            // those, the server-resolved effectiveType already applies.
                            if (cp.type && !isDisplayMemberProp) {
                                cpType = cp.type;
                                effectiveType = cp.type;
                            }
                            displayVal = $('<div>').html(cp.value || '').text();
                            rawVal     = cp.raw_value != null ? cp.raw_value : null;
                            break;
                        }
                    }
                }

                // Skip property types that don't work with inline editing
                if (NON_EDITABLE_CP_TYPES.indexOf(effectiveType) >= 0) {
                    return null;
                }

                if (effectiveType === 'contact' || effectiveType === 'user' || effectiveType === 'user_select') {
                    return _makeContactCPSelect(cpId, rawVal, displayVal, task, effectiveType);
                }

                if (effectiveType === 'boolean') {
                    var $sel = $('<select class="ice-input">');
                    $sel.append($('<option>').val('0').text(lang('cp boolean not specified')));
                    $sel.append($('<option>').val('1').text(lang('yes')));
                    $sel.append($('<option>').val('-1').text(lang('no')));
                    $sel.val(rawVal != null && String(rawVal) !== '' ? String(rawVal) : '0');
                    return $sel;
                }

                if (effectiveType === 'date' || effectiveType === 'datetime') {
                    // raw_value comes from the DB as a system-format string (e.g. "15/01/2024").
                    // _makeDateField() needs ISO "yyyy-mm-dd", so parse it back first.
                    var isoDate = rawVal ? _serverDateToISO(String(rawVal)) : '';
                    return _makeDateField($td, isoDate);
                }

                if (effectiveType === 'numeric' || effectiveType === 'amount') {
                    var numericSrc = (rawVal != null && rawVal !== '') ? rawVal : displayVal;
                    return $('<input type="number" class="ice-input">')
                        .attr('step', _cpNumericStep(cpMeta, numericSrc))
                        .val(numericSrc);
                }

                if (effectiveType === 'list') {
                    return _makeListCPSelect(resolvedValues, rawVal, displayVal);
                }

                return $('<input type="text" class="ice-input">').val(displayVal);
            },
            serialize: function ($input) {
                var val = $input.val();

                if ((effectiveType === 'numeric' || effectiveType === 'amount')
                    && typeof val === 'string' && $.trim(val) !== ''
                    && isNaN(parseFloat(val))) {
                    og.err(lang('value must be numeric', (cpMeta && cpMeta.name) || ''));
                    return null;
                }

                var d = {};
                d['task[custom_property_values][' + cpId + ']'] = val;
                return d;
            }
        };
    }

    // Build a <select> for a CP of type 'list'. `valuesStr` is the comma-separated
    function _makeListCPSelect(valuesStr, rawVal, displayVal) {
        var $sel = $('<select class="ice-input">');
        $sel.append($('<option>').val('').text('— ' + lang('none') + ' —'));
        if (typeof valuesStr === 'string' && valuesStr.length > 0) {
            var opts = valuesStr.split(',');
            for (var i = 0; i < opts.length; i++) {
                var raw = opts[i];
                var v = raw, t = raw;
                var atIx = raw.indexOf('@');
                if (atIx >= 0) {
                    v = raw.substring(0, atIx);
                    t = raw.substring(atIx + 1);
                }
                $sel.append($('<option>').val(v).text(t));
            }
        }
        // Pre-select by raw value if available; otherwise try matching by displayed text.
        if (rawVal != null && String(rawVal) !== '') {
            $sel.val(String(rawVal));
        } else if (displayVal) {
            $sel.find('option').each(function () {
                if ($(this).text() === displayVal) $sel.val($(this).val());
            });
        }
        return $sel;
    }

    function _makeContactCPSelect(cpId, currentId, currentName, task, cpType) {
        var $sel = $('<select class="ice-input">');
        $sel.append($('<option>').val('').text('— ' + lang('none') + ' —'));

        if (currentId && currentName) {
            $sel.append($('<option>').val(currentId).text(og.clean(currentName)).prop('selected', true));
        }
        // ice-async-select marks this control for the reveal-once flow in _activate
        // (hidden + spinner until its options load, then shown at its final size).
        $sel.addClass('ice-loading ice-async-select');

        var url = og.getUrl('contact', 'get_contacts_for_selector');
        if (cpType === 'user_select' || cpType === 'user') {
            // Match the normal-edit form: only non-resource users.
            // is_resource:0 excludes resource-only accounts; without it those
            // extra entries push the total over the 30-item page limit and
            // hide real users (Support EVX, Virginia Janssen, etc.).
            url += (url.indexOf('?') >= 0 ? '&' : '?') + 'filters=' + encodeURIComponent(JSON.stringify({is_user: 1, is_resource: 0}));
        }

        og.openLink(url, {
            hideLoading: true,
            callback: function (success, data) {
                if (!success || !data || !data.contacts) {
                    $sel.removeClass('ice-loading');
                    return;
                }
                $sel.empty();
                $sel.append($('<option>').val('').text('— ' + lang('none') + ' —'));

                var foundCurrent = false;
                for (var i = 0; i < data.contacts.length; i++) {
                    var c = data.contacts[i];
                    if (!c || !c.id || c.id < 0) continue;
                    var isSelected = currentId && c.id == currentId;
                    if (isSelected) foundCurrent = true;
                    $sel.append($('<option>').val(c.id).text(og.clean(c.name)).prop('selected', isSelected));
                }

                if (currentId && !foundCurrent && currentName) {
                    $sel.append($('<option>').val(currentId).text(og.clean(currentName)).prop('selected', true));
                }
                $sel.removeClass('ice-loading');
            }
        });

        return $sel;
    }

    // ── Called by reDrawTask() after each row replacement ─────────────
    //
    // The browser fires mouseleave on the old TD when replaceWith() removes it,
    // but does NOT fire mouseenter on the newly-inserted TD (the mouse hasn't
    // physically moved).  This leaves _$hoverCell null and the action bar hidden
    // even though the cursor is visually over a valid editable cell.
    //
    // We fix this by using the last-tracked mouse position and elementFromPoint()
    // to discover which cell is now under the cursor, then restoring the action
    // bar there — no mouse wiggle required.
    function onRowRedrawn(taskId) {
        // Don't interfere while an editor is open or a save is in flight.
        if (_activeCell || _saving) {
            return;
        }

        // Find the element currently under the cursor.
        var el = document.elementFromPoint(_mouseX, _mouseY);
        var $td = el ? $(el).closest('#tasksPanelContainer td[data-editable]') : $();

        if (!$td.length) {
            // Mouse is not over an editable cell; clean up any stale reference.
            if (_$hoverCell && !$.contains(document.documentElement, _$hoverCell[0])) {
                _$hoverCell = null;
                _$actionBar.hide().appendTo('body');
            }
            return;
        }

        // Skip cells that are already in an edit or saving state.
        if ($td.hasClass('ice-active') || $td.hasClass('ice-saving')) return;

        // Restore the action bar on whatever editable cell is under the cursor.
        // We intentionally do NOT restrict to taskId: the mouse may be over a
        // different task's cell whose action bar was incorrectly hidden by the
        // stale mouseleave from the redrawn row (fixed above, but defensive here too).
        _$hoverCell = $td;
        _$actionBar.appendTo($td).show();
    }

    // ── Return ─────────────────────────────────────────────────────────
    return { init: init, registerEditor: registerEditor, onRowRedrawn: onRowRedrawn };

})(jQuery);
