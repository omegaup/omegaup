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
   
    protected function GetRequest()
    {
        $this->request = array(
            "contest_id" => new ApiExposedProperty("contest_id", true, GET, array(
                new NumericValidator(),
                new CustomValidator( 
                    function ($value)
                    {
                        // Check if the contest exists
                        return ContestsDAO::getByPK($value);
                    }) 
            ))                                 
        );
                
    } 
    
    protected function GenerateResponse() 
    {
        // @todo validar si el concursante puede ver el contest
        $myScoreboard = new Scoreboard($this->request["contest_id"]->getValue());
         
        // Get the scoreboard        
        $this->scoreboardData = $myScoreboard->generate();
        
        // Push scoreboard data in response
        $this->response = $this->scoreboardData;
    }
    
    
}

?>
