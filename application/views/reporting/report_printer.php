<?php if ( !isset($is_csv) || !$is_csv ) : ?>
<style>
body {
	font-family: sans-serif;
	font-size:11px;
}
.header {
	border-bottom: 1px solid black;
	padding: 10px;
}
h1 {
	font-size: 150%;
	margin: 15px 0;
}
h2 {
	font-size: 120%;
	margin: 15px 0;
}
th {
	border-bottom:2px solid #333;
}
.body {
	margin-left: 20px;
	padding: 10px;
}
table {
	border-collapse:collapse;
	border-spacing:0;
}
</style>
<?php echo stylesheet_tag('og/reporting.css');?>
<div class="print" style="padding:7px">

<div class="printHeader report-print-header">
	<div class="title-container"><h1 align="center"><?php echo $title ?></h1></div>
	<?php if (isset($pdf_export) && $pdf_export) : ?>
	<div class="company-info">
		<?php if (owner_company()->getPictureFile() != '') {
			?><div class="logo-container"><img src="<?php echo owner_company()->getPictureUrl()?>"/></div> 
		<?php } else {
			?><div class="comp-name-container"><?php echo clean(owner_company()->getObjectName())?></div><br />
		<?php } ?>
		<?php 
			$address = owner_company()->getStringAddress('work');
			$email = owner_company()->getEmailAddress('work');
			$phone = owner_company()->getPhoneNumber('work');
			if ($address != '') {
				?><div class="address-container"><?php echo $address?></div><br /><?php
			}
			if ($email != '') {
				?><div class="email-container link-ico ico-email"><?php echo $email?></div><?php
			}
			if ($phone != '') {
				?><div class="phone-container link-ico ico-phone"><?php echo $phone?></div><?php
			}
		?>
	</div>
	<?php endif; //is pdf ?>
	<div style="clear:both;"></div>
<?php endif; ?>
<?php $this->includeTemplate(get_template_path($template_name, 'reporting'));?>
<?php if ( !isset($is_csv) || !$is_csv) : ?>
</div>
</div>

<?php 	if (!isset($pdf_export) || !$pdf_export) : ?>
<script>
window.print();
</script>
<?php 	else : ?>
<?php  		pdf_convert_and_download($html_filename, $pdf_filename, $orientation); ?>
<?php 	endif; ?>
<?php endif; ?>