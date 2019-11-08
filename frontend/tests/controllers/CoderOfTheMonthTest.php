<?php

/**
 * Test getting general user Info methods
 *
 * @author Alberto
 */
class CoderOfTheMonthTest extends OmegaupTestCase {
    public function testCoderOfTheMonthCalc() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        // Creating 10 AC runs for our user in the last month
        $runCreationDate = new DateTimeImmutable(date('Y-m-d'));
        $runCreationDate = $runCreationDate->modify('first day of last month');
        $runCreationDate = $runCreationDate->format('Y-m-d');

        $this->createRuns($identity, $runCreationDate, 10 /*numRuns*/);

        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request()
        );

        $this->assertEquals($user->username, $response['userinfo']['username']);
        $this->assertFalse(array_key_exists('email', $response['userinfo']));

        // Calling API again to verify response is the same that in first time
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonth(
            new \OmegaUp\Request()
        );

        $this->assertEquals($user->username, $response['userinfo']['username']);
    }

    public function testCoderOfTheMonthList() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $auth_token = self::login($identity);

        $r = new \OmegaUp\Request([
            'auth_token' => $auth_token
        ]);

        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList($r);

        $this->assertCount(0, $response['coders']);

        // Adding parameter date should return the same value, it helps
        // to test getMonthlyList function, which never was tested
        $r['date'] = date('Y-m-d', \OmegaUp\Time::get());
        $response = \OmegaUp\Controllers\User::apiCoderOfTheMonthList($r);
        $this->assertCount(0, $response['coders']);
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
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
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
        ['user' => $mentorUser, 'identity' => $mentorIdentity] = UserFactory::createMentorIdentity();
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
        ['user' => $userLastYear, 'identity' => $identity] = UserFactory::createUser();

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
            $userLastYear->username,
            $responseCoder['userinfo']['username']
        );

        $responseCoder = $this->getCoderOfTheMonth($today, '-11 month');
        $this->assertNotEquals(
            $userLastYear->username,
            $responseCoder['userinfo']['username']
        );

        $responseCoder = $this->getCoderOfTheMonth($today, '1 month');
        $this->assertEquals(
            $userLastYear->username,
            $responseCoder['userinfo']['username']
        );
    }

    private function createRuns(
        \OmegaUp\DAO\VO\Identities $identity = null,
        string $runCreationDate = null,
        int $n = 5
    ) {
        if (!$identity) {
            ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        }
        if (!$runCreationDate) {
            $runCreationDate = date('Y-m-d', \OmegaUp\Time::get());
        }
        $contest = ContestsFactory::createContest();
        $problem = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problem, $contest);
        ContestsFactory::addUser($contest, $identity);

        for ($i = 0; $i < $n; $i++) {
            $runData = RunsFactory::createRun($problem, $contest, $identity);
            RunsFactory::gradeRun($runData);
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
        ['user' => $mentorUser, 'identity' => $mentorIdentity] = UserFactory::createMentorIdentity();

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

        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
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
        ['user' => $mentorUser, 'identity' => $mentorIdentity] = UserFactory::createMentorIdentity();

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
        ['user' => $user1, 'identity' => $identity1] = UserFactory::createUser();
        ['user' => $user2, 'identity' => $identity2] = UserFactory::createUser();
        ['user' => $user3, 'identity' => $identity3] = UserFactory::createUser();
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
            $user3->username
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

        ['user' => $mentorUser, 'identity' => $mentorIdentity] = UserFactory::createMentorIdentity();

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
