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
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
            function ($value)
            {
                // Check if the contest exists
                return ContestsDAO::getByPK($value);
            }, "Contest is invalid."))
        ->validate(RequestContext::get("contest_id"), "contest_id");
                
    } 
    
    protected function GenerateResponse() 
    {
        // @todo validar si el concursante puede ver el contest
        $myScoreboard = new Scoreboard(RequestContext::get("contest_id"));
                 
        // Get the scoreboard        
        $this->scoreboardData = $myScoreboard->generate();        
        
        // Push scoreboard data in response
        $this->response = $this->scoreboardData;
    }        
}

?>
