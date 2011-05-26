<?php

include_once("../config.php");
include_once("../includes/db_con.php");


if(isset($_REQUEST['execID'])){
	$execID = addslashes($_REQUEST['execID']);	
}else{
	
	$error["success"] = false;
	$error["reason"] = "No excID";
		
	echo json_encode( $error );
	return;
}

$consulta = "SELECT * FROM `Ejecucion` WHERE execID = '{$execID}' LIMIT 1";
$res = mysql_query($consulta);
$row = mysql_fetch_assoc($res);
echo json_encode($row);
