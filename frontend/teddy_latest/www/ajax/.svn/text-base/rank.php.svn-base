<?php

include_once("../config.php");
include_once("../includes/db_con.php");

	date_default_timezone_set('America/Mexico_City');

if(isset($_REQUEST['cid'])){
	$cid = addslashes($_REQUEST['cid']);
}else{
	$cid = "-1";
}


$resultado = mysql_query( "select Inicio from Concurso where ( CID = {$cid}  ) ;" );
$row = mysql_fetch_array($resultado);
$inicio = $row['Inicio'];

$resultado = mysql_query( "select * from Ejecucion where ( Concurso = {$cid}  ) ;" );
while($row = mysql_fetch_array($resultado)){
	
	//setear el userID
	//	userID->userID 
	$data[ $row[ 'userID' ] ][ 'userID' ] = $row['userID'];
	
	//setear penalty en cero
	//	userID->PENALTY = 0
	if(!isset($data[ $row[ 'userID' ] ]["PENALTY"])){
		$data[ $row[ 'userID' ] ]["PENALTY"] = 0;
	}
	
	
	//setear penalty en cero
	//	userID->PENALTY = 0
	if(!isset($data[ $row[ 'userID' ] ]["ENVIOS"])){
		$data[ $row[ 'userID' ] ]["ENVIOS"] = 0;
	}
	
	
	//setear penalty en cero
	//	userID->PENALTY = 0
	if(!isset($data[ $row[ 'userID' ] ]["RANK"])){
		$data[ $row[ 'userID' ] ]["RANK"] = 0;
	}
	
	//setear ok's en cero
	//	userID->OK = 0
	if(!isset($data[ $row[ 'userID' ] ]["OK"])){
		$data[ $row[ 'userID' ] ]["OK"] = 0;
	}

	//set this problem 
	// userID->problemas->probID = 0
	if(!isset($data[ $row[ 'userID' ] ][ "problemas" ][ $row['probID'] ])){
		$data[ $row[ 'userID' ] ][ "problemas" ][ $row['probID'] ]["probID"] = $row['probID'];
		$data[ $row[ 'userID' ] ][ "problemas" ][ $row['probID'] ]["bad"] = 0;
		$data[ $row[ 'userID' ] ][ "problemas" ][ $row['probID'] ]["ok"] = 0;
		$data[ $row[ 'userID' ] ][ "problemas" ][ $row['probID'] ]["ok_time"] = 0;
	}
	
	$data[ $row[ 'userID' ] ]["ENVIOS"]++;
	
	if($row["status"] == "OK") {
		
		//si resolvio el mismo problema, solo agregar uno al ok total
		if($data[ $row[ 'userID' ] ][ "problemas" ][ $row['probID'] ]["ok"] == 0 ) $data[ $row[ 'userID' ] ]["OK"]++;
		
		$data[ $row[ 'userID' ] ][ "problemas" ][ $row['probID'] ]["ok"]++;
		$data[ $row[ 'userID' ] ][ "problemas" ][ $row['probID'] ]["ok_time"] = intval( (strtotime($row['fecha'])-strtotime($inicio))/60 );

	}else{
		$data[ $row[ 'userID' ] ][ "problemas" ][ $row['probID'] ]["bad"]++;
	}

}


//calcular penalty
foreach( $data as $userID => $userArr)
{
	
	foreach( $userArr['problemas'] as $probID => $probArr)
	{
		//estoy en cada problema de cada usuario
		if( $probArr['ok'] == 0 ){
			continue;
		}
		
		$data[ $userID ]['PENALTY'] += ((int)$probArr['bad']) * 20 ;
		$data[ $userID ]['PENALTY'] += ((int)$probArr['ok_time'])  ;
	}
}






// Comparison function
function cmp($a, $b) {
	
	if($a['OK'] == $b['OK']){
		
		if ($a['PENALTY'] == $b['PENALTY']){
			
			if ($a['ENVIOS'] == $b['ENVIOS']){

				return 0;
			}
			
	    	return ($a['ENVIOS'] < $b['ENVIOS']) ? -1 : 1;			
		} 
	        

	    return ($a['PENALTY'] < $b['PENALTY']) ? -1 : 1;
	}

    return ($a['OK'] > $b['OK']) ? -1 : 1;	
	
}


// SORTING
uasort($data, 'cmp');

//agregando el rank
$R = 1;
foreach( $data as $row => $k){
	
	if(isset($old)){
		if(($data[$old]["OK"] == $data[$row]["OK"]) && ($data[$old]["PENALTY"] == $data[$row]["PENALTY"])){
			$data[$row]['RANK'] = $R;
		}else{
			$R++;
			$data[$row]['RANK'] = $R;			
		}
	}else{
		$data[$row]['RANK'] = $R;		
	}

	$old = $row;
}


$json = array();

foreach( $data as $row ){
	array_push($json, $row);
}

echo json_encode($json);

