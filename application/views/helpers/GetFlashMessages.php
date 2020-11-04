<?php
class View_Helper_GetFlashMessages extends Zend_View_Helper_Abstract
{
        public function getFlashMessages()
        {
		$messenger = Zend_Controller_Action_Helperbroker::getStaticHelper('FlashMessages');
                return $messenger->getMessages();
        }
}