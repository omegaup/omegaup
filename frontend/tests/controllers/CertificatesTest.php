<?php

/**
 * Description of ContestArchiveContest
 */
class CertificatesTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Generate certificates in a contest
     *
     */
    public function testGenerateContestCertificates() {
        //Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        //login
        $loginIdentity = self::login($identity);

        //add role certificate generator to identity user
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'username' => $identity->username,
            'role' => 'CertificateGenerator'
        ]));

        // Create a valid contest Request object
        $contestData = \OmegaUp\Test\Factories\Contest::getRequest(new \OmegaUp\Test\Factories\ContestParams(
            ['admissionMode' => 'private']
        ));

        $r = $contestData['request'];
        $contestDirector = $contestData['director'];

        // Log in the user and set the auth token in the new request
        $login = self::login($contestDirector);
        $r['auth_token'] = $login->auth_token;
        $r['certificates_status'] = 'uninitiated';

        // Call the API
        $response = \OmegaUp\Controllers\Contest::apiCreate(
            $r
        );

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $certificates_cutoff = 3;

        \OmegaUp\Controllers\Certificate::apiGenerateContestCertificates(
            new \OmegaUp\Request([
                'auth_token' => $loginIdentity->auth_token,
                'contest_id' => $contest->contest_id,
                'certificates_cutoff' => $certificates_cutoff
            ])
        );

        // Assert status of new contest
        $this->assertSame('ok', $response['status']);

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $this->assertEquals(3, $contest->certificate_cutoff);
    }
}
