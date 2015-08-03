<h1>Getmails complete!</h1>
<div>Successful account retrieval: <?php echo $succ ?> accounts</div>
<div>Total emails received: <?php echo $mailsReceived ?></div>
<br/>
<?php if ($err > 0) { ?>
<fieldset>
<div>Unsuccessful account retrieval: <?php echo $err ?> accounts</div>
<table><tr><th style="text-align:left">Account name</th><th style="text-align:left; padding-left:15px">Error message</th></tr>
<?php foreach($errAccounts as $error) { ?>
	<tr><td style="text-align:left"><?php echo clean($error["accountName"]) ?></td>
	<td style="text-align:left; padding-left:15px"><?php echo clean($error["message"]) ?></td></tr>
	<?php } // foreach ?>
</table>
</fieldset>
<?php } ?>