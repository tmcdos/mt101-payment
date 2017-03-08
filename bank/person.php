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

  if($_POST['firma']==0) $_POST['firma'] = 1;

if(isset($_POST['edit1']))
{
	//$col['ID'] = fnum($_POST['nomer']);
	$col['NAME'] = ivo_str($_POST['imena']);
	$col['IBAN'] = ivo_str($_POST['iban']);
	$col['EGN'] = ivo_str($_POST['egn']);
	$col['ZAPLATA'] = fnum($_POST['zaplata']);
	$col['ACTIVE'] = (int)$_POST['active'];
	/*if(!$col['ID']) $err='Въведете служебния номер на лицето';
	else*/if(strlen($col['NAME'])>80) $err='Full name can be no longer than 80 symbols';
	elseif(strlen($col['IBAN'])>25) $err='IBAN is longer than 25 symbols';
	elseif($col['IBAN']=='') $err='Missing IBAN';
	elseif($col['EGN']=='') $err='Missing social security number';
	elseif($col['NAME']=='') $err='Missing full name';
	else
	{
		if($err=='')
		{
		  $col['FIRMA'] = $_POST['firma'];
			if($_REQUEST['edit_1']) $query = 'UPDATE PERSON SET '.IVO_update($col,'IVO').' WHERE ID='.$_REQUEST['edit_1'];
				else $query = 'INSERT INTO PERSON '.IVO_insert($col,'IVO');
		 	$result = mysql_query($query,$conn);
		 	$a = mysql_errno($conn);
		 	if($a)
		 	{
		 		if($a == 1062) $err='Duplicate information - name or IBAN';
			 		else trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
		 	}
		 	else
		 	{
		 		if($_REQUEST['edit_1']) $query = 'UPDATE PERSON SET CHANGED=NOW(),CHANGER='.$user->login['ID'].' WHERE ID='.$_REQUEST['edit_1'];
		 			else $query = 'UPDATE PERSON SET CREATED=NOW(),WRITER='.$user->login['ID'].' WHERE ID='.mysql_insert_id();
			 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
		 	}
		}
		if($err=='')
		{
			unset($col);
			unset($_REQUEST['edit_1']);
		}
	}
}

if($_GET['del_1'])
{
}

if($_GET['act_1'])
{
	$id = $_GET['act_1'];
	$query = 'UPDATE PERSON SET ACTIVE = NOT ACTIVE,CHANGED=NOW(),CHANGER='.$user->login['ID'].' WHERE ID='.$id;
 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
 	header('Location:person.php?firma='.$_REQUEST['firma']);
 	die;
}

if(isset($_POST['cmdImport']) AND is_array($_FILES['import']))
{
  $up = &$_FILES['import'];
  if($up['size']>0)
  {
    if (($handle = fopen($up, "r")) !== FALSE) 
    {
      $header = false;
      while (($data = fgetcsv($handle)) !== FALSE) 
      {
        if($header) // skip header
        {
          $query = 'INSERT IGNORE INTO PERSON(FIRMA,NAME,IBAN,EGN,ZAPLATA,WRITER,CREATED,CHANGER,CHANGED) 
            VALUES('.$_POST['firma'].',"'.mysql_real_escape_string(mb_convert_encoding($data[0],'CP1251','UTF-8')).'",
            "'.$data[1].'","'.$data[2].'",'.$data[3].','.$user->login['ID'].',NOW(),'.$user->login['ID'].',NOW())';
         	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
        }
        else $header = true;
      }
      fclose($handle);
    }    
  }
}

	if($b = @file_get_contents($tmpdir.'/temp/person.htm'))
	{
		$b = str_replace('{HEADER}',@file_get_contents($tmpdir.'/temp/header.htm'),$b);
		$b = str_replace('{FOOTER}',@file_get_contents($tmpdir.'/temp/footer.htm'),$b);
		if($err!='') $z = 'alert("'.$err.'");';
			else $z = '';
		$b = str_replace('<!--{ERROR}-->',$z,$b);
		$b = str_replace('{PREP}',WEBDIR,$b);
		MakeMenu($b);

		$query = 'SELECT P.ID,P.NAME,IBAN,ZAPLATA,P.ACTIVE,W.NAME AVTOR,CREATED,U.NAME UPDATER,CHANGED,EGN FROM PERSON P
			LEFT JOIN USER W ON WRITER=W.ID 
			LEFT JOIN USER U ON CHANGER=U.ID 
			WHERE FIRMA='.$_POST['firma'].' ORDER BY P.NAME';
	 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
	 	$z = '';
		while($row = mysql_fetch_array($result,MYSQL_ASSOC))
		{
			$z.= '<tr';
			if($row['ID']==$_REQUEST['edit_1']) $z.=' bgcolor="'.COL_EDIT.'"';
			elseif(!$row['ACTIVE']) $z.= ' bgcolor="'.COL_DEL.'"';
			$z.= '><td align="center">'.$row['ID'].'</td><td>&nbsp;'.$row['NAME'].'&nbsp;</td><td align="center">'.$row['IBAN'].'</td>
			  <td align="center">'.$row['EGN'].'</td><td align="right">'.number_format($row['ZAPLATA'],2,'.',chr(160)).'</td>
			  <td align="center"><a href="'.WEBDIR.'/person.php?act_1='.$row['ID'].'" onClick="javascript: blur();"><img src="'.WEBDIR.'/images/'.($row['ACTIVE'] ? 'v' : 'x').'_serif.gif" border="0" align="absmiddle" width="16" height="16" alt="Check"></a></td>
			  <td align="center">
			  <a href="'.WEBDIR.'/person.php?edit_1='.$row['ID'].'" onClick="javascript: blur();"><img src="'.WEBDIR.'/images/editicon.gif" border="0" align="absmiddle" width="12" height="12"></a>&nbsp;&nbsp;';
			//$z.= '<a href="'.WEBDIR.'/person.php?del_1='.$row['ID'].'" onClick="javascript: blur(); return FinalConfirm(\'Do you really want to delete this color ?\');"><img src="'.WEBDIR.'/images/stop.gif" border="0" align="absmiddle" width="16" height="16"></a>';
			$z.= '</td><td>'.($row['AVTOR']!='' ? $row['AVTOR'] : '&nbsp;').'</td><td nowrap>'.((int)$row['CREATED'] ? ADate($row['CREATED'],'-') : '&nbsp;').'</td>
			  <td>'.($row['UPDATER']!='' ? $row['UPDATER'] : '&nbsp;').'</td><td align="center" nowrap>'.((int)$row['CHANGED'] ? ADate($row['CHANGED'],'-') : '&nbsp;').'</td></tr>'.chr(13).chr(10);
		}
		$b = str_replace('<tr><td>{ITEM_1}</td></tr>',$z,$b);
		if($_REQUEST['edit_1'])
		{
			$query = 'SELECT * FROM PERSON WHERE ID='.$_REQUEST['edit_1'];
		 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
	   	if(mysql_num_rows($result)) $item = mysql_fetch_array($result,MYSQL_ASSOC);
			$b = str_replace('{EDIT_1}','ПРОМЕНИ',$b);
		}
		else $b = str_replace('{EDIT_1}','ДОБАВИ',$b);
		$b = str_replace('{EDITID1}',$_REQUEST['edit_1'],$b);
		$b = str_replace('{NOMER}',$item['ID'],$b);
		$b = str_replace('{NAME}',$item['NAME'],$b);
		$b = str_replace('{IBAN}',$item['IBAN'],$b);
		$b = str_replace('{EGN}',$item['EGN'],$b);
		$b = str_replace('{ZAPLATA}',$item['ZAPLATA'],$b);
		$b = str_replace('{ACT_0}',($item['ACTIVE']==0 AND $_REQUEST['edit_1']>0) ? 'selected' : '',$b);
		$b = str_replace('{ACT_1}',($item['ACTIVE']!=0 AND $_REQUEST['edit_1']>0) ? 'selected' : '',$b);
		$b = str_replace('<option value="0">{FIRMA}</option>',loadItems('FIRMA','FIRMA',$_REQUEST['firma'],'','','ID'),$b);

		echo $b;
	}
	else die('Could not find template - person.htm');
?>