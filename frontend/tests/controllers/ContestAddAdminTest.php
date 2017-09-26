<?php

/**
 * Description of ContestAddAdminTest
 *
 * @author joemmanuel
 */
class ContestAddAdminTest extends OmegaupTestCase {
    public function testAddContestAdmin() {
        // Get a contest
        $contestData = ContestsFactory::createContest([]);

        // Get a user
        $user = UserFactory::createUser();

        // Prepare request
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        $response = ContestController::apiAddAdmin($r);

        // Get the role
        $contest = $contestData['contest'];
        $ur = UserRolesDAO::getByPK($user->user_id, Authorization::ADMIN_ROLE, $contest->acl_id);

        $this->assertNotNull($ur);
    }

    public function testIsContestAdminCheck() {
        // Get a contest
        $contestData = ContestsFactory::createContest([]);

        // Get a user
        $user = UserFactory::createUser();

        // Prepare request
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        ContestController::apiAddAdmin($r);
        unset($login);

        // Prepare request for an update
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];

        // Log in with contest director
        $login = self::login($user);
        $r['auth_token'] = $login->auth_token;

        // Update title
        $r['title'] = Utils::CreateRandomString();

        // Call API
        $response = ContestController::apiUpdate($r);

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
        $contestData = ContestsFactory::createContest([]);

        // Get users
        $user = UserFactory::createUser();
        $user2 = UserFactory::createUser();

        ContestsFactory::addAdminUser($contestData, $user);
        ContestsFactory::addAdminUser($contestData, $user2);

        // Prepare request for remove one admin
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        ContestController::apiRemoveAdmin($r);

        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $this->AssertFalse(Authorization::isContestAdmin($user->user_id, $contest));
        $this->AssertTrue(Authorization::isContestAdmin($user2->user_id, $contest));
    }

    public function testAddContestGroupAdmin() {
        // Get a contest
        $contestData = ContestsFactory::createContest([]);

        // Get a user
        $user = UserFactory::createUser();

        // Get a group
        $groupData = GroupsFactory::createGroup();
        GroupsFactory::addUserToGroup($groupData, $user);

        // Prepare request
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'group' => $groupData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        $response = ContestController::apiAddGroupAdmin($r);

        // Get the role
        $ur = GroupRolesDAO::getByPK($groupData['group']->group_id, Authorization::ADMIN_ROLE, $contestData['contest']->acl_id);

        $this->assertNotNull($ur);
    }

    public function testIsContestGroupAdminCheck() {
        // Get a contest
        $contestData = ContestsFactory::createContest([]);

        // Get a user
        $user = UserFactory::createUser();

        // Get a group
        $groupData = GroupsFactory::createGroup();
        GroupsFactory::addUserToGroup($groupData, $user);

        // Prepare request
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'group' => $groupData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        ContestController::apiAddGroupAdmin($r);
        unset($login);

        // Prepare request for an update
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];

        // Log in with contest director
        $login = self::login($user);
        $r['auth_token'] = $login->auth_token;

        // Update title
        $r['title'] = Utils::CreateRandomString();

        // Call API
        $response = ContestController::apiUpdate($r);

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
        $contestData = ContestsFactory::createContest([]);

        // Get users
        $user = UserFactory::createUser();
        $user2 = UserFactory::createUser();

        // Get a group
        $groupData = GroupsFactory::createGroup();
        GroupsFactory::addUserToGroup($groupData, $user);
        GroupsFactory::addUserToGroup($groupData, $user2);

        // Prepare request
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'group' => $groupData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        ContestController::apiAddGroupAdmin($r);
        unset($login);

        $contest = $contestData['contest'];
        $this->AssertTrue(Authorization::isContestAdmin($user->user_id, $contest));
        $this->AssertTrue(Authorization::isContestAdmin($user2->user_id, $contest));

        // Prepare request for remove the group
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'group' => $groupData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api
        ContestController::apiRemoveGroupAdmin($r);

        $this->AssertFalse(Authorization::isContestAdmin($user->user_id, $contest));
        $this->AssertFalse(Authorization::isContestAdmin($user2->user_id, $contest));
    }
}
