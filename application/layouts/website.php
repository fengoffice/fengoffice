<?php header ("Content-Type: text/html; charset=utf-8", true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
	<title><?php echo clean(CompanyWebsite::instance()->getCompany()->getFirstName()) . ' - ' . PRODUCT_NAME ?></title>
	
	<?php $favicon_name = 'favicon.ico';
		Hook::fire('change_favicon', null, $favicon_name); ?>
	<?php echo link_tag(with_slash(ROOT_URL).$favicon_name, "rel", "shortcut icon") ?>
	<?php echo add_javascript_to_page("og/app.js") // loaded first because it's needed for translating?>
	<?php echo add_javascript_to_page(get_url("access", "get_javascript_translation")); ?>
	<?php echo add_javascript_to_page(get_url("access", "get_javascript_translation_default")); ?>
	<!--[if IE 7]>
	<?php echo stylesheet_tag("og/ie7.css"); ?>
	<![endif]-->
	<!--[if IE 8]>
	<?php echo stylesheet_tag("og/ie8.css"); ?>
	<![endif]-->
	<?php $loading_url = get_image_url("layout/loading.gif");
		Hook::fire('change_loading_img', null, $loading_url); ?>
	<?php echo meta_tag('content-type', 'text/html; charset=utf-8', true) ?>
<?php

	// Old Internet Explorer versions does not allow to import more than 32 css files, so we must use the compressed css.
	include_once ROOT . "/library/browser/Browser.php";
	$is_old_ie = Browser::instance()->getBrowser() == Browser::BROWSER_IE && Browser::instance()->getVersion() < 10;
	
	// By default use compressed css
	if (!defined('COMPRESSED_CSS')) {
		define('COMPRESSED_CSS', true);
	}
	
	$version = product_version();
	if ($is_old_ie || COMPRESSED_CSS) {
		echo stylesheet_tag("ogmin.css");
	} else {
		echo stylesheet_tag('website.css');
	}
	
	// Include plguin specif stylesheets - include all installed plugins, no matter if they they have not been activated
	foreach (Plugins::instance()->getAll() as $p) {
		/* @var $p Plugin */
		$css_file =	PLUGIN_PATH ."/".$p->getSystemName()."/public/assets/css/".$p->getSystemName().".css" ;
		if (is_file($css_file)) {
			echo stylesheet_tag(ROOT_URL."/plugins/".$p->getSystemName()."/public/assets/css/".$p->getSystemName().".css" );
			echo "\n";// exit;
		}
	}
	
	
	
	$theme = config_option('theme', DEFAULT_THEME);
	if (is_file(PUBLIC_FOLDER . "/assets/themes/$theme/stylesheets/custom.css")) {
		echo stylesheet_tag('custom.css');
	}
	$css = array();
	Hook::fire('autoload_stylesheets', null, $css);
	foreach ($css as $c) {
		echo stylesheet_tag($c);
	}

	if (defined('COMPRESSED_JS') && COMPRESSED_JS) {
		$jss = array("ogmin.js");
	} else {
		$jss = include "javascripts.php";
	}
	Hook::fire('autoload_javascripts', null, $jss);
	if (defined('USE_JS_CACHE') && USE_JS_CACHE) {
		echo add_javascript_to_page(with_slash(ROOT_URL)."public/tools/combine.php?version=$version&type=javascript&files=".implode(',', $jss));
	} else {
		foreach ($jss as $onejs) {
			echo add_javascript_to_page($onejs);
		}
	}
	$ext_lang_file = get_ext_language_file(get_locale());
	if ($ext_lang_file)	{
		echo add_javascript_to_page("extjs/locale/$ext_lang_file");
	}
	echo add_javascript_to_page("ckeditor/ckeditor.js");
	
	$html_templates = include "html_templates.php";
	foreach ($html_templates as $template) {
		include $template;
	}
	
	// Include plguin specif templates
	foreach (Plugins::instance()->getActive() as $p) {		
		$templates_file =	PLUGIN_PATH ."/".$p->getSystemName()."/application/views/html_templates.php" ;
		if (is_file($templates_file)) {
			$plugins_html_templates = include $templates_file;
			foreach ($plugins_html_templates as $pl_template) {
				include PLUGIN_PATH ."/".$p->getSystemName()."/application/views/".$pl_template;
			}
		}
	}
	
	// Include plguin specif js
	foreach (Plugins::instance()->getActive() as $p) {
		/* @var $p Plugin */
		$js_file =	PLUGIN_PATH ."/".$p->getSystemName()."/public/assets/javascript/".$p->getSystemName().".js" ;
		if (is_file($js_file)) {
			add_javascript_to_page(get_public_url("assets/javascript/".$p->getSystemName().".js", $p->getSystemName()));
			echo "\n";
		}
	}
	
	?>
	<style>
		#loading {
		    font-size: 20px;
		    left: 45%;
		    position: absolute;
		    top: 45%;
			color: #333333;
    		line-height: 150%;
		}
	</style>
</head>
<body id="body" <?php echo render_body_events() ?>>

<iframe name="_download" style="display:none"></iframe>

<div id="loading">
	<img src="<?php echo $loading_url ?>" width="32" height="32" style="margin-right:8px;vertical-align: middle;"/><?php echo lang("loading") ?>...
</div>

<div id="subWsExpander" onmouseover="clearTimeout(og.eventTimeouts['swst']);" onmouseout="og.eventTimeouts['swst'] = setTimeout('og.HideSubWsTooltip()', 2000);" style="display:none;top:10px;"></div>

<?php 
echo render_page_javascript();
echo render_page_inline_js();
$use_owner_company_logo = owner_company()->hasPicture();
$show_owner_company_name_header = config_option("show_owner_company_name_header");
?>
<!-- header -->
<div id="header">
	<div id="headerContent">
	  <table style="width:100%;"><tr><td id="left-header-cell">
		<div style="float: left;" class="header-content-left">
			<div id="logodiv" onclick="og.Breadcrumbs.resetSelection();">
				<div style="height: 55px;" id="logo_company_margin_top">
					<img src="<?php echo ($use_owner_company_logo) ? owner_company()->getPictureUrl('large') : get_product_logo_url() ?>" name="img_company_margin" id="img_company_margin" style="display: none;"/>
					<script>
						$('#img_company_margin').load(function() {
							var margin = (Ext.isIE) ? 25 : Math.round(parseInt(document.img_company_margin.height) / 2);
							$("#img_company_margin").show();
							var img_h = $("#img_company_margin").height();
							if (img_h < 55) {
								$("#img_company_margin").css('margin-top', ((55-img_h)/2)+'px')
							}
						});
					</script>
				</div>
				<div style="float: left;">
				<?php if($show_owner_company_name_header){?>
					<h1 style="padding-top:10px;line-height: 35px;"><?php echo clean(owner_company()->getObjectName()) ?></h1>
				<?php } ?>
				</div>
			</div>
		</div>
		
	  </td><td id="center-header-cell">
	
		<div id="headerBreadcrumb" class="header-breadcrumb-container">
		  <table><tr><td>
			<div class="header-breadcrumb home" onclick="og.Breadcrumbs.resetSelection();" style="display:none;"><?php echo lang('menu home')?></div>
		  </td><td>
			<div class="breadcrumb-members">
				<div class="primary-breadcrumb"></div>
				<div class="secondary-breadcrumb"></div>
			</div>
		  </td></tr></table>
		</div>
		
	  </td><td id="right-header-cell">
		
		<div class="header-content-right">			
			<ul class="options_menu">
				<li>
					<div class="gen-search">
					<?php
						// General search selector parameters
						if (!isset($genid)) $genid = gen_id();
						$container_id = $genid . '_general_search';
						$extra_param = '0';
						$search_function = 'ogSearchSelector.generalSearch';
						$select_function = 'ogSearchSelector.onGeneralSearchResultSelect';
						$search_placeholder = lang('search');
						$result_limit = '5';
						$search_minLength = 0;
						$search_delay = 1100;
						
						include get_template_path("search_selector_view", "search_selector");			
					?>
					</div>
				</li>	
			  	<li id="userboxWrapper" class="<?php echo config_option('brand_colors_texture',1)?'texture-n-1':''; ?>" onclick="showUserOptionsPanel()">
					<img src="<?php echo logged_user()->getPictureUrl(); ?>" alt="" />
					<a id="userLink" style="margin-right: 5px;" href="#" ><?php echo clean(logged_user()->getObjectName()); ?></a>	
					<div class="account"></div>										
			  	</li>			  				  
			</ul>
			<div class="clear"></div>
			<?php echo render_user_box(logged_user())?>
		</div>
		<?php Hook::fire('render_page_header', null, $ret); 
			  Hook::fire('on_page_load', 'mail', $ret);
		?>
	  </td></tr></table>
	</div>
</div>
<!-- /header -->

<!-- footer -->
<div id="footer">
	<div id="copy">
		<?php if(0 && is_valid_url($owner_company_homepage = owner_company()->getHomepage())) { 
		//FIXME Pepe getHomepage not defined
			?>
			<?php echo lang('footer copy with homepage', date('Y'), $owner_company_homepage, clean(owner_company()->getObjectName())) ?>
		<?php } else { ?>
			<?php echo lang('footer copy without homepage', date('Y'), clean(owner_company()->getObjectName())) ?>
		<?php } // if ?>
	</div>
	<?php Hook::fire('render_page_footer', null, $ret) ?>
	<div id="productSignature"><?php echo product_signature() ?></div>
</div>
<!-- /footer -->

<script>
		

// OG config options
og.hostName = '<?php echo ROOT_URL ?>';
og.sandboxName = <?php echo defined('SANDBOX_URL') ? "'".SANDBOX_URL."'" : 'false'; ?>;
og.maxUploadSize = '<?php echo get_max_upload_size() ?>';
<?php //FIXME initialWS for initialMembers
$initialWS = user_config_option('initialWorkspace');
if ($initialWS === "remember") {
	$initialWS = user_config_option('lastAccessedWorkspace', 0);
}
?>

og.initialWorkspace = '<?php echo $initialWS ?>';
<?php $qs = (trim($_SERVER['QUERY_STRING'])) ? "&" . $_SERVER['QUERY_STRING'] : "";  ?>
og.queryString = '<?php echo $_SERVER['QUERY_STRING'] ?>';

og.initialURL = '<?php echo ROOT_URL ."/?".$_SERVER['QUERY_STRING'] ?>';
<?php if (user_config_option("rememberGUIState")) { ?>
og.initialGUIState = <?php echo json_encode(GUIController::getState()) ?>;
<?php }
if (user_config_option("autodetect_time_zone", null)) {
	$now = DateTimeValueLib::now();
?>
	og.usertimezone = og.calculate_time_zone(new Date(<?php echo $now->getYear() ?>,<?php echo $now->getMonth() - 1 ?>,<?php echo $now->getDay() ?>,<?php echo $now->getHour() ?>,<?php echo $now->getMinute() ?>,<?php echo $now->getSecond() ?>));
	og.openLink(og.getUrl('account', 'set_timezone', {'tz': og.usertimezone}), {'hideLoading': true});
<?php 
} ?>
og.CurrentPagingToolbar = <?php echo defined('INFINITE_PAGING') && INFINITE_PAGING ? 'og.InfinitePagingToolbar' : 'og.PagingToolbar' ?>;
og.ownerCompany = {
	id: '<?php echo owner_company()->getId()?>',
	name: '<?php echo escape_character(clean(owner_company()->getObjectName()))?>',
	logo_url: '<?php echo (owner_company()->getPictureFile() != '' ? owner_company()->getPictureUrl() : '')?>',
	email: '<?php echo escape_character(clean(owner_company()->getEmailAddress('work'))) ?>',
	phone: '<?php echo escape_character(clean(owner_company()->getPhoneNumber('work'))) ?>',
	address: '<?php echo str_replace("\n", " ", escape_character(clean(owner_company()->getStringAddress('work')))) ?>',
	homepage: '<?php echo escape_character(clean(owner_company()->getWebpageUrl('work'))) ?>'
};
og.loggedUser = {
	id: <?php echo logged_user()->getId() ?>,
	username: <?php echo json_encode(logged_user()->getUsername()) ?>,
	displayName: <?php echo json_encode(logged_user()->getObjectName()) ?>,
	isAdmin: <?php echo logged_user()->isAdministrator() ? 'true' : 'false' ?>,
	isGuest: <?php echo logged_user()->isGuest() ? 'true' : 'false' ?>,
	tz: <?php echo logged_user()->getTimezone() ?>,
	type: <?php echo logged_user()->getUserType() ?>,
	localization: '<?php echo logged_user()->getLocale() ?>',
	can_instantiate_templates: <?php echo can_instantiate_templates(logged_user()) ? 'true' : 'false'?>,
	can_manage_tasks: <?php echo can_manage_tasks(logged_user()) ? 'true' : 'false' ?>
};
og.zipSupported = <?php echo zip_supported() ? 1 : 0 ?>;
og.hasNewVersions = <?php
	if (config_option('upgrade_last_check_new_version', false) && logged_user()->isAdministrator()) {
		echo json_encode(lang('new Feng Office version available', "#", "og.openLink(og.getUrl('administration', 'upgrade'))"));
	} else {
		echo "false";
	}
?>;
og.config = {
	'mails_per_page': <?php echo json_encode(user_config_option('mails_per_page',50)) ?>,
	'contacts_per_page': <?php echo json_encode(user_config_option('contacts_per_page',50)) ?>,
	'files_per_page': <?php echo json_encode(config_option('files_per_page', 50)) ?>,
	'days_on_trash': <?php echo json_encode(config_option("days_on_trash", 0)) ?>,
	'checkout_notification_dialog': <?php echo json_encode(config_option('checkout_notification_dialog', 0)) ?>,
	'use_time_in_task_dates': <?php echo json_encode(config_option('use_time_in_task_dates')) ?>,
	'working_days': <?php echo json_encode(user_config_option('pushUseWorkingDays')) ?>,
	'can_assign_tasks_to_companies': <?php echo json_encode(config_option('can_assign_tasks_to_companies')) ?>,
	'enable_notes_module': <?php echo json_encode(module_enabled("messages")) ?>,
	'enable_email_module': <?php echo json_encode(module_enabled("mails")) ?>,
	'enable_contacts_module': <?php echo json_encode(module_enabled("contacts")) ?>,
	'enable_calendar_module': <?php echo json_encode(module_enabled("calendar")) ?>,
	'enable_documents_module': <?php echo json_encode(module_enabled("documents")) ?>,
	'enable_tasks_module': <?php echo json_encode(module_enabled("tasks")) ?>,
	'enable_weblinks_module': <?php echo json_encode(module_enabled('weblinks')) ?>,
	'enable_time_module': <?php echo json_encode(module_enabled("time") && can_manage_time(logged_user())) ?>,
	'enable_reporting_module': <?php echo json_encode(module_enabled("reporting")) ?>,
	'use_tasks_dependencies': <?php echo json_encode(module_enabled("tasks")) ?>,
	'enabled_dimensions': Ext.util.JSON.decode('<?php echo json_encode(config_option('enabled_dimensions')) ?>'),
	'brand_colors': {
		brand_colors_head_back: '<?php echo config_option('brand_colors_head_back')?>',
		brand_colors_head_font: '<?php echo config_option('brand_colors_head_font')?>',
		brand_colors_tabs_back: '<?php echo config_option('brand_colors_tabs_back')?>',
		brand_colors_tabs_font: '<?php echo config_option('brand_colors_tabs_font')?>',
		brand_colors_texture: '<?php echo config_option('brand_colors_texture')?>'
	},
	'with_perm_user_types': Ext.util.JSON.decode('<?php echo json_encode(config_option('give_member_permissions_to_new_users'))?>'),
	'member_selector_page_size': 100,
	'currency_code': '<?php echo config_option('currency_code', '$') ?>'
};
og.preferences = {
	'viewContactsChecked': <?php echo json_encode(user_config_option('viewContactsChecked')) ?>,
	'viewUsersChecked': <?php echo json_encode(user_config_option('viewUsersChecked')) ?>,
	'viewCompaniesChecked': <?php echo json_encode(user_config_option('viewCompaniesChecked')) ?>,
	'rememberGUIState': <?php echo user_config_option('rememberGUIState') ? '1' : '0' ?>,
	'time_format_use_24': <?php echo json_encode(user_config_option('time_format_use_24')) ?>,
	'show_unread_on_title': <?php echo user_config_option('show_unread_on_title') ? '1' : '0' ?>,
	'email_polling': <?php echo json_encode(user_config_option('email_polling')) ?> ,
	'email_check_acc_errors': <?php echo json_encode(user_config_option('mail_account_err_check_interval')) ?> ,
	'date_format': <?php echo json_encode(user_config_option('date_format')) ?>,
	'date_format_tip': <?php echo json_encode(date_format_tip(user_config_option('date_format'))) ?>,
	'start_monday': <?php echo user_config_option('start_monday') ? '1' : '0' ?>,
	'draft_autosave_timeout': <?php echo json_encode(user_config_option('draft_autosave_timeout')) ?>,
	'drag_drop_prompt': <?php echo json_encode(user_config_option('drag_drop_prompt')) ?>,
	'mail_drag_drop_prompt': <?php echo json_encode(user_config_option('mail_drag_drop_prompt')) ?>,
	'access_member_after_add': <?php echo user_config_option('access_member_after_add') ? '1' : '0' ?>,
	'access_member_after_add_remember': <?php echo user_config_option('access_member_after_add_remember') ? '1' : '0' ?>,
	'listing_preferences': [],
	'breadcrumb_member_count': <?php echo user_config_option('breadcrumb_member_count') ?>,
	'can_modify_navigation_panel': <?php echo user_config_option('can_modify_navigation_panel') ? '1' : '0' ?>,
	'show_birthdays_in_calendar': <?php echo user_config_option('show_birthdays_in_calendar') ? '1' : '0' ?>
};

og.userRoles = {};
<?php $all_roles = PermissionGroups::instance()->getNonPersonalSameLevelPermissionsGroups();
	foreach ($all_roles as $role) {?>
		og.userRoles[<?php echo $role->getId()?>] = {
			code:'<?php echo $role->getName() ?>', 
			name:'<?php echo escape_character(lang($role->getName()))?>', 
			parent:'<?php echo $role->getParentId()?>',
			hint:'<?php echo escape_character(lang($role->getName().' user role description') . '&nbsp;<a href="http://www.fengoffice.com/web/user_types.php" target="_blank">'.lang('more information about user roles').'</a>') ?>'
		};
<?php } ?>

og.userTypes = {};
<?php $all_user_types = PermissionGroups::instance()->getUserTypeGroups();
	foreach ($all_user_types as $type) {?>
		og.userTypes[<?php echo $type->getId()?>] = {code:'<?php echo $type->getName() ?>', name:'<?php echo escape_character(lang($type->getName()))?>'};
<?php } ?>
og.defaultRoleByType = {};
<?php $default_roles_by_type = PermissionGroups::instance()->getDefaultRolesByType();
	foreach ($default_roles_by_type as $type => $role) {?>
		og.defaultRoleByType[<?php echo $type?>] = <?php echo $role?>;
<?php } ?>

<?php
$rolePermissions = SystemPermissions::getAllRolesPermissions();
echo "og.userRolesPermissions =".json_encode($rolePermissions).";";
$maxRolePermissions = MaxSystemPermissions::getAllMaxRolesPermissions();
echo "og.userMaxRolesPermissions =".json_encode($maxRolePermissions).";";

echo "og.defaultRoleObjectTypePermissions = ".json_encode(RoleObjectTypePermissions::getAllRoleObjectTypePermissionsInfo()).";";
echo "og.maxRoleObjectTypePermissions = ".json_encode(MaxRoleObjectTypePermissions::getAllMaxRoleObjectTypePermissionsInfo()).";";
?>

<?php 
$tabs_allowed = TabPanelPermissions::getAllRolesModules();
echo "og.tabs_allowed=".json_encode($tabs_allowed).";";
$guest_groups = PermissionGroups::instance()->getGuestPermissionGroups();
echo "og.guest_permission_group_ids = [];";
foreach ($guest_groups as $gg) {
	echo "og.guest_permission_group_ids.push(".$gg->getId().");";
}
$executive_groups = PermissionGroups::instance()->getExecutivePermissionGroups();
echo "og.executive_permission_group_ids = [];";
foreach ($executive_groups as $eg) {
	echo "og.executive_permission_group_ids.push(".$eg->getId().");";
}
?>

<?php 
$allUsers = Contacts::getAllUsers(null, true);
foreach($allUsers as $usr) {
    $usr_info = $usr->getArrayInfo();
    $allUsers_array[$usr->getId()] = $usr_info;
}
?>
og.allUsers =  <?php echo clean(str_replace('"',"'", escape_character(json_encode($allUsers_array)))) ?>;

<?php 
$object_types = ObjectTypes::getAllObjectTypes();

foreach ($object_types as $ot) {
	$types[$ot->getId()] = array(
		"name" => $ot->getName(),
		"icon" => $ot->getIconClass(),
		"type" => $ot->getType()
	);
	if ($ot->getType() == 'content_object') {
		$types[$ot->getId()]['controller'] = $ot->getObjectTypeController();
		$types[$ot->getId()]['add_action'] = $ot->getObjectTypeAddAction();
	}
}
?>
og.objectTypes =  <?php echo clean(str_replace('"',"'", escape_character(json_encode($types)))) ?>;

<?php
	$listing_preferences = ContactConfigOptions::getOptionsByCategoryName('listing preferences');
	foreach ($listing_preferences as $lp) {
		if (str_starts_with($lp->getName(), 'lp_dim_')) {
			$dcode = str_replace('lp_dim_', '', str_replace('_show_as_column', '', $lp->getName()));
			$dim = Dimensions::findByCode($dcode);
			?>og.preferences['listing_preferences']['<?php echo 'lp_dim_'.$dim->getId().'_show_as_column' ?>'] = <?php echo user_config_option($lp->getName()) ? '1' : '0'?>;<?php
		}
	} 
?>
og.breadcrumbs_skipped_dimensions = [];

Ext.Ajax.timeout = <?php echo get_max_execution_time()*1100 // give a 10% margin to PHP's timeout ?>;
og.musicSound = new Sound();
og.systemSound = new Sound();

<?php $all_dimension_associations = DimensionMemberAssociations::instance()->getAllAssociationsInfo(); ?>
og.dimension_member_associations = Ext.util.JSON.decode('<?php echo json_encode($all_dimension_associations)?>');

<?php if (!defined('DISABLE_JS_POLLING') || !DISABLE_JS_POLLING) { ?>
var isActiveBrowserTab = true;
if (Ext.isIE) {
	document.onfocusin = function () {
	  isActiveBrowserTab = true;
	};
	document.onfocusout = function () {
	  isActiveBrowserTab = false;
	};
} else {
	window.onfocus = function () {
	  isActiveBrowserTab = true;
	};
	window.onblur = function () {
	  isActiveBrowserTab = false;
	};
}

og.dimensions_check_date = new Date();

setInterval(function() {
	if (window.isActiveBrowserTab) {
		og.openLink(og.getUrl('object', 'popup_reminders'), {
			hideLoading: true,
			hideErrors: true,
			preventPanelLoad: true,
			post: {
				dims_check_date: Math.floor(og.dimensions_check_date.getTime()/1000)
			},
			callback: function(success, data) {

				//reload og.dimensions
				if (data.reload_dims) {
					ogMemberCache.reset_dimensions_cache();										
				}
			}
		});
	}
}, 60000);
<?php } ?>

og.additional_dashboard_actions = [];
<?php 
//additional buttons in dashboard objects list
$actions = array();
Hook::fire('additional_dashboard_actions', null, $actions);
$i=0;
foreach ($actions as $action) {
	$i++;
	?>
	og.additional_dashboard_actions.push(
		new Ext.Action({
			id: "add-action-<?php echo $action['id']?>",
			assoc_ot: <?php echo array_var($action, 'assoc_ot', '0')?>,
			assoc_dim: <?php echo array_var($action, 'assoc_dim', '0')?>,
			text: "<?php echo $action['name']?>",
			tooltip: "<?php echo $action['name']?>",
			cls: "x-btn-text-icon dash-additional-action",
			iconCls: "<?php echo $action['class']?>",
			handler: function() {
				<?php echo $action['onclick']?>
			},
			scope: this
		})
	);
	<?php
}
?>

<?php if (Plugins::instance()->isActivePlugin('mail')) { ?>
	og.loadEmailAccounts('view');
	og.loadEmailAccounts('edit');
	og.loggedUserHasEmailAccounts = <?php echo logged_user()->hasEmailAccounts() ? 'true' : 'false' ?>;
	og.emailFilters = {};
	og.emailFilters.classif = '<?php echo user_config_option('mails classification filter') ?>';
	og.emailFilters.read = '<?php echo user_config_option('mails read filter') ?>';
	<?php
		$acc = MailAccounts::findById(user_config_option('mails account filter'));
		if ($acc instanceof MailAccount) {
			?>
			og.emailFilters.account = '<?php echo user_config_option('mails account filter') ?>';
			og.emailFilters.accountName = '<?php echo mysql_real_escape_string($acc->getName()) ?>';
			<?php
		} else { 
			?>
			og.emailFilters.account = '';
			og.emailFilters.accountName = '';
			<?php
		}
	?>
<?php } ?>
og.lastSelectedRow = {messages:0, mails:0, contacts:0, documents:0, weblinks:0, overview:0, linkedobjs:0, archived:0};

og.menuPanelCollapsed = false;

og.dimensionPanels = [
	<?php
	$dim_obj_type_descendants = array();
	$enabled_dimensions = config_option("enabled_dimensions");
	$dimensionController = new DimensionController();
	$first = true; 
	$dimensions = $dimensionController->get_context();
	foreach ( $dimensions['dimensions'] as $dimension ):
	 	if (!in_array($dimension->getId(), $enabled_dimensions)) {
	 		continue;
	 	}
	 		
		/* @var $dimension Dimension */
		$title = $dimension->getName();
		if (!$first) echo ",";
		$first = false;
		
		if (defined('JSON_NUMERIC_CHECK')) {
			$reloadDimensions = json_encode( DimensionMemberAssociations::instance()->getDimensionsToReloadByObjectType($dimension->getId()), JSON_NUMERIC_CHECK );
		} else {
			$reloadDimensions = json_encode( DimensionMemberAssociations::instance()->getDimensionsToReloadByObjectType($dimension->getId()) );
		}
		
		?>
		{	
			reloadDimensions: <?php echo $reloadDimensions ?>,
			xtype: 'member-tree',
			id: 'dimension-panel-<?php echo $dimension->getId() ; ?>',
			lines: false,
			dimensionId: <?php echo $dimension->getId() ; ?>,
			dimensionCode: '<?php echo $dimension->getCode() ; ?>',
			dimensionOptions: '{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"}}',
			isDefault: '<?php echo (int) $dimension->isDefault() ; ?>',
			title: "<?php echo $title ?>",
			multipleSelection: <?php echo (int)$dimension->getAllowsMultipleSelection() ?>,
			isRoot: <?php echo (int) $dimension->getIsRoot(); ?>,
			requiredObjectTypes: <?php echo json_encode($dimension->getRequiredObjectTypes()) ?>,
			hidden: <?php echo (int) ! $dimension->getIsRoot(); ?>,
			isManageable: <?php echo (int) $dimension->getIsManageable() ?>,
			quickAdd: <?php echo ( intval($dimension->getOptionValue('quickAdd')) ) ? 'true' : 'false'  ?>,
			minHeight: 10
			//animate: false,
			//animCollapse: false
			
		}	
	<?php
		$dim_obj_types = DimensionObjectTypes::getObjectTypeIdsByDimension($dimension->getId());
		$dim_obj_type_descendants[$dimension->getId()] = array();
		foreach ($dim_obj_types as $ot_id) {
			$all_child_ots = DimensionObjectTypeHierarchies::getAllChildrenObjectTypeIds($dimension->getId(), $ot_id);
			$dim_obj_type_descendants[$dimension->getId()][$ot_id] = array_values($all_child_ots);
		}
	?>
	<?php endforeach; ?>
];
og.dimension_object_type_descendants = Ext.util.JSON.decode('<?php echo json_encode($dim_obj_type_descendants)?>');

og.contextManager.construct();
og.objPickerTypeFilters = [];
<?php
	$obj_picker_type_filters = ObjectTypes::findAll(array("conditions" => "`type` = 'content_object'
		AND (plugin_id IS NULL OR plugin_id = 0 OR plugin_id IN (SELECT distinct(id) FROM ".TABLE_PREFIX."plugins WHERE is_installed = 1 AND is_activated = 1 ))
		AND `name` <> 'file revision' AND name <> 'template_task' AND name <> 'template_milestone' AND `id` NOT IN (
			SELECT `object_type_id` FROM ".TabPanels::instance()->getTableName(true)." WHERE `enabled` = 0
		)  OR `type` = 'comment' OR `name` = 'milestone'"));
	
	$pg_ids = logged_user()->getPermissionGroupIds();
	if (!is_array($pg_ids) || count($pg_ids) == 0) $pg_ids = array(0);
	
	foreach ($obj_picker_type_filters as $type) {
		if (! $type instanceof  ObjectType ) continue ;
		/* @var $type ObjectType */
		$linkable = $type->getIsLinkableObjectType();
		if ($linkable) {
			$tab_ids = DB::executeAll("SELECT id FROM ".TABLE_PREFIX."tab_panels WHERE object_type_id = ".$type->getId());
			if (count($tab_ids) > 0) {
				$tab_id = $tab_ids[0]['id'];
				if (!TabPanelPermissions::isModuleEnabled($tab_id, implode(',', $pg_ids))) {
					continue;
				}
			}
?>
			og.objPickerTypeFilters.push({
				id: '<?php echo $type->getName() ?>',
				name: '<?php echo lang($type->getName()) ?>',
				type: '<?php echo $type->getName() ?>',
				filter: 'type',
				iconCls: 'ico-<?php echo $type->getIcon() ?>'
			});
<?php
		}
	}
?>


	og.additional_list_columns = [];
	og.additional_on_dimension_object_click = [];
	og.dimension_object_types = [];
<?php
	$dimension_object_types = ObjectTypes::findAll(array('conditions' => "`type` IN ('dimension_object', 'dimension_group')"));
	foreach ($dimension_object_types as $dot) { ?>
		og.dimension_object_types[<?php echo $dot->getId()?>] = '<?php echo $dot->getName()?>';
<?php
	}
	foreach (Plugins::instance()->getActive() as $p) {
		$js_code = 'if (og.'.$p->getName().' && og.'.$p->getName().'.init) og.'.$p->getName().'.init();'."\n";
		echo $js_code;
	} 
?>

og.dimension_object_type_contents = {};
<?php 
	$dotcs = DimensionObjectTypeContents::findAll();
	foreach ($dotcs as $dotc) { /* @var $dotc DimensionObjectTypeContent */?>
		var dim = <?php echo $dotc->getDimensionId() ?>;
		var dot = <?php echo $dotc->getDimensionObjectTypeId() ?>;
		var cot = <?php echo $dotc->getContentObjectTypeId() ?>;
		if (!og.dimension_object_type_contents[dim]) og.dimension_object_type_contents[dim] = [];
		if (!og.dimension_object_type_contents[dim][dot]) og.dimension_object_type_contents[dim][dot] = [];
		og.dimension_object_type_contents[dim][dot][cot] = {required:<?php echo $dotc->getIsRequired()?"1":"0"?>, multiple:<?php echo $dotc->getIsMultiple()?"1":"0"?>};
<?php
	} 
?>

function showUserOptionsPanel() {
    $('div.user-box-actions').slideToggle();  
}

og.getting_started_step = '<?php echo config_option('getting_started_step') ?>';

$(document).ready(function() {

	og.createBrandColorsSheet(og.config.brand_colors);
	
	var logo_link = document.getElementById("change-logo-link");
	if (logo_link) {
		logo_link.onclick = function(e){
			if(e && e.stopPropagation) {
				e.stopPropagation();
			} else {
				e = window.event;
				e.cancelBubble = true;
			}
		}
	}

	og.custom_properties_by_type = {};
	og.openLink(og.getUrl('object', 'get_cusotm_property_columns'), {
		callback: function(success, data){
			if (typeof data.properties != 'undefined' && !(data.properties instanceof Array )) {
				og.custom_properties_by_type = data.properties;
			}
		}
	});

	og.openLink(og.getUrl('dimension', 'load_dimensions_info'), {
		hideLoading: true,
		hideErrors: true,
		preventPanelLoad: true,
		callback: function(s, d) {
			if (d.dim_names) {
				og.dimensions_info = d.dim_names;
			}
		}
	});

	var all_steps = 99;
	if (og.getting_started_step < all_steps) {
		og.more_panel_int = setInterval(function() {
			var more_panel = Ext.getCmp('more-panel');
			if (more_panel) {
				var tp = Ext.getCmp('tabs-panel');
				if (tp) tp.setActiveTab(more_panel);
				clearInterval(og.more_panel_int);
			}
		}, 500);
	}

	setTimeout(function() {
		og.Breadcrumbs.resizeHeaderBreadcrumbs();
	}, 250);
	
	$(window).resize(function() {
		og.Breadcrumbs.resizeHeaderBreadcrumbs();
		og.checkAndAdjustTabsSize();
	});
});

<?php
$default_currency = Currencies::getDefaultCurrencyInfo();
if (is_array($default_currency) && count($default_currency) > 0) {
	?>og.default_currency = Ext.util.JSON.decode('<?php echo json_encode($default_currency)?>');<?php
} 
?>

</script>
<?php include_once(Env::getLayoutPath("listeners"));?>
	<div style="height:100%;width:100%;display:none;position:fixed;top:0px;left:0px;z-index: 2000;overflow-y: auto;" id="modal-forms-container"></div>

	<div id="quick-form" > 
            <div id="close_text" style="float: right; cursor: pointer;height: 12px;position: absolute;right: 19px;top: 2px;"><a href="#" onclick="$('.close').click();"><?php echo lang('close')?></a></div>
            <div id="close_ico" class="close" style="float: right;"></div>
            <div class="form-container"></div>
	</div>
</body>
</html>

<?php Hook::fire('page_rendered', null, $ret); ?>

