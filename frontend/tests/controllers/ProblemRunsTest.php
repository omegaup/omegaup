<?php

/**
 * Tests getting runs of a problem.
 */
class ProblemRunsTest extends OmegaupTestCase {
    /**
     * Contestant submits runs and admin is able to get them.
     */
    public function testGetRunsForProblem() {
        $problemData = ProblemsFactory::createProblem();
        $contestants = [];
        $runs = [];
        for ($i = 0; $i < 2; ++$i) {
            ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
            $runData = RunsFactory::createRunToProblem($problemData, $identity);
            RunsFactory::gradeRun($runData);
            $contestants[] = $identity;
            $runs[] = $runData;
        }

        // Regular users cannot use "show_all".
        try {
            $login = self::login($contestants[0]);
            \OmegaUp\Controllers\Problem::apiRuns(new \OmegaUp\Request([
                'problem_alias' => $problemData['problem']->alias,
                'auth_token' => $login->auth_token,
                'show_all' => true,
            ]));
            $this->fail('Should not have been able to call this API');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // OK.
        }

        // Each user can only see their own runs.
        for ($i = 0; $i < count($contestants); ++$i) {
            $login = self::login($contestants[$i]);
            $response = \OmegaUp\Controllers\Problem::apiRuns(new \OmegaUp\Request([
                'problem_alias' => $problemData['problem']->alias,
                'auth_token' => $login->auth_token,
            ]));
            $this->assertCount(1, $response['runs']);
            $this->assertEquals(
                $runs[$i]['response']['guid'],
                $response['runs'][0]['guid']
            );
        }

        // Admins can also see each contestants' runs.
        $login = self::login($problemData['author']);
        for ($i = 0; $i < count($contestants); ++$i) {
            $response = \OmegaUp\Controllers\Problem::apiRuns(new \OmegaUp\Request([
                'problem_alias' => $problemData['problem']->alias,
                'auth_token' => $login->auth_token,
                'show_all' => true,
                'username' => $contestants[$i]->username,
            ]));
            $this->assertCount(1, $response['runs']);
            $this->assertEquals(
                $runs[$i]['response']['guid'],
                $response['runs'][0]['guid']
            );
        }

        // Admins can see all contestants' runs.
        $response = \OmegaUp\Controllers\Problem::apiRuns(new \OmegaUp\Request([
            'problem_alias' => $problemData['problem']->alias,
            'auth_token' => $login->auth_token,
            'show_all' => true,
        ]));
        $this->assertCount(2, $response['runs']);
        // Runs are sorted in reverse order.
        for ($i = 0; $i < count($contestants); ++$i) {
            $this->assertEquals(
                $runs[$i]['response']['guid'],
                $response['runs'][count($contestants) - $i - 1]['guid']
            );
        }
    }

    public function testUserHasTriedToSolvedProblem() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        // Never tried, never solved
        $this->assertFalse(\OmegaUp\DAO\Problems::hasTriedToSolveProblem(
            $problemData['problem'],
            $identity->identity_id
        ));
        // Tried, but didn't solve the problem
        $runData = RunsFactory::createRunToProblem($problemData, $identity);
        RunsFactory::gradeRun($runData, 0, 'WA', 60);
        $this->assertFalse(\OmegaUp\DAO\Problems::isProblemSolved(
            $problemData['problem'],
            $identity->identity_id
        ));
        $this->assertTrue(\OmegaUp\DAO\Problems::hasTriedToSolveProblem(
            $problemData['problem'],
            $identity->identity_id
        ));
        // Already tried and solved also
        $runData = RunsFactory::createRunToProblem($problemData, $identity);
        RunsFactory::gradeRun($runData);
        $this->assertTrue(\OmegaUp\DAO\Problems::isProblemSolved(
            $problemData['problem'],
            $identity->identity_id
        ));
        $this->assertTrue(\OmegaUp\DAO\Problems::hasTriedToSolveProblem(
            $problemData['problem'],
            $identity->identity_id
        ));
    }
}
