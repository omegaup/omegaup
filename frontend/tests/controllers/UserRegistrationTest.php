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
        $salt = Time::get();

        // Test users should not exist
        $this->assertNull(UsersDAO::FindByUsername('A'.$salt));
        $this->assertNull(UsersDAO::FindByUsername('A'.$salt.'1'));
        $this->assertNull(UsersDAO::FindByUsername('A'.$salt.'2'));

        // Create collision
        $c = new SessionController();
        $c->LoginViaGoogle('A'.$salt.'@isp1.com');
        $c->LoginViaGoogle('A'.$salt.'@isp2.com');
        $c->LoginViaGoogle('A'.$salt.'@isp3.com');

        $this->assertNotNull(UsersDAO::FindByUsername('A'.$salt));
        $this->assertNotNull(UsersDAO::FindByUsername('A'.$salt.'1'));
        $this->assertNotNull(UsersDAO::FindByUsername('A'.$salt.'2'));
    }

    /**
     * User logged via google, try log in with native mode
     */
    public function testUserLoggedViaGoogleAndThenNativeMode() {
        $username = 'X'.Time::get();
        $password = Utils::CreateRandomString();

        $c = new SessionController();
        $c->LoginViaGoogle($username.'@isp.com');
        $user = UsersDAO::FindByUsername($username);

        // Users logged via google, facebook, linkedin does not have password
        $this->assertNull($user->password);

        // Inflate request
        UserController::$permissionKey = uniqid();
        $r = new Request([
            'username' => $username,
            'password' => $password,
            'email' => $username.'@isp.com',
            'permission_key' => UserController::$permissionKey
        ]);

        // Call API
        $response = UserController::apiCreate($r);

        $user = UsersDAO::FindByUsername($username);

        // Users logged in native mode must have password
        $this->assertNotNull($user->password);
    }

    /**
     * User logged via google, try log in with native mode, and
     * different username
     */
    public function testUserLoggedViaGoogleAndThenNativeModeWithDifferentUsername() {
        $username = 'Y'.Time::get();
        $email = $username.'@isp.com';

        $c = new SessionController();
        $c->LoginViaGoogle($email);
        $user = UsersDAO::FindByUsername($username);
        $email_user = EmailsDAO::getByPK($user->main_email_id);

        // Asserts that user has the initial username and email
        $this->assertEquals($user->username, $username);
        $this->assertEquals($email, $email_user->email);

        // Inflate request
        UserController::$permissionKey = uniqid();
        $r = new Request([
            'username' => 'Z'.$username,
            'password' => Utils::CreateRandomString(),
            'email' => $email,
            'permission_key' => UserController::$permissionKey
        ]);

        // Call API
        $response = UserController::apiCreate($r);

        $user = UsersDAO::FindByUsername('Z'.$username);
        $email_user = EmailsDAO::getByPK($user->main_email_id);

        // Asserts that user has different username but the same email
        $this->assertNotEquals($user->username, $username);
        $this->assertEquals($email, $email_user->email);
    }
}
