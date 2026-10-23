var React = require('react');

/**
 * Two-panel column manager body — embeddable in a parent modal.
 *
 * Props:
 *   columns      {Array}    Full ordered array of {key, visible} — action cols excluded by caller
 *   getColumnDef {Function} (key) => {key, label, ...} column definition
 *   labels       {Object}   Optional label overrides (available, visible, selectAll, removeAll,
 *                           dragToReorder, search, noColumnsMatch, selectColumnsHint)
 */
class ColumnManagerContent extends React.Component {
    constructor(props) {
        super(props);

        var initial = (props.columns || []).map(function(c) {
            return { key: c.key, visible: !!c.visible };
        });

        this.state = {
            draft: initial,
            searchQuery: '',
            draggedIndex: null,
            dragOverIndex: null
        };

        this.handleSearchChange = this.handleSearchChange.bind(this);
        this.toggleColumn = this.toggleColumn.bind(this);
        this.selectAll = this.selectAll.bind(this);
        this.removeAll = this.removeAll.bind(this);
        this.removeVisible = this.removeVisible.bind(this);
        this.handleDragStart = this.handleDragStart.bind(this);
        this.handleDragOver = this.handleDragOver.bind(this);
        this.handleDragLeave = this.handleDragLeave.bind(this);
        this.handleDrop = this.handleDrop.bind(this);
        this.handleDragEnd = this.handleDragEnd.bind(this);
    }

    getDraft() {
        return this.state.draft;
    }

    handleSearchChange(e) {
        this.setState({ searchQuery: e.target.value });
    }

    toggleColumn(key) {
        this.setState(function(prev) {
            return {
                draft: prev.draft.map(function(c) {
                    return c.key === key ? { key: c.key, visible: !c.visible } : c;
                })
            };
        });
    }

    selectAll() {
        this.setState(function(prev) {
            return {
                draft: prev.draft.map(function(c) { return { key: c.key, visible: true }; })
            };
        });
    }

    removeAll() {
        this.setState(function(prev) {
            return {
                draft: prev.draft.map(function(c) { return { key: c.key, visible: false }; })
            };
        });
    }

    removeVisible(key) {
        this.setState(function(prev) {
            return {
                draft: prev.draft.map(function(c) {
                    return c.key === key ? { key: c.key, visible: false } : c;
                })
            };
        });
    }

    handleDragStart(visibleIndex, e) {
        this.setState({ draggedIndex: visibleIndex });
        if (e.dataTransfer) {
            e.dataTransfer.effectAllowed = 'move';
            try { e.dataTransfer.setData('text/plain', String(visibleIndex)); } catch (err) {}
        }
    }

    handleDragOver(visibleIndex, e) {
        e.preventDefault();
        if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
        if (this.state.dragOverIndex !== visibleIndex) {
            this.setState({ dragOverIndex: visibleIndex });
        }
    }

    handleDragLeave(visibleIndex) {
        if (this.state.dragOverIndex === visibleIndex) {
            this.setState({ dragOverIndex: null });
        }
    }

    handleDrop(visibleIndex, e) {
        e.preventDefault();
        var fromVI = this.state.draggedIndex;
        if (fromVI === null || fromVI === visibleIndex) {
            this.setState({ draggedIndex: null, dragOverIndex: null });
            return;
        }

        var draft = this.state.draft.slice();
        var visibleKeys = draft.filter(function(c) { return c.visible; }).map(function(c) { return c.key; });
        var movedKey = visibleKeys[fromVI];
        visibleKeys.splice(fromVI, 1);
        visibleKeys.splice(visibleIndex, 0, movedKey);

        var invisibleCols = draft.filter(function(c) { return !c.visible; });
        var newDraft = visibleKeys.map(function(k) { return { key: k, visible: true }; })
            .concat(invisibleCols);

        this.setState({ draft: newDraft, draggedIndex: null, dragOverIndex: null });
    }

    handleDragEnd() {
        this.setState({ draggedIndex: null, dragOverIndex: null });
    }

    render() {
        var self = this;
        var labels = this.props.labels || {};
        var getColumnDef = this.props.getColumnDef;
        var draft = this.state.draft;
        var query = (this.state.searchQuery || '').trim().toLowerCase();
        var hasFilter = query !== '';

        var availableCount = draft.length;
        var visibleCols = draft.filter(function(c) { return c.visible; });
        var visibleCount = visibleCols.length;

        var availableEntries = draft.filter(function(c) {
            if (!hasFilter) return true;
            var def = getColumnDef(c.key);
            return def && String(def.label || '').toLowerCase().indexOf(query) !== -1;
        }).slice().sort(function(a, b) {
            var la = String((getColumnDef(a.key) || {}).label || '').toLowerCase();
            var lb = String((getColumnDef(b.key) || {}).label || '').toLowerCase();
            return la < lb ? -1 : la > lb ? 1 : 0;
        });

        return (
            <div className="col-manager-body">
                <div className="col-manager-panel col-manager-panel-available">
                    <div className="col-manager-panel-header">
                        <span className="col-manager-panel-title">
                            {labels.available || 'Available'} <span className="col-manager-count">({availableCount})</span>
                        </span>
                        <span className="col-manager-panel-actions">
                            <a href="#" className="col-manager-action" onClick={function(e) { e.preventDefault(); self.selectAll(); }}>
                                {labels.selectAll || 'Select all'}
                            </a>
                            {' · '}
                            <a href="#" className="col-manager-action" onClick={function(e) { e.preventDefault(); self.removeAll(); }}>
                                {labels.removeAll || 'Remove all'}
                            </a>
                        </span>
                    </div>
                    <input
                        type="text"
                        className="col-manager-search"
                        placeholder={labels.search || 'Search...'}
                        value={this.state.searchQuery}
                        onChange={this.handleSearchChange} />
                    <ul className="col-manager-available-list">
                        {availableEntries.length === 0
                            ? <li className="col-manager-no-match">{labels.noColumnsMatch || 'No columns match'}</li>
                            : availableEntries.map(function(c) {
                                var def = getColumnDef(c.key);
                                if (!def) return null;
                                return (
                                    <li key={c.key}
                                        className={'col-manager-available-item' + (c.visible ? ' selected' : '')}
                                        onClick={function() { self.toggleColumn(c.key); }}>
                                        <span className={'col-manager-checkbox' + (c.visible ? ' checked' : '')}></span>
                                        <span className="col-manager-item-label">{def.label}</span>
                                    </li>
                                );
                            })}
                    </ul>
                </div>

                <div className="col-manager-panel col-manager-panel-visible">
                    <div className="col-manager-panel-header">
                        <span className="col-manager-panel-title">
                            {labels.visible || 'Visible'} <span className="col-manager-count">({visibleCount})</span>
                        </span>
                        <span className="col-manager-drag-hint">{labels.dragToReorder || 'Drag to reorder'}</span>
                    </div>
                    <ul className="col-manager-visible-list">
                        {visibleCols.map(function(c, vi) {
                            var def = getColumnDef(c.key);
                            if (!def) return null;
                            var isDragging = self.state.draggedIndex === vi;
                            var isDragOver = self.state.dragOverIndex === vi && self.state.draggedIndex !== null && self.state.draggedIndex !== vi;
                            return (
                                <li key={c.key}
                                    className={'col-manager-visible-item'
                                        + (isDragging ? ' dragging' : '')
                                        + (isDragOver ? ' drag-over' : '')}
                                    draggable
                                    onDragStart={function(e) { self.handleDragStart(vi, e); }}
                                    onDragOver={function(e) { self.handleDragOver(vi, e); }}
                                    onDragLeave={function() { self.handleDragLeave(vi); }}
                                    onDrop={function(e) { self.handleDrop(vi, e); }}
                                    onDragEnd={self.handleDragEnd}>
                                    <span className="col-manager-visible-handle">&#9776;</span>
                                    <span className="col-manager-visible-num">{vi + 1}</span>
                                    <span className="col-manager-visible-label">{def.label}</span>
                                    <button type="button"
                                        className="col-manager-visible-remove"
                                        onClick={function() { self.removeVisible(c.key); }}>&times;</button>
                                </li>
                            );
                        })}
                    </ul>
                    <div className="col-manager-visible-hint">
                        {labels.selectColumnsHint || 'Select columns from the left to add them'}
                    </div>
                </div>
            </div>
        );
    }
}

/**
 * Reusable two-panel column manager modal (standalone).
 *
 * Props:
 *   title        {string}   Modal header title
 *   columns      {Array}    Full ordered array of {key, visible} — action cols excluded by caller
 *   getColumnDef {Function} (key) => {key, label, ...} column definition
 *   onClose      {Function} Called when the modal should close without saving
 *   onApply      {Function} (newColumns) => void — called with updated {key, visible}[] on Apply
 *   labels       {Object}   Optional label overrides
 */
class ColumnManagerModal extends React.Component {
    constructor(props) {
        super(props);
        this.contentRef = React.createRef();
        this.handleApply = this.handleApply.bind(this);
    }

    handleApply() {
        if (!this.contentRef.current) return;
        this.props.onApply(this.contentRef.current.getDraft());
    }

    render() {
        var labels = this.props.labels || {};

        return (
            <div className="col-manager-overlay">
                <div className="col-manager-modal">
                    <div className="col-manager-header">
                        <h3 className="col-manager-title">{this.props.title || 'Manage Columns'}</h3>
                        <button type="button" className="col-manager-close" onClick={this.props.onClose}>&times;</button>
                    </div>

                    <ColumnManagerContent
                        ref={this.contentRef}
                        columns={this.props.columns}
                        getColumnDef={this.props.getColumnDef}
                        labels={labels} />

                    <div className="col-manager-footer">
                        <button type="button" className="fo-widget-btn fo-widget-btn-secondary" onClick={this.props.onClose}>
                            {labels.cancel || 'Cancel'}
                        </button>
                        <button type="button" className="fo-widget-btn fo-widget-btn-primary" onClick={this.handleApply}>
                            {labels.apply || 'Apply'}
                        </button>
                    </div>
                </div>
            </div>
        );
    }
}

module.exports = ColumnManagerModal;
module.exports.ColumnManagerContent = ColumnManagerContent;
