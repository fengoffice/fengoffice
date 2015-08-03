<?php

  /**
  * Upload error
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class InvalidUploadError extends Error {

    /**
    * Filename
    *
    * @var string
    */
    private $name;
    
    /**
    * MIME type
    *
    * @var string
    */
    private $type;
    
    /**
    * Filesize
    *
    * @var integer
    */
    private $size;
    
    /**
    * TMP file location
    *
    * @var string
    */
    private $tmp_name;
    
    /**
    * Upload error code
    *
    * @var integer
    */
    private $upload_error_code;
     
    /**
    * Construct the InvalidUploadError
    *
    * @access public
    * @param array $file Element of $_FILES array
    * @return InvalidUploadError
    */
    function __construct($file, $message = null) {
      if(is_null($message)) $message = self::generateErrorMessage(array_var($file, 'error'));
      parent::__construct($message);
      
      $this->setName(array_var($file, 'name'));
      $this->setType(array_var($file, 'type'));
      $this->setSize(array_var($file, 'size'));
      $this->setTmpName(array_var($file, 'tmp_name'));
      $this->setUploadErrorCode(array_var($file, 'error'));
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
        'name'              => $this->getName(),
        'type'              => $this->getType(),
        'size'              => $this->getSize(),
        'tmp name'          => $this->getTmpName(),
        'upload error code' => $this->getUploadErrorCode()
      ); // array
    } // getAdditionalParams
    
    
    // UPLOAD_ERR_OK         Value: 0
    // There is no error, the file uploaded with success.
     
    // UPLOAD_ERR_INI_SIZE   Value: 1
    // The uploaded file exceeds the upload_max_filesize directive in php.ini.
     
    // UPLOAD_ERR_FORM_SIZE  Value: 2
    // The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
     
    // UPLOAD_ERR_PARTIAL    Value: 3
    // The uploaded file was only partially uploaded.
     
    // UPLOAD_ERR_NO_FILE    Value: 4
    // No file was uploaded.
     
    // UPLOAD_ERR_NO_TMP_DIR Value: 6
    // Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.
     
    // UPLOAD_ERR_CANT_WRITE Value: 7
    // Failed to write file to disk. Introduced in PHP 5.1.0.
     
    // UPLOAD_ERR_EXTENSION  Value: 8
    // A PHP extension stopped the file upload. Introduced in PHP 5.2.0.
    private static function generateErrorMessage($error_code) {
    	Env::useHelper('format');
    	switch ($error_code) {
    		case UPLOAD_ERR_INI_SIZE: return lang('upload error msg UPLOAD_ERR_INI_SIZE', format_filesize(get_max_upload_size()));
    		case UPLOAD_ERR_FORM_SIZE: return lang('upload error msg UPLOAD_ERR_FORM_SIZE', format_filesize(get_max_upload_size()));
    		case UPLOAD_ERR_PARTIAL: return lang('upload error msg UPLOAD_ERR_PARTIAL');
    		case UPLOAD_ERR_NO_FILE: return lang('upload error msg UPLOAD_ERR_NO_FILE');
    		case UPLOAD_ERR_NO_TMP_DIR: return lang('upload error msg UPLOAD_ERR_NO_TMP_DIR');
    		case UPLOAD_ERR_CANT_WRITE: return lang('upload error msg UPLOAD_ERR_CANT_WRITE');
    		case UPLOAD_ERR_EXTENSION: return lang('upload error msg UPLOAD_ERR_EXTENSION');
    		default: return lang('error upload file');
    	}
    }
    
    // ---------------------------------------------------
    //  Getters and setters
    // ---------------------------------------------------
    
    /**
    * Get name
    *
    * @access public
    * @param null
    * @return string
    */
    function getName() {
      return $this->name;
    } // getName
    
    /**
    * Set name value
    *
    * @access public
    * @param string $value
    * @return null
    */
    function setName($value) {
      $this->name = $value;
    } // setName
    
    /**
    * Get type
    *
    * @access public
    * @param null
    * @return string
    */
    function getType() {
      return $this->type;
    } // getType
    
    /**
    * Set type value
    *
    * @access public
    * @param string $value
    * @return null
    */
    function setType($value) {
      $this->type = $value;
    } // setType
    
    /**
    * Get size
    *
    * @access public
    * @param null
    * @return integer
    */
    function getSize() {
      return $this->size;
    } // getSize
    
    /**
    * Set size value
    *
    * @access public
    * @param integer $value
    * @return null
    */
    function setSize($value) {
      $this->size = $value;
    } // setSize
    
    /**
    * Get tmp_name
    *
    * @access public
    * @param null
    * @return string
    */
    function getTmpName() {
      return $this->tmp_name;
    } // getTmpName
    
    /**
    * Set tmp_name value
    *
    * @access public
    * @param string $value
    * @return null
    */
    function setTmpName($value) {
      $this->tmp_name = $value;
    } // setTmpName
    
    /**
    * Get upload_error_code
    *
    * @access public
    * @param null
    * @return integer
    */
    function getUploadErrorCode() {
      return $this->upload_error_code;
    } // getUploadErrorCode
    
    /**
    * Set upload_error_code value
    *
    * @access public
    * @param integer $value
    * @return null
    */
    function setUploadErrorCode($value) {
      $this->upload_error_code = $value;
    } // setUploadErrorCode
  
  } // InvalidUploadError

?>