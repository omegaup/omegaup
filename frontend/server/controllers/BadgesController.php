<?php
require_once 'libs/dao/User_Rank.dao.php';

/**
 * BadgesController
 *
 * @author carlosabcs
 */
 class BadgesController extends Controller {
    /**
     * Returns a list of existing badges
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiList(Request $r) {
        // Check who is visiting, a not logged user can still view
        // the list of badges but all in black.
        try {
            self::authenticateRequest($r);
        } catch (UnauthorizedException $e) {
            // Just show badges
            $badges = UsersBadgesDAO::getAllBadges();
            foreach ($badges as $badge) {
                $badge['assignation_date'] = null;
            }
            return [
                'total_badges' => sizeof($badges),
                'results' => $badges
            ];
        }
        // Hay que pasárselo así
        //  API.User.problemsSolved({username: user_profile.username})
        $user = self::resolveTargetUser($r);
        try {
            $badges = UsersBadgesDAO::getAllBadges();
            $owned_badges = UsersBadgesDAO::getBadgesOwnedByUser($user->user_id);
            // Acá hay que recorrer con un FOR todos los badges que se tienen y ponerles fecha de asignación distinta de null (la que corresponde según owned_badges)
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Returns a list of badges obtained by a user X
     * but being seen by user Y.
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiListProfileBadges(Request $r) {
      // Check who is visiting, a not logged user can still view
      // the list of badges but all in black.
      try {
          self::authenticateRequest($r);
      } catch (UnauthorizedException $e) {
          // Just show badges
          $badges = UsersBadgesDAO::getAllBadges();
          foreach ($badges as $badge) {
              $badge['assignation_date'] = null;
          }
          return [
              'total_badges' => sizeof($badges),
              'results' => $badges
          ];
      }
      // Hay que pasárselo así
      //  API.User.problemsSolved({username: user_profile.username})
      $user = self::resolveTargetUser($r);
      try {
          $badges = UsersBadgesDAO::getAllBadges();
          $owned_badges = UsersBadgesDAO::getBadgesOwnedByUser($user->user_id);
          // Acá hay que recorrer con un FOR todos los badges que se tienen y ponerles fecha de asignación distinta de null (la que corresponde según owned_badges)
      } catch (Exception $e) {
          throw $e;
      }
  }
 }
//TODO: Están pendientes de pasar: EL DAO, EL CONTROLLER, EL SCRIPT.