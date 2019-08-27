<?php

/**
 *  GroupController
 *
 * @author joemmanuel
 */

class GroupController extends Controller {
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
            ACLsDAO::create($groupAcl);
            $group->acl_id = $groupAcl->acl_id;

            GroupsDAO::create($group);

            self::$log->info("Group {$alias} created.");

            \OmegaUp\DAO\DAO::transEnd();
        } catch (Exception $e) {
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
        self::authenticateRequest($r);

        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', true);
        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name', true);
        \OmegaUp\Validators::validateStringNonEmpty($r['description'], 'description', false);

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
     * @param string $groupAlias
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function validateGroup(
        string $groupAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        \OmegaUp\Validators::validateStringNonEmpty($groupAlias, 'group_alias');
        $group = GroupsDAO::findByAlias($groupAlias);
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
     *
     * @param string $groupAlias
     * @param \OmegaUp\DAO\VO\Identities $identity
     */
    private static function validateGroupAndOwner(
        string $groupAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        return self::validateGroup($groupAlias, $identity);
    }

    /**
     * Add identity to group
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiAddUser(\OmegaUp\Request $r) {
        self::authenticateRequest($r);
        $group = self::validateGroupAndOwner($r['group_alias'], $r->identity);
        $resolvedIdentity = IdentityController::resolveIdentity($r['usernameOrEmail']);

        GroupsIdentitiesDAO::create(new \OmegaUp\DAO\VO\GroupsIdentities([
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
        self::authenticateRequest($r);
        $group = self::validateGroupAndOwner($r['group_alias'], $r->identity);
        $resolvedIdentity = IdentityController::resolveIdentity($r['usernameOrEmail']);

        // Check user is actually in group
        $groupIdentities = GroupsIdentitiesDAO::getByPK(
            $group->group_id,
            $resolvedIdentity->identity_id
        );
        if (is_null($groupIdentities)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'User');
        }

        GroupsIdentitiesDAO::delete($groupIdentities);
        self::$log->info("Removed {$resolvedIdentity->username}");

        return ['status' => 'ok'];
    }

    /**
     * Returns a list of groups by owner
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiMyList(\OmegaUp\Request $r) {
        self::authenticateRequest($r, true /* requireMainUserIdentity */);

        $groups = GroupsDAO::getAllGroupsAdminedByUser(
            $r->user->user_id,
            $r->identity->identity_id
        );

        $response = [
            'status' => 'ok',
            'groups' => [],
        ];
        foreach ($groups as $group) {
            $response['groups'][] = $group->asArray();
        }
        return $response;
    }

    /**
     * Returns a list of groups that match a partial name. This returns an
     * array instead of an object since it is used by typeahead.
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiList(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        if (is_null($r['query'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterEmpty', 'query');
        }
        if (strlen($r['query']) < 2) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'query');
        }

        $groups = GroupsDAO::SearchByName($r['query']);

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
        self::authenticateRequest($r);
        $group = self::validateGroupAndOwner($r['group_alias'], $r->identity);

        if (is_null($group)) {
            return [
                'exists' => false,
                'status' => 'ok',
            ];
        }

        $scoreboards = GroupsScoreboardsDAO::getByGroup($group->group_id);

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
        self::authenticateRequest($r);
        $group = self::validateGroupAndOwner($r['group_alias'], $r->identity);

        return [
            'status' => 'ok',
            'identities' => GroupsIdentitiesDAO::GetMemberIdentities($group),
        ];
    }

    /**
     * Create a scoreboard set to a group
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiCreateScoreboard(\OmegaUp\Request $r) {
        self::authenticateRequest($r);
        $group = self::validateGroup($r['group_alias'], $r->identity);

        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', true);
        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name', true);
        \OmegaUp\Validators::validateStringNonEmpty($r['description'], 'description', false);

        GroupsScoreboardsDAO::create(new \OmegaUp\DAO\VO\GroupsScoreboards([
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
