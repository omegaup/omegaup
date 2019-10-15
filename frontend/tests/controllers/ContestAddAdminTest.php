<?php

/**
 * Description of ContestAddAdminTest
 *
 * @author joemmanuel
 */
class ContestAddAdminTest extends OmegaupTestCase {
    public function testAddContestAdmin() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Get a user
        $user = UserFactory::createUser();

        // Prepare request
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
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
        $contestData = ContestsFactory::createContest();

        // Get a user
        $user = UserFactory::createUser();

        // Prepare request
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        \OmegaUp\Controllers\Contest::apiAddAdmin($r);
        unset($login);

        // Prepare request for an update
        $r = new \OmegaUp\Request();
        $r['contest_alias'] = $contestData['request']['alias'];

        // Log in with contest director
        $login = self::login($user);
        $r['auth_token'] = $login->auth_token;

        // Update title
        $r['title'] = Utils::CreateRandomString();

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiUpdate($r);

        // To validate, we update the title to the original request and send
        // the entire original request to assertContest. Any other parameter
        // should not be modified by Update api
        $contestData['request']['title'] = $r['title'];
        $this->assertContest($contestData['request']);
    }

    /**
     * Tests remove admins
     */
    public function testRemoveAdmin() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Get users
        $user = UserFactory::createUser();
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
        $user2 = UserFactory::createUser();
        $identity2 = \OmegaUp\DAO\Identities::getByPK($user2->main_identity_id);
        ContestsFactory::addAdminUser($contestData, $user);
        ContestsFactory::addAdminUser($contestData, $user2);

        // Prepare request for remove one admin
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
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
        $contestData = ContestsFactory::createContest();

        // Get a user
        $user = UserFactory::createUser();

        // Get a group
        $groupData = GroupsFactory::createGroup();
        GroupsFactory::addUserToGroup($groupData, $user);

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
        $contestData = ContestsFactory::createContest();

        // Get a user
        $user = UserFactory::createUser();

        // Get a group
        $groupData = GroupsFactory::createGroup();
        GroupsFactory::addUserToGroup($groupData, $user);

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

        // Prepare request for an update
        $r = new \OmegaUp\Request();
        $r['contest_alias'] = $contestData['request']['alias'];

        // Log in with contest director
        $login = self::login($user);
        $r['auth_token'] = $login->auth_token;

        // Update title
        $r['title'] = Utils::CreateRandomString();

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
        $contestData = ContestsFactory::createContest();

        // Get users
        $user = UserFactory::createUser();
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
        $user2 = UserFactory::createUser();
        $identity2 = \OmegaUp\DAO\Identities::getByPK($user2->main_identity_id);

        // Get a group
        $groupData = GroupsFactory::createGroup();
        GroupsFactory::addUserToGroup($groupData, $user);
        GroupsFactory::addUserToGroup($groupData, $user2);

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
