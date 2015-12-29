<?php

class GroupsFactory {
    /**
     * Create group
     *
     * @param type $owner
     * @param type $name
     * @param type $description
     */
    public static function createGroup($owner = null, $name = null, $description = null, $alias = null) {
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

        $r = new Request(array(
            'auth_token' => OmegaupTestCase::login($owner),
            'name' => $name,
            'description' => $description,
            'alias' => $alias
        ));

        $response = GroupController::apiCreate($r);
        $groups = GroupsDAO::search(new Groups(array(
            'alias' => $alias
        )));

        return array(
            'request' => $r,
            'response' => $response,
            'owner' => $owner,
            'group' => $groups[0]
        );
    }

    /**
     * Add user to group helper
     *
     * @param array $groupData
     * @param Users $user
     */
    public static function addUserToGroup(array $groupData, Users $user) {
        GroupController::apiAddUser(new Request(array(
            'auth_token' => OmegaupTestCase::login($groupData['owner']),
            'usernameOrEmail' => $user->username,
            'group_alias' => $groupData['group']->alias
        )));
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

        $request = new Request(array(
            'auth_token' => OmegaupTestCase::login($groupData['owner']),
            'group_alias' => $groupData['group']->alias,
            'name' => $name,
            'alias' => $alias,
            'description' => $description
        ));
        $response = GroupController::apiCreateScoreboard($request);

        $scoreboards = GroupsScoreboardsDAO::search(new GroupsScoreboards(array(
            'alias' => $alias
        )));

        return array(
            'request' => $request,
            'response' => $response,
            'scoreboard' => $scoreboards[0]
        );
    }

    /**
     * Adds contest to scoreboard helper
     *
     * @param array $contestData
     * @param array $scorebaordData
     * @param array $groupData
     */
    public static function addContestToScoreboard($contestData, $scoreboardData, $groupData, $onlyAC = 0, $weight = 1) {
        GroupScoreboardController::apiAddContest(new Request(array(
            'auth_token' => OmegaupTestCase::login($groupData['owner']),
            'group_alias' => $groupData['request']['alias'],
            'scoreboard_alias' => $scoreboardData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
            'only_ac' => $onlyAC,
            'weight' => $weight
        )));
    }
}
