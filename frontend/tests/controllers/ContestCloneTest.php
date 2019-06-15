<?php

class ContestCloneTest extends OmegaupTestCase {
    /**
     * Create clone of a contest
     */
    public function testCreateContestClone() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add 3 problems to the contest
        $numberOfProblems = 3;

        for ($i = 0; $i < $numberOfProblems; $i++) {
            $problemData[$i] = ProblemsFactory::createProblem();
            ContestsFactory::addProblemToContest($problemData[$i], $contestData);
        }

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

        // Call API
        $clonedContestProblemsResponse = ContestController::apiProblems(new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestAlias,
        ]));

        $originalContestProblemsResponse = ContestController::apiProblems(new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        $this->assertEquals(
            $originalContestProblemsResponse['problems'],
            $clonedContestProblemsResponse['problems'],
            'Problems of the cloned contest does not match with problems of the original contest'
        );
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
