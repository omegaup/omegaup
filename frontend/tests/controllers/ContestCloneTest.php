<?php

class ContestCloneTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Create clone of a contest
     */
    public function testCreateContestClone() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add 3 problems to the contest
        $numberOfProblems = 3;

        for ($i = 0; $i < $numberOfProblems; $i++) {
            $problemData[$i] = \OmegaUp\Test\Factories\Problem::createProblem();
            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problemData[$i],
                $contestData
            );
        }

        $contestAlias = \OmegaUp\Test\Utils::createRandomString();

        // Clone the contest
        $login = self::login($contestData['director']);
        $contestClonedData = \OmegaUp\Controllers\Contest::apiClone(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'title' => \OmegaUp\Test\Utils::createRandomString(),
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $contestAlias,
            'contest' => $contestData['contest'],
            'start_time' => \OmegaUp\Time::get()
        ]));

        $this->assertEquals($contestAlias, $contestClonedData['alias']);

        // Call API
        $clonedContestProblemsResponse = \OmegaUp\Controllers\Contest::apiProblems(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestAlias,
        ]));

        $originalContestProblemsResponse = \OmegaUp\Controllers\Contest::apiProblems(new \OmegaUp\Request([
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
     */
    public function testCreateContestCloneWithTheSameAlias() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        $contestAlias = \OmegaUp\Test\Utils::createRandomString();

        // Clone the contest
        $login = self::login($contestData['director']);
        try {
            \OmegaUp\Controllers\Contest::apiClone(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'title' => \OmegaUp\Test\Utils::createRandomString(),
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $contestData['request']['alias'],
                'contest' => $contestData['contest'],
                'start_time' => \OmegaUp\Time::get()
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('titleInUse', $e->getMessage());
        }
    }

    /**
     * Creating a clone of a private contest without its access
     */
    public function testCreatePrivateContestCloneWithoutAccess() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create new user
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = \OmegaUp\Controllers\User::apiLogin(new \OmegaUp\Request([
            'usernameOrEmail' => $identity->username,
            'password' => $identity->password
        ]));

        // Clone the contest
        try {
            \OmegaUp\Controllers\Contest::apiClone(new \OmegaUp\Request([
                'auth_token' => $login['auth_token'],
                'contest_alias' => $contestData['request']['alias'],
                'title' => \OmegaUp\Test\Utils::createRandomString(),
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => \OmegaUp\Test\Utils::createRandomString(),
                'contest' => $contestData['contest'],
                'start_time' => \OmegaUp\Time::get()
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
