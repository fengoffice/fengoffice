<?php

	function clear_page_actions(){
		return PageActions::instance()->clearActions();
	}

  /**
  * Return all page actions
  *
  * @access public
  * @param void
  * @return array
  */
  function page_actions() {
    return PageActions::instance()->getActions();
  } // page_actions
  
  /**
  * Add single page action
  * 
  * You can use set of two params where first param is title and second one
  * is URL (the default set) and you can use array of actions as first
  * parram mapped like $title => $url
  *
  * @access public
  * @param string $title
  * @param string $url
  * @return PageAction
  */
  function add_page_action() {
    
    $args = func_get_args();
    if(!is_array($args) || !count($args)) return;
    
    // Array of data as first param mapped like $title => $url
    if(is_array(array_var($args, 0))) {
      
      foreach(array_var($args, 0) as $title => $url) {
        if(!empty($title) && !empty($url)) {
          PageActions::instance()->addAction( new PageAction($title, $url, array_var($args, 1)) );
        } // if
      } // foreach
      
    // Four string params, title, URL and name
    } else {
      
      $title = array_var($args, 0);
      $url = array_var($args, 1);
      $name = array_var($args, 2);
      $target = array_var($args, 3);
      $attributes = array_var($args, 4);
      $isCommon = array_var($args, 5);
      
      if(!empty($title) && !empty($url)) {
        PageActions::instance()->addAction( new PageAction($title, $url, $name, $target, $attributes,$isCommon) );
      } // if
      
    } // if
    
  } // add_page_action

  /**
  * Single page action
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class PageAction {
    
    /**
    * Acction title
    *
    * @var string
    */
    public $title;
    
    /**
    * Action URL
    *
    * @var string
    */
    public $url;
    
    /**
     * Name to identify the action
     *
     * @var string
     */
    public $name;
    
    public $target;
    
    public $isCommon;
    
    public $attributes = array();
  
    /**
    * Construct the PageAction
    *
    * @access public
    * @param void
    * @return PageAction
    */
    function __construct($title, $url, $name, $target = null, $attributes = null,$isCommon = true) {
      $this->setTitle($title);
      $this->setURL($url);
      $this->setName($name);
      $this->setTarget($target);
      $this->setAttributes($attributes);
      $this->setIsCommon($isCommon);
    } // __construct
    
    // ---------------------------------------------------
    //  Getters and setters
    // ---------------------------------------------------
    
    /**
    * Get title
    *
    * @access public
    * @param null
    * @return string
    */
    function getTitle() {
      return $this->title;
    } // getTitle
    
    /**
    * Set title value
    *
    * @access public
    * @param string $value
    * @return null
    */
    function setTitle($value) {
      $this->title = $value;
    } // setTitle
    
    /**
     * Get the name that identifies the action
     *
     * @return string
     */
    function getName() {
    	return $this->name;
    }
    
    /**
     * Set the name that identifies the action
     *
     * @param string $name
     */
    function setName($name) {
    	$this->name = $name;
    }
    
    /**
    * Get url
    *
    * @access public
    * @param null
    * @return string
    */
    function getURL() {
      return $this->url;
    } // getURL
    
    /**
    * Set url value
    *
    * @access public
    * @param string $value
    * @return null
    */
    function setURL($value) {
      $this->url = $value;
    } // setURL
  
    
    /**
    * Get target
    *
    * @access public
    * @param null
    * @return string
    */
    function getTarget() {
      return $this->target;
    } // getTarget
    
    /**
    * Set target value
    *
    * @access public
    * @param string $value
    * @return null
    */
    function setTarget($value) {
      $this->target = $value;
    } // setTarget
  
    /**
    * Get attributes
    *
    * @access public
    * @param null
    * @return array
    */
    function getAttributes() {
      return $this->attributes;
    } // getAttributes
    
    /**
    * Set attributes value
    *
    * @access public
    * @param array $value
    * @return null
    */
    function setAttributes($value) {
      $this->attributes = $value;
    } // setAttributes
    
    /**
    * Get isCommon
    *
    * @access public
    * @param null
    * @return boolean
    */
    function getIsCommon() {
      return $this->isCommon;
    } // getIsCommon
    
    /**
    * Set isCommon value
    *
    * @access public
    * @param boolean $value
    * @return null
    */
    function setIsCommon($value) {
      $this->isCommon = $value;
    } // setIsCommon
    
  } // PageAction
  
  /**
  * Page actions container that can be accessed globaly
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class PageActions {
    
    /**
    * Array of PageAction objects
    *
    * @var array
    */
    private $actions = array();
    
    /**
    * Return all actions that are in this container
    *
    * @access public
    * @param void
    * @return array
    */
    function getActions() {
      return count($this->actions) ? $this->actions : null;
    } // getActions
    
    /**
    * Add single action
    *
    * @access public
    * @param PageAction $action
    * @return PageAction
    */
    function addAction(PageAction $action) {
      $this->actions[] = $action;
      return $action;
    } // addAction
    
    /**
     * Remove a single action
     * 
     * @access public
     * @param String $name
     */
    function removeAction($name) {
    	foreach ($this->actions as $k => &$action) {
    		if ($action->getName() == $name) {
    			unset($this->actions[$k]);
    		}
    	}
    }
    
    /**
    * Return single PageActions instance
    *
    * @access public
    * @param void
    * @return PageActions
    */
    function instance() {
      static $instance;
      
      // Check instance
      if(!($instance instanceof PageActions)) {
        $instance = new PageActions();
      } // if
      
      // Done!
      return $instance;
      
    } // instance
    
    function clearActions(){
    	$this->actions = array();
    	return true;
    }
  } // PageActions

?>