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
        $r = new Request();
        $r['auth_token'] = $this->login($contestData['director']);
        $r['usernameOrEmail'] = $user->getUsername();
        $r['contest_alias'] = $contestData['request']['alias'];

        // Call api
        $response = ContestController::apiAddAdmin($r);

        // Get the role
        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        $ur = UserRolesDAO::getByPK($user->getUserId(), CONTEST_ADMIN_ROLE, $contest->getContestId());

        $this->assertNotNull($ur);
    }

    public function testIsContestAdminCheck() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Get a user
        $user = UserFactory::createUser();

        // Prepare request
        $r = new Request();
        $r['auth_token'] = $this->login($contestData['director']);
        $r['usernameOrEmail'] = $user->getUsername();
        $r['contest_alias'] = $contestData['request']['alias'];

        // Call api
        ContestController::apiAddAdmin($r);

        // Prepare request for an update
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];

        // Log in with contest director
        $r['auth_token'] = $this->login($user);

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
        $contestData = ContestsFactory::createContest();

        // Get users
        $user = UserFactory::createUser();
        $user2 = UserFactory::createUser();

        ContestsFactory::addAdminUser($contestData, $user);
        ContestsFactory::addAdminUser($contestData, $user2);

        // Prepare request for remove one admin
        $r = new Request();
        $r['auth_token'] = $this->login($contestData['director']);
        $r['usernameOrEmail'] = $user->getUsername();
        $r['contest_alias'] = $contestData['request']['alias'];

        // Call api
        ContestController::apiRemoveAdmin($r);

        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $this->AssertFalse(Authorization::IsContestAdmin($user->getUserId(), $contest));
        $this->AssertTrue(Authorization::IsContestAdmin($user2->getUserId(), $contest));
    }
}
