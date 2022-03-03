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

        $guid = $runData['response']['guid'];

        // Disqualify submission
        \OmegaUp\Controllers\Run::apiDisqualify(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'run_alias' => $guid
            ])
        );

        $this->assertEquals(
            'disqualified',
            \OmegaUp\DAO\Submissions::getByGuid($guid)->type
        );

        // Requalify submission
        \OmegaUp\Controllers\Run::apiRequalify(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'run_alias' => $guid
            ])
        );

        $this->assertEquals(
            'normal',
            \OmegaUp\DAO\Submissions::getByGuid($guid)->type
        );
    }
}
