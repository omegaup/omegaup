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
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser(
            new UserParams(
                ['username' => 'testuser1']
            )
        );

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayNotHasKey('password', $response['userinfo']);
        $this->assertEquals(
            $identity->username,
            $response['userinfo']['username']
        );
    }

    /*
     * Test for the function which returns the general user info
     */
    public function testUserDataAnotherUser() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser(
            new UserParams(
                ['username' => 'testuser2']
            )
        );
        ['user' => $user2, 'identity' => $identity2] = UserFactory::createUser(
            new UserParams(
                ['username' => 'testuser3']
            )
        );

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity2->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayNotHasKey('password', $response['userinfo']);
        $this->assertArrayNotHasKey('email', $response['userinfo']);
        $this->assertEquals(
            $identity2->username,
            $response['userinfo']['username']
        );
    }

    /*
     * Test apiProfile with is_private enabled
     */
    public function testUserPrivateDataAnotherUser() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        // Mark user2's profile as private (5th argument)
        ['user' => $user2, 'identity' => $identity2] = UserFactory::createUser(
            new UserParams(
                ['is_private' => true]
            )
        );

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity2->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $visibleAttributes = ['is_private', 'username', 'rankinfo', 'classname'];
        foreach ($response['userinfo'] as $k => $v) {
            if (in_array($k, $visibleAttributes)) {
                continue;
            }
            $this->assertNull($v);
        }
        foreach ($response['userinfo']['rankinfo'] as $k => $v) {
            if ($k == 'status') {
                continue;
            }
            $this->assertNull($v);
        }
        $this->assertEquals(
            $identity2->username,
            $response['userinfo']['username']
        );
    }

    /*
     * Test admin can see emails for all non-private profiles
     */
    public function testAdminCanSeeEmails() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        ['user' => $admin, 'identity' => $identityAdmin] = UserFactory::createAdminUser();

        $login = self::login($identityAdmin);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayHasKey('email', $response['userinfo']);
    }

    /*
     * Test admin can see all details for private profiles
     */
    public function testAdminCanSeePrivateProfile() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser(
            new UserParams(['is_private' => true])
        );
        ['user' => $admin, 'identity' => $identityAdmin] = UserFactory::createAdminUser();

        $login = self::login($identityAdmin);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayHasKey('email', $response['userinfo']);
        $visibleAttributes = ['email', 'gravatar_92', 'name', 'username', 'rankinfo'];
        foreach ($response['userinfo'] as $k => $v) {
            if (in_array($k, $visibleAttributes)) {
                $this->assertNotNull($v);
            }
        }
        foreach ($response['userinfo']['rankinfo'] as $k => $v) {
            $this->assertNotNull($v);
        }
    }

    /*
     * User can see his own email
     */
    public function testUserCanSeeSelfEmail() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayHasKey('email', $response['userinfo']);
    }

    /*
     * Test the contest which a certain user has participated
     */
    public function testUserContests() {
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        $contests = [];
        $contests[0] = ContestsFactory::createContest();
        $contests[1] = ContestsFactory::createContest();

        ContestsFactory::addUser($contests[0], $identity);
        ContestsFactory::addUser($contests[1], $identity);

        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contests[0]);

        $runData = RunsFactory::createRun(
            $problemData,
            $contests[0],
            $identity
        );
        RunsFactory::gradeRun($runData);

        // Get ContestStats
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiContestStats(new \OmegaUp\Request(
            [
                    'auth_token' => $login->auth_token,
                ]
        ));

        // Result should be 1 since user has only actually participated in 1 contest (submitted run)
        $this->assertEquals(1, count($response['contests']));
    }

    /*
     * Test the contest which a certain user has participated.
     * API can be accessed by a user who cannot see the contest (contest is private)
     */
    public function testUserContestsPrivateContestOutsider() {
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        $contests = [];
        $contests[0] = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );
        $contests[1] = ContestsFactory::createContest();

        ContestsFactory::addUser($contests[0], $identity);
        ContestsFactory::addUser($contests[1], $identity);

        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contests[0]);

        $runData = RunsFactory::createRun(
            $problemData,
            $contests[0],
            $identity
        );
        RunsFactory::gradeRun($runData);

        ['user' => $externalUser, 'identity' => $externalIdentity] = UserFactory::createUser();

        $login = self::login($externalIdentity);
        // Get ContestStats
        $response = \OmegaUp\Controllers\User::apiContestStats(new \OmegaUp\Request(
            [
                    'auth_token' => $login->auth_token,
                    'username' => $identity->username
                ]
        ));

        // Result should be 1 since user has only actually participated in 1 contest (submitted run)
        $this->assertEquals(1, count($response['contests']));
    }

    /*
     * Test the problems solved by user
     */
    public function testProblemsSolved() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $contest = ContestsFactory::createContest();

        $problemOne = ProblemsFactory::createProblem();
        $problemTwo = ProblemsFactory::createProblem();

        ContestsFactory::addProblemToContest($problemOne, $contest);
        ContestsFactory::addProblemToContest($problemTwo, $contest);

        ContestsFactory::addUser($contest, $identity);

        //Submission gap between runs must be 60 seconds
        $runs = [];
        $runs[0] = RunsFactory::createRun($problemOne, $contest, $identity);
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runs[1] = RunsFactory::createRun($problemTwo, $contest, $identity);
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runs[2] = RunsFactory::createRun($problemOne, $contest, $identity);

        RunsFactory::gradeRun($runs[0]);
        RunsFactory::gradeRun($runs[1]);
        RunsFactory::gradeRun($runs[2]);

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);

        $response = \OmegaUp\Controllers\User::apiProblemsSolved($r);

        $this->assertEquals(2, count($response['problems']));
    }

    /**
     * Test update main email api
     */
    public function testUpdateMainEmail() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'email' => 'new@email.com'
        ]);
        $response = \OmegaUp\Controllers\User::apiUpdateMainEmail($r);

        // Check email in db
        $user_in_db = \OmegaUp\DAO\Users::findByEmail('new@email.com');
        $this->assertEquals($user->user_id, $user_in_db->user_id);
    }

    /**
     * Test update main email api
     */
    public function testStats() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $problem = ProblemsFactory::createProblem();

        $login = self::login($identity);
        {
            $run = RunsFactory::createRunToProblem($problem, $user, $login);
            RunsFactory::gradeRun($run, 0.0, 'CE');
        }
        {
            $run = RunsFactory::createRunToProblem($problem, $user, $login);
            RunsFactory::gradeRun($run, 0.5, 'PA');
        }
        {
            $run = RunsFactory::createRunToProblem($problem, $user, $login);
            RunsFactory::gradeRun($run);
        }

        $response = \OmegaUp\Controllers\User::apiStats(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        foreach (['CE', 'PA', 'AC'] as $verdict) {
            $this->assertEquals(
                1,
                $this->findByPredicate($response['runs'], function ($run) use ($verdict) {
                    return $run['verdict'] == $verdict;
                })['runs']
            );
        }
    }
}
