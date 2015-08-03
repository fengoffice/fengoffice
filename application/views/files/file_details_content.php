<?php $file = $object;
	$maxRevisionsToShow = 5;
	$revisions = $file->getRevisions();
	$last_revision = $file->getLastRevision();
	$genid = gen_id();
?>

<?php if ($file->getType() == ProjectFiles::TYPE_WEBLINK) { ?>
	<b><?php 
	if (strpos($file->getUrl(), 'docs.google.com/') != false){
		$content = '<div style="position: relative; left:0; top: 0; width: 100%; height: 600px; background-color: white">';
		$content .= '<iframe id="'.$genid.'ifr" name="'.$genid.'ifr" style="width:100%;height:100%" frameborder="0" src="'.$file->getUrl().'" 
								onload="javascipt:iframe=document.getElementById(\''.$genid.'ifr\'); iframe.parentNode.style.height = Math.min(600, iframe.contentWindow.document.body.scrollHeight + 30) + \'px\' ;">
							</iframe>';
				'<script>if (Ext.isIE) document.getElementById(\''.$genid.'ifr\').contentWindow.location.reload();</script>';
		$content .= '<a class="ico-expand" style="display: block; width: 16px; height: 16px; cursor: pointer; position: absolute; right: 20px; top: 2px" title="' . lang('expand') . '" onclick="og.expandDocumentView(this)"></a>
					</div>';	
	    echo lang("blank_google_doc");
	    echo $content;
	} else {?>
	<b><?php echo lang('url') ?></b>: <a href="<?php echo clean($file->getUrl()) ?>" target="_blank"><?php echo clean($file->getUrl()) ?></a>
<?php }
} ?>

<?php if ($file->isDisplayable()) {?>
<div>
	<div id="<?php echo $genid?>document-view" style="position: relative; left:0; top: 0; width: 100%; height: 700px; background-color: white">
		<iframe class="document-preview" style="width:100%;height:100%;border:1px solid #ddd;" src="<?php echo get_sandbox_url("feed", "display_content", array("id" => $file->getId(), "user_id" => logged_user()->getId(), "token" => logged_user()->getTwistedToken())) ?>"></iframe>
		<a id="expander" class="ico-expand" style="display: block; width: 16px; height: 16px; cursor: pointer; position: absolute; right: 20px; top: 2px" title="<?php echo lang('expand') ?>" onclick="og.expandDocumentView(this)"></a>
	</div>
	
	<script>
		$(function(){
			$("iframe.document-preview").load(function(){
				$("iframe.document-preview").contents().find("a").attr("target", "_blank");
			});
		});
		
		//resize document height on load
		var percentView = 70;
		$( document ).ready(function() {
			var documentHeight = $(window).height() * percentView / 100;
			$("#<?php echo $genid?>document-view").height(documentHeight);
		});
		
		// resize document height when resizing window
		$(window).resize(function() {
			var expander = $("#<?php echo $genid?>document-view #expander");
			var is_expanded = expander.prop('expanded');
			if (is_expanded) {
				var documentHeight = $("#<?php echo $genid?>document-view").closest(".x-panel-body").height();
			} else {
				var documentHeight = $(window).height() * percentView / 100;
			}
			$("#<?php echo $genid?>document-view").height(documentHeight);
		});
	</script>
	
</div>
<?php } // if ?> 

<?php if ($file->getUpdatedOn() instanceof DateTimeValue) {
	$modtime = $file->getUpdatedOn()->getTimestamp();
} else {
	$modtime = $file->getCreatedOn()->getTimestamp();
}?>

<?php if(($ftype = $file->getFileType()) instanceof FileType && $ftype->getIsImage()){?>
	<div>
		<a href="<?php echo get_url('files', 'download_image', array('id' => $file->getId(), 'inline' => true, 'modtime' => $modtime)); ?>" target="_blank" title="<?php echo lang('show image in new page') ?>">
			<img id="<?php echo $genid ?>Image" src="<?php echo get_url('files', 'download_image', array('id' => $file->getId(), 'inline' => true, 'modtime' => $modtime)); ?>" style="max-width:450px;max-height:500px"/>
		</a>
	</div>
<?php }?>

<?php
   if (strtolower(substr($file->getFilename(), -3)) == 'pdf'){
      echo'<div>';
      if($file->getType() != ProjectFiles::TYPE_WEBLINK){       
        $urlpdf=get_url('files', 'download_image', array('id' => $file->getId(), 'inline' => true, 'modtime' => $modtime));
      }else{      
        $urlpdf=$file->getUrl();
      }
      echo "<iframe src=".$urlpdf." width='100%' height='900px' frameborder=0 align='center'></iframe>";
      echo '</div>';
   }else if (substr($file->getFilename(), -3) == '.mm') {
	require_javascript('flashobject.js');
	$flashurl = get_flash_url('visorFreemind.swf') ?>
	<div id="<?php echo $genid ?>mm">
	<script>
		var fo = new FlashObject("<?php echo $flashurl ?>", "visorFreeMind", "100%", "350px", 6, "#9999ff");
		fo.addParam("quality", "high");
		fo.addParam("bgcolor", "#ffffff");
		fo.addVariable("initLoadFile", "<?php echo $file->getDownloadUrl() ?>");
		fo.addVariable("openUrl", "_blank");
		fo.write("<?php echo $genid ?>mm");
	</script>
<?php } ?>


<?php if (count($revisions) && !$file->getType() == ProjectFiles::TYPE_WEBLINK){?>
<fieldset>
  <legend class="toggle_expanded" onclick="og.toggle('<?php echo $genid ?>revisions',this)"><?php echo lang('revisions'); ?> (<?php echo count($revisions);?>)</legend>
<div id="<?php echo $genid ?>revisions" >
<table class="revisions">
<?php  $counter = 0;
	foreach($revisions as $revision) { 
		$hasComments = trim($revision->getComment());
		$counter++; 
		$bgColor = $counter % 2 ? ($counter == 1? '#FFD39F' : '#DDD') : '#EEE';
?>
	<tr <?php if($counter > $maxRevisionsToShow){echo 'class="extra_revisions" style="display: none"'; } ?>>
		<td rowspan=2 class='number' style="background-color:<?php echo $bgColor ?>">
			<?php if ($file->canDownload(logged_user())){?>
				<?php if ($file->getType() == ProjectFiles::TYPE_WEBLINK) {?>
				<a target="_blank" class="downloadLink" href="<?php echo $revision->getTypeString() ?>" title="<?php echo $revision->getTypeString()?>">
					<span style="font-size:12px">#</span><?php echo $revision->getRevisionNumber() ?>
				</a>
				<?php } else { ?>
				<a target="_self" class="downloadLink" href="<?php echo $revision->getDownloadUrl() ?>" title="<?php echo lang('download') . ' (' . format_filesize($revision->getFileSize()) .')'?>">
					<span style="font-size:12px">#</span><?php echo $revision->getRevisionNumber() ?>
				</a>
				<?php } ?>
			<?php } else {?>
				<span style="font-size:12px">#</span><?php echo $revision->getRevisionNumber() ?>
			<?php } // if ?>
		</td>
		<td class='line_header' style="background-color:<?php echo $bgColor ?>;">
			<?php if($revision->getCreatedBy() instanceof Contact) { ?>
			    <?php echo lang('file revision title long', $revision->getCreatedBy()->getCardUserUrl(), clean($revision->getCreatedBy()->getObjectName()), format_datetime($revision->getCreatedOn())) ?>
			<?php } else { ?>
			    <?php echo lang('file revision title short', format_datetime($revision->getCreatedOn())) ?>
			<?php } // if ?>
		</td>
		<td class='line_header_icons' style="background-color:<?php echo $bgColor ?>;width:50px;">
			<?php if ($file->canDownload(logged_user())){?>
				<?php if ($file->getType() == ProjectFiles::TYPE_WEBLINK) { ?>
				<a target="_blank" class="downloadLink coViewAction ico-open-link" href="<?php echo $revision->getTypeString() ?>" title="<?php echo $revision->getTypeString()?>">&nbsp;</a>
				<?php } else {?>
				<a target="_self" class="downloadLink coViewAction ico-download" href="<?php echo $revision->getDownloadUrl() ?>" title="<?php echo lang('download') . ' (' . format_filesize($revision->getFileSize()) .')'?>">&nbsp;</a>
				<?php } ?>
			<?php } ?>
			<?php if ($file->canDelete(logged_user()) && !$file->isTrashed()) {?>
				<a onclick="return confirm('<?php echo escape_single_quotes(lang('confirm move to trash'))?>')" href="<?php echo $revision->getDeleteUrl() ?>" class="internalLink coViewAction ico-trash" title="<?php echo lang('move to trash')?>">&nbsp;</a>
			<?php } ?>
		</td>
	</tr>
	<tr <?php if($counter > $maxRevisionsToShow){echo 'class="extra_revisions" style="display: none"'; } ?>>
		<td class='line_comments'>
			<div style="padding:2px;padding-left:6px;padding-right:6px;min-height:24px;">
		<?php if($hasComments) {?>
			 <?php echo nl2br(clean($revision->getComment()))?>
		<?php } ?>
			&nbsp;</div>
		</td>
		<td class="line_comments_icons">
			<?php if ($file->canEdit(logged_user()) && !$file->isTrashed()){?>
				<a href="<?php echo $revision->getEditUrl() ?>" class="internalLink coViewAction ico-edit" title="<?php echo lang('edit revision comment')?>">&nbsp;</a>
			<?php }?>
		</td>
	</tr>	
<?php } // foreach ?>

<?php if($counter >= $maxRevisionsToShow){ ?>

		<tr>
					<td colspan="2" align="right" style="padding:20px 0 5px; width: 20px; color: #003562;">
						<span onclick="hideRevisions('<?php echo $genid?>')" id="hidelnk<?php echo $genid?>" style="cursor:pointer; display:none;" title="<?php echo lang('hide') ?>"><?php echo lang('hide') ?></span>
						<span id="separatorlnk<?php echo $genid?>" style="display:none;"> / </span>
						<span onclick="showRevisions('<?php echo $genid?>')" id="showlnk<?php echo $genid?>" style="cursor:pointer;" title="<?php echo lang('show more') ?>"><?php echo lang('show more') ?></span>
					</td>
		</tr>

<?php } ?>
</table>
</div>
</fieldset>
<?php } // if ?>

<?php if(($file->getDescription())) { ?>
      <fieldset><legend><?php echo lang('description')?></legend>
      <?php echo escape_html_whitespace(convert_to_links(clean($file->getDescription()))) ?>
      </fieldset>
<?php } // if ?>

<?php if(($ftype = $file->getFileType()) instanceof FileType && $ftype->getIsImage()){?>
	<script>
	function resizeImage(genid){
		var image = document.getElementById(genid + 'Image');
		if (image){
			var width = (Ext.isIE)? image.parentNode.parentNode.offsetWidth : image.parentNode.parentNode.clientWidth;
			
			image.style.maxWidth = (width - 20) + "px";
			image.style.maxHeight = (width - 20) + "px";
		}
	}
	resizeImage('<?php echo $genid ?>');
	function resizeSmallImage(genid){
		var image = document.getElementById(genid + 'Image');
		if (image){
			image.style.maxWidth = "1px";
			image.style.maxHeight = "1px";
		}
	}
	function resizeImage<?php echo $genid ?>(){
		resizeSmallImage('<?php echo $genid ?>');
		setTimeout('resizeImage("<?php echo $genid ?>")',50);
	}
	og.addDomEventHandler(window, 'resize', resizeImage<?php echo $genid ?>);
	</script>
<?php } ?>
<script>
	var numOfRevToShow = 20;
	function showRevisions(genid){
		$('#hidelnk' + genid).show();
		$(".extra_revisions:hidden:lt("+numOfRevToShow * 2+")").show();
		if($(".extra_revisions:hidden").length == 0){
			$('#showlnk' + genid).hide();
			$('#separatorlnk' + genid).hide();
		}else{
			$('#separatorlnk' + genid).show();
		} 
	}
	function hideRevisions(genid){
		$(".extra_revisions:visible").hide();
		if($(".extra_revisions:visible").length == 0){
			$('#hidelnk' + genid).hide();
			$('#showlnk' + genid).show();
			$('#separatorlnk' + genid).hide();
		}
	}
</script>
