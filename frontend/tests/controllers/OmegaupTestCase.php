<?php

/**
 * Parent class of all Test cases for Omegaup
 * Implements common methods for setUp and asserts
 *
 * @author joemmanuel
 */
class OmegaupTestCase extends PHPUnit_Framework_TestCase {
	
	/**
	 * setUp function gets executed before each test (thanks to phpunit)
	 */
	public function setUp() {		
		
		//Clean $_REQUEST before each test
		unset($_REQUEST);				
	}
	
	
	/**
	 * Given an User, checks that login let state as supposed
	 * 
	 * @param Users $user
	 * @param type $auth_token
	 */
	public function assertLogin(Users $user, $auth_token = null) {
		
		// Check auth token
		$authTokenKey = new AuthTokens(array(
		   "user_id" => $user->getUserId() 
		));
		$auth_token_bd = AuthTokensDAO::search($authTokenKey);       

		// Checar que tenemos al menos 1 token
		$this->assertEquals(1, count($auth_token_bd));        
		
		// Validar que el token que esta en la DB es el mismo que tenemos por 
		// parametro
		if (!is_null($auth_token)){
			$this->assertEquals($auth_token, $auth_token_bd[0]->getToken());
		}

		// @todo check last access time
	}
		
}

