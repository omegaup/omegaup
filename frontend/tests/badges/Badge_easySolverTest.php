<?php

/**
 * Simple test for Easy Solver Badge
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
                $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
                // Set the problem's difficulty manually
                $problem = \OmegaUp\DAO\Problems::getByPK(
                    $problemData['problem']->problem_id
                );
                $problem->difficulty = 1.0; // Set it to easy
                \OmegaUp\DAO\Problems::update($problem);

                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $identities['easyUser']
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }
        }

        \OmegaUp\Time::setTimeForTesting($originalTimestamp);

        \OmegaUp\Test\Utils::runAggregateFeedback();

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/easySolver/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $this->assertSame([$identities['easyUser']->user_id], $results);
    }
}
