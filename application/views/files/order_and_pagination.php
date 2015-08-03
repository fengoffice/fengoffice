<div class="filesOrderAndPagination">
  <div class="filesOrder">
    <span><?php echo lang('order by') ?>:</span> 
<?php
  $order_by_name_url = ProjectFiles::getIndexUrl(ProjectFiles::ORDER_BY_NAME);
  $order_by_posttime_url = ProjectFiles::getIndexUrl(ProjectFiles::ORDER_BY_POSTTIME);
?>
<?php if($order == ProjectFiles::ORDER_BY_NAME) { ?>
    <a href="<?php echo $order_by_name_url ?>" class="selected internalLink"><?php echo lang('order by filename') ?></a> | <a class="internalLink" href="<?php echo $order_by_posttime_url ?>"><?php echo lang('order by posttime') ?></a>
<?php } else { ?>
    <a class="internalLink" href="<?php echo $order_by_name_url ?>"><?php echo lang('order by filename') ?></a> | <a href="<?php echo $order_by_posttime_url ?>" class="selected internalLink"><?php echo lang('order by posttime') ?></a>
<?php } // if ?>
  </div>
  <div class="filesPagination">
<?php if($pagination instanceof DataPagination) { ?>
<?php echo advanced_pagination($pagination, ProjectFiles::getIndexUrl($order, '#PAGE#')) ?>
<?php } // if ?>
  </div>
</div>