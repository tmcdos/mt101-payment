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

$month = Array('','January','February','March','April','May','June','July','August','September','October','November','December');
if($_POST['mesec']!='')	list($god,$mes) = explode(',',$_POST['mesec']);

if(isset($_POST['cmdSave']) AND $god>0)
{
  if(is_array($_POST['suma'])) 
  {
    $query = 'REPLACE INTO SALARY(CHANGED,UPDATER,DATUM,PERSON,SUMA) VALUES(NOW(),'.$user->login['ID'].',"'.$god.'-'.$mes.'-01",';
    foreach($_POST['suma'] as $k=>$v)
    {
		 	$result = mysql_query($query.$k.','.$v.')') or trigger_error($query.$k.','.$v.')<br>'.mysql_error(),E_USER_ERROR);
    }
  }
}

// Mass payment MT101 for Raiffaisen
if(isset($_POST['cmdFile']) AND $god>0)
{
	$z = '';
  $query = 'SELECT NAME,IBAN,SUMA FROM SALARY LEFT JOIN PERSON ON PERSON=PERSON.ID WHERE FIRMA='.$_POST['firma'].' AND DATUM="'.$god.'-'.$mes.'-01" ORDER BY NAME';
 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
 	$i = 2;
	while($row = mysql_fetch_array($result,MYSQL_ASSOC))
	{
    $z.= '{1:F01RZBB9155XXXX0000000000}{2:I103RZBB9155XXXXN0000}{4:'.chr(13).chr(10);
    $z.= ':20:'.date('YmdHi').str_pad($i,4,'0',STR_PAD_LEFT).chr(13).chr(10);
    $z.= ':23B:CRED'.chr(13).chr(10);
    $z.= ':32A:'.date('ymd').'BGN'.number_format($row['SUMA'],2,',','').chr(13).chr(10);
    $z.= ':50K:/'.IBAN_RBB.chr(13).chr(10);
    $z.= MY_COMPANY.chr(13).chr(10);
    $z.= ':52D:RZBB9155'.chr(13).chr(10);
    $z.= '–¿…‘¿…«≈Õ¡¿Õ '.chr(13).chr(10);
    $z.= ':57D:RZBB9155'.chr(13).chr(10);
    $z.= '–¿…‘¿…«≈Õ¡¿Õ  ¿ƒ —Œ‘»ﬂ'.chr(13).chr(10);
    $z.= ':59:/'.$row['IBAN'].chr(13).chr(10);
    $z.= $row['NAME'].chr(13).chr(10);
    $z.= ':70:«¿œÀ¿“¿'.chr(13).chr(10);
    $z.= $god.'/'.str_pad($mes,2,'0',STR_PAD_LEFT).'/01'.chr(13).chr(10);
    $z.= '/NTYPE/000000000000'.chr(13).chr(10);
    $z.= '/OPNAT/J'.chr(13).chr(10);
    $z.= ':71A:SHA'.chr(13).chr(10);
    $z.= ':72:/DTYPE/PORD/OPER/BISER'.chr(13).chr(10);
    $z.= '/BAEREF/000000000000000000'.chr(13).chr(10);
    $z.= '/PROL/NORM/MASS/'.chr(13).chr(10);
    $z.= '-}'.chr(12);
	  $suma += $row['SUMA'];
	  $i++;
	}

  $h = '{1:F01RZBB9155XXXX0000000000}{2:I198RZBB9155XXXXN0000}{4:'.chr(13).chr(10);
  $h.= ':20:'.date('YmdHi').'0001'.chr(13).chr(10);
  $h.= ':12:151'.chr(13).chr(10);
  $h.= ':77E::B01:'.date('ymd').chr(13).chr(10);
  $h.= ':B1T:'.mysql_num_rows($result).'BGN'.number_format($suma,2,',','').chr(13).chr(10);
  $h.= '-}'.chr(12);

  $z = $h.$z;

  header('Content-Type: application/force-download');
  header('Content-Length: '.strlen($z));
	header('Content-Disposition: attachment; filename='.a_select('FIRMA',$_POST['firma']).'.bgi');
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	ob_end_flush();
  echo $z;
  die;
}

// Fixed size TXT mass payment for UBB
if(isset($_POST['cmdUBB']))
{
  // 1st row = Date (YYYYMMDD), your company IBAN (22 chars), suma (left, 21 chars, 2 decimals), number of rows (5 chars)
  // other rows = BIC (8 chars), IBAN (22 chars), SSN (10 chars), Name (35 chars), suma (left, 15 chars, 2 decimals), Reason (70 chars)
	$z = '';
  $query = 'SELECT LEFT(NAME,35) NAME,IBAN,SUMA,EGN FROM SALARY LEFT JOIN PERSON ON PERSON=PERSON.ID WHERE FIRMA='.$_POST['firma'].' AND DATUM="'.$god.'-'.$mes.'-01" ORDER BY NAME';
 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
	while($row = mysql_fetch_array($result,MYSQL_ASSOC))
	{
    $z.= substr($row['IBAN'],4,4).'BGSF'.$row['IBAN'].str_pad($row['EGN'],10,' ',STR_PAD_RIGHT).str_pad($row['NAME'],35,' ',STR_PAD_RIGHT);
    $z.= str_pad(number_format($row['SUMA'],2,'.',''),15,' ',STR_PAD_RIGHT).str_pad('SALARY FOR '.strtoupper($month[$mes]).' '.$god,70,' ',STR_PAD_RIGHT).chr(13).chr(10);
	  $suma += $row['SUMA'];
	}

  $h = date('Ymd').IBAN_UBB.str_pad(number_format($suma,2,'.',''),21,' ',STR_PAD_RIGHT).str_pad(mysql_num_rows($result),5,' ',STR_PAD_RIGHT).chr(13).chr(10);

  $z = $h.$z;

  header('Content-Type: application/force-download');
  header('Content-Length: '.strlen($z));
	header('Content-Disposition: attachment; filename=MPLSSAL.TXT');
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	ob_end_flush();
  echo $z;
  die;
}

// Mass payment MT101 for UniCredit
if(isset($_POST['cmdUni']))
{
	$z = '';
  $query = 'SELECT LEFT(NAME,35) NAME,IBAN,SUMA,EGN FROM SALARY LEFT JOIN PERSON ON PERSON=PERSON.ID WHERE FIRMA='.$_POST['firma'].' AND DATUM="'.$god.'-'.$mes.'-01" ORDER BY NAME';
 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
 	$i = 2;
	while($row = mysql_fetch_array($result,MYSQL_ASSOC))
	{
    $z.= '{1:F01UNCR7000XXXX0000000000}{2:I103UNCR7000XXXXN0000}{4:'.chr(13).chr(10);
    $z.= ':20:'.date('YmdHi').str_pad($i,4,'0',STR_PAD_LEFT).chr(13).chr(10);
    $z.= ':23B:CRED'.chr(13).chr(10);
    $z.= ':32A:'.date('ymd').'BGN'.number_format($row['SUMA'],2,',','').chr(13).chr(10);
    $z.= ':50K:/'.IBAN_UNC.chr(13).chr(10);
    $z.= MY_COMPANY.chr(13).chr(10);
    $z.= ':52D:UNCR7000'.chr(13).chr(10);
    $z.= '¡”À¡¿Õ '.chr(13).chr(10);
    $z.= ':57D:UNCR7000'.chr(13).chr(10);
    $z.= '¡”À¡¿Õ  ¿ƒ —Œ‘»ﬂ'.chr(13).chr(10);
    $z.= ':59:/'.$row['IBAN'].chr(13).chr(10);
    $z.= $row['NAME'].chr(13).chr(10);
    $z.= ':70:«¿œÀ¿“¿'.chr(13).chr(10);
    $z.= $god.'/'.str_pad($mes,2,'0',STR_PAD_LEFT).'/01'.chr(13).chr(10);
    $z.= '/NTYPE/000000000000'.chr(13).chr(10);
    $z.= '/OPNAT/J'.chr(13).chr(10);
    $z.= ':71A:SHA'.chr(13).chr(10);
    $z.= ':72:/DTYPE/PORD/OPER/BISER'.chr(13).chr(10);
    $z.= '/BAEREF/000000000000000000'.chr(13).chr(10);
    $z.= '/PROL/NORM/MASS/'.chr(13).chr(10);
    $z.= '-}'.chr(12);
	  $suma += $row['SUMA'];
	  $i++;
	}

  $h = '{1:F01UNCR7000XXXX0000000000}{2:I198UNCR7000XXXXN0000}{4:'.chr(13).chr(10);
  $h.= ':20:'.date('YmdHi').'0001'.chr(13).chr(10);
  $h.= ':12:151'.chr(13).chr(10);
  $h.= ':77E::B01:'.date('ymd').chr(13).chr(10);
  $h.= ':B1T:'.mysql_num_rows($result).'BGN'.number_format($suma,2,',','').chr(13).chr(10);
  $h.= '-}'.chr(12);

  $z = $h.$z;

  header('Content-Type: application/force-download');
  header('Content-Length: '.strlen($z));
	header('Content-Disposition: attachment; filename='.a_select('FIRMA',$_POST['firma']).'.bgi');
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	ob_end_flush();
  echo $z;
  die;
}

	if($b = @file_get_contents($tmpdir.'/temp/salary.htm'))
	{
		$b = str_replace('{HEADER}',@file_get_contents($tmpdir.'/temp/header.htm'),$b);
		$b = str_replace('{FOOTER}',@file_get_contents($tmpdir.'/temp/footer.htm'),$b);
		if($err!='') $z = 'alert("'.$err.'");';
			else $z = '';
		$b = str_replace('<!--{ERROR}-->',$z,$b);
		$b = str_replace('{PREP}',WEBDIR,$b);
		MakeMenu($b);
		
		// show month list
		$y_b = 2011;
		$m_b = 8;
		$today = date('Y')*12+date('m');
		$z = '';
		while($y_b*12+$m_b <= $today)
		{
		  $z = '<option value="'.$y_b.','.$m_b.'" '.(($y_b==$god AND $m_b==$mes) ? 'selected' : '').'>'.$month[$m_b].' '.$y_b.'</option>'.$z;
		  $m_b++;
		  if($m_b>12)
		  {
		    $m_b = 1;
		    $y_b++;
		  }
		}
		$b = str_replace('{MESEC}',$z,$b);
		
	 	$z = '';
	 	$cnt = false;
	 	if($_POST['mesec']!=0)
	 	{
  		$query = 'SELECT S.ID,P.ID PID,P.NAME,COALESCE(SUMA,P.ZAPLATA) SUMA,W.NAME AVTOR,DATE_FORMAT(S.CHANGED,"%d-%m-%Y") CHANGED,SUMA MONEY,
  		  YEAR(DATUM) GOD,MONTH(DATUM) MES
  		  FROM PERSON P
  			LEFT JOIN SALARY S ON PERSON=P.ID AND DATUM BETWEEN "'.$god.'-'.$mes.'-01" AND "'.$god.'-'.$mes.'-31" 
  			LEFT JOIN USER W ON S.UPDATER=W.ID
  			WHERE P.ACTIVE AND FIRMA='.$_POST['firma'].'
  			ORDER BY DATUM,P.NAME';
  	 	$result = mysql_query($query,$conn) or trigger_error($query.'<br>'.mysql_error($conn),E_USER_ERROR);
  	 	$i = 1;
  		while($row = mysql_fetch_array($result,MYSQL_ASSOC))
  		{
  			$z.= '<tr><td align="right">'.$i++.'</td><td>'.$row['NAME'].'</td><td align="right">';
  			if($row['GOD']*12+$row['MES']+1 <= $today) $z.= '<input type="text" class="edit" name="suma['.$row['PID'].']" size="6" maxlength="6" value="'.round($row['SUMA'],2).'">';
  			  else $z.= number_format($row['SUMA'],2,'.',chr(160));
  			$z.= '</td><td align="center">'.($row['CHANGED']!='' ? $row['CHANGED'] : '&nbsp;').'</td>
  			  <td>'.($row['AVTOR']!='' ? $row['AVTOR'] : '&nbsp;').'</td>
          </tr>'.chr(13).chr(10);
    	 	if($row['MONEY']!='') $cnt = true;
    	 	$total += round($row['SUMA'],2);
  		}
  	}
		$b = str_replace('{TOTAL}',number_format($total,2,'.',chr(160)),$b);
		$b = str_replace('<tr><td>{ITEM_1}</td></tr>',$z,$b);
		$b = str_replace('{EN_FILE}',$cnt ? '' : 'disabled',$b);
		$b = str_replace('<option value="0">{FIRMA}</option>',loadItems('FIRMA','FIRMA',$_REQUEST['firma'],'','','ID'),$b);

		echo $b;
	}
	else die('Could not find template - salary.htm');
?>