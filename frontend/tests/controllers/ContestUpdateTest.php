<?php

/**
 * Description of UpdateContest
 *
 * @author joemmanuel
 */
class UpdateContestTest extends OmegaupTestCase {
    private $contestFactory;
    private $contestFactoryPrivate;

    public function __construct() {
        $this->contestFactory = new ContestsFactory(new ContestsParams([]));
        $this->contestFactoryPrivate = new ContestsFactory(new ContestsParams(['public' => 0]));
    }

    /**
     * Only update the contest title. Rest should stay the same
     */
    public function testUpdateContestTitle() {
        // Get a contest
        $contestData = $this->contestFactory->createContest();

        // Update title.
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'title' => Utils::CreateRandomString(),
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);

        // To validate, we update the title to the original request and send
        // the entire original request to assertContest. Any other parameter
        // should not be modified by Update api
        $contestData['request']['title'] = $r['title'];
        $this->assertContest($contestData['request']);
    }

    /**
     *
     * @expectedException ForbiddenAccessException
     */
    public function testUpdateContestNonDirector() {
        // Get a contest
        $contestData = $this->contestFactory->createContest();
        // Update title
        $login = self::login(UserFactory::createUser());
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'title' => Utils::CreateRandomString(),
        ]);

        // Call API
        ContestController::apiUpdate($r);
    }

    /**
     * Update from private to public. Should fail if no problems in contest
     *
     * @expectedException InvalidParameterException
     */
    public function testUpdatePrivateContestToPublicWithoutProblems() {
        // Get a contest
        $contestData = $this->contestFactoryPrivate->createContest();

        // Update public
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'public' => 1,
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);
    }

    /**
     * Update from private to public with problems added
     *
     */
    public function testUpdatePrivateContestToPublicWithProblems() {
        // Get a contest
        $contestData = $this->contestFactoryPrivate->createContest();

        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Update public
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'public' => 1,
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);

        $contestData['request']['public'] = $r['public'];
        $this->assertContest($contestData['request']);
    }

     /**
      * Set Recommended flag to a given contest
      */
    public function testSetRecommendedFlag() {
        // Get a contest
        $contestData = $this->contestFactory->createContest();

        // Update value
        $login = self::login(UserFactory::createAdminUser());
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'value' => 1,
        ]);

        // Call API
        ContestController::apiSetRecommended($r);

        // Verify setting
        $contestData['request']['recommended'] = $r['value'];
        $this->assertContest($contestData['request']);

        // Turn flag down
        $r['value'] = 0;

        // Call API again
        ContestController::apiSetRecommended($r);

        // Verify setting
        $contestData['request']['recommended'] = $r['value'];
        $this->assertContest($contestData['request']);
    }

     /**
      * Set Recommended flag to a given contest from non admin account
      *
      * @expectedException ForbiddenAccessException
      */
    public function testSetRecommendedFlagNonAdmin() {
        // Get a contest
        $contestData = $this->contestFactory->createContest();

        // Update value
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'value' => 1,
        ]);

        // Call API
        ContestController::apiSetRecommended($r);
    }

    /**
     * Contest length can't be too long
     *
     * @expectedException InvalidParameterException
     */
    public function testUpdateContestLengthTooLong() {
        // Get a contest
        $contestData = $this->contestFactory->createContest();

        // Update length
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'start_time' => 0,
            'finish_time' => 60 * 60 * 24 * 32,
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);
    }

    /**
     * Submit a run into a contest helper
     *
     */
    private function createRunInContest($contestData) {
        // STEP 1: Create a problem and add it to the contest
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // STEP 2: Get contestant ready to create a run
        // Create our contestant
        $contestant = UserFactory::createUser();

        // Our contestant has to open the contest before sending a run
        ContestsFactory::openContest($contestData, $contestant);

        // Then we need to open the problem
        ContestsFactory::openProblemInContest($contestData, $problemData, $contestant);

        // STEP 3: Send a new run
        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['alias'],
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        RunController::apiCreate($r);
    }

    /**
     * Contest start can't be updated if already contains runs
     *
     * @expectedException InvalidParameterException
     */
    public function testUpdateContestStartWithRuns() {
        // Get a contest
        $contestData = $this->contestFactory->createContest();

        // Submit a run
        $this->createRunInContest($contestData);

        // Update length
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'start_time' => $contestData['request']['start_time'] + 1,
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);
    }

    /**
     * Contest length can be updated if no runs
     *
     */
    public function testUpdateContestStartNoRuns() {
        // Get a contest
        $contestData = $this->contestFactory->createContest();

        // Update length
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'start_time' => $contestData['request']['start_time'] + 1,
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);

        // Check contest data from DB
        $contestData['request']['start_time'] = $r['start_time'];
        $this->assertContest($contestData['request']);
    }

    /**
     * Contest title can be updated if already contains runs and start time does not change
     *
     */
    public function testUpdateContestTitleWithRuns() {
        // Get a contest
        $contestData = $this->contestFactory->createContest();

        // Submit a run
        $this->createRunInContest($contestData);

        // Update title
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'start_time' => $contestData['request']['start_time'],
            'title' => 'New title',
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);

        // Check contest data from DB
        $contestData['request']['start_time'] = $r['start_time'];
        $contestData['request']['title'] = $r['title'];
        $this->assertContest($contestData['request']);
    }
}
