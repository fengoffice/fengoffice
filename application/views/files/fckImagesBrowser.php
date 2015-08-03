<?php 
$genid = gen_id();
?>
<head>
	<?php echo stylesheet_tag('website.css'); ?>
</head>
<body>

<div class="adminHeader">
  	<div class="adminHeaderUpperRow">
  		<div class="adminTitle">
  			<table style="width:535px">
  			<tr>
  				<td>
  					<?php echo lang('images') ?>
  				</td>
  			</tr>
  			</table>
  		</div>
  	</div>  	
</div>
  
  <div class="adminSeparator"></div>
  
  <div class="adminMainBlock">
	<script type="text/javascript">
		ogrolloverimage = function (row){
			row.style.background = "#aaf";
		};
		ogrolloutimage = function (row){
			
			if (row.id.substring(0,3) == 'odd'){
				row.style.background = "#eeeef3";
			}else{
				row.style.background = "#FFF";
			}
		};
		ogimageFileSelected=function(url){
			window.opener.SetUrl( url ) ;
			window.close() ;		
		};
	</script>
	<table width="60%" cellspacing="0px" style="border: solid 1px #ccc; margin-top: 15px; margin-left: 10px;">
		<tr style="border: #ddd solid 1px; height: 30px;">
			<td style="background-color:#D7E5F5;padding:5px 10px 5px;">
				<div style="float:left;"><h1> <?php echo lang('images') ;?> </h1></div>
				<div style="float: right; padding-top: 16px">
					<?php if (count($images) <= $limit && $start > 0){?>
						<a href="<?php echo (get_url('files','fckimagesbrowser',array('start'=>($start-$limit),'limit'=>$limit))) ?>">
							<img src="<?php echo get_image_url("/16x16/prevmonth.png")?>" />
						</a>
					<?php }
					if (count($paginatedImages[0]) >= $limit){?>
						<a href="<?php echo (get_url('files','fckimagesbrowser',array('start'=>($start+$limit),'limit'=>$limit))) ?>">
							<img src="<?php echo get_image_url("/16x16/nextmonth.png")?>" />
						</a>
					<?php }?>
				</div>
			</td>
		</tr>
		<?php	
		$count = 0;
		if (isset($paginatedImages)&& is_array($paginatedImages)){
			$images = $paginatedImages[0];
			foreach ($images as $img){
				$count++;
				?>
				<tr  style="height:30px; border-left:#ddd solid 1px; border-right:#ddd solid 1px; background:<?php echo($count%2 == 0)? '#eeeef3;':'' ?>" id="<?php echo($count%2 == 0)? 'odd' . $count :'cln' . $count ?>" onmouseover="ogrolloverimage(this)" onmouseout="ogrolloutimage(this)" >
					<td>
						<div style=" padding: 15px">
						<span id="imgId<?php echo $img->getId()?>" class="db-ico ico-picture" > 
						<a style="margin: 18px;" id="linkImg<?php echo $img->getId();?>" href="#" onClick="ogimageFileSelected('<?php echo $img->getDownloadUrl() ?>')" >
							<?php echo $img->getTitle() ?>
						</a>
						</span>
						</div>
					</td>					
				</tr>
			<?php }
		}	
		?>	
	</table>
	</div>
</body>