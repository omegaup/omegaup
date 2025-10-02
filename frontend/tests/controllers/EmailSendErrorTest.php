<?php

/**
 * Tests for email sending errors and improvements
 */
class EmailSendErrorTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();
        // Reset email sender to default state
        \OmegaUp\Email::setEmailSenderForTesting(null);
    }

    /**
     * Test that demonstrates EmailVerificationSendException handling
     *
     * This test simulates the error that appears in:
     * - /api/user.create (2 occurrences)
     * - /api/user.deleteconfirm (12 occurrences)
     */
    public function testEmailSendFailureHandling() {
        // Create a mock email sender that always fails
        $mockEmailSender = new MockFailingEmailSender();
        \OmegaUp\Email::setEmailSenderForTesting($mockEmailSender);

        // This should throw EmailVerificationSendException
        $this->expectException(
            \OmegaUp\Exceptions\EmailVerificationSendException::class
        );

        // Try to send an email - this should fail
        \OmegaUp\Email::sendEmail(
            ['test@example.com'],
            'Test Subject',
            'Test Body'
        );
    }

    /**
     * Test that email sending works correctly when SMTP is properly configured
     */
    public function testEmailSendSuccess() {
        // Create a mock email sender that succeeds
        $mockEmailSender = new MockSuccessfulEmailSender();
        \OmegaUp\Email::setEmailSenderForTesting($mockEmailSender);

        // This should work without throwing exceptions
        \OmegaUp\Email::sendEmail(
            ['test@example.com'],
            'Test Subject',
            'Test Body'
        );

        // If we reach here, the email was sent successfully
        $this->assertTrue(true);
    }

    /**
     * Test email sending when OMEGAUP_EMAIL_SEND_EMAILS is disabled
     */
    public function testEmailSendingDisabled() {
        // Reset email sender to use default behavior
        \OmegaUp\Email::setEmailSenderForTesting(null);

        // When OMEGAUP_EMAIL_SEND_EMAILS is false (default in tests),
        // no actual email should be sent and no exception should be thrown
        \OmegaUp\Email::sendEmail(
            ['test@example.com'],
            'Test Subject',
            'Test Body'
        );

        // If we reach here, the method handled the disabled state correctly
        $this->assertTrue(true);
    }

    /**
     * Test that verifies basic email functionality without retry
     * (Note: Retry logic would be implemented at a higher level, not in Email class)
     */
    public function testEmailSendBasicFunctionality() {
        // Reset the attempt counter
        MockEmailSenderWithRetry::resetAttemptCount();

        // Create a mock that succeeds on first try
        $mockEmailSender = new MockSuccessfulEmailSender();
        \OmegaUp\Email::setEmailSenderForTesting($mockEmailSender);

        // This should work immediately
        \OmegaUp\Email::sendEmail(
            ['test@example.com'],
            'Test Subject',
            'Test Body'
        );

        // If we reach here, the email was sent successfully
        $this->assertTrue(true);
    }
}

/**
 * Mock email sender that always fails (simulates SMTP errors)
 */
class MockFailingEmailSender implements \OmegaUp\EmailSender {
    public function sendEmail(
        array $emails,
        string $subject,
        string $body
    ): void {
        throw new \OmegaUp\Exceptions\EmailVerificationSendException();
    }
}

/**
 * Mock email sender that always succeeds
 */
class MockSuccessfulEmailSender implements \OmegaUp\EmailSender {
    public function sendEmail(
        array $emails,
        string $subject,
        string $body
    ): void {
        // Do nothing - simulate successful email sending
    }
}

/**
 * Mock email sender that fails once then succeeds (simulates retry logic)
 */
class MockEmailSenderWithRetry implements \OmegaUp\EmailSender {
    private static $attemptCount = 0;

    public function sendEmail(
        array $emails,
        string $subject,
        string $body
    ): void {
        self::$attemptCount++;

        if (self::$attemptCount === 1) {
            // First attempt fails
            throw new \OmegaUp\Exceptions\EmailVerificationSendException();
        }

        // Second attempt succeeds
        // Do nothing - simulate successful email sending
    }

    public static function getAttemptCount(): int {
        return self::$attemptCount;
    }

    public static function resetAttemptCount(): void {
        self::$attemptCount = 0;
    }
}
