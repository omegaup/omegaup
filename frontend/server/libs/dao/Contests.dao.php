<?php

require_once ('Estructura.php');
require_once("base/Contests.dao.base.php");
require_once("base/Contests.vo.base.php");
/** Page-level DocBlock .
  * 
  * @author alanboy
  * @package docs
  * 
  */
/** Contests Data Access Object (DAO).
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Contests }. 
  * @author alanboy
  * @access public
  * @package docs
  * 
  */
class ContestsDAO extends ContestsDAOBase
{
	public static final function getByAlias($alias)
	{

		$sql = "SELECT * FROM Contests WHERE (alias = ? ) LIMIT 1;";
		$params = array(  $alias );
                
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
		                
        $contest = new Contests( $rs );

        return $contest;

	}
	
	public static function getPrivateContestsCount(Users $user) {
		
		$sql = "SELECT count(*) as Total FROM Contests WHERE public = 0 and (director_id = ?);";		
		$params = array($user->getUserId());
                
		global $conn;
		$rs = $conn->GetRow($sql, $params);				                        
		
		if (!array_key_exists("Total", $rs)) {
			return 0;
		}
 		
        return $rs["Total"];		
	}
}
