<?php

/**
 * Description of RunDetailsTest
 *
 * @author juan.pablo
 */

class RunDetailsTest extends \OmegaUp\Test\ControllerTestCase {
    protected $contestData;
    protected $admin;
    protected $problemData;
    protected $identity;

    public function setUp(): void {
        parent::setUp();

        // Get a contest
        $this->contestData = \OmegaUp\Test\Factories\Contest::createContest();

        $this->admin = $this->contestData['director'];
        $adminLogin = self::login($this->admin);

        // Get a problem
        $this->problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            /*$params=*/            null,
            $adminLogin
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $this->problemData,
            $this->contestData
        );

        // Create contestant
        [
            'identity' => $this->identity
        ] = \OmegaUp\Test\Factories\User::createUser();
    }

    private function assertCanSeeRunDetails(
        string $guid,
        \OmegaUp\DAO\VO\Identities $user
    ) {
        $login = self::login($user);

        $response = \OmegaUp\Controllers\Run::apiDetails(
            new \OmegaUp\Request([
                'run_alias' => $guid,
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertArrayHasKey('details', $response);
    }

    private function assertCanNotSeeRunDetails(
        string $guid,
        \OmegaUp\DAO\VO\Identities $user
    ) {
        $login = self::login($user);

        $response = \OmegaUp\Controllers\Run::apiDetails(
            new \OmegaUp\Request([
                'run_alias' => $guid,
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertArrayNotHasKey('details', $response);
    }

    /**
     * A PHPUnit data provider for all the tests that can accept feedback.
     *
     * @return list<list<array{0: string, 1: bool, 2: bbol, 3: bool}>>
     */
    public function contestUserFeedbackProvider(): array {
        return [
            // feedback, solved, before contest end, can see details
            ['none', false, false],
            ['none', false, false],
            ['none', true, false],
            ['summary', false, true],
            ['summary', false, true],
            ['summary', true, true],
            ['summary', true, true],
            ['detailed', false, true],
            ['detailed', false, true],
            ['detailed', true, true],
            ['detailed', true, true],
        ];
    }

    /**
     * A PHPUnit data provider for all the tests that can accept feedback.
     *
     * @return list<list<string>>
     */
    public function contestAdminFeedbackProvider(): array {
        return [
            ['none'],
            ['summary'],
            ['detailed'],
        ];
    }

    /**
     * Admin always can see run details for all submissions
     */
    public function testAdminOutOfContest() {
        $adminLogin = self::login($this->admin);
        $login = self::login($this->identity);

        $waRunData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $this->problemData,
            $this->identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($waRunData, 0, 'WA', 60);

        $this->assertCanSeeRunDetails(
            $waRunData['response']['guid'],
            $this->admin
        );

        $acRunData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $this->problemData,
            $this->identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($acRunData, 1, 'AC', 65);

        $this->assertCanSeeRunDetails(
            $acRunData['response']['guid'],
            $this->admin
        );
    }

    public function testDownload() {
        $adminLogin = self::login($this->admin);
        $login = self::login($this->identity);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $this->problemData,
            $this->identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 1, 'AC', 50);

        ob_start();
        \OmegaUp\Controllers\Run::downloadSubmission(
            $runData['response']['guid'],
            $this->identity,
            /*$passthru=*/true,
            /*$skipAuthorization=*/true
        );
        $zipContents = ob_get_contents();
        ob_end_clean();

        $this->assertTrue(true);
    }

    /**
     * User only can see run details for submissions when gets the verdict AC
     */
    public function testUserOutOfContest() {
        $login = self::login($this->identity);

        $waRunData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $this->problemData,
            $this->identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($waRunData, 0, 'WA', 60);

        $this->assertCanNotSeeRunDetails(
            $waRunData['response']['guid'],
            $this->identity
        );

        $login = self::login($this->identity);

        $acRunData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $this->problemData,
            $this->identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($acRunData, 1, 'AC', 65);

        $this->assertCanSeeRunDetails(
            $acRunData['response']['guid'],
            $this->identity
        );
        $this->assertCanSeeRunDetails(
            $waRunData['response']['guid'],
            $this->identity
        );
    }

    /**
     * Admin always can see run details for all submissions
     *
     * @dataProvider contestAdminFeedbackProvider
     */
    public function testAdminInContest(string $feedback) {
        $adminLogin = self::login($this->admin);
        $login = self::login($this->identity);

        // Call API
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'contest_alias' => $this->contestData['request']['alias'],
            'feedback' => $feedback,
        ]));

        $waRunData = \OmegaUp\Test\Factories\Run::createRun(
            $this->problemData,
            $this->contestData,
            $this->identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($waRunData, 0, 'WA', 60);

        $this->assertCanSeeRunDetails(
            $waRunData['response']['guid'],
            $this->admin
        );

        // Two minutes later
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60 * 2);

        $acRunData = \OmegaUp\Test\Factories\Run::createRun(
            $this->problemData,
            $this->contestData,
            $this->identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($acRunData, 1, 'AC', 60);

        $this->assertCanSeeRunDetails(
            $acRunData['response']['guid'],
            $this->admin
        );

        // Asserts contest alias in problem details is the same that the provided
        $this->assertEquals(
            $this->contestData['request']['alias'],
            $acRunData['details']['runs'][0]['contest_alias']
        );
    }

    /**
     * User can see run details in contest only when feedback is not set 'none'
     *
     * @dataProvider contestUserFeedbackProvider
     */
    public function testUserInContest(
        string $feedback,
        bool $solved,
        bool $canSeeDetails
    ) {
        $adminLogin = self::login($this->admin);
        $login = self::login($this->identity);

        // Call API
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'contest_alias' => $this->contestData['request']['alias'],
            'feedback' => $feedback,
        ]));

        $waRunData = \OmegaUp\Test\Factories\Run::createRun(
            $this->problemData,
            $this->contestData,
            $this->identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($waRunData, 0, 'WA', 60);

        if ($canSeeDetails) {
            $this->assertCanSeeRunDetails(
                $waRunData['response']['guid'],
                $this->identity
            );
        } else {
            $this->assertCanNotSeeRunDetails(
                $waRunData['response']['guid'],
                $this->identity
            );
        }

        // Two minutes later
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60 * 2);

        $acRunData = \OmegaUp\Test\Factories\Run::createRun(
            $this->problemData,
            $this->contestData,
            $this->identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($acRunData, 1, 'AC', 60);

        if ($canSeeDetails) {
            $this->assertCanSeeRunDetails(
                $acRunData['response']['guid'],
                $this->identity
            );
        } else {
            $this->assertCanNotSeeRunDetails(
                $acRunData['response']['guid'],
                $this->identity
            );
        }

        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 7200);
        // The result is always visible because contestant got AC verdict at least once
        $this->assertCanSeeRunDetails(
            $acRunData['response']['guid'],
            $this->identity
        );

        // Asserts contest alias in problem details is the same that the provided
        $this->assertEquals(
            $this->contestData['request']['alias'],
            $acRunData['details']['runs'][0]['contest_alias']
        );
    }
}
