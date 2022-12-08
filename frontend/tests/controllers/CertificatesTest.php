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
    }

    /**
     * Generate certificates in a contest
     *
     */
    public function testConnectionRabbitMQ() {
        //Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        //Create users
        ['identity' => $test1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $test2] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $test3] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $test4] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $test5] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $test6] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $test7] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $test8] = \OmegaUp\Test\Factories\User::createUser();

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

        // Create problems
        $problems = [];
        for ($i = 0; $i < 5; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        // Add problems to the contest
        for ($i = 0; $i < 5; $i++) {
            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problems[$i],
                $contestData
            );
        }

        /// send submissions test1
        for ($i = 0; $i < 5; $i++) {
            $run = \OmegaUp\Test\Factories\Run::createRun(
                $problems[$i],
                $contestData,
                $test1
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);
        }
        /// send submissions test2
        $run = \OmegaUp\Test\Factories\Run::createRun(
            $problems[1],
            $contestData,
            $test2
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);

        /// send submissions test3
        for ($i = 0; $i < 4; $i++) {
            $run = \OmegaUp\Test\Factories\Run::createRun(
                $problems[$i],
                $contestData,
                $test3
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);
        }

        /// send submissions test4
        $run = \OmegaUp\Test\Factories\Run::createRun(
            $problems[2],
            $contestData,
            $test4
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);

        /// send submissions test5
        $run = \OmegaUp\Test\Factories\Run::createRun(
            $problems[3],
            $contestData,
            $test5
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);

        /// send submissions test6
        $run = \OmegaUp\Test\Factories\Run::createRun(
            $problems[0],
            $contestData,
            $test6
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);

        /// send submissions test7
        for ($i = 0; $i < 2; $i++) {
            $run = \OmegaUp\Test\Factories\Run::createRun(
                $problems[$i],
                $contestData,
                $test7
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);
        }
        /// send submissions test8
        for ($i = 0; $i < 3; $i++) {
            $run = \OmegaUp\Test\Factories\Run::createRun(
                $problems[$i],
                $contestData,
                $test8
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);
        }

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

        $this->assertEquals(4, $contest->certificate_cutoff);
    }

    /**
     * try to generate certificates in a contest as a normal user
     *
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
}
