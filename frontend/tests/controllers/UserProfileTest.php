<?php

/**
 * Test getting general user Info methods
 *
 * @author Alberto
 */
class UserProfileTest extends \OmegaUp\Test\ControllerTestCase {
    /*
     * Test for the function which returns the general user info
     */
    public function testUserData() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'testuser1']
            )
        );

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayNotHasKey('password', $response);
        $this->assertEquals(
            $identity->username,
            $response['username']
        );
    }

    /*
     * Test for the function which returns the general user info
     */
    public function testUserDataAnotherUser() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'testuser2']
            )
        );
        ['user' => $user2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'testuser3']
            )
        );

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity2->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayNotHasKey('password', $response);
        $this->assertArrayNotHasKey('email', $response);
        $this->assertEquals(
            $identity2->username,
            $response['username']
        );
    }

    /*
     * Test apiProfile with is_private enabled
     */
    public function testUserPrivateDataAnotherUser() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        // Mark user2's profile as private (5th argument)
        ['user' => $user2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['isPrivate' => true]
            )
        );

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity2->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $visibleAttributes = [
            'is_private',
            'username',
            'rankinfo',
            'classname',
            'hide_problem_tags',
            'verified',
            'programming_languages',
        ];
        foreach ($response as $k => $v) {
            if (in_array($k, $visibleAttributes)) {
                continue;
            }
            $this->assertNull($v);
        }
        foreach ($response['rankinfo'] as $k => $v) {
            if ($k == 'status') {
                continue;
            }
            $this->assertNull($v);
        }
        $this->assertEquals(
            $identity2->username,
            $response['username']
        );
    }

    /*
     * Test admin can see emails for all non-private profiles
     */
    public function testAdminCanSeeEmails() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $admin, 'identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();

        $login = self::login($identityAdmin);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayHasKey('email', $response);
    }

    /*
     * Test admin can see all details for private profiles
     */
    public function testAdminCanSeePrivateProfile() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['isPrivate' => true])
        );
        ['user' => $admin, 'identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();

        $login = self::login($identityAdmin);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayHasKey('email', $response);
        $visibleAttributes = ['email', 'gravatar_92', 'name', 'username', 'rankinfo'];
        foreach ($response as $k => $v) {
            if (in_array($k, $visibleAttributes)) {
                $this->assertNotNull($v);
            }
        }
        $this->assertNull($response['rankinfo']['author_ranking']);
        unset($response['rankinfo']['author_ranking']);
        foreach ($response['rankinfo'] as $k => $v) {
            $this->assertNotNull($v);
        }
    }

    /*
     * User can see his own email
     */
    public function testUserCanSeeSelfEmail() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayHasKey('email', $response);
    }

    /*
     * Test the contest which a certain user has participated
     */
    public function testUserContests() {
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $contests = [];
        $contests[0] = \OmegaUp\Test\Factories\Contest::createContest();
        $contests[1] = \OmegaUp\Test\Factories\Contest::createContest();

        \OmegaUp\Test\Factories\Contest::addUser($contests[0], $identity);
        \OmegaUp\Test\Factories\Contest::addUser($contests[1], $identity);

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contests[0]
        );

        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contests[0],
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Get ContestStats
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiContestStats(new \OmegaUp\Request(
            [
                'auth_token' => $login->auth_token,
            ]
        ));

        // Result should be 1 since user has only actually participated in 1 contest (submitted run)
        $this->assertEquals(1, count($response['contests']));
        $alias = $contests[0]['contest']->alias;
        $this->assertEquals(
            $alias,
            $response['contests'][$alias]['data']['alias']
        );
        $this->assertArrayHasKey(
            'title',
            $response['contests'][$alias]['data']
        );
        $this->assertArrayNotHasKey(
            'contest_id',
            $response['contests'][$alias]['data']
        );
        $this->assertArrayNotHasKey(
            'scoreboard_url_admin',
            $response['contests'][$alias]['data']
        );
    }

    /*
     * Test the contest which a certain user has participated.
     * API can be accessed by a user who cannot see the contest (contest is private)
     */
    public function testUserContestsPrivateContestOutsider() {
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $contests = [];
        $contests[0] = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $contests[1] = \OmegaUp\Test\Factories\Contest::createContest();

        \OmegaUp\Test\Factories\Contest::addUser($contests[0], $identity);
        \OmegaUp\Test\Factories\Contest::addUser($contests[1], $identity);

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contests[0]
        );

        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contests[0],
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        ['user' => $externalUser, 'identity' => $externalIdentity] = \OmegaUp\Test\Factories\User::createUser();

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
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $contest = \OmegaUp\Test\Factories\Contest::createContest();

        $problemOne = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemTwo = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemOne,
            $contest
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemTwo,
            $contest
        );

        \OmegaUp\Test\Factories\Contest::addUser($contest, $identity);

        //Submission gap between runs must be 60 seconds
        $runs = [];
        $runs[0] = \OmegaUp\Test\Factories\Run::createRun(
            $problemOne,
            $contest,
            $identity
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runs[1] = \OmegaUp\Test\Factories\Run::createRun(
            $problemTwo,
            $contest,
            $identity
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runs[2] = \OmegaUp\Test\Factories\Run::createRun(
            $problemOne,
            $contest,
            $identity
        );

        \OmegaUp\Test\Factories\Run::gradeRun($runs[0]);
        \OmegaUp\Test\Factories\Run::gradeRun($runs[1]);
        \OmegaUp\Test\Factories\Run::gradeRun($runs[2]);

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);

        $response = \OmegaUp\Controllers\User::apiProblemsSolved($r);

        $this->assertEquals(2, count($response['problems']));
    }

    /*
     * Test the problems solved by user
     */
    public function testProblemsCreated() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        $problems = [];
        for ($i = 0; $i < 3; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
                $identity,
                $login
            );
        }

        // As all problems are public, this function should retrieve 10 records
        $response = \OmegaUp\Controllers\User::apiProblemsCreated(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals(count($problems), count($response['problems']));

        // Now make one of those problems private, results must change
        \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problems[0]['problem']->alias,
            'visibility' => 'private',
            'message' => 'public -> private',
        ]));
        $response = \OmegaUp\Controllers\User::apiProblemsCreated(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $expectedProblemCount = count($problems) - 1;
        $this->assertEquals(
            $expectedProblemCount,
            count(
                $response['problems']
            )
        );

        // Now, as another user, request the problems created by initial user
        ['user' => $otherUser, 'identity' => $otherIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($otherIdentity);

        $response = \OmegaUp\Controllers\User::apiProblemsCreated(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]));
        $this->assertEquals(
            $expectedProblemCount,
            count(
                $response['problems']
            )
        );
    }

    /**
     * Test update main email api
     */
    public function testUpdateMainEmail() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiUpdateMainEmail(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'email' => 'new@email.com'
            ])
        );

        // Check email in db
        $user_in_db = \OmegaUp\DAO\Users::findByEmail('new@email.com');
        $this->assertEquals($user->user_id, $user_in_db->user_id);
    }

    /**
     * Test update main email api
     */
    public function testStats() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        $login = self::login($identity);
        {
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problem,
                $identity,
                $login
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run, 0.0, 'CE');
        }
        {
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problem,
                $identity,
                $login
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run, 0.5, 'PA');
        }
        {
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problem,
                $identity,
                $login
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run);
        }

        $response = \OmegaUp\Controllers\User::apiStats(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        foreach (['CE', 'PA', 'AC'] as $verdict) {
            $this->assertEquals(
                1,
                $this->findByPredicate(
                    $response['runs'],
                    fn ($run) => $run['verdict'] == $verdict
                )['runs']
            );
        }
    }

    /**
     * A PHPUnit data provider for all the tests that can accept a status.
     *
     * @return list<array{0: string, 1: string}>
     */
    public function qualityNominationsDemotionStatusProvider(): array {
        return [
            ['banned', 'private_banned'],
            ['warning', 'private_warning'],
        ];
    }

    /**
     * Check that can search nominations.
     * @dataProvider qualityNominationsDemotionStatusProvider
     */
    public function testCreatedProblemWithDemotionNomination(
        string $status,
        string $visibilityPrivate
    ) {
        ['identity' => $author] = \OmegaUp\Test\Factories\User::createUser(new \OmegaUp\Test\Factories\UserParams(
            [
                'username' => 'user_test_author'
            ]
        ));
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams(
            [
                'author' => $author,
                'title' => 'problem_1'
            ]
        ));
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(new \OmegaUp\Test\Factories\UserParams(
            [
                'username' => 'user_test_nominator'
            ]
        ));
        $login = self::login($identity);

        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                ],
                'rationale' => 'qwert',
                'reason' => 'offensive',
            ]),
        ]));

        \OmegaUp\Test\Factories\QualityNomination::initQualityReviewers();
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'status' => $status,
                'problem_alias' => $problemData['request']['problem_alias'],
                'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
                'rationale' => 'ew plus something else',
            ])
        );

        $login = self::login($author);
        // Since the problem is public, this function should retrieve 1 problem.
        $response = \OmegaUp\Controllers\User::apiProblemsCreated(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertCount(1, $response['problems']);
        $this->assertEquals('problem_1', $response['problems'][0]['alias']);

        // Now make one of those problems private, results must change
        \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' =>  $problemData['request']['problem_alias'],
            'visibility' => $visibilityPrivate,
            'message' => 'public -> private',
        ]));
        $response = \OmegaUp\Controllers\User::apiProblemsCreated(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $this->assertEmpty($response['problems']);

        // Now, as another user, request the problems created by initial user
        ['user' => $otherUser, 'identity' => $otherIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($otherIdentity);

        $response = \OmegaUp\Controllers\User::apiProblemsCreated(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]));
        $this->assertEmpty($response['problems']);
    }
}
