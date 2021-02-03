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
        $outputFilesContent = [
            'easy.00.out' => '3',
            'easy.01.out' => '5',
            'medium.00.out' => '300',
            'medium.01.out' => '6912',
            'sample.out' => '3',
        ];

        \OmegaUp\Test\Factories\Run::gradeRun(
            $runData,
            /*$points=*/1,
            /*$verdict=*/'AC',
            /*$submitDelay=*/50,
            /*$runGuid=*/null,
            /*$runID*/null,
            /*$problemsetPoints*/100,
            \OmegaUp\Test\Utils::zipFileForContents($outputFilesContent)
        );

        ob_start();
        try {
            \OmegaUp\Controllers\Run::apiDownload(new \OmegaUp\Request([
                'run_alias' => $runData['response']['guid'],
                'auth_token' => $login->auth_token,
                'show_diff' => true,
            ]));
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // This is expected.
        }
        $zipFile = tmpfile();
        $zipPath = stream_get_meta_data($zipFile)['uri'];
        file_put_contents($zipPath, ob_get_contents());
        ob_end_clean();

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::RDONLY) !== true) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        foreach ($outputFilesContent as $file => $fileContent) {
            $this->assertEquals($zip->statName($file)['name'], $file);
            $fp = $zip->getStream($file);
            if (!$fp) {
                throw new \OmegaUp\Exceptions\NotFoundException();
            }
            $content = '';
            while (!feof($fp)) {
                $content .= fread($fp, 1024);
            }
            fclose($fp);

            $this->assertEquals($content, $fileContent);
        }
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
            'languages' => 'c11-gcc',
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
            'languages' => 'c11-gcc',
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

    /**
     * A PHPUnit data provider for the test with valid show_diff values.
     *
    * @return list<array{0: string, 1: array<string, array<string, string>>}>
     */
    public function showDiffValueProvider(): array {
        return [
            ['none', []],
            ['examples', ['sample' => ['in' => "1 2\n", 'out' => "3\n"]]],
            ['all', [
                    'easy.00' => ['in' => "1 2\n", 'out' => "3\n"],
                    'easy.01' => ['in' => "2 3\n", 'out' => "5\n"],
                    'medium.00' => ['in' => "100 200\n", 'out' => "300\n"],
                    'medium.01' => ['in' => "1234 5678\n", 'out' => "6912\n"],
                    'sample' => ['in' => "1 2\n", 'out' => "3\n"],
                ],
            ],
        ];
    }

    /**
     * @param array<string, array<string, string>> $cases
     * @dataProvider showDiffValueProvider
     */
    public function testRunDetailsForProblemWithValidShowDiffValues(
        string $showDiffValue,
        array $cases
    ) {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'show_diff' => $showDiffValue,
            ])
        );
        $login = self::login($this->identity);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $this->identity,
            $login
        );
        $outputFilesContent = [
            'easy.00.out' => '3',
            'easy.01.out' => '5',
            'medium.00.out' => '300',
            'medium.01.out' => '6912',
            'sample.out' => '3',
        ];

        \OmegaUp\Test\Factories\Run::gradeRun(
            $runData,
            /*$points=*/1,
            /*$verdict=*/'AC',
            /*$submitDelay=*/50,
            /*$runGuid=*/null,
            /*$runID*/null,
            /*$problemsetPoints*/100,
            \OmegaUp\Test\Utils::zipFileForContents($outputFilesContent)
        );

        $response = \OmegaUp\Controllers\Run::apiDetails(
            new \OmegaUp\Request([
                'run_alias' => $runData['response']['guid'],
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertEquals($response['cases'], $cases);
    }

    public function testRunDetailsCasesAreHiddenWhenFileIsLargerThan4KB() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'bigtestproblem.zip',
                'show_diff' => 'all',
            ])
        );
        $login = self::login($this->identity);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $this->identity,
            $login
        );
        $outputFilesContent = [
            'easy.00.out' => '3',
            'easy.01.out' => '5',
            'medium.00.out' => '300',
            'medium.01.out' => '6912',
            'sample.out' => '3',
        ];

        \OmegaUp\Test\Factories\Run::gradeRun(
            $runData,
            /*$points=*/1,
            /*$verdict=*/'AC',
            /*$submitDelay=*/50,
            /*$runGuid=*/null,
            /*$runID*/null,
            /*$problemsetPoints*/100,
            \OmegaUp\Test\Utils::zipFileForContents($outputFilesContent)
        );

        $response = \OmegaUp\Controllers\Run::apiDetails(
            new \OmegaUp\Request([
                'run_alias' => $runData['response']['guid'],
                'auth_token' => $login->auth_token,
            ])
        );

        // Cases are not visible, because of the size file restrictions
        $this->assertEquals($response['show_diff'], 'none');
        $this->assertEquals($response['cases'], []);
    }
}
