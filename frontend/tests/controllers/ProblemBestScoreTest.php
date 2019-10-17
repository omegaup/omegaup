<?php

/**
 * @author joemmanuel
 */

class ProblemBestScoreTest extends OmegaupTestCase {
    /**
     * Test apiBestScore for submits in a problem for current user
     */
    public function testBestScoreInProblem() {
        // Create problem
        $problemData = ProblemsFactory::createProblem();

        // Create contestant
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Create 2 runs, 100 and 50.
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        $runDataPA = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);
        RunsFactory::gradeRun($runDataPA, 0.5, 'PA');

        // Call API
        $login = self::login($contestant);
        $response = \OmegaUp\Controllers\Problem::apiBestScore(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias']
        ]));

        $this->assertEquals(100.00, $response['score']);
    }

    /**
     * Test apiBestScore for submits inside a contest
     */
    public function testBestScoreInsideContest() {
        // Create problem and contest
        $problemData = ProblemsFactory::createProblem();
        $contestData = ContestsFactory::createContest();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create contestant
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Create 2 runs, 100 and 50.
        $runDataOutsideContest = RunsFactory::createRunToProblem(
            $problemData,
            $contestant
        );
        $runDataInsideContest = RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestant
        );
        RunsFactory::gradeRun($runDataOutsideContest);
        RunsFactory::gradeRun($runDataInsideContest, 0.5, 'PA');

        // Call API
        $login = self::login($contestant);
        $response = \OmegaUp\Controllers\Problem::apiBestScore(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'contest_alias' => $contestData['request']['alias']
        ]));

        $this->assertEquals(50.00, $response['score']);
    }

    /**
     * Test apiBestScore for submits in a problem for other user
     */
    public function testBestScoreInProblemOtherUser() {
        // Create problem
        $problemData = ProblemsFactory::createProblem();

        // Create contestant
        ['user' => $contestant, 'identity' => $contestantIdentity] = UserFactory::createUser();

        // Create user who will use the API
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        // Create 2 runs, 100 and 50.
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        $runDataPA = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);
        RunsFactory::gradeRun($runDataPA, 0.5, 'PA');

        // Call API
        $login = self::login($user);
        $response = \OmegaUp\Controllers\Problem::apiBestScore(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'username' => $contestant->username
        ]));

        $this->assertEquals(100.00, $response['score']);
    }
}
