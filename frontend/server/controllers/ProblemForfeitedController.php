<?php

/**
 * ProblemForfeitedController
 *
 * @author carlosabcs
 */
class ProblemForfeitedController extends Controller {
    // For each 10 solved problems, 1 solution is granted
    const SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION = 10;

    /**
     * Returns the number of solutions allowed
     * and the number of solutions already seen
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiGetCounts(Request $r) {
        self::authenticateRequest($r, true /* requireMainUserIdentity */);
        return [
            'status' => 'ok',
            'allowed' => intval(ProblemsDAO::getProblemsSolvedCount($r->identity) /
                                static::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION),
            'seen' => ProblemsForfeitedDAO::getProblemsForfeitedCount($r->user),
        ];
    }
}
