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

        $today = date('Y-m-d');

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

    private function createRuns($user, $runCreationDate, $n) {
        $contestFactory = new ContestsFactory(new ContestsParams([]));
        $contest = $contestFactory->createContest();
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
}
