<?php
/**
 * FlashMessages handler
 *
 * @author manu
 */
class Helper_Authentication extends Zend_Controller_Action_Helper_Abstract
{
	public function init()
	{
		$authentication=Service_Authentication::getInstance();
		$identity=$authentication->getIdentity();
		Zend_Registry::set('identity',$identity);
	}
	
	public function preDispatch()
	{
		$identity=Zend_Registry::get('identity');
		if ($identity!==false)
		{
			$controller = $this->getActionController();
			$controller->view->login=$identity['login'];
		}
	}
}