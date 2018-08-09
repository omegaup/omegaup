<?php

/**
 *  IdentityController
 *
 * @author juan.pablo
 */
class IdentityController extends Controller {
    public static function convertFromUser(Users $user) {
        return IdentitiesDAO::save(new Identities([
            'identity_id' => $user->main_identity_id,
            'username' => $user->username,
            'password' => $user->password,
            'name' => $user->name,
            'user_id' => $user->user_id,
            'language_id' => $user->language_id,
            'country_id' => $user->country_id,
            'state_id' => $user->state_id,
            'school_id' => $user->school_id
        ]));
    }

    /**
     * Given a username or a email, returns the identity object
     *
     * @param type $userOrEmail
     * @return Identity
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     */
    public static function resolveIdentity($userOrEmail) {
        Validators::isStringNonEmpty($userOrEmail, 'usernameOrEmail');
        try {
            $identity = IdentitiesDAO::FindByEmail($userOrEmail);
            if (!is_null($identity)) {
                return $identity;
            }

            $identity = IdentitiesDAO::FindByUsername($userOrEmail);
            if (!is_null($identity)) {
                return $identity;
            }
            throw new NotFoundException('userOrMailNotFound');
        } catch (ApiException $apiException) {
            throw $apiException;
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * Entry point for Update an Identity API
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiUpdate(Request $r) {
        self::validateUpdateRequest($r);
        $original_identity = self::resolveIdentity($r['username']);

        // Prepare DAOs
        $identity = self::createIdentity(
            $r['username'],
            $r['name'],
            $r['country_id'],
            $r['state_id'],
            $r['gender'],
            $r['school_name'],
            $r['group_alias']
        );

        $identity->identity_id = $original_identity->identity_id;

        // Save in DB
        IdentitiesDAO::save($identity);

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Entry point for change passowrd of an identity
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws DuplicatedEntryInDatabaseException
     */
    public static function apiChangePassword(Request $r) {
        self::validateUpdateRequest($r);
        $identity = self::resolveIdentity($r['username']);

        SecurityTools::testStrongPassword($r['password']);
        $hashedPassword = SecurityTools::hashString($r['password']);

        // Save object into DB
        try {
            // Update password
            IdentitiesDAO::save(new Identities([
                'identity_id' => $identity->identity_id,
                'password' => $hashedPassword,
            ]));
        } catch (ApiException $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @param Request $r
     * @throws InvalidParameterException
     */
    private static function validateUpdateRequest(Request $r) {
        self::authenticateRequest($r);
        if (!Authorization::isGroupIdentityCreator($r['current_identity_id'])) {
            throw new ForbiddenAccessException();
        }
        GroupController::validateGroup($r);
        if (!is_array($r['identities']) && (!isset($r['username']) && !isset($r['name']) && !isset($r['group_alias']))) {
            throw new InvalidParameterException('parameterInvalid', 'identities');
        }
    }

    public static function validateIdentity($username, &$name, &$gender, $groupAlias) {
        // Check group is present
        $identityUsername = explode(':', $username);
        if (count($identityUsername) != 2) {
            throw new InvalidParameterException('parameterInvalid', 'username');
        }
        $identityGroupAlias = $identityUsername[0];
        if ($identityGroupAlias != $groupAlias) {
            throw new InvalidParameterException('parameterInvalid', 'group_alias');
        }
        // Validate request
        Validators::isValidUsernameIdentity($identityUsername[1], 'username');

        if (!is_null($name)) {
            $name = trim($name);
            Validators::isStringNonEmpty($name, 'name', true);
            Validators::isStringOfMaxLength($name, 'name', 50);
        }

        if (!is_null($gender)) {
            $gender = trim($gender);
        }
        if (!empty($gender)) {
            Validators::isInEnum($gender, 'gender', UserController::ALLOWED_GENDER_OPTIONS, false);
        }
    }

    private static function createIdentity(
        $username,
        $name,
        $countryId,
        $stateId,
        $gender,
        $school,
        $aliasGroup
    ) {
        self::validateIdentity($username, $name, $gender, $aliasGroup);

        $state = SchoolController::getStateIdFromCountryAndState($countryId, $stateId);
        $schoolId = SchoolController::createSchool(trim($school), $state);

        return new Identities([
            'username' => $username,
            'name' => $name,
            'country_id' => $countryId,
            'state_id' => $stateId,
            'gender' => $gender,
            'school_id' => $schoolId,
        ]);
    }

    /**
     * Get identity profile from cache
     * Requires $r["identity"] to be an actual Identity
     *
     * @param Request $r
     * @param array $response
     * @param Request $r
     * @return type
     */
    public static function getProfile(Request $r) {
        if (is_null($r['identity'])) {
            throw new InvalidParameterException('parameterNotFound', 'Identity');
        }

        $response = [];

        Cache::getFromCacheOrSet(
            Cache::USER_PROFILE,
            $r['identity']->username,
            $r,
            function (Request $r) {
                if (!is_null($r['user'])) {
                    return UserController::getProfileImpl($r['user'], $r['identity']);
                }
                return IdentityController::getProfileImpl($r['identity']);
            },
            $response
        );

        if (!empty($r['omit_rank'])) {
            $response['userinfo']['rankinfo'] = UserController::getRankByProblemsSolved($r);
        } else {
            $response['userinfo']['rankinfo'] = [];
        }

        // Do not leak plain emails in case the request is for a profile other than
        // the logged identity's one. Admins can see emails
        if (Authorization::isSystemAdmin($r['current_identity_id'])
              || $r['identity']->identity_id == $r['current_identity_id']) {
            return $response;
        }

        // Mentors can see current coder of the month email.
        if (Authorization::canViewEmail($r['current_identity_id']) &&
              CoderOfTheMonthDAO::isLastCoderOfTheMonth($r['identity']->username)) {
            return $response;
        }
        unset($response['userinfo']['email']);
        return $response;
    }

    /**
     * Returns the profile of the identity given
     *
     * @param Identities $identity
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function getProfileImpl(Identities $identity) {
        try {
            $extendedProfile = IdentitiesDAO::getExtendedProfileDataByPk($identity->identity_id);

            return [
                'userinfo' => [
                    'username' => $identity->username,
                    'name' => $identity->name,
                    'preferred_language' => null,
                    'country' => $extendedProfile['country'],
                    'country_id' => $identity->country_id,
                    'state' => $extendedProfile['state'],
                    'state_id' => $identity->state_id,
                    'school' => $extendedProfile['school'],
                    'school_id' => $identity->school_id,
                    'is_private' => true,
                    'locale' => UserController::convertToSupportedLanguage($extendedProfile['locale']),
                ]
            ];
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
    }
}
