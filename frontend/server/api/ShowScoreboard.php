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
require_once(dirname(__FILE__) . "/Scoreboard.php");
require_once(SERVER_PATH . "/libs/Authorization.php");

class ShowScoreboard extends ApiHandler
{
    private $contest;
   
    protected function RegisterValidatorsToRequest()
    {
	ValidatorFactory::stringNotEmptyValidator()->validate(RequestContext::get("contest_alias"), "contest_alias");

	try {
		$this->contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
	} catch(Exception $e) {
		// Operation failed in the data layer
		throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
	}
    } 
    
    protected function GenerateResponse() 
    {
        // Create scoreboard
	$scoreboard = new Scoreboard(
		$this->contest->getContestId(),
		Authorization::IsContestAdmin($this->_user_id, $this->contest)
	);
                 
        // Push scoreboard data in response
        $this->addResponse('ranking', $scoreboard->generate());
    }        
}
