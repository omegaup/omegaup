<?php

 namespace OmegaUp\Controllers;

/**
 *  GroupController
 *
 * @author joemmanuel
 */

class Group extends \OmegaUp\Controllers\Controller {
    /**
     * Utility function to create a new group.
     */
    public static function createGroup(
        string $alias,
        string $name,
        string $description,
        int $ownerId
    ): \OmegaUp\DAO\VO\Groups {
        $group = new \OmegaUp\DAO\VO\Groups([
            'alias' => $alias,
            'name' => $name,
            'description' => $description,
        ]);
        $groupAcl = new \OmegaUp\DAO\VO\ACLs([
            'owner_id' => $ownerId,
        ]);

        \OmegaUp\DAO\DAO::transBegin();

        try {
            \OmegaUp\DAO\ACLs::create($groupAcl);
            $group->acl_id = $groupAcl->acl_id;

            \OmegaUp\DAO\Groups::create($group);

            self::$log->info("Group {$alias} created.");

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

        return $group;
    }

    /**
     * New group
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{status: string}
     *
     * @omegaup-request-param null|string $alias
     * @omegaup-request-param string $description
     * @omegaup-request-param string $name
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();

        $groupAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name');
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['description'],
            'description'
        );

        self::createGroup(
            $groupAlias,
            $r['name'],
            $r['description'],
            $r->user->user_id
        );

        return ['status' => 'ok'];
    }

    /**
     * Validate group param
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function validateGroupAndOwner(
        string $groupAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ): ?\OmegaUp\DAO\VO\Groups {
        $group = \OmegaUp\DAO\Groups::findByAlias($groupAlias);
        if (is_null($group)) {
            return null;
        }

        if (!\OmegaUp\Authorization::isGroupAdmin($identity, $group)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        return $group;
    }

    /**
     * Add identity to group
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $group_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiAddUser(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::namespacedAlias($alias)
        );
        $group = self::validateGroupAndOwner($groupAlias, $r->identity);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'group_alias'
            );
        }
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );
        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );

        if (
            !is_null(\OmegaUp\DAO\GroupsIdentities::getByPK(
                $group->group_id,
                $resolvedIdentity->identity_id
            ))
        ) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'identityInGroup'
            );
        }

        \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
            'group_id' => $group->group_id,
            'identity_id' => $resolvedIdentity->identity_id
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Remove user from group
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $group_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiRemoveUser(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::namespacedAlias($alias)
        );
        $group = self::validateGroupAndOwner($groupAlias, $r->identity);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'group_alias'
            );
        }
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );
        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );

        // Check user is actually in group
        $groupIdentities = \OmegaUp\DAO\GroupsIdentities::getByPK(
            $group->group_id,
            $resolvedIdentity->identity_id
        );
        if (is_null($groupIdentities)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'User'
            );
        }

        \OmegaUp\DAO\GroupsIdentities::delete($groupIdentities);
        self::$log->info("Removed {$resolvedIdentity->username}");

        return ['status' => 'ok'];
    }

    /**
     * Returns a list of groups by owner
     *
     * @param \OmegaUp\Request $r
     * @return array{groups: list<array{alias: string, create_time: \OmegaUp\Timestamp, description: null|string, name: string}>}
     */
    public static function apiMyList(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        $groups = \OmegaUp\DAO\Groups::getAllGroupsAdminedByUser(
            $r->user->user_id,
            $r->identity->identity_id
        );

        return [
            'groups' => $groups,
        ];
    }

    /**
     * Returns a list of groups that match a partial name. This returns an
     * array instead of an object since it is used by typeahead.
     *
     * @param \OmegaUp\Request $r
     *
     * @return list<array{label: string, value: string}>
     *
     * @omegaup-request-param null|string $query
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringOfLengthInRange(
            $r['query'],
            'query',
            /*$minLength=*/2,
            /*$maxLength=*/null
        );

        $groups = \OmegaUp\DAO\Groups::SearchByName($r['query']);

        $response = [];
        foreach ($groups as $group) {
            $response[] = [
                'label' => strval($group->name),
                'value' => strval($group->alias),
            ];
        }
        return $response;
    }

    /**
     * Details of a group (scoreboards)
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{group: array{create_time: int, alias: null|string, name: null|string, description: null|string}, scoreboards: list<array{alias: string, create_time: string, description: null|string, name: string}>}
     *
     * @omegaup-request-param string $group_alias
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::namespacedAlias($alias)
        );
        $group = self::validateGroupAndOwner($groupAlias, $r->identity);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\NotFoundException('groupNotFound');
        }

        $scoreboards = \OmegaUp\DAO\GroupsScoreboards::getByGroup(
            intval($group->group_id)
        );

        /** @var array{create_time: int, alias: null|string, name: null|string, description: null|string} */
        $filteredGroup = $group->asFilteredArray([
            'create_time',
            'alias',
            'name',
            'description',
        ]);
        $response = [
            'group' => $filteredGroup,
            'scoreboards' => [],
        ];
        foreach ($scoreboards as $scoreboard) {
            /** @var array{alias: string, create_time: string, description: null|string, name: string} */
            $response['scoreboards'][] = $scoreboard->asFilteredArray([
                'alias',
                'create_time',
                'description',
                'name',
            ]);
        }
        return $response;
    }

    /**
     * Members of a group (usernames only).
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{identities: list<array{classname: string, country?: null|string, country_id?: null|string, name?: null|string, school?: null|string, school_id?: int|null, state?: null|string, state_id?: null|string, username: string}>}
     *
     * @omegaup-request-param string $group_alias
     */
    public static function apiMembers(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::namespacedAlias($alias)
        );
        $group = self::validateGroupAndOwner($groupAlias, $r->identity);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'group_alias'
            );
        }

        return [
            'identities' => \OmegaUp\DAO\GroupsIdentities::GetMemberIdentities(
                $group
            ),
        ];
    }

    /**
     * Create a scoreboard set to a group
     *
     * @return array{status: string}
     *
     * @omegaup-request-param null|string $alias
     * @omegaup-request-param null|string $description
     * @omegaup-request-param string $group_alias
     * @omegaup-request-param string $name
     */
    public static function apiCreateScoreboard(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $groupAlias = $r->ensureString(
            'group_alias',
            fn (string $alias) => \OmegaUp\Validators::namespacedAlias($alias)
        );
        $group = self::validateGroupAndOwner($groupAlias, $r->identity);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'group_alias'
            );
        }

        $groupScoreboardAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name');
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['description'],
            'description'
        );

        \OmegaUp\DAO\GroupsScoreboards::create(new \OmegaUp\DAO\VO\GroupsScoreboards([
            'group_id' => $group->group_id,
            'name' => $r['name'],
            'description' => $r['description'],
            'alias' => $groupScoreboardAlias,
            'create_time' => \OmegaUp\Time::get(),
        ]));

        self::$log->info("New scoreboard created {$groupScoreboardAlias}");

        return ['status' => 'ok'];
    }

    /**
     * @return array{smartyProperties: array{IS_ORGANIZER: bool, payload: array{countries: list<\OmegaUp\DAO\VO\Countries>}}, template: string}
     */
    public static function getGroupEditDetailsForSmarty(
        \OmegaUp\Request $r
    ): array {
        // Authenticate user
        $r->ensureMainUserIdentity();

        $isOrganizer = \OmegaUp\Experiments::getInstance()->isEnabled(
            \OmegaUp\Experiments::IDENTITIES
        ) && \OmegaUp\Authorization::canCreateGroupIdentities(
            $r->identity
        );
        return [
            'smartyProperties' => [
                'IS_ORGANIZER' => $isOrganizer,
                'payload' => [
                    'countries' => \OmegaUp\DAO\Countries::getAll(
                        null,
                        100,
                        'name'
                    ),
                ],
            ],
            'template' => 'group.edit.tpl',
        ];
    }

    /**
     * @return array{payload: array{groups: array{alias: string, create_time: \OmegaUp\Timestamp, description: null|string, name: string}[]}}
     */
    public static function getGroupListForSmarty(\OmegaUp\Request $r): array {
        // Authenticate user
        $r->ensureMainUserIdentity();

        return [
            'payload' => [
                'groups' => \OmegaUp\DAO\Groups::getAllGroupsAdminedByUser(
                    $r->user->user_id,
                    $r->identity->identity_id
                ),
            ],
        ];
    }
}
