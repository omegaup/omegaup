<?php

/**
 * Tests for user streak calculation.
 */
class StreakTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test streak is 0 for a user with no submissions.
     */
    public function testZeroStreak(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $streak = \OmegaUp\DAO\Submissions::getCurrentStreak(
            intval($identity->identity_id)
        );
        $this->assertSame(0, $streak);

        $userStreak = \OmegaUp\Controllers\User::getCurrentStreak($identity);
        $this->assertSame(0, $userStreak);

        $login = self::login($identity);
        \OmegaUp\Controllers\Session::invalidateCache();
        $commonPayload = \OmegaUp\UITools::getCommonPayload(
            false,
            '',
            new \OmegaUp\Request(['auth_token' => $login->auth_token])
        );
        $this->assertSame(0, $commonPayload['currentStreak']);
    }

    /**
     * Test a consecutive 3-day streak ending today.
     */
    public function testThreeDayStreak(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $baseTime = strtotime('2026-05-15 12:00:00');
        $originalTime = \OmegaUp\Time::get();
        try {
            // Day -2, Day -1, Day 0 (today)
            for ($daysAgo = 2; $daysAgo >= 0; $daysAgo--) {
                \OmegaUp\Time::setTimeForTesting(
                    $baseTime - ($daysAgo * 86400)
                );
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $identity
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }

            \OmegaUp\Time::setTimeForTesting($baseTime);
            $streak = \OmegaUp\DAO\Submissions::getCurrentStreak(
                intval($identity->identity_id)
            );
            $this->assertSame(3, $streak);

            $userStreak = \OmegaUp\Controllers\User::getCurrentStreak(
                $identity
            );
            $this->assertSame(3, $userStreak);

            $login = self::login($identity);
            \OmegaUp\Controllers\Session::invalidateCache();
            $commonPayload = \OmegaUp\UITools::getCommonPayload(
                false,
                '',
                new \OmegaUp\Request(['auth_token' => $login->auth_token])
            );
            $this->assertSame(3, $commonPayload['currentStreak']);
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }
    }

    /**
     * Test non-AC submissions (e.g. WA) do not count towards the streak.
     */
    public function testNonAcceptedSubmissionsDoNotCount(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $baseTime = strtotime('2026-05-15 12:00:00');
        $originalTime = \OmegaUp\Time::get();
        try {
            // Day -1 (yesterday) with WA verdict
            \OmegaUp\Time::setTimeForTesting($baseTime - 86400);
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problemData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run, 0, 'WA');

            // Today at baseTime
            \OmegaUp\Time::setTimeForTesting($baseTime);
            $streak = \OmegaUp\DAO\Submissions::getCurrentStreak(
                intval($identity->identity_id)
            );
            $this->assertSame(0, $streak);
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }
    }

    /**
     * Test a gap in submissions resets the streak.
     */
    public function testGapResetsStreak(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $baseTime = strtotime('2026-05-15 12:00:00');
        $originalTime = \OmegaUp\Time::get();
        try {
            // Submitted 3 days ago and 2 days ago, but missed yesterday (day -1)
            foreach ([3, 2] as $daysAgo) {
                \OmegaUp\Time::setTimeForTesting(
                    $baseTime - ($daysAgo * 86400)
                );
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $identity
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }

            // At baseTime (today), without submitting today: gap yesterday resets streak to 0
            \OmegaUp\Time::setTimeForTesting($baseTime);
            $streak = \OmegaUp\DAO\Submissions::getCurrentStreak(
                intval($identity->identity_id)
            );
            $this->assertSame(0, $streak);

            // Now submit today: streak resets to 1 (new streak started today)
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problemData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run);

            $streak = \OmegaUp\DAO\Submissions::getCurrentStreak(
                intval($identity->identity_id)
            );
            $this->assertSame(1, $streak);
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }
    }

    /**
     * Test submitted yesterday but not yet today keeps streak alive.
     */
    public function testSubmittedYesterdayButNotToday(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $baseTime = strtotime('2026-05-15 12:00:00');
        $originalTime = \OmegaUp\Time::get();
        try {
            // Submitted 2 days ago and yesterday (day -1)
            foreach ([2, 1] as $daysAgo) {
                \OmegaUp\Time::setTimeForTesting(
                    $baseTime - ($daysAgo * 86400)
                );
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $identity
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }

            // Today is baseTime, no submission today yet
            \OmegaUp\Time::setTimeForTesting($baseTime);
            $streak = \OmegaUp\DAO\Submissions::getCurrentStreak(
                intval($identity->identity_id)
            );
            $this->assertSame(2, $streak);
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }
    }

    /**
     * Test 30+ boundary (30 and 31 consecutive days).
     */
    public function testThirtyPlusBoundary(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $baseTime = strtotime('2026-05-15 12:00:00');
        $originalTime = \OmegaUp\Time::get();
        try {
            // 31 consecutive days: day -30 up to day 0 in chronological order
            for ($daysAgo = 30; $daysAgo >= 0; $daysAgo--) {
                \OmegaUp\Time::setTimeForTesting(
                    $baseTime - ($daysAgo * 86400)
                );
                $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $identity
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run);
            }

            // At yesterday (day -1), streak is 30
            \OmegaUp\Time::setTimeForTesting($baseTime - 86400);
            $streak = \OmegaUp\DAO\Submissions::getCurrentStreak(
                intval($identity->identity_id)
            );
            $this->assertSame(30, $streak);

            // At today (day 0), streak is 31
            \OmegaUp\Time::setTimeForTesting($baseTime);
            $streak = \OmegaUp\DAO\Submissions::getCurrentStreak(
                intval($identity->identity_id)
            );
            $this->assertSame(31, $streak);
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }
    }
}
