<?php


class ProblemsController {
	
	public static function getProblemList( $servidor = null ){
		
	}
	
	public static function getJudgesList(){
		return array(  'uva' => "Universidad Valladolid",
						'livearchive' => "Live Archive",
						'pku' => "Pekin University",
						'tju' => "Tianjing ",
						'spoj' => "SPOJ" );
	}
	
}



?>