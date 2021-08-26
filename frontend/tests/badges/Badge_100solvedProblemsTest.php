<?php

/**
 * Simple test for 100solvedProblems Badge
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_100solvedProblemsTest extends \OmegaUp\Test\BadgesTestCase {
    public function test100SolvedProblems(): void {
        // Creates two users, one solves 99 problems the other 101.
        ['identity' => $identity99] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $user101, 'identity' => $identity101] = \OmegaUp\Test\Factories\User::createUser();
        $problems = [];
        for ($i = 0; $i < 101; $i++) {
            $newProblem = \OmegaUp\Test\Factories\Problem::createProblem();
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $newProblem,
                $identity101
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run);
            $problems[] = $newProblem;
        }
        for ($i = 0; $i < 99; $i++) {
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problems[$i],
                $identity99
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run);
        }
        $queryPath = static::OMEGAUP_BADGES_ROOT . '/100solvedProblems/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [$user101->user_id];
        $this->assertEquals($expected, $results);
    }

    public function test100RunsToSameProblem(): void {
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        for ($i = 0; $i < 101; $i++) {
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problem,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run);
        }
        $queryPath = static::OMEGAUP_BADGES_ROOT . '/100solvedProblems/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [];
        $this->assertEquals($expected, $results);
    }
}
