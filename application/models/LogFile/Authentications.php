<?php
/**
 * Fetch all authentication attempts in the logs
 *
 * @author manu
 */
class Model_LogFile_Authentications {
	//constants
	const NONAME='N/A';
	private $days; //look for the last x days in the log files
	private $logFiles;
	private $months=array('Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'May'=>5,'Jun'=>6,'Jul'=>7,'Aug'=>8,'Sep'=>9,'Oct'=>10,'Nov'=>11,'Dec'=>12);
	
	//arguments
	private $logins;
	private $filteredLogins;
	private $useSudo;

	public function __construct()
	{
		$configuration = Zend_Registry::get('config');

		$this->logFiles=$configuration['options']['logFiles'];
		$this->days=$configuration['options']['days'];
		$this->useSudo=$configuration['options']['useSudo'];
		$this->getLoginsFromFiles();
	}

	private function getLoginsFromFiles()
	{
		$monthsThere=array();
		//parse apache logs
		ob_start();
		$command=ROOT_PATH."/bin/parselogs.sh ".$this->logFiles." ".$this->days;
		if ($this->useSudo)
		{
			$command="sudo $command";
		}
		passthru($command);
		$result=ob_get_contents();
		ob_end_clean();
		$logins=array();
		$names=array();
		$ids=array();
		$types=array();

		$lines=explode("\n",$result);

		foreach($lines as $line)
		{
			$line=trim($line);
			if (!empty($line))
			{
				$split=explode(" ",$line);
				$rawlogin=$split[0];
				$currentLogin=mktime(substr($rawlogin,12,2),substr($rawlogin,15,2),substr($rawlogin,18,2),$this->months[substr($rawlogin,3,3)],substr($rawlogin,0,2),substr($rawlogin,7,4));
				if (count($split)==2)
				{
					$device=$this->getDevice($split[1]);
					if (is_array($device))
					{
						$logins[]=$currentLogin;
						$names[]=self::NONAME;
						$ids[]=$device[0];
						$types[]=$device[1];
					}
				}
				else
				{
					$names[]=preg_replace('/.*\\\\/','',urldecode($split[1]));
					$ids[]=$split[2];
					$types[]=$split[3];
					$logins[]=$currentLogin;
				}
			}
		}

		//sort everything and take only the latest record of every type of login
		array_multisort($names,$ids,$types,$logins);
		$notableKeys=array();
		$lastRecord='';
		$lastKey='';
		foreach($names as $key=>$name)
		{
			$newLastRecord="$name $ids[$key] $types[$key]";
			if ($newLastRecord!=$lastRecord)
			{
				$lastRecord=$newLastRecord;
				$notableKeys[]=$lastKey;
			}
			$lastKey=$key;
		}
		$notableKeys[]=$lastKey;
		array_shift($notableKeys);

		//create the return array
		$notableNames=array();
		$notableIds=array();
		$notableTypes=array();
		$notableLogins=array();
		foreach($notableKeys as $key)
		{
			$notableNames[]=$names[$key];
			$notableIds[]=$ids[$key];
			$notableTypes[]=$types[$key];
			$notableLogins[]=date("Y-m-d H:i:s",$logins[$key]);
		}
		array_multisort($notableLogins,SORT_DESC,SORT_STRING,$notableNames,SORT_ASC,SORT_STRING,$notableIds,SORT_ASC,SORT_STRING,$notableTypes,SORT_ASC,SORT_STRING); //sort according to logins
		$ret=array();
		foreach($notableNames as $key=>$name)
		{
			$ret[]=array(
				'name'		=>	$name,
				'id'		=>	$notableIds[$key],
				'type'		=>	$notableTypes[$key],
				'login'		=>	$notableLogins[$key]
				);
		}
		$this->logins=$ret;
	}

	public function getLogins()
	{
		return $this->logins;
	}

	public function filterLogins($names,$types,$ids)
	{
		$this->filteredLogins=array();
		$count=1;
		$addArray=array();
		$newFlags=array();

		foreach($this->logins as $key=>$login)
		{
			$newFlags['name']=false;
			$newFlags['type']=false;
			$newFlags['id']=false;

			if (!in_array($login['name'],$names) && $login['name']!=self::NONAME)
			{
				$newFlags['name']=true;
				$this->logins[$key]['nameExists']=false;
			}
			else
			{
				if ($login['name']!=self::NONAME)
				{
					$this->logins[$key]['nameExists']=true;
				}
				else
				{
					$this->logins[$key]['nameExists']=false;
				}
			}
			if (!in_array($login['type'],$types))
			{
				$newFlags['type']=true;
				$this->logins[$key]['typeExists']=false;
			}
			else
			{
				$this->logins[$key]['typeExists']=true;
			}
			if (!in_array($login['id'],$ids))
			{
				$newFlags['id']=true;
				$this->logins[$key]['idExists']=false;
			}
			else
			{
				$this->logins[$key]['idExists']=true;
			}

			$login['new']=array();

			$login['new']['name']=$newFlags['name'];
			$login['new']['type']=$newFlags['type'];
			$login['new']['id']=$newFlags['id'];

			if ($newFlags['name'] || $newFlags['id'] || $newFlags['type'])
			{
				$login['count']=$count++;
				$this->filteredLogins[$login['count']]=$login;
			}
		}
	}

	public function getFilteredLogins()
	{
		return $this->filteredLogins;
	}

	private function getDevice($codedValue)
	{
		//decode string
		$codedValue=base64_decode($codedValue);
		//Device id
		$pointer=4;
		$deviceId='';
		$length=ord(substr($codedValue,$pointer,1));
		if (strlen($codedValue)<7+$length) return false;
		for($t=1;$t<=$length;$t++)
		{
			$value = sprintf("00%X",ord(substr($codedValue,$pointer+$t,1)));
			$deviceId = $deviceId . substr($value,strlen($value)-2);
		}
		//Policy Key (ignored)
		$pointer = $pointer + $length + 1;
		$length = ord(substr($codedValue,$pointer,1));
		if (strlen($codedValue)<$pointer+$length+1) return false;
		//Device type
		$pointer = $pointer + $length + 1;
		$length = ord(substr($codedValue,$pointer,1));
		if (strlen($codedValue)<$pointer+$length) return false;
		$pointer = $pointer + 1;
		$deviceType=substr($codedValue,$pointer,$length);
		return array($deviceId,$deviceType);
	}
}
