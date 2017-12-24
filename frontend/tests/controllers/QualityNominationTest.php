<?php

class QualityNominationTest extends OmegaupTestCase {
    public static function testGetNominationsHasAuthorAndNominatorSet() {
        $problemData = ProblemsFactory::createProblem();
        $contestant = UserFactory::createUser();

        $login = self::login($contestant);
        QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]),
        ]));

        $nominations = QualityNominationsDAO::getNominations(null, null);
        self::assertArrayHasKey('author', $nominations[0]);
        self::assertArrayHasKey('nominator', $nominations[0]);
    }

    public static function testGetByIdHasAuthorAndNominatorSet() {
        $problemData = ProblemsFactory::createProblem();
        $contestant = UserFactory::createUser();

        $login = self::login($contestant);
        $result = QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]),
        ]));

        $nomination = QualityNominationsDAO::getById($result['qualitynomination_id']);
        self::assertArrayHasKey('author', $nomination);
        self::assertArrayHasKey('nominator', $nomination);
        self::assertEquals($contestant->username, $nomination['nominator']['username']);
    }

    public function testApiDetailsReturnsFieldsRequiredByUI() {
        $problemData = ProblemsFactory::createProblem();
        $user = UserFactory::createUser();

        $contents = json_encode([
                 'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                 ],
                 'rationale' => 'ew',
                 'reason' => 'offensive',
            ]);

        $login = self::login($user);
        $qualitynomination = QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => $contents,
        ]));

        // Login as a reviewer and approve ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new Request([
            'auth_token' => $reviewerLogin->auth_token,
            'qualitynomination_id' => $qualitynomination['qualitynomination_id']]);

        $details = QualityNominationController::apiDetails($request);
        $this->assertEquals('demotion', $details['nomination'], 'Should have set demotion');
        $this->assertEquals($user->username, $details['nominator']['username'], 'Should have set user');
        $this->assertEquals($problemData['request']['alias'], $details['problem']['alias'], 'Should have set problem');
        $this::assertArrayHasKey('author', $details);
        $this->assertEquals(json_decode($contents, true), $details['contents'], 'Should have set contents');
        $this->assertEquals(true, $details['reviewer'], 'Should have set reviewer');
        $this->assertEquals($qualitynomination['qualitynomination_id'], $details['qualitynomination_id'], 'Should have set qualitynomination_id');
    }

    /**
     * Basic test. Check that before nominating a problem for quality, the user
     * must have solved it first.
     */
    public function testMustSolveBeforeNominatingItForPromotion() {
        $problemData = ProblemsFactory::createProblem();
        $contestant = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);

        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'promotion',
            'contents' => json_encode([
                'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                ],
                'source' => 'omegaUp',
                'tags' => [],
            ]),
        ]);

        try {
            QualityNominationController::apiCreate($r);
            $this->fail('Should not have been able to nominate the problem');
        } catch (PreconditionFailedException $e) {
            // still expected.
        }

        RunsFactory::gradeRun($runData);

        QualityNominationController::apiCreate($r);

        $response = QualityNominationController::apiMyList(new Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals(1, count($response['nominations']));
        $nomination = $response['nominations'][0];
        $this->assertEquals(
            $problemData['request']['alias'],
            $nomination['problem']['alias']
        );
        $this->assertEquals(
            $problemData['request']['alias'],
            $nomination['problem']['alias']
        );
        $this->assertEquals(
            QualityNominationController::REVIEWERS_PER_NOMINATION,
            count($nomination['votes'])
        );

        $details = QualityNominationController::apiDetails(new Request([
            'auth_token' => $login->auth_token,
            'qualitynomination_id' => $nomination['qualitynomination_id'],
        ]));
        $this->assertEquals(
            $contestant->username,
            $details['nominator']['username']
        );
        $this->assertNotNull($details['original_contents']);
    }

    /**
     * Check that before suggesting improvements to a problem, the user must
     * have solved it first.
     */
    public function testMustSolveBeforeSuggesting() {
        $problemData = ProblemsFactory::createProblem();
        $contestant = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);

        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'suggestion',
            'contents' => json_encode([
                // No difficulty!
                'quality' => 3,
                'tags' => [],
            ]),
        ]);

        try {
            QualityNominationController::apiCreate($r);
            $this->fail('Should not have been able to make suggestion about the problem');
        } catch (PreconditionFailedException $e) {
            // still expected.
        }

        RunsFactory::gradeRun($runData);

        $response = QualityNominationController::apiCreate($r);

        $r['qualitynomination_id'] = $response['qualitynomination_id'];
        $nomination = QualityNominationController::apiDetails($r);
        $this->assertEquals(
            $problemData['request']['alias'],
            $nomination['problem']['alias']
        );
    }

    /**
     * Basic test. Check that before nominating a problem for demotion, the
     * user might not have solved it first.
     */
    public function testNominatingForDemotion() {
        $problemData = ProblemsFactory::createProblem();
        $contestant = UserFactory::createUser();

        $login = self::login($contestant);
        QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]),
        ]));
    }

    public function testExtractAliasFromArgument() {
        $inputAndExpectedOutput =
                ['http://localhost:8080/arena/prueba/#problems/sumas' => 'sumas',
                 'http://localhost:8080/arena/prueba/practice/#problems/sumas' => 'sumas',
                 'http://localhost:8080/arena/problem/sumas#problems' => 'sumas',
                 'http://localhost:8080/course/prueba/assignment/prueba/#problems/sumas' => 'sumas',
                 'http://localhost:8080/arena/prueba/#problems/sumas29187' => 'sumas29187',
                 'http://localhost:8080/arena/prueba/practice/#problems/sumas_29187' => 'sumas_29187',
                 'http://localhost:8080/arena/problem/_sumas29187-#problems' => '_sumas29187-',
                 'http://localhost:8080/course/prueba/assignment/prueba/#problems/___asd_-_23-2-_' => '___asd_-_23-2-_'];

        foreach ($inputAndExpectedOutput as $input => $expectedOutput) {
            $actualOutput = QualityNominationController::extractAliasFromArgument($input);
            $this->assertEquals($expectedOutput, $actualOutput, 'Incorrect alias was extracted from URL.');
        }
    }

    /**
     * Check that a non-reviewer user cannot change the status of a demotion qualitynomination.
     */
    public function testDemotionCannotBeResolvedByRegularUser() {
        $problemData = ProblemsFactory::createProblem();
        $user = UserFactory::createUser();

        $login = self::login($user);
        $qualitynomination = QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]),
        ]));

        $request = new Request([
            'auth_token' => $login->auth_token,
            'status' => 'approved',
            'qualitynomination_id' => $qualitynomination['qualitynomination_id']]);
        try {
            $response = QualityNominationController::apiResolve($request);
            $this->fail("Normal user shouldn't be able to resolve demotion");
        } catch (ForbiddenAccessException $e) {
            // Expected.
        }
    }

    /**
     * Check that a demotion can be approved and then reverted by a reviewer.
     */
    public function testDemotionCanBeApprovedAndLaterRevertedByReviewer() {
        $problemData = ProblemsFactory::createProblem();
        $user = UserFactory::createUser();

        $login = self::login($user);
        $qualitynomination = QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                 'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                 ],
                 'rationale' => 'ew',
                 'reason' => 'offensive',
            ]),
        ]));
        // Login as a reviewer and approve ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'approved',
            'problem_alias' => $problemData['request']['alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id']]);
        $response = QualityNominationController::apiResolve($request);

        $details = QualityNominationController::apiDetails($request);
        $this->assertEquals('approved', $details['nomination_status'], 'qualitynomination should have been marked as approved');

        $problem = ProblemController::apiDetails($request);
        $this->assertEquals(ProblemController::VISIBILITY_PUBLIC_BANNED, $problem['visibility'], 'Problem should have been public banned');

        // Revert ban.
        $request = new Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'denied',
            'problem_alias' => $problemData['request']['alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id']]);
        $response = QualityNominationController::apiResolve($request);

        $details = QualityNominationController::apiDetails($request);
        $this->assertEquals('denied', $details['nomination_status'], 'qualitynomination should have been marked as denied');

        $problem = ProblemController::apiDetails($request);
        $this->assertEquals(ProblemController::VISIBILITY_PUBLIC, $problem['visibility'], 'Problem should have been made public');
    }

    /**
     * Check that a demotion approved by a reviewer sends an email to the problem creator.
     */
    public function testDemotionApprovedByReviewerAndSendMail() {
        $emailSender = new ScopedEmailSender();
        $problemData = ProblemsFactory::createProblem();
        $user = UserFactory::createUser();

        $login = self::login($user);
        $qualitynomination = QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                 'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                 ],
                 'rationale' => 'qwert',
                 'reason' => 'offensive',
            ]),
        ]));
        // Login as a reviewer and approve ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'approved',
            'problem_alias' => $problemData['request']['alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id']]);
        $response = QualityNominationController::apiResolve($request);

        $this->assertContains($problemData['problem']->title, $emailSender::$listEmails[0]['subject']);
        $this->assertContains($problemData['author']->name, $emailSender::$listEmails[0]['body']);
        $this->assertContains('qwert', $emailSender::$listEmails[0]['body']);
        $this->assertEquals(1, count($emailSender::$listEmails));
    }

    /**
     * Check that a demotion can be denied by a reviewer.
     */
    public function testDemotionCanBeDeniedByReviewer() {
        $problemData = ProblemsFactory::createProblem(null /* zipName */, null /* title */, ProblemController::VISIBILITY_PUBLIC);
        $user = UserFactory::createUser();

        $login = self::login($user);
        $qualitynomination = QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                 'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                 ],
                 'rationale' => 'ew',
                 'reason' => 'offensive',
            ]),
        ]));
        // Login as a reviewer and deny ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'denied',
            'problem_alias' => $problemData['request']['alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id']]);
        $response = QualityNominationController::apiResolve($request);

        $details = QualityNominationController::apiDetails($request);
        $this->assertEquals('denied', $details['nomination_status'], 'qualitynomination should have been marked as denied');

        $problem = ProblemController::apiDetails($request);
        $this->assertEquals(ProblemController::VISIBILITY_PUBLIC, $problem['visibility'], 'Problem should have remained public');
    }

    /**
     * Check that a demotion can be approved and then reopned by a reviewer.
     */
    public function testDemotionCanBeApprovedAndThenReopenedByReviewer() {
        $problemData = ProblemsFactory::createProblem();
        $user = UserFactory::createUser();

        $login = self::login($user);
        $qualitynomination = QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                 'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                 ],
                 'rationale' => 'ew',
                 'reason' => 'offensive',
            ]),
        ]));
        // Login as a reviewer and approve ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'approved',
            'problem_alias' => $problemData['request']['alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id']]);
        $response = QualityNominationController::apiResolve($request);

        $details = QualityNominationController::apiDetails($request);
        $this->assertEquals('approved', $details['nomination_status'], 'qualitynomination should have been marked as approved');

        $problem = ProblemController::apiDetails($request);
        $this->assertEquals(ProblemController::VISIBILITY_PUBLIC_BANNED, $problem['visibility'], 'Problem should have been public banned');

        // Reopen demotion request.
        $request = new Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'open',
            'problem_alias' => $problemData['request']['alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id']]);
        $response = QualityNominationController::apiResolve($request);

        $details = QualityNominationController::apiDetails($request);
        $this->assertEquals('open', $details['nomination_status'], 'qualitynomination should have been re-opened');

        $problem = ProblemController::apiDetails($request);
        $this->assertEquals(ProblemController::VISIBILITY_PUBLIC_BANNED, $problem['visibility'], 'Problem should have remained public banned');
    }

    /**
     * Check that a demotion of a private problem can be approved and
     * then denied, and it keeps its original visibility
     */
    public function testDemotionOfPrivateProblemApprovedAndThenDeniedKeepsItsOriginalVisibility() {
        $problemData = ProblemsFactory::createProblem(null, null, ProblemController::VISIBILITY_PRIVATE);
        $user = UserFactory::createUser();

        $login = self::login($user);
        $qualitynomination = QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                 'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                 ],
                 'rationale' => 'ew',
                 'reason' => 'offensive',
            ]),
        ]));
        // Login as a reviewer and approve ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'approved',
            'problem_alias' => $problemData['request']['alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id']]);
        $response = QualityNominationController::apiResolve($request);

        $details = QualityNominationController::apiDetails($request);
        $this->assertEquals('approved', $details['nomination_status'], 'qualitynomination should have been marked as approved');

        $problem = ProblemController::apiDetails($request);
        $this->assertEquals(ProblemController::VISIBILITY_PRIVATE_BANNED, $problem['visibility'], 'Problem should have been private banned');

        // Reopen demotion request.
        $request = new Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'denied',
            'problem_alias' => $problemData['request']['alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id']]);
        $response = QualityNominationController::apiResolve($request);

        $details = QualityNominationController::apiDetails($request);
        $this->assertEquals('denied', $details['nomination_status'], 'qualitynomination should have been re-opened');

        $problem = ProblemController::apiDetails($request);
        $this->assertEquals(ProblemController::VISIBILITY_PRIVATE, $problem['visibility'], 'Problem should have been private');
    }

    /**
     * Check that before a duplicate nomination needs to have a valid original problem.
     */
    public function testNominatingForDuplicate() {
        $originalProblemData = ProblemsFactory::createProblem();
        $problemData = ProblemsFactory::createProblem();
        $contestant = UserFactory::createUser();

        $login = self::login($contestant);

        try {
            QualityNominationController::apiCreate(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['alias'],
                'nomination' => 'demotion',
                'contents' => json_encode([
                    'rationale' => 'ew',
                    'reason' => 'duplicate',
                ]),
            ]));
            $this->fail('Missing "original" should have been caught');
        } catch (InvalidParameterException $e) {
        }

        try {
            QualityNominationController::apiCreate(new Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['alias'],
                'nomination' => 'demotion',
                'contents' => json_encode([
                    'rationale' => 'otro sumas',
                    'reason' => 'duplicate',
                    'original' => '$invalid problem alias$',
                ]),
            ]));
            $this->fail('Invalid "original" should have been caught');
        } catch (NotFoundException $e) {
        }

        QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'otro sumas',
                'reason' => 'duplicate',
                'original' => $originalProblemData['request']['alias'],
            ]),
        ]));

        QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'otro sumas',
                'reason' => 'duplicate',
                'original' => 'https://omegaup.com/arena/problem/' . $originalProblemData['request']['alias'] . '#problems',
            ]),
        ]));
    }

    /**
     * Nomination list test.
     */
    public function testNominationList() {
        $problemData = ProblemsFactory::createProblem();
        $contestant = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        $login = self::login($contestant);
        QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'promotion',
            'contents' => json_encode([
                'rationale' => 'cool!',
                'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                ],
                'source' => 'omegaUp',
                'tags' => ['ez-pz'],
            ]),
        ]));

        // Login as an arbitrary reviewer.
        $login = self::login(QualityNominationFactory::$reviewers[0]);
        $response = QualityNominationController::apiList(new Request([
            'auth_token' => $login->auth_token,
        ]));
        $nomination = $this->findByPredicate(
            $response['nominations'],
            function ($nomination) use (&$problemData) {
                return $nomination['problem']['alias'] == $problemData['request']['alias'];
            }
        );
        $this->assertNotNull($nomination);
        $this->assertEquals(
            QualityNominationController::REVIEWERS_PER_NOMINATION,
            count($nomination['votes'])
        );

        // Login as one of the reviewers of that nomination.
        $reviewer = $this->findByPredicate(
            QualityNominationFactory::$reviewers,
            function ($reviewer) use (&$nomination) {
                return $reviewer->username == $nomination['votes'][0]['user']['username'];
            }
        );
        $this->assertNotNull($reviewer);
        $login = self::login($reviewer);
        $response = QualityNominationController::apiMyAssignedList(new Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertArrayContainsWithPredicate(
            $response['nominations'],
            function ($nomination) use (&$problemData) {
                return $nomination['problem']['alias'] == $problemData['request']['alias'];
            }
        );
    }

    /**
     * Test that nomination list by default only shows promotions or demotions.
     * All other nomination types should not appear on this list.
     */
    public function testNominationListDoesntShowSuggestionsOrDismisssal() {
        $problemData = ProblemsFactory::createProblem();
        $contestant = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        // Create promotion nomination.
        $login = self::login($contestant);
        QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'promotion',
            'contents' => json_encode([
                'rationale' => 'cool!',
                'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                ],
                'source' => 'omegaUp',
                'tags' => ['ez-pz'],
            ]),
        ]));

        // Create demotion nomination.
        $qualitynomination = QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]),
        ]));

        // Create dismissal nomination.
        QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'dismissal',
            'contents' => json_encode([]),
        ]));

        // Create dismissal nomination.
        QualityNominationFactory::createSuggestion(
            $login,
            $problemData['request']['alias'],
            null,
            1,
            ['DP', 'Math']
        );

        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $list = QualityNominationController::apiList(new Request([
            'auth_token' => $reviewerLogin->auth_token,
        ]));
        $this->assertEquals('ok', $list['status'], 'Status of apiList call is not ok');
        $this->assertGreaterThanOrEqual(2, count($list['nominations']), "List didn't return enough nominations");
        foreach ($list['nominations'] as $nomination) {
            $isPromotion = ($nomination['nomination'] == 'promotion');
            $isDemotion = ($nomination['nomination'] == 'demotion');
            $this->assertTrue(
                $isPromotion || $isDemotion,
                'Found a nomination of type ' + $nomination['nomination'] + '. Only promotion and demotion should be shown.'
            );
        }
    }

    /**
     * Check that before discard a problem, the user must
     * have solved it first.
     */
    public function testMustSolveBeforeDismissed() {
        $problemData = ProblemsFactory::createProblem();
        $contestant = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'dismissal',
            'contents' => json_encode([]),
        ]);
        try {
            QualityNominationController::apiCreate($r);
            $this->fail('Should not have been able to dismissed the problem');
        } catch (PreconditionFailedException $e) {
        }
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
        }
        $key = new QualityNominations([
            'user_id' => $r['current_user_id'],
            'problem_id' => $problem->problem_id,
            'nomination' => $r['nomination'],
            'contents' => json_encode([]), // re-encoding it for normalization.
            'status' => 'open',
        ]);
        $problem_dismissed = QualityNominationsDAO::search($key);
        RunsFactory::gradeRun($runData);
        try {
            $this->assertEquals(0, count($problem_dismissed), 'Should not have been able to dismiss the problem');
        } catch (PreconditionFailedException $e) {
        }
        try {
            QualityNominationController::apiCreate($r);
            $pd = QualityNominationsDAO::search($key);
            $this->assertGreaterThan(0, count($pd), 'The problem should have been dismissed');
        } catch (PreconditionFailedException $e) {
        }
    }

    public function testGetGlobalDifficultyAndQuality() {
        $problemData[0] = ProblemsFactory::createProblem();
        $problemData[1] = ProblemsFactory::createProblem();
        self::setUpSyntheticSuggestions($problemData);

        $actualGlobals = QualityNominationsDAO::getGlobalDifficultyAndQuality();
        $expectedGlobals = [23/11 /*quality*/, 54/16 /*difficulty*/];

        $this->assertEquals($expectedGlobals, $actualGlobals);
    }

    public function testGetSuggestionRowMap() {
        $problemData[0] = ProblemsFactory::createProblem();
        $problemData[1] = ProblemsFactory::createProblem();
        self::setUpSyntheticSuggestions($problemData);

        $actualResult[0] = QualityNominationsDAO::getProblemSuggestionAggregates($problemData[0]['problem']->problem_id);
        $actualResult[1] = QualityNominationsDAO::getProblemSuggestionAggregates($problemData[1]['problem']->problem_id);

        $expectedResult[0] = [
            'quality_sum' => 13,
            'quality_n' => 5,
            'difficulty_sum' => 25,
            'difficulty_n' => 7,
            'tags_n' => 15,
            'tags' => [
                'dp' => 6,
                'math' => 6,
                'matrices' => 1,
                'greedy' => 2,
                ]
            ];
        $expectedResult[1] = [
            'quality_sum' => 10,
            'quality_n' => 6,
            'difficulty_sum' => 29,
            'difficulty_n' => 9,
            'tags_n' => 15,
            'tags' => [
                'search' => 6,
                'geometry' => 4,
                'matrices' => 1,
                'math' => 2,
                'dp' => 2,
            ],
        ];
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testAggregateFeedback() {
        TagsDAO::save(new Tags(['name' => 'dp']));
        TagsDAO::save(new Tags(['name' => 'math']));
        TagsDAO::save(new Tags(['name' => 'matrices']));
        TagsDAO::save(new Tags(['name' => 'greedy']));
        TagsDAO::save(new Tags(['name' => 'geometry']));
        TagsDAO::save(new Tags(['name' => 'search']));

        $problemData[0] = ProblemsFactory::createProblem();
        $problemData[1] = ProblemsFactory::createProblem();
        self::setUpSyntheticSuggestions($problemData);

        // Ensure all suggestions are written to the database before invoking
        // the external script.
        self::commit();
        shell_exec('python3 ' . escapeshellarg(OMEGAUP_ROOT) . '/../stuff/cron/aggregate_feedback.py' .
                 ' --host ' . escapeshellarg(OMEGAUP_DB_HOST) .
                 ' --user ' . escapeshellarg(OMEGAUP_DB_USER) .
                 ' --database ' . escapeshellarg(OMEGAUP_DB_NAME) .
                 ' --password ' . escapeshellarg(OMEGAUP_DB_PASS));

        $newProblem[0] = ProblemsDAO::getByAlias($problemData[0]['request']['alias']);
        $newProblem[1] = ProblemsDAO::getByAlias($problemData[1]['request']['alias']);
        $this->assertEquals(3.48958, $newProblem[0]->difficulty, 'Wrong difficulty.', 0.001);
        $this->assertEquals(2.34545, $newProblem[0]->quality, 'Wrong quality.', 0.001);
        $this->assertEquals(3.27678, $newProblem[1]->difficulty, 'Wrong difficulty.', 0.001);
        $this->assertEquals(1.8595, $newProblem[1]->quality, 'Wrong quality.', 0.001);

        $tagArrayForProblem1 = ProblemsTagsDAO::getProblemTags(
            $newProblem[0],
            false /* public_only */,
            true /* includeAutogenerated */
        );
        $tagArrayForProblem2 = ProblemsTagsDAO::getProblemTags(
            $newProblem[1],
            false /* public_only */,
            true /* includeAutogenerated */
        );
        $extractName = function ($tag) {
            return $tag['name'];
        };
        $tags1 = array_map($extractName, $tagArrayForProblem1);
        $tags2 = array_map($extractName, $tagArrayForProblem2);
        $this->assertEquals($tags1, ['dp', 'math', 'greedy']);
        $this->assertEquals($tags2, ['dp', 'math', 'geometry', 'search']);
    }

    public function setUpSyntheticSuggestions($problemData) {
        // First, remove all suggestions.
        $filter = new QualityNominations([
            'nomination' => 'suggestion',
        ]);
        $allNominations = QualityNominationsDAO::search($filter);
        foreach ($allNominations as $nomination) {
            QualityNominationsDAO::delete($nomination);
        }

        // Setup synthetic data.
        $login = [];
        for ($i = 0; $i < 10; $i++) {
            $contestant = UserFactory::createUser();
            for ($j = 0; $j < 2; $j++) {
                $runData = RunsFactory::createRunToProblem($problemData[$j], $contestant);
                RunsFactory::gradeRun($runData);
            }
            $login[] = self::login($contestant);
        }

        QualityNominationFactory::createSuggestion(
            $login[0],
            $problemData[0]['request']['alias'],
            null,
            1,
            ['DP', 'Math']
        );
        QualityNominationFactory::createSuggestion(
            $login[1],
            $problemData[0]['request']['alias'],
            3,
            3,
            ['Math', 'DP']
        );
        QualityNominationFactory::createSuggestion(
            $login[2],
            $problemData[0]['request']['alias'],
            4,
            0,
            ['Matrices', 'Math']
        );
        QualityNominationFactory::createSuggestion(
            $login[3],
            $problemData[0]['request']['alias'],
            null,
            null,
            ['Math']
        );
        QualityNominationFactory::createSuggestion(
            $login[4],
            $problemData[0]['request']['alias'],
            3,
            4,
            ['DP', 'Math', 'Greedy']
        );
        QualityNominationFactory::createSuggestion(
            $login[5],
            $problemData[0]['request']['alias'],
            3,
            null,
            []
        );
        QualityNominationFactory::createSuggestion(
            $login[6],
            $problemData[0]['request']['alias'],
            null,
            1,
            []
        );
        QualityNominationFactory::createSuggestion(
            $login[7],
            $problemData[0]['request']['alias'],
            4,
            null,
            ['Greedy', 'DP']
        );
        QualityNominationFactory::createSuggestion(
            $login[8],
            $problemData[0]['request']['alias'],
            4,
            0,
            ['DP']
        );
        QualityNominationFactory::createSuggestion(
            $login[9],
            $problemData[0]['request']['alias'],
            4,
            4,
            ['DP', 'Math']
        );

        QualityNominationFactory::createSuggestion(
            $login[0],
            $problemData[1]['request']['alias'],
            4,
            1,
            ['Search', 'Geometry']
        );
        QualityNominationFactory::createSuggestion(
            $login[1],
            $problemData[1]['request']['alias'],
            1,
            1,
            ['Search', 'Geometry']
        );
        QualityNominationFactory::createSuggestion(
            $login[2],
            $problemData[1]['request']['alias'],
            4,
            3,
            ['Matrices', 'Search']
        );
        QualityNominationFactory::createSuggestion(
            $login[3],
            $problemData[1]['request']['alias'],
            3,
            null,
            ['Search']
        );
        QualityNominationFactory::createSuggestion(
            $login[4],
            $problemData[1]['request']['alias'],
            3,
            null,
            ['Search', 'Math', 'Geometry']
        );
        QualityNominationFactory::createSuggestion(
            $login[5],
            $problemData[1]['request']['alias'],
            3,
            null,
            []
        );
        QualityNominationFactory::createSuggestion(
            $login[6],
            $problemData[1]['request']['alias'],
            null,
            1,
            []
        );
        QualityNominationFactory::createSuggestion(
            $login[7],
            $problemData[1]['request']['alias'],
            3,
            null,
            ['Search', 'DP']
        );
        QualityNominationFactory::createSuggestion(
            $login[8],
            $problemData[1]['request']['alias'],
            4,
            1,
            ['DP']
        );
        QualityNominationFactory::createSuggestion(
            $login[9],
            $problemData[1]['request']['alias'],
            4,
            3,
            ['Geometry', 'Math']
        );
    }

    private static function commit() {
        global $conn;
        $conn->Execute('COMMIT');
    }

    public function testMostVotedTags() {
        $tags = [
            'DP' => 15,
            'Graph' => 10,
            'Binary Search' => 5,
            'Math' => 2,
            'Greedy' => 1,
        ];

        $this->assertEquals(
            QualityNominationsDAO::mostVotedTags($tags, 0.25),
            ['DP', 'Graph', 'Binary Search']
        );

        $this->assertEquals(
            QualityNominationsDAO::mostVotedTags($tags, 0.5),
            ['DP', 'Graph']
        );

        $this->assertEquals(
            QualityNominationsDAO::mostVotedTags($tags, 0.9),
            ['DP']
        );

        $this->assertEquals(
            QualityNominationsDAO::mostVotedTags($tags, 0.9),
            ['DP']
        );

        $this->assertEquals(
            QualityNominationsDAO::mostVotedTags($tags, 0.01),
            ['DP', 'Graph', 'Binary Search', 'Math', 'Greedy']
        );

        $tagsWithLittleVotes = [
            'DP' => 2,
            'Graph' => 1,
        ];

        $this->assertEquals(
            QualityNominationsDAO::mostVotedTags($tagsWithLittleVotes, 0.25),
            [],
            'There must be at least 5 votes.'
        );

        $tooManyTagsWithMaxVotes = [
            'T1' => 9, 'T2' => 9, 'T3' => 9, 'T4' => 9, 'T5' => 9, 'T6' => 9,
            'T7' => 9, 'T8' => 9, 'T9' => 9, 'T10' => 9, 'T11' => 9, 'T12' => 9];

        $this->assertEquals(
            QualityNominationsDAO::mostVotedTags($tooManyTagsWithMaxVotes, 0.25),
            [],
            'There must be a maximum number of tags to be assigned.'
        );
    }
}
