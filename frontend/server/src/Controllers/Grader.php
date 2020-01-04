<?php

 namespace OmegaUp\Controllers;

/**
 * Description of GraderController
 *
 * @author joemmanuel
 */
class Grader extends \OmegaUp\Controllers\Controller {
    /**
     * Calls to /status grader
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiStatus(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'grader' => \OmegaUp\Grader::getInstance()->status(),
        ];
    }
}
