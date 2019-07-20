<?php

/**
 * ProblemForfeitedController
 *
 * @author carlosabcs
 */
class ProblemForfeitedController extends Controller {
  /**
   * Returns problems solved count
   * and problems forfeited count
   *
   * @param Request $r
   * @return array
   * @throws InvalidDatabaseOperationException
   */
    public static function apiGetCounts(Request $r) {
        self::authenticateRequest($r);
        if (is_null($r->user) || is_null($r->identity)) {
            throw new NotFoundException('userNotExist');
        }
        return [
            'status' => 'ok',
            'allowed' => intval(ProblemsDAO::getProblemsSolvedCount($r->identity->identity_id) / 10),
            'seen' => ProblemsForfeitedDAO::getProblemsForfeitedCount($r->user->user_id),
        ];
    }
}
