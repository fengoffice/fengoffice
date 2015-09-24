<?php
if (isset($email)){
	if (!$email->isTrashed()) {
		if (logged_user()->hasEmailAccounts()) {
			add_page_action(lang('reply mail'), $email->getReplyMailUrl()  , 'ico-reply', null, null, true);
			add_page_action(lang('reply to all mail'), $email->getReplyMailUrl()."&all=1"  , 'ico-reply-all', null, null, true);
			add_page_action(lang('forward mail'), $email->getForwardMailUrl()  , 'ico-forward', null, null, true);
		}
		add_page_action(lang('print'), $email->getPrintUrl(), 'ico-print', "_blank", null, true);
	}
	if($email->canDelete(logged_user())) {
		if ($email->isTrashed()) {
			add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $email->getUntrashUrl() ."');", 'ico-restore', null, null, true);
			add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $email->getDeletePermanentlyUrl() ."');", 'ico-delete', null, null, true);
		} else {
			add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) {og.openLink('" . $email->getTrashUrl() . "');}", 'ico-trash', null, null, true);
		}
	}
	if ($email->canEdit(logged_user()) && !$email->isTrashed()){
		add_page_action(lang('classify'), "javascript: og.render_modal_form('', {c:'mail', a:'classify', params: {id: '" .$email->getId(). "'}, focusFirst: false})", 'ico-classify', null, null, true);
		
		if (!$email->isArchived()) {
			add_page_action(lang('archive'), "javascript:if(confirm(lang('confirm archive object'))) og.openLink('" . $email->getArchiveUrl() ."');", 'ico-archive-obj');
		} else {
			add_page_action(lang('unarchive'), "javascript:if(confirm(lang('confirm unarchive object'))) og.openLink('" . $email->getUnarchiveUrl() ."');", 'ico-unarchive-obj', null, null, true);
		}
		
		if ($email->getState() == 0 || $email->getState() == 5) {
			add_page_action(lang('report as spam'), get_url('mail', 'change_email_folder', array("id" => $email->getId(), "newf" => 4)), 'ico-spam');
		} else if ($email->getState() == 4) {
			add_page_action(lang('not spam'), get_url('mail', 'change_email_folder', array("id" => $email->getId(), "newf" => 0)), 'ico-unclassify');
		}
	}
	add_page_action(lang('mark as unread'), get_url('mail', 'mark_as_unread', array('id' => $email->getId())), 'ico-mark-as-unread');
	
	if ( !logged_user()->isGuest()) {
		add_page_action(lang('create task from email'), "javascript:og.render_modal_form('', {c:'task', a:'add_task', params: {id:".$email->getId().", from_email:".$email->getId()."}});", 'ico-task', null, null, true);
		$ret = null;
		Hook::fire('additional_email_actions', array('email' => $email), $ret);
	}
	if ($email->getState() < 200) {
		$download_url = get_url('mail', 'download', array('id' => $email->getId()));
		include_once ROOT . "/library/browser/Browser.php";
		if (Browser::instance()->getBrowser() == Browser::BROWSER_IE) {
			$download_url = "javascript:location.href = '$download_url';";
		}
		add_page_action(lang('download email'), $download_url, 'ico-download', '_self');
	}
} 
	$c = 0;
	$genid = gen_id();
	$use_24_hours = user_config_option('time_format_use_24');
	$hide_quoted_text_in_emails = user_config_option('hide_quoted_text_in_emails');
	$time_format = $use_24_hours ? 'G:i' : 'g:i a';
?>

<script>
	og.showQuotedText = function(genid) {
		document.getElementById(genid + 'noQuoteMail').style.display = 'none';
		document.getElementById(genid + 'quotedLink').style.display = 'none';
		document.getElementById(genid + 'completeMail').style.display = 'block';
	}
	og.showMailImages = function(pre, rand, genid, tt) {
		if (document.getElementById(genid + 'viewingQuoted').value != 'yes') {
			pre = "q_" + pre;
		}
		og.changeContentIframeSrc(pre, rand, genid, tt);
		document.getElementById(genid + 'showImagesLink').style.display = 'none';
		document.getElementById(genid + 'viewingImages').value = "yes";
	}
	
	og.showQuotedHtml = function(pre, rand, genid, tt) {
		if (document.getElementById(genid + 'viewingImages').value != 'yes') {
			pre = "i_" + pre;
		}
		og.changeContentIframeSrc(pre, rand, genid, tt); 
		document.getElementById(genid + 'showQuotedText').style.display = 'none';
		document.getElementById(genid + 'viewingQuoted').value = "yes";
	}
	
	og.changeContentIframeSrc = function(pre, rand, genid, tt) {
		var iframe = document.getElementById(genid + 'ifr');
		if (og.sandboxName) {
			iframe.src = og.getSandboxUrl('feed', 'show_html_mail', {pre: pre, r: rand, id: og.loggedUser.id, token: tt});
		} else {
			iframe.src = og.getUrl('mail', 'show_html_mail', {pre: pre, r: rand});
		}
		/*iframe.style.display = 'none';
		iframe.style.display = 'block';
		if (Ext.isIE) iframe.contentWindow.location.reload();*/
	}
</script>

<?php if (isset($email) && $email instanceof MailContent) {?>
<div style="padding:7px">
<div class="email">

	<?php $description = '<div class="coInfo">
	<table>
	<tr><td style="width:100px">' . lang('from') . ':</td><td>' . MailUtilities::displayMultipleAddresses(clean($email->getFromName()." <".$email->getFrom().">")) . '</td></tr>
	<tr><td>' . lang('to') . ':</td><td>' . MailUtilities::displayMultipleAddresses(clean($email->getTo())) . '</td></tr>';
	if ($email->getCc() != '') {
		$description .= '<tr><td>' . lang('mail CC') . ':</td><td>' . MailUtilities::displayMultipleAddresses(clean($email->getCc())) . '</td></tr>';
	}
	if ($email->getBcc() != '') {		
		$description .= '<tr><td>' . lang('mail BCC') . ':</td><td>' . MailUtilities::displayMultipleAddresses(clean($email->getBcc())) . '</td></tr>';
	}
	$description .= '<tr><td>' . lang('date') . ':</td><td>' . format_datetime($email->getSentDate(), 'l, j F Y - '.$time_format, logged_user()->getTimezone()) . '</td></tr>';
	
	if (user_config_option('view_mail_attachs_expanded')) {
		$attach_toggle_cls = "toggle_expanded";
		$attach_div_style = "";
	} else {
		$attach_toggle_cls = "toggle_collapsed";
		$attach_div_style = "display:none;";
	}
	
	if ($email->getHasAttachments() && is_array($attachments) && count($attachments) > 0) {
		$description .=	'<tr><td colspan=2>	<fieldset>
		<legend class="'.$attach_toggle_cls.'" onclick="og.toggle(\'mv_attachments\',this)">' . lang('attachments') . '</legend>
		<div id="mv_attachments" style="'.$attach_div_style.'">
		<table>';
		foreach($attachments as $att) {
			if (!array_var($att, 'hide')) {
				$size = $att['size'];//format_filesize(strlen($att["Data"]));
				$fName = str_starts_with($att["FileName"], "=?") ? iconv_mime_decode($att["FileName"], 0, "UTF-8") : utf8_safe($att["FileName"]);
				if (trim($fName) == "" && strlen($att["FileName"]) > 0) $fName = utf8_encode($att["FileName"]);
				$description .= '<tr><td style="padding-right: 10px">';
				$ext = get_file_extension($fName);
				$fileType = FileTypes::getByExtension($ext);
				if (isset($fileType))
					$icon = $fileType->getIcon();
				else
					$icon = "unknown.png";
				$download_url = get_url('mail', 'download_attachment', array('email_id' => $email->getId(), 'attachment_id' => $c));
				include_once ROOT . "/library/browser/Browser.php";
				if (Browser::instance()->getBrowser() == Browser::BROWSER_IE) {
					$download_url = "javascript:location.href = '$download_url';";
				}
	      		$description .=	'<img src="' . get_image_url("filetypes/" . $icon) .'"></td>
				<td><a target="_self" href="' . $download_url . '">' . clean($fName) . " ($size)" . '</a></td></tr>';
			}
      		$c++;
		}
		$description .= '</table></div></fieldset></td></tr>';
  } //if
  $description .= '</table></div>';
		if (($email_count = MailContents::countMailsInConversation($email)) > 1) {
			$emails_info = MailContents::getMailsFromConversation($email);
			$conversation_block = '';
			$conversation_block .= '<div id="'.$genid.'conversation" style="margin-bottom:10px;' . 
				(count($emails_info) > 6 ? 'max-height:101px;overflow:auto' : ''  ) . '"><table style="width:100%;">';
			
			$unread = 0;
			foreach($emails_info as $count => $info) { 
				$row_cls = $count % 2 ? 'odd' : 'even';
				$is_current = $info->getId() == $email->getId();
				$style = $is_current ? "style='background-color:#FFDD78'" : "";
				$conversation_block .= '<tr class="'.$row_cls.'" ' . $style . '>';
				
				$state = $info->getState();
				$show_user_icon = false;
				if ($state == 1 || $state == 3 || $state == 5) {
					if ($info->getCreatedById() == logged_user()->getId()) {
						$from = lang('you');
					} else {
						$from = $info->getCreatedByDisplayName();
					}
					$show_user_icon = true;
				} else {
					$from = $info->getFrom();
				}
				
				$read_style = "";
				if (!$info->getIsRead(logged_user()->getId()) ) {
					$read_style = "font-weight: bold;";
					$unread++;
				}
				
				$conversation_block .= '<td style="width:20px;'.$read_style.'">';
				if ($info->getHasAttachments()) { 
					$conversation_block .= '<div class="db-ico ico-attachment"></div>';
				}
				$conversation_block .= '<td style="width:20px;'.$read_style.'">';
				if ($show_user_icon) { 
					$conversation_block .= '<div class="db-ico ico-user"></div>';
				}
				
				$info_text = $info->getTextBody();
				if (strlen_utf($info_text) > 90) $info_text = substr_utf($info_text, 0, 90) . "...";		
				
				$view_url = get_url('mail', 'view', array('id' => $info->getId(), 'replace' => 1));
				$conversation_block .= '<td>';
				$conversation_block .= '	<a style="'.$read_style.'" class="internalLink" href="'.$view_url.'" onclick="og.openLink(\''.$view_url.'\');return false;" title="'.$info->getFrom().'">';
				$conversation_block .= $from;
				if (!$is_current) $conversation_block .= '	</a><span class="desc">- '.$info_text.'</span></td>';
				
				$info_date = $info->getReceivedDate() instanceof DateTimeValue ? ($info->getReceivedDate()->isToday() ? format_time($info->getReceivedDate()) : format_datetime($info->getReceivedDate())) : lang('n/a');
				$conversation_block .= '</td><td style="text-align:right;padding-right:3px"><span class="desc">'. lang('date').': </span>'. $info_date .'</td>';

			} //foreach
			$conversation_block .= '</table>';
			$conversation_block .= '</div>';
		} else {
			$conversation_block = '';
		}
		
		if($email->getBodyHtml() != ''){
			$html_content = $email->getBodyHtml();
			
			// inline images
			$end_while = false;
			$offset = 0;
			$matches = array();
			while (!$end_while) {
				$pos = strpos($html_content, "<img", $offset);
				if ($pos === false) {
					$end_while = true;
				} else {
					$pos_src = strpos($html_content, 'src="', $pos) + 5;
					if($pos_src < $pos){
						$pos = $pos + 5;
						$offset = $pos;
					}else{
						$end_pos = strpos($html_content, '"', $pos);
						$matches[] = substr($html_content, $pos, $end_pos - $pos);
							
						$offset = $end_pos;
					}					
				}
			}
			
			foreach ($matches as $url) {
				if (str_starts_with($url, "data:")) {
					$mime_type = substr($url, 5, strpos($url, ';') - 5 );
					$extension = substr($mime_type, strpos($mime_type, "/")+1);
						
					$file_url = ROOT_URL."/tmp/".gen_id().".$extension";
					$path = str_replace(ROOT_URL, ROOT, $file_url);
						
					$data = substr($url, strpos($url, "base64") + 6);
					file_put_contents($path, base64_decode($data));
						
					$html_content = str_replace($url, $file_url, $html_content);
				}
			}
			
			if (defined('SANDBOX_URL')) {
				// prevent some outlook malformed tags
				if(substr_count($html_content, "<style") != substr_count($html_content, "</style>") && substr_count($html_content, "/* Font Definitions */") >= 1) {
					$p1 = strpos($html_content, "/* Font Definitions */", 0);
					$html_content1 = substr($html_content, 0, $p1);
					$p0 = strrpos($html_content1, "</style>");
					$html_content = ($p0 >= 0 ? substr($html_content1, 0, $p0) : $html_content1) . substr($html_content, $p1);
					
					$html_content = str_replace_first("/* Font Definitions */","<style>",$html_content);
				}
			} else {
				$html_content = purify_html($html_content);
			}
			
			if (strpos($html_content, "<html") === false) {
				if (strpos($html_content, "<body") === false) {
					$html_content = "<body>" . $html_content . "</body>";
				}
				if (strpos($html_content, "<head") === false) {
					$html_content = "<head></head>" . $html_content;
				}
				$html_content = "<html>" . $html_content . "</html>";
			}
			//$html_content = convert_to_links($html_content); // commented because it can break HTML (e.g. if an URL or email is found on the title of an element)
			// links must open in a new tab or window
			$html_content = preg_replace('/<a\s/', '<a target="_blank" ', $html_content);
			
			//remove attributes from body
			$html_content = preg_replace("/<body*[^>]*>/i",'<body>', $html_content);
			
			$html_content = str_replace("<head>", '<head><link rel="stylesheet" type="text/css" href="'.ROOT_URL.'/public/assets/javascript/ckeditor/contents.css" /><link rel="stylesheet" type="text/css" href="'.ROOT_URL.'/plugins/mail/public/assets/css/mail.css" />', $html_content);
			$html_content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n" . $html_content;
			
			if (!is_dir(ROOT.'/tmp')) mkdir(ROOT.'/tmp');
			
			// HIDE QUOTED TEXT AND IMAGES IF APPLICABLE
			$tmpfile = $email->getAccountId() . '_' . logged_user()->getId() ."_". $email->getId().'_temp_mail_content.html';
			// FULL CONTENT
			$tmppath = ROOT.'/tmp/'.$tmpfile;
			$handle = fopen($tmppath, 'wb');
			if ($handle) {
				fwrite($handle, $html_content);
				fclose($handle);
			}
			
			// CONTENT NO IMAGES
			$html_no_images = remove_images_from_html($html_content);
			$tmppath = ROOT.'/tmp/i_'.$tmpfile;
			$handle = fopen($tmppath, 'wb');
			if ($handle) {
				fwrite($handle, $html_no_images);
				fclose($handle);
			}
			
			// CONTENT NO QUOTED
			$html_no_quoted = MailUtilities::replaceQuotedBlocks($html_content, '<div style="color: #777;font-style:italic;padding: 5px 20px">&lt;'.lang('hidden quoted text').'&gt;</div>');
			$tmppath = ROOT.'/tmp/q_'.$tmpfile;
			$handle = fopen($tmppath, 'wb');
			if ($handle) {
				fwrite($handle, $html_no_quoted);
				fclose($handle);
			}
			
			// CONTENT NO QUOTED NO IMAGES
			$html_no_quoted_no_images = MailUtilities::replaceQuotedBlocks($html_no_images, '<div style="color: #777;font-style:italic;padding: 5px 20px">&lt;'.lang('hidden quoted text').'&gt;</div>');
			$tmppath = ROOT.'/tmp/iq_'.$tmpfile;
			$handle = fopen($tmppath, 'wb');
			if ($handle) {
				fwrite($handle, $html_no_quoted_no_images);
				fclose($handle);
			}
			
			// VIEW CONTENT (iframe and links)
			$remove_images = false;
			$remove_quoted = false;
			if (user_config_option('block_email_images') && html_has_images($html_content)) {
				$remove_images = true;
			}
			if ($hide_quoted_text_in_emails && MailUtilities::hasQuotedBlocks($html_content)) {
				$remove_quoted = true;
			}			
			$pre = $email->getAccountId() . '_' . logged_user()->getId() . '_' . $email->getId();
			$user_token = defined('SANDBOX_URL') ? logged_user()->getTwistedToken() : '';
			$content = "";
			if ($remove_images) {
				$content = '<div id="'.$genid.'showImagesLink" style="background-color:#FFFFCC">'.lang('images are blocked').' 
					<a href="#" onclick="og.showMailImages(\''.$pre.'\', \''.gen_id().'\', \''.$genid.'\', \''.$user_token.'\');" style="text-decoration: underline;">'.lang('show images').'</a>
				</div>';
			}
			if ($remove_images && $remove_quoted) {
				$tpre = "iq_" . $pre;
			} else if ($remove_images) {
				$tpre = "i_" . $pre;
			} else if ($remove_quoted) {
				$tpre = "q_" . $pre;
			} else {
				$tpre = $pre;
			}
			if (defined('SANDBOX_URL')) {
				$url = get_sandbox_url('feed', 'show_html_mail', array('pre' => $tpre, 'r' => gen_id(), 'id' => logged_user()->getId(), 'token' => $user_token));
			} else {
				$url = get_url('mail', 'show_html_mail', array('pre' => $tpre, 'r' => gen_id()));
			}
			$content .= '<div style="position: relative; left:0; top: 0; width: 100%; height: 600px; background-color: white">';
			$content .= '<iframe id="'.$genid.'ifr" name="'.$genid.'ifr" style="width:100%;height:100%" frameborder="0" src="'.$url.'" 
							onload="javascipt:iframe=document.getElementById(\''.$genid.'ifr\'); iframe.parentNode.style.height = Math.min(600, iframe.contentWindow.document.body.scrollHeight + 60) + \'px\' ;">
						</iframe>';
			'<script>if (Ext.isIE) document.getElementById(\''.$genid.'ifr\').contentWindow.location.reload();</script>';
			$content .= '<a class="ico-expand" style="display: block; width: 16px; height: 16px; cursor: pointer; position: absolute; right: 20px; top: 2px" title="' . lang('expand') . '" onclick="og.expandDocumentView(this)"></a>
				</div>';

			if ($remove_quoted) {
				$content .= '<a id="'.$genid.'showQuotedText" style="font-family:verdana,arial,helvetica,sans-serif; font-size:11px; line-height:150%; cursor:pointer; color:#003562; padding-left:10px;"
						onclick="og.showQuotedHtml(\''.$pre.'\', \''.gen_id().'\', \''.$genid.'\', \''.$user_token.'\');">
						:: '.lang('show quoted text').' ::</a>';					
			}
			$content .= '
				<input type="hidden" id="'.$genid.'viewingImages" value="'.($remove_images?'no':'yes').'" />
				<input type="hidden" id="'.$genid.'viewingQuoted" value="'.($remove_quoted?'no':'yes').'" />
			';
		} else {
			if ($email->getBodyPlain() != '') {
				$remove_quoted = MailUtilities::hasQuotedText($email->getBodyPlain()) && $hide_quoted_text_in_emails;
				$content = "";
				if ($remove_quoted) {
					$content = MailUtilities::replaceQuotedText($email->getBodyPlain(), '-----'.lang('hidden quoted text').'-----');
					$content = '<div id="'.$genid.'noQuoteMail">' . escape_html_whitespace(convert_to_links(clean($content))) . '</div>';
					$content = str_replace('-----'.lang('hidden quoted text')."-----", '<span style="color: #777;font-style:italic;padding: 5px 20px">&lt;'.lang('hidden quoted text').'&gt;</span>', $content);
					$content .= '<a class="internalLink" style="padding-left:10px;" id="'.$genid.'quotedLink" href="#" onclick="og.showQuotedText(\''.$genid.'\')">:: '.lang('show quoted text').' ::</a>';
				}
				$content .= '<div id="'.$genid.'completeMail"'.($remove_quoted ? ' style="display:none"' : '').'>' . escape_html_whitespace(convert_to_links(clean($email->getBodyPlain()))) . '</div>';
				$content = '<div style="max-height: 600px; overflow: auto;">' . $content . '</div>';
			} else $content = '<div></div>';
		}
		$strDraft = '';
		if ($email->getIsDraft()) {
			$strDraft = "<span style='font-size:80%;color:red'>&nbsp;".lang('draft')."</style>";
		}
				
		tpl_assign("title", lang('email') . ': ' . clean($email->getSubject()).$strDraft);
		tpl_assign('iconclass', $email->isTrashed()? 'ico-large-email-trashed' : ($email->isArchived() ? 'ico-large-email-archived' : 'ico-large-email'));
		tpl_assign("mail_conversation_block" , $conversation_block);
		tpl_assign("content", $content);
		tpl_assign("object", $email);
		tpl_assign("description", $description);
		
		$this->includeTemplate(get_template_path('view', 'co'));
	?>

</div>
</div>
<?php } else { echo lang('email not available'); } //if ?>

