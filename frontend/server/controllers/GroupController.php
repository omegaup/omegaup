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
        $group = new Groups([
            'alias' => $alias,
            'name' => $name,
            'description' => $description,
        ]);
        $groupAcl = new ACLs([
            'owner_id' => $owner_id,
        ]);

        DAO::transBegin();

        try {
            ACLsDAO::create($groupAcl);
            $group->acl_id = $groupAcl->acl_id;

            GroupsDAO::create($group);

            self::$log->info("Group {$alias} created.");

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();
            if (DAO::isDuplicateEntryException($e)) {
                throw new DuplicatedEntryInDatabaseException('aliasInUse', $e);
            }
            throw $e;
        }

        return $group;
    }

    /**
     * New group
     *
     * @param Request $r
     */
    public static function apiCreate(Request $r) {
        self::authenticateRequest($r);

        Validators::validateValidAlias($r['alias'], 'alias', true);
        Validators::validateStringNonEmpty($r['name'], 'name', true);
        Validators::validateStringNonEmpty($r['description'], 'description', false);

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
     * @param Identities $identity
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    public static function validateGroup(
        string $groupAlias,
        Identities $identity
    ) {
        Validators::validateStringNonEmpty($groupAlias, 'group_alias');
        $group = GroupsDAO::findByAlias($groupAlias);
        if (is_null($group)) {
            return null;
        }

        if (!Authorization::isGroupAdmin($identity, $group)) {
            throw new ForbiddenAccessException();
        }
        return $group;
    }

    /**
     * Validate common params for these APIs
     *
     * @param string $groupAlias
     * @param Identities $identity
     */
    private static function validateGroupAndOwner(
        string $groupAlias,
        Identities $identity
    ) {
        return self::validateGroup($groupAlias, $identity);
    }

    /**
     * Add identity to group
     *
     * @param Request $r
     */
    public static function apiAddUser(Request $r) {
        self::authenticateRequest($r);
        $group = self::validateGroupAndOwner($r['group_alias'], $r->identity);
        $resolvedIdentity = IdentityController::resolveIdentity($r['usernameOrEmail']);

        GroupsIdentitiesDAO::create(new GroupsIdentities([
            'group_id' => $group->group_id,
            'identity_id' => $resolvedIdentity->identity_id
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Remove user from group
     *
     * @param Request $r
     */
    public static function apiRemoveUser(Request $r) {
        self::authenticateRequest($r);
        $group = self::validateGroupAndOwner($r['group_alias'], $r->identity);
        $resolvedIdentity = IdentityController::resolveIdentity($r['usernameOrEmail']);

        // Check user is actually in group
        $groupIdentities = GroupsIdentitiesDAO::getByPK(
            $group->group_id,
            $resolvedIdentity->identity_id
        );
        if (is_null($groupIdentities)) {
            throw new InvalidParameterException('parameterNotFound', 'User');
        }

        GroupsIdentitiesDAO::delete($groupIdentities);
        self::$log->info("Removed {$resolvedIdentity->username}");

        return ['status' => 'ok'];
    }

    /**
     * Returns a list of groups by owner
     *
     * @param Request $r
     */
    public static function apiMyList(Request $r) {
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
     * @param Request $r
     */
    public static function apiList(Request $r) {
        self::authenticateRequest($r);

        if (is_null($r['query'])) {
            throw new InvalidParameterException('parameterEmpty', 'query');
        }
        if (strlen($r['query']) < 2) {
            throw new InvalidParameterException('parameterInvalid', 'query');
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
     * @param Request $r
     */
    public static function apiDetails(Request $r) {
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
     * @param Request $r
     */
    public static function apiMembers(Request $r) {
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
     * @param Request $r
     */
    public static function apiCreateScoreboard(Request $r) {
        self::authenticateRequest($r);
        $group = self::validateGroup($r['group_alias'], $r->identity);

        Validators::validateValidAlias($r['alias'], 'alias', true);
        Validators::validateStringNonEmpty($r['name'], 'name', true);
        Validators::validateStringNonEmpty($r['description'], 'description', false);

        GroupsScoreboardsDAO::create(new GroupsScoreboards([
            'group_id' => $group->group_id,
            'name' => $r['name'],
            'description' =>$r['description'],
            'alias' => $r['alias'],
            'create_time' => Time::get(),
        ]));

        self::$log->info("New scoreboard created {$r['alias']}");

        return ['status' => 'ok'];
    }
}
