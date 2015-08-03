<?php

  /**
  * Dimensions
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
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
  	


	/**
	 * 
	 * Returns list of Dimensions where objects with $content_object_type_id can be located
	 * @param $content_object_type_id
	 * @author Pepe
	 */
	static function getAllowedDimensions($content_object_type_id) {
		$enabled_dimensions_sql = "AND false";
		$enabled_dimensions_ids = implode(',', config_option('enabled_dimensions'));
		if ($enabled_dimensions_ids != "") {
			$enabled_dimensions_sql = "AND d.id IN ($enabled_dimensions_ids)";
		}
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
				content_object_type_id = $content_object_type_id
				$enabled_dimensions_sql
			GROUP BY dimension_id
			ORDER BY is_required DESC, d.default_order ASC, dimension_name ASC
		
		";
		$dimensions = array();
		$res= DB::execute($sql);
		return $res->fetchAll();
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

    
  } // Dimensions 
