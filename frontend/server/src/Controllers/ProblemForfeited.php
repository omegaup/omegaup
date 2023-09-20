<?php

 namespace OmegaUp\Controllers;

/**
 * ProblemForfeitedController
 */
class ProblemForfeited extends \OmegaUp\Controllers\Controller {
    // For each 10 solved problems, 1 solution is granted    
    const SOLUTIONS_ALLOWED_TO_SEE_PER_DAY = 5;

    /**
     * Returns the number of solutions allowed
     * and the number of solutions already seen
     *
     * @param \OmegaUp\Request $r
     * @return array{allowed: int, seen: int}
     */
    public static function apiGetCounts(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        $seen = \OmegaUp\DAO\ProblemsForfeited::getProblemsForfeitedCount(
            $r->user
        );
        $allowed = SOLUTIONS_ALLOWED_TO_SEE_PER_DAY;
        return [
            'allowed' => $allowed,
            'seen' => $seen,
        ];
    }
}
