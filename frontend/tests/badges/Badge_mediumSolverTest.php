<?php

/**
 * Simple test for 7dayStreak Badge
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_MediumSolverTest extends \OmegaUp\Test\BadgesTestCase {
    public function testMediumSolverStreak(): void {
        $originalTimestamp = \OmegaUp\Time::get();
        $identities = [];

        [
            'identity' => $identities['mediumUser'],
        ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['username' => 'mediumUser'])
        );

        foreach (range(0, 6) as $day) {
            \OmegaUp\Time::setTimeForTesting(
                $originalTimestamp - (60 * 60 * 24 * (6 - $day))
            );
            foreach (range(0, 1) as $_) {
                $problem = \OmegaUp\Test\Factories\Problem::createProblem(
                    new \OmegaUp\Test\Factories\ProblemParams(['difficulty' => 2.0])
                );
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problem,
                    $identities['mediumUser']
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }
        }

        \OmegaUp\Time::setTimeForTesting($originalTimestamp);

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/mediumSolver/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $this->assertSame([$identities['mediumUser']->user_id], $results);
    }
}
