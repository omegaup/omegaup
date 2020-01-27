<?php

 namespace OmegaUp\Controllers;

/**
 * ProblemForfeitedController
 *
 * @author carlosabcs
 */
class ProblemForfeited extends \OmegaUp\Controllers\Controller {
    // For each 10 solved problems, 1 solution is granted
    const SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION = 10;

    /**
     * Returns the number of solutions allowed
     * and the number of solutions already seen
     *
     * @param \OmegaUp\Request $r
     * @return array{allowed: int, seen: int}
     */
    public static function apiGetCounts(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        return [
            'allowed' => intval(
                \OmegaUp\DAO\Problems::getProblemsSolvedCount(
                    $r->identity
                ) /
                intval(static::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION)
            ),
            'seen' => \OmegaUp\DAO\ProblemsForfeited::getProblemsForfeitedCount(
                $r->user
            ),
        ];
    }
}
