<?php
/**
 * Tests for SecurityTools password validation functionality.
 */

class SecurityToolsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test that a strong password passes validation
     */
    public function testStrongPasswordPasses(): void {
        // This should not throw any exception
        \OmegaUp\SecurityTools::testStrongPassword('StrongP@ss1');
        $this->assertTrue(true); // If we get here, the test passed
    }

    /**
     * Test that a password with all requirements passes
     */
    public function testPasswordWithAllRequirementsPasses(): void {
        // Multiple valid passwords
        \OmegaUp\SecurityTools::testStrongPassword('Abc123!@#');
        \OmegaUp\SecurityTools::testStrongPassword('Password1!');
        \OmegaUp\SecurityTools::testStrongPassword('Test@123');
        \OmegaUp\SecurityTools::testStrongPassword('MyP@ssw0rd');
        $this->assertTrue(true);
    }

    /**
     * Data provider for rejected password test cases.
     *
     * @return list<array{string, ?string, string}>
     */
    public function rejectedPasswordProvider(): array {
        return [
            ['parameterStringTooShort', 'Ab1!xyz', 'testPasswordTooShortRejected'],
            ['parameterStringTooLong', 'Aa1!' . str_repeat('x', 69), 'testPasswordTooLongRejected'],
            ['passwordMustContainUppercase', 'password1!', 'testPasswordWithoutUppercaseRejected'],
            ['passwordMustContainLowercase', 'PASSWORD1!', 'testPasswordWithoutLowercaseRejected'],
            ['passwordMustContainDigit', 'Password!@', 'testPasswordWithoutDigitRejected'],
            ['passwordMustContainSpecialChar', 'Password123', 'testPasswordWithoutSpecialCharRejected'],
            ['parameterStringTooShort', null, 'testNullPasswordRejected'],
            ['parameterStringTooShort', '', 'testEmptyPasswordRejected'],
            ['passwordMustContainUppercase', '12345678', 'testCommonWeakPassword12345678'],
            ['passwordMustContainUppercase', 'password', 'testCommonWeakPasswordAllLowercase'],
            ['passwordMustContainLowercase', 'PASSWORD', 'testCommonWeakPasswordAllUppercase'],
        ];
    }

    /**
     * @dataProvider rejectedPasswordProvider
     *
     * Test that invalid passwords are rejected with the expected error message.
     */
    public function testPasswordRejected(
        string $expectedMessage,
        ?string $password,
        string $description
    ): void {
        $this->expectException(
            \OmegaUp\Exceptions\InvalidParameterException::class
        );
        $this->expectExceptionMessage($expectedMessage);
        \OmegaUp\SecurityTools::testStrongPassword($password);
    }

    /**
     * Test that various special characters are accepted
     */
    public function testVariousSpecialCharactersAccepted(): void {
        // Test each special character individually
        $specialChars = ['!', '@', '#', '$', '%', '^', '&', '*', '(', ')', ',', '.', '?', '"', ':', '{', '}', '|', '<', '>'];

        foreach ($specialChars as $char) {
            $password = "Password1{$char}";
            try {
                \OmegaUp\SecurityTools::testStrongPassword($password);
            } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
                $this->fail(
                    "Password with special char '{$char}' should be accepted but got: {$e->getMessage()}"
                );
            }
        }
        $this->assertTrue(true);
    }

    /**
     * Test password at exactly 8 characters (minimum boundary)
     */
    public function testPasswordAtMinimumLength(): void {
        // Exactly 8 characters with all requirements
        \OmegaUp\SecurityTools::testStrongPassword('Aa1!xxxx');
        $this->assertTrue(true);
    }

    /**
     * Test password at exactly 72 characters (maximum boundary)
     */
    public function testPasswordAtMaximumLength(): void {
        // Exactly 72 characters with all requirements
        $password = 'Aa1!' . str_repeat('x', 68);
        $this->assertSame(72, strlen($password));
        \OmegaUp\SecurityTools::testStrongPassword($password);
        $this->assertTrue(true);
    }
}
