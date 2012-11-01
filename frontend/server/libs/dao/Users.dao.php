<?php

require_once ('Estructura.php');
require_once("base/Users.dao.base.php");
require_once("base/Users.vo.base.php");
/** Page-level DocBlock .
  * 
  * @author alanboy
  * @package docs
  * 
  */
/** Users Data Access Object (DAO).
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Users }. 
  * @author alanboy
  * @access public
  * @package docs
  * 
  */
class UsersDAO extends UsersDAOBase
{
  
    public static function searchUserByEmail($email)
    {

      global  $conn;
      $sql = "select u.* from Users u, Emails e where e.email = ? and e.user_id = u.user_id";
      $params = array( $email );

      $rs = $conn->GetRow($sql, $params);
      
      if(count($rs)==0)return NULL;
      
      return new Users( $rs );
    }


    
}
