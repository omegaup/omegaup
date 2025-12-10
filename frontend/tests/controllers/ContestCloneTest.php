<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

class ContestCloneTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * A PHPUnit data provider for the test with different valid format dates.
     *
     * @return list<list<int|float>>
     */
    public function dateValueProvider(): array {
        return [
            [\OmegaUp\Time::get()],
            [\OmegaUp\Time::get() + 0.047],
        ];
    }

    /**
     * @dataProvider dateValueProvider
     * Create clone of a contest
     * @param int|float $date
     */
    public function testCreateContestClone($date) {
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
        $contestClonedData = \OmegaUp\Controllers\Contest::apiClone(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'title' => \OmegaUp\Test\Utils::createRandomString(),
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $contestAlias,
                'contest' => $contestData['contest'],
                'start_time' => $date,
            ])
        );

        $this->assertSame($contestAlias, $contestClonedData['alias']);

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
            $this->assertSame('aliasInUse', $e->getMessage());
        }
    }
    /**
     * Check if the plagiarism value is stored correctly in the database when
     * a contest is cloned
     */
    public function testPlagiarismThresholdValueInClonedContest() {
        // Create a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'checkPlagiarism' => true,
            ])
        );

        $clonedContestAlias = \OmegaUp\Test\Utils::createRandomString();

        // Login with director to clone the contest
        $login = self::login($contestData['director']);

        \OmegaUp\Controllers\Contest::apiClone(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'title' => $clonedContestAlias,
                'description' => $clonedContestAlias,
                'alias' => $clonedContestAlias,
                'start_time' => \OmegaUp\Time::get(),
            ])
        );

        $response = \OmegaUp\DAO\Contests::getByAlias($clonedContestAlias);

        $this->assertTrue(
            $response->check_plagiarism
        );
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
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /*
     * Under13 users can't clone contests.
     */
    public function testUserUnder13CannotCloneContests() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $defaultDate = strtotime('2022-01-01T00:00:00Z');
        \OmegaUp\Time::setTimeForTesting($defaultDate);
        // Create a 10 years-old user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'birthDate' => strtotime('2012-01-01T00:00:00Z'),
            ]),
        );

        // Log in the user and set the auth token in the new request
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Contest::apiClone(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['contest']->alias,
                'title' => \OmegaUp\Test\Utils::createRandomString(),
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => \OmegaUp\Time::get()
            ]));
            $this->fail(
                'Creating contests should not have been allowed for U13'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('U13CannotPerform', $e->getMessage());
        }
    }

    /**
     * Check if the languages of the cloned contest match with the languages of
     * the original contest
        */
    public function testLanguagesInClonedContest() {
        // Create a contest with specific languages allowed
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'languages' => ['c11-gcc','c11-clang','cpp11-gcc']
            ])
        );

        $clonedContestAlias = \OmegaUp\Test\Utils::createRandomString();

        // Login with director to clone the contest
        $login = self::login($contestData['director']);

        \OmegaUp\Controllers\Contest::apiClone(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'title' => $clonedContestAlias,
                'description' => $clonedContestAlias,
                'alias' => $clonedContestAlias,
                'start_time' => \OmegaUp\Time::get(),
            ])
        );

        // Get original contest languages
        $originalContestResponse = \OmegaUp\Controllers\Contest::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        );

        // Get cloned contest languages
        $clonedContestResponse = \OmegaUp\Controllers\Contest::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $clonedContestAlias,
            ])
        );

        // Assert that the languages match
        $this->assertEquals(
            $originalContestResponse['languages'],
            $clonedContestResponse['languages'],
            'Languages of the cloned contest do not match with languages of the original contest'
        );
    }
}
