<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Utils
 *
 * @author Nancy
 */

require_once '../Login.php';
require_once '../Logout.php';

class Utils
{
    //put your code here
    static function cleanup()
    {
        unset($_POST);
        unset($_GET);
    }
    
    static function ConnectToDB()
    {
        if(defined("WHOAMI") && WHOAMI == "API")
        {
		
            require_once('adodb5/adodb.inc.php');
            require_once('adodb5/adodb-exceptions.inc.php');
            require_once('dao/model.inc.php');
            if(file_exists('dao/model.inc.php')) echo "exists!";
            $conn = null;

            try{                    
                $conn = ADONewConnection(OMEGAUP_DB_DRIVER);                    
                $conn->debug = OMEGAUP_DB_DEBUG;
                $conn->PConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, OMEGAUP_DB_NAME);

                if(!$conn) {
                            /**
                             * Dispatch missing parameters
                             * */
                            header('HTTP/1.1 500 INTERNAL SERVER ERROR');

                            die(json_encode(array(
                                    "status" => "error",
                                    "error"	 => "Conection to the database has failed.",
                                    "errorcode" => 1
                            )));

                }

            } catch (Exception $e) {

                    header('HTTP/1.1 500 INTERNAL SERVER ERROR');

                    die(json_encode(array(
                            "status" => "error",
                            "error"	 => $e,
                            "errorcode" => 2
                    )));

            }
            $GLOBALS["conn"] = $conn;
            return;
	}
    }
    
    static function Login($username, $password)
    {
        self::cleanup();
        $_POST["username"] = $username;
        $_POST["password"] = $password;
        
        // Login                                        
        $loginApi = new Login();  
                
        $cleanValue = $loginApi->ExecuteApi();
        $auth_token = $cleanValue["auth_token"];
                
        
        self::cleanup();        
        return $auth_token;
        
    }
    
    static function Logout($auth_token)
    {
        self::cleanup();
        
        // Logout            
        $_POST["auth_token"] = $auth_token;
        
        $logoutApi = new Logout();        
        $cleanValue = $logoutApi->ExecuteApi();
                
        //Validate that token isn´t there anymore        
        $resultsDB = AuthTokensDAO::search(new AuthTokens(array("auth_token" => $auth_token)));
        if(sizeof($resultsDB) !== 0)
        {
            throw new Exception("User was not logged out correctly");
        }
        
        self::cleanup();
    }
    
    static function LoginAsJudge()
    {
        return self::Login("judge", "password");
    }
    
    static function GetJudgeUserId()
    {
        return 3;
    }
    
    static function LoginAsAdmin()
    {
        return self::Login("admin", "password");
    }
    
    static function LoginAsContestant()
    {
        return self::Login("user", "password");
    }
    
    static function SetAuthToken($auth_token)
    {
        $_POST["auth_token"] = $auth_token;
    }
    
    static function RandomString()
    {
        return md5(uniqid(rand(), true));
    }
    
}

?>
