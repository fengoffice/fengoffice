<?php
//Env::useLibrary('swift');

class SwiftHeaderValidator extends Swift_Mime_Headers_AbstractHeader {
	
	function validate_id_header_value($id) {
		if (!isset($this->_grammar) || !is_array($this->_grammar) || count($this->_grammar) == 0) {
			$this->_grammar = $this->getGrammar()->getGrammarDefinitions();
			//$this->initializeGrammar();
		}
		return preg_match(
			'/^' . $this->_grammar['id-left'] . '@' .
			$this->_grammar['id-right'] . '$/D',
			$id
		);
	}
	
	
	
	
  /**
   * Get the type of Header that this instance represents.
   * @return int
   * @see TYPE_TEXT, TYPE_PARAMETERIZED, TYPE_MAILBOX
   * @see TYPE_DATE, TYPE_ID, TYPE_PATH
   */
  public function getFieldType()
  {
    return self::TYPE_ID;
  }
  
  /**
   * Set the model for the field body.
   * This method takes a string ID, or an array of IDs
   * @param mixed $model
   * @throws Swift_RfcComplianceException
   */
  public function setFieldBodyModel($model)
  {
    //$this->setId($model);
  }
  
  /**
   * Get the model for the field body.
   * This method returns an array of IDs
   * @return array
   */
  public function getFieldBodyModel()
  {
    return "";//$this->getIds();
  }
  
    /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding white space will
   * not be added at this stage (see {@link toString()} for that).
   * @return string
   * @see toString()
   * @throws Swift_RfcComplianceException
   */
  public function getFieldBody()
  {
    if (!$this->getCachedValue())
    {
      $angleAddrs = array();
    
      foreach ($this->_ids as $id)
      {
        $angleAddrs[] = '<' . $id . '>';
      }
    
      $this->setCachedValue(implode(' ', $angleAddrs));
    }
    return $this->getCachedValue();
  }
  
}
?>