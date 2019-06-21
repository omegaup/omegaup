<?php

/**
 * Simple test for 100solvedProblems Badge
 *
 * @author carlosabcs
 */
class oneHundredSolvedProblems extends BadgesTestCase {
    public function test100SolvedProblems() {
        // Creates two users, one solves 99 problems the other 101.
        $user99 = UserFactory::createUser();
        $user101 = UserFactory::createUser();
        $n = 99;
        while ($n--) {
            $problem = ProblemsFactory::createProblem();
            $run = RunsFactory::createRunToProblem($problem, $user99);
            RunsFactory::gradeRun($run);
            $run = RunsFactory::createRunToProblem($problem, $user101);
            RunsFactory::gradeRun($run);
        }
        $n = 2;
        while ($n--) {
            $problem = ProblemsFactory::createProblem();
            $run = RunsFactory::createRunToProblem($problem, $user101);
            RunsFactory::gradeRun($run);
        }
        $queryPath = static::OMEGAUP_BADGES_ROOT . '/100solvedProblems/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [$user101->user_id];
        $this->assertEquals($expected, $results);
    }
}
