<?php

/**
  * Let user select an email account in a config option
  *
  * @author Alvaro Torterola <alvarotm01@gmail.com>
  */
class MailAccountConfigHandler extends ConfigHandler {
	
	/**
	 * Render form control
	 *
	 * @param string $control_name        	
	 * @return string
	 */
	function render($control_name) {
		$options = array ();
		
		$accounts = array();
		if (Plugins::instance()->isActivePlugin('mail')) {
			$accounts = MailAccounts::findAll();
		}
		
		foreach ($accounts as $account) {
			/* @var $account MailAccount */
			$account_text = $account->getName() . " [". $account->getEmailAddress() ."]";
			$option_attributes = $this->getValue() == $account->getId() ? array ('selected' => 'selected') : null;
			$options [] = option_tag($account_text, $account->getId(), $option_attributes);
			
		}
		
		return select_box($control_name, $options);
	}
	
} // MailFieldConfigHandler
