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
            'contest' => $contestData['contest'],
            'start_time' => Time::get()
        ]));

        $this->assertEquals($contestAlias, $contestClonedData['alias']);

        // Create request
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
            'contest' => $contestData['contest'],
            'start_time' => Time::get()
        ]));
    }

    /**
     * Creating a clone of a private contest without its access
     *
     * @expectedException ForbiddenAccessException
     */
    public function testCreatePrivateContestCloneWithoutAccess() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create new user
        $user = UserFactory::createUser();
        $login = UserController::apiLogin(new Request([
            'usernameOrEmail' => $user->username,
            'password' => $user->password
        ]));

        // Clone the contest
        $contestClonedData = ContestController::apiClone(new Request([
            'auth_token' => $login['auth_token'],
            'contest_alias' => $contestData['request']['alias'],
            'title' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'contest' => $contestData['contest'],
            'start_time' => Time::get()
        ]));
    }
}
