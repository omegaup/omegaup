<?php

require_once('base/Users_Badges.dao.base.php');
require_once('base/Users_Badges.vo.base.php');
/** UsersBadges Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link UsersBadges }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class UsersBadgesDAO extends UsersBadgesDAOBase {
  const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';

  public static function getAllBadges() {
      $aliases = array_diff(scandir(static::OMEGAUP_BADGES_ROOT), ['..', '.', 'default_icon.svg']);
      $results = [];
      foreach ($aliases as $alias) {
        if (!is_dir(static::OMEGAUP_BADGES_ROOT . "/${alias}")) {
          $results[] = [
            "alias" => $alias,
            "assignation_time" => null,
          ];
        }
      }
      return $results;
  }

  public static function getAllBadgesForUser(str $userId) {
      global $conn;
      $sql = "SELECT ub.badge_alias, ub.assignation_time FROM Users_Badges WHERE ub.user_id = ?;";
      $args = [$userId];
      $rs = $conn->GetAll($sql, $args);
      $allBadges = self::getAllBadges();
      foreach ($allBadges as $badge) {
        if (in_array($badge["alias"], $rs["badge_alias"])) {
          $allBadges["assignation_time"] = $rs["assignation_time"];
        }
      }
      return $allBadges;
  }

  public static function userHasBadge(str $userId) {

  }
}
