<?php

/**
 * Testing new user special cases
 *
 * @author alanboy@omegaup.com
 */
class UserRegistrationTest extends OmegaupTestCase {
    /**
     *  Scenario:
     *      user A creates a new native account :
     *          username=A email=A@example.com
     *
     *      user B logs in with fb/google:
     *          email=A@gmail.com
     */
    public function testUserNameCollision() {
        $salt = \OmegaUp\Time::get();

        // Test users should not exist
        $this->assertNull(\OmegaUp\DAO\Users::FindByUsername('A'.$salt));
        $this->assertNull(\OmegaUp\DAO\Users::FindByUsername('A'.$salt.'1'));
        $this->assertNull(\OmegaUp\DAO\Users::FindByUsername('A'.$salt.'2'));

        // Create collision
        \OmegaUp\Controllers\Session::LoginViaGoogle('A'.$salt.'@isp1.com');
        \OmegaUp\Controllers\Session::LoginViaGoogle('A'.$salt.'@isp2.com');
        \OmegaUp\Controllers\Session::LoginViaGoogle('A'.$salt.'@isp3.com');

        $this->assertNotNull(\OmegaUp\DAO\Users::FindByUsername('A'.$salt));
        $this->assertNotNull(\OmegaUp\DAO\Users::FindByUsername('A'.$salt.'1'));
        $this->assertNotNull(\OmegaUp\DAO\Users::FindByUsername('A'.$salt.'2'));
    }

    /**
     * User logged via google, try log in with native mode
     */
    public function testUserLoggedViaGoogleAndThenNativeMode() {
        $username = 'X'.\OmegaUp\Time::get();
        $password = Utils::CreateRandomString();

        \OmegaUp\Controllers\Session::LoginViaGoogle($username.'@isp.com');
        $user = \OmegaUp\DAO\Users::FindByUsername($username);

        // Users logged via google, facebook, linkedin does not have password
        $this->assertNull($user->password);

        // Inflate request
        \OmegaUp\Controllers\User::$permissionKey = uniqid();
        $r = new \OmegaUp\Request([
            'username' => $username,
            'password' => $password,
            'email' => $username.'@isp.com',
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey
        ]);

        // Call API
        $response = \OmegaUp\Controllers\User::apiCreate($r);

        $user = \OmegaUp\DAO\Users::FindByUsername($username);
    }

    /**
     * User logged via google, try log in with native mode, and
     * different username
     */
    public function testUserLoggedViaGoogleAndThenNativeModeWithDifferentUsername() {
        $username = 'Y'.\OmegaUp\Time::get();
        $email = $username.'@isp.com';

        \OmegaUp\Controllers\Session::LoginViaGoogle($email);
        $user = \OmegaUp\DAO\Users::FindByUsername($username);
        $email_user = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);

        // Asserts that user has the initial username and email
        $this->assertEquals($user->username, $username);
        $this->assertEquals($email, $email_user->email);

        // Inflate request
        \OmegaUp\Controllers\User::$permissionKey = uniqid();
        $r = new \OmegaUp\Request([
            'username' => 'Z'.$username,
            'password' => Utils::CreateRandomString(),
            'email' => $email,
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey
        ]);

        // Call API
        $response = \OmegaUp\Controllers\User::apiCreate($r);

        $user = \OmegaUp\DAO\Users::FindByUsername('Z'.$username);
        $email_user = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);
    }
}
