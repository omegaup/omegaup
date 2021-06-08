<?php

 namespace OmegaUp\Controllers;

/**
 *  TeamsGroupController
 *
 * @psalm-type Identity=array{classname?: string, country: null|string, country_id: null|string, gender: null|string, name: null|string, password?: string, school: null|string, school_id: int|null, school_name?: string, state: null|string, state_id: null|string, username: string}
 * @psalm-type TeamGroupEditPayload=array{countries: list<\OmegaUp\DAO\VO\Countries>, identities: list<Identity>, isOrganizer: bool, teamGroup: array{alias: string, description: null|string, name: null|string}}
 * @psalm-type TeamsGroup=array{alias: string, create_time: \OmegaUp\Timestamp, description: null|string, name: string}
 * @psalm-type TeamsGroupListPayload=array{teamsGroups: list<TeamsGroup>}
 */

class TeamsGroup extends \OmegaUp\Controllers\Controller {
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
     * @return array{smartyProperties: array{payload: TeamsGroupListPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getGroupListForTypeScript(\OmegaUp\Request $r): array {
        // Authenticate user
        $r->ensureMainUserIdentity();

        return [
            'smartyProperties' => [
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
     * @return array{smartyProperties: array{payload: TeamGroupEditPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
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
            'smartyProperties' => [
                'payload' => [
                    'teamGroup' => [
                        'alias' => $teamGroupAlias,
                        'name' => $teamGroup->name,
                        'description' => $teamGroup->description,
                    ],
                    'countries' => \OmegaUp\DAO\Countries::getAll(
                        null,
                        100,
                        'name'
                    ),
                    'identities' => \OmegaUp\DAO\Teams::getTeamGroupIdentities(
                        $teamGroup
                    ),
                    'isOrganizer' => \OmegaUp\Experiments::getInstance()->isEnabled(
                        \OmegaUp\Experiments::IDENTITIES
                    ) && \OmegaUp\Authorization::canCreateGroupIdentities(
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
    public static function createTeamGroup(
        string $alias,
        string $name,
        string $description,
        int $ownerUserId
    ): \OmegaUp\DAO\VO\TeamGroups {
        $teamGroup = new \OmegaUp\DAO\VO\TeamGroups([
            'alias' => $alias,
            'name' => $name,
            'description' => $description,
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
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();

        $teamGroupAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $name = $r->ensureString('name');
        $description = $r->ensureString('description');

        self::createTeamGroup(
            $teamGroupAlias,
            $name,
            $description,
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
        \OmegaUp\DAO\TeamGroups::update($teamsGroup);
        self::$log->info(
            "Teams group {$teamsGroup->alias} updated succesfully."
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
}
