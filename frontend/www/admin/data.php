<?php
	define( "LEVEL_NEEDED", false );
	
	require_once( "/bootstrap.php" );

	require_once( "../../server/controllers/problems.controller.php" );
	//Regresa un array con los datos del problema que coincida con el id ($q) que se paso como parametro
	
	
	$q = $_REQUEST['q'];
	$query="SELECT problem_id,`title`,`public`,time_limit,memory_limit,visits,submissions,accepted,difficulty FROM problems WHERE problem_id = ?";
	
	$rs = $conn->getRow($query,$q);		
	$json = array();
	array_push($json,$rs);
	echo json_encode($json);
?>