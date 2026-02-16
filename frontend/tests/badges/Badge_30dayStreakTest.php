<?php

/**
 * Simple test for 30dayStreak Badge
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_30dayStreakTest extends \OmegaUp\Test\BadgesTestCase {
    public function test7daysStreak(): void {
        // Create problems and runs for each user
        // The submissions are created in a loop to simulate the 30 day streak
        // The first user will have 30 problems in a row and the second one 25
        // The first user will get the badge and the second one won't
        $usersMapping = [
            [
                'username' => 'user1',
                'problemsCount' => 30,
            ],
            [
                'username' => 'user2',
                'problemsCount' => 25,
            ],
        ];

        $originalTimestamp = \OmegaUp\Time::get();
        $identities = [];
        foreach ($usersMapping as $user) {
            [
                'identity' => $identities[$user['username']],
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams([
                    'username' => $user['username'],
                ])
            );

            $problems = [];
            foreach (range(0, $user['problemsCount'] - 1) as $i) {
                \OmegaUp\Time::setTimeForTesting(
                    \OmegaUp\Time::get() - (60 * 60 * 24)
                );
                $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problems[$i],
                    $identities[$user['username']]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }
        }
        \OmegaUp\Time::setTimeForTesting($originalTimestamp);

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/30dayStreak/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = array_map(
            fn($user) => $identities[$user['username']]->user_id,
            array_filter(
                $usersMapping,
                fn($user) => $user['problemsCount'] >= 30
            )
        );

        $this->assertSame($expected, $results);
    }
}
