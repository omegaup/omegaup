<?php

/**
 * Simple test for 100solvedProblems Badge
 *
 * @author carlosabcs
 */
class OneHundredSolvedProblems extends \OmegaUp\Test\BadgesTestCase {
    public function test100SolvedProblems() {
        // Creates two users, one solves 99 problems the other 101.
        ['user' => $user99, 'identity' => $identity99] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $user101, 'identity' => $identity101] = \OmegaUp\Test\Factories\User::createUser();
        $problems = [];
        for ($i = 0; $i < 101; $i++) {
            $newProblem = \OmegaUp\Test\Factories\Problem::createProblem();
            $run = RunsFactory::createRunToProblem($newProblem, $identity101);
            RunsFactory::gradeRun($run);
            $problems[] = $newProblem;
        }
        for ($i = 0; $i < 99; $i++) {
            $run = RunsFactory::createRunToProblem($problems[$i], $identity99);
            RunsFactory::gradeRun($run);
        }
        $queryPath = static::OMEGAUP_BADGES_ROOT . '/100solvedProblems/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [$user101->user_id];
        $this->assertEquals($expected, $results);
    }

    public function test100RunsToSameProblem() {
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        for ($i = 0; $i < 101; $i++) {
            $run = RunsFactory::createRunToProblem($problem, $identity);
            RunsFactory::gradeRun($run);
        }
        $queryPath = static::OMEGAUP_BADGES_ROOT . '/100solvedProblems/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [];
        $this->assertEquals($expected, $results);
    }
}
