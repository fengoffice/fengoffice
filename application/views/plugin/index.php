<div class="page plugin-manager">
	<div style="display:none" class="error"></div>
	<h1 class="title">Plugin Manager</h1> 
	<table  class="plugin-list">
		<thead>
			<tr>
				<th class="manage-column column-name" id="name" scope="col">Plugin</th>
				<th class="manage-column column-description" id="description" scope="col">Description</th>
				<th class="dependences" id="dependences" >Dependences</th>
				<th class="column-status" >Status</th>
			</tr>
		</thead>
	
		<tfoot>
			<tr>
				<th style="" class="manage-column column-name" scope="col">Plugin</th>
				<th style="" class="manage-column column-description" scope="col">Description</th>	
				<th class="dependences" id="dependences" >Dependences</th>
				<th class="column-status" >Status</th>
			</tr>
		</tfoot>
	
		<tbody id="plugin-list">
		<?php foreach ($plugins as $plugin): $metadata = $plugin->getMetadata(); /* @var $plugin Plugin */ ?>
			<tr 
				class="plugin-row <?php echo ($plugin->isActive())?"active":"inactive"?> <?php echo ($plugin->isInstalled())?"installed":"uninstalled"?>" 
				id="<?php echo $plugin->getId()?>"
			>

				<td class="plugin-title">
					<strong><?php echo $plugin->getName() ?></strong>
					<div class="row-actions-visible">
						<span class="deactivate" style="<?php if (!$plugin->isActive() || !$plugin->isInstalled()) echo "display:none";  ?>">
							<a class="deactivate-button" title="Deactivate this plugin" href="#">Deactivate</a> | 
						</span><span class="edit"></span>
						
						<span class="activate"  style="<?php if ($plugin->isActive() || !$plugin->isInstalled()) echo "display:none";  ?>" >
							<a class="activate-button" title="Activate this plugin" href="#">Activate</a> | 
						</span><span class="edit"></span>
						
						<span class="uninstall" style="<?php if (!$plugin->isInstalled() || $plugin->isActive() ) echo "display:none";  ?>">
							<a class="uninstall-button"  title="Uninstall this plugin" href="#">Uninstall</a> | 
						</span><span class="edit"></span>
						
						<span class="install" style="<?php if ($plugin->isInstalled()) echo "display:none";  ?>">
							<a class="install-button" title="Install this plugin" href="#">Install</a> | 
						</span><span class="edit"></span>
						
					</div>
				</td>			
				<td class="column-description">
					<div class="plugin-description">
						<p><?php echo $metadata['description'] ?></p>
					</div>
					<div class="active second plugin-version-author-uri">
						<?php 
							if ( $plugin->isInstalled() && $plugin->getVersion() ) {
								echo "Version: ". $plugin->getVersion();
							} else{
								if (!empty($metadata['version'])) echo "Version: ".$metadata['version'];
							}
							
							if (!empty($metadata['author'])) : ?> | By <a title="Visit author homepage" target='_blank' href="<?php echo $metadata['website'] ?>"> <?php echo $metadata['author']?> </a><?php endif;?>
					</div>
				</td>
				<td class="column-dependences">
					<?php if (!empty($metadata['dependences']) && is_array($metadata['dependences'] ) ) echo implode(" | " , $metadata['dependences'] );?>
				</td>
				<td class="column-status">
					<div class="plugin-status"> 
						<?php 
							if ($plugin->isActive()) {
								echo "active";
							}elseif ($plugin->isInstalled()){
								echo "inactive";
							}else{
								echo "uninstalled";
							}
						?>
					</div>
				</td>
				
			</tr>
			<?php if($plugin->isInstalled() && $plugin->isActive() && $plugin->updateAvailable()):?>
			<tr plg-id="<?php echo $plugin->getId()?>">
				<td colspan=4>
					<div class="update-container">
						There is a new version of <?php echo $plugin->getName()?>. <a class="update-button"  title="Update now plugin" href="#">Update now from Version <?php echo $plugin->getVersion() ?> to Version <?php  echo array_var($metadata,'version')?></a> 
					</div>
				</td>
			<tr/>
			<?php endif;?>
		<?php endforeach ;?>
		</tbody>		
	</table>
	
	<div style="display:none " class="contextualHelp reload ">
		<p>Some plugins need to reload your page after installation.</p>
		<p>If you want to reload now click <a href="#" onclick="window.location.href='<?php echo ROOT_URL ?>'">  here. </a></p>
	</div>
	
</div>

<script>
	$(function(){
		og.pluginManager.init();
	});
</script>