<?php
/**
 * FlashMessages handler
 *
 * @author manu
 */
class Helper_FlashMessages extends Zend_Controller_Action_Helper_Abstract
{
	const SUCCESS='success';
	const INFO='info';
	const WARNING='warning';
	const ERROR='error';

	private $flashTypes;
	private $_flashMessenger;
	
	public function init()
	{
		$this->flashTypes=array(self::SUCCESS,self::INFO,self::WARNING,self::ERROR);
		$this->_flashMessenger = Zend_Controller_Action_Helperbroker::getStaticHelper('FlashMessenger');
	}

	public function addMessage($message,$type=self::INFO)
	{
		if (!in_array($type,$this->flashTypes))
		{
			$type=self::INFO;
		}
		$this->_flashMessenger->setNamespace('flash_'.$type);
		$this->_flashMessenger->addMessage($message);
	}

	public function getMessages()
	{
		$flashMessages=array();
		foreach($this->flashTypes as $flashType)
		{
			$this->_flashMessenger->setNamespace('flash_'.$flashType);
			$flashMessages[$flashType]=$this->_flashMessenger->getMessages();
		}
		return $flashMessages;
	}

	public function direct($message,$type=self::INFO)
	{
		$this->addMessage($message,$type);
	}
}