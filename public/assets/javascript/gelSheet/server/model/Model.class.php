<?php

/**
 * Generic model implementation.
 * @abstract 
 */
class Model {
	
	function __construct(){	
	}
		
	//TODO recorrer TODAS LAS fields que no sean array e insertarlas.
	// Ojo chequear que si el id esta seateado sino hacer el insert sin ese id
	function save(){

		$classname = get_class($this) ;
		$tablename = table(strtolower( $classname."s" )) ; //

		$sql_into  =  '(' ;
		$sql_values = "(";
		$has_attr = false ; 
		foreach ( get_class_vars($classname) as $varname => $null) {
			if ( is_array($this->$varname) ) {  //TODO
				// es una coleccion y no esta mapeada en la tabla. Mandaar a persistir a cada una 
				//TODO			
			}else {
			
				$has_attr = true ;
				// Bueno , aca lo mas probale es que cada atributo tenga su currespondiente en la table structure.
				// Entonces estaria de toque . . .
				$fieldname = ucfirst($varname) ; // standart de nuestra base ej BookId
				$sql_into .= "$fieldname," ;
		
				$value = $this->$varname;
		 		if ( !isset($value) ) {
		 			$sql_values .= 'null';
		 		}
				if  ( is_string($value) )  {
					$sql_values.="'$value'" ;
				}elseif (is_bool($value)) {
					$sql_values .=  ($value)?1:0 ;
					
				}else {
					$sql_values .= $value ;
				}	
				$sql_values.="," ; 
			}		
		}
		if ( $has_attr )  {  
			//  tengo que sacar la ultima coma y agragarle un espacio al final.
			$sql_into = substr($sql_into,0,-1).") " ;
			$sql_values = substr($sql_values,0,-1).") " ;
		}	
		
		// genero el sql ;
		$sql = "INSERT INTO $tablename ".$sql_into." VALUES " .$sql_values ;
		
		if  ( !mysql_query($sql) ) {
			$err = new GsError(234,"Error saving book") ;
			if ( $err->isDebugging() ) {
				$err->addContentElement("Table" , $tablename ) ;
				$err->addContentElement("SQL Error" , mysql_error() ) ;
				$err->addContentElement("SQL: ", $sql ) ;
			}	
			throw $err ; 
		}
	}
	
	
	function load($id = null) {
		$classname = get_class($this) ;
		$tablename = table(strtolower( $classname."s" )) ; // 
		$idname = $classname."Id" ; 		
		$idvalue = ( $id != null ) ? $id : $this->$idname ;
		
		$sql = "SELECT * FROM  $tablename WHERE $idname=$idvalue LIMIT 1";
		$result =  mysql_query($sql);
		if ($row = mysql_fetch_object($result)) {
			foreach (get_class_vars($classname) as $attribute => $null ) {
				$dbfield = ucfirst($attribute);
				$this->$attribute = $row->$dbfield ;
			}
		}
		$this->loadCollections();		
	}
	
	
	function loadCollections() {
		//loadsheets, loadFontstyles, etc
		//TODO recorrer las que sean array y hacer loadXXX
		
	}
	
	/**
	 * @abstract 
	 */
	function delete(){}
	
	function fromJson ($json_obj) {
		foreach (get_object_vars($json_obj) as $var_name => $var_value ) { 
			$this->$var_name  = $var_value ;  	
		}
		return $this ;
	}
	
	function toJson() {
		return json_encode($this) ;
	}
	
	/**
	 * Implements the tipical functions based on the attribute names, such as 'get', 'set' , 'add' 
	 *
	 * @param string $method
	 * @param array $attributes
	 * @return unknown
	 */
	function __call($method, $attributes) {	
		if(isset($attributes) && count($attributes))
			$value = $attributes[0] ;
		else 
			$value = null;
			
		$fieldname = substr($method,3 ) ;
		$fieldname[0] = strtolower($fieldname[0]) ;
		$prefix = substr($method,0,3) ;
		
		if ( $prefix== "get")  {
			return $this->$fieldname ;
			
		}elseif ( $prefix == "set" ) {
			$this->$fieldname = $value  ;
			 
		}elseif ( $prefix == "add" ) {
			$fieldname.="s" ;
			if ( $this->$fieldname == null )
				$this->$fieldname = array() ;
			array_push($this->$fieldname, $value) ;	
			 			
		
		}
	}
}
