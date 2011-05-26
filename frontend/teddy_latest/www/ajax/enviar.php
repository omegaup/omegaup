<?php
session_start();

if(!(isset($_REQUEST['probID']) && isset($_REQUEST['filename'])))
	return;

//utility function
function endsWith( $str, $sub ) {
	return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
}

include_once("../config.php");
include_once("../includes/db_con.php");


	
$usuario = $_SESSION["userID"];
$probID  = stripslashes($_REQUEST["probID"]);
$fname   = stripslashes($_REQUEST["filename"]);



//buscar el id de este problea y que sea publico
//revisar si existe este problema
$consulta = "select probID , titulo from Problema where BINARY ( probID = '{$probID}' AND publico = 'SI') ";
$resultado = mysql_query( $consulta ) or die('ERROR');


//insertar un nuevo run y obtener el id insertado
//como estado, hay que ponerle uploading
	if(mysql_num_rows($resultado) != 1) {
		echo "UNKNOWN_PROBID";
		return;
	}

	if( endsWith($fname, ".java") ) {$TIPO = "JAVA"; $ext = ".java";}
	if( endsWith($fname, ".c") ) {$TIPO = "C"; $ext = ".c";}
	if( endsWith($fname, ".cpp") ) {$TIPO = "C++"; $ext = ".cpp";}
	if( endsWith($fname, ".py") ) {$TIPO = "Pyton"; $ext = ".py";}
	if( endsWith($fname, ".cs") ) {$TIPO = "C#"; $ext = ".cs";}
	if( endsWith($fname, ".pl") ) {$TIPO = "Perl"; $ext = ".pl";}

	//insertar userID, probID, remoteIP
	mysql_query ( "INSERT INTO Ejecucion (`userID` , `probID` , `remoteIP`, `LANG`  ) VALUES ('{$usuario}', {$probID}, '" . $_SERVER['REMOTE_ADDR']. "', '{$TIPO}'); " ) or die('Algo anda mal: ' . mysql_error());

	//get latest ID
	$resultado = mysql_query ( "SELECT `execID` FROM `Ejecucion` order by `fecha` desc limit 1;" ) or die('Algo anda mal: ' . mysql_error());
	$row = mysql_fetch_array ( $resultado );

	
	//regresar ese execID en un json
	echo $row["execID"] . $ext;
	
?>
