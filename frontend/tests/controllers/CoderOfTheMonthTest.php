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
        $response = UserController::apiCoderOfTheMonth(
            new Request(['date' => $reviewDate])
        );
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
        $currentDate = date('Y-m-d', Time::get());
        $lastDayOfMonth = new DateTime($currentDate);
        $lastDayOfMonth->modify('last day of this month');
        $currentMonth = date('m', Time::get());
        $date = new DateTime('now');
        $date->modify('last day of this month');
        $lastDayOfMonth = $date->format('Y-m-d');
        $result = Authorization::canChooseCoder();
        $dateToCalculate = $result['canChoose'] ? date('Y-' . $result['monthToChoose'] . '-d') : $currentDate;
        $coders = CoderOfTheMonthDAO::calculateCoderOfTheMonth($dateToCalculate, true);
        if ($currentDate == $lastDayOfMonth) {
            $this->assertTrue($result['canChoose']);
            $this->assertEquals($result['monthToChoose'], $currentMonth);
        } else {
            $this->assertFalse($result['canChoose']);
        }
        $this->assertEquals(3, count($coders));

        // Setting the date to the last day of the currrent month and testing mentor can choose the coder
        Time::setTimeForTesting($date->getTimestamp());
        $currentDate = date('Y-m-d', Time::get());
        $currentMonth = date('m', Time::get());
        $result = Authorization::canChooseCoder();
        $dateToCalculate = $result['canChoose'] ? date('Y-' . $result['monthToChoose'] . '-d') : $currentDate;
        $coders = CoderOfTheMonthDAO::calculateCoderOfTheMonth($dateToCalculate, true);
        $this->assertTrue($result['canChoose']);
        $this->assertEquals($result['monthToChoose'], $currentMonth);
        $this->assertEquals(3, count($coders));

        // Setting the date to the first day of the next month and testing mentor still can choose the coder
        // In this case, mentor is still watching coders of the previous month
        Time::setTimeForTesting($date->getTimestamp() + (60 * 60 * 24));
        $currentDate = date('Y-m-d', Time::get());
        $currentMonth = date('m', Time::get());
        $result = Authorization::canChooseCoder();
        $dateToCalculate = $result['canChoose'] ? date('Y-' . $result['monthToChoose'] . '-d') : $currentDate;
        $coders = CoderOfTheMonthDAO::calculateCoderOfTheMonth($dateToCalculate, true);
        $this->assertTrue($result['canChoose']);
        $this->assertEquals($result['monthToChoose'], $currentMonth - 1);
        $this->assertEquals(3, count($coders));

        // Setting the date to the second day of the next month and testing mentor can not choose the coder
        Time::setTimeForTesting($date->getTimestamp() + (60 * 60 * 48));
        $currentDate = date('Y-m-d', Time::get());
        $currentMonth = date('m', Time::get());
        $result = Authorization::canChooseCoder();
        $dateToCalculate = $result['canChoose'] ? date('Y-' . $result['monthToChoose'] . '-d') : $currentDate;
        $coders = CoderOfTheMonthDAO::calculateCoderOfTheMonth($dateToCalculate, true);
        $this->assertFalse($result['canChoose']);
        $this->assertArrayNotHasKey('monthToChoose', $result, 'Function canChooseCoder does not return this key when canChoose attribute is false');
        // No runs to calculate the coder for this month
        $this->assertNull($coders);
    }
}
