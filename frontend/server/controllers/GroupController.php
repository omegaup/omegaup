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
        $group_acl = new ACLs([
            'owner_id' => $owner_id,
        ]);

        GroupsDAO::transBegin();

        try {
            ACLsDAO::save($group_acl);
            $group->acl_id = $group_acl->acl_id;

            GroupsDAO::save($group);

            self::$log->info('Group ' . $alias . ' created.');

            GroupsDAO::transEnd();
        } catch (Exception $e) {
            GroupsDAO::transRollback();

            if (strpos($e->getMessage(), '1062') !== false) {
                throw new DuplicatedEntryInDatabaseException('aliasInUse', $e);
            } else {
                throw new InvalidDatabaseOperationException($e);
            }
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

        Validators::isValidAlias($r['alias'], 'alias', true);
        Validators::isStringNonEmpty($r['name'], 'name', true);
        Validators::isStringNonEmpty($r['description'], 'description', false);

        self::createGroup(
            $r['alias'],
            $r['name'],
            $r['description'],
            $r['current_user_id']
        );

        return ['status' => 'ok'];
    }

    /**
     * Validate group param
     *
     * @param $groupAlias
     * @param $identityId
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    public static function validateGroup($groupAlias, $identityId) {
        Validators::isStringNonEmpty($groupAlias, 'group_alias');
        try {
            $group = GroupsDAO::FindByAlias($groupAlias);

            if (is_null($group)) {
                throw new InvalidParameterException('parameterNotFound', 'Group');
            }
        } catch (ApiException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        if (!Authorization::isGroupAdmin($identityId, $group)) {
            throw new ForbiddenAccessException();
        }
        return $group;
    }

    /**
     * Validate common params for these APIs
     *
     * @param $groupAlias
     * @param $identityId
     */
    private static function validateGroupAndOwner($groupAlias, $identityId) {
        return self::validateGroup($groupAlias, $identityId);
    }

    /**
     * Add identity to group
     *
     * @param Request $r
     */
    public static function apiAddUser(Request $r) {
        self::authenticateRequest($r);
        $group = self::validateGroupAndOwner($r['group_alias'], $r['current_identity_id']);
        $r['identity'] = IdentityController::resolveIdentity($r['usernameOrEmail']);

        try {
            $groups_identity = new GroupsIdentities([
                'group_id' => $group->group_id,
                'identity_id' => $r['identity']->identity_id
            ]);
            GroupsIdentitiesDAO::save($groups_identity);
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        return ['status' => 'ok'];
    }

    /**
     * Remove user from group
     *
     * @param Request $r
     */
    public static function apiRemoveUser(Request $r) {
        self::authenticateRequest($r);
        $group = self::validateGroupAndOwner($r['group_alias'], $r['current_identity_id']);
        $r['identity'] = IdentityController::resolveIdentity($r['usernameOrEmail']);

        try {
            // Check user is actually in group
            $groupIdentities = GroupsIdentitiesDAO::getByPK(
                $group->group_id,
                $r['identity']->identity_id
            );
            if (count($groupIdentities) === 0) {
                throw new InvalidParameterException('parameterNotFound', 'User');
            }

            GroupsIdentitiesDAO::delete($groupIdentities);
            self::$log->info('Removed ' . $r['identity']->username . ' removed.');
        } catch (ApiException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        return ['status' => 'ok'];
    }

    /**
     * Returns a list of groups by owner
     *
     * @param Request $r
     */
    public static function apiMyList(Request $r) {
        self::authenticateRequest($r);

        $response = [];
        $response['groups'] = [];

        try {
            $groups = GroupsDAO::getAllGroupsAdminedByUser(
                $r['current_user_id'],
                $r['current_identity_id']
            );

            foreach ($groups as $group) {
                $response['groups'][] = $group->asArray();
            }
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        $response['status'] = 'ok';
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

        try {
            $groups = GroupsDAO::SearchByName($r['query']);
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

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
        $group = self::validateGroupAndOwner($r['group_alias'], $r['current_identity_id']);

        $response = [];

        try {
            $response['group'] = $group->asArray();

            $scoreboards = GroupsScoreboardsDAO::getByGroup($group->group_id);

            $response['scoreboards'] = [];
            foreach ($scoreboards as $scoreboard) {
                $response['scoreboards'][] = $scoreboard->asArray();
            }
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Members of a group (usernames only).
     *
     * @param Request $r
     */
    public static function apiMembers(Request $r) {
        self::authenticateRequest($r);
        $group = self::validateGroupAndOwner($r['group_alias'], $r['current_identity_id']);

        $response = [];

        try {
            $response['identities'] = GroupsIdentitiesDAO::GetMemberIdentities($group);
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Create a scoreboard set to a group
     *
     * @param Request $r
     */
    public static function apiCreateScoreboard(Request $r) {
        self::authenticateRequest($r);
        $group = self::validateGroup($r['group_alias'], $r['current_identity_id']);

        Validators::isValidAlias($r['alias'], 'alias', true);
        Validators::isStringNonEmpty($r['name'], 'name', true);
        Validators::isStringNonEmpty($r['description'], 'description', false);

        try {
            $groupScoreboard = new GroupsScoreboards([
                'group_id' => $group->group_id,
                'name' => $r['name'],
                'description' =>$r['description'],
                'alias' => $r['alias'],
                'create_time' => gmdate('Y-m-d H:i:s', Time::get())
            ]);

            GroupsScoreboardsDAO::save($groupScoreboard);

            self::$log->info('New scoreboard created ' . $r['alias']);
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        return ['status' => 'ok'];
    }
}
