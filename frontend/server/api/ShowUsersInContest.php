<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * GET /ShowUsertsInContest/:contest_alias
 * Si el usuario tiene permisos (contest director o admin) agrega un user a su concurso
 *
 * */

require_once("ApiHandler.php");

class ShowUsersInContest extends ApiHandler
{ 
    
    private $_contest;

    protected function RegisterValidatorsToRequest()
    {          
        
        // Check contest_alias
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return ContestsDAO::getByAlias($value);
                }, "Contest is invalid."))
            ->validate(RequestContext::get("contest_alias"), "contest_alias");
                        
                
        // Only director is allowed to create problems in contest
        try
        {
            $this->_contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
        }
        catch(Exception $e)
        {  
           // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
        }                
        
        if($this->_contest->getDirectorId() !== $this->_user_id)
        {
            throw new ApiException(ApiHttpErrors::forbiddenSite());
        }
    }
    
    protected function GenerateResponse() 
    {
        // Get users from DB
        $contest_user_key = new ContestsUsers();
        $contest_user_key->setContestId($this->_contest->getContestId());
        
        try
        {
            $db_results = ContestsUsersDAO::search($contest_user_key);
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
        }
        
        $users = array();
        
        // Add all users to an array
        foreach($db_results as $r)
        {
            $user_id = $r->getUserId();
            $user = UsersDAO::getByPK($user_id);
            $users[] = array("user_id" => $user_id, "username" => $user->getUsername());
        }
            
        $this->addResponse("users", $users);
    }
}

?>