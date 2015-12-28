<?php

/**
 * Description of OmegaupUITestCase
 *
 * @author joemmanuel
 */

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class OmegaupUITestCase extends PHPUnit_Extensions_SeleniumTestCase {
    protected function setUp() {
        $this->setBrowser('*firefox');
        $this->setBrowserUrl(OMEGAUP_BASE_URL);
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
        $this->type('user', $contestant->getUsername());
        $this->type('pass', $contestant->getPassword());

        // Click inicia sesion
        $this->clickAndWait("//input[@value='Inicia sesion']");

        // Sanity check that we are logged in
        $this->waitForElementPresent('//*[@id="wrapper"]/div[1]/a');
        $this->assertElementContainsText('//*[@id="wrapper"]/div[1]/a', $contestant->getUsername());

        // Back to index
        $this->open('/');

        return $contestant;
    }

    protected function createAdminUserAndLogin() {
        $contestant = $this->createUserAndLogin();

        $userRoles = new UserRoles(array(
            'user_id' => $contestant->getUserId(),
            'role_id' => ADMIN_ROLE,
            'contest_id' => 0,
        ));
        UserRolesDAO::save($userRoles);

        return $contestant;
    }
}
