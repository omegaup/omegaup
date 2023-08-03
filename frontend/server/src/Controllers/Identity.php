<?php

 namespace OmegaUp\Controllers;

/**
 *  IdentityController
 */
class Identity extends \OmegaUp\Controllers\Controller {
    /**
     * Given a username or a email, returns the identity object
     *
     * @throws \OmegaUp\Exceptions\ApiException
     */
    public static function resolveIdentity(
        ?string $userOrEmail
    ): \OmegaUp\DAO\VO\Identities {
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
        $exception = new \OmegaUp\Exceptions\NotFoundException(
            'userOrMailNotFound'
        );
        $exception->addCustomMessageToArray(
            'userEmail',
            $userOrEmail
        );
        throw $exception;
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
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{username: string}
     *
     * @omegaup-request-param null|string $country_id
     * @omegaup-request-param string $gender
     * @omegaup-request-param null|string $group_alias
     * @omegaup-request-param mixed $identities
     * @omegaup-request-param string $name
     * @omegaup-request-param string $password
     * @omegaup-request-param string $school_name
     * @omegaup-request-param null|string $state_id
     * @omegaup-request-param string $username
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        $group = self::validateGroupOwnership($r);
        if (is_null($group->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'groupNotFound'
            );
        }
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['username'],
            'username'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['name'],
            'name'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['password'],
            'password'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['gender'],
            'gender'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['school_name'],
            'school_name'
        );

        // Save objects into DB
        try {
            \OmegaUp\DAO\DAO::transBegin();

            // Prepare DAOs
            $countryId = $r->ensureOptionalString('country_id');
            $stateId = $r->ensureOptionalString('state_id');
            $identity = self::createIdentity(
                $r['username'],
                $r['name'],
                $r['password'],
                $countryId,
                $stateId,
                $r['gender'],
                $group->alias
            );

            $state = null;
            if (!is_null($countryId) && !is_null($stateId)) {
                $state = \OmegaUp\DAO\States::getByPK(
                    $countryId,
                    $stateId
                );
            }
            $schoolId = \OmegaUp\Controllers\School::createSchool(
                trim($r['school_name']),
                $state
            );

            // Save in DB
            \OmegaUp\DAO\Identities::create($identity);
            \OmegaUp\DAO\GroupsIdentities::create(
                new \OmegaUp\DAO\VO\GroupsIdentities([
                    'group_id' => intval($group->group_id),
                    'identity_id' => $identity->identity_id,
                ])
            );

            // Create IdentitySchool
            $identitySchool = new \OmegaUp\DAO\VO\IdentitiesSchools([
                'identity_id' => $identity->identity_id,
                'school_id' => $schoolId,
            ]);

            \OmegaUp\DAO\IdentitiesSchools::create($identitySchool);

            // Save current_identity_school_id on Identity
            $identity->current_identity_school_id = $identitySchool->identity_school_id;
            \OmegaUp\DAO\Identities::update($identity);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'username' => strval($identity->username),
        ];
    }

    /**
     * Entry point for Create bulk Identities API
     *
     * @return array{status: string}
     *
     * @omegaup-request-param null|string $group_alias
     * @omegaup-request-param string $identities
     * @omegaup-request-param mixed $name
     * @omegaup-request-param mixed $username
     */
    public static function apiBulkCreate(\OmegaUp\Request $r): array {
        $group = self::validateGroupOwnership($r);
        if (is_null($group->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'groupNotFound'
            );
        }

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['identities'],
            'identities'
        );

        /** @var list<array{country_id: string, gender: string, name: string, password: string, school_name: string, state_id: string, username: string}>|null $identities */
        $identities = json_decode($r['identities'], true);

        if (!is_array($identities)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'identities'
            );
        }
        /** @var array<string, bool> $seenUsernames */
        $seenUsernames = [];
        $duplicatedUsernames = [];
        foreach ($identities as $identity) {
            if (isset($seenUsernames[$identity['username']])) {
                $duplicatedUsernames[] = $identity['username'];
            }
            $seenUsernames[$identity['username']] = true;
        }

        if (!empty($duplicatedUsernames)) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInArrayException(
                'groupMemberUsernameInUse',
                'usernames',
                // Displaying only the first 20 duplicated usernames in order to
                // prevent the error message from ruining the UI
                duplicatedItemsInArray: array_slice($duplicatedUsernames, 0, 20)
            );
        }

        // Save objects into DB
        try {
            \OmegaUp\DAO\DAO::transBegin();

            foreach ($identities as $identity) {
                // Prepare DAOs
                $countryId = empty(
                    $identity['country_id']
                ) ? null : strval(
                    $identity['country_id']
                );
                $stateId = empty(
                    $identity['state_id']
                ) ? null : strval(
                    $identity['state_id']
                );
                $newIdentity = self::createIdentity(
                    $identity['username'],
                    $identity['name'],
                    $identity['password'],
                    $countryId,
                    $stateId,
                    $identity['gender'],
                    $group->alias
                );

                $school = null;
                $schoolId = null;
                if (isset($identity['school_name'])) {
                    $school = trim($identity['school_name']);
                }
                if (!empty($school)) {
                    $state = null;
                    if (!is_null($countryId) && !is_null($stateId)) {
                        $state = \OmegaUp\DAO\States::getByPK(
                            $countryId,
                            $stateId
                        );
                    }
                    $schoolId = \OmegaUp\Controllers\School::createSchool(
                        $school,
                        $state
                    );
                }

                self::saveIdentityGroupInsideTransaction(
                    $newIdentity,
                    $group
                );

                if (!is_null($schoolId)) {
                    // Create IdentitySchool
                    $identitySchool = new \OmegaUp\DAO\VO\IdentitiesSchools([
                        'identity_id' => $newIdentity->identity_id,
                        'school_id' => $schoolId,
                    ]);

                    \OmegaUp\DAO\IdentitiesSchools::create($identitySchool);

                    // Save current_identity_school_id on Identity
                    $newIdentity->current_identity_school_id = $identitySchool->identity_school_id;
                }

                \OmegaUp\DAO\Identities::update($newIdentity);
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

    /**
     * Entry point for Create bulk Identities for teams API
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $team_group_alias
     * @omegaup-request-param string $team_identities
     */
    public static function apiBulkCreateForTeams(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentityIsOver13();
        if (!\OmegaUp\Authorization::isGroupIdentityCreator($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        $teamGroupAlias = $r->ensureString(
            'team_group_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $teamGroup = \OmegaUp\Controllers\TeamsGroup::validateTeamGroupAndOwner(
            $teamGroupAlias,
            $r->identity
        );
        if (is_null($teamGroup)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'groupNotFound'
            );
        }

        if (is_null($teamGroup->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'groupNotFound'
            );
        }
        $encodedTeamIdentities = $r->ensureString('team_identities');

        /** @var list<array{country_id: string, gender: string, identityUsernames: list<array{password?: string, username: string}>|null, name: string, password: string, school_name: string, state_id: string, username: string, usernames: string}>|null $teamIdentities */
        $teamIdentities = json_decode($encodedTeamIdentities, true);
        if (!is_array($teamIdentities) || empty($teamIdentities)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'identities'
            );
        }
        /** @var array<string, bool> $seenUsernames */
        $seenUsernames = [];
        /** @var array<string, bool> $seenMemberUsernames */
        $seenMemberUsernames = [];
        foreach ($teamIdentities as $username => $teamIdentity) {
            if (isset($seenUsernames[$teamIdentity['username']])) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'teamAliasInUse'
                );
            }
            $seenUsernames[$teamIdentity['username']] = true;

            if ($teamIdentity['usernames'] == '') {
                $teamIdentities[$username]['identityUsernames'] = null;
                continue;
            }
            // When usernames are provided we need to avoid duplicated users in
            // different teams.
            /** @var list<array{password?: string, username: string}> */
            $identities = json_decode($teamIdentity['usernames'], true);
            if (
                count(
                    $identities
                ) > $teamGroup->number_of_contestants
            ) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'teamMemberExceededNumberOfContestants'
                );
            }
            foreach ($identities as $identityMember) {
                if (
                    isset(
                        $seenMemberUsernames[$identityMember['username']]
                    ) && !empty(
                        $identityMember['username']
                    )
                ) {
                    throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                        'teamMemberUsernameInUse'
                    );
                }
                $seenMemberUsernames[$identityMember['username']] = true;
            }
            $teamIdentities[$username]['identityUsernames'] = $identities;
        }

        // Save objects into DB
        try {
            \OmegaUp\DAO\DAO::transBegin();

            foreach ($teamIdentities as $teamIdentity) {
                // Prepare DAOs
                $countryId = empty(
                    $teamIdentity['country_id']
                ) ? null : strval(
                    $teamIdentity['country_id']
                );
                $stateId = empty(
                    $teamIdentity['state_id']
                ) ? null : strval(
                    $teamIdentity['state_id']
                );
                $newIdentity = self::createIdentityTeam(
                    $teamIdentity['username'],
                    $teamIdentity['name'],
                    $teamIdentity['password'],
                    $countryId,
                    $stateId,
                    $teamIdentity['gender'],
                    $teamGroup->alias
                );

                $state = null;
                if (!is_null($countryId) && !is_null($stateId)) {
                    $state = \OmegaUp\DAO\States::getByPK(
                        $countryId,
                        $stateId
                    );
                }
                $schoolId = \OmegaUp\Controllers\School::createSchool(
                    trim($teamIdentity['school_name']),
                    $state
                );

                $team = self::saveIdentityTeamInsideTransaction(
                    $newIdentity,
                    $teamGroup
                );

                if (
                    !is_null($teamIdentity['identityUsernames'])
                    && !is_null($team->team_id)
                ) {
                    /** @var list<array{password: string, username: string}> $selfGeneratedIdentities */
                    $selfGeneratedIdentities = array_filter(
                        $teamIdentity['identityUsernames'],
                        fn ($user) => isset(
                            $user['password']
                        )
                    );

                    foreach ($selfGeneratedIdentities as $selfGeneratedIdentity) {
                        $newIdentity = self::createIdentity(
                            $selfGeneratedIdentity['username'],
                            name: null,
                            password: $selfGeneratedIdentity['password'],
                            countryId: $countryId,
                            stateId: $stateId,
                            gender: 'decline',
                            aliasGroup: $teamGroup->alias
                        );

                        $preexistingIdentity = \OmegaUp\DAO\Identities::findByUsername(
                            $selfGeneratedIdentity['username']
                        );
                        if (is_null($preexistingIdentity)) {
                            \OmegaUp\DAO\Identities::create($newIdentity);
                        } else {
                            $preexistingIdentity->password = $newIdentity->password;

                            \OmegaUp\DAO\Identities::update(
                                $preexistingIdentity
                            );
                            $newIdentity = $preexistingIdentity;
                        }
                    }
                    \OmegaUp\DAO\TeamUsers::createTeamUsersBulk(
                        $team->team_id,
                        array_map(
                            /**
                             * @param array{password?: string, username: string} $user
                             * @return string
                             */
                            fn ($user) => $user['username'],
                            $teamIdentity['identityUsernames']
                        )
                    );
                }

                $preexistingIdentitySchool = \OmegaUp\DAO\IdentitiesSchools::getByIdentityAndSchoolId(
                    $newIdentity,
                    $schoolId
                );

                if (is_null($preexistingIdentitySchool)) {
                    // Create IdentitySchool
                    $identitySchool = new \OmegaUp\DAO\VO\IdentitiesSchools([
                        'identity_id' => $newIdentity->identity_id,
                        'school_id' => $schoolId,
                    ]);

                    \OmegaUp\DAO\IdentitiesSchools::create($identitySchool);
                } else {
                    $identitySchool = $preexistingIdentitySchool;
                }

                // Save current_identity_school_id on Identity
                $newIdentity->current_identity_school_id = $identitySchool->identity_school_id;
                \OmegaUp\DAO\Identities::update($newIdentity);
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

    private static function validateNameAndGenderIdentity(
        ?string &$name,
        ?string &$gender
    ): void {
        if (!is_null($name)) {
            $name = trim($name);
            \OmegaUp\Validators::validateLengthInRange(
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
                \OmegaUp\Controllers\User::ALLOWED_GENDER_OPTIONS
            );
        }
    }

    public static function validateIdentityTeam(
        ?string $username,
        ?string &$name,
        ?string &$gender,
        string $groupAlias
    ): void {
        // Validate request
        \OmegaUp\Validators::validateValidUsernameIdentityTeam(
            $username,
            'username'
        );

        // Check group is present
        $identityUsername = explode(':', $username);
        if (count($identityUsername) !== 3) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'username'
            );
        }
        $namespace = $identityUsername[0];
        $identityGroupAlias = $identityUsername[1];
        if ($identityGroupAlias !== $groupAlias || $namespace !== 'teams') {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'teams_group_alias'
            );
        }

        self::validateNameAndGenderIdentity($name, $gender);
    }

    /**
     * Entry point for Update an Identity team API
     *
     * @return array{status: string}
     *
     * @omegaup-request-param null|string $country_id
     * @omegaup-request-param string $gender
     * @omegaup-request-param string $group_alias
     * @omegaup-request-param mixed $identities
     * @omegaup-request-param string $name
     * @omegaup-request-param string $original_username
     * @omegaup-request-param string $school_name
     * @omegaup-request-param null|string $state_id
     * @omegaup-request-param string $username
     */
    public static function apiUpdateIdentityTeam(\OmegaUp\Request $r): array {
        self::validateUpdateRequest($r);
        $originalUsername = $r->ensureString('original_username');
        $username = $r->ensureString('username');
        $name = $r->ensureString('name');
        $gender = $r->ensureString('gender');
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $schoolName = $r->ensureString('school_name');

        $originalIdentity = self::resolveIdentity($originalUsername);

        $originalSchoolId = null;
        if (!is_null($originalIdentity->current_identity_school_id)) {
            $originalIdentitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $originalIdentity->current_identity_school_id
            );
            $originalSchoolId = !is_null(
                $originalIdentitySchool
            ) ? $originalIdentitySchool->school_id : null;
        }

        // Prepare DAOs
        $state = null;
        $countryId = $r->ensureOptionalString('country_id');
        $stateId = $r->ensureOptionalString('state_id');
        if (!is_null($countryId) && !is_null($stateId)) {
            $state = \OmegaUp\DAO\States::getByPK($countryId, $stateId);
        }
        self::validateIdentityTeam($username, $name, $gender, $groupAlias);
        $identity = self::updateIdentity(
            $username,
            $name,
            $state,
            $gender,
            $groupAlias,
            $originalIdentity
        );

        $identity->identity_id = $originalIdentity->identity_id;

        $schoolId = \OmegaUp\Controllers\School::createSchool(
            trim($schoolName),
            $state
        );

        if ($originalSchoolId !== $schoolId) {
            $newIdentitySchool = \OmegaUp\DAO\IdentitiesSchools::createNewSchoolForIdentity(
                $identity,
                schoolId: $schoolId,
                graduationDate: null,
            );
            $identity->current_identity_school_id = $newIdentitySchool->identity_school_id;
        }

        // Save in DB
        \OmegaUp\DAO\Identities::update($identity);

        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::USER_PROFILE,
            strval($identity->username)
        );

        return [
            'status' => 'ok',
        ];
    }

    private static function createIdentityTeam(
        ?string $username,
        ?string $name,
        string $password,
        ?string $countryId,
        ?string $stateId,
        ?string $gender,
        string $aliasGroup
    ): \OmegaUp\DAO\VO\Identities {
        self::validateIdentityTeam($username, $name, $gender, $aliasGroup);

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
        ]);
    }

    /**
     * @omegaup-request-param null|string $group_alias
     * @omegaup-request-param mixed $identities
     * @omegaup-request-param mixed $name
     * @omegaup-request-param mixed $username
     */
    private static function validateGroupOwnership(\OmegaUp\Request $r): \OmegaUp\DAO\VO\Groups {
        $r->ensureIdentity();
        if (!\OmegaUp\Authorization::isGroupIdentityCreator($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $group = \OmegaUp\Controllers\Group::validateGroupAndOwner(
            $groupAlias,
            $r->identity
        );
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'groupNotFound'
            );
        }
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
        ?string $username,
        ?string $name,
        ?\OmegaUp\DAO\VO\States $state,
        ?string $gender,
        string $aliasGroup,
        \OmegaUp\DAO\VO\Identities $originalIdentity
    ): \OmegaUp\DAO\VO\Identities {
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
            'current_identity_school_id' => $originalIdentity->current_identity_school_id,
            'password' => $originalIdentity->password,
            'user_id' => $originalIdentity->user_id,
        ]);
    }

    /**
     * Save object Identities in DB, and add user into group.
     * This function is expected to be called inside a transaction.
     */
    private static function saveIdentityGroupInsideTransaction(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Groups $group
    ): void {
        if (is_null($identity->username)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userNotExist'
            );
        }
        if (is_null($identity->country_id) && !is_null($identity->state_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidStateNeedsToBelongToCountry',
                $identity->username
            );
        } elseif (
            !is_null($identity->country_id)
            && !is_null($identity->state_id)
        ) {
            $countryStates = \OmegaUp\DAO\States::getByCountry(
                $identity->country_id
            );
            $states = array_map(
                /**
                 * @param \OmegaUp\DAO\VO\States $state
                 */
                fn ($state) => $state->state_id,
                $countryStates
            );
            if (!in_array($identity->state_id, $states)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalidStateDoesNotBelongToCountry',
                    $identity->username
                );
            }
        }
        $preexistingIdentity = \OmegaUp\DAO\Identities::findByUsername(
            $identity->username
        );
        if (is_null($preexistingIdentity)) {
            \OmegaUp\DAO\Identities::create($identity);
        } else {
            $identity->identity_id = $preexistingIdentity->identity_id;
            $identity->user_id = $preexistingIdentity->user_id;
        }
        \OmegaUp\DAO\GroupsIdentities::replace(
            new \OmegaUp\DAO\VO\GroupsIdentities([
                'group_id' => intval($group->group_id),
                'identity_id' => $identity->identity_id,
            ])
        );
    }

    /**
     * Save object Identities in DB, and add user into team group.
     * This function is expected to be called inside a transaction.
     */
    private static function saveIdentityTeamInsideTransaction(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\TeamGroups $teamGroup
    ): \OmegaUp\DAO\VO\Teams {
        if (is_null($identity->username)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userNotExist'
            );
        }
        if (is_null($identity->country_id) && !is_null($identity->state_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidStateNeedsToBelongToCountry',
                $identity->username
            );
        }
        if (
            !is_null($identity->country_id)
            && !is_null($identity->state_id)
        ) {
            $countryStates = \OmegaUp\DAO\States::getByCountry(
                $identity->country_id
            );
            $states = array_map(
                /**
                 * @param \OmegaUp\DAO\VO\States $state
                 */
                fn ($state) => $state->state_id,
                $countryStates
            );
            if (!in_array($identity->state_id, $states)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalidStateDoesNotBelongToCountry',
                    $identity->username
                );
            }
        }
        $preexistingIdentity = \OmegaUp\DAO\Identities::findByUsername(
            $identity->username
        );
        if (is_null($preexistingIdentity)) {
            \OmegaUp\DAO\Identities::create($identity);
        } else {
            $identity->identity_id = $preexistingIdentity->identity_id;
        }
        $preexistingTeam = \OmegaUp\DAO\Teams::getByTeamGroupIdAndIdentityId(
            intval($teamGroup->team_group_id),
            intval($identity->identity_id)
        );
        $team = new \OmegaUp\DAO\VO\Teams([
            'team_group_id' => $teamGroup->team_group_id,
            'identity_id' => $identity->identity_id,
        ]);
        if (is_null($preexistingTeam)) {
            \OmegaUp\DAO\Teams::create($team);
            return $team;
        }
        $team->team_id = $preexistingTeam->team_id;

        \OmegaUp\DAO\Teams::update($team);
        return $team;
    }

    /**
     * Entry point for Update an Identity API
     *
     * @return array{status: string}
     *
     * @omegaup-request-param null|string $country_id
     * @omegaup-request-param string $gender
     * @omegaup-request-param string $group_alias
     * @omegaup-request-param mixed $identities
     * @omegaup-request-param string $name
     * @omegaup-request-param string $original_username
     * @omegaup-request-param string $school_name
     * @omegaup-request-param null|string $state_id
     * @omegaup-request-param string $username
     */
    public static function apiUpdate(\OmegaUp\Request $r): array {
        self::validateUpdateRequest($r);
        $originalUsername = $r->ensureString('original_username');
        $username = $r->ensureString('username');
        $name = $r->ensureString('name');
        $gender = $r->ensureString('gender');
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['school_name'],
            'school_name'
        );

        $originalIdentity = self::resolveIdentity($originalUsername);

        $originalSchoolId = null;
        if (!is_null($originalIdentity->current_identity_school_id)) {
            $originalIdentitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $originalIdentity->current_identity_school_id
            );
            $originalSchoolId = !is_null(
                $originalIdentitySchool
            ) ? $originalIdentitySchool->school_id : null;
        }

        // Prepare DAOs
        $state = null;
        $countryId = $r->ensureOptionalString('country_id');
        $stateId = $r->ensureOptionalString('state_id');
        if (!is_null($countryId) && !is_null($stateId)) {
            $state = \OmegaUp\DAO\States::getByPK($countryId, $stateId);
        }
        self::validateIdentity($username, $name, $gender, $groupAlias);
        $identity = self::updateIdentity(
            $username,
            $name,
            $state,
            $gender,
            $groupAlias,
            $originalIdentity
        );

        $identity->identity_id = $originalIdentity->identity_id;

        $schoolId = \OmegaUp\Controllers\School::createSchool(
            trim($r['school_name']),
            $state
        );

        if ($originalSchoolId !== $schoolId) {
            $newIdentitySchool = \OmegaUp\DAO\IdentitiesSchools::createNewSchoolForIdentity(
                $identity,
                schoolId: $schoolId,
                graduationDate: null,
            );
            $identity->current_identity_school_id = $newIdentitySchool->identity_school_id;
        }

        // Save in DB
        \OmegaUp\DAO\Identities::update($identity);

        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::USER_PROFILE,
            strval($identity->username)
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Entry point for change passowrd of an identity
     *
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $group_alias
     * @omegaup-request-param mixed $identities
     * @omegaup-request-param mixed $name
     * @omegaup-request-param string $password
     * @omegaup-request-param string $username
     */
    public static function apiChangePassword(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['username'],
            'username'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['password'],
            'password'
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
     * Entry point for switching between associated identities for a user
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param null|string $auth_token
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiSelectIdentity(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        $usernameOrEmail = $r->ensureString(
            'usernameOrEmail',
            fn (string $username) => \OmegaUp\Validators::usernameOrTeamUsernameOrEmail(
                $username
            )
        );

        \OmegaUp\Controllers\Session::loginWithAssociatedIdentity(
            $r,
            $usernameOrEmail,
            $r->identity
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @param \OmegaUp\Request $r
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @return void
     *
     * @omegaup-request-param string $group_alias
     * @omegaup-request-param mixed $identities
     * @omegaup-request-param mixed $name
     * @omegaup-request-param mixed $username
     */
    private static function validateUpdateRequest(\OmegaUp\Request $r): void {
        $r->ensureIdentity();
        if (!\OmegaUp\Authorization::isGroupIdentityCreator($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Controllers\Group::validateGroupAndOwner(
            $groupAlias,
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
        ?string $username,
        ?string &$name,
        ?string &$gender,
        string $groupAlias
    ): void {
        // Validate request
        \OmegaUp\Validators::validateValidUsernameIdentity(
            $username,
            'username'
        );

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

        self::validateNameAndGenderIdentity($name, $gender);
    }

    private static function createIdentity(
        ?string $username,
        ?string $name,
        string $password,
        ?string $countryId,
        ?string $stateId,
        ?string $gender,
        string $aliasGroup
    ): \OmegaUp\DAO\VO\Identities {
        self::validateIdentity($username, $name, $gender, $aliasGroup);

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
        ]);
    }

    /**
     * Get identity profile from cache
     *
     * @return array{birth_date?: \OmegaUp\Timestamp|null, classname: null|string, country: null|string, country_id: null|string, email?: null|string, gender?: null|string, graduation_date: \OmegaUp\Timestamp|null, gravatar_92: null|string, has_competitive_objective?: bool|null, has_learning_objective?: bool|null, has_scholar_objective?: bool|null, has_teaching_objective?: bool|null, hide_problem_tags: bool, is_own_profile: bool, is_private: bool, locale: string, name: null|string, preferred_language: null|string, rankinfo: array{author_ranking: int|null, name: string|null, problems_solved: int|null, rank: int|null}, scholar_degree: null|string, school: null|string, school_id: int|null, state: null|string, state_id: null|string, username: null|string, verified: bool|null}
     */
    public static function getProfile(
        ?\OmegaUp\DAO\VO\Identities $loggedIdentity,
        \OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Users $user,
        bool $omitRank,
        string $category = 'all'
    ): array {
        if (is_null($identity->username)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'username'
            );
        }
        $response = \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::USER_PROFILE,
            $identity->username,
            /** @return array{birth_date?: \OmegaUp\Timestamp|null, classname: null|string, country: null|string, country_id: null|string, email?: null|string, gender?: null|string, graduation_date: \OmegaUp\Timestamp|null, gravatar_92: null|string, has_competitive_objective: bool|null, has_learning_objective: bool|null, has_scholar_objective: bool|null, has_teaching_objective: bool|null, hide_problem_tags: bool, is_own_profile: bool, is_private: bool, locale: string, name: null|string, preferred_language: null|string, scholar_degree: null|string, school: null|string, school_id: int|null, state: null|string, state_id: null|string, username: null|string, verified: bool|null} */
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
            $response['rankinfo'] = [
                'name' => null,
                'problems_solved' => null,
                'rank' => null,
                'author_ranking' => null,
            ];
        } else {
            $response['rankinfo'] =
                \OmegaUp\Controllers\User::getUserRankInfo(
                    $identity
                );
        }

        // Do not leak plain emails, birth dates, genders and user's objectives in case the request is for a profile other than
        // the logged identity's one. Admins can see emails, birth dates, genders and user's objectives
        if (
            !is_null($loggedIdentity)
            && (\OmegaUp\Authorization::isSystemAdmin($loggedIdentity)
                || $identity->identity_id === $loggedIdentity->identity_id)
        ) {
            $response['is_own_profile'] = true;
            return $response;
        }
        unset($response['birth_date']);
        unset($response['gender']);
        unset($response['has_learning_objective']);
        unset($response['has_teaching_objective']);
        unset($response['has_scholar_objective']);
        unset($response['has_competitive_objective']);

        // Mentors can see current coder of the month email.
        if (
            !is_null($loggedIdentity)
            && \OmegaUp\Authorization::canViewEmail($loggedIdentity)
            && \OmegaUp\DAO\CoderOfTheMonth::isLastCoderOfTheMonth(
                $identity->username,
                $category
            )
        ) {
            return $response;
        }
        unset($response['email']);
        return $response;
    }

    /**
     * Returns the profile of the identity given
     *
     * @return array{birth_date: \OmegaUp\Timestamp|null, classname: null|string, country: null|string, country_id: null|string, gender: null|string, graduation_date: \OmegaUp\Timestamp|null, gravatar_92: null, has_competitive_objective: bool|null, has_learning_objective: bool|null, has_scholar_objective: bool|null, has_teaching_objective: bool|null, hide_problem_tags: bool, is_own_profile: bool, is_private: true, locale: string, name: null|string, preferred_language: null, scholar_degree: null|string, school: null|string, school_id: int|null, state: null|string, state_id: null|string, username: null|string, verified: bool|null}
     */
    private static function getProfileImpl(\OmegaUp\DAO\VO\Identities $identity) {
        $extendedProfile = \OmegaUp\DAO\Identities::getExtendedProfileDataByPk(
            $identity->identity_id
        );
        if (is_null($extendedProfile)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $schoolId = null;
        if (!is_null($identity->current_identity_school_id)) {
            $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $identity->current_identity_school_id
            );
            if (!is_null($identitySchool)) {
                $schoolId = $identitySchool->school_id;
            }
        }

        return [
            'birth_date' => $extendedProfile['birth_date'],
            'classname' => $extendedProfile['classname'],
            'gender' => $extendedProfile['gender'],
            'graduation_date' => $extendedProfile['graduation_date'],
            'gravatar_92' => null,
            'has_competitive_objective' => $extendedProfile['has_competitive_objective'],
            'has_learning_objective' => $extendedProfile['has_learning_objective'],
            'has_scholar_objective' => $extendedProfile['has_scholar_objective'],
            'has_teaching_objective' => $extendedProfile['has_teaching_objective'],
            'hide_problem_tags' => $extendedProfile['hide_problem_tags'],
            'scholar_degree' => $extendedProfile['scholar_degree'],
            'verified' => $extendedProfile['verified'],
            'username' => $identity->username,
            'name' => $identity->name,
            'preferred_language' => null,
            'country' => $extendedProfile['country'],
            'country_id' => $identity->country_id,
            'state' => $extendedProfile['state'],
            'state_id' => $identity->state_id,
            'school' => $extendedProfile['school'],
            'school_id' => $schoolId,
            'is_private' => true,
            'locale' => \OmegaUp\Controllers\Identity::convertToSupportedLanguage(
                $extendedProfile['locale'] ?? ''
            ),
            'is_own_profile' => false,
        ];
    }

    /**
     * Returns the prefered language as a string (en,es,fra) of the identity given
     * If no identity is given, language is retrived from the browser.
     *
     * @omegaup-request-param null|string $lang
     */
    public static function getPreferredLanguage(
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\Request $request = null
    ): string {
        // for quick debugging
        $requestLanguage = (
            !is_null($request) ?
            $request->ensureOptionalString('lang') :
            null
        );
        if (!empty($requestLanguage)) {
            return self::convertToSupportedLanguage($requestLanguage);
        }
        $requestLang = \OmegaUp\Request::getRequestVar('lang');
        if (!empty($requestLang)) {
            return self::convertToSupportedLanguage($requestLang);
        }

        try {
            if (!is_null($identity) && !is_null($identity->language_id)) {
                $result = \OmegaUp\DAO\Languages::getByPK(
                    $identity->language_id
                );
                if (is_null($result) || is_null($result->name)) {
                    self::$log->warning('Invalid language id for identity');
                } else {
                    return \OmegaUp\Controllers\Identity::convertToSupportedLanguage(
                        $result->name
                    );
                }
            }
        } catch (\OmegaUp\Exceptions\NotFoundException $ex) {
            self::$log->debug(
                'convertToSupportedLanguage',
                ['exception' => $ex],
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $ex) {
            self::$log->debug(
                'convertToSupportedLanguage',
                ['exception' => $ex],
            );
        }

        /** @var array<string, float> */
        $langs = [];

        $acceptLanguage = \OmegaUp\Request::getServerVar(
            'HTTP_ACCEPT_LANGUAGE'
        );
        if (!empty($acceptLanguage)) {
            // break up string into pieces (languages and q factors)
            if (
                preg_match_all(
                    '/([a-z]{1,8}(?:-[a-z]{1,8})?)\s*(?:;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
                    $acceptLanguage,
                    $langParse
                ) !== false &&
                !empty($langParse[1])
            ) {
                // create a list like "en" => 0.8
                /** @var array{0: list<string>, 1: list<string>, 2: list<string>} $langParse */
                [$_, $lang, $q] = $langParse;
                $langs = array_combine($lang, $q);

                // set default to 1 for any without q factor
                foreach ($langs as $lang => $val) {
                    if ($val === '') {
                        $langs[$lang] = 1.0;
                    } else {
                        $langs[$lang] = floatval($val);
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

    public static function convertToSupportedLanguage(?string $lang): string {
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
