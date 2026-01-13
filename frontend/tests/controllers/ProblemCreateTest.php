<?php
/**
 * Tests for apiCreate in ProblemController
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
        $this->assertSame('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retrieved 1 element
        $this->assertSame(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertSame($r['title'], $problem->title);
        $this->assertSame(
            substr(
                $r['title'],
                0,
                \OmegaUp\Validators::ALIAS_MAX_LENGTH
            ),
            $problem->alias
        );
        $this->assertSame($r['order'], $problem->order);
        $this->assertSame($r['source'], $problem->source);
        $this->assertSameSets($r['languages'], $problem->languages);

        // Verify author username -> author id conversion
        $acl = \OmegaUp\DAO\ACLs::getByPK($problem->acl_id);
        $identity = \OmegaUp\DAO\Identities::findByUserId($acl->owner_id);
        $this->assertSame($identity->username, $r['author_username']);

        // Verify problem settings.
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($r['problem_alias']);
        $problemSettings = json_decode($problemArtifacts->get('settings.json'));
        $this->assertSame(false, $problemSettings->Slow);
        $this->assertSame($r['validator'], $problemSettings->Validator->Name);
        $this->assertSame(5000, $r['time_limit']);
        $this->assertSame('5s', $problemSettings->Limits->TimeLimit);
        $this->assertSame(
            $r['memory_limit'] * 1024,
            $problemSettings->Limits->MemoryLimit
        );

        // Verify problem contents were copied
        $this->assertTrue($problemArtifacts->exists('settings.json'));
        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/en.markdown'));

        // Default data
        $this->assertSame(0, $problem->visits);
        $this->assertSame(0, $problem->submissions);
        $this->assertSame(0, $problem->accepted);
        $this->assertNull($problem->difficulty);
    }

    /**
     * A PHPUnit data provider for testCreateWithInvalidAlias.
     *
     * @return list<list<string>>
     */
    public function invalidAliasValueProvider(): array {
        return [
            ['this has a space', 'parameterInvalid'],
            ['this-alias-is-way-too-long-and-should-be-rejected', 'parameterStringTooLong'],
            ['colons:are-disallowed', 'parameterInvalid'],
            ['new', 'parameterInvalid'],  // restricted alias
        ];
    }

    /**
     * @dataProvider invalidAliasValueProvider
     */
    public function testCreateWithInvalidAlias(
        string $alias,
        string $expectedMessage
    ) {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest();
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r = $problemData['request'];
        $r['auth_token'] = $login->auth_token;
        $r['problem_alias'] = $alias;

        // Call the API
        try {
            \OmegaUp\Controllers\Problem::apiCreate($r);
            $this->fail('Problem creation should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame($e->getMessage(), $expectedMessage);
        }
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
        $this->assertSame('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retrieved 1 element
        $this->assertSame(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);

        // Verify problem settings.
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($r['problem_alias']);
        $problemSettings = json_decode($problemArtifacts->get('settings.json'));
        $this->assertSame(true, $problemSettings->Slow);
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
        $this->assertSame('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retrieved 1 element
        $this->assertSame(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);

        // Verify problem settings.
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($r['problem_alias']);
        $problemSettings = json_decode($problemArtifacts->get('settings.json'));
        $this->assertSame(false, $problemSettings->Slow);
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
        $this->assertSame('ok', $response['status']);

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
                \OmegaUp\Controllers\Problem::apiCreate($r);
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
                \OmegaUp\Controllers\Problem::apiCreate($r);
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
        $this->assertSame('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retrieved 1 element
        $this->assertSame(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertSame($r['title'], $problem->title);
        $this->assertSame(
            substr(
                $r['title'],
                0,
                \OmegaUp\Validators::ALIAS_MAX_LENGTH
            ),
            $problem->alias
        );
        $this->assertSame($r['order'], $problem->order);
        $this->assertSame($r['source'], $problem->source);

        // Verify author username -> author id conversion
        $acl = \OmegaUp\DAO\ACLs::getByPK($problem->acl_id);
        $identity = \OmegaUp\DAO\Identities::findByUserId($acl->owner_id);
        $this->assertSame($identity->username, $r['author_username']);

        // Verify problem contents were copied
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);

        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/es.markdown'));

        // Default data
        $this->assertSame(0, $problem->visits);
        $this->assertSame(0, $problem->submissions);
        $this->assertSame(0, $problem->accepted);
        $this->assertNull($problem->difficulty);
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
        $this->assertSame('ok', $response['status']);

        // Get problem info from DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);
        $this->assertSame(1, count($problems));
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
        $this->assertSame('ok', $response['status']);

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
        $this->assertSame(
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
        $this->assertSame($expectedImageHash, $actualImageHash);
    }

    /**
     * Test that source files (for statements / solutions) work.
     */
    public function testSources() {
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos_sources.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);
        $this->assertSame('ok', $response['status']);

        // Check that the sources are there.
        $response = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $r['problem_alias'],
        ]));
        $this->assertSame(
            $response['statement']['sources'],
            [
                'plantilla.py' => '#!/usr/bin/python3

def _main() -> None:
    n = int(input().strip())
    aristas = map(int, input().strip().split())

if __name__ == \'__main__\':
    _main()
',
            ]
        );
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
        $this->assertSame('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retrieved 1 element
        $this->assertSame(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertSame($r['title'], $problem->title);

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
     * test for count problems with levelTag
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
                        'quality_seal' => true,
                    ])
                );
            }
        }

        $problemsCount = [];
        $total = 0;
        $response = \OmegaUp\Controllers\Problem::getProblemCollectionDetailsForTypeScript(
            new \OmegaUp\Request([])
        )['templateProperties']['payload'];
        foreach ($response['problemCount'] as $levelTag) {
            $problemsCount[] = $levelTag['problems_per_tag'];
            $total += $levelTag['problems_per_tag'];
            $this->assertSame(
                $problemLevelMapping[$levelTag['name']],
                $levelTag['problems_per_tag']
            );
        }

        $this->assertSame(35, $total);
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
            \OmegaUp\Controllers\Problem::apiCreate($r);
            $this->fail('Exception was expected. Wrong attribute');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame($e->getMessage(), 'parameterEmpty');
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
            $this->assertSame(
                'problemDeployerNoStatements',
                $e->getMessage()
            );
        }
    }

    /**
     * Basic test for uploading problem missing outputs
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
            $this->assertSame(
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
        $this->assertSame('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retrieved 1 element
        $this->assertSame(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertSame($r['title'], $problem->title);
        $this->assertSame(
            substr(
                $r['title'],
                0,
                \OmegaUp\Validators::ALIAS_MAX_LENGTH
            ),
            $problem->alias
        );
        $this->assertSame($r['order'], $problem->order);
        $this->assertSame($r['source'], $problem->source);

        // Verify author username -> author id conversion
        $acl = \OmegaUp\DAO\ACLs::getByPK($problem->acl_id);
        $identity = \OmegaUp\DAO\Identities::findByUserId($acl->owner_id);
        $this->assertSame($identity->username, $r['author_username']);

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
        $this->assertSame(0, $problem->visits);
        $this->assertSame(0, $problem->submissions);
        $this->assertSame(0, $problem->accepted);
        $this->assertNull($problem->difficulty);

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

    /**
     * Test that we are able to submit a lecture problem without public tags.
     */
    public function testValidLecture() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest(
            new \OmegaUp\Test\Factories\ProblemParams([
                'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'readonlylecture.zip',
                'languages' => '',
                'selectedTags' => '',
            ])
        );
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);

        // Verify response
        $this->assertSame('ok', $response['status']);

        // Verify data in DB
        $problems = \OmegaUp\DAO\Problems::getByTitle($r['title']);

        // Check that we only retrieved 1 element
        $this->assertCount(1, $problems);
        $problem = $problems[0];

        // Verify problem was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertSame($r['title'], $problem->title);
        $this->assertSame(
            substr(
                $r['title'],
                0,
                \OmegaUp\Validators::ALIAS_MAX_LENGTH
            ),
            $problem->alias
        );
        $this->assertSame($r['order'], $problem->order);
        $this->assertSame($r['source'], $problem->source);
        $this->assertSameSets($r['languages'], $problem->languages);

        // Verify author username -> author id conversion
        $acl = \OmegaUp\DAO\ACLs::getByPK($problem->acl_id);
        $identity = \OmegaUp\DAO\Identities::findByUserId($acl->owner_id);
        $this->assertSame($identity->username, $r['author_username']);

        // Verify problem settings
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);
        $this->assertTrue($problemArtifacts->exists('settings.json'));
        $problemSettings = json_decode($problemArtifacts->get('settings.json'));
        $this->assertSame(false, $problemSettings->Slow);
        $this->assertSame($r['validator'], $problemSettings->Validator->Name);
        $this->assertSame(5000, $r['time_limit']);
        $this->assertSame('5s', $problemSettings->Limits->TimeLimit);
        $this->assertSame(
            $r['memory_limit'] * 1024,
            $problemSettings->Limits->MemoryLimit
        );

        // Verify problem contents were copied
        $this->assertTrue($problemArtifacts->exists('statements/en.markdown'));

        // Default data
        $this->assertSame(0, $problem->visits);
        $this->assertSame(0, $problem->submissions);
        $this->assertSame(0, $problem->accepted);
        $this->assertNull($problem->difficulty);

        // Verify problem has the expected tags
        [
            'tags' => $tags,
        ] = \OmegaUp\Controllers\Problem::apiTags(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $expectedTags = [
            'problemLevelBasicIntroductionToProgramming',
            'problemRestrictedTagNoSubmissions',
        ];

        foreach ($expectedTags as $selectedTag) {
            $this->assertArrayContainsWithPredicate(
                $tags,
                fn ($tag) => $tag['name'] == $selectedTag
            );
        }
    }

    /**
     * Test that we are able to generate the input .zip of a problem that
     * admits an output-only solution.
     */
    public function testGenerateInputZip() {
        // Get the problem data
        $problemData = \OmegaUp\Test\Factories\Problem::getRequest(new \OmegaUp\Test\Factories\ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip',
            'languages' => 'cat',
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Problem::apiCreate($r);
        $this->assertSame('ok', $response['status']);

        $problem = \OmegaUp\DAO\Problems::getByTitle($r['title'])[0];

        $filename = "{$problem->alias}-input.zip";
        \OmegaUp\Controllers\Problem::generateInputZip(
            $problem,
            $problem->commit,
            $filename
        );

        // Verify that the templates were generated.
        $zipPath = INPUTS_PATH . "{$problem->alias}/{$problem->commit}/{$filename}";
        $this->assertTrue(file_exists($zipPath));

        $zipArchive = new \ZipArchive();
        try {
            /** @var true|int */
            $err = $zipArchive->open($zipPath, \ZipArchive::RDONLY);
            $this->assertTrue($err);

            /** @var list<string> */
            $filenames = [];
            for ($i = 0; $i < $zipArchive->numFiles; ++$i) {
                $filenames[] = $zipArchive->getNameIndex($i);
            }
            $this->assertEqualsCanonicalizing(
                $filenames,
                ['1.in', '2.in', '3.in', '4.in']
            );
        } finally {
            $zipArchive->close();
        }
    }

    public function testProblemParams() {
        $problemParams = new \OmegaUp\ProblemParams([
            'problem_alias' => \OmegaUp\Test\Utils::createRandomString(),
        ]);

        // Asserting all default values
        $this->assertNull($problemParams->visibility);
        $this->assertSame(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
            $problemParams->updatePublished
        );
        $this->assertSame(null, $problemParams->validatorTimeLimit);
        $this->assertSame(null, $problemParams->overallWallTimeLimit);
        $this->assertSame(null, $problemParams->extraWallTime);
        $this->assertSame(null, $problemParams->outputLimit);
        $this->assertSame(10240, $problemParams->inputLimit);
        $this->assertSame(null, $problemParams->emailClarifications);

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

        $this->assertSame($titleAlias, $problemParams->title);
        $this->assertSame(
            \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE,
            $problemParams->updatePublished
        );
        $this->assertSame(
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
            \OmegaUp\Validators::ALIAS_MAX_LENGTH
        );

        \OmegaUp\Controllers\Problem::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'title' => $title,
            'problem_alias' => $problemAlias,
            'source' => 'yo',
            'problem_level' => 'problemLevelBasicIntroductionToProgramming',
            'selected_tags' => json_encode([
                [
                    'tagname' => 'problemTagBinarySearchTree',
                    'public' => true,
                ],
            ]),
        ]));

        $response = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemAlias,
            ])
        );

        $this->assertSame(10240, $response['input_limit']);
        {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts($problemAlias);
            $problemSettings = json_decode(
                $problemArtifacts->get(
                    'settings.json'
                )
            );

            $this->assertSame('1s', $problemSettings->Limits->TimeLimit);
            $this->assertSame('0s', $problemSettings->Limits->ExtraWallTime);
            $this->assertSame(
                \Omegaup\Controllers\Problem::parseSize('64MiB'),
                $problemSettings->Limits->MemoryLimit
            );
            $this->assertSame(
                \Omegaup\Controllers\Problem::parseSize('10240KiB'),
                $problemSettings->Limits->OutputLimit
            );
            $this->assertSame(
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

        $this->assertSame($showDiffValue, $problem->show_diff);
    }

    public function testCreateProblemWithInvalidShowDiffValue() {
        try {
            \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'show_diff' => 'invalid',
                ])
            );
            $this->fail('Exception was expected.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterNotInExpectedSet', $e->getMessage());
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

        $this->assertSame(
            $allowUserAddTagsValue,
            $problem->allow_user_add_tags
        );
    }

    public function testUsersOnlySeeAllowedProblems(): void {
        // Define the structure of users, problems and permissions
        $testScenario = [
            'users' => [
                'owner1' => [
                    'problems' => [
                        'owner1_private' => [
                            'visibility' => 'private',
                            'admins' => ['guest1'],
                        ],
                        'owner1_public' => [
                            'visibility' => 'public',
                            'admins' => [],
                        ],
                    ],
                ],
                'owner2' => [
                    'problems' => [
                        'owner2_private' => [
                            'visibility' => 'private',
                            'admins' => ['guest2'],
                        ],
                        'owner2_public' => [
                            'visibility' => 'public',
                            'admins' => [],
                        ],
                    ],
                ],
                'guest1' => [
                    'problems' => [],
                ],
                'guest2' => [
                    'problems' => [],
                ],
            ],
            'expectedProblemsListByUserBeforeAdmins' => [
                'guest1' => ['owner1_public', 'owner2_public'],
                'guest2' => ['owner1_public', 'owner2_public'],
            ],
            'expectedProblemsListByUserAfterAdmins' => [
                'guest1' => ['owner1_private', 'owner1_public', 'owner2_public'],
                'guest2' => ['owner1_public', 'owner2_private', 'owner2_public'],
            ],
        ];

        // Create all users
        $users = [];
        foreach (array_keys($testScenario['users']) as $userName) {
            ['identity' => $users[$userName]] = \OmegaUp\Test\Factories\User::createUser();
        }

        // Create all problems
        $problems = [];
        foreach ($testScenario['users'] as $ownerName => $ownerData) {
            foreach ($ownerData['problems'] as $problemAlias => $problemData) {
                $problems[$problemAlias] = \OmegaUp\Test\Factories\Problem::createProblem(
                    new \OmegaUp\Test\Factories\ProblemParams([
                        'author' => $users[$ownerName],
                        'visibility' => $problemData['visibility'],
                        'alias' => $problemAlias,
                    ])
                );
            }
        }

        // Verify that each guest only sees public problems (before adding permissions)
        $problemsByGuest = $testScenario['expectedProblemsListByUserBeforeAdmins'];
        foreach ($problemsByGuest as $guestName => $expectedProblemsBeforeAdmins) {
            $guestLogin = self::login($users[$guestName]);
            $response = \OmegaUp\Controllers\Problem::apiList(
                new \OmegaUp\Request(['auth_token' => $guestLogin->auth_token])
            );
            foreach ($expectedProblemsBeforeAdmins as $selectedProblemAlias) {
                self::assertArrayContainsWithPredicate(
                    $response['results'],
                    fn(array $problem) => $problem['alias'] == $selectedProblemAlias,
                    "{$guestName} should see problem {$selectedProblemAlias}"
                );
            }
        }

        // Add admins to problems according to configuration
        foreach ($testScenario['users'] as $ownerData) {
            foreach ($ownerData['problems'] as $problemAlias => $problemData) {
                foreach ($problemData['admins'] as $adminName) {
                    \OmegaUp\Test\Factories\Problem::addAdminUser(
                        $problems[$problemAlias],
                        $users[$adminName]
                    );
                }
            }
        }

        // Verify that each guest now sees the problems they were added to as admin
        $problemsByGuest = $testScenario['expectedProblemsListByUserAfterAdmins'];
        foreach ($problemsByGuest as $guestName => $expectedProblemsAfterAdmins) {
            $guestLogin = self::login($users[$guestName]);
            $response = \OmegaUp\Controllers\Problem::apiList(
                new \OmegaUp\Request(['auth_token' => $guestLogin->auth_token])
            );
            foreach ($expectedProblemsAfterAdmins as $selectedProblemAlias) {
                self::assertArrayContainsWithPredicate(
                    $response['results'],
                    fn(array $problem) => $problem['alias'] == $selectedProblemAlias,
                    "{$guestName} should see problem {$selectedProblemAlias}"
                );
            }
        }
    }
}
