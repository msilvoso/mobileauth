<?php

class IndexController extends Zend_Controller_Action
{

	public function indexAction()
	{
		$this->_helper->logger()->debug(__METHOD__);

		//get the logins
		$logsParse=new Model_LogFile_Authentications();
		$confParse=new Model_ConfFile_AuthorizedLogins();
		$logsParse->filterLogins($confParse->getDetail('names'), $confParse->getDetail('types'), $confParse->getDetail('ids'));
		$this->view->filteredLogins=$logsParse->getFilteredLogins();
		$this->view->logins=$logsParse->getLogins();

		//get the days
		$configuration = Zend_Registry::get('config');
		$this->view->days=$configuration['options']['days'];
	}

	public function addAction()
	{
		$this->_helper->logger()->debug(__METHOD__);
		$this->_helper->viewRenderer->setNoRender();

		//get the logins
		
		$logsParse=new Model_LogFile_Authentications();
		$confParse=new Model_ConfFile_AuthorizedLogins();
		$logsParse->filterLogins($confParse->getDetail('names'), $confParse->getDetail('types'), $confParse->getDetail('ids'));
		$logins=$logsParse->getFilteredLogins();

		//add the missing login
		$toAdd=$this->_getParam('id','');
		if (!empty($toAdd) && is_numeric($toAdd))
		{
			if (!empty($logins[$toAdd]))
			{
				$confParse->addSet($logins[$toAdd]);
			}
		}

		//redirect to the index action
		$this->_helper->flashMessages('Credentials added!',Service_FlashMessages::SUCCESS);
		$this->_helper->logger()->info($this->view->login.' | Add: '.$logins[$toAdd]['name'].' '.$logins[$toAdd]['id'].' '.$logins[$toAdd]['type']);
		$this->_helper->Redirector('index','index');
	}

	public function delAction()
	{
		$this->_helper->logger()->debug(__METHOD__);
		$this->_helper->viewRenderer->setNoRender();

		//get the logins
		$logsParse=new Model_LogFile_Authentications();
		$confParse=new Model_ConfFile_AuthorizedLogins();

		$toDelId=$this->_getParam('id','');
		$toDelName=$this->_getParam('name','');
		$toDelType=$this->_getParam('type','');

		if (!empty($toDelId))
		{
			$confParse->removeElement('ids',$toDelId);
		}
		elseif (!empty($toDelName))
		{
			$confParse->removeElement('names',$toDelName);
		}
		elseif (!empty($toDelType))
		{
			$confParse->removeElement('types',$toDelType);
		}

		$confParse->commitConfFiles();

		//redirect to the index action
		$this->_helper->flashMessages('Detail removed!',Service_FlashMessages::SUCCESS);
		$this->_helper->logger()->info($this->view->login." | Remove: $toDelId$toDelName$toDelType");
		$this->_helper->Redirector('index','index');
	}

	public function logoutAction()
	{
		$this->_helper->logger()->debug(__METHOD__);
		$this->_helper->viewRenderer->setNoRender();

		$authentication=Service_Authentication::getInstance();
		$authentication->clear();

		$this->_helper->flashMessages('You have been logged out!',Service_FlashMessages::INFO);

		$this->_helper->logger()->info('Logout:'.$this->view->login);
		$this->_helper->Redirector('out','index');
	}

	public function changepasswordAction()
	{
		$this->_helper->logger()->debug(__METHOD__);
		$this->_helper->viewRenderer->setNoRender();

		$password1=$this->_getParam('passwd1','');
		$password2=$this->_getParam('passwd2','');

		if ($password1!=$password2)
		{
			$this->_helper->flashMessages('The passwords are not the same!',Service_FlashMessages::ERROR);
		}
		elseif (strlen($password1)<10)
		{
			$this->_helper->flashMessages('Password length has to be 10 characters at least!',Service_FlashMessages::ERROR);
		}
		else
		{
			$this->_helper->flashMessages('Password successfully changed!',Service_FlashMessages::SUCCESS);
			$users=new Model_DbTable_Users();
			$authentication=Service_Authentication::getInstance();
			$identity=$authentication->getIdentity();
			$users->updateUser($identity['login'], $password1);

		}
		$this->_helper->Redirector('index','index');
	}

	public function __call( $method, $args )
	{
		if('Action' == substr($method, -6)) 
		{
			$this->_redirect('index');
		}
	}
}
