<?php 

class ContactWidgetOptions extends BaseContactWidgetOptions {

	function getDefaultOption($widget, $option) {
		$info = array();
		$option = $this->findOne(array('conditions' => array('contact_id=0 AND widget_name=? AND `option`=?',$widget,$option)));
		if ($option instanceof ContactWidgetOption) {
			$info = $option->getArrayInfo();
		}
		return $info;
	}
	
	function getDefaultOptions($widget) {
		$infos = array();
		$options = $this->findAll(array('conditions' => array('contact_id=0 AND widget_name=?',$widget)));
		foreach ($options as $option) {
			$infos[] = $option->getArrayInfo();
		}
		return $infos;
	}
	
	
	function getContactOption($widget, $contact_id, $option) {
		$info = array();
		$option = $this->findOne(array('conditions' => array('contact_id=? AND widget_name=? AND `option`=?',$contact_id,$widget,$option)));
		if ($option instanceof ContactWidgetOption) {
			$info = $option->getArrayInfo();
		}
		return $info;
	}
	
	function getContactOptions($widget, $contact_id) {
		$infos = $this->getDefaultOptions($widget);
		foreach ($infos as &$info) {
			$contact_info = $this->getContactOption($widget, $contact_id, $info['option']);
			if (count($contact_info) > 0) {
				$info['value'] = $contact_info['value'];
				$info['contact_id'] = $contact_info['contact_id'];
			}
		}
		
		return $infos;
	}
}