<?php

class OutController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$logger=Zend_Registry::get('log');
		$this->_helper->logger()->debug(__METHOD__);
		$authentication=Service_Authentication::getInstance();
		$identity=$authentication->getIdentity();
		if ($identity!==false)
		{
			//not logged in
			$this->_helper->Redirector('index','index');
		}
	}
	
	public function loginAction()
	{
		$logger=Zend_Registry::get('log');
		$this->_helper->logger()->debug(__METHOD__);
		$this->_helper->viewRenderer->setNoRender();
		
		$authentication=Service_Authentication::getInstance();
		$identity=$authentication->getIdentity();
		if ($identity!==false)
		{
			$this->_helper->Redirector('index','index');
		}

		$credentials=array();
		$credentials['login']=$this->_getParam('login','');
		$credentials['passwd']=$this->_getParam('passwd','');

		if ($authentication->authenticate($credentials))
		{
			$this->_helper->logger()->info('Login successful:'.$credentials['login']);
			$this->_helper->Redirector('index','index');
		}
		else
		{
			$this->_helper->flashMessages('Login failed!',Service_FlashMessages::ERROR);
			$this->_helper->logger()->notice('Login failed:'.$credentials['login']);
			$this->_helper->Redirector('index','out');
		}
	}


	public function __call( $method, $args )
	{
		if('Action' == substr($method, -6)) 
		{
			$this->_redirect('index');
		}
	}
}