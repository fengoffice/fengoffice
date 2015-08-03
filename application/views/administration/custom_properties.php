<?php 
  // Set page title
  require_javascript("og/CustomPropertyFunctions.js");
  set_page_title(lang('custom properties'));  
  $genid = gen_id();
?>

<div style="height:100%;background-color:white">
<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo lang('custom properties') ?>
	</div>
  </div>

</div>
	
<div class="coInputMainBlock adminMainBlock">
  
  <?php echo label_tag(lang('select object type'), 'objectTypeSel');?>
  <?php echo select_box('objectTypeSel', $object_types, array('id' => 'objectTypeSel' ,'onchange' => 'og.objectTypeChanged("'.$genid.'")', 'style' => 'width:250px;')) ?>
  <hr/>
  
  <form class="internalForm" style='height:100%;background-color:white' action="<?php echo get_url("administration", "custom_properties") ?>" method="post" onsubmit="return og.validateCustomProperties('<?php echo $genid ?>');">
  	<input type="hidden" name="objectType" id="objectType"/>
  	<div id="<?php echo $genid?>">
	</div>
	<br/>
	<div id="CPactions<?php echo $genid ?>" style="display:none;">
	<a href="#" class="link-ico ico-add" onclick="og.addCustomProperty('<?php echo $genid ?>', null)"><?php echo lang('add custom property')?></a>
	<br/>
	<?php echo submit_button(lang('save changes')) ?>
	</div>
  </form>  
  
  <script>
  	og.loadCustomPropertyFlags();
  </script>
  
  </div>
</div>