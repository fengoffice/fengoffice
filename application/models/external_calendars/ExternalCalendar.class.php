<?php
/**
 * ExternalCalendar
 * Generado el 22/2/2012
 * 
 */
class ExternalCalendar extends BaseExternalCalendar {
	
	//START ExternalCalendarProperties functions
	function getExternalCalendarProperties() {
		$properties = ExternalCalendarProperties::findByExternalCalendarId($this->getId());
		return $properties;
	} 

	function getExternalCalendarProperty($key) {
		$property = ExternalCalendarProperties::findByExternalCalendarIdAndKey($this->getId(),$key);
		if($property instanceof ExternalCalendarProperty){
			return $property;
		}else{
			return null;
		}
	}

	function getExternalCalendarPropertyValue($key) {
		$property = ExternalCalendarProperties::findByExternalCalendarIdAndKey($this->getId(),$key);
		if($property instanceof ExternalCalendarProperty){
			return $property->getValue();
		}else{
			return null;
		}
	}
	
	function setExternalCalendarPropertyValue($key, $value) {
		$property = ExternalCalendarProperties::findByExternalCalendarIdAndKey($this->getId(),$key);
		if($property instanceof ExternalCalendarProperty){
			$property->setValue($value);
			$property->save();
		}else{
			$property = new ExternalCalendarProperty();
			$property->setExternalCalendarId($this->getId());
			$property->setKey($key);
			$property->setValue($value);
			$property->save();
		}		
	}
	//END ExternalCalendarProperties functions
			
	function delete() {
		//delete properties for this calendar
		$properties = $this->getExternalCalendarProperties();		
		if(count($properties) > 0){
			foreach ($properties as $prop){
				$prop->delete();
			}
		}
		return parent::delete();
	}
}
?>