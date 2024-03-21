<?php
/**
 * Test getting general user Info methods
 */
class UserProfileTest extends \OmegaUp\Test\ControllerTestCase {
    /*
     * Test for the function which returns the general user info
     */
    public function testUserData() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
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
        $this->assertSame(
            $identity->username,
            $response['username']
        );
    }

    /*
     * Test for the function which returns the general user info
     */
    public function testUserDataAnotherUser() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'testuser2']
            )
        );
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser(
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
        $this->assertArrayNotHasKey('has_competitive_objective', $response);
        $this->assertArrayNotHasKey('has_learning_objective', $response);
        $this->assertArrayNotHasKey('has_scholar_objective', $response);
        $this->assertArrayNotHasKey('has_teaching_objective', $response);
        $this->assertSame(
            $identity2->username,
            $response['username']
        );
    }

    /*
     * Test apiProfile with is_private enabled
     */
    public function testUserPrivateDataAnotherUser() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        // Mark user2's profile as private (5th argument)
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser(
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
            'country_id',
            'gravatar_92',
            'classname',
            'hide_problem_tags',
            'verified',
            'programming_languages',
            'is_own_profile',
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
        $this->assertSame(
            $identity2->username,
            $response['username']
        );
    }

    /*
     * Test admin can see emails for all non-private profiles
     */
    public function testAdminCanSeeEmails() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();

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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['isPrivate' => true])
        );
        ['identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();

        $login = self::login($identityAdmin);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayHasKey('email', $response);
        $this->assertArrayHasKey('has_competitive_objective', $response);
        $this->assertArrayHasKey('has_learning_objective', $response);
        $this->assertArrayHasKey('has_scholar_objective', $response);
        $this->assertArrayHasKey('has_teaching_objective', $response);
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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayHasKey('email', $response);
    }

    /*
     * User can see his own objectives
     */
    public function testUserCanSeeSelfObjectives() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r);

        $this->assertArrayHasKey('has_competitive_objective', $response);
        $this->assertArrayHasKey('has_learning_objective', $response);
        $this->assertArrayHasKey('has_scholar_objective', $response);
        $this->assertArrayHasKey('has_teaching_objective', $response);
    }

    /*
     * Test the contest which a certain user has participated
     */
    public function testUserContests() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $contests = [];
        $contests[0] = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $contests[1] = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

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
        $response = \OmegaUp\Controllers\User::apiContestStats(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        // Result should be 1 since user has only actually participated in 1 contest (submitted run)
        $this->assertSame(1, count($response['contests']));
        $alias = $contests[0]['contest']->alias;
        $this->assertSame(
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

        $login = self::login($contests[0]['director']);

        // When user is removed from the contest, is no longer able to see their
        // contest stats, but no exception is thrown.
        \OmegaUp\Controllers\Contest::apiRemoveUser(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $alias,
                'usernameOrEmail' => $identity->username,
            ])
        );

        // Get ContestStats
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiContestStats(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        // Result should be 0 since user was removed from the only contest who
        // participated (submitted run)
        $this->assertSame(0, count($response['contests']));
    }

    /*
     * Test the contest which a certain user has participated.
     * API can be accessed by a user who cannot see the contest (contest is private)
     */
    public function testUserContestsPrivateContestOutsider() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

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

        ['identity' => $externalIdentity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($externalIdentity);
        // Get ContestStats
        $response = \OmegaUp\Controllers\User::apiContestStats(new \OmegaUp\Request(
            [
                    'auth_token' => $login->auth_token,
                    'username' => $identity->username
                ]
        ));

        // Result should be 1 since user has only actually participated in 1 contest (submitted run)
        $this->assertSame(1, count($response['contests']));
    }

    /*
     * Test the problems solved by user
     */
    public function testProblemsSolved() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

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

        $this->assertSame(2, count($response['problems']));
    }

    /*
     * Test the problems solved by user
     */
    public function testProblemsCreated() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

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
        $this->assertSame(count($problems), count($response['problems']));

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
        $this->assertSame(
            $expectedProblemCount,
            count(
                $response['problems']
            )
        );

        // Now, as another user, request the problems created by initial user
        ['identity' => $otherIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($otherIdentity);

        $response = \OmegaUp\Controllers\User::apiProblemsCreated(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]));
        $this->assertSame(
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
        \OmegaUp\Controllers\User::apiUpdateMainEmail(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'email' => 'new@email.com'
            ])
        );

        // Check email in db
        $user_in_db = \OmegaUp\DAO\Users::findByEmail('new@email.com');
        $this->assertSame($user->user_id, $user_in_db->user_id);
    }

    /**
     * Test update main email api
     */
    public function testStats() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
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
            $this->assertSame(
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
        $this->assertSame('problem_1', $response['problems'][0]['alias']);

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
        ['identity' => $otherIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($otherIdentity);

        $response = \OmegaUp\Controllers\User::apiProblemsCreated(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]));
        $this->assertEmpty($response['problems']);
    }

    /**
     * A PHPUnit data provider for all the tests to get profile details.
     *
     * @return list<array{0: bool, 1: bool, 2: bool}>
     */
    public function profileDetailsProvider(): array {
        return [
            [true, true, false],
            [true, false, false],
            [false, false, false],
            [true, false, true],
        ];
    }

    /**
     * Test different cases for getting profile details
     * @dataProvider profileDetailsProvider
     */
    public function testGetProfileDetails(
        bool $isLoggedIn,
        bool $isOwnProfile,
        bool $isPrivate
    ) {
        $userParams = [ 'username' => 'testusername1', 'name' => 'testuser1' ];
        if ($isPrivate) {
            $userParams = array_merge($userParams, ['isPrivate' => true]);
        }
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams($userParams)
        );
        [
            'identity' => $otherIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['isPrivate' => true])
        );

        $login = null;
        if ($isLoggedIn) {
            if ($isOwnProfile) {
                $login = self::login($identity);
            } else {
                $login = self::login($otherIdentity);
            }
        }

        $requestParams = [];
        if ($isLoggedIn) {
            $requestParams = array_merge(
                $requestParams,
                ['auth_token' => $login->auth_token]
            );
        }

        if (!$isOwnProfile) {
            $requestParams = array_merge(
                $requestParams,
                ['username' => 'testusername1']
            );
        }

        $response = \OmegaUp\Controllers\User::getProfileDetailsForTypeScript(
            new \OmegaUp\Request($requestParams)
        )['templateProperties']['payload'];
        $profile = $response['profile'];

        // User's name is hidden when private profile is set
        $identityName = $isPrivate ? null : $identity->name;
        $this->assertSame($identity->username, $profile['username']);
        $this->assertSame($identityName, $profile['name']);
        $this->assertSame($profile['is_own_profile'], $isOwnProfile);
        $this->assertSame($profile['is_private'], $isPrivate);
    }
    public const SUPPORTED_LANGUAGES = [
        'kp' => 'Karel (Pascal)',
        'kj' => 'Karel (Java)',
        'c11-gcc' => 'C11 (gcc 10.3)',
        'c11-clang' => 'C11 (clang 10.0)',
        'cpp11-gcc' => 'C++11 (g++ 10.3)',
        'cpp11-clang' => 'C++11 (clang++ 10.0)',
        'cpp17-gcc' => 'C++17 (g++ 10.3)',
        'cpp17-clang' => 'C++17 (clang++ 10.0)',
        'cpp20-gcc' => 'C++20 (g++ 10.3)',
        'cpp20-clang' => 'C++20 (clang++ 10.0)',
        'java' => 'Java (openjdk 16.0)',
        'kt' => 'Kotlin (1.6.10)',
        'py2' => 'Python (2.7)',
        'py3' => 'Python (3.9)',
        'rb' => 'Ruby (2.7)',
        'cs' => 'C# (10, dotnet 6.0)',
        'pas' => 'Pascal (fpc 3.0)',
        'cat' => 'Output Only',
        'hs' => 'Haskell (ghc 8.8)',
        'lua' => 'Lua (5.3)',
        'go' => 'Go (1.18.beta2)',
        'rs' => 'Rust (1.56.1)',
        'js' => 'JavaScript (Node.js 16)',
    ];
    public function testGetSupportedProgrammingLanguages($excludedLanguage, $expectedLanguages)
    {
        $result = \OmegaUp\Controllers\User::getSupportedProgrammingLanguages($excludedLanguage);
        $this->assertSame($self::SUPPORTED_LANGUAGES, $result);
    }

    public function testGetUserDependents() {
        ['identity' => $mainIdentity, 'user' => $mainUser ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams()
        );
        $mainEmail = \OmegaUp\DAO\Emails::getByUserId($mainUser->user_id)[0];

        $dependents = [];
        $users = [];
        for ($i = 0; $i < 3; $i++) {
            [
                'identity' => $dependents[],
                'user' => $users[],
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams([
                    'birthDate' => 1406684800,
                ])
            );
            $users[$i]->parent_email_id = $mainEmail->email_id;
            \OmegaUp\DAO\Users::update($users[$i]);
        }

        // This user is not a dependent
        \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams()
        );

        $login = self::login($mainIdentity);
        $response = \OmegaUp\Controllers\User::getUserDependentsForTypeScript(
            new \OmegaUp\Request([ 'auth_token' => $login->auth_token ])
        )['templateProperties']['payload'];
        $this->assertCount(3, $response['dependents']);
        $this->assertEquals(
            $dependents[0]->name,
            $response['dependents'][0]['name']
        );
        $this->assertEquals(
            $dependents[1]->name,
            $response['dependents'][1]['name']
        );
        $this->assertEquals(
            $dependents[2]->name,
            $response['dependents'][2]['name']
        );
    }
}
