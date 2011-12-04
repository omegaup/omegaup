<?php

require_once 'ApiHandler.php';
require_once 'ApiOutputFormatter.php';

class ApiLoader
{
    private static $output_formatter = NULL;
    
    public static function init($output_format = NULL)
    {
        if($output_format === 'test')
        {            
            require_once 'tests/TestApiOutputFormatter.php';
            self::$output_formatter = new TestApiOutputFormatter();
        }
        else
        {
            self::$output_formatter = new ApiOutputFormatter();
        }        
    }
        
    public static function load($output_format = NULL)
    {
        // Call init
        self::init($output_format);
        
        // Get API name        
        $api_name = RequestContext::get("apicmd");

        if(is_null($api_name))
        {
            return self::NotFound();
        }

        switch($api_name)
        {
            case 'Login':
                require_once('Login.php');
                $api = new Login();
                break;
            
            case 'Logout':
                require_once('Logout.php');
                $api = new Logout();
                break;
            
            case 'NewClarification':
                require_once('NewClarification.php');
                $api = new NewClarification();
                break;
            
            case 'NewContest':
                require_once('NewContest.php');
                $api = new NewContest();
                break;
            
            case 'NewProblemInContest':
                require_once('NewProblemInContest.php');
                $api = new NewProblemInContest();
                break;
            
            case 'NewRun':
                require_once('NewRun.php');
                $api = new NewRun();
                break;
            
            case 'ShowClarification':
                require_once('ShowClarification.php');
                $api = new ShowClarification();
                break;
            
            case 'ShowContest':
                require_once('ShowContest.php');
                $api = new ShowContest();
                break;
            
            case 'ShowContests':
                require_once('ShowContests.php');
                $api = new ShowContests();
                break;
            
            case 'ShowProblemInContest':
                require_once('ShowProblemInContest.php');
                $api = new ShowProblemInContest();
                break;
            
            case 'ShowProblemRuns':
                require_once('ShowProblemRuns.php');
                $api = new ShowProblemRuns();
                break;
            
            case 'ShowRun':
                require_once('ShowRun.php');
                $api = new ShowRun();
                break;
            
            case 'ShowScoreboard':
                require_once('ShowScoreboard.php');
                $api = new ShowScoreboard();
                break;
            
            case 'UpdateClarification':
                require_once('UpdateClarification.php');
                $api = new UpdateClarification();
                break;

            default:
                return self::NotFound();
                break;
        }

        try
        {
            $return_array = $api->ExecuteApi();
        }
        catch(ApiException $e)
        {
            // If something goes wrong, set the proper header and print output
            $exception_array = $e->getArrayMessage();                        
            return self::$output_formatter->PrintOuput($exception_array, $exception_array["header"]);
        }
        
        // Happy ending
        return self::$output_formatter->PrintOuput($return_array);
    }
    
    private static function NotFound()
    {
        $error_dispatcher = ApiHttpErrors::getInstance();
        $not_found = $error_dispatcher->notFound();
        
        return self::$output_formatter->PrintOuput($not_found, $not_found["header"]);
    }
    
}

?>
