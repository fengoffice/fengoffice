var React = require('react');

/**
 * Drag-to-reorder checklist for a table widget's row-hover "quick actions" menu
 * (e.g. Tasks' kebab: Add subtask, Edit, Mark as started, ...). One flat list —
 * unlike the column manager there's no separate available/visible split, since
 * every action always has a fixed slot; the checkbox just controls whether it
 * shows up in the row menu.
 *
 * Props:
 *   actions      {Array}    Ordered [{key, visible}], one entry per catalog action
 *   getActionDef {Function} (key) => {key, label, icon} action definition
 */
class QuickActionsSortList extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            draft: (props.actions || []).map(function(a) { return { key: a.key, visible: !!a.visible }; }),
            draggedIndex: null,
            dragOverIndex: null
        };

        this.toggle = this.toggle.bind(this);
        this.handleDragStart = this.handleDragStart.bind(this);
        this.handleDragOver = this.handleDragOver.bind(this);
        this.handleDragLeave = this.handleDragLeave.bind(this);
        this.handleDrop = this.handleDrop.bind(this);
        this.handleDragEnd = this.handleDragEnd.bind(this);
    }

    getDraft() {
        return this.state.draft;
    }

    toggle(key) {
        this.setState(function(prev) {
            return {
                draft: prev.draft.map(function(a) {
                    return a.key === key ? { key: a.key, visible: !a.visible } : a;
                })
            };
        });
    }

    handleDragStart(index, e) {
        this.setState({ draggedIndex: index });
        if (e.dataTransfer) {
            e.dataTransfer.effectAllowed = 'move';
            try { e.dataTransfer.setData('text/plain', String(index)); } catch (err) {}
        }
    }

    handleDragOver(index, e) {
        e.preventDefault();
        if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
        if (this.state.dragOverIndex !== index) {
            this.setState({ dragOverIndex: index });
        }
    }

    handleDragLeave(index) {
        if (this.state.dragOverIndex === index) {
            this.setState({ dragOverIndex: null });
        }
    }

    handleDrop(index, e) {
        e.preventDefault();
        var from = this.state.draggedIndex;
        if (from === null || from === index) {
            this.setState({ draggedIndex: null, dragOverIndex: null });
            return;
        }
        var draft = this.state.draft.slice();
        var moved = draft[from];
        draft.splice(from, 1);
        draft.splice(index, 0, moved);
        this.setState({ draft: draft, draggedIndex: null, dragOverIndex: null });
    }

    handleDragEnd() {
        this.setState({ draggedIndex: null, dragOverIndex: null });
    }

    render() {
        var self = this;
        var getActionDef = this.props.getActionDef;

        return (
            <ul className="fo-widget-sortlist">
                {this.state.draft.map(function(a, i) {
                    var def = getActionDef(a.key);
                    if (!def) return null;
                    var isDragging = self.state.draggedIndex === i;
                    var isDragOver = self.state.dragOverIndex === i && self.state.draggedIndex !== null && self.state.draggedIndex !== i;
                    return (
                        <li key={a.key}
                            className={'fo-widget-sortlist-item'
                                + (isDragging ? ' dragging' : '')
                                + (isDragOver ? ' drag-over' : '')}
                            draggable
                            onDragStart={function(e) { self.handleDragStart(i, e); }}
                            onDragOver={function(e) { self.handleDragOver(i, e); }}
                            onDragLeave={function() { self.handleDragLeave(i); }}
                            onDrop={function(e) { self.handleDrop(i, e); }}
                            onDragEnd={self.handleDragEnd}>
                            <span className="fo-widget-sortlist-handle">&#9776;</span>
                            <input type="checkbox" checked={a.visible} onChange={function() { self.toggle(a.key); }} />
                            <span className="fo-widget-sortlist-ico"><i className={def.icon}></i></span>
                            <span>{def.label}</span>
                        </li>
                    );
                })}
            </ul>
        );
    }
}

module.exports = QuickActionsSortList;
