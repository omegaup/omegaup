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
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun(
            $problemData,
            $contestData,
            $identity
        );

        $login = self::login($identity);
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
        ['user' => $user, 'identity' => $contestantIdentity] = UserFactory::createUser();

        $authorIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $problemData['author']->username
        );
        $runData = RunsFactory::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
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
