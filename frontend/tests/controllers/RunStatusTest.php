<?php

/**
 * Description of DetailsRunTest
 */
class RunStatusTest extends OmegaupTestCase {
    /**
     * Basic test of viewing run details
     */
    public function testShowRunDetailsValid() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        $login = self::login($contestant);
        $response = \OmegaUp\Controllers\Run::apiStatus(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_alias' => $runData['response']['guid'],
        ]));

        $this->assertEquals($runData['response']['guid'], $response['guid']);
        $this->assertEquals('JE', $response['verdict']);
        $this->assertEquals('new', $response['status']);
    }

    /**
     * Basic test of downloading a full run.
     */
    public function testDownload() {
        $problemData = ProblemsFactory::createProblem();
        $user = UserFactory::createUser();
        $contestantIdentity = \OmegaUp\Controllers\Identity::resolveIdentity($user->username);
        $authorIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $problemData['author']->username
        );
        $runData = RunsFactory::createRunToProblem($problemData, $user);
        RunsFactory::gradeRun($runData);

        try {
            \OmegaUp\Controllers\Run::downloadSubmission(
                $runData['response']['guid'],
                $contestantIdentity,
                false
            );
            $this->fail('Should not have allowed to download submission');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        $submissionZip = \OmegaUp\Controllers\Run::downloadSubmission(
            $runData['response']['guid'],
            $authorIdentity,
            false
        );
        $this->assertNotNull($submissionZip);
    }
}
