<?php 
$show_help_option = user_config_option('show_context_help'); 
if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_documents_widget_context_help', true, logged_user()->getId()))) {
	render_context_help($this, 'chelp documents widget', 'documents_widget');
} ?>
  
<div style="padding:10px">
<table id="dashTableDocuments" style="width:100%">
<?php $c = 0;
	foreach ($documents as $document){ $c++;?>
	<tr class="<?php echo $c % 2 == 1? '':'dashAltRow'; echo ' ' . ($c > 5? 'dashSMDC':''); ?>" style="<?php echo $c > 5? 'display:none':'' ?>">
	<td width=18>
		<div class="db-ico ico-unknown ico-<?php echo str_replace(".", "_", str_replace("/", "-", $document->getTypeString()))?> ico-ext-<?php echo pathinfo($document->getFilename(), PATHINFO_EXTENSION)?>"></div>
		
	</td>
	<td style="padding-left:2px">
	<?php if ($document->isCheckedOut()) {?>
			<div class="db-ico ico-unknown ico-locked" style="padding-left:16px;display:inline" title="<?php echo lang('checked out by') . " " . $document->getCheckedOutBy()->getObjectName()?>">&nbsp;</div>
	<?php } // if ?>
	<?php 
		$dws = $document->getWorkspaces(logged_user()->getWorkspacesQuery());
		$projectLinks = array();
		foreach ($dws as $ws) {
			$projectLinks[] = $ws->getId();
		}
		echo '<div style="padding-right:0px;display:inline">' . '<span class="project-replace">' . implode(',',$projectLinks) . '</span></div>';?>
	<a class="internalLink" href="<?php echo get_url('files','file_details', array('id' => $document->getId()))?>"
		title="<?php echo lang('message posted on by linktitle', format_datetime($document->getCreatedOn()), clean($document->getCreatedByDisplayName())) ?>">
	<?php echo clean($document->getFilename())?>
	</a></td>
	<td style="text-align:right">
	<?php if ($document->isModifiable() && $document->canEdit(logged_user())){ ?>
		<a class="internalLink"  href="<?php echo $document->getModifyUrl()?>"><?php echo lang('edit') ?></a>
	<?php } ?></td></tr>
<?php } // foreach ?>
	<?php if ($c >= 10) {?>
		<tr class="dashSMDC" style="display:none"><td></td>
		<td style="text-align:right"><a href="#" onclick="Ext.getCmp('tabs-panel').activate('documents-panel');"><?php echo lang('show all') ?>...</a>
		</td></tr>
	<?php } ?>
</table>
<?php if ($c > 5) { ?>
<div id="dashSMDT" style="width:100%; text-align:right">
	<a href="#" onclick="og.hideAndShowByClass('dashSMDT', 'dashSMDC', 'dashTableDocuments'); return false;"><?php echo lang("show more") ?>...</a>
</div>
<?php } // if ?>
</div>