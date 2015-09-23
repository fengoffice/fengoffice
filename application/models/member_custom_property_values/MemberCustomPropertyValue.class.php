<?php

/**
 * MemberCustomPropertyValue class
 */
class MemberCustomPropertyValue extends BaseMemberCustomPropertyValue {

	/**
	 * Construct the object
	 *
	 * @param void
	 * @return null
	 */
	function __construct() {
		parent::__construct();
	} // __construct

	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		$cp = MemberCustomProperties::getCustomProperty($this->getCustomPropertyId());
		if($cp instanceof MemberCustomProperty){
			if($cp->getIsRequired() && ($this->getValue() == '')){
				$errors[] = lang('custom property value required', $cp->getName());
			}
			if($cp->getType() == 'numeric'){
				if($cp->getIsMultipleValues()){
					foreach(explode(',', $this->getValue()) as $value){
						if($value != '' && !is_numeric($value)){
							$errors[] = lang('value must be numeric', $cp->getName());
						}
					}
				}else{
					if($this->getValue() != '' && !is_numeric($this->getValue())){
						$errors[] = lang('value must be numeric', $cp->getName());
					}
				}
			}
		}//if
	} // validate

	
	function format_value() {
		$formatted = "";
		
		$cp = MemberCustomProperties::getCustomProperty($this->getCustomPropertyId());
		if($cp instanceof MemberCustomProperty) {
			switch ($cp->getType()) {
				case 'text':
				case 'numeric':
				case 'memo':
					$formatted = $this->getValue();
					break;
				case 'user':
				case 'contact':
					$c = Contacts::findById($this->getValue());
					$formatted = $c instanceof Contact ? clean($c->getObjectName()) : '';
					break;
				case 'boolean':
					$formatted = '<div class="db-ico ico-'. ($this->getValue() ? 'complete' : 'delete') .'">&nbsp;</div>';
					break;
				case 'date':
					if ($this->getValue() != '' && $this->getValue() != EMPTY_DATE && $this->getValue() != EMPTY_DATETIME) {
						$dtv = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $this->getValue());
						if ($dtv instanceof DateTimeValue) {
							$formatted = format_date($dtv, null, 0);
						}
					}
					break;
				case 'list':
					$formatted = $this->getValue();
					break;
				case 'table':
					$formatted = $this->getValue();
					break;
				case 'address':
					$values = str_replace("\|", "%%_PIPE_%%", $this->getValue());
					$exploded = explode("|", $values);
					foreach ($exploded as &$v) {
						$v = str_replace("%%_PIPE_%%", "|", $v);
						$v = escape_character($v);
					}
					if (count($exploded) > 0) {
						$address_type = array_var($exploded, 0, '');
						$street = array_var($exploded, 1, '');
						$city = array_var($exploded, 2, '');
						$state = array_var($exploded, 3, '');
						$country = array_var($exploded, 4, '');
						$zip_code = array_var($exploded, 5, '');
							
						$out = $street;
						if($city != '') $out .= ' - ' . $city;
						if($state != '') $out .= ' - ' . $state;
						if($country != '') $out .= ' - ' . lang("country $country");
					
						$formatted = '<div class="info-content-item">'. $out .'&nbsp;<a class="map-link coViewAction ico-map" href="http://maps.google.com/?q='. $out .'" target="_blank">'. lang('map') .'</a></div>';
					}
					break;
				default:
					$formatted = $this->getValue();
			}
		}
		
		return $formatted;
	}

} // ObjectPropertyValue

?>