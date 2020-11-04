<?php
/**
 * FlashMessages handler
 *
 * @author manu
 */
class Plugin_Authentication extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		if ($request->getControllerName()!='error')
		{
			$authentication=Service_Authentication::getInstance();

			$identity=$authentication->getIdentity();

			if ($identity===false
				&& ($request->getControllerName()!='out'))
			{
				//not logged in
				$request->setControllerName('out')
					->setActionName('index')
					->setDispatched(false);
			}
		}
	}
}

