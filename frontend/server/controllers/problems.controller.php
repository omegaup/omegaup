<?php

require_once( "dao/Problems.dao.php" );	

class ProblemsController {

	/**
	  * Returns all the problems from the judge which are active,
	  * 
	  *	
	  **/
	public static function getProblemList( $servidor = null ){
		$prob = new Problems();
		$prob->setPublic(1);
		
		$results = ProblemsDAO::search($prob);
		
		return $results;
	}
	
	public static function getJudgesList(){
		return array(  'uva' => "Universidad Valladolid",
						'livearchive' => "Live Archive",
						'pku' => "Pekin University",
						'tju' => "Tianjing ",
						'spoj' => "SPOJ" );
	}


	
}



