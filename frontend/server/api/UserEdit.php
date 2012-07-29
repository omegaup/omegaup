<?php

require_once("ApiHandler.php");

class UserEdit extends ApiHandler {
	
	protected function RegisterValidatorsToRequest(){

	}


	protected function generateResponse(){
		
		//throw new ApiException("");
		
		//die( RequestContext::get("username") );

		$f = RequestContext::get("username");

		//echo $f;
		if(is_null($f)){
			Logger::log("null");
		}else{
			Logger::log($f);	
		}
		

		return array("asdf" => 23 );
	}


}