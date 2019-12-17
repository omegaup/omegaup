<?php

/**
 * Test getting general user Info methods
 *
 * @author Alberto
 */
class CoderOfTheMonthTest extends \OmegaUp\Test\ControllerTestCase {
    public function testCoderOfTheMonthCalc() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        ['user' => $extraUser, 'identity' => $extraIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Add a custom school
        $login = self::login($identity);
        $school = SchoolsFactory::createSchool()['school'];
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school->school_id,
        ]));

        // First user solves two problems, second user solves just one
        $runCreationDate = new DateTimeImmutable(date('Y-m-d'));
        $runCreationDate = $runCreationDate->modify('first day of last month');
        $runCreationDate = $runCreationDate->format('Y-m-d');

        $this->createRuns($identity, $runCreationDate, 1 /*numRuns*/);
        $this->createRuns($identity, $runCreationDate, 1 /*numRuns*/);
        $this->createRuns($extraIdentity, $runCreationDate, 1 /*numRuns*/);

        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request()
        );

        $this->assertEquals(
            $identity->username,
            $response['userinfo']['username']
        );
        $this->assertFalse(array_key_exists('email', $response['userinfo']));

        // Calling API again to verify response is the same that in first time
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request()
        );

        $this->assertEquals(
            $identity->username,
            $response['userinfo']['username']
        );

        // CoderOfTheMonth school_id should match with identity school_id
        $this->assertEquals(
            $school->school_id,
            $response['userinfo']['school_id']
        );

        // Now check if the other user has been saved on database too
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'date' => date('Y-m-d', \OmegaUp\Time::get())
        ]));
        $this->assertEquals(
            $identity->username,
            $response['coders'][0]['username']
        );
        $this->assertEquals(
            $extraIdentity->username,
            $response['coders'][1]['username']
        );
    }

    public function testCoderOfTheMonthList() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $auth_token = self::login($identity);

        $r = new \OmegaUp\Request([
            'auth_token' => $auth_token
        ]);

        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList($r);

        // Just one Coder of The Month, the one calculated on the previous test.
        $this->assertCount(1, $response['coders']);

        // Adding parameter date should return the same value, it helps
        // to test getMonthlyList function.
        // It should return two users (the ones that got stored on the previous test)
        $r['date'] = date('Y-m-d', \OmegaUp\Time::get());
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList($r);
        $this->assertCount(2, $response['coders']);
    }

    public function testCodersOfTheMonthBySchool() {
        ['user' => $user_1, 'identity' => $identity_1] = \OmegaUp\Test\Factories\User::createUser();

        ['user' => $user_2, 'identity' => $identity_2] = \OmegaUp\Test\Factories\User::createUser();

        // Identity 3 won't have school_id
        ['user' => $user_3, 'identity' => $identity_3] = \OmegaUp\Test\Factories\User::createUser();

        // Add a custom school for identities 1 and 2
        $school = SchoolsFactory::createSchool()['school'];

        $login = self::login($identity_1);
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school->school_id,
        ]));

        $login = self::login($identity_2);
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school->school_id,
        ]));

        $today = date('Y-m') . '-01';

        // Identity 1 will be the coder of the month of four months ago
        $runCreationDate = date_create($today);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '-4 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($identity_1, $runCreationDate, 1 /*numRuns*/);
        $this->getCoderOfTheMonth($today, '-4 month');

        // Identity 2 will be the coder of the month of three months ago
        $runCreationDate = date_create($runCreationDate);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '1 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($identity_2, $runCreationDate, 1 /*numRuns*/);
        $this->getCoderOfTheMonth($today, '-3 month');

        // Identity 3 will be the coder of the month of two months ago
        $runCreationDate = date_create($runCreationDate);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '1 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($identity_3, $today, 1 /*numRuns*/);
        $this->getCoderOfTheMonth($today, '-2 month');

        // First run api with invalid school_id
        try {
            \OmegaUp\Controllers\School::apiSchoolCodersOfTheMonth(new \OmegaUp\Request([
                'school_id' => 1231,
            ]));
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertEquals($e->getMessage(), 'schoolNotFound');
        }

        // Now run api with valid school_id
        $result = \OmegaUp\Controllers\School::apiSchoolCodersOfTheMonth(new \OmegaUp\Request([
            'school_id' => $school->school_id
        ]));

        // Get all usernames and verify that only identity_1 username
        // and identity_2 username are part of results
        $resultCoders = [];
        foreach ($result['coders'] as $res) {
            $resultCoders[] = $res['username'];
        }

        $this->assertContains($identity_1->username, $resultCoders);
        $this->assertContains($identity_2->username, $resultCoders);
        $this->assertNotContains($identity_3->username, $resultCoders);
    }

    public function testCoderOfTheMonthDetailsForSmarty() {
        // Test coder of the month details when user is not logged
        $r = new \OmegaUp\Request();
        $response = \OmegaUp\Controllers\User::getCoderOfTheMonthDetailsForSmarty(
            $r,
            null
        );
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('codersOfCurrentMonth', $response['payload']);
        $this->assertArrayHasKey('codersOfPreviousMonth', $response['payload']);
        $this->assertFalse($response['payload']['isMentor']);
        $this->assertArrayNotHasKey('options', $response['payload']);

        // Test coder of the month details when common user is logged, it's the
        // same that not logged user
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $r['auth_token'] = $login->auth_token;
        $response = \OmegaUp\Controllers\User::getCoderOfTheMonthDetailsForSmarty(
            $r,
            $identity
        );
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('codersOfCurrentMonth', $response['payload']);
        $this->assertArrayHasKey('codersOfPreviousMonth', $response['payload']);
        $this->assertFalse($response['payload']['isMentor']);
        $this->assertArrayNotHasKey('options', $response['payload']);

        // Test coder of the month details when mentor user is logged
        ['user' => $mentorUser, 'identity' => $mentorIdentity] = \OmegaUp\Test\Factories\User::createMentorIdentity();
        $login = self::login($mentorIdentity);
        $r['auth_token'] = $login->auth_token;
        $response = \OmegaUp\Controllers\User::getCoderOfTheMonthDetailsForSmarty(
            $r,
            $mentorIdentity
        );
        $this->assertTrue($response['payload']['isMentor']);
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('options', $response['payload']);
    }

    public function testCoderOfTheMonthAfterYear() {
        ['user' => $userLastYear, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Using the first day of the month as "today" to avoid failures near
        // certain dates.
        $today = date('Y-m') . '-01';

        $runCreationDate = date_create($today);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '-13 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($identity, $runCreationDate, 2 /*numRuns*/);

        $runCreationDate = date_create($runCreationDate);
        date_add(
            $runCreationDate,
            date_interval_create_from_date_string(
                '1 month'
            )
        );
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($identity, $runCreationDate, 2 /*numRuns*/);

        $this->createRuns($identity, $today, 2 /*numRuns*/);

        // Getting Coder Of The Month
        $responseCoder = $this->getCoderOfTheMonth($today, '-1 year');
        $this->assertEquals(
            $identity->username,
            $responseCoder['userinfo']['username']
        );

        $responseCoder = $this->getCoderOfTheMonth($today, '-11 month');
        $this->assertNotEquals(
            $identity->username,
            $responseCoder['userinfo']['username']
        );

        $responseCoder = $this->getCoderOfTheMonth($today, '1 month');
        $this->assertEquals(
            $identity->username,
            $responseCoder['userinfo']['username']
        );
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
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();
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

    private function getCoderOfTheMonth($revDate, $interval) {
        $reviewDate = date_create($revDate);
        date_add($reviewDate, date_interval_create_from_date_string($interval));
        $reviewDate = date_format($reviewDate, 'Y-m-01');

        \OmegaUp\Time::setTimeForTesting(
            strtotime(
                $reviewDate
            ) + (60 * 60 * 24)
        );
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request(
                []
            )
        );
        return $response;
    }

    /*
     * Mentor can see the last coder of the month email
     */
    public function testMentorCanSeeLastCoderOfTheMonthEmail() {
        ['user' => $mentor, 'identity' => $mentorIdentity] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        $login = self::login($mentorIdentity);
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token
        ]));

        $coders = [];
        foreach ($response['coders'] as $index => $coder) {
            $coders[$index] = $coder['username'];
        }
        $coders = array_unique($coders);

        foreach ($coders as $index => $coder) {
            // HACK: Deleting cache from identity to avoid failures in test
            \OmegaUp\Cache::deleteFromCache(
                \OmegaUp\Cache::USER_PROFILE,
                $coder
            );
            $profile = \OmegaUp\Controllers\User::apiProfile(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $coder
            ]));

            if ($index == 0) {
                // Mentor can see the current coder of the month email
                $this->assertArrayHasKey('email', $profile['userinfo']);
            } else {
                $this->assertArrayNotHasKey('email', $profile['userinfo']);
            }
        }

        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $user_login = self::login($identity);

        foreach ($coders as $index => $coder) {
            $profile = \OmegaUp\Controllers\User::apiProfile(new \OmegaUp\Request([
                'auth_token' => $user_login->auth_token,
                'username' => $coder
            ]));

            $this->assertArrayNotHasKey('email', $profile['userinfo']);
        }
    }

    /**
     * Sending several submissions at next month to verify
     * apiSelectCoderOfTheMonth is working correctly, current month
     * already has a coder of the month selected
     */
    public function testMentorSelectsUserAsCoderOfTheMonth() {
        ['user' => $mentor, 'identity' => $mentorIdentity] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        // Setting time to the 15th of next month.
        $runCreationDate = new DateTimeImmutable(
            date(
                'Y-m-d',
                \OmegaUp\Time::get()
            )
        );
        $runCreationDate = $runCreationDate->modify('first day of next month');
        $runCreationDate = new DateTimeImmutable(
            $runCreationDate->format(
                'Y-m-15'
            )
        );
        \OmegaUp\Time::setTimeForTesting(
            strtotime(
                $runCreationDate->format(
                    'Y-m-d'
                )
            )
        );

        // Submitting some runs with new users
        ['user' => $user1, 'identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $user2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $user3, 'identity' => $identity3] = \OmegaUp\Test\Factories\User::createUser();
        $this->createRuns($identity1, $runCreationDate->format('Y-m-d'), 2);
        $this->createRuns($identity1, $runCreationDate->format('Y-m-d'), 3);
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
            \OmegaUp\Controllers\User::apiSelectCoderOfTheMonth(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $identity3->username,
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
        \OmegaUp\Controllers\User::apiSelectCoderOfTheMonth(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity3->username,
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
            new \OmegaUp\Request()
        );
        $this->assertNotNull(
            $response['userinfo'],
            'A user has been selected by a mentor'
        );
        $this->assertEquals(
            $response['userinfo']['username'],
            $identity3->username
        );
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList(
            new \OmegaUp\Request()
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

        ['user' => $mentor, 'identity' => $mentorIdentity] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        $login = self::login($mentorIdentity);
        $this->assertTrue(\OmegaUp\Authorization::isMentor($mentorIdentity));

        // Testing with an intermediate day of the month
        $timestampTest = \OmegaUp\Time::get();
        $dateTest = date('Y-m-15', $timestampTest);
        $timestampTest = strtotime($dateTest);
        $canChooseCoder = \OmegaUp\Authorization::canChooseCoder(
            $timestampTest
        );
        $this->assertFalse($canChooseCoder);

        // Setting the date to the last day of the current month and testing mentor can choose the coder
        $date = new DateTime('now');
        $date->modify('last day of this month');
        $date->format('Y-m-d');
        \OmegaUp\Time::setTimeForTesting($date->getTimestamp());
        $timestampTest = \OmegaUp\Time::get();
        $dateTest = date('Y-m-d', $timestampTest);
        $canChooseCoder = \OmegaUp\Authorization::canChooseCoder(
            $timestampTest
        );
        $this->assertTrue($canChooseCoder);

        // Setting the date to the first day of the next month and testing mentor can not choose the coder
        \OmegaUp\Time::setTimeForTesting(
            $date->getTimestamp() + (60 * 60 * 24)
        );
        $timestampTest = \OmegaUp\Time::get();
        $dateTest = date('Y-m-d', $timestampTest);
        $canChooseCoder = \OmegaUp\Authorization::canChooseCoder(
            $timestampTest
        );
        $this->assertFalse($canChooseCoder);

        // Setting the date to the second day of the next month and testing mentor can not choose the coder
        \OmegaUp\Time::setTimeForTesting(
            $date->getTimestamp() + (60 * 60 * 48)
        );
        $timestampTest = \OmegaUp\Time::get();
        $dateTest = date('Y-m-d', $timestampTest);
        $canChooseCoder = \OmegaUp\Authorization::canChooseCoder(
            $timestampTest
        );
        $this->assertFalse($canChooseCoder);
    }
}
