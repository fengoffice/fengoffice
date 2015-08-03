<?php

  /**
  * This exception is thrown when we fail to save object because it failed 
  * to pass validation
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class DAOValidationError extends Error {
    
    /**
    * Object that we failed to save
    *
    * @var DataObject
    */
    private $object;
    
    /**
    * Array of validation errors
    *
    * @var array
    */
    private $errors;
  
    /**
    * Construct the DAOValidationError
    *
    * @access public
    * @param DataObject $object
    * @param array $errors Validation errors
    * @param string $message
    * @return DAOValidationError
    */
    function __construct($object, $errors, $message = null) {
      $this->setObject($object);
      $this->setErrors($errors);
      
      if(is_null($message)) {
        $message = lang('error form validation') . ":";
      } // if
      
      $message .= "\r\n- " . implode("\r\n- ", $errors);      
           	
      parent::__construct($message);
      
    } // __construct
    
    
    /**
    * Return errors specific params...
    *
    * @access public
    * @param void
    * @return array
    */
    function getAdditionalParams() {
      return array(
        'object' => $this->getObject(),
        'errors' => $this->getErrors()
      ); // array
    } // getAdditionalParams
    
    /**
    * Get object
    *
    * @access public
    * @param null
    * @return DataObject
    */
    function getObject() {
      return $this->object;
    } // getObject
    
    /**
    * Set object value
    *
    * @access public
    * @param DataObject $value
    * @return null
    */
    function setObject($value) {
      $this->object = $value;
    } // setObject
    
    /**
    * Get errors
    *
    * @access public
    * @param null
    * @return array
    */
    function getErrors() {
      return $this->errors;
    } // getErrors
    
    /**
    * Set errors value
    *
    * @access public
    * @param array $value
    * @return null
    */
    function setErrors($value) {
      $this->errors = $value;
    } // setErrors
  
  } // DAOValidationError

?>