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
        $response = RunController::apiStatus(new Request([
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
        $contestantIdentity = IdentityController::resolveIdentity($user->username);
        $authorIdentity = IdentityController::resolveIdentity(
            $problemData['author']->username
        );
        $runData = RunsFactory::createRunToProblem($problemData, $user);
        RunsFactory::gradeRun($runData);

        try {
            RunController::downloadSubmission(
                $runData['response']['guid'],
                $contestantIdentity,
                false
            );
            $this->fail('Should not have allowed to download submission');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        $submissionZip = RunController::downloadSubmission(
            $runData['response']['guid'],
            $authorIdentity,
            false
        );
        $this->assertNotNull($submissionZip);
    }
}
