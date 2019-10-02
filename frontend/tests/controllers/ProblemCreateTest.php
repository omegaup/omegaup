<?php

/**
 * Tests for apiCreate in ProblemController
 *
 * @author joemmanuel
 */

class CreateProblemTest extends OmegaupTestCase {
    /**
     * Basic test for creating a problem
     */
    public function testCreateValidProblem() {
        // Get the problem data
        $problemData = ProblemsFactory::getRequest();
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

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
        $problemData = ProblemsFactory::getRequest();
        $r = $problemData['request'];
        $r['time_limit'] = 8000;
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

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
        $problemData = ProblemsFactory::getRequest();
        $r = $problemData['request'];
        $r['time_limit'] = 8000;
        $r['overall_wall_time_limit'] = 20000;
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

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
        $problemData = ProblemsFactory::getRequest(new ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'mrkareltastic.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

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
        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

        // Array of valid keys
        $valid_keys = [
            'title',
            'validator',
            'time_limit',
            'memory_limit',
            'source',
            'languages',
        ];

        foreach ($valid_keys as $key) {
            // Get the problem data
            $problemData = ProblemsFactory::getRequest();
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
        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

        foreach (['abc', 'c,cpp,cows', 'java,coffee,espresso'] as $languages) {
            // Get the problem data
            $problemData = ProblemsFactory::getRequest();
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
        $problemData = ProblemsFactory::getRequest(new ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

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
        $problemData = ProblemsFactory::getRequest(new ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'nonutf8stmt.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

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
        $markdown_contents = $problemArtifacts->get('statements/es.markdown');
        if (strpos($markdown_contents, 'ó') === false) {
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
        $problemData = ProblemsFactory::getRequest(new ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'imagetest.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

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
        $markdown_contents = $problemArtifacts->get('statements/es.markdown');
        $this->assertContains('![Saluda](bunny.jpg)', $markdown_contents);
        // And the direct URL.
        $this->assertContains("![Saluda]($imageAbsoluteUrl)", $markdown_contents);
        // And the unmodified, not found image.
        $this->assertContains('![404](notfound.jpg)', $markdown_contents);

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
    }

    /**
     * Test that we can produce a valid alias from the title
     */
    public function testConstructAliasFromTitle() {
        // Get the problem data
        $problemData = ProblemsFactory::getRequest();
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Set a valid "complex" title
        $r['title'] = 'Lá Venganza Del Malvado Dr. Liraaa';

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

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
        $problemData = ProblemsFactory::getRequest();
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;
        $expectedTags = [
            ['tagname' => 'math', 'public' => true],
            ['tagname' => 'geometry', 'public' => false],
        ];

        $r['selected_tags'] = json_encode(
            $expectedTags + [
                // The following tags will be ignored:
                ['tagname' => 'karel', 'public' => true],
                ['tagname' => 'solo-salida', 'public' => false],
            ]
        );

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

        // Call the API
        \OmegaUp\Controllers\Problem::apiCreate($r);

        $tags = \OmegaUp\Controllers\Problem::apiTags(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]))['tags'];

        foreach ($expectedTags as $selectedTag) {
            $this->assertArrayContainsWithPredicate($tags, function ($tag) use ($selectedTag) {
                return $tag['name'] == $selectedTag['tagname'];
            });
        }
        $this->assertArrayContainsWithPredicate($tags, function ($tag) use ($selectedTag) {
            return $tag['name'] == 'lenguaje';
        });
        $this->assertArrayNotContainsWithPredicate($tags, function ($tag) use ($selectedTag) {
            return ($tag['name'] == 'karel' || $tag['name'] == 'solo-salida');
        });
    }

    /**
     * Basic test for creating a problem with wrong attribute
     */
    public function testCreateProblemTagsWithWrongAttribute() {
        // Get the problem data
        $problemData = ProblemsFactory::getRequest();
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;
        $r['selected_tags'] = json_encode([
            ['name' => 'math', 'public' => true],
            ['tagname' => 'geometry', 'public' => false],
        ]);

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

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
        $problemData = ProblemsFactory::getRequest(new ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'nostmt.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

        // Call the API
        try {
            \OmegaUp\Controllers\Problem::apiCreate($r);
            $this->fail('Exception was expected.');
        } catch (\OmegaUp\Exceptions\ProblemDeploymentFailedException $e) {
            $this->assertEquals('problemDeployerNoStatements', $e->getMessage());
        }
    }

    /**
     * Basic test for uploadin problem missing outputs
     */
    public function testCreateProblemMissingOutput() {
        // Get the problem data
        $problemData = ProblemsFactory::getRequest(new ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'missingout.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

        // Call the API
        try {
            \OmegaUp\Controllers\Problem::apiCreate($r);
            $this->fail('Exception was expected.');
        } catch (\OmegaUp\Exceptions\ProblemDeploymentFailedException $e) {
            $this->assertEquals('problemDeployerMismatchedInputFile', $e->getMessage());
        }
    }

    /**
     * Test that we are able to submit a problem with an interactive/ directory.
     */
    public function testValidProblemInteractive() {
        // Get the problem data
        $problemData = ProblemsFactory::getRequest(new ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos_interactive.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting($this->createFileUploaderMock());

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
        $this->assertTrue($problemArtifacts->exists('interactive/triangulos.idl'));
        $this->assertTrue($problemArtifacts->exists('interactive/Main.cpp'));
        $this->assertTrue($problemArtifacts->exists('interactive/Main.distrib.cpp'));
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

        // Verify that the templates were generated.
        $this->assertTrue(
            file_exists(TEMPLATES_PATH . "/{$problem->alias}/{$problem->commit}/{$problem->alias}_unix_cpp.tar.bz2")
        );
        $this->assertTrue(
            file_exists(TEMPLATES_PATH . "/{$problem->alias}/{$problem->commit}/{$problem->alias}_windows_cpp.zip")
        );
    }
}
