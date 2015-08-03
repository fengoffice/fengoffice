<?php 

  // Set page title and set crumbs to index
  set_page_title(lang('upgrade'));

?>
<div class="adminUpgrade">
<?php if(is_array($versions = $versions_feed->getNewVersions(product_version())) && count($versions)) { ?>
	<div class="availableVersions">
	<?php foreach($versions as $version) { ?>
		<div class="availableVersion">
			<h2><a target="_blank" href="<?php echo $version->getDetailsUrl() ?>"><?php echo clean($version->getSignature()) ?></a></h2>
			<h3><?php echo lang("release notes") ?>:</h3>
			<div class="releaseNotes"><?php echo $version->getReleaseNotes() ?></div>
		<?php
			$download_links = array(); 
			foreach($version->getDownloadLinks() as $download_link) {
				$download_links[] = '<a target="_blank" href="' . $download_link->getUrl() . '">' . clean($download_link->getFormat()) .' (' . format_filesize($download_link->getSize()) . ')</a>';
			} // foreach
		?>
				<div class="downloadLinks"><strong><?php echo lang('manual upgrade') ?>:</strong> <?php echo implode(' | ', $download_links) ?><div class="desc"><?php echo lang("manual upgrade desc") ?></div></div>
				<div class="downloadLinks"><strong><?php echo lang('automatic upgrade') ?>:</strong> <a target="_self" href="<?php echo get_url('administration', 'auto_upgrade', array('version' => $version->getVersionNumber())) ?>"><?php echo lang("start automatic upgrade") ?></a><div class="desc"><?php echo lang("automatic upgrade desc") ?></div></div>
			</div>
<?php } // foreach ?>
	</div>
<?php } else { ?>
	<p><?php echo lang('upgrade is not available') ?></p>
<?php } // if ?>
</div>