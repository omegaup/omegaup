<?php

class ContestCloneTest extends OmegaupTestCase {
    /**
     * Create clone of a contest
     */
    public function testCreateContestClone() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        $contestAlias = Utils::CreateRandomString();

        // Clone the contest
        $login = self::login($contestData['director']);
        $contestClonedData = ContestController::apiClone(new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'title' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'alias' => $contestAlias,
            'start_time' => Time::get()
        ]));

        $this->assertEquals($contestAlias, $contestClonedData['alias']);

        // Create request
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestAlias,
        ]);

        // Call API
        $response = ContestController::apiProblems($r);

        foreach ($response['problems'] as $problem) {
            $this->assertEquals($problemData['request']['problem_alias'], $problem['alias']);
        }
    }

    /**
     * Creating a clone with the original contest alias
     *
     * @expectedException DuplicatedEntryInDatabaseException
    */
    public function testCreateContestCloneWithTheSameAlias() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        $contestAlias = Utils::CreateRandomString();

        // Clone the contest
        $login = self::login($contestData['director']);
        $contestClonedData = ContestController::apiClone(new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'title' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'alias' => $contestData['request']['alias'],
            'start_time' => Time::get()
        ]));
    }
}
