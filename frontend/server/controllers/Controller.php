<?php

/**
 * Controllers parent class
 *
 * @author joemmanuel
 */
class Controller {
    public static $log;

    /**
     * List of verdicts
     *
     * @var array
     */
    public static $verdicts = ['AC', 'PA', 'WA', 'TLE', 'MLE', 'OLE', 'RTE', 'RFE', 'CE', 'JE', 'NO-AC'];

    /**
     * Given the request, returns what user is performing the request by
     * looking at the auth_token, when requireMainUserIdentity flag is true, we
     * need to ensure that the request is made by the main identity of the
     * logged user
     *
     * @param Request $r
     * @param bool $requireMainUserIdentity
     * @throws InvalidDatabaseOperationException
     * @throws UnauthorizedException
     */
    protected static function authenticateRequest(
        Request $r,
        bool $requireMainUserIdentity = false
    ) {
        $r->user = null;
        $session = SessionController::apiCurrentSession($r)['session'];
        if (is_null($session['identity'])) {
            $r->user = null;
            $r->identity = null;
            throw new UnauthorizedException();
        }
        if (!is_null($session['user'])) {
            $r->user = $session['user'];
        }
        $r->identity = $session['identity'];
        if ($requireMainUserIdentity && (is_null($r->user) ||
            $r->user->main_identity_id != $r->identity->identity_id)
        ) {
            throw new ForbiddenAccessException();
        }
    }

    /**
     * Calls authenticateRequest and throws only if authentication fails AND
     * there's no target username in Request.
     * This is to allow unauthenticated access to APIs that work for both
     * current authenticated user and a targeted user (via $r["username"])
     *
     * @param Request $r
     */
    protected static function authenticateOrAllowUnauthenticatedRequest(Request $r) {
        try {
            self::authenticateRequest($r);
        } catch (UnauthorizedException $e) {
            // allow unauthenticated only if it has $r["username"]
            if (is_null($r['username'])) {
                throw $e;
            }
        }
    }

    /**
     * Resolves the target user for the API. If a username is provided in
     * the request, then we use that one. Otherwise, we use currently logged-in
     * user.
     *
     * Request must be authenticated before this function is called.
     *
     * @param Request $r
     * @return Users
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    protected static function resolveTargetUser(Request $r) {
        // By default use current user
        $user = $r->user;

        if (!is_null($r['username'])) {
            Validators::validateStringNonEmpty($r['username'], 'username');

            try {
                $user = UsersDAO::FindByUsername($r['username']);

                if (is_null($user)) {
                    throw new NotFoundException('userNotExist');
                }
            } catch (ApiException $e) {
                throw $e;
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }
        }

        return $user;
    }

    /**
     * Resolves the target identity for the API. If a username is provided in
     * the request, then we use that one. Otherwise, we use currently logged-in
     * identity.
     *
     * Request must be authenticated before this function is called.
     *
     * @param Request $r
     * @return Identity
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    protected static function resolveTargetIdentity(Request $r) {
        // By default use current identity
        $identity = $r->identity;

        if (is_null($r['username'])) {
            return $identity;
        }
        Validators::validateStringNonEmpty($r['username'], 'username');

        try {
            $identity = IdentitiesDAO::findByUsername($r['username']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($identity)) {
            throw new NotFoundException('userNotExist');
        }

        return $identity;
    }

    /**
     * Update properties of $object based on what is provided in $request.
     * $properties can have 'simple' and 'complex' properties.
     * - A simple property is just a name using underscores, and it's getter and setter methods should
     *   be the camel case version of the property name.
     * - An advanced property can have:
     *   > A getter/setter base name
     *   > A flag indicating it is important. Important properties are checked to determined if they
     *     really changed. For example: properties that should cause a problem to be rejudged,
     *     like time limits or memory constraints.
     *   > A transform method that takes the new property value stored in the request and transforms
     *     it into the proper form that should be stored in $object. For example:
     *     function($value) { return gmdate('Y-m-d H:i:s', $value); }
     *
     * @param Request $request
     * @param object $object
     * @param array $properties
     * @return bool True if there were changes to any property marked as 'important'.
     */
    protected static function updateValueProperties(
        Request $request,
        object $object,
        array $properties
    ) : bool {
        $importantChange = false;
        foreach ($properties as $source => $info) {
            if (is_int($source)) {
                // Simple property:
                $source = $info;
                $info = [$source];
            }
            if (is_null($request[$source])) {
                continue;
            }
            // Get the field name.
            if (isset($info[0])) {
                $field_name = $info[0];
            } else {
                $field_name = $source;
            }
            // Get or calculate new value.
            $value = $request[$source];
            if (isset($info[2]) || isset($info['transform'])) {
                $transform = isset($info[2]) ? $info[2] : $info['transform'];
                $value = $transform($value);
            }
            // Important property, so check if it changes.
            if (isset($info[1]) || isset($info['important'])) {
                $important = isset($info[1]) ? $info[1] : $info['important'];
                if ($important) {
                    if ($value != $object->$field_name) {
                        $importantChange = true;
                    }
                }
            }
            $object->$field_name = $value;
        }
        return $importantChange;
    }
}

Controller::$log = Logger::getLogger('controller');
