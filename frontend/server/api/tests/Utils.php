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
require_once 'NewContestsTest.php';
require_once 'NewProblemInContestTest.php';


class Utils
{
    //put your code here
    static function cleanup()
    {
        foreach($_POST as $p)
        {
            unset($p);
        }
        foreach($_GET as $g)
        {
            unset($g);
        }        
    }
    
    static function ConnectToDB()
    {
       		
        require_once('adodb5/adodb.inc.php');
        require_once('adodb5/adodb-exceptions.inc.php');
        require_once('dao/model.inc.php');
        
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
    
    static function Login($username, $password)
    {
        self::cleanup();
        $_POST["username"] = $username;
        $_POST["password"] = $password;
        
        // Login                                        
        $loginApi = new Login();  
        
        try
        {
            $cleanValue = $loginApi->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());
        }
        
        $auth_token = $cleanValue["auth_token"];
                
        
        self::cleanup();        
        return $auth_token;
        
    }
    
    static function Logout($auth_token)
    {                        
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
        return self::Login(self::GetContestantUsername(), "password");
    }
    
    static function GetContestantUsername()
    {
        return "user";
    }
    
    static function GetContestantUserId()
    {
        return 1;
    }
    
    static function LoginAsContestant2()
    {
        return self::Login(self::GetContestant2Username(), "password");
    }
    
    static function GetContestant2Username()
    {
        return "user2";
    }
    
    static function GetContestant2UserId()
    {
        return 4;
    }
    
    
    static function SetAuthToken($auth_token)
    {
        $_POST["auth_token"] = $auth_token;
    }
    
    static function CreateRandomString()
    {
        return md5(uniqid(rand(), true));
    }
    
    static function GetValidPublicContestId()
    {
        // Create a clean contest and get the ID
        $contestCreator = new NewContestsTest();
        $contest_id = $contestCreator->testCreateValidContest(1);
        
        return $contest_id;
    }
    
    static function GetValidProblemOfContest($contest_id)
    {
        // Create problem in our contest
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        
        return $problem_id;
    }
    
    static function DeleteAllContests()
    {    
        try
        {
            $contests = ContestsDAO::getAll();
            foreach($contests as $c)
            {
                ContestsDAO::delete($c);
            }
        }
        catch(ApiException $e)
        {
            // Propagate exception
            var_dump($e->getArrayMessage());
            throw $e;
        }
        
    }
    
    static function DeleteClarificationsFromProblem($problem_id)
    {
        self::ConnectToDB();
        
        // Get clarifications
        $clarifications = ClarificationsDAO::getAll();
        
        // Delete those who belong to problem_id
        foreach($clarifications as $c)
        {
            if($c->getProblemId() == $problem_id)
            {                
                try
                {
                    ClarificationsDAO::delete($c);
                }
                catch(ApiException $e)
                {
                    var_dump($e->getArrayMessage());
                    throw $e;
                }
            }
        }
        
        self::cleanup();
    }
       
    
    static function GetDBUnixTimestamp($time = NULL)
    {                        
        if( is_null($time))
        {
            return time();
        }
        else
        {
            return strtotime($time);
        }                                                                              
    }
    
    static function GetTimeFromUnixTimestam($time)
    {        
        // Go to the DB to take the unix timestamp
        global $conn;
        
        $sql = "SELECT FROM_UNIXTIME(?)";
        $params = array($time);
        $rs = $conn->GetRow($sql, $params);                
        
        if(count($rs)===0)
        {
            return NULL;
        }        
                
        return $rs[0]; 
    }
}

?>
