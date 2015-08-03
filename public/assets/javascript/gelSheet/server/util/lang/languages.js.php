<?php include("lang_list.php"); ?>

var lang_array = new Array() ; 
<?php if (! $lang[$this->lang])  $lang[$this->lang]=array(); ?>
<?php foreach ( $lang[$this->lang] as  $term => $term_traduced): ?>
lang_array['<?php echo $term ?>']= '<?php echo $term_traduced ?>' ; 
<?php endforeach; ?>

function lang( term ) {
	if ( lang_array[term] == undefined ) {
		return term ;
	}else  {
		return lang_array[term] ;
	}
}