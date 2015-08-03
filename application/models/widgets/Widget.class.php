<?php class Widget extends BaseWidget {
		
	var $path = null;
	
	/**
	 * @var Plugin
	 */
	var $plugin = null;
	
	function getOptions() {
		return ContactWidgetOptions::instance()->getDefaultOptions($this->getName());
	}
	
	/**
	 * @return Plugin 
	 */
	function getPlugin() {
		if (is_null($this->plugin) ) {
			if ($pid = $this->getPluginId()){
				$this->plugin = Plugins::instance()->findById($pid);
			}
		}
		return $this->plugin ;
	}
	
	function getPath() {
		$name = $this->getName();
		if ($this->path) {
			return $this->path;
		}elseif (parent::getPath()) {
			$this->path = parent::getPath();
			return $this->path;
		}else{
			// If path not set explicity: calc it
			$prefix = ROOT;
			if ($plg = $this->getPlugin()){
				$plgName = $this->getPlugin()->getSystemName();
				$prefix = PLUGIN_PATH."/".$plgName;
			}
			$this->path = $prefix ."/application/widgets/$name/index.php";
		}
		return $this->path;
	}
	
	function execute() {
		$path =  $this->getPath() ;
		if (file_exists( $path ) ) {
			include $path;
		}else{
		//	throw new Error("Widget has invalid path: '".$path."'") ;
		}
	}
	
	
	function getContactWidgetSettings($contact) {
		if (!$contact instanceof Contact) $contact = logged_user();
		
		$info = $this->getDefaultWidgetSettings();
		
		$contact_widget = ContactWidgets::instance()->findOne(array('conditions' => array('contact_id = ? AND widget_name = ?', $contact->getId(), $this->getName())));
		if ($contact_widget instanceof ContactWidget) {
			$info['order'] = $contact_widget->getOrder();
			$info['section'] = $contact_widget->getSection();
			$info['options'] = ContactWidgetOptions::instance()->getContactOptions($contact_widget->getWidgetName(), $contact->getId());
		}
		
		return $info;
	}
	
	function getDefaultWidgetSettings() {
		$info = array(
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'plugin_id' => $this->getPluginId(),
			'path' => $this->getPath(),
			'options' => $this->getOptions(),
			'section' => $this->getDefaultSection(),
			'order' => $this->getDefaultOrder(),
			'icon' => $this->getIconCls()
		);
		
		return $info;
	}
	
}