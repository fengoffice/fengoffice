<?php

  /**
  * ContactWebpage class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ContactWebpage extends BaseContactWebpage {
  
    /**
    * Return Webpage type
    *
    * @access public
    * @param void
    * @return WebpageType
    */
    function getWebpageType() {
      return WebpageTypes::findById($this->getWebTypeId());
    } // getWebpageType
    
    /**
    * Return contact
    *
    * @access public
    * @param void
    * @return Contact
    */
    function getContact() {
      return Contacts::findById($this->getContactId());
    } // getContact
    
    /**
    * Edit webpage URL address
    *
    * @access public
    * @param string $URL
    * @return void
    */
    function editWebpageURL($URL) {
        	if($this->getURL() != $URL){
      		$this->setURL($URL);
      		$this->save();
    	}
    } // editWebpageURL
    
    
    /**
     * Builds a correct url from the url field, e.g.: adds the "http://" part if the original url doesn't have the scheme.
     * @return string
     */
    function getFixedUrl() {
    	// parse original url
    	$parsed_url = parse_url($this->getUrl());
    	
    	// scheme
    	$url_string = array_var($parsed_url, 'scheme', 'http') . "://";
    	
    	// put host and port if they were parsed
    	if (isset($parsed_url['host'])) {
    		$url_string .= $parsed_url['host'];
    		if (isset($parsed_url['port'])) {
    			$url_string .= ":" . $parsed_url['port'];
    		}
    	}
    	
    	// add the path to the file or folder, if defined
    	$url_string .= array_var($parsed_url, 'path', '');
    	
    	// add the query parameters
    	if (isset($parsed_url['query'])) {
    		$url_string .= "?" . $parsed_url['query'];
    	}
    	
    	// add the anchor if it is defined 
    	if (isset($parsed_url['fragment'])) {
    		$url_string .= "#" . $parsed_url['fragment'];
    	}
    	
    	return $url_string;
    }
    
  } // ContactWebpage 

?>