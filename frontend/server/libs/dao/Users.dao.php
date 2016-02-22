<?php

require_once('base/Users.dao.base.php');
require_once('base/Users.vo.base.php');
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
class UsersDAO extends UsersDAOBase {
    public static function FindByEmail($email) {
        global  $conn;
        $sql = 'select u.* from Users u, Emails e where e.email = ? and e.user_id = u.user_id';
        $params = array( $email );
        $rs = $conn->GetRow($sql, $params);
        if (count($rs)==0) {
            return null;
        }
        return new Users($rs);
    }

    public static function FindByUsername($username) {
        $vo_Query = new Users(array(
            'username' => $username
        ));

        $a_Results = UsersDAO::search($vo_Query);

        if (sizeof($a_Results) != 1) {
            return null;
        }

        return array_pop($a_Results);
    }

    public static function FindByUsernameOrName($usernameOrName) {
        global  $conn;
        $sql = "select DISTINCT u.* from Users u where u.username LIKE CONCAT('%', ?, '%') or u.name LIKE CONCAT('%', ?, '%') LIMIT 10";
        $args = array($usernameOrName, $usernameOrName);

        $rs = $conn->Execute($sql, $args);
        $ar = array();
        foreach ($rs as $foo) {
            $bar =  new Users($foo);
            array_push($ar, $bar);
        }
        return $ar;
    }

    public static function FindResetInfoByEmail($email) {
        $user = self::FindByEmail($email);
        if (is_null($user)) {
            return null;
        } else {
            return array(
                'reset_digest'  => $user->reset_digest,
                'reset_sent_at'     => $user->reset_sent_at
            );
        }
    }
}
