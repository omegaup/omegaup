<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 * GET /contests/:id/ranking/
 * Si el usuario puede verlo, Muestra el ranking completo del contest ID.
 *
 * */


require_once("ApiHandler.php");
require_once("Scoreboard.php");

class ShowScoreboard extends ApiHandler
{
    private $scoreboardData;
   
    protected function RegisterValidatorsToRequest()
    {
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return ContestsDAO::getByAlias($value);
                }, "Contest is invalid."))
            ->validate(RequestContext::get("contest_alias"), "contest_alias");                
    } 
    
    protected function GenerateResponse() 
    {
        // Get contest
        // Get our contest given the alias
        try
        {            
            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );                
        }
        
        // Create scoreboard
        $myScoreboard = new Scoreboard($contest->getContestId());
                 
        // Get the scoreboard        
        $this->scoreboardData = $myScoreboard->generate();        
        
        // Push scoreboard data in response
        $this->response = $this->scoreboardData;
    }        
}

?>
