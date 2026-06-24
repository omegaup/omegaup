<?php
/**
 * Tests for contest problem change log (optimized architecture).
 */

class ContestProblemChangeLogTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * When a problem is added to an active contest
     */
    public function testLogCreatedOnProblemAddedToActiveContest(): void {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        ['identity' => $contestant] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $contestant);

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $directorLogin = self::login($contestData['director']);
        \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'points' => 100,
            'order_in_contest' => 1,
        ]));

        $logs = \OmegaUp\DAO\ContestProblemChangeLog::getByContestId(
            intval($contestData['contest']->contest_id)
        );
        $this->assertCount(1, $logs);
        $this->assertSame('added', $logs[0]['change_type']);
        $this->assertSame(
            $problemData['problem']->alias,
            $logs[0]['problem_alias']
        );
    }

    /**
     * When a problem is modified in an active contest,a 'modified' log entry should be created.
     */
    public function testLogCreatedOnProblemModifiedInActiveContest(): void {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Re-add with different points (triggers 'modified')
        $directorLogin = self::login($contestData['director']);
        \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'points' => 200,
            'order_in_contest' => 1,
        ]));

        $logs = \OmegaUp\DAO\ContestProblemChangeLog::getByContestId(
            intval($contestData['contest']->contest_id)
        );

        // First addProblemToContest also creates a log entry ('added'),
        // then the re-add creates a 'modified' entry
        $modifiedLogs = array_filter(
            $logs,
            fn($log) => $log['change_type'] === 'modified'
        );
        $this->assertCount(1, $modifiedLogs);
    }

    /**
     * When a problem is removed from an active contest, a 'removed'
     * log entry should be created.
     */
    public function testLogCreatedOnProblemRemovedFromActiveContest(): void {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
            ])
        );

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        \OmegaUp\Test\Factories\Contest::removeProblemFromContest(
            $problemData,
            $contestData
        );

        $logs = \OmegaUp\DAO\ContestProblemChangeLog::getByContestId(
            intval($contestData['contest']->contest_id)
        );
        $removedLogs = array_filter(
            $logs,
            fn($log) => $log['change_type'] === 'removed'
        );
        $this->assertCount(1, $removedLogs);
    }

    /**
     * No log entry should be created if the contest is NOT currently active.
     */
    public function testNoLogWhenContestNotActive(): void {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => new \OmegaUp\Timestamp(
                    \OmegaUp\Time::get() + 60 * 60
                ),
                'finishTime' => new \OmegaUp\Timestamp(
                    \OmegaUp\Time::get() + 2 * 60 * 60
                ),
            ])
        );

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $directorLogin = self::login($contestData['director']);
        \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'points' => 100,
            'order_in_contest' => 1,
        ]));

        $logs = \OmegaUp\DAO\ContestProblemChangeLog::getByContestId(
            intval($contestData['contest']->contest_id)
        );
        $this->assertCount(0, $logs);
    }

    /**
     * The apiProblemChangeLogs endpoint should return the correct
     * log entries for a contest.
     */
    public function testProblemChangeLogsEndpoint(): void {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        ['identity' => $contestant] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $contestant);

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $directorLogin = self::login($contestData['director']);
        \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'points' => 100,
            'order_in_contest' => 1,
        ]));

        // Fetch logs via the new API endpoint
        $login = self::login($contestant);
        $response = \OmegaUp\Controllers\Contest::apiProblemChangeLogs(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        );

        $this->assertArrayHasKey('logs', $response);
        $this->assertCount(1, $response['logs']);
        $this->assertSame('added', $response['logs'][0]['change_type']);
        $this->assertSame(
            $problemData['problem']->alias,
            $response['logs'][0]['problemAlias']
        );
    }
}
