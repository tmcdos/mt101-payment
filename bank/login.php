<?php
include('./INC/DBASE.PHP');
include('./INC/logon.php');
session_start();
if(!is_object($_SESSION['user'])) $_SESSION['user'] = new dealer();
$user = &$_SESSION['user'];

if($user->login['ID'])
{
	if($user->logout > time())
	{
		header('Location:blank.php');
		die;
	}
	else
	{
		loger('Session timeout ['.$user->login['NAME'].']');
		@session_destroy();
	}
}

if(isset($_POST['login']))
{
	$u = ivo_str($_POST["username"]);
	$p = ivo_str($_POST["password"]);
	if ($u!='' AND $p!='')
	{
		if($user->LogDealer($u,$p))
		{
			loger('OK login user ['.$user->login['NAME'].']');
			// set TIMESTAMP to logout
			$user->logout = time() + SESS_LEN;
			// redirect according ROLE
			header('Location:blank.php');
			die;
		}
		else
		{
			$err='Unknown user or wrong password supplied';
			loger('Bad login - User = '.$u.', Pass = '.$p);
		}
	}
	else $err='Please supply non-empty username and password';
}

	if($b = @file_get_contents($tmpdir.'/temp/login.htm'))
	{
		$b = str_replace('{HEADER}',@file_get_contents($tmpdir.'/temp/header.htm'),$b);
		$b = str_replace('{FOOTER}',@file_get_contents($tmpdir.'/temp/footer.htm'),$b);
		if($err!='') $z = 'alert("'.$err.'");';
			else $z = '';
		$b = str_replace('<!--{ERROR}-->',$z,$b);
		$b = str_replace('{PREP}',WEBDIR,$b);
		MakeMenu($b);
		$b = str_replace('{USERNAME}',$_POST['username'],$b);
		echo $b;
	}
	else die('Could not find template - login.htm');

?>