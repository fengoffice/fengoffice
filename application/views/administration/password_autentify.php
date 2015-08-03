<?php
  set_page_title(lang('autentify password title'));
?>
<div class="adminConfiguration" style="height:100%;background-color:white">
	<div class="adminHeader">
		<div class="adminTitle"><?php echo lang('autentify password title') ?></div>
	</div>
	  <div class="adminSeparator"></div>
	  <div class="adminMainBlock">
	  		<p> <?php echo lang('autentify password desc') ?></p>
		  <div style="border: 3px double rgb(204, 204, 204); padding: 10px; margin-left: 20px; margin-top: 20px; width: 208px;">
			<form id="autentification-form" class="internalForm" method="post" action="<?php echo get_url('administration','password_autentify') ?>" >
				<input type="hidden" name="url" value="<?php echo $url ?>" />
				<input type="hidden" name="userName" value="<?php echo logged_user()->getUsername() ?>" />
				<label>
					<?php echo lang('user')?>:
				</label>
				<p style=" cursor:default; border: solid 1px #ccc; padding: 2px;"> <?php echo logged_user()->getUsername() ?></p>
				<br/>
				<label><?php echo lang('password')?>:</label><input type="password" name ="enetedPassword" value="" />
				<br/>
				<button class="submit" type="submit" ><?php echo lang('login')?></button>
			</form> 
		</div>
	</div>
</div>
