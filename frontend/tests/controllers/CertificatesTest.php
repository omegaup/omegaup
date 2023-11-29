<?php

/**
 * Description of CertificatesTest
 */
class CertificatesTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Generate certificates in a contest
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
    }*/

    /**
     * Generate certificates in a contest
     */
    public function testConnectionRabbitMQ() {
        //Create the certificate generator
        ['identity' => $certificateGenerator] = \OmegaUp\Test\Factories\User::createUser();

        //Create contestants
        $identities = [];
        $numOfIdentities = 8;
        foreach (range(0, $numOfIdentities - 1) as $index) {
            ['identity' => $identities[$index]] = \OmegaUp\Test\Factories\User::createUser();
        }

        $loginIdentity = self::login($certificateGenerator);

        //Add role certificate generator
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'username' => $certificateGenerator->username,
            'role' => 'CertificateGenerator'
        ]));

        //Create a contest that hasn't ended to send submissions
        $currentTime = \OmegaUp\Time::get();
        $timePast =  new \OmegaUp\Timestamp($currentTime - 60 * 60);
        $timeFuture =  new \OmegaUp\Timestamp($currentTime + 60 * 60);
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'public',
                'startTime' => $timePast,
                'finishTime' => $timeFuture,
            ])
        );

        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contestData,
            $certificateGenerator
        );

        //Create and add problems to the contest
        $problems = [];
        for ($i = 0; $i < 5; $i++) {
            $problems[$i] = \OmegaUp\Test\Factories\Problem::createProblem();

            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problems[$i],
                $contestData
            );
        }

        //Associate the identity index with
        //the problem indexes they are going to send
        $submissions = [
            0 => ['problems' => [0, 1, 2, 3, 4]],
            1 => ['problems' => [1]],
            2 => ['problems' => [0, 1, 2, 3]],
            3 => ['problems' => [2]],
            4 => ['problems' => [3]],
            5 => ['problems' => [0]],
            6 => ['problems' => [0, 1]],
            7 => ['problems' => [0, 1, 2]],
        ];

        //Send submissions
        foreach ($submissions as $identityIndex => $problemsOfIdentity) {
            foreach ($problemsOfIdentity['problems'] as $problemIndex) {
                $run = \OmegaUp\Test\Factories\Run::createRun(
                    $problems[$problemIndex],
                    $contestData,
                    $identities[$identityIndex]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);
            }
        }

        //Finish the contest to generate certificates
        $contestData['contest']->finish_time = \OmegaUp\Time::get() - 1;
        \OmegaUp\DAO\Contests::update($contestData['contest']);

        $certificatesCutoff = 4;

        $response = \OmegaUp\Controllers\Certificate::apiGenerateContestCertificates(
            new \OmegaUp\Request([
                'auth_token' => $loginIdentity->auth_token,
                'contest_id' => $contestData['contest']->contest_id,
                'certificates_cutoff' => $certificatesCutoff
            ])
        );

        //Assert status of new contest
        $this->assertSame('ok', $response['status']);

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $this->assertEquals(4, $contest->certificate_cutoff);

        $certificates = \OmegaUp\DAO\Certificates::getAll();
        $this->assertCount(0, $certificates);

        \OmegaUp\Test\Utils::runGenerateContestCertificates();

        $certificates = \OmegaUp\DAO\Certificates::getAll();
        $this->assertCount(8, $certificates);
    }

    /**
     * Try to generate certificates in a contest as a normal user
     */
    public function testGenerateContestCertificatesAsNormalUser() {
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
    }

    /**
     * Try to generate certificates in a contest as a certificate generator
     * but not as a contest admin
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

        $certificatesCutoff = 3;

        try {
            \OmegaUp\Controllers\Certificate::apiGenerateContestCertificates(
                new \OmegaUp\Request([
                    'auth_token' => $loginIdentity->auth_token,
                    'contest_id' => $contestData['contest']->contest_id,
                    'certificates_cutoff' => $certificatesCutoff
                ])
            );
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Try to generate certificates in a contest as a contest admin
     * but not as a certificate generator
     */
    public function testGenerateContestCertificatesOnlyAsContestAdmin() {
        //Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $loginIdentity = self::login($identity);

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

        try {
            \OmegaUp\Controllers\Certificate::apiGenerateContestCertificates(
                new \OmegaUp\Request([
                    'auth_token' => $loginIdentity->auth_token,
                    'contest_id' => $contestData['contest']->contest_id,
                    'certificates_cutoff' => $certificatesCutoff
                ])
            );
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Try to generate certificates in a contest that hasn't ended
     */
    public function testGenerateCurrentContestCertificates() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $loginIdentity = self::login($identity);

        //add role certificate generator to identity user
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'username' => $identity->username,
            'role' => 'CertificateGenerator'
        ]));

        //create a contest that hasn't ended
        $currentTime = \OmegaUp\Time::get();
        $timePast =  new \OmegaUp\Timestamp($currentTime - 60 * 60);
        $timeFuture =  new \OmegaUp\Timestamp($currentTime + 60 * 60);
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'alias' => 'pasado',
                'startTime' => $timePast,
                'finishTime' => $timeFuture,
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

        $this->assertSame('error', $response['status']);
    }
}
