<?php

 namespace OmegaUp\Controllers;

/**
 *  IdentityController
 *
 * @author juan.pablo
 */
class Identity extends \OmegaUp\Controllers\Controller {
    /**
     * Given a username or a email, returns the identity object
     *
     * @param ?string $userOrEmail
     * @return \OmegaUp\DAO\VO\Identities
     * @throws \OmegaUp\Exceptions\ApiException
     */
    public static function resolveIdentity(?string $userOrEmail): \OmegaUp\DAO\VO\Identities {
        \OmegaUp\Validators::validateStringNonEmpty(
            $userOrEmail,
            'usernameOrEmail'
        );
        $identity = \OmegaUp\DAO\Identities::findByUsername($userOrEmail);
        if (!is_null($identity)) {
            return $identity;
        }
        $identity = \OmegaUp\DAO\Identities::findByEmail($userOrEmail);
        if (!is_null($identity)) {
            return $identity;
        }
        throw new \OmegaUp\Exceptions\NotFoundException('userOrMailNotFound');
    }

    /**
     * Tests a if a password is valid for a given identity.
     */
    public static function testPassword(
        \OmegaUp\DAO\VO\Identities $identity,
        string $password
    ): bool {
        if (is_null($identity->password)) {
            // The user had logged in through a third-party account.
            throw new \OmegaUp\Exceptions\LoginDisabledException(
                'loginThroughThirdParty'
            );
        }

        if (empty($identity->password)) {
            throw new \OmegaUp\Exceptions\LoginDisabledException(
                'loginDisabled'
            );
        }

        return \OmegaUp\SecurityTools::compareHashedStrings(
            $password,
            $identity->password
        );
    }

    /**
     * Entry point for Create an Identity API
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        \OmegaUp\Experiments::getInstance()->ensureEnabled(
            \OmegaUp\Experiments::IDENTITIES
        );
        $group = self::validateGroupOwnership($r);

        // Save objects into DB
        try {
            \OmegaUp\DAO\DAO::transBegin();

            // Prepare DAOs
            $identity = self::createIdentity(
                $r['username'],
                $r['name'],
                $r['password'],
                is_null($r['country_id']) ? null : strval($r['country_id']),
                is_null($r['state_id']) ? null : strval($r['state_id']),
                $r['gender'],
                $r['school_name'],
                $r['group_alias']
            );

            // Save in DB
            self::saveIdentityGroup($identity, $group->group_id);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
            'username' => $identity->username,
        ];
    }

    /**
     * Entry point for Create bulk Identities API
     */
    public static function apiBulkCreate(\OmegaUp\Request $r): array {
        \OmegaUp\Experiments::getInstance()->ensureEnabled(
            \OmegaUp\Experiments::IDENTITIES
        );
        $group = self::validateGroupOwnership($r);

        // Save objects into DB
        try {
            \OmegaUp\DAO\DAO::transBegin();

            /** @var array<string, string> $identity */
            foreach ($r['identities'] as $identity) {
                // Prepare DAOs
                $identity = self::createIdentity(
                    $identity['username'],
                    $identity['name'],
                    $identity['password'],
                    empty(
                        $identity['country_id']
                    ) ? null : strval(
                        $identity['country_id']
                    ),
                    empty(
                        $identity['state_id']
                    ) ? null : strval(
                        $identity['state_id']
                    ),
                    $identity['gender'],
                    $identity['school_name'],
                    $r['group_alias']
                );

                self::saveIdentityGroup($identity, $group->group_id);
            }

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok'
        ];
    }

    private static function validateGroupOwnership(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        if (!\OmegaUp\Authorization::isGroupIdentityCreator($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        $group = \OmegaUp\Controllers\Group::validateGroup(
            $r['group_alias'],
            $r->identity
        );
        if (
            !is_array($r['identities']) &&
            !isset($r['username']) &&
            !isset($r['name']) &&
            !isset($r['group_alias'])
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'identities'
            );
        }
        return $group;
    }

    private static function updateIdentity(
        $username,
        $name,
        ?string $countryId,
        ?string $stateId,
        $gender,
        $school,
        $aliasGroup,
        $originalIdentity
    ) {
        self::validateIdentity($username, $name, $gender, $aliasGroup);

        $state = \OmegaUp\Controllers\School::getStateIdFromCountryAndState(
            $countryId,
            $stateId
        );
        $schoolId = \OmegaUp\Controllers\School::createSchool(
            trim(
                $school
            ),
            $state
        );

        return new \OmegaUp\DAO\VO\Identities([
            'username' => $username,
            'name' => $name ?? $originalIdentity->name,
            'country_id' => !is_null(
                $state
            ) ? $state->country_id : $originalIdentity->country_id,
            'state_id' => !is_null(
                $state
            ) ? $state->state_id : $originalIdentity->state_id,
            'gender' => $gender ?? $originalIdentity->gender,
            'school_id' => $schoolId,
            'password' => $originalIdentity->password,
            'user_id' => $originalIdentity->user_id,
        ]);
    }

    /**
     * Save object Identities in DB, and add user into group.
     * This function is called inside a transaction.
     */
    private static function saveIdentityGroup(
        \OmegaUp\DAO\VO\Identities $identity,
        $groupId
    ) {
        try {
            \OmegaUp\DAO\DAO::transBegin();

            \OmegaUp\DAO\Identities::create($identity);
            \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
                'group_id' => $groupId,
                'identity_id' => $identity->identity_id,
            ]));

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'aliasInUse',
                    $e
                );
            }
            throw $e;
        }
    }

    /**
     * Entry point for Update an Identity API
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiUpdate(\OmegaUp\Request $r) {
        \OmegaUp\Experiments::getInstance()->ensureEnabled(
            \OmegaUp\Experiments::IDENTITIES
        );
        self::validateUpdateRequest($r);
        $originalIdentity = self::resolveIdentity($r['original_username']);

        // Prepare DAOs
        $identity = self::updateIdentity(
            $r['username'],
            $r['name'],
            is_null($r['country_id']) ? null : strval($r['country_id']),
            is_null($r['state_id']) ? null : strval($r['state_id']),
            $r['gender'],
            $r['school_name'],
            $r['group_alias'],
            $originalIdentity
        );

        $identity->identity_id = $originalIdentity->identity_id;

        // Save in DB
        \OmegaUp\DAO\Identities::update($identity);

        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::USER_PROFILE,
            $identity->username
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Entry point for change passowrd of an identity
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public static function apiChangePassword(\OmegaUp\Request $r) {
        \OmegaUp\Experiments::getInstance()->ensureEnabled(
            \OmegaUp\Experiments::IDENTITIES
        );
        self::validateUpdateRequest($r);
        $identity = self::resolveIdentity($r['username']);

        \OmegaUp\SecurityTools::testStrongPassword($r['password']);
        $identity->password = \OmegaUp\SecurityTools::hashString(
            $r['password']
        );

        // Save object into DB
        try {
            // Update password
            \OmegaUp\DAO\Identities::update($identity);
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateUpdateRequest(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        if (!\OmegaUp\Authorization::isGroupIdentityCreator($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        \OmegaUp\Controllers\Group::validateGroup(
            $r['group_alias'],
            $r->identity
        );
        if (
            !is_array($r['identities']) &&
            !isset($r['username']) &&
            !isset($r['name']) &&
            !isset($r['group_alias'])
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'identities'
            );
        }
    }

    public static function validateIdentity(
        $username,
        &$name,
        &$gender,
        $groupAlias
    ) {
        // Check group is present
        $identityUsername = explode(':', $username);
        if (count($identityUsername) != 2) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'username'
            );
        }
        $identityGroupAlias = $identityUsername[0];
        if ($identityGroupAlias != $groupAlias) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'group_alias'
            );
        }
        // Validate request
        \OmegaUp\Validators::validateValidUsernameIdentity(
            $username,
            'username'
        );

        if (!is_null($name)) {
            /** @var null|string $name */
            $name = trim($name);
            \OmegaUp\Validators::validateStringOfLengthInRange(
                $name,
                'name',
                1,
                50
            );
        }

        if (!is_null($gender)) {
            $gender = trim($gender);
        }
        if (!empty($gender)) {
            \OmegaUp\Validators::validateInEnum(
                $gender,
                'gender',
                \OmegaUp\Controllers\User::ALLOWED_GENDER_OPTIONS,
                false
            );
        }
    }

    private static function createIdentity(
        $username,
        $name,
        $password,
        ?string $countryId,
        ?string $stateId,
        $gender,
        $school,
        $aliasGroup
    ) {
        self::validateIdentity($username, $name, $gender, $aliasGroup);

        $state = \OmegaUp\Controllers\School::getStateIdFromCountryAndState(
            $countryId,
            $stateId
        );
        $schoolId = \OmegaUp\Controllers\School::createSchool(
            trim(
                $school
            ),
            $state
        );

        // Check password
        \OmegaUp\SecurityTools::testStrongPassword($password);
        $hashedPassword = \OmegaUp\SecurityTools::hashString($password);

        return new \OmegaUp\DAO\VO\Identities([
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
     * @param \OmegaUp\Request $r
     * @param array $response
     * @param \OmegaUp\Request $r
     * @return type
     */
    public static function getProfile(
        \OmegaUp\Request $r,
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Users $user,
        bool $omitRank
    ): array {
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'Identity'
            );
        }

        $response = \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::USER_PROFILE,
            $identity->username,
            function () use ($identity, $user) {
                if (!is_null($user)) {
                    return \OmegaUp\Controllers\User::getProfileImpl(
                        $user,
                        $identity
                    );
                }
                return \OmegaUp\Controllers\Identity::getProfileImpl($identity);
            }
        );

        if ($omitRank) {
            $response['userinfo']['rankinfo'] = [];
        } else {
            $response['userinfo']['rankinfo'] =
                \OmegaUp\Controllers\User::getRankByProblemsSolved(
                    $r,
                    '',
                    1,
                    100,
                    $identity
                );
        }

        // Do not leak plain emails in case the request is for a profile other than
        // the logged identity's one. Admins can see emails
        if (
            !is_null($r->identity)
            && (\OmegaUp\Authorization::isSystemAdmin($r->identity)
                || $identity->identity_id == $r->identity->identity_id)
        ) {
            return $response;
        }

        // Mentors can see current coder of the month email.
        if (
            !is_null($r->identity)
            && \OmegaUp\Authorization::canViewEmail($r->identity)
            && \OmegaUp\DAO\CoderOfTheMonth::isLastCoderOfTheMonth(
                $identity->username
            )
        ) {
            return $response;
        }
        unset($response['userinfo']['email']);
        return $response;
    }

    /**
     * Returns the profile of the identity given
     *
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @return array
     */
    private static function getProfileImpl(\OmegaUp\DAO\VO\Identities $identity) {
        $extendedProfile = \OmegaUp\DAO\Identities::getExtendedProfileDataByPk(
            $identity->identity_id
        );

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
                'locale' => \OmegaUp\Controllers\Identity::convertToSupportedLanguage(
                    $extendedProfile['locale']
                ),
            ]
        ];
    }

    /**
     * Returns the prefered language as a string (en,es,fra) of the identity given
     * If no identity is given, language is retrived from the browser.
     */
    public static function getPreferredLanguage(\OmegaUp\Request $r): string {
        // for quick debugging
        if (isset($_GET['lang'])) {
            return self::convertToSupportedLanguage($_GET['lang']);
        }

        try {
            $identity = self::resolveTargetIdentity($r);
            if (!is_null($identity) && !is_null($identity->language_id)) {
                $result = \OmegaUp\DAO\Languages::getByPK(
                    $identity->language_id
                );
                if (is_null($result)) {
                    self::$log->warn('Invalid language id for identity');
                } else {
                    return \OmegaUp\Controllers\Identity::convertToSupportedLanguage(
                        $result->name
                    );
                }
            }
        } catch (\OmegaUp\Exceptions\NotFoundException $ex) {
            self::$log->debug($ex);
        } catch (\OmegaUp\Exceptions\InvalidParameterException $ex) {
            self::$log->debug($ex);
        }

        $langs = [];

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // break up string into pieces (languages and q factors)
            preg_match_all(
                '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
                $_SERVER['HTTP_ACCEPT_LANGUAGE'],
                $lang_parse
            );

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
