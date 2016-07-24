<?php

/**
 * Controllers parent class
 *
 * @author joemmanuel
 */
class Controller {
    // If we turn this into protected,
    // how are we going to initialize?
    public static $log;

    /**
     * List of verdicts
     *
     * @var array
     */
    public static $verdicts = array('AC', 'PA', 'WA', 'TLE', 'MLE', 'OLE', 'RTE', 'RFE', 'CE', 'JE', 'NO-AC');

    /**
     * Given the request, returns what user is performing the request by
     * looking at the auth_token
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     * @throws UnauthorizedException
     */
    protected static function authenticateRequest(Request $r) {
        $session = SessionController::apiCurrentSession($r);
        if (!$session['valid'] || $session['user'] == null) {
            throw new UnauthorizedException();
        }

        $r['current_user'] = $session['user'];
        $r['current_user_id'] = $session['user']->user_id;
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
        $user = $r['current_user'];

        if (!is_null($r['username'])) {
            Validators::isStringNonEmpty($r['username'], 'username');

            try {
                $user = UsersDAO::FindByUsername($r['username']);

                if (is_null($user)) {
                    throw new InvalidParameterException('parameterNotFound', 'Username');
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
     * Retunrs a random string of size $length
     *
     * @param string $length
     * @return string
     */
    public static function randomString($length) {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $str = '';
        $size = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $index = 0;

            if (function_exists('random_int')) {
                $index = random_int(0, $size - 1);
            } else {
                $index = mt_rand(0, $size - 1);
            }

            $str .= $chars[$index];
        }

        return $str;
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
     * @return boolean True if there were changes to any property marked as 'important'.
     */
    protected static function updateValueProperties($request, $object, $properties) {
        $importantChange = false;
        foreach ($properties as $source => $info) {
            if (is_int($source)) {
                // Simple property:
                $source = $info;
                $info = array($source);
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
