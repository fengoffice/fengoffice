<?php header ("Content-Type: text/html; charset=utf-8", true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
  <head>
    <title><?php echo get_page_title() ?></title>
<?php $favicon_name = 'favicon.ico';
	Hook::fire('change_favicon', null, $favicon_name);?>
<?php echo add_favicon_to_page($favicon_name) ?>
<?php echo meta_tag('content-type', 'text/html; charset=utf-8', true) ?> 
<?php echo render_page_head() ?>
    <style>
      #dialog {
        margin: 100px auto 0 auto;
        border: 3px solid #ccc;
        padding: 10px;
        width: 332px;
        text-align: left;
      }
    </style>
  </head>
  <body>
    <div id="dialog">
      <h1><?php echo get_page_title() ?></h1>
      <?php echo $content_for_layout ?>
    </div>
  </body>
</html>