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

		if(sizeof($userToEditVo) != 1){
 			throw new ApiException( ApiHttpErrors::invalidParameter("User does not exist") );

		}else{
			$userToEditVo = $userToEditVo[0];

		}


		$cu = LoginController::getCurrentUser();



		//if no admin, username must be himself
		if(!Authorization::IsSystemAdmin($cu->getUserId()) ){
			//am i the user denoted by username?
			if($cu->getUsername() != $userToEditVo->getUsername()){
				throw new ApiException( ApiHttpErrors::invalidParameter("You may onlye change your user details.") );
			}
		}		




		if(!Authorization::IsSystemAdmin($cu->getUserId()) && !is_null(RequestContext::get("password"))){

				//i want to change password, and i am not admin,
				//do the testing


				//he must provide the old password
				if(is_null(RequestContext::get("old_password"))){
					throw new ApiException( ApiHttpErrors::invalidParameter("You must provide your old password."));
				}


				if(md5(RequestContext::get("old_password")) != $cu->getPassword()){
					throw new ApiException( ApiHttpErrors::invalidParameter("Your old password does not match."));
				}


				if(RequestContext::get("password") == RequestContext::get("old_password")){
					throw new ApiException( ApiHttpErrors::invalidParameter("You provided the same password."));	
				}

				//test for valid strong password
				//legth, not the same as username, not used before


				$userToEditVo->setPassword(md5(RequestContext::get("password")));

		}



		try{
			UsersDAO::save( $userToEditVo );	

		}catch(Exception $e){
			throw new ApiException( ApiHttpErrors::invalidDatabaseOperation("Something went wrong"));	

		}
		



		// Am I Admin?

		return array("asdf" => 23 );
	}


}