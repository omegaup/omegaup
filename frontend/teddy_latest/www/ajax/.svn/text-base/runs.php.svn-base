<?php

include_once("../config.php");
include_once("../includes/db_con.php");


if(isset($_REQUEST['cid'])){
	$cid = addslashes($_REQUEST['cid']);	
}else{
	$cid = "-1";
}



$consulta = "SELECT `execID`, `userID`, `probID`, `status`, `tiempo`, `fecha`, `LANG` FROM `Ejecucion` WHERE Concurso = '{$cid}' order by fecha desc LIMIT 100";

$res = mysql_query($consulta);

$json = array();

while($row = mysql_fetch_assoc($res)){
	array_push($json, $row);
}

echo json_encode($json);

?>
