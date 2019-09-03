<?php

namespace OmegaUp\Controllers;

/**
 * Controllers parent class
 *
 * @author joemmanuel
 */
class Controller {
    /** @var \Logger */
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
     * @param \OmegaUp\Request $r
     * @param bool $requireMainUserIdentity
     * @throws \OmegaUp\Exceptions\UnauthorizedException
     */
    protected static function authenticateRequest(
        \OmegaUp\Request $r,
        bool $requireMainUserIdentity = false
    ) : void {
        $r->user = null;
        $session = \OmegaUp\Controllers\Session::apiCurrentSession($r)['session'];
        if (is_null($session) || is_null($session['identity'])) {
            $r->user = null;
            $r->identity = null;
            throw new \OmegaUp\Exceptions\UnauthorizedException();
        }
        if (!is_null($session['user'])) {
            $r->user = $session['user'];
        }
        $r->identity = $session['identity'];
        if ($requireMainUserIdentity && (is_null($r->user) ||
            $r->user->main_identity_id != $r->identity->identity_id)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
    }

    /**
     * Calls authenticateRequest and throws only if authentication fails AND
     * there's no target username in Request.
     * This is to allow unauthenticated access to APIs that work for both
     * current authenticated user and a targeted user (via $r["username"])
     *
     * @param \OmegaUp\Request $r
     */
    protected static function authenticateOrAllowUnauthenticatedRequest(
        \OmegaUp\Request $r
    ) : void {
        try {
            self::authenticateRequest($r);
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
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
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    protected static function resolveTargetUser(
        \OmegaUp\Request $r
    ) : ?\OmegaUp\DAO\VO\Users {
        // By default use current user
        $user = $r->user;

        if (!is_null($r['username'])) {
            \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');
            $user = \OmegaUp\DAO\Users::FindByUsername($r['username']);
            if (is_null($user)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
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
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    protected static function resolveTargetIdentity(
        \OmegaUp\Request $r
    ) : ?\OmegaUp\DAO\VO\Identities {
        // By default use current identity
        $identity = $r->identity;

        if (!is_null($r['username'])) {
            \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');
            $identity = \OmegaUp\DAO\Identities::findByUsername($r['username']);
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
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
     * @param \OmegaUp\Request $request
     * @param object $object
     * @param array<int|string, string|array{transform?: callable(mixed):mixed, important?: bool}> $properties
     * @return bool True if there were changes to any property marked as 'important'.
     */
    protected static function updateValueProperties(
        \OmegaUp\Request $request,
        object $object,
        array $properties
    ) : bool {
        $importantChange = false;
        foreach ($properties as $source => $info) {
            /** @var null|callable(mixed):mixed */
            $transform = null;
            $important = false;
            if (is_int($source)) {
                $fieldName = $info;
            } else {
                $fieldName = $source;
                if (isset($info['transform']) && is_callable($info['transform'])) {
                    $transform = $info['transform'];
                }
                if (isset($info['important']) && $info['important'] === true) {
                    $important = $info['important'];
                }
            }
            if (is_null($request[$fieldName])) {
                continue;
            }
            // Get or calculate new value.
            /** @var mixed */
            $value = $request[$fieldName];
            if (!is_null($transform)) {
                /** @var mixed */
                $value = $transform($value);
            }
            // Important property, so check if it changes.
            if ($important) {
                $importantChange |= ($value != $object->$fieldName);
            }
            $object->$fieldName = $value;
        }
        return $importantChange;
    }
}

\OmegaUp\Controllers\Controller::$log = \Logger::getLogger('controller');
