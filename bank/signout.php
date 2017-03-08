<?php 
include("./INC/DBASE.PHP");
include("./INC/logon.php");
session_start();
if($_GET['timeout']!=1) loger('Logout user ['.$user->login['USER'].']');
	else loger('Session timeout ['.$user->login['USER'].']');
session_destroy();
header('Location:login.php');
?>