<ul>
	<?php foreach ($config_form_data['plugins_available'] as $plg ): ?>
	<li>
		<input type="checkbox"  checked="checked" id="<?php echo array_var($plg,'name') ?>" name="config_form[plugins][]" value="<?php echo array_var($plg,'name') ?>" /> 
		<label for="<?php echo array_var($plg,'name') ?>"><?php echo ucfirst(array_var($plg,'name')) ?></label> 
		<span class="plugin-description" ><?php echo array_var($plg, 'description' )?> </span>
	</li>
	<?php endforeach; ?>
</ul>