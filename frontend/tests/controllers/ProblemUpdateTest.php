<?php
/**
 * Description of ProblemUpdateTest
 */

class ProblemUpdateTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting(
            $this->createFileUploaderMock()
        );
    }

    /**
     * A PHPUnit data provider for the test with valid show_diff values.
     *
     * @return list<list<string>>
     */
    public function showDiffValueProvider(): array {
        return [
            ['none'],
            ['examples'],
            ['all'],
        ];
    }

    public function testProblemUpdateLanguages() {
        // Get a problem (with 'es' statements)
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip',
            'title' => 'Problem Language'
        ]));

        // Update statement
        $login = self::login($problemData['author']);

        $problemLanguages = \OmegaUp\DAO\ProblemsLanguages::getByProblemId(
            $problemData['problem']->problem_id
        );
        // This problem only has one language at this point
        $this->assertSame(1, count($problemLanguages));

        \OmegaUp\Controllers\Problem::apiUpdateStatement(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'New statement is now more fun',
            'statement' => 'This is the new statement',
            'lang' => 'en'
        ]));

        // The problem has two languages at this point
        $problemLanguages = \OmegaUp\DAO\ProblemsLanguages::getByProblemId(
            $problemData['problem']->problem_id
        );
        $this->assertSame(2, count($problemLanguages));
    }

    /**
     * Test for updating the level of a problem
     */
    public function testUpdateProblemLevel() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemAuthor = $problemData['author'];
        $login = self::login($problemAuthor);

        $problemLevel = \OmegaUp\DAO\ProblemsTags::getProblemLevel(
            $problemData['problem']
        );
        $this->assertSame(
            $problemLevel,
            'problemLevelBasicIntroductionToProgramming'
        );

        $selectedLevel = 'problemLevelBasicKarel';
        \OmegaUp\Controllers\Problem::apiUpdateProblemLevel(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['problem']->alias,
            'level_tag' => $selectedLevel,
        ]));
        $problemLevel = \OmegaUp\DAO\ProblemsTags::getProblemLevel(
            $problemData['problem']
        );
        $this->assertSame($selectedLevel, $problemLevel);

        $selectedLevel = 'problemLevelBasicIntroductionToProgramming';
        \OmegaUp\Controllers\Problem::apiUpdateProblemLevel(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['problem']->alias,
            'level_tag' => $selectedLevel,
        ]));
        $problemLevel = \OmegaUp\DAO\ProblemsTags::getProblemLevel(
            $problemData['problem']
        );
        $this->assertSame($selectedLevel, $problemLevel);
    }

    public function testUpdateProblemTitleAndContents() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $runData = [];
        // Create a run
        $runData[0] = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runData[1] = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );

        // Grade the run
        \OmegaUp\Test\Factories\Run::gradeRun($runData[0]);
        \OmegaUp\Test\Factories\Run::gradeRun($runData[1]);

        // Update Problem calls grader to rejudge, we need to detour grader calls
        // We will submit 2 runs to the problem, a call to grader to rejudge them
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Set file upload context
        $login = self::login($problemData['author']);
        $_FILES['problem_contents']['tmp_name'] = OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip';
        $newTitle = 'new title';
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'title' => $newTitle,
            'time_limit' => 12345,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Changed some properties',
        ]));
        $this->assertTrue($response['rejudged']);
        unset($_FILES['problem_contents']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($newTitle);

        // Check that we only retrieved 1 element
        $this->assertSame(1, count($problems));

        {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts(
                $problemData['request']['problem_alias']
            );

            // Verify problem contents were copied
            $this->assertTrue($problemArtifacts->exists('cases'));
            $this->assertTrue(
                $problemArtifacts->exists(
                    'statements/es.markdown'
                )
            );
            $this->assertFalse($problemArtifacts->exists('examples/sample.in'));

            // Check update in statements
            $statement = $problemArtifacts->get('statements/es.markdown');
            $this->assertStringContainsString('perímetro', $statement);

            $problemDistribSettings = json_decode(
                $problemArtifacts->get('settings.distrib.json'),
                associative: true,
            );

            // This example comes from the problem statement.
            $this->assertSame(
                [
                    'statement_001' => [
                        'in' => "6\n2 3 2 3 2 4",
                        'out' => '10',
                        'weight' => 1,
                    ],
                ],
                $problemDistribSettings['cases']
            );
        }

        // Call API again to add an example, should not trigger rejudge.
        $_FILES['problem_contents']['tmp_name'] = OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos-examples.zip';
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Add example',
        ]));
        $this->assertFalse($response['rejudged']);
        unset($_FILES['problem_contents']);
        {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts(
                $problemData['request']['problem_alias']
            );

            // Verify problem contents were copied
            $this->assertTrue($problemArtifacts->exists('cases'));
            $this->assertTrue(
                $problemArtifacts->exists(
                    'statements/es.markdown'
                )
            );
            $this->assertTrue($problemArtifacts->exists('examples/sample.in'));

            // Check update in statements
            $statement = $problemArtifacts->get('statements/es.markdown');
            $this->assertStringContainsString('perímetro', $statement);

            $problemDistribSettings = json_decode(
                $problemArtifacts->get('settings.distrib.json'),
                associative: true,
            );
            $this->assertSame(
                [
                    'sample' => [
                        'in' => "6\n2 3 2 3 2 4\n",
                        'out' => "10\n",
                        'weight' => 1
                    ],
                ],
                $problemDistribSettings['cases']
            );
        }

        $this->assertSame(2, $detourGrader->getGraderCallCount());
    }

    public function testUpdateProblemSettings() {
        // Get a problem with a run.
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemAlias = $problemData['request']['problem_alias'];
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $runData = [];
        $runData[0] = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData[0]);

        {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts($problemAlias);
            $this->assertTrue($problemArtifacts->exists('cases'));
            $this->assertTrue(
                $problemArtifacts->exists(
                    'statements/es.markdown'
                )
            );
            $problemSettings = json_decode(
                $problemArtifacts->get(
                    'settings.json'
                )
            );
            $this->assertSame(
                3,
                count($problemSettings->Cases)
            );
            $this->assertSame(
                ($problemData['request']['time_limit'] / 1000) . 's',
                $problemSettings->Limits->TimeLimit
            );
        }

        // Update Problem calls grader to rejudge, we need to detour grader calls
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Call API to update time limit.
        $newTimeLimit = 12345;
        $login = self::login($problemData['author']);
        unset($_FILES['problem_contents']);
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'time_limit' => $newTimeLimit,
            'problem_alias' => $problemAlias,
            'message' => 'Increased time limit',
        ]));
        $this->assertTrue($response['rejudged']);
        unset($_FILES['problem_contents']);

        // Verify problem settings were set.
        {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts($problemAlias);
            $this->assertTrue($problemArtifacts->exists('cases'));
            $this->assertTrue(
                $problemArtifacts->exists(
                    'statements/es.markdown'
                )
            );
            $problemSettings = json_decode(
                $problemArtifacts->get(
                    'settings.json'
                )
            );
            $this->assertSame(
                3,
                count($problemSettings->Cases)
            );
            $this->assertSame(
                ($newTimeLimit / 1000.0) . 's',
                $problemSettings->Limits->TimeLimit
            );
        }

        $this->assertSame(1, $detourGrader->getGraderCallCount());
    }

    public function testUpdateValidatorTimeout() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos_validator.zip',
                'validator' => 'custom',
            ])
        );
        $problemAlias = $problemData['request']['problem_alias'];

        // Call API to update time limit.
        $newTimeLimit = 12345;
        $login = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'validator_time_limit' => $newTimeLimit,
            'problem_alias' => $problemAlias,
            'message' => 'Changed time limit',
        ]));
        $this->assertTrue($response['rejudged']);

        // Verify problem settings were set.
        {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts($problemAlias);
            $problemSettings = json_decode(
                $problemArtifacts->get(
                    'settings.json'
                ),
                associative: true
            );
            $this->assertSame(
                ($newTimeLimit / 1000.0) . 's',
                $problemSettings['Validator']['Limits']['TimeLimit']
            );
        }
    }

    public function testUpdateProblemWithValidLanguages() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'title' => 'valid-languages'
        ]));

        $languages = 'hs,java';

        // Call API
        $login = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'languages' => $languages,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Changed alias and languages',
        ]));
        $this->assertFalse($response['rejudged']);

        // Verify data in DB
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemData['request']['problem_alias']
        );

        // Check that we only retrieved 1 element
        $this->assertNotNull($problem);
        $this->assertSameSets($languages, $problem->languages);
    }

    public function testUpdateProblemWithInvalidLanguages() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $login = self::login($problemData['author']);

        try {
            \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'languages' => 'cows,hs,java,pl',
                'problem_alias' => $problemData['request']['problem_alias'],
                'message' => 'Changed invalid languages',
            ]));
            $this->fail('Expected update to fail');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'parameterNotInExpectedSet',
                $e->getMessage()
            );
        }
    }

    /**
     * Test apiUpdateStatement
     */
    public function testProblemStatementUpdate() {
        // Get a problem (with 'es' statements)
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip'
        ]));

        // Update statement
        $statement = 'This is the new statement \$x\$';
        $login = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiUpdateStatement(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Statement is now more fun',
            'statement' => $statement
        ]));

        $this->assertSame('ok', $response['status']);

        // Check statement contents
        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            $problemData['request']['problem_alias']
        );

        $statementMarkdownContents = $problemArtifacts->get(
            'statements/es.markdown'
        );

        $this->assertStringContainsString(
            $statement,
            $statementMarkdownContents
        );
    }

    /**
     * Test apiUpdateSolution
     */
    public function testProblemSolutionUpdate() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Update solution
        $solution = 'La nueva solución \$x\$';
        $login = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiUpdateSolution(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Solution modified for test.',
            'solution' => $solution,
        ]));

        $this->assertSame('ok', $response['status']);

        // Check solution contents
        $response = \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $this->assertStringContainsString(
            $solution,
            $response['solution']['markdown']
        );
    }

    /**
     * Test apiUpdateStatement with embedded imgs via data URI
     */
    public function testProblemStatementUpdateWithImagesAsDataURI() {
        // Get a problem (with 'es' statements)
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip'
        ]));

        // Update statement
        $imgUri = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==';

        $statement = "This is the new statement with an image omg ![Alt text]($imgUri \"Optional title\")\n";
        $login = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiUpdateStatement(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Statement now contains images',
            'statement' => $statement
        ]));

        $this->assertSame('ok', $response['status']);

        // Check statement contents
        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            $problemData['request']['problem_alias']
        );
        $statementMarkdownContents = $problemArtifacts->get(
            'statements/es.markdown'
        );

        $this->assertSame($statement, $statementMarkdownContents);
    }

    /**
     * Tests update problem: on error, original contents should persist
     */
    public function testUpdateProblemFailed() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Update Problem calls grader to rejudge, we need to detour grader calls
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Call API. Should fail
        try {
            $_FILES['problem_contents']['tmp_name'] = OMEGAUP_TEST_RESOURCES_ROOT . 'nostmt.zip';
            $login = self::login($problemData['author']);
            \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'title' => 'new title',
                'time_limit' => 12345,
                'problem_alias' => $problemData['request']['problem_alias'],
                'message' => 'This should fail',
            ]));
            $this->fail('Expected update to fail');
        } catch (\OmegaUp\Exceptions\ProblemDeploymentFailedException $e) {
            $this->assertSame(
                'problemDeployerNoStatements',
                $e->getMessage()
            );
        } finally {
            unset($_FILES['problem_contents']);
        }

        // Verify contents were not erased
        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            $problemData['request']['problem_alias']
        );

        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/es.markdown'));

        // Check statements still is the original one
        $statement = $problemArtifacts->get('statements/es.markdown');
        $this->assertStringContainsString('# Entrada', $statement);

        $this->assertSame(0, $detourGrader->getGraderCallCount());
    }

    /**
     * Tests problem admin can edit a problem
     */
    public function testUpdateProblemWithProblemAdmin() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create our new admin
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add admin to the problem
        $adminLogin = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiAddAdmin(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $this->assertSame('ok', $response['status']);

        //Call API
        $newTitle = 'new title coadmin';
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'title' => $newTitle,
            'message' => 'Admin powers',
        ]));
        $this->assertFalse($response['rejudged']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($newTitle);

        $this->assertTrue(!is_null($problems));
    }

    /**
     * Tests removed problem admin can't edit a problem anymore
     */
    public function testUpdateProblemWithRemovedProblemAdmin() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create our new admin
        ['identity' => $problemAdmin] = \OmegaUp\Test\Factories\User::createUser();

        // Add admin to the problem
        $adminLogin = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiAddAdmin(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $problemAdmin->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $this->assertSame('ok', $response['status']);

        // Then remove the user
        $response = \OmegaUp\Controllers\Problem::apiRemoveAdmin(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $problemAdmin->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertSame('ok', $response['status']);

        //Call API
        $login = self::login($problemAdmin);
        try {
            \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'title' => 'new title coadmin',
                'message' => 'Non-admin powers',
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Tests problem admins list API
     */
    public function testProblemAdmins() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create our new admin
        ['identity' => $problemAdmin] = \OmegaUp\Test\Factories\User::createUser();

        // Add admin to the problem
        $login = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiAddAdmin(new \OmegaUp\Request([
            'usernameOrEmail' => $problemAdmin->username,
            'problem_alias' => $problemData['request']['problem_alias'],
            'auth_token' => $login->auth_token,
        ]));

        $this->assertSame('ok', $response['status']);

        // Get the list of admins
        $response = \OmegaUp\Controllers\Problem::apiAdmins(new \OmegaUp\Request([
            'problem_alias' => $problemData['request']['problem_alias'],
            'auth_token' => $login->auth_token,
        ]));

        $adminFound = false;
        $ownerFound = false;
        foreach ($response['admins'] as $adminEntry) {
            if ($adminEntry['username'] == $problemAdmin->username) {
                $adminFound = true;
            }

            if ($adminEntry['username'] == $problemData['author']->username) {
                $ownerFound = true;
            }
        }
        $this->assertTrue($adminFound);
        $this->assertTrue($ownerFound);
    }

    /**
     * Test reviewers can do some problem-related tasks.
     */
    public function testProblemUpdateByReviewer() {
        // Create a private problem.
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip',
            'visibility' => 'private'
        ]));

        // Normal user shouldn't even be able to see the problem.
        ['user' => $reviewer, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
            $this->fail('Should not have been able to see the problem');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('problemIsPrivate', $e->getMessage());
        }

        // Promote to reviewer, can see the problem now.
        \OmegaUp\Test\Factories\User::addSystemRole(
            $reviewer,
            \OmegaUp\Authorization::REVIEWER_ROLE
        );
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertArrayContainsWithPredicate(
            $response['results'],
            fn ($problem) => $problem['alias'] == $problemData['request']['problem_alias']
        );
        $response = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        // Update statement
        $statement = 'This is the new statement \$x\$';
        $response = \OmegaUp\Controllers\Problem::apiUpdateStatement(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Statement is now more fun',
            'statement' => $statement
        ]));

        // Add a tag
        $response = \OmegaUp\Controllers\Problem::apiAddTag(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'name' => 'test',
        ]));
    }

    public function testAddOnlyPrivateTags() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip',
            'visibility' => 'private'
        ]));
        $login = self::login($problemData['author']);
        \OmegaUp\Controllers\Problem::apiAddTag(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'name' => 'test-tag',
            'public' => false,
        ]));

        $response = \OmegaUp\Controllers\Problem::apiTags(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'name' => 'test-tag',
        ]));
        $this->assertEqualsCanonicalizing(
            [
                [
                    'name' => 'problemLevelBasicIntroductionToProgramming',
                    'public' => true,
                ],
                [
                    'name' => 'problemRestrictedTagLanguage',
                    'public' => true,
                ],
                [
                    'name' => 'test-tag',
                    'public' => false,
                ],
            ],
            $response['tags']
        );
    }

    /**
     * Tests that problems cannot change their visibility under some scenarios.
     */
    public function testProblemUpdateVisibility() {
        // Create a public problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $login = self::login($problemData['author']);
        $problem = $problemData['problem'];

        // Make it private.
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => 'private',
            'message' => 'public -> private',
        ]));
        $this->assertFalse($response['rejudged']);

        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => 'private',
            'message' => 'no-op',
        ]));
        $this->assertFalse($response['rejudged']);
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'message' => 'no-op',
        ]));
        $this->assertFalse($response['rejudged']);

        // Make it public
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => 'public',
            'message' => 'private -> public',
        ]));
        $this->assertFalse($response['rejudged']);

        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => 'public',
            'message' => 'no-op',
        ]));
        $this->assertFalse($response['rejudged']);
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'message' => 'no-op',
        ]));
        $this->assertFalse($response['rejudged']);

        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => 'public_banned',
            'message' => 'public -> banned',
        ]));
        $this->assertFalse($response['rejudged']);

        try {
            \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'visibility' => 'promoted',
                'message' => 'public -> promoted',
            ]));
            $this->fail('Cannot ban problem from API');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                $e->getMessage(),
                'qualityNominationProblemHasBeenBanned'
            );
        }

        // Ban the problem.
        $problem->visibility = 'public_banned';
        \OmegaUp\DAO\Problems::update($problem);

        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => 'public_banned',
            'message' => 'no-op',
        ]));
        $this->assertFalse($response['rejudged']);
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'message' => 'no-op',
        ]));
        $this->assertFalse($response['rejudged']);

        try {
            \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'visibility' => 'private',
                'message' => 'banned -> private',
            ]));
            $this->fail('Cannot un-ban problem from API');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                $e->getMessage(),
                'qualityNominationProblemHasBeenBanned'
            );
        }

        try {
            \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'visibility' => 'public',
                'message' => 'banned -> public',
            ]));
            $this->fail('Cannot un-ban problem from API');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                $e->getMessage(),
                'qualityNominationProblemHasBeenBanned'
            );
        }

        // Promote the problem.
        $problem->visibility = 'promoted';
        \OmegaUp\DAO\Problems::update($problem);

        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => 'promoted',
            'message' => 'no-op',
        ]));
        $this->assertFalse($response['rejudged']);
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'message' => 'no-op',
        ]));
        $this->assertFalse($response['rejudged']);

        try {
            \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'visibility' => 'private',
                'message' => 'promoted -> private',
            ]));
            $this->fail('Cannot un-promote problem from API');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                $e->getMessage(),
                'qualityNominationProblemHasBeenPromoted'
            );
        }

        try {
            \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'visibility' => 'public',
                'message' => 'promoted -> public',
            ]));
            $this->fail('Cannot un-promote problem from API');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                $e->getMessage(),
                'qualityNominationProblemHasBeenPromoted'
            );
        }
    }

    /**
     * Tests tag operations.
     */
    public function testTags() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $login = self::login($problemData['author']);
        $this->assertEqualsCanonicalizing(
            [
                [
                    'name' => 'problemLevelBasicIntroductionToProgramming',
                    'public' => true,
                ],
                [
                    'name' => 'problemRestrictedTagLanguage',
                    'public' => true,
                ],
            ],
            \OmegaUp\Controllers\Problem::apiTags(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ]))['tags']
        );

        \OmegaUp\Controllers\Problem::apiAddTag(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['problem']->alias,
            'name' => 'foo',
            'public' => false,
        ]));
        $this->assertEqualsCanonicalizing(
            [
                [
                    'name' => 'problemLevelBasicIntroductionToProgramming',
                    'public' => true,
                ],
                [
                    'name' => 'problemRestrictedTagLanguage',
                    'public' => true,
                ],
                [
                    'name' => 'foo',
                    'public' => false,
                ],
            ],
            \OmegaUp\Controllers\Problem::apiTags(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ]))['tags']
        );

        \OmegaUp\Controllers\Problem::apiRemoveTag(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['problem']->alias,
            'name' => 'foo',
            'public' => 'true',
        ]));
        $this->assertEqualsCanonicalizing(
            [
                [
                    'name' => 'problemLevelBasicIntroductionToProgramming',
                    'public' => true,
                ],
                [
                    'name' => 'problemRestrictedTagLanguage',
                    'public' => true,
                ],
            ],
            \OmegaUp\Controllers\Problem::apiTags(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ]))['tags']
        );

        try {
            \OmegaUp\Controllers\Problem::apiAddTag(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
                'name' => 'problemTagTestTag',
                'public' => false,
            ]));
            $this->fail('Should have failed.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('tagPrefixRestricted', $e->getMessage());
        }

        try {
            \OmegaUp\Controllers\Problem::apiRemoveTag(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
                'name' => 'problemRestrictedTagLanguage',
                'public' => 'true',
            ]));
            $this->fail('Should not have been able to remove restricted tag');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('tagRestricted', $e->getMessage());
        }

        $testTags = [
            'test-tag-a',
            'test-tag-b',
        ];
        \OmegaUp\Controllers\Problem::apiAddTag(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['problem']->alias,
            'name' => $testTags[0],
            'public' => false,
        ]));

        \OmegaUp\Controllers\Problem::apiAddTag(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['problem']->alias,
            'name' => $testTags[1],
            'public' => false,
        ]));

        $privateTags = \OmegaUp\DAO\ProblemsTags::getTagsForProblem(
            $problemData['problem'],
            !\OmegaUp\Authorization::canEditProblem(
                $problemData['author'],
                $problemData['problem']
            ),
        );
        $this->assertSame($testTags, $privateTags);

        ['identity' => $extraIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $privateTags = \OmegaUp\DAO\ProblemsTags::getTagsForProblem(
            $problemData['problem'],
            !\OmegaUp\Authorization::canEditProblem(
                $extraIdentity,
                $problemData['problem']
            ),
        );
        $this->assertEmpty($privateTags);
    }

    /**
     * Tests problem version update.
     */
    public function testProblemVersionUpdate() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problem = $problemData['problem'];
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 1.0, 'AC');

        $login = self::login($problemData['author']);
        $originalVersionData = \OmegaUp\Controllers\Problem::apiVersions(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
        ]));
        $originalDetails = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
        ]));

        // Change the problem to something completely different.
        {
            $_FILES['problem_contents']['tmp_name'] = OMEGAUP_TEST_RESOURCES_ROOT . 'mrkareltastic.zip';
            $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();
            $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'message' => 'Changed to mrkareltastic',
                'validator' => 'token',
                'time_limit' => 1000,
                'overall_wall_time_limit' => 30000,
                'validator_time_limit' => 0,
                'extra_wall_time' => 1000,
                'memory_limit' => 64000,
                'output_limit' => 20480,
            ]));
            $this->assertTrue($response['rejudged']);
            $this->assertSame(1, $detourGrader->getGraderCallCount());
            unset($_FILES['problem_contents']);
        foreach ($detourGrader->getRuns() as $run) {
            \OmegaUp\Test\Factories\Run::gradeRun(
                null,
                0,
                'WA',
                null,
                null,
                $run->run_id
            );
        }
        }

        $modifiedVersionData = \OmegaUp\Controllers\Problem::apiVersions(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
        ]));
        $this->assertNotEquals(
            $originalVersionData['published'],
            $modifiedVersionData['published']
        );
        $modifiedDetails = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
        ]));
        $this->assertNotEquals(
            $originalDetails['statement'],
            $modifiedDetails['statement']
        );

        // Change it back to the original problem. Should not cause any new
        // rejudges, but current_version should go back to the original.
        {
            $_FILES['problem_contents']['tmp_name'] = OMEGAUP_TEST_RESOURCES_ROOT . 'testproblem.zip';
            $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();
            $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'message' => 'Changed back',
                'validator' => 'token',
                'time_limit' => 5000,
                'overall_wall_time_limit' => 60000,
                'validator_time_limit' => 30000,
                'extra_wall_time' => 0,
                'memory_limit' => 32000,
                'output_limit' => 10240,
            ]));
            $this->assertTrue($response['rejudged']);
            $this->assertSame(0, $detourGrader->getGraderCallCount());
            unset($_FILES['problem_contents']);
        }

        $restoredVersionData = \OmegaUp\Controllers\Problem::apiVersions(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
        ]));
        $this->assertNotEquals(
            $originalVersionData['published'],
            $restoredVersionData['published']
        );
        $restoredDetails = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
        ]));
        $this->assertSame(
            $originalDetails['statement'],
            $restoredDetails['statement']
        );

        // Now that the problem is set up, we'll attempt to change the version.
        \OmegaUp\Controllers\Problem::apiSelectVersion(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'commit' => $originalVersionData['published'],
        ]));
        $this->assertEquals(
            $originalDetails,
            \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
            ]))
        );
        $this->assertSame(
            $originalVersionData['published'],
            \OmegaUp\Controllers\Problem::apiVersions(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
            ]))['published']
        );
        $this->assertSame(
            1.0,
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $runData['response']['guid']
                )->current_run_id
            )->score
        );

        // Change it to the second version.
        \OmegaUp\Controllers\Problem::apiSelectVersion(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'commit' => $modifiedVersionData['published'],
        ]));
        $this->assertEquals(
            $modifiedDetails,
            \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
            ]))
        );
        $this->assertSame(
            $modifiedVersionData['published'],
            \OmegaUp\Controllers\Problem::apiVersions(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
            ]))['published']
        );
        $this->assertSame(
            0.0,
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $runData['response']['guid']
                )->current_run_id
            )->score
        );

        // Change it back to the restored version.
        \OmegaUp\Controllers\Problem::apiSelectVersion(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'commit' => $restoredVersionData['published'],
        ]));
        $this->assertEquals(
            $restoredDetails,
            \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
            ]))
        );
        $this->assertSame(
            $restoredVersionData['published'],
            \OmegaUp\Controllers\Problem::apiVersions(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
            ]))['published']
        );
        $this->assertSame(
            1.0,
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $runData['response']['guid']
                )->current_run_id
            )->score
        );
    }

    /**
     * Tests problem version update when test cases change in a submission
     * outside a contest.
     */
    public function testProblemVersionUpdateCases() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problem = $problemData['problem'];
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($problemData['author']);
        // Change the problem to something completely different.
        {
            $_FILES['problem_contents']['tmp_name'] = OMEGAUP_TEST_RESOURCES_ROOT . 'testproblem_updatedcases.zip';
            $_detourGrader = new \OmegaUp\Test\ScopedGraderDetour();
            $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'message' => 'Changed to mrkareltastic',
                'validator' => 'token',
                'time_limit' => 1000,
                'overall_wall_time_limit' => 30000,
                'validator_time_limit' => 0,
                'extra_wall_time' => 1000,
                'memory_limit' => 64000,
                'output_limit' => 20480,
            ]));
            $this->assertTrue($response['rejudged']);
            unset($_FILES['problem_contents']);
        }
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );

        \OmegaUp\Test\Factories\Run::gradeRun(
            runData: $runData,
            points: 0.75,
            verdict: 'PA',
            submitDelay: null,
            runGuid: null,
            runId: null,
            problemsetPoints: 100,
            outputFilesContent: null,
            problemsetScoreMode: 'max_per_group',
            runScoreByGroups: [
                ['group_name' => 'sample', 'score' => 0.25, 'verdict' => 'AC'],
                ['group_name' => 'easy', 'score' => 0.25, 'verdict' => 'AC'],
                ['group_name' => 'medium', 'score' => 0.25, 'verdict' => 'AC'],
                ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'WA'],
            ],
        );

        $contestantLogin = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'problem_alias' => $problem->alias,
                'auth_token' => $contestantLogin->auth_token,
            ])
        );
        $this->assertSame(75.0, $response['score']);

        $login = self::login($problemData['author']);
        $versionData = \OmegaUp\Controllers\Problem::apiVersions(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
            ])
        );

        $versions = array_map(
            fn ($log) => [
                'commit' => $log['commit'],
                'version' => $log['version'],
            ],
            $versionData['log']
        );

        \OmegaUp\Controllers\Problem::apiSelectVersion(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'commit' => $versions[1]['commit'],
        ]));

        $response = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'problem_alias' => $problem->alias,
                'auth_token' => $contestantLogin->auth_token,
            ])
        );

        $this->assertSame(0.0, $response['score']);
    }

    /**
     * A PHPUnit data provider for all the score mode contests to update problem
     * cases.
     *
     * @return list<array{0:string, 1:float, 2:string, 3:int, 4:list<array{group_name: string, score: float, verdict: string}>}>
     */
    public function scoreModeProvider(): array {
        return [
            [
                'partial',
                0.75,
                100,
                'PA',
                []
            ],
            [
                'all_or_nothing',
                1.0,
                1,
                'AC',
                []
            ],
            [
                'max_per_group',
                75.0,
                1,
                'PA',
                [
                    ['group_name' => 'sample', 'score' => 0.25, 'verdict' => 'AC'],
                    ['group_name' => 'easy', 'score' => 0.25, 'verdict' => 'AC'],
                    ['group_name' => 'medium', 'score' => 0.25, 'verdict' => 'AC'],
                    ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'WA'],
                ]
            ],
        ];
    }

    /**
     * Tests problem version update when test cases change in a submission
     * inside a different types of contest.
     *
     * @param list<array{group_name:string, score:float, verdict:string}> $scoreByGroups
     *
     * @dataProvider scoreModeProvider
     */
    public function testProblemVersionUpdateCasesInAContest(
        string $scoreMode,
        float $points,
        int $problemsetPoints,
        string $verdict,
        $scoreByGroups
    ) {
        // Create an admin
        [
            'user' => $userAdmin,
            'identity' => $admin,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'author' => $admin,
                'authorUser' => $userAdmin,
            ])
        );
        $problem = $problemData['problem'];

        // Create a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'scoreMode' => $scoreMode,
                'contestDirector' => $admin,
                'contestDirectorUser' => $userAdmin,
            ])
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create a contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a run
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        $login = self::login($problemData['author']);
        // Change the problem to something completely different.
        {
            $_FILES['problem_contents']['tmp_name'] = OMEGAUP_TEST_RESOURCES_ROOT . 'testproblem_updatedcases.zip';
            $_detourGrader = new \OmegaUp\Test\ScopedGraderDetour();
            $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'message' => 'Changed to mrkareltastic',
                'validator' => 'token',
                'time_limit' => 1000,
                'overall_wall_time_limit' => 30000,
                'validator_time_limit' => 0,
                'extra_wall_time' => 1000,
                'memory_limit' => 64000,
                'output_limit' => 20480,
            ]));
            $this->assertTrue($response['rejudged']);
            unset($_FILES['problem_contents']);
        }

        \OmegaUp\Test\Factories\Run::gradeRun(
            runData: $runData,
            points: $points,
            verdict: $verdict,
            submitDelay: null,
            runGuid: null,
            runId: null,
            problemsetPoints: $problemsetPoints,
            outputFilesContent: null,
            problemsetScoreMode: $scoreMode,
            runScoreByGroups: $scoreByGroups,
        );

        $login = self::login($problemData['author']);
        $versionData = \OmegaUp\Controllers\Problem::apiVersions(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
            ])
        );

        $versions = array_map(
            fn ($log) => [
                'commit' => $log['commit'],
                'version' => $log['version'],
            ],
            $versionData['log']
        );

        [
            'ranking' => $scoreboardContestBeforeUpdate,
        ] = \OmegaUp\Controllers\Contest::apiScoreboard(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        );

        $this->assertSame(
            $scoreboardContestBeforeUpdate[0]['total']['points'],
            $points * $problemsetPoints
        );

        \OmegaUp\Controllers\Problem::apiSelectVersion(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'commit' => $versions[1]['commit'],
            'update_published' => \OmegaUp\ProblemParams::UPDATE_PUBLISHED_OWNED_PROBLEMSETS
        ]));

        // Let's grade the updated run
        \OmegaUp\Test\Factories\Run::gradeRun(
            runData: $runData,
            points: 0.0,
            verdict: 'WA',
            submitDelay: null,
            runGuid: null,
            runId: null,
            problemsetPoints: $problemsetPoints,
            outputFilesContent: null,
            problemsetScoreMode: $scoreMode,
            runScoreByGroups: [
                ['group_name' => 'sample', 'score' => 0.0, 'verdict' => 'WA'],
                ['group_name' => 'easy', 'score' => 0.0, 'verdict' => 'WA'],
                ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'WA'],
                ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'WA'],
            ],
        );
        // After five minutes, create a new run
        $time = \OmegaUp\Time::get();

        \OmegaUp\Time::setTimeForTesting($time + (5 * 60));

        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        \OmegaUp\Test\Factories\Run::gradeRun(
            runData: $runData,
            points: 0.0,
            verdict: 'WA',
            submitDelay: null,
            runGuid: null,
            runId: null,
            problemsetPoints: $problemsetPoints,
            outputFilesContent: null,
            problemsetScoreMode: $scoreMode,
            runScoreByGroups: [
                ['group_name' => 'sample', 'score' => 0.0, 'verdict' => 'WA'],
                ['group_name' => 'easy', 'score' => 0.0, 'verdict' => 'WA'],
                ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'WA'],
                ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'WA'],
            ],
        );
        [
            'ranking' => $scoreboardContestAfterUpdate,
        ] = \OmegaUp\Controllers\Contest::apiScoreboard(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        );

        $this->assertSame(
            $scoreboardContestAfterUpdate[0]['total']['points'],
            0.0
        );
    }

    public function testUpdateProblemInputLimitAndEmailClarifications() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $login = self::login($problemData['author']);

        // Update input_limit to verify its value changes properly
        $newInputLimit = 10000;
        \OmegaUp\Controllers\Problem::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'input_limit' => $newInputLimit,
                'problem_alias' => $problemData['request']['problem_alias'],
                'message' => 'Changed input limit',
            ])
        );

        // Verify data in DB
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemData['request']['problem_alias']
        );

        $this->assertSame($newInputLimit, $problem->input_limit);

        // Update email_clarifications to verify its value changes properly
        $newEmailClarifications = true;
        \OmegaUp\Controllers\Problem::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'email_clarifications' => $newEmailClarifications,
                'problem_alias' => $problemData['request']['problem_alias'],
                'message' => 'Changed email clarifications',
            ])
        );

        // Verify data in DB
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemData['request']['problem_alias']
        );

        $this->assertSame(
            $newEmailClarifications,
            $problem->email_clarifications
        );

        $newEmailClarifications = false;
        \OmegaUp\Controllers\Problem::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'email_clarifications' => $newEmailClarifications,
                'problem_alias' => $problemData['request']['problem_alias'],
                'message' => 'Changed email clarifications',
            ])
        );

        // Verify data in DB
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemData['request']['problem_alias']
        );

        $this->assertSame(
            $newEmailClarifications,
            $problem->email_clarifications
        );
    }

    private function updateProblemsetProblemWithRuns(
        string $updatePublished,
        ?\OmegaUp\DAO\VO\Identities $problemAuthor = null,
        ?\OmegaUp\DAO\VO\Identities $contestDirector = null,
        ?\OmegaUp\DAO\VO\Identities $contestAdmin = null
    ) {
        $originalTime = \OmegaUp\Time::get();
        try {
            $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'author' => $problemAuthor,
                ])
            );
            $problem = $problemData['problem'];
            ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

            \OmegaUp\Time::setTimeForTesting($originalTime - 30 * 60);

            // Create a standalone run.
            $pastStandaloneRunData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problemData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($pastStandaloneRunData);

            // Create a contest in the past with one run.
            $pastContestData = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'startTime' => $originalTime - 120 * 60,
                    'finishTime' => $originalTime - 5 * 60,
                    'contestDirector' => $contestDirector,
                ])
            );
            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problemData,
                $pastContestData
            );
            \OmegaUp\Test\Factories\Contest::addUser(
                $pastContestData,
                $identity
            );
            if (!is_null($contestAdmin)) {
                \OmegaUp\Test\Factories\Contest::addAdminUser(
                    $pastContestData,
                    $contestAdmin
                );
            }
            $pastRunData = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $pastContestData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($pastRunData);

            // Now create one in the present with one more run.
            $presentContestData = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'startTime' => $originalTime - 60 * 60,
                    'finishTime' => $originalTime + 60 * 60,
                    'contestDirector' => $contestDirector,
                ])
            );
            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problemData,
                $presentContestData
            );
            \OmegaUp\Test\Factories\Contest::addUser(
                $presentContestData,
                $identity
            );
            if (!is_null($contestAdmin)) {
                \OmegaUp\Test\Factories\Contest::addAdminUser(
                    $presentContestData,
                    $contestAdmin
                );
            }
            $presentRunData = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $presentContestData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($presentRunData);

            \OmegaUp\Time::setTimeForTesting($originalTime + 5 * 60);

            $login = self::login($problemData['author']);
            [
                'ranking' => $scoreboardPastContestBeforeUpdate,
            ] = \OmegaUp\Controllers\Contest::apiScoreboard(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'contest_alias' => $pastContestData['request']['alias'],
                ])
            );
            [
                'ranking' => $scoreboardPresentContestBeforeUpdate,
            ] = \OmegaUp\Controllers\Contest::apiScoreboard(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'contest_alias' => $presentContestData['request']['alias'],
                ])
            );
            // Change the problem to something completely different.
            $_FILES['problem_contents']['tmp_name'] = OMEGAUP_TEST_RESOURCES_ROOT . 'mrkareltastic.zip';
            $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();
            $response = \OmegaUp\Controllers\Problem::apiUpdate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'problem_alias' => $problem->alias,
                    'message' => 'Changed to mrkareltastic',
                    'validator' => 'token',
                    'time_limit' => 1000,
                    'overall_wall_time_limit' => 30000,
                    'validator_time_limit' => 0,
                    'extra_wall_time' => 1000,
                    'memory_limit' => 64000,
                    'output_limit' => 20480,
                    'update_published' => $updatePublished,
                ])
            );
            $this->assertSame(
                $response['rejudged'],
                $updatePublished != 'none'
            );
            // Runs are only added when the publishing mode is not none.
            $this->assertSame(
                $updatePublished === \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE ? 0 : 3,
                $detourGrader->getGraderCallCount()
            );
            foreach ($detourGrader->getRuns() as $run) {
                \OmegaUp\Test\Factories\Run::gradeRun(
                    null,
                    0,
                    'WA',
                    null,
                    null,
                    $run->run_id
                );
            }
            [
                'ranking' => $scoreboardPastContestAfterUpdate,
            ] = \OmegaUp\Controllers\Contest::apiScoreboard(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'contest_alias' => $pastContestData['request']['alias'],
                ])
            );[
                'ranking' => $scoreboardPresentContestAfterUpdate,
            ] = \OmegaUp\Controllers\Contest::apiScoreboard(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'contest_alias' => $presentContestData['request']['alias'],
                ])
            );
            // The scoreboard should remain the same after updating problems
            $this->assertSame(
                $scoreboardPastContestAfterUpdate,
                $scoreboardPastContestBeforeUpdate
            );
            $this->assertSame(
                $scoreboardPresentContestAfterUpdate,
                $scoreboardPresentContestBeforeUpdate
            );

            return [
                'pastRunData' => $pastRunData,
                'presentRunData' => $presentRunData,
                'pastStandaloneRunData' => $pastStandaloneRunData,
                'pastProblemData' => $problemData,
                'pastContestData' => $pastContestData,
                'presentContestData' => $presentContestData,
            ];
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime);
            unset($_FILES['problem_contents']);
        }
    }

    /**
     * Tests problem version update in a problemset context with no updates to
     * the published branch. All runs are kept as-is.
     */
    public function testProblemInProblemsetVersionUpdateNone() {
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE,
            $identity,
            $identity
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
    }

    /**
     * Tests problem version update in a problemset context with non-problemset
     * as the update option. Runs are also kept as-is.
     */
    public function testProblemInProblemsetVersionUpdateNonProblemset() {
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET,
            $identity,
            $identity
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        // Ensure that rolling back the version change would make a difference.
        $login = self::login($result['pastProblemData']['author']);
        $diffResult = \OmegaUp\Controllers\Problem::apiRunsDiff(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $result['pastProblemData']['problem']->alias,
            'version' => $result['pastProblemData']['problem']->current_version,
        ]));
        $this->assertSame(1, count($diffResult['diff']));
        $this->assertSame(
            $result['pastStandaloneRunData']['response']['guid'],
            $diffResult['diff'][0]['guid']
        );
        $this->assertSame('WA', $diffResult['diff'][0]['old_verdict']);
        $this->assertSame('AC', $diffResult['diff'][0]['new_verdict']);
    }

    /**
     * Tests problem version update in a problemset context with owned problemset
     * as the update option. Only the runs from problemsets that the problem
     * owner also has edit access will be updated.
     */
    public function testProblemInProblemsetVersionUpdateOwnedProblemsets() {
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_OWNED_PROBLEMSETS
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_OWNED_PROBLEMSETS,
            $identity,
            $identity
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertSame(
            'WA',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        // Ensure that the version change would make a difference.
        $login = self::login($result['pastProblemData']['author']);
        $presentProblem = \OmegaUp\DAO\Problems::getByAlias(
            $result['pastProblemData']['problem']->alias
        );
        $diffResult = \OmegaUp\Controllers\Contest::apiRunsDiff(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $result['pastProblemData']['problem']->alias,
            'contest_alias' => $result['pastContestData']['contest']->alias,
            'version' => $presentProblem->current_version,
        ]));
        $this->assertSame(1, count($diffResult['diff']));
        $this->assertSame(
            $result['pastRunData']['response']['guid'],
            $diffResult['diff'][0]['guid']
        );
        $this->assertSame('AC', $diffResult['diff'][0]['old_verdict']);
        $this->assertSame('WA', $diffResult['diff'][0]['new_verdict']);

        // Changing the version of the problemset to whatever it is currently
        // should not have any visible effect.
        \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $result['pastProblemData']['problem']->alias,
            'contest_alias' => $result['pastContestData']['contest']->alias,
            'commit' => $result['pastProblemData']['problem']->commit,
            'points' => 100,
            'order_in_contest' => 1,
        ]));
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        // Now explicitly change the version of the problemset.
        \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $result['pastProblemData']['problem']->alias,
            'contest_alias' => $result['pastContestData']['contest']->alias,
            'commit' => $presentProblem->commit,
            'points' => 100,
            'order_in_contest' => 1,
        ]));
        $this->assertSame(
            'WA',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
    }

    /**
     * Tests problem version update in a problemset context with owned problemset
     * as the update option. Only the runs from problemsets that the problem
     * owner also has edit access will be updated.
     */
    public function testProblemInProblemsetVersionUpdateEditableProblemsets() {
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
            $identity,
            null,
            $identity
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertSame(
            'WA',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
            $identity,
            $identity
        );
        $this->assertSame(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertSame(
            'WA',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
    }

    public function testUpdateProblemSettingsAndVisibility() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemAlias = $problemData['request']['problem_alias'];
        {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts($problemAlias);
            $problemSettings = json_decode(
                $problemArtifacts->get(
                    'settings.json'
                )
            );

            // Asserts default values
            $this->assertSame(
                floatval($problemData['request']['time_limit']),
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->TimeLimit
                )
            );
            $this->assertSame(
                floatval($problemData['request']['extra_wall_time']),
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->ExtraWallTime
                )
            );
            $this->assertSame(
                $problemData['request']['memory_limit'],
                $problemSettings->Limits->MemoryLimit / 1024
            );
            $this->assertSame(
                $problemData['request']['output_limit'],
                $problemSettings->Limits->OutputLimit
            );
            $this->assertSame(
                floatval($problemData['request']['overall_wall_time_limit']),
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->OverallWallTimeLimit
                )
            );
        }

        // Call API to update time limit.
        $newTimeLimit = 4000.0;
        $newExtraWallTime = 100.0;
        $newMemoryLimit = 16000;
        $newOutputLimit = 5120;
        $newOverallWallTimeLimit = 30000.0;
        $login = self::login($problemData['author']);
        \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemAlias,
            'time_limit' => $newTimeLimit,
            'extra_wall_time' => $newExtraWallTime,
            'memory_limit' => $newMemoryLimit,
            'output_limit' => $newOutputLimit,
            'overall_wall_time_limit' => $newOverallWallTimeLimit,
            'message' => 'Updated all problem settings',
        ]));

        // Verify problem settings were set.
        {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts($problemAlias);
            $problemSettings = json_decode(
                $problemArtifacts->get(
                    'settings.json'
                )
            );
            $this->assertSame(
                $newTimeLimit,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->TimeLimit
                )
            );
            $this->assertSame(
                $newExtraWallTime,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->ExtraWallTime
                )
            );
            $this->assertSame(
                $newMemoryLimit,
                $problemSettings->Limits->MemoryLimit / 1024
            );
            $this->assertSame(
                $newOutputLimit,
                $problemSettings->Limits->OutputLimit
            );
            $this->assertSame(
                $newOverallWallTimeLimit,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->OverallWallTimeLimit
                )
            );
        }

        // Updated problem visibility only
        \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemAlias,
            'visibility' => 'private',
            'message' => 'Visibility updated to private',
        ]));

        // Verify problem settings were not modified.
        {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts($problemAlias);
            $problemSettings = json_decode(
                $problemArtifacts->get(
                    'settings.json'
                )
            );
            $this->assertSame(
                $newTimeLimit,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->TimeLimit
                )
            );
            $this->assertSame(
                $newExtraWallTime,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->ExtraWallTime
                )
            );
            $this->assertSame(
                $newMemoryLimit,
                $problemSettings->Limits->MemoryLimit / 1024
            );
            $this->assertSame(
                $newOutputLimit,
                $problemSettings->Limits->OutputLimit
            );
            $this->assertSame(
                $newOverallWallTimeLimit,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->OverallWallTimeLimit
                )
            );
        }
        // But visibility mode has changed
        $response = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemAlias,
            ])
        );
        $this->assertSame(0, $response['visibility']);

        // Updated problem settings and visibility
        $newTimeLimit = 3000.0;
        $newExtraWallTime = 200.0;
        $newMemoryLimit = 8000;
        $newOutputLimit = 2560;
        $newOverallWallTimeLimit = 15000.0;
        \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemAlias,
            'visibility' => 'public_warning',
            'time_limit' => $newTimeLimit,
            'extra_wall_time' => $newExtraWallTime,
            'memory_limit' => $newMemoryLimit,
            'output_limit' => $newOutputLimit,
            'overall_wall_time_limit' => $newOverallWallTimeLimit,
            'message' => 'Visibility updated to private',
        ]));

        // Verify problem settings were not modified.
        {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts($problemAlias);
            $problemSettings = json_decode(
                $problemArtifacts->get(
                    'settings.json'
                )
            );
            $this->assertSame(
                $newTimeLimit,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->TimeLimit
                )
            );
            $this->assertSame(
                $newExtraWallTime,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->ExtraWallTime
                )
            );
            $this->assertSame(
                $newMemoryLimit,
                $problemSettings->Limits->MemoryLimit / 1024
            );
            $this->assertSame(
                $newOutputLimit,
                $problemSettings->Limits->OutputLimit
            );
            $this->assertSame(
                $newOverallWallTimeLimit,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->OverallWallTimeLimit
                )
            );
        }
        // But visibility mode has changed
        $response = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemAlias,
            ])
        );
        $this->assertSame(1, $response['visibility']);
    }

    /**
     * @dataProvider showDiffValueProvider
     */
    public function testUpdateProblemWithValidShowDiffValues(
        string $showDiffValue
    ) {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Call API
        $login = self::login($problemData['author']);

        \OmegaUp\Controllers\Problem::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'show_diff' => $showDiffValue,
                'problem_alias' => $problemData['request']['problem_alias'],
                'message' => 'Changed show_diff',
            ])
        );
        // Verify data in DB
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemData['request']['problem_alias']
        );

        // Check that we retrieved 1 element
        $this->assertNotNull($problem);
        $this->assertSameSets($showDiffValue, $problem->show_diff);
    }

    public function testUpdateProblemWithInvalidShowDiffValue() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $showDiffValues = ['none', 'examples', 'all', 'invalid'];

        foreach ($showDiffValues as $_) {
            // Call API
            $login = self::login($problemData['author']);
            try {
                \OmegaUp\Controllers\Problem::apiUpdate(
                    new \OmegaUp\Request([
                        'auth_token' => $login->auth_token,
                        'show_diff' => 'invalid',
                        'problem_alias' => $problemData['request']['problem_alias'],
                        'message' => 'Changed show_diff',
                    ])
                );
                $this->fail('Exception was expected.');
            } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
                $this->assertSame(
                    'parameterNotInExpectedSet',
                    $e->getMessage()
                );
            }
        }
    }

    /**
     * A PHPUnit data provider for the test with allow_user_add_tags values.
     *
     * @return list<list<string>>
     */
    public function allowUserAddTagsValueProvider(): array {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider allowUserAddTagsValueProvider
     */
    public function testUpdateProblemWithallowUserAddTagsValues(
        bool $allowUserAddTagsValue
    ) {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Call API
        $login = self::login($problemData['author']);

        \OmegaUp\Controllers\Problem::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'allow_user_add_tags' => $allowUserAddTagsValue,
                'problem_alias' => $problemData['request']['problem_alias'],
                'message' => 'Changed allow_user_add_tags',
            ])
        );
        // Verify data in DB
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemData['request']['problem_alias']
        );

        // Check that we retrieved 1 element
        $this->assertNotNull($problem);
        $this->assertSameSets(
            $allowUserAddTagsValue,
            $problem->allow_user_add_tags
        );
    }

    public function testUpdateStatementProblemViaSubmit() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Login API
        $login = self::login($problemData['author']);

        $response = \OmegaUp\Controllers\Problem::getProblemEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem' => $problemData['request']['problem_alias'],
            ])
        )['templateProperties'];

        $this->assertArrayHasKey('statement', $response['payload']);

        $originalStatement = $response['payload']['statement'];
        $newStatement = [
            'language' => $originalStatement['language'],
            'images' => [],
            'markdown' => 'New markdown',
        ];
        $markdownPT = 'Markdown in pt language';
        $contents = json_encode([
            $newStatement['language'] => $newStatement['markdown'],
            'pt' => $markdownPT,
        ]);

        // Updating more than one statement at the same time
        $response = \OmegaUp\Controllers\Problem::getProblemEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem' => $problemData['request']['problem_alias'],
                'request' => 'markdown',
                'message' => 'Change in statement',
                'directory' => 'statements',
                'contents' => $contents,
            ])
        )['templateProperties'];

        // Getting problem details for preferred user language('es')
        $response = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ])
        );
        $this->assertSame(
            $newStatement['language'],
            $response['statement']['language']
        );
        $this->assertStringContainsString(
            $newStatement['markdown'],
            $response['statement']['markdown']
        );

        // Getting problem details for an specific language('pt')
        $response = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'lang' => 'pt'
            ])
        );

        $this->assertSame('pt', $response['statement']['language']);
        $this->assertStringContainsString(
            $markdownPT,
            $response['statement']['markdown']
        );
    }

    public function testCommitModifiedZipExcludesEasyCases(): void {
        // Get a problem
        [
            'problem' => $problem,
            'author' => $identity,
        ] = \OmegaUp\Test\Factories\Problem::createProblem();

        //Get the current ZIP file with all the files
        $artifacts = new \OmegaUp\ProblemArtifacts($problem->alias);
        $currentZipPath = $artifacts->getZip();

        // Define changes
        $pathsToExclude = [
            'cases/sample',
            'cases/easy.01',
        ];
        $filesToAdd = [
            'cases/easy.001.in'  => "10 5\n",
            'cases/easy.001.out' => "15\n",
        ];
        $pathsToRename = [
            'cases/medium.'  => 'cases/hard.',
        ];

        $deployer = new \OmegaUp\ProblemDeployer($problem->alias);

        //  Commit the changes to the ZIP file
        $deployer->commitModifiedZip(
            'Test complex edit on cases',
            $identity,
            $currentZipPath,
            $pathsToExclude,
            $filesToAdd,
            $pathsToRename,
        );
        if (is_file($currentZipPath)) {
            unlink($currentZipPath);
        }
        // Retrieve the modified ZIP file
        $newArtifacts = new \OmegaUp\ProblemArtifacts(
            $problem->alias
        );
        $newZipPath = $newArtifacts->getZip();

        $zip = new \ZipArchive();
        $this->assertTrue(
            $zip->open($newZipPath),
            'The resulting ZIP file could not be opened.'
        );

        // Excluded: the new ZIP file must not contain any files beginning with these prefixes.
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            foreach ($pathsToExclude as $excludePrefix) {
                $this->assertStringStartsNotWith(
                    $excludePrefix,
                    $name,
                    "The excluded file {$name} still exists."
                );
            }
        }

        // Aggregates: cases must exist and have the expected content
        foreach ($filesToAdd as $path => $expectedContent) {
            $this->assertNotFalse(
                $zip->locateName($path),
                "The file {$path} should exist"
            );
            $this->assertSame(
                $expectedContent,
                $zip->getFromName($path),
                "Incorrect content in {$path}"
            );
        }

        // Renamed: there should be no cases with the previous prefix.
        foreach ($pathsToRename as $oldPrefix => $newPrefix) {
            $hasNewPrefix = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                $this->assertStringStartsNotWith(
                    $oldPrefix,
                    $name,
                    "The file {$name} must not start with the old prefix {$oldPrefix}."
                );
                if (str_starts_with($name, $newPrefix)) {
                    $hasNewPrefix = true;
                }
            }
            $this->assertTrue(
                $hasNewPrefix,
                "No file with the new prefix {$newPrefix} was found."
            );
        }

        $zip->close();

        if (is_file($newZipPath)) {
            unlink($newZipPath);
        }
    }

    public function testTriangulosZipToCdp() {
        $zipPath = OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos_extended.zip';
        $problemName = 'triangulos_extended';

        // Convert ZIP to CDP
        $cdp = \OmegaUp\ZipToCdpConverter::convert($zipPath, $problemName);

        // Normalize UUIDs for comparison
        $normalizedCdp = $this->normalizeUuids($cdp);

        // Load expected JSON
        $expectedJsonPath = OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos_extended_expected_cdp.json';
        $this->assertFileExists(
            $expectedJsonPath,
            'Expected JSON file not found'
        );

        $expectedJson = file_get_contents($expectedJsonPath);
        $expected = json_decode($expectedJson, true);

        $this->assertIsArray($expected, 'Expected JSON should be valid');

        // Normalize UUIDs for comparison
        $normalizedExpected = $this->normalizeUuids($expected);

        $this->assertEquals($normalizedExpected, $normalizedCdp);
    }

    /**
     * Recursively replace all UUID values with a deterministic counter
     */
    private function normalizeUuids(
        array $data,
        array &$uuidMap = [],
        int &$counter = 1
    ): array {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = $this->normalizeUuids($value, $uuidMap, $counter);
                continue;
            }

            if (
                is_string($value) &&
                in_array($key, ['groupID', 'caseID', 'lineID'], true)
            ) {
                if (!isset($uuidMap[$value])) {
                    $uuidMap[$value] = sprintf(
                        '%08d-0000-0000-0000-000000000000',
                        $counter++
                    );
                }
                $value = $uuidMap[$value];
            }
        }
        return $data;
    }

    /**
     * @dataProvider shouldOverrideMarkdownProvider
     * @param null|string $current
     * @param string $candidate
     * @param string $preference
     * @param bool $expected
     */
    public function testShouldOverrideMarkdown(
        null|string $current,
        string $candidate,
        string $preference,
        bool $expected
    ): void {
        $result = \OmegaUp\CdpBuilder::shouldOverrideMarkdown(
            $current,
            $candidate,
            $preference
        );

        $this->assertSame(
            $expected,
            $result
        );
    }

    /**
     * Data provider for shouldOverrideMarkdown with 5 main flow cases.
     * Format: [currentLanguage, candidateLanguage, languagePreference, expectedResult]
     *
     * @return array<string, array{0: null|string, 1: string, 2: string, 3: bool}>
     */
    public function shouldOverrideMarkdownProvider(): array {
        return [
            'null_current_overrides' => [
                null, 'en', 'es', true
            ],
            'current_equals_preference' => [
                'es', 'es', 'es', false
            ],
            'candidate_equals_preference' => [
                'en', 'es', 'es', true
            ],
            'candidate_is_default' => [
                'en', 'es', 'pt', true
            ],
            'no_rule_matches' => [
                'en', 'pt', 'es', false
            ],
        ];
    }

    /**
     * Test for updating an existing case in a problem
     * This test validates the 'cases' request type
     */
    public function testUpdateCasesViaProblemEditDetailsForTypeScript() {
        // Create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemAlias = $problemData['request']['problem_alias'];

        // Login as problem author
        $login = self::login($problemData['author']);

        // Get the problem edit details to extract the current CDP
        $response = \OmegaUp\Controllers\Problem::getProblemEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem' => $problemAlias,
            ])
        )['templateProperties'];

        $this->assertArrayHasKey('cdp', $response['payload']);
        $initialCdp = $response['payload']['cdp'];
        $this->assertArrayHasKey('casesStore', $initialCdp);
        $this->assertArrayHasKey('groups', $initialCdp['casesStore']);

        // Get the initial group
        $initialGroup = $initialCdp['casesStore']['groups'][0];
        $this->assertArrayHasKey('groupID', $initialGroup);
        $this->assertArrayHasKey('cases', $initialGroup);
        $this->assertGreaterThan(0, count($initialGroup['cases']));

        $initialCase = $initialGroup['cases'][0];
        $initialCaseId = $initialCase['caseID'];

        // Prepare the updated case data
        $updatedCaseName = 'updated_case_name';
        $newInput = "10 20\n";
        $newOutput = "30\n";

        $updatedCaseData = [
            'case' => [
                'caseID' => $initialCaseId,
                'groupID' => $initialGroup['groupID'],
                'name' => $updatedCaseName,
                'points' => $initialCase['points'],
                'autoPoints' => $initialCase['autoPoints'],
                'lines' => [
                    [
                        'lineID' => $initialCase['lines'][0]['lineID'],
                        'caseID' => $initialCaseId,
                        'label' => 'input',
                        'data' => [
                            'kind' => 'multiline',
                            'value' => $newInput,
                        ],
                    ],
                ],
                'output' => $newOutput,
            ],
            'group' => [
                'groupID' => $initialGroup['groupID'],
                'name' => $initialGroup['name'],
                'points' => $initialGroup['points'],
                'autoPoints' => $initialGroup['autoPoints'],
                'ungroupedCase' => $initialGroup['ungroupedCase'],
                'cases' => $initialGroup['cases'],
            ],
        ];

        $response = \OmegaUp\Controllers\Problem::getProblemEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem' => $problemAlias,
                'request' => 'cases',
                'message' => 'Updated case input and output',
                'contents' => json_encode($updatedCaseData),
            ])
        )['templateProperties'];

        $this->assertTrue($response['payload']['statusSuccess']);
        $this->assertArrayHasKey('cdp', $response['payload']);
        $updatedCdp = $response['payload']['cdp'];

        // Verify the case was updated in the CDP
        $updatedGroup = $updatedCdp['casesStore']['groups'][0];
        $updatedCase = null;
        foreach ($updatedGroup['cases'] as $case) {
            if ($case['caseID'] === $initialCaseId) {
                $updatedCase = $case;
                break;
            }
        }

        $this->assertNotNull($updatedCase, 'Case should exist in updated CDP');
        $this->assertSame($updatedCaseName, $updatedCase['name']);
        $this->assertSame($newInput, $updatedCase['lines'][0]['data']['value']);
        $this->assertSame($newOutput, $updatedCase['output']);
    }

    /**
     * Test for adding a new case to an existing group
     */
    public function testAddNewCaseViaProblemEditDetailsForTypeScript() {
        // Create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemAlias = $problemData['request']['problem_alias'];

        // Login as problem author
        $login = self::login($problemData['author']);

        // Get the problem edit details to extract the current CDP
        $response = \OmegaUp\Controllers\Problem::getProblemEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem' => $problemAlias,
            ])
        )['templateProperties'];

        $this->assertArrayHasKey('cdp', $response['payload']);
        $initialCdp = $response['payload']['cdp'];
        $this->assertArrayHasKey('casesStore', $initialCdp);
        $this->assertArrayHasKey('groups', $initialCdp['casesStore']);

        // Get the initial group
        $initialGroup = $initialCdp['casesStore']['groups'][0];
        $this->assertArrayHasKey('groupID', $initialGroup);
        $this->assertArrayHasKey('cases', $initialGroup);

        // Prepare new case data
        $newCaseId = '00000000-0000-0000-0000-000000000001';
        $newCaseName = 'new_test_case';

        $newCaseData = [
            'case' => [
                'caseID' => $newCaseId,
                'groupID' => $initialGroup['groupID'],
                'name' => $newCaseName,
                'points' => 50,
                'autoPoints' => false,
                'lines' => [
                    [
                        'lineID' => '00000000-0000-0000-0000-000000000002',
                        'caseID' => $newCaseId,
                        'label' => 'input',
                        'data' => [
                            'kind' => 'multiline',
                            'value' => "5 10\n",
                        ],
                    ],
                ],
                'output' => "15\n",
            ],
            'group' => [
                'groupID' => $initialGroup['groupID'],
                'name' => $initialGroup['name'],
                'points' => $initialGroup['points'],
                'autoPoints' => $initialGroup['autoPoints'],
                'ungroupedCase' => $initialGroup['ungroupedCase'],
                'cases' => array_merge($initialGroup['cases'], [
                    [
                        'caseID' => $newCaseId,
                        'groupID' => $initialGroup['groupID'],
                        'name' => $newCaseName,
                        'points' => 50,
                        'autoPoints' => false,
                        'lines' => [],
                        'output' => "15\n",
                    ],
                ]),
            ],
        ];

        $response = \OmegaUp\Controllers\Problem::getProblemEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem' => $problemAlias,
                'request' => 'cases',
                'message' => 'Added new case',
                'contents' => json_encode($newCaseData),
            ])
        )['templateProperties'];

        $this->assertTrue($response['payload']['statusSuccess']);
        $finalCdp = $response['payload']['cdp'];

        // Verify the new case was added
        $finalGroup = $finalCdp['casesStore']['groups'][0];
        $newCaseFound = false;
        foreach ($finalGroup['cases'] as $case) {
            if ($case['caseID'] === $newCaseId && $case['name'] === $newCaseName) {
                $newCaseFound = true;
                break;
            }
        }

        $this->assertTrue($newCaseFound, 'New case should exist in final CDP');

        // Verify that the group has more cases than initially
        $this->assertGreaterThan(
            count($initialGroup['cases']),
            count($finalGroup['cases']),
            'Group should have more cases after adding a new one'
        );
    }

    /**
     * Test for deleting a single case in a problem
     * Uses triangulos.zip which has ungrouped cases (each case creates its own group)
     */
    public function testDeleteCaseViaProblemEditDetailsForTypeScript() {
        // Create a problem with ungrouped cases
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip'
            ])
        );
        $problemAlias = $problemData['request']['problem_alias'];

        // Login as problem author
        $login = self::login($problemData['author']);

        // Get the current CDP structure
        $response = \OmegaUp\Controllers\Problem::getProblemEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem' => $problemAlias,
            ])
        )['templateProperties'];

        $this->assertArrayHasKey('cdp', $response['payload']);
        $initialCdp = $response['payload']['cdp'];
        $initialGroups = $initialCdp['casesStore']['groups'];

        // Get the first case to delete (should be an ungrouped case)
        $firstGroup = $initialGroups[0];
        $firstCase = $firstGroup['cases'][0];
        $firstCaseId = $firstCase['caseID'];
        $firstGroupId = $firstGroup['groupID'];

        // Delete the case
        $deleteRequest = [
            'id' => $firstCaseId,
        ];

        $response = \OmegaUp\Controllers\Problem::getProblemEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem' => $problemAlias,
                'request' => 'deleteGroupCase',
                'message' => 'Deleted test case',
                'contents' => json_encode($deleteRequest),
            ])
        )['templateProperties'];

        $this->assertTrue($response['payload']['statusSuccess']);
        $this->assertArrayHasKey('cdp', $response['payload']);
        $afterDeleteCaseCdp = $response['payload']['cdp'];

        // Verify the case was deleted from the CDP
        // Since it's an ungrouped case, the group should also be deleted
        $groupStillExists = false;
        foreach ($afterDeleteCaseCdp['casesStore']['groups'] as $group) {
            if ($group['groupID'] === $firstGroupId) {
                $groupStillExists = true;
                break;
            }
        }

        $this->assertFalse(
            $groupStillExists,
            'When deleting an ungrouped case, its group should also be deleted'
        );

        $this->assertLessThan(
            count($initialGroups),
            count($afterDeleteCaseCdp['casesStore']['groups']),
            'Should have fewer groups after deleting ungrouped case'
        );
    }
    /**
     * Test for deleting an entire group in a problem
     * Uses a basic problem without ungrouped cases
     */
    public function testDeleteGroupViaProblemEditDetailsForTypeScript() {
        // Create a basic problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemAlias = $problemData['request']['problem_alias'];

         // Login as problem author
        $login = self::login($problemData['author']);

        // Get the current CDP structure
        $initialResponse = \OmegaUp\Controllers\Problem::getProblemEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem' => $problemAlias,
            ])
        )['templateProperties'];

        $this->assertArrayHasKey('cdp', $initialResponse['payload']);
        $initialCdp = $initialResponse['payload']['cdp'];
        $initialGroups = $initialCdp['casesStore']['groups'];

        $this->assertGreaterThan(
            1,
            count($initialGroups),
            'Test requires at least 2 groups'
        );

        // Get the second group to delete
        $secondGroup = $initialGroups[1];
        $secondGroupId = $secondGroup['groupID'];

        $deleteGroupRequest = [
            'id' => $secondGroupId,
        ];

        $response = \OmegaUp\Controllers\Problem::getProblemEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem' => $problemAlias,
                'request' => 'deleteGroupCase',
                'message' => 'Deleted entire group',
                'contents' => json_encode($deleteGroupRequest),
            ])
        )['templateProperties'];

        $this->assertTrue($response['payload']['statusSuccess']);
        $afterDeleteGroupCdp = $response['payload']['cdp'];

        // Verify the group was deleted
        $groupExists = false;
        foreach ($afterDeleteGroupCdp['casesStore']['groups'] as $group) {
            if ($group['groupID'] === $secondGroupId) {
                $groupExists = true;
                break;
            }
        }

        $this->assertFalse(
            $groupExists,
            'Deleted group should not exist in CDP'
        );

        // Verify that we have one less group
        $this->assertLessThan(
            count($initialGroups),
            count($afterDeleteGroupCdp['casesStore']['groups']),
            'Should have fewer groups after deletion'
        );
    }
}
