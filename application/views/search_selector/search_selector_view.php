<?php 
/*Required Parameters
 	$container_id 
	$search_placeholder 
	$search_function 
	$result_limit 
	$select_function 
	$extra_param       (for search and select function)
	$search_minLength
	$search_delay
*/
	$search_selector_init = "ogSearchSelector.init('$genid','$container_id',$extra_param,$search_function,$select_function,$search_minLength,$search_delay)";
?>

<div id="stargett"></div>	
<div id="<?php echo $container_id;?>" class="search-selector">
	<input id="<?php echo $container_id;?>-input-first-limit"  value="<?php echo $result_limit;?>" type="hidden">
	<input id="<?php echo $container_id;?>-input-limit"  value="<?php echo $result_limit;?>" type="hidden">
	
	<div class="search-input-div">
		
		<input id="<?php echo $container_id;?>-input" class="ico-search-m" type="text" class="search-input" tabindex="1000" autocomplete="off" placeholder="<?php echo $search_placeholder;?>">
	</div>
	
	<!--Item templates-->
	
	<!--template 1-->
	<div id="searchSelectorItemTemplate1" class="item-template search-selector-result">
		<div id="searchResultLeftTemplate1" class="searchResultLeft">
			<div id="searchResultImgTemplate1" class="search-result-ico"></div>
		</div>
		<div id="searchResultInfoTemplate1" class="searchResultInfo"></div>
	</div>
	
	<div id="showMoreTemplate1" class="item-template search-selector-more">
		<?php echo lang('show more');?>
	</div>	
	<!--END template 1-->
	
	<!--template 2-->
	<div id="searchSelectorItemTemplate2" class="item-template search-selector-result">
		<div id="searchResultLeftTemplate2" class="searchResultLeft">
			<div id="searchResultImgTemplate2" class="search-result-ico"></div>
		</div>
		<div id="searchResultInfoTemplate2" class="searchResultName"></div><br/>
		<div id="searchResultInfoTemplate2" class="searchResultInfo"></div>
		<div class="divi"></div>
	</div>
	
	<div id="showMoreTemplate2" class="item-template search-selector-more">
		<?php echo lang('show more');?>		
	</div>	
	
	<div id="advancedSearchTemplate2" class="item-template search-selector-more">
		<a href="#"><?php echo lang('advanced search')?></a>
	</div>
	<!--END template 2-->
	
	
</div>

<script>
$(function() {
	<?php echo $search_selector_init;?>;	
});
</script>

