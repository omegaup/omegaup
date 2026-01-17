<?php
/**
 * Test getting general user Info methods
 */
class CoderOfTheMonthTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * A PHPUnit data provider for all the tests that can accept a category.
     *
     * @return list<list<string>>
     */
    public function coderOfTheMonthCategoryProvider(): array {
        return [
            ['all'],
            ['female'],
        ];
    }

    private function createRuns(
        \OmegaUp\DAO\VO\Identities $identity = null,
        string $runCreationDate = null,
        int $numRuns = 5,
        bool $quality = true
    ): void {
        if ($numRuns < 1) {
            return;
        }
        if (!$identity) {
            ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        }
        if (!$runCreationDate) {
            $runCreationDate = date('Y-m-d', \OmegaUp\Time::get());
        }
        $contest = \OmegaUp\Test\Factories\Contest::createContest();
        $problems = [];
        foreach (range(0, $numRuns - 1) as $index) {
            $problems[$index] = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'quality_seal' => $quality,
                ])
            );
            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problems[$index],
                $contest
            );
        }
        \OmegaUp\Test\Factories\Contest::addUser($contest, $identity);

        foreach (range(0, $numRuns - 1) as $index) {
            $runData = \OmegaUp\Test\Factories\Run::createRun(
                $problems[$index],
                $contest,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData);
            //sumbmission gap between runs must be 60 seconds
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);

            // Force the submission to be in any date
            $submission = \OmegaUp\DAO\Submissions::getByGuid(
                $runData['response']['guid']
            );
            $submission->time = $runCreationDate;
            \OmegaUp\DAO\Submissions::update($submission);
            $run = \OmegaUp\DAO\Runs::getByPK($submission->current_run_id);
            $run->time = $runCreationDate;
            \OmegaUp\DAO\Runs::update($run);
        }
    }

    private function getCoderOfTheMonth(
        string $revDate,
        string $interval,
        string $category
    ) {
        $reviewDate = date_create($revDate);
        date_add($reviewDate, date_interval_create_from_date_string($interval));
        $reviewDate = date_format($reviewDate, 'Y-m-01');

        \OmegaUp\Time::setTimeForTesting(
            strtotime($reviewDate) + (60 * 60 * 24)
        );
        return \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request([
                'category' => $category,
            ])
        );
    }

    private function updateIdentity(
        \OmegaUp\DAO\VO\Identities $identity,
        string $gender
    ): void {
        $login = self::login($identity);
        $locale = \OmegaUp\DAO\Languages::getByName('pt');
        $states = \OmegaUp\DAO\States::getByCountry('MX');
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'gender' => $gender,
            'scholar_degree' => 'master',
            'birth_date' => strtotime('1988-01-01'),
            'graduation_date' => strtotime('2016-02-02'),
            'locale' => $locale->name,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);
         // Check user from db
        $userDb = \OmegaUp\DAO\AuthTokens::getUserByToken($r['auth_token']);
        $identityDb = \OmegaUp\DAO\AuthTokens::getIdentityByToken(
            $r['auth_token']
        )['loginIdentity'];
        $graduationDate = null;
        if (!is_null($identityDb['current_identity_school_id'])) {
            $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $identityDb['current_identity_school_id']
            );
            if (!is_null($identitySchool)) {
                $graduationDate = $identitySchool->graduation_date;
            }
        }

        $this->assertSame($r['name'], $identityDb['name']);
        $this->assertSame($r['country_id'], $identityDb['country_id']);
        $this->assertSame($r['state_id'], $identityDb['state_id']);
        $this->assertSame($r['scholar_degree'], $userDb->scholar_degree);
        $this->assertSame(
            gmdate(
                'Y-m-d',
                $r['birth_date']
            ),
            $userDb->birth_date
        );
        // Graduation date without school is not saved on database.
        $this->assertNull($graduationDate);
        $this->assertSame($locale->language_id, $identityDb['language_id']);
    }

    /**
     * Test the API behavior when user has solved problems in the last month
     * that has been seen the solutions
     *
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCoderOfTheMonthWithSolutionsSeenOnProblems(
        string $category
    ) {
        $gender = $category == 'all' ? 'male' : 'female';

        // Create a submissions mapping for 3 different users solving 5 problems
        $submissionsMapping = [
            [
                ['username' => 'user_01', 'run' => 1, 'seenSolution' => true],
                ['username' => 'user_02', 'run' => 0, 'seenSolution' => false],
                ['username' => 'user_03', 'run' => 1, 'seenSolution' => true],
            ],
            [
                ['username' => 'user_01', 'run' => 1, 'seenSolution' => true],
                ['username' => 'user_02', 'run' => 1, 'seenSolution' => false],
                ['username' => 'user_03', 'run' => 1, 'seenSolution' => false],
            ],
            [
                ['username' => 'user_01', 'run' => 1, 'seenSolution' => true],
                ['username' => 'user_02', 'run' => 0, 'seenSolution' => false],
                ['username' => 'user_03', 'run' => 0, 'seenSolution' => false],
            ],
            [
                ['username' => 'user_01', 'run' => 1, 'seenSolution' => false],
                ['username' => 'user_02', 'run' => 0, 'seenSolution' => false],
                ['username' => 'user_03', 'run' => 1, 'seenSolution' => true],
            ],
            [
                ['username' => 'user_01', 'run' => 1, 'seenSolution' => true],
                ['username' => 'user_02', 'run' => 1, 'seenSolution' => false],
                ['username' => 'user_03', 'run' => 1, 'seenSolution' => true],
            ],
        ];

        // Create 3 users
        $identities = [];
        foreach ($submissionsMapping[0] as $index => $user) {
            [
                'identity' => $identities[$index],
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams(
                    ['username' => $user['username']]
                )
            );
            self::updateIdentity($identities[$index], $gender);
        }

        $runCreationDate = self::setFirstDayOfTheLastMonth();
        // Create 5 problems and submissions for some users depending on the
        // submissions mapping
        $problemData = [];
        foreach ($submissionsMapping as $indexProblem => $problemSubmissions) {
            $problemData[$indexProblem] = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'quality_seal' => true,
                    'alias' => 'problem_' . ($indexProblem + 1),
                ])
            );

            foreach ($problemSubmissions as $indexUser => $submissionsUser) {
                if ($submissionsUser['seenSolution']) {
                    continue;
                }
                $login = self::login($identities[$indexUser]);
                \OmegaUp\Controllers\Problem::apiSolution(
                    new \OmegaUp\Request([
                        'auth_token' => $login->auth_token,
                        'problem_alias' => $problemData[$indexProblem]['problem']->alias,
                        'forfeit_problem' => true,
                    ])
                );
                if (!$submissionsUser['run']) {
                    continue;
                }
                \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
                    $identities[$indexUser],
                    $problemData[$indexProblem],
                    $runCreationDate
                );
            }
        }

        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);

        $today = date('Y-m-01', \OmegaUp\Time::get());

        $response = $this->getCoderOfTheMonth(
            $today,
            'this month',
            $category
        );

        // user_02 should be the coder of the month because they have solved
        // all the problems without seeing the solutions
        $this->assertSame(
            $identities[1]->username,
            $response['coderinfo']['username']
        );
    }

    /**
     * Test the API behavior when there is more than one candidate for Coder of
     * the Month during the first days of the current month
     *
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCodersOfTheMonthWithSubmissionsInDifferentMonths(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';
        // Create a submissions mapping for different users, months, and problems.
        // The winner for the current month should be user_01 because all their
        // submissions are in the current month. Submissions from past months
        // should not be considered for the current month.
        $submissionsMapping = [
            0 => [
                ['username' => 'user_01', 'numRuns' => [0, 0, 0]], // 0
                ['username' => 'user_02', 'numRuns' => [1, 1, 1]], // 0
                ['username' => 'user_03', 'numRuns' => [1, 1, 0]], // 0
            ],
            1 => [
                ['username' => 'user_01', 'numRuns' => [0, 1, 1]], // 1
                ['username' => 'user_02', 'numRuns' => [0, 1, 1]], // 1
                ['username' => 'user_03', 'numRuns' => [1, 1, 0]], // 0
            ],
            2 => [
                ['username' => 'user_01', 'numRuns' => [0, 1, 1]], // 2
                ['username' => 'user_02', 'numRuns' => [1, 1, 1]], // 1
                ['username' => 'user_03', 'numRuns' => [1, 1, 0]], // 0
            ],
            3 => [
                ['username' => 'user_01', 'numRuns' => [0, 1, 1]], // 3
                ['username' => 'user_02', 'numRuns' => [1, 1, 1]], // 1
                ['username' => 'user_03', 'numRuns' => [0, 1, 1]], // 1
            ],
            4 => [
                ['username' => 'user_01', 'numRuns' => [0, 1, 1]], // 4
                ['username' => 'user_02', 'numRuns' => [0, 1, 1]], // 2
                ['username' => 'user_03', 'numRuns' => [1, 1, 0]], // 1
            ],
        ];

        // Create 3 users and their identities
        $identities = [];
        foreach ($submissionsMapping[0] as $index => $user) {
            [
                'identity' => $identities[$index],
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams(
                    ['username' => $user['username']]
                )
            );
            self::updateIdentity($identities[$index], $gender);
        }

        $runCreationDate = self::setFirstDayOfTheCurrentMonth();

        $problemData = [];
        foreach ($submissionsMapping as $indexProblem => $problemSubmissions) {
            $problemData[$indexProblem] = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'quality_seal' => true,
                    'alias' => 'problem_' . $indexProblem,
                ])
            );
            foreach ($problemSubmissions as $indexUser => $submissionsUser) {
                foreach ($submissionsUser['numRuns'] as $month => $shouldCreateRun) {
                    if ($shouldCreateRun == 0) {
                        continue;
                    }
                    switch ($month) {
                        case 0:
                            $runCreationDate = self::setFirstDayOfCustomMonths(
                                monthsLeft: 2
                            );
                            break;
                        case 1:
                            $runCreationDate = self::setFirstDayOfTheLastMonth();
                            break;
                        case 2:
                            $runCreationDate = self::setFirstDayOfTheCurrentMonth();
                            break;
                    }
                    \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
                        $identities[$indexUser],
                        $problemData[$indexProblem],
                        $runCreationDate
                    );
                }
            }
        }
        $runCreationDate = self::setFirstDayOfTheLastMonth();
        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);

        $today = date('Y-m-01', \OmegaUp\Time::get());

        $response = $this->getCoderOfTheMonth(
            $today,
            'this month',
            $category
        );

        $this->assertSame(
            $identities[0]->username,
            $response['coderinfo']['username']
        );
    }

    /**
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCoderOfTheMonthCalc(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity, $gender);
        [
            'identity' => $extraIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($extraIdentity, $gender);

        // Add a custom school
        $login = self::login($identity);
        $school = \OmegaUp\Test\Factories\Schools::createSchool()['school'];
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school->school_id,
        ]));

        // First user solves two problems, second user solves just one, third
        // solves same problems than first.
        $runCreationDate = self::setFirstDayOfTheLastMonth();

        $this->createRuns($identity, $runCreationDate, numRuns: 1);
        $this->createRuns($identity, $runCreationDate, numRuns: 1);
        $this->createRuns($extraIdentity, $runCreationDate, numRuns: 1);

        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );

        $this->assertSame(
            $identity->username,
            $response['coderinfo']['username']
        );
        $this->assertArrayNotHasKey('email', $response['coderinfo']);

        // Calling API again to verify response is the same that in first time
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );

        $this->assertSame(
            $identity->username,
            $response['coderinfo']['username']
        );

        // CoderOfTheMonth school_id should match with identity school_id
        $this->assertSame(
            $school->school_id,
            $response['coderinfo']['school_id']
        );

        // Now check if the other user has been saved on database too
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'date' => date('Y-m-d', \OmegaUp\Time::get()),
            'category' => $category,
        ]));
        // Now check if the third user has not participated in the coder of the
        // month in that category.
        $this->assertSame(
            $identity->username,
            $response['coders'][0]['username']
        );
        $this->assertSame(
            $extraIdentity->username,
            $response['coders'][1]['username']
        );
    }

    /**
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCoderOfMonthWithQualityProblems(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity, $gender);
        [
            'identity' => $extraIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($extraIdentity, $gender);

        // Add a custom school
        $login = self::login($identity);
        $school = \OmegaUp\Test\Factories\Schools::createSchool()['school'];
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school->school_id,
        ]));

        // First user solves two problems, second user solves just one, third
        // solves same problems than first.
        $runCreationDate = self::setFirstDayOfTheLastMonth();

        $this->createRuns($identity, $runCreationDate, numRuns: 1);
        $this->createRuns($identity, $runCreationDate, numRuns: 1);
        $this->createRuns($identity, $runCreationDate, numRuns: 1);
        $this->createRuns(
            $extraIdentity,
            $runCreationDate,
            numRuns: 1,
            quality: false
        );
        $this->createRuns(
            $extraIdentity,
            $runCreationDate,
            numRuns: 1,
            quality: false
        );
        $this->createRuns($extraIdentity, $runCreationDate, numRuns: 1);
        $this->createRuns($extraIdentity, $runCreationDate, numRuns: 1);

        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );

        $this->assertSame(
            $identity->username,
            $response['coderinfo']['username']
        );
        $this->assertArrayNotHasKey('email', $response['coderinfo']);

        // Calling API again to verify response is the same that in first time
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );

        $this->assertSame(
            $identity->username,
            $response['coderinfo']['username']
        );

        // CoderOfTheMonth school_id should match with identity school_id
        $this->assertSame(
            $school->school_id,
            $response['coderinfo']['school_id']
        );

        // Now check if the other user has been saved on database too
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'date' => date('Y-m-d', \OmegaUp\Time::get()),
            'category' => $category,
        ]));

        $this->assertSame(
            $identity->username,
            $response['coders'][0]['username']
        );
        $this->assertSame(
            $extraIdentity->username,
            $response['coders'][1]['username']
        );
    }

    /**
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCoderOfTheMonthList(string $category) {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'category' => $category,
        ]));

        // There are no previous Coders of The Month.
        $this->assertEmpty($response['coders']);

        // Adding parameter date should return the same value, it helps
        // to test getMonthlyList function.
        // It should return two users (the ones that got stored on the previous test)
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'date' => date('Y-m-d', \OmegaUp\Time::get()),
            'category' => $category,
        ]));
        $this->assertEmpty($response['coders']);
    }

    /**
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCodersOfTheMonthBySchool(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity1, $gender);

        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity2, $gender);

        // Identity 3 won't have school_id
        ['identity' => $identity3] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity3, $gender);

        // Add a custom school for identities 1 and 2
        $school = \OmegaUp\Test\Factories\Schools::createSchool()['school'];

        $login = self::login($identity1);
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school->school_id,
        ]));

        $login = self::login($identity2);
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school->school_id,
        ]));

        $today = date('Y-m-01', \OmegaUp\Time::get());

        // Identity 1 will be the coder of the month of four months ago
        $runCreationDate = date_create($today);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '-4 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($identity1, $runCreationDate, numRuns: 1);
        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $this->getCoderOfTheMonth($today, '-3 month', $category);

        // Identity 2 will be the coder of the month of three months ago
        $runCreationDate = date_create($runCreationDate);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '1 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($identity2, $runCreationDate, numRuns: 1);
        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $this->getCoderOfTheMonth($today, '-2 month', $category);

        // Identity 3 will be the coder of the month of two months ago
        $runCreationDate = date_create($runCreationDate);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '1 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($identity3, $runCreationDate, numRuns: 1);
        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $this->getCoderOfTheMonth($today, '-1 month', $category);

        // First run function with invalid school_id
        try {
            \OmegaUp\Controllers\School::getSchoolCodersOfTheMonth(
                1231
            );
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame($e->getMessage(), 'schoolNotFound');
        }

        // Now run function with valid school_id
        $results = \OmegaUp\Controllers\School::getSchoolCodersOfTheMonth(
            $school->school_id
        );
        // Get all usernames and verify that only identity1 username
        // and identity2 username are part of results
        $resultCoders = [];
        foreach ($results as $res) {
            $resultCoders[] = $res['username'];
        }

        $this->assertContains($identity1->username, $resultCoders);
        $this->assertContains($identity2->username, $resultCoders);
        $this->assertNotContains($identity3->username, $resultCoders);
    }

    /**
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCoderOfTheMonthDetailsForTypeScript(string $category) {
        // Test coder of the month details when user is not logged
        $response = \OmegaUp\Controllers\User::getCoderOfTheMonthDetailsForTypeScript(
            new \OmegaUp\Request(['category' => $category])
        )['templateProperties'];
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('codersOfCurrentMonth', $response['payload']);
        $this->assertArrayHasKey('codersOfPreviousMonth', $response['payload']);
        $this->assertFalse($response['payload']['isMentor']);
        $this->assertArrayNotHasKey('options', $response['payload']);

        // Test coder of the month details when common user is logged, it's the
        // same that not logged user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::getCoderOfTheMonthDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'category' => $category,
            ])
        )['templateProperties'];
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('codersOfCurrentMonth', $response['payload']);
        $this->assertArrayHasKey('codersOfPreviousMonth', $response['payload']);
        $this->assertFalse($response['payload']['isMentor']);
        $this->assertArrayNotHasKey('options', $response['payload']);

        // Test coder of the month details when mentor user is logged
        [
            'identity' => $mentorIdentity,
        ] = \OmegaUp\Test\Factories\User::createMentorIdentity();
        $login = self::login($mentorIdentity);
        $response = \OmegaUp\Controllers\User::getCoderOfTheMonthDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'category' => $category,
            ])
        )['templateProperties'];
        $this->assertTrue($response['payload']['isMentor']);
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('options', $response['payload']);
    }

    /**
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCoderOfTheMonthAfterYear(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'identityA']
            )
        );
        self::updateIdentity($identity, $gender);

        // User "B" is always the second one in the ranking based on score
        ['identity' => $identityB] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'identityB']
            )
        );
        self::updateIdentity($identityB, $gender);

        // Using the first day of the month as "today" to avoid failures near
        // certain dates.
        $today = date('Y-m-01', \OmegaUp\Time::get());

        $runCreationDate = date_create($today);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '-13 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($identity, $runCreationDate, numRuns: 10);
        $this->createRuns($identityB, $runCreationDate, numRuns: 5);
        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);

        $runCreationDate = date_create($runCreationDate);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '1 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($identity, $runCreationDate, numRuns: 10);
        $this->createRuns($identityB, $runCreationDate, numRuns: 5);
        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);

        $this->createRuns($identity, $today, numRuns: 10);
        $this->createRuns($identityB, $runCreationDate, numRuns: 5);
        \OmegaUp\Test\Utils::runUpdateRanks($today);

        // Getting Coder Of The Month
        $responseCoder = $this->getCoderOfTheMonth(
            $today,
            '-12 month',
            $category
        );
        $this->assertSame(
            $identity->username,
            $responseCoder['coderinfo']['username']
        );

        $responseCoder = $this->getCoderOfTheMonth(
            $today,
            '-11 month',
            $category
        );
        // IdentityB is the CotM as Identity has already been selected.
        $this->assertSame(
            $identityB->username,
            $responseCoder['coderinfo']['username']
        );

        $responseCoder = $this->getCoderOfTheMonth(
            $today,
            '1 month',
            $category
        );
        $this->assertSame(
            $identity->username,
            $responseCoder['coderinfo']['username']
        );
    }

    /**
     * Mentor can see the last coder of the month email
     *
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testMentorCanSeeLastCoderOfTheMonthEmail(string $category) {
        [
            'identity' => $mentorIdentity,
        ] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        $login = self::login($mentorIdentity);
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'category' => $category,
        ]));
        $coders = [];
        foreach ($response['coders'] as $index => $coder) {
            $coders[$index] = $coder['username'];
        }
        $coders = array_unique($coders);

        foreach ($coders as $index => $coder) {
            $profile = \OmegaUp\Controllers\User::apiProfile(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'username' => $coder,
                    'category' => $category,
                ])
            );
            if ($index == 0) {
                // Mentor can see the current coder of the month email
                $this->assertArrayHasKey('email', $profile);
            } else {
                $this->assertArrayNotHasKey('email', $profile);
            }
        }

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($identity);

        foreach ($coders as $index => $coder) {
            $profile = \OmegaUp\Controllers\User::apiProfile(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'username' => $coder,
                ])
            );

            $this->assertArrayNotHasKey('email', $profile);
        }
    }

    /**
     * Sending several submissions at next month to verify
     * apiSelectCoderOfTheMonth is working correctly, current month
     * already has a coder of the month selected
     *
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testMentorSelectsUserAsCoderOfTheMonth(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';
        [
            'identity' => $mentorIdentity,
        ] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        // Setting time to the 15th of next month.
        $runCreationDate = new DateTimeImmutable(
            (new DateTimeImmutable(
                date(
                    'Y-m-d',
                    \OmegaUp\Time::get()
                )
            ))
                ->format('Y-m-15')
        );
        \OmegaUp\Time::setTimeForTesting(
            strtotime(
                $runCreationDate->format(
                    'Y-m-d'
                )
            )
        );

        // Submitting some runs with new users
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity1, $gender);
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity2, $gender);
        ['identity' => $identity3] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity3, $gender);
        $this->createRuns($identity1, $runCreationDate->format('Y-m-d'), 2);
        $this->createRuns($identity1, $runCreationDate->format('Y-m-d'), 4);
        $this->createRuns($identity2, $runCreationDate->format('Y-m-d'), 4);
        $this->createRuns($identity2, $runCreationDate->format('Y-m-d'), 1);
        $this->createRuns($identity3, $runCreationDate->format('Y-m-d'), 2);

        // Setting new date to the first of the month following the run
        // creation.
        $firstDayOfNextMonth = $runCreationDate->modify(
            'first day of next month'
        );
        \OmegaUp\Time::setTimeForTesting(
            strtotime(
                $firstDayOfNextMonth->format(
                    'Y-m-d'
                )
            )
        );

        // Selecting one user as coder of the month
        $login = self::login($mentorIdentity);

        // Call api. This should fail.
        try {
            \OmegaUp\Test\Utils::runUpdateRanks();
            \OmegaUp\Controllers\User::apiSelectCoderOfTheMonth(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $identity3->username,
                'category' => $category,
            ]));
            $this->fail(
                'Exception was expected, because date is not in the range to select coder'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame(
                $e->getMessage(),
                'coderOfTheMonthIsNotInPeriodToBeChosen'
            );
            // Pass
        }

        // Changing date to the last day of the month in which the run was created.
        \OmegaUp\Time::setTimeForTesting(
            strtotime(
                $runCreationDate->format(
                    'Y-m-t'
                )
            )
        );

        // Call api again.
        \OmegaUp\Test\Utils::runUpdateRanks(
            date(
                'Y-m-d',
                \OmegaUp\Time::get()
            )
        );

        \OmegaUp\Controllers\User::apiSelectCoderOfTheMonth(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity3->username,
            'category' => $category,
        ]));

        // Set date to first day of next month
        \OmegaUp\Time::setTimeForTesting(
            strtotime(
                $firstDayOfNextMonth->format(
                    'Y-m-d'
                )
            )
        );

        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );
        $this->assertNotNull(
            $response['coderinfo'],
            'A user has been selected by a mentor'
        );
        $this->assertSame(
            $response['coderinfo']['username'],
            $identity3->username
        );
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(
            new \OmegaUp\Request(['category' => $category])
        );

        $this->assertSame(
            $firstDayOfNextMonth->format(
                'Y-m-d'
            ),
            $response['coders'][0]['date']
        );

        // Should get all other candidates for coder of the month that had not been
        // selected, and also the coder of the month previously selected.
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(
            new \OmegaUp\Request([
                'date' => date('Y-m-d', \OmegaUp\Time::get()),
                'category' => $category,
            ])
        );
        $coders = [];
        foreach ($response['coders'] as $coder) {
            $coders[] = $coder['username'];
        }

        $this->assertContains($identity1->username, $coders);
        $this->assertContains($identity2->username, $coders);
        $this->assertContains($identity3->username, $coders);
    }

    /**
     * Mentor can choose the coder of the month only the last day
     * of the current month or the first day of the next month
     */
    public function testMentorCanChooseCoderOfTheMonth() {
        // Creating runs for 3 users
        $this->createRuns();
        $this->createRuns(null, null, 3);
        $this->createRuns(null, null, 2);

        [
            'identity' => $mentorIdentity,
        ] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        $this->assertTrue(\OmegaUp\Authorization::isMentor($mentorIdentity));

        // Testing with an intermediate day of the month
        $timestampTest = \OmegaUp\Time::get();
        $dateTest = date('Y-m-15', $timestampTest);
        $timestampTest = strtotime($dateTest);
        $canChooseCoder = \OmegaUp\Authorization::canChooseCoderOrSchool(
            $timestampTest
        );
        $this->assertFalse($canChooseCoder);

        // Setting the date to the last day of the current month and testing
        // mentor can choose the coder.
        $date = new DateTime('now');
        $date->modify('last day of this month');
        $date->format('Y-m-d');
        \OmegaUp\Time::setTimeForTesting($date->getTimestamp());
        $timestampTest = \OmegaUp\Time::get();
        $dateTest = date('Y-m-d', $timestampTest);
        $canChooseCoder = \OmegaUp\Authorization::canChooseCoderOrSchool(
            $timestampTest
        );
        $this->assertTrue($canChooseCoder);

        // Setting the date to the first day of the next month and testing
        // mentor can not choose the coder.
        \OmegaUp\Time::setTimeForTesting(
            $date->getTimestamp() + (60 * 60 * 24)
        );
        $timestampTest = \OmegaUp\Time::get();
        $dateTest = date('Y-m-d', $timestampTest);
        $canChooseCoder = \OmegaUp\Authorization::canChooseCoderOrSchool(
            $timestampTest
        );
        $this->assertFalse($canChooseCoder);

        // Setting the date to the second day of the next month and testing
        // mentor can not choose the coder.
        \OmegaUp\Time::setTimeForTesting(
            $date->getTimestamp() + (60 * 60 * 48)
        );
        $timestampTest = \OmegaUp\Time::get();
        $dateTest = date('Y-m-d', $timestampTest);
        $canChooseCoder = \OmegaUp\Authorization::canChooseCoderOrSchool(
            $timestampTest
        );
        $this->assertFalse($canChooseCoder);
    }

    public function testCodersOfTheMonthIsTheSame() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity, 'female');
        [
            'identity' => $extraIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($extraIdentity, 'female');

        // Add a custom school
        $login = self::login($identity);
        $school = \OmegaUp\Test\Factories\Schools::createSchool()['school'];
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school->school_id,
        ]));

        // First user solves two problems, second user solves just one, third
        // solves same problems than first.
        $today = date('Y-m-01');
        $runCreationDate = date_create($today);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '-5 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($identity, $runCreationDate, numRuns: 1);
        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $coderFemale = $this->getCoderOfTheMonth($today, '-5 month', 'female');
        $coderAll = $this->getCoderOfTheMonth($today, '-5 month', 'all');

        // Now check if the third user has not participated in the coder of the
        // month female.
        if (isset($coderAll['coderinfo']['username'])) {
            $this->assertSame(
                $coderAll['coderinfo']['username'],
                $coderFemale['coderinfo']['username']
            );
        }
    }

    /**
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCoderOfTheMonthCalcWithIdentities(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';
        ['identity' => $user1] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($user1, $gender);
        ['identity' => $user2] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($user2, $gender);
        ['identity' => $user3] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($user3, $gender);

        // Add a custom school
        $login = self::login($user1);
        $school = \OmegaUp\Test\Factories\Schools::createSchool()['school'];
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school->school_id,
        ]));

        // First user solves two problems, second user solves just one, third
        // solves same problems than first.
        $runCreationDate = self::setFirstDayOfTheLastMonth();

        $this->createRuns($user1, $runCreationDate, numRuns: 1);
        $this->createRuns($user1, $runCreationDate, numRuns: 1);
        $this->createRuns($user2, $runCreationDate, numRuns: 1);
        $this->createRuns($user3, $runCreationDate, numRuns: 1);
        $this->createRuns($user3, $runCreationDate, numRuns: 1);
        $this->createRuns($user3, $runCreationDate, numRuns: 1);

        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );

        $this->assertSame(
            $user3->username,
            $response['coderinfo']['username']
        );

        // Now check if the other users have been saved on database too
        ['coders' => $coders] = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'date' => date('Y-m-d', \OmegaUp\Time::get()),
                'category' => $category,
            ])
        );
        // Now check if the third user has not participated in the coder of the
        // month in that category.
        $this->assertSame($user3->username, $coders[0]['username']);
        $this->assertSame($user1->username, $coders[1]['username']);
        $this->assertSame($user2->username, $coders[2]['username']);

        // Identity creator group member will upload csv file
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            name: null,
            description: null,
            alias: null,
            login: $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $identityPassword = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => "{$group['group']->alias}:{$identityName}",
            'name' => $identityName,
            'password' => $identityPassword,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => $gender,
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));
        $identity = \OmegaUp\DAO\Identities::findByUsername(
            "{$group['group']->alias}:{$identityName}"
        );
        $identity->password = $identityPassword;

        $this->createRuns($identity, $runCreationDate, numRuns: 1);
        $this->createRuns($identity, $runCreationDate, numRuns: 1);
        $this->createRuns($identity, $runCreationDate, numRuns: 1);
        $this->createRuns($identity, $runCreationDate, numRuns: 1);

        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );

        $this->assertSame(
            $user3->username,
            $response['coderinfo']['username']
        );

        $login = self::login($user2);
        \OmegaUp\Controllers\User::apiAssociateIdentity(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $identity->username,
                'password' => $identityPassword,
            ])
        );

        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );

        // Now user2 is the coder of the month
        $this->assertSame(
            $user2->username,
            $response['coderinfo']['username']
        );
    }

    /**
     * Test the API behavior when the submissions are made by unassociated
     * identities. Only one identity belongs to a user. The user should be the
     * coder of the month even if they have fewer accepted submissions.
     *
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testUnassociatedIdentities(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';

        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $groupAlias = 'submissions-group';
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            name: 'Group for submissions',
            description: 'This group will be used to create identities',
            alias: $groupAlias,
            login: $creatorLogin
        );

        $submissionsMapping = [
            ['username' => 'id_1', 'numRuns' => 3, 'password' => 'password_1'],
            ['username' => 'id_2', 'numRuns' => 4, 'password' => 'password_2'],
            ['username' => 'id_3', 'numRuns' => 2, 'password' => 'password_3'],
            ['username' => 'id_4', 'numRuns' => 6, 'password' => 'password_4'],
            ['username' => 'id_5', 'numRuns' => 5, 'password' => 'password_5'],
        ];

        $identities = [];

        $identityPassword = \OmegaUp\Test\Utils::createRandomString();
        foreach ($submissionsMapping as $index => $submissionsByUser) {
            $identityName = $submissionsByUser['username'];
            $identityPassword = $submissionsByUser['password'];
            $numRuns = $submissionsByUser['numRuns'];
            // Call api using identity creator group member
            \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'username' => "{$group['group']->alias}:{$identityName}",
                'name' => $identityName,
                'password' => $identityPassword,
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => $gender,
                'school_name' => \OmegaUp\Test\Utils::createRandomString(),
                'group_alias' => $group['group']->alias,
            ]));
            $identities[$index] = \OmegaUp\DAO\Identities::findByUsername(
                "{$group['group']->alias}:{$identityName}"
            );
            $identities[$index]->password = $identityPassword;

            [
                'identity' => $users[$index],
                ] = \OmegaUp\Test\Factories\User::createUser();
            self::updateIdentity($users[$index], $gender);

            $runCreationDate = self::setFirstDayOfTheLastMonth();
            $this->createRuns(
                $identities[$index],
                $runCreationDate,
                numRuns: $numRuns
            );
        }

        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );

        $this->assertNull($response['coderinfo']);

        // Create 5 user accounts and associate them with their corresponding
        // identities.
        $users = [];
        foreach ($identities as $index => $identity) {
            $username = $identity->name;
            $identityUsername = $identity->username;
            $identityPassword = $submissionsMapping[$index]['password'];

            [
                'identity' => $users[$index],
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams(
                    [
                        'username' => $username,
                        'name' => $username,
                    ]
                )
            );

            $login = self::login($users[$index]);
            \OmegaUp\Controllers\User::apiAssociateIdentity(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'username' => $identityUsername,
                    'password' => $identityPassword,
                ])
            );
        }

        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );

        $this->assertSame(
            $response['coderinfo']['username'],
            $identities[3]->name
        );
    }

    /**
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCoderOfTheMonthDuringOneYear(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';

        // Create a submissions mapping for different users solving problems
        // in different months during a year
        $submissionsMapping = [
            0 => [
                ['username' => 'user_01', 'numRuns' => 0],
                ['username' => 'user_02', 'numRuns' => 0],
                ['username' => 'user_03', 'numRuns' => 0],
                ['username' => 'user_04', 'numRuns' => 0],
                ['username' => 'user_05', 'numRuns' => 0],
                ['username' => 'user_06', 'numRuns' => 0],
                ['username' => 'user_07', 'numRuns' => 0],
                ['username' => 'user_08', 'numRuns' => 0],
                ['username' => 'user_09', 'numRuns' => 1],
                ['username' => 'user_10', 'numRuns' => 0],
                ['username' => 'user_11', 'numRuns' => 0],
                ['username' => 'user_12', 'numRuns' => 0],
                ['username' => 'user_13', 'numRuns' => 0],
            ],
            1 => [
                ['username' => 'user_01', 'numRuns' => 0],
                ['username' => 'user_02', 'numRuns' => 0],
                ['username' => 'user_03', 'numRuns' => 1],
                ['username' => 'user_04', 'numRuns' => 0],
                ['username' => 'user_05', 'numRuns' => 0],
                ['username' => 'user_06', 'numRuns' => 0],
                ['username' => 'user_07', 'numRuns' => 0],
                ['username' => 'user_08', 'numRuns' => 0],
                ['username' => 'user_09', 'numRuns' => 0],
                ['username' => 'user_10', 'numRuns' => 0],
                ['username' => 'user_11', 'numRuns' => 0],
                ['username' => 'user_12', 'numRuns' => 0],
                ['username' => 'user_13', 'numRuns' => 0],
            ],
            2 => [
                ['username' => 'user_01', 'numRuns' => 0],
                ['username' => 'user_02', 'numRuns' => 0],
                ['username' => 'user_03', 'numRuns' => 0],
                ['username' => 'user_04', 'numRuns' => 0],
                ['username' => 'user_05', 'numRuns' => 0],
                ['username' => 'user_06', 'numRuns' => 0],
                ['username' => 'user_07', 'numRuns' => 0],
                ['username' => 'user_08', 'numRuns' => 0],
                ['username' => 'user_09', 'numRuns' => 0],
                ['username' => 'user_10', 'numRuns' => 1],
                ['username' => 'user_11', 'numRuns' => 0],
                ['username' => 'user_12', 'numRuns' => 0],
                ['username' => 'user_13', 'numRuns' => 0],
            ],
            3 => [
                ['username' => 'user_01', 'numRuns' => 0],
                ['username' => 'user_02', 'numRuns' => 1],
                ['username' => 'user_03', 'numRuns' => 0],
                ['username' => 'user_04', 'numRuns' => 0],
                ['username' => 'user_05', 'numRuns' => 0],
                ['username' => 'user_06', 'numRuns' => 0],
                ['username' => 'user_07', 'numRuns' => 0],
                ['username' => 'user_08', 'numRuns' => 0],
                ['username' => 'user_09', 'numRuns' => 0],
                ['username' => 'user_10', 'numRuns' => 0],
                ['username' => 'user_11', 'numRuns' => 0],
                ['username' => 'user_12', 'numRuns' => 0],
                ['username' => 'user_13', 'numRuns' => 0],
            ],
            4 => [
                ['username' => 'user_01', 'numRuns' => 1],
                ['username' => 'user_02', 'numRuns' => 0],
                ['username' => 'user_03', 'numRuns' => 0],
                ['username' => 'user_04', 'numRuns' => 0],
                ['username' => 'user_05', 'numRuns' => 0],
                ['username' => 'user_06', 'numRuns' => 0],
                ['username' => 'user_07', 'numRuns' => 0],
                ['username' => 'user_08', 'numRuns' => 0],
                ['username' => 'user_09', 'numRuns' => 0],
                ['username' => 'user_10', 'numRuns' => 0],
                ['username' => 'user_11', 'numRuns' => 0],
                ['username' => 'user_12', 'numRuns' => 0],
                ['username' => 'user_13', 'numRuns' => 0],
            ],
            5 => [
                ['username' => 'user_01', 'numRuns' => 0],
                ['username' => 'user_02', 'numRuns' => 0],
                ['username' => 'user_03', 'numRuns' => 0],
                ['username' => 'user_04', 'numRuns' => 0],
                ['username' => 'user_05', 'numRuns' => 0],
                ['username' => 'user_06', 'numRuns' => 0],
                ['username' => 'user_07', 'numRuns' => 0],
                ['username' => 'user_08', 'numRuns' => 1],
                ['username' => 'user_09', 'numRuns' => 0],
                ['username' => 'user_10', 'numRuns' => 0],
                ['username' => 'user_11', 'numRuns' => 0],
                ['username' => 'user_12', 'numRuns' => 0],
                ['username' => 'user_13', 'numRuns' => 0],
            ],
            6 => [
                ['username' => 'user_01', 'numRuns' => 0],
                ['username' => 'user_02', 'numRuns' => 0],
                ['username' => 'user_03', 'numRuns' => 0],
                ['username' => 'user_04', 'numRuns' => 0],
                ['username' => 'user_05', 'numRuns' => 0],
                ['username' => 'user_06', 'numRuns' => 0],
                ['username' => 'user_07', 'numRuns' => 0],
                ['username' => 'user_08', 'numRuns' => 0],
                ['username' => 'user_09', 'numRuns' => 0],
                ['username' => 'user_10', 'numRuns' => 0],
                ['username' => 'user_11', 'numRuns' => 1],
                ['username' => 'user_12', 'numRuns' => 0],
                ['username' => 'user_13', 'numRuns' => 0],
            ],
            7 => [
                ['username' => 'user_01', 'numRuns' => 0],
                ['username' => 'user_02', 'numRuns' => 0],
                ['username' => 'user_03', 'numRuns' => 0],
                ['username' => 'user_04', 'numRuns' => 1],
                ['username' => 'user_05', 'numRuns' => 0],
                ['username' => 'user_06', 'numRuns' => 0],
                ['username' => 'user_07', 'numRuns' => 0],
                ['username' => 'user_08', 'numRuns' => 0],
                ['username' => 'user_09', 'numRuns' => 0],
                ['username' => 'user_10', 'numRuns' => 0],
                ['username' => 'user_11', 'numRuns' => 0],
                ['username' => 'user_12', 'numRuns' => 0],
                ['username' => 'user_13', 'numRuns' => 0],
            ],
            8 => [
                ['username' => 'user_01', 'numRuns' => 0],
                ['username' => 'user_02', 'numRuns' => 0],
                ['username' => 'user_03', 'numRuns' => 0],
                ['username' => 'user_04', 'numRuns' => 0],
                ['username' => 'user_05', 'numRuns' => 1],
                ['username' => 'user_06', 'numRuns' => 0],
                ['username' => 'user_07', 'numRuns' => 0],
                ['username' => 'user_08', 'numRuns' => 0],
                ['username' => 'user_09', 'numRuns' => 0],
                ['username' => 'user_10', 'numRuns' => 0],
                ['username' => 'user_11', 'numRuns' => 0],
                ['username' => 'user_12', 'numRuns' => 0],
                ['username' => 'user_13', 'numRuns' => 0],
            ],
            9 => [
                ['username' => 'user_01', 'numRuns' => 0],
                ['username' => 'user_02', 'numRuns' => 0],
                ['username' => 'user_03', 'numRuns' => 0],
                ['username' => 'user_04', 'numRuns' => 0],
                ['username' => 'user_05', 'numRuns' => 0],
                ['username' => 'user_06', 'numRuns' => 0],
                ['username' => 'user_07', 'numRuns' => 0],
                ['username' => 'user_08', 'numRuns' => 0],
                ['username' => 'user_09', 'numRuns' => 0],
                ['username' => 'user_10', 'numRuns' => 0],
                ['username' => 'user_11', 'numRuns' => 0],
                ['username' => 'user_12', 'numRuns' => 1],
                ['username' => 'user_13', 'numRuns' => 0],
            ],
            10 => [
                ['username' => 'user_01', 'numRuns' => 0],
                ['username' => 'user_02', 'numRuns' => 0],
                ['username' => 'user_03', 'numRuns' => 0],
                ['username' => 'user_04', 'numRuns' => 0],
                ['username' => 'user_05', 'numRuns' => 0],
                ['username' => 'user_06', 'numRuns' => 1],
                ['username' => 'user_07', 'numRuns' => 0],
                ['username' => 'user_08', 'numRuns' => 0],
                ['username' => 'user_09', 'numRuns' => 0],
                ['username' => 'user_10', 'numRuns' => 0],
                ['username' => 'user_11', 'numRuns' => 0],
                ['username' => 'user_12', 'numRuns' => 0],
                ['username' => 'user_13', 'numRuns' => 0],
            ],
            11 => [
                ['username' => 'user_01', 'numRuns' => 0],
                ['username' => 'user_02', 'numRuns' => 0],
                ['username' => 'user_03', 'numRuns' => 0],
                ['username' => 'user_04', 'numRuns' => 0],
                ['username' => 'user_05', 'numRuns' => 0],
                ['username' => 'user_06', 'numRuns' => 0],
                ['username' => 'user_07', 'numRuns' => 1],
                ['username' => 'user_08', 'numRuns' => 0],
                ['username' => 'user_09', 'numRuns' => 0],
                ['username' => 'user_10', 'numRuns' => 0],
                ['username' => 'user_11', 'numRuns' => 0],
                ['username' => 'user_12', 'numRuns' => 0],
                ['username' => 'user_13', 'numRuns' => 0],
            ],
            12 => [
                ['username' => 'user_01', 'numRuns' => 2],
                ['username' => 'user_02', 'numRuns' => 2],
                ['username' => 'user_03', 'numRuns' => 2],
                ['username' => 'user_04', 'numRuns' => 2],
                ['username' => 'user_05', 'numRuns' => 2],
                ['username' => 'user_06', 'numRuns' => 2],
                ['username' => 'user_07', 'numRuns' => 2],
                ['username' => 'user_08', 'numRuns' => 2],
                ['username' => 'user_09', 'numRuns' => 2],
                ['username' => 'user_10', 'numRuns' => 2],
                ['username' => 'user_11', 'numRuns' => 2],
                ['username' => 'user_12', 'numRuns' => 2],
                ['username' => 'user_13', 'numRuns' => 1],
            ],
        ];

        $expectedWinners = [
            0 => 'user_09',
            1 => 'user_03',
            2 => 'user_10',
            3 => 'user_02',
            4 => 'user_01',
            5 => 'user_08',
            6 => 'user_11',
            7 => 'user_04',
            8 => 'user_05',
            9 => 'user_12',
            10 => 'user_06',
            11 => 'user_07',
            // user_13 is the only one who has solved a problem in the last month
            // and hasn't won in the last 12 months, even when they have solved
            // less number of problems than the other users.
            12 => 'user_13',
        ];

        // Create the identities
        $identities = [];

        foreach ($submissionsMapping[0] as $index => $user) {
            [
                'identity' => $identities[$index],
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams(
                    ['username' => $user['username']]
                )
            );
            self::updateIdentity($identities[$index], $gender);
        }

        $initialMonth = 14;
        $runCreationDate = self::setFirstDayOfCustomMonths($initialMonth);
        foreach ($submissionsMapping as $month => $months) {
            foreach ($months as $submissionIndex => $submissions) {
                $this->createRuns(
                    $identities[$submissionIndex],
                    $runCreationDate,
                    $submissions['numRuns']
                );
            }
            \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
            $coderOfTheMonth = $this->getCoderOfTheMonth(
                $runCreationDate,
                '1 month',
                $category
            )['coderinfo'];

            $this->assertSame(
                $coderOfTheMonth['username'],
                $expectedWinners[$month]
            );

            $runCreationDate = self::setFirstDayOfTheCurrentMonth();
        }
    }

    /**
     * Test the API behavior when there is more than one candidate for Coder of
     * the Month during the first days of the current month
     *
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testMultipleCodersOfTheMonth(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';
        // Create a submissions mapping for different users
        // Add random number of runs for 5 users in 8 days
        $submissionsMapping = [
            0 => [
                ['username' => 'user_01', 'numRuns' => 2, 'expectedPosition' => 3],
                ['username' => 'user_02', 'numRuns' => 3, 'expectedPosition' => 2],
                ['username' => 'user_03', 'numRuns' => 4, 'expectedPosition' => 1],
                ['username' => 'user_04', 'numRuns' => 6, 'expectedPosition' => 0],
                ['username' => 'user_05', 'numRuns' => 1, 'expectedPosition' => 4],
            ],
            1 => [
                ['username' => 'user_01', 'numRuns' => 4, 'expectedPosition' => 1],
                ['username' => 'user_02', 'numRuns' => 1, 'expectedPosition' => 3],
                ['username' => 'user_03', 'numRuns' => 1, 'expectedPosition' => 2],
                ['username' => 'user_04', 'numRuns' => 2, 'expectedPosition' => 0],
                ['username' => 'user_05', 'numRuns' => 0, 'expectedPosition' => 4],
            ],
            2 => [
                ['username' => 'user_01', 'numRuns' => 1, 'expectedPosition' => 1],
                ['username' => 'user_02', 'numRuns' => 2, 'expectedPosition' => 2],
                ['username' => 'user_03', 'numRuns' => 0, 'expectedPosition' => 3],
                ['username' => 'user_04', 'numRuns' => 3, 'expectedPosition' => 0],
                ['username' => 'user_05', 'numRuns' => 1, 'expectedPosition' => 4],
            ],
            3 => [
                ['username' => 'user_01', 'numRuns' => 1, 'expectedPosition' => 3],
                ['username' => 'user_02', 'numRuns' => 4, 'expectedPosition' => 1],
                ['username' => 'user_03', 'numRuns' => 4, 'expectedPosition' => 2],
                ['username' => 'user_04', 'numRuns' => 1, 'expectedPosition' => 0],
                ['username' => 'user_05', 'numRuns' => 5, 'expectedPosition' => 4],
            ],
            4 => [
                ['username' => 'user_01', 'numRuns' => 1, 'expectedPosition' => 4],
                ['username' => 'user_02', 'numRuns' => 0, 'expectedPosition' => 3],
                ['username' => 'user_03', 'numRuns' => 2, 'expectedPosition' => 2],
                ['username' => 'user_04', 'numRuns' => 0, 'expectedPosition' => 1],
                ['username' => 'user_05', 'numRuns' => 6, 'expectedPosition' => 0],
            ],
            5 => [
                ['username' => 'user_01', 'numRuns' => 3, 'expectedPosition' => 2],
                ['username' => 'user_02', 'numRuns' => 2, 'expectedPosition' => 3],
                ['username' => 'user_03', 'numRuns' => 0, 'expectedPosition' => 4],
                ['username' => 'user_04', 'numRuns' => 2, 'expectedPosition' => 0],
                ['username' => 'user_05', 'numRuns' => 1, 'expectedPosition' => 1],
            ],
            6 => [
                ['username' => 'user_01', 'numRuns' => 3, 'expectedPosition' => 1],
                ['username' => 'user_02', 'numRuns' => 2, 'expectedPosition' => 2],
                ['username' => 'user_03', 'numRuns' => 1, 'expectedPosition' => 4],
                ['username' => 'user_04', 'numRuns' => 3, 'expectedPosition' => 0],
                ['username' => 'user_05', 'numRuns' => 0, 'expectedPosition' => 3],
            ],
            7 => [
                ['username' => 'user_01', 'numRuns' => 1, 'expectedPosition' => 2],
                ['username' => 'user_02', 'numRuns' => 1, 'expectedPosition' => 3],
                ['username' => 'user_03', 'numRuns' => 3, 'expectedPosition' => 4],
                ['username' => 'user_04', 'numRuns' => 2, 'expectedPosition' => 0],
                ['username' => 'user_05', 'numRuns' => 4, 'expectedPosition' => 1],
            ],
        ];

        foreach ($submissionsMapping[0] as $index => $user) {
            [
                'identity' => $identity[$index],
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams(
                    ['username' => $user['username']]
                )
            );
            self::updateIdentity($identity[$index], $gender);
        }
        $runCreationDate = self::setFirstDayOfTheCurrentMonth();
        foreach ($submissionsMapping as $submissions) {
            foreach ($submissions as $index => $submission) {
                $this->createRuns(
                    $identity[$index],
                    $runCreationDate,
                    $submission['numRuns']
                );
            }
            $runCreationDate = date(
                'Y-m-d',
                strtotime(
                    $runCreationDate . ' +1 day'
                )
            );

            \OmegaUp\Test\Utils::runUpdateRanks();
            $response = \OmegaUp\Controllers\User::getCoderOfTheMonthDetailsForTypeScript(
                new \OmegaUp\Request([
                    'category' => $category,
                ])
            )['templateProperties']['payload'];

            foreach ($response['candidatesToCoderOfTheMonth'] as $index => $candidate) {
                $expectedCandidates = array_filter(
                    $submissions,
                    fn($element) => $element['expectedPosition'] === $index
                );
                $expectedCandidate = array_pop($expectedCandidates);
                $this->assertSame(
                    $expectedCandidate['username'],
                    $candidate['username'],
                    "Failed in the iteration for the day {$runCreationDate}, user {$candidate['username']}"
                );
            }
        }
    }

    /**
     * Test the API behavior when problem admins resolve their own problems
     *
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testProblemAdminsResolvingOwnProblems(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';

        // Create a submissions' mapping for different users solving problems
        // Some users are the admins of the problems they solve
        // The test will cover four scenarios:
        // 1. A user solves problems they created
        // 2. A user solves problems which the admin add them as an admin
        // 3. A user solves problems which the admin add the group admin where
        //    the user is a member
        // 4. A user solves sproblem which they aren't the admin
        $submissionsMapping = [
            [
                ['username' => 'user_admin_creator',       'run' => 1],
                ['username' => 'user_admin_invited',       'run' => 1],
                ['username' => 'user_group_admin_invited', 'run' => 1],
                ['username' => 'user_non_admin',           'run' => 1],
            ],
            [
                ['username' => 'user_admin_creator',       'run' => 1],
                ['username' => 'user_admin_invited',       'run' => 1],
                ['username' => 'user_group_admin_invited', 'run' => 1],
                ['username' => 'user_non_admin',           'run' => 1],
            ],
            [
                ['username' => 'user_admin_creator',       'run' => 1],
                ['username' => 'user_admin_invited',       'run' => 1],
                ['username' => 'user_group_admin_invited', 'run' => 1],
                ['username' => 'user_non_admin',           'run' => 0],
            ],
            [
                ['username' => 'user_admin_creator',       'run' => 0],
                ['username' => 'user_admin_invited',       'run' => 1],
                ['username' => 'user_group_admin_invited', 'run' => 1],
                ['username' => 'user_non_admin',           'run' => 0],
            ],
            [
                ['username' => 'user_admin_creator',       'run' => 0],
                ['username' => 'user_admin_invited',       'run' => 1],
                ['username' => 'user_group_admin_invited', 'run' => 0],
                ['username' => 'user_non_admin',           'run' => 0],
            ],
        ];

        $identities = [];
        foreach ($submissionsMapping[0] as $indexUser => $submissionsUser) {
            [
                'identity' => $identities[$indexUser],
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams(
                    ['username' => $submissionsUser['username']]
                )
            );
            self::updateIdentity($identities[$indexUser], $gender);
        }

        // user_admin_creator creates 5 problems and invites user_admin_invited
        // and user_group_admin_invited is a member of the group where
        // user_admin_creator add as group admin
        $problems = [];

        $login = self::login($identities[0]);

        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $identities[0],
            name: 'prblem_admin_group_name',
            description: 'problem admin group_description',
            alias: 'problem_admin_group_alias',
            login: $login
        );
        \OmegaUp\Controllers\Group::apiAddUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $identities[2]->username,
            'group_alias' => $group['group']->alias
        ]));

        foreach ($submissionsMapping as $indexProblem => $submissionsProblem) {
            $login = self::login($identities[0]);
            $problems[$indexProblem] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
                $identities[0],
                $login
            );

            \OmegaUp\Test\Factories\Problem::addAdminUser(
                $problems[$indexProblem],
                $identities[1]
            );

            \OmegaUp\Test\Factories\Problem::addGroupAdmin(
                $problems[$indexProblem],
                $group['group']
            );
        }

        $runCreationDate = self::setFirstDayOfTheLastMonth();
        foreach ($submissionsMapping as $indexProblem => $submissionsProblem) {
            foreach ($submissionsProblem as $indexUser => $submissionsUser) {
                if ($submissionsUser['run'] == 0) {
                    continue;
                }
                \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
                    $identities[$indexUser],
                    $problems[$indexProblem],
                    $runCreationDate
                );
            }
        }

        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $coderOfTheMonth = $this->getCoderOfTheMonth(
            $runCreationDate,
            '1 month',
            $category
        )['coderinfo'];

        $this->assertSame(
            $identities[3]->username,
            $coderOfTheMonth['username']
        );
    }

    /**
     * Test the API behavior when there is more than one candidate for Coder of
     * the Month during the first days of the latest three months when the
     * verdict is different
     *
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCodersOfTheMonthWithSubmissionsInDifferentMonthsAndVerdicts(
        string $category
    ) {
        $gender = $category == 'all' ? 'male' : 'female';
        // Create a submissions mapping for different users, months, verdicts
        // and problems.
        $usernames = ['user_01', 'user_02', 'user_03', 'user_04', 'user_05', 'user_06'];
        $problems = ['problem_0', 'problem_1', 'problem_2', 'problem_3', 'problem_4'];
        $submissionsMapping = [
            0 => [
                'problem_0' => [ // 63 [3,5]
                    ['username' => 'user_01', 'verdict' => 'PA', 'points' => 0.5],
                    ['username' => 'user_02', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_03', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_04', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_05', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_06', 'verdict' => 'PA', 'points' => 0.5],
                ],
                'problem_1' => [ // 63 [4,5]
                    ['username' => 'user_01', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_02', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_03', 'verdict' => 'PA', 'points' => 0.5],
                    ['username' => 'user_04', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_05', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_06', 'verdict' => 'PA', 'points' => 0.5],
                ],
                'problem_2' => [ // 63 [4,5]
                    ['username' => 'user_01', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_02', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_03', 'verdict' => 'PA', 'points' => 0.5],
                    ['username' => 'user_04', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_05', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_06', 'verdict' => 'WA', 'points' => 0.0],
                ],
                'problem_3' => [ // 63 [2,5]
                    ['username' => 'user_01', 'verdict' => 'PA', 'points' => 0.5],
                    ['username' => 'user_02', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_03', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_04', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_05', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_06', 'verdict' => 'PA', 'points' => 0.5],
                ],
                'problem_4' => [ // 50 [1,4,6]
                    ['username' => 'user_01', 'verdict' => 'AC', 'points' => 1.0], // 50
                    ['username' => 'user_02', 'verdict' => 'PA', 'points' => 0.1], // 63
                    ['username' => 'user_03', 'verdict' => 'WA', 'points' => 0.0], // 63
                    ['username' => 'user_04', 'verdict' => 'AC', 'points' => 1.0], // 176
                    ['username' => 'user_05', 'verdict' => 'PA', 'points' => 0.3], // 252
                    ['username' => 'user_06', 'verdict' => 'AC', 'points' => 1.0], // 50
                ],
            ],
            1 => [
                'problem_0' => [ // 43 [1,2,3,5]
                    ['username' => 'user_01', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_02', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_03', 'verdict' => 'PA', 'points' => 0.5],
                    ['username' => 'user_04', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_05', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_06', 'verdict' => 'PA', 'points' => 0.5],
                ],
                'problem_1' => [ // 50 [1,4,5]
                    ['username' => 'user_01', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_02', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_03', 'verdict' => 'PA', 'points' => 0.5],
                    ['username' => 'user_04', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_05', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_06', 'verdict' => 'PA', 'points' => 0.5],
                ],
                'problem_2' => [ // 50 [2,4,5]
                    ['username' => 'user_01', 'verdict' => 'PA', 'points' => 0.5],
                    ['username' => 'user_02', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_03', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_04', 'verdict' => 'PA', 'points' => 0.5],
                    ['username' => 'user_05', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_06', 'verdict' => 'PA', 'points' => 0.2],
                ],
                'problem_3' => [ // 43 [1,2,4,5]
                    ['username' => 'user_01', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_02', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_03', 'verdict' => 'PA', 'points' => 0.2],
                    ['username' => 'user_04', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_05', 'verdict' => 'PA', 'points' => 0.2],
                    ['username' => 'user_06', 'verdict' => 'WA', 'points' => 0.0],
                ],
                'problem_4' => [ // 43 [1,4,5,6]
                    ['username' => 'user_01', 'verdict' => 'WA', 'points' => 0.0], // 136
                    ['username' => 'user_02', 'verdict' => 'WA', 'points' => 0.0], // 93
                    ['username' => 'user_03', 'verdict' => 'PA', 'points' => 0.2], // 0
                    ['username' => 'user_04', 'verdict' => 'AC', 'points' => 1.0], // 43
                    ['username' => 'user_05', 'verdict' => 'AC', 'points' => 1.0], // 43
                    ['username' => 'user_06', 'verdict' => 'AC', 'points' => 0.0], // 0
                ],
            ],
            2 => [
                'problem_0' => [ // 39 [1,2,3,4,5]
                    ['username' => 'user_01', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_02', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_03', 'verdict' => 'PA', 'points' => 0.3],
                    ['username' => 'user_04', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_05', 'verdict' => 'PA', 'points' => 0.3],
                    ['username' => 'user_06', 'verdict' => 'WA', 'points' => 0.0],
                ],
                'problem_1' => [ // 36 [1,2,3,4,5,6]
                    ['username' => 'user_01', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_02', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_03', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_04', 'verdict' => 'PA', 'points' => 0.5],
                    ['username' => 'user_05', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_06', 'verdict' => 'AC', 'points' => 1.0],
                ],
                'problem_2' => [ // 39 [1,2,3,4,5]
                    ['username' => 'user_01', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_02', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_03', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_04', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_05', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_06', 'verdict' => 'PA', 'points' => 0.3],
                ],
                'problem_3' => [ // 39 [1,2,3,4,5]
                    ['username' => 'user_01', 'verdict' => 'WA', 'points' => 0.0],
                    ['username' => 'user_02', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_03', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_04', 'verdict' => 'AC', 'points' => 1.0],
                    ['username' => 'user_05', 'verdict' => 'PA', 'points' => 0.3],
                    ['username' => 'user_06', 'verdict' => 'WA', 'points' => 0.0],
                ],
                'problem_4' => [ // 39 [1,2,4,5,6]
                    ['username' => 'user_01', 'verdict' => 'AC', 'points' => 1.0], // 78
                    ['username' => 'user_02', 'verdict' => 'AC', 'points' => 1.0], // 75
                    ['username' => 'user_03', 'verdict' => 'WA', 'points' => 0.0], // 114
                    ['username' => 'user_04', 'verdict' => 'PA', 'points' => 0.5], // 39
                    ['username' => 'user_05', 'verdict' => 'WA', 'points' => 0.0], // 0
                    ['username' => 'user_06', 'verdict' => 'AC', 'points' => 1.0], // 36
                ],
            ],
        ];
        $expectedWinners = [
            0 => [
                ['username' => 'user_05', 'score' => 252.0],
                ['username' => 'user_04', 'score' => 176.0],
                ['username' => 'user_03', 'score' => 63.0],
                ['username' => 'user_02', 'score' => 63.0],
                ['username' => 'user_01', 'score' => 50.0],
                //['username' => 'user_06', 'score' => 50.0],
                // user_6 shouldn't be considered because only 5 users are set
                // to be displayed
            ],
            1 => [
                ['username' => 'user_01', 'score' => 136.0],
                ['username' => 'user_02', 'score' => 93.0],
                ['username' => 'user_04', 'score' => 43.0],
                ['username' => 'user_06', 'score' => 0.0],
                // user_05 is not considered because they have already won the
                // CoTM prize
                // user_03 is not considered because they don't have any AC run
                // in the current month
                // user_06 appears on the coders list because they have solved 1
                // problem, but it was solved in the previous month
            ],
            2 => [
                ['username' => 'user_03', 'score' => 114.0],
                ['username' => 'user_02', 'score' => 75.0],
                ['username' => 'user_04', 'score' => 39.0],
                ['username' => 'user_06', 'score' => 36.0],
                // user_01 and user_05 are not considered because they have
                // already won the CoTM prize
            ],
        ];

        // Create 3 users and their identities
        $identities = [];
        foreach ($usernames as $username) {
            [
                'identity' => $identities[$username],
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams(
                    ['username' => $username]
                )
            );
            self::updateIdentity($identities[$username], $gender);
        }

        // Create the problems
        $problemData = [];
        foreach ($problems as $problemAlias) {
            $problemData[
                $problemAlias
            ] = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'quality_seal' => true,
                    'alias' => $problemAlias,
                ])
            );
        }

        $originalTime = \OmegaUp\Time::get();
        // Create the runs by month
        foreach ($submissionsMapping as $month => $problemsRuns) {
            switch ($month) {
                case 0:
                    $runCreationDate = self::setFirstDayOfCustomMonths(
                        monthsLeft: 3
                    );
                    $dateToCalculate = self::setFirstDayOfCustomMonths(
                        monthsLeft: 2
                    );
                    break;
                case 1:
                    $runCreationDate = self::setFirstDayOfCustomMonths(
                        monthsLeft: 2
                    );
                    $dateToCalculate = self::setFirstDayOfTheLastMonth();
                    break;
                case 2:
                    $runCreationDate = self::setFirstDayOfTheLastMonth();
                    $dateToCalculate = self::setFirstDayOfTheCurrentMonth();
                    break;
            }
            foreach ($problemsRuns as $problemAlias => $submissionsProblem) {
                foreach ($submissionsProblem as $runInfo) {
                    [
                        'username' => $username,
                        'verdict' => $verdict,
                        'points' => $points,
                    ] = $runInfo;
                    if (is_null($points)) {
                        continue;
                    }
                    \OmegaUp\Test\Factories\Run::createRunForSpecificProblem(
                        $identities[$username],
                        $problemData[$problemAlias],
                        $runCreationDate,
                        $points,
                        $verdict
                    );
                }
            }

            // Update the rankings
            \OmegaUp\Test\Utils::runUpdateRanks(
                $runCreationDate,
                codersListCount: 5
            );

            // Getting the coder of the month regarding the date
            \OmegaUp\Time::setTimeForTesting(
                strtotime($dateToCalculate) + (60 * 60 * 24)
            );
            $coder = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
                new \OmegaUp\Request([
                    'category' => $category,
                ])
            )['coderinfo'];
            $this->assertSame(
                $coder['username'],
                $expectedWinners[$month][0]['username']
            );
            $codersList = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(
                new \OmegaUp\Request([
                    'date' => $dateToCalculate,
                    'category' => $category,
                ])
            )['coders'];

            $this->assertSameSize(
                $expectedWinners[$month],
                $codersList,
                "Failed on month {$month}"
            );
            foreach ($expectedWinners[$month] as $index => $expectedWinner) {
                $this->assertSame(
                    $expectedWinner['username'],
                    $codersList[$index]['username'],
                    "Failed the order of codersList on month {$month}"
                );
                $this->assertSame(
                    $expectedWinner['score'],
                    $codersList[$index]['score'],
                    "Failed the score on month {$month}"
                );
            }
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }
    }

    private static function setFirstDayOfTheLastMonth() {
        return (new DateTimeImmutable(
            date(
                'Y-m-d',
                \OmegaUp\Time::get()
            )
        ))->modify(
            'first day of last month'
        )->format(
            'Y-m-d'
        );
    }

    private static function setFirstDayOfTheCurrentMonth() {
        return (new DateTimeImmutable(
            date(
                'Y-m-d',
                \OmegaUp\Time::get()
            )
        ))->modify(
            'first day of this month'
        )->format(
            'Y-m-d'
        );
    }

    private static function setFirstDayOfCustomMonths(int $monthsLeft) {
        return (new DateTimeImmutable(
            date(
                'Y-m-d',
                \OmegaUp\Time::get()
            )
        ))->modify("first day of -{$monthsLeft} months")->format('Y-m-d');
    }

    /**
     * Test that site-admins are excluded from Coder of the Month even if they
     * solve more problems than regular users.
     *
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testSiteAdminExcludedFromCoderOfTheMonth(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';

        // Create a regular user who solves few problems
        ['identity' => $regularIdentity] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($regularIdentity, $gender);

        // Create a site-admin user who solves many problems
        ['identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        self::updateIdentity($adminIdentity, $gender);

        $runCreationDate = self::setFirstDayOfTheLastMonth();

        // Admin solves 10 problems (should be excluded from CotM)
        $this->createRuns($adminIdentity, $runCreationDate, numRuns: 10);

        // Regular user solves only 2 problems (should win CotM)
        $this->createRuns($regularIdentity, $runCreationDate, numRuns: 2);

        // Run the ranking calculation
        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);

        // Get the coder of the month
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );

        // Verify that the regular user won CotM, not the site-admin
        $this->assertSame(
            $regularIdentity->username,
            $response['coderinfo']['username'],
            'Regular user should be coder of the month, not site-admin'
        );

        // Verify the admin is not in the candidate list
        $login = self::login($regularIdentity);
        $candidateList = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'date' => date('Y-m-d', \OmegaUp\Time::get()),
                'category' => $category,
            ])
        );

        // Check that admin is not in the list
        foreach ($candidateList['coders'] as $coder) {
            $this->assertNotSame(
                $adminIdentity->username,
                $coder['username'],
                'Site-admin should not appear in CotM candidate list'
            );
        }
    }
}
