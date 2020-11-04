<?php

/**
 * Description of AuthenticationAdapter
 *
 * @author manu
 */
class Service_AuthenticationAdapter implements Zend_Auth_Adapter_Interface
{
	private $_login;
	private $_passwd;

	public function __construct($login,$passwd)
	{
		$this->_login=$login;
		$this->_passwd=$passwd;
	}

	public function authenticate()
	{
		$users=new Model_DbTable_Users();
		if ($users->checkPassword($this->_login, $this->_passwd))
		{
			$identity=$users->getUser($this->_login);
			return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS,$identity);
		}
		return new Zend_Auth_Result(Zend_Auth_Result::FAILURE,null);
	}
}
?>
