<?php
  require_javascript("og/CustomPropertyFunctions.js");
  $genid = gen_id();
?>

<style>
.custom-properties-admin ul.object-type-list li {
	padding: 8px;
}
.custom-properties-admin ul.object-type-list li a {
	text-decoration: underline;
}
.custom-properties-admin .cp-admin-container {
	float: left;
	width: 49%;
	border-right: 1px dotted #ccc;
}
.custom-properties-admin .cp-admin-container.member-cps-container {
	
}
</style>

<div class="custom-properties-admin">

	<div class="cp-admin-container">
	
		<div class="coInputHeader">
		  <div class="coInputHeaderUpperRow">
			<div class="coInputTitle">
				<?php echo lang('object custom properties') ?>
			</div>
		  </div>
		</div>
		
		<div class="coInputMainBlock adminMainBlock">
		  
			<ul class="object-type-list">
			<?php $cls = "odd";
				foreach ($ordered_object_types as $id => $name) {
					$cls = $cls == "even" ? 'odd' : "even";
					$url = get_url('administration', 'list_custom_properties_for_type', array('id'=>$id));
			?>
				<li class="<?php echo $cls ?>">
					<a href="<?php echo $url ?>"><?php echo $name ?></a>
				</li>
				
			<?php } ?>
			</ul>
		  
		</div>
	
	</div>

<?php $null=null; Hook::fire('custom_properties_admin_sections', null, $ret)?>

</div>
<div class="clear"></div>