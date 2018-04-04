<?php

/**
 * Description of UpdateProblem
 *
 * @author joemmanuel
 */

class UpdateProblemTest extends OmegaupTestCase {
    public function testProblemUpdateLanguages() {
        // Get a problem (with 'es' statements)
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'zipName' => OMEGAUP_RESOURCES_ROOT . 'triangulos.zip',
            'title' => 'Problem Language'
        ]));

        // Update statement
        $login = self::login($problemData['author']);

        $problem_languages = ProblemsLanguagesDAO::search([
            'problem_id' => $problemData['problem']->problem_id,
        ]);
        // This problem only has one language at this point
        $this->assertEquals(1, count($problem_languages));

        ProblemController::apiUpdateStatement(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'New statement is now more fun',
            'statement' => 'This is the new statement',
            'lang' => 'en'
        ]));

        // The problem has two languages at this point
        $problem_languages = ProblemsLanguagesDAO::search([
            'problem_id' => $problemData['problem']->problem_id,
        ]);
        $this->assertEquals(2, count($problem_languages));
    }

    public function testUpdateProblemTitleAndContents() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a run
        $runData[0] = RunsFactory::createRun($problemData, $contestData, $contestant);
        $runData[1] = RunsFactory::createRun($problemData, $contestData, $contestant);

        // Grade the run
        RunsFactory::gradeRun($runData[0]);
        RunsFactory::gradeRun($runData[1]);

        // Get File Uploader Mock and tell Omegaup API to use it
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Update Problem calls grader to rejudge, we need to detour grader calls
        // We will submit 2 runs to the problem, a call to grader to rejudge them
        $this->detourGraderCalls($this->exactly(1));

        $login = self::login($problemData['author']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'title' => 'new title',
            'time_limit' => 12345,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Changed some properties',
        ]);

        // Set file upload context
        $_FILES['problem_contents']['tmp_name'] = OMEGAUP_RESOURCES_ROOT.'triangulos.zip';

        // Call API
        $response = ProblemController::apiUpdate($r);

        // Verify data in DB
        $problem_mask = new Problems();
        $problem_mask->title = $r['title'];
        $problems = ProblemsDAO::search($problem_mask);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));

        // Validate rsponse
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(true, $response['rejudged']);
        $this->assertEquals('cases/1.in', $response['uploaded_files'][0]);

        // Verify problem contents were copied
        $problemArtifacts = new ProblemArtifacts($r['problem_alias']);

        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/es.html'));
        $this->assertFalse($problemArtifacts->exists('examples/sample.in'));

        // Check update in statements
        $statement = $problemArtifacts->get('statements/es.html');
        $this->assertContains('perímetro', $statement);

        // Call API again to add an example, should not trigger rejudge.
        $_FILES['problem_contents']['tmp_name'] = OMEGAUP_RESOURCES_ROOT.'triangulos-examples.zip';
        $response = ProblemController::apiUpdate($r);
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(false, $response['rejudged']);
        $this->assertTrue($problemArtifacts->exists('examples/sample.in'));
    }

    public function testUpdateProblemWithValidLanguages() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'title' => 'valid-languages'
        ]));

        $login = self::login($problemData['author']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'languages' => 'hs,java,pl',
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Changed alias and languages',
        ]);

        //Call API
        $response = ProblemController::apiUpdate($r);

        // Verify data in DB
        $problem_mask = new Problems();
        $problem_mask->alias = $r['alias'];
        $problems = ProblemsDAO::search($problem_mask);

        // Check that we only retrieved 1 element
        $this->assertEquals(1, count($problems));
        $this->assertEqualSets($r['languages'], $problems[0]->languages);

        // Validate response
        $this->assertEquals('ok', $response['status']);
    }

    /**
     * @expectedException InvalidParameterException
     */
    public function testUpdateProblemWithInvalidLanguages() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        $login = self::login($problemData['author']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'languages' => 'cows,hs,java,pl',
            'problem_alias' => $problemData['request']['alias'],
            'message' => 'Changed invalid languages',
        ]);

        // Log in as contest director

        //Call API
        $response = ProblemController::apiUpdate($r);
    }

    /**
     * Test apiUpdateStatement
     */
    public function testProblemStatementUpdate() {
        // Get a problem (with 'es' statements)
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'zipName' => OMEGAUP_RESOURCES_ROOT . 'triangulos.zip'
        ]));

        // Update statement
        $statement = 'This is the new statement \$x\$';
        $login = self::login($problemData['author']);
        $response = ProblemController::apiUpdateStatement(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Statement is now more fun',
            'statement' => $statement
        ]));

        $this->assertEquals($response['status'], 'ok');

        // Check statment contents
        $problemArtifacts = new ProblemArtifacts($problemData['request']['problem_alias']);

        $statementHtmlContents = $problemArtifacts->get('statements/es.html');
        $statementMarkdownContents = $problemArtifacts->get('statements/es.markdown');

        $this->assertContains('<p>This is the new statement \$x\$</p>', $statementHtmlContents);
        $this->assertContains($statement, $statementMarkdownContents);
    }

    /**
     * Test apiUpdateStatement with embedded imgs via data URI
     */
    public function testProblemStatementUpdateWithImagesAsDataURI() {
        // Get a problem (with 'es' statements)
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'zipName' => OMEGAUP_RESOURCES_ROOT . 'triangulos.zip'
        ]));

        // Update statement
        $imgUri = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==';
        $imgFilename = 'af6a4603e039cca2f6823d287f6c87e561aa6e68.png';

        $statement = "This is the new statement with an image omg ![Alt text]($imgUri \"Optional title\")";
        $login = self::login($problemData['author']);
        $response = ProblemController::apiUpdateStatement(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Statement now contains images',
            'statement' => $statement
        ]));

        $this->assertEquals($response['status'], 'ok');

        // Check statment contents
        $problemArtifacts = new ProblemArtifacts($problemData['request']['problem_alias']);
        $statementHtmlContents = $problemArtifacts->get('statements/es.html');
        $statementMarkdownContents = $problemArtifacts->get('statements/es.markdown');

        $this->assertFileExists(IMAGES_PATH . $imgFilename);
        $this->assertTrue($problemArtifacts->exists("statements/$imgFilename"));
        $this->assertContains('<img src="' . IMAGES_URL_PATH . $imgFilename . '" alt="Alt text" title="Optional title" />', $statementHtmlContents);
        $this->assertContains('![Alt text](' . IMAGES_URL_PATH . "$imgFilename \"Optional title\")", $statementMarkdownContents);
    }

    /**
     * Tests update problem: on error, original contents should persist
     */
    public function testUpdateProblemFailed() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get File Uploader Mock and tell Omegaup API to use it
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Update Problem calls grader to rejudge, we need to detour grader calls
        // We will submit 2 runs to the problem, so we can expect 2 calls to grader
        // to rejudge them
        $this->detourGraderCalls($this->exactly(0));

        // Prepare request
        $login = self::login($problemData['author']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'title' => 'new title',
            'time_limit' => 12345,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'This shoudl fail',
        ]);

        // Set file upload context. This problem should fail
        $_FILES['problem_contents']['tmp_name'] = OMEGAUP_RESOURCES_ROOT.'nostmt.zip';

        // Call API. Should fail
        try {
            ProblemController::apiUpdate($r);
        } catch (InvalidParameterException $e) {
            // Expected
        }

        // Verify contents were not erased
        $problemArtifacts = new ProblemArtifacts($r['problem_alias']);

        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/es.html'));

        // Check statements still is the original one
        $statement = $problemArtifacts->get('statements/es.html');
        $this->assertContains('<h1>Entrada</h1>', $statement);
    }

    /**
     * Tests problem admin can edit a problem
     */
    public function testUpdateProblemWithProblemAdmin() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Create our new admin
        $problemAdmin = UserFactory::createUser();

        // Add admin to the problem
        $adminLogin = self::login($problemData['author']);
        $response = ProblemController::apiAddAdmin(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $problemAdmin->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $this->assertEquals('ok', $response['status']);

        //Call API
        $newTitle = 'new title coadmin';
        $login = self::login($problemAdmin);
        $response = ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'title' => $newTitle,
            'message' => 'Admin powers',
        ]));

        // Verify data in DB
        $problem_mask = new Problems();
        $problem_mask->title = $newTitle;
        $problems = ProblemsDAO::search($problem_mask);

        $this->assertTrue(!is_null($problems));
    }

    /**
     * Tests removed problem admin can't edit a problem anymore
     *
     * @expectedException ForbiddenAccessException
     */
    public function testUpdateProblemWithRemovedProblemAdmin() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Create our new admin
        $problemAdmin = UserFactory::createUser();

        // Add admin to the problem
        $adminLogin = self::login($problemData['author']);
        $response = ProblemController::apiAddAdmin(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $problemAdmin->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $this->assertEquals('ok', $response['status']);

        // Then remove the user
        $response = ProblemController::apiRemoveAdmin(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $problemAdmin->username,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertEquals('ok', $response['status']);

        //Call API
        $newTitle = 'new title coadmin';
        $login = self::login($problemAdmin);
        $response = ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'title' => $newTitle,
            'message' => 'Non-admin powers',
        ]));

        // Verify data in DB
        $problem_mask = new Problems();
        $problem_mask->title = $newTitle;
        $problems = ProblemsDAO::search($problem_mask);
    }

    /**
     * Tests problem admins list API
     */
    public function testProblemAdmins() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Create our new admin
        $problemAdmin = UserFactory::createUser();

        // Add admin to the problem
        $login = self::login($problemData['author']);
        $response = ProblemController::apiAddAdmin(new Request([
            'usernameOrEmail' => $problemAdmin->username,
            'problem_alias' => $problemData['request']['problem_alias'],
            'auth_token' => $login->auth_token,
        ]));

        $this->assertEquals('ok', $response['status']);

        // Get the list of admins
        $response = ProblemController::apiAdmins(new Request([
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
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'zipName' => OMEGAUP_RESOURCES_ROOT . 'triangulos.zip',
            'visibility' => 0
        ]));

        // Normal user shouldn't even be able to see the problem.
        $reviewer = UserFactory::createUser();
        $login = self::login($reviewer);
        try {
            ProblemController::apiDetails(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
            $this->fail('Should not have been able to see the problem');
        } catch (ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'problemIsPrivate');
        }

        // Promote to reviewer, can see the problem now.
        UserFactory::addSystemRole($reviewer, Authorization::REVIEWER_ROLE);
        $response = ProblemController::apiList(new Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals($response['status'], 'ok');
        $this->assertArrayContainsWithPredicate($response['results'], function ($problem) use (&$problemData) {
            return $problem['alias'] == $problemData['request']['problem_alias'];
        });
        $response = ProblemController::apiDetails(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertEquals($response['status'], 'ok');

        // Update statement
        $statement = 'This is the new statement \$x\$';
        $response = ProblemController::apiUpdateStatement(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'message' => 'Statement is now more fun',
            'statement' => $statement
        ]));
        $this->assertEquals($response['status'], 'ok');

        // Add a tag
        $response = ProblemController::apiAddTag(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'name' => 'test',
        ]));
        $this->assertEquals($response['status'], 'ok');
    }

    /**
     * Tests that problems cannot change their visibility under some scenarios.
     */
    public function testProblemUpdateVisibility() {
        // Create a public problem
        $problemData = ProblemsFactory::createProblem();
        $login = self::login($problemData['author']);
        $problem = $problemData['problem'];

        // Make it private.
        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => ProblemController::VISIBILITY_PRIVATE,
            'message' => 'public -> private',
        ]));

        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => ProblemController::VISIBILITY_PRIVATE,
            'message' => 'no-op',
        ]));
        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'message' => 'no-op',
        ]));

        // Make it public
        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => ProblemController::VISIBILITY_PUBLIC,
            'message' => 'private -> public',
        ]));

        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => ProblemController::VISIBILITY_PUBLIC,
            'message' => 'no-op',
        ]));
        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'message' => 'no-op',
        ]));

        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => ProblemController::VISIBILITY_PUBLIC_BANNED,
            'message' => 'public -> banned',
        ]));

        try {
            ProblemController::apiUpdate(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'visibility' => ProblemController::VISIBILITY_PROMOTED,
                'message' => 'public -> promoted',
            ]));
            $this->fail('Cannot ban problem from API');
        } catch (InvalidParameterException $e) {
        }

        // Ban the problem.
        $problem->visibility = ProblemController::VISIBILITY_PUBLIC_BANNED;
        ProblemsDAO::save($problem);

        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => ProblemController::VISIBILITY_PUBLIC_BANNED,
            'message' => 'no-op',
        ]));
        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'message' => 'no-op',
        ]));

        try {
            ProblemController::apiUpdate(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'visibility' => ProblemController::VISIBILITY_PRIVATE,
                'message' => 'banned -> private',
            ]));
            $this->fail('Cannot un-ban problem from API');
        } catch (InvalidParameterException $e) {
            $this->assertEquals($e->getMessage(), 'qualityNominationProblemHasBeenBanned');
        }

        try {
            ProblemController::apiUpdate(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'visibility' => ProblemController::VISIBILITY_PUBLIC,
                'message' => 'banned -> public',
            ]));
            $this->fail('Cannot un-ban problem from API');
        } catch (InvalidParameterException $e) {
            $this->assertEquals($e->getMessage(), 'qualityNominationProblemHasBeenBanned');
        }

        // Promote the problem.
        $problem->visibility = ProblemController::VISIBILITY_PROMOTED;
        ProblemsDAO::save($problem);

        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => ProblemController::VISIBILITY_PROMOTED,
            'message' => 'no-op',
        ]));
        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'message' => 'no-op',
        ]));

        try {
            ProblemController::apiUpdate(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'visibility' => ProblemController::VISIBILITY_PRIVATE,
                'message' => 'promoted -> private',
            ]));
            $this->fail('Cannot un-promote problem from API');
        } catch (InvalidParameterException $e) {
            $this->assertEquals($e->getMessage(), 'qualityNominationProblemHasBeenPromoted');
        }

        try {
            ProblemController::apiUpdate(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'visibility' => ProblemController::VISIBILITY_PUBLIC,
                'message' => 'promoted -> public',
            ]));
            $this->fail('Cannot un-promote problem from API');
        } catch (InvalidParameterException $e) {
            $this->assertEquals($e->getMessage(), 'qualityNominationProblemHasBeenPromoted');
        }
    }
}
