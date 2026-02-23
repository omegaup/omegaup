<?php

/**
 * Simple test for medium Solver Badge
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
                $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
                // Set the problem's difficulty manually
                $problem = \OmegaUp\DAO\Problems::getByPK(
                    $problemData['problem']->problem_id
                );
                $problem->difficulty = 2.5; // Set it to difficult
                \OmegaUp\DAO\Problems::update($problem);

                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $identities['mediumUser']
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }
        }

        \OmegaUp\Time::setTimeForTesting($originalTimestamp);

        \OmegaUp\Test\Utils::runAggregateFeedback();

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/mediumSolver/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $this->assertSame([$identities['mediumUser']->user_id], $results);
    }
}
