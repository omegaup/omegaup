<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

class ProblemBestScoreTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test apiBestScore for submits in a problem for current user
     */
    public function testBestScoreInProblem() {
        // Create problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create contestant
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create 2 runs, 100 and 50.
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        $runDataPA = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::gradeRun($runDataPA, 0.5, 'PA');

        // Call API
        $login = self::login($identity);
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
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create contestant
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create 2 runs, 100 and 50.
        $runDataOutsideContest = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        $runDataInsideContest = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataOutsideContest);
        \OmegaUp\Test\Factories\Run::gradeRun($runDataInsideContest, 0.5, 'PA');

        // Call API
        $login = self::login($identity);
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
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create contestant
        ['user' => $contestant, 'identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Create user who will use the API
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create 2 runs, 100 and 50.
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
        $runDataPA = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::gradeRun($runDataPA, 0.5, 'PA');

        // Call API
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiBestScore(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'username' => $contestantIdentity->username
        ]));

        $this->assertEquals(100.00, $response['score']);
    }
}
