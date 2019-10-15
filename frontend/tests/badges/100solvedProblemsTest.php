<?php

/**
 * Simple test for 100solvedProblems Badge
 *
 * @author carlosabcs
 */
class OneHundredSolvedProblems extends BadgesTestCase {
    public function test100SolvedProblems() {
        // Creates two users, one solves 99 problems the other 101.
        $user99 = UserFactory::createUser();
        $user101 = UserFactory::createUser();
        $problems = [];
        for ($i = 0; $i < 101; $i++) {
            $newProblem = ProblemsFactory::createProblem();
            $run = RunsFactory::createRunToProblem($newProblem, $user101);
            RunsFactory::gradeRun($run);
            $problems[] = $newProblem;
        }
        for ($i = 0; $i < 99; $i++) {
            $run = RunsFactory::createRunToProblem($problems[$i], $user99);
            RunsFactory::gradeRun($run);
        }
        $queryPath = static::OMEGAUP_BADGES_ROOT . '/100solvedProblems/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [$user101->user_id];
        $this->assertEquals($expected, $results);
    }

    public function test100RunsToSameProblem() {
        $problem = ProblemsFactory::createProblem();
        $user = UserFactory::createUser();
        for ($i = 0; $i < 101; $i++) {
            $run = RunsFactory::createRunToProblem($problem, $user);
            RunsFactory::gradeRun($run);
        }
        $queryPath = static::OMEGAUP_BADGES_ROOT . '/100solvedProblems/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [];
        $this->assertEquals($expected, $results);
    }
}
