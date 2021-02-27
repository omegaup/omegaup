<?php

/**
 * Description of ProblemUpdateTest
 *
 * @author joemmanuel
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
        $this->assertEquals(1, count($problemLanguages));

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
        $this->assertEquals(2, count($problemLanguages));
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
        $this->assertEquals(
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
        $this->assertEquals($selectedLevel, $problemLevel);

        $selectedLevel = 'problemLevelBasicIntroductionToProgramming';
        \OmegaUp\Controllers\Problem::apiUpdateProblemLevel(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['problem']->alias,
            'level_tag' => $selectedLevel,
        ]));
        $problemLevel = \OmegaUp\DAO\ProblemsTags::getProblemLevel(
            $problemData['problem']
        );
        $this->assertEquals($selectedLevel, $problemLevel);
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

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

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));

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
                true /* assoc */
            );

            // This example comes from the problem statement.
            $this->assertEquals(
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
                true /* assoc */
            );
            $this->assertEquals(
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

        $this->assertEquals(2, $detourGrader->getGraderCallCount());
    }

    public function testUpdateProblemSettings() {
        // Get a problem with a run.
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemAlias = $problemData['request']['problem_alias'];
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
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
            $this->assertEquals(
                3,
                count($problemSettings->Cases)
            );
            $this->assertEquals(
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
            $this->assertEquals(
                3,
                count($problemSettings->Cases)
            );
            $this->assertEquals(
                ($newTimeLimit / 1000.0) . 's',
                $problemSettings->Limits->TimeLimit
            );
        }

        $this->assertEquals(1, $detourGrader->getGraderCallCount());
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
                /*$assoc=*/true
            );
            $this->assertEquals(
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
        $this->assertEqualSets($languages, $problem->languages);
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
            $this->assertEquals(
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

        $this->assertEquals('ok', $response['status']);

        // Check statment contents
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

        $this->assertEquals('ok', $response['status']);

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
        $imgFilename = 'af6a4603e039cca2f6823d287f6c87e561aa6e68.png';

        $statement = "This is the new statement with an image omg ![Alt text]($imgUri \"Optional title\")\n";
        $login = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiUpdateStatement(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Statement now contains images',
            'statement' => $statement
        ]));

        $this->assertEquals('ok', $response['status']);

        // Check statment contents
        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            $problemData['request']['problem_alias']
        );
        $statementMarkdownContents = $problemArtifacts->get(
            'statements/es.markdown'
        );

        $this->assertEquals($statement, $statementMarkdownContents);
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
            $this->assertEquals(
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

        $this->assertEquals(0, $detourGrader->getGraderCallCount());
    }

    /**
     * Tests problem admin can edit a problem
     */
    public function testUpdateProblemWithProblemAdmin() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create our new admin
        ['user' => $problemAdmin, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add admin to the problem
        $adminLogin = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiAddAdmin(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $this->assertEquals('ok', $response['status']);

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
        ['user' => $user, 'identity' => $problemAdmin] = \OmegaUp\Test\Factories\User::createUser();

        // Add admin to the problem
        $adminLogin = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiAddAdmin(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $problemAdmin->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $this->assertEquals('ok', $response['status']);

        // Then remove the user
        $response = \OmegaUp\Controllers\Problem::apiRemoveAdmin(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $problemAdmin->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertEquals('ok', $response['status']);

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
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Tests problem admins list API
     */
    public function testProblemAdmins() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create our new admin
        ['user' => $user, 'identity' => $problemAdmin] = \OmegaUp\Test\Factories\User::createUser();

        // Add admin to the problem
        $login = self::login($problemData['author']);
        $response = \OmegaUp\Controllers\Problem::apiAddAdmin(new \OmegaUp\Request([
            'usernameOrEmail' => $problemAdmin->username,
            'problem_alias' => $problemData['request']['problem_alias'],
            'auth_token' => $login->auth_token,
        ]));

        $this->assertEquals('ok', $response['status']);

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
            $this->assertEquals('problemIsPrivate', $e->getMessage());
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
            $this->assertEquals(
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
            $this->assertEquals(
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
            $this->assertEquals(
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
            $this->assertEquals(
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
            $this->assertEquals(
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
            $this->assertEquals('tagPrefixRestricted', $e->getMessage());
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
            $this->assertEquals('tagRestricted', $e->getMessage());
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
            /*public=*/ false
        );
        $this->assertEquals($testTags, $privateTags);

        ['user' => $extraUser, 'identity' => $extraIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $privateTags = \OmegaUp\DAO\ProblemsTags::getTagsForProblem(
            $problemData['problem'],
            !\OmegaUp\Authorization::canEditProblem(
                $extraIdentity,
                $problemData['problem']
            ),
            /*public=*/ false
        );
        $this->assertEmpty($privateTags);
    }

    /**
     * Tests problem version update.
     */
    public function testProblemVersionUpdate() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problem = $problemData['problem'];
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
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
            $this->assertEquals(1, $detourGrader->getGraderCallCount());
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
            $this->assertEquals(0, $detourGrader->getGraderCallCount());
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
        $this->assertEquals(
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
        $this->assertEquals(
            $originalVersionData['published'],
            \OmegaUp\Controllers\Problem::apiVersions(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
            ]))['published']
        );
        $this->assertEquals(
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
        $this->assertEquals(
            $modifiedVersionData['published'],
            \OmegaUp\Controllers\Problem::apiVersions(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
            ]))['published']
        );
        $this->assertEquals(
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
        $this->assertEquals(
            $restoredVersionData['published'],
            \OmegaUp\Controllers\Problem::apiVersions(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
            ]))['published']
        );
        $this->assertEquals(
            1.0,
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $runData['response']['guid']
                )->current_run_id
            )->score
        );
    }

    public function testUpdateProblemInputLimitAndEmailClarifications() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create our contestant
        [
            'user' => $contestant,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
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

        $this->assertEquals($newInputLimit, $problem->input_limit);

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

        $this->assertEquals(
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

        $this->assertEquals(
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
            $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
                'author' => $problemAuthor,
            ]));
            $problem = $problemData['problem'];
            ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

            \OmegaUp\Time::setTimeForTesting($originalTime - 30 * 60);

            // Create a standalone run.
            $pastStandaloneRunData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problemData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($pastStandaloneRunData);

            // Create a contest in the past with one run.
            $pastContestData = \OmegaUp\Test\Factories\Contest::createContest(new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime - 5 * 60,
                'contestDirector' => $contestDirector,
            ]));
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
            $presentContestData = \OmegaUp\Test\Factories\Contest::createContest(new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime + 60 * 60,
                'contestDirector' => $contestDirector,
            ]));
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
            // Change the problem to something completely different.
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
                'update_published' => $updatePublished,
            ]));
            $this->assertEquals(
                $response['rejudged'],
                $updatePublished != 'none'
            );
            // Runs are only added when the publishing mode is not none.
            $this->assertEquals(
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
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        ['user' => $owner, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE,
            $identity,
            $identity
        );
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertEquals(
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
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        ['user' => $owner, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET,
            $identity,
            $identity
        );
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertEquals(
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
        $this->assertEquals(1, count($diffResult['diff']));
        $this->assertEquals(
            $result['pastStandaloneRunData']['response']['guid'],
            $diffResult['diff'][0]['guid']
        );
        $this->assertEquals('WA', $diffResult['diff'][0]['old_verdict']);
        $this->assertEquals('AC', $diffResult['diff'][0]['new_verdict']);
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
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        ['user' => $owner, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_OWNED_PROBLEMSETS,
            $identity,
            $identity
        );
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertEquals(
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
        $this->assertEquals(1, count($diffResult['diff']));
        $this->assertEquals(
            $result['pastRunData']['response']['guid'],
            $diffResult['diff'][0]['guid']
        );
        $this->assertEquals('AC', $diffResult['diff'][0]['old_verdict']);
        $this->assertEquals('WA', $diffResult['diff'][0]['new_verdict']);

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
        $this->assertEquals(
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
        $this->assertEquals(
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
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        ['user' => $owner, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
            $identity,
            null,
            $identity
        );
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertEquals(
            'WA',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['presentRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );

        ['user' => $owner, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $result = $this->updateProblemsetProblemWithRuns(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
            $identity,
            $identity
        );
        $this->assertEquals(
            'AC',
            \OmegaUp\DAO\Runs::getByPK(
                \OmegaUp\DAO\Submissions::getByGuid(
                    $result['pastRunData']['response']['guid']
                )->current_run_id
            )->verdict
        );
        $this->assertEquals(
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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts($problemAlias);
            $problemSettings = json_decode(
                $problemArtifacts->get(
                    'settings.json'
                )
            );

            // Asserts default values
            $this->assertEquals(
                $problemData['request']['time_limit'],
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->TimeLimit
                )
            );
            $this->assertEquals(
                $problemData['request']['extra_wall_time'],
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->ExtraWallTime
                )
            );
            $this->assertEquals(
                $problemData['request']['memory_limit'],
                $problemSettings->Limits->MemoryLimit / 1024
            );
            $this->assertEquals(
                $problemData['request']['output_limit'],
                $problemSettings->Limits->OutputLimit
            );
            $this->assertEquals(
                $problemData['request']['overall_wall_time_limit'],
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->OverallWallTimeLimit
                )
            );
        }

        // Call API to update time limit.
        $newTimeLimit = 4000;
        $newExtraWallTime = 100;
        $newMemoryLimit = 16000;
        $newOutputLimit = 5120;
        $newOverallWallTimeLimit = 30000;
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
            $this->assertEquals(
                $newTimeLimit,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->TimeLimit
                )
            );
            $this->assertEquals(
                $newExtraWallTime,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->ExtraWallTime
                )
            );
            $this->assertEquals(
                $newMemoryLimit,
                $problemSettings->Limits->MemoryLimit / 1024
            );
            $this->assertEquals(
                $newOutputLimit,
                $problemSettings->Limits->OutputLimit
            );
            $this->assertEquals(
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
            $this->assertEquals(
                $newTimeLimit,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->TimeLimit
                )
            );
            $this->assertEquals(
                $newExtraWallTime,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->ExtraWallTime
                )
            );
            $this->assertEquals(
                $newMemoryLimit,
                $problemSettings->Limits->MemoryLimit / 1024
            );
            $this->assertEquals(
                $newOutputLimit,
                $problemSettings->Limits->OutputLimit
            );
            $this->assertEquals(
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
        $this->assertEquals(0, $response['visibility']);

        // Updated problem setttings and visibility
        $newTimeLimit = 3000;
        $newExtraWallTime = 200;
        $newMemoryLimit = 8000;
        $newOutputLimit = 2560;
        $newOverallWallTimeLimit = 15000;
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
            $this->assertEquals(
                $newTimeLimit,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->TimeLimit
                )
            );
            $this->assertEquals(
                $newExtraWallTime,
                \Omegaup\Controllers\Problem::parseDuration(
                    $problemSettings->Limits->ExtraWallTime
                )
            );
            $this->assertEquals(
                $newMemoryLimit,
                $problemSettings->Limits->MemoryLimit / 1024
            );
            $this->assertEquals(
                $newOutputLimit,
                $problemSettings->Limits->OutputLimit
            );
            $this->assertEquals(
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
        $this->assertEquals(1, $response['visibility']);
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
        $this->assertEqualSets($showDiffValue, $problem->show_diff);
    }

    public function testUpdateProblemWithInvalidShowDiffValue() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $showDiffValues = ['none', 'examples', 'all', 'invalid'];

        foreach ($showDiffValues as $showDiffValue) {
            // Call API
            $login = self::login($problemData['author']);
            try {
                $response = \OmegaUp\Controllers\Problem::apiUpdate(
                    new \OmegaUp\Request([
                        'auth_token' => $login->auth_token,
                        'show_diff' => 'invalid',
                        'problem_alias' => $problemData['request']['problem_alias'],
                        'message' => 'Changed show_diff',
                    ])
                );
                $this->fail('Exception was expected.');
            } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
                $this->assertEquals(
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
        $this->assertEqualSets(
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
        )['smartyProperties'];

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
        )['smartyProperties'];

        // Getting problem details for preferred user language('es')
        $response = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ])
        );
        $this->assertEquals(
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

        $this->assertEquals('pt', $response['statement']['language']);
        $this->assertStringContainsString(
            $markdownPT,
            $response['statement']['markdown']
        );
    }
}
