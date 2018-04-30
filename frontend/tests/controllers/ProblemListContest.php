<?php

/**
 * Class ProblemListContest
 *
 * @author juan.pablo
 */

class ProblemListContestTest extends OmegaupTestCase {
    public function testSolvedProblemByContest() {
        $num_users = 3;
        $num_problems = 3;

        // Create contest
        $contestData = ContestsFactory::createContest();

        // Create problems and add to contest
        for ($i = 0; $i < $num_problems; $i++) {
            $problem[$i] = ProblemsFactory::createProblem();
            ContestsFactory::addProblemToContest($problem[$i], $contestData);
        }

        // Create users and add to contest
        for ($i = 0; $i < $num_users; $i++) {
            $user[$i] = UserFactory::createUser();
            ContestsFactory::addUser($contestData, $user[$i]);
        }

        // Create runs to contest and to problems directly, both should appear in the report
        $runs = [];
        $runs[0] = RunsFactory::createRun($problem[0], $contestData, $user[0]);
        $runs[1] = RunsFactory::createRun($problem[1], $contestData, $user[1]);
        $runs[2] = RunsFactory::createRun($problem[2], $contestData, $user[2]);
        $runs[3] = RunsFactory::createRunToProblem($problem[0], $user[1]);
        $runs[4] = RunsFactory::createRunToProblem($problem[0], $user[2]);
        RunsFactory::gradeRun($runs[0], '0.0', 'WA'); // run with a WA verdict
        RunsFactory::gradeRun($runs[1], '0.0', 'WA'); // run with a WA verdict
        RunsFactory::gradeRun($runs[2]); // run with a AC verdict
        RunsFactory::gradeRun($runs[3]); // run with a AC verdict
        RunsFactory::gradeRun($runs[4]); // run with a AC verdict

        $directorLogin = self::login($contestData['director']);
        $solvedProblems = ContestController::apiListSolvedProblems(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['contest']->alias,
        ]));
        $unsolvedProblems = ContestController::apiListUnsolvedProblems(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['contest']->alias,
        ]));

        $this->assertArrayHasKey($user[0]->username, $unsolvedProblems['user_problems']);
        $this->assertEquals(1, count($unsolvedProblems['user_problems'][$user[0]->username]));

        $this->assertArrayHasKey($user[1]->username, $unsolvedProblems['user_problems']);
        $this->assertEquals(1, count($unsolvedProblems['user_problems'][$user[1]->username]));
        $this->assertArrayHasKey($user[1]->username, $solvedProblems['user_problems']);
        $this->assertEquals(1, count($solvedProblems['user_problems'][$user[1]->username]));

        $this->assertArrayHasKey($user[2]->username, $solvedProblems['user_problems']);
        $this->assertEquals(2, count($solvedProblems['user_problems'][$user[2]->username]));

        // Now, user[0] submit one run with AC verdict
        $runs[5] = RunsFactory::createRunToProblem($problem[0], $user[0]);
        RunsFactory::gradeRun($runs[5]);

        $solvedProblems = ContestController::apiListSolvedProblems(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['contest']->alias,
        ]));
        $unsolvedProblems = ContestController::apiListUnsolvedProblems(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['contest']->alias,
        ]));

        $this->assertArrayNotHasKey($user[0]->username, $unsolvedProblems['user_problems']);
        $this->assertArrayHasKey($user[0]->username, $solvedProblems['user_problems']);
    }
}
