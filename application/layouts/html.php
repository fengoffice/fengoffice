<?php header ("Content-Type: text/html; charset=utf-8", true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
<?php echo meta_tag('content-type', 'text/html; charset=utf-8', true) ?>
<?php 
$favicon_name = 'favicon.ico';
Hook::fire('change_favicon', null, $favicon_name);
echo add_favicon_to_page($favicon_name);
echo link_tag(with_slash(ROOT_URL).$favicon_name, "rel", "shortcut icon");
?>
<title><?php echo get_page_title() ?></title>
</head>
<body>

<?php
echo $content_for_layout;
?>

</body>
</html>