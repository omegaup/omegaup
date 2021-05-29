<?php

namespace OmegaUp\Test\Factories;

class Groups {
    /**
     * Create group
     *
     * @return array{group: \OmegaUp\DAO\VO\Groups, owner: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{status: string}}
     */
    public static function createGroup(
        ?\OmegaUp\DAO\VO\Identities $owner = null,
        ?string $name = null,
        ?string $description = null,
        ?string $alias = null,
        ?\OmegaUp\Test\ScopedLoginToken $login = null
    ) {
        if (is_null($owner)) {
            ['user' => $user, 'identity' => $owner] = \OmegaUp\Test\Factories\User::createUser();
        }

        if (is_null($name)) {
            $name = \OmegaUp\Test\Utils::createRandomString();
        }

        if (is_null($description)) {
            $description = \OmegaUp\Test\Utils::createRandomString();
        }

        if (is_null($alias)) {
            $alias = \OmegaUp\Test\Utils::createRandomString();
        }

        if (is_null($login)) {
            $login = \OmegaUp\Test\ControllerTestCase::login($owner);
        }
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $name,
            'description' => $description,
            'alias' => $alias
        ]);

        $response = \OmegaUp\Controllers\Group::apiCreate($r);
        $group = \OmegaUp\DAO\Groups::findByAlias($alias);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseGroupNotFound'
            );
        }

        return [
            'request' => $r,
            'response' => $response,
            'owner' => $owner,
            'group' => $group
        ];
    }

    /**
     * Create teams group
     *
     * @return array{teamGroup: \OmegaUp\DAO\VO\TeamGroups, owner: \OmegaUp\DAO\VO\Identities|null}
     */
    public static function createTeamsGroup(
        ?\OmegaUp\DAO\VO\Identities $owner = null,
        ?string $name = null,
        ?string $description = null,
        ?string $alias = null,
        ?\OmegaUp\Test\ScopedLoginToken $login = null
    ) {
        if (is_null($owner)) {
            [
                'identity' => $owner,
            ] = \OmegaUp\Test\Factories\User::createUser();
        }

        if (is_null($name)) {
            $name = \OmegaUp\Test\Utils::createRandomString();
        }

        if (is_null($description)) {
            $description = \OmegaUp\Test\Utils::createRandomString();
        }

        if (is_null($alias)) {
            $alias = \OmegaUp\Test\Utils::createRandomString();
        }

        if (is_null($login)) {
            $login = \OmegaUp\Test\ControllerTestCase::login($owner);
        }

        \OmegaUp\Controllers\Group::apiCreateTeamGroup(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'alias' => $alias,
                'name' => $name,
                'description' => $description,
            ])
        );

        $createdTeamGroup = \OmegaUp\DAO\TeamGroups::getByAlias($alias);
        if (is_null($createdTeamGroup)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseGroupNotFound'
            );
        }

        return [
            'owner' => $owner,
            'teamGroup' => $createdTeamGroup,
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
        \OmegaUp\Test\ScopedLoginToken $login = null
    ): void {
        if (is_null($login)) {
            $login = \OmegaUp\Test\ControllerTestCase::login(
                $groupData['owner']
            );
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
     * @param array{group: \OmegaUp\DAO\VO\Groups, owner: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{status: string}} $groupData
     * @return array{response: array{status: string}, request: \OmegaUp\Request, scoreboard: \OmegaUp\DAO\VO\GroupsScoreboards}
     */
    public static function createGroupScoreboard(
        array $groupData,
        ?string $name = null,
        ?string $description = null,
        ?string $alias = null
    ) {
        if (is_null($name)) {
            $name = \OmegaUp\Test\Utils::createRandomString();
        }

        if (is_null($description)) {
            $description = \OmegaUp\Test\Utils::createRandomString();
        }

        if (is_null($alias)) {
            $alias = \OmegaUp\Test\Utils::createRandomString();
        }

        $login = \OmegaUp\Test\ControllerTestCase::login($groupData['owner']);
        $request = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['group']->alias,
            'name' => $name,
            'alias' => $alias,
            'description' => $description
        ]);
        $response = \OmegaUp\Controllers\Group::apiCreateScoreboard($request);

        $scoreboard = \OmegaUp\DAO\GroupsScoreboards::getByAlias($alias);
        if (is_null($scoreboard)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

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
        $login = \OmegaUp\Test\ControllerTestCase::login($groupData['owner']);
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
