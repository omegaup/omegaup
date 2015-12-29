<?php

require_once('base/Contests_Users.dao.base.php');
require_once('base/Contests_Users.vo.base.php');
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
class ContestsUsersDAO extends ContestsUsersDAOBase {
    public static function CheckAndSaveFirstTimeAccess($user_id, $contest_id, $grant_access = false) {
            $contest_user = self::getByPK($user_id, $contest_id);

        if (is_null($contest_user)) {
            if (!$grant_access) {
                // User was not authorized to do this.
                throw new ForbiddenAccessException();
            }
            $contest_user = new ContestsUsers();
            $contest_user->setUserId($user_id);
            $contest_user->setContestId($contest_id);
            $contest_user->setAccessTime(date('Y-m-d H:i:s'));
            $contest_user->setScore(0);
            $contest_user->setTime(0);

            ContestsUsersDAO::save($contest_user);
        } elseif ($contest_user->getAccessTime() === '0000-00-00 00:00:00') {
            // If its set to default time, update it
            $contest_user->setAccessTime(date('Y-m-d H:i:s'));

            ContestsUsersDAO::save($contest_user);
        }

            return $contest_user;
    }
}
