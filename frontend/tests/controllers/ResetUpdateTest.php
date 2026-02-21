<?php
class ResetUpdateTest extends \OmegaUp\Test\ControllerTestCase {
    public function testShouldRequireAllParameters() {
        try {
            $r = new \OmegaUp\Request();
            \OmegaUp\Controllers\Reset::apiUpdate($r);
            $this->fail('Request should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $expected) {
            // Verify that the cause of the exception was the expected.
            $this->assertSame('parameterEmpty', $expected->getMessage());
        }
    }

    public function testShouldRefuseInvalidResetToken() {
        try {
            $user_data = \OmegaUp\Test\Factories\User::generateUser();
            $user_data['password_confirmation'] = $user_data['password'];
            $user_data['reset_token'] = 'abcde';
            $r = new \OmegaUp\Request($user_data);
            \OmegaUp\Controllers\Reset::apiUpdate($r);
            $this->fail('Request should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $expected) {
            $this->assertSame('invalidResetToken', $expected->getMessage());
        }
    }

    public function testShouldRefusePasswordMismatch() {
        try {
            $user_data = \OmegaUp\Test\Factories\User::generateUser();
            $r = new \OmegaUp\Request(['email' => $user_data['email']]);
            $response = \OmegaUp\Controllers\Reset::apiCreate($r);
            $user_data['reset_token'] = $response['token'];
            $user_data['password_confirmation'] = 'abcde';
            $r = new \OmegaUp\Request($user_data);
            \OmegaUp\Controllers\Reset::apiUpdate($r);
            $this->fail('Request should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $expected) {
            $this->assertSame('passwordMismatch', $expected->getMessage());
        }
    }

    public function testShouldRefuseInvalidPassword() {
        $user_data = \OmegaUp\Test\Factories\User::generateUser();
        $r = new \OmegaUp\Request(['email' => $user_data['email']]);
        $response = \OmegaUp\Controllers\Reset::apiCreate($r);
        $user_data['reset_token'] = $response['token'];

        $user_data['password'] = 'abcde';
        $user_data['password_confirmation'] = 'abcde';
        $r = new \OmegaUp\Request($user_data);
        try {
            \OmegaUp\Controllers\Reset::apiUpdate($r);
            $this->fail('Request should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $expected) {
            $this->assertSame(
                'parameterStringTooShort',
                $expected->getMessage()
            );
        }

        $user_data['password'] = str_pad('', 73, 'a');
        $user_data['password_confirmation'] = str_pad('', 73, 'a');
        $r = new \OmegaUp\Request($user_data);
        try {
            \OmegaUp\Controllers\Reset::apiUpdate($r);
            $this->fail('Request should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $expected) {
            $this->assertSame(
                'parameterStringTooLong',
                $expected->getMessage()
            );
        }
    }

    public function testShouldRefuseExpiredReset() {
        $user_data = \OmegaUp\Test\Factories\User::generateUser();
        $r = new \OmegaUp\Request(['email' => $user_data['email']]);
        $response = \OmegaUp\Controllers\Reset::apiCreate($r);
        $user_data['password_confirmation'] = $user_data['password'];
        $user_data['reset_token'] = $response['token'];

        // Time travel
        $reset_sent_at = \OmegaUp\ApiUtils::getStringTime(
            \OmegaUp\Time::get() - PASSWORD_RESET_TIMEOUT - 1
        );
        $user = \OmegaUp\DAO\Users::findByEmail($user_data['email']);
        $user->reset_sent_at = $reset_sent_at;
        \OmegaUp\DAO\Users::update($user);

        try {
            $r = new \OmegaUp\Request($user_data);
            \OmegaUp\Controllers\Reset::apiUpdate($r);
            $this->fail('Request should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $expected) {
            $this->assertSame(
                'passwordResetResetExpired',
                $expected->getMessage()
            );
        }
    }

    public function testShouldLogInWithNewPassword() {
        $user_data = \OmegaUp\Test\Factories\User::generateUser();
        $r = new \OmegaUp\Request(['email' => $user_data['email']]);
        $create_response = \OmegaUp\Controllers\Reset::apiCreate($r);
        $reset_token = $create_response['token'];
        $user_data['reset_token'] = $reset_token;

        $new_password = 'N3wPassw0rd!';
        $user_data['password'] = $new_password;
        $user_data['password_confirmation'] = $new_password;
        $r = new \OmegaUp\Request($user_data);

        \OmegaUp\Controllers\Reset::apiUpdate($r);
        $identity = \OmegaUp\DAO\Identities::findByEmail($user_data['email']);
        $identity->password = $new_password;
        self::login($identity);
    }
}
