<?php

class QualityNominationTest extends OmegaupTestCase {
    public function testGetNominationsHasAuthorAndNominatorSet() {
        $problemData = ProblemsFactory::createProblem();
        $contestant = UserFactory::createUser();

        $login = self::login($contestant);
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]),
        ]));

        $nominations = \OmegaUp\DAO\QualityNominations::getNominations(null, null);
        self::assertArrayHasKey('author', $nominations[0]);
        self::assertArrayHasKey('nominator', $nominations[0]);
    }

    public function testGetByIdHasAuthorAndNominatorSet() {
        $problemData = ProblemsFactory::createProblem();
        $contestant = UserFactory::createUser();

        $login = self::login($contestant);
        $result = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]),
        ]));

        $nomination = \OmegaUp\DAO\QualityNominations::getById($result['qualitynomination_id']);
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
        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'demotion',
            'contents' => $contents,
        ]));

        // Login as a reviewer and approve ban.
        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'qualitynomination_id' => $qualitynomination['qualitynomination_id']]);

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals('demotion', $details['nomination'], 'Should have set demotion');
        $this->assertEquals($user->username, $details['nominator']['username'], 'Should have set user');
        $this->assertEquals($problemData['request']['problem_alias'], $details['problem']['alias'], 'Should have set problem');
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
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
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
            \OmegaUp\Controllers\QualityNomination::apiCreate($r);
            $this->fail('Should not have been able to nominate the problem');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            // still expected.
        }

        RunsFactory::gradeRun($runData);

        \OmegaUp\Controllers\QualityNomination::apiCreate($r);

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
        $contestant = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);

        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'suggestion',
            'contents' => json_encode([
                // No difficulty!
                'quality' => 3,
                'tags' => [],
            ]),
        ]);

        try {
            \OmegaUp\Controllers\QualityNomination::apiCreate($r);
            $this->fail('Should not have been able to make suggestion about the problem');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            // still expected.
        }

        RunsFactory::gradeRun($runData);

        $response = \OmegaUp\Controllers\QualityNomination::apiCreate($r);

        $r['qualitynomination_id'] = $response['qualitynomination_id'];
        $nomination = \OmegaUp\Controllers\QualityNomination::apiDetails($r);
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
        $contestant = UserFactory::createUser();

        $login = self::login($contestant);
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
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
            $actualOutput = \OmegaUp\Controllers\QualityNomination::extractAliasFromArgument($input);
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
        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]),
        ]));

        $request = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'status' => 'approved',
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew plus something else'
        ]);
        try {
            $response = \OmegaUp\Controllers\QualityNomination::apiResolve($request);
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
        $user = UserFactory::createUser();

        $login = self::login($user);
        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
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
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'approved',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew plus something else',
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve($request);

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals('approved', $details['nomination_status'], 'qualitynomination should have been marked as approved');

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED, $problem['visibility'], 'Problem should have been public banned');

        // Revert ban.
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'denied',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve($request);

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals('denied', $details['nomination_status'], 'qualitynomination should have been marked as denied');

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC, $problem['visibility'], 'Problem should have been made public');
    }

    /**
     * Check that a demotion approved by a reviewer sends an email to the problem creator.
     */
    public function testDemotionApprovedByReviewerAndSendMail() {
        $emailSender = new ScopedEmailSender();
        $problemData = ProblemsFactory::createProblem();
        $user = UserFactory::createUser();

        $login = self::login($user);
        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
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
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'approved',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'qwert plus something else'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve($request);

        $this->assertContains(
            $problemData['problem']->title,
            $emailSender::$listEmails[0]['subject']
        );
        $this->assertContains(
            $problemData['authorIdentity']->name,
            $emailSender::$listEmails[0]['body']
        );
        $this->assertContains('qwert', $emailSender::$listEmails[0]['body']);
        $this->assertContains('something else', $emailSender::$listEmails[0]['body']);
        $this->assertEquals(1, count($emailSender::$listEmails));
    }

    /**
     * Check that a demotion can be denied by a reviewer.
     */
    public function testDemotionCanBeDeniedByReviewer() {
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC
        ]));
        $user = UserFactory::createUser();

        $login = self::login($user);
        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
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
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'denied',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve($request);

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals('denied', $details['nomination_status'], 'qualitynomination should have been marked as denied');

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC, $problem['visibility'], 'Problem should have remained public');
    }

    /**
     * Check that a demotion can be approved and then reopned by a reviewer.
     */
    public function testDemotionCanBeApprovedAndThenReopenedByReviewer() {
        $problemData = ProblemsFactory::createProblem();
        $user = UserFactory::createUser();

        $login = self::login($user);
        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
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
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'approved',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew plus something else'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve($request);

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals('approved', $details['nomination_status'], 'qualitynomination should have been marked as approved');

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED, $problem['visibility'], 'Problem should have been public banned');

        // Reopen demotion request.
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'open',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve($request);

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals('open', $details['nomination_status'], 'qualitynomination should have been re-opened');

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED, $problem['visibility'], 'Problem should have remained public banned');
    }

    /**
     * Check that a demotion of a private problem can be approved and
     * then denied, and it keeps its original visibility
     */
    public function testDemotionOfPrivateProblemApprovedAndThenDeniedKeepsItsOriginalVisibility() {
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE
        ]));
        $user = UserFactory::createUser();

        $login = self::login($user);
        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
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
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'approved',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew plus something else'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve($request);

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals('approved', $details['nomination_status'], 'qualitynomination should have been marked as approved');

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(\OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE_BANNED, $problem['visibility'], 'Problem should have been private banned');

        // Reopen demotion request.
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'denied',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew'
        ]);
        $response = \OmegaUp\Controllers\QualityNomination::apiResolve($request);

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertEquals('denied', $details['nomination_status'], 'qualitynomination should have been re-opened');

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertEquals(\OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE, $problem['visibility'], 'Problem should have been private');
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
            \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'nomination' => 'demotion',
                'contents' => json_encode([
                    'rationale' => 'ew',
                    'reason' => 'duplicate',
                ]),
            ]));
            $this->fail('Missing "original" should have been caught');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // Expected.
        }

        try {
            \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'nomination' => 'demotion',
                'contents' => json_encode([
                    'rationale' => 'otro sumas',
                    'reason' => 'duplicate',
                    'original' => '$invalid problem alias$',
                ]),
            ]));
            $this->fail('Invalid "original" should have been caught');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            // Expected.
        }

        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'otro sumas',
                'reason' => 'duplicate',
                'original' => $originalProblemData['request']['problem_alias'],
            ]),
        ]));

        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'otro sumas',
                'reason' => 'duplicate',
                'original' => 'https://omegaup.com/arena/problem/' . $originalProblemData['request']['problem_alias'] . '#problems',
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
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
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
        $contestant = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        $login = self::login($contestant);
        try {
            \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'nomination' => 'promotion',
                'contents' => json_encode([
                    'rationale' => 'cool!',
                    'statements' => [
                        'es' => [
                            'markdown' => 'a + b',
                        ],
                    ],
                    'source' => 'omegaUp',
                    'tags' => ['ez-pz', 'ez', 'ez'],
                ]),
            ]));
            $this->fail('Duplicate tags should be caught.');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInArrayException $e) {
            // Expected.
        }

        try {
            \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'nomination' => 'suggestion',
                'contents' => json_encode([
                    // No difficulty!
                    'quality' => 3,
                    'tags' => ['ez-pz', 'ez', 'ez'],
                ]),
            ]));
            $this->fail('Duplicate tags should be caught.');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInArrayException $e) {
            // Expected.
        }

        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'promotion',
            'contents' => json_encode([
                'rationale' => 'cool!',
                'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                ],
                'source' => 'omegaUp',
                'tags' => ['ez-pz', 'ez'],
            ]),
        ]));

        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'suggestion',
            'contents' => json_encode([
                // No difficulty!
                'quality' => 3,
                'tags' => ['ez-pz', 'ez'],
            ]),
        ]));
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
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
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
        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]),
        ]));

        // Create dismissal nomination.
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'dismissal',
            'contents' => json_encode([]),
        ]));

        // Create dismissal nomination.
        QualityNominationFactory::createSuggestion(
            $login,
            $problemData['request']['problem_alias'],
            null,
            1,
            ['DP', 'Math']
        );

        $reviewerLogin = self::login(QualityNominationFactory::$reviewers[0]);
        $list = \OmegaUp\Controllers\QualityNomination::apiList(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
        ]));
        $this->assertEquals('ok', $list['status'], 'Status of apiList call is not ok');
        $this->assertGreaterThanOrEqual(2, count($list['nominations']), "List didn't return enough nominations");
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
        $contestant = UserFactory::createUser();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'dismissal',
            'contents' => json_encode([]),
        ]);
        try {
            \OmegaUp\Controllers\QualityNomination::apiCreate($r);
            $this->fail('Should not have been able to dismissed the problem');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            // Expected.
        }
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problemDismissed = \OmegaUp\DAO\QualityNominations::getByUserAndProblem(
            $r->user->user_id,
            $problem->problem_id,
            $r['nomination'],
            json_encode([]), // re-encoding it for normalization.
            'open'
        );
        RunsFactory::gradeRun($runData);
        try {
            $this->assertEquals(0, count($problemDismissed), 'Should not have been able to dismiss the problem');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            // Expected.
        }
        try {
            \OmegaUp\Controllers\QualityNomination::apiCreate($r);
            $pd = \OmegaUp\DAO\QualityNominations::getByUserAndProblem(
                $r->user->user_id,
                $problem->problem_id,
                $r['nomination'],
                json_encode([]), // re-encoding it for normalization.
                'open'
            );
            $this->assertGreaterThan(0, count($pd), 'The problem should have been dismissed');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            // Expected.
        }
    }

    public function testGetGlobalDifficultyAndQuality() {
        $problemData[0] = ProblemsFactory::createProblem();
        $problemData[1] = ProblemsFactory::createProblem();
        self::setUpSyntheticSuggestions($problemData);

        $globalContents = \OmegaUp\DAO\QualityNominations::getAllNominations();
        $actualGlobals = \OmegaUp\DAO\QualityNominations::calculateGlobalDifficultyAndQuality($globalContents);
        $expectedGlobals = [23/13 /*quality*/, 54/16 /*difficulty*/];

        $this->assertEquals($expectedGlobals, $actualGlobals);
    }

    public function testGetSuggestionRowMap() {
        $problemData[0] = ProblemsFactory::createProblem();
        $problemData[1] = ProblemsFactory::createProblem();
        self::setUpSyntheticSuggestions($problemData);
        $contents[0] = \OmegaUp\DAO\QualityNominations::getAllSuggestionsPerProblem($problemData[0]['problem']->problem_id);
        $actualResult[0] = \OmegaUp\DAO\QualityNominations::calculateProblemSuggestionAggregates($contents[0]);
        $contents[1] = \OmegaUp\DAO\QualityNominations::getAllSuggestionsPerProblem($problemData[1]['problem']->problem_id);
        $actualResult[1] = \OmegaUp\DAO\QualityNominations::calculateProblemSuggestionAggregates($contents[1]);

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
        for ($i = 0; $i < 5; $i++) {
            $userData[$i] = UserFactory::createUser();
        }
        self::setUpRankForUsers($problemData, $userData, true);

        Utils::RunAggregateFeedback();

        $newProblem[0] = \OmegaUp\DAO\Problems::getByAlias($problemData[0]['request']['problem_alias']);
        $this->assertEquals(2.971428571, $newProblem[0]->difficulty, 'Wrong difficulty.', 0.001);
        $this->assertEquals(2.2, $newProblem[0]->quality, 'Wrong quality.', 0.001);
        $this->assertEquals('[0, 0, 2, 2, 1]', $newProblem[0]->difficulty_histogram, 'Wrong difficulty histogram');
        $this->assertEquals('[1, 1, 0, 1, 2]', $newProblem[0]->quality_histogram, 'Wrong quality histogram');

        $newProblem[2] = \OmegaUp\DAO\Problems::getByAlias($problemData[2]['request']['problem_alias']);
        $this->assertEquals(0, $newProblem[2]->difficulty, 'Wrong difficulty', 0.001);
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
        $this->assertEquals($tags1, ['dp', 'math', 'matrices', 'greedy', 'lenguaje']);

        $tags3 = array_map($extractName, $tagArrayForProblem3);
        $this->assertEquals($tags3, ['dp', 'greedy', 'geometry', 'search', 'lenguaje']);

        Utils::RunUpdateUserRank();
        Utils::RunAggregateFeedback();

        $newProblem[0] = \OmegaUp\DAO\Problems::getByAlias($problemData[0]['request']['problem_alias']);
        $this->assertEquals(2.895384615, $newProblem[0]->difficulty, 'Wrong difficulty.', 0.001);
        $this->assertEquals(2.538378378, $newProblem[0]->quality, 'Wrong quality.', 0.001);

        $newProblem[1] = \OmegaUp\DAO\Problems::getByAlias($problemData[1]['request']['problem_alias']);
        $this->assertEquals(3.446886447, $newProblem[1]->difficulty, 'Wrong difficulty.', 0.001);
        $this->assertEquals(0, $newProblem[1]->quality, 'Wrong quality.', 0.001);

        $newProblem[2] = \OmegaUp\DAO\Problems::getByAlias($problemData[2]['request']['problem_alias']);
        $this->assertEquals(2.684981685, $newProblem[2]->difficulty, 'Wrong difficulty', 0.001);
        $this->assertEquals(1.736164736, $newProblem[2]->quality, 'Wrong quality', 0.001);

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
        $this->assertEquals($tags3, ['dp', 'greedy', 'geometry', 'search', 'lenguaje']);
    }

    public function setUpRankForUsers($problems, $users, $withSuggestions = false) {
        for ($i = 0; $i < 5; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $runData = RunsFactory::createRunToProblem($problems[$j], $users[$i]);
                RunsFactory::gradeRun($runData);
            }
            $login[] = self::login($users[$i]);
        }

        if ($withSuggestions) {
            Utils::deleteAllSuggestions();

            QualityNominationFactory::createSuggestion(
                $login[0],
                $problems[0]['request']['problem_alias'],
                2, /* difficulty */
                1, /* quality */
                ['DP', 'Math']
            );

            QualityNominationFactory::createSuggestion(
                $login[1],
                $problems[0]['request']['problem_alias'],
                3, /* difficulty */
                3, /* quality */
                ['Matrices', 'Math']
            );

            QualityNominationFactory::createSuggestion(
                $login[2],
                $problems[0]['request']['problem_alias'],
                4, /* difficulty */
                0, /* quality */
                ['Math', 'DP']
            );

            QualityNominationFactory::createSuggestion(
                $login[3],
                $problems[0]['request']['problem_alias'],
                2, /* difficulty */
                4, /* quality */
                ['DP', 'Math', 'Greedy']
            );

            QualityNominationFactory::createSuggestion(
                $login[4],
                $problems[0]['request']['problem_alias'],
                3, /* difficulty */
                4, /* quality */
                ['Greedy', 'DP']
            );

            QualityNominationFactory::createSuggestion(
                $login[1],
                $problems[1]['request']['problem_alias'],
                3, /* difficulty */
                null, /* quality */
                ['Matrices', 'Math']
            );

            QualityNominationFactory::createSuggestion(
                $login[2],
                $problems[1]['request']['problem_alias'],
                null, /* difficulty */
                1, /* quality */
                ['Math', 'DP']
            );

            QualityNominationFactory::createSuggestion(
                $login[3],
                $problems[1]['request']['problem_alias'],
                4, /* difficulty */
                null, /* quality */
                ['DP', 'Math', 'Greedy']
            );

            QualityNominationFactory::createSuggestion(
                $login[4],
                $problems[1]['request']['problem_alias'],
                4, /* difficulty */
                0, /* quality */
                ['Greedy', 'DP']
            );

            QualityNominationFactory::createSuggestion(
                $login[2],
                $problems[2]['request']['problem_alias'],
                4, /* difficulty */
                4, /* quality */
                ['Search', 'DP', 'Greedy']
            );

            QualityNominationFactory::createSuggestion(
                $login[3],
                $problems[2]['request']['problem_alias'],
                4, /* difficulty */
                1, /* quality */
                ['Geometry', 'DP', 'Search', 'Greedy']
            );

            QualityNominationFactory::createSuggestion(
                $login[4],
                $problems[2]['request']['problem_alias'],
                1, /* difficulty */
                1, /* quality */
                ['Search', 'Greedy']
            );

            QualityNominationFactory::createSuggestion(
                $login[3],
                $problems[3]['request']['problem_alias'],
                4, /* difficulty */
                3, /* quality */
                ['DP', 'Math', 'Greedy']
            );

            QualityNominationFactory::createSuggestion(
                $login[4],
                $problems[3]['request']['problem_alias'],
                3, /* difficulty */
                null, /* quality */
                ['Greedy', 'DP']
            );

            QualityNominationFactory::createSuggestion(
                $login[4],
                $problems[4]['request']['problem_alias'],
                3, /* difficulty */
                null, /* quality */
                ['Greedy', 'DP']
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

        $problemOfTheWeek = \OmegaUp\DAO\ProblemOfTheWeek::getByDificulty('easy');
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
        $login = [];
        for ($i = 0; $i < 10; $i++) {
            $contestant = UserFactory::createUser();
            for ($j = 0; $j < $numberOfProblems; $j++) {
                $runData = RunsFactory::createRunToProblem($problemData[$j], $contestant);
                RunsFactory::gradeRun($runData);
            }
            $login[] = self::login($contestant);
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
                    $login[$userIdx],
                    $problemData[$problemIdx]['request']['problem_alias'],
                    $difficultyRatings[$problemIdx][$userIdx],
                    $qualityRatings[$problemIdx][$userIdx],
                    [] // No tags.
                );
            }
        }

        // Set date for all quality nominations as 1 week ago, so that they are eligible for
        // current problem of the week.
        $dateOneWeekAgo = (new DateTime())->sub(new DateInterval('P7D'))->format('Y-m-d H:i:s');
        \OmegaUp\MySQLConnection::getInstance()->Execute('UPDATE `QualityNominations` SET `time` = ?', [$dateOneWeekAgo]);

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
            $problemData[0]['request']['problem_alias'],
            null,
            1,
            ['DP', 'Math']
        );
        QualityNominationFactory::createSuggestion(
            $login[1],
            $problemData[0]['request']['problem_alias'],
            3,
            3,
            ['Math', 'DP']
        );
        QualityNominationFactory::createSuggestion(
            $login[2],
            $problemData[0]['request']['problem_alias'],
            4,
            0,
            ['Matrices', 'Math']
        );
        QualityNominationFactory::createSuggestion(
            $login[3],
            $problemData[0]['request']['problem_alias'],
            null,
            null,
            ['Math']
        );
        QualityNominationFactory::createSuggestion(
            $login[4],
            $problemData[0]['request']['problem_alias'],
            3,
            4,
            ['DP', 'Math', 'Greedy']
        );
        QualityNominationFactory::createSuggestion(
            $login[5],
            $problemData[0]['request']['problem_alias'],
            3,
            null,
            []
        );
        QualityNominationFactory::createSuggestion(
            $login[6],
            $problemData[0]['request']['problem_alias'],
            null,
            1,
            []
        );
        QualityNominationFactory::createSuggestion(
            $login[7],
            $problemData[0]['request']['problem_alias'],
            4,
            null,
            ['Greedy', 'DP']
        );
        QualityNominationFactory::createSuggestion(
            $login[8],
            $problemData[0]['request']['problem_alias'],
            4,
            0,
            ['DP']
        );
        QualityNominationFactory::createSuggestion(
            $login[9],
            $problemData[0]['request']['problem_alias'],
            4,
            4,
            ['DP', 'Math']
        );

        QualityNominationFactory::createSuggestion(
            $login[0],
            $problemData[1]['request']['problem_alias'],
            4,
            1,
            ['Search', 'Geometry']
        );
        QualityNominationFactory::createSuggestion(
            $login[1],
            $problemData[1]['request']['problem_alias'],
            1,
            1,
            ['Search', 'Geometry']
        );
        QualityNominationFactory::createSuggestion(
            $login[2],
            $problemData[1]['request']['problem_alias'],
            4,
            3,
            ['Matrices', 'Search']
        );
        QualityNominationFactory::createSuggestion(
            $login[3],
            $problemData[1]['request']['problem_alias'],
            3,
            null,
            ['Search']
        );
        QualityNominationFactory::createSuggestion(
            $login[4],
            $problemData[1]['request']['problem_alias'],
            3,
            null,
            ['Search', 'Math', 'Geometry']
        );
        QualityNominationFactory::createSuggestion(
            $login[5],
            $problemData[1]['request']['problem_alias'],
            3,
            null,
            []
        );
        QualityNominationFactory::createSuggestion(
            $login[6],
            $problemData[1]['request']['problem_alias'],
            null,
            1,
            []
        );
        QualityNominationFactory::createSuggestion(
            $login[7],
            $problemData[1]['request']['problem_alias'],
            3,
            null,
            ['Search', 'DP']
        );
        QualityNominationFactory::createSuggestion(
            $login[8],
            $problemData[1]['request']['problem_alias'],
            4,
            1,
            ['DP']
        );
        QualityNominationFactory::createSuggestion(
            $login[9],
            $problemData[1]['request']['problem_alias'],
            4,
            3,
            ['Geometry', 'Math']
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
            \OmegaUp\DAO\QualityNominations::mostVotedTags($tagsWithLittleVotes, 0.25),
            [],
            'There must be at least 5 votes.'
        );

        $tooManyTagsWithMaxVotes = [
            'T1' => 9, 'T2' => 9, 'T3' => 9, 'T4' => 9, 'T5' => 9, 'T6' => 9,
            'T7' => 9, 'T8' => 9, 'T9' => 9, 'T10' => 9, 'T11' => 9, 'T12' => 9];

        $this->assertEquals(
            \OmegaUp\DAO\QualityNominations::mostVotedTags($tooManyTagsWithMaxVotes, 0.25),
            [],
            'There must be a maximum number of tags to be assigned.'
        );
    }
}
