<?php

/**
 * Description of ContestArchiveContest
 */
class CertificatesTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Generate certificates in a contest
     *
     */
    /*public function testGenerateContestCertificates() {
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

        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'alias' => 'pasado',
            ])
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
    }*/

    /**
     * Generate certificates in a contest
     *
     */
    public function testConnectionRabbitMQ() {
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

        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'public']
            )
        );

        $certificatesCutoff = 4;

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

        \OmegaUp\Test\Utils::runGenerateContestCertificates();

        $this->assertEquals(3, $contest->certificate_cutoff);
    }

    /**
     * try to generate certificates in a contest as a normal user
     *
     */
    /*public function testGenerateContestCertificatesAsNormalUser() {
        //Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        //login
        $loginIdentity = self::login($identity);

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
    }*/

    /**
     * Generate certificates in a contest
     *
     */
    /*public function testGetUserCertificates() {
        //Create user
        ['identity' => $certificateGenerator] = \OmegaUp\Test\Factories\User::createUser();

        //login
        $loginCertificateGenerator = self::login($certificateGenerator);

        //add role certificate generator to identity user
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginCertificateGenerator->auth_token,
            'username' => $certificateGenerator->username,
            'role' => 'CertificateGenerator'
        ]));

        // Get a problem
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create contest with 2 hours and a window length 30 of minutes
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'alias' => 'pasado',
            ])
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problem,
            $contestData
        );

        // Create a contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        error_log(print_r($identity->user_id, true));

        // Add contestant to contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        // User creates a run in a valid time
        $run = \OmegaUp\Test\Factories\Run::createRun(
            $problem,
            $contestData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);

        $certificatesCutoff = 3;

        \OmegaUp\Controllers\Certificate::apiGenerateContestCertificates(
            new \OmegaUp\Request([
                'auth_token' => $loginCertificateGenerator->auth_token,
                'contest_id' => $contestData['contest']->contest_id,
                'certificates_cutoff' => $certificatesCutoff
            ])
        );

        //login
        $loginIdentity = self::login($identity);

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $this->assertEquals(3, $contest->certificate_cutoff);

        ///checar los

        \OmegaUp\Controllers\Certificate::apiGetUserCertificates(
            new \OmegaUp\Request([
                'auth_token' => $loginIdentity->auth_token,
                'user_id' => $identity->user_id
            ])
        );
    }*/
}
