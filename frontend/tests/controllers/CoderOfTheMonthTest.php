<?php

/**
 * Test getting general user Info methods
 *
 * @author Alberto
 */
class CoderOfTheMonthTest extends OmegaupTestCase {
    public function testCoderOfTheMonthCalc() {
        $user = UserFactory::createUser();

        $contest = ContestsFactory::createContest();
        $problem = ProblemsFactory::createProblem();

        ContestsFactory::addProblemToContest($problem, $contest);
        ContestsFactory::addUser($contest, $user);

        // Creating 10 AC runs for our user in the last month
        $n = 10;

        $lastMonth = intval(date('m')) - 1;
        $runCreationDate = null;
        if ($lastMonth == 0) {
            $runCreationDate = date(intval((date('Y')) - 1) . '-12-01');
        } else {
            $runCreationDate = date('Y-' . $lastMonth . '-01');
        }

        $this->saveRuns($problem, $contest, $user, $runCreationDate, $n);

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
        $userThisYear = UserFactory::createUser();

        $contestLastYear = ContestsFactory::createContest();
        $problemLastYear = ProblemsFactory::createProblem();
        $contestThisYear = ContestsFactory::createContest();
        $problemThisYear = ProblemsFactory::createProblem();
        $contestThisMonth = ContestsFactory::createContest();
        $problemThisMonth = ProblemsFactory::createProblem();

        ContestsFactory::addProblemToContest($problemLastYear, $contestLastYear);
        ContestsFactory::addUser($contestLastYear, $userLastYear);
        ContestsFactory::addProblemToContest($problemThisYear, $contestThisYear);
        ContestsFactory::addUser($contestThisYear, $userThisYear);
        ContestsFactory::addProblemToContest($problemThisMonth, $contestThisMonth);
        ContestsFactory::addUser($contestThisMonth, $userLastYear);

        $thisYear = intval(date('Y'));
        $lastYear = $thisYear - 1;
        $nextYear = $lastYear + 1;
        $thisMonth = intval(date('m'));
        $nextMonth = $thisMonth + 1;
        $lastMonth = $thisMonth - 1;
        $reviewDate = null;
        // Creating review date of the last year and of this month
        if ($nextMonth == 13) {
            $reviewDate = date('Y-01-01');
            $reviewDateThisMonth = date($nextYear . '-01-01');
        } else {
            $reviewDate = date($lastYear . '-' . str_pad($thisMonth, 2, '0', STR_PAD_LEFT) . '-01');
            $reviewDateThisMonth = date('Y-' . $nextMonth . '-01');
        }
        // Creating run date of the this month
        $runCreationDateTM = date('Y-m-d');
        // Creating run date of the last year
        $runCreationDate = date($lastYear . '-' . ($thisMonth - 1) . '-d');
        // Creating run date of 11 months ago
        $runCreationDate11 = date($lastYear . '-' . $thisMonth . '-d');

        $n = 2;
        $nThis = 1;
        $nThisMonth = 2;

        $this->saveRuns($problemLastYear, $contestLastYear, $userLastYear, $runCreationDate, $n);
        $this->saveRuns($problemLastYear, $contestLastYear, $userLastYear, $runCreationDate11, $n);
        $this->saveRuns($problemThisYear, $contestThisYear, $userThisYear, $runCreationDate11, $nThis);
        $this->saveRuns($problemThisMonth, $contestThisMonth, $userLastYear, $runCreationDateTM, $nThisMonth);

        // Creating review date of 11 months ago
        $reviewDateThisYear = null;
        if ($nextMonth == 13) {
            $reviewDateThisYear = date($lastYear . '-12-01');
        } else {
            $reviewDateThisYear = date($lastYear . '-' . str_pad($nextMonth, 2, '0', STR_PAD_LEFT) . '-01');
        }

        $rLastYear = new Request(['date' => $reviewDate]);
        $rThisYear = new Request(['date' => $reviewDateThisYear]);
        $rThisMonth = new Request(['date' => $reviewDateThisMonth]);

        $responseLastYear = UserController::apiCoderOfTheMonth($rLastYear);
        $responseThisYear = UserController::apiCoderOfTheMonth($rThisYear);
        $responseThisMonth = UserController::apiCoderOfTheMonth($rThisMonth);
        $this->assertEquals($userLastYear->username, $responseLastYear['userinfo']['username']);
        $this->assertNotEquals($userLastYear->username, $responseThisYear['userinfo']['username']);
        $this->assertEquals($userLastYear->username, $responseThisMonth['userinfo']['username']);
    }

    private function saveRuns($problem, $contest, $user, $runCreationDate, $n = 1) {
        for ($i = 0; $i < $n; $i++) {
            $runData = RunsFactory::createRun($problem, $contest, $user);
            RunsFactory::gradeRun($runData);

            // Force the run to be in any date
            $run = RunsDAO::getByAlias($runData['response']['guid']);
            $run->time = $runCreationDate;
            RunsDAO::save($run);
        }
    }
}
