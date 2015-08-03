<div class="user-box-actions" style="display: none;">
	<ul>
		<li>
			<img src="<?php echo $_userbox_user->getPictureUrl()?>" align="left" />
			<h2><?php echo clean($_userbox_user->getObjectName()) ?></h2>
			<p><?php echo clean($_userbox_user->getJobTitle()) ?></p>
		</li>
		<li class="clear"></li>
		<li class="line-top"></li>			
		<?php 
		foreach ($_userbox_crumbs as $crumb) {
			$onclick = isset($crumb['onclick']) ? $crumb['onclick'] : '';
			echo '<li><a';
			if (isset($crumb['id'])) echo ' id="' . $crumb['id'] .'"';
			if (isset($crumb['target'])) echo ' target="' . $crumb['target'] .'"';
			echo ' onclick="$(\'div.user-box-actions\').fadeOut(\'fast\');'.$onclick.'" href="' . array_var($crumb, 'url', '') . '">';
			echo array_var($crumb, 'text', '');
			echo '</a></li>';
		} 
		?>
		<li class="line-top">
			<a href="#" target="_self" onclick="window.location.href='<?php echo get_url('access', 'logout') ?>'"><?php echo lang('logout') ?></a>
		</li>
	</ul>
</div>