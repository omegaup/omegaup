<?php
/**
 * Tests for the problem recommendations feed.
 */
class ProblemRecommendationsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * @param array{problem: \OmegaUp\DAO\VO\Problems} $solvedProblemData
     * @param array{problem: \OmegaUp\DAO\VO\Problems} $recommendedProblemData
     */
    private static function addRecommendation(
        array $solvedProblemData,
        array $recommendedProblemData,
        float $score
    ): void {
        \OmegaUp\DAO\ProblemRecommendations::replace(
            new \OmegaUp\DAO\VO\ProblemRecommendations([
                'solved_problem_id' => $solvedProblemData['problem']->problem_id,
                'recommended_problem_id' => $recommendedProblemData['problem']->problem_id,
                'score' => $score,
            ])
        );
    }

    public function testEmptyFeedWhenNoModelHasBeenPublished() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiRecommendations(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertEmpty($response['problems']);
    }

    public function testRecommendationsDerivedFromSolvedProblems() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $solvedProblemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $bestProblemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $otherProblemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $solvedProblemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        self::addRecommendation($solvedProblemData, $bestProblemData, 0.9);
        self::addRecommendation($solvedProblemData, $otherProblemData, 0.4);

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiRecommendations(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertCount(2, $response['problems']);
        $this->assertSame(
            $bestProblemData['problem']->alias,
            $response['problems'][0]['alias']
        );
        $this->assertSame(0.9, $response['problems'][0]['score']);
        $this->assertSame(
            $solvedProblemData['problem']->alias,
            $response['problems'][0]['solved_problem_alias']
        );
        $this->assertSame(
            $otherProblemData['problem']->alias,
            $response['problems'][1]['alias']
        );
    }

    public function testAlreadySolvedProblemsAreExcluded() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $solvedProblemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $alsoSolvedProblemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $unsolvedProblemData = \OmegaUp\Test\Factories\Problem::createProblem();

        foreach ([$solvedProblemData, $alsoSolvedProblemData] as $problemData) {
            $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problemData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData);
        }

        self::addRecommendation(
            $solvedProblemData,
            $alsoSolvedProblemData,
            0.9
        );
        self::addRecommendation($solvedProblemData, $unsolvedProblemData, 0.4);

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiRecommendations(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertCount(1, $response['problems']);
        $this->assertSame(
            $unsolvedProblemData['problem']->alias,
            $response['problems'][0]['alias']
        );
    }

    public function testPrivateProblemsAreExcluded() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $solvedProblemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $privateProblemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'private',
            ])
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $solvedProblemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        self::addRecommendation($solvedProblemData, $privateProblemData, 0.9);

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiRecommendations(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertEmpty($response['problems']);
    }

    public function testDuplicateRecommendationKeepsHighestScore() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $firstSolvedProblemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $secondSolvedProblemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $recommendedProblemData = \OmegaUp\Test\Factories\Problem::createProblem();

        foreach (
            [$firstSolvedProblemData, $secondSolvedProblemData] as $problemData
        ) {
            $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problemData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData);
        }

        self::addRecommendation(
            $firstSolvedProblemData,
            $recommendedProblemData,
            0.4
        );
        self::addRecommendation(
            $secondSolvedProblemData,
            $recommendedProblemData,
            0.9
        );

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiRecommendations(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertCount(1, $response['problems']);
        $this->assertSame(
            $recommendedProblemData['problem']->alias,
            $response['problems'][0]['alias']
        );
        $this->assertSame(0.9, $response['problems'][0]['score']);
        $this->assertSame(
            $secondSolvedProblemData['problem']->alias,
            $response['problems'][0]['solved_problem_alias']
        );
    }

    public function testRecommendationsRequireLogin() {
        try {
            \OmegaUp\Controllers\Problem::apiRecommendations(
                new \OmegaUp\Request([])
            );
            $this->fail('Should have thrown an UnauthorizedException');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame('loginRequired', $e->getMessage());
        }
    }
}
