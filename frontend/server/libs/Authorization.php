<?php
/**
 * Authorization.php - Contains static function calls that return true if a user is authorized to perform certain action.
 */
class Authorization 
{
    public static function IsContestAdmin($user_id, Contests $contest) 
    {
        return ($contest->getDirectorId() === $user_id) || self::IsSystemAdmin($user_id);
    }
    
    public static function IsSystemAdmin($user_id)
    {
        return $user_id === 3 || $user_id === 37;
    }
    
    // @todo user in contest
}
