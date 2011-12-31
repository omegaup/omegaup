<?php

require_once ('Estructura.php');
require_once("base/Runs.dao.base.php");
require_once("base/Runs.vo.base.php");
/** Page-level DocBlock .
  * 
  * @author alanboy
  * @package docs
  * 
  */
/** Runs Data Access Object (DAO).
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Runs }. 
  * @author alanboy
  * @access public
  * @package docs
  * 
  */
class RunsDAO extends RunsDAOBase
{
    /*
     * 
     * Get last run of a user
    * 
     */
    public static final function GetLastRun($contest_id, $problem_id, $user_id)
    {
        //Build SQL statement
        $sql = "SELECT * from Runs where user_id = ? and contest_id = ? and problem_id = ? ORDER BY time DESC LIMIT 1";
        $val = array($user_id, $contest_id, $problem_id);

        global $conn;
        $rs = $conn->GetRow($sql, $val);            

        if(count($rs) === 0)
        {
            return null;
        }
        $bar =  new Runs($rs);
        
        return $bar;
    }
        

    public static final function IsRunInsideSubmissionGap($contest_id, $problem_id, $user_id)
    {
        // Get last run
        $lastrun = self::GetLastRun($contest_id, $problem_id, $user_id);
        
        if(is_null($lastrun))
        {            
            return true;
        }
        
        // Get submissions gap
        $contest = ContestsDAO::getByPK($contest_id);
                
        // Giving 10 secs as gift
        return time() >= (strtotime($lastrun->getTime()) + (int)$contest->getSubmissionsGap() - 10);
    }

}
