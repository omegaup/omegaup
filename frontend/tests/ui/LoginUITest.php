<?php

/**
 * Description of LoginUITest
 *
 * @author joemmanuel
 */

class LoginUITest extends OmegaupUITestCase {
    public function testLogin() {
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
    }
}
