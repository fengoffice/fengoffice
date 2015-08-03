<?php

  /**
  * SearchableObjects, generated on Tue, 13 Jun 2006 12:15:44 +0200 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class SearchableObjects extends BaseSearchableObjects {
    

    /**
     * Returns the searched words placed in a context, already cleaned and formatted in HTML
     * 
     * @param $content The content where the words were found
     * @param $search_for The searched words
     * @return String
     */
    function getContext($content, $search_for){
    	$context = '';
    	$context_length = 80;
    	
    	$content_lc = strtolower($content);
    	$search_for_lc = strtolower($search_for);
    	$pos = strpos($content_lc,$search_for_lc);
    	
    	if ($pos !== false){
	    	$beginning = substr($content, 0, $pos);
	    	
	    	//Get the beginning of the context
	    	if (strlen($beginning) > $context_length){
				$short_beginning = substr($beginning, strlen($beginning)-$context_length); // Shorten the part
	    		$beginning = '&hellip;' . clean(substr($short_beginning, strpos($short_beginning,' ') + 1)); // Do not cut words in half
	    	} else
	    		$beginning = clean($beginning);
	    	
	    	// Get the word searched for
	    	$middle = clean(substr($content, $pos, strlen($search_for)));
	    	
	    	//Get the end part of the context
	    	$ending = substr($content, $pos + strlen($search_for));
	    	if (strlen($ending) > $context_length){
	    		$short_ending = substr($ending, 0, $context_length);
	    		$ending = clean(substr($short_ending, 0, strrpos($short_ending,' '))) . '&hellip;';
	    	} else
	    		$ending = clean($ending);
	    	
	    	//Form the sentence
	    	$context = $beginning . '<b>' . $middle . '</b>' . $ending;
    	}
    	return $context;
    }
    
    /**
    * Return number of unique objects
    *
    * @param string $conditions
    * @return integer
    */
    function countUniqueObjects($conditions) {
      $table_name = SearchableObjects::instance()->getTableName(true);
      //$tags_table_name = Tags::instance()->getTableName();
      $where = '';
      if(trim($conditions <> '')) $where = "WHERE $conditions";
      
      $sql = "SELECT count(distinct `rel_object_manager`, `rel_object_id`) AS `count` FROM $table_name $where";
      $result = DB::executeAll($sql);
      if (!is_array($result) || !count($result)) return 0;
      
      return $result[0]['count'];
    } // countUniqueObjects
    
    /**
    * Drop all content from table related to $object
    *
    * @param ProjectDataObject $object
    * @return boolean
    */
    static function dropContentByObject(ApplicationDataObject $object) {
    	return SearchableObjects::delete(array('`rel_object_id` = ?', $object->getObjectId()));
    } // dropContentByObject
    
    /**
    * Drop all content from table related to $object
    *
    * @param ProjectDataObject $object
    * @return boolean
    */
    static function dropObjectPropertiesByObject(ApplicationDataObject $object) {
    	return SearchableObjects::delete(array('`rel_object_id` = ? AND `column_name` LIKE "property%" ', $object->getObjectId()));
    } // dropContentByObject
    
    /**
    * Drop column content from table related to $object
    *
    * @param ProjectDataObject $object
    * @return boolean
    */
    static function dropContentByObjectColumn(ApplicationDataObject $object, $column = '') {
    	return SearchableObjects::delete(array('`rel_object_id` = ? AND `column_name` = '. "'". $column . "'" ,  $object->getObjectId(), $column));
    } // dropContentByObject
    
    /**
    * Drop columns content from table related to $object
    *
    * @param ApplicationDataObject $object
    * @return boolean
    */
    static function dropContentByObjectColumns(ApplicationDataObject $object, $columns = array()) {
    	$columns_csv = "'" . implode("','",$columns) . "'";
    	
    	return SearchableObjects::delete(array('`rel_object_id` = ? AND `column_name` in ('. $columns_csv . ')' , $object->getObjectId()));
    }
    
  } 
