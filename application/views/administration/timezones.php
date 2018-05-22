<?php $genid = gen_id();?>
<div id="<?php echo $genid ?>adminContainer" class="adminGroups">

<div class="coInputHeader">
<table><tr><td>
  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo lang('timezones') ?>
	</div>
  </div>
  <div class="desc">
	<?php echo lang('timezones admin desc') ?>
  </div>
  <div class="clear"></div>
</td><td>
	<?php echo submit_button(lang('save'), 's', array('style' => 'margin-top:0;', 'class' => 'blue', 'onclick' => '$("#'.$genid.'_submit_btn").click();'))?>	
</td></tr></table>
</div>
	
<div class="page tab-manager adminMainBlock coInputMainBlock">
	<form method = "POST" action="<?php echo get_url("administration" , "timezones_submit" )?>" >
	
		<div style="margin:15px 0;">
			<h1 style="border:0px none; margin-bottom:0; padding-bottom:5px;"><?php echo lang('default timezone') ?></h1>
			<?php echo timezone_selector('default_timezone', config_option('default_timezone'));?>
			
			<div class="clear"></div>
		</div>
		
		
		<h1 style="border:0px none; margin-bottom:0; padding-bottom:5px;"><?php echo lang('timezone options') ?></h1>
		<table style="border: 1px solid #D7E5F5; width: auto; margin: 0px;">
			<tr>
				<th><?php echo lang("country")?></th>
				<th><?php echo lang("timezone")?></th>
				<th><?php echo lang("gmt offset")?></th>
				<th><?php echo lang("gmt dst offset")?></th>
				<th><?php echo lang("using dst")?></th>
			</tr>
			<?php 
			$altRow = 'altRow';
			foreach ( $grouped_time_zones as $code => $zones ) {
				$altRow = $altRow == '' ? 'altRow' : '';
				
				$first = true;
				foreach ( $zones as $zone ) {
					$formatted = Timezones::getFormattedDescription($zone, true);
					$formatted_name = $formatted['name'];
					$formatted_offset = $formatted['offset'];
					$formatted_dst_offset = $formatted['dst_offset'];
				?>
					<tr class="<?php echo $altRow ?>">
						<td><?php echo $first ? array_var($countries, $code) : ""; ?></td>
						
						<td><?php echo $formatted_name ?></td>
						<td style="min-width: 100px;"><?php echo $formatted_offset ?></td>
						<td style="min-width: 100px;"><?php echo $formatted_dst_offset ?></td>
							
						<td style="text-align:center;"><?php
						if ($zone['has_dst']) { ?>
							<input name="tz_dst_<?php echo $zone['id'] ?>" <?php echo $zone['using_dst'] ? "checked='checked'" : "" ?> 
								type="checkbox" onchange="og.onAdminTzDstChange(this, '<?php echo $genid?>', '<?php echo $zone['id'] ?>')"/>
								
							<input name="timezones[<?php echo $zone['id'] ?>][using_dst]" value="<?php echo $zone['using_dst'] ? "1" : "0" ?>" 
								type="hidden" id="<?php echo $genid."tz_dst_".$zone['id']; ?>"/>
						<?php
						}
						?></td>
					</tr>
			<?php
					$first = false;
				} ?>
		<?php } ?>
		</table>
		<?php echo submit_button(lang('save'), 's', array('id' => $genid.'_submit_btn'))?>
	</form>
</div>
<script>
og.onAdminTzDstChange = function(checkbox, genid, zone_id) {
	var val = $(checkbox).attr('checked') == 'checked' ? 1 : 0;
	$("#"+genid+"tz_dst_"+zone_id).val(val);
}
</script>