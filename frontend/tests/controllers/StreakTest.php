<?php

/**
 * Tests for user streak calculation.
 */
class StreakTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test streak is 0 for a user with no submissions.
     */
    public function testZeroStreak(): void {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $streak = \OmegaUp\DAO\Runs::getCurrentStreak($user->user_id);
        $this->assertSame(0, $streak);

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Session::apiCurrentSession(
            new \OmegaUp\Request(['auth_token' => $login->auth_token])
        );
        $this->assertSame(0, $response['session']['current_streak']);
    }

    /**
     * Test a consecutive 3-day streak ending today.
     */
    public function testThreeDayStreak(): void {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $baseTime = strtotime('2026-05-15 12:00:00');
        $originalTime = \OmegaUp\Time::get();
        try {
            // Day -2, Day -1, Day 0 (today)
            for ($daysAgo = 2; $daysAgo >= 0; $daysAgo--) {
                \OmegaUp\Time::setTimeForTesting($baseTime - ($daysAgo * 86400));
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $identity
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }

            \OmegaUp\Time::setTimeForTesting($baseTime);
            $streak = \OmegaUp\DAO\Runs::getCurrentStreak($user->user_id);
            $this->assertSame(3, $streak);

            $login = self::login($identity);
            $response = \OmegaUp\Controllers\Session::apiCurrentSession(
                new \OmegaUp\Request(['auth_token' => $login->auth_token])
            );
            $this->assertSame(3, $response['session']['current_streak']);
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }
    }

    /**
     * Test a gap in submissions resets the streak.
     */
    public function testGapResetsStreak(): void {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $baseTime = strtotime('2026-05-15 12:00:00');
        $originalTime = \OmegaUp\Time::get();
        try {
            // Submitted 3 days ago and 2 days ago, but missed yesterday (day -1)
            foreach ([3, 2] as $daysAgo) {
                \OmegaUp\Time::setTimeForTesting($baseTime - ($daysAgo * 86400));
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $identity
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }

            // At baseTime (today), without submitting today: gap yesterday resets streak to 0
            \OmegaUp\Time::setTimeForTesting($baseTime);
            $streak = \OmegaUp\DAO\Runs::getCurrentStreak($user->user_id);
            $this->assertSame(0, $streak);

            // Now submit today: streak resets to 1 (new streak started today)
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problemData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run);

            $streak = \OmegaUp\DAO\Runs::getCurrentStreak($user->user_id);
            $this->assertSame(1, $streak);
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }
    }

    /**
     * Test submitted yesterday but not yet today keeps streak alive.
     */
    public function testSubmittedYesterdayButNotToday(): void {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $baseTime = strtotime('2026-05-15 12:00:00');
        $originalTime = \OmegaUp\Time::get();
        try {
            // Submitted 2 days ago and yesterday (day -1)
            foreach ([2, 1] as $daysAgo) {
                \OmegaUp\Time::setTimeForTesting($baseTime - ($daysAgo * 86400));
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $identity
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }

            // Today is baseTime, no submission today yet
            \OmegaUp\Time::setTimeForTesting($baseTime);
            $streak = \OmegaUp\DAO\Runs::getCurrentStreak($user->user_id);
            $this->assertSame(2, $streak);
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }
    }

    /**
     * Test 30+ boundary (30 and 31 consecutive days).
     */
    public function testThirtyPlusBoundary(): void {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $baseTime = strtotime('2026-05-15 12:00:00');
        $originalTime = \OmegaUp\Time::get();
        try {
            // 30 consecutive days from day -29 to day 0
            for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
                \OmegaUp\Time::setTimeForTesting($baseTime - ($daysAgo * 86400));
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $identity
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }

            \OmegaUp\Time::setTimeForTesting($baseTime);
            $streak = \OmegaUp\DAO\Runs::getCurrentStreak($user->user_id);
            $this->assertSame(30, $streak);

            // Add day -30 for a 31-day streak
            \OmegaUp\Time::setTimeForTesting($baseTime - (30 * 86400));
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problemData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run);

            \OmegaUp\Time::setTimeForTesting($baseTime);
            $streak = \OmegaUp\DAO\Runs::getCurrentStreak($user->user_id);
            $this->assertSame(31, $streak);
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }
    }
}
