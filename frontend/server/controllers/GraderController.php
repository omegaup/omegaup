<?php

/**
 * Description of GraderController
 *
 * @author joemmanuel
 */
class GraderController extends Controller {
    /**
     * Validate requests for grader apis
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateRequest(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        if (!Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
    }

    /**
     * Calls to /status grader
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiStatus(\OmegaUp\Request $r) {
        self::validateRequest($r);

        self::$log->debug('Getting grader /status');
        return [
            'status' => 'ok',
            'grader' => Grader::getInstance()->status(),
        ];
    }
}
