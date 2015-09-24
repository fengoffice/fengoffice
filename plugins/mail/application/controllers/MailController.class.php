<?php
/**
 * Mail controller
 * @version 1.0
 * @author Carlos Palma <chonwil@gmail.com>
 */
class MailController extends ApplicationController {
	
		
	var $plugin_name = "mail";
	
	/**
	 * Construct the MailController
	 *
	 * @access public
	 * @param void
	 * @return MailController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		Env::useHelper('MailUtilities.class', $this->plugin_name);
		require_javascript("AddMail.js",  $this->plugin_name);
	}

	function init() {
		require_javascript('MailAccountMenu.js',  $this->plugin_name);
		require_javascript("MailManager.js",  $this->plugin_name);
		ajx_current("panel", "mails-containerpanel", null, null, true);
		ajx_replace(true);
	}
	
	private function getDefaultAccountId($user = null) {
		if (!$user) $user = logged_user();
		$default_account = MailAccountContacts::findOne(array('conditions' => array('`contact_id` = ? AND `is_default` = ?', $user->getId(), true)));
		if ($default_account instanceof MailAccountContact && $default_account->getAccount() instanceof MailAccount) {
			return $default_account->getAccount()->getId();
		}
		return 0;
	}
	
	private function build_original_mail_info($original_mail, $type = 'plain') {
		$loc = new Localization();
		$loc->setDateTimeFormat("D, d M Y H:i:s O");
		if ($type == 'plain') {
			$cc_cell = $original_mail->getCc() == '' ? '' : "\n".lang('mail CC').": ".$original_mail->getCc();
			$str = "\n\n----- ".lang('original message')."-----\n".lang('mail from').": ".$original_mail->getFrom()."\n".lang('mail to').": ".$original_mail->getTo()."$cc_cell\n".lang('mail sent').": ".$loc->formatDateTime($original_mail->getSentDate(), logged_user()->getTimezone())."\n".lang('mail subject').": ".$original_mail->getSubject()."\n\n";
		} else {
			$cc_cell = $original_mail->getCc() == '' ? '' : "<tr><td>".lang('mail CC').": ".$original_mail->getCc()."</td></tr>";
			$str = "<br><br><table><tr><td>----- ".lang('original message')." -----</td></tr><tr><td>".lang('mail from').": ".$original_mail->getFrom()."</td></tr><tr><td>".lang('mail to').": ".$original_mail->getTo()."</td></tr>$cc_cell<tr><td>".lang('mail sent').": ".$loc->formatDateTime($original_mail->getSentDate(), logged_user()->getTimezone())."</td></tr><tr><td>".lang('mail subject').": ".$original_mail->getSubject()."</td></tr></table><br>";
		}		 
		return $str;
	}
	
	function reply_mail() {
		$this->setTemplate('add_mail');
		$mail = new MailContent();
		$original_mail = MailContents::findById(get_id());
		if(!$original_mail instanceof MailContent) {
			flash_error(lang('email dnx'));
			ajx_current("empty");
			return;
		}
		$mail_data = array_var($_POST, 'mail', null);
		if (!is_array($mail_data)) {
			$re_subject = str_starts_with(strtolower($original_mail->getSubject()), 're:') ? $original_mail->getSubject() : 'Re: ' . $original_mail->getSubject();
			
			$clean_mail = $this->cleanMailBodyAndGetMailData($original_mail);
				
			$mail_data = array(
				'to' => $clean_mail['to'],
				'cc' => $clean_mail['cc'],
				'type' => $clean_mail['type'],
				'subject' => $re_subject,
				'account_id' => $original_mail->getAccountId(),
				'body' =>  '<div id="original_mail">'.$clean_mail['clean_body'].'</div>',
				'conversation_id' => $original_mail->getConversationId(),
				'in_reply_to_id' => $original_mail->getMessageId(),
				'original_id' => $original_mail->getId(),
				'last_mail_in_conversation' => MailContents::getLastMailIdInConversation($original_mail->getConversationId(), true),
				'pre_body_fname' => $clean_mail['cache_fname'],
			); // array
		} // if
		$mail_accounts = MailAccounts::getMailAccountsByUser(logged_user());
		if(!$mail_accounts) {
			flash_error(lang('no mail accounts set'));
			ajx_current("empty");
		}
		
		$def_acc = $this->getDefaultAccountId();
		if ($def_acc > 0){
			tpl_assign('default_account_replay', $def_acc);
		}else{
			tpl_assign('default_account_replay', $mail_accounts[0]->getId());
		}
		tpl_assign('mail', $mail);
		tpl_assign('mail_data', $mail_data);
		tpl_assign('mail_accounts', $mail_accounts);
		
	}
	
	private function cleanMailBodyAndGetMailData($original_mail, $copy_attachments = false) {
		$return_mail_data = array();
		
		if ($original_mail->getBodyHtml() != '') $type = 'html';
		else $type = user_config_option('last_mail_format');
		if (!$type) $type = 'plain';
		if(!$original_mail->getIsRead(logged_user()->getId())){
			$original_mail->setIsRead(logged_user()->getId(), true);
		}
		if ($original_mail->getBodyHtml() != '' && $type == 'html'){
			if (!defined('SANDBOX_URL')) {
				$re_body = purify_html($original_mail->getBodyHtml());
			} else {
				$html_content = $original_mail->getBodyHtml();
				if(substr_count($html_content, "<style>") != substr_count($html_content, "</style>") && substr_count($html_content, "/* Font Definitions */") >= 1) {
					$p1 = strpos($html_content, "/* Font Definitions */", 0);
					$html_content1 = substr($html_content, 0, $p1);
					$p0 = strrpos($html_content1, "</style>");
					$html_content = ($p0 >= 0 ? substr($html_content1, 0, $p0) : $html_content1) . substr($html_content, $p1);
		
					$re_body = str_replace_first("/* Font Definitions */","<style>", $html_content);
				} else {
					$re_body = $html_content;
				}
			}
		}else{
			$re_body = $original_mail->getBodyPlain();
		}
		if ($type == 'html') {
			$pre_quote = '<blockquote type="cite" style="margin-left: 10px; padding-left:10px; border-left: 1px solid #987ADD;">';
			$post_quote = "</blockquote>";
		} else {
			$pre_quote = "";
			$post_quote = "";
			$lines = explode("\n", $re_body);
			$re_body = "";
			foreach($lines as $line) {
				$re_body .= ">$line\n";
			}
		}
		if ($original_mail->getBodyHtml() == '' && $type == 'html') {
			$re_body = str_replace("\n", "<br>", $re_body);
		}
		$re_info = $this->build_original_mail_info($original_mail, $type);
			
		$pos = stripos($re_body, "<body");
		if ($pos !== FALSE) {
			$pos = stripos($re_body, ">", $pos);
		}
			
		if ($pos !== FALSE) {
			$re_body = substr($re_body, 0, $pos+1) . $re_info . $pre_quote . substr($re_body, $pos+1) . $post_quote;
		} else {
			$re_body = $re_info . $pre_quote . $re_body . $post_quote;
		}
			
		// Put original mail images in the reply or foward
		if ($original_mail->getBodyHtml() != '') {
			MailUtilities::parseMail($original_mail->getContent(), $decoded, $parsedEmail, $warnings);
			$tmp_folder = "/tmp/" . $original_mail->getId() . "_reply";
			if (is_dir(ROOT . $tmp_folder)) remove_dir(ROOT . $tmp_folder);
			if ($parts_container = array_var($decoded, 0)) {
				$re_body = self::rebuild_body_html($re_body, array_var($parts_container, 'Parts'), $tmp_folder);
			}
		}
		
		$attachs = array();
		if($copy_attachments){
			//Attachs			
			if ($original_mail->getHasAttachments()) {
				$utils = new MailUtilities();
				if (!isset($parsedEmail)) {
					MailUtilities::parseMail($original_mail->getContent(), $decoded, $parsedEmail, $warns);
				}
				if (isset($parsedEmail['Attachments'])) $attachments = $parsedEmail['Attachments'];
				foreach($attachments as $att) {
					$fName = iconv_mime_decode($att["FileName"], 0, "UTF-8");
					$fName = str_replace(':', ' ', $fName);
					$fileType = $att["content-type"];
					$fid = gen_id();
					$attachs[] = "FwdMailAttach:$fName:$fileType:$fid";
					file_put_contents(ROOT . "/tmp/" . logged_user()->getId() . "_" .$original_mail->getAccountId() . "_FwdMailAttach_$fid", $att['Data']);
				}
			}
		}
		
		$to = $original_mail->getFrom();
		$cc = "";
		$my_address = $original_mail->getAccount()->getEmailAddress();
		if (array_var($_GET,'all','') != '') {
			if ($original_mail->getFrom() != $my_address) {
				$cc = $original_mail->getTo() . "," . $original_mail->getCc();
				$regexp = '/[^\,]*' . preg_quote($my_address) . '[^,]*/';
				$cc = preg_replace($regexp, "", $cc);
				$cc = preg_replace('/\,\s*?\,/', ',', $cc);
				$cc = trim($cc, ',');
				$to = $original_mail->getFrom();
			} else {
				$cc = $original_mail->getCc();
				$to = $original_mail->getTo();
			}
		}
		
		$cache_fname = "";
		if (strlen($re_body) > 200 * 1024) {
			$cache_fname = gen_id();
			file_put_contents(ROOT . "/tmp/$cache_fname", $re_body);
			$re_body = lang("content too long not loaded");
		}
			
		if (defined('SANDBOX_URL')) {
			$re_body = str_replace('<!--', '<!-- ', $re_body);
		}
		$re_body = preg_replace("/<body*[^>]*>/i",'<body>', $re_body);
				
		$return_mail_data['clean_body'] = $re_body;
		$return_mail_data['to'] = $to;
		$return_mail_data['cc'] = $cc;
		$return_mail_data['type'] = $type;		
		$return_mail_data['attachs'] = $attachs;
		$return_mail_data['cache_fname'] = $cache_fname;		
		
		return $return_mail_data;
	}
	
	private function checkRequiredCustomPropsBeforeSave($custom_props) {
		$errors = array();
		if (is_array($custom_props)) {
			foreach ($custom_props as $id => $value) {
				$cp = CustomProperties::findById($id);
				if (!$cp) continue;
				if ($cp->getIsRequired() && $value == '') {
					 $errors[] = lang('custom property value required', $cp->getName());
				}
			}
		}
		return $errors;
	}
	
	function change_email_folder() {
		$email = MailContents::findById(get_id());
		if (!$email instanceof MailContent) {
			flash_error(lang('email dnx'));
			ajx_current("empty");
			return;
		}
		if ($email->getIsDeleted()) {
			flash_error(lang('email dnx deleted'));
			ajx_current("empty");
			return;
		}
		if (!$email->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$folder = array_var($_GET, 'newf');
		if (is_numeric($folder)) {
			try {
				DB::beginWork();
				$email->setState($folder);
				$email->save();
                                
                                if($folder == 4 || $folder == 0)
                                {
                                    $this->mark_spam_no_spam($folder,$email);                                    
                                }
                                
				DB::commit();
				ajx_current("back");
				return;
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
				return;
			}
		} else {
			flash_error($e->getMessage());
			ajx_current("empty");
			return;
		}
	}

	/**
	 * Add single mail
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add_mail() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->addHelper('textile');
		$mail_accounts = MailAccounts::getMailAccountsByUser(logged_user());
		if (count($mail_accounts) < 1){
			flash_error(lang('no mail accounts set'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_mail');
		$mail_data = array_var($_POST, 'mail');
		$sendBtnClick = array_var($mail_data, 'sendBtnClick', '') == 'true' ? true : false;
		$isDraft = array_var($mail_data, 'isDraft', '') == 'true' ? true : false;
		$isUpload = array_var($mail_data, 'isUpload', '') == 'true' ? true : false;
		$autosave = array_var($mail_data,'autosave', '') == 'true';

		$id = array_var($mail_data, 'id');
		$mail = MailContents::findById($id);
		$isNew = false;
		if (!$mail) {
			$isNew = true;
			$mail = new MailContent();
		}
		
		tpl_assign('mail_to', urldecode(array_var($_GET, 'to')));
		tpl_assign('link_to_objects', array_var($_GET, 'link_to_objects'));

		$def_acc_id = $this->getDefaultAccountId();
		if ($def_acc_id > 0){
			$def_acc = MailAccounts::getAccountById($def_acc_id);
			if ($def_acc instanceof MailAccount){
				tpl_assign('default_account', $def_acc);
			}			
		}		
		tpl_assign('mail', $mail);
		tpl_assign('mail_data', $mail_data);
		tpl_assign('mail_accounts', $mail_accounts);
		
		Hook::fire('send_to', array_var($_GET, 'ids'),array_var($_GET, 'me'));		

		// Form is submited
		if (is_array($mail_data)) {
			$account = 	MailAccounts::findById(array_var($mail_data, 'account_id'));
			if (!$account instanceof MailAccount) {
				flash_error(lang('mail account dnx'));
				ajx_current("empty");
				return;
			}
			$accountUser = MailAccountContacts::getByAccountAndContact($account, logged_user());
			if (!$accountUser instanceof MailAccountContact) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			}
			if ($account->getOutgoingTrasnportType() == 'ssl' || $account->getOutgoingTrasnportType() == 'tls') {
				$available_transports = stream_get_transports();
				if (array_search($account->getOutgoingTrasnportType(), $available_transports) === FALSE) {
					flash_error('The server does not support SSL.');
					ajx_current("empty");
					return;
				}
			}
			$cp_errs = $this->checkRequiredCustomPropsBeforeSave(array_var($_POST, 'object_custom_properties', array()));
			if (is_array($cp_errs) && count($cp_errs) > 0) {
				foreach ($cp_errs as $err) {
					flash_error($err);
				}
				ajx_current("empty");
				return;
			}

			$subject = array_var($mail_data, 'subject');
			$body = array_var($mail_data, 'body');
			if (($pre_body_fname = array_var($mail_data, 'pre_body_fname')) != "") {
				$body = str_replace(lang('content too long not loaded'), '', $body, $count=1);
				$tmp_filename = ROOT . "/tmp/$pre_body_fname";
				if (is_file($tmp_filename)) {
					$body .= file_get_contents($tmp_filename);
					if (!$isDraft) @unlink($tmp_filename);
				}
			}
			if (array_var($mail_data, 'format') == 'html') {
				$css = "font-family:sans-serif,Arial,Verdana; font-size:14px; line-height:1.6; color:#222;";
				Hook::fire('email_base_css', null, $css);
				str_replace(array("\r","\n"), "", $css);
				$body = '<div style="' . $css . '">' . $body . '</div>';
				$body = str_replace('<blockquote>', '<blockquote style="border-left:1px solid #987ADD;padding-left:10px;">', $body);
			}
			$type = 'text/' . array_var($mail_data, 'format');
			
			$to = trim(array_var($mail_data, 'to'));
			if (str_ends_with($to, ",") || str_ends_with($to, ";")) $to = substr($to, 0, strlen($to) - 1);
			$mail_data['to'] = $to;
			$cc = trim(array_var($mail_data,'cc'));
			if (str_ends_with($cc, ",") || str_ends_with($cc, ";")) $cc = substr($cc, 0, strlen($cc) - 1);
			$mail_data['cc'] = $cc;			
			$bcc = trim(array_var($mail_data,'bcc'));
			if (str_ends_with($bcc, ",") || str_ends_with($bcc, ";")) $bcc = substr($bcc, 0, strlen($bcc) - 1);
			$mail_data['bcc'] = $bcc;
			
			if (!$isDraft && trim($to.$cc.$bcc) == '') {
				flash_error(lang('recipient must be specified'));
				ajx_current("empty");
				return;
			}
			
			$invalid_to = MailUtilities::validate_email_addresses($to);
			if (is_array($invalid_to)) {
				flash_error(lang('error invalid recipients', lang('mail to'), implode(", ", $invalid_to)));
				ajx_current("empty");
				return;
			}
			$invalid_cc = MailUtilities::validate_email_addresses($cc);
			if (is_array($invalid_cc)) {
				flash_error(lang('error invalid recipients', lang('mail CC'), implode(", ", $invalid_cc)));
				ajx_current("empty");
				return;
			}
			$invalid_bcc = MailUtilities::validate_email_addresses($bcc);
			if (is_array($invalid_bcc)) {
				flash_error(lang('error invalid recipients', lang('mail BCC'), implode(", ", $invalid_bcc)));
				ajx_current("empty");
				return;
			}
			
			$last_mail_in_conversation = array_var($mail_data, 'last_mail_in_conversation');
			$conversation_id = array_var($mail_data, 'conversation_id');
			if ($last_mail_in_conversation && $conversation_id) {
				$new_mail_in_conversation = MailContents::getLastMailIdInConversation($conversation_id, true);
				if ($new_mail_in_conversation != $last_mail_in_conversation) {
					ajx_current("empty");
					evt_add("new email in conversation", array(
						'id' => $new_mail_in_conversation,
						'genid' => array_var($_POST, 'instanceName')
					));
					return;
				}
			}
			
			$mail->setFromAttributes($mail_data);
			$mail->setTo($to);
			$mail->setCc($cc);
			$mail->setBcc($bcc);
			$mail->setSubject($mail_data['subject']);
				
			$utils = new MailUtilities();
			
			// attachment
			$linked_attachments = array();
 			$attachments = array();
 			$project_files_attachments = array();
 			$objects = array_var($_POST, 'linked_objects');
 			$attach_contents = array_var($_POST, 'attach_contents', array());
 			
 			$original_email = isset($mail_data['original_id']) ? MailContents::findById($mail_data['original_id']) : null;
 			
 			if (is_array($objects)) {
 				$err = 0;
 				$count = -1;
 				foreach ($objects as $objid) {
 					$count++;
 					$split = explode(":", $objid);
 					if (count($split) == 2) {
 						$object = Objects::instance()->findObject($split[1]);
 					}else if (count($split) == 4) {
 						if ($split[0] == 'FwdMailAttach') {
 							$tmp_filename = ROOT . "/tmp/" . logged_user()->getId() . "_" . ($original_email ? $original_email->getAccountId() : $mail_data['account_id']) . "_FwdMailAttach_" . $split[3];
 							if (is_file($tmp_filename)) {
	 							$attachments[] = array(
			 						"data" => file_get_contents($tmp_filename),
			 						"name" => $split[1],
			 						"type" => $split[2]
			 					);
			 					continue;
 							}
 						}
 					}
 					
 					if (!isset($object) || !$object) {
 						flash_error(lang('file dnx'));
	 					$err++;
 					} else {
	 					if (isset($attach_contents[$count])) {
	 						if ($split[0] == 'ProjectFiles') {
			 					$file = ProjectFiles::findById($object->getId());
			 					if (!($file instanceof ProjectFile)) {
			 						flash_error(lang('file dnx'));
			 						$err++;
			 					} // if
//			 					if(!$file->canDownload(logged_user())) {
//			 						flash_error(lang('no access permissions'));
//			 						$err++;
//			 					} // if
								$project_files_attachments[] = $file;
			 
			 					$attachments[] = array(
			 						"data" => $file->getFileContent(),
			 						"name" => $file->getFilename(),
			 						"type" => $file->getTypeString()
			 					);
	 						} else if ($split[0] == 'MailContents') {
	 							$email = MailContents::findById($object->getId());
			 					if (!($email instanceof MailContent)) {
			 						flash_error(lang('email dnx'));
			 						$err++;
			 					} // if
			 					if(!$email->canView(logged_user())) {
			 						flash_error(lang('no access permissions'));
			 						$err++;
			 					} // if
			 
			 					$attachments[] = array(
			 						"data" => $email->getContent(),
			 						"name" => $email->getSubject() . ".eml",
			 						"type" => 'message/rfc822'
			 					);
	 						}
	 					} else {
	 						$linked_attachments[] = array(
		 						"data" => $object->getViewUrl(),
		 						"name" => clean($object->getObjectName()),
		 						"type" => lang($object->getObjectTypeName()),
	 							"id" => $object->getId(),
		 					);
	 					}
 					}
 				}
 				if ($err > 0) {
 					flash_error(lang('some objects could not be linked', $err));
 					ajx_current('empty');
 					return;
 				}
 			}
				
			$to = preg_split('/;|,/', $to);
			$to = $utils->parse_to($to);
		 			
			if ($body == '') $body.=' ';

			try {
				$linked_users = array();
				
				//create contacts from recipients of email
				if (user_config_option('create_contacts_from_email_recipients') || can_manage_contacts(logged_user())) {
					foreach ($to as $to_user) {
						$linked_user = Contacts::getByEmail($to_user[1]);
						if (!$linked_user instanceof Contact) {
							try {
								DB::beginWork();
								$linked_user = create_user_from_email($to_user[1], $to_user[0], null, false);
								DB::commit();
							} catch (Exception $e) {
								Logger::log($e->getMessage());
								DB::rollback();
							}
						}
						if ($linked_user instanceof Contact) $linked_users[] = $linked_user;
					}
				}
				
				if (count($linked_attachments)) {
					$linked_atts = $type == 'text/html' ? '<div style="font-family:arial;"><br><br><br><span style="font-size:12pt;font-weight:bold;color:#777">'.lang('linked attachments').'</span><ul>' : "\n\n\n-----------------------------------------\n".lang('linked attachments')."\n\n";
					foreach ($linked_attachments as $att) {
						$linked_atts .= $type == 'text/html' ? '<li><a href="'.$att['data'].'">' . $att['name'] . ' (' . $att['type'] . ')</a></li>' : $att['name'] . ' (' . $att['type'] . '): ' . $att['data'] . "\n";
						foreach ($linked_users as $linked_user) {
							try {
								$linked_user->giveAccessToObject(Objects::findObject($att['id']));
							} catch (Exception $e) {
								//Logger::log($e->getMessage());
							}
						}
					}
					$linked_atts .= $type == 'text/html' ? '</ul></div>' : '';
				} else $linked_atts = '';
				$body .= $linked_atts;
				
				if (count($attachments) > 0) {
					$i = 0;
					$str = "";
				/*	foreach ($attachments as $att) {
						$str .= "--000000000000000000000000000$i\n";
						$str .= "Name: ".$att['name'] .";\n";
						$str .= "Type: ".$att['type'] .";\n";
						//$str .= "Encoding: ".$att['type'] .";\n";
						$str .= base64_encode($att['data']) ."\n";
						$str .= "--000000000000000000000000000$i--\n";
						$i++;
					}
				*/
					
					$str = "#att_ver 2\n";
					foreach ($attachments as $att) {
						$rep_id = $utils->saveContent($att['data']);
						if (str_starts_with($att['name'], "#")) $att['name'] = str_replace_first("#", "@@sharp@@", $att['name']);
						$str .= $att['name'] . "|" . $att['type'] . "|" . $rep_id . "\n";
					}

					// save attachments, when mail is sent this file is deleted and full content is saved
					$repository_id = $utils->saveContent($str);
					if (!$isNew) {
						if (FileRepository::isInRepository($mail->getContentFileId())) {
							// delete old attachments
							$content = FileRepository::getFileContent($mail->getContentFileId());
							if (str_starts_with($content, "#att_ver")) {
								$lines = explode("\n", $content);
								foreach ($lines as $line) {
									if (!str_starts_with($line, "#") && trim($line) !== "") {
										$data = explode("|", $line);
										if (isset($data[2]) && FileRepository::isInRepository($data[2])) FileRepository::deleteFile($data[2]);
									}
								}
							}
							FileRepository::deleteFile($mail->getContentFileId());
						}
					}
					$mail->setContentFileId($repository_id);
				}

				$mail->setHasAttachments((is_array($attachments) && count($attachments) > 0) ? 1 : 0);
				$mail->setAccountEmail($account->getEmailAddress());

 				$mail->setSentDate(DateTimeValueLib::now());
 				$mail->setReceivedDate(DateTimeValueLib::now());
 				
				DB::beginWork();
				
				$msg_id = MailUtilities::generateMessageId($account->getEmailAddress());
				$conversation_id = array_var($mail_data, 'conversation_id');
				$in_reply_to_id = array_var($mail_data, 'in_reply_to_id');
				if ($conversation_id) {
					$in_reply_to = MailContents::findById(array_var($mail_data, 'original_id'));
					if ($in_reply_to instanceof MailContent && $in_reply_to->getSubject() && strpos(strtolower($mail->getSubject()), strtolower($in_reply_to->getSubject())) === false) {
						$conversation_id = null;
						$in_reply_to_id = '';
					}
				}
				if (!$conversation_id) $conversation_id = MailContents::getNextConversationId($account->getId());;
				
				
				$mail->setMessageId($msg_id);
				$mail->setConversationId($conversation_id);
				$mail->setInReplyToId($in_reply_to_id);
				
				$mail->setUid(gen_id());
				$mail->setState(($isDraft && !$sendBtnClick)? 2 : 200);
				
				set_user_config_option('last_mail_format', array_var($mail_data, 'format', 'plain'), logged_user()->getId());
				$body = utf8_safe($body);
				if (array_var($mail_data,'format') == 'html') {
					$body = preg_replace("/<body*[^>]*>/i",'<body>', $body);
					// commented because sometimes brokes the html and leaves the body in blank
					//$body = convert_to_links(preg_replace("/<body*[^>]*>/i",'<body>', $body));
					$mail->setBodyHtml($body);
					$mail->setBodyPlain(utf8_safe(html_to_text($body)));
				} else {
					$mail->setBodyPlain($body);
					$mail->setBodyHtml('');
				}
				$mail->setFrom($account->getEmailAddress());
				if ($accountUser->getIsDefault() && $accountUser->getSenderName()=="") {
					$mail->setFromName(logged_user()->getObjectName());
				} else {
					$mail->setFromName($accountUser->getSenderName());
				}

				$mail->save();
				//$mail->setIsRead(logged_user()->getId(), true);
				
				if(Plugins::instance()->isActivePlugin('mail_rules')){
					if (array_var($mail_data,'format') == 'html') {
						$img = MailTracks::get_track_mark_img($mail->getId());
						$body = $body.$img;
						$mail->setBodyHtml($body);
						$mail->setBodyPlain(utf8_safe(html_to_text($body)));
						$mail->save();					
					}
					
				}
				
				foreach ($project_files_attachments as $pfatt) {
					if ($pfatt instanceof ProjectFile) {
						$pfatt->setMailId($mail->getId());
						$pfatt->save();
						$pfatt->addToSharingTable();
					}
				}
				
				$member_ids = active_context_members(false);
				
				// if replying a classified email classify on same workspace
				$classified_with_conversation = false;
				if (array_var($mail_data, 'original_id')) {
					$in_reply_to = MailContents::findById(array_var($mail_data, 'original_id'));
					if ($in_reply_to instanceof MailContent) {
						$member_ids = array_merge($member_ids, $in_reply_to->getMemberIds());
						$classified_with_conversation = true;
					}
				}
				// autoclassify sent email if not classified
				if (!$classified_with_conversation) {
					$acc_mem_ids = explode(',', $account->getMemberId());
					foreach ($acc_mem_ids as $acc_mem_id) {
						$member_ids[] = $acc_mem_id;
					}
				}
				
				$object_controller = new ObjectController();
				foreach ($member_ids as $k => &$mem_id) {
					if ($mem_id == "") unset($member_ids[$k]);
				}
				if (count($member_ids) > 0) {
					//$object_controller->add_to_members($mail, $member_ids);
					$members = Members::instance()->findAll(array('conditions' => 'id IN ('.implode(',', $member_ids).')'));
					$mail->addToMembers($members, true);
					$mail->addToSharingTable();
				}
				$object_controller->link_to_new_object($mail);
				$object_controller->add_subscribers($mail);
				
				/*
				if (array_var($mail_data, 'link_to_objects') != ''){
					$lto = explode('|', array_var($mail_data, 'link_to_objects'));
					foreach ($lto as $object_string){
						$split_object = explode('-', $object_string);
						$object = Objects::findObject($split_object[1]);
						if ($object instanceof ContentDataObject){
							$mail->linkObject($object);
						}
					}
				}*/ 
				
				//subscribe user
				$user = Contacts::findById($account->getContactId());
				if($user instanceof Contact){
					$mail->subscribeUser($user);
				}
				
				
				
				/*if (user_config_option('create_contacts_from_email_recipients') && can_manage_contacts(logged_user())) {
					// automatically create contacts
					foreach ($to as $recipient) {
						$recipient_name = trim($recipient[0]);
						$recipient_address = trim($recipient[1]);
						if (!$recipient_address) continue;
						$contact = Contacts::getByEmail($recipient_address);
						if (!$contact instanceof Contact) {
							try {
								$contact = new Contact();
								$contact->addEmail($recipient_address, 'personal');
								if ($recipient_name && $recipient_name != $recipient_address) {
									$contact->setFirstName($recipient_name);
								} else {
									$index = strpos($recipient_address, "@");
									$recipient_name = substr($recipient_address, 0, $index);
									$contact->setFirstName($recipient_name);
								}
								$contact->save();
							} catch (Exception $e) {
								Logger::log($e->getMessage());
							}
						}
					}
				}*/
				$mail->addToSharingTable();
				$mail->orderConversation();
				DB::commit();
				ApplicationLogs::createLog($mail,  ApplicationLogs::ACTION_ADD,false,true);
				
				$mail->setIsRead(logged_user()->getId(), true);
				
				if (!$autosave) {
					if ($isDraft && !$sendBtnClick) {
						flash_success(lang('success save mail'));
						ajx_current("empty");
					} else {
						evt_add("must send mails", array("account" => $mail->getAccountId()));
						//flash_success(lang('mail is being sent'));
						ajx_current("back");
					}
					evt_add("email saved", array("id" => $mail->getId(), "instance" => array_var($_POST, 'instanceName')));
				} else {
					evt_add("draft mail autosaved", array("id" => $mail->getId(), "hf_id" => $mail_data['hf_id']));
					flash_success(lang('success autosave draft'));
					ajx_current("empty");
				}
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // add_mail

	private function readAttachmentsFromFileSystem(MailContent $mail, &$att_version) {
		$att_version = 2;
		if ($mail->getHasAttachments() && FileRepository::isInRepository($mail->getContentFileId())) {
					
			$attachments = array();
			$content = FileRepository::getFileContent($mail->getContentFileId());
			if (str_starts_with($content, "--")) {
				$att_version = 1;
			} else if (str_starts_with($content, "#att_ver")) {
				$att_version = trim(str_replace("#att_ver", "", substr($content, 0, strpos($content, "\n"))));
			}
			if ($att_version < 2) {
				$i=0; $offset = 0;
				while ($offset < strlen($content)) {
					$delim = "--000000000000000000000000000$i";
					if (strpos($content, $delim, $offset) !== FALSE) {
						$offset = strpos($content, $delim) + strlen($delim);
						$endline = strpos($content, ";", $offset);
						$name = substr($content, $offset + 1, $endline - $offset - 1);
						$pos = strpos($name, ":");
						$name = trim(substr($name, $pos+1, strlen($name) - $pos - 1));
						
						$offset = $endline + 1;
						$endline = strpos($content, ";", $offset);
						$type = substr($content, $offset + 1, $endline - $offset - 1);
						$pos = strpos($type, ":");
						$type = trim(substr($type, $pos+1, strlen($type) - $pos - 1));
	
						$offset = $endline + 1;
						$endline = strpos($content, "$delim--");
						$attachments[] = array('name' => $name, 'type' => $type, 'data' => base64_decode(trim(substr($content, $offset, $endline - $offset - 1))));
						$offset = strpos($content, "$delim--") + strlen("$delim--")+1;
					} else break;
					$i++;
				}
			} else {
				$lines = explode("\n", $content);
				foreach ($lines as $line) {
					if (!str_starts_with($line, "#") && trim($line) !== "") {
						$data = explode("|", $line);
						if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem ) {
							$path = FileRepository::getBackend()->getFilePath($data[2]);
						} else {
							$path = ROOT."/tmp/".gen_id();
							file_put_contents($path, FileRepository::getFileContent($data[2]));
						}
						if (str_starts_with($data[0], "@@sharp@@")) $data[0] = str_replace_first("@@sharp@@", "#", $data[0]);
						$attachments[] = array('name' => $data[0], 'type' => $data[1], 'path' => $path, 'repo_id' => $data[2]);
					}
				}
			}
		} else $attachments = null;
		
		return $attachments;
	}
	
	function send_outbox_mails($user=null,$user_account=null,$from_time=null) {
		if(is_null($user)){
			$user = logged_user();
		}
		
		$from_time_cond = "";
		if(!is_null($from_time) && $from_time instanceof DateTimeValue){
			$from_time_cond = " AND `created_on` > '".$from_time->toMySQL()."'";						
		}
		
		session_commit();
		set_time_limit(0);
		
		$utils = new MailUtilities();
		
		if(!is_null($user_account)){
			$userAccounts = array($user_account);
		}elseif (array_var($_GET, 'acc_id')){
			$account = MailAccounts::findById(array_var($_GET, 'acc_id'));
			$userAccounts = array($account);
		}else{
			$userAccounts = MailAccounts::getMailAccountsByUser($user);
		}
				
		$old_memory_limit = ini_get('memory_limit');
		if (php_config_value_to_bytes($old_memory_limit) < 256*1024*1024) {
			ini_set('memory_limit', '256M');
		}
		
		foreach ($userAccounts as $account) {
			
			$accountUser = null;
			if ($user instanceof Contact) $accountUser = MailAccountContacts::getByAccountAndContact($account, $user);
			
			if (!$account || !$accountUser) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			}
			$errorMailId = 0;
			
			try {
				$mails = MailContents::findAll(array(
					"conditions" => array("`is_deleted`=0 AND `state` >= 200 AND `account_id` = ? AND `created_by_id` = ? $from_time_cond", $account->getId(), $accountUser->getContactId()),
					"order" => "`state` ASC"
				));
				$count = 0;
				foreach ($mails as $mail) {
					/* @var $mail MailContent */
					if ($mail->getTrashedById() > 0) continue;
					
					// Only send mails with pair status
					if ($mail->getState() % 2 == 1) continue;
					
					//if is archived do not send it
					if($mail->isArchived())continue;
					
					// Set impair status, to avoid sending it again when sending it in parallel
					if (!$mail->addToStatus(1)) continue;
					
					try {
					
						$errorMailId = $mail->getId();
						$to = $mail->getTo();
						if (!$accountUser instanceof MailAccountContact) {
							$from = array($account->getEmailAddress() => $account->getFromName());
						} else {
							if ($user instanceof Contact && $accountUser->getIsDefault() && $accountUser->getSenderName()=="") {
								$from = array($account->getEmailAddress() => $user->getObjectName());
							} else {
								$from = array($account->getEmailAddress() => $accountUser->getSenderName());
							}
						}
						$subject = $mail->getSubject();
						$body = $mail->getBodyHtml() != '' ? $mail->getBodyHtml() : $mail->getBodyPlain();
						$cc = $mail->getCc();
						$bcc = $mail->getBcc();
						$type = $mail->getBodyHtml() != '' ? 'text/html' : 'text/plain';
						$msg_id = $mail->getMessageId();
						$in_reply_to_id = $mail->getInReplyToId();
		
						$attachments = self::readAttachmentsFromFileSystem($mail, $att_version);
						
						if ($mail->getBodyHtml() != '') {
							$images = get_image_paths($body);
						} else {
							$images = null;
						}
						
						// Inline images
						preg_match_all("/<img[^>]*src=[\"'][^\"']*[\"']/", $body, $matches);
						foreach ($matches as $match) {
							$pos = strpos($match[0], 'src="');
							$url = substr($match[0], $pos + 5);
							$url = substr($url, 0, -1);
							if (str_starts_with($url, ROOT_URL."/tmp/")) {
								$path = str_replace(ROOT_URL, ROOT, $url);
								if (!is_array($images)) $images = array();
								$images[$url] = $path;
							}
							
							if (str_starts_with($url, "data:")) {
								$mime_type = substr($url, 5, strpos($url, ';') - 5 );
								$extension = substr($mime_type, strpos($mime_type, "/")+1);

								if (!is_array($images)) $images = array();
								$file_url = ROOT_URL."/tmp/".gen_id().".$extension";
								$path = str_replace(ROOT_URL, ROOT, $file_url);
								
								$data = substr($url, strpos($url, "base64") + 6);
								file_put_contents($path, base64_decode($data));
								$images[$file_url] = $path;
								
								$body = str_replace($url, $file_url, $body);
							}
						}
						
						$mail->setSentDate(DateTimeValueLib::now());
						$mail->setReceivedDate(DateTimeValueLib::now());
						
						if (defined('DEBUG') && DEBUG) file_put_contents(ROOT."/cache/log_mails.txt", gmdate("d-m-Y H:i:s") . " antes de enviar: ".$mail->getId() . "\n", FILE_APPEND);
						
						$sentOK = $utils->sendMail($account->getSmtpServer(), $to, $from, $subject, $body, $cc, $bcc, $attachments, $account->getSmtpPort(), $account->smtpUsername(), $account->smtpPassword(), $type, $account->getOutgoingTrasnportType(), $msg_id, $in_reply_to_id, $images, $complete_mail, $att_version);
						$mail->orderConversation();
					} catch (Exception $e) {
						// actions are taken below depending on the sentOK variable
						Logger::log("Could not send email: ".$e->getMessage()."\nmail_id=".$mail->getId());
						$sentOK = false;
					}	
					
					try {
						if ($sentOK) {
							DB::beginWork();
							$mail->setState(3);
							$mail->save();
							DB::commit();
						} else {
							Logger::log("Swift returned sentOK = false after sending email\nmail_id=".$mail->getId());
							// set status to a higher and pair value, to retry later.
							if (!$mail->addToStatus(1)) Logger::log("Swift could not send the email and the state could not be set to retry later.\nmail_id=".$mail->getId());
						}
					} catch (Exception $e) {
						$extra_exception_info = ($sentOK == true) ? '(but it has been sent)' : '(and it has NOT been sent)';
						Logger::log("Exception marking email as sent ".$extra_exception_info.": ".$e->getMessage()."\nmail_id=".$mail->getId());
						if ($sentOK) DB::rollback();
					}
						
					if (defined('DEBUG') && DEBUG) file_put_contents(ROOT."/cache/log_mails.txt", gmdate("d-m-Y H:i:s") . " despues de enviar: ".$mail->getId() . "\n", FILE_APPEND);
					
					try {	
						//if user selected the option to keep a copy of sent mails on the server						
						if ($sentOK && config_option("sent_mails_sync") && $account->getSyncServer() != null && $account->getSyncSsl()!=null && $account->getSyncSslPort()!=null && $account->getSyncFolder()!=null && $account->getSyncAddr()!=null && $account->getSyncPass()!=null){							
							$check_sync_box = MailUtilities::checkSyncMailbox($account->getSyncServer(), $account->getSyncSsl(), $account->getOutgoingTrasnportType(), $account->getSyncSslPort(), $account->getSyncFolder(), $account->getSyncAddr(), $account->getSyncPass());
							if ($check_sync_box) MailUtilities::sendToServerThroughIMAP($account->getSyncServer(), $account->getSyncSsl(), $account->getOutgoingTrasnportType(), $account->getSyncSslPort(), $account->getSyncFolder(), $account->getSyncAddr(), $account->getSyncPass(), $complete_mail);
						}
					} catch (Exception $e) {
						Logger::log("Could not save sent mail in server through imap: ".$e->getMessage()."\nmail_id=".$mail->getId());
					}
					
					if (defined('DEBUG') && DEBUG) file_put_contents(ROOT."/cache/log_mails.txt", gmdate("d-m-Y H:i:s") . " antes de try: ".$mail->getId() . "\n", FILE_APPEND);
					
					try {
						if ($sentOK) {
							if (defined('DEBUG') && DEBUG) file_put_contents(ROOT."/cache/log_mails.txt", gmdate("d-m-Y H:i:s") . " sentOK=true: ".$mail->getId() . "\n", FILE_APPEND);
							if (FileRepository::isInRepository($mail->getContentFileId())) {
								if ($att_version >= 2) {
									// delete attachments from repository
									foreach ($attachments as $att) {
										if (FileRepository::isInRepository($att['repo_id'])) FileRepository::deleteFile($att['repo_id']);
									}
									if (isset($att['path']) && is_file($att['path'])) @unlink($att['path']); // if file was copied to tmp -> delete it
									if (defined('DEBUG') && DEBUG) file_put_contents(ROOT."/cache/log_mails.txt", gmdate("d-m-Y H:i:s") . " deleted attachments: ".$mail->getId() . "\n", FILE_APPEND);
								}
								FileRepository::deleteFile($mail->getContentFileId());
								if (defined('DEBUG') && DEBUG) file_put_contents(ROOT."/cache/log_mails.txt", gmdate("d-m-Y H:i:s") . " deleted att list: ".$mail->getId() . "\n", FILE_APPEND);
							}
						}else{
							if (defined('DEBUG') && DEBUG) file_put_contents(ROOT."/cache/log_mails.txt", gmdate("d-m-Y H:i:s") . " sentOK=false: ".$mail->getId() ." - Error when sending mail: SentOK = false \n", FILE_APPEND);							
						}
					} catch (Exception $e) {
						Logger::log("Exception deleting tmp repository files (attachment list): ".$e->getMessage()."\nmail_id=".$mail->getId());
					}
					
					try {
						DB::beginWork();
						if ($sentOK) {
							$content = $complete_mail;
							$repository_id = $utils->saveContent($content);
							if (defined('DEBUG') && DEBUG) file_put_contents(ROOT."/cache/log_mails.txt", gmdate("d-m-Y H:i:s") . " content saved: ".$mail->getId() . "\n", FILE_APPEND);
							
							$mail->setContentFileId($repository_id);
							$mail->setSize(strlen($content));
							if (config_option("sent_mails_sync") && isset($check_sync_box) && $check_sync_box)
								$mail->setSync(true);
							$mail->save();
							
							if (defined('DEBUG') && DEBUG) file_put_contents(ROOT."/cache/log_mails.txt", gmdate("d-m-Y H:i:s") . " email saved: ".$mail->getId() . "\n", FILE_APPEND);
							
							$properties = array("id" => $mail->getId());
							evt_add("mail sent", $properties);
							$count++;
						}
						DB::commit();
					} catch (Exception $e) {
						DB::rollback();
						Logger::log("Exception deleting tmp repository files (attachment list): ".$e->getMessage()."\nmail_id=".$mail->getId());
					}
				}
				if ($count > 0) {
					evt_add("mails sent", $count);
				}
			} catch (Exception $e) {
				$errorEmailUrl = '';
				if ($errorMailId > 0){
					$email = MailContents::findById($errorMailId);
					if ($email instanceof MailContent){
						Logger::log("failed to send mail: ".$e->getMessage()."\n".$email->getEditUrl());
						Logger::log($e->getTraceAsString());
						$errorEmailUrl = $email->getEditUrl();
					}
				}
				
				flash_error($errorEmailUrl != '' ? ($e->getMessage() /*. '<br/><a href="' . $errorEmailUrl . '">' . lang('view email') . '</a>'*/) : $e->getMessage());
				ajx_current("empty");
			}
		}
		ini_set('memory_limit', $old_memory_limit);
		ajx_current("empty");
		
	}
	
	//to send old sent emails to the email server (synchronization)
	function sync_old_sent_mails(){		
		if (!config_option("sent_mails_sync")){
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		set_time_limit(0);		
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}	

		$id = get_id();
		$account = MailAccounts::findById($id);
					
		if(!($account instanceof MailAccount)) {
			flash_error(lang('mailAccount dnx'));
			ajx_current("empty");
			return;
		}	

		$pass = $account->getSyncPass();		
		$server = $account->getSyncServer();
		$folder = $account->getSyncFolder();
		$address = $account->getSyncAddr();		
		if($pass == null || $server == null || $folder == null || $address == null) {		
			flash_error(lang('cant sync account'));
			ajx_current("empty");
			return;
		}			
		$conditions = array("conditions" => array("`sync`=0 AND `state` = 3 AND `account_id` =".$account->getId()));			
		
		$check_sync_box = MailUtilities::checkSyncMailbox($server, $account->getSyncSsl(), $account->getOutgoingTrasnportType(), $account->getSyncSslPort(), $folder, $address, $pass);			
		
		if ($check_sync_box){
			$sent_mails = MailContents::findAll($conditions);			
			if (count($sent_mails)==0){					
				flash_success(lang('mails on imap acc already sync'));
				ajx_current("empty");			
				return;
			}		
			foreach ($sent_mails as $mail){			
				try{
					DB::beginWork();				
					$content = $mail->getContent();		
					MailUtilities::sendToServerThroughIMAP($server, $account->getSyncSsl(), $account->getOutgoingTrasnportType(), $account->getSyncSslPort(), $folder, $address, $pass, $content);			
					$mail->setSync(true);
					$mail->save();
					DB::commit();				
				}
				catch(Exception $e){			
					DB::rollback();
				}						
			}			
			flash_success(lang('sync complete'));
			ajx_current("empty");
			return;
		}else{			
			flash_error(lang('invalid sync settings'));
			ajx_current("empty");
			return;
		}
	}
			
	function mark_as_unread() {
		ajx_current("empty");
		$email = MailContents::findById(array_var($_GET, 'id', 0));
		if ($email instanceof MailContent) {
			$object_controler = new ObjectController();
			$object_controler->do_mark_as_read_unread_objects(array($email->getId()), false);
			ajx_current("back");
		} else {
			flash_error(lang("email dnx"));
		}
	}
	
	function mark_as_spam() {
		ajx_current("empty");
		$csvids = array_var($_GET, 'ids');
		$ids = explode(",", $csvids);
		$succ = 0;
		$err = 0;
		foreach ($ids as $id) {
			$mail = Objects::findObject($id);
			if ($mail instanceof MailContent) {
				$mail->setState(4);
				$mail->save();
				$this->mark_spam_no_spam("4",$mail);
				$succ++;
			} else {
				$err++;
			}
		}
		if ($err <= 0) {
			flash_success(lang('success mark as spam', $succ));
		} else {
			flash_error(lang('error mark as spam', $succ));
		}
	}
	
	function mark_as_ham() {
		ajx_current("empty");
		$csvids = array_var($_GET, 'ids');
		$ids = explode(",", $csvids);
		$succ = 0;
		$err = 0;
		foreach ($ids as $id) {
			$mail = Objects::findObject($id);
			if ($mail instanceof MailContent) {
				$mail->setState(0);
				$mail->save();
				$this->mark_spam_no_spam("0",$mail);
				$succ++;
			} else {
				$err++;
			}
		}
		if ($err <= 0) {
			flash_success(lang('success mark as ham', $succ));
		} else {
			flash_error(lang('error mark as ham', $succ));
		}
	}
	
	/**
	 * View specific email
	 *
	 */
	function view() {
		$this->addHelper('textile');
		$email = MailContents::findById(get_id());
		if (!$email instanceof MailContent) {
			flash_error(lang('email dnx'));
			ajx_current("empty");
			return;
		}
		if ($email->getIsDeleted()) {
			flash_error(lang('email dnx deleted'));
			ajx_current("empty");
			return;
		}
		
		if (!$email->canView(logged_user())) {			
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		 
		tpl_assign('email', $email);
		
		$additional_body = "";

		$attachments = array();
		if($email->getState()>= 200) {
			$old_memory_limit = ini_get('memory_limit');
			if (php_config_value_to_bytes($old_memory_limit) < 256*1024*1024) {
				ini_set('memory_limit', '256M');
			}
			$attachments = self::readAttachmentsFromFileSystem($email, $att_ver);
			if ($attachments && is_array($attachments)) {
				foreach($attachments as &$attach) {
					if ($att_ver < 2) {
						$attach["FileName"] = $attach['name'];
						$attach['size'] = format_filesize(strlen($attach["data"]));
						unset($attach['name']);
						unset($attach['data']);
					} else {
						$attach["FileName"] = $attach['name'];
						$attach['size'] = format_filesize(filesize($attach["path"]));
						unset($attach['name']);
					}
				}
			} else {
				
			}
			ini_set('memory_limit', $old_memory_limit);
		} else {
			MailUtilities::parseMail($email->getContent(), $decoded, $parsedEmail, $warnings);
			if (isset($parsedEmail['Attachments'])) {
				$attachments = $parsedEmail['Attachments'];
			} else if ($email->getHasAttachments() && !in_array($parsedEmail['Type'], array('html', 'text', 'delivery-status')) && isset($parsedEmail['FileName'])) {
				// the email is the attachment
				$attach = array(
					'Data' => $parsedEmail['Data'],
					'Type' => $parsedEmail['Type'],
					'FileName' => $parsedEmail['FileName']
				);
				$attachments = array($attach);
			}
			$to_remove = array();
			foreach($attachments as $k => &$attach) {
				
				// dont show inline images in attachments box
				if (array_var($attach, 'FileDisposition') == 'inline' && array_var($parsedEmail, 'Type') == 'html') {
					$attach['hide'] = true;
				}
				if (array_var($attach, 'Type') == 'html') {
					$attach_tmp = $attach['Data'];
					$attach_tmp = preg_replace('/<html[^>]*[>]/', '', $attach_tmp);
					$attach_tmp = preg_replace('/<\/html>/', '', $attach_tmp);
					$attach_tmp = preg_replace('/<head>*<\/head>/', '', $attach_tmp);
					$attach_tmp = preg_replace('/<body[^>]*[>]/', '', $attach_tmp);
					$attach_tmp = preg_replace('/<\/body>/', '', $attach_tmp);
					
					$additional_body .= $attach_tmp;
					//break;
				}
			 	$attach['size'] = format_filesize(strlen($attach["Data"]));
			 	unset($attach['Data']);
			}
		}
		if ($email->getBodyHtml() != '') {
			$tmp_folder = "/tmp/" . $email->getAccountId() . "_" . logged_user()->getId()."_". $email->getId() . "_temp_mail_content_res";
			if (is_dir(ROOT . $tmp_folder)) remove_dir(ROOT . $tmp_folder);
			$parts_array = array_var($decoded, 0, array('Parts' => ''));
			$email->setBodyHtml(self::rebuild_body_html($email->getBodyHtml(), array_var($parts_array, 'Parts'), $tmp_folder) . $additional_body);
		}
		
		tpl_assign('attachments', $attachments);
		ajx_extra_data(array("title" => $email->getSubject(), 'icon' => 'ico-email'));
		ajx_set_no_toolbar(true);
		if (array_var($_GET, 'replace')) {
			ajx_replace(true);
		}
		
		if(!$email->getIsRead(logged_user()->getId())){
			$object_controler = new ObjectController();
			$object_controler->do_mark_as_read_unread_objects(array($email->getId()), true);
		}
		ApplicationReadLogs::createLog($email, null , ApplicationReadLogs::ACTION_READ);
	}
	
	/**
	 * Images that are attachments are saved to the filesystem and the links to them are rebuilt
	 * files are saved in root/tmp directory
	 */
	private function rebuild_body_html($html, $parts, $tmp_folder) {
		$enc_conv = EncodingConverter::instance();
		$html = preg_replace("/src=cid:([^[:space:]>]*)/i", "src=\"cid:$1\"", $html);
		$end_find = false;
		$to_find = 'src="cid:';
		$end_pos = 0;
		while (!$end_find) {
			$part_name = "";
			$cid_pos = strpos($html, $to_find, $end_pos);
			if ($cid_pos !== FALSE) {
				$cid_pos += strlen($to_find);
				$end_pos = strpos($html, '"', $cid_pos);

				$part_name = substr($html, $cid_pos, $end_pos-$cid_pos);
			} else 
				$end_find = true;

			if (!$end_find) {
				if (!is_dir(ROOT."$tmp_folder")) mkdir(ROOT."$tmp_folder");
				if (!is_array($parts)) continue;
				foreach ($parts as $part) {
					if (is_array($part['Headers'])) {
						
						if (isset($part['Headers']['content-id:']) && $part['Headers']['content-id:'] == "<$part_name>") {
							$filename = isset($part['FileName']) ? $part['FileName'] : $part_name;
							$filename = $enc_conv->convert(detect_encoding($filename), "UTF-8", $filename, false);
							$file_content = $part['Body'];
							$handle = fopen(ROOT."$tmp_folder/$filename", "wb");
							fwrite($handle, $file_content);
							fclose($handle);
							
							$html = str_replace('src="cid:'.$part_name.'"', "src=\"".ROOT_URL."$tmp_folder/$filename\"", $html);
							$html = str_replace('src="cid:'.$part_name, "src=\"".ROOT_URL."$tmp_folder/$filename\"", $html);
						} else {
							if (isset($part['Parts'])) $html = self::rebuild_body_html($html, $part['Parts'], $tmp_folder);
						}
					}
				}
			}
		}
		return $html;
	}
	
	function discard() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$email = MailContents::findById(get_id());
		if ($email && $email->getState() == 2) { // if mc is Draft
			$this->delete();
		}
		else ajx_current("back");
	}
	
	/**
	 * Delete specific email
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$email = MailContents::findById(get_id());
		if (!$email instanceof MailContent || $email->getIsDeleted()){
			flash_error(lang('email dnx'));
			ajx_current("empty");
			return;
		}
		 
		if (!$email->canDelete(logged_user())){
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		try {
			DB::beginWork();
			$email->trash();
			DB::commit();
			ApplicationLogs::createLog($email, ApplicationLogs::ACTION_TRASH);
			flash_success(lang('success delete email'));
			ajx_current("back");
			 
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete email'));
			ajx_current("empty");
		}
	} // delete

	/**
	 * Download specific file
	 *
	 * @param void
	 * @return null
	 */
	function download_attachment() {
		$emailId = array_var($_GET, 'email_id');
		$email = MailContents::findById($emailId);
		$attId = array_var($_GET, 'attachment_id');

		if ($email->getState() >= 200) {
			$attachments = self::readAttachmentsFromFileSystem($email, $att_ver);
			$attachment = $attachments[$attId];
			if ($att_ver >= 2) {
				$attachment['data'] = is_file($attachment['path']) ? file_get_contents($attachment['path']) : '';
			}
			$data_field = "data";
			$name_field = "name";
		} else {
			MailUtilities::parseMail($email->getContent(), $decoded, $parsedEmail, $warnings);
			
			if (isset($parsedEmail["Attachments"]) && isset($parsedEmail["Attachments"][$attId])) {
				$attachment = $parsedEmail["Attachments"][$attId];
			} else {
				if ($email->getHasAttachments() && !in_array($parsedEmail['Type'], array('html', 'text', 'delivery-status')) && isset($parsedEmail['FileName'])) {
					$attachment = array(
						'Data' => $parsedEmail['Data'],
						'Type' => $parsedEmail['Type'],
						'FileName' => $parsedEmail['FileName']
					);
				}
			}
			$data_field = "Data";
			$name_field = "FileName";
		}

		$content = $attachment[$data_field];
		$filename = str_starts_with($attachment[$name_field], "=?") ? iconv_mime_decode($attachment[$name_field], 0, "UTF-8") : utf8_safe($attachment[$name_field]);
		if (trim($filename) == "" && strlen($attachment[$name_field]) > 0) $filename = utf8_encode($attachment[$name_field]);
		$typeString = "application/octet-stream";
		$filesize = strlen($content);
		$inline = false;
		
		download_contents($content, $typeString, $filename, $filesize, !$inline);
		die();
	} // download_file

	/**
	 * Unclassify specific email
	 *
	 */
	function unclassify() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$email = MailContents::findById(get_id());
		if (!$email instanceof MailContent) {
			flash_error(lang('email dnx'));
			ajx_current("empty");
			return;
		}
		if ($email->getIsDeleted()) {
			flash_error(lang('email dnx deleted'));
			ajx_current("empty");
			return;
		}
		if (!$email->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		if ($this->do_unclassify($email) ) {
			flash_success(lang('success unclassify email'));
			ajx_current("back");
		} else {
			DB::rollback();
			//Logger::log("Error: Unclassify email\r\n".$e->getMessage());
			flash_error(lang('error unclassify email'));
			ajx_current("empty");
		}
	}
	
	function unclassify_many() {
		ajx_current("empty");
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			return;
		}
		try {
			$ids = explode(",", array_var($_GET, 'ids', ''));
			$count = 0;
			foreach ($ids as $id) {
				$parts = explode(":", $id);
				if (count($parts) > 1) $id = $parts[1];
				$email = MailContents::findById($id);
				if (!$email instanceof MailContent || $email->getIsdeleted() || !$email->canEdit(logged_user())) continue;
				
				if ($this->do_unclassify($email)) $count++;
			}
			flash_success(lang('success unclassify emails', $count));
		} catch (Exception $e) {
			flash_error($e->getMessage());
		}
	}
	
	function do_unclassify($main_email) {
		$conv_emails = MailContents::getMailsFromConversation($main_email);
		foreach ($conv_emails as $email) {
			try {
				DB::beginWork();
				//only get workspaces with R&W permissions
				/*
				 * 
				 * TODO members /dimension
				$all_workspaces = ProjectContacts::getProjectsByUser(logged_user());
				$ws_ids = array();
				foreach ($all_workspaces as $ws) {
					$has_ws_perm = logged_user()->hasProjectPermission($ws, ProjectUsers::CAN_WRITE_MAILS);
					$has_gr_perm = false;
					if (!$has_ws_perm) {
						$groups = logged_user()->getGroups();
						foreach($groups as $group) {
							$has_gr_perm = $group->getProjectPermission($ws, ProjectUsers::CAN_WRITE_MAILS);
						}
					}
					if ($has_ws_perm || $has_gr_perm) $ws_ids[]= $ws->getId();
				}
				$ws_ids = implode(',',$ws_ids);
				
				// remove workspaces
				$email->removeFromWorkspaces($ws_ids);
				*/
				// unclassify attachments, remove all allowed ws, then if file has no ws -> delete it 
				if ($email->getHasAttachments()) {
					MailUtilities::parseMail($email->getContent(),$decoded,$parsedEmail,$warnings);
					if (isset($parsedEmail['Attachments'])) {
						$files = ProjectFiles::findAll(array('conditions' => 'mail_id = '.$email->getId()));
						foreach ($files as $file) {
							// TODO Feng 2 members 
							/*
							$file->removeFromWorkspaces($ws_ids);
							$current_wss = $file->getWorkspaces();
							if (!is_array($current_wss) || count($current_wss) == 0) $file->delete();
							*/
						}
					}
				}
				DB::commit();
			
				return true;
			} catch (Exception $e) {
				DB::rollback();
				return false;
			}
		}
	}
	
	/**
	 * Classify specific email
	 *
	 */
	function classify() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$email = MailContents::findById(get_id());
		if (!$email instanceof MailContent){
			flash_error(lang('email dnx'));
			ajx_current("empty");
			return;
		}
		if ($email->getIsDeleted()){
			flash_error(lang('email dnx deleted'));
			ajx_current("empty");
			return;
		}
		if(!$email->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} 
		
		// set layout for modal form
		if (array_var($_REQUEST, 'modal')) {
			$this->setLayout("json");
			tpl_assign('modal', true);
		}
		
		MailUtilities::parseMail($email->getContent(), $decoded, $parsedEmail, $warnings);
		if (array_var($_POST,'submit')){
			$members = json_decode(array_var($_POST, 'members'));
			$this->do_classify_mail($email, $members); 
		} 
		tpl_assign('email', $email);
		tpl_assign('parsedEmail', $parsedEmail);
	}
	
	/**
	 * @param $email MailContent to classify
	 * @param $members array of member ids in which the $email will be classified
	 * @param $classification_data additional data needed for classification
	 * @param $process_conversation boolean, if true all the conversation will be classified
	 * @param $after_receiving boolean, indicates wheather the function was called after receiving the email or if only the user is classiffying the email 
	 */
	function do_classify_mail($email, $members, $classification_data = null, $process_conversation = true, $after_receiving = false) {
		try {
			$ctrl = new ObjectController();
			$create_task = false;//array_var($classification_data, 'create_task') == 'checked';
			
			if (is_null($classification_data)) {
				$classification_data = array();
				MailUtilities::parseMail($email->getContent(), $decoded, $parsedEmail, $warnings);
				for ($j=0; $j < count(array_var($parsedEmail, "Attachments", array())); $j++) {
					$classification_data["att_".$j] = true;
				}
			}
			foreach ($members as $k => &$mem_id) {
				if ($mem_id == "") unset($members[$k]);
			}
			
			$canWriteFiles = $this->checkFileWritability($classification_data, $parsedEmail);
			if ($canWriteFiles) {
				
				// if $after_receiving == true transaction has already been started, so dont start a new one
				if (!$after_receiving) {
					DB::beginWork();
				}
				
				if (count($members) > 0) {
					$account_owner = logged_user() instanceof contact ? logged_user() : Contacts::findById($email->getAccount()->getContactId());
					$ctrl->add_to_members($email, $members, $account_owner);
					
					if ($after_receiving && $email->getHasAttachments() && user_config_option('auto_classify_attachments')
						|| !$after_receiving && $email->getHasAttachments() && 
							(user_config_option('mail_drag_drop_prompt')=='classify' || user_config_option('mail_drag_drop_prompt')=='prompt' && intval(array_var($_REQUEST, 'classify_attachments')) > 0) ) {
							
						if (count($members) > 0) {
							$member_instances = Members::findAll(array('conditions' => 'id IN ('.implode(',',$members).')'));
							MailUtilities::parseMail($email->getContent(), $decoded, $parsedEmail, $warnings);
							$this->classifyFile($classification_data, $email, $parsedEmail, $member_instances, false, !$after_receiving);
						}
					}
					
				} else {
					$email->removeFromMembers(logged_user() instanceof contact ? logged_user() : Contacts::findById($email->getAccount()->getContactId(), $email->getMembers()));
				}
				
				if ($process_conversation) {
					$conversation = MailContents::getMailsFromConversation($email);
					
					if (count($members) > 0) {
						$member_instances = Members::findAll(array('conditions' => 'id IN ('.implode(',',$members).')'));
						foreach ($conversation as $conv_email) {
							// dont process orignal email again
							if ($conv_email->getId() == $email->getId()) {
								continue;
							}
							
							$account_owner = logged_user() instanceof contact ? logged_user() : Contacts::findById($conv_email->getAccount()->getContactId());
							$ctrl->add_to_members($conv_email, $members, $account_owner);
							MailUtilities::parseMail($conv_email->getContent(), $decoded, $parsedEmail, $warnings);
							
							if ($conv_email->getHasAttachments()) {
								if ($after_receiving && user_config_option('auto_classify_attachments')
									|| !$after_receiving && 
										(user_config_option('mail_drag_drop_prompt')=='classify' || user_config_option('mail_drag_drop_prompt')=='prompt' && intval(array_var($_REQUEST, 'classify_attachments')) > 0)) {
										
									$this->classifyFile($classification_data, $conv_email, $parsedEmail, $member_instances, false, !$after_receiving);
								}
							}
						}
					} else {
						if (!$after_receiving) {
							foreach ($conversation as $conv_email) {
								$conv_email->removeFromMembers(logged_user() instanceof contact ? logged_user() : Contacts::findById($email->getAccount()->getContactId(), $conv_email->getMembers()));
							}
						}
					}
				}
				
				if (!$after_receiving) {
					DB::commit();
				}
				
				flash_success(lang('success classify email'));
				if ($create_task) {
					ajx_replace(true);
					$this->redirectTo('task', 'add_task', array('from_email' => $email->getId(), 'replace' =>  1));
				} else {
					ajx_current("back");
					if (!$after_receiving) {
						evt_add("reload current panel", array());
					}
				}
			} else {
				flash_error(lang("error classifying attachment cant open file"));
				ajx_current("empty");
			} // If can write files
			// Error...
		} catch(Exception $e) {
			if (!$after_receiving) {
				DB::rollback();
			}
			flash_error($e->getMessage());
			ajx_current("empty");
		}
	}

	function classifyFile($classification_data, $email, $parsedEmail, $members, $remove_prev, $use_transaction) {
		if (!is_array($classification_data)) $classification_data = array();

		if (!isset($parsedEmail["Attachments"])) {
			return;
			//throw new Exception(lang('no attachments found for email'));
		}
		
		$account_owner = logged_user() instanceof contact ? logged_user() : Contacts::findById($email->getAccount()->getContactId());
		
		for ($c = 0; $c < count($classification_data); $c++) {
			if (isset($classification_data["att_".$c]) && $classification_data["att_".$c] && isset($parsedEmail["Attachments"][$c])) {
			  // dont classify inline images
			  if (array_var($parsedEmail["Attachments"][$c], 'FileDisposition') == 'attachment') {
				
				$att = $parsedEmail["Attachments"][$c];
				$fName = str_starts_with($att["FileName"], "=?") ? iconv_mime_decode($att["FileName"], 0, "UTF-8") : utf8_safe($att["FileName"]);
				if (trim($fName) == "" && strlen($att["FileName"]) > 0) $fName = utf8_encode($att["FileName"]);

				$extension = get_file_extension(basename($fName));
				$type_file_allow = FileTypes::getByExtension($extension);
				if(!($type_file_allow instanceof FileType) || $type_file_allow->getIsAllow() == 1){
					try {
						$remove_previous_members = $remove_prev;
						
						// check for file name and size, if there are some then compare the contents, if content is equal do not classify the attachment.
						$file_exists = 0;
						$possible_equal_file_rows = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."project_file_revisions r 
								INNER JOIN ".TABLE_PREFIX."objects o ON o.id=r.file_id  
								INNER JOIN ".TABLE_PREFIX."project_files f ON f.object_id=r.file_id
								WHERE o.name=".DB::escape($fName)." AND r.filesize='".strlen($att["Data"])."' 
								AND r.revision_number=(SELECT max(r2.revision_number) FROM ".TABLE_PREFIX."project_file_revisions r2 WHERE r2.file_id=r.file_id)");
						
						if (is_array($possible_equal_file_rows)) {
							foreach ($possible_equal_file_rows as $row) {
								$content = FileRepository::getFileContent($row['repository_id']);
								if ($content == $att['Data']) {
									// file already exists
									$file_exists = $row['file_id'];
									//Logger::log($email->getId()." - ".$row['mail_id']." - $fName");
									if ($remove_previous_members && $row['mail_id'] != $email->getId()) {
										$remove_previous_members = false;
									}
									break;
								}
							}
						}
						
						if ($file_exists > 0) {
							$file = ProjectFiles::findById($file_exists);
						} else {
							$file = ProjectFiles::findOne(array('conditions' => "mail_id = ".$email->getId()." AND o.name = ".DB::escape($fName).""));
						}
						
						if ($use_transaction) {
							DB::beginWork();
						}
						if ($file == null){
							$fileIsNew = true;
							$file = new ProjectFile();
							$file->setFilename($fName);
							$file->setIsVisible(true);
							$file->setMailId($email->getId());
							$file->setCreatedById($account_owner->getId());
							$file->save();							
						} else {
							$fileIsNew = false;
						}

						if($remove_previous_members){
							$dim_ids = array(0);
							foreach ($members as $m) $dim_ids[$m->getDimensionId()] = $m->getDimensionId();
							ObjectMembers::delete('`object_id` = ' . $file->getId() . ' AND `member_id` IN (SELECT `m`.`id` FROM `'.TABLE_PREFIX.'members` `m` WHERE `m`.`dimension_id` IN ('.implode(',',$dim_ids).'))');
						}

						$file->addToMembers($members);
						
						// fill sharing table in background
						add_object_to_sharing_table($file, $account_owner);
						//$file->addToSharingTable();

						$enc = array_var($parsedMail,'Encoding','UTF-8');
						$ext = utf8_substr($fName, strrpos($fName, '.') + 1, utf8_strlen($fName, $enc), $enc);

						$mime_type = '';
						if (Mime_Types::instance()->has_type($att["content-type"])) {
							$mime_type = $att["content-type"]; //mime type is listed & valid
						} else {
							$mime_type = Mime_Types::instance()->get_type($ext); //Attempt to infer mime type
						}

						$userid = logged_user() ? logged_user()->getId() : "0";
						$tempFileName = ROOT ."/tmp/". $userid ."x". gen_id();
						$fh = fopen($tempFileName, 'w') or die("Can't open file");
						fwrite($fh, $att["Data"]);
						fclose($fh);

						$fileToSave = array(
							"name" => $fName,
							"type" => $mime_type,
							"tmp_name" => $tempFileName,
							"error" => 0,
							"size" => filesize($tempFileName)
						);

						if ($fileIsNew || (!($file->getLastRevision() instanceof ProjectFileRevision))) {
							$revision = $file->handleUploadedFile($fileToSave, true, lang('attachment from email', $email->getSubject())); // handle uploaded file
							$revision->setCreatedById($account_owner->getId());
							$revision->save();
							ApplicationLogs::createLog($file, ApplicationLogs::ACTION_ADD);
					/*	}else{
							$revision = $file->getLastRevision();
							$new_hash = hash_file("sha256", $tempFileName);
							if ($revision->getHash() != $new_hash) {
								$revision = $file->handleUploadedFile($fileToSave, true, lang('attachment from email', $email->getSubject())); // handle uploaded file
								ApplicationLogs::createLog($file, ApplicationLogs::ACTION_ADD);
							}*/
						}
						
						if ($use_transaction) {
							DB::commit();
						}
						// Error...
					} catch(Exception $e) {
						if ($use_transaction) {
							DB::rollback();
						}
						flash_error($e->getMessage());
						ajx_current("empty");
					}
				}else{
					flash_error(lang('file extension no allow classify', $fName));
				}
				
				if (isset($tempFileName) && is_file($tempFileName)) unlink($tempFileName);
			}
		  }
		}
	}
	
	function showContents(){
		$email = MailContents::findById(get_id());
		$mailContents = MailContents::findById(get_id());
		if (!$email instanceof MailContent){
			flash_error(lang('email dnx'));
			ajx_current("empty");
			return;
		}
		if ($email->getIsDeleted()){
			flash_error(lang('email dnx deleted'));
			ajx_current("empty");
			return;
		}
		if (!$email->canView(logged_user())){
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		 
		echo $email->getContent(); die();
	}
	
	function show_html_mail() {
		$pre = array_var($_GET, 'pre');
		$filename = ROOT."/tmp/".$pre."_temp_mail_content.html";		
		if (!file_exists($filename)) {
			ajx_current("empty");
			return;
		}
		
		$content = file_get_contents($filename);		
		$encoding = detect_encoding($content, array('UTF-8', 'ISO-8859-1', 'WINDOWS-1252'));
		
		header("Expires: " . gmdate("D, d M Y H:i:s", mktime(date("H") + 2, date("i"), date("s"), date("m"), date("d"), date("Y"))) . " GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Content-Type: text/html;charset=".$encoding);
		header("Content-Length: " . (string) strlen($content));
		
		print($content);
		die();		
	}

	function checkFileWritability($classification_data, $parsedEmail){
		$userid = logged_user() instanceof Contact ? logged_user()->getId() : "0";
		$c = 0;
		while(isset($classification_data["att_".$c]))
		{
			if ($classification_data["att_".$c])
			{
				$att = $parsedEmail["Attachments"][$c];
				$fName = iconv_mime_decode($att["FileName"], 0, "UTF-8");
				$tempFileName = ROOT ."/tmp/". $userid ."x".$fName;
				$fh = fopen($tempFileName, 'w');
				if (!$fh){
					return false;
				}
				fclose($fh);
				unlink($tempFileName);
			}
			$c++;
		}
		return true;
	}


	function checkmail() {
		@set_time_limit(0);
		$accounts = MailAccounts::getMailAccountsByUser(logged_user());
		session_commit();
		if (is_array($accounts) && count($accounts) > 0){
			// check a maximum of $max emails per account
			$max = config_option("user_email_fetch_count", 10);
			MailUtilities::getmails($accounts, $err, $succ, $errAccounts, $mailsReceived, $max);

			$errMessage = "";
			if ($succ > 0) {
				$errMessage = lang('success check mail', $mailsReceived);
			}
			if ($err > 0){
				foreach($errAccounts as $error) {
					$errMessage .= lang('error check mail', $error["accountName"], $error["message"]);
				}
			}
			if ($succ > 0) $err = 0;
		} else {
			$err = 1;
			$errMessage = lang('no mail accounts set for check');
		}
		
		ajx_add("overview-panel", "reload");

		return array($err, $errMessage);
	}
	
	
	// ---------------------------------------------------
	//  Mail Accounts
	// ---------------------------------------------------

	/**
	 * Add email account
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add_account() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		if(!MailAccount::canAdd(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		$mailAccount = new MailAccount();
		tpl_assign('mailAccount', $mailAccount);

		$mailAccount_data = array_var($_POST, 'mailAccount');
		tpl_assign('mailAccount_data', $mailAccount_data);

		
		// get mail account users
		$mau = array(
			logged_user()->getId() => array(
				'name' => logged_user()->getObjectName(),
				'can_edit' => true,
			)
		);
		tpl_assign('mailAccountUsers', $mau);
		$is_admin = logged_user()->isAdministrator();
		tpl_assign('is_admin', $is_admin);
		
		if(is_array(array_var($_POST, 'mailAccount'))) {
			$email_address = array_var(array_var($_POST, 'mailAccount'), 'email_addr');
			/*if (MailAccounts::findOne(array('conditions' => "`email` = '$email_address'")) != null) {
				flash_error(lang('email address already exists'));
				ajx_current("empty");
				return;
			}*/

			try {
				$selected_user = array_var($_POST, 'users_select_box');					
				if (!$is_admin){
					$mail_account_user = logged_user(); 
				}
				else{
					$mail_account_user = Contacts::findById($selected_user);
				}
				
				$mailAccount_data['sync_ssl'] = array_var($mailAccount_data, 'sync_ssl') == "checked";
				$mailAccount_data['contact_id'] = $mail_account_user->getId();

				if (!array_var($mailAccount_data, 'del_mails_from_server', false)) $mailAccount_data['del_from_server'] = 0;
				if (!array_var($mailAccount_data, 'mark_read_on_server', false)) $mailAccount_data['mark_read_on_server'] = 0;
				$mailAccount->setFromAttributes($mailAccount_data);
				$mailAccount->setServer(trim($mailAccount->getServer()));
				$mailAccount->setPassword(MailUtilities::ENCRYPT_DECRYPT($mailAccount->getPassword()));
				$mailAccount->setSmtpPassword(MailUtilities::ENCRYPT_DECRYPT($mailAccount->getSmtpPassword()));
				$outbox_folder = array_var($_POST, 'outbox_select_box');
				if (config_option("sent_mails_sync") && isset($outbox_folder)){										
					$mailAccount->setSyncPass(MailUtilities::ENCRYPT_DECRYPT($mailAccount_data['sync_pass']));						
					$mailAccount->setSyncFolder($outbox_folder);					
				}
				
				$member_ids = json_decode(array_var($_POST, 'members'));
				$member_ids_str = "";
				foreach ($member_ids as $mid) {
					if (is_numeric($mid)) $member_ids_str .= ($member_ids_str == "" ? "" : ",") . $mid;
				}
				$mailAccount->setMemberId($member_ids_str);
				
				DB::beginWork();
				$mailAccount->save();
				
				
				// process users
				$account_users = Contacts::getAllUsers();
				$user_access = array_var($_POST, 'user_access');
				foreach ($account_users as $account_user) {
					$user_id = $account_user->getId();
					$access = array_var($user_access, $user_id);
					if (!is_null($access) && $access != 'none' || $user_id == $mail_account_user->getId()) {
						$account_user = new MailAccountContact();
						$account_user->setAccountId($mailAccount->getId());
						$account_user->setContactId($user_id);
						$account_user->setCanEdit($access == 'write');
						$account_user->save();
					}
				}
				
				if ($mailAccount->getIsImap() && is_array(array_var($_POST, 'check'))) {
					$real_folders = MailUtilities::getImapFolders($mailAccount);
					foreach ($real_folders as $folder_name) {
						if (!MailAccountImapFolders::findById(array('account_id' => $mailAccount->getId(), 'folder_name' => $folder_name))) {
							$acc_folder = new MailAccountImapFolder();
							$acc_folder->setAccountId($mailAccount->getId());
							$acc_folder->setFolderName($folder_name);
							$acc_folder->setCheckFolder($folder_name == 'INBOX');// By default only INBOX is checked
		
							$acc_folder->save();
						}
					}
					$imap_folders = MailAccountImapFolders::getMailAccountImapFolders($mailAccount->getId());
					
					$checks = array_var($_POST, 'check');
					if (is_array($imap_folders) && count($imap_folders)) {
						foreach ($imap_folders as $folder) {
							$folder->setCheckFolder(false);
							foreach ($checks as $name => $cf) {
								$name = str_replace(array('¡','!'), array('[',']'), $name);//to avoid a mistaken array if name contains [ 
								if (strcasecmp($name, $folder->getFolderName()) == 0) {
									$folder->setCheckFolder($cf == 'checked');
									break;
								}
							}
							$folder->save();
						}
					}
				}
				
				// personal settings
				if (array_var($_POST, 'is_default')) {
					$user_accounts = MailAccountContacts::getByContact(logged_user());
					foreach ($user_accounts as $acc) {
						if ($acc->getAccountId() != $mailAccount->getId()) {
							$acc->setIsDefault(false);
							$acc->save();				
						} else {
							$acc->setIsDefault(true);
							$acc->save();
						}
					}
				}
				$logged_user_settings = MailAccountContacts::getByAccountAndContact($mailAccount, logged_user());
				if ($logged_user_settings instanceof MailAccountContact) {
					$logged_user_settings->setSignature(array_var($_POST, 'signature'));
					$logged_user_settings->setSenderName(array_var($_POST, 'sender_name'));
					$logged_user_settings->save();
				}


				if ($mailAccount->canView(logged_user())) {
					evt_add("mail account added", array(
						"id" => $mailAccount->getId(),
						"name" => $mailAccount->getName(),
						"email" => $mailAccount->getEmail()
					));
				}

				// Restore old emails, if account was deleted and its emails weren't
				DB::executeAll("
						UPDATE ".TABLE_PREFIX."mail_contents mc INNER JOIN ".TABLE_PREFIX."objects o ON mc.object_id = o.id
							SET mc.account_id=".$mailAccount->getId()." 
							WHERE o.`created_by_id` = " . $mail_account_user->getId() . " 
									AND mc.`account_email` = '" . $mailAccount->getEmail() . "' 
									AND mc.`account_id` NOT IN (SELECT `id` FROM `" . TABLE_PREFIX . "mail_accounts`)");
				
				DB::commit();

				flash_success(lang('success add mail account', $mailAccount->getName()));
				ajx_current("back");
				// Error...
			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} 
		}
	} 

	/**
	 * Edit email account
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit_account() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_account', $this->plugin_name);

		$mailAccount = MailAccounts::findById(get_id());
		if(!($mailAccount instanceof MailAccount)) {
			flash_error(lang('mailAccount dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$mailAccount->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		// get mail account users
		$mailAccountUsers = MailAccountContacts::getByAccount($mailAccount);
		$mau = array();
		foreach ($mailAccountUsers as $au) {
			$contact = $au->getContact();
			if (!$contact instanceof Contact) continue;
			
			$mau[$au->getContactId()] = array(
				'name' => $contact->getObjectName(),
				'can_edit' => $au->getCanEdit(),
			);
		}
		tpl_assign('mailAccountUsers', $mau);
		
		$is_admin = logged_user()->isAdministrator();
		tpl_assign('is_admin', $is_admin);
		
		$mailAccount_data = array_var($_POST, 'mailAccount');
		if(!is_array($mailAccount_data)) {
			$mailAccount_data = array(
		          'user_id' => logged_user()->getId(),
		          'name' => $mailAccount->getName(),
		          'email' => $mailAccount->getEmail(),
		          'email_addr' => $mailAccount->getEmailAddress(),
		          'password' => MailUtilities::ENCRYPT_DECRYPT($mailAccount->getPassword()),
		          'server' => $mailAccount->getServer(),
		          'is_imap' => $mailAccount->getIsImap(),
		          'incoming_ssl' => $mailAccount->getIncomingSsl(),
		          'incoming_ssl_port' => $mailAccount->getIncomingSslPort(),
		          'smtp_server' => $mailAccount->getSmtpServer(),
		          'smtp_port' => $mailAccount->getSmtpPort(),
		          'smtp_username' => $mailAccount->getSmtpUsername(),
		          'smtp_password' => MailUtilities::ENCRYPT_DECRYPT($mailAccount->getSmtpPassword()),
		          'smtp_use_auth' => $mailAccount->getSmtpUseAuth(),
		          'del_from_server' => $mailAccount->getDelFromServer(),
				  'mark_read_on_server' => $mailAccount->getMarkReadOnServer(),
		          'outgoing_transport_type' => $mailAccount->getOutgoingTrasnportType(),
			); // array
			if(config_option('sent_mails_sync')){								
				$sync_details = array('sync_server' => $mailAccount->getSyncServer(),
				  'sync_addr' => $mailAccount->getSyncAddr(),
				  'sync_pass' => MailUtilities::ENCRYPT_DECRYPT($mailAccount->getSyncPass()),
				  'sync_ssl' => $mailAccount->getSyncSsl(),
				  'sync_sslport' => $mailAccount->getSyncSslPort());
				$mailAccount_data = array_merge ($mailAccount_data, $sync_details);
			}
		} else {
			if (!isset($mailAccount_data['sync_ssl']))
				$mailAccount_data['sync_ssl'] = false;
			if (!isset($mailAccount_data['incoming_ssl']))
				$mailAccount_data['incoming_ssl'] = false;
			if (!isset($mailAccount_data['is_default']))
				$mailAccount_data['is_default'] = false;
		}
		
		if ($mailAccount->getIsImap()) {
			/*try {
				$real_folders = MailUtilities::getImapFolders($mailAccount);
				DB::beginWork();
				foreach ($real_folders as $folder_name) {
					if (!MailAccountImapFolders::findById(array('account_id' => $mailAccount->getId(), 'folder_name' => $folder_name))) {
						$acc_folder = new MailAccountImapFolder();
						$acc_folder->setAccountId($mailAccount->getId());
						$acc_folder->setFolderName($folder_name);
						$acc_folder->setCheckFolder($folder_name == 'INBOX');// By default only INBOX is checked
					 
						$acc_folder->save();
					}
				}
				DB::commit();
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
			}*/
			 
			$imap_folders = MailAccountImapFolders::getMailAccountImapFolders($mailAccount->getId());
			tpl_assign('imap_folders', $imap_folders);
		}

		tpl_assign('mailAccount', $mailAccount);
		tpl_assign('mailAccount_data', $mailAccount_data);

		if(array_var($_POST, 'submitted')) {
			try {
				$user_changed = false;
				$selected_user = array_var($_POST, 'users_select_box');					
				if(!$is_admin){
					$selected_user = $mailAccount->getContactId();
				}
				
				$mail_account_user = Contacts::findById($selected_user);
				if($mail_account_user instanceof Contact){
					$old_user_id = $mailAccount->getContactId();
					if ($old_user_id != $mail_account_user->getId())
						$user_changed = true;
					$mailAccount_data['user_id'] = $mail_account_user->getId();					
				}
				$mailAccount_data['sync_ssl'] = array_var($mailAccount_data, 'sync_ssl') == "checked";
				
				DB::beginWork();
				$logged_user_settings = MailAccountContacts::getByAccountAndContact($mailAccount, logged_user());
				$logged_user_can_edit = $logged_user_settings instanceof MailAccountContact && $logged_user_settings->getCanEdit() || $mailAccount->getContactId() == logged_user()->getId() || logged_user()->isAdministrator();
				if ($logged_user_can_edit || $is_admin) {
					if (!array_var($mailAccount_data, 'del_mails_from_server', false)) $mailAccount_data['del_from_server'] = 0;
					if (!array_var($mailAccount_data, 'mark_read_on_server', false)) $mailAccount_data['mark_read_on_server'] = 0;
					$mailAccount->setFromAttributes($mailAccount_data);
					$mailAccount->setServer(trim($mailAccount->getServer()));
					$mailAccount->setPassword(MailUtilities::ENCRYPT_DECRYPT($mailAccount->getPassword()));
					$mailAccount->setSmtpPassword(MailUtilities::ENCRYPT_DECRYPT($mailAccount->getSmtpPassword()));
					$outbox_folder = array_var($_POST, 'outbox_select_box');
					if (config_option("sent_mails_sync") && isset($outbox_folder)){		
						$mailAccount->setSyncPass(MailUtilities::ENCRYPT_DECRYPT($mailAccount_data['sync_pass']));						
						$mailAccount->setSyncFolder($outbox_folder);					
					}
					
					
					//in case there is a new owner of the email account
					if ($user_changed && $mail_account_user instanceof Contact){
						DB::executeAll("UPDATE ".TABLE_PREFIX."objects SET created_by_id=".$mail_account_user->getId()." WHERE  
							`created_by_id` = '$old_user_id' AND (select `account_id` FROM ".TABLE_PREFIX."mail_contents mc WHERE mc.object_id=id) = ".$mailAccount->getId());
						$mailAccount->setContactId($mail_account_user->getId());
					}
					
					//If imap, save folders to check
					if($mailAccount->getIsImap() && is_array(array_var($_POST, 'check'))) {
					  	$checks = array_var($_POST, 'check');
						
					  	$names = array();
					  	foreach ($checks as $name => $checked) {
					  		$name = str_replace(array('¡','!'), array('[',']'), $name);//to avoid a mistaken array if name contains [
					  		$names[] = $name;
					  		$imap_folder = MailAccountImapFolders::instance()->findOne(array('conditions' => array('folder_name = ? AND account_id = ?', $name,$mailAccount->getId())));
					  		if (!$imap_folder instanceof MailAccountImapFolder) {
					  			$imap_folder = new MailAccountImapFolder();
					  			$imap_folder->setAccountId($mailAccount->getId());
					  			$imap_folder->setFolderName($name);
					  		}
					  		$imap_folder->setCheckFolder($checked == 'checked');
					  		$imap_folder->save();
					  	}
					  	if (count($names) > 0) {
					  		DB::execute("UPDATE ".TABLE_PREFIX."mail_account_imap_folder SET check_folder=0 WHERE account_id=".$mailAccount->getId()." AND folder_name NOT IN ('".implode("','",$names)."')");
					  	}
					}
					
					$member_ids = json_decode(array_var($_POST, 'members'));
					$member_ids_str = "";
					foreach ($member_ids as $mid) {
						if (is_numeric($mid)) $member_ids_str .= ($member_ids_str == "" ? "" : ",") . $mid;
					}
					$mailAccount->setMemberId($member_ids_str);
					
					$mailAccount->save();
					
					// process users
					
					$account_users = Contacts::findAll();
					$user_access = array_var($_POST, 'user_access');
					foreach ($account_users as $account_user) {
						$user_id = $account_user->getId();
						$access = array_var($user_access, $user_id, 'none');
						$account_user = MailAccountContacts::getByAccountAndContact($mailAccount, $account_user);
						if ($mail_account_user instanceof Contact && ($access != 'none' || $user_id == $mail_account_user->getId())) {
							if (!$account_user instanceof MailAccountContact) {
								$account_user = new MailAccountContact();
								$account_user->setAccountId($mailAccount->getId());
								$account_user->setContactId($user_id);
							}
							$account_user->setCanEdit($access == 'write');
							$account_user->save();
						} else if ($account_user instanceof MailAccountContact) {
							$account_user->delete();
						}
					}
					/*// delete any remaining ones
					$account_users = MailAccountContacts::getByAccount($mailAccount);
					foreach ($account_users as $account_user) {
						if ($access = array_var($user_access, $account_user->getId(), 'none') == 'none') {
							$account_user->delete();
						}
					}*/
					
					evt_add("mail account edited", array(
							"id" => $mailAccount->getId(),
							"name" => $mailAccount->getName(),
							"email" => $mailAccount->getEmail()
					));
				}
				
				// personal settings
				if (array_var($_POST, 'is_default')) {
					$user_accounts = MailAccountContacts::getByContact(logged_user());
					foreach ($user_accounts as $acc) {
						if ($acc->getAccountId() != $mailAccount->getId()) {
							$acc->setIsDefault(false);
							$acc->save();				
						} else {
							$acc->setIsDefault(true);
							$acc->save();
						}
					}
				}
				$logged_user_settings = MailAccountContacts::getByAccountAndContact($mailAccount, logged_user());
				if ($logged_user_settings instanceof MailAccountContact) { 
					$logged_user_settings->setSignature(array_var($_POST, 'signature'));
					$logged_user_settings->setSenderName(array_var($_POST, 'sender_name'));
					$logged_user_settings->save();
				}
				DB::commit();

				flash_success(lang('success edit mail account', $mailAccount->getName()));
				ajx_current("back");

		  // Error...
			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // edit

	/**
	 * List user email accounts
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function list_accounts(){
		ajx_current("empty");
		$type = array_var($_GET,'type');
		 
		$accounts = MailAccounts::getMailAccountsEditByUser(logged_user());
		 
		$object = array();
		if (isset($accounts)){
			foreach($accounts as $acc)
			{
				$loadAcc = true;
				if (isset($type))
				{
					if ($type == "view")
					$loadAcc = $acc->canView(logged_user());
					if ($type == "edit")
					$loadAcc = $acc->canEdit(logged_user());
				}
				if ($loadAcc)
				$object[] = array(
						"id" => $acc->getId(),
						"name" => $acc->getName(),
						"email" => $acc->getEmail()
				);
			}
		}
		ajx_extra_data(array("accounts" => $object));
	}

	/**
	 * Delete specific mail account
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete_account() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$account = MailAccounts::findById(get_id());
		if (!$account instanceof MailAccount) {
			flash_error(lang('error delete mail account'));
			ajx_current("empty");
			return;
		}
		if (!$account->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$deleteMails = array_var($_GET, 'deleteMails', false);
		try {
			$accId = $account->getId();
			$accName = $account->getName();
			$accEmail = $account->getEmail();
			 
			DB::beginWork();
			$account->delete($deleteMails);
			DB::commit();

			evt_add("mail account deleted", array(
					"id" => $accId,
					"name" => $accName,
					"email" => $accEmail
			));

			flash_success(lang('success delete mail account'));
			if (array_var($_GET, 'reload', false)) {
				ajx_current("reload");
			} else {
				ajx_current("back");
			}
    
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete mail account'));
			ajx_current("empty");
		}
	} // delete


	function check_account_errors() {
		ajx_current("empty");
		$user = logged_user();
		if (!$user instanceof Contact) return;
		$acc_users = MailAccountContacts::instance()->getByContact(logged_user());
		foreach ($acc_users as $acc_user) {
			/* @var $acc_user MailAccountContact */
 			if ($acc_user->getLastErrorState() == MailAccountContacts::MA_ERROR_UNREAD) {
				$account = $acc_user->getAccount();
				if (!$account instanceof MailAccount) continue;
				flash_error($account->getLastErrorMsg());
				$acc_user->setLastErrorState(MailAccountContacts::MA_ERROR_READ);
				$acc_user->save();
			}
		}
	}



	/**
	 * Forward email
	 *
	 * @param void
	 * @return null
	 */
	function forward_mail(){
		$this->setTemplate('add_mail');
		$mail = new MailContent();
		if(array_var($_GET,'id','') == ''){
			flash_error('Invalid parameter.');
			ajx_current("empty");
		}
		$original_mail = MailContents::findById(get_id('id',$_GET));
		if(! $original_mail){
			flash_error('Invalid parameter.');
			ajx_current("empty");
		}
		$mail_data = array_var($_POST, 'mail', null);

		if(!is_array($mail_data)) {
			$fwd_subject = str_starts_with(strtolower($original_mail->getSubject()),'fwd:') ? $original_mail->getSubject() : 'Fwd: ' . $original_mail->getSubject();
			
			$clean_mail = $this->cleanMailBodyAndGetMailData($original_mail, true);
						
			$mail_data = array(
	          'to' => '',
	          'subject' => $fwd_subject,
	          'body' =>  '<div id="original_mail">'.$clean_mail['clean_body'].'</div>',
	          'type' => $clean_mail['type'],
			  'attachs' => $clean_mail['attachs'],
	          'account_id' => $original_mail->getAccountId(),
			  'conversation_id' => $original_mail->getConversationId(),
			  'in_reply_to_id' => $original_mail->getMessageId(),
			  'original_id' => $original_mail->getId(),
			  'last_mail_in_conversation' => MailContents::getLastMailIdInConversation($original_mail->getConversationId(), true),
			
			); // array
		} // if
		$mail_accounts = MailAccounts::getMailAccountsByUser(logged_user());
		tpl_assign('link_to_objects', 'MailContents-' . $original_mail->getId());
		tpl_assign('mail', $mail);
		tpl_assign('mail_data', $mail_data);
		tpl_assign('mail_accounts', $mail_accounts);
		
	}//forward_mail


	/**
	 * Edit email
	 *
	 * @param void
	 * @return null
	 */
	function edit_mail(){
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_mail');
		if(array_var($_GET,'id','') == ''){
			flash_error('Invalid parameter.');
			ajx_current("empty");
		}
		$original_mail = MailContents::findById(get_id('id',$_GET));
		if(! $original_mail){
			flash_error('Invalid parameter.');
			ajx_current("empty");
		}
		
		$mail_accounts = MailAccounts::getMailAccountsByUser(logged_user());	
		if (count($mail_accounts) < 1){
			flash_error(lang('no mail accounts set'));
			ajx_current("empty");
			return;
		}
		
		$mail_data = array_var($_POST, 'mail', null);

		if(!is_array($mail_data)) {
			$body = $original_mail->getBodyHtml() == '' ? $original_mail->getBodyPlain() : $original_mail->getBodyHtml();

			//Attachs
			$attachs = array();
			if ($original_mail->getHasAttachments()) {
				$attachments = self::readAttachmentsFromFileSystem($original_mail, $att_version);
				foreach($attachments as $att) {
					$fName = $att["name"];
					$fileType = $att["type"];
					$fid = gen_id();
					$attachs[] = "FwdMailAttach:$fName:$fileType:$fid";
					if ($att_version >= 2) {
						@copy($att['path'], ROOT . "/tmp/" . logged_user()->getId() . "_" .$original_mail->getAccountId() . "_FwdMailAttach_$fid");
					} else {
						file_put_contents(ROOT . "/tmp/" . logged_user()->getId() . "_" .$original_mail->getAccountId() . "_FwdMailAttach_$fid", $att['data']);
					}
				}
			}
			
			$mail_data = array(
	          'to' => $original_mail->getTo(),
	          'cc' => $original_mail->getCc(),
	          'bcc' => $original_mail->getBcc(),
	          'subject' => $original_mail->getSubject(),
	          'body' => $body,
	          'type' => $original_mail->getBodyHtml() != '' ? 'html' : 'plain',
	          'account_id' => $original_mail->getAccountId(),
			  'conversation_id' => $original_mail->getConversationId(),
			  'in_reply_to_id' => $original_mail->getMessageId(),
			  'original_id' => $original_mail->getId(),
			  'last_mail_in_conversation' => MailContents::getLastMailIdInConversation($original_mail->getConversationId(), true),
	          'id' => $original_mail->getId(),
			  'draft_edit' => 1,
			  'attachs' => $attachs
			); // array
		} // if
				
		tpl_assign('mail', $original_mail);
		tpl_assign('mail_data', $mail_data);
		tpl_assign('mail_accounts', $mail_accounts);
		
	}//edit_mail


	/**
	 * Lists emails.
	 *
	 */
	function list_all() {
		ajx_current("empty");

		// Get all variables from request
		$start = array_var($_GET, 'start');
		$limit = user_config_option('mails_per_page')? user_config_option('mails_per_page') : config_option('files_per_page');
		if (!is_numeric($start)) {
			$start = 0;
		}
		
		$action = array_var($_GET,'action');
		$attributes = array(
			"ids" => explode(',', array_var($_GET,'ids')),
			"types" => explode(',', array_var($_GET,'types')),
			"accountId" => array_var($_GET,'account_id'),
			"viewType" => array_var($_GET,'view_type'),
			"classifType" => array_var($_GET,'classif_type'),
			"readType" => array_var($_GET,'read_type'),
			"stateType" => array_var($_GET,'state_type'),
			"moveTo" => array_var($_GET, 'moveTo'),
			"mantainWs" => array_var($_GET, 'mantainWs'),
			"classify_atts" => array_var($_GET, 'classify_atts'),
		);
		$dir = array_var($_GET,'dir');
		if ($dir != 'ASC' && $dir != 'DESC') {
			$dir = 'ASC';
		}
		$order = array_var($_GET,'sort');
		$join_params = array();
		switch ($order){
			case 'title':
			case 'subject':
				$order = '`name`';
				break;
			case 'accountName':
				$order = '`account_email`';
				break;
			case 'from':
				$order = "`from_name` $dir, `from`";
				break;
			case 'to':
				$order = "`to`";
				$join_params = array(
					'table' => TABLE_PREFIX.'mail_datas',
					'jt_field' => 'id',
					'e_field' => 'object_id',
					'join_type' => 'inner'
				);
				break;
			case 'folder':
				$order = '`imap_folder_name`';
				break;
			default:
				$order = "`received_date`";
		}
		//Resolve actions to perform
		$actionMessage = array();
		if (isset($action)) {
			$actionMessage = $this->resolveAction($action, $attributes);
			if ($actionMessage["errorCode"] == 0) {
				flash_success($actionMessage["errorMessage"]);
			} else {
				flash_error($actionMessage["errorMessage"]);
			}
		}

		// Get all emails to display
		$context = active_context();
		
		// Get only last mail in conversation for this folder if is set show_emails_as_conversations
		if(user_config_option('show_emails_as_conversations')){
			$conversation_list = 1;
		}else{
			$conversation_list = 0;
		}
		
		$only_count_result = array_var($_GET, 'only_result',false);
		
		$result = $this->getEmails($attributes, $context, $start, $limit, $order, $dir, $join_params, $conversation_list,$only_count_result);
		
		$total = $result->total;
		$emails = $result->objects;
		
		// Prepare response object
		$object = $this->prepareObject($emails, $start, $limit, $total,$attributes);
		ajx_extra_data($object);
		//ajx_extra_data(array('unreadCount' => MailContents::countUserInboxUnreadEmails()));
		tpl_assign("listing", $object);
	}


	/**
	 * Returns a list of emails according to the requested parameters
	 *
	 * @param string $action
	 * @param string $tag
	 * @param array $attributes
	 * @param Project $project
	 * @return array
	 */
	private function getEmails($attributes, $context = null, $start = null, $limit = null, $order_by = 'sent_date', $dir = 'ASC',$join_params = null, $conversation_list = null, $only_count_result = null) {
		// Return if no emails should be displayed
		if (!isset($attributes["viewType"]) || ($attributes["viewType"] != "all" && $attributes["viewType"] != "emails")) return null;
		$account = array_var($attributes, "accountId");
		$classif_filter = array_var($attributes, 'classifType');
		$read_filter = array_var($attributes, 'readType');
		
		//set_user_config_option('mails account filter', $account, logged_user()->getId());
		//set_user_config_option('mails classification filter', $classif_filter, logged_user()->getId());
		//set_user_config_option('mails read filter', $read_filter, logged_user()->getId());
		
		$state = array_var($attributes, 'stateType');
		
		$result = MailContents::getEmails($account, $state, $read_filter, $classif_filter, $context, $start, $limit, $order_by, $dir, $join_params, null, $conversation_list, $only_count_result);
		

		return $result;
	}

	function get_user_preferences() {
		ajx_current("empty");
		$prefereneces = array(
			'accFilter' => user_config_option('mails account filter'),
			'classifFilter' => user_config_option('mails classification filter'),
			'readFilter' => user_config_option('mails read filter'),
		);
		ajx_extra_data($prefereneces);
	}

	 
	/**
	 * Prepares return object for a list of emails and messages
	 *
	 * @param array $totMsg
	 * @param integer $start
	 * @param integer $limit
	 * @return array
	 */
	private function prepareObject($emails, $start, $limit, $total, $attributes = null) {
		$object = array(
			"totalCount" => intval($total),
			"start" => $start,//(integer)min(array(count($totMsg) - (count($totMsg) % $limit),$start)),
			"messages" => array()
		);
		$custom_properties = CustomProperties::getAllCustomPropertiesByObjectType(MailContents::instance()->getObjectTypeId());
		$i=0;
		foreach ($emails as $email) {
			if ($email instanceof MailContent) {
				$properties = $this->getMailProperties($email, $i);
				$object["messages"][$i] = $properties;
			}
			
			foreach ($custom_properties as $cp) {
				$object["messages"][$i]['cp_'.$cp->getId()] = get_custom_property_value_for_listing($cp, $email);
			}
			$i++;
		}
		
		//set columns to show for this folder
		if(isset($attributes)){
			$string = user_config_option("folder_".$attributes["stateType"]."_columns");
			$columns = explode(",", $string);
			foreach ($columns as $col){
				$object["folder_columns"][] = $col;
			}
			$object["folder_name"] = $attributes["stateType"];
			
			//if you want to add a column add their name here too
			$object["folder_columns_all"] = array("from","to","subject","account","date","folder","actions");
		}
		
		return $object;
	}


	private function getMailProperties($msg, $i=0) {
		$text = $msg->getTextBody();
		// plain body is already converted to UTF-8 (when mail was saved)
		if (strlen_utf($text) > 150) {
			$text = substr_utf($text, 0, 150) . "...";
		}

		$show_as_conv = user_config_option('show_emails_as_conversations');
		if ($show_as_conv) {
			$conv_total = MailContents::countMailsInConversation($msg);
			$conv_unread = MailContents::countUnreadMailsInConversation($msg);
			$conv_hasatt = MailContents::conversationHasAttachments($msg);
		}
		//if the variable is not set, make the query and set it.
		//seba
		// Comented by php TODO: Feng 2 context/members
		/*
		if(!isset($this->user_workspaces_ids)){
			$sql = logged_user()->getWorkspacesQuery();
			$rows = DB::executeAll($sql);
			if (count($rows)== 0) $this->user_workspaces_ids = "0";
			else{
				foreach ($rows as $row){
						if ($this->user_workspaces_ids != "") $this->user_workspaces_ids .= ",";
						$this->user_workspaces_ids .= $row['project_id'];						
				}
			}
		}*/
/* @var $msg MailContent */
		
		$persons_dim = Dimensions::findByCode('feng_persons');
		$persons_dim_id = $persons_dim instanceof Dimension ? $persons_dim->getId() : "0";
		$mail_member_ids = array_flat(DB::executeAll("SELECT om.member_id FROM ".TABLE_PREFIX."object_members om
				INNER JOIN ".TABLE_PREFIX."members m ON m.id=om.member_id 
				WHERE om.object_id = '".$msg->getId()."' AND om.is_optimization = 0 AND m.dimension_id<>$persons_dim_id"));
		
		$properties = array(
		    "id" => $msg->getId(),
			"ix" => $i,
			"object_id" => $msg->getId(),
			"ot_id" => $msg->getObjectTypeId(),
			"type" => 'email',
			"hasAttachment" => $msg->getHasAttachments(),
			"accountId" => $msg->getAccountId(),
			"accountName" => ($msg->getAccount() instanceof MailAccount ? $msg->getAccount()->getName() : lang('n/a')),
			"subject" => $msg->getSubject(),
			"text" => $text,
			"date" => $msg->getReceivedDate() instanceof DateTimeValue ? ($msg->getReceivedDate()->isToday() ? format_time($msg->getReceivedDate()) : format_datetime($msg->getReceivedDate())) : lang('n/a'),
			"userId" => ($msg->getAccount() instanceof MailAccount  && $msg->getAccount()->getOwner() instanceof Contact ? $msg->getAccount()->getOwner()->getId() : 0),
			"userName" => ($msg->getAccount() instanceof MailAccount  && $msg->getAccount()->getOwner() instanceof Contact ? $msg->getAccount()->getOwner()->getObjectName() : lang('n/a')),
			"isRead" => $show_as_conv ? ($conv_unread == 0) : $msg->getIsRead(logged_user()->getId()),
			"from" => $msg->getFromName() != '' ? $msg->getFromName() : $msg->getFrom(),
			"from_email" => $msg->getFrom(),
			"isDraft" => $msg->getIsDraft(),
			"isSent" => $msg->getIsSent(),
			"folder" => $msg->getImapFolderName(),
			"to" => $msg->getTo(),
			"memPath" => json_encode($msg->getMembersIdsToDisplayPath()),
			"memberIds" => implode(",", $mail_member_ids),
		);
		
		if ($show_as_conv) {
			$properties["conv_total"] = $conv_total;
			$properties["conv_unread"] = $conv_unread;
			$properties["conv_hasatt"] = $conv_hasatt;
		}
		return $properties;
	}

	private function getAllowedAddresses($extra_conds = null){
		$addresses = array();
		$contacts = Contacts::instance()->getAllowedContacts($extra_conds);
		foreach ($contacts as $contact ) {
			/* @var $contact Contact */
			if ($addr = $contact->getEmailAddress()) {
				$name = str_replace(",", " ", $contact->getObjectName());
				$addresses[] =  $name." <".$addr.">";
			}
		}
		return $addresses;
	}
	
	function get_allowed_addresses() {
		$extra_conds = null;
		if ($filter = array_var($_POST, 'name_filter')) {
			$filter = mysql_real_escape_string($filter, DB::connection()->getLink());
			$extra_conds = "(e.first_name like '%$filter%' || e.surname like '%$filter%' || 
				(select count(id) from ".TABLE_PREFIX."contact_emails ce where ce.contact_id=e.object_id and ce.email_address like '%$filter%'))";
			$addresses = $this->getAllowedAddresses($extra_conds);
		} else {
			$return_values = true;
			$max = array_var($_POST, 'max');
			if ($max > 0) {
				$return_values = Contacts::instance()->countAllowedContacts() <= $max;
			}
			
			if ($return_values) {
				$addresses = $this->getAllowedAddresses();
			} else {
				$addresses = array();
			}
		}
		ajx_current("empty");
		ajx_extra_data(array('addresses' => $addresses));
	}
	
	/**
	 * Resolve action to perform
	 *
	 * @param string $action
	 * @param array $attributes
	 * @return string $message
	 */
	private function resolveAction($action, $attributes){
		$resultMessage = "";
		$resultCode = 0;
		switch ($action){
			case "delete":
				$err = 0; $succ = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
						
					switch ($type){
						case "email":
							$email = MailContents::findById($id);
							if ($email instanceof MailContent && $email->canDelete(logged_user())) {
								if ($email->getState() == 2) {
									// we are deleting a draft email
									$emails_in_conversation = array($email);
								} else {
									if (user_config_option('show_emails_as_conversations', true, logged_user()->getId())) {
										$emails_in_conversation = MailContents::getMailsFromConversation($email);
									} else { 
										$emails_in_conversation = array($email);
									}
								}
								foreach ($emails_in_conversation as $email){
									if ($email->canDelete(logged_user())) {
										try {
											$email->trash();
											ApplicationLogs::createLog($email, $email->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
											$succ++;
										} catch(Exception $e) {
											$err++;
										}
									} else {
										$err++;
									}
								}
							} else {
								$err++;
							}
							break;
								
						default:
							$err++;
							break;
					} // switch
				} // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error delete objects", $err) . "<br />" . ($succ > 0 ? lang("success delete objects", $succ) : "");
				} else {
					$resultMessage = lang("success delete objects", $succ);
				}
				ajx_add("overview-panel", "reload");
				break;
			case "unclassify":
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					switch ($type){
						case "email":
							$email = MailContents::findById($id);
							if (isset($email) && !$email->isDeleted() && $email->canEdit(logged_user())){
								$this->do_unclassify($email);
								ApplicationLogs::createLog($email, $email->getWorkspaces(), ApplicationLogs::ACTION_TAG,false,null,true,$tag);
								$resultMessage = lang("success unclassify emails", count($attributes["ids"]));
							};
							break;

						default:
							$resultMessage = "Unimplemented type: '" . $type . "'";
							$resultCode = 2;
							break;
					}; // switch
				}; // for
				break;
			
				
			case "checkmail":
				$resultCheck = MailController::checkmail();
				$resultMessage = $resultCheck[1];// if
				$resultCode = $resultCheck[0];
				ajx_add("overview-panel", "reload");
				break;

			case "markAsRead":
			case "markAsUnRead":
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					switch ($type){
						case "email":
							$email = MailContents::findById($id);
							if (isset($email)) {
								if (user_config_option('show_emails_as_conversations', true, logged_user()->getId())) {
									$emails_in_conversation = MailContents::getMailsFromConversation($email);
								} else {
									$emails_in_conversation = array($email);
								}
								foreach ($emails_in_conversation as $email) {
									if ($email->canEdit(logged_user())) {
										$email->setIsRead(logged_user()->getId(), $action == 'markAsRead');
									}
								}
							};
							break;

						default:
							$resultMessage = "Unimplemented type: '" . $type . "'";
							$resultCode = 2;
							break;
					}; // switch
				}; // for

				ajx_add("overview-panel", "reload");
				break;
			case "archive":
				$err = 0; $succ = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
						
					switch ($type){
						case "email":
							$email = MailContents::findById($id);
							if (isset($email)) {
								if (user_config_option('show_emails_as_conversations', true, logged_user()->getId())) {
									$emails_in_conversation = MailContents::getMailsFromConversation($email);
								} else {
									$emails_in_conversation = array($email);
								}
								foreach ($emails_in_conversation as $email) {
									if ($email->canEdit(logged_user())) {
										try {
											$email->archive(null);
											ApplicationLogs::createLog($email, $email->getWorkspaces(), ApplicationLogs::ACTION_ARCHIVE);
											$succ++;
										} catch(Exception $e) {
											$err++;
										}
									}
								}
							} else {
								$err++;
							}
							break;
								
						default:
							$err++;
							break;
					} // switch
				} // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error archive objects", $err) . "<br />" . ($succ > 0 ? lang("success archive objects", $succ) : "");
				} else {
					$resultMessage = lang("success archive objects", $succ);
				}
				ajx_add("overview-panel", "reload");
				break;
			default:
				if ($action) {
					$resultMessage = "Unimplemented action: '" . $action. "'";
					$resultCode = 2;
				}
				break;
		} // switch
		return array("errorMessage" => $resultMessage, "errorCode" => $resultCode);
	}
	
	function addEmailToWorkspace($id, $destination, $mantainWs = true) {
		$email = MailContents::findById($id);
		if ($email instanceof MailContent && $email->canEdit(logged_user())){
			if (!$mantainWs) {
				$removed = "";
				$ws = $email->getWorkspaces();
				foreach ($ws as $w) {
					if (can_add(logged_user(), $w, 'MailContents')) {
						$email->removeFromWorkspace($w);
						$removed .= $w->getId() . ",";
					}
				}
				$removed = substr($removed, 0, -1);
				$log_action = ApplicationLogs::ACTION_MOVE;
				$log_data = ($removed == "" ? "" : "from:$removed;") . "to:".$destination->getId();
			} else {
				$log_action = ApplicationLogs::ACTION_COPY;
				$log_data = "to:".$destination->getId();
			}
			$email->addToWorkspace($destination);
			ApplicationLogs::createLog($email, $email->getWorkspaces(), $log_action, false, null, true, $log_data);
			return 1;
		} else return 0; 
	}
	
	function fetch_imap_folders_sync(){
		$this->setTemplate('fetch_imap_folders_sync');
		self::fetch_imap_folders();
	}

	function fetch_imap_folders() {
		$server = trim(array_var($_GET, 'server'));
		$ssl = array_var($_GET, 'ssl') == "checked";
		$port = array_var($_GET, 'port');
		$email = trim(array_var($_GET, 'email'));
		$pass = array_var($_GET, 'pass');
		$genid = array_var($_GET, 'genid');
		tpl_assign('genid', $genid);

		$account = new MailAccount();
		$account->setIncomingSsl($ssl);
		$account->setIncomingSslPort($port);
		$account->setEmail($email);
		$account->setPassword(MailUtilities::ENCRYPT_DECRYPT($pass));
		$account->setServer($server);

		try {
			$real_folders = MailUtilities::getImapFolders($account);
			$imap_folders = array();
			foreach ($real_folders as $folder_name) {
				$acc_folder = new MailAccountImapFolder();
				$acc_folder->setAccountId(0);
				$acc_folder->setFolderName($folder_name);
				$acc_folder->setCheckFolder($folder_name == 'INBOX');// By default only INBOX is checked
				$imap_folders[] = $acc_folder;
			}
			tpl_assign('imap_folders', $imap_folders);
		} catch (Exception $e) {
			Logger::log($e->getMessage());
			Logger::log($e->getTraceAsString());
		}
	}

	function get_conversation_info() {
		$email = MailContents::findById(array_var($_GET, 'id'));
		if (!$email instanceof MailContent) {
			flash_error(lang('email dnx'));
			ajx_current("empty");
			return;
		}

		$info = array();
		$mails = MailContents::getMailsFromConversation($email);
		foreach ($mails as $mail) {
			$text = $mail->getBodyPlain();
			if (strlen_utf($text) > 80) $text = substr_utf($text, 0, 80) . "...";
			$state = $mail->getState();
			$show_user_icon = false;
			if ($state == 1 || $state == 3 || $state == 5) {
				if ($mail->getCreatedById() == logged_user()->getId()) {
					$from = lang('you');
				} else {
					$from = $mail->getCreatedByDisplayName();
				}
				$show_user_icon = true;
			} else {
				$from = $mail->getFrom();
			}
			
			$info[] = array(
				'id' => $mail->getId(),
				'date' => $mail->getReceivedDate() instanceof DateTimeValue ? ($mail->getReceivedDate()->isToday() ? format_time($mail->getReceivedDate()) : format_datetime($mail->getReceivedDate())) : lang('n/a'),
				'has_att' => $mail->getHasAttachments(),
				'text' => htmlentities($text),
				'read' => $mail->getIsRead(logged_user()->getId()),
				'from_name' => ($state == 1 || $state == 3 || $state == 5) ? lang('you') : $mail->getFromName(),
				'from' => $from,
				'show_ico' => $show_user_icon
			);
		}

		tpl_assign('source_email_id', array_var($_GET, 'id'));
		tpl_assign('emails_info', $info);
		$this->setLayout("html");
		$this->setTemplate("llo_email_conversation");
		ajx_current("empty");
	}
	
	function get_unread_count() {
		ajx_current("empty");
		ajx_extra_data(array('unreadCount' => MailContents::countUserInboxUnreadEmails()));
	}
	
	function print_mail() {
		$this->setLayout("html");
		$email = MailContents::findById(get_id());
		if (!$email instanceof MailContent) {
			flash_error(lang('email dnx'));
			ajx_current("empty");
			return;
		}
		if ($email->getIsDeleted()) {
			flash_error(lang('email dnx deleted'));
			ajx_current("empty");
			return;
		}
		if (!$email->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		 
		if ($email->getBodyHtml() != '') {
			MailUtilities::parseMail($email->getContent(), $decoded, $parsedEmail, $warnings);
			$tmp_folder = "/tmp/" . $email->getAccountId() . "_" . logged_user()->getId()."_". $email->getId() . "_temp_mail_content_res";
			if (is_dir(ROOT . $tmp_folder)) remove_dir(ROOT . $tmp_folder);
			if ($parts_container = array_var($decoded, 0)) {
				$email->setBodyHtml(self::rebuild_body_html($email->getBodyHtml(), array_var($parts_container, 'Parts'), $tmp_folder));
			}
		}
		
		tpl_assign('email', $email);
		$this->setTemplate("print_view");
		//ajx_current("empty");
	}
	
	function download() {
		$this->setTemplate(get_template_path('back'));
		$id = array_var($_GET, 'id');
		$email = MailContents::findById($id);
		if (!$email instanceof MailContent) {
			flash_error(lang('email dnx'));
			return;
		}
		if (!$email->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		}
		if ($email->getContent()) {
			download_contents($email->getContent(), 'message/rfc822', $email->getSubject() . ".eml", strlen($email->getContent()), true);
			die();
		} else {
			download_from_repository($email->getContentFileId(), 'message/rfc822', $email->getSubject() . ".eml", true);
			die();
		}
	}
	
	function get_mail_css() {
		$css = file_get_contents('public/assets/javascript/ckeditor/contents.css');
		$css .= "\nbody {\n";
		Hook::fire('email_base_css', null, $css);
		$css .= "\n}\n";
		header("Content-type: text/css");
		echo $css;
		die();
	}
        
        function mark_spam_no_spam($folder,$email){
            if($folder == 0)
            {
                $spam_state = "no spam";
            }   
            else if($folder == 4)
            {
                $spam_state = "spam";
            }
            try {                                            
                    $spam_email = MailSpamFilters::getRow($email);                                            
                    if ($spam_email)
                    {
                        $spam_filter = MailSpamFilters::findById($spam_email[0]->getId());
                        $spam_filter->setSpamState($spam_state);
                        $spam_filter->save();
                    }
                    else
                    {
                        $spam_filter = new MailSpamFilter();
                        $spam_filter->setAccountId($email->getAccountId());
                        $spam_filter->setTextType('email_address');
                        $spam_filter->setText($email->getFrom());
                        $spam_filter->setSpamState($spam_state);
                        $spam_filter->save();
                    }                                             
            }
            catch(Exception $e) {
                    flash_error($e->getMessage());
                    ajx_current("empty");
            }
        }

} // MailController
