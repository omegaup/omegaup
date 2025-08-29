<?php
/**
 * Description of CertificatesTest
 */
class CertificatesTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * @param  int     $numOfIdentities
     * @param  int     $numOfProblems
     * @param  string  $admissionMode
     * @param  int     $certificatesCutoff
     * @param  array   $submissions
     * @param  array   $places
     */
    private function createContestCertificates(
        int $numOfIdentities,
        int $numOfProblems,
        string $admissionMode,
        int $certificatesCutoff,
        array $submissions,
        array $places
    ) {
        //Create the certificate generator
        ['identity' => $supportTeamMember] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $certificateGenerator] = \OmegaUp\Test\Factories\User::createUser();

        //Add role certificate generator
        $loginSupport = self::login($supportTeamMember);
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginSupport->auth_token,
            'username' => $certificateGenerator->username,
            'role' => 'CertificateGenerator'
        ]));

        //Create a contest that hasn't ended to send submissions
        $currentTime = \OmegaUp\Time::get();
        $timePast =  new \OmegaUp\Timestamp($currentTime - 60 * 60);
        $timeFuture =  new \OmegaUp\Timestamp($currentTime + 60 * 60);
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => $admissionMode,
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
        for ($i = 0; $i < $numOfProblems; $i++) {
            $problems[$i] = \OmegaUp\Test\Factories\Problem::createProblem();

            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problems[$i],
                $contestData
            );
        }

        //Create contestants and add them to the contest
        $identities = [];
        $placesWithIdentityId = [];
        foreach (range(0, $numOfIdentities - 1) as $index) {
            ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
            $identities[$index] = $identity;
            \OmegaUp\Test\Factories\Contest::addUser(
                $contestData,
                $identity
            );
            $placesWithIdentityId[$identity->identity_id] = $places[$index];
        }

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

        // Now, the contest has finished, and we can generate the certificates
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + (2 * 60 * 60));

        \OmegaUp\Test\Utils::runInitializeRabbitmq(
            queue: 'contest',
            exchange: 'certificates',
            routingKey: 'ContestQueue'
        );

        //Send the message to RabbitMQ using the API
        $loginIdentity = self::login($certificateGenerator);
        $response = \OmegaUp\Controllers\Certificate::apiGenerateContestCertificates(
            new \OmegaUp\Request([
                'auth_token' => $loginIdentity->auth_token,
                'contest_alias' => $contestData['contest']->alias,
                'certificates_cutoff' => $certificatesCutoff
            ])
        );

        $this->assertSame('ok', $response['status']);

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $this->assertEquals($certificatesCutoff, $contest->certificate_cutoff);

        $certificates = \OmegaUp\DAO\Certificates::countAll();
        $this->assertSame(0, $certificates);
        $notifications = \OmegaUp\DAO\Notifications::countAll();
        $this->assertSame(0, $notifications);

        //Adds the certificates only for one contest to the database,
        //so it must be called after each successful call to the API
        \OmegaUp\Test\Utils::runGenerateContestCertificates();

        $certificates = \OmegaUp\DAO\Certificates::countAll();
        //Should add one certificate per contestant
        $this->assertSame($numOfIdentities, $certificates);

        $notifications = \OmegaUp\DAO\Notifications::countAll();
        //Should add one notification per contestant
        $this->assertSame($numOfIdentities, $notifications);

        $certificates = \OmegaUp\DAO\Certificates::getAll();
        $notifications = \OmegaUp\DAO\Notifications::getAll();

        //Check the certificates data
        $verificationCodes = [];
        foreach ($certificates as $certificate) {
            $this->assertSame(
                $placesWithIdentityId[$certificate->identity_id],
                $certificate->contest_place
            );
            $this->assertSame('contest', $certificate->certificate_type);
            $this->assertSame(
                $contestData['contest']->contest_id,
                $certificate->contest_id
            );

            $identity = \OmegaUp\DAO\Identities::getByPK(
                $certificate->identity_id
            );
            $verificationCodes[$identity->user_id] = $certificate->verification_code;
        }

        //Check the notifications data
        foreach ($notifications as $notification) {
            $contents = json_decode($notification->contents, true);
            $this->assertSame(
                \OmegaUp\DAO\Notifications::CERTIFICATE_AWARDED,
                $contents['type']
            );
            $this->assertEquals(
                'notificationNewContestCertificate',
                $contents['body']['localizationString']
            );
            $this->assertEquals(
                $contestData['contest']->title,
                $contents['body']['localizationParams']['contest_title']
            );
            $this->assertEquals(
                "/certificates/mine/#{$verificationCodes[$notification->user_id]}",
                $contents['body']['url']
            );
        }
    }

    /**
     * Generate certificates in a public contest
     */
    public function testGeneratePublicContestCertificates() {
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

        $places = [
            0 => 1,
            1 => null,
            2 => 2,
            3 => null,
            4 => null,
            5 => null,
            6 => 4,
            7 => 3,
        ];

        $this->createContestCertificates(
            numOfIdentities: 8,
            numOfProblems: 5,
            admissionMode: 'public',
            certificatesCutoff: 4,
            submissions: $submissions,
            places: $places
        );
    }

    /**
     * Generate certificates in a private contest
     */
    public function testGeneratePrivateContestCertificates() {
        //Associate the identity index with
        //the problem indexes they are going to send
        $submissions = [
            0 => ['problems' => [0, 1, 2, 3, 4, 5]],
            1 => ['problems' => [1]],
            2 => ['problems' => [0, 1, 2, 3]],
            3 => ['problems' => [3]],
            4 => ['problems' => [0, 1]],
            5 => ['problems' => [0, 1, 2]],
        ];

        $places = [
            0 => 1,
            1 => null,
            2 => 2,
            3 => null,
            4 => null,
            5 => 3,
        ];

        $this->createContestCertificates(
            numOfIdentities: 6,
            numOfProblems: 6,
            admissionMode: 'private',
            certificatesCutoff: 3,
            submissions: $submissions,
            places: $places
        );
    }

    /**
     * Try to generate certificates in a contest as a normal user
     */
    public function testGenerateContestCertificatesAsNormalUser() {
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

        $certificatesCutoff = 3;

        try {
            \OmegaUp\Controllers\Certificate::apiGenerateContestCertificates(
                new \OmegaUp\Request([
                    'auth_token' => $loginIdentity->auth_token,
                    'contest_alias' => $contestData['contest']->alias,
                    'certificates_cutoff' => $certificatesCutoff
                ])
            );
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Try to generate certificates in a contest as a certificate generator
     * but not as a contest admin
     */
    public function testGenerateContestCertificatesOnlyAsCertificateGenerator() {
        ['identity' => $supportTeamMember] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $loginSupport = self::login($supportTeamMember);
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginSupport->auth_token,
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
            $loginIdentity = self::login($identity);
            \OmegaUp\Controllers\Certificate::apiGenerateContestCertificates(
                new \OmegaUp\Request([
                    'auth_token' => $loginIdentity->auth_token,
                    'contest_alias' => $contestData['contest']->alias,
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
                    'contest_alias' => $contestData['contest']->alias,
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
        ['identity' => $supportTeamMember] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        //add role certificate generator to identity user
        $loginSupport = self::login($supportTeamMember);
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginSupport->auth_token,
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

        try {
            $loginIdentity = self::login($identity);
            \OmegaUp\Controllers\Certificate::apiGenerateContestCertificates(
                new \OmegaUp\Request([
                    'auth_token' => $loginIdentity->auth_token,
                    'contest_alias' => $contestData['contest']->alias,
                    'certificates_cutoff' => $certificatesCutoff
                ])
            );
            $this->fail('Should have thrown a InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'contestCertificatesCurrentContestError',
                $e->getMessage()
            );
        }
    }
}
