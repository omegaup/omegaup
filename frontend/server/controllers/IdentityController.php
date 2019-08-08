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
     * @param ?string $userOrEmail
     * @return Identities
     * @throws ApiException
     */
    public static function resolveIdentity(?string $userOrEmail) : Identities {
        Validators::validateStringNonEmpty($userOrEmail, 'usernameOrEmail');
        $identity = IdentitiesDAO::findByUsername($userOrEmail);
        if (!is_null($identity)) {
            return $identity;
        }
        $identity = IdentitiesDAO::FindByEmail($userOrEmail);
        if (!is_null($identity)) {
            return $identity;
        }
        throw new NotFoundException('userOrMailNotFound');
    }

    /**
     * Tests a if a password is valid for a given identity.
     *
     * @param Identities $identity    The identity.
     * @param string     $password    The password.
     * @return bool                   Whether the password is valid.
     * @throws LoginDisabledException When the identity is not allowed to login
     *                                using a password.
     */
    public static function testPassword(Identities $identity, string $password) : bool {
        if (is_null($identity->password)) {
            // The user had logged in through a third-party account.
            throw new LoginDisabledException('loginThroughThirdParty');
        }

        if (empty($identity->password)) {
            throw new LoginDisabledException('loginDisabled');
        }

        return SecurityTools::compareHashedStrings(
            $password,
            $identity->password
        );
    }

    /**
     * Entry point for Create an Identity API
     *
     * @param Request $r
     * @return array
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
        if (!Authorization::isGroupIdentityCreator($r->identity)) {
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
     */
    private static function saveIdentityGroup(Identities $identity, $groupId) {
        try {
            DAO::transBegin();

            IdentitiesDAO::create($identity);
            GroupsIdentitiesDAO::create(new GroupsIdentities([
                'group_id' => $groupId,
                'identity_id' => $identity->identity_id,
            ]));

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();
            if (DAO::isDuplicateEntryException($e)) {
                throw new DuplicatedEntryInDatabaseException('aliasInUse', $e);
            }
            throw $e;
        }
    }

    /**
     * Entry point for Update an Identity API
     *
     * @param Request $r
     * @return array
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
        IdentitiesDAO::update($identity);

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
            IdentitiesDAO::update($identity);
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
        if (!Authorization::isGroupIdentityCreator($r->identity)) {
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

        $response = Cache::getFromCacheOrSet(
            Cache::USER_PROFILE,
            $identity->username,
            function () use ($identity, $user) {
                if (!is_null($user)) {
                    return UserController::getProfileImpl($user, $identity);
                }
                return IdentityController::getProfileImpl($identity);
            }
        );

        if ($omitRank) {
            $response['userinfo']['rankinfo'] = [];
        } else {
            $response['userinfo']['rankinfo'] =
                UserController::getRankByProblemsSolved($r, $identity);
        }

        // Do not leak plain emails in case the request is for a profile other than
        // the logged identity's one. Admins can see emails
        if (!is_null($r->identity)
            && (Authorization::isSystemAdmin($r->identity)
                || $identity->identity_id == $r->identity->identity_id)
        ) {
            return $response;
        }

        // Mentors can see current coder of the month email.
        if (!is_null($r->identity)
            && Authorization::canViewEmail($r->identity)
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
     */
    private static function getProfileImpl(Identities $identity) {
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
    }

    /**
     * Returns the prefered language as a string (en,es,fra) of the identity given
     * If no identity is given, language is retrived from the browser.
     *
     * @return String
     */
    public static function getPreferredLanguage(Request $r) {
        // for quick debugging
        if (isset($_GET['lang'])) {
            return self::convertToSupportedLanguage($_GET['lang']);
        }

        try {
            $identity = self::resolveTargetIdentity($r);
            if (!is_null($identity) && !is_null($identity->language_id)) {
                $result = LanguagesDAO::getByPK($identity->language_id);
                if (is_null($result)) {
                    self::$log->warn('Invalid language id for identity');
                } else {
                    return IdentityController::convertToSupportedLanguage($result->name);
                }
            }
        } catch (NotFoundException $ex) {
            self::$log->debug($ex);
        } catch (InvalidParameterException $ex) {
            self::$log->debug($ex);
        }

        $langs = [];

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // break up string into pieces (languages and q factors)
            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

            if (count($lang_parse[1])) {
                // create a list like "en" => 0.8
                $langs = array_combine($lang_parse[1], $lang_parse[4]);

                // set default to 1 for any without q factor
                foreach ($langs as $lang => $val) {
                    if ($val === '') {
                        $langs[$lang] = 1;
                    }
                }

                // sort list based on value
                arsort($langs, SORT_NUMERIC);
            }
        }

        foreach ($langs as $langCode => $langWeight) {
            switch (substr($langCode, 0, 2)) {
                case 'en':
                    return 'en';

                case 'es':
                    return 'es';

                case 'pt':
                    return 'pt';
            }
        }

        // Fallback to spanish.
        return 'es';
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
