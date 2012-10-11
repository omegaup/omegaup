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
require_once 'NewContestTest.php';
require_once 'NewProblemInContestTest.php';


class Utils
{
    static $contestant;
    static $contestant_2;
    static $judge;
    static $problem_author;
    static $admin;
    
    static $inittime;
    static $counttime;
    
    
    //put your code here
    static function cleanup()
    {
        foreach($_REQUEST as $p)
        {
            unset($p);
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

        $mockCreator = new NewContestTest();
        $sessionManagerMock = $mockCreator->getMock('SessionManager', array('SetCookie'));
        
        $sessionManagerMock->expects($mockCreator->any())
                ->method('SetCookie')
                ->will($mockCreator->returnValue(true));
        
        RequestContext::set("username", $username);
        RequestContext::set("password", $password);
        
        // Login                                        
        $loginApi = new Login($sessionManagerMock);  
        
        try
        {
            $cleanValue = $loginApi->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());
        }
        
        $auth_token = $cleanValue["auth_token"];
        Logger::log("login authtoken " . $auth_token          );
        
        self::cleanup();                
        return $auth_token;
        
    }
    
    static function Logout($auth_token)
    {                        
        // Mock SessionManager
        $mockCreator = new NewContestTest();
        $sessionManagerMock = $mockCreator->getMock('SessionManager', array('SetCookie'));
        
        $sessionManagerMock->expects($mockCreator->any())
                ->method('SetCookie')
                ->will($mockCreator->returnValue(true));

        
        // Logout            
        RequestContext::set("auth_token", $auth_token);
        
        $logoutApi = new Logout($sessionManagerMock);        
        $cleanValue = $logoutApi->ExecuteApi();
                
        //Validate that token isn´t there anymore        
        $resultsDB = AuthTokensDAO::search(new AuthTokens(array("auth_token" => $auth_token)));
        if(sizeof($resultsDB) !== 0)
        {
            throw new Exception("User was not logged out correctly");
        }
                
    }
    
    static function LoginAsContestDirector()
    {
        return self::Login(self::$judge->getUsername(), self::$judge->getPassword());
    }
    
    static function GetContestDirectorUserId()
    {
        return self::$judge->getUserId();
    }
    
    static function LoginAsAdmin()
    {
        return self::Login(self::$admin->getUsername(), self::$admin->getPassword());
    }
    
    static function LoginAsContestant()
    {
        return self::Login(self::$contestant->getUsername(), self::$contestant->getPassword());
    }
    
    static function LoginAsProblemAuthor()
    {
        return self::Login(self::$problem_author->getUsername(), self::$problem_author->getPassword());
    }
    
    static function GetContestantUsername()
    {
        return self::$contestant->getUsername();
    }
    
    static function GetContestantUserId()
    {
        return self::$contestant->getUserId();
    }
    
    static function GetProblemAuthorUsername()
    {
        return self::$problem_author->getUsername();
    }
    
    static function GetProblemAuthorUserId()
    {
        return self::$problem_author->getUserId();
    }
    
    static function LoginAsContestant2()
    {
        return self::Login(self::$contestant_2->getUsername(), self::$contestant_2->getPassword());
    }
    
    static function GetContestant2Username()
    {
        return self::$contestant_2->getUsername();
    }
    
    static function GetContestant2UserId()
    {
        return self::$contestant_2->getUserId();
    }
    
    
    static function SetAuthToken($auth_token)
    {
        RequestContext::set("auth_token", $auth_token);        
    }
    
    static function CreateRandomString()
    {
        return md5(uniqid(rand(), true));
    }
    
    static function GetValidPublicContestId()
    {                
        // Create a clean contest and get the ID
        $contestCreator = new NewContestTest();
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
       
    
    static function GetPhpUnixTimestamp($time = NULL)
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
    
    static function GetDbDatetime()
    {
        // Go to the DB 
        global $conn;
        
        $sql = "SELECT NOW()";
        $rs = $conn->GetRow($sql);                
        
        if(count($rs)===0)
        {
            return NULL;
        }        
                
        return $rs[0]; 
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
    
    static function CreateUser($username, $password)
    {
        $contestant = new Users();
        $contestant->setUsername(Utils::CreateRandomString());
        $contestant->setPassword(md5($password));
        $contestant->setSolved(0);
        $contestant->setSubmissions(0);
        UsersDAO::save($contestant);
        
        // Save localy clean password
        $contestant->setPassword($password);
        
        return $contestant;
    }
    
    static function getNextTime()
    {        
        self::$counttime++;                
        return Utils::GetTimeFromUnixTimestam(self::$inittime + self::$counttime);
    }
    
    static function CleanLog()
    {
        exec("echo -n > " . OMEGAUP_LOG_ACCESS_FILE);
        exec("echo -n > " . OMEGAUP_LOG_ERROR_FILE);
    }
    
    static function CleanProblemsPath()
    {
        exec("rm -r " . PROBLEMS_PATH);
        exec("mkdir " . PROBLEMS_PATH);
    }
    
    static function CleanRunsPath()
    {
        exec("rm -r " . RUNS_PATH);
        exec("mkdir " . RUNS_PATH);
    }
    
    static function CleanupDB()
    {
        global $conn;
        
        // Tables to truncate
        $tables = array (
            'Runs', 
            'Contest_Problems', 
            'Contests_Users', 
            'Clarifications',
            'Contest_Problem_Opened',
            'Problems', 
            'Auth_Tokens',
            'Contests',
            'Users'
            );
        
        foreach($tables as $t)
        {
	    try
  	    {
           	 $sql = "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE " . $t . "; SET FOREIGN_KEY_CHECKS=1;";
		 $conn->GetRow($sql);
            }
	    catch(Exception $e)
	    {
	    }	 		
   	}

    }
}

?>
