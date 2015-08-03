<?php
set_page_title($message->getObjectName());
?>

<style>
body {
	font-family: sans-serif;
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
.body {
	margin-left: 20px;
	padding: 10px;
}
.comments {
	border-top: 1px solid black;
}
.comment {
	margin-left: 20px;
}
.comment-header {
	border-bottom: 1px solid black;
}
.comment-body {
	margin-left: 20px;
}
</style>

<div class="print-view-message">

<div class="header">
<h1><?php echo clean($message->getObjectName()); ?></h1>
<b><?php echo lang('from') ?>:</b> <?php echo clean($message->getCreatedByDisplayName()) ?><br />
<b><?php echo lang('date') ?>:</b> <?php echo format_datetime($message->getUpdatedOn(), null, logged_user()->getTimezone()) ?><br />
<b><?php /*FIXME echo lang('workspace') ?>:</b> <?php echo clean($message->getWorkspacesNamesCSV()) */?><br />
</div>

<div class="body">
<?php 
    if($message->getTypeContent() == "text"){
        echo escape_html_whitespace(convert_to_links(clean($message->getText())));
    }else{
        echo purify_html(nl2br($message->getText()));
    }
?>
</div>

<?php
$i = 0;
$comments = $message->getComments();
if (count($comments) > 0) {
?>
<div class="comments">
<h2><?php echo lang("comments") ?></h2>
<?php foreach ($comments as $comment) {
	$i++;
?>
	<div class="comment">
		<div class="comment-header">
			<b>#<?php echo $i ?>:</b><?php echo lang('comment posted on by', format_datetime($comment->getUpdatedOn()), $comment->getCreatedByCardUrl(), clean($comment->getCreatedByDisplayName())) ?>
		</div>
		<div class="comment-body">
		<?php echo $comment->getText() ?>
		</div>
	</div>
<?php } ?>
</div>
<?php } ?>

</div>

<script>
window.print();
</script>