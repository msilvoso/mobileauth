<?php
/**
 * FlashMessages handler
 *
 * @author manu
 */
class Service_FlashMessages
{
	const SUCCESS='success';
	const INFO='info';
	const WARNING='warning';
	const ERROR='error';

	private $flashTypes;
	private $_flashMessenger;

	public function __construct($flashMessenger)
	{
		$this->_flashMessenger=$flashMessenger;
		$this->flashTypes=array(self::SUCCESS,self::INFO,self::WARNING,self::ERROR);
	}

	public function addMessage($message,$type)
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

}
