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
        ['identity' => $certificateGenerator] = \OmegaUp\Test\Factories\User::createUser();

        //Create users
        $identities = [];
        for ($i = 0; $i < 8; $i++) {
            ['identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser();
        }

        //login
        $loginIdentity = self::login($certificateGenerator);

        //add role certificate generator to identity user
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'username' => $certificateGenerator->username,
            'role' => 'CertificateGenerator'
        ]));

        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'public']
            )
        );

        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contestData,
            $certificateGenerator
        );

        // Create and add problems to the contest
        $problems = [];
        for ($i = 0; $i < 5; $i++) {
            $problems[$i] = \OmegaUp\Test\Factories\Problem::createProblem();

            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problems[$i],
                $contestData
            );
        }

        $submissions = [[0, 1, 2, 3, 4], [1], [0, 1, 2, 3], [2], [3], [0], [0, 1], [0, 1, 2]];

        // Send submissions
        for ($i = 0; $i < sizeof($submissions); $i++) {
            for ($k = 0; $k < sizeof($submissions[$i]); $k++) {
                $run = \OmegaUp\Test\Factories\Run::createRun(
                    $problems[$submissions[$i][$k]],
                    $contestData,
                    $identities[$i]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);
            }
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
