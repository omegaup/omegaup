<?php

/**
 *  IdentityController
 *
 * @author juan.pablo
 */
class IdentityController extends Controller {
    /**
     * Given a username or a email, returns the identity object
     *
     * @param type $userOrEmail
     * @return Identity
     * @throws ApiException
     */
    public static function resolveIdentity($userOrEmail) {
        Validators::validateStringNonEmpty($userOrEmail, 'usernameOrEmail');
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
        }
    }

    /**
     * Entry point for Create an Identity API
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(Request $r) {
        global $experiments;
        $experiments->ensureEnabled(Experiments::IDENTITIES);
        $group = self::validateGroupOwnership($r);

        // Save objects into DB
        try {
            DAO::transBegin();

            // Prepare DAOs
            $identity = self::createIdentity(
                $r['username'],
                $r['name'],
                $r['password'],
                $r['country_id'],
                $r['state_id'],
                $r['gender'],
                $r['school_name'],
                $r['group_alias']
            );

            // Save in DB
            self::saveIdentityGroup($identity, $group->group_id);

            DAO::transEnd();
        } catch (ApiException $e) {
            DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
            'username' => $identity->username,
        ];
    }

    /**
     * Entry point for Create bulk Identities API
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws DuplicatedEntryInDatabaseException
     */
    public static function apiBulkCreate(Request $r) {
        global $experiments;
        $experiments->ensureEnabled(Experiments::IDENTITIES);
        $group = self::validateGroupOwnership($r);

        // Save objects into DB
        try {
            DAO::transBegin();

            foreach ($r['identities'] as $identity) {
                // Prepare DAOs
                $identity = self::createIdentity(
                    $identity['username'],
                    $identity['name'],
                    $identity['password'],
                    $identity['country_id'],
                    $identity['state_id'],
                    $identity['gender'],
                    $identity['school_name'],
                    $r['group_alias']
                );

                self::saveIdentityGroup($identity, $group->group_id);
            }

            DAO::transEnd();
        } catch (ApiException $e) {
            DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     * @param Request $r
     * @throws InvalidParameterException
     */
    private static function validateGroupOwnership(Request $r) {
        self::authenticateRequest($r);
        if (!Authorization::isGroupIdentityCreator($r->identity->identity_id)) {
            throw new ForbiddenAccessException('userNotAllowed');
        }
        $group = GroupController::validateGroup($r['group_alias'], $r->identity);
        if (!is_array($r['identities']) && (!isset($r['username']) && !isset($r['name']) && !isset($r['group_alias']))) {
            throw new InvalidParameterException('parameterInvalid', 'identities');
        }
        return $group;
    }

    private static function updateIdentity(
        $username,
        $name,
        $countryId,
        $stateId,
        $gender,
        $school,
        $aliasGroup,
        $originalIdentity
    ) {
        self::validateIdentity($username, $name, $gender, $aliasGroup);

        $state = SchoolController::getStateIdFromCountryAndState($countryId, $stateId);
        $schoolId = SchoolController::createSchool(trim($school), $state);

        return new Identities([
            'username' => $username,
            'name' => $name ?? $originalIdentity->name,
            'country_id' => !is_null($state) ? $state->country_id : $originalIdentity->country_id,
            'state_id' => !is_null($state) ? $state->state_id : $originalIdentity->state_id,
            'gender' => $gender ?? $originalIdentity->gender,
            'school_id' => $schoolId,
            'password' => $originalIdentity->password,
            'user_id' => $originalIdentity->user_id,
        ]);
    }

    /**
     * Save object Identities in DB, and add user into group.
     * This function is called inside a transaction.
     * @param Identities $identity
     * @param $groupId
     * @throws InvalidDatabaseOperationException
     */
    private static function saveIdentityGroup(Identities $identity, $groupId) {
        try {
            IdentitiesDAO::save($identity);

            GroupsIdentitiesDAO::save(new GroupsIdentities([
                'group_id' => $groupId,
                'identity_id' => $identity->identity_id,
            ]));
        } catch (Exception $e) {
            if (DAO::isDuplicateEntryException($e)) {
                throw new DuplicatedEntryInDatabaseException('aliasInUse', $e);
            } else {
                throw new InvalidDatabaseOperationException($e);
            }
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
        global $experiments;
        $experiments->ensureEnabled(Experiments::IDENTITIES);
        self::validateUpdateRequest($r);
        $originalIdentity = self::resolveIdentity($r['original_username']);

        // Prepare DAOs
        $identity = self::updateIdentity(
            $r['username'],
            $r['name'],
            $r['country_id'],
            $r['state_id'],
            $r['gender'],
            $r['school_name'],
            $r['group_alias'],
            $originalIdentity
        );

        $identity->identity_id = $originalIdentity->identity_id;

        // Save in DB
        IdentitiesDAO::save($identity);

        Cache::deleteFromCache(Cache::USER_PROFILE, $identity->username);

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
        global $experiments;
        $experiments->ensureEnabled(Experiments::IDENTITIES);
        self::validateUpdateRequest($r);
        $identity = self::resolveIdentity($r['username']);

        SecurityTools::testStrongPassword($r['password']);
        $identity->password = SecurityTools::hashString($r['password']);

        // Save object into DB
        try {
            // Update password
            IdentitiesDAO::save($identity);
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
        if (!Authorization::isGroupIdentityCreator($r->identity->identity_id)) {
            throw new ForbiddenAccessException('userNotAllowed');
        }
        GroupController::validateGroup($r['group_alias'], $r->identity);
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
        Validators::validateValidUsernameIdentity($username, 'username');

        if (!is_null($name)) {
            $name = trim($name);
            Validators::validateStringNonEmpty($name, 'name', true);
            Validators::validateStringOfLengthInRange($name, 'name', null, 50);
        }

        if (!is_null($gender)) {
            $gender = trim($gender);
        }
        if (!empty($gender)) {
            Validators::validateInEnum($gender, 'gender', UserController::ALLOWED_GENDER_OPTIONS, false);
        }
    }

    private static function createIdentity(
        $username,
        $name,
        $password,
        $countryId,
        $stateId,
        $gender,
        $school,
        $aliasGroup
    ) {
        self::validateIdentity($username, $name, $gender, $aliasGroup);

        $state = SchoolController::getStateIdFromCountryAndState($countryId, $stateId);
        $schoolId = SchoolController::createSchool(trim($school), $state);

        // Check password
        SecurityTools::testStrongPassword($password);
        $hashedPassword = SecurityTools::hashString($password);

        return new Identities([
            'username' => $username,
            'name' => $name,
            'password' => $hashedPassword,
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
    public static function getProfile(
        Request $r,
        ?Identities $identity,
        ?Users $user,
        bool $omitRank
    ) : array {
        if (is_null($identity)) {
            throw new InvalidParameterException('parameterNotFound', 'Identity');
        }

        $response = [];

        Cache::getFromCacheOrSet(
            Cache::USER_PROFILE,
            $identity->username,
            [$identity, $user],
            function (array $params) {
                [$identity, $user] = $params;
                if (!is_null($user)) {
                    return UserController::getProfileImpl($user, $identity);
                }
                return IdentityController::getProfileImpl($identity);
            },
            $response
        );

        if ($omitRank) {
            $response['userinfo']['rankinfo'] = [];
        } else {
            $response['userinfo']['rankinfo'] = UserController::getRankByProblemsSolved($r);
        }

        // Do not leak plain emails in case the request is for a profile other than
        // the logged identity's one. Admins can see emails
        if (!is_null($r->identity)
            && (Authorization::isSystemAdmin($r->identity->identity_id)
                || $identity->identity_id == $r->identity->identity_id)
        ) {
            return $response;
        }

        // Mentors can see current coder of the month email.
        if (!is_null($r->identity)
            && Authorization::canViewEmail($r->identity->identity_id)
            && CoderOfTheMonthDAO::isLastCoderOfTheMonth($identity->username)
        ) {
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
    private static function getProfileImpl(Identities $identity) {
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
                    'locale' => IdentityController::convertToSupportedLanguage($extendedProfile['locale']),
                ]
            ];
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
    }

    public static function convertToSupportedLanguage($lang) {
        switch ($lang) {
            case 'en':
            case 'en-us':
                return 'en';

            case 'es':
            case 'es-mx':
                return 'es';

            case 'pt':
            case 'pt-pt':
            case 'pt-br':
                return 'pt';

            case 'pseudo':
                return 'pseudo';
        }

        // Fallback to spanish.
        return 'es';
    }
}
