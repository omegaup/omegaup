<?php
	define( "LEVEL_NEEDED", false );
	
	require_once( "/bootstrap.php" );

	require_once( "../../server/controllers/problems.controller.php" );
	if(isset( $_REQUEST['problem_id']))
		$problem_id  = mysql_real_escape_string(htmlentities($_REQUEST['problem_id']));
	if(isset( $_REQUEST['title']))
		$title = mysql_real_escape_string(htmlentities($_REQUEST['title']));
	if(isset($_REQUEST['public'] ))
		$public = mysql_real_escape_string(htmlentities($_REQUEST['public']));
	if(isset($_REQUEST['time_limit'] ))
		$time_limit = mysql_real_escape_string(htmlentities($_REQUEST['time_limit']));
	if(isset($_REQUEST['memory_limit'] ))
		$memory_limit = mysql_real_escape_string(htmlentities($_REQUEST['memory_limit']));
	if(isset( $_REQUEST['visits']))
		$visits = mysql_real_escape_string(htmlentities($_REQUEST['visits']));
	if(isset( $_REQUEST['submissions']))
		$submissions = mysql_real_escape_string(htmlentities($_REQUEST['submissions']));
	if(isset($_REQUEST['accepted'] ))
		$accepted = mysql_real_escape_string(htmlentities($_REQUEST['accepted']));
	if(isset($_REQUEST['difficulty'] ))
		$difficulty = mysql_real_escape_string(htmlentities($_REQUEST['difficulty']));
	if(isset( $_REQUEST['html'] ))
		$file_html = mysql_real_escape_string($_REQUEST['html']);
	if(isset($_REQUEST['in'] ))
		$file_in = mysql_real_escape_string(htmlentities($_REQUEST['in']));
	if(isset($_REQUEST['out'] ))
		$file_out = mysql_real_escape_string(htmlentities($_REQUEST['out']));
	if(isset($_REQUEST['action'] ))
		$action = mysql_real_escape_string(htmlentities($_REQUEST['action']));
	
	//si el id=-1, esperar hasta que
	
	switch($action){
	case 1:
	if($problem_id != -1){
		$query="Update problems
			 SET 
			`title` = ?,
			`public` = ?,
			`time_limit` = ?,
			`memory_limit` =  ?,
			`visits` = ?,
			`submissions` = ?,
			`accepted` = ?,
			`difficulty` = ?
			 WHERE `problem_id` = ?";
			
		$params = array(
						$title,
						$public,
						$time_limit,
						$memory_limit,
						$visits,
						$submissions,
						$accepted,
						$difficulty,
						$problem_id
					);
		try {$conn->Execute( $query, $params );	}
		catch(Exception $e){echo '[{"status":"Error","id":"none"}]'; return;}
		
		
		echo  '[{"status":"Ok","id":"'.$problem_id.'"}]'; 
	}else {
		$query="INSERT INTO problems (			 
			`title`,
			`public`,
			`time_limit`,
			`memory_limit`,
			`visits`,
			`submissions`,
			`accepted`,
			`difficulty`)
			VALUES (?,?,?,?,?,?,?,?)
			";
			
		$params = array(
						$title,
						$public,
						$time_limit,
						$memory_limit,
						$visits,
						$submissions,
						$accepted,
						$difficulty						
					);
		try {$conn->Execute( $query, $params );	}
		catch(Exception $e){echo '[{"status":"Error","id":"none"}]'; return;}		
		
		$query="SELECT problem_id FROM problems ORDER BY creation_date	 DESC LIMIT 1 ";
		
		try { $rs = $conn->getRow( $query);	}
		catch(Exception $e){echo '[{"status":"Error","id":"none"}]'; return;}	
		echo  '[{"status":"Ok","id":"'.$rs[0].'"}]'; 
	}
	break;
	//guardar html
	case 2:
		//html
		$file = "files/problems/".$problem_id.".html";
		if(($f = fopen($file,"w+"))){
			if(fwrite($f, $file_html)) {
				echo  '[{"status":"Ok","id":"'.$problem_id.'"}]'; 
				fclose($f); 
			}
		} 
	break;
	//guardar caso prueba in
	case 3:
		//in
		$file = "files/in/".$problem_id.".in";
		if(($f = fopen($file,"w+"))){
			if(fwrite($f, $file_in)) {
				echo  '[{"status":"Ok","id":"'.$problem_id.'"}]'; 
				fclose($f); 
			}
		} 
	break;
	//guardar caso de prueba out
	case 4:
		//out
		$file = "files/out/".$problem_id.".out";
		if(($f = fopen($file,"w+"))){
			if(fwrite($f, $file_out)) {			
				echo  '[{"status":"Ok","id":"'.$problem_id.'"}]'; 
				fclose($f); 
			}
		} 
	break;
}//switch
?>