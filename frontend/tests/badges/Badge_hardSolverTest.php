<?php

/**
 * Simple test for hardSolver Badge
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
                $problem = \OmegaUp\Test\Factories\Problem::createProblem(
                    new \OmegaUp\Test\Factories\ProblemParams(['difficulty' => 3.5])
                );
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problem,
                    $identities['hardUser']
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }
        }

        \OmegaUp\Time::setTimeForTesting($originalTimestamp);

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/hardSolver/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $this->assertSame([$identities['hardUser']->user_id], $results);
    }
}
