<?php

include('base/Problemset_Users.dao.base.php');
include('base/Problemset_Users.vo.base.php');
/** ProblemsetUsers Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetUsers }.
  * @access public
  *
  */
class ProblemsetUsersDAO extends ProblemsetUsersDAOBase {
    public static function CheckAndSaveFirstTimeAccess($user_id, $problemset_id, $grant_access = false) {
        $problemset_user = self::getByPK($user_id, $problemset_id);
        if (is_null($problemset_user)) {
            if (!$grant_access) {
                // User was not authorized to do this.
                throw new ForbiddenAccessException();
            }
            $problemset_user = new ProblemsetUsers();
            $problemset_user->user_id = $user_id;
            $problemset_user->problemset_id = $problemset_id;
            $problemset_user->access_time = date('Y-m-d H:i:s');
            $problemset_user->score = 0;
            $problemset_user->time = 0;
            ProblemsetUsersDAO::save($problemset_user);
        } elseif ($problemset_user->access_time === '0000-00-00 00:00:00') {
            // If its set to default time, update it
            $problemset_user->access_time = date('Y-m-d H:i:s');
            ProblemsetUsersDAO::save($problemset_user);
        }
        return $problemset_user;
    }
}
