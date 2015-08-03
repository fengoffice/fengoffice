<b><?php 
if (strpos($url, 'docs.google.com/') != false){
	$content = '<div style="position: relative; left:0; top: 0; width: 100%; height: 600px; background-color: white">';
	$content .= '<iframe id="'.$genid.'ifr" name="'.$genid.'ifr" style="width:100%;height:100%" frameborder="0" src="'.$url.'" 
							onload="javascipt:iframe=document.getElementById(\''.$genid.'ifr\'); iframe.parentNode.style.height = Math.min(600, iframe.contentWindow.document.body.scrollHeight + 30) + \'px\' ;">
						</iframe>';
			'<script>if (Ext.isIE) document.getElementById(\''.$genid.'ifr\').contentWindow.location.reload();</script>';
	$content .= '<a class="ico-expand" style="display: block; width: 16px; height: 16px; cursor: pointer; position: absolute; right: 20px; top: 2px" title="' . lang('expand') . '" onclick="og.expandDocumentView(this)"></a>
				</div>';	
    echo lang("blank_google_doc");
    echo $content;
} else {
    echo lang("url") ?>: </b><a target="_blank" href="<?php echo $url ?>"><?php echo $url ?></a>
    <?php if (isset($desc) && trim($desc) != "") { ?>
        <fieldset><legend><?php echo lang('description') ?></legend>
            <?php echo $desc ?>
        </fieldset>
    <?php }//if
} //else ?>