<?php

require_once("ApiHandler.php");

class UserEdit extends ApiHandler {
	
	protected function RegisterValidatorsToRequest(){

	}


	protected function generateResponse(){
		
		//throw new ApiException("");
		
		//die( RequestContext::get("username") );

		$userToEdit = RequestContext::get("username");

		$userToEditVo = UsersDAO::search(new Users(array( "username" => $userToEdit )));

		if(is_null($userToEditVo)){
 			throw new ApiException( ApiHttpErrors::invalidParameter("User does not exist") );
		}






		//echo $f;
		/*if(is_null($f)){
			Logger::log("null");

		}else{
			Logger::log($f);

		}
			*/




		return array("asdf" => 23 );
	}


}