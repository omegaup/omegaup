<?php

/**
 * Tests of the ContestController::apiRemoveProblem
 *
 * @author edhzsz
 */

class ContestRemoveProblemTest extends OmegaupTestCase {
    /**
     * Check in DB that a problem does not exist on a contest
     *
     * @param array $problemData
     * @param array $contestData
     * @param Request $r
     */
    public static function assertProblemRemovedFromContest($problemData, $contestData) {
        // Get problem and contest from DB
        $problem = ProblemsDAO::getByAlias($problemData['request']['alias']);
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);

        // Get problem-contest and verify it does not exist
        $contest_problems = ContestProblemsDAO::getByPK($contest->contest_id, $problem->problem_id);

        self::assertNull($contest_problems);
    }

    /**
     * Check in DB for problem added to contest
     *
     * @param array $problemData
     * @param array $contestData
     * @param Request $r
     */
    public static function assertProblemExistsInContest($problemData, $contestData) {
        // Get problem and contest from DB
        $problem = ProblemsDAO::getByAlias($problemData['request']['alias']);
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);

        // Get problem-contest and verify it
        $contest_problems = ContestProblemsDAO::getByPK($contest->contest_id, $problem->problem_id);

        self::assertNotNull($contest_problems);
    }

    /**
     * Removes a problem from a private contest.
     * Should not fail and problem should have been removed.
     */
    public function testRemoveProblemFromPrivateContest() {
        // Get a contest
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        // Get two problema
        $problemData = ProblemsFactory::createProblem();

        // Add the problems to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create an empty request
        $r = new Request();

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Build request
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData['request']['alias'];

        // Call API
        $response = ContestController::apiRemoveProblem($r);

        // Validate
        $this->assertEquals('ok', $response['status']);

        $this->assertProblemRemovedFromContest($problemData, $contestData);
    }

    /**
     * Removes a problem from a private contest.
     *
     * @expectedException InvalidParameterException
     */
    public function testRemoveInvalidProblemFromPrivateContest() {
        // Get a contest
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        // Get two problema
        $problemData = ProblemsFactory::createProblem();

        // Add the problems to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create an empty request
        $r = new Request();

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Build request
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = 'this problem doesnt exists';

        // Call API
        $response = ContestController::apiRemoveProblem($r);
    }

    /**
     * Removes the oldest problem from a contest made public with two problems.
     * Should not fail and contest should have a single problem.
     */
    public function testRemoveOldestProblemFromPublicContestWithTwoProblems() {
        // Get a contest
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        // Get two problema
        $problemData1 = ProblemsFactory::createProblem();
        $problemData2 = ProblemsFactory::createProblem();

        // Add the problems to the contest
        ContestsFactory::addProblemToContest($problemData1, $contestData);
        ContestsFactory::addProblemToContest($problemData2, $contestData);

        // Create an empty request
        $r = new Request();

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Build request
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData1['request']['alias'];

        // Call API
        $response = ContestController::apiRemoveProblem($r);

        // Validate
        $this->assertEquals('ok', $response['status']);

        $this->assertProblemRemovedFromContest($problemData1, $contestData);
        $this->assertProblemExistsInContest($problemData2, $contestData);
    }

    /**
     * Removes the newest problem from a contest made public with two problems.
     * Should not fail and contest should have a single problem.
     */
    public function testRemoveNewestProblemFromPublicContestWithTwoProblems() {
        // Get a contest
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        // Get two problema
        $problemData1 = ProblemsFactory::createProblem();
        $problemData2 = ProblemsFactory::createProblem();

        // Add the problems to the contest
        ContestsFactory::addProblemToContest($problemData1, $contestData);
        ContestsFactory::addProblemToContest($problemData2, $contestData);

        // Create an empty request
        $r = new Request();

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Build request
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData2['request']['alias'];

        // Call API
        $response = ContestController::apiRemoveProblem($r);

        // Validate
        $this->assertEquals('ok', $response['status']);

        $this->assertProblemRemovedFromContest($problemData2, $contestData);
        $this->assertProblemExistsInContest($problemData1, $contestData);
    }

    /**
     * Remove a single problem from a public contest.
     *
     * @expectedException InvalidParameterException
     */
    public function testRemoveProblemsFromPublicContestWithASingleProblem() {
        // Get a contest
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        // Add a problem to the contest
        $problemData = ProblemsFactory::createProblem();

        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create an empty request
        $r = new Request();

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Build request
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData['request']['alias'];

        // Call API
        $response = ContestController::apiRemoveProblem($r);
    }

    /**
     * Removes all problems from a public contest.
     *
     * @expectedException InvalidParameterException
     */
    public function testRemoveAllProblemsFromPublicContestWithtwoProblems() {
        // Get a contest
        $contestData = ContestsFactory::createContest(null, 0 /* private */);

        // Add a problem to the contest
        $problemData1 = ProblemsFactory::createProblem();
        $problemData2 = ProblemsFactory::createProblem();

        ContestsFactory::addProblemToContest($problemData1, $contestData);
        ContestsFactory::addProblemToContest($problemData1, $contestData);

        // Create an empty request
        $r = new Request();

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Build request
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData1['request']['alias'];

        // Call API
        $response = ContestController::apiRemoveProblem($r);

        // Validate
        $this->assertEquals('ok', $response['status']);

        $r['problem_alias'] = $problemData2['request']['alias'];
        $response = ContestController::apiRemoveProblem($r);
    }

}