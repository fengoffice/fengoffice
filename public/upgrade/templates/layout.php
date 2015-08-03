<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
  <title><?php echo clean($upgrader->getName()) ?></title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" /> 
  <link rel="stylesheet" href="assets/style.css" media="all" />
</head>
<body>
  <div id="wrapper">
    <div id="header">
      <h1><?php echo clean($upgrader->getName()) ?></h1>
      <div id="installationDesc"><?php echo clean($upgrader->getDescription()) ?></div>
    </div>
<?php
$installed_version = installed_version();
$product_version = include ROOT . '/version.php';
if (!is_array($form_data)) {
	$form_data = array(
		'upgrade_from' => $installed_version,
		'upgrade_to' => $product_version,
	);
}
if (array_var($form_data, 'upgrade_from') == 'unknown') $form_data['upgrade_from'] = '1.1';
$scripts = $upgrader->getScriptsSince($installed_version);
if (count($scripts) > 0) {
?>
    <form class="internalForm" action="index.php" id="upgraderForm" method="post">
      <div id="upgraderControls">
      	<div class="warning">
      		<strong><?php echo lang('upgrade warning') ?>:</strong> <?php echo lang('upgrade warning desc', help_link()) ?>
      	</div>
        <table class="formBlock">
          <tr>
            <th colspan="2"><?php echo lang('upgrade')?></th>
          </tr>
<?php if ($installed_version == "unknown") { ?>
          <tr>
            <td class="optionLabel"><label for="upgradeFormFrom"><?php echo lang('upgrade from')?>: </label></td>
            <td>
              <select name="form_data[upgrade_from]" id="upgradeFormFrom">
	<?php foreach($scripts as $script) { ?>
                <option <?php if ($script->getVersionFrom() == array_var($form_data, 'upgrade_from', '1.1')) echo 'selected="selected"'; ?> value="<?php echo clean($script->getVersionFrom()) ?>">Feng Office <?php echo clean($script->getVersionFrom()) ?></option>
	<?php } // foreach ?>
              </select>
            </td>
          </tr>
<?php } else { ?>
		<tr>
			<td class="optionLabel"><label><?php echo lang('upgrade from')?>: </label></td>
			<td>
				<input name="form_data[upgrade_from]" type="hidden" value="<?php echo $installed_version ?>" />
				<?php echo $installed_version ?></td>
		</tr>
<?php } ?>
          <tr>
            <td class="optionLabel"><label for="upgradeFormTo"><?php echo lang('upgrade to')?>: </label></td>
            <td>
              <select name="form_data[upgrade_to]" id="upgradeFormTo">
	<?php foreach($scripts as $script) { ?>
                <option <?php if ($script->getVersionTo() == array_var($form_data, 'upgrade_to', $product_version)) echo 'selected="selected"'; ?> value="<?php echo clean($script->getVersionTo()) ?>"><?php echo PRODUCT_NAME?> <?php echo clean($script->getVersionTo()) ?></option>
	<?php } // foreach ?>
              </select>
            </td>
          </tr>
        </table>
        <button type="submit" accesskey="s"><?php echo lang('upgrade')?> (Alt+S)</button>
        <input type="hidden" name="submited" value="submited" />
      </div>
    </form>
<?php } else { ?>
	<div style="padding: 20px"><?php echo lang('already upgraded', $installed_version)?></div>
<?php } ?>
      <div id="content">
<?php
	if (isset($_SESSION['status_messages'])) {
		if (isset($status_messages)) $status_messages = array_merge($status_messages, $_SESSION['status_messages']);
		else $status_messages = $_SESSION['status_messages'];
		unset($_SESSION['status_messages']);
	} else {
		if (!isset($status_messages)) $status_messages = array();
		$status_messages[] = "<strong>Please check for plugin updates.</strong><br>Open a console and execute the script 'public/install/plugin-console.php'<br>with argument 'list' to view the plugins' status or argument 'update_all' to update them all.";
	}
?>
<?php if(isset($status_messages) && count($status_messages)) { ?>
        <ul>
<?php foreach($status_messages as $status_message) { ?>
          <li><?php echo $status_message ?></li>
<?php } // foreach ?>
        </ul>
<?php } // if ?>
      </div>

<?php if (!isset($_SESSION['hide_back_button']) || !$_SESSION['hide_back_button']) { ?>
	<div class="back"><a href="../../"><?php echo lang('back to fengoffice')?></a></div>
<?php } ?>

<?php
	if (isset($_SESSION['additional_steps'])) {
		if (isset($additional_steps)) $additional_steps = array_merge($additional_steps, $_SESSION['additional_steps']);
		else $additional_steps = $_SESSION['additional_steps'];
		unset($_SESSION['additional_steps']);
	}
	
	if (isset($additional_steps) && is_array($additional_steps)) {
		foreach ($additional_steps as $step) {
			if (!is_file($step['filename'])) continue; 
?>
	<div><a href="<?php echo $step['url']?>"><?php echo $step['name']?></a></div>
<?php 	}
	}
?>
	
    <div id="footer">&copy; <?php echo date('Y') ?> <a href="<?php echo PRODUCT_URL?>"><?php echo PRODUCT_NAME?></a>. <?php echo lang('all rights reserved')?>.</div>
  </div>

</body>
</html>