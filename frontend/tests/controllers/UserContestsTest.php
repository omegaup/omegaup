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

        $contestFactory = new ContestsFactory(new ContestsParams(['contestDirector' => $director]));
        $contestData[0] = $contestFactory->createContest();
        $contestFactory = new ContestsFactory(new ContestsParams(['contestDirector' => $director]));
        $contestData[1] = $contestFactory->createContest();

        // Call api
        $login = self::login($director);
        $r = new Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = ContestController::apiMyList($r);

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
        $contestAdminData = [];

        // Create a group with two arbitrary users.
        $helperGroup = GroupsFactory::createGroup($director);
        GroupsFactory::addUserToGroup($helperGroup, UserFactory::createUser());
        GroupsFactory::addUserToGroup($helperGroup, UserFactory::createUser());

        $contestFactory = new ContestsFactory(new ContestsParams([]));
        // Get two contests with another director, add $director to their
        // admin list
        $contestAdminData[0] = $contestFactory->createContest();
        ContestsFactory::addAdminUser($contestAdminData[0], $director);
        ContestsFactory::addGroupAdmin($contestAdminData[0], $helperGroup['group']);

        // Get two contests with another director, add $director to their
        // group admin list
        $contestFactory = new ContestsFactory(new ContestsParams([]));
        $contestAdminData[1] = $contestFactory->createContest();
        $group = GroupsFactory::createGroup($contestAdminData[1]['director']);
        GroupsFactory::addUserToGroup($group, $director);
        GroupsFactory::addUserToGroup($group, UserFactory::createUser());
        ContestsFactory::addGroupAdmin($contestAdminData[1], $group['group']);
        ContestsFactory::addGroupAdmin($contestAdminData[1], $helperGroup['group']);

        $contestFactory = new ContestsFactory(new ContestsParams(['contestDirector' => $director]));
        $contestDirectorData[0] = $contestFactory->createContest();
        ContestsFactory::addGroupAdmin($contestDirectorData[0], $helperGroup['group']);
        $contestFactory = new ContestsFactory(new ContestsParams(['contestDirector' => $director, 'public' => 0]));
        $contestDirectorData[1] = $contestFactory->createContest();
        ContestsFactory::addGroupAdmin($contestDirectorData[1], $helperGroup['group']);

        // Call api
        $login = self::login($director);
        $r = new Request([
            'auth_token' => $login->auth_token,
        ]);
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
        $contestFactory = new ContestsFactory(new ContestsParams(['public' => 0]));
        $contestData = $contestFactory->createContest();
        $user = $contestData['director'];

        $this->assertEquals(1, ContestsDAO::getPrivateContestsCount($user));
    }

    /**
     * Test ContestsDAO::getPrivateContestsCount when there's 1 public contest
     */
    public function testPrivateContestsCountWithPublicContest() {
        // Create private contest
        $contestFactory = new ContestsFactory(new ContestsParams([]));
        $contestData = $contestFactory->createContest();
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
