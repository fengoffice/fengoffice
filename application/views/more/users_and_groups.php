<?php
$genid = gen_id();

//$user_type_cond = "AND user_type IN (SELECT id FROM ".TABLE_PREFIX."permission_groups WHERE type='roles' AND name IN ('Super Administrator','Administrator','Manager','Executive'))";
$user_type_cond = "";
$internal_users = Contacts::instance()->getAllUsers($user_type_cond, true, 'last_activity DESC');
/*
$user_type_cond = "AND user_type>0 AND user_type IN (SELECT id FROM ".TABLE_PREFIX."permission_groups WHERE type='roles' AND name IN ('Collaborator Customer','Internal Collaborator','External Collaborator'))";
$collaborators = Contacts::instance()->getAllUsers($user_type_cond, false, 'last_activity DESC');

$user_type_cond = "AND user_type>0 AND user_type IN (SELECT id FROM ".TABLE_PREFIX."permission_groups WHERE type='roles' AND name IN ('Guest Customer','Guest','Non-Exec Director'))";
$guests = Contacts::instance()->getAllUsers($user_type_cond, false, 'last_activity DESC');
*/
$exe_user_type = PermissionGroups::findOne(array('conditions' => "type='roles' AND name='Executive'"))->getId();
$col_user_type = PermissionGroups::findOne(array('conditions' => "type='roles' AND name='Internal Collaborator'"))->getId();
$guest_user_type = PermissionGroups::findOne(array('conditions' => "type='roles' AND name='Guest'"))->getId();
?>

<div class="user-groups-container">
	<div class="title">
		<div class="titletext"><?php echo lang('users groups and permissions')?></div>
		<button title="<?php echo lang('close')?>" style="float:left; margin: -10px 0 0 15px;" class="add-first-btn" onclick="og.save_user_and_groups_changes(this)">
			<img src="public/assets/themes/default/images/layout/close16.png" style="margin-bottom:-1px;">&nbsp;<?php echo lang('close')?>
		</button>
		<div class="clear"></div>
	</div>
	
	<div class="user-groups-section">
		<h1><?php echo lang('users') ?></h1>
		<div class="section-description desc">
			<?php echo lang('full access users desc')?><br />
			<?php echo lang('collaborators desc', '<br />')?><br />
			<?php echo lang('guests desc', '<br />')?><br />
		</div>
		<div class="section-content section1">
			<ul>
		<?php
		$count = 0;
		foreach ($internal_users as $user) {?>
			<li class="user <?php echo ($user->getDisabled() ? "disabled" : "")?>" style="<?php echo $count > 10 ? "display:none;" : ""?>">
				<a href="<?php echo $user->getCardUrl()?>" class="internalLink" target="<?php echo array_var($_REQUEST, 'current')?>"><div class="wrapper">
				<?php if ($user->hasPicture()){ ?>
					<div class="coViewIconImage"><img src="<?php echo $user->getPictureUrl() ?>" alt="<?php echo clean($user->getObjectName()) ?> picture" /></div>
				<?php } else { ?>
					<div class="coViewIconImage ico-large-contact"></div>
				<?php } ?>
					<div class="user-name-container">
						<?php echo $user->getObjectName() . ($user->getJobTitle() == "" ? "" : " (" .$user->getJobTitle().")")?>
						<?php if ($user->getDisabled()) echo '<span class="desc"> - '.lang('disabled') . '</span>'?>
						<div class="desc"><?php echo $user->getUserTypeName()?></div>
					</div>
					<div class="clear"></div>
				</div></a>
			</li>
		<?php
			$count++;
		} ?>
			</ul>
		<?php if ($count > 10) { ?>
			<div class="clear more-users-msg">
				<span class="desc"><?php echo lang('there are x users more', $count-10);?></span>
				<a href="#" onclick="$('.section-content.section1 li.user').show(); $(this).parent().hide(); return false;"><?php echo lang('view all')?></a><br />
			</div>
		<?php } ?>
			<div class="clear"></div>
			<button title="<?php echo lang('add new internal user')?>" class="add-first-btn blue" onclick="og.openLink(og.getUrl('contact','add',{is_user:1, user_type:'<?php echo $exe_user_type?>'}));">
				<img src="public/assets/themes/default/images/16x16/add.png">&nbsp;<?php echo lang('add user')?>
			</button>
			<div class="clear"></div>
		</div>
	</div>

<?php if (false && !$only_full_users) { ?>

	<div class="user-groups-section">
		<h1><?php echo lang('collaborators') ?></h1>
		<div class="section-description desc"><?php echo lang('collaborators desc', '<br />')?>
			<a href="#" onclick="document.getElementById('<?php echo $genid?>col_desc2').style.display=''; this.style.display='none'; return false;" style="font-style:normal;color:#003562;"><?php echo lang('read more')?></a>
		</div>
		<div class="section-description desc" style="display:none;" id="<?php echo $genid?>col_desc2"><?php echo lang('collaborators desc 2', '<br />')?></div>
		<div class="section-content section2">
			<ul>
		<?php
	if (is_array($collaborators) && count($collaborators) > 0) {
		foreach ($collaborators as $user) {?>
			<li class="user">
			<?php if ($user->hasPicture()){ ?>
				<div class="coViewIconImage"><img src="<?php echo $user->getPictureUrl() ?>" alt="<?php echo clean($user->getObjectName()) ?> picture" /></div>
			<?php } else { ?>
				<div class="coViewIconImage ico-large-contact"></div>
			<?php } ?>
				<div class="user-name-container"><a href="<?php echo $user->getCardUrl()?>" class="internalLink" target="<?php echo array_var($_REQUEST, 'current')?>">
					<?php echo $user->getObjectName() . ($user->getCompanyId() > 0 ? " (" .$user->getCompany()->getObjectName().")" : "")?>
					</a>
					<div class="desc"><?php echo $user->getUserTypeName()?></div>
				</div>
				<div class="clear"></div>
			</li>
		<?php 
		}
	} else {
		?><li class="no-user-message"><?php echo lang('no collaborators have been added yet') ?></li><?php
	} ?>
			</ul>
			<div class="clear"></div>
			<button title="<?php echo lang('add new collaborator user')?>" class="add-first-btn" onclick="og.openLink(og.getUrl('contact','add',{is_user:1, user_type:'<?php echo $col_user_type?>'}));">
				<img src="public/assets/themes/default/images/16x16/add.png">&nbsp;<?php echo lang('add user')?>
			</button>
			<div class="clear"></div>
		</div>
	</div>
	
	<div class="user-groups-section">
		<h1><?php echo lang('guests') ?></h1>
		<div class="section-description desc"><?php echo lang('guests desc', '<br />')?></div>
		<div class="section-content section2">
			<ul>
		<?php
	if (is_array($guests) && count($guests) > 0) {
		foreach ($guests as $user) {?>
			<li class="user">
			<?php if ($user->hasPicture()){ ?>
				<div class="coViewIconImage"><img src="<?php echo $user->getPictureUrl() ?>" alt="<?php echo clean($user->getObjectName()) ?> picture" /></div>
			<?php } else { ?>
				<div class="coViewIconImage ico-large-contact"></div>
			<?php } ?>
				<div class="user-name-container"><a href="<?php echo $user->getCardUrl()?>" class="internalLink" target="<?php echo array_var($_REQUEST, 'current')?>">
					<?php echo $user->getObjectName() . ($user->getCompanyId() > 0 ? " (" .$user->getCompany()->getObjectName().")" : "")?>
					</a>
					<div class="desc"><?php echo $user->getUserTypeName()?></div>
				</div>
				<div class="clear"></div>
			</li>
		<?php 
		}
	} else {
		?><li class="no-user-message"><?php echo lang('no guests have been added yet') ?></li><?php
	} ?>
			</ul>
			<div class="clear"></div>
			<button title="<?php echo lang('add new collaborator user')?>" class="add-first-btn" onclick="og.openLink(og.getUrl('contact','add',{is_user:1, user_type:'<?php echo $guest_user_type?>'}));">
				<img src="public/assets/themes/default/images/16x16/add.png">&nbsp;<?php echo lang('add user')?>
			</button>
			<div class="clear"></div>
		</div>
	</div>
	
<?php } ?>
	

<?php
$groups = PermissionGroups::getNonRolePermissionGroups();
$gr_lengths = array();
foreach ($groups as $gr) {
	$count = ContactPermissionGroups::count("`permission_group_id` = ".$gr->getId());
	$gr_lengths[$gr->getId()] = $count;
}
?>
	<div class="user-groups-section">
		<h1><?php echo lang('groups') ?></h1>
		<div class="section-description desc"><?php echo lang('groups desc', '<br />')?></div>
		<div class="section-content section3">
			<ul>
		<?php
	if (is_array($groups) && count($groups) > 0) {
		foreach ($groups as $group) {?>
			<li class="user">
			  <a href="<?php echo $group->getEditUrl()?>" class="internalLink" target="<?php echo array_var($_REQUEST, 'current')?>"><div class="wrapper">
				<div class="coViewIconImage ico-large-group"></div>
				<div class="user-name-container">
					<?php echo $group->getName() ?>
					<div class="desc"><?php echo $gr_lengths[$group->getId()] . ' ' . lang('users') ?></div>
				</div>
				<div class="clear"></div>
			  </div></a>
			</li>
		<?php 
		}
	} else {
		?><li class="no-user-message"><?php echo lang('no groups in company') ?></li><?php
	} ?>
			</ul>
			<div class="clear"></div>
			<button title="<?php echo lang('add new group')?>" class="add-first-btn blue" onclick="og.openLink(og.getUrl('group','add'));">
				<img src="public/assets/themes/default/images/16x16/add.png" />&nbsp;<?php echo lang('add new group')?>
			</button>
			<div class="clear"></div>
		</div>
	</div>

<?php
if (false) {
$companies = Contacts::instance()->findAll(array('conditions' => 'is_company=1 AND EXISTS (SELECT c.object_id FROM '.TABLE_PREFIX.'contacts c WHERE c.user_type>0 AND c.company_id=o.id)'));
?>
	<div class="user-groups-section">
		<h1><?php echo lang('companies with users') ?></h1>
		<div class="section-content section4">
			<ul>
		<?php
		foreach ($companies as $comp) {?>
			<li class="user">
			  <a href="<?php echo $comp->getCardUrl()?>" class="internalLink" target="<?php echo array_var($_REQUEST, 'current')?>"><div class="wrapper">
			<?php if ($comp->hasPicture()){ ?>
				<div class="coViewIconImage"><img src="<?php echo $comp->getPictureUrl() ?>" alt="<?php echo clean($comp->getObjectName()) ?> picture" /></div>
			<?php } else { ?>
				<div class="coViewIconImage ico-large-company"></div>
			<?php } ?>
				<div class="company-name-container">
					<?php echo $comp->getObjectName() ?>
				</div>
				<div class="clear"></div>
			  </div></a>
			</li>
		<?php 
		} ?>
			</ul>
			<div class="clear"></div>
			<button title="<?php echo lang('add new company')?>" class="add-first-btn" onclick="og.openLink(og.getUrl('contact','add_company'));">
				<img src="public/assets/themes/default/images/16x16/add.png">&nbsp;<?php echo lang('add new company')?>
			</button>
			<div class="clear"></div>
	</div>
<?php } ?>
</div>

<script>
	og.save_user_and_groups_changes = function(btn) {
		og.openLink(og.getUrl('more', 'set_getting_started_step', {'step': 3}), {
			callback: function(success, data) {
				og.goback(btn);
			}
		});
	}
	
	$(function(){
		$(".user-groups-container").parent().css('backgroundColor', 'white');
	});
</script>