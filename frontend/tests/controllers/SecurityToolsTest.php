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
     * Test that passwords shorter than 8 characters are rejected
     */
    public function testPasswordTooShortRejected(): void {
        $this->expectException(
            \OmegaUp\Exceptions\InvalidParameterException::class
        );
        $this->expectExceptionMessage('parameterStringTooShort');
        \OmegaUp\SecurityTools::testStrongPassword('Ab1!xyz');
    }

    /**
     * Test that passwords longer than 72 characters are rejected
     */
    public function testPasswordTooLongRejected(): void {
        $this->expectException(
            \OmegaUp\Exceptions\InvalidParameterException::class
        );
        $this->expectExceptionMessage('parameterStringTooLong');
        // Create a 73-character password with all requirements
        $longPassword = 'Aa1!' . str_repeat('x', 69);
        \OmegaUp\SecurityTools::testStrongPassword($longPassword);
    }

    /**
     * Test that password without uppercase letter is rejected
     */
    public function testPasswordWithoutUppercaseRejected(): void {
        $this->expectException(
            \OmegaUp\Exceptions\InvalidParameterException::class
        );
        $this->expectExceptionMessage('passwordMustContainUppercase');
        \OmegaUp\SecurityTools::testStrongPassword('password1!');
    }

    /**
     * Test that password without lowercase letter is rejected
     */
    public function testPasswordWithoutLowercaseRejected(): void {
        $this->expectException(
            \OmegaUp\Exceptions\InvalidParameterException::class
        );
        $this->expectExceptionMessage('passwordMustContainLowercase');
        \OmegaUp\SecurityTools::testStrongPassword('PASSWORD1!');
    }

    /**
     * Test that password without digit is rejected
     */
    public function testPasswordWithoutDigitRejected(): void {
        $this->expectException(
            \OmegaUp\Exceptions\InvalidParameterException::class
        );
        $this->expectExceptionMessage('passwordMustContainDigit');
        \OmegaUp\SecurityTools::testStrongPassword('Password!@');
    }

    /**
     * Test that password without special character is rejected
     */
    public function testPasswordWithoutSpecialCharRejected(): void {
        $this->expectException(
            \OmegaUp\Exceptions\InvalidParameterException::class
        );
        $this->expectExceptionMessage('passwordMustContainSpecialChar');
        \OmegaUp\SecurityTools::testStrongPassword('Password123');
    }

    /**
     * Test that null password is rejected
     */
    public function testNullPasswordRejected(): void {
        $this->expectException(
            \OmegaUp\Exceptions\InvalidParameterException::class
        );
        \OmegaUp\SecurityTools::testStrongPassword(null);
    }

    /**
     * Test that empty password is rejected
     */
    public function testEmptyPasswordRejected(): void {
        $this->expectException(
            \OmegaUp\Exceptions\InvalidParameterException::class
        );
        \OmegaUp\SecurityTools::testStrongPassword('');
    }

    /**
     * Test that common weak passwords are rejected
     */
    public function testCommonWeakPasswordsRejected(): void {
        // Test '12345678' - has digit but missing uppercase, lowercase, special
        try {
            \OmegaUp\SecurityTools::testStrongPassword('12345678');
            $this->fail('Expected exception for password "12345678"');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('passwordMustContainUppercase', $e->getMessage());
        }

        // Test 'password' - has lowercase but missing uppercase, digit, special
        try {
            \OmegaUp\SecurityTools::testStrongPassword('password');
            $this->fail('Expected exception for password "password"');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('passwordMustContainUppercase', $e->getMessage());
        }

        // Test 'PASSWORD' - has uppercase but missing lowercase, digit, special
        try {
            \OmegaUp\SecurityTools::testStrongPassword('PASSWORD');
            $this->fail('Expected exception for password "PASSWORD"');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('passwordMustContainLowercase', $e->getMessage());
        }
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
