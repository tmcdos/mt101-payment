<?php

class Dealer
{
	var $login; // User information from table CLIENT

	function Dealer()
	{
		$this->jam = WEBDIR.'/login.php';
	}
	
// Проверява за името и паролата
	function LogDealer($u,$p)
	{	
		if($u=='' OR $p=='') return false;
		$query = "SELECT * FROM USER WHERE LOGIN='".$u."' AND PASS='".$p."' LIMIT 1";
		$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
		if(mysql_num_rows($result))
		{
			$this->login = mysql_fetch_array($result,MYSQL_ASSOC);
			return true;
		}
		else
		{
			unset($this->login);
			return false;
		}
	}

}
?>