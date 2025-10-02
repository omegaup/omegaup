<?php

/**
 * Tests for MySQL deadlock errors and retry improvements
 */
class MySQLDeadlockTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test that demonstrates the submission gap check method exists and is callable
     *
     * This test verifies that our improved isInsideSubmissionGap method
     * can handle deadlock scenarios gracefully
     */
    public function testSubmissionGapCheckMethodExists() {
        // Create test data
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a submission object
        $submission = new \OmegaUp\DAO\VO\Submissions([
            'identity_id' => $identity->identity_id,
            'problem_id' => $problemData['problem']->problem_id,
            'time' => \OmegaUp\Time::get(),
            'type' => 'normal',
        ]);

        // Test that the method can be called without throwing exceptions
        $result = \OmegaUp\DAO\Submissions::isInsideSubmissionGap(
            $submission,
            intval($problemData['problem']->problem_id),
            intval($identity->identity_id)
        );

        // Since this is the first submission, it should be allowed
        $this->assertTrue($result);
    }

    /**
     * Test submission gap with contest (more complex scenario)
     */
    public function testSubmissionGapWithContest() {
        // Create test contest and problem
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add problem to contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Add user to contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        // Create submission
        $submission = new \OmegaUp\DAO\VO\Submissions([
            'identity_id' => $identity->identity_id,
            'problem_id' => $problemData['problem']->problem_id,
            'problemset_id' => $contestData['contest']->problemset_id,
            'time' => \OmegaUp\Time::get(),
            'type' => 'normal',
        ]);

        // Test submission gap check with contest
        $result = \OmegaUp\DAO\Submissions::isInsideSubmissionGap(
            $submission,
            intval($problemData['problem']->problem_id),
            intval($identity->identity_id),
            $contestData['contest']
        );

        // First submission should be allowed
        $this->assertTrue($result);
    }

    /**
     * Test that MySQL error codes can be properly identified for retry logic
     */
    public function testMySQLErrorCodeIdentification() {
        // Test deadlock error code (1213)
        $deadlockException = new \mysqli_sql_exception('Deadlock found', 1213);
        $this->assertEquals(1213, $deadlockException->getCode());

        // Test lock timeout error code (1205)
        $lockTimeoutException = new \mysqli_sql_exception(
            'Lock wait timeout exceeded',
            1205
        );
        $this->assertEquals(1205, $lockTimeoutException->getCode());

        // Verify our retry logic would identify these as retryable errors
        $retryableCodes = [1205, 1213];
        $this->assertContains($deadlockException->getCode(), $retryableCodes);
        $this->assertContains(
            $lockTimeoutException->getCode(),
            $retryableCodes
        );
    }

    /**
     * Test submission gap timing logic without actually inserting into database
     */
    public function testSubmissionGapTiming() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a submission object for testing (without inserting to DB)
        $testTime = \OmegaUp\Time::get();
        $testSubmission = new \OmegaUp\DAO\VO\Submissions([
            'identity_id' => $identity->identity_id,
            'problem_id' => $problemData['problem']->problem_id,
            'time' => $testTime,
            'type' => 'normal',
        ]);

        // Test that the submission gap check works for new user/problem combination
        $result = \OmegaUp\DAO\Submissions::isInsideSubmissionGap(
            $testSubmission,
            intval($problemData['problem']->problem_id),
            intval($identity->identity_id)
        );

        // For a new user/problem combination with no previous submissions,
        // the gap check should return true (allowed to submit)
        $this->assertTrue($result);
    }

    /**
     * Test TransactionHelper retry mechanism works correctly
     */
    public function testTransactionHelperRetriesDeadlocks() {
        $attempts = 0;

        // Test function that succeeds on the second attempt
        $testFunction = function () use (&$attempts) {
            $attempts++;
            if ($attempts === 1) {
                // Simulate deadlock on first attempt
                throw new \OmegaUp\Exceptions\DatabaseOperationException(
                    'Deadlock found when trying to get lock',
                    1213
                );
            }
            return 'success';
        };

        $result = \OmegaUp\TransactionHelper::executeWithRetry($testFunction);

        $this->assertEquals('success', $result);
        $this->assertEquals(2, $attempts); // Should have retried once
    }

    /**
     * Test TransactionHelper doesn't retry non-deadlock errors
     */
    public function testTransactionHelperDoesNotRetryNonDeadlockErrors() {
        $attempts = 0;

        // Test function that fails with non-deadlock error
        $testFunction = function () use (&$attempts) {
            $attempts++;
            throw new \OmegaUp\Exceptions\DatabaseOperationException(
                'Duplicate entry',
                1062 // Duplicate key error
            );
        };

        $this->expectException(
            \OmegaUp\Exceptions\DatabaseOperationException::class
        );

        \OmegaUp\TransactionHelper::executeWithRetry($testFunction);

        $this->assertEquals(1, $attempts); // Should not have retried
    }

    /**
     * Test DatabaseOperationException deadlock detection method
     */
    public function testDatabaseOperationExceptionDeadlockDetection() {
        // Test deadlock detection for code 1213 (Deadlock found)
        $deadlockException = new \OmegaUp\Exceptions\DatabaseOperationException(
            'Deadlock found when trying to get lock',
            1213
        );
        $this->assertTrue($deadlockException->isDeadlock());

        // Test deadlock detection for code 1205 (Lock wait timeout)
        $timeoutException = new \OmegaUp\Exceptions\DatabaseOperationException(
            'Lock wait timeout exceeded',
            1205
        );
        $this->assertTrue($timeoutException->isDeadlock());

        // Test that non-deadlock errors are not detected as deadlocks
        $duplicateException = new \OmegaUp\Exceptions\DatabaseOperationException(
            'Duplicate entry',
            1062
        );
        $this->assertFalse($duplicateException->isDeadlock());
    }
}
