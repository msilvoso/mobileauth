<?php
/**
 * FlashMessages handler
 *
 * @author manu
 */
class Helper_Logger extends Zend_Controller_Action_Helper_Abstract
{
	private $_logger;

	public function init()
	{
		$this->_logger=Zend_Registry::get('log');
	}
	
	public function direct()
	{
		return $this->_logger;
	}
}