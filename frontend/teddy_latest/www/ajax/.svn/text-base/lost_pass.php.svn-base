<?php


session_start();
include_once("../config.php");
include_once("../includes/db_con.php");

$usuario = addslashes( $_REQUEST['user'] );

$resultado = mysql_query ( "SELECT `userID` FROM `Usuario` where userID = '". $usuario ."' or mail = '". $usuario ."';" ) or die('Algo anda mal: ' . mysql_error());
if(mysql_num_rows($resultado) != 1){
	echo "{ \"success\" : false, \"reason\" : \"Este usuario no existe.\" }";
	return;
}

$row = mysql_fetch_array($resultado);
$usuario = $row[0];

$foo = 55; $bar = "";
while($foo-- > 0){
	$bar .= rand( 5, 123 );
}

$token = md5($bar);

mysql_query ( "INSERT INTO LostPassword (`userID` , `IP` , `Token` ) VALUES ('{$usuario}', '" . $_SERVER['REMOTE_ADDR']. "', '{$token}'); " ) or die('Algo anda mal: ' . mysql_error());

echo "{ \"success\" : true }";


