<?php

/**
 * Test getting general user Info methods
 *
 * @author Alberto
 */
class CoderOfTheMonthTest extends OmegaupTestCase {
    public function testCoderOfTheMonthCalc() {
        $user = UserFactory::createUser();

        // Creating 10 AC runs for our user in the last month
        $runCreationDate = date_create(date('Y-m-d'));
        date_add($runCreationDate, date_interval_create_from_date_string('-1 month'));
        $runCreationDate = date_format($runCreationDate, 'Y-m-01');

        $this->createRuns($user, $runCreationDate, 10 /*numRuns*/);

        $response = UserController::apiCoderOfTheMonth(new Request());

        $this->assertEquals($user->username, $response['userinfo']['username']);
        $this->assertFalse(array_key_exists('email', $response['userinfo']));

        // Calling API again to verify response is the same that in first time
        $response = UserController::apiCoderOfTheMonth(new Request());

        $this->assertEquals($user->username, $response['userinfo']['username']);
    }

    public function testCoderOfTheMonthList() {
        $user = UserFactory::createUser();
        $auth_token = self::login($user);

        $r = new Request([
            'auth_token' => $auth_token
        ]);

        $response = UserController::apiCoderOfTheMonthList($r);

        $this->assertEquals(1, count($response['coders']));
    }

    public function testCoderOfTheMonthAfterYear() {
        $userLastYear = UserFactory::createUser();

        // Using the first day of the month as "today" to avoid failures near
        // certain dates.
        $today = date('Y-m') . '-01';

        $runCreationDate = date_create($today);
        date_add($runCreationDate, date_interval_create_from_date_string('-13 month'));
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($userLastYear, $runCreationDate, 2 /*numRuns*/);

        $runCreationDate = date_create($runCreationDate);
        date_add($runCreationDate, date_interval_create_from_date_string('1 month'));
        $runCreationDate = date_format($runCreationDate, 'Y-m-d');
        $this->createRuns($userLastYear, $runCreationDate, 2 /*numRuns*/);

        $this->createRuns($userLastYear, $today, 2 /*numRuns*/);

        // Getting Coder Of The Month
        $responseCoder = $this->getCoderOfTheMonth($today, '-1 year');
        $this->assertEquals($userLastYear->username, $responseCoder['userinfo']['username']);

        $responseCoder = $this->getCoderOfTheMonth($today, '-11 month');
        $this->assertNotEquals($userLastYear->username, $responseCoder['userinfo']['username']);

        $responseCoder = $this->getCoderOfTheMonth($today, '1 month');
        $this->assertEquals($userLastYear->username, $responseCoder['userinfo']['username']);
    }

    private function createRuns($user = null, $runCreationDate = null, $n = 5) {
        if (!$user) {
            $user = UserFactory::createUser();
        }
        if (!$runCreationDate) {
            $runCreationDate = date('Y-m-d', Time::get());
        }
        $contest = ContestsFactory::createContest();
        $problem = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problem, $contest);
        ContestsFactory::addUser($contest, $user);

        for ($i = 0; $i < $n; $i++) {
            $runData = RunsFactory::createRun($problem, $contest, $user);
            RunsFactory::gradeRun($runData);

            // Force the run to be in any date
            $run = RunsDAO::getByAlias($runData['response']['guid']);
            $run->time = $runCreationDate;
            RunsDAO::save($run);
        }
    }

    private function getCoderOfTheMonth($revDate, $interval) {
        $reviewDate = date_create($revDate);
        date_add($reviewDate, date_interval_create_from_date_string($interval));
        $reviewDate = date_format($reviewDate, 'Y-m-01');

        Time::setTimeForTesting(strtotime($reviewDate) + (60 * 60 * 24));
        $response = UserController::apiCoderOfTheMonth(new Request([]));
        return $response;
    }

    /*
     * Mentor can see the last coder of the month email
     */
    public function testMentorCanSeeLastCoderOfTheMonthEmail() {
        $mentor = UserFactory::createMentorIdentity();

        $login = self::login($mentor);
        $response = UserController::apiCoderOfTheMonthList(new Request([
            'auth_token' => $login->auth_token
        ]));

        $coders = [];
        foreach ($response['coders'] as $index => $coder) {
            $coders[$index] = $coder['username'];
        }
        $coders = array_unique($coders);

        foreach ($coders as $index => $coder) {
            $profile = UserController::apiProfile(new Request([
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

        $user = UserFactory::createUser();
        $user_login = self::login($user);

        foreach ($coders as $index => $coder) {
            $profile = UserController::apiProfile(new Request([
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
        $mentor = UserFactory::createMentorIdentity();

        // Setting time
        $currentMonth = date('m');
        $runCreationDate = date('Y-' . ($currentMonth + 1) . '-15');
        Time::setTimeForTesting(strtotime($runCreationDate));

        // Submitting some runs with new users
        $user1 = UserFactory::createUser();
        $user2 = UserFactory::createUser();
        $user3 = UserFactory::createUser();
        $this->createRuns($user1, $runCreationDate, 2);
        $this->createRuns($user1, $runCreationDate, 3);
        $this->createRuns($user2, $runCreationDate, 4);
        $this->createRuns($user2, $runCreationDate, 1);
        $this->createRuns($user3, $runCreationDate, 2);

        // Setting new date
        $firstDayOfMonth = new DateTime($runCreationDate);
        $firstDayOfMonth->modify('first day of next month');
        Time::setTimeForTesting(strtotime($firstDayOfMonth->format('Y-m-d')));

        // Selecting one user as coder of the month
        $login = self::login($mentor);

        // Call api. This should fail.
        try {
            $response = UserController::apiSelectCoderOfTheMonth(new Request([
                'auth_token' => $login->auth_token,
                'username' => $user3->username,
            ]));
            $this->fail('Exception was expected, because date is not in the range to select coder');
        } catch (ForbiddenAccessException $e) {
            // Pass
        }

        // Changing date with valid range
        $runCreationDate = date('Y-' . ($currentMonth + 2) . '-t');
        Time::setTimeForTesting(strtotime($runCreationDate));

        // Call api again.
        $response = UserController::apiSelectCoderOfTheMonth(new Request([
            'auth_token' => $login->auth_token,
            'username' => $user3->username,
        ]));

        $response = UserController::apiCoderOfTheMonth(new Request([]));
        $this->assertNotNull($response['userinfo'], 'A user has been selected by a mentor');
        $this->assertEquals($response['userinfo']['username'], $user3->username);
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

        $mentor = UserFactory::createMentorIdentity();

        $login = self::login($mentor);
        $this->assertTrue(Authorization::isMentor($mentor->main_identity_id));

        // Testing with the current date
        $currentDateTimestamp = Time::get();
        $currentDate = date('Y-m-d', $currentDateTimestamp);
        $lastDayOfMonth = new DateTime($currentDate);
        $lastDayOfMonth->modify('last day of this month');
        $date = new DateTime('now');
        $date->modify('last day of this month');
        $lastDayOfMonth = $date->format('Y-m-d');
        $coders = CoderOfTheMonthDAO::calculateCoderOfCurrentMonth($currentDate);
        $this->assertEquals(3, count($coders));

        // Setting the date to the last day of the currrent month and testing mentor can choose the coder
        Time::setTimeForTesting($date->getTimestamp());
        $currentDateTimestamp = Time::get();
        $currentDate = date('Y-m-d', $currentDateTimestamp);
        $coders = CoderOfTheMonthDAO::calculateCoderOfCurrentMonth($currentDate);
        $this->assertEquals(3, count($coders));

        // Setting the date to the first day of the next month and testing mentor can not choose the coder
        Time::setTimeForTesting($date->getTimestamp() + (60 * 60 * 24));
        $currentDateTimestamp = Time::get();
        $currentDate = date('Y-m-d', $currentDateTimestamp);
        $coders = CoderOfTheMonthDAO::calculateCoderOfCurrentMonth($currentDate);
        $this->assertNull($coders);

        // Setting the date to the second day of the next month and testing mentor can not choose the coder
        Time::setTimeForTesting($date->getTimestamp() + (60 * 60 * 48));
        $currentDateTimestamp = Time::get();
        $currentDate = date('Y-m-d', $currentDateTimestamp);
        $coders = CoderOfTheMonthDAO::calculateCoderOfCurrentMonth($currentDate);
        // No runs to calculate the coder for this month
        $this->assertNull($coders);
    }
}
