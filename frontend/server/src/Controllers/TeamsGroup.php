<?php

 namespace OmegaUp\Controllers;

/**
 *  TeamsGroupController
 *
 * @psalm-type Identity=array{classname?: string, country: null|string, country_id: null|string, gender: null|string, name: null|string, password?: string, school: null|string, school_id: int|null, school_name?: string, state: null|string, state_id: null|string, username: string}
 * @psalm-type Participant=array{country_id?: string, gender?: string, name?: string, password?: string, school_name?: string, state_id?: string, username: string, participant_username: string, participant_password?: string}
 * @psalm-type TeamMember=array{classname: string, isMainUserIdentity: bool, name: null|string, team_alias: string, team_name: null|string, username: string}
 * @psalm-type TeamGroupEditPayload=array{countries: list<\OmegaUp\DAO\VO\Countries>, identities: list<Identity>, isOrganizer: bool, maxNumberOfContestants: int, teamGroup: array{alias: string, description: null|string, name: null|string, numberOfContestants: int}, teamsMembers: list<TeamMember>}
 * @psalm-type TeamGroupNewPayload=array{numberOfContestants: int, maxNumberOfContestants: int}
 * @psalm-type TeamsGroup=array{alias: string, create_time: \OmegaUp\Timestamp, description: null|string, name: string}
 * @psalm-type TeamsGroupListPayload=array{teamsGroups: list<TeamsGroup>}
 * @psalm-type ListItem=array{key: string, value: string}
 */

class TeamsGroup extends \OmegaUp\Controllers\Controller {
    public const NUMBER_OF_CONTESTANTS = 3;
    public const MAX_NUMBER_OF_CONTESTANTS = 10;

    /**
     * Validate team group param
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function validateTeamGroupAndOwner(
        string $teamGroupAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ): ?\OmegaUp\DAO\VO\TeamGroups {
        $teamGroup = \OmegaUp\DAO\TeamGroups::getByAlias($teamGroupAlias);
        if (is_null($teamGroup)) {
            return null;
        }

        if (!\OmegaUp\Authorization::isTeamGroupAdmin($identity, $teamGroup)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        return $teamGroup;
    }

    /**
     * Details of a team group
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{team_group: array{create_time: int, alias: null|string, name: null|string, description: null|string}}
     *
     * @omegaup-request-param string $team_group_alias
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        $teamGroupAlias = $r->ensureString(
            'team_group_alias',
            fn (string $alias) => \OmegaUp\Validators::namespacedAlias($alias)
        );
        $teamGroup = self::validateTeamGroupAndOwner(
            $teamGroupAlias,
            $r->identity
        );
        if (is_null($teamGroup)) {
            throw new \OmegaUp\Exceptions\NotFoundException('groupNotFound');
        }

        /** @var array{create_time: int, alias: null|string, name: null|string, description: null|string} */
        $filteredTeamGroup = $teamGroup->asFilteredArray([
            'create_time',
            'alias',
            'name',
            'description',
        ]);
        return [
            'team_group' => $filteredTeamGroup,
        ];
    }

    /**
     * @return array{templateProperties: array{payload: TeamsGroupListPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getGroupListForTypeScript(\OmegaUp\Request $r): array {
        // Authenticate user
        $r->ensureMainUserIdentity();

        return [
            'templateProperties' => [
                'payload' => [
                    'teamsGroups' => \OmegaUp\DAO\TeamGroups::getAllTeamsGroupsAdminedByUser(
                        $r->user->user_id
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleTeamsGroups'
                ),
            ],
            'entrypoint' => 'teams_group_list',
        ];
    }

    /**
     * @return array{templateProperties: array{payload: TeamGroupNewPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getTeamGroupNewDetailsForTypeScript(
        \OmegaUp\Request $r
    ) {
        // Authenticate user
        $r->ensureMainUserIdentity();

        return [
            'templateProperties' => [
                'payload' => [
                    'numberOfContestants' => self::NUMBER_OF_CONTESTANTS,
                    'maxNumberOfContestants' => self::MAX_NUMBER_OF_CONTESTANTS,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleTeamsGroupNew'
                ),
            ],
            'entrypoint' => 'teams_group_new',
        ];
    }

    /**
     * @return array{templateProperties: array{payload: TeamGroupEditPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $team_group_alias
     */
    public static function getTeamGroupEditDetailsForTypeScript(
        \OmegaUp\Request $r
    ) {
        // Authenticate user
        $r->ensureMainUserIdentity();

        $teamGroupAlias = $r->ensureString(
            'team_group_alias',
            fn (string $alias) => \OmegaUp\Validators::namespacedAlias($alias)
        );
        $teamGroup = self::validateTeamGroupAndOwner(
            $teamGroupAlias,
            $r->identity
        );
        if (is_null($teamGroup) || is_null($teamGroup->team_group_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'team_group_alias'
            );
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'teamGroup' => [
                        'alias' => $teamGroupAlias,
                        'name' => $teamGroup->name,
                        'description' => $teamGroup->description,
                        'numberOfContestants' => $teamGroup->number_of_contestants,
                    ],
                    'maxNumberOfContestants' => self::MAX_NUMBER_OF_CONTESTANTS,
                    'countries' => \OmegaUp\DAO\Countries::getAll(
                        null,
                        100,
                        'name'
                    ),
                    'identities' => \OmegaUp\DAO\Teams::getTeamGroupIdentities(
                        $teamGroup
                    ),
                    'teamsMembers' => \OmegaUp\DAO\TeamUsers::getByTeamGroupId(
                        $teamGroup->team_group_id
                    )['teamsUsers'],
                    'isOrganizer' => \OmegaUp\Authorization::canCreateGroupIdentities(
                        $r->identity
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleTeamsGroupEdit'
                ),
            ],
            'entrypoint' => 'teams_group_edit',
        ];
    }

    /**
     * Utility function to create a new team group.
     */
    private static function createTeamGroup(
        string $alias,
        string $name,
        string $description,
        int $numberOfContestants,
        int $ownerUserId
    ): \OmegaUp\DAO\VO\TeamGroups {
        $teamGroup = new \OmegaUp\DAO\VO\TeamGroups([
            'alias' => $alias,
            'name' => $name,
            'description' => $description,
            'number_of_contestants' => $numberOfContestants,
        ]);
        $teamGroupAcl = new \OmegaUp\DAO\VO\ACLs(['owner_id' => $ownerUserId]);

        \OmegaUp\DAO\DAO::transBegin();

        try {
            \OmegaUp\DAO\ACLs::create($teamGroupAcl);
            $teamGroup->acl_id = $teamGroupAcl->acl_id;

            \OmegaUp\DAO\TeamGroups::create($teamGroup);

            self::$log->info("Team group {$alias} created.");

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

        return $teamGroup;
    }

    /**
     * New team group
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $alias
     * @omegaup-request-param string $description
     * @omegaup-request-param string $name
     * @omegaup-request-param int|null $numberOfContestants
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentityIsOver13();

        $teamGroupAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $name = $r->ensureString('name');
        $description = $r->ensureString('description');
        $numberOfContestants = $r->ensureOptionalInt(
            'numberOfContestants'
        ) ?? self::NUMBER_OF_CONTESTANTS;

        self::createTeamGroup(
            $teamGroupAlias,
            $name,
            $description,
            $numberOfContestants,
            $r->user->user_id
        );

        return ['status' => 'ok'];
    }

    /**
     * Update an existing teams group
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $alias
     * @omegaup-request-param string $description
     * @omegaup-request-param string $name
     * @omegaup-request-param int|null $numberOfContestants
     */
    public static function apiUpdate(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();

        $teamsGroupAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $teamsGroup = self::validateTeamGroupAndOwner(
            $teamsGroupAlias,
            $r->identity
        );
        if (is_null($teamsGroup)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'teams_group_alias'
            );
        }

        $teamsGroup->name = $r->ensureString('name');
        $teamsGroup->description = $r->ensureString('description');
        $teamsGroup->number_of_contestants = $r->ensureInt(
            'numberOfContestants',
            lowerBound: 1,
            upperBound: self::MAX_NUMBER_OF_CONTESTANTS,
        );
        \OmegaUp\DAO\TeamGroups::update($teamsGroup);
        self::$log->info(
            "Teams group {$teamsGroup->alias} updated successfully."
        );

        return ['status' => 'ok'];
    }

    /**
     * Teams of a teams group
     *
     * @return array{identities: list<Identity>}
     *
     * @omegaup-request-param string $team_group_alias
     */
    public static function apiTeams(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $teamGroupAlias = $r->ensureString(
            'team_group_alias',
            fn (string $alias) => \OmegaUp\Validators::namespacedAlias($alias)
        );
        $teamGroup = self::validateTeamGroupAndOwner(
            $teamGroupAlias,
            $r->identity
        );
        if (is_null($teamGroup)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'team_group_alias'
            );
        }

        return [
            'identities' => \OmegaUp\DAO\Teams::getTeamGroupIdentities(
                $teamGroup
            ),
        ];
    }

    /**
     * Remove team from teams group
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $team_group_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiRemoveTeam(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        $teamGroupAlias = $r->ensureString(
            'team_group_alias',
            fn (string $alias) => \OmegaUp\Validators::namespacedAlias($alias)
        );
        $teamGroup = self::validateTeamGroupAndOwner(
            $teamGroupAlias,
            $r->identity
        );
        if (is_null($teamGroup)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'team_group_alias'
            );
        }

        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r->ensureString('usernameOrEmail')
        );

        // Check team is actually in group
        $teams = \OmegaUp\DAO\Teams::getByTeamGroupIdAndIdentityId(
            intval($teamGroup->team_group_id),
            intval($resolvedIdentity->identity_id)
        );
        if (is_null($teams)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'User'
            );
        }

        \OmegaUp\DAO\Teams::delete($teams);
        self::$log->info("Removed {$resolvedIdentity->username}");

        return ['status' => 'ok'];
    }

    /**
     * Add one or more users to a given team
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $team_group_alias The username of the team.
     * @omegaup-request-param string $usernames Username of all members to add
     */
    public static function apiAddMembers(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();

        $teamUsername = $r->ensureString(
            'team_group_alias',
            fn (string $alias) => \OmegaUp\Validators::usernameOrTeamUsernameOrEmail(
                $alias
            )
        );
        $team = \OmegaUp\DAO\TeamGroups::getByTeamUsername($teamUsername);
        if (is_null($team)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'teams_group_alias'
            );
        }

        $teamsGroup = self::validateTeamGroupAndOwner(
            $team['alias'],
            $r->identity
        );
        if (is_null($teamsGroup) || is_null($teamsGroup->team_group_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'teams_group_alias'
            );
        }

        $identitiesUsernames = explode(',', $r->ensureString('usernames'));

        $teamUsers = \OmegaUp\DAO\TeamUsers::getByTeamGroupId(
            $teamsGroup->team_group_id
        )['teamsUsers'];

        $teamsUsersUsernames = array_map(
            fn ($teamUser) => $teamUser['username'],
            $teamUsers,
        );

        foreach ($identitiesUsernames as $identityUsername) {
            if (in_array($identityUsername, $teamsUsersUsernames)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'teamMemberUsernameInUse'
                );
            }
        }

        \OmegaUp\DAO\TeamUsers::createTeamUsersBulk(
            $team['team_id'],
            $identitiesUsernames
        );

        return ['status' => 'ok'];
    }

    /**
     * Gets a list of teams groups. This returns an array instead of an object
     * since it is used by typeahead.
     *
     * @omegaup-request-param null|string $query
     *
     * @return list<ListItem>
     */
    public static function apiList(\OmegaUp\Request $r): array {
        // Authenticate user
        $r->ensureMainUserIdentity();

        $query = $r->ensureString('query');

        return \OmegaUp\DAO\TeamGroups::findByNameOrAlias($query);
    }

    /**
     * Remove an existing team member of a teams group
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $team_group_alias The username of the team
     * @omegaup-request-param string $username The username of user to remove
     */
    public static function apiRemoveMember(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();

        $teamUsername = $r->ensureString(
            'team_group_alias',
            fn (string $alias) => \OmegaUp\Validators::usernameOrTeamUsernameOrEmail(
                $alias
            )
        );
        $team = \OmegaUp\DAO\TeamGroups::getByTeamUsername($teamUsername);
        if (is_null($team)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'teams_group_alias'
            );
        }

        $teamsGroup = self::validateTeamGroupAndOwner(
            $team['alias'],
            $r->identity
        );
        if (is_null($teamsGroup) || is_null($teamsGroup->team_group_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'teams_group_alias'
            );
        }

        $identity = \OmegaUp\DAO\Identities::findByUsername(
            $r->ensureString('username')
        );
        if (is_null($identity) || is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'identity_id'
            );
        }

        \OmegaUp\DAO\TeamUsers::delete(
            new \OmegaUp\DAO\VO\TeamUsers([
                'team_id' => $team['team_id'],
                'identity_id' => $identity->identity_id,
            ])
        );

        return ['status' => 'ok'];
    }

    /**
     * Get a list of team members of a teams group
     *
     * @return array{pageNumber: int, teamsUsers: list<TeamMember>, totalRows: int}
     *
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     * @omegaup-request-param string $team_group_alias The username of the team.
     */
    public static function apiTeamsMembers(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt('page_size') ?? 100;
        $teamGroupAlias = $r->ensureString(
            'team_group_alias',
            fn (string $alias) => \OmegaUp\Validators::namespacedAlias($alias)
        );
        $teamGroup = self::validateTeamGroupAndOwner(
            $teamGroupAlias,
            $r->identity
        );
        if (is_null($teamGroup) || is_null($teamGroup->team_group_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'team_group_alias'
            );
        }

        return \OmegaUp\DAO\TeamUsers::getByTeamGroupId(
            $teamGroup->team_group_id,
            $page,
            $pageSize
        );
    }
}
