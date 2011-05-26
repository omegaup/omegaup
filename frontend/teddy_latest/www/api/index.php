<?php


	

	if(!isset($_REQUEST['metodo'])){
		$arr = array ( 'status' => 'error', 'causa'=>'No hay metodo definido.');
		echo json_encode($arr);
		return;
	}
		
	/*
		API
	*/
	
	if($_REQUEST['metodo'] == "showapi") echo '
		<div class="metodo">
			<b>showapi</b> Muestra el API.
		</div>
	';	
	
	
	/*
		MANEJO DE USUARIOS
	*/
	
	
	/*
		getUserListPage
	*/
	if($_REQUEST['metodo'] == "showapi") echo '
		<div class="metodo">
			<b>getUserListPage</b> Regresa los primeros 10 usuarios, para paginas subsecuentes, agregue <i>pagina</i> = n, donde n es el numero de pagina.
		</div>
	';
	function getUserListPage(){
		
		$page = isset($_REQUEST['pagina']) ? $_REQUEST['pagina'] : 0;
		$page *= 10;
		//$page ++;
		
		$q = "select  `userID` ,  `nombre` ,  `solved` ,  `tried` ,  `ubicacion` ,  `escuela` ,  `cuenta`  FROM  `Usuario` LIMIT " . $page ." , 10;";
		echo $q;
		$result = mysql_query( $q );
		
		$final = array();
		
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			//echo json_encode( $row ) . "\n";
			//$final.push(  );
			Array_push( $final, $row );
		}
		
		echo json_encode( $final ) ;
		
		mysql_free_result($result);
	}
	
	
	
	
	/*
		getUserData
	*/
	if($_REQUEST['metodo'] == "showapi") echo '
		<div class="metodo">
			<b>getUserData</b> Regresa los datos del usuario, donde el usuario es especificado por <i>username</i>.
		</div>
	';
	function getUserData(){
		
		if(isset($_R))
		
		$q = "select  `userID` ,  `nombre` ,  `solved` ,  `tried` ,  `ubicacion` ,  `escuela` ,  `cuenta`  FROM  `Usuario` LIMIT " . $page ." , 10;";
		echo $q;
		$result = mysql_query( $q );
		
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			echo json_encode( $row ) . "\n";
		}
		
		mysql_free_result($result);
	}
	
	
	
	
	//conectarse a la base de datos
	include("../includes/db_con.php");
	
	//main switch
	switch($_REQUEST['metodo']){
		case "": break;
		case "getUserListPage": getUserListPage(); break;
		case "showapi": break;
		default : echo "Parametro metodo mal formado.";
	}		
	
?>