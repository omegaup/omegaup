<?php

/**
 * Tests for apiCreate in ProblemController
 *
 * @author joemmanuel
 */

class ProblemCreateTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting(
            $this->createFileUploaderMock()
        );
    }

    /**
     * Basic test for creating a problem
     */
    public function testCreateValidProblem() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest();
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertEquals($r['title'], $problem->title);
        $this->assertEquals(substr($r['title'], 0, 32), $problem->alias);
        $this->assertEquals($r['order'], $problem->order);
        $this->assertEquals($r['source'], $problem->source);
        $this->assertEqualSets($r['languages'], $problem->languages);

        // Verify author username -> author id conversion
        $acl = \OmegaUp\DAO\ACLs::getByPK($problem->acl_id);
        $identity = \OmegaUp\DAO\Identities::findByUserId($acl->owner_id);
        $this->assertEquals($identity->username, $r['author_username']);

        // Verify problem settings.
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($r['problem_alias']);
        $problemSettings = json_decode($problemArtifacts->get('settings.json'));
        $this->assertEquals(false, $problemSettings->Slow);
        $this->assertEquals($r['validator'], $problemSettings->Validator->Name);
        $this->assertEquals(5000, $r['time_limit']);
        $this->assertEquals('5s', $problemSettings->Limits->TimeLimit);
        $this->assertEquals(
            $r['memory_limit'] * 1024,
            $problemSettings->Limits->MemoryLimit
        );

        // Verify problem contents were copied
        $this->assertTrue($problemArtifacts->exists('settings.json'));
        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/en.markdown'));

        // Default data
        $this->assertEquals(0, $problem->visits);
        $this->assertEquals(0, $problem->submissions);
        $this->assertEquals(0, $problem->accepted);
        $this->assertEquals(0, $problem->difficulty);
    }

    /**
     * Basic test for slow problems
     */
    public function testSlowQueue() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest();
        $r = $problemData['request'];
        $r['time_limit'] = 8000;
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);

        // Verify problem settings.
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($r['problem_alias']);
        $problemSettings = json_decode($problemArtifacts->get('settings.json'));
        $this->assertEquals(true, $problemSettings->Slow);
    }

    /**
     * Basic test for slow problems
     */
    public function testSlowQueueWithWallLimit() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest();
        $r = $problemData['request'];
        $r['time_limit'] = 8000;
        $r['overall_wall_time_limit'] = 20000;
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);

        // Verify problem settings.
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($r['problem_alias']);
        $problemSettings = json_decode($problemArtifacts->get('settings.json'));
        $this->assertEquals(false, $problemSettings->Slow);
    }

    /**
     * Basic test for creating a problem
     */
    public function testCreateValidProblemWithINCases() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'mrkareltastic.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);

        // Verify problem contents were copied
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($r['problem_alias']);

        $this->assertTrue($problemArtifacts->exists('settings.json'));
        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('cases/g1.train0.in'));
        $this->assertTrue($problemArtifacts->exists('cases/g1.train0.out'));
        $this->assertTrue($problemArtifacts->exists('statements/es.markdown'));
    }

    /**
     * Test that sends incomplete requests
     */
    public function testRequiredParameters() {
        // Array of valid keys
        $valid_keys = [
            'title',
            'source',
        ];

        foreach ($valid_keys as $key) {
            // Get the problem data
            $problemData = \OmegaUp\Test\Factories\Problem::getRequest();
            $r = $problemData['request'];
            $problemAuthor = $problemData['author'];

            // Login user
            $login = self::login($problemAuthor);
            $r['auth_token'] = $login->auth_token;

            // Unset key
            unset($r[$key]);

            try {
                // Call the API
                $response = \OmegaUp\Controllers\Problem::apiCreate($r);
            } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
                // We're OK, clean up our mess and continue
                unset($_REQUEST);
                continue;
            }

            $this->fail('Exception was expected. Parameter: ' . $key);
        }
    }

    /**
     * Test that sends invalid languages.
     */
    public function testInvalidLanguage() {
        foreach (['abc', 'c,cpp,cows', 'java,coffee,espresso'] as $languages) {
            // Get the problem data
            $problemData = \OmegaUp\Test\Factories\Problem::getRequest();
            $r = $problemData['request'];
            $problemAuthor = $problemData['author'];

            // Login user
            $login = self::login($problemAuthor);
            $r['auth_token'] = $login->auth_token;
            $r['languages'] = $languages;
            try {
                // Call the API
                $response = \OmegaUp\Controllers\Problem::apiCreate($r);
            } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
                // We're OK, clean up our mess and continue
                unset($_REQUEST);
                continue;
            }
            $this->fail("Exception was expected. Language set: $languages");
        }
    }

    /**
     * Test that we are able to submit a problem without testplan
     */
    public function testValidProblemNoTestplan() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertEquals($r['title'], $problem->title);
        $this->assertEquals(substr($r['title'], 0, 32), $problem->alias);
        $this->assertEquals($r['order'], $problem->order);
        $this->assertEquals($r['source'], $problem->source);

        // Verify author username -> author id conversion
        $acl = \OmegaUp\DAO\ACLs::getByPK($problem->acl_id);
        $identity = \OmegaUp\DAO\Identities::findByUserId($acl->owner_id);
        $this->assertEquals($identity->username, $r['author_username']);

        // Verify problem contents were copied
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);

        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/es.markdown'));

        // Default data
        $this->assertEquals(0, $problem->visits);
        $this->assertEquals(0, $problem->submissions);
        $this->assertEquals(0, $problem->accepted);
        $this->assertEquals(0, $problem->difficulty);
    }

    /**
     * Test that we are able to submit a problem without testplan
     */
    public function testValidProblemWithNonUTF8CharsInStmt() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'nonutf8stmt.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);

        // Verify response
        $this->assertEquals('ok', $response['status']);

        // Get problem info from DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify problem contents were copied
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);
        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/es.markdown'));

        // Verify we have the accents, lol
        $markdownContents = $problemArtifacts->get('statements/es.markdown');
        if (strpos($markdownContents, 'ó') === false) {
            $this->fail('ó not found when expected.');
        }
    }

    /**
     * Test that image upload works.
     */
    public function testImageUpload() {
        $imageGitObjectId = '7b1279806a8c59f5a2c6ae21544ed2a8074691ab';
        $imageExtension = 'jpg';
        $imageAbsoluteUrl = 'http://i.imgur.com/fUkvDkw.png';

        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'imagetest.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);

        // Verify response
        $this->assertEquals('ok', $response['status']);

        // Verify problem contents were copied
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($r['problem_alias']);
        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/es.markdown'));
        $this->assertTrue($problemArtifacts->exists('statements/bunny.jpg'));

        // Do image path checks in the markdown file
        $markdownContents = $problemArtifacts->get('statements/es.markdown');
        $this->assertStringContainsString(
            '![Saluda](bunny.jpg)',
            $markdownContents
        );
        // And the direct URL.
        $this->assertStringContainsString(
            "![Saluda]($imageAbsoluteUrl)",
            $markdownContents
        );
        // And the unmodified, not found image.
        $this->assertStringContainsString(
            '![404](notfound.jpg)',
            $markdownContents
        );

        // Check that the images are there.
        $response = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $r['problem_alias'],
        ]));
        $imagePath = "{$r['problem_alias']}/{$imageGitObjectId}.{$imageExtension}";
        $this->assertEquals(
            IMAGES_URL_PATH . $imagePath,
            $response['statement']['images']['bunny.jpg']
        );
        $this->assertFileExists(IMAGES_PATH . $imagePath);
        $expectedImageHash = sha1(file_get_contents(IMAGES_PATH . $imagePath));

        // Delete the image and check that it exists after
        // regeneration.
        unlink(IMAGES_PATH . $imagePath);
        $this->assertFileDoesNotExist(IMAGES_PATH . $imagePath);
        \OmegaUp\Controllers\Problem::regenerateImage(
            $r['problem_alias'],
            $imageGitObjectId,
            $imageExtension
        );
        $this->assertFileExists(IMAGES_PATH . $imagePath);
        $actualImageHash = sha1(file_get_contents(IMAGES_PATH . $imagePath));
        $this->assertEquals($expectedImageHash, $actualImageHash);
    }

    /**
     * Test that we can produce a valid alias from the title
     */
    public function testConstructAliasFromTitle() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest();
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Set a valid "complex" title
        $r['title'] = 'Lá Venganza Del Malvado Dr. Liraaa';

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertEquals($r['title'], $problem->title);

        // Verify problem contents were copied
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);

        $this->assertTrue($problemArtifacts->exists('settings.json'));
        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/en.markdown'));
    }

    /**
     * Basic test for creating a problem
     */
    public function testCreateProblemWithTags() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest();
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;
        $expectedTags = [
            ['tagname' => 'problemTagMatrices', 'public' => true],
            ['tagname' => 'geometry', 'public' => false],
        ];

        $r['selected_tags'] = json_encode(
            $expectedTags + [
                // The following tags will be ignored:
                ['tagname' => 'karel', 'public' => true],
                ['tagname' => 'problemRestrictedTagOnlyOutput', 'public' => false],
            ]
        );

        // Call the API
        \OmegaUp\Controllers\Problem::apiCreate($r);

        $tags = \OmegaUp\Controllers\Problem::apiTags(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]))['tags'];

        foreach ($expectedTags as $selectedTag) {
            $this->assertArrayContainsWithPredicate(
                $tags,
                fn ($tag) => $tag['name'] == $selectedTag['tagname']
            );
        }
        $this->assertArrayContainsWithPredicate(
            $tags,
            fn ($tag) => $tag['name'] == 'problemRestrictedTagLanguage'
        );
        $this->assertArrayNotContainsWithPredicate(
            $tags,
            fn ($tag) => (
                $tag['name'] == 'problemRestrictedTagKarel' ||
                $tag['name'] == 'problemRestrictedTagOnlyOutput'
            )
        );
    }

    /**
     * test for count problems whit levelTag
     */
    public function testCountProblemsWithLevelTags() {
        // Create problems by level
        $problemLevelMapping = [
            'problemLevelBasicIntroductionToProgramming' => 5,
            'problemLevelIntermediateMathsInProgramming' => 5,
            'problemLevelIntermediateDataStructuresAndAlgorithms' => 5,
            'problemLevelIntermediateAnalysisAndDesignOfAlgorithms' => 5,
            'problemLevelAdvancedCompetitiveProgramming' => 5,
            'problemLevelAdvancedSpecializedTopics' => 5,
            'problemLevelBasicKarel' => 5,
        ];
        $problemData = [];
        foreach ($problemLevelMapping as $level => $numberOfProblems) {
            foreach (range(0, $numberOfProblems - 1) as $_) {
                $problemData[] = \OmegaUp\Test\Factories\Problem::createProblem(
                    new \OmegaUp\Test\Factories\ProblemParams([
                        'problem_level' => $level,
                    ])
                );
            }
        }

        $problemsCount = [];
        $total = 0;
        $response = \OmegaUp\Controllers\Problem::getProblemCollectionDetailsForSmarty(
            new \OmegaUp\Request([])
        )['smartyProperties']['payload'];
        foreach ($response['problemCount'] as $levelTag) {
            $problemsCount[] = $levelTag['problems_per_tag'];
            $total += $levelTag['problems_per_tag'];
            $this->assertEquals(
                problemLevelMapping[$levelTag['name']],
                $levelTag['problems_per_tag']
            );
        }

        $this->assertEquals(35, $total);
    }

    /**
     * Basic test for creating a problem with wrong attribute
     */
    public function testCreateProblemTagsWithWrongAttribute() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest();
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;
        $r['selected_tags'] = json_encode([
            ['name' => 'math', 'public' => true],
            ['tagname' => 'geometry', 'public' => false],
        ]);

        try {
            // Call the API
            $response = \OmegaUp\Controllers\Problem::apiCreate($r);
            $this->fail('Exception was expected. Wrong attribute');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals($e->getMessage(), 'parameterEmpty');
        }
    }

    /**
     * Basic test for uploading problem without statement
     */
    public function testCreateProblemWithoutStatement() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'nostmt.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        try {
            \OmegaUp\Controllers\Problem::apiCreate($r);
            $this->fail('Exception was expected.');
        } catch (\OmegaUp\Exceptions\ProblemDeploymentFailedException $e) {
            $this->assertEquals(
                'problemDeployerNoStatements',
                $e->getMessage()
            );
        }
    }

    /**
     * Basic test for uploadin problem missing outputs
     */
    public function testCreateProblemMissingOutput() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'missingout.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        try {
            \OmegaUp\Controllers\Problem::apiCreate($r);
            $this->fail('Exception was expected.');
        } catch (\OmegaUp\Exceptions\ProblemDeploymentFailedException $e) {
            $this->assertEquals(
                'problemDeployerMismatchedInputFile',
                $e->getMessage()
            );
        }
    }

    /**
     * Test that we are able to submit a problem with an interactive/ directory.
     */
    public function testValidProblemInteractive() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos_interactive.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertEquals($r['title'], $problem->title);
        $this->assertEquals(substr($r['title'], 0, 32), $problem->alias);
        $this->assertEquals($r['order'], $problem->order);
        $this->assertEquals($r['source'], $problem->source);

        // Verify author username -> author id conversion
        $acl = \OmegaUp\DAO\ACLs::getByPK($problem->acl_id);
        $identity = \OmegaUp\DAO\Identities::findByUserId($acl->owner_id);
        $this->assertEquals($identity->username, $r['author_username']);

        // Verify problem contents were copied
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);

        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/es.markdown'));
        $this->assertTrue(
            $problemArtifacts->exists(
                'interactive/triangulos.idl'
            )
        );
        $this->assertTrue($problemArtifacts->exists('interactive/Main.cpp'));
        $this->assertTrue(
            $problemArtifacts->exists(
                'interactive/Main.distrib.cpp'
            )
        );
        $this->assertTrue($problemArtifacts->exists('settings.json'));
        $problemSettings = json_decode(
            $problemArtifacts->get('settings.json'),
            true /* assoc */
        );
        $this->assertArrayHasKey('Interactive', $problemSettings);

        // Default data
        $this->assertEquals(0, $problem->visits);
        $this->assertEquals(0, $problem->submissions);
        $this->assertEquals(0, $problem->accepted);
        $this->assertEquals(0, $problem->difficulty);

        \OmegaUp\Controllers\Problem::regenerateTemplates(
            $problem->alias,
            $problem->commit
        );

        // Verify that the templates were generated.
        $this->assertTrue(
            file_exists(
                TEMPLATES_PATH . "/{$problem->alias}/{$problem->commit}/{$problem->alias}_unix_cpp.tar.bz2"
            )
        );
        $this->assertTrue(
            file_exists(
                TEMPLATES_PATH . "/{$problem->alias}/{$problem->commit}/{$problem->alias}_windows_cpp.zip"
            )
        );
    }

    public function testProblemParams() {
        $problemParams = new \OmegaUp\ProblemParams([
            'problem_alias' => \OmegaUp\Test\Utils::createRandomString(),
        ]);

        // Asserting all default values
        $this->assertEquals(0, $problemParams->visibility);
        $this->assertEquals(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
            $problemParams->updatePublished
        );
        $this->assertEquals(null, $problemParams->validatorTimeLimit);
        $this->assertEquals(null, $problemParams->overallWallTimeLimit);
        $this->assertEquals(null, $problemParams->extraWallTime);
        $this->assertEquals(null, $problemParams->outputLimit);
        $this->assertEquals(10240, $problemParams->inputLimit);
        $this->assertEquals(null, $problemParams->emailClarifications);

        // New object with custom values
        $titleAlias = \OmegaUp\Test\Utils::createRandomString();
        $overallWallTimeLimit = 50000;
        $problemParams = new \OmegaUp\ProblemParams([
            'problem_alias' => $titleAlias,
            'title' => $titleAlias,
            'update_published' => \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE,
            'overall_wall_time_limit' => $overallWallTimeLimit,
            'email_clarifications' => true,
        ]);

        $this->assertEquals($titleAlias, $problemParams->title);
        $this->assertEquals(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE,
            $problemParams->updatePublished
        );
        $this->assertEquals(
            $overallWallTimeLimit,
            $problemParams->overallWallTimeLimit
        );
        $this->assertTrue($problemParams->emailClarifications);
    }

    public function testCreateProblemWithDefaultValues() {
        // Create our contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $title = \OmegaUp\Test\Utils::createRandomString();
        $problemAlias = substr(
            preg_replace(
                '/[^a-zA-Z0-9_-]/',
                '',
                str_replace(' ', '-', $title)
            ),
            0,
            32
        );

        \OmegaUp\Controllers\Problem::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'title' => $title,
            'problem_alias' => $problemAlias,
            'source' => 'yo',
            'problem_level' => 'problemLevelBasicIntroductionToProgramming',
        ]));

        $response = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemAlias,
            ])
        );

        $this->assertEquals(10240, $response['input_limit']);
        {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts($problemAlias);
            $problemSettings = json_decode(
                $problemArtifacts->get(
                    'settings.json'
                )
            );

            $this->assertEquals('1s', $problemSettings->Limits->TimeLimit);
            $this->assertEquals('0s', $problemSettings->Limits->ExtraWallTime);
            $this->assertEquals(
                \Omegaup\Controllers\Problem::parseSize('64MiB'),
                $problemSettings->Limits->MemoryLimit
            );
            $this->assertEquals(
                \Omegaup\Controllers\Problem::parseSize('10240KiB'),
                $problemSettings->Limits->OutputLimit
            );
            $this->assertEquals(
                '30s',
                $problemSettings->Limits->OverallWallTimeLimit
            );
        }
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

    /**
     * @dataProvider showDiffValueProvider
     */
    public function testCreateProblemWithValidShowDiffValues(
        string $showDiffValue
    ) {
        [
            'problem' => $problem,
        ] = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'show_diff' => $showDiffValue,
            ])
        );

        $this->assertEquals($showDiffValue, $problem->show_diff);
    }

    public function testCreateProblemWithInvalidShowDiffValue() {
        try {
            [
                'problem' => $problem,
            ] = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'show_diff' => 'invalid',
                ])
            );
            $this->fail('Exception was expected.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterNotInExpectedSet', $e->getMessage());
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
    public function testCreateProblemWithAllowUserAddTagsValues(
        bool $allowUserAddTagsValue
    ) {
        [
            'problem' => $problem,
        ] = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'allow_user_add_tags' => $allowUserAddTagsValue,
            ])
        );

        $this->assertEquals(
            $allowUserAddTagsValue,
            $problem->allow_user_add_tags
        );
    }
}
