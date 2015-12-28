<?php

/**
 * Testing new user special cases
 *
 * @author alanboy@omegaup.com
 */
class UserRegistrationTest extends OmegaupTestCase {
    /*
	 *  Scenario:
	 *		user A creates a new native account :
	 *			username=A email=A@example.com
	 *
	 *		user B logs in with fb/google:
	 *			email=A@gmail.com
	 */
    public function testUserNameCollision() {
        $salt = time();

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
}
