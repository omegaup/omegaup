<?php

/**
 * Description of UpdateProblem
 *
 * @author joemmanuel
 */

class UpdateProblemTest extends OmegaupTestCase {
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
            'problem_alias' => $problemData['request']['alias'],
            'stack_limit' => 12345,
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
        $targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r['problem_alias'] . DIRECTORY_SEPARATOR;

        $this->assertFileExists($targetpath . 'cases');
        $this->assertFileExists($targetpath . 'statements/es.html');
        $this->assertFileNotExists($targetpath . 'examples/sample.in');

        // Check update in statements
        $statement = file_get_contents($targetpath . 'statements' . DIRECTORY_SEPARATOR . 'es.html');
        $this->assertContains('perímetro', $statement);

        $this->assertEquals(12345, $problems[0]->stack_limit);

        // Call API again to add an example, should not trigger rejudge.
        $_FILES['problem_contents']['tmp_name'] = OMEGAUP_RESOURCES_ROOT.'triangulos-examples.zip';
        $response = ProblemController::apiUpdate($r);
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(false, $response['rejudged']);
        $this->assertFileExists($targetpath . 'examples/sample.in');
    }

    public function testUpdateProblemWithValidLanguages() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem(null, 'valid-languages');

        $login = self::login($problemData['author']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'languages' => 'hs,java,pl',
            'problem_alias' => $problemData['request']['alias'],
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
        $problemData = ProblemsFactory::createProblem(OMEGAUP_RESOURCES_ROOT . 'triangulos.zip');

        // Update statement
        $statement = 'This is the new statement \$x\$';
        $login = self::login($problemData['author']);
        $response = ProblemController::apiUpdateStatement(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'message' => 'Statement is now more fun',
            'statement' => $statement
        ]));

        $this->assertEquals($response['status'], 'ok');

        // Check statment contents
        $targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problemData['request']['alias'] . DIRECTORY_SEPARATOR;
        $statementHtmlContents = file_get_contents($targetpath . 'statements' . DIRECTORY_SEPARATOR . 'es.html');
        $statementMarkdownContents = file_get_contents($targetpath . 'statements' . DIRECTORY_SEPARATOR . 'es.markdown');

        $this->assertContains('<p>This is the new statement \$x\$</p>', $statementHtmlContents);
        $this->assertContains($statement, $statementMarkdownContents);
    }

    /**
     * Test apiUpdateStatement with embedded imgs via data URI
     */
    public function testProblemStatementUpdateWithImagesAsDataURI() {
        // Get a problem (with 'es' statements)
        $problemData = ProblemsFactory::createProblem(OMEGAUP_RESOURCES_ROOT . 'triangulos.zip');

        // Update statement
        $imgUri = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==';
        $imgFilename = 'af6a4603e039cca2f6823d287f6c87e561aa6e68.png';

        $statement = "This is the new statement with an image omg ![Alt text]($imgUri \"Optional title\")";
        $login = self::login($problemData['author']);
        $response = ProblemController::apiUpdateStatement(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'message' => 'Statement now contains images',
            'statement' => $statement
        ]));

        $this->assertEquals($response['status'], 'ok');

        // Check statment contents
        $targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problemData['request']['alias'] . DIRECTORY_SEPARATOR;
        $statementHtmlContents = file_get_contents($targetpath . 'statements' . DIRECTORY_SEPARATOR . 'es.html');
        $statementMarkdownContents = file_get_contents($targetpath . 'statements' . DIRECTORY_SEPARATOR . 'es.markdown');

        $this->assertFileExists(IMAGES_PATH . $imgFilename);
        $this->assertFileExists("$targetpath/statements/$imgFilename");
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
            'problem_alias' => $problemData['request']['alias'],
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
        $targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r['problem_alias'] . DIRECTORY_SEPARATOR;
        $temppath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r['problem_alias'] . '_tmp' . DIRECTORY_SEPARATOR;

        $this->assertFileNotExists($temppath);
        $this->assertFileExists($targetpath . 'cases');
        $this->assertFileExists($targetpath . 'statements' . DIRECTORY_SEPARATOR . 'es.html');

        // Check statements still is the original one
        $statement = file_get_contents($targetpath . 'statements' . DIRECTORY_SEPARATOR . 'es.html');
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
            'problem_alias' => $problemData['request']['alias'],
        ]));

        $this->assertEquals('ok', $response['status']);

        //Call API
        $newTitle = 'new title coadmin';
        $login = self::login($problemAdmin);
        $response = ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
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
            'problem_alias' => $problemData['request']['alias'],
        ]));

        $this->assertEquals('ok', $response['status']);

        // Then remove the user
        $response = ProblemController::apiRemoveAdmin(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $problemAdmin->username,
            'problem_alias' => $problemData['request']['alias'],
        ]));
        $this->assertEquals('ok', $response['status']);

        //Call API
        $newTitle = 'new title coadmin';
        $login = self::login($problemAdmin);
        $response = ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
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
            'problem_alias' => $problemData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]));

        $this->assertEquals('ok', $response['status']);

        // Get the list of admins
        $response = ProblemController::apiAdmins(new Request([
            'problem_alias' => $problemData['request']['alias'],
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
        $problemData = ProblemsFactory::createProblem(OMEGAUP_RESOURCES_ROOT . 'triangulos.zip', null, 0);

        // Normal user shouldn't even be able to see the problem.
        $reviewer = UserFactory::createUser();
        $login = self::login($reviewer);
        try {
            ProblemController::apiDetails(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['alias'],
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
            return $problem['alias'] == $problemData['request']['alias'];
        });
        $response = ProblemController::apiDetails(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
        ]));
        $this->assertEquals($response['status'], 'ok');

        // Update statement
        $statement = 'This is the new statement \$x\$';
        $response = ProblemController::apiUpdateStatement(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'message' => 'Statement is now more fun',
            'statement' => $statement
        ]));
        $this->assertEquals($response['status'], 'ok');

        // Add a tag
        $response = ProblemController::apiAddTag(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
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
            'visibility' => 0,
            'message' => 'public -> private',
        ]));

        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => 0,
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
            'visibility' => 1,
            'message' => 'private -> public',
        ]));

        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => 1,
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
                'visibility' => -1,
                'message' => 'public -> banned',
            ]));
            $this->fail('Cannot ban problem from API');
        } catch (InvalidParameterException $e) {
        }

        try {
            ProblemController::apiUpdate(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problem->alias,
                'visibility' => 2,
                'message' => 'public -> promoted',
            ]));
            $this->fail('Cannot ban problem from API');
        } catch (InvalidParameterException $e) {
        }

        // Ban the problem.
        $problem->visibility = -1;
        ProblemsDAO::save($problem);

        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => -1,
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
                'visibility' => 0,
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
                'visibility' => 1,
                'message' => 'banned -> public',
            ]));
            $this->fail('Cannot un-ban problem from API');
        } catch (InvalidParameterException $e) {
            $this->assertEquals($e->getMessage(), 'qualityNominationProblemHasBeenBanned');
        }

        // Promote the problem.
        $problem->visibility = 2;
        ProblemsDAO::save($problem);

        ProblemController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problem->alias,
            'visibility' => 2,
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
                'visibility' => 0,
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
                'visibility' => 1,
                'message' => 'promoted -> public',
            ]));
            $this->fail('Cannot un-promote problem from API');
        } catch (InvalidParameterException $e) {
            $this->assertEquals($e->getMessage(), 'qualityNominationProblemHasBeenPromoted');
        }
    }
}
