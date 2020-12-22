<?php

/**
 * Description of ContestAddAdminTest
 *
 * @author joemmanuel
 */
class ContestAddAdminTest extends \OmegaUp\Test\ControllerTestCase {
    public function testAddContestAdmin() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a user
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Prepare request
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $identity->username,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        $response = \OmegaUp\Controllers\Contest::apiAddAdmin($r);

        // Get the role
        $contest = $contestData['contest'];
        $ur = \OmegaUp\DAO\UserRoles::getByPK(
            $user->user_id,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $contest->acl_id
        );

        $this->assertNotNull($ur);
    }

    public function testIsContestAdminCheck() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a user
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Prepare request
        $login = self::login($contestData['director']);

        // Call api
        \OmegaUp\Controllers\Contest::apiAddAdmin(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $identity->username,
            'contest_alias' => $contestData['request']['alias'],
        ]));
        unset($login);

        // Log in with contest director
        $login = self::login($identity);

        // Update title
        $contestData['request']['title'] = \OmegaUp\Test\Utils::createRandomString();
        $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'title' => $contestData['request']['title'],
            'languages' => 'c11-gcc',
        ]));

        // To validate, we update the title to the original request and send
        // the entire original request to assertContest. Any other parameter
        // should not be modified by Update api
        $this->assertContest($contestData['request']);
    }

    /**
     * Tests remove admins
     */
    public function testRemoveAdmin() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get users
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $user2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::addAdminUser($contestData, $identity);
        \OmegaUp\Test\Factories\Contest::addAdminUser($contestData, $identity2);

        // Prepare request for remove one admin
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $identity->username,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        \OmegaUp\Controllers\Contest::apiRemoveAdmin($r);

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $this->AssertFalse(
            \OmegaUp\Authorization::isContestAdmin(
                $identity,
                $contest
            )
        );
        $this->AssertTrue(
            \OmegaUp\Authorization::isContestAdmin(
                $identity2,
                $contest
            )
        );
    }

    public function testAddContestGroupAdmin() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a user
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Get a group
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        \OmegaUp\Test\Factories\Groups::addUserToGroup($groupData, $identity);

        // Prepare request
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group' => $groupData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        $response = \OmegaUp\Controllers\Contest::apiAddGroupAdmin($r);

        // Get the role
        $ur = \OmegaUp\DAO\GroupRoles::getByPK(
            $groupData['group']->group_id,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $contestData['contest']->acl_id
        );

        $this->assertNotNull($ur);
    }

    public function testIsContestGroupAdminCheck() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a user
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Get a group
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        \OmegaUp\Test\Factories\Groups::addUserToGroup($groupData, $identity);

        // Prepare request
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group' => $groupData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
            'languages' => 'c11-gcc',
        ]);

        // Call api
        \OmegaUp\Controllers\Contest::apiAddGroupAdmin($r);
        unset($login);

        // Prepare request for an update
        $r = new \OmegaUp\Request();
        $r['contest_alias'] = $contestData['request']['alias'];

        // Log in with contest director
        $login = self::login($identity);
        $r['auth_token'] = $login->auth_token;

        // Update title
        $r['title'] = \OmegaUp\Test\Utils::createRandomString();

        // Update Languages
        $r['languages'] = 'c11-gcc';

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiUpdate($r);

        // To validate, we update the title to the original request and send
        // the entire original request to assertContest. Any other parameter
        // should not be modified by Update api
        $contestData['request']['title'] = $r['title'];
        $this->assertContest($contestData['request']);
    }

    /**
     * Tests remove group admins
     */
    public function testRemoveGroupAdmin() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get users
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $user2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        // Get a group
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        \OmegaUp\Test\Factories\Groups::addUserToGroup($groupData, $identity);
        \OmegaUp\Test\Factories\Groups::addUserToGroup($groupData, $identity2);

        // Prepare request
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group' => $groupData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        \OmegaUp\Controllers\Contest::apiAddGroupAdmin($r);
        unset($login);

        $contest = $contestData['contest'];
        $this->AssertTrue(
            \OmegaUp\Authorization::isContestAdmin(
                $identity,
                $contest
            )
        );
        $this->AssertTrue(
            \OmegaUp\Authorization::isContestAdmin(
                $identity2,
                $contest
            )
        );

        // Prepare request for remove the group
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group' => $groupData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        \OmegaUp\Controllers\Contest::apiRemoveGroupAdmin($r);

        $this->AssertFalse(
            \OmegaUp\Authorization::isContestAdmin(
                $identity,
                $contest
            )
        );
        $this->AssertFalse(
            \OmegaUp\Authorization::isContestAdmin(
                $identity2,
                $contest
            )
        );
    }
}
