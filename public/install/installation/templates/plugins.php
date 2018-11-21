<ul>
	<?php 

	   //After first failure this does not work.
	   //Needs improvement to handle the situation where there is an issue with connecting to the DB the first time
	   $plugins = $config_form_data['plugins_available'] ? $config_form_data['plugins_available'] : array();
	   foreach ($plugins as $plg ):
	?>
	<li>
		<input type="checkbox" id="<?php echo array_var($plg,'name') ?>" name="config_form[plugins][]" value="<?php echo array_var($plg,'name') ?>" />
		<label for="<?php echo array_var($plg,'name') ?>"><?php echo ucfirst(array_var($plg,'name')) ?></label> 
		<span class="plugin-description" ><?php echo array_var($plg, 'description' )?> </span>
	</li>
	<?php endforeach; ?>
</ul>