<?php

final class LuceneDB {
	
	static private $index;
	
	/**
	 * Returns the search index
	 *
	 * @return Zend_Search_Lucene_Interface
	 */
	static function GetIndex(){
		if (!self::$index) {
			try {
				self::$index = Zend_Search_Lucene::open(SEARCH_DB_PATH);
			} catch (Exception $err) {
				self::$index = new Zend_Search_Lucene(SEARCH_DB_PATH, true); 
			}
		}
		return self::$index;
	}
	
	static function DeleteFromIndex(ProjectDataObject $object, $commitOnEnd = true){
		$term = new Zend_Search_Lucene_Index_Term(get_class($object->manager()) . $object->getObjectId(), 'id');
		
    	foreach (self::GetIndex()->termDocs($term) as $id)
        	self::GetIndex()->delete($id);
        
        if ($commitOnEnd)
        	self::GetIndex()->commit();
	}
	
	
	static function AddToIndex(SearchableObject $object, $commitOnEnd = true){
		$doc = new Zend_Search_Lucene_Document();
 
    	$doc->addField(Zend_Search_Lucene_Field::Keyword('combinedid', $object->getRelObjectManager() . $object->getRelObjectId()));
    	$doc->addField(Zend_Search_Lucene_Field::UnIndexed('objectid', $object->getRelObjectId()));
    	$doc->addField(Zend_Search_Lucene_Field::Keyword('manager', $object->getRelObjectManager()));
    	$doc->addField(Zend_Search_Lucene_Field::UnIndexed('column', $object->getColumnName()));
    	$doc->addField(Zend_Search_Lucene_Field::UnStored('text', $object->getContent()));
    	$doc->addField(Zend_Search_Lucene_Field::Text('workspaces', "ws" . $object->getProjectId() . " "));
    	$doc->addField(Zend_Search_Lucene_Field::Text('isprivate', ($object->getIsPrivate()? '1':'0') . " "));
    	
    	self::GetIndex()->addDocument($doc);
    	
        if ($commitOnEnd)
        	self::GetIndex()->commit();
        	
        return true;
	}
	
	/**
	 * Searches for objets
	 *
	 * @param unknown_type $conditions
	 * @return array
	 */
	static function findClean($conditions){
		$hits = self::GetIndex()->find($conditions);
		
		$result = array();
    	$loaded = array();
    	foreach ($hits as $hit) {
    		$id = $hit->manager . '-' . $hit->objectid;
    		if(!isset($loaded[$id]) || !($loaded[$id])){
              	$loaded[$id] = true;
              	$result[] = $hit;
    		}
    	}
        
        return $result;
	}
	
}
?>