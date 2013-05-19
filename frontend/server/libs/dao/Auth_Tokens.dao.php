<?php

require_once ('Estructura.php');
require_once("base/Auth_Tokens.dao.base.php");
require_once("base/Auth_Tokens.vo.base.php");
/** Page-level DocBlock .
  * 
  * @author alanboy
  * @package docs
  * 
  */
/** AuthTokens Data Access Object (DAO).
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link AuthTokens }. 
  * @author alanboy
  * @access public
  * @package docs
  * 
  */
class AuthTokensDAO extends AuthTokensDAOBase
{


	public static function getUserByToken( $auth_token )
    {

		//look for it on the database
		global  $conn;
		
		$sql = "select u.* from Users u, Auth_Tokens at where at.user_id = u.user_id and at.token = ? and NOW() < DATE_ADD(at.create_time, INTERVAL ".OMEGAUP_EXPIRE_TOKEN_AFTER.")  ;";		
		
		$params = array( $auth_token );

		$rs = $conn->GetRow($sql, $params);

		//no matches
		if(count($rs)==0) return NULL;

		return new Users( $rs );
	}
	
	public static function expireAuthTokens($user_id) {
		//look for it on the database		
		global  $conn;
		
		$sql = "delete from Auth_Tokens where user_id = ? AND NOW() > DATE_ADD(create_time, INTERVAL ".OMEGAUP_EXPIRE_TOKEN_AFTER.")  ;";
		
		$params = array( $user_id );
		
		$conn->Execute($sql, $params);		
		return $conn->Affected_Rows();
	}
}
