<?php

/**
 * VirtualContestTest
 */

class VirtualContestTest extends \OmegaUp\Test\ControllerTestCase {
    public function testCreateVirtualContest() {
        // create a real contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Add problem to contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Let assume the original contest has been finished
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 3600);

        // Create a new contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        $response = \OmegaUp\Controllers\Contest::apiCreateVirtual(
            new \OmegaUp\Request([
                'alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        // Get generated virtual contest alias
        $virtualContestAlias = $response['alias'];

        $virtualContest = \OmegaUp\DAO\Contests::getByAlias(
            $virtualContestAlias
        );

        $originalContest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $this->assertNull($originalContest->rerun_id);

        // Assert virtual contest
        $this->assertSame(
            $originalContest->contest_id,
            $virtualContest->rerun_id
        );
        $this->assertSame($originalContest->title, $virtualContest->title);
        $this->assertSame(
            $originalContest->description,
            $virtualContest->description
        );
        $this->assertSame('private', $virtualContest->admission_mode); // Virtual contest must be private
        $this->assertSame(
            $originalContest->scoreboard,
            $virtualContest->scoreboard
        );
        $this->assertSame(
            $originalContest->points_decay_factor,
            $virtualContest->points_decay_factor
        );
        $this->assertSame(
            $originalContest->partial_score,
            $virtualContest->partial_score
        );
        $this->assertSame(
            $originalContest->submissions_gap,
            $virtualContest->submissions_gap
        );
        $this->assertSame(
            $originalContest->feedback,
            $virtualContest->feedback
        );
        $this->assertSame(
            $originalContest->penalty,
            $virtualContest->penalty
        );
        $this->assertSame(
            $originalContest->penalty_type,
            $virtualContest->penalty_type
        );
        $this->assertSame(
            $originalContest->penalty_calc_policy,
            $virtualContest->penalty_calc_policy
        );
        $this->assertSame(
            $originalContest->languages,
            $virtualContest->languages
        );

        // Assert virtual contest problemset problems
        $originalProblems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $originalContest->problemset_id,
            needSubmissions: false
        );
        $virtualProblems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $virtualContest->problemset_id,
            needSubmissions: false
        );
        // Number of problems must be equal
        $this->assertSame(count($originalProblems), count($virtualProblems));

        // Because we only put one problem in contest we can assert only the first element
        $this->assertSame($originalProblems[0], $virtualProblems[0]);

        \OmegaUp\Test\Factories\Contest::openContest(
            $virtualContest,
            $identity
        );

        $login = self::login($identity);

        $result = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'contest_alias' => $virtualContest->alias,
                'auth_token' => $login->auth_token,
            ])
        );

        $response = $result['templateProperties']['payload'];

        // Virtual contests have information of the original contest
        $this->assertArrayHasKey('original', $response);

        $this->assertArrayHasKey('contest', $response['original']);
        $this->assertArrayHasKey('scoreboard', $response['original']);
        $this->assertArrayHasKey('scoreboardEvents', $response['original']);

        $this->assertSame(
            $originalContest->alias,
            $response['original']['contest']->alias
        );
        $this->assertSame('arena_contest_virtual', $result['entrypoint']);
    }

    public function testCreateVirtualContestBeforeTheOriginalEnded() {
        // Create a real contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create a new contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() - 100);

        try {
            \OmegaUp\Controllers\Contest::apiCreateVirtual($r);
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'originalContestHasNotEnded');
        }
    }

    public function testCreateVirtualContestWithInvalidAlias() {
        // Create a new contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'alias' => 'wrong alias',
            'auth_token' => $login->auth_token
        ]);

        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() - 100);

        try {
            \OmegaUp\Controllers\Contest::apiCreateVirtual($r);
            $this->fail('Should have thrown a InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame($e->getMessage(), 'parameterInvalid');
        }
    }

    public function testVirtualContestRestrictedApiAddProblem() {
        // Create a real contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create a new contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Lets assume the original contest has been finished
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 3600);

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        $response = \OmegaUp\Controllers\Contest::apiCreateVirtual($r);
        $virtualContestAlias = $response['alias'];

        try {
            \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
                'contest_alias' => $virtualContestAlias,
                'problem_alias' => $problemData['problem']->alias,
                'points' => 100,
                'auth_token' => $login->auth_token
            ]));
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'forbiddenInVirtualContest');
        }
    }

    public function testVirtualContestRestrictedApiRemoveProblem() {
        // Create a real contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Add problem to original contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create a new contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Lets assume the original contest has been finished
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 3600);

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        $response = \OmegaUp\Controllers\Contest::apiCreateVirtual($r);
        $virtualContestAlias = $response['alias'];

        try {
            \OmegaUp\Controllers\Contest::apiRemoveProblem(new \OmegaUp\Request([
                'contest_alias' => $virtualContestAlias,
                'problem_alias' => $problemData['problem']->alias,
                'auth_token' => $login->auth_token
            ]));
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'forbiddenInVirtualContest');
        }
    }

    public function testVirtualContestRestrictedApiUpdate() {
        // Create a real contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create a new contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Lets assume the original contest has been finished
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 3600);

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token
        ]);

        $response = \OmegaUp\Controllers\Contest::apiCreateVirtual($r);
        $virtualContestAlias = $response['alias'];

        try {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'contest_alias' => $virtualContestAlias,
                'title' => 'testtest',
                'auth_token' => $login->auth_token,
                'languages' => 'c11-gcc',
            ]));
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'forbiddenInVirtualContest');
        }
    }
}
