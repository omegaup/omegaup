<?php

class GroupsFactory {
    /**
     * Create group
     *
     * @param type $owner
     * @param type $name
     * @param type $description
     */
    public static function createGroup(Users $owner = null, $name = null, $description = null, $alias = null, ScopedLoginToken $login = null) {
        if (is_null($owner)) {
            $owner = UserFactory::createUser();
        }

        if (is_null($name)) {
            $name = Utils::CreateRandomString();
        }

        if (is_null($description)) {
            $description = Utils::CreateRandomString();
        }

        if (is_null($alias)) {
            $alias = Utils::CreateRandomString();
        }

        if (is_null($login)) {
            $login = OmegaupTestCase::login($owner);
        }
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => $name,
            'description' => $description,
            'alias' => $alias
        ]);

        $response = GroupController::apiCreate($r);
        $groups = GroupsDAO::search(new Groups([
            'alias' => $alias
        ]));

        return [
            'request' => $r,
            'response' => $response,
            'owner' => $owner,
            'group' => $groups[0]
        ];
    }

    /**
     * Add user to group helper
     *
     * @param array $groupData
     * @param Users $user
     */
    public static function addUserToGroup(array $groupData, Users $user, ScopedLoginToken $login = null) {
        if (is_null($login)) {
            $login = OmegaupTestCase::login($groupData['owner']);
        }
        GroupController::apiAddUser(new Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
            'group_alias' => $groupData['group']->alias
        ]));
    }

    /**
     * Creates a scoreboard in a group
     *
     * @param array $groupData
     * @param type $name
     * @param type $description
     * @param type $alias
     */
    public static function createGroupScoreboard(array $groupData, $name = null, $description = null, $alias = null) {
        if (is_null($name)) {
            $name = Utils::CreateRandomString();
        }

        if (is_null($description)) {
            $description = Utils::CreateRandomString();
        }

        if (is_null($alias)) {
            $alias = Utils::CreateRandomString();
        }

        $login = OmegaupTestCase::login($groupData['owner']);
        $request = new Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['group']->alias,
            'name' => $name,
            'alias' => $alias,
            'description' => $description
        ]);
        $response = GroupController::apiCreateScoreboard($request);

        $scoreboards = GroupsScoreboardsDAO::search(new GroupsScoreboards([
            'alias' => $alias
        ]));

        return [
            'request' => $request,
            'response' => $response,
            'scoreboard' => $scoreboards[0]
        ];
    }

    /**
     * Adds contest to scoreboard helper
     *
     * @param array $contestData
     * @param array $scorebaordData
     * @param array $groupData
     */
    public static function addContestToScoreboard($contestData, $scoreboardData, $groupData, $onlyAC = 0, $weight = 1) {
        $login = OmegaupTestCase::login($groupData['owner']);
        GroupScoreboardController::apiAddContest(new Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['request']['alias'],
            'scoreboard_alias' => $scoreboardData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
            'only_ac' => $onlyAC,
            'weight' => $weight
        ]));
    }
}
