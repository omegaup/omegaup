<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /AddUserToPrivateContest/:user_id/:contest_alias
 * Si el usuario tiene permisos (contest director o admin) agrega un user a su concurso
 *
 * */

require_once("ApiHandler.php");

class AddUserToPrivateContest extends ApiHandler
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
                
        // Check username
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if user exists
                    return UsersDAO::getByPK($value);
                }, "User ID is invalid."))
            ->validate(RequestContext::get("user_id"), "user_id");
                
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
        
        if(!Authorization::IsContestAdmin($this->_user_id, $this->_contest))
        {
            throw new ApiException(ApiHttpErrors::forbiddenSite());
        }
    }
    
    protected function GenerateResponse() 
    {
        $contest_user = new ContestsUsers();
        $contest_user->setContestId($this->_contest->getContestId());
        $contest_user->setUserId(RequestContext::get("user_id"));
        $contest_user->setAccessTime("0000-00-00 00:00:00");
        $contest_user->setScore("0");
        $contest_user->setTime("0");
        
        // Save the contest to the DB
        try
        {
            ContestsUsersDAO::save($contest_user);
        }
        catch(Exception $e)
        {
           // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );        
        }
    }
}
?>
