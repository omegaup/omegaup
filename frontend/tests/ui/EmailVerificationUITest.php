<?php

/**
 * Description of LoginUITest
 *
 * @author joemmanuel
 */

class EmailVerificationUITest extends OmegaupUITestCase {
    public function testLogin() {
        // Turn off sending email on usere creation
        UserController::$sendEmailOnVerify = false;

        // Create a user
        $contestant = UserFactory::createUserWithoutVerify();

        // Open index
        $this->open('/');

        // Click in Iniciar Sesion
        $this->clickAndWait('link=Inicia sesion');

        // Type login data
        $this->type('user', $contestant->username);
        $this->type('pass', $contestant->password);

        // Click inicia sesion
        $this->clickAndWait("//input[@value='Inicia sesion']");

        // Wait for message
        $this->waitForElementPresent('//*[@id="content"]/div[2]/div');
        $this->assertElementContainsText('//*[@id="content"]/div[2]/div', 'Your email is not verified yet. Please check your e-mail.');

        // Go to verification page and wait for redirection to login page
        $this->open('/api/user/verifyemail/id/'.$contestant->verification_id);
        $this->waitForElementPresent('//*[@id="content"]/div[2]/div[1]/h1');

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
