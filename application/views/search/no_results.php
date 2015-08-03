<?php $genid = gen_id();?>
<div id="headerDiv" class="searchDescription">
<?php 
        if (array_var($_GET, 'search_all_projects') != 'true' && current_member_search()){            
            $context = '';
            foreach (current_member_search() as $context_){
                 $context .= $context_->getName() . ", ";
            }
            echo lang("search for in project", clean($search_string), substr($context, 0, -2));
        }else{
            echo lang("search for", clean($search_string));
        }
	if (array_var($_GET, 'search_all_projects') != 'true' && current_member_search()) { ?>
	<br/><a class="internalLink" href="<?php echo get_url('search','search',array("search_for" => array_var($_GET, 'search_for'), "search_all_projects" => "true" )) ?>"><?php echo lang('search in all workspaces') ?></a>
        <?php } ?>
</div>
<div id="<?php echo $genid; ?>Search" class="search-container">
	<div class="no-results">
		<?php echo lang("no search result for", $search_string);?>
	</div>
</div>
<script>
	$("#searchButton").prop("disabled",false);
	$("#search_for_in").prop("disabled",false);	

</script>