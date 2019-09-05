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
    public static function createGroup($alias, $name, $description, $owner_id) {
        $group = new \OmegaUp\DAO\VO\Groups([
            'alias' => $alias,
            'name' => $name,
            'description' => $description,
        ]);
        $groupAcl = new \OmegaUp\DAO\VO\ACLs([
            'owner_id' => $owner_id,
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
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('aliasInUse', $e);
            }
            throw $e;
        }

        return $group;
    }

    /**
     * New group
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', true);
        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name');
        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['description'], 'description');

        self::createGroup(
            $r['alias'],
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
    public static function validateGroup(
        ?string $groupAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ) : ?\OmegaUp\DAO\VO\Groups {
        \OmegaUp\Validators::validateStringNonEmpty($groupAlias, 'group_alias');
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
     * Validate common params for these APIs
     */
    private static function validateGroupAndOwner(
        ?string $groupAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ) : ?\OmegaUp\DAO\VO\Groups {
        return self::validateGroup($groupAlias, $identity);
    }

    /**
     * Add identity to group
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiAddUser(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $group = self::validateGroupAndOwner($r['group_alias'], $r->identity);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'group_alias'
            );
        }
        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity($r['usernameOrEmail']);

        if (!is_null(\OmegaUp\DAO\GroupsIdentities::getByPK(
            $group->group_id,
            $resolvedIdentity->identity_id
        ))) {
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
     */
    public static function apiRemoveUser(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $group = self::validateGroupAndOwner($r['group_alias'], $r->identity);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'group_alias'
            );
        }
        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity($r['usernameOrEmail']);

        // Check user is actually in group
        $groupIdentities = \OmegaUp\DAO\GroupsIdentities::getByPK(
            $group->group_id,
            $resolvedIdentity->identity_id
        );
        if (is_null($groupIdentities)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'User');
        }

        \OmegaUp\DAO\GroupsIdentities::delete($groupIdentities);
        self::$log->info("Removed {$resolvedIdentity->username}");

        return ['status' => 'ok'];
    }

    /**
     * Returns a list of groups by owner
     *
     * @param \OmegaUp\Request $r
     * @return array{status: string, groups: array{alias: string, create_time: int, description: string, name: string}[]}
     * @psalm-suppress PossiblyNullArgument
     */
    public static function apiMyList(\OmegaUp\Request $r) : array {
        $r->ensureMainUserIdentity();

        $groups = \OmegaUp\DAO\Groups::getAllGroupsAdminedByUser(
            $r->user->user_id,
            $r->identity->identity_id
        );

        return [
            'status' => 'ok',
            'groups' => $groups,
        ];
    }

    /**
     * Returns a list of groups that match a partial name. This returns an
     * array instead of an object since it is used by typeahead.
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiList(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        if (is_null($r['query'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterEmpty', 'query');
        }
        if (strlen($r['query']) < 2) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'query');
        }

        $groups = \OmegaUp\DAO\Groups::SearchByName($r['query']);

        $response = [];
        foreach ($groups as $group) {
            array_push($response, ['label' => $group->name, 'value' => $group->alias]);
        }
        return $response;
    }

    /**
     * Details of a group (scoreboards)
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $group = self::validateGroupAndOwner($r['group_alias'], $r->identity);
        if (is_null($group)) {
            return [
                'exists' => false,
                'status' => 'ok',
            ];
        }

        /** @var int $group->group_id */
        $scoreboards = \OmegaUp\DAO\GroupsScoreboards::getByGroup($group->group_id);

        $response = [
            'status' => 'ok',
            'exists' => true,
            'group' => $group->asArray(),
            'scoreboards' => [],
        ];
        foreach ($scoreboards as $scoreboard) {
            $response['scoreboards'][] = $scoreboard->asArray();
        }
        return $response;
    }

    /**
     * Members of a group (usernames only).
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiMembers(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $group = self::validateGroupAndOwner($r['group_alias'], $r->identity);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'group_alias'
            );
        }

        return [
            'status' => 'ok',
            'identities' => \OmegaUp\DAO\GroupsIdentities::GetMemberIdentities($group),
        ];
    }

    /**
     * Create a scoreboard set to a group
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiCreateScoreboard(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $group = self::validateGroup($r['group_alias'], $r->identity);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'group_alias'
            );
        }

        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', true);
        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name');
        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['description'], 'description');

        \OmegaUp\DAO\GroupsScoreboards::create(new \OmegaUp\DAO\VO\GroupsScoreboards([
            'group_id' => $group->group_id,
            'name' => $r['name'],
            'description' =>$r['description'],
            'alias' => $r['alias'],
            'create_time' => \OmegaUp\Time::get(),
        ]));

        self::$log->info("New scoreboard created {$r['alias']}");

        return ['status' => 'ok'];
    }
}
