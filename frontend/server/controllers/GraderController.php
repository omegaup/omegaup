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
     * @param Request $r
     * @throws ForbiddenAccessException
     */
    private static function validateRequest(Request $r) {
        self::authenticateRequest($r);

        if (!Authorization::isSystemAdmin($r['current_identity_id'])) {
            throw new ForbiddenAccessException();
        }
    }

    /**
     * Calls to /status grader
     *
     * @param Request $r
     * @return array
     */
    public static function apiStatus(Request $r) {
        self::validateRequest($r);

        self::$log->debug('Getting grader /status');
        return [
            'status' => 'ok',
            'grader' => Grader::getInstance()->status(),
        ];
    }
}
