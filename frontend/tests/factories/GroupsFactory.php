<?php

class GroupsFactory {
    /**
     * Create group
     *
     * @param type $owner
     * @param type $name
     * @param type $description
     */
    public static function createGroup(
        \OmegaUp\DAO\VO\Identities $owner = null,
        $name = null,
        $description = null,
        $alias = null,
        ScopedLoginToken $login = null
    ) {
        if (is_null($owner)) {
            ['user' => $user, 'identity' => $owner] = UserFactory::createUser();
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
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $name,
            'description' => $description,
            'alias' => $alias
        ]);

        $response = \OmegaUp\Controllers\Group::apiCreate($r);
        $group = \OmegaUp\DAO\Groups::findByAlias($alias);

        return [
            'request' => $r,
            'response' => $response,
            'owner' => $owner,
            'group' => $group
        ];
    }

    /**
     * Add identity to group helper
     *
     * @param array{group: \OmegaUp\DAO\VO\Groups, owner: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{status: string}} $groupData
     */
    public static function addUserToGroup(
        array $groupData,
        \OmegaUp\DAO\VO\Identities $identity,
        ScopedLoginToken $login = null
    ) {
        if (is_null($login)) {
            $login = OmegaupTestCase::login($groupData['owner']);
        }
        \OmegaUp\Controllers\Group::apiAddUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $identity->username,
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
    public static function createGroupScoreboard(
        array $groupData,
        $name = null,
        $description = null,
        $alias = null
    ) {
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
        $request = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['group']->alias,
            'name' => $name,
            'alias' => $alias,
            'description' => $description
        ]);
        $response = \OmegaUp\Controllers\Group::apiCreateScoreboard($request);

        $scoreboard = \OmegaUp\DAO\GroupsScoreboards::getByAlias($alias);

        return [
            'request' => $request,
            'response' => $response,
            'scoreboard' => $scoreboard
        ];
    }

    /**
     * Adds contest to scoreboard helper
     *
     * @param array{director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, contest: \OmegaUp\DAO\VO\Contests} $contestData
     * @param array{response: array{status: string}, request: \OmegaUp\Request, scoreboard: \OmegaUp\DAO\VO\GroupsScoreboards} $scoreboardData
     * @param array{response: array{status: string}, owner: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, group: \OmegaUp\DAO\VO\Groups} $groupData
     */
    public static function addContestToScoreboard(
        $contestData,
        $scoreboardData,
        $groupData,
        int $onlyAC = 0,
        int $weight = 1
    ): void {
        $login = OmegaupTestCase::login($groupData['owner']);
        \OmegaUp\Controllers\GroupScoreboard::apiAddContest(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['request']['alias'],
            'scoreboard_alias' => $scoreboardData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
            'only_ac' => $onlyAC,
            'weight' => $weight
        ]));
    }
}
