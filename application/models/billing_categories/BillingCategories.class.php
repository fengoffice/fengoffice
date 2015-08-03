<?php

/**
 * BillingCategories
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class BillingCategories extends BaseBillingCategories {
	
	public static function getDefaultBillingAmounts(){
		$categories = self::findAll();
		$billing_amounts = array();
		if ($categories){
			foreach ($categories as $bc){
				$billing_amounts[] = array('category' => $bc, 'value' => $bc->getDefaultValue(), 'origin' => 'default', 'default' => $bc->getDefaultValue());
			}
		}
		return $billing_amounts;
	}
} // BillingCategories

?>