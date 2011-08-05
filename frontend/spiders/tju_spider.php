<?php
		// database connect
		function connect($server,$user,$pass,$db){
			mysql_connect($server,$user,$pass)or die (mysql_error());
			return mysql_select_db($db)or die (mysql_error());
		}
		function getHtmlCode($url){
			
			$ch = curl_init(); 			
			curl_setopt($ch, CURLOPT_POST, 1);
			//curl_setopt($ch, CURLOPT_POSTFIELDS,"username=".$user."&password=".$pass);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_exec ($ch); // connect
			//GEt html code from $url
			$html_code = curl_exec($ch); // connect
			return $html_code;
		}
		
		//returns an array with all data problem
		function getProblemData( $id_problem , $title){
			$url = "http://acm.tju.edu.cn/toj/showp$id_problem.html";
			$html_code = getHtmlCode($url);			
			$time_limit = "";
			$memory_limit = "";
			$server = "tju";
			$creation_date = time();
			
			//data example: Time Limit:</font> 0.5
			$pattern = '(Time Limit:</font>.[0-9\.]*)';
			preg_match_all($pattern, $html_code , $matches, PREG_OFFSET_CAPTURE);
			$pattern_replace_1 = '(Time Limit:</font>.)';
			$time_limit = preg_replace($pattern_replace_1,"",$matches[0][0][0],1);
			
			
			//data example: Memory Limit: </font>65536
			$pattern = '(Memory Limit: </font>.[0-9\.]*)';
			preg_match_all($pattern, $html_code , $matches, PREG_OFFSET_CAPTURE);
			$pattern_replace_1 = '(Memory Limit: </font>.)';
			$memory_limit = preg_replace($pattern_replace_1,"",$matches[0][0][0],1);
			
			return array($id_problem,$title,$time_limit,$memory_limit);
		}
		
		//Main
		
		//database connect 
		connect("localhost","root","","omegaup");
		
		//The number_list is represents a "volume" of data set problem
		
		$number_list = 1;
		
		$url= "http://acm.tju.edu.cn/toj/list$number_list.html";
		$html_code = getHtmlCode($url);
		/* Data line example '0,0,1001,"Hello, world!",69374,10399,21111,"49.26",0,1,0'
							
   							 'x,x,remote_id,"TITLE",x,x,x,x,x,x,x'
		   
		   the 3rd number is the remote_id and after we gonna use it to get all the problem data.
		   
		*/
		
		$pattern = '([0-9],[0-9],[0-9]*,\".*\",[0-9]*,[0-9]*,[0-9]*,\"[0-9\.]*\",[0-9],[0-9],[0-9])';
		$pattern_replace_1 = '([0-9],[0-9],)';
		$pattern_replace_2 = '(,[0-9]*,[0-9]*,[0-9]*,\"[0-9\.]*\",[0-9],[0-9],[0-9])';
		preg_match_all($pattern, $html_code , $matches, PREG_OFFSET_CAPTURE);		
		$size = sizeof($matches[0]);				
		return;
		$remote_id = "";
		$title = "";		
		
		for( $i = 0 ; $i < $size ;  $i++ ){
			$matches[0][$i][0] = preg_replace($pattern_replace_1,"",$matches[0][$i][0],1);
			$matches[0][$i][0] = preg_replace($pattern_replace_2,"",$matches[0][$i][0]);
			$matches[0][$i][0] = preg_replace("(,)","<special_tag>",$matches[0][$i][0],1);
			$split = explode("<special_tag>",$matches[0][$i][0]);
			$title =  preg_replace("(\")","",$split[1]);
			$remote_id = $split[0];	
			$data = getProblemData( $remote_id , $title );
			echo $data[0];
			$q = "INSERT INTO `problems`(`remote_id`,`title`,`time_limit`,`memory_limit`) VALUES(".$data[0].",'".$data[1]."',".$data[2].",".$data[3].")"; 
			mysql_query($q) or die(mysql_error());
		}		
		
		
?>