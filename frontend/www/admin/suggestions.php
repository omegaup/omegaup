<?php
	define( "LEVEL_NEEDED", false );
	
	require_once( "/bootstrap.php" );

	require_once( "../../server/controllers/problems.controller.php" );
	
	$q = $_REQUEST['q'];	
	//echo $q;
	$query="SELECT `problem_id`,`title`,`public`,`time_limit`,`memory_limit`
					,`visits`,`submissions`,`accepted`,`difficulty` FROM `problems` WHERE `problem_id` LIKE $q LIMIT 6";
	$rs = $conn->Execute($query);	
	
	
	//$html = "Por id<hr/>";
	$json = array();
	foreach($rs as $row){
		//$html .= "(".$row[0].")".$row[1]."</br>";
		array_push($json,$row);
	}
	
	$q = $_REQUEST['q'];
	$query="SELECT `problem_id`,`title`,`public`,`time_limit`,`memory_limit`
					,`visits`,`submissions`,`accepted`,`difficulty` FROM `problems` WHERE `title` LIKE $q LIMIT 7";
	$rs = $conn->Execute($query);	
	
	//$html .= "<br/>Por title<hr/>";
	foreach($rs as $row){
		//$html .= $row[1]."</br>";
		array_push($json,$row);
	}
	echo json_encode($json);
	//echo '{"a":["1","8"],"b":"2","c":"3"}';
?>