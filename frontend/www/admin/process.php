<?php

	define( "LEVEL_NEEDED", false );
	
	require_once( "/bootstrap.php" );

	require_once( "../../server/controllers/problems.controller.php" );
	
	$action = $_REQUEST['action'];	
	switch($action){
		//Para guardar el archivo .html del problema
		case 1:
		
			if(isset($_FILES['html'])){
				$problem_id	= $_REQUEST['problem_id'];
				$nombre = $_FILES['html']['name'];
				$temp   = $_FILES['html']['tmp_name'];
				$dir = "files/problems";

				//validar el tipo de archivo, debe ser hmtl para la definicion de los problemas
				$split = explode(".",$nombre);
				if(!($split[sizeof($split)-1] == "html" || $split[sizeof($split)-1] == "htm")){
					echo '[{"status":"Archivo no valido, '.$split[sizeof($split)-1].'","id":"'.$problem_id.'"}]';
					return;
				}
				// subir archivo al servidor
				if(!move_uploaded_file($temp, $dir."/".$problem_id.".html"))
				{
					echo '[{"status":"Error al intentar subir el archivo","id":"'.$problem_id.'"}]';
					return;
				}
					echo '[{"status":"Ok","id":"'.$problem_id.'"}]';
					return;
			} else{
				echo '[{"status":"Error","id":"'.$problem_id.'"}]';
				return;
			}
		break;
		// guardar caso de prueba "in"
		case 2;
			if(isset($_FILES['in'])){
				$problem_id	= $_REQUEST['problem_id'];
				$nombre = $_FILES['in']['name'];
				$temp   = $_FILES['in']['tmp_name'];
				$dir = "files/in";

				//validar el tipo de archivo, debe ser hmtl para la definicion de los problemas
				$split = explode(".",$nombre);
				if(!($split[sizeof($split)-1] == "in" )){
					echo '[{"status":"Archivo no valido, '.$split[sizeof($split)-1].'","id":"'.$problem_id.'"}]';
					return;
				}
				// subir archivo al servidor
				if(!move_uploaded_file($temp, $dir."/".$problem_id.".in"))
				{
					echo '[{"status":"Error al intentar subir el archivo","id":"'.$problem_id.'"}]';
					return;
				}
					echo '[{"status":"Ok","id":"'.$problem_id.'"}]';
					return;
			} else{
				echo '[{"status":"Error","id":"'.$problem_id.'"}]';
				return;
			}
		break;
		//guardar caso de prueba "out"
		case 3:
			if(isset($_FILES['out'])){
				$problem_id	= $_REQUEST['problem_id'];
				$nombre = $_FILES['out']['name'];
				$temp   = $_FILES['out']['tmp_name'];
				$dir = "files/out";

				//validar el tipo de archivo, debe ser hmtl para la definicion de los problemas
				$split = explode(".",$nombre);
				if(!($split[sizeof($split)-1] == "out" )){
					echo '[{"status":"Archivo no valido, '.$split[sizeof($split)-1].'","id":"'.$problem_id.'"}]';
					return;
				}
				// subir archivo al servidor
				if(!move_uploaded_file($temp, $dir."/".$problem_id.".out"))
				{
					echo '[{"status":"Error al intentar subir el archivo","id":"'.$problem_id.'"}]';
					return;
				}
					echo '[{"status":"Ok","id":"'.$problem_id.'"}]';
					return;
			} else{
				echo '[{"status":"Error","id":"'.$problem_id.'"}]';
				return;
			}
		break;
		//guardar una imagen
		case 4:
			if(isset($_FILES['img'])){
				$problem_id	= $_REQUEST['problem_id'];
				$nombre = $_FILES['img']['name'];
				$temp   = $_FILES['img']['tmp_name'];
				$dir = "files/problems/".$problem_id."/";

				//validar el tipo de archivo, debe ser hmtl para la definicion de los problemas
				$split = explode(".",$nombre);
				$ext = $split[sizeof($split)-1];
				if(!($ext == "png" || $ext == "jpg" || $ext == "jpeg" || $ext == "gif")){
					echo '[{"status":"Archivo no valido, '.$split[sizeof($split)-1].'","id":"'.$problem_id.'"}]';
					return;
				}
				//crear la carpeta de imagenes si no existe para este problema
				
				//$nombre_archivo=carpeta_nueva;

				if (!file_exists($dir)) {
					mkdir($dir,0777);
				}				
				// subir archivo al servidor
				if(!move_uploaded_file($temp, $dir."/".$nombre))
				{
					echo '[{"status":"Error al intentar subir el archivo","id":"'.$problem_id.'"}]';
					return;
				}
					echo '[{"status":"Ok","id":"'.$problem_id.'"}]';
					return;
			} else{
				echo '[{"status":"Error","id":"'.$problem_id.'"}]';
				return;
			}
		break;
		//borrar una imagen
		case 5:
			$problem_id	= $_REQUEST['problem_id'];
			$nombre = $_REQUEST['file'];
			$dir = "./files/problems/$problem_id/$nombre";
			if (file_exists($dir)) {
					if(unlink($dir)) 
					echo '[{"status":"Ok","id":"'.$problem_id.'"}]';
			}	
		break;
		//borrar todo un problema, esto incluye registros, casos de prueba,.html e imagenes
		case 6:
			$problem_id	= $_REQUEST['problem_id'];
			$query="DELETE FROM `problems` WHERE `problem_id` = ?";
			$dir_html = "./files/problems/$problem_id.html";
			$dir_img = "./files/problems/$problem_id/";
			$dir_in = "./files/in/$problem_id.in";
			$dir_out = "./files/out/$problem_id.out";
			//borrar html
			if (file_exists($dir_html)) {
					unlink($dir_html);
			}
			// borrar in
			if (file_exists($dir_in)) {
					unlink($dir_in);
			}
			// borrar out
			if (file_exists($dir_out)) {
					unlink($dir_out);		
			}
			//borrar imagenes
			if (file_exists($dir_img)) {
				delDir($dir_img);
			}
			$params = array($problem_id);
			try {$conn->Execute( $query , $params);	echo '[{"status":"Ok","id":"'.$problem_id.'"}]';}
			catch(Exception $e){echo '[{"status":"Error","id":"none"}]'; return;}		
		break;
	}
	
	
function delDir($dir){ 
		$current_dir = opendir($dir); 
		while($entryname = readdir($current_dir)){ 
			if(is_dir("$dir/$entryname") and ($entryname != "." and $entryname!="..")){ 
				deldir("${dir}/${entryname}");   
			}else if($entryname != "." and $entryname!=".."){ 
				unlink("${dir}/${entryname}"); 
			} 
		} 
		closedir($current_dir); 
		rmdir(${'dir'}); 
	}

?>