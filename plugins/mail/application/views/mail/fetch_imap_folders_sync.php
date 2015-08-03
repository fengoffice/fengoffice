<?php if (!isset($imap_folders) || !is_array($imap_folders)) $imap_folders = array(); ?>
<?php $base_tabindex = 71 ?>
<table style="min-width: 400px; margin-top: 10px;">
	<tr>
		<th><?php echo lang('select folder for outbox') ?></th>
		<th style="text-align:right">
		<script>
		og.fetchImapFoldersSync = function(genid) {
			var server = document.getElementById(genid + 'sync_server').value;
			var email = document.getElementById(genid + 'sync_addr').value;
			var password = document.getElementById(genid + 'sync_pass').value;
			var ssl = document.getElementById(genid + 'sync_ssl').checked ? document.getElementById(genid + 'sync_ssl').value : '';
			var sslport = document.getElementById(genid + 'sync_sslport').value;			
			og.openLink(og.getUrl('mail', 'fetch_imap_folders_sync', {
					server: server,
					email: email,
					pass: password,
					ssl: ssl,
					port: sslport,
					genid: genid
				}), {
				preventPanelLoad: true,
				onSuccess: function(data) {					
					document.getElementById(genid + 'imap_folders_sync').innerHTML = data.current.data;					
				}
			});
		};
		</script>
			<a class="link-ico ico-refresh" tabindex="<?php echo $base_tabindex?>" href="javascript:og.fetchImapFoldersSync('<?php echo $genid ?>')">
			<?php echo lang("fetch imap folders")?>
			</a>  
		</th>
	</tr>
	</table>
	<div class="mail-account-item">
	
	<?php
	$options = array();	
	if (isset($mail_acc_id) && config_option('sent_mails_sync')){		
		$mail_acc = MailAccounts::findById($mail_acc_id);	
		$selected_folder = $mail_acc->getSyncFolder();		
	}
	foreach($imap_folders as $folder) {			
			if (isset ($selected_folder) && $folder->getFolderName() == $selected_folder)
				$options[] = option_tag($folder->getFolderName(), null, array('selected'=>"selected"));				
			else
				$options[] = option_tag($folder->getFolderName(), null, null);
	}
	$outbox_select_box_attrib = array('id'=>$genid.'outbox_select_box');
	echo select_box('outbox_select_box', $options ,$outbox_select_box_attrib);			
	?> </div>

