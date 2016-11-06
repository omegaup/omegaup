<?php

/**
 * Description of UserContestsTest
 *
 * @author joemmanuel
 */
class UserContestsTest extends OmegaupTestCase {
    /**
     * Get contests where user is the director
     */
    public function testDirectorList() {
        // Our director
        $director = UserFactory::createUser();

        $contestData[0] = ContestsFactory::createContest(null /*title*/, 1 /*public*/, $director);
        $contestData[1] = ContestsFactory::createContest(null /*title*/, 1 /*public*/, $director);

        // Call api
        $r = new Request(array(
            'auth_token' => self::login($director)
        ));
        $response = UserController::apiContests($r);

        // Contests should come ordered by contest id desc
        $this->assertEquals(count($contestData), count($response['contests']));
        $this->assertEquals($contestData[1]['request']['alias'], $response['contests'][0]['alias']);
        $this->assertEquals($contestData[0]['request']['alias'], $response['contests'][1]['alias']);
    }

    /**
     * Test getting list of contests where the user is the admin
     */
    public function testAdminList() {
        // Our director
        $director = UserFactory::createUser();
        $contestAdminData = array();

        // Get two contests with another director, add $director to their
        // admin list
        $contestAdminData[0] = ContestsFactory::createContest();
        ContestsFactory::addAdminUser($contestAdminData[0], $director);

        // Get two contests with another director, add $director to their
        // group admin list
        $contestAdminData[1] = ContestsFactory::createContest();
        $group = GroupsFactory::createGroup($contestAdminData[1]['director']);
        GroupsFactory::addUserToGroup($group, $director);
        ContestsFactory::addGroupAdmin($contestAdminData[1], $group['group']);

        $contestDirectorData[0] = ContestsFactory::createContest(null /*title*/, 1 /*public*/, $director);
        $contestDirectorData[1] = ContestsFactory::createContest(null /*title*/, 0 /*public*/, $director);

        // Call api
        $login = self::login($director);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
        ));
        $response = ContestController::apiAdminList($r);

        // Contests should come ordered by contest id desc
        $this->assertEquals(count($contestDirectorData) + count($contestAdminData), count($response['contests']));
        $this->assertEquals($contestDirectorData[1]['request']['alias'], $response['contests'][0]['alias']);
        $this->assertEquals($contestDirectorData[0]['request']['alias'], $response['contests'][1]['alias']);
        $this->assertEquals($contestAdminData[1]['request']['alias'], $response['contests'][2]['alias']);
        $this->assertEquals($contestAdminData[0]['request']['alias'], $response['contests'][3]['alias']);
    }

    /**
     * Test ContestsDAO::getPrivateContestsCount when there's 1 private contest
     * count
     */
    public function testPrivateContestsCount() {
        // Create private contest
        $contestData = ContestsFactory::createContest(null, 0 /*public*/);
        $user = $contestData['director'];

        $this->assertEquals(1, ContestsDAO::getPrivateContestsCount($user));
    }

    /**
     * Test ContestsDAO::getPrivateContestsCount when there's 1 public contest
     */
    public function testPrivateContestsCountWithPublicContest() {
        // Create private contest
        $contestData = ContestsFactory::createContest(null, 1 /*public*/);
        $user = $contestData['director'];

        $this->assertEquals(0, ContestsDAO::getPrivateContestsCount($user));
    }

    /**
     * Test ContestsDAO::getPrivateContestsCount when there's 0 contests
     * created
     */
    public function testPrivateContestsCountWithNoContests() {
        $user = UserFactory::createUser();

        $this->assertEquals(0, ContestsDAO::getPrivateContestsCount($user));
    }
}
