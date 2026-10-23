/**
 * Getting started checklist (application/views/more/getting_started.php).
 * Opens the form of each step, keeps the checklist in sync with the installation
 * and finishes or skips the initial setup.
 */
og.GettingStarted = {

	panelId: 'more-panel',

	/**
	 * Called by every checklist that is drawn. The clicks are handled by the document (see the end of this
	 * file), so nothing has to be bound to the checklist itself.
	 */
	init: function(genid) {
		// users without steps have no card, and their page must stay fresh too
		this.watchTab();
	},

	/** Opens the form of a step: as a modal, or in the panel for the forms that are full screens. */
	runAction: function(button) {
		var data = button.dataset;
		if (data.actionType == 'modal') {
			og.render_modal_form('', {
				c: data.controller,
				a: data.action,
				params: JSON.parse(data.params || '{}'),
				// fires when the form is saved and when it is cancelled
				callbackOnClose: function() {
					og.GettingStarted.refresh();
				}
			});
		} else {
			og.openLink(data.url);
		}
	},

	/** Leaves one step aside. It keeps its action, in case it is done later. */
	skipStep: function(button) {
		button.disabled = true;
		og.openLink(og.getUrl('more', 'skip_getting_started_step', {step_id: button.getAttribute('data-step')}), {
			hideLoading: true,
			callback: function(success) {
				if (!success) button.disabled = false;
				og.GettingStarted.refresh();
			}
		});
	},

	/** Renders the checklist again from the server, if it is what the panel is showing. */
	refresh: function() {
		var panel = Ext.getCmp(this.panelId);
		if (panel && document.querySelector('#' + this.panelId + ' .gs-page')) {
			panel.reload();
		}
	},

	/** Steps can be completed from other tabs, so the state is recomputed when this one is shown again. */
	watchTab: function() {
		if (this.watching) return;
		var panel = Ext.getCmp(this.panelId);
		if (!panel) return;
		this.watching = true;
		panel.on('activate', function() {
			og.GettingStarted.refresh();
		});
	},

	showSkipConfirm: function(root, show) {
		var confirm = root.querySelector('.gs-skip-confirm');
		if (confirm) confirm.hidden = !show;
	},

	/** Ends the initial setup; the tab becomes the settings page. */
	finish: function(button) {
		button.disabled = true;
		og.openLink(og.getUrl('more', 'set_getting_started_step', {step: 99, reload_panel: 1}), {
			hideLoading: true,
			callback: function(success, data) {
				if (!success) {
					button.disabled = false;
					return;
				}
				og.GettingStarted.showSettingsTab();
			}
		});
	},

	/**
	 * The tab strip is rendered once per page load: update its label and icon in place.
	 * The tab is not made closable here. Closing is only remembered for a tab the server rendered
	 * as closable, which happens from the next page load on.
	 */
	showSettingsTab: function() {
		var tab = $("#tabs-panel__" + this.panelId);
		tab.find(".x-tab-strip-text").removeClass("ico-more-tab").addClass("ico-administration").html(lang('more-panel'));
	},

	/** Path of this installation. Browser storage is per origin, and one origin can host several installations. */
	installPath: function() {
		return window.location.pathname.replace(/[^\/]*$/, '');
	},

	/**
	 * Where "the tab was already opened for this user" is remembered.
	 *
	 * @param {Number|String} userId
	 * @param {String} installPath defaults to the path of this installation
	 * @return {String}
	 */
	openedKey: function(userId, installPath) {
		if (typeof installPath == 'undefined') installPath = this.installPath();
		return 'og.gettingStarted.opened.' + installPath + '.' + userId;
	},

	/**
	 * Makes the tab open by itself again on the next page load. For steps that have to reload the
	 * whole page (applying module changes), so the administrator lands back on the checklist.
	 */
	reopenOnNextLoad: function() {
		try {
			window.localStorage.removeItem(this.openedKey(og.loggedUser.id));
		} catch (e) {
			// storage is not available: the tab is not forced and the checklist stays one click away
		}
	},

	/**
	 * Whether the tab should be opened by itself on this page load: only for users who can manage the
	 * configuration, only while the setup is not finished and only the first time in each browser.
	 *
	 * @param {Number|String} step value of the getting_started_step option
	 * @param {Boolean} canManage the user can manage the configuration
	 * @param {Storage} storage where "already opened" is remembered (window.localStorage)
	 * @param {Number|String} userId
	 * @return {Boolean}
	 */
	shouldAutoOpen: function(step, canManage, storage, userId) {
		if (!canManage || !(Number(step) < 99)) return false;
		var key = this.openedKey(userId);
		try {
			if (storage.getItem(key) == '1') return false;
			storage.setItem(key, '1');
			return true;
		} catch (e) {
			// storage is not available (private mode, blocked site data): never force the tab
			return false;
		}
	}
};

// One set of handlers for every checklist that is ever drawn, bound when this file loads. Binding them
// from the script that comes with each checklist left an instant, right after it was drawn, in which a
// click did nothing.
$(document)
	.on('click', '.gs-checklist .gs-step-action', function() {
		og.GettingStarted.runAction(this);
	})
	.on('click', '.gs-checklist .gs-step-skip', function() {
		og.GettingStarted.skipStep(this);
	})
	.on('click', '.gs-checklist .gs-skip', function() {
		og.GettingStarted.showSkipConfirm($(this).closest('.gs-checklist')[0], true);
	})
	.on('click', '.gs-checklist .gs-skip-cancel', function() {
		og.GettingStarted.showSkipConfirm($(this).closest('.gs-checklist')[0], false);
	})
	.on('click', '.gs-checklist .gs-finish, .gs-checklist .gs-skip-confirm-yes', function() {
		og.GettingStarted.finish(this);
	});
