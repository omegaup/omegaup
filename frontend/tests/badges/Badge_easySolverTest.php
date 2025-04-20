<?php

/**
 * Simple test for easySolver Badge
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_EasySolverTest extends \OmegaUp\Test\BadgesTestCase {
    public function testEasySolverStreak(): void {
        $originalTimestamp = \OmegaUp\Time::get();
        $identities = [];

        [
            'identity' => $identities['easyUser'],
        ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['username' => 'easyUser'])
        );

        foreach (range(0, 6) as $day) {
            \OmegaUp\Time::setTimeForTesting(
                $originalTimestamp - (60 * 60 * 24 * (6 - $day))
            );
            foreach (range(0, 1) as $_) {
                $problem = \OmegaUp\Test\Factories\Problem::createProblem(
                    new \OmegaUp\Test\Factories\ProblemParams(['difficulty' => 1.0])
                );
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problem,
                    $identities['easyUser']
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }
        }

        \OmegaUp\Time::setTimeForTesting($originalTimestamp);

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/easySolver/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $this->assertSame([$identities['easyUser']->user_id], $results);
    }
}
