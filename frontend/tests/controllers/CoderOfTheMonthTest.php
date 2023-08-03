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
    ) {
        if (!$identity) {
            ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        }
        if (!$runCreationDate) {
            $runCreationDate = date('Y-m-d', \OmegaUp\Time::get());
        }
        $contest = \OmegaUp\Test\Factories\Contest::createContest();
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'quality_seal' => $quality,
            ])
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problem,
            $contest
        );
        \OmegaUp\Test\Factories\Contest::addUser($contest, $identity);

        foreach (range(0, $numRuns - 1) as $_) {
            $runData = \OmegaUp\Test\Factories\Run::createRun(
                $problem,
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
        $runCreationDate = self::setFirstDayOfTheMonth();

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
        $this->assertFalse(array_key_exists('email', $response['coderinfo']));

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
        $runCreationDate = self::setFirstDayOfTheMonth();

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
        $this->assertFalse(array_key_exists('email', $response['coderinfo']));

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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity, $gender);

        // User "B" is always the second one in the ranking based on score
        ['identity' => $identityB] = \OmegaUp\Test\Factories\User::createUser();
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
        $runCreationDate = self::setFirstDayOfTheMonth();

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

    private static function setFirstDayOfTheMonth() {
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
}
