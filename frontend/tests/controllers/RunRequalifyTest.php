<?php
/**
 * Unittest for requalifying run
 */
class RunRequalifyTest extends \OmegaUp\Test\ControllerTestCase {
    public function testRequalifyByAdmin() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a new run
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $login = self::login($contestData['director']);

        // Disqualify submission
        \OmegaUp\Controllers\Run::apiDisqualify(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'run_alias' => $runData['response']['guid']
            ])
        );

        $this->assertEquals(
            'disqualified',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $runData['response']['guid']
                )->current_run_id
            )->type
        );

        // Requalify submission
        \OmegaUp\Controllers\Run::apiRequalify(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'run_alias' => $runData['response']['guid']
            ])
        );

        $this->assertEquals(
            'normal',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $runData['response']['guid']
                )->current_run_id
            )->type
        );
    }
}
