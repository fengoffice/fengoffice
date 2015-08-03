	<?php extract($result)?>
	<div id="result-item<?php echo $id?>"  class="result-item">
		<div class="title">
			<a href="<?php echo $url?>" ><?php echo $title ?> </a>
		</div>
		<div class="content">
			<p><?php echo $content?></p>
		</div>
		<div class="footer">
			<span class="breadcrumb"></span>
				<script>
					<?php $crumbOptions = $memPath;
					$crumbJs = " og.getEmptyCrumbHtml($crumbOptions,'.breadcrumb-container', og.breadcrumbs_skipped_dimensions) ";?>
					
					var crumbHtml = "<div class='breadcrumb-container' style='display: inline-block;min-width: 250px;'>";
					crumbHtml += <?php echo $crumbJs;?>;
					crumbHtml += "</div>";
					
					$("#result-item<?php echo $id?> .breadcrumb").html(crumbHtml);
				</script>
			<span class="footers_links">
				<span class="created_by"><?php echo $updated_by ?></span> -
				<span class="updated_on"><?php echo $updated_on ?></span> -
				<span class="type"><?php echo lang($type) . (isset($additional_type) ? " - $additional_type" : '') ?></span> 
			</span>
		</div>
	</div>