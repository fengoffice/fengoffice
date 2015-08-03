<?php 
  // Set page title
  require_javascript("og/ObjectSubtypesFunctions.js");
  set_page_title(lang('object subtypes'));  
  $genid = gen_id();
  
  if (!is_array($managers)) $managers = array();
  if (!is_array($co_types)) $co_types = array();
?>
<script>
	var genid = '<?php echo $genid ?>';
</script>

<div style="height:100%;background-color:white">
  <form class="internalForm" style='height:100%;background-color:white' action="<?php echo get_url("administration", "object_subtypes") ?>" method="post">
  <div class="adminHeader">
  	<div class="adminTitle"><table style="width: 535px;"><tr><td><?php echo lang('object subtypes') ?>
  	</td><td style="text-align:right;"><?php echo submit_button(lang('save changes')) ?></td></tr></table></div>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
  
  
	<?php foreach ($managers as $title => $manager) { ?>
	<label><?php echo lang($title) ?></label>
	<div id="<?php echo $genid . $manager?>_container">
		<?php 
			$man_co_types = array_var($co_types, $manager, array()); 
			if (is_array($man_co_types) && count($man_co_types) > 0) $display = "";
			else $display = "display:none;"
		?>
		<table style="width:400px;border:1px solid #CCC;<?php echo $display ?>" id="<?php echo $genid . $manager?>_table">
			<tr><th><?php echo lang('name') ?></th><th></th></tr>
		<?php foreach ($man_co_types as $co_type) { ?>
			<?php echo "<script>og.addObjectSubtype('$genid', {$co_type->getId()}, '{$co_type->getName()}', '$manager', true);</script>" ?>
		<?php } ?>
		</table>
		<div id="<?php echo $genid ?>actions">
			<a href="#" class="link-ico ico-add" onclick="og.addObjectSubtype('<?php echo $genid ?>', 0, '', '<?php echo $manager ?>', false);"><?php echo lang('add object subtype')?></a>
		</div>
  	</div>
  	
  	<?php } ?>

	<?php echo submit_button(lang('save changes')) ?>
  </div>
  </form>
</div>
