<?php header ("Content-Type: text/html; charset=utf8", true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
  <head>
    <title><?php echo get_page_title() ?></title>
<?php $favicon_name = 'favicon.ico';
	Hook::fire('change_favicon', null, $favicon_name);?>
<?php echo add_favicon_to_page($favicon_name) ?>
<?php echo stylesheet_tag('dialog.css') ?> 
<?php echo stylesheet_tag('login.css') ?> 
<?php echo meta_tag('content-type', 'text/html; charset=utf-8', true) ?> 
<?php echo render_page_head() ?>
  </head>
  <body class="" style="text-align:center;">
  
<?php if(!is_null(flash_get('success'))) { ?>
          <div id="success" onclick="this.style.display = 'none'"><?php echo clean(flash_get('success')) ?></div>
<?php } ?>
<?php if(!is_null(flash_get('error'))) { ?>
          <div id="error" onclick="this.style.display = 'none'"><?php echo clean(flash_get('error')) ?></div>
<?php } ?>
<?php echo $content_for_layout ?>
  
	
  </body>
</html>