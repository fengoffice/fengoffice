
<div class="clear"></div>

<div class="listing-admin">

	<div class="coInputHeader">
	  <div class="coInputHeaderUpperRow">
		<div class="coInputTitle">
			<?php echo lang('token external list') ?>
		</div>
		<div class="desc"><?php echo lang('property groups desc') ?></div>
	  </div>
	</div>
	
	<div class="coInputMainBlock adminMainBlock">
	
		<a href="#" class="link-ico ico-add" onclick="og.render_modal_form('', {c:'account', a:'add_token', params: {user_id:<?php echo $user->getId() ?>}});">
			<?php echo lang('add new external token') ?>
		</a>
		<br />
				
		<table class="bordered property-groups">
			<tr class="header">
				<th><?php echo lang('token title') ?></th>
				<th><?php //echo lang('token external type')?></th>
				<th><?php echo lang('token external key')?></th>
				<th><?php echo lang('token external name')?></th>
				<th><?php //echo lang('token expire')?></th>
				<th><?php echo lang('actions')?></th>
			</tr>
			
	<?php
		$cls = "altRow";
		if ($external_tokens) {
			$cls = $cls == "" ? 'alt' : "";
	?>
			<tbody class="<?php echo $cls ?>">

			<?php 
				    foreach ($external_tokens as $token) {
			?>
					<tr class="prop-data">
						<td class="prop-name" id="prop-name"><?php echo $token->getToken() ?></td>
						<td><?php //echo $token->getType() ?></td>
						<td><?php echo $token->getExternalKey() ?></td>
						<td><?php echo $token->getExternalName() ?></td>
						<td><?php //echo $token->getExpiredDate() ?></td>
						<td>
							<a onclick="og.render_modal_form('', {c:'account', a:'add_token', params:{user_id:<?php echo $user->getId() ?>,token_id:<?php echo $token->getId()?>,action:'edit'}}); return false;" class="link-ico ico-edit" href="#">&nbsp;</a>
							<a class="link-ico ico-delete" data-token_id="<?php echo $token->getId() ?>" href="#">&nbsp;</a>
						</td>
					</tr>
			<?php 
					}
				
		      ?>

			</tbody>
	<?php } ?>
	
		</table>
		
	</div>
	
</div>

	</div>
</div>




<style>
.adminMainBlock table.bordered.property-groups .object-type td {

}
.adminMainBlock table.bordered.property-groups td.message,
.adminMainBlock table.bordered.property-groups td.prop-name,
.adminMainBlock table.bordered.property-groups td.link {
	padding-left: 30px;
}
</style>

<script type="text/javascript">
$( document ).ready(function() {

    $(".ico-delete").one('click', function (event) {
    	event.preventDefault();
        //do something
        $(".ico-delete").prop('disabled', true);
        
        og.openLink( og.makeAjaxUrl('<?php echo $user->getDeleteExternalTokensUrl() ?>',{token_id: $(this).attr("data-token_id")}));
    });

});

</script>

