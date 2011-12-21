<?php

class ZipHandler{
	
	
  /**
    *
    *
    **/	
  	private static function DeflateZip( $pathToZip ){

		if( is_null($pathToZip) ){
			throw new Exception( "Invalid path to ZIP" );
		}

		//tmp name for deflated contents
		$zid = time();


		//try to read up the zip
		try{
			$zip = zip_open( $pathToZip );
			
		}catch(Exception $e){
			Logger::error($e);
			throw $e;
			
		}



		//unsuccesful?
		if (!$zip) {
			throw new Exception("Unable to open " . $pathToZip );
		}


		//create tmp folder
		$tmp_dir  = OMEGAUP_ROOT . "tmp/" . $zid;
		
		if(!mkdir ($tmp_dir)){
			Logger::error("Unable to create temporary directory while trying to unzip.");
			throw new Excepetion( "Unable to create temporary directory." );
		}


		//dump zipped files there
		while ($zip_entry = zip_read($zip)) {
			
			$fp = @fopen( $tmp_dir . "/" . zip_entry_name($zip_entry), "w");
			
			if(!$fp) continue;
			
			if (zip_entry_open($zip, $zip_entry, "r")) {
				
				$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
				
				if( fwrite($fp, "$buf") === FALSE ){
					Logger::error("Failure while writing unziped contents of " . $pathToZip);
					throw new Exception("Failure while writing unziped contents of " . $pathToZip);
					
				}
				
				zip_entry_close($zip_entry);
				
				fclose($fp);
			}

		}

		zip_close($zip);

		return $zid;

	}//deflateZip()



  /**
    * Deletes temporary folder used by ZipHandler::DeflateZIP
    *
    **/
	private static function ZipCleanup( $zid ){

	    $tmp_dir  = OMEGAUP_ROOT . "tmp/" . $zid;
	
	    if( unlink( $tmp_dir ) === FALSE ){
			Logger::error("Unable to remove the file " . $tmp_dir . " while doing ZipCleanup");
			throw new Excpetion("Unable to remove the file " . $tmp_dir);
		}
	

	}





	
}