<?php

/**
 * Test getting general user Info methods
 *
 * @author Alberto
 */
class UserProfileTest extends OmegaupTestCase {
    /*
	 * Test for the function which returns the general user info
	 */
    public function testUserData() {
        $user = UserFactory::createUser('testuser1');

        $r = new Request(array(
            'auth_token' => self::login($user)
        ));
        $response = UserController::apiProfile($r);

        $this->assertArrayNotHasKey('password', $response['userinfo']);
        $this->assertEquals($user->getUsername(), $response['userinfo']['username']);
    }

    /*
	 * Test for the function which returns the general user info
	 */
    public function testUserDataAnotherUser() {
        $user = UserFactory::createUser('testuser3');
        $user2 = UserFactory::createUser('testuser4');

        $r = new Request(array(
            'auth_token' => self::login($user),
            'username' => $user2->getUsername()
        ));
        $response = UserController::apiProfile($r);

        $this->assertArrayNotHasKey('password', $response['userinfo']);
        $this->assertEquals($user2->getUsername(), $response['userinfo']['username']);
    }

    /*
	 * Test the contest which a certain user has participated
	 */
    public function testUserContests() {
        $contestant = UserFactory::createUser();

        $contests = array();
        $contests[0] = ContestsFactory::createContest();
        $contests[1] = ContestsFactory::createContest();

        ContestsFactory::addUser($contests[0], $contestant);
        ContestsFactory::addUser($contests[1], $contestant);

        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contests[0]);

        $runData = RunsFactory::createRun($problemData, $contests[0], $contestant);
        RunsFactory::gradeRun($runData);

        // Get ContestStats
        $response = UserController::apiContestStats(new Request(
            array(
                    'auth_token' => self::login($contestant)
                )
        ));

        // Result should be 1 since user has only actually participated in 1 contest (submitted run)
        $this->assertEquals(1, count($response['contests']));
    }

    /*
	 * Test the contest which a certain user has participated.
	 * API can be accessed by a user who cannot see the contest (contest is private)
	 */
    public function testUserContestsPrivateContestOutsider() {
        $contestant = UserFactory::createUser();

        $contests = array();
        $contests[0] = ContestsFactory::createContest(null /*title*/, 0 /*public*/);
        $contests[1] = ContestsFactory::createContest();

        ContestsFactory::addUser($contests[0], $contestant);
        ContestsFactory::addUser($contests[1], $contestant);

        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contests[0]);

        $runData = RunsFactory::createRun($problemData, $contests[0], $contestant);
        RunsFactory::gradeRun($runData);

        $externalUser = UserFactory::createUser();

        // Get ContestStats
        $response = UserController::apiContestStats(new Request(
            array(
                    'auth_token' => self::login($externalUser),
                    'username' => $contestant->getUsername()
                )
        ));

        // Result should be 1 since user has only actually participated in 1 contest (submitted run)
        $this->assertEquals(1, count($response['contests']));
    }

    /*
	 * Test the problems solved by user
	 */
    public function testProblemsSolved() {
        $user = UserFactory::createUser();

        $contest = ContestsFactory::createContest();

        $problemOne = ProblemsFactory::createProblem();
        $problemTwo = ProblemsFactory::createProblem();

        ContestsFactory::addProblemToContest($problemOne, $contest);
        ContestsFactory::addProblemToContest($problemTwo, $contest);

        ContestsFactory::addUser($contest, $user);

        $runs = array();
        $runs[0] = RunsFactory::createRun($problemOne, $contest, $user);
        $runs[1] = RunsFactory::createRun($problemTwo, $contest, $user);
        $runs[2] = RunsFactory::createRun($problemOne, $contest, $user);

        RunsFactory::gradeRun($runs[0]);
        RunsFactory::gradeRun($runs[1]);
        RunsFactory::gradeRun($runs[2]);

        $r = new Request(array(
            'auth_token' => self::login($user)
        ));

        $response = UserController::apiProblemsSolved($r);

        $this->assertEquals(2, count($response['problems']));
    }

    /**
     * Test update main email api
     */
    public function testUpdateMainEmail() {
        $user = UserFactory::createUser();

        $r = new Request(array(
            'auth_token' => self::login($user),
            'email' => 'new@email.com'
        ));
        $response = UserController::apiUpdateMainEmail($r);

        // Check email in db
        $user_in_db = UsersDAO::FindByEmail('new@email.com');
        $this->assertEquals($user->getUserId(), $user_in_db->getUserId());
    }
}
