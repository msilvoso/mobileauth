<?php
class Model_DbTable_Users extends Zend_Db_Table_Abstract
{
	protected $_name 	= 'users';
	protected $_primary 	= 'login';

        protected $_sequence    = false;

	public function getUser($login)
	{
		$row = $this->fetchRow($this->select()->where('login = ?', $login));

		if (!$row) 
		{
			return false;
		}
		return $row->toArray();
	}
	public function addUser($login, $password)
	{
		if ($this->getUser($login)===false)
		{
			$data = array(
				'login' => $login,
				'passwd' => sha1($password),
			);
			$this->insert($data);
			return true;
		}
		return false;
	}
	public function updateUser($login, $password)
	{
		if ($this->getUser($login)!==false)
		{
			$data = array(
					'passwd' => sha1($password),
				);
			$where = $this->getAdapter()->quoteInto('login = ?', $login);
			$this->update($data, $where);
			return true;
		}
		return false;
	}
	public function checkPassword($login, $password)
	{
		$testUser=$this->getUser($login);
		if ($testUser['passwd']==sha1($password))
		{
			return true;
		}
		return false;
	}
}
