var React = require('react');
var ReactDOM = require('react-dom');
var ColumnManagerModal = require('./helpers/ColumnManagerModal');
var ColumnManagerContent = ColumnManagerModal.ColumnManagerContent;
var QuickActionsSortList = require('./helpers/QuickActionsSortList');

function isEmptyValue(raw) {
    if (raw === null || raw === undefined) return true;
    if (typeof raw === 'string') return raw.trim() === '';
    if (Array.isArray(raw)) return raw.length === 0;
    return false;
}

function isHtmlVisuallyEmpty(htmlStr) {
    return String(htmlStr).replace(/<[^>]*>/g, '').trim() === '';
}

// Generic table + column-manager widget for any "dimension widget" (evx_projects today;
// any future widget built on plugins/evx_widgets/application/helpers/dimension_widget.php
// mounts through this same component). Everything it renders — columns, rows, labels,
// actions — comes from props.data; nothing here is specific to any one object type.
class DimensionTableWidget extends React.Component {
    constructor(props) {
        super(props);

        var data = props.data || {};
        var availableColumns = Array.isArray(data.availableColumns) ? data.availableColumns : [];
        var selectedColumns = Array.isArray(data.selectedColumns) ? data.selectedColumns : [];
        var selectedColumnsOrder = Array.isArray(data.selectedColumnsOrder) ? data.selectedColumnsOrder : [];

        var visibleSet = {};
        selectedColumns.forEach(function(key) { visibleSet[key] = true; });

        var orderSource = selectedColumnsOrder.length > 0 ? selectedColumnsOrder : selectedColumns;

        var columns = [];
        var seen = {};
        orderSource.forEach(function(key) {
            var def = availableColumns.find(function(c) { return c.key === key; });
            if (def && !seen[key]) {
                columns.push({ key: key, visible: !!visibleSet[key] || selectedColumnsOrder.length === 0 });
                seen[key] = true;
            }
        });
        availableColumns.forEach(function(col) {
            if (!seen[col.key]) {
                columns.push({ key: col.key, visible: false });
                seen[col.key] = true;
            }
        });

        // Action columns are always shown, never user-toggleable. Leading ones (e.g. the
        // "__edit__" pencil) sit before Name; trailing ones (e.g. Tasks' "__quick_actions__"
        // kebab) sit after every other visible column, matching the mockup's right-edge menu.
        var leadingActionDefs = availableColumns.filter(function(c) { return c.is_action && !c.is_trailing_action; });
        var trailingActionDefs = availableColumns.filter(function(c) { return c.is_action && c.is_trailing_action; });
        columns = columns.filter(function(c) {
            var def = availableColumns.find(function(d) { return d.key === c.key; });
            return !def || !def.is_action;
        });
        leadingActionDefs.slice().reverse().forEach(function(def) {
            columns.unshift({ key: def.key, visible: true });
        });
        trailingActionDefs.forEach(function(def) {
            columns.push({ key: def.key, visible: true });
        });

        // rows may opt into starting expanded (row.detail_expanded), e.g. the customer-events widget
        // shows each event's participants open by default.
        var initialExpanded = {};
        (Array.isArray(data.rows) ? data.rows : []).forEach(function(r) {
            if (r && r.detail_expanded && r.detail_html != null && String(r.detail_html) !== '') {
                initialExpanded[r.id] = true;
            }
        });

        this.state = {
            columns: columns,
            showConfigModal: false,
            draftLimit: null,
            draftOrderBy: null,
            draftOrderDir: null,
            // per-row expand state for widgets whose rows carry a detail_html (e.g. the customer-events
            // widget's participant list). Rows without detail_html are never expandable, so widgets
            // that don't provide it behave exactly as before.
            expandedRows: initialExpanded,
            // draft values for optional widget-declared extra numeric settings (data.extraNumberSettings)
            draftExtraNumbers: {},
            saving: false,
            saveError: false
        };

        this.colContentRef = React.createRef();
        this.quickActionsContentRef = React.createRef();

        this.openConfigModal = this.openConfigModal.bind(this);
        this.closeConfigModal = this.closeConfigModal.bind(this);
        this.handleConfigModalKeyDown = this.handleConfigModalKeyDown.bind(this);
        this.applyConfig = this.applyConfig.bind(this);
        this.toggleRowExpand = this.toggleRowExpand.bind(this);
        this.handleExtraNumberChange = this.handleExtraNumberChange.bind(this);
        this.handleLimitChange = this.handleLimitChange.bind(this);
        this.handleOrderByChange = this.handleOrderByChange.bind(this);
        this.handleOrderDirChange = this.handleOrderDirChange.bind(this);
        this.handleHeaderSort = this.handleHeaderSort.bind(this);

        this.tbodyRef = React.createRef();
    }

    componentDidMount() {
        var data = this.props.data || {};
        if (data.genid) {
            window['evxWidgetOpenConfigure_' + data.genid] = this.openConfigModal;
        }
        document.addEventListener('keydown', this.handleConfigModalKeyDown);
        this._fireBreadcrumbReplace();
        this._equalizeRowHeights();
    }

    handleConfigModalKeyDown(e) {
        if (e.key === 'Escape' && this.state.showConfigModal) {
            this.closeConfigModal();
        }
    }

    componentDidUpdate(prevProps, prevState) {
        if (prevState.columns !== this.state.columns) {
            this._fireBreadcrumbReplace();
        }
        this._equalizeRowHeights();
    }

    _equalizeRowHeights() {
        var tbody = this.tbodyRef.current;
        if (!tbody) return;
        // only equalize the main rows; expandable detail rows have their own natural height
        var rows = Array.from(tbody.querySelectorAll('tr.evx-widget-row'));
        if (rows.length < 2) return;

        rows.forEach(function(row) { row.style.height = ''; });

        var maxHeight = Math.max.apply(null, rows.map(function(row) { return row.offsetHeight; }));
        rows.forEach(function(row) { row.style.height = maxHeight + 'px'; });
    }

    componentWillUnmount() {
        var data = this.props.data || {};
        if (data.genid) {
            if (window['evxWidgetOpenConfigure_' + data.genid] === this.openConfigModal) {
                delete window['evxWidgetOpenConfigure_' + data.genid];
            }
        }
        document.removeEventListener('keydown', this.handleConfigModalKeyDown);
    }

    _fireBreadcrumbReplace() {
        if (!window.og || !og.emptyBreadcrumbsToRefresh || !og.eventManager || typeof og.eventManager.fireEvent !== 'function') return;
        var rows = Array.isArray((this.props.data || {}).rows)
            ? this.props.data.rows : [];
        rows.forEach(function(r) {
            (r.assoc_member_ids || []).forEach(function(id) {
                if (og.emptyBreadcrumbsToRefresh.indexOf(id) === -1) {
                    og.emptyBreadcrumbsToRefresh.push(id);
                }
            });
        });
        og.eventManager.fireEvent('replace all empty breadcrumb', null);
    }

    getColumnDef(key) {
        var availableColumns = (this.props.data && this.props.data.availableColumns) || [];
        return availableColumns.find(function(c) { return c.key === key; });
    }

    getQuickActionDef(key) {
        var catalog = (this.props.data && this.props.data.quickActionsCatalog) || [];
        return catalog.find(function(a) { return a.key === key; });
    }

    // ---- Unified config modal ----

    openConfigModal() {
        var data = this.props.data || {};
        var initialLimit = parseInt(data.limit, 10);
        if (!Number.isFinite(initialLimit) || initialLimit <= 0) initialLimit = 10;

        var orderableColumns = Array.isArray(data.orderableColumns) ? data.orderableColumns : [];
        var initialOrderBy = data.orderBy || 'name';
        var hasOrderBy = orderableColumns.some(function(c) { return c.key === initialOrderBy; });
        if (!hasOrderBy) {
            initialOrderBy = orderableColumns.length > 0 ? orderableColumns[0].key : 'name';
        }
        var initialOrderDir = String(data.orderDir).toUpperCase() === 'DESC' ? 'DESC' : 'ASC';

        var extraNumbers = {};
        (Array.isArray(data.extraNumberSettings) ? data.extraNumberSettings : []).forEach(function(s) {
            var v = parseInt(s.value, 10);
            if (!Number.isFinite(v) || v <= 0) v = s.min || 1;
            extraNumbers[s.key] = v;
        });

        this.setState({
            showConfigModal: true,
            draftLimit: initialLimit,
            draftOrderBy: initialOrderBy,
            draftOrderDir: initialOrderDir,
            draftExtraNumbers: extraNumbers,
            saveError: false
        });
    }

    closeConfigModal() {
        this.setState({
            showConfigModal: false,
            draftLimit: null,
            draftOrderBy: null,
            draftOrderDir: null,
            saveError: false
        });
    }

    handleLimitChange(e) {
        var raw = e.target.value;
        if (raw === '') { this.setState({ draftLimit: '' }); return; }
        var n = parseInt(raw, 10);
        if (!Number.isFinite(n)) return;
        var data = this.props.data || {};
        var min = data.minLimit || 1;
        var max = data.maxLimit || 50;
        if (n < min) n = min;
        if (n > max) n = max;
        this.setState({ draftLimit: n });
    }

    // Toggle a single row's detail (participant list) open/closed.
    toggleRowExpand(rowId) {
        this.setState(function(prev) {
            var next = {};
            Object.keys(prev.expandedRows).forEach(function(k) { next[k] = prev.expandedRows[k]; });
            if (next[rowId]) { delete next[rowId]; } else { next[rowId] = true; }
            return { expandedRows: next };
        });
    }

    // Clamp + store a draft value for a widget-declared extra numeric setting (e.g. participants per event).
    handleExtraNumberChange(key, raw, def) {
        var self = this;
        this.setState(function(prev) {
            var next = {};
            Object.keys(prev.draftExtraNumbers).forEach(function(k) { next[k] = prev.draftExtraNumbers[k]; });
            if (raw === '') { next[key] = ''; return { draftExtraNumbers: next }; }
            var n = parseInt(raw, 10);
            if (!Number.isFinite(n)) return null;
            var min = def && def.min ? def.min : 1;
            var max = def && def.max ? def.max : 100;
            if (n < min) n = min;
            if (n > max) n = max;
            next[key] = n;
            return { draftExtraNumbers: next };
        });
    }

    handleOrderByChange(e) {
        this.setState({ draftOrderBy: e.target.value });
    }

    handleOrderDirChange(e) {
        this.setState({ draftOrderDir: e.target.value === 'DESC' ? 'DESC' : 'ASC' });
    }

    applyConfig() {
        var self = this;
        var data = this.props.data || {};
        var limit = this.state.draftLimit;
        var min = data.minLimit || 1;
        var max = data.maxLimit || 50;

        if (!Number.isInteger(limit) || limit < min || limit > max) {
            self.setState({ saveError: true });
            return;
        }

        var columnDraft = this.colContentRef.current
            ? this.colContentRef.current.getDraft()
            : null;

        var options = [
            { name: data.configOptionLimitName, value: limit }
        ];
        // widget-declared extra numeric settings (e.g. participants per event), saved under their own key
        (Array.isArray(data.extraNumberSettings) ? data.extraNumberSettings : []).forEach(function(s) {
            var v = self.state.draftExtraNumbers[s.key];
            if (!Number.isInteger(v)) v = s.min || 1;
            options.push({ name: s.key, value: v });
        });
        if (columnDraft) {
            var orderedPayload = columnDraft.map(function(c) { return { key: c.key, visible: !!c.visible }; });
            options.push({ name: data.configOptionName, value: orderedPayload });
        }
        if (data.configOptionOrderByName && this.state.draftOrderBy) {
            options.push({ name: data.configOptionOrderByName, value: this.state.draftOrderBy });
        }
        if (data.configOptionOrderDirName) {
            options.push({ name: data.configOptionOrderDirName, value: this.state.draftOrderDir === 'DESC' ? 'DESC' : 'ASC' });
        }
        var quickActionsDraft = this.quickActionsContentRef.current
            ? this.quickActionsContentRef.current.getDraft()
            : null;
        if (quickActionsDraft && data.configOptionQuickActionsName) {
            var quickActionsPayload = quickActionsDraft.map(function(a) { return { key: a.key, visible: !!a.visible }; });
            options.push({ name: data.configOptionQuickActionsName, value: quickActionsPayload });
        }

        self.setState({ saving: true, saveError: false });

        var url = data.saveConfigOptionsUrl || data.saveConfigUrl;
        var post = data.saveConfigOptionsUrl
            ? { widget_name: data.widgetName, options: JSON.stringify(options) }
            : { widget_name: data.widgetName, option_name: options[0].name, option_value: options[0].value };

        og.openLink(url, {
            post: post,
            preventPanelLoad: true,
            silent: true,
            postProcess: function(ok) {
                if (!ok) { self.setState({ saving: false, saveError: true }); return; }
                self.setState({ showConfigModal: false, saving: false }, function() {
                    self._reloadWidget();
                });
            }
        });
    }

    // ---- Header click sorting ----

    // Returns the map of orderable column keys (those listing() can sort by).
    _orderableKeys() {
        var data = this.props.data || {};
        var keys = {};
        (Array.isArray(data.orderableColumns) ? data.orderableColumns : []).forEach(function(c) {
            keys[c.key] = true;
        });
        return keys;
    }

    // Click on a sortable header: toggle direction if already active, else sort ASC.
    handleHeaderSort(key) {
        if (this.state.saving) return;
        if (!this._orderableKeys()[key]) return;
        var data = this.props.data || {};
        var currentBy = data.orderBy || 'name';
        var currentDir = String(data.orderDir).toUpperCase() === 'DESC' ? 'DESC' : 'ASC';
        var newDir = (currentBy === key) ? (currentDir === 'ASC' ? 'DESC' : 'ASC') : 'ASC';
        this._persistOrder(key, newDir);
    }

    // Persist just the order options, then reload the widget to re-query the list.
    _persistOrder(orderBy, orderDir) {
        var self = this;
        var data = this.props.data || {};
        if (!data.saveConfigOptionsUrl || !data.configOptionOrderByName) return;
        var options = [
            { name: data.configOptionOrderByName,  value: orderBy },
            { name: data.configOptionOrderDirName, value: orderDir }
        ];
        self.setState({ saving: true });
        og.openLink(data.saveConfigOptionsUrl, {
            post: { widget_name: data.widgetName, options: JSON.stringify(options) },
            preventPanelLoad: true,
            silent: true,
            postProcess: function(ok) {
                if (!ok) { self.setState({ saving: false }); return; }
                self._reloadWidget();
            }
        });
    }

    _reloadWidget() {
        var self = this;
        var data = this.props.data || {};
        var widgetEl = document.getElementById(data.widgetWrapperId);
        if (widgetEl && data.reloadWidgetUrl && window.og && og.openLink) {
            og.openLink(data.reloadWidgetUrl, {
                preventPanelLoad: true,
                silent: true,
                postProcess: function(ok2, html) {
                    if (!html) { window.location.reload(); return; }
                    var scripts = [];
                    var cleanHtml = html.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(_, sc) {
                        if (sc.trim()) scripts.push(sc);
                        return '';
                    });
                    // Unmount the React root BEFORE the DOM swap below destroys its container —
                    // jQuery's replaceWith()/outerHTML is a raw DOM mutation React 16 never sees,
                    // so componentWillUnmount would otherwise never fire, leaking this component's
                    // keydown listener (and creating an orphaned instance) on every reload. The
                    // root is the INNER mount node (evx-widget-<genid>), not widgetEl itself, which
                    // is the outer wrapper passed as widgetWrapperId — unmountComponentAtNode is a
                    // no-op on any node that isn't the exact one ReactDOM.render() was called with.
                    if (data.genid) {
                        var mountEl = document.getElementById('evx-widget-' + data.genid);
                        if (mountEl) ReactDOM.unmountComponentAtNode(mountEl);
                    }
                    if (window.jQuery) {
                        jQuery(widgetEl).replaceWith(cleanHtml);
                    } else {
                        widgetEl.outerHTML = cleanHtml;
                    }
                    setTimeout(function() {
                        scripts.forEach(function(src) {
                            var s = document.createElement('script');
                            s.textContent = src;
                            document.head.appendChild(s);
                            document.head.removeChild(s);
                        });
                    }, 0);
                }
            });
        } else {
            window.location.reload();
        }
    }

    // ---- Rendering ----

    // expandIndicator: pass true/false to prepend an expand chevron (open/closed) to this cell — used
    // on the first cell of an expandable row. Pass null/undefined for every other cell (unchanged markup).
    renderCell(row, columnDef, expandIndicator) {
        var values = (row && row.values) || {};
        var raw = values[columnDef.key];
        var className = 'evx-widget-cell evx-widget-cell-' + columnDef.key;
        if (columnDef.is_multiline) className += ' evx-widget-cell-multiline';
        if (columnDef.numeric) className += ' evx-widget-cell-numeric';

        var chevron = null;
        if (expandIndicator === true || expandIndicator === false) {
            className += ' evx-widget-cell-expander';
            chevron = React.createElement('span', {
                className: 'evx-widget-row-expander' + (expandIndicator ? ' is-open' : ''),
                'aria-hidden': 'true'
            }, expandIndicator ? '▾' : '▸');
        }

        if (columnDef.is_action) {
            return <td key={columnDef.key} className={className} dangerouslySetInnerHTML={{ __html: String(raw || '') }} />;
        }

        if (isEmptyValue(raw)) {
            return <td key={columnDef.key} className={className}>{chevron}{chevron ? <span className="evx-widget-cell-text">--</span> : '--'}</td>;
        }

        if (columnDef.is_html) {
            if (chevron) {
                return <td key={columnDef.key} className={className}>{chevron}<span dangerouslySetInnerHTML={{ __html: String(raw) }} /></td>;
            }
            return <td key={columnDef.key} className={className} dangerouslySetInnerHTML={{ __html: String(raw) }} />;
        }

        var text = String(raw);
        return (
            <td key={columnDef.key} className={className}>
                {chevron}
                <div className="evx-widget-cell-text" title={text}>{text}</div>
            </td>
        );
    }

    renderTable() {
        var self = this;
        var data = this.props.data || {};
        var labels = data.labels || {};
        var rows = Array.isArray(data.rows) ? data.rows : [];
        var visibleColumns = this.state.columns
            .filter(function(c) { return c.visible; })
            .map(function(c) { return self.getColumnDef(c.key); })
            .filter(function(def) { return !!def; });

        if (visibleColumns.length === 0) {
            return <div className="evx-widget-empty">{labels.noItems || ''}</div>;
        }

        var orderableKeys = this._orderableKeys();
        var orderableColumns = Array.isArray(data.orderableColumns) ? data.orderableColumns : [];
        var activeOrderBy = data.orderBy || 'name';
        var activeOrderDir = String(data.orderDir).toUpperCase() === 'DESC' ? 'DESC' : 'ASC';
        var arrowGlyph = activeOrderDir === 'DESC' ? '▼' : '▲';

        return (
            <div className="evx-widget-table-container">
                {this.renderSortCaption(orderableColumns, activeOrderBy, activeOrderDir)}
                <div className="evx-widget-table-wrapper">
                <table className="evx-widget-table">
                    <thead>
                        <tr>
                            {visibleColumns.map(function(col) {
                                var isOrderable = !!orderableKeys[col.key];
                                var isActive = isOrderable && col.key === activeOrderBy;
                                var thClass = 'evx-widget-header evx-widget-header-' + col.key
                                    + (col.numeric ? ' evx-widget-header-numeric' : '')
                                    + (isOrderable ? ' evx-widget-header-sortable' : '')
                                    + (isActive ? ' evx-widget-header-sorted' : '');
                                var onClick = isOrderable
                                    ? function() { self.handleHeaderSort(col.key); }
                                    : undefined;
                                var title = isOrderable ? (labels.sortBy || 'Sort by') + ': ' + col.label : undefined;
                                return (
                                    <th key={col.key} className={thClass} onClick={onClick} title={title}>
                                        {col.label}
                                        {isActive
                                            ? <span className="evx-widget-sort-arrow">{arrowGlyph}</span>
                                            : null}
                                    </th>
                                );
                            })}
                        </tr>
                    </thead>
                    <tbody ref={this.tbodyRef}>
                        {rows.map(function(row) {
                            var hasDetail = row.detail_html != null && String(row.detail_html) !== '';
                            var isExpanded = hasDetail && !!self.state.expandedRows[row.id];
                            var rowClass = 'evx-widget-row'
                                + (hasDetail ? ' evx-widget-row-expandable' : '')
                                + (isExpanded ? ' is-expanded' : '');
                            var mainRow = (
                                <tr key={row.id} className={rowClass}
                                    onClick={hasDetail ? function(e) {
                                        var t = e.target;
                                        // let interactive elements inside the row (links, buttons, menus) act normally
                                        if (t && t.closest && t.closest('a, button, input, select, textarea, .evx-task-kebab')) return;
                                        self.toggleRowExpand(row.id);
                                    } : undefined}>
                                    {visibleColumns.map(function(col, ci) {
                                        return self.renderCell(row, col, (hasDetail && ci === 0) ? isExpanded : null);
                                    })}
                                </tr>
                            );
                            if (!isExpanded) return mainRow;
                            return [
                                mainRow,
                                <tr key={row.id + '__detail'} className="evx-widget-detail-row">
                                    <td className="evx-widget-detail-cell" colSpan={visibleColumns.length}
                                        dangerouslySetInnerHTML={{ __html: String(row.detail_html) }} />
                                </tr>
                            ];
                        })}
                    </tbody>
                </table>
                </div>
            </div>
        );
    }

    // Always-visible line stating the active sort, since the sorted column
    // may be scrolled out of view (or not visible at all).
    renderSortCaption(orderableColumns, activeOrderBy, activeOrderDir) {
        var labels = (this.props.data || {}).labels || {};
        var match = orderableColumns.find(function(c) { return c.key === activeOrderBy; });
        if (!match) {
            // Saved criterion no longer available: backend falls back to name order.
            match = orderableColumns.find(function(c) { return c.key === 'name'; });
        }
        if (!match) return null;
        var dirLabel = activeOrderDir === 'DESC'
            ? (labels.descending || 'Descending')
            : (labels.ascending || 'Ascending');
        return (
            <div className="evx-widget-sort-caption">
                {(labels.sortedBy || 'Sorted by') + ' '}
                <span className="evx-widget-sort-caption-col">{match.label}</span>
                {' (' + dirLabel.toLowerCase() + ')'}
            </div>
        );
    }

    renderConfigModal() {
        var self = this;
        var data = this.props.data || {};
        var labels = data.labels || {};

        var nonActionColumns = this.state.columns.filter(function(c) {
            var def = self.getColumnDef(c.key);
            return !def || !def.is_action;
        });

        var orderableColumns = Array.isArray(data.orderableColumns) ? data.orderableColumns : [];

        return (
            <div className="fo-widget-modal-overlay">
                <div className="fo-widget-modal fo-widget-modal-config">
                    <div className="fo-widget-modal-header">
                        <h3 className="fo-widget-modal-title">
                            {labels.widgetSettings || 'Widget settings'}
                        </h3>
                        <button type="button" className="fo-widget-modal-close" onClick={this.closeConfigModal}>&times;</button>
                    </div>

                    <div className="fo-widget-modal-body fo-widget-modal-config-body">
                        <div className="fo-widget-config-section">
                            <div className="fo-widget-config-section-heading">
                                {labels.generalSettings || 'General'}
                            </div>
                            <div className="fo-widget-modal-option-section limit">
                                <label className="fo-widget-modal-option-label">
                                    <span className="fo-widget-modal-option-label-text">{labels.displayLines || 'Display'}</span>
                                    <input
                                        type="number"
                                        className="fo-widget-modal-limit-input"
                                        min={data.minLimit || 1}
                                        max={data.maxLimit || 50}
                                        value={this.state.draftLimit === null ? '' : this.state.draftLimit}
                                        onChange={this.handleLimitChange}
                                        aria-label={labels.displayLines || 'Display'} />
                                    <span>{labels.displayItemsUnit || 'items'}</span>
                                </label>
                            </div>
                            {(Array.isArray(data.extraNumberSettings) ? data.extraNumberSettings : []).map(function(s) {
                                return (
                                    <div className="fo-widget-modal-option-section limit" key={s.key}>
                                        <label className="fo-widget-modal-option-label">
                                            <span className="fo-widget-modal-option-label-text">{s.label}</span>
                                            <input
                                                type="number"
                                                className="fo-widget-modal-limit-input"
                                                min={s.min || 1}
                                                max={s.max || 100}
                                                value={self.state.draftExtraNumbers[s.key] === undefined ? '' : self.state.draftExtraNumbers[s.key]}
                                                onChange={function(e) { self.handleExtraNumberChange(s.key, e.target.value, s); }}
                                                aria-label={s.label} />
                                            {s.unit ? <span>{s.unit}</span> : null}
                                        </label>
                                    </div>
                                );
                            })}
                            {orderableColumns.length > 0 ? (
                                <div className="fo-widget-modal-option-section order">
                                    <label className="fo-widget-modal-option-label">
                                        <span className="fo-widget-modal-option-label-text">{labels.sortBy || 'Sort by'}</span>
                                        <select
                                            className="fo-widget-modal-order-select"
                                            value={this.state.draftOrderBy || 'name'}
                                            onChange={this.handleOrderByChange}
                                            aria-label={labels.sortBy || 'Sort by'}>
                                            {orderableColumns.map(function(col) {
                                                return <option key={col.key} value={col.key}>{col.label}</option>;
                                            })}
                                        </select>
                                        <select
                                            className="fo-widget-modal-order-dir-select"
                                            value={this.state.draftOrderDir || 'ASC'}
                                            onChange={this.handleOrderDirChange}
                                            aria-label={labels.sortDirection || 'Sort direction'}>
                                            <option value="ASC">{labels.ascending || 'Ascending'}</option>
                                            <option value="DESC">{labels.descending || 'Descending'}</option>
                                        </select>
                                    </label>
                                </div>
                            ) : null}
                        </div>

                        <div className="fo-widget-config-section fo-widget-config-section-columns">
                            <div className="fo-widget-config-section-heading">
                                {labels.columns || 'Columns'}
                            </div>
                            <ColumnManagerContent
                                ref={this.colContentRef}
                                columns={nonActionColumns}
                                getColumnDef={this.getColumnDef.bind(this)}
                                labels={{
                                    available:         labels.available         || 'Available',
                                    visible:           labels.visible           || 'Visible',
                                    selectAll:         labels.selectAll         || 'Select all',
                                    removeAll:         labels.removeAll         || 'Remove all',
                                    dragToReorder:     labels.dragToReorder     || 'Drag to reorder',
                                    search:            labels.searchColumns     || 'Search...',
                                    noColumnsMatch:    labels.noColumnsMatch    || 'No columns match',
                                    selectColumnsHint: labels.selectColumnsHint || 'Select columns from the left to add them'
                                }} />
                        </div>

                        {Array.isArray(data.quickActionsCatalog) && data.quickActionsCatalog.length > 0 ? (
                            <div className="fo-widget-config-section fo-widget-config-section-quickactions">
                                <div className="fo-widget-config-section-heading">
                                    {labels.quickActions || 'Quick actions'}
                                    {labels.quickActionsHint
                                        ? <span className="fo-widget-config-section-hint"> — {labels.quickActionsHint}</span>
                                        : null}
                                </div>
                                <QuickActionsSortList
                                    ref={this.quickActionsContentRef}
                                    actions={data.quickActionsOrder}
                                    getActionDef={this.getQuickActionDef.bind(this)} />
                            </div>
                        ) : null}
                    </div>

                    {this.state.saveError
                        ? <div className="fo-widget-modal-error">{labels.saveError || 'Could not save changes. Please try again.'}</div>
                        : null}

                    <div className="fo-widget-modal-footer">
                        <button type="button"
                            className="fo-widget-btn fo-widget-btn-secondary"
                            onClick={this.closeConfigModal}
                            disabled={this.state.saving}>{labels.cancel || 'Cancel'}</button>
                        <button type="button"
                            className="fo-widget-btn fo-widget-btn-primary"
                            onClick={this.applyConfig}
                            disabled={this.state.saving}>{labels.apply || 'Apply'}</button>
                    </div>
                </div>
            </div>
        );
    }

    render() {
        var data = this.props.data || {};
        var labels = data.labels || {};
        var rows = Array.isArray(data.rows) ? data.rows : [];
        var hasRows = rows.length > 0;

        return (
            <div className="evx-widget-react">
                {hasRows
                    ? this.renderTable()
                    : <div className="evx-widget-empty">{labels.noItems || ''}</div>}
                {this.state.showConfigModal ? this.renderConfigModal() : null}
            </div>
        );
    }
}

function showDimensionTableWidget(data, element) {
    ReactDOM.render(<DimensionTableWidget data={data} />, element);
}

module.exports = showDimensionTableWidget;
