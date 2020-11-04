<?php
/**
 * Get and set data in conf files
 *
 * @author manu
 */
class Model_ConfFile_AuthorizedLogins
{
	private $confFilesDir;
	private $confFileNames=array("names"=>"users.dat","ids"=>"ids.dat","types"=>"types.dat");
	private $confFiles=array();
	private $names;
	private $types;
	private $ids;
	private $useSudo;
	private $copyConf;

	public function __construct()
	{
		$configuration = Zend_Registry::get('config');

		$this->copyConf=$configuration['options']['copyConf'];
		$this->useSudo=$configuration['options']['useSudo'];

		$this->setConfFilesDir($configuration['options']['confFile']);

		if ($this->copyConf)
		{
			$command=ROOT_PATH."/bin/saveconf.sh";
			if ($this->useSudo)
			{
				$command="sudo $command";
			}
			exec($command);
		}
		foreach($this->confFileNames as $key=>$fileName)
		{
			$this->setConfFile($key,file_get_contents($this->getConfFilesDir().$fileName));
		}
		$this->parseConfFiles();
	}
	
	private function parseConfFiles()
	{
		foreach($this->confFileNames as $key=>$fileName)
		{
			$conf=$this->getConfFile($key);
			$lines=explode("\n",$conf);

			$this->setDetail($key,$lines);
		}
	}
	/**
	 * Set a credentials detail array (names, ids or types)
	 *
	 * @param String $key the name of the array to set (names, ids or types)
	 * @param String[] $array
	 */
	private function setDetail($key,$array)
	{
		if (is_array($array))
		{
			foreach($array as $index=>$value)
			{
				if (empty($value))
				{
					unset($array[$index]);
				}
			}
			$this->$key=$array;
		}
		else
		{
			$this->$key=array();
		}
	}

	/**
	 * Get a credentials detail array (names, ids or types)
	 *
	 * @param String $key (names, ids or types)
	 * @return String[]
	 */
	public function getDetail($key)
	{
		return $this->$key;
	}

	public function removeElement($key,$element)
	{
		$array=$this->getDetail($key);
		foreach($array as $index=>$item)
		{
			if ($element==$item)
			{
				unset($array[$index]);
			}
		}
		$this->setDetail($key,$array);
	}

	public function addElement($key,$element)
	{
		$array=$this->getDetail($key);
		$array[]=$element;
		$this->setDetail($key,$array);
	}

	public function getConfFilesDir()
	{
		return $this->confFilesDir;
	}

	private function setConfFilesDir($confFileName)
	{
		$this->confFilesDir=$confFileName;
	}

	public function getConfFile($key)
	{
		if (isset($this->confFiles[$key]))
		{
			return $this->confFiles[$key];
		}
		return "";
	}

	private function setConfFile($key,$conf)
	{
		$this->confFiles[$key]=$conf;
	}

	public function addSet($set)
	{
		if ($set['new']['name'])
		{
			$this->addElement('names',$set['name']);
		}
		if ($set['new']['type'])
		{
			$this->addElement('types',$set['type']);
		}
		if ($set['new']['id'])
		{
			$this->addElement('ids',$set['id']);
		}
		$this->commitConfFiles();
	}

	public function commitConfFiles()
	{
		foreach($this->confFileNames as $key=>$fileName)
		{
			$lines=$this->getDetail($key);
			$this->setConfFile($key,implode("\n",$lines));
			file_put_contents($this->getConfFilesDir().$fileName,$this->getConfFile($key));
		}
		//do the system stuff
		if ($this->copyConf)
		{
			$command=ROOT_PATH."/bin/applyconf.sh";
			if ($this->useSudo)
			{
				$command="sudo $command";
			}
			exec($command);
		}
	}
}
