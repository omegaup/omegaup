<?php

/**
 * Description of OmegaupUITestCase
 *
 * @author joemmanuel
 */

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class OmegaupUITestCase extends PHPUnit_Extensions_SeleniumTestCase {
    protected function setUp() {
        $this->browser = '*firefox';
        $this->browser_url = OMEGAUP_BASE_URL;
    }

    protected function createUserAndLogin() {
        // Turn off sending email on usere creation
        UserController::$sendEmailOnVerify = false;

        // Create a user
        $contestant = UserFactory::createUser();

        // Open index
        $this->open('/');

        // Click in Iniciar Sesion
        $this->clickAndWait('link=Inicia sesion');

        // Type login data
        $this->type('user', $contestant->username);
        $this->type('pass', $contestant->password);

        // Click inicia sesion
        $this->clickAndWait("//input[@value='Inicia sesion']");

        // Sanity check that we are logged in
        $this->waitForElementPresent('//*[@id="root"]/div[1]/a');
        $this->assertElementContainsText('//*[@id="root"]/div[1]/a', $contestant->username);

        // Back to index
        $this->open('/');

        return $contestant;
    }

    protected function createAdminUserAndLogin() {
        $contestant = $this->createUserAndLogin();

        $userRoles = new UserRoles(array(
            'user_id' => $contestant->user_id,
            'role_id' => Authorization::ADMIN_ROLE,
            'acl_id' => Authorization::SYSTEM_ACL,
        ));
        UserRolesDAO::save($userRoles);

        return $contestant;
    }
}
