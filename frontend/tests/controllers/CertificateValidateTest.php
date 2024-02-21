<?php
/**
 * Tests for apiValidateCertificate in CertificateController
 */

class CertificateValidateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test to verify that a certificate that doesn't exist is invalid
     */
    public function testValidateCertificateWithInvalidVerificationCode() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\DAO\Certificates::create(new \OmegaUp\DAO\VO\Certificates([
            'identity_id' => $identity->identity_id,
            'timestamp' => '2023-09-04',
            'certificate_type' => 'contest',
            'contest_id' => $contestData['contest']->contest_id,
            'verification_code' => '45KoPi9aM3'
        ]));

        $response = \OmegaUp\Controllers\Certificate::apiValidateCertificate(
            new \OmegaUp\Request(['verification_code' => 'D89lJ2aOZ3',])
        );

        $this->assertFalse($response['valid']);
    }

    /**
     * Test to verify that an existing certificate is valid
     */
    public function testValidateCertificateWithValidVerificationCode() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\DAO\Certificates::create(new \OmegaUp\DAO\VO\Certificates([
            'identity_id' => $identity->identity_id,
            'timestamp' => '2023-09-04',
            'certificate_type' => 'contest',
            'contest_id' => $contestData['contest']->contest_id,
            'verification_code' => 'D89lJ2aOZ3'
        ]));

        $response = \OmegaUp\Controllers\Certificate::apiValidateCertificate(
            new \OmegaUp\Request(['verification_code' => 'D89lJ2aOZ3',])
        );

        $this->assertTrue($response['valid']);
    }
}
