<?php

require_once( "dao/Problems.dao.php" );	

class ProblemsController {

	/**
	  * Returns all the problems from the judge which are active,
	  * 
	  *	
	  **/
	public static function getProblemList( $sizePage , $noPage , $servidor = null ,$orderBy){
		
		$condition = "server = '$servidor' and public = 1";
		$results = ProblemsDAO::byPage ( $sizePage , $noPage , $condition , $servidor, $orderBy);		
		return $results;
	}
	
	public static function getJudgesList(){
		return array(  'uva' => "Universidad Valladolid |",
						'livearchive' => "Live Archive |",
						'pku' => "Pekin University |",
						'tju' => "<a href='?serv=tju'> Tianjing </a> |",
						'spoj' => "SPOJ" );
	}

  /**
    * Adds a problem from a remote server to the list of known
    * problems.
    *
    * @return bool|string True if problem was added successfully.
    *                     Error message, otherwise.
    * @todo Add JSON responses
    */
  public static function addRemoteProblem(
      $judge
    , $remote_id
    , $public = true
  ) {
  
		try {
      $prob = new Problems();
      // Validating that $judge is in fact a valid judge happens
      // in setServidor
      $prob->setServidor($judge);
      $prob->setIdRemoto($remote_id);
      $prob->setPublico($public);

			ProblemsDAO::save($prob);
		} catch(Exception $e) {
			return $e->getMessage();
		}
    
    // If we make it this far, the problem was added successfully
    return true;
  }
  
  /**
    * Adds one or more tags to a problem.
    * This function should allow tagging multiple problems with multiple tags
    * in a single function call. Arguments may be single values or arrays.
    *
    * @param mixed $problem_id The id may be a numeric problem_id or
    *              a problem alias.
    * @param int|array $tag_id Id of the tag (or tags) to be added.
    *
    * @return bool True if successful.
    */
  public static function addTags(
      $problem_id
    , $tag_id
  ) {
  }
}



