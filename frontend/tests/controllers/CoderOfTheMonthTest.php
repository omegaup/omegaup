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
        $runCreationDate = new DateTimeImmutable(date('Y-m-d'));
        $runCreationDate = $runCreationDate->modify('first day of last month');
        $runCreationDate = $runCreationDate->format('Y-m-d');

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

        $this->assertCount(0, $response['coders']);

        // Adding parameter date should return the same value, it helps
        // to test getMonthlyList function, which never was tested
        $r['date'] = date('Y-m-d', Time::get());
        $response = UserController::apiCoderOfTheMonthList($r);
        $this->assertCount(0, $response['coders']);
    }

    public function testCoderOfTheMonthDetailsForSmarty() {
        // Test coder of the month details when user is not logged
        $r = new Request();
        $response = UserController::getCoderOfTheMonthDetailsForSmarty($r, null);
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('codersOfCurrentMonth', $response['payload']);
        $this->assertArrayHasKey('codersOfPreviousMonth', $response['payload']);
        $this->assertFalse($response['payload']['isMentor']);
        $this->assertArrayNotHasKey('options', $response['payload']);

        // Test coder of the month details when common user is logged, it's the
        // same that not logged user
        $user = UserFactory::createUser();
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);
        $login = self::login($user);
        $r['auth_token'] = $login->auth_token;
        $response = UserController::getCoderOfTheMonthDetailsForSmarty($r, $identity);
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('codersOfCurrentMonth', $response['payload']);
        $this->assertArrayHasKey('codersOfPreviousMonth', $response['payload']);
        $this->assertFalse($response['payload']['isMentor']);
        $this->assertArrayNotHasKey('options', $response['payload']);

        // Test coder of the month details when mentor user is logged
        [$mentorUser, $mentorIdentity] = UserFactory::createMentorIdentity();
        $login = self::login($mentorUser);
        $r['auth_token'] = $login->auth_token;
        $response = UserController::getCoderOfTheMonthDetailsForSmarty(
            $r,
            $mentorIdentity
        );
        $this->assertTrue($response['payload']['isMentor']);
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('options', $response['payload']);
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
            //sumbmission gap between runs must be 60 seconds
            Time::setTimeForTesting(Time::get() + 60);

            // Force the submission to be in any date
            $submission = SubmissionsDAO::getByGuid($runData['response']['guid']);
            $submission->time = $runCreationDate;
            SubmissionsDAO::update($submission);
            $run = RunsDAO::getByPK($submission->current_run_id);
            $run->time = $runCreationDate;
            RunsDAO::update($run);
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
        [$mentorUser,] = UserFactory::createMentorIdentity();

        $login = self::login($mentorUser);
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
        [$mentorUser,] = UserFactory::createMentorIdentity();

        // Setting time to the 15th of next month.
        $runCreationDate = new DateTimeImmutable(date('Y-m-d', Time::get()));
        $runCreationDate = $runCreationDate->modify('first day of next month');
        $runCreationDate = new DateTimeImmutable($runCreationDate->format('Y-m-15'));
        Time::setTimeForTesting(strtotime($runCreationDate->format('Y-m-d')));

        // Submitting some runs with new users
        $user1 = UserFactory::createUser();
        $user2 = UserFactory::createUser();
        $user3 = UserFactory::createUser();
        $this->createRuns($user1, $runCreationDate->format('Y-m-d'), 2);
        $this->createRuns($user1, $runCreationDate->format('Y-m-d'), 3);
        $this->createRuns($user2, $runCreationDate->format('Y-m-d'), 4);
        $this->createRuns($user2, $runCreationDate->format('Y-m-d'), 1);
        $this->createRuns($user3, $runCreationDate->format('Y-m-d'), 2);

        // Setting new date to the first of the month following the run
        // creation.
        $firstDayOfNextMonth = $runCreationDate->modify('first day of next month');
        Time::setTimeForTesting(strtotime($firstDayOfNextMonth->format('Y-m-d')));

        // Selecting one user as coder of the month
        $login = self::login($mentorUser);

        // Call api. This should fail.
        try {
            UserController::apiSelectCoderOfTheMonth(new Request([
                'auth_token' => $login->auth_token,
                'username' => $user3->username,
            ]));
            $this->fail('Exception was expected, because date is not in the range to select coder');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'coderOfTheMonthIsNotInPeriodToBeChosen');
            // Pass
        }

        // Changing date to the last day of the month in which the run was created.
        Time::setTimeForTesting(strtotime($runCreationDate->format('Y-m-t')));

        // Call api again.
        UserController::apiSelectCoderOfTheMonth(new Request([
            'auth_token' => $login->auth_token,
            'username' => $user3->username,
        ]));

        // Set date to first day of next month
        Time::setTimeForTesting(strtotime($firstDayOfNextMonth->format('Y-m-d')));

        $response = UserController::apiCoderOfTheMonth(new Request());
        $this->assertNotNull($response['userinfo'], 'A user has been selected by a mentor');
        $this->assertEquals($response['userinfo']['username'], $user3->username);
        $response = UserController::apiCoderOfTheMonthList(new Request());

        $this->assertEquals($firstDayOfNextMonth->format('Y-m-d'), $response['coders'][0]['date']);
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

        [$mentorUser, $mentorIdentity] = UserFactory::createMentorIdentity();

        $login = self::login($mentorUser);
        $this->assertTrue(Authorization::isMentor($mentorIdentity));

        // Testing with an intermediate day of the month
        $timestampTest = Time::get();
        $dateTest = date('Y-m-15', $timestampTest);
        $timestampTest = strtotime($dateTest);
        $canChooseCoder = Authorization::canChooseCoder($timestampTest);
        $this->assertFalse($canChooseCoder);

        // Setting the date to the last day of the current month and testing mentor can choose the coder
        $date = new DateTime('now');
        $date->modify('last day of this month');
        $date->format('Y-m-d');
        Time::setTimeForTesting($date->getTimestamp());
        $timestampTest = Time::get();
        $dateTest = date('Y-m-d', $timestampTest);
        $canChooseCoder = Authorization::canChooseCoder($timestampTest);
        $this->assertTrue($canChooseCoder);

        // Setting the date to the first day of the next month and testing mentor can not choose the coder
        Time::setTimeForTesting($date->getTimestamp() + (60 * 60 * 24));
        $timestampTest = Time::get();
        $dateTest = date('Y-m-d', $timestampTest);
        $canChooseCoder = Authorization::canChooseCoder($timestampTest);
        $this->assertFalse($canChooseCoder);

        // Setting the date to the second day of the next month and testing mentor can not choose the coder
        Time::setTimeForTesting($date->getTimestamp() + (60 * 60 * 48));
        $timestampTest = Time::get();
        $dateTest = date('Y-m-d', $timestampTest);
        $canChooseCoder = Authorization::canChooseCoder($timestampTest);
        $this->assertFalse($canChooseCoder);
    }
}
