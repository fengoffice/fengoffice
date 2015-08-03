<style>
	div.x-panel-body 
	{
		background: #fff !important;
	}

	div.overview-activity
	{
		background: #fff;
		height: 100%;	
		font-family: lucida grande,tahoma,verdana,arial,sans-serif;	
	}
	
	div.overview-activity h2
	{
		font-size: 18px;
		padding-bottom: 10px;	
	}
	
	div.overview-activity div.filter
	{
		font-size: 12px;		
	}
	
	div.overview-activity div.filter ul
	{
		border: solid 1px #CDDBF0;	
		background: #E7EDF8;
		height: 24px;		
	}

	div.overview-activity div.filter ul li
	{
		float: left;
		padding: 3px;
	}
	
	div.overview-activity div.filter ul li a.active
	{
		font-weight: bold;
	}	
	
	div.overview-activity div.feeds
	{
		clear: both;
		padding-top: 15px;	
	}
	
    div.overview-activity div.feeds ul
    {
        margin: 0px;
    }
    
	div.overview-activity div.feeds ul li
	{
		border-top: solid 1px #E9E9E9;		
		padding: 5px;
		clear: both;
        margin: 0px;		
	}

	div.feeds ul li div.user-avatar, div.feeds ul li div.feed-content
	{
		float: left;
		padding: 5px;
		font-size: 12px;
		color: #666666;
	}
	
	div.feeds ul li div.feed-content
	{
		width: auto;	
	}
	
	div.feeds ul li div.user-avatar, div.feeds ul li div.feed-content a.person
	{
		font-size: 13px;
		font-weight: bold;
	}

	div.feeds ul li div.user-avatar, div.feeds ul li div.feed-content p
	{
		font-size: 12px;
		color: #000;
		padding: 5px;
	}
	
	div.feeds ul li div.feed-content em.feed-date
	{
		padding: 3px 0 0 20px;
		font-size: 11px;
	}
	input.comment_submit{
		width: auto !important;	
	}
</style>
<div class="overview-activity">
	<div class="feeds">
		<ul>
		<?php 
		foreach ($feeds['objects'] as $feed): ?>
			<li>
				<div class="user-avatar">
					<a href="index.php?c=contact&a=card&id=<?php echo $feed['createdById'] ?>" class="person"><img src="<?php echo $feed['picture']; ?>" /></a>
				</div>
				
				<div class="feed-content">
					<a href="Javascript:;" onclick="og.openLink('index.php?c=contact&a=card&id=<?php echo $feed['createdById'] ?>');" class="person"><?php echo $feed['createdBy'] ?></a> - 
					<em class="<?php echo $feed['icon'] ?> feed-date"><?php echo $feed['friendly_date'];?></em><br />
					<a href="Javascript:;" onclick="og.openLink('<?php echo $feed['url'] ?>');"><?php echo $feed['name'] ?></a>
					<p><?php echo substr($feed['content'], 0, 200); ?></p>
					<div class="user-comment">
						<a>Comments(<?php echo count($feed['comment']);?>)</a><br />
						<div>
						<?php foreach ($feed['comment'] as $comment):?>
				 			<?php include (get_template_path('comment_unit', 'comment')); ?>
						<?php endforeach ?>
						</div>
						<textarea class="comment_text_<?php echo $feed['object_id']?>" onkeydown="og.commentBoxKeyDown(this,event,<?php echo $feed['object_id']?>)" placeholder="<?php echo lang('add_comment')?>" class="huge front-color-value"></textarea>
   						<br />
   						<input type="button" class="comment_submit_<?php echo $feed['object_id']?>" onclick="og.sendComment(<?php echo $feed['object_id']?>,'comment_submit_<?php echo $feed['object_id']?>')" value="<?php echo lang('add_comment')?>">
					</div>	
	
				</div>
			</li>
		<?php 
		endforeach; ?>
		</ul>
	</div>
</div>