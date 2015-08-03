<?php
class SearchController extends ApplicationController {
	
	static $MYSQL_MIN_WORD_LENGHT = 4 ;
	
	/**
	 * Debug mode (Dev only)
	 * @var unknown_type
	 */
	var $debug = 0 ;
	
	/**
	 * @var boolean
	 */
	var $showQueryTime = false ;
	
	/**
	 * Characters that mysql take as word separator
	 * @var array
	 */
	var $mysqlWordSeparator = array('@' , '.' , ',');
	
	/**
	 * Search string
	 * @var unknown_type
	 */
	var $search_for ;
	
	var $advanced_conditions;
        
        /**
	 * Search dimension
	 * @var unknown_type
	 */
	var $search_dimension ;
	
	/**
	 * Page size
	 * @var unknown_type
	 */
	var $limit = 10;
	
	/**
	 * Start integer
	 * @var unknown_type
	 */
	var $start = 0 ;
	
	/**
	 * If $ignoreMinWordLength = false: 
	 * =>	Makes a standart fulltext always. 
	 * 		Depending on mysql configuration ft_min_word_len if the word wil be searched
	 * Else : 
	 * => 	If searchString has words with legth ft_min_word_len
	 * 		Makes a like query (Performance Killer)
	 * @var integer
	 */
	var $ignoreMinWordLength = true;
	
	/**
	 * Real limit  SQL satatement.
	 * We dont make a 'count' on SQL. 
	 * This will help to guess to total results, o at least, if render the 'next' button  
	 * Should be Greater than limit, because of PHP result filters
	 * @var int
	 */
	var $limitTest = 30 ;
	 
	/**
	 * Max content size to show on results view
	 * @var unknown_type
	 */
	var $contentSize = 200;
	
	/**
	 * If true search for prefixes, giving more results.
	 * @var boolean
	 */
	var $wildCardSearch = true ;
	
	/**
	 * Max title size to show on results view
	 * @var integer
	 */
	var $titleSize = 100;
	
	/**
	 * True to filter duplicated results in PHP
	 * This may cause errors on "total" pagination
	 * @var boolean
	 */
	var $filterDuplicate = true ;
	
	/**
	 * Max number of links to show on pagination
	 * @var unknown_type
	 */
	var $maxPageLinks = 5 ;
	
	/**
	 * @var StdClass
	 */
	var $pagination = null  ;
	
	
	function __construct() {
		$this->pagination = new StdClass();
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		ajx_set_panel("search");
		self::$MYSQL_MIN_WORD_LENGHT = (int)array_var(DB::executeOne("SHOW variables LIKE 'ft_min_word_len' "),"Value");
	}
	
	
	/**
	 * Execute search
	 * TODO: Performance gus: 
	 * Fetch only ids and execute a select statement by pk (fer each result)
	 * @param void
	 * @return null
	 */
	function search() {
		// Init vars
		$search_for = array_var($_GET, 'search_for');
        $search_dimension = array_var($_GET, 'search_dimension');
        $advanced = array_var($_GET, 'advanced');
		//$minWordLength = $this->minWordLength($search_for);
		//$useLike = ( $minWordLength && ($this->ignoreMinWordLength) && ($minWordLength < self::$MYSQL_MIN_WORD_LENGHT) );
        $useLike = false;
		if(strlen($search_for) < 4){
			$useLike = true;
		}
		$search_pieces= explode(" ", $search_for);
		$search_string = "";
		
		$search_string = mysql_real_escape_string($search_for, DB::connection()->getLink());
		
		$this->search_for = $search_for;
		$limit = $this->limit;
		$start = array_var($_REQUEST, 'start' , $this->start);
		$this->start = $start;
		$limitTest = max( $this->limitTest , $this->limit);
		$filteredResults = 0;
		$uid = logged_user()->getId();
		
		if(!isset($search_dimension)){
			$members = active_context_members(false);
		}else{
			if($search_dimension == 0){
				$members = array();
			}else{
				$members = array($search_dimension);
			}
		}
		
		// click on search everywhere
		if (array_var($_REQUEST, 'search_all_projects')) {
			$members = array();
		}
		
		$revisionObjectTypeId = ObjectTypes::findByName("file revision")->getId();
		
		$members_sql = "";
		if(count($members) > 0){
			$context_condition = "(EXISTS
										(SELECT om.object_id
											FROM  ".TABLE_PREFIX."object_members om
											WHERE	om.member_id IN (" . implode ( ',', $members ) . ") AND so.rel_object_id = om.object_id
											GROUP BY object_id
											HAVING count(member_id) = ".count($members)."
										)
									)";
			$context_condition_rev = "(EXISTS
										(SELECT fr.object_id FROM " . TABLE_PREFIX . "object_members om
															INNER JOIN ".TABLE_PREFIX."project_file_revisions fr ON om.object_id=fr.file_id
															INNER JOIN ".TABLE_PREFIX."objects ob ON fr.object_id=ob.id
															WHERE fr.file_id = so.rel_object_id AND ob.object_type_id = $revisionObjectTypeId AND member_id IN (" . implode ( ',', $members ) . ") 
															GROUP BY object_id 
															HAVING count(member_id) = ".count($members)."
										)
									)";
			$members_sql = "AND ( ".$context_condition." OR  ".$context_condition_rev.")";
									
			$this->search_dimension = implode ( ',', $members );
		}else{
			$this->search_dimension = 0;
		}

		$listableObjectTypeIds = implode(",",ObjectTypes::getListableObjectTypeIds());
		
		$can_see_all_tasks_cond = "";
		if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
			$can_see_all_tasks_cond = " AND IF((SELECT ot.name FROM ".TABLE_PREFIX."object_types ot WHERE ot.id=o.object_type_id)='task',
			 (SELECT t.assigned_to_contact_id FROM ".TABLE_PREFIX."project_tasks t WHERE t.object_id=o.id) = ".logged_user()->getId().",
			 true)";
		}
		
		if($_POST) {
			
			$conditions = array_var($_POST, 'conditions');
			$search = array_var($_POST, 'search');
			$type_object = array_var($search, 'search_object_type_id');
			if(!is_array($conditions)) $conditions = array();
			$where_condiition = '';
			$conditions_view = array();
			$cont = 0;
			$joincp ="";
			$value="";
			$custom_prop_id="";
			foreach($conditions as $condition){
				$condValue = array_key_exists('value', $condition) ? $condition['value'] : '';
				if($condition['field_type'] == 'boolean'){
					$value = array_key_exists('value', $condition);
				}else if($condition['field_type'] == 'date'){
					if ($condValue != '') {
						$dtFromWidget = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format'), $condValue);
						$value = date("m/d/Y", $dtFromWidget->getTimestamp());
					}
				}else{
					$value = mysql_real_escape_string($condValue, DB::connection()->getLink());
				}			
				$condition_condition = mysql_real_escape_string(array_var($condition, 'condition'), DB::connection()->getLink());
				$condition_field_name = mysql_real_escape_string(array_var($condition, 'field_name'), DB::connection()->getLink());
				$conditionLocal = "like";
				tpl_assign('type_object', $type_object);
				//CREO QUE ESTO ESTA MAL
				if (isset($condition['custom_property_id']) and is_numeric($condition['custom_property_id'])){
					$condition_field_name = 'value';
					$joincp = 'JOIN  '.TABLE_PREFIX.'custom_property_values cp ON cp.object_id = so.rel_object_id';
				};
				if (($condition['custom_property_id'] == 'phone_number') ){
					$condition_field_name = 'number';
					$joincp = 'JOIN  '.TABLE_PREFIX.'contact_telephones ct ON ct.contact_id = so.rel_object_id';
				};				
				if (($condition['custom_property_id'] == 'email_address') ){
					$condition_field_name = 'email_address';
					$joincp = 'JOIN  '.TABLE_PREFIX.'contact_emails ce ON ce.contact_id = so.rel_object_id';
				};
				if (($condition['custom_property_id'] == 'web_url') ){
					$condition_field_name = 'url';
					$joincp = 'JOIN  '.TABLE_PREFIX.'contact_web_pages cw ON cw.contact_id = so.rel_object_id';
				};
				if (($condition['custom_property_id'] == 'im_value') ){
					$condition_field_name = 'value';
					$joincp = 'JOIN  '.TABLE_PREFIX.'contact_im_values cim ON cim.contact_id = so.rel_object_id';
				};
				
				if ($condition_condition == "=" or $condition_condition == ">" or $condition_condition == "<" or $condition_condition == "<>" or $condition_condition == ">=" or $condition_condition == "<="){
					$conditionLocal = $condition_condition;
				};	
				if($condition_field_name == "id"){
					$condition_field_name = "o`.`id" ;
				};			
				
				if($condition_condition == "like"){
					$where_condiition .= " AND `" . $condition_field_name . "` " . "like" . " '%" . $value . "%' ";
					$con = "like '%" . $value . "%' ";
				}else if($condition_condition == "ends with"){
					$where_condiition .= " AND `" . $condition_field_name . "` " . "like" . " '%" . $value . "' ";
					$con = "like '%" . $value . "' ";
				}else if($condition_condition == "start with"){
					$where_condiition .= " AND `" . $condition_field_name . "` " . "like" . " '" . $value . "%' ";
					$con = "like '" . $value . "%' ";
				}else if($condition_condition == "not like"){
					$where_condiition .= " AND `" . $condition_field_name . "` " . "not like" . " '%" . $value . "%' ";
					$con = "not like '%" . $value . "%' ";
				}else{					
					$where_condiition .= " AND `" . $condition_field_name . "` " . $conditionLocal . " '" . $value . "' ";
					$con = $conditionLocal . " '" . $value . "' ";
				}
				if (($condition['custom_property_id'] == 'address') ){
					$addressCondiition .= " AND (street ".$con;
					$addressCondiition .= " OR city " .$con;
					$addressCondiition .= " OR state ".$con;
					$addressCondiition .= " OR country ".$con;
					$addressCondiition .= " OR zip_code ".$con. ")";
					$where_condiition = $addressCondiition;
					$joincp = 'JOIN  '.TABLE_PREFIX.'contact_addresses ca ON ca.contact_id = so.rel_object_id';
				};
				$conditions_view[$cont]['id'] = $condition['id'];
				$conditions_view[$cont]['custom_property_id'] = $custom_prop_id;
				$conditions_view[$cont]['field_name'] = $condition['field_name'];
				$conditions_view[$cont]['condition'] = $condition['condition'];
				$conditions_view[$cont]['value'] = $value;
				$cont++;
			}
			tpl_assign('conditions', $conditions_view);
			
			if(empty($conditions)){
				$search_string = array_var($search, 'text');
				$where_condiition .= " AND so.content LIKE '%$search_string%'";
			}
			if($type_object){
				$object_table = ObjectTypes::findById($type_object);
				$table = $object_table->getTableName();				
			}

			$sql = "
			SELECT DISTINCT so.rel_object_id AS id
			FROM ".TABLE_PREFIX."searchable_objects so
			".$joincp."
			INNER JOIN  ".TABLE_PREFIX.$table." nto ON nto.object_id = so.rel_object_id 
			INNER JOIN  ".TABLE_PREFIX."objects o ON o.id = so.rel_object_id 
			WHERE (
				(
					EXISTS ( 
						SELECT object_id FROM ".TABLE_PREFIX."sharing_table sh
						WHERE o.id = sh.object_id 
						AND sh.group_id  IN (
		   									SELECT permission_group_id FROM ".TABLE_PREFIX."contact_permission_groups WHERE contact_id = $uid
											)
							)				
			 	)
			) " . $where_condiition . $members_sql . $can_see_all_tasks_cond . " ORDER by o.updated_on DESC
			LIMIT $start, $limitTest";
		} else {
			
			$type_object = '';
			
			$sql = "	
			SELECT DISTINCT so.rel_object_id AS id   
			FROM ".TABLE_PREFIX."searchable_objects so
			WHERE " . (($useLike) ? " so.content LIKE '%$search_string%' " : " MATCH (so.content) AGAINST ('\"$search_string\"' IN BOOLEAN MODE) ") . "  
			AND (EXISTS
				(SELECT o.id
				 FROM  ".TABLE_PREFIX."objects o
				 WHERE	o.id = so.rel_object_id AND (	
							(o.object_type_id = $revisionObjectTypeId AND  
								EXISTS ( 
									SELECT group_id FROM ".TABLE_PREFIX."sharing_table WHERE object_id  = ( SELECT file_id FROM ".TABLE_PREFIX."project_file_revisions WHERE object_id = o.id ) 
									AND group_id IN (SELECT permission_group_id FROM ".TABLE_PREFIX."contact_permission_groups WHERE contact_id = $uid )
								)
							) 
							OR (
								(EXISTS
									(SELECT object_id
										FROM  ".TABLE_PREFIX."sharing_table sh
										WHERE o.id = sh.object_id 
										AND sh.group_id  IN (
											SELECT permission_group_id FROM ".TABLE_PREFIX."contact_permission_groups WHERE contact_id = $uid
										)
									)
								)
			 				)
			 		) AND o.object_type_id IN ($listableObjectTypeIds) " . $members_sql . $can_see_all_tasks_cond . "
				)
			)			
			
			ORDER BY id DESC 
			LIMIT $start, $limitTest";
		}
		
		tpl_assign('type_object', $type_object);
		$db_search_results = array();
		$search_results_ids = array();
		
		if(!$advanced){
			$timeBegin = time();
			$res = DB::execute($sql);
			$timeEnd = time();
			while ($row = $res->fetchRow() ) {
				$search_results_ids[$row['id']] = $row['id'];
			}
		}
		// Prepare results for view to avoid processing at presentation layer 
		$search_results = $this->prepareResults($search_results_ids, $null, $limit);
		
		// Calculate or approximate total for pagination
		$total = count($search_results_ids) + $start ;
		
		if ( count ( $search_results_ids ) < $limitTest ) {
			$total = count($search_results_ids) + $start ;
		}else{
			$total = lang("many") ;
		}
		//$total -= $filteredResults ;
		$this->total = $total ;
		
		// Pagination
		$this->buildPagination($search_results, $search_for);
		
		// Extra data
		$extra = new stdClass() ;
		if ($this->showQueryTime) {
			$extra->time = $timeEnd-$timeBegin ;
		}
		//$extra->filteredResults = $filteredResults ;

		// Template asigns
		tpl_assign('pagination', $this->pagination);
		tpl_assign('search_string', $search_for);
		tpl_assign('search_dimension', $this->search_dimension);
		tpl_assign('search_results', $search_results);
		tpl_assign('advanced', $advanced);
		tpl_assign('extra', $extra );

		$types = array(array("", lang("select one")));
		$object_types = ObjectTypes::getAvailableObjectTypes();

		foreach ($object_types as $ot) {
			$types[] = array($ot->getId(), lang($ot->getName()));
		}
//		if ($selected_type != '')
//		tpl_assign('allowed_columns', $this->get_allowed_columns($selected_type));
		
		tpl_assign('object_types', $types);
		
		//Ajax
		if (!$total && !$advanced){
			if($_POST && count($search_results < 0)){
				tpl_assign('msg_advanced', true);
			}else{
				$this->setTemplate('no_results');
			}
		}
		ajx_set_no_toolbar(true);
		
	}
	
	function general_search() {
		// Init vars
		$search_dimension = array_var($_GET, 'search_dimension');
						
		$filteredResults = 0;
		$uid = logged_user()->getId();
		
		if(!isset($search_dimension)){
			$members = active_context_members(false);
		}else{
			if($search_dimension == 0){
				$members = array();
			}else{
				$members = array($search_dimension);
			}
		}
		
		// click on search everywhere
		if (array_var($_REQUEST, 'search_all_projects')) {
			$members = array();
		}
		
		$revisionObjectTypeId = ObjectTypes::findByName("file revision")->getId();
		
		$members_sql = "";
		if(count($members) > 0){
			$context_condition = "(EXISTS
										(SELECT om.object_id
											FROM  ".TABLE_PREFIX."object_members om
											WHERE	om.member_id IN (" . implode ( ',', $members ) . ") AND so.rel_object_id = om.object_id
											GROUP BY object_id
											HAVING count(member_id) = ".count($members)."
										)
									)";
			$context_condition_rev = "(EXISTS
										(SELECT fr.object_id FROM " . TABLE_PREFIX . "object_members om
															INNER JOIN ".TABLE_PREFIX."project_file_revisions fr ON om.object_id=fr.file_id
															INNER JOIN ".TABLE_PREFIX."objects ob ON fr.object_id=ob.id
																	WHERE fr.file_id = so.rel_object_id AND ob.object_type_id = $revisionObjectTypeId AND member_id IN (" . implode ( ',', $members ) . ")
															GROUP BY object_id
															HAVING count(member_id) = ".count($members)."
										)
									)";
			$members_sql = "AND ( ".$context_condition." OR  ".$context_condition_rev.")";
				
			$this->search_dimension = implode ( ',', $members );
		}else{
			$this->search_dimension = 0;
		}
		
		$listableObjectTypeIds = implode(",",ObjectTypes::getListableObjectTypeIds());
		
		$can_see_all_tasks_cond = "";
		if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
			$can_see_all_tasks_cond = " AND IF((SELECT ot.name FROM ".TABLE_PREFIX."object_types ot WHERE ot.id=o.object_type_id)='task',
			 (SELECT t.assigned_to_contact_id FROM ".TABLE_PREFIX."project_tasks t WHERE t.object_id=o.id) = ".logged_user()->getId().",
			 true)";
		}
				
		$search_string = trim(array_var($_REQUEST, 'query', ''));
		$search_string = mysql_real_escape_string($search_string, DB::connection()->getLink());
		$start = array_var($_REQUEST, 'start' , 0);
		$orig_limit = array_var($_REQUEST, 'limit');
		$limit = $orig_limit + 1;
		
		$useLike = false;
		if(user_config_option("search_engine") == 'like'){
			$useLike = true;
		}
		if(strlen($search_string) < 4){
			$useLike = true;
		}
	
		if(strlen($search_string) > 0) {
			$this->search_for = $search_string;
			$logged_user_pgs = implode(',', logged_user()->getPermissionGroupIds());
			
			$sql = "
			SELECT DISTINCT so.rel_object_id AS id, so.content AS text_match, so.column_name AS field_match
			FROM ".TABLE_PREFIX."searchable_objects so
			WHERE " . (($useLike) ? " so.content LIKE '%$search_string%' " : " MATCH (so.content) AGAINST ('\"$search_string\"' IN BOOLEAN MODE) ") . "
			AND (EXISTS
				(SELECT o.id
				 FROM  ".TABLE_PREFIX."objects o
							 WHERE	o.id = so.rel_object_id AND (
							 (o.object_type_id = $revisionObjectTypeId AND
							 EXISTS (
							 SELECT group_id FROM ".TABLE_PREFIX."sharing_table WHERE object_id  = ( SELECT file_id FROM ".TABLE_PREFIX."project_file_revisions WHERE object_id = o.id )
									AND group_id IN ($logged_user_pgs)
												)
												)
												OR (
												(EXISTS
												(SELECT object_id
												FROM  ".TABLE_PREFIX."sharing_table sh
										WHERE o.id = sh.object_id
										AND sh.group_id  IN (
											$logged_user_pgs
														)
														)
														)
														)
														) AND o.object_type_id IN ($listableObjectTypeIds) " . $members_sql . $can_see_all_tasks_cond . "
														)
														)															
						GROUP BY(id)	
						ORDER BY(id) DESC							
						LIMIT $start, $limit";
				
			$rows = DB::executeAll($sql);
			if (!is_array($rows)) $rows = array();
				
			// show more
			$show_more = false;
			if(count($rows) > $orig_limit){
				array_pop($rows);
				$show_more = true;
			}
				
			if($show_more){
				ajx_extra_data(array('show_more' => $show_more));
			}

			$search_results = array();
			$object_ids = array();
			foreach ($rows as $ob_data) {
				// basic data
				$data = array(
						'id' => $ob_data['id'],
						'text_match' => $this->highlightOneResult($ob_data['text_match']),
						'field_match' => $ob_data['field_match'],												
				);
				$object_ids[] = $ob_data['id'];
				$search_results[] = $data;
			}
			
			if(count($object_ids) > 0){
				$result = ContentDataObjects::listing(array(
						"extra_conditions" => " AND o.id IN (".implode(",",$object_ids).") ",
						"include_deleted" => true,
				));
				$objects = $result->objects;
				foreach ($objects as $object) {
					foreach ($search_results as $key => $search_result) {
						if($search_result['id'] == $object->getId()){
							$search_results[$key]['name'] = $object->getObjectName();
							$class = 'ico-' . $object->getObjectTypeName();
							$search_results[$key]['iconCls'] = $class;
							$search_results[$key]['url'] = $object->getViewUrl();
							continue;
						}
					}
				}	
			}
			
			$row = "search-result-row-medium";
			ajx_extra_data(array('row_class' => $row));
				
			ajx_extra_data(array('search_results' => $search_results));
				
		}
		ajx_current("empty");
	}
	
	private function minWordLength($str) {
		$min = null ;		
		foreach ( explode(" ", $str) as $word ){
			if ( $len = strlen_utf(trim($word)) ){
				if (is_null($min) || $len < $min) {
					$min = $len ;
				}
			}
		}
		return $min ;
	}
	
	/**
	 * Build pagination based on $total, $limit and $search_results
	 * @param unknown_type $search_results
	 */
	private function buildPagination($search_results) {
		$start = $this->start;
		$limit = $this->limit;
		$total = $this->total;
		$search_for = $this->search_for;
		$search_dimension = $this->search_dimension;
		$this->pagination = new StdClass();
		$this->pagination->search_for = $search_for;
		$this->pagination->currentPage = ceil (( $start+1 ) / $limit);
		$this->pagination->currentStart = $start+1;
		$this->pagination->currentEnd = $start + count($search_results);
		$this->pagination->hasNext = ( count($search_results) == $limit );
		$this->pagination->hasPrevious = ($start-$limit >= 0); 
		$this->pagination->nextUrl = get_url("search", "search" , array("start" => $start+$limit , "search_for" => $search_for , "search_dimension" => $search_dimension));
		$this->pagination->previousUrl = get_url("search", "search" , array("start" => $start-$limit , "search_for" => $search_for , "search_dimension" => $search_dimension));
		$this->pagination->total = $total;
		$this->pagination->nextPages = array();
		$this->pagination->links = $this->buildPaginationLinks();
	}
	
	
	
	/**
	 * Map parameters and make some grouping, orders limits not done in DB
	 * 
	 * @param unknown_type array of int 
	 * @param unknown_type $filtered_results
	 * @param unknown_type $total
	 */
	private function prepareResults($ids, &$filtered_results, $limit) {
		$return = array();
		foreach ($ids as $search_result_id) {
			$search_results = array();
			if (!$limit) break;
			if (!is_numeric($search_result_id)) continue;
			
			$obj = Objects::findObject($search_result_id);
			if (!$obj instanceof ContentDataObject) continue;
			/* @var $obj ContentDataObject */
			$search_result['id'] = $obj->getId();
			$search_result['otid'] = $obj->getObjectTypeId();
			$search_result['title'] = $this->prepareTitle($obj->getObjectName());
			$search_result['memPath'] = json_encode($obj->getMembersIdsToDisplayPath());
			$search_result['url'] = $obj->getViewUrl();
			$search_result['created_by'] = $this->prepareCreatedBy($obj->getCreatedByDisplayName(), $obj->getCreatedById());
			$search_result['updated_by'] = $this->prepareCreatedBy($obj->getUpdatedByDisplayName(), $obj->getUpdatedById());
			$search_result['type'] = $obj->getObjectTypeName();
			$search_result['created_on'] = friendly_date($obj->getCreatedOn());
			$search_result['updated_on'] = friendly_date($obj->getObjectTypeName() == 'mail' ? $obj->getSentDate() : $obj->getUpdatedOn());
			$search_result['content'] = $this->highlightResult($obj->getSummary(array(
				"size" => $this->contentSize,
				"near" => $this->search_for
			)));
			hook::fire("search_result", $search_result, $search_result);
			$return[] = $search_result;
			$limit--;
		}
		return $return;
		
	} 	
		
	private function prepareCreatedBy($name,$id){
		return "<a href='".get_url('contact', 'card', array('id'=>$id))."'>".$name."</a>";
	}
	
	private function prepareContent($content) {
		return $this->highlightResult($this->cutResult($content, $this->contentSize));
	}

	private function prepareUrl($id, $handler) {
		if($handler) {
			eval('$item_class = '.$handler.'::instance()->getItemClass(); $instance = new $item_class();');
			$instance->setObjectId($id);
			$instance->setId($id);
			return $instance->getViewUrl();
		}else{
			return "#";
		} 
	}
	
	private function prepareTitle($title){
		if (!$title) {
			return lang("empty title");
		}
		return $this->highlightResult($this->cutResult($title, $this->titleSize));
	}
	
	/**
	 * Emphaisis around search keywords
	 * @param unknown_type $content
	 */
	private function highlightResult($text) {
		$pieces = explode(" ", $this->search_for);
		
		foreach ($pieces as $word) {
			$text = str_ireplace($word, "<em>".$word."</em>", $text) ;
		}
		return $text;
    }
    
    /**
     * Emphaisis around one search keywords and 
     * @param unknown_type $content
     */
    private function highlightOneResult($text) {
    	$text = html_to_text($text);
    	$pieces = explode(" ", $this->search_for);
    	
    	if(strlen($text) > 100){
	    	$text_ret = '...';    	    
	    	$text_ret .= substr($text, strpos($text, $pieces[0]) , 100);
	    	$text_ret .= '...';
    	}else{
    		$text_ret = $text;
    	}
    	return $text_ret;
    }

    
	private function buildPaginationLinks() {
		$currentPage = $this->pagination->currentPage;
		$links = array();
		$totalPages = ceil( $this->total / $this->limit );
		$links_count = 0;
		if ( is_numeric($this->total) ){
			$links_count =  ceil ( min ( $this->maxPageLinks, $totalPages ));
		}
		$startPage = min ( max(1,$currentPage - floor($links_count / 2) ), max(1,$totalPages - $links_count) );
		$endPage =  min ($totalPages , $startPage + $this->maxPageLinks);
		
		for ($i = $startPage ; $i <=$endPage ; $i++) {
			$links[$i] = get_url("search", "search", array(
				"start" =>  ($i-1 ) * $this->limit,
				"search_for"=>$this->search_for,
				"search_dimension"=>$this->search_dimension)
			);
		}
		return $links;
	}		
	
	
	/**
	 * Cut results
	 * @param unknown_type $content
	 * @param unknown_type $size
	 */
	private function cutResult($content, $size = 200  ) {
		$position = strpos($content,$this->search_for);
		$spacesBefore = min(10, $position); 
		if (strlen($content) > $size ){
			return substr($content , $position - $spacesBefore, $size)."...";
			
		}else{
			return $content ;
		}
	}
	
	function get_object_fields(){
		$fields = $this->get_allowed_columns(array_var($_GET, 'object_type'));
		ajx_current("empty");
		ajx_extra_data(array('fields' => $fields));		
	}
	
	function get_external_field_values(){
		$field = array_var($_GET, 'external_field');
		$report_type = array_var($_GET, 'report_type');
		$values = $this->get_ext_values($field, $report_type);
		ajx_current("empty");
		ajx_extra_data(array('values' => $values));
	}
	
	function get_object_column_list_task(){
		$allowed_columns = $this->get_allowed_columns_custom_properties(array_var($_GET, 'object_type'));
		$for_task = true;
		
		tpl_assign('allowed_columns', $allowed_columns);
		tpl_assign('columns', explode(',', array_var($_GET, 'columns', array())));	
		tpl_assign('genid', array_var($_GET, 'genid'));
		tpl_assign('for_task', $for_task);
		
		$this->setLayout("html");
		$this->setTemplate("column_list");
	}
	
	
	private function get_allowed_columns_custom_properties($object_type) {
		return array(); //FIXME: no usar todo lo de custom properties por el momento
		$fields = array();
		if(isset($object_type)){
			$customProperties = CustomProperties::getAllCustomPropertiesByObjectType($object_type);
			$objectFields = array();
			foreach($customProperties as $cp){				
				if ($cp->getType() != 'table')
					$fields[] = array('id' => $cp->getId(), 'name' => $cp->getName(), 'type' => $cp->getType(), 'values' => $cp->getValues(), 'multiple' => $cp->getIsMultipleValues());
			}
			$ot = ObjectTypes::findById($report->getObjectTypeId());
			eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");	
	
			$common_columns = Objects::instance()->getColumns(false);
			$common_columns = array_diff_key($common_columns, array_flip($managerInstance->getSystemColumns()));
			$objectFields = array_merge($objectFields, $common_columns);
			
			foreach($objectFields as $name => $type){
				if($type == DATA_TYPE_FLOAT || $type == DATA_TYPE_INTEGER){
					$type = 'numeric';
				}else if($type == DATA_TYPE_STRING){
					$type = 'text';
				}else if($type == DATA_TYPE_BOOLEAN){
					$type = 'boolean';
				}else if($type == DATA_TYPE_DATE || $type == DATA_TYPE_DATETIME){
					$type = 'date';
				}
				
				$field_name = Localization::instance()->lang('field '.$ot->getHandlerClass().' '.$name);
				if (is_null($field_name)) $field_name = lang('field Objects '.$name);
				
				$fields[] = array('id' => $name, 'name' => $field_name, 'type' => $type);
			}
	
		}
		usort($fields, array(&$this, 'compare_FieldName'));
		return $fields;
	}
	
	
	private function get_ext_values($field, $manager = null){
		$values = array(array('id' => '', 'name' => '-- ' . lang('select') . ' --'));
		if($field == 'contact_id' || $field == 'created_by_id' || $field == 'updated_by_id' || $field == 'assigned_to_contact_id' || $field == 'completed_by_id'
			|| $field == 'approved_by_id'){
			$users = Contacts::getAllUsers();
			foreach($users as $user){
				$values[] = array('id' => $user->getId(), 'name' => $user->getObjectName());
			}
		}else if($field == 'milestone_id'){
			$milestones = ProjectMilestones::getActiveMilestonesByUser(logged_user());
			foreach($milestones as $milestone){
				$values[] = array('id' => $milestone->getId(), 'name' => $milestone->getObjectName());
			}
		/*} else if($field == 'object_subtype'){
			$object_types = ProjectCoTypes::findAll(array('conditions' => (!is_null($manager) ? "`object_manager`='$manager'" : "")));
			foreach($object_types as $object_type){
				$values[] = array('id' => $object_type->getId(), 'name' => $object_type->getName());
			}*/
		}
		return $values;
	}
        
        private function get_allowed_columns($object_type) {
		$fields = array();
		if(isset($object_type)){
			$customProperties = CustomProperties::getAllCustomPropertiesByObjectType($object_type);
			$objectFields = array();
			
			foreach($customProperties as $cp){
				if ($cp->getType() == 'table') continue;
				
				$fields[] = array('id' => $cp->getId(), 'name' => $cp->getName(), 'type' => $cp->getType(), 'values' => $cp->getValues(), 'multiple' => $cp->getIsMultipleValues());
			}
			
			$ot = ObjectTypes::findById($object_type);
			eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
			$objectColumns = $managerInstance->getColumns();
			
			$objectFields = array();
			
			$objectColumns = array_diff($objectColumns, $managerInstance->getSystemColumns());
			foreach($objectColumns as $column){
				$objectFields[$column] = $managerInstance->getColumnType($column);
			}
			
			$common_columns = Objects::instance()->getColumns(false);
			$common_columns = array_diff_key($common_columns, array_flip($managerInstance->getSystemColumns()));
			$objectFields = array_merge($objectFields, $common_columns);

			foreach($objectFields as $name => $type){
				if($type == DATA_TYPE_FLOAT || $type == DATA_TYPE_INTEGER){
					$type = 'numeric';
				}else if($type == DATA_TYPE_STRING){
					$type = 'text';
				}else if($type == DATA_TYPE_BOOLEAN){
					$type = 'boolean';
				}else if($type == DATA_TYPE_DATE || $type == DATA_TYPE_DATETIME){
					$type = 'date';
				}
				
				$field_name = Localization::instance()->lang('field '.$ot->getHandlerClass().' '.$name);
				if (is_null($field_name)) $field_name = lang('field Objects '.$name);

				$fields[] = array('id' => $name, 'name' => $field_name, 'type' => $type);
			}
	
			$externalFields = $managerInstance->getExternalColumns();
			foreach($externalFields as $extField){
				$field_name = Localization::instance()->lang('field '.$ot->getHandlerClass().' '.$extField);
				if (is_null($field_name)) $field_name = lang('field Objects '.$extField);
				
				$fields[] = array('id' => $extField, 'name' => $field_name, 'type' => 'external', 'multiple' => 0);
			}
			//if Object type is person
			$objType = ObjectTypes::findByName('contact');
			if ($objType instanceof ObjectType){
				if($object_type == $objType->getId()){
					$fields[] = array('id' => 'email_address', 'name' => lang('email address'), 'type' => 'text');
					$fields[] = array('id' => 'phone_number', 'name' => lang('phone number'), 'type' => 'text');
					$fields[] = array('id' => 'web_url', 'name' => lang('web pages'), 'type' => 'text');
					$fields[] = array('id' => 'im_value', 'name' => lang('instant messengers'), 'type' => 'text');
					$fields[] = array('id' => 'address', 'name' => lang('address'), 'type' => 'text');	
				}
			}		
		}
		usort($fields, array(&$this, 'compare_FieldName'));
		return $fields;
		
	}
	
	private function compare_FieldName($field1, $field2){
		return strnatcasecmp($field1['name'], $field2['name']);
	}
}
