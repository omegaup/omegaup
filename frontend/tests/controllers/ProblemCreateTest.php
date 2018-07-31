<?php

/**
 * Tests for apiCreate in ProblemController
 *
 * @author joemmanuel
 */
require_once 'libs/FileHandler.php';

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
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Call the API
        $response = ProblemController::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals('testplan', $response['uploaded_files'][10]);

        // Verify data in DB
        $problems = ProblemsDAO::getByTitle($r['title']);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertEquals($r['title'], $problem->title);
        $this->assertEquals(substr($r['title'], 0, 32), $problem->alias);
        $this->assertEquals($r['validator'], $problem->validator);
        $this->assertEquals($r['time_limit'], $problem->time_limit);
        $this->assertEquals($r['memory_limit'], $problem->memory_limit);
        $this->assertEquals($r['order'], $problem->order);
        $this->assertEquals($r['source'], $problem->source);
        $this->assertEqualSets($r['languages'], $problem->languages);
        $this->assertEquals(0, $problem->slow);

        // Verify author username -> author id conversion
        $acl = ACLsDAO::getByPK($problem->acl_id);
        $user = UsersDAO::getByPK($acl->owner_id);
        $this->assertEquals($user->username, $r['author_username']);

        // Verify problem contents were copied
        $problemArtifacts = new ProblemArtifacts($problem->alias);

        $this->assertTrue($problemArtifacts->exists('testplan'));
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
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Call the API
        $response = ProblemController::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals('testplan', $response['uploaded_files'][10]);

        // Verify data in DB
        $problems = ProblemsDAO::getByTitle($r['title']);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);

        // Verify DB data
        $this->assertEquals(1, $problem->slow);
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
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Call the API
        $response = ProblemController::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals('testplan', $response['uploaded_files'][10]);

        // Verify data in DB
        $problems = ProblemsDAO::getByTitle($r['title']);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);

        // Verify DB data
        $this->assertEquals(0, $problem->slow);
    }

    /**
     * Basic test for creating a problem
     */
    public function testCreateValidProblemWithINCases() {
        // Get the problem data
        $problemData = ProblemsFactory::getRequest(new ProblemParams([
            'zipName' => OMEGAUP_RESOURCES_ROOT . 'mrkareltastic.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Call the API
        $response = ProblemController::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals('cases/g1.train0.in', $response['uploaded_files'][0]);
        $this->assertEquals('cases/g1.train0.out', $response['uploaded_files'][1]);

        // Verify problem contents were copied
        $problemArtifacts = new ProblemArtifacts($r['problem_alias']);

        $this->assertTrue($problemArtifacts->exists('testplan'));
        $this->assertTrue($problemArtifacts->exists('cases/in/g1.train0.in'));
        $this->assertTrue($problemArtifacts->exists('cases/out/g1.train0.out'));
        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/es.markdown'));
    }

    /**
     * Test that sends incomplete requests
     */
    public function testRequiredParameters() {
        // Get File Uploader Mock and tell Omegaup API to use it
        FileHandler::SetFileUploader($this->createFileUploaderMock());

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
                $response = ProblemController::apiCreate($r);
            } catch (InvalidParameterException $e) {
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
        FileHandler::SetFileUploader($this->createFileUploaderMock());

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
                $response = ProblemController::apiCreate($r);
            } catch (InvalidParameterException $e) {
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
            'zipName' => OMEGAUP_RESOURCES_ROOT . 'triangulos.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Call the API
        $response = ProblemController::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals('cases/1.in', $response['uploaded_files'][0]);

        // Verify data in DB
        $problems = ProblemsDAO::getByTitle($r['title']);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertEquals($r['title'], $problem->title);
        $this->assertEquals(substr($r['title'], 0, 32), $problem->alias);
        $this->assertEquals($r['validator'], $problem->validator);
        $this->assertEquals($r['time_limit'], $problem->time_limit);
        $this->assertEquals($r['memory_limit'], $problem->memory_limit);
        $this->assertEquals($r['order'], $problem->order);
        $this->assertEquals($r['source'], $problem->source);

        // Verify author username -> author id conversion
        $acl = ACLsDAO::getByPK($problem->acl_id);
        $user = UsersDAO::getByPK($acl->owner_id);
        $this->assertEquals($user->username, $r['author_username']);

        // Verify problem contents were copied
        $problemArtifacts = new ProblemArtifacts($problem->alias);

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
            'zipName' => OMEGAUP_RESOURCES_ROOT . 'nonutf8stmt.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Call the API
        $response = ProblemController::apiCreate($r);

        // Verify response
        $this->assertEquals('ok', $response['status']);

        // Get problem info from DB
        $problems = ProblemsDAO::getByTitle($r['title']);
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify problem contents were copied
        $problemArtifacts = new ProblemArtifacts($problem->alias);
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
            'zipName' => OMEGAUP_RESOURCES_ROOT . 'imagetest.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Call the API
        $response = ProblemController::apiCreate($r);

        // Verify response
        $this->assertEquals('ok', $response['status']);

        // Verify problem contents were copied
        $problemArtifacts = new ProblemArtifacts($r['problem_alias']);
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
        $response = ProblemController::apiDetails(new Request([
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
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Call the API
        $response = ProblemController::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals('testplan', $response['uploaded_files'][10]);

        // Verify data in DB
        $problems = ProblemsDAO::getByTitle($r['title']);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertEquals($r['title'], $problem->title);

        // Verify problem contents were copied
        $problemArtifacts = new ProblemArtifacts($problem->alias);

        $this->assertTrue($problemArtifacts->exists('testplan'));
        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/en.markdown'));
    }

    /**
     * Basic test for uploadin problem without statement
     *
     * @expectedException InvalidParameterException
     */
    public function testCreateProblemWithoutStatement() {
        // Get the problem data
        $problemData = ProblemsFactory::getRequest(new ProblemParams([
            'zipName' => OMEGAUP_RESOURCES_ROOT . 'nostmt.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Call the API
        $response = ProblemController::apiCreate($r);
    }

    /**
     * Basic test for uploadin problem missing outputs
     *
     * @expectedException InvalidParameterException
     */
    public function testCreateProblemMissingOutput() {
        // Get the problem data
        $problemData = ProblemsFactory::getRequest(new ProblemParams([
            'zipName' => OMEGAUP_RESOURCES_ROOT . 'missingout.zip'
        ]));
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Call the API
        $response = ProblemController::apiCreate($r);
    }
}
