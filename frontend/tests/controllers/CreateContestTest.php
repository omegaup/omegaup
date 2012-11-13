<?php

/**
 * Description of ContestControllerTest
 *
 * @author joemmanuel
 */


require_once SERVER_PATH . 'controllers/contest.controller.php';

require_once 'Utils.php';
require_once 'UsersFactory.php';
require_once 'ContestsFactory.php';

/*
 *  Tests de LoginController
 * 
 */
class CreateContestTest extends PHPUnit_Framework_TestCase
{
            
    /**
     *  Basic test of create contest
     * 
     */
    public function testCreateContest(){ 
                       
        $contestContext = ContestsFactory::setContestContext();
        
        $sc = new ContestController();
        try{
            $sc->current_user_id = $contestContext["contestDirector"]->getUserId();
            $sc->current_user_obj = $contestContext["contestDirector"];
            $response = $sc->create();
        }catch(ApiException $e){
            var_dump($e->getPrevious()->getMessage());
            $this->fail("Unexpected exception");
        }
        
        return $response;
        
        // Assert status of new contest
        self::assertEquals("ok", $response["status"]);
        
        // Validate that data was written to DB by iterating through all contests
        $contest = new Contests();
        $contest->setTitle($contestContext["title"]);
        $contests = ContestsDAO::search($contest);
        $contest = $contests[0];
        
        // Assert that we found our contest       
        $this->assertNotNull($contest);
        $this->assertNotNull($contest->getContestId());
        
        // Assert data was correctly saved
        $this->assertEquals(RequestContext::get("description"), $contest->getDescription());
        $this->assertEquals(RequestContext::get("start_time"), Utils::GetPhpUnixTimestamp($contest->getStartTime()));
        $this->assertEquals(RequestContext::get("finish_time"), Utils::GetPhpUnixTimestamp($contest->getFinishTime()));
        $this->assertEquals(RequestContext::get("window_length"), $contest->getWindowLength());
        $this->assertEquals(RequestContext::get("public"), $contest->getPublic());
        $this->assertEquals(RequestContext::get("alias"), $contest->getAlias());
        $this->assertEquals(RequestContext::get("points_decay_factor"), $contest->getPointsDecayFactor());
        $this->assertEquals(RequestContext::get("partial_score"), $contest->getPartialScore());
        $this->assertEquals(RequestContext::get("submissions_gap"), $contest->getSubmissionsGap());
        $this->assertEquals(RequestContext::get("feedback"), $contest->getFeedback());
        $this->assertEquals(RequestContext::get("penalty"), $contest->getPenalty());
        $this->assertEquals(RequestContext::get("scoreboard"), $contest->getScoreboard());
        $this->assertEquals(RequestContext::get("penalty_time_start"), $contest->getPenaltyTimeStart());
        $this->assertEquals(RequestContext::get("penalty_calc_policy"), $contest->getPenaltyCalcPolicy());        
    }
    
    /**
     * 
     */
    public function testMissingParameters(){
       // Array of valid keys
        $valid_keys = array(
            "title",
            "description",
            "start_time",
            "finish_time",            
            "public",
            "alias",
            "points_decay_factor",
            "partial_score",
            "submissions_gap",
            "feedback",
            "penalty",
            "scoreboard",
            "penalty_time_start",
            "penalty_calc_policy"            
        );
        
        foreach($valid_keys as $key)        
        {        
            // Insert new contest
            $contestContext = ContestsFactory::setContestContext();
            
            // unset the current key from request
            unset($_REQUEST[$key]);
            
            $sc = new ContestController();        
            $sc->current_user_id = $contestContext["contestDirector"]->getUserId();
            $sc->current_user_obj = $contestContext["contestDirector"];
            try
            {            
                $response = $sc->create();
            }catch(ApiException $e)
            {
                // Exception is expected
                $exception_array = $e->asArray();            

                // Validate exception
                $this->assertNotNull($exception_array);
                $this->assertEquals(400, $exception_array["errorcode"]);
                $this->assertEquals("HTTP/1.1 400 BAD REQUEST", $exception_array["header"]);                

                return;
            }

            $this->fail("Exception was expected. Parameter: ". $key);            
        }  
    }
    
    /**
     * 
     */
    public function testDuplicatedAlias(){
        
        $contestContext = ContestsFactory::setContestContext();
        
        $sc = new ContestController();
        $sc->current_user_id = $contestContext["contestDirector"]->getUserId();
        $sc->current_user_obj = $contestContext["contestDirector"];
        
        // First time should be fine
        try{                                    
            $response = $sc->create();
        }catch(ApiException $e){
            var_dump($e->getPrevious()->getMessage());
            $this->fail("Unexpected exception");
        }
        
        // Second time with same aliass should crash
        try{                                    
            $response = $sc->create();
        }catch(DuplicatedEntryInDatabaseException $e){            
            return;
        }
                
        $this->fail("Didn't complain about duplicated alias in Contests");
    }
    
}
