<?php

/**
 * Description of CertificatesTest
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

        $currentTime = \OmegaUp\Time::get();
        $timePast1 =  new \OmegaUp\Timestamp($currentTime - 120 * 60);
        $timePast2 =  new \OmegaUp\Timestamp($currentTime - 60 * 60);
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'alias' => 'pasado',
                'startTime' => $timePast1,
                'finishTime' => $timePast2,
            ])
        );

        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contestData,
            $identity
        );

        $certificatesCutoff = 3;

        $response = \OmegaUp\Controllers\Certificate::apiGenerateContestCertificates(
            new \OmegaUp\Request([
                'auth_token' => $loginIdentity->auth_token,
                'contest_id' => $contestData['contest']->contest_id,
                'certificates_cutoff' => $certificatesCutoff
            ])
        );

        // Assert status of new contest
        $this->assertSame('ok', $response['status']);

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $this->assertEquals(3, $contest->certificate_cutoff);
    }

    /**
     * Try to generate certificates in a contest as a certificate generator
     * but not as a contest admin
     *
     */
    public function testGenerateContestCertificatesOnlyAsCertificateGenerator() {
        //Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $loginIdentity = self::login($identity);

        //add role certificate generator to identity user
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'username' => $identity->username,
            'role' => 'CertificateGenerator'
        ]));

        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'alias' => 'pasado',
            ])
        );

        $certificatesCutoff = 3;

        try {
            \OmegaUp\Controllers\Certificate::apiGenerateContestCertificates(
                new \OmegaUp\Request([
                    'auth_token' => $loginIdentity->auth_token,
                    'contest_id' => $contestData['contest']->contest_id,
                    'certificates_cutoff' => $certificatesCutoff
                ])
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }
}
