<?php if($pagination->hasPrevious) :?>
	<a href="<?php echo isset($conditions) ? "#" : $pagination->previousUrl ?>" onclick="og.submitSearchPaginationForm(<?php echo ($pagination->currentPage - 1)?>);">&lt; <?php echo lang("previous")?></a>
<?php endif?> 

<?php
	if (count($pagination->links) > 1 ){
		foreach($pagination->links as $page => $url){ ?>
		<a href = "<?php echo isset($conditions) ? "#" : $url ?>" onclick="og.submitSearchPaginationForm(<?php echo $page?>);">
			<?php 
				if ($pagination->currentPage == $page) echo "<em>";
				echo $page;
				if ($pagination->currentPage == $page) echo "</em>";
			?>
		</a>
<?php 
		}
	}
	else{ 
		 echo "$pagination->currentStart - $pagination->currentEnd ".lang("of")." ".$pagination->total ;		
	}
?>

<?php if($pagination->hasNext) :?>  
	<a href="<?php echo isset($conditions) ? "#" : $pagination->nextUrl ?>" onclick="og.submitSearchPaginationForm(<?php echo ($pagination->currentPage + 1)?>);"><?php echo lang("next")?> &gt;</a>
<?php endif;?>


<script>
<?php if (isset($conditions)) : ?>
	og.tmpform = document.createElement("form");
	og.tmpform.setAttribute("method", "post");
	og.tmpform.setAttribute("action", og.getUrl('search', 'search', {search_for:document.getElementById('search[text]').value}));

	var hiddenField = document.createElement("input");
	hiddenField.setAttribute("type", "hidden");
	hiddenField.setAttribute("name", "advanced");
	hiddenField.setAttribute("value", "1");
	og.tmpform.appendChild(hiddenField);
	
	hiddenField = document.createElement("input");
	hiddenField.setAttribute("type", "hidden");
	hiddenField.setAttribute("name", "objectTypeSelSearch");
	hiddenField.setAttribute("value", "<?php echo $type_object ?>");
	og.tmpform.appendChild(hiddenField);
	
	hiddenField = document.createElement("input");
	hiddenField.setAttribute("type", "hidden");
	hiddenField.setAttribute("name", "search[search_object_type_id]");
	hiddenField.setAttribute("value", "<?php echo $type_object ?>");
	og.tmpform.appendChild(hiddenField);
	
	<?php
		foreach ($conditions as $k => $condition) {
			foreach ($condition as $name => $value) {
			?>
				var hiddenField = document.createElement("input");
				hiddenField.setAttribute("type", "hidden");
				hiddenField.setAttribute("name", "conditions[<?php echo $k?>][<?php echo $name?>]");
				hiddenField.setAttribute("value", "<?php echo $value?>");
				og.tmpform.appendChild(hiddenField);
			<?php
			}
		}
	?>
<?php endif;?>

	var submit_pagination_form = <?php echo isset($conditions) ? 'true' : 'false' ?>;

	og.submitSearchPaginationForm = function(page) {
		if (submit_pagination_form) {
			
			var hiddenField = document.createElement("input");
			hiddenField.setAttribute("type", "hidden");
			hiddenField.setAttribute("name", "start");
			hiddenField.setAttribute("value", (page - 1)*10);
			og.tmpform.appendChild(hiddenField);
			
			og.ajaxSubmit(og.tmpform);
			return false;
		} else {
			return true;
		}
	}
	$("#searchButton").prop("disabled",false);
	$("#search_for_in").prop("disabled",false);
</script>