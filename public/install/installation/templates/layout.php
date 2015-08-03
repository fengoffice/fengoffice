<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
  <title><?php echo $installation_name ?></title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" /> 
  <link rel="stylesheet" href="assets/style.css" media="all" />
</head>
<body>
  <div id="wrapper">

    <div id="header">
      <h1><?php echo $installation_name ?></h1>
      <div id="installationDesc"><?php echo clean($installation_description) ?></div>
    </div>
    <form class="internalForm" action="<?php echo $current_step->getStepUrl() ?>" id="installerForm" method="post">
      <?php $this->includeTemplate(get_template_path('__step_errors.php')) ?>
      <div id="content"><?php echo $content_for_layout ?></div>
      <?php $this->includeTemplate(get_template_path('__step_controls.php')) ?>
      <input type="hidden" name="submited" value="submited" />
    </form>
    <div id="footer">&copy; <?php echo date('Y') ?> <a href="http://www.fengoffice.com/">Feng Office</a>. All rights reserved.</div>
  </div>

</body>
</html>