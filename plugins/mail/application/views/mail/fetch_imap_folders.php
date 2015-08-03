<?php if (!isset($imap_folders) || !is_array($imap_folders)) $imap_folders = array(); ?>
<?php $base_tabindex = 71 ?>
<table style="min-width: 400px; margin-top: 10px;">
	<tr>
		<th><?php echo lang('folders to check') ?></th>
		<th style="text-align:right">
		<script>
		og.fetchImapFolders = function(genid) {
			var server = document.getElementById(genid + 'server').value;
			var email = document.getElementById(genid + 'email').value;
			var password = document.getElementById(genid + 'password').value;
			var ssl = document.getElementById(genid + 'ssl').checked ? document.getElementById(genid + 'ssl').value : '';
			var sslport = document.getElementById(genid + 'sslport').value;
			og.openLink(og.getUrl('mail', 'fetch_imap_folders', {
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
	</tr>
	<?php $isAlt = false; $i=0;
	foreach($imap_folders as $folder) { ?>
	<tr <?php echo ($isAlt ? ' class="altRow"': '') ?>>
		<td style="padding-left: 10px;"><?php echo $folder->getFolderName() ?></td>
		<td style="padding-left: 30px;"><?php echo checkbox_field('check['.str_replace(array('[',']'), array('¡','!'), $folder->getFolderName()).']', $folder->getCheckFolder(), array('tabindex'=>$base_tabindex + $i++)) ?></td>
	</tr>
	<?php $isAlt = !$isAlt;
	} ?>
</table>
