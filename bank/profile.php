<?php
include('./INC/DBASE.PHP');
include('./INC/logon.php');
session_start();
$user = &$_SESSION['user'];

if($user->logout <= time())
{
	loger('Session timeout ['.$user->login['NAME'].']');
	@session_destroy();
	header('Location:'.WEBDIR.'/login.php');
	die;
}
else
{
	// set TIMESTAMP to logout
	$user->logout = time() + SESS_LEN;
}


if(isset($_POST['person'])) $arr['NAME']=ivo_str($_POST['person']);

if(isset($_POST['user'])) $arr['LOGIN']=ivo_str($_POST['user']);
if(isset($_POST['pass'])) $arr['PASS']=ivo_str($_POST['pass']);
if(isset($_POST['pass_2'])) $pass2=ivo_str($_POST['pass_2']);
	else $pass2=$arr[$a]['PASS'];

if(isset($_POST['cmdSave']))
{
	if($arr['NAME']=='' OR count(explode(' ',$arr['NAME']))<2) $err='Missing first or last name';
	elseif(strlen($arr['NAME'])>48) $err='Full name can be no longer than 50 symbols';
	elseif($arr['LOGIN']=='') $err='Missing Username';
	elseif(strlen($arr['LOGIN'])>16) $err='Username can be no longer than 16 symbols';
	elseif($arr['PASS']=='') $err='Missing password';
	elseif(strlen($arr['PASS'])>16) $err='Password can be no longer than 16 symbols';
	elseif($arr['PASS']!=ivo_str($_POST['pass2'])) $err='Passwords differ - type again';

	if($err=='')
	{
		$query = "SELECT COUNT(*) FROM USER WHERE LOGIN='".$arr['LOGIN']."' AND PASS='".$arr['PASS']."' AND ID<>".$user->login['ID'];
		$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
		$us = mysql_result($result,0,0);
		if($us) $err='This combination of Username + Password is already taken ! Please choose another';
		else
		{
			$query = 'UPDATE USER SET '.IVO_update($arr,'ID').' WHERE ID='.$user->login['ID'];
			$result = mysql_query($query) or trigger_error($query.'<br>'.mysql_error(),E_USER_ERROR);
			loger('Updated client ID='.$user->login['ID']);
			$user->login['LOGIN'] = $arr['LOGIN'];
			$user->login['PASS'] = $arr['PASS'];
			$user->login['NAME'] = $arr['NAME'];
		}
	}
}

	if($b = @file_get_contents($tmpdir.'/temp/profile.htm'))
	{
		$b = str_replace('{HEADER}',@file_get_contents($tmpdir.'/temp/header.htm'),$b);
		$b = str_replace('{FOOTER}',@file_get_contents($tmpdir.'/temp/footer.htm'),$b);
		if($err!='') $z = 'alert("'.$err.'");';
			else $z = '';
		$b = str_replace('<!--{ERROR}-->',$z,$b);
		$b = str_replace('{PREP}',WEBDIR,$b);
		MakeMenu($b);

		$b = str_replace('{USER}',$err!='' ? $arr['LOGIN'] : $user->login['LOGIN'],$b);
		$b = str_replace('{PASS}',$err!='' ? $arr['PASS'] : $user->login['PASS'],$b);
		$b = str_replace('{PASS_2}',$err!='' ? $pass2 : '',$b);

		$b = str_replace('{PERSON}',$err!='' ? $arr['NAME'] : $user->login['NAME'],$b);

		echo $b;
	}
	else die('Could not find template - profile.htm');
?>