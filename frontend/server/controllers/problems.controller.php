<?php

require_once( "dao/Problems.dao.php" );	

class ProblemsController {

	/**
	  * Returns all the problems from the judge which are active,
	  * 
	  *	
	  **/
	public static function getProblemList( $pageSize = 10 , $pageNumber = 1 , $servidor = null , $orderBy = null){
		
		//$condition = "server = '$servidor' and public = 1";
		//$results = ProblemsDAO::byPage ( $sizePage , $noPage , $condition , $servidor, $orderBy );		

		return ProblemsDAO::getAll ( $pageNumber, $pageSize, $orderBy );

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






  private static function deflateZip( $pathToZip ){

    if(is_null($pathToZip)){
      throw new Exception();
    }
    
    //tmp name for deflated contents
    $zid = time();


    //try to read up the zip
    $zip = zip_open( $pathToZip );


    //unsuccesful?
    if (!$zip) {
      throw new Exception();
    }

    //create tmp folder
    $tmp_dir  = OMEGAUP_ROOT . "tmp/" . $zid;
    mkdir ($tmp_dir);
  

    //dump zipped files there
    while ($zip_entry = zip_read($zip)) {

      $fp = @fopen( $tmp_dir . "/" . zip_entry_name($zip_entry), "w");
      if(!$fp) continue;
      if (zip_entry_open($zip, $zip_entry, "r")) {
        $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
        fwrite($fp, "$buf");
        zip_entry_close($zip_entry);
        fclose($fp);
      }

    }

    zip_close($zip);
  

    return $zid;
  
  }//deflateZip()

  private static function zipCleanup($zid){
    $tmp_dir  = OMEGAUP_ROOT . "tmp/" . $zid;
    unlink( $tmp_dir );
    return;
  }

  private static function parseZipContents(  ){
    
  }

  private static function testZip( ){
    $files_needed_in_zip = array(  );

    /*foreach($files_needed_in_zip as $f){
      
    }*/

    return true;
  }

  public static function parseZip( $pathToZip ){

    $zid = self::deflateZip( $pathToZip );

    if( ! self::testZip( $zid ) ){
      //missing files in zip
      throw Exception();  
    }

    self::parseZipContents( $zid );
    
    self::zipCleanup( $zid );
  }

}



