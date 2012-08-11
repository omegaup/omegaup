<?php
/**
 * Authorization.php - Contains static function calls that return true if a user is authorized to perform certain action.
 */

 define('ADMIN_ROLE',  '1');

class Authorization 
{
    public static function CanViewRun($user_id, Runs $run)
    {
        try
        {   
            $contest = ContestsDAO::getByPK($run->getContestId());
            $problem = ProblemsDAO::getByPK($run->getProblemId());
        }
        catch(Exception $e)
        {
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);     
        }
        
        return ($run->getUserId() == $user_id 
                || Authorization::CanEditRun($user_id, $run));        
    }
    
    public static function CanEditRun($user_id, Runs $run)
    {
        try
        {   
            $contest = ContestsDAO::getByPK($run->getContestId());
            $problem = ProblemsDAO::getByPK($run->getProblemId());
        }
        catch(Exception $e)
        {
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);     
        }
        
        return Authorization::IsContestAdmin($user_id, $contest) 
                || $problem->getAuthorId() == $user_id;
    }
    
    public static function CanViewClarification($user_id, Clarifications $clarification)
    {
        try
        {
            $contest = ContestsDAO::getByPK($clarification->getContestId());
        }
        catch(Exception $e)
        {
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);     
        }
        
        return ($clarification->getAuthorId() == $user_id 
                || Authorization::IsContestAdmin($user_id, $contest));
    }
    
    public static function CanEditClarification($user_id, Clarifications $clarification)
    {
        try
        {
            $contest = ContestsDAO::getByPK($clarification->getContestId());                        
            $problem = ProblemsDAO::getByPK($clarification->getProblemId());
        }
        catch(Exception $e)
        {
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);     
        }
        
        return ($problem->getAuthorId() === $user_id 
                || Authorization::IsContestAdmin($user_id, $contest));
    }
    
    public static function CanEditProblem($user_id, Problems $problem)
    {
        return ($problem->getAuthorId() == $user_id || Authorization::IsSystemAdmin($user_id));
    }
        
    public static function IsContestAdmin($user_id, Contests $contest) 
    {
        return ($contest->getDirectorId() === $user_id) || self::IsSystemAdmin($user_id);
    }
    
    public static function IsSystemAdmin($user_id)
    {
        try
        {
            $ur = UserRolesDAO::getByPK($user_id, ADMIN_ROLE);
            
            return !is_null($ur);
            
        }
        catch(Exception $e)
        {
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);     
        }               
    }    
    // @todo user in contest
}
