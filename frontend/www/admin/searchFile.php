<?php
	$problem_id = $_REQUEST['problem_id'];
	$file_option = $_REQUEST['option'];
	//ver si existe definicion del problema
	switch($file_option){
		// regresa el contenido del archivo html en caso de existir para el problem_id
		case 1:
		$file = "files/problems/".$problem_id.".html";		
		if (file_exists($file)){ 
			 $fileop = file_get_contents($file);
			 echo $fileop;
		
		} else {
		
			echo 'Archivo no disponible.';
			return;
		}
		break;
		case 2:
		$file = "files/in/".$problem_id.".in";		
		if (file_exists($file)){ 
			 $fileop = file_get_contents($file);
			 echo $fileop;
		
		} else {
		
			echo 'Archivo no disponible.';
			return;
		}
		break;
		case 3:
		$file = "files/out/".$problem_id.".out";		
		if (file_exists($file)){ 
			 $fileop = file_get_contents($file);
			 echo $fileop;
		
		} else {
		
			echo 'Archivo no disponible.';
			return;
		}
		break;
		case 4:
			if($problem_id==-1)return;
			
			$path="files/problems/".$problem_id."/";
			if(!file_exists($path)){
				mkdir($path,0777);
			}
			$directorio=dir($path);
			$i=0;
			while ($archivo = $directorio->read()){
				$i++;
				if($i<3)continue;
				echo "<div class='title'><a onclick='delImg(\"".$archivo."\")'>X</a></div>";
				echo "<img src='$path/".$archivo."' class='min'/> <br/> &lt;img src=\"./$problem_id/$archivo\" /&gt;<hr/>";
			}
			$directorio->close();
		break;
	}
?>