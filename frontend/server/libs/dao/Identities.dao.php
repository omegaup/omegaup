<?php

include_once('base/Identities.dao.base.php');
include_once('base/Identities.vo.base.php');
/** Identities Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Identities }.
  * @access public
  *
  */
class IdentitiesDAO extends IdentitiesDAOBase {
    public static function FindByUserId($user_id) {
        $vo_Query = new Identities([
            'main_user_id' => $user_id
        ]);

        $a_Results = IdentitiesDAO::search($vo_Query);

        if (sizeof($a_Results) != 1) {
            return null;
        }

        return array_pop($a_Results);
    }

    public static function FindByUsername($username) {
        $vo_Query = new Identities([
            'username' => $username
        ]);

        $a_Results = IdentitiesDAO::search($vo_Query);

        if (sizeof($a_Results) != 1) {
            return null;
        }

        return array_pop($a_Results);
    }
}
