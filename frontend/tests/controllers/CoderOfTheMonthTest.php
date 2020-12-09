<?php

/**
 * Test getting general user Info methods
 *
 * @author Alberto
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
        int $n = 5
    ) {
        if (!$identity) {
            ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        }
        if (!$runCreationDate) {
            $runCreationDate = date('Y-m-d', \OmegaUp\Time::get());
        }
        $contest = \OmegaUp\Test\Factories\Contest::createContest();
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'quality_seal' => true,
            ])
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problem,
            $contest
        );
        \OmegaUp\Test\Factories\Contest::addUser($contest, $identity);

        for ($i = 0; $i < $n; $i++) {
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

        $this->assertEquals($r['name'], $identityDb['name']);
        $this->assertEquals($r['country_id'], $identityDb['country_id']);
        $this->assertEquals($r['state_id'], $identityDb['state_id']);
        $this->assertEquals($r['scholar_degree'], $userDb->scholar_degree);
        $this->assertEquals(
            gmdate(
                'Y-m-d',
                $r['birth_date']
            ),
            $userDb->birth_date
        );
        // Graduation date without school is not saved on database.
        $this->assertNull($graduationDate);
        $this->assertEquals($locale->language_id, $identityDb['language_id']);
    }

    /**
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCoderOfTheMonthCalc(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity, $gender);
        [
            'user' => $extraUser,
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
        $runCreationDate = (new DateTimeImmutable(
            date(
                'Y-m-d',
                \OmegaUp\Time::get()
            )
        ))
            ->modify('first day of last month')
            ->format('Y-m-d');

        $this->createRuns($identity, $runCreationDate, 1 /*numRuns*/);
        $this->createRuns($identity, $runCreationDate, 1 /*numRuns*/);
        $this->createRuns($extraIdentity, $runCreationDate, 1 /*numRuns*/);

        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );

        $this->assertEquals(
            $identity->username,
            $response['coderinfo']['username']
        );
        $this->assertFalse(array_key_exists('email', $response['coderinfo']));

        // Calling API again to verify response is the same that in first time
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(['category' => $category])
        );

        $this->assertEquals(
            $identity->username,
            $response['coderinfo']['username']
        );

        // CoderOfTheMonth school_id should match with identity school_id
        $this->assertEquals(
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
        $this->assertEquals(
            $identity->username,
            $response['coders'][0]['username']
        );
        $this->assertEquals(
            $extraIdentity->username,
            $response['coders'][1]['username']
        );
    }

    /**
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCoderOfTheMonthList(string $category) {
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
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
        [
            'user' => $user1,
            'identity' => $identity1,
        ] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity1, $gender);

        [
            'user' => $user_2,
            'identity' => $identity2,
        ] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity2, $gender);

        // Identity 3 won't have school_id
        [
            'user' => $user_3,
            'identity' => $identity3,
        ] = \OmegaUp\Test\Factories\User::createUser();
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
        $this->createRuns($identity1, $runCreationDate, 1 /*numRuns*/);
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
        $this->createRuns($identity2, $runCreationDate, 1 /*numRuns*/);
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
        $this->createRuns($identity3, $runCreationDate, 1 /*numRuns*/);
        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $this->getCoderOfTheMonth($today, '-1 month', $category);

        // First run function with invalid school_id
        try {
            \OmegaUp\Controllers\School::getSchoolCodersOfTheMonth(
                1231
            );
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertEquals($e->getMessage(), 'schoolNotFound');
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
    public function testCoderOfTheMonthDetailsForSmarty(string $category) {
        // Test coder of the month details when user is not logged
        $response = \OmegaUp\Controllers\User::getCoderOfTheMonthDetailsForSmarty(
            new \OmegaUp\Request(['category' => $category])
        )['smartyProperties'];
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('codersOfCurrentMonth', $response['payload']);
        $this->assertArrayHasKey('codersOfPreviousMonth', $response['payload']);
        $this->assertFalse($response['payload']['isMentor']);
        $this->assertArrayNotHasKey('options', $response['payload']);

        // Test coder of the month details when common user is logged, it's the
        // same that not logged user
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::getCoderOfTheMonthDetailsForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'category' => $category,
            ])
        )['smartyProperties'];
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('codersOfCurrentMonth', $response['payload']);
        $this->assertArrayHasKey('codersOfPreviousMonth', $response['payload']);
        $this->assertFalse($response['payload']['isMentor']);
        $this->assertArrayNotHasKey('options', $response['payload']);

        // Test coder of the month details when mentor user is logged
        [
            'user' => $mentorUser,
            'identity' => $mentorIdentity,
        ] = \OmegaUp\Test\Factories\User::createMentorIdentity();
        $login = self::login($mentorIdentity);
        $response = \OmegaUp\Controllers\User::getCoderOfTheMonthDetailsForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'category' => $category,
            ])
        )['smartyProperties'];
        $this->assertTrue($response['payload']['isMentor']);
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('options', $response['payload']);
    }

    /**
     * @dataProvider coderOfTheMonthCategoryProvider
     */
    public function testCoderOfTheMonthAfterYear(string $category) {
        $gender = $category == 'all' ? 'male' : 'female';
        [
            'user' => $userLastYear,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity, $gender);

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
        $this->createRuns($identity, $runCreationDate, 10 /*numRuns*/);
        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);

        $runCreationDate = date_create($runCreationDate);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '1 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($identity, $runCreationDate, 10 /*numRuns*/);
        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);

        $this->createRuns($identity, $today, 10 /*numRuns*/);
        \OmegaUp\Test\Utils::runUpdateRanks($today);

        // Getting Coder Of The Month
        $responseCoder = $this->getCoderOfTheMonth(
            $today,
            '-12 month',
            $category
        );
        $this->assertEquals(
            $identity->username,
            $responseCoder['coderinfo']['username']
        );

        $responseCoder = $this->getCoderOfTheMonth(
            $today,
            '-11 month',
            $category
        );
        $this->assertNull($responseCoder['coderinfo']);

        $responseCoder = $this->getCoderOfTheMonth(
            $today,
            '1 month',
            $category
        );
        $this->assertEquals(
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

        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
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
            'user' => $mentor,
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
            $this->assertEquals(
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
        $this->assertEquals(
            $response['coderinfo']['username'],
            $identity3->username
        );
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(
            new \OmegaUp\Request(['category' => $category])
        );

        $this->assertEquals(
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
            'user' => $mentor,
            'identity' => $mentorIdentity,
        ] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        $login = self::login($mentorIdentity);
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
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        self::updateIdentity($identity, 'female');
        [
            'user' => $extraUser,
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
        $this->createRuns($identity, $runCreationDate, 1 /*numRuns*/);
        \OmegaUp\Test\Utils::runUpdateRanks($runCreationDate);
        $coderFemale = $this->getCoderOfTheMonth($today, '-5 month', 'female');
        $coderAll = $this->getCoderOfTheMonth($today, '-5 month', 'all');

        // Now check if the third user has not participated in the coder of the
        // month female.
        if (isset($coderAll['coderinfo']['username'])) {
            $this->assertEquals(
                $coderAll['coderinfo']['username'],
                $coderFemale['coderinfo']['username']
            );
        }
    }
}
