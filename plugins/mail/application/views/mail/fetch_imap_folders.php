<?php if (!isset($imap_folders) || !is_array($imap_folders)) $imap_folders = array(); ?>
<?php $base_tabindex = 71 ?>
<table style="min-width: 400px; margin-top: 10px;">
	<tr>
		<th><?php echo lang('folders to check') ?></th>
		<th style="text-align:right">
		<script>
		og.fetchImapFolders = function(genid) {
			var account_id = document.getElementById(genid + 'mail_account_id').value;
			var server = document.getElementById(genid + 'server').value;
			var email = document.getElementById(genid + 'email').value;
			var password = document.getElementById(genid + 'password').value;
			var ssl = document.getElementById(genid + 'ssl').checked ? document.getElementById(genid + 'ssl').value : '';
			var sslport = document.getElementById(genid + 'sslport').value;
			og.openLink(og.getUrl('mail', 'fetch_imap_folders', {
					account_id: account_id,
					server: server,
					email: email,
					pass: password,
					ssl: ssl,
					port: sslport,
					genid: genid
				}), {
				preventPanelLoad: true,
				onSuccess: function(data) {
					document.getElementById(genid + 'imap_folders').innerHTML = data.current.data;
				}
			});
		};
		</script>
			<a class="link-ico ico-refresh" tabindex="<?php echo $base_tabindex?>" href="javascript:og.fetchImapFolders('<?php echo $genid ?>')">
			<?php echo lang("fetch imap folders")?>
			</a>  
		</th>
		<th></th>
	</tr>
	<?php $isAlt = false; $i=0;
	foreach($imap_folders as $folder) { 
		$folder_name_key = str_replace(array('[',']'), array('¡','!'), $folder->getFolderName());
	?>
	<tr <?php echo ($isAlt ? ' class="altRow"': '') ?>>
		<td style="padding-left: 10px;"><?php echo $folder->getFolderName() ?></td>
		<td style="padding-left: 30px;"><?php echo checkbox_field('imap_folders['.$folder_name_key.'][check]', $folder->getCheckFolder(), array('tabindex'=>$base_tabindex + $i++)) ?></td>
		<td style="padding-left: 30px;"><?php 
			if ($can_detect_special_folders) {
				echo str_replace("\\", "", $folder->getSpecialUse());
		?>
			<input type="hidden" name="imap_folders[<?php echo $folder_name_key?>][special_use]" value="<?php echo $folder->getSpecialUse()?>" />
		<?php } else { 
				$mu = new MailUtilities();
				$folder_codes = $mu->getSpecialImapFolderCodes();
				$options = array(option_tag("", ""));
				foreach ($folder_codes as $code) {
					$code_text = str_replace("\\", "", $code);
					$attr = array();
					if ($folder->getSpecialUse() == $code) $attr["selected"] = "selected";
					$options[] = option_tag($code_text, $code, $attr);
				}
				echo select_box('imap_folders['.$folder_name_key.'][special_use]', $options);
			}
		?>
			<input type="hidden" name="can_detect_special_folders" value="<?php echo $can_detect_special_folders?"1":"0" ?>" />
		</td>
	</tr>
	<?php $isAlt = !$isAlt;
	} ?>
</table>
