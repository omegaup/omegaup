<?php

require_once ('Estructura.php');
require_once("base/Contests_Users.dao.base.php");
require_once("base/Contests_Users.vo.base.php");
/** Page-level DocBlock .
  * 
  * @author alanboy
  * @package docs
  * 
  */
/** ContestsUsers Data Access Object (DAO).
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link ContestsUsers }. 
  * @author alanboy
  * @access public
  * @package docs
  * 
  */
class ContestsUsersDAO extends ContestsUsersDAOBase
{
    public static function CheckAndSaveFirstTimeAccess($user_id, $contest_id)
    {
        $contest_user = self::getByPK($user_id, $contest_id);
        
        // If is null, add our contest_user relationship 
        if(is_null($contest_user))
        {
            $contest_user = new ContestsUsers();
            $contest_user->setUserId($user_id);
            $contest_user->setContestId($contest_id);
            $contest_user->setAccessTime(date("Y-m-d H:i:s"));
            $contest_user->setScore(0);
            $contest_user->setTime(0);

            ContestsUsersDAO::save($contest_user);                                         
        }
        else if($contest_user->getAccessTime() === "0000-00-00 00:00:00")
        {
            // If its set to default time, update it
            $contest_user->setAccessTime(date("Y-m-d H:i:s"));
            
            ContestsUsersDAO::save($contest_user);            
        }
        
        return $contest_user;
    }    
}
