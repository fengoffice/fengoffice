<?php

  /**
  * Dimensions Model
  *
  * @author Feng Office
  * 
  */
  class Dimensions extends BaseDimensions {
    
  	private static $dimensions_by_id = array();
  	private static $dimensions_by_code = array();
  	
  	static function getDimensionById($id) {
  		$dim = array_var(self::$dimensions_by_id, $id);
  		if (!$dim instanceof Dimension) {
  			$dim = Dimensions::findById($id);
  			if ($dim instanceof Dimension) self::$dimensions_by_id[$id] = $dim;
  		}
  		return $dim;
  	}
    
  	
  	/**
  	 *
  	 * Returns list of Dimensions associated to a dimension (and? or? both?) object types
  	 * @param $associated_dimension_id
  	 * @param $associated_object_type
  	 * @param $get_properties
  	 * 
  	 */
  	static function getAssociatedDimensions($associated_dimension_id, $associated_object_type, $get_properties = true) {
  		
  		if ($get_properties) {
  			$dim_field = 'associated_dimension_id';
  			$ot_field = 'associated_object_type_id';
  			$res_dim_field = 'dimension_id';
  		} else {
  			$dim_field = 'dimension_id';
  			$ot_field = 'object_type_id';
  			$res_dim_field = 'associated_dimension_id';
  		}
  		
  		$search_condition = "`$dim_field` = $associated_dimension_id AND `$ot_field` = $associated_object_type";
  		$associations = DimensionMemberAssociations::findAll(array('conditions' => $search_condition));
  		// TODO: Hacerlo recursivo cuando get_properties = true
  		
  		$dimensions = array();
  		foreach ($associations as $assoc) {
  			$dimensions[] = Dimensions::getDimensionById($assoc->getColumnValue($res_dim_field));
  		}
  		return $dimensions;
  	}
  	

	static private $allowed_dimensions_by_type_cache = array();
	/**
	 * 
	 * Returns list of Dimensions where objects with $content_object_type_id can be located
	 * @param $content_object_type_id
	 * @author Pepe
	 */
	static function getAllowedDimensions($content_object_type_id) {

		// try to get the data from cache
		$dims = array_var(self::$allowed_dimensions_by_type_cache, $content_object_type_id);
		if ($dims) {
			return $dims;
		}

		// if not in cache then get it from db and store in cache var

		$enabled_dimensions_sql = "AND false";
		$enabled_dimensions_ids = implode(',', config_option('enabled_dimensions'));
		if ($enabled_dimensions_ids != "") {
			$enabled_dimensions_sql = "AND d.id IN ($enabled_dimensions_ids)";
		}
		
		//@ToDo: If advanced_core is activated, filter out the 'hidden' (not related) dimensions to the object_type
		//Hook: 
		//INNER JOIN fo_dimension_content_object_options dcoo ON d.id = dcoo.dimension_id
		//and dcoo.content_object_type_id = $content_object_type_id
		//and dcoo.option = "hide_member_selector_in_forms"
		//and dcoo.value = 0
		    
		
		$sql = "
			SELECT
				dotc.dimension_id AS dimension_id,
				d.name as dimension_name,
				d.code as dimension_code,
				d.options as dimension_options,
				(dotc.is_required OR d.is_required) AS is_required,
				dotc.is_multiple AS is_multiple,
				d.is_manageable AS is_manageable
			
			FROM 
				".TABLE_PREFIX."dimension_object_type_contents dotc
				INNER JOIN ".TABLE_PREFIX."dimensions d ON d.id = dotc.dimension_id
				INNER JOIN ".TABLE_PREFIX."object_types t ON t.id = dotc.dimension_object_type_id
			
			WHERE 
				dotc.content_object_type_id = $content_object_type_id
				$enabled_dimensions_sql
			GROUP BY dimension_id
			ORDER BY is_required DESC, d.default_order ASC, dimension_name ASC
		
		";
		$dimensions = array();
		$res= DB::execute($sql);
		$dims = $res->fetchAll();

		// store in cache var
		self::$allowed_dimensions_by_type_cache[$content_object_type_id] = $dims;

		return $dims;
	}
	
	/**
	 * @return Dimension
	 */
	static function findByCode($code) {
		if (count(self::$dimensions_by_code) == 0) {
			$dims = Dimensions::findAll();
			foreach ($dims as $dim) self::$dimensions_by_code[$dim->getCode()] = $dim;
		}
		return array_var(self::$dimensions_by_code, $code);
	}

    

	static function getSmallDimensions($max_size = 500) {

		$dimension_ids = array();
		$enabled_dimension_ids = config_option('enabled_dimensions');

		$rows = DB::executeAll("
			select dimension_id, count(id) as size
			from ".TABLE_PREFIX."members 
			group by dimension_id
		");

		foreach ($rows as $row) {
			if (in_array($row['dimension_id'], $enabled_dimension_ids) && $row['size'] < 500) {
				$dimension_ids[] = $row['dimension_id'];
			}
		}

		return $dimension_ids;
	}

  } // Dimensions 
