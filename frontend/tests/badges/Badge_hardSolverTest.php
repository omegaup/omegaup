<?php

/**
 * Simple test for Hard Solver Badge
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_HardSolverTest extends \OmegaUp\Test\BadgesTestCase {
    public function testHardSolverStreak(): void {
        $originalTimestamp = \OmegaUp\Time::get();
        $identities = [];

        [
            'identity' => $identities['hardUser'],
        ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['username' => 'hardUser'])
        );

        foreach (range(0, 6) as $day) {
            \OmegaUp\Time::setTimeForTesting(
                $originalTimestamp - (60 * 60 * 24 * (6 - $day))
            );
            foreach (range(0, 1) as $_) {
                $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
                // Set the problem's difficulty manually
                $problem = \OmegaUp\DAO\Problems::getByPK(
                    $problemData['problem']->problem_id
                );
                $problem->difficulty = 3.5; // Set it to difficult
                \OmegaUp\DAO\Problems::update($problem);

                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $identities['hardUser']
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }
        }

        \OmegaUp\Time::setTimeForTesting($originalTimestamp);

        \OmegaUp\Test\Utils::runAggregateFeedback();

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/hardSolver/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $this->assertSame([$identities['hardUser']->user_id], $results);
    }
}
