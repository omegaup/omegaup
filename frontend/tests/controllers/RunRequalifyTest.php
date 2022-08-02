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

        try {
            // Trying to requalify a normal run
            \OmegaUp\Controllers\Run::apiRequalify(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'run_alias' => $guid
                ])
            );
            $this->fail('A run cannot be requalified when it is normal.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('runCannotBeRequalified', $e->getMessage());
        }

        // Disqualify submission
        \OmegaUp\Controllers\Run::apiDisqualify(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'run_alias' => $guid
            ])
        );

        $this->assertSame(
            'disqualified',
            \OmegaUp\DAO\Submissions::getByGuid($guid)->type
        );

        try {
            // Trying to disqualify a disqualified run
            \OmegaUp\Controllers\Run::apiDisqualify(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'run_alias' => $guid
                ])
            );
            $this->fail(
                'A run cannot be disqualified when it has been disqualfied before.'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('runCannotBeDisqualified', $e->getMessage());
        }

        // Requalify submission
        \OmegaUp\Controllers\Run::apiRequalify(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'run_alias' => $guid
            ])
        );

        $this->assertSame(
            'normal',
            \OmegaUp\DAO\Submissions::getByGuid($guid)->type
        );
    }
}
