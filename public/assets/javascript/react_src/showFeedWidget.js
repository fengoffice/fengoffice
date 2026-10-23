var React = require('react');
var ReactDOM = require('react-dom');

// Generic list/feed widget for any "feed widget" (a dashboard widget backed by
// application/helpers/dimension_widget.php's evx_widgets_build_feed_widget_data()):
// Activity and Emails today. Unlike the table widget, there's no column concept —
// each row arrives as already-rendered HTML from the PHP data provider. The only
// client-side state is the settings modal (display limit + whatever extra filters
// the widget declares: a checkbox or a multi-select checklist).
class FeedWidget extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            showConfigModal: false,
            draftLimit: null,
            draftFilterValues: {},
            saving: false,
            saveError: false
        };

        this.openConfigModal = this.openConfigModal.bind(this);
        this.closeConfigModal = this.closeConfigModal.bind(this);
        this.handleConfigModalKeyDown = this.handleConfigModalKeyDown.bind(this);
        this.applyConfig = this.applyConfig.bind(this);
        this.handleLimitChange = this.handleLimitChange.bind(this);
    }

    componentDidMount() {
        var data = this.props.data || {};
        if (data.genid) {
            window['evxWidgetOpenConfigure_' + data.genid] = this.openConfigModal;
        }
        document.addEventListener('keydown', this.handleConfigModalKeyDown);
    }

    handleConfigModalKeyDown(e) {
        if (e.key === 'Escape' && this.state.showConfigModal) {
            this.closeConfigModal();
        }
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

    // ---- Unified config modal ----

    openConfigModal() {
        var data = this.props.data || {};
        var initialLimit = parseInt(data.limit, 10);
        if (!Number.isFinite(initialLimit) || initialLimit <= 0) initialLimit = 10;

        var extraFilters = Array.isArray(data.extraFilters) ? data.extraFilters : [];
        var filterValues = data.filterValues || {};
        var draftFilterValues = {};
        extraFilters.forEach(function(f) {
            if (f.type === 'checklist') {
                draftFilterValues[f.key] = Array.isArray(filterValues[f.key]) ? filterValues[f.key].slice() : [];
            } else if (f.type === 'select') {
                draftFilterValues[f.key] = filterValues[f.key] || '';
            } else {
                draftFilterValues[f.key] = String(filterValues[f.key]) === '1';
            }
        });

        this.setState({
            showConfigModal: true,
            draftLimit: initialLimit,
            draftFilterValues: draftFilterValues,
            saveError: false
        });
    }

    closeConfigModal() {
        this.setState({
            showConfigModal: false,
            draftLimit: null,
            draftFilterValues: {},
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

    toggleCheckbox(key) {
        var draft = this.state.draftFilterValues;
        var next = Object.assign({}, draft, { [key]: !draft[key] });
        this.setState({ draftFilterValues: next });
    }

    toggleChecklistOption(key, value) {
        var draft = this.state.draftFilterValues;
        var current = Array.isArray(draft[key]) ? draft[key] : [];
        var idx = current.indexOf(value);
        var next = current.slice();
        if (idx >= 0) { next.splice(idx, 1); } else { next.push(value); }
        this.setState({ draftFilterValues: Object.assign({}, draft, { [key]: next }) });
    }

    handleSelectFilterChange(key, value) {
        var draft = this.state.draftFilterValues;
        this.setState({ draftFilterValues: Object.assign({}, draft, { [key]: value }) });
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

        var extraFilters = Array.isArray(data.extraFilters) ? data.extraFilters : [];
        var options = [
            { name: data.configOptionLimitName, value: limit }
        ];
        extraFilters.forEach(function(f) {
            var val = self.state.draftFilterValues[f.key];
            var optionValue;
            if (f.type === 'checklist') optionValue = val || [];
            else if (f.type === 'select') optionValue = val || '';
            else optionValue = val ? '1' : '0';
            options.push({ name: f.key, value: optionValue });
        });

        self.setState({ saving: true, saveError: false });

        og.openLink(data.saveConfigOptionsUrl, {
            post: { widget_name: data.widgetName, options: JSON.stringify(options) },
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

    _reloadWidget() {
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

    renderItems() {
        var data = this.props.data || {};
        var items = Array.isArray(data.itemsHtml) ? data.itemsHtml : [];
        var labels = data.labels || {};

        if (items.length === 0) {
            return <div className="evx-widget-empty">{data.noItemsText || labels.noItems || ''}</div>;
        }

        return (
            <ul className="evx-widget-feed-list-inner">
                {items.map(function(html, i) {
                    return <li key={i} className="evx-widget-feed-item" dangerouslySetInnerHTML={{ __html: html }} />;
                })}
            </ul>
        );
    }

    renderConfigModal() {
        var self = this;
        var data = this.props.data || {};
        var labels = data.labels || {};
        var extraFilters = Array.isArray(data.extraFilters) ? data.extraFilters : [];

        return (
            <div className="fo-widget-modal-overlay">
                <div className="fo-widget-modal">
                    <div className="fo-widget-modal-header">
                        <h3 className="fo-widget-modal-title">
                            {labels.widgetSettings || 'Widget settings'}
                        </h3>
                        <button type="button" className="fo-widget-modal-close" onClick={this.closeConfigModal}>&times;</button>
                    </div>

                    <div className="fo-widget-modal-body">
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

                            {extraFilters.map(function(f) {
                                if (f.type === 'checklist') {
                                    var options = Array.isArray(f.options) ? f.options : [];
                                    var selected = self.state.draftFilterValues[f.key] || [];
                                    return (
                                        <div className="fo-widget-field" key={f.key} style={{ flexDirection: 'column', alignItems: 'stretch' }}>
                                            <span className="fo-widget-field-label" style={{ marginBottom: '6px' }}>{f.label}</span>
                                            <ul className="fo-widget-checklist">
                                                {options.map(function(opt) {
                                                    return (
                                                        <li key={opt.value}>
                                                            <label className="fo-widget-checklist-item">
                                                                <input
                                                                    type="checkbox"
                                                                    checked={selected.indexOf(opt.value) >= 0}
                                                                    onChange={function() { self.toggleChecklistOption(f.key, opt.value); }} />
                                                                {opt.label}
                                                            </label>
                                                        </li>
                                                    );
                                                })}
                                            </ul>
                                        </div>
                                    );
                                }
                                if (f.type === 'select') {
                                    var selectOptions = Array.isArray(f.options) ? f.options : [];
                                    return (
                                        <div className="fo-widget-field" key={f.key}>
                                            <span className="fo-widget-field-label">{f.label}</span>
                                            <select
                                                className="fo-widget-select"
                                                value={self.state.draftFilterValues[f.key] || ''}
                                                onChange={function(e) { self.handleSelectFilterChange(f.key, e.target.value); }}>
                                                {selectOptions.map(function(opt) {
                                                    return <option key={opt.value} value={opt.value}>{opt.label}</option>;
                                                })}
                                            </select>
                                        </div>
                                    );
                                }
                                // checkbox
                                return (
                                    <label className="fo-widget-field fo-widget-field-check" key={f.key}>
                                        <input
                                            type="checkbox"
                                            checked={!!self.state.draftFilterValues[f.key]}
                                            onChange={function() { self.toggleCheckbox(f.key); }} />
                                        <span>{f.label}</span>
                                    </label>
                                );
                            })}
                        </div>
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
        return (
            <div className="evx-widget-feed-react">
                {this.renderItems()}
                {this.state.showConfigModal ? this.renderConfigModal() : null}
            </div>
        );
    }
}

function showFeedWidget(data, element) {
    ReactDOM.render(<FeedWidget data={data} />, element);
}

module.exports = showFeedWidget;
