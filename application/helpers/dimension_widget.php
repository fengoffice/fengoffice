<?php

/**
 * Generic shell for dashboard "table widgets": a dashboard widget that renders a
 * configurable table (columns, order, limit) backed by any data source.
 *
 * evx_projects was the first widget built this way, and originally this whole file
 * assumed the data source was always "members of one object type within one
 * dimension". That assumption has been split out: evx_widgets_build_widget_data()
 * below is now agnostic to where rows come from — it only handles what's the same
 * for every table widget (limit/order/column persistence via contact_widget_options),
 * and delegates fetching rows + columns to a $config['data_provider'] callable.
 *
 * The Members/Dimension-specific logic (MemberController::listing(), custom
 * property columns, dimension-association columns, percent-completed) still lives
 * here, but now as evx_widgets_member_dimension_data_provider() — a provider
 * *factory* that evx_projects (and any future Members-backed widget) uses. A
 * non-dimension widget (e.g. Tasks, backed by ProjectTasks) supplies its own
 * provider instead and reuses the exact same generic shell + React table.
 */

/**
 * Read a per-user widget option the same way Widget::getContactOptionValue() does,
 * without needing the Widget instance (the shell isn't a Widget method).
 */
function evx_widgets_get_widget_option($widget_name, $option_name) {
	$option = ContactWidgetOptions::instance()->getContactOption($widget_name, logged_user()->getId(), $option_name);
	if (empty($option)) {
		$option = ContactWidgetOptions::instance()->getDefaultOption($widget_name, $option_name);
	}
	return !empty($option) ? $option['value'] : '';
}

/**
 * Build every piece of data a table widget needs to render, or return false if the
 * widget shouldn't render at all (the data provider decides that — e.g. dimension
 * disabled, active context yields nothing, no rows and nothing to add).
 *
 * @param array $config {
 *     @type string   widget_name              Registered widget name (contact_widget_options key)
 *     @type callable data_provider            function($limit, $order_by, $order_dir) -> array|false {
 *                                                  @type int    total
 *                                                  @type array  available_columns
 *                                                  @type array  orderable_columns  (columns the provider can sort by)
 *                                                  @type array  rows               [ ['id'=>, 'values'=>[key=>val,...], 'assoc_member_ids'=>[] (optional)], ... ]
 *                                                  @type string widget_title
 *                                              }
 *     @type callable view_all_onclick         function() -> onclick JS string (the widget's own index.php
 *                                                  closes over whatever context it needs, e.g. dim_id/object_type_id)
 *     @type array    labels                   Pre-resolved (already lang()'d) labels for the React widget
 *     @type string   no_objects_text          Pre-resolved lang() text shown when the list is empty
 *     @type array    default_selected_columns Optional, defaults to ['name']
 *     @type array    header_toggle_groups     Optional. Segmented-toggle button groups rendered in the
 *                                                  widget header (e.g. Tasks' "Assigned to me / All" and
 *                                                  "All / Due today / ..." quick filters). Each entry:
 *                                                  ['key'=>, 'options'=>[['value'=>,'label'=>],...], 'default'=>].
 *                                                  Current values (persisted like any other widget option,
 *                                                  but without needing a data_changes seed row — an unset
 *                                                  option just falls back to 'default') are passed to the
 *                                                  data_provider as a 4th argument: $toggle_values[key]=>value.
 * }
 * @return array|false
 */
function evx_widgets_build_widget_data($config) {
	$raw_limit = evx_widgets_get_widget_option($config['widget_name'], 'limit');
	$limit = (int)($raw_limit !== '' ? $raw_limit : 10);
	if ($limit <= 0) {
		$limit = 10;
	}

	// Order criteria (per user, per widget). Defaults to name ascending. What the widget
	// column key means for sorting purposes (and whether it's even sortable) is entirely
	// up to the data provider — the shell just persists/forwards the raw key.
	$order_by = evx_widgets_get_widget_option($config['widget_name'], 'order_by');
	if ($order_by === '') {
		$order_by = 'name';
	}
	$order_dir = strtoupper((string)evx_widgets_get_widget_option($config['widget_name'], 'order_dir'));
	if ($order_dir !== 'DESC') {
		$order_dir = 'ASC';
	}

	// Optional widget-declared extra numeric settings (rendered in the config modal's General section,
	// next to the display-lines limit; e.g. the customer-events widget's "participants per event").
	// Each def: array('key'=>, 'label'=>, 'default'=>, optional 'min','max','unit'). The current value
	// is read from contact_widget_options under the def's key and passed to the data provider.
	$extra_number_defs = !empty($config['extra_number_settings']) ? $config['extra_number_settings'] : array();
	$extra_number_values = array();
	$extra_number_settings = array();
	foreach ($extra_number_defs as $def) {
		$raw = evx_widgets_get_widget_option($config['widget_name'], $def['key']);
		$value = (int)($raw !== '' ? $raw : (isset($def['default']) ? $def['default'] : 1));
		if ($value <= 0) $value = isset($def['default']) ? (int)$def['default'] : 1;
		$extra_number_values[$def['key']] = $value;
		$def['value'] = $value;
		$extra_number_settings[] = $def;
	}

	$toggle_group_defs = !empty($config['header_toggle_groups']) ? $config['header_toggle_groups'] : array();
	$toggle_values = array();
	$header_toggle_groups = array();
	foreach ($toggle_group_defs as $group) {
		$raw = evx_widgets_get_widget_option($config['widget_name'], $group['key']);
		$current = $raw !== '' ? $raw : $group['default'];
		$toggle_values[$group['key']] = $current;
		$group['current'] = $current;
		$header_toggle_groups[] = $group;
	}

	// Quick-actions catalog (opt-in, e.g. Tasks' row-hover kebab menu): same persistence
	// pattern as columns — an ordered [{key, visible}] list saved under the 'quick_actions'
	// option, defaulting to catalog order with everything visible. Resolved up front (like
	// genid) so the data_provider can build each row's kebab menu already respecting the
	// user's saved order/visibility.
	$quick_actions_catalog = !empty($config['quick_actions_catalog']) ? $config['quick_actions_catalog'] : array();
	$quick_actions_order = array();
	if (!empty($quick_actions_catalog)) {
		$saved_qa = evx_widgets_get_widget_option($config['widget_name'], 'quick_actions');
		$decoded_qa = $saved_qa ? json_decode($saved_qa, true) : null;
		if (is_array($decoded_qa) && !empty($decoded_qa)) {
			foreach ($decoded_qa as $entry) {
				if (!is_array($entry) || !array_key_exists('key', $entry)) continue;
				$quick_actions_order[] = array('key' => $entry['key'], 'visible' => !empty($entry['visible']));
			}
		}
		if (empty($quick_actions_order)) {
			foreach ($quick_actions_catalog as $qa) {
				$quick_actions_order[] = array('key' => $qa['key'], 'visible' => true);
			}
		}
	}

	// Generated up front (not after calling the provider) so the provider can embed
	// 'evxWidgetReload_<genid>(this)' calls in its row HTML — e.g. Tasks' kebab actions
	// (mark as started/complete), which need to refresh the table after a mutation.
	$genid = gen_id();

	$provider_result = call_user_func($config['data_provider'], $limit, $order_by, $order_dir, $toggle_values, $genid, $quick_actions_order, $extra_number_values);
	if ($provider_result === false) {
		return false;
	}

	$available_columns = $provider_result['available_columns'];
	$orderable_columns  = isset($provider_result['orderable_columns']) ? $provider_result['orderable_columns'] : array();
	$rows               = isset($provider_result['rows']) ? $provider_result['rows'] : array();
	$total              = isset($provider_result['total']) ? $provider_result['total'] : count($rows);
	$widget_title       = $provider_result['widget_title'];
	$title_count        = isset($provider_result['title_count']) ? $provider_result['title_count'] : null;

	// A data provider may need to force a specific sort for the current request (e.g. a Tasks
	// quick filter always shows the earliest/most-overdue due date first) without persisting that
	// override as the user's saved "Sort by" preference — the Settings modal keeps reflecting the
	// stored order_by/order_dir either way.
	if (isset($provider_result['effective_order_by']))  $order_by  = $provider_result['effective_order_by'];
	if (isset($provider_result['effective_order_dir'])) $order_dir = $provider_result['effective_order_dir'];

	// Same idea for header toggles (e.g. Tasks falling back from "mine" to "all" when the user
	// has nothing of their own to show): the toggle button rendered as active reflects what was
	// actually used for this request, without overwriting the user's saved preference.
	if (!empty($provider_result['effective_toggle_values']) && is_array($provider_result['effective_toggle_values'])) {
		foreach ($header_toggle_groups as &$group) {
			if (array_key_exists($group['key'], $provider_result['effective_toggle_values'])) {
				$group['current'] = $provider_result['effective_toggle_values'][$group['key']];
			}
		}
		unset($group);
	}

	// User-selected columns stored in contact_widget_options (per user, per widget).
	$default_selected_columns = !empty($config['default_selected_columns']) ? $config['default_selected_columns'] : array('name');

	$saved_selected = evx_widgets_get_widget_option($config['widget_name'], 'columns');
	$decoded_saved = $saved_selected ? json_decode($saved_selected, true) : null;

	$selected_columns = array();
	$selected_columns_order = array();

	if (is_array($decoded_saved) && !empty($decoded_saved)) {
		$is_object_format = is_array($decoded_saved[0]) && array_key_exists('key', $decoded_saved[0]);
		if ($is_object_format) {
			foreach ($decoded_saved as $entry) {
				if (!is_array($entry) || !array_key_exists('key', $entry)) continue;
				$selected_columns_order[] = $entry['key'];
				if (array_key_exists('visible', $entry) && $entry['visible']) {
					$selected_columns[] = $entry['key'];
				}
			}
		} else {
			foreach ($decoded_saved as $key) {
				if (is_string($key)) {
					$selected_columns[] = $key;
					$selected_columns_order[] = $key;
				}
			}
		}
	}

	if (empty($selected_columns)) {
		$selected_columns = $default_selected_columns;
	}
	if (empty($selected_columns_order)) {
		$selected_columns_order = $default_selected_columns;
	}

	return array(
		'genid'                    => $genid,
		'widget_name'              => $config['widget_name'],
		'widget_title'             => $widget_title,
		'title_count'              => $title_count,
		'available_columns'        => $available_columns,
		'selected_columns'         => $selected_columns,
		'selected_columns_order'   => $selected_columns_order,
		'orderable_columns'        => $orderable_columns,
		'order_by'                 => $order_by,
		'order_dir'                => $order_dir,
		'rows'                     => $rows,
		'limit'                    => $limit,
		'no_objects_text'          => $total > 0 ? '' : $config['no_objects_text'],
		'labels'                   => $config['labels'],
		'view_all_onclick'         => call_user_func($config['view_all_onclick']),
		'header_toggle_groups'     => $header_toggle_groups,
		'quick_actions_catalog'    => $quick_actions_catalog,
		'quick_actions_order'      => $quick_actions_order,
		'extra_number_settings'    => $extra_number_settings,
	);
}

/**
 * Render the widget shell (header, gear, "view all", body mount point) and the script that
 * mounts the shared React table component into it. Every table widget uses this same
 * markup/mount — only the data differs.
 *
 * @param array $data  Return value of evx_widgets_build_widget_data()
 */
function evx_widgets_render_table_widget($data) {
	$genid = $data['genid'];
	$save_config_url = get_url('dashboard', 'save_widget_option', array('ajax' => 'true'));

	// this hook is used to add additional html content to the dashboard before rendering this widget
	$params = array('genid' => $genid, 'widget_name' => $data['widget_name']);
	$ignored = null;
	Hook::fire('before_render_evx_widgets_table_widget', $params, $ignored);
	?>

	<?php $widget_name_class = 'evx-widget-name-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $data['widget_name']); ?>
	<div class="evx-widget-dimension-table widget <?php echo $widget_name_class ?>" id="evx-widget-outer-<?php echo $genid ?>">

		<div style="overflow: hidden;" class="widget-header color-1" onclick="og.dashExpand('<?php echo $genid ?>');">
			<div class="widget-title">
				<?php echo clean($data['widget_title']); ?>
				<?php if ($data['title_count'] !== null) { ?>
					<span class="fo-widget-count"><?php echo (int) $data['title_count'] ?></span>
				<?php } ?>
			</div>
			<?php foreach ($data['header_toggle_groups'] as $group) { ?>
				<div class="fo-widget-tabs" onclick="event.stopPropagation();">
					<?php foreach ($group['options'] as $opt) { ?>
						<button type="button"
							class="fo-widget-tab<?php echo $opt['value'] === $group['current'] ? ' active' : '' ?>"
							onclick="evxWidgetSetToggle_<?php echo $genid ?>('<?php echo clean($group['key']) ?>', '<?php echo clean($opt['value']) ?>'); return false;">
							<?php echo clean($opt['label']) ?>
						</button>
					<?php } ?>
				</div>
			<?php } ?>
			<a href="#" class="evx-widget-gear"
				title="<?php echo lang('evx projects widget settings') ?>"
				onclick="if (window['evxWidgetOpenConfigure_<?php echo $genid ?>']) window['evxWidgetOpenConfigure_<?php echo $genid ?>'](); event.stopPropagation(); return false;">
				<i class="icon-settings"></i>
			</a>
			<div class="view-all-container">
				<a href="#" onclick="<?php echo $data['view_all_onclick'] ?>"><?php echo lang("view all") ?></a>
			</div>
			<div style="margin-left: 15px;">
				<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
			</div>
		</div>

		<div class="widget-body" id="<?php echo $genid; ?>_widget_body">
			<div class="evx-widget-list" id="evx-widget-<?php echo $genid ?>"></div>
		</div>

	</div>

	<script>
	(function() {
		var dataForComponent = {
			availableColumns: <?php echo json_encode($data['available_columns']) ?>,
			selectedColumns: <?php echo json_encode($data['selected_columns']) ?>,
			selectedColumnsOrder: <?php echo json_encode($data['selected_columns_order']) ?>,
			orderableColumns: <?php echo json_encode($data['orderable_columns']) ?>,
			orderBy: <?php echo json_encode($data['order_by']) ?>,
			orderDir: <?php echo json_encode($data['order_dir']) ?>,
			rows: <?php echo json_encode($data['rows']) ?>,
			limit: <?php echo (int) $data['limit'] ?>,
			minLimit: 1,
			maxLimit: 50,
			labels: <?php echo json_encode($data['labels']) ?>,
			saveConfigUrl: <?php echo json_encode($save_config_url) ?>,
			saveConfigOptionsUrl: <?php echo json_encode(get_url('dashboard', 'save_widget_options', array('ajax' => 'true'))) ?>,
			widgetName: <?php echo json_encode($data['widget_name']) ?>,
			configOptionName: 'columns',
			configOptionLimitName: 'limit',
			configOptionOrderByName: 'order_by',
			configOptionOrderDirName: 'order_dir',
			quickActionsCatalog: <?php echo json_encode($data['quick_actions_catalog']) ?>,
			quickActionsOrder: <?php echo json_encode($data['quick_actions_order']) ?>,
			configOptionQuickActionsName: 'quick_actions',
			extraNumberSettings: <?php echo json_encode(isset($data['extra_number_settings']) ? $data['extra_number_settings'] : array()) ?>,
			reloadWidgetUrl: <?php echo json_encode(get_url('dashboard', 'load_widget', array('name' => $data['widget_name'], 'ajax' => 'true'))) ?>,
			widgetWrapperId: <?php echo json_encode('evx-widget-outer-' . $genid) ?>,
			genid: <?php echo json_encode($genid) ?>
		};

		try {
			if (typeof showDimensionTableWidget === 'function') {
				showDimensionTableWidget(dataForComponent, document.getElementById('evx-widget-<?php echo $genid ?>'));
			} else {
				console.error('showDimensionTableWidget function not found');
			}
		} catch (error) {
			console.error('Error initializing evx_widgets table React widget:', error);
		}

		// Reload the whole widget from the server (fresh query, correctly re-filtered/re-sorted).
		// Used by the header toggles below and, when quick actions are configured, by each row's
		// kebab menu (e.g. Tasks' "Mark as started"/"Complete") after a successful mutation —
		// those rows are plain server-rendered HTML, not React-controlled, so they can't update
		// themselves reactively.
		window['evxWidgetReload_<?php echo $genid ?>'] = function() {
			var widgetEl = document.getElementById(dataForComponent.widgetWrapperId);
			if (!widgetEl || !dataForComponent.reloadWidgetUrl) { window.location.reload(); return; }
			og.openLink(dataForComponent.reloadWidgetUrl, {
				preventPanelLoad: true,
				silent: true,
				postProcess: function(ok2, html) {
					if (!html) { window.location.reload(); return; }
					var scripts = [];
					var cleanHtml = html.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(_, sc) {
						if (sc.trim()) scripts.push(sc);
						return '';
					});
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
		};

		// Header quick-filter toggles (e.g. Tasks' scope/due-date tabs): save the option and
		// reload the whole widget so rows come from a fresh, correctly-filtered query — same
		// save+reload mechanism the React settings modal uses, just triggered without a modal.
		window['evxWidgetSetToggle_<?php echo $genid ?>'] = function(key, value) {
			og.openLink(dataForComponent.saveConfigOptionsUrl, {
				post: { widget_name: dataForComponent.widgetName, options: JSON.stringify([{ name: key, value: value }]) },
				preventPanelLoad: true,
				silent: true,
				postProcess: function(ok) {
					if (ok && window['evxWidgetReload_<?php echo $genid ?>']) window['evxWidgetReload_<?php echo $genid ?>']();
				}
			});
		};

		<?php if (!empty($data['quick_actions_catalog'])) { ?>
		// Row-hover kebab menu (Tasks' Add subtask/Edit/Mark as started/...). Each row renders
		// its own kebab button + a hidden menu right next to it (server-side HTML, see the data
		// provider); this just toggles the clicked row's menu and positions it with
		// position:fixed so it isn't clipped by the table's horizontal-scroll wrapper.
		(function() {
			var widgetEl = document.getElementById(dataForComponent.widgetWrapperId);
			if (!widgetEl) return;

			function closeAllMenus() {
				widgetEl.querySelectorAll('.evx-task-menu').forEach(function(m) { m.hidden = true; });
				widgetEl.querySelectorAll('.evx-task-kebab.is-open').forEach(function(k) { k.classList.remove('is-open'); });
			}

			widgetEl.addEventListener('click', function(e) {
				var kebab = e.target.closest && e.target.closest('.evx-task-kebab');
				if (!kebab) return;
				e.preventDefault();
				e.stopPropagation();
				var menu = document.getElementById(kebab.getAttribute('data-menu-target'));
				if (!menu) return;
				var wasHidden = menu.hidden;
				closeAllMenus();
				if (!wasHidden) return;
				menu.hidden = false;
				kebab.classList.add('is-open');
				var r = kebab.getBoundingClientRect();
				var menuWidth = menu.offsetWidth || 190;
				var left = r.right - menuWidth;
				if (left < 4) left = 4;
				menu.style.left = left + 'px';
				var menuH = menu.offsetHeight;
				var top;
				if (window.innerHeight - r.bottom < menuH + 12 && r.top > menuH + 12) {
					top = r.top - menuH - 4;
				} else {
					top = r.bottom + 4;
				}
				menu.style.top = top + 'px';
			});

			if (!window._evxTaskMenuGlobalHandlerAdded) {
				window._evxTaskMenuGlobalHandlerAdded = true;
				document.addEventListener('click', function(e) {
					document.querySelectorAll('.evx-task-menu:not([hidden])').forEach(function(m) {
						if (!m.contains(e.target) && !(e.target.closest && e.target.closest('.evx-task-kebab'))) m.hidden = true;
					});
					document.querySelectorAll('.evx-task-kebab.is-open').forEach(function(k) {
						var m = document.getElementById(k.getAttribute('data-menu-target'));
						if (!m || m.hidden) k.classList.remove('is-open');
					});
				});
				document.addEventListener('keydown', function(e) {
					if (e.key === 'Escape') {
						document.querySelectorAll('.evx-task-menu').forEach(function(m) { m.hidden = true; });
						document.querySelectorAll('.evx-task-kebab.is-open').forEach(function(k) { k.classList.remove('is-open'); });
					}
				});
			}
		})();
		<?php } ?>
	})();
	</script>
	<?php
}

/**
 * Build every piece of data a "feed widget" needs to render, or return false if it
 * shouldn't render at all. A feed widget is a vertical list (Activity, Emails) —
 * items are already-rendered HTML from the data provider, since there's no column
 * concept to negotiate client-side (unlike table widgets).
 *
 * @param array $config {
 *     @type string   widget_name       Registered widget name (contact_widget_options key)
 *     @type callable data_provider     function($limit, $filter_values) -> array|false {
 *                                           @type array  items_html    [ html string, ... ] — one per row
 *                                           @type string widget_title
 *                                       }
 *     @type callable view_all_onclick  function() -> onclick JS string
 *     @type array    labels            Pre-resolved (already lang()'d) labels for the React widget
 *     @type string   no_objects_text   Pre-resolved lang() text shown when the list is empty
 *     @type array    extra_filters     Optional. Each: ['key'=>, 'label'=>, 'type'=>'checkbox'|'checklist',
 *                                       'options'=>[['value'=>,'label'=>],...] (checklist only), 'default'=>]
 *     @type array    header_extra_actions  Optional. Extra header icons rendered before the gear (e.g.
 *                                       Emails' "Compose"). Each: ['icon'=>'icon-x', 'title'=>, 'onclick'=>JS string]
 * }
 * @return array|false
 */
function evx_widgets_build_feed_widget_data($config) {
	$raw_limit = evx_widgets_get_widget_option($config['widget_name'], 'limit');
	$limit = (int)($raw_limit !== '' ? $raw_limit : 10);
	if ($limit <= 0) {
		$limit = 10;
	}

	$filter_defs = !empty($config['extra_filters']) ? $config['extra_filters'] : array();
	$filter_values = array();
	foreach ($filter_defs as $filter) {
		$raw = evx_widgets_get_widget_option($config['widget_name'], $filter['key']);
		if ($filter['type'] === 'checklist') {
			$decoded = $raw !== '' ? json_decode($raw, true) : null;
			$filter_values[$filter['key']] = is_array($decoded) ? $decoded : (isset($filter['default']) ? $filter['default'] : array());
		} else if ($filter['type'] === 'select') {
			// single value, stored as a plain string ('' = no filter / "all")
			$filter_values[$filter['key']] = $raw !== '' ? $raw : (isset($filter['default']) ? $filter['default'] : '');
		} else {
			// checkbox — stored as '1'/'0'
			$filter_values[$filter['key']] = $raw !== '' ? $raw : (isset($filter['default']) ? $filter['default'] : '0');
		}
	}

	// Generated up front (not after calling the provider, unlike the table shell) so the
	// provider can embed 'evxWidgetReload_<genid>(this)' calls in its item HTML — e.g. Emails'
	// archive/delete row actions, which need to refresh the list after a successful mutation.
	$genid = gen_id();

	$provider_result = call_user_func($config['data_provider'], $limit, $filter_values, $genid);
	if ($provider_result === false) {
		return false;
	}

	$items_html = isset($provider_result['items_html']) ? $provider_result['items_html'] : array();
	$title_count = isset($provider_result['title_count']) ? $provider_result['title_count'] : null;

	return array(
		'genid'                => $genid,
		'widget_name'          => $config['widget_name'],
		'widget_title'         => $provider_result['widget_title'],
		'title_count'          => $title_count,
		'items_html'           => $items_html,
		'limit'                => $limit,
		'no_objects_text'      => !empty($items_html) ? '' : $config['no_objects_text'],
		'labels'               => $config['labels'],
		'extra_filters'        => $filter_defs,
		'filter_values'        => $filter_values,
		'view_all_onclick'     => !empty($config['view_all_onclick']) ? call_user_func($config['view_all_onclick']) : null,
		'view_all_label'       => !empty($config['view_all_label']) ? $config['view_all_label'] : null,
		'header_extra_actions' => !empty($config['header_extra_actions']) ? $config['header_extra_actions'] : array(),
	);
}

/**
 * Render the feed widget shell (header, gear, "view all", body mount point) and the
 * script that mounts the shared React feed component into it.
 *
 * @param array $data  Return value of evx_widgets_build_feed_widget_data()
 */
function evx_widgets_render_feed_widget($data) {
	$genid = $data['genid'];

	$params = array('genid' => $genid, 'widget_name' => $data['widget_name']);
	$ignored = null;
	Hook::fire('before_render_evx_widgets_feed_widget', $params, $ignored);
	?>

	<div class="evx-widget-feed widget" id="evx-widget-outer-<?php echo $genid ?>">

		<div style="overflow: hidden;" class="widget-header color-1" onclick="og.dashExpand('<?php echo $genid ?>');">
			<div class="widget-title">
				<?php echo clean($data['widget_title']); ?>
				<?php if ($data['title_count'] !== null) { ?>
					<span class="fo-widget-count"><?php echo (int) $data['title_count'] ?></span>
				<?php } ?>
			</div>
			<?php foreach ($data['header_extra_actions'] as $action) { ?>
				<a href="#" class="evx-widget-gear"
					title="<?php echo clean($action['title']) ?>"
					onclick="<?php echo clean($action['onclick']) ?> event.stopPropagation(); return false;">
					<i class="<?php echo clean($action['icon']) ?>"></i>
				</a>
			<?php } ?>
			<a href="#" class="evx-widget-gear"
				title="<?php echo lang('evx projects widget settings') ?>"
				onclick="if (window['evxWidgetOpenConfigure_<?php echo $genid ?>']) window['evxWidgetOpenConfigure_<?php echo $genid ?>'](); event.stopPropagation(); return false;">
				<i class="icon-settings"></i>
			</a>
			<?php if (!empty($data['view_all_onclick'])) { ?>
			<div class="view-all-container">
				<a href="#" onclick="<?php echo $data['view_all_onclick'] ?>"><?php echo !empty($data['view_all_label']) ? clean($data['view_all_label']) : lang("view all") ?></a>
			</div>
			<?php } ?>
			<div style="margin-left: 15px;">
				<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
			</div>
		</div>

		<div class="widget-body" id="<?php echo $genid; ?>_widget_body">
			<div class="evx-widget-feed-list" id="evx-widget-<?php echo $genid ?>"></div>
		</div>

	</div>

	<script>
	(function() {
		var dataForComponent = {
			itemsHtml: <?php echo json_encode($data['items_html']) ?>,
			noItemsText: <?php echo json_encode($data['no_objects_text']) ?>,
			limit: <?php echo (int) $data['limit'] ?>,
			minLimit: 1,
			maxLimit: 50,
			extraFilters: <?php echo json_encode($data['extra_filters']) ?>,
			filterValues: <?php echo json_encode($data['filter_values']) ?>,
			labels: <?php echo json_encode($data['labels']) ?>,
			saveConfigOptionsUrl: <?php echo json_encode(get_url('dashboard', 'save_widget_options', array('ajax' => 'true'))) ?>,
			widgetName: <?php echo json_encode($data['widget_name']) ?>,
			configOptionLimitName: 'limit',
			reloadWidgetUrl: <?php echo json_encode(get_url('dashboard', 'load_widget', array('name' => $data['widget_name'], 'ajax' => 'true'))) ?>,
			widgetWrapperId: <?php echo json_encode('evx-widget-outer-' . $genid) ?>,
			genid: <?php echo json_encode($genid) ?>
		};

		try {
			if (typeof showFeedWidget === 'function') {
				showFeedWidget(dataForComponent, document.getElementById('evx-widget-<?php echo $genid ?>'));
			} else {
				console.error('showFeedWidget function not found');
			}
		} catch (error) {
			console.error('Error initializing evx_widgets feed React widget:', error);
		}

		// Lets an item's own row HTML (built server-side by the data provider — e.g. Emails'
		// archive/delete actions) trigger a full widget reload after a successful mutation,
		// since those rows aren't React-controlled and can't remove themselves reactively.
		window['evxWidgetReload_<?php echo $genid ?>'] = function() {
			var widgetEl = document.getElementById(dataForComponent.widgetWrapperId);
			if (!widgetEl || !dataForComponent.reloadWidgetUrl) { window.location.reload(); return; }
			og.openLink(dataForComponent.reloadWidgetUrl, {
				preventPanelLoad: true,
				silent: true,
				postProcess: function(ok2, html) {
					if (!html) { window.location.reload(); return; }
					var scripts = [];
					var cleanHtml = html.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(_, sc) {
						if (sc.trim()) scripts.push(sc);
						return '';
					});
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
		};
	})();
	</script>
	<?php
}

// ============================================================================
// Members/Dimension data provider — the original evx_projects data source.
// Everything below is specific to "rows are members of one object type within
// one dimension"; it's kept in this file (rather than moved to the plugin)
// because it's still the most common table-widget shape and several helpers
// here (CP resolution, dim_assoc columns) are only meaningful for it.
// ============================================================================

/**
 * Build a data_provider callable (see evx_widgets_build_widget_data()) backed by
 * MemberController::listing() for one dimension + object type.
 *
 * @param array $member_config {
 *     @type Dimension  dimension                 Resolved Dimension instance
 *     @type ObjectType object_type                Resolved ObjectType instance
 *     @type string     object_model_class         Model manager class, e.g. 'Projects'
 *     @type string|null edit_controller           Controller name for the __edit__ action column (null = no edit column)
 *     @type callable   native_columns_provider    function($objects, $dimension_id) -> array('columns'=>[], 'values_by_object_id'=>[])
 *     @type bool       include_percent_completed  Opt-in: show the percent_completed column (advanced_billing) — this is a
 *                                                     project billing metric, not a generic dimension-member attribute.
 *     @type bool       use_dimension_custom_name  Opt-in: prefer the DIMENSION's custom name (Settings > Dimension options,
 *                                                     DimensionOptions 'custom_dimension_name') for the widget title over the
 *                                                     OBJECT TYPE's custom name. Off by default because a dimension can host
 *                                                     more than one object type (e.g. "customer_project" has both "project"
 *                                                     and "customer") — turning this on for every widget built on this shell
 *                                                     would make an unrelated widget (e.g. the customers one) inherit
 *                                                     whatever name was set for a DIFFERENT widget sharing the same
 *                                                     dimension. Only opt in for the widget that the custom-name row in
 *                                                     that settings screen actually corresponds to.
 * }
 * @return callable
 */
function evx_widgets_member_dimension_data_provider($member_config) {
	return function($limit, $order_by, $order_dir) use ($member_config) {
		$dimension   = $member_config['dimension'];
		$object_type = $member_config['object_type'];
		$dim_id      = $dimension->getId();
		$ot_id       = $object_type->getId();

		$dot = DimensionObjectTypes::instance()->findOne(array('conditions' => 'dimension_id=' . $dim_id . ' AND object_type_id=' . $ot_id));
		if (!$dot instanceof DimensionObjectType || !$dot->getEnabled()) {
			return false;
		}

		// Translate the widget column key to a listing() sort key. Unknown keys fall back to
		// display_name (build_listing_order_parameters() also degrades unknown keys to mem.name).
		if ($order_by === 'name') {
			$listing_sort = 'display_name';
		} else if (str_starts_with($order_by, 'dim_assoc_')) {
			$listing_sort = 'dimassoc_' . substr($order_by, strlen('dim_assoc_'));
		} else if (str_starts_with($order_by, 'cp_') || $order_by === 'percent_completed') {
			$listing_sort = $order_by;
		} else {
			$listing_sort = 'display_name';
		}

		// Determine if a same-dimension context member is selected (used for hiding the widget
		// and for filtering to that member's children).
		$context_member = null;
		$context = active_context();
		if (is_array($context)) {
			foreach ($context as $sel) {
				if ($sel instanceof Member && $sel->getDimensionId() == $dim_id) {
					$context_member = $sel;
					break;
				}
			}
		}

		// MemberController::listing() handles permissions, parent filter, and associated-dimension
		// filtering (other context dimensions) automatically via active_context().
		// exclude_association_columns=true keeps the EXISTS filter for context filtering but drops
		// GROUP_CONCAT JOINs and GROUP BY, allowing MySQL to use LIMIT efficiently.
		$mc = new MemberController();
		$listing_rows = $mc->listing(array(
			'dim_id'                      => $dim_id,
			'type_id'                     => $ot_id,
			'limit'                       => $limit,
			'start'                       => 0,
			'archived'                    => false,
			'sort'                        => $listing_sort,
			'dir'                         => $order_dir,
			'parent_id'                   => $context_member instanceof Member ? $context_member->getId() : null,
			'just_parent_sons'            => $context_member instanceof Member,
			'raw_rows'                    => true,
			'exclude_association_columns' => true,
		));
		if (!is_array($listing_rows)) $listing_rows = array();

		// Hide the widget when a context is active but yields no objects.
		$has_other_dim_context = false;
		if (is_array($context)) {
			foreach ($context as $sel) {
				if ($sel instanceof Member && $sel->getDimensionId() != $dim_id) {
					$has_other_dim_context = true;
					break;
				}
			}
		}
		$context_was_active = ($context_member instanceof Member) || $has_other_dim_context;
		if ($context_was_active && count($listing_rows) === 0) {
			return false;
		}

		// Load the full objects preserving listing order.
		$object_ids = array_map(function($r) { return (int)$r['object_id']; }, $listing_rows);
		$objects = array();
		if (count($object_ids) > 0) {
			$model_class = $member_config['object_model_class'];
			$objects = $model_class::instance()->findAll(array(
				'conditions' => "object_id IN (" . implode(',', $object_ids) . ")",
				'order'      => "FIELD(object_id, " . implode(',', $object_ids) . ")",
			));
		}
		$total = count($objects);

		// Build association map early — needed for the batch assoc query below.
		$associations = DimensionMemberAssociations::getAssociatations($dim_id, $ot_id);
		$associations_map = array();
		foreach ($associations as $assoc) { /* @var $assoc DimensionMemberAssociation */
			$assoc_info = $assoc->getArrayInfo();
			if (!is_array($assoc_info)) continue;
			$associations_map[$assoc->getId()] = $assoc;
		}

		// Index listing rows and members by object_id for O(1) access below.
		// Warm ORM cache with a single bulk query first so individual findById calls are cache hits.
		$listing_member_ids = array_map(function($r) { return (int)$r['member_id']; }, $listing_rows);
		if (count($listing_member_ids) > 0) {
			Members::instance()->findAll(array('conditions' => 'id IN (' . implode(',', $listing_member_ids) . ')'));
		}
		$members_map = array();
		$listing_rows_by_object_id = array();
		foreach ($listing_rows as $row) {
			$oid = (int)$row['object_id'];
			$m   = Members::instance()->findById((int)$row['member_id']);
			if ($m instanceof Member) {
				$members_map[$oid]              = $m;
				$listing_rows_by_object_id[$oid] = $row;
			}
		}

		// Batch-fetch association data for all listing members in one query and merge
		// dimassoc_{assoc_id} columns into listing_rows_by_object_id.
		if (count($listing_member_ids) > 0 && count($associations_map) > 0) {
			$assoc_rows = DB::executeAll(
				"SELECT mpm.member_id, mpm.association_id, GROUP_CONCAT(DISTINCT mpm.property_member_id ORDER BY mpm.property_member_id) AS member_ids
				 FROM " . TABLE_PREFIX . "member_property_members mpm
				 WHERE mpm.member_id IN (" . implode(',', $listing_member_ids) . ")
				   AND mpm.association_id IN (" . implode(',', array_keys($associations_map)) . ")
				 GROUP BY mpm.member_id, mpm.association_id"
			);
			if (is_array($assoc_rows)) {
				$assoc_data = array();
				foreach ($assoc_rows as $ar) {
					$assoc_data[(int)$ar['member_id']][(int)$ar['association_id']] = $ar['member_ids'];
				}
				foreach ($listing_rows_by_object_id as $oid => &$row) {
					$mid = (int)$row['member_id'];
					foreach ($associations_map as $assoc_id => $assoc) {
						$row['dimassoc_' . $assoc_id] = isset($assoc_data[$mid][$assoc_id]) ? $assoc_data[$mid][$assoc_id] : '0';
					}
				}
				unset($row);
			}
		}

		// Native ("business") columns: whatever is specific to this object type — provided by the
		// widget, computed over the already-paginated $objects. These can never be orderable (see
		// the rule below), because they're only correct within the current page.
		$native = array('columns' => array(), 'values_by_object_id' => array());
		if (!empty($member_config['native_columns_provider'])) {
			$native = call_user_func($member_config['native_columns_provider'], $objects, $dim_id);
			if (!isset($native['columns'])) $native['columns'] = array();
			if (!isset($native['values_by_object_id'])) $native['values_by_object_id'] = array();
		}

		$render_add = can_manage_dimension_members(logged_user());
		$render_add_templates = false;
		if ($render_add) {
			if (Plugins::instance()->isActivePlugin('member_templates')) {
				$member_templates = MemberTemplates::instance()->findAll(array("conditions" => "object_type_id=" . $ot_id, "order" => "name"));
				if (count($member_templates) > 0) {
					$render_add = false;
					$render_add_templates = true;
				}
			}
		}

		// ---------------------------------------------------------------
		// Column catalog + per-object values for the React widget.
		// ---------------------------------------------------------------

		$available_columns = array();
		if (!empty($member_config['edit_controller'])) {
			$available_columns[] = array('key' => '__edit__', 'label' => '', 'type' => 'native', 'is_html' => true, 'is_action' => true);
		}
		$available_columns[] = array('key' => 'name', 'label' => $object_type->getObjectTypeName() . ' ' . lang('Name'), 'type' => 'native');

		foreach ($native['columns'] as $col) {
			$available_columns[] = $col;
		}

		// percent_completed is a project billing metric, not a generic dimension-member
		// attribute — only show it for widgets that explicitly opt in (evx_projects does;
		// evx_customers, for example, should not, since "percent completed" has no meaning
		// for a client).
		$has_advanced_billing = !empty($member_config['include_percent_completed']) && Plugins::instance()->isActivePlugin('advanced_billing');
		if ($has_advanced_billing) {
			$available_columns[] = array(
				'key'     => 'percent_completed',
				'label'   => lang('percent completed'),
				'type'    => 'native',
				'is_html' => true,
			);
		}

		foreach ($associations_map as $assoc_id => $assoc) { /* @var $assoc DimensionMemberAssociation */
			$assoc_info = $assoc->getArrayInfo();
			if (!is_array($assoc_info)) continue;
			$available_columns[] = array(
				'key'     => 'dim_assoc_' . $assoc_id,
				'label'   => $assoc_info['name'],
				'type'    => 'native',
				'is_html' => true,
			);
		}

		// Warm CP value caches for all objects and their members in two bulk queries,
		// so the per-object CP resolution loop below makes zero DB round-trips.
		CustomPropertyValues::prefetchForObjects($object_ids);
		MemberCustomPropertyValues::prefetchForMembers(array_keys($members_map));

		$cps = CustomProperties::getAllCustomPropertiesByObjectType($ot_id);
		if (is_array($cps)) {
			foreach ($cps as $cp) { /* @var $cp CustomProperty */
				$cp_type = $cp->getType();
				$available_columns[] = array(
					'key'   => 'cp_' . $cp->getId(),
					'label' => $cp->getName(),
					'type'  => 'cp',
					'cp_id' => $cp->getId(),
					'cp_type' => $cp_type,
					'is_multiline' => in_array($cp_type, array('address', 'memo'), true) || (method_exists($cp, 'getIsMultipleValues') && $cp->getIsMultipleValues()),
				);
			}
		}

		// Orderable columns for the "Sort by" dropdown: only columns that MemberController::listing()
		// can sort natively (name, associated dimensions, custom properties, and percent_completed).
		// Native business columns (from the provider) are computed in PHP after the query limit, so
		// they must never be orderable — the value is only correct within the current page.
		$orderable_columns = array();
		foreach ($available_columns as $col) {
			$key = $col['key'];
			$is_orderable = ($key === 'name')
				|| str_starts_with($key, 'dim_assoc_')
				|| str_starts_with($key, 'cp_')
				|| $key === 'percent_completed';
			if ($is_orderable) {
				$orderable_columns[] = array('key' => $key, 'label' => $col['label']);
			}
		}
		usort($orderable_columns, function($a, $b) {
			return strcasecmp($a['label'], $b['label']);
		});

		$pct_completed_map = array();
		if ($has_advanced_billing && count($members_map) > 0) {
			$pct_member_ids = array();
			foreach ($members_map as $m) {
				$pct_member_ids[] = (int)$m->getId();
			}
			$pct_rows = MemberTotals::instance()->findAll(array(
				'conditions' => 'member_id IN (' . implode(',', $pct_member_ids) . ')',
			));
			if (is_array($pct_rows)) {
				foreach ($pct_rows as $pct_row) {
					$pct_completed_map[(int)$pct_row->getMemberId()] = (int)$pct_row->getColumnValue('percent_completed');
				}
			}
		}

		$rows = array();
		foreach ($objects as $object) { /* @var $object ContentDataObject */
			$member = isset($members_map[$object->getId()]) ? $members_map[$object->getId()] : null;
			if (!$member instanceof Member) {
				continue;
			}

			$values = array();
			if (!empty($member_config['edit_controller'])) {
				$values['__edit__'] = '<a class="evx-widget-edit-icon" href="#" ' .
					'onclick="og.render_modal_form(\'\', {c:\'' . $member_config['edit_controller'] . '\', a:\'edit\', params:{id:' . $object->getId() . '}}); return false;" ' .
					'title="' . lang('edit') . '">' .
					'<i class="icon-pencil-line"></i>' .
					'</a>';
			}
			$values['name'] = clean($member->getDisplayName());

			$listing_row = isset($listing_rows_by_object_id[$object->getId()]) ? $listing_rows_by_object_id[$object->getId()] : array();
			$dim_assoc   = evx_widgets_build_dim_assoc_values($listing_row, $associations_map);
			$values      = array_merge($values, $dim_assoc['values']);
			$assoc_member_ids = $dim_assoc['member_ids'];

			$native_values = isset($native['values_by_object_id'][$object->getId()]) ? $native['values_by_object_id'][$object->getId()] : array();
			foreach ($native_values as $k => $v) {
				$values[$k] = $v;
			}

			if ($has_advanced_billing) {
				$pct = isset($pct_completed_map[$member->getId()]) ? $pct_completed_map[$member->getId()] : 0;
				$values['percent_completed'] = evx_widgets_render_percent_completed($pct);
			}

			if (is_array($cps)) {
				foreach ($cps as $cp) { /* @var $cp CustomProperty */
					$values['cp_' . $cp->getId()] = evx_widgets_resolve_cp_value($cp, $object, $member);
				}
			}

			$rows[] = array(
				'id'               => $object->getId(),
				'member_id'        => $member->getId(),
				'values'           => $values,
				'assoc_member_ids' => $assoc_member_ids,
			);
		}

		if (!($total > 0 || $render_add || $render_add_templates)) {
			return false;
		}

		$widget_title = Members::getTypeNameToShowByObjectType($dim_id, $ot_id, null, true);
		if (!empty($member_config['use_dimension_custom_name'])) {
			$custom_dim_name = DimensionOptions::getOptionValue($dim_id, 'custom_dimension_name');
			if ($custom_dim_name && trim($custom_dim_name) !== '') {
				$widget_title = $custom_dim_name;
			}
		}

		return array(
			'total'             => $total,
			'available_columns' => $available_columns,
			'orderable_columns' => $orderable_columns,
			'rows'              => $rows,
			'widget_title'      => $widget_title,
		);
	};
}

/**
 * Build dim_assoc_* cell values and collect associated member IDs for a single object.
 * Uses the dimassoc_{id} GROUP_CONCAT columns already present in the MemberController listing row.
 *
 * @param array $listing_row       Row from MemberController::listing() for this object
 * @param array $associations_map  [ assoc_id => DimensionMemberAssociation ]
 * @return array  ['values' => [dim_assoc_N => html, ...], 'member_ids' => [int, ...]]
 */
function evx_widgets_build_dim_assoc_values($listing_row, $associations_map) {
	$values     = array();
	$member_ids = array();

	foreach ($associations_map as $assoc_id => $_) {
		$raw_ids = isset($listing_row['dimassoc_' . $assoc_id]) ? $listing_row['dimassoc_' . $assoc_id] : '';
		$ids     = array_filter(
			array_map('intval', explode(',', $raw_ids)),
			function($id) { return $id > 0; }
		);

		if (empty($ids)) {
			$values['dim_assoc_' . $assoc_id] = '';
			continue;
		}

		$html = '';
		foreach ($ids as $mid) {
			$member_ids[] = $mid;
			$html .=
				'<span class="member-path">' .
				'<span class="bread-crumb-' . $mid .
				' empty-bread-crumb member-path"' .
				' data-container-to-fill=".evx-widget-cell-dim_assoc_' . (int)$assoc_id . '"' .
				' data-show-link="1"' .
				' data-exclude-parents-path="0">' .
				'</span></span>';
		}
		$values['dim_assoc_' . $assoc_id] = $html;
	}

	return array('values' => $values, 'member_ids' => $member_ids);
}

/**
 * Resolve the display value of a custom property for an object row.
 * Falls back to the member-level CP value when the object-level value is empty.
 *
 * @param CustomProperty $cp
 * @param ContentDataObject $object
 * @param Member $member
 * @return mixed  Formatted string (or array for multivalue types)
 */
function evx_widgets_resolve_cp_value($cp, $object, $member) {
	$cp_type  = $cp->getType();
	$needs_raw = in_array($cp_type, array('address', 'memo'), true);
	$is_multi  = method_exists($cp, 'getIsMultipleValues') && $cp->getIsMultipleValues() && !$needs_raw;

	$cp_value = get_custom_property_value_for_listing($cp, $object, null, $needs_raw);
	if ($needs_raw) {
		$cp_value = evx_widgets_format_cp_value_with_newlines($cp_type, $cp_value);
	} elseif ($is_multi && is_string($cp_value) && strpos($cp_value, ', ') !== false) {
		$parts    = array_filter(array_map('trim', explode(', ', $cp_value)), function($p) { return $p !== ''; });
		$cp_value = implode("\n", $parts);
	}

	$is_empty = $cp_value === null
		|| (is_string($cp_value) && trim($cp_value) === '')
		|| (is_array($cp_value) && empty($cp_value));

	if ($is_empty && $member instanceof Member && function_exists('get_member_custom_property_value_for_listing')) {
		$member_value = get_member_custom_property_value_for_listing($cp, $member->getId(), null, $needs_raw);
		if ($needs_raw) {
			$member_value = evx_widgets_format_cp_value_with_newlines($cp_type, $member_value);
		} elseif ($is_multi && is_string($member_value) && strpos($member_value, ', ') !== false) {
			$parts        = array_filter(array_map('trim', explode(', ', $member_value)), function($p) { return $p !== ''; });
			$member_value = implode("\n", $parts);
		}
		$has_value = (is_string($member_value) && trim($member_value) !== '')
			|| (is_array($member_value) && !empty($member_value));
		if ($has_value) {
			$cp_value = $member_value;
		}
	}

	return $cp_value;
}

function evx_widgets_format_cp_value_with_newlines($cp_type, $raw_value) {
	if ($cp_type === 'memo') {
		return is_string($raw_value) ? $raw_value : '';
	}
	if ($cp_type === 'address') {
		if (!is_array($raw_value) || empty($raw_value)) return '';
		$is_multi = isset($raw_value[0]) && is_array($raw_value[0]);
		$entries = $is_multi ? $raw_value : array($raw_value);
		$rendered = array();
		foreach ($entries as $addr) {
			if (!is_array($addr)) continue;
			$street       = isset($addr['street'])   ? $addr['street']   : '';
			$city         = isset($addr['city'])     ? $addr['city']     : '';
			$state        = isset($addr['state'])    ? $addr['state']    : '';
			$zip_code     = isset($addr['zip_code']) ? $addr['zip_code'] : '';
			$country_name = isset($addr['country'])  ? $addr['country']  : '';
			$second_line_parts = array();
			if ($city != '')         $second_line_parts[] = $city;
			if ($state != '')        $second_line_parts[] = $state;
			if ($zip_code != '')     $second_line_parts[] = $zip_code;
			if ($country_name != '') $second_line_parts[] = $country_name;
			$second_line = implode(' - ', $second_line_parts);
			$piece = ($street !== '' ? $street . "\n" : '') . $second_line;
			$piece = rtrim($piece, "\n");
			if (trim($piece) !== '') $rendered[] = $piece;
		}
		return implode("\n", $rendered);
	}
	return is_string($raw_value) ? $raw_value : '';
}

/**
 * Render the percent-completed progress bar HTML for a dimension member.
 *
 * @param int $pct  Raw percent value (may exceed 100)
 * @return string
 */
function evx_widgets_render_percent_completed($pct) {
	if ($pct < 25)       $bucket = '0';
	elseif ($pct < 50)   $bucket = '25';
	elseif ($pct < 75)   $bucket = '50';
	elseif ($pct < 100)  $bucket = '75';
	elseif ($pct == 100) $bucket = '100';
	else                 $bucket = 'more-estimate';

	$display = min($pct, 100);
	return '<div class="evx-widget-progress-container">'
		. '<div class="evx-widget-progress-track">'
		. '<div class="evx-widget-progress-fill task-percent-completed-' . $bucket . '" style="--evx-progress-target: ' . $display . '%;"></div>'
		. '</div>'
		. '<span class="evx-widget-progress-label">' . $display . '%</span>'
		. '</div>';
}
