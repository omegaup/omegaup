<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Description of DetailsRunTest
 */
class RunStatusTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic test of viewing run details
     */
    public function testShowRunDetailsValid() {
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a run
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Run::apiStatus(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_alias' => $runData['response']['guid'],
        ]));

        $this->assertSame($runData['response']['guid'], $response['guid']);
        $this->assertSame('JE', $response['verdict']);
        $this->assertSame('uploading', $response['status']);
    }

    /**
     * Basic test of viewing run details with grade run
     */
    public function testShowRunDetailsValidWithGradeRun() {
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

        // Create a run
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0.05, 'PA');

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Run::apiStatus(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_alias' => $runData['response']['guid'],
        ]));

        $this->assertSame($runData['response']['guid'], $response['guid']);
        $this->assertSame('PA', $response['verdict']);
        $this->assertSame('ready', $response['status']);
        $this->assertSame(5.0, $response['contest_score']);
        $this->assertSame(0.05, $response['score']);
    }

    /**
     * Basic test of downloading a full run.
     */
    public function testDownload() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['user' => $user, 'identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();

        $authorIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $problemData['author']->username
        );
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        try {
            \OmegaUp\Controllers\Run::downloadSubmission(
                $runData['response']['guid'],
                $contestantIdentity,
                false
            );
            $this->fail('Should not have allowed to download submission');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }

        $submissionZip = \OmegaUp\Controllers\Run::downloadSubmission(
            $runData['response']['guid'],
            $authorIdentity,
            false
        );
        $this->assertNotNull($submissionZip);
    }
}
