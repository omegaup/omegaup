<?php

class QualityNominationTest extends OmegaupTestCase {
    public function testGetNominationsHasAuthorAndNominatorSet() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        QualityNominationFactory::createQualityNomination(
            $contestant,
            $problemData['request']['problem_alias'],
            'demotion',
            [
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]
        );

        $nominations = \OmegaUp\DAO\QualityNominations::getNominations(
            null,
            null
        );
        self::assertArrayHasKey('author', $nominations[0]);
        self::assertArrayHasKey('nominator', $nominations[0]);
    }

    public function testGetByIdHasAuthorAndNominatorSet() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        $nomination = QualityNominationFactory::createQualityNomination(
            $contestant,
            $problemData['request']['problem_alias'],
            'demotion',
            [
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]
        );

        $nomination = \OmegaUp\DAO\QualityNominations::getById(
            $nomination->qualitynomination_id
        );
        self::assertArrayHasKey('author', $nomination);
        self::assertArrayHasKey('nominator', $nomination);
        self::assertEquals(
            $contestant->username,
            $nomination['nominator']['username']
        );
    }

    public function testApiDetailsReturnsFieldsRequiredByUI() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $contents = [
            'statements' => [
            'es' => [
                'markdown' => 'a + b',
            ],
            ],
            'rationale' => 'ew',
            'reason' => 'offensive',
        ];

        $qualitynomination = QualityNominationFactory::createQualityNomination(
            $user,
            $problemData['request']['problem_alias'],
            'demotion',
            $contents
        );

        // Login as a reviewer and approve ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new \OmegaUp\Request(
            [
                'auth_token' => $reviewerLogin->auth_token,
                'qualitynomination_id' => $qualitynomination->qualitynomination_id
            ]
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals(
            'demotion',
            $details['nomination'],
            'Should have set demotion'
        );
        $this->assertEquals(
            $user->username,
            $details['nominator']['username'],
            'Should have set user'
        );
        $this->assertEquals(
            $problemData['request']['problem_alias'],
            $details['problem']['alias'],
            'Should have set problem'
        );
        $this::assertArrayHasKey('author', $details);
        $this->assertEquals(
            $contents,
            $details['contents'],
            'Should have set contents'
        );
        $this->assertEquals(
            true,
            $details['reviewer'],
            'Should have set reviewer'
        );
        $this->assertEquals(
            $qualitynomination->qualitynomination_id,
            $details['qualitynomination_id'],
            'Should have set qualitynomination_id'
        );
    }

    /**
     * Basic test. Check that before nominating a problem for quality, the user
     * must have solved it first.
     */
    public function testMustSolveBeforeNominatingItForPromotion() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);

        $contents = [
            'statements' => [
                'es' => [
                    'markdown' => 'a + b',
                ],
            ],
            'source' => 'omegaUp',
            'tags' => [],
        ];

        try {
            QualityNominationFactory::createQualityNomination(
                $contestant,
                $problemData['request']['problem_alias'],
                'promotion',
                $contents
            );
            $this->fail('Should not have been able to nominate the problem');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            // still expected.
        }

        RunsFactory::gradeRun($runData);

        QualityNominationFactory::createQualityNomination(
            $contestant,
            $problemData['request']['problem_alias'],
            'promotion',
            $contents
        );

        $login = self::login($contestant);
        $response = \OmegaUp\Controllers\QualityNomination::apiMyList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals(1, count($response['nominations']));
        $nomination = $response['nominations'][0];
        $this->assertEquals(
            $problemData['request']['problem_alias'],
            $nomination['problem']['alias']
        );
        $this->assertEquals(
            $problemData['request']['problem_alias'],
            $nomination['problem']['alias']
        );
        $this->assertEquals(
            \OmegaUp\Controllers\QualityNomination::REVIEWERS_PER_NOMINATION,
            count($nomination['votes'])
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails(new \OmegaUp\Request([
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
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);

        $contents = [
            // No difficulty!
            'quality' => 3,
            'tags' => [],
        ];

        try {
            QualityNominationFactory::createQualityNomination(
                $contestant,
                $problemData['request']['problem_alias'],
                'suggestion',
                $contents
            );
            $this->fail(
                'Should not have been able to make suggestion about the problem'
            );
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            // still expected.
        }

        RunsFactory::gradeRun($runData);

        $response = QualityNominationFactory::createQualityNomination(
            $contestant,
            $problemData['request']['problem_alias'],
            'suggestion',
            $contents
        );

        $login = self::login($contestant);
        $nomination = \OmegaUp\Controllers\QualityNomination::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'qualitynomination_id' => $response->qualitynomination_id,
            ])
        );
        $this->assertEquals(
            $problemData['request']['problem_alias'],
            $nomination['problem']['alias']
        );
    }

    /**
     * Basic test. Check that before nominating a problem for demotion, the
     * user might not have solved it first.
     */
    public function testNominatingForDemotion() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        QualityNominationFactory::createQualityNomination(
            $contestant,
            $problemData['request']['problem_alias'],
            'demotion',
            [
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]
        );
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
            $actualOutput = \OmegaUp\Controllers\QualityNomination::extractAliasFromArgument(
                $input
            );
            $this->assertEquals(
                $expectedOutput,
                $actualOutput,
                'Incorrect alias was extracted from URL.'
            );
        }
    }

    /**
     * Check that a non-reviewer user cannot change the status of a demotion qualitynomination.
     */
    public function testDemotionCannotBeResolvedByRegularUser() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $qualitynomination = QualityNominationFactory::createQualityNomination(
            $user,
            $problemData['request']['problem_alias'],
            'demotion',
            [
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]
        );

        $login = self::login($user);
        $request = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'status' => 'approved',
            'qualitynomination_id' => $qualitynomination->qualitynomination_id,
            'rationale' => 'ew plus something else'
        ]);

        try {
            $response = \OmegaUp\Controllers\QualityNomination::apiResolve(
                $request
            );
            $this->fail("Normal user shouldn't be able to resolve demotion");
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // Expected.
        }
    }

    /**
     * Check that a demotion can be approved and then reverted by a reviewer.
     */
    public function testDemotionCanBeApprovedAndLaterRevertedByReviewer() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $qualitynomination = QualityNominationFactory::createQualityNomination(
            $user,
            $problemData['request']['problem_alias'],
            'demotion',
            [
                'statements' => [
                   'es' => [
                       'markdown' => 'a + b',
                   ],
                ],
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]
        );
        // Login as a reviewer and approve ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'approved',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination->qualitynomination_id,
            'rationale' => 'ew plus something else',
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals(
            'approved',
            $details['nomination_status'],
            'qualitynomination should have been marked as approved'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(
            \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED,
            $problem['visibility'],
            'Problem should have been public banned'
        );

        // Revert ban.
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'denied',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination->qualitynomination_id,
            'rationale' => 'ew'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals(
            'denied',
            $details['nomination_status'],
            'qualitynomination should have been marked as denied'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(
            \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC,
            $problem['visibility'],
            'Problem should have been made public'
        );
    }

    /**
     * Check that a demotion approved by a reviewer sends an email to the problem creator.
     */
    public function testDemotionApprovedByReviewerAndSendMail() {
        $emailSender = new ScopedEmailSender();
        $problemData = ProblemsFactory::createProblem();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $qualitynomination = QualityNominationFactory::createQualityNomination(
            $user,
            $problemData['request']['problem_alias'],
            'demotion',
            [
                'statements' => [
                   'es' => [
                       'markdown' => 'a + b',
                   ],
                ],
                'rationale' => 'qwert',
                'reason' => 'offensive',
            ]
        );
        // Login as a reviewer and approve ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'approved',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination->qualitynomination_id,
            'rationale' => 'qwert plus something else'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $this->assertContains(
            $problemData['problem']->title,
            $emailSender::$listEmails[0]['subject']
        );
        $this->assertContains(
            $problemData['authorIdentity']->name,
            $emailSender::$listEmails[0]['body']
        );
        $this->assertContains('qwert', $emailSender::$listEmails[0]['body']);
        $this->assertContains(
            'something else',
            $emailSender::$listEmails[0]['body']
        );
        $this->assertEquals(1, count($emailSender::$listEmails));
    }

    /**
     * Check that a demotion can be denied by a reviewer.
     */
    public function testDemotionCanBeDeniedByReviewer() {
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC
        ]));
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $qualitynomination = QualityNominationFactory::createQualityNomination(
            $user,
            $problemData['request']['problem_alias'],
            'demotion',
            [
                'statements' => [
                   'es' => [
                       'markdown' => 'a + b',
                   ],
                ],
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]
        );
        // Login as a reviewer and deny ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'denied',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination->qualitynomination_id,
            'rationale' => 'ew'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals(
            'denied',
            $details['nomination_status'],
            'qualitynomination should have been marked as denied'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(
            \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC,
            $problem['visibility'],
            'Problem should have remained public'
        );
    }

    /**
     * Check that a demotion can be approved and then reopned by a reviewer.
     */
    public function testDemotionCanBeApprovedAndThenReopenedByReviewer() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $qualitynomination = QualityNominationFactory::createQualityNomination(
            $user,
            $problemData['request']['problem_alias'],
            'demotion',
            [
                'statements' => [
                   'es' => [
                       'markdown' => 'a + b',
                   ],
                ],
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]
        );
        // Login as a reviewer and approve ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'approved',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination->qualitynomination_id,
            'rationale' => 'ew plus something else'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals(
            'approved',
            $details['nomination_status'],
            'qualitynomination should have been marked as approved'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(
            \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED,
            $problem['visibility'],
            'Problem should have been public banned'
        );

        // Reopen demotion request.
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'open',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination->qualitynomination_id,
            'rationale' => 'ew'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals(
            'open',
            $details['nomination_status'],
            'qualitynomination should have been re-opened'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(
            \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED,
            $problem['visibility'],
            'Problem should have remained public banned'
        );
    }

    /**
     * Check that a demotion of a private problem can be approved and
     * then denied, and it keeps its original visibility
     */
    public function testDemotionOfPrivateProblemApprovedAndThenDeniedKeepsItsOriginalVisibility() {
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE
        ]));
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $qualitynomination = QualityNominationFactory::createQualityNomination(
            $user,
            $problemData['request']['problem_alias'],
            'demotion',
            [
                'statements' => [
                   'es' => [
                       'markdown' => 'a + b',
                   ],
                ],
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]
        );
        // Login as a reviewer and approve ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'approved',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination->qualitynomination_id,
            'rationale' => 'ew plus something else'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals(
            'approved',
            $details['nomination_status'],
            'qualitynomination should have been marked as approved'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(
            \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE_BANNED,
            $problem['visibility'],
            'Problem should have been private banned'
        );

        // Reopen demotion request.
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'denied',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination->qualitynomination_id,
            'rationale' => 'ew'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals(
            'denied',
            $details['nomination_status'],
            'qualitynomination should have been re-opened'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(
            \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE,
            $problem['visibility'],
            'Problem should have been private'
        );
    }

    /**
     * User who tried a problem but couldn't solve it yet (non AC, CE, JE verdicts),
     * should be able to send a dismissal or suggestion for it adding the before_ac
     * flag on nomination contents.
     */
    public function testBeforeACNomination() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        try {
            QualityNominationFactory::createSuggestion(
                $user,
                $problemData['request']['problem_alias'],
                null,
                3,
                ['ez-pz', 'ez', 'ez'],
                true
            );
            $this->fail('Must have tried to solve the problem first.');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            $this->assertEquals(
                $e->getMessage(),
                'qualityNominationMustHaveTriedToSolveProblem'
            );
        }

        // Now TRY to solve the problem
        $runData = RunsFactory::createRunToProblem($problemData, $user);
        RunsFactory::gradeRun($runData, 0, 'WA', 60);
        $result = QualityNominationFactory::createSuggestion(
            $user,
            $problemData['request']['problem_alias'],
            null,
            3,
            ['ez-pz', 'ez'],
            true
        );
        $this->assertNotNull($result);

        // Dismissals could be sent before AC also
        $result = QualityNominationFactory::createQualityNomination(
            $user,
            $problemData['request']['problem_alias'],
            'dismissal',
            ['before_ac' => true]
        );
        $this->assertNotNull($result);

        // Now solve the problem, it must not be allowed to send a before AC
        // nomination, as the problem is already solved
        $runData = RunsFactory::createRunToProblem($problemData, $user);
        RunsFactory::gradeRun($runData);
        try {
            QualityNominationFactory::createSuggestion(
                $user,
                $problemData['request']['problem_alias'],
                null,
                3,
                ['ez-pz', 'ez', 'ez'],
                true
            );
            $this->fail('Must not have solved the problem.');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            $this->assertEquals(
                $e->getMessage(),
                'qualityNominationMustNotHaveSolvedProblem'
            );
        }
    }

    /**
     * Check that before a duplicate nomination needs to have a valid original problem.
     */
    public function testNominatingForDuplicate() {
        $originalProblemData = ProblemsFactory::createProblem();
        $problemData = ProblemsFactory::createProblem();
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        try {
            QualityNominationFactory::createQualityNomination(
                $contestant,
                $problemData['request']['problem_alias'],
                'demotion',
                [
                    'rationale' => 'ew',
                    'reason' => 'duplicate',
                ]
            );
            $this->fail('Missing "original" should have been caught');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // Expected.
        }

        try {
            QualityNominationFactory::createQualityNomination(
                $contestant,
                $problemData['request']['problem_alias'],
                'demotion',
                [
                    'rationale' => 'otro sumas',
                    'reason' => 'duplicate',
                    'original' => '$invalid problem alias$',
                ]
            );
            $this->fail('Invalid "original" should have been caught');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            // Expected.
        }

        QualityNominationFactory::createQualityNomination(
            $contestant,
            $problemData['request']['problem_alias'],
            'demotion',
            [
                'rationale' => 'otro sumas',
                'reason' => 'duplicate',
                'original' => $originalProblemData['request']['problem_alias'],
            ]
        );

        QualityNominationFactory::createQualityNomination(
            $contestant,
            $problemData['request']['problem_alias'],
            'demotion',
            [
                'rationale' => 'otro sumas',
                'reason' => 'duplicate',
                'original' => 'https://omegaup.com/arena/problem/' . $originalProblemData['request']['problem_alias'] . '#problems',
            ]
        );
    }

    /**
     * Nomination list test.
     */
    public function testNominationList() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        QualityNominationFactory::createQualityNomination(
            $contestant,
            $problemData['request']['problem_alias'],
            'promotion',
            [
                'rationale' => 'cool!',
                'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                ],
                'source' => 'omegaUp',
                'tags' => ['ez-pz'],
            ]
        );

        // Login as an arbitrary reviewer.
        $login = self::login(QualityNominationFactory::$reviewers[0]);
        $response = \OmegaUp\Controllers\QualityNomination::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $nomination = $this->findByPredicate(
            $response['nominations'],
            function ($nomination) use (&$problemData) {
                return $nomination['problem']['alias'] == $problemData['request']['problem_alias'];
            }
        );
        $this->assertNotNull($nomination);
        $this->assertEquals(
            \OmegaUp\Controllers\QualityNomination::REVIEWERS_PER_NOMINATION,
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
        $response = \OmegaUp\Controllers\QualityNomination::apiMyAssignedList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertArrayContainsWithPredicate(
            $response['nominations'],
            function ($nomination) use (&$problemData) {
                return $nomination['problem']['alias'] == $problemData['request']['problem_alias'];
            }
        );
    }

    /**
     * Duplicate tag test.
     */
    public function testTagsForDuplicate() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        try {
            QualityNominationFactory::createQualityNomination(
                $contestant,
                $problemData['request']['problem_alias'],
                'promotion',
                [
                    'rationale' => 'cool!',
                    'statements' => [
                        'es' => [
                            'markdown' => 'a + b',
                        ],
                    ],
                    'source' => 'omegaUp',
                    'tags' => ['ez-pz', 'ez', 'ez'],
                ]
            );
            $this->fail('Duplicate tags should be caught.');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInArrayException $e) {
            // Expected.
        }

        try {
            QualityNominationFactory::createSuggestion(
                $contestant,
                $problemData['request']['problem_alias'],
                null,
                3,
                ['ez-pz', 'ez', 'ez'],
                false
            );
            $this->fail('Duplicate tags should be caught.');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInArrayException $e) {
            // Expected.
        }

        QualityNominationFactory::createQualityNomination(
            $contestant,
            $problemData['request']['problem_alias'],
            'promotion',
            [
                'rationale' => 'cool!',
                'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                ],
                'source' => 'omegaUp',
                'tags' => ['ez-pz', 'ez'],
            ]
        );

        QualityNominationFactory::createSuggestion(
            $contestant,
            $problemData['request']['problem_alias'],
            null,
            3,
            ['ez-pz', 'ez'],
            false
        );
    }

    /**
     * Test that nomination list by default only shows promotions or demotions.
     * All other nomination types should not appear on this list.
     */
    public function testNominationListDoesntShowSuggestionsOrDismisssal() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        // Create promotion nomination.
        QualityNominationFactory::createQualityNomination(
            $contestant,
            $problemData['request']['problem_alias'],
            'promotion',
            [
                'rationale' => 'cool!',
                'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                ],
                'tags' => ['DP', 'Math'],
                'source' => 'omegaUp',
            ]
        );

        // Create demotion nomination.
        QualityNominationFactory::createQualityNomination(
            $contestant,
            $problemData['request']['problem_alias'],
            'demotion',
            [
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]
        );

        // Create dismissal nomination.
        QualityNominationFactory::createQualityNomination(
            $contestant,
            $problemData['request']['problem_alias'],
            'dismissal',
            []
        );

        // Create suggestion nomination.
        QualityNominationFactory::createSuggestion(
            $contestant,
            $problemData['request']['problem_alias'],
            null,
            1,
            ['DP', 'Math'],
            false
        );

        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $list = \OmegaUp\Controllers\QualityNomination::apiList(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
        ]));
        $this->assertEquals(
            'ok',
            $list['status'],
            'Status of apiList call is not ok'
        );
        $this->assertGreaterThanOrEqual(
            2,
            count(
                $list['nominations']
            ),
            "List didn't return enough nominations"
        );
        foreach ($list['nominations'] as $nomination) {
            $isPromotion = ($nomination['nomination'] == 'promotion');
            $isDemotion = ($nomination['nomination'] == 'demotion');
            $this->assertTrue(
                $isPromotion || $isDemotion,
                'Found a nomination of type ' . $nomination['nomination'] . '. Only promotion and demotion should be shown.'
            );
        }
    }

    /**
     * Check that before discard a problem, the user must
     * have solved it first.
     */
    public function testMustSolveBeforeDismissed() {
        $problemData = ProblemsFactory::createProblem();
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        $nominationType = 'dismissal';
        try {
            QualityNominationFactory::createQualityNomination(
                $contestant,
                $problemData['request']['problem_alias'],
                'dismissal',
                []
            );
            $this->fail('Should not have been able to dismissed the problem');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            // Expected.
        }
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemData['request']['problem_alias']
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problemDismissed = \OmegaUp\DAO\QualityNominations::getByUserAndProblem(
            $contestant->user_id,
            $problem->problem_id,
            $nominationType,
            json_encode([]), // re-encoding it for normalization.
            'open'
        );
        RunsFactory::gradeRun($runData);
        try {
            $this->assertEquals(
                0,
                count(
                    $problemDismissed
                ),
                'Should not have been able to dismiss the problem'
            );
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            // Expected.
        }
        try {
            QualityNominationFactory::createQualityNomination(
                $contestant,
                $problemData['request']['problem_alias'],
                'dismissal',
                []
            );
            $pd = \OmegaUp\DAO\QualityNominations::getByUserAndProblem(
                $contestant->user_id,
                $problem->problem_id,
                $nominationType,
                json_encode([]), // re-encoding it for normalization.
                'open'
            );
            $this->assertGreaterThan(
                0,
                count(
                    $pd
                ),
                'The problem should have been dismissed'
            );
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            // Expected.
        }
    }

    public function testGetGlobalDifficultyAndQuality() {
        $problemData[0] = ProblemsFactory::createProblem();
        $problemData[1] = ProblemsFactory::createProblem();
        self::setUpSyntheticSuggestions($problemData);

        $globalContents = \OmegaUp\DAO\QualityNominations::getAllNominations();
        $actualGlobals = \OmegaUp\DAO\QualityNominations::calculateGlobalDifficultyAndQuality(
            $globalContents
        );
        $expectedGlobals = [23 / 13 /*quality*/, 54 / 16 /*difficulty*/];

        $this->assertEquals($expectedGlobals, $actualGlobals);
    }

    public function testGetSuggestionRowMap() {
        $problemData[0] = ProblemsFactory::createProblem();
        $problemData[1] = ProblemsFactory::createProblem();
        self::setUpSyntheticSuggestions($problemData);
        $contents[0] = \OmegaUp\DAO\QualityNominations::getAllSuggestionsPerProblem(
            $problemData[0]['problem']->problem_id
        );
        $actualResult[0] = \OmegaUp\DAO\QualityNominations::calculateProblemSuggestionAggregates(
            $contents[0]
        );
        $contents[1] = \OmegaUp\DAO\QualityNominations::getAllSuggestionsPerProblem(
            $problemData[1]['problem']->problem_id
        );
        $actualResult[1] = \OmegaUp\DAO\QualityNominations::calculateProblemSuggestionAggregates(
            $contents[1]
        );

        $expectedResult[0] = [
            'quality_sum' => 13,
            'quality_n' => 7,
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

    /*
        Creates 5 problems and 5 users.
         - The first time the cronjob is executed, the problems are voted by users as unranked users (with vote weight = 2)
         - The second time, the problems are voted by ranked users according to the number of problems they solved
    */
    public function testAggregateFeedback() {
        /* Previous tests create some users with their assigned ranges and forget to delete them, which affects this test */
        Utils::deleteAllRanks();
        Utils::deleteAllPreviousRuns();

        for ($i = 0; $i < 5; $i++) {
            $problemData[$i] = ProblemsFactory::createProblem();
        }
        $userData = [];
        $identityData = [];
        for ($i = 0; $i < 5; $i++) {
            ['user' => $userData[$i], 'identity' => $identityData[$i]] = UserFactory::createUser();
        }
        self::setUpRankForUsers($problemData, $userData, true);

        Utils::RunAggregateFeedback();

        $newProblem[0] = \OmegaUp\DAO\Problems::getByAlias(
            $problemData[0]['request']['problem_alias']
        );
        $this->assertEquals(
            2.971428571,
            $newProblem[0]->difficulty,
            'Wrong difficulty.',
            0.001
        );
        $this->assertEquals(
            2.2,
            $newProblem[0]->quality,
            'Wrong quality.',
            0.001
        );
        $this->assertEquals(
            '[0, 0, 2, 2, 1]',
            $newProblem[0]->difficulty_histogram,
            'Wrong difficulty histogram'
        );
        $this->assertEquals(
            '[1, 1, 0, 1, 2]',
            $newProblem[0]->quality_histogram,
            'Wrong quality histogram'
        );

        $newProblem[2] = \OmegaUp\DAO\Problems::getByAlias(
            $problemData[2]['request']['problem_alias']
        );
        $this->assertEquals(
            0,
            $newProblem[2]->difficulty,
            'Wrong difficulty',
            0.001
        );
        $this->assertEquals(0, $newProblem[2]->quality, 'Wrong quality', 0.001);

        $tagArrayForProblem1 = \OmegaUp\DAO\ProblemsTags::getProblemTags(
            $newProblem[0],
            false /* public_only */,
            true /* includeAutogenerated */
        );

        $tagArrayForProblem3 = \OmegaUp\DAO\ProblemsTags::getProblemTags(
            $newProblem[2],
            false /* public_only */,
            true /* includeAutogenerated */
        );

        $extractName = function ($tag) {
            return $tag['name'];
        };

        $tags1 = array_map($extractName, $tagArrayForProblem1);
        $this->assertEquals(
            $tags1,
            ['dp', 'math', 'matrices', 'greedy', 'lenguaje']
        );

        $tags3 = array_map($extractName, $tagArrayForProblem3);
        $this->assertEquals(
            $tags3,
            ['dp', 'greedy', 'geometry', 'search', 'lenguaje']
        );

        Utils::RunUpdateUserRank();
        Utils::RunAggregateFeedback();

        $newProblem[0] = \OmegaUp\DAO\Problems::getByAlias(
            $problemData[0]['request']['problem_alias']
        );
        $this->assertEquals(
            2.895384615,
            $newProblem[0]->difficulty,
            'Wrong difficulty.',
            0.001
        );
        $this->assertEquals(
            2.538378378,
            $newProblem[0]->quality,
            'Wrong quality.',
            0.001
        );

        $newProblem[1] = \OmegaUp\DAO\Problems::getByAlias(
            $problemData[1]['request']['problem_alias']
        );
        $this->assertEquals(
            3.446886447,
            $newProblem[1]->difficulty,
            'Wrong difficulty.',
            0.001
        );
        $this->assertEquals(
            0,
            $newProblem[1]->quality,
            'Wrong quality.',
            0.001
        );

        $newProblem[2] = \OmegaUp\DAO\Problems::getByAlias(
            $problemData[2]['request']['problem_alias']
        );
        $this->assertEquals(
            2.684981685,
            $newProblem[2]->difficulty,
            'Wrong difficulty',
            0.001
        );
        $this->assertEquals(
            1.736164736,
            $newProblem[2]->quality,
            'Wrong quality',
            0.001
        );

        $tagArrayForProblem1 = \OmegaUp\DAO\ProblemsTags::getProblemTags(
            $newProblem[0],
            false /* public_only */,
            true /* includeAutogenerated */
        );

        $tagArrayForProblem3 = \OmegaUp\DAO\ProblemsTags::getProblemTags(
            $newProblem[2],
            false /* public_only */,
            true /* includeAutogenerated */
        );

        $tags1 = array_map($extractName, $tagArrayForProblem1);
        $this->assertEquals($tags1, ['dp', 'math', 'greedy', 'lenguaje']);

        $tags3 = array_map($extractName, $tagArrayForProblem3);
        $this->assertEquals(
            $tags3,
            ['dp', 'greedy', 'geometry', 'search', 'lenguaje']
        );
    }

    public function setUpRankForUsers(
        $problems,
        $users,
        $withSuggestions = false
    ) {
        for ($i = 0; $i < 5; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $runData = RunsFactory::createRunToProblem(
                    $problems[$j],
                    $users[$i]
                );
                RunsFactory::gradeRun($runData);
            }
        }

        if ($withSuggestions) {
            Utils::deleteAllSuggestions();

            QualityNominationFactory::createSuggestion(
                $users[0],
                $problems[0]['request']['problem_alias'],
                2, /* difficulty */
                1, /* quality */
                ['DP', 'Math'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[1],
                $problems[0]['request']['problem_alias'],
                3, /* difficulty */
                3, /* quality */
                ['Matrices', 'Math'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[2],
                $problems[0]['request']['problem_alias'],
                4, /* difficulty */
                0, /* quality */
                ['Math', 'DP'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[3],
                $problems[0]['request']['problem_alias'],
                2, /* difficulty */
                4, /* quality */
                ['DP', 'Math', 'Greedy'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[4],
                $problems[0]['request']['problem_alias'],
                3, /* difficulty */
                4, /* quality */
                ['Greedy', 'DP'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[1],
                $problems[1]['request']['problem_alias'],
                3, /* difficulty */
                null, /* quality */
                ['Matrices', 'Math'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[2],
                $problems[1]['request']['problem_alias'],
                null, /* difficulty */
                1, /* quality */
                ['Math', 'DP'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[3],
                $problems[1]['request']['problem_alias'],
                4, /* difficulty */
                null, /* quality */
                ['DP', 'Math', 'Greedy'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[4],
                $problems[1]['request']['problem_alias'],
                4, /* difficulty */
                0, /* quality */
                ['Greedy', 'DP'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[2],
                $problems[2]['request']['problem_alias'],
                4, /* difficulty */
                4, /* quality */
                ['Search', 'DP', 'Greedy'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[3],
                $problems[2]['request']['problem_alias'],
                4, /* difficulty */
                1, /* quality */
                ['Geometry', 'DP', 'Search', 'Greedy'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[4],
                $problems[2]['request']['problem_alias'],
                1, /* difficulty */
                1, /* quality */
                ['Search', 'Greedy'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[3],
                $problems[3]['request']['problem_alias'],
                4, /* difficulty */
                3, /* quality */
                ['DP', 'Math', 'Greedy'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[4],
                $problems[3]['request']['problem_alias'],
                3, /* difficulty */
                null, /* quality */
                ['Greedy', 'DP'],
                false
            );

            QualityNominationFactory::createSuggestion(
                $users[4],
                $problems[4]['request']['problem_alias'],
                3, /* difficulty */
                null, /* quality */
                ['Greedy', 'DP'],
                false
            );
        }
    }

    // Creates 4 problems:
    // * An easy one with low quality.
    // * An easy one with high quality.
    // * A hard one with very high quality.
    // * A hard one with low quality.
    // The problem of the week should be the second one because we choose the highest-quality one
    // with difficulty < 2.
    public function testUpdateProblemOfTheWeek() {
        $syntheticProblems = self::setUpSyntheticSuggestionsForProblemOfTheWeek();
        Utils::RunAggregateFeedback();

        $problemOfTheWeek = \OmegaUp\DAO\ProblemOfTheWeek::getByDificulty(
            'easy'
        );
        $this->assertEquals(count($problemOfTheWeek), 1);
        $this->assertEquals(
            $problemOfTheWeek[0]->problem_id,
            $syntheticProblems[1]['problem']->problem_id
        );
        // TODO(heduenas): Make assertation for hard problem of the week when that gets implmented.
    }

    public function setUpSyntheticSuggestionsForProblemOfTheWeek() {
        // Delete existing suggestions and problems of the week.
        Utils::deleteAllSuggestions();
        Utils::deleteAllProblemsOfTheWeek();

        // Setup synthetic data.
        $numberOfProblems = 4;
        for ($i = 0; $i < $numberOfProblems; $i++) {
            $problemData[$i] = ProblemsFactory::createProblem();
        }
        $contestants = [];
        for ($i = 0; $i < 10; $i++) {
            ['user' => $contestants[], 'identity' => $identity] = UserFactory::createUser();
            for ($j = 0; $j < $numberOfProblems; $j++) {
                $runData = RunsFactory::createRunToProblem(
                    $problemData[$j],
                    $contestants[$i]
                );
                RunsFactory::gradeRun($runData);
            }
        }

        // Easy problem with low quality.
        $difficultyRatings[0] = [0, 0, 0, 1, 0, 0, 1, 0, 1, 2]; // Average = 0.5.
        $qualityRatings[0] = [0, 2, 3, 2, 2, 2, 1, 2, 1, 1]; // Average = 1.6.
        // Easy problem with high quality.
        $difficultyRatings[1] = [0, 1, 2, 1, 0, 0, 0, 1, 2, 2]; // Average = 0.9
        $qualityRatings[1] = [4, 4, 3, 3, 4, 3, 4, 2, 4, 3]; // Average = 3.4.
        // Hard problem with very high quality.
        $difficultyRatings[2] = [4, 3, 1, 1, 4, 4, 4, 3, 4, 4]; // Average = 3.2.
        $qualityRatings[2] = [4, 4, 4, 4, 3, 4, 4, 3, 4, 4]; // Average = 3.8.
        // Hard problem with low quality.
        $difficultyRatings[3] = [3, 2, 4, 4, 4, 4, 2, 4, 3, 4]; // Average = 3.4
        $qualityRatings[3] = [0, 2, 2, 3, 1, 2, 2, 1, 1, 2]; // Average = 1.6

        for ($problemIdx = 0; $problemIdx < $numberOfProblems; $problemIdx++) {
            for ($userIdx = 0; $userIdx < 10; $userIdx++) {
                QualityNominationFactory::createSuggestion(
                    $contestants[$userIdx],
                    $problemData[$problemIdx]['request']['problem_alias'],
                    $difficultyRatings[$problemIdx][$userIdx],
                    $qualityRatings[$problemIdx][$userIdx],
                    [], // No tags.
                    false
                );
            }
        }

        // Set date for all quality nominations as 1 week ago, so that they are eligible for
        // current problem of the week.
        $dateOneWeekAgo = (new DateTime())->sub(
            new DateInterval(
                'P7D'
            )
        )->format(
            'Y-m-d H:i:s'
        );
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            'UPDATE `QualityNominations` SET `time` = ?',
            [$dateOneWeekAgo]
        );

        return $problemData;
    }

    public function testAutogeneratedTagsWithConflicts() {
        $problemData[0] = ProblemsFactory::createProblem();
        $problemData[1] = ProblemsFactory::createProblem();
        self::setUpSyntheticSuggestions($problemData);

        // Manually add one tag.
        ProblemsFactory::addTag($problemData[0], 'dp', 1 /* public */);
        $tags = array_map(function ($tag) {
            return $tag['name'];
        }, \OmegaUp\DAO\ProblemsTags::getProblemTags(
            $problemData[0]['problem'],
            false /* public_only */,
            true /* includeAutogenerated */
        ));
        $this->assertEquals($tags, ['dp', 'lenguaje']);

        Utils::RunAggregateFeedback();

        $tags = array_map(function ($tag) {
            return $tag['name'];
        }, \OmegaUp\DAO\ProblemsTags::getProblemTags(
            $problemData[0]['problem'],
            false /* public_only */,
            true /* includeAutogenerated */
        ));
        $this->assertEquals($tags, ['dp', 'math', 'greedy', 'lenguaje']);
    }

    public function setUpSyntheticSuggestions($problemData) {
        Utils::deleteAllSuggestions();

        // Setup synthetic data.
        $contestants = [];
        for ($i = 0; $i < 10; $i++) {
            ['user' => $contestants[], 'identity' => $identity] = UserFactory::createUser();
            for ($j = 0; $j < 2; $j++) {
                $runData = RunsFactory::createRunToProblem(
                    $problemData[$j],
                    $contestants[$i]
                );
                RunsFactory::gradeRun($runData);
            }
        }

        QualityNominationFactory::createSuggestion(
            $contestants[0],
            $problemData[0]['request']['problem_alias'],
            null,
            1,
            ['DP', 'Math'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[1],
            $problemData[0]['request']['problem_alias'],
            3,
            3,
            ['Math', 'DP'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[2],
            $problemData[0]['request']['problem_alias'],
            4,
            0,
            ['Matrices', 'Math'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[3],
            $problemData[0]['request']['problem_alias'],
            null,
            null,
            ['Math'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[4],
            $problemData[0]['request']['problem_alias'],
            3,
            4,
            ['DP', 'Math', 'Greedy'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[5],
            $problemData[0]['request']['problem_alias'],
            3,
            null,
            [],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[6],
            $problemData[0]['request']['problem_alias'],
            null,
            1,
            [],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[7],
            $problemData[0]['request']['problem_alias'],
            4,
            null,
            ['Greedy', 'DP'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[8],
            $problemData[0]['request']['problem_alias'],
            4,
            0,
            ['DP'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[9],
            $problemData[0]['request']['problem_alias'],
            4,
            4,
            ['DP', 'Math'],
            false
        );

        QualityNominationFactory::createSuggestion(
            $contestants[0],
            $problemData[1]['request']['problem_alias'],
            4,
            1,
            ['Search', 'Geometry'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[1],
            $problemData[1]['request']['problem_alias'],
            1,
            1,
            ['Search', 'Geometry'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[2],
            $problemData[1]['request']['problem_alias'],
            4,
            3,
            ['Matrices', 'Search'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[3],
            $problemData[1]['request']['problem_alias'],
            3,
            null,
            ['Search'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[4],
            $problemData[1]['request']['problem_alias'],
            3,
            null,
            ['Search', 'Math', 'Geometry'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[5],
            $problemData[1]['request']['problem_alias'],
            3,
            null,
            [],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[6],
            $problemData[1]['request']['problem_alias'],
            null,
            1,
            [],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[7],
            $problemData[1]['request']['problem_alias'],
            3,
            null,
            ['Search', 'DP'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[8],
            $problemData[1]['request']['problem_alias'],
            4,
            1,
            ['DP'],
            false
        );
        QualityNominationFactory::createSuggestion(
            $contestants[9],
            $problemData[1]['request']['problem_alias'],
            4,
            3,
            ['Geometry', 'Math'],
            false
        );
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
            \OmegaUp\DAO\QualityNominations::mostVotedTags($tags, 0.25),
            ['DP', 'Graph', 'Binary Search']
        );

        $this->assertEquals(
            \OmegaUp\DAO\QualityNominations::mostVotedTags($tags, 0.5),
            ['DP', 'Graph']
        );

        $this->assertEquals(
            \OmegaUp\DAO\QualityNominations::mostVotedTags($tags, 0.9),
            ['DP']
        );

        $this->assertEquals(
            \OmegaUp\DAO\QualityNominations::mostVotedTags($tags, 0.9),
            ['DP']
        );

        $this->assertEquals(
            \OmegaUp\DAO\QualityNominations::mostVotedTags($tags, 0.01),
            ['DP', 'Graph', 'Binary Search', 'Math', 'Greedy']
        );

        $tagsWithLittleVotes = [
            'DP' => 2,
            'Graph' => 1,
        ];

        $this->assertEquals(
            \OmegaUp\DAO\QualityNominations::mostVotedTags(
                $tagsWithLittleVotes,
                0.25
            ),
            [],
            'There must be at least 5 votes.'
        );

        $tooManyTagsWithMaxVotes = [
            'T1' => 9, 'T2' => 9, 'T3' => 9, 'T4' => 9, 'T5' => 9, 'T6' => 9,
            'T7' => 9, 'T8' => 9, 'T9' => 9, 'T10' => 9, 'T11' => 9, 'T12' => 9];

        $this->assertEquals(
            \OmegaUp\DAO\QualityNominations::mostVotedTags(
                $tooManyTagsWithMaxVotes,
                0.25
            ),
            [],
            'There must be a maximum number of tags to be assigned.'
        );
    }
}
