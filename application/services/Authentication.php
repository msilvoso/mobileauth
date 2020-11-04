<?php
/**
 * Default authentication class
 *
 * @author manu
 */
class Service_Authentication
{
	/**
	* @var Zend_Auth_Adapter_DbTable
	*/
	protected $_authAdapter;

	/**
	* @var Zend_Auth
	*/
	protected $_auth;

	/**
	 * @var Service_Authentication
	 */
	protected static $_instance=null;

	/**
	* Authenticate a user
	*
	* @param  array $credentials Matched pair array containing login/passwd
	* @return boolean
	*/
	public function authenticate($credentials)
	{
		$adapter = $this->getAuthAdapter($credentials);
		$auth    = $this->getAuth();
		$result  = $auth->authenticate($adapter);

		if (!$result->isValid()) {
			return false;
		}

		return true;
	}

	public function getAuth()
	{
		if (null === $this->_auth) {
			$this->_auth = Zend_Auth::getInstance();
		}
		return $this->_auth;
	}

	public function getIdentity()
	{
		$auth = $this->getAuth();
		if ($auth->hasIdentity()) {
			return $auth->getIdentity();
		}
		return false;
	}

	/**
	* Clear any authentication data
	*/
	public function clear()
	{
		$this->getAuth()->clearIdentity();
	}

	/**
	* Set the auth adpater.
	*
	* @param Zend_Auth_Adapter_Interface $adapter
	*/
	public function setAuthAdapter(Zend_Auth_Adapter_Interface $adapter)
	{
		$this->_authAdapter = $adapter;
	}

	/**
	* Get and configure the auth adapter
	*
	* @param  array $value Array of user credentials
	* @return Zend_Auth_Adapter_DbTable
	*/
	public function getAuthAdapter($values)
	{
		if (null === $this->_authAdapter) {
			$authAdapter = new Service_AuthenticationAdapter($values['login'],$values['passwd']);
			$this->setAuthAdapter($authAdapter);
		}
		return $this->_authAdapter;
	}

	public static function getInstance()
	{
		if (null === self::$_instance)
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}
