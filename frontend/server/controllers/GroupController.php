<?php

/**
 *  GroupController
 *
 * @author joemmanuel
 */

class GroupController extends Controller {
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

        try {
            $group = new Groups([
                'owner_id' => $r['current_user_id'],
                'name' => $r['name'],
                'description' =>$r['description'],
                'alias' => $r['alias'],
                'create_time' => gmdate('Y-m-d H:i:s', time()),
            ]);

            GroupsDAO::save($group);

            self::$log->info('Group ' . $r['alias'] . ' created.');
        } catch (Exception $e) {
            if (strpos($e->getMessage(), '1062') !== false) {
                throw new DuplicatedEntryInDatabaseException('aliasInUse', $e);
            } else {
                throw new InvalidDatabaseOperationException($e);
            }
        }

        return ['status' => 'ok'];
    }

    /**
     * Validate group param
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    public static function validateGroup(Request $r) {
        self::authenticateRequest($r);

        Validators::isStringNonEmpty($r['group_alias'], 'group_alias');
        try {
            $groups = GroupsDAO::search(new Groups([
                'alias' => $r['group_alias']
            ]));

            if (is_null($groups) || count($groups) === 0) {
                throw new InvalidParameterException('parameterNotFound', 'Group');
            } else {
                $r['group'] = $groups[0];
            }
        } catch (ApiException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        if (!Authorization::isGroupAdmin($r['current_user_id'], $r['group'])) {
            throw new ForbiddenAccessException();
        }
    }

    /**
     * Validate common params for these APIs
     *
     * @param Request $r
     */
    private static function validateGroupAndOwner(Request $r) {
        self::validateGroup($r);
    }

    /**
     * Add user to group
     *
     * @param Request $r
     */
    public static function apiAddUser(Request $r) {
        self::validateGroupAndOwner($r);
        $r['user'] = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $groups_user = new GroupsUsers([
                'group_id' => $r['group']->group_id,
                'user_id' => $r['user']->user_id
            ]);
            GroupsUsersDAO::save($groups_user);
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
        self::validateGroupAndOwner($r);
        $r['user'] = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $key = new GroupsUsers([
                'group_id' => $r['group']->group_id,
                'user_id' => $r['user']->user_id
            ]);

            // Check user is actually in group
            $groups_user = GroupsUsersDAO::search($key);
            if (count($groups_user) === 0) {
                throw new InvalidParameterException('parameterNotFound', 'User');
            }

            GroupsUsersDAO::delete($key);
            self::$log->info('Removed ' . $r['user']->username . ' removed.');
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
            $groups = GroupsDAO::search(new Groups([
                'owner_id' => $r['current_user_id']
            ]));

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
        self::validateGroupAndOwner($r);

        $response = [];

        try {
            $response['group'] = $r['group']->asArray();

            $scoreboards = GroupsScoreboardsDAO::search(
                new GroupsScoreboards(
                    ['group_id' => $r['group']->group_id]
                )
            );

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
        self::validateGroupAndOwner($r);

        $response = [];

        try {
            $response['users'] = GroupsUsersDAO::GetMemberUsernames($r['group']);
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
        self::validateGroup($r);

        Validators::isValidAlias($r['alias'], 'alias', true);
        Validators::isStringNonEmpty($r['name'], 'name', true);
        Validators::isStringNonEmpty($r['description'], 'description', false);

        try {
            $groupScoreboard = new GroupsScoreboards([
                'group_id' => $r['group']->group_id,
                'name' => $r['name'],
                'description' =>$r['description'],
                'alias' => $r['alias'],
                'create_time' => gmdate('Y-m-d H:i:s', time())
            ]);

            GroupsScoreboardsDAO::save($groupScoreboard);

            self::$log->info('New scoreboard created ' . $r['alias']);
        } catch (Exception $ex) {
            throw new InvalidDatabaseOperationException($ex);
        }

        return ['status' => 'ok'];
    }
}
