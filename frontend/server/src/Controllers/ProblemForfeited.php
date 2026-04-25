<?php

 namespace OmegaUp\Controllers;

/**
 * ProblemForfeitedController
 */
class ProblemForfeited extends \OmegaUp\Controllers\Controller {
    public const SOLUTIONS_ALLOWED_TO_SEE_PER_DAY = 5;

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
            'allowed' => intval(static::SOLUTIONS_ALLOWED_TO_SEE_PER_DAY),
            'seen' => \OmegaUp\DAO\ProblemsForfeited::getProblemsForfeitedCountInDay(
                $r->user
            ),
        ];
    }
}
