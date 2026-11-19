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
        $this->assertSame($expected, $results);
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
        $this->assertSame($expected, $results);
    }

    public function testOwnProblemsAreNotCounted(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        [
            'user' => $user100,
            'identity' => $identity100,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $problems = [];
        for ($i = 0; $i < 99; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }
        $problems[] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
            $identity
        );
        foreach ($problems as $problem) {
            foreach ([$identity, $identity100] as $solver) {
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problem,
                    $solver
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }
        }
        $queryPath = static::OMEGAUP_BADGES_ROOT . '/100solvedProblems/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $this->assertSame([$user100->user_id], $results);
    }

    public function testForfeitedProblemsAreNotCounted(): void {
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        [
            'user' => $user100,
            'identity' => $identity100,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $problems = [];
        for ($i = 0; $i < 100; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }
        foreach ($problems as $problem) {
            foreach ([$identity, $identity100] as $solver) {
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problem,
                    $solver
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }
        }
        \OmegaUp\DAO\ProblemsForfeited::create(
            new \OmegaUp\DAO\VO\ProblemsForfeited([
                'user_id' => $user->user_id,
                'problem_id' => $problems[0]['problem']->problem_id,
            ])
        );
        $queryPath = static::OMEGAUP_BADGES_ROOT . '/100solvedProblems/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $this->assertSame([$user100->user_id], $results);
    }
}
