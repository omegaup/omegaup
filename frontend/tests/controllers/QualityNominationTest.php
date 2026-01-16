<?php
class QualityNominationTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();

        \OmegaUp\Test\Factories\QualityNomination::initQualityReviewers();
        \OmegaUp\Test\Factories\QualityNomination::initTopicTags();
    }

    /**
     * A PHPUnit data provider for all the tests that can accept a status.
     *
     * @return list<array{0: string, 1: int}>
     */
    public function qualityNominationsDemotionStatusProvider(): array {
        return [
            ['banned', \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED],
            ['warning', \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING],
        ];
    }

    public function testGetNominationsHasAuthorAndNominatorSet() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $login = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );

        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]),
        ]));

        $response = \OmegaUp\Controllers\QualityNomination::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token
        ]));
        self::assertArrayHasKey('author', $response['nominations'][0]);
        self::assertArrayHasKey('nominator', $response['nominations'][0]);
    }

    public function testGetByIdHasAuthorAndNominatorSet() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $result = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]),
        ]));

        $nomination = \OmegaUp\DAO\QualityNominations::getById(
            $result['qualitynomination_id']
        );
        self::assertArrayHasKey('author', $nomination);
        self::assertArrayHasKey('nominator', $nomination);
        self::assertSame(
            $identity->username,
            $nomination['nominator']['username']
        );
    }

    public function testApiDetailsReturnsFieldsRequiredByUI() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $contents = json_encode([
                 'statements' => [
                    'es' => [
                        'markdown' => 'a + b',
                    ],
                 ],
                 'rationale' => 'ew',
                 'reason' => 'offensive',
            ]);

        $login = self::login($identity);
        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'demotion',
            'contents' => $contents,
        ]));

        // Login as a reviewer and approve ban.
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        $request = new \OmegaUp\Request(
            [
            'auth_token' => $reviewerLogin->auth_token,
            'qualitynomination_id' => $qualitynomination['qualitynomination_id']]
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertSame(
            'demotion',
            $details['nomination'],
            'Should have set demotion'
        );
        $this->assertSame(
            $identity->username,
            $details['nominator']['username'],
            'Should have set user'
        );
        $this->assertSame(
            $problemData['request']['problem_alias'],
            $details['problem']['alias'],
            'Should have set problem'
        );
        $this::assertArrayHasKey('author', $details);
        $this->assertSame(
            json_decode(
                $contents,
                true
            ),
            $details['contents'],
            'Should have set contents'
        );
        $this->assertSame(
            true,
            $details['reviewer'],
            'Should have set reviewer'
        );
        $this->assertSame(
            $qualitynomination['qualitynomination_id'],
            $details['qualitynomination_id'],
            'Should have set qualitynomination_id'
        );
    }

    /**
     * Basic test. Check that before nominating a problem for quality, the user
     * must have solved it first.
     */
    public function testMustSolveBeforeNominatingItForPromotion() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );

        $login = self::login($identity);
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

        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        \OmegaUp\Controllers\QualityNomination::apiCreate($r);

        $response = \OmegaUp\Controllers\QualityNomination::apiMyList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertSame(1, count($response['nominations']));
        $nomination = $response['nominations'][0];
        $this->assertSame(
            $problemData['request']['problem_alias'],
            $nomination['problem']['alias']
        );
        $this->assertSame(
            $problemData['request']['problem_alias'],
            $nomination['problem']['alias']
        );
        $this->assertSame(
            \OmegaUp\Controllers\QualityNomination::REVIEWERS_PER_NOMINATION,
            count($nomination['votes'])
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'qualitynomination_id' => $nomination['qualitynomination_id'],
        ]));
        $this->assertSame(
            $identity->username,
            $details['nominator']['username']
        );
        $this->assertNotNull($details['original_contents']);
    }

    /**
     * Check if only a category tag is allowed for a nomination of
     * type 'quality tag'.
     */
    public function testCategoryTagOnQualityTagNomination() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'nomination' => 'quality_tag',
                'contents' => json_encode([
                    'quality_seal' => false,
                    'tags' => ['problemTagFunctions', 'problemTagRecursion'],
                ]),
            ]));
            $this->fail('The user must be a reviewer.');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }

        $reviewer = \OmegaUp\Test\Factories\QualityNomination::$reviewers[0];
        $reviewerLogin = self::login($reviewer);
        try {
            \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'nomination' => 'quality_tag',
                'contents' => json_encode([
                    'quality_seal' => false,
                    'level' => 'problemLevel',
                    'tags' => ['problemTagFunctions', 'problemTagRecursion'],
                ]),
            ]));
            $this->fail('The tag should be one of the level tags group.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }

        try {
            \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'nomination' => 'quality_tag',
                'contents' => json_encode([
                    'quality_seal' => false,
                    'tags' => ['problemTopic'],
                ]),
            ]));
            $this->fail('The tag should be one of the public tags group.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }

        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode([
                'quality_seal' => false,
                'tag' => 'problemLevelAdvancedCompetitiveProgramming',
            ]),
        ]));

        // Now the reviewer can edit a nomination for a problem
        $contents = [
            'quality_seal' => false,
            'level' => 'problemLevelBasicKarel',
        ];
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode($contents),
        ]));
        $qualityNomination = \OmegaUp\DAO\QualityNominations::getQualityNominationContentsForProblemAndReviewer(
            $reviewer,
            $problemData['problem']
        );
        $this->assertSame(
            $contents,
            json_decode($qualityNomination['contents'], true)
        );
    }

    /**
     * Check that before suggesting improvements to a problem, the user must
     * have solved it first.
     */
    public function testMustSolveBeforeSuggesting() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );

        $login = self::login($identity);
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
            $this->fail(
                'Should not have been able to make suggestion about the problem'
            );
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            // still expected.
        }

        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $response = \OmegaUp\Controllers\QualityNomination::apiCreate($r);

        $r['qualitynomination_id'] = $response['qualitynomination_id'];
        $nomination = \OmegaUp\Controllers\QualityNomination::apiDetails($r);
        $this->assertSame(
            $problemData['request']['problem_alias'],
            $nomination['problem']['alias']
        );
    }

    /**
     * Basic test. Check that before nominating a problem for demotion, the
     * user might not have solved it first.
     */
    public function testNominatingForDemotion() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
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
                ['http://localhost:8001/arena/prueba/#problems/sumas' => 'sumas',
                 'http://localhost:8001/arena/prueba/practice/#problems/sumas' => 'sumas',
                 'http://localhost:8001/arena/problem/sumas#problems' => 'sumas',
                 'http://localhost:8001/course/prueba/assignment/prueba/#problems/sumas' => 'sumas',
                 'http://localhost:8001/arena/prueba/#problems/sumas29187' => 'sumas29187',
                 'http://localhost:8001/arena/prueba/practice/#problems/sumas_29187' => 'sumas_29187',
                 'http://localhost:8001/arena/problem/_sumas29187-#problems' => '_sumas29187-',
                 'http://localhost:8001/course/prueba/assignment/prueba/#problems/___asd_-_23-2-_' => '___asd_-_23-2-_'];

        foreach ($inputAndExpectedOutput as $input => $expectedOutput) {
            $actualOutput = \OmegaUp\Controllers\QualityNomination::extractAliasFromArgument(
                $input
            );
            $this->assertSame(
                $expectedOutput,
                $actualOutput,
                'Incorrect alias was extracted from URL.'
            );
        }
    }

    /**
     * Check that a non-reviewer user cannot change the status of a demotion qualitynomination.
     * @dataProvider qualityNominationsDemotionStatusProvider
     */
    public function testDemotionCannotBeResolvedByRegularUser(string $status) {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'demotion',
            'contents' => json_encode([
                'rationale' => 'ew',
                'reason' => 'offensive',
            ]),
        ]));

        try {
            \OmegaUp\Controllers\QualityNomination::apiResolve(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'status' => $status,
                    'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
                    'rationale' => 'ew plus something else'
                ])
            );
            $this->fail("Normal user shouldn't be able to resolve demotion");
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // Expected.
        }
    }

    /**
     * Check that a demotion can be banned or warning and then reverted by a reviewer.
     * @dataProvider qualityNominationsDemotionStatusProvider
     */
    public function testDemotionCanBeResolvedAndLaterRevertedByReviewer(
        string $status,
        int $visibility
    ) {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
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
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'status' => $status,
                'problem_alias' => $problemData['request']['problem_alias'],
                'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
                'rationale' => 'ew plus something else',
            ])
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
        ]));
        $this->assertSame(
            $status,
            $details['nomination_status'],
            "qualitynomination should have been marked as {$status}"
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertSame(
            $visibility,
            $problem['visibility'],
            "Problem should have been public {$status}"
        );

        // Revert ban.
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'status' => 'resolved',
                'problem_alias' => $problemData['request']['problem_alias'],
                'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
                'rationale' => 'ew'
            ])
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
        ]));
        $this->assertSame(
            'resolved',
            $details['nomination_status'],
            'qualitynomination should have been marked as resolved'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertSame(
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
            $problem['visibility'],
            'Problem should have been made public'
        );
    }

    /**
     * Check that multiple demotion can be banned or warning and then reverted by a reviewer.
     * @dataProvider qualityNominationsDemotionStatusProvider
     */
    public function testMultipleDemotionCanBeResolvedAndLaterRevertedByReviewer(
        string $status,
        int $visibility
    ) {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
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
        $qualitynomination1 = \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
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
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'status' => $status,
                'problem_alias' => $problemData['request']['problem_alias'],
                'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
                'rationale' => 'ew plus something else',
                'all' => true,
            ])
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
        ]));
        $this->assertSame(
            $status,
            $details['nomination_status'],
            "qualitynomination should have been marked as {$status}"
        );
        $details1 = \OmegaUp\Controllers\QualityNomination::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'qualitynomination_id' => $qualitynomination1['qualitynomination_id'],
            ])
        );
        $this->assertSame(
            $status,
            $details1['nomination_status'],
            "qualitynomination should have been marked as {$status}"
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertSame(
            $visibility,
            $problem['visibility'],
            "Problem should have been public {$status}"
        );

        // Revert ban.
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'status' => 'resolved',
                'problem_alias' => $problemData['request']['problem_alias'],
                'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
                'rationale' => 'ew',
                'all' => true,
            ])
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
        ]));
        $this->assertSame(
            'resolved',
            $details['nomination_status'],
            'qualitynomination should have been marked as resolved'
        );
        $details1 = \OmegaUp\Controllers\QualityNomination::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'qualitynomination_id' => $qualitynomination1['qualitynomination_id'],
            ])
        );
        $this->assertSame(
            'resolved',
            $details1['nomination_status'],
            'qualitynomination should have been marked as resolved'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertSame(
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
            $problem['visibility'],
            'Problem should have been made public'
        );
    }

    /**
     * Check that a demotion banned or warning by a reviewer sends an email to the problem creator.
     * @dataProvider qualityNominationsDemotionStatusProvider
     */
    public function testDemotionResolvedByReviewerAndSendMail(string $status) {
        $emailSender = new \OmegaUp\Test\FakeEmailSender();
        $scopedSender = new \OmegaUp\Test\ScopedEmailSender($emailSender);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
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
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );

        \OmegaUp\Controllers\QualityNomination::apiResolve(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'status' => $status,
                'problem_alias' => $problemData['request']['problem_alias'],
                'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
                'rationale' => 'qwert plus something else'
            ])
        );

        $this->assertCount(1, $emailSender->listEmails);
        $this->assertStringContainsString(
            $problemData['problem']->title,
            $emailSender->listEmails[0]['subject']
        );
        $this->assertStringContainsString(
            $problemData['author']->name,
            $emailSender->listEmails[0]['body']
        );
        $this->assertStringContainsString(
            'qwert',
            $emailSender->listEmails[0]['body']
        );
        $this->assertStringContainsString(
            'something else',
            $emailSender->listEmails[0]['body']
        );

        unset($scopedSender);
    }

    /**
     * Check that a multiple demotion banned or warning by a reviewer sends an email to the problem creator.
     * @dataProvider qualityNominationsDemotionStatusProvider
     */
    public function testMultipleDemotionResolvedByReviewerAndSendMail(string $status) {
        $emailSender = new \OmegaUp\Test\FakeEmailSender();
        $scopedSender = new \OmegaUp\Test\ScopedEmailSender($emailSender);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
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
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
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
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'status' => $status,
                'problem_alias' => $problemData['request']['problem_alias'],
                'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
                'rationale' => 'qwert plus something else',
                'all' => true,
            ])
        );

        $this->assertCount(1, $emailSender->listEmails);
        $this->assertStringContainsString(
            $problemData['problem']->title,
            $emailSender->listEmails[0]['subject']
        );
        $this->assertStringContainsString(
            $problemData['author']->name,
            $emailSender->listEmails[0]['body']
        );
        $this->assertStringContainsString(
            'qwert',
            $emailSender->listEmails[0]['body']
        );
        $this->assertStringContainsString(
            'something else',
            $emailSender->listEmails[0]['body']
        );

        unset($scopedSender);
    }

    /**
     * Check that a demotion's logs is saved correctly.
     * @dataProvider qualityNominationsDemotionStatusProvider
     */
    public function testDemotionLogsSavedCorrectly(
        string $status,
        int $visibility
    ) {
        ['identity' => $author] = \OmegaUp\Test\Factories\User::createUser(new \OmegaUp\Test\Factories\UserParams(
            [
                'username' => 'user_test_author'
            ]
        ));
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams(
            [
                'author' => $author,
                'title' => 'problem_1'
            ]
        ));
        ['identity' => $nominator] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($nominator);
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
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );

        $rationale = 'rationale';
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'status' => $status,
                'problem_alias' => $problemData['request']['problem_alias'],
                'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
                'rationale' => $rationale,
            ])
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
        ]));
        $this->assertSame($rationale, $details['contents']['rationale']);

        $logs = \OmegaUp\DAO\QualityNominationLog::getAllLogsForNomination(
            $qualitynomination['qualitynomination_id']
        );

        $this->assertCount(1, $logs);
        $this->assertSame(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]->user_id,
            $logs[0]->user_id
        );
        $this->assertSame('open', $logs[0]->from_status);
        $this->assertSame($status, $logs[0]->to_status);
        $this->assertSame($rationale, $logs[0]->rationale);

        // Revert ban.
        $rationale = 'problem solved';
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'status' => 'resolved',
                'problem_alias' => $problemData['request']['problem_alias'],
                'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
                'rationale' => $rationale
            ])
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
        ]));
        $this->assertSame($rationale, $details['contents']['rationale']);

        $logs = \OmegaUp\DAO\QualityNominationLog::getAllLogsForNomination(
            $qualitynomination['qualitynomination_id']
        );

        $this->assertCount(2, $logs);
        $this->assertSame(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]->user_id,
            $logs[1]->user_id
        );
        $this->assertSame($status, $logs[1]->from_status);
        $this->assertSame('resolved', $logs[1]->to_status);
        $this->assertSame($rationale, $logs[1]->rationale);
    }

    /**
     * Check that a demotion can be resolved by a reviewer.
     */
    public function testDemotionCanBeBannedByReviewer() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public'
        ]));
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
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
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'resolved',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew'
        ]);
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertSame(
            'resolved',
            $details['nomination_status'],
            'qualitynomination should have been marked as resolved'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertSame(
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
            $problem['visibility'],
            'Problem should have remained public'
        );
    }

    /**
     * A PHPUnit data provider for all the tests that can accept a column for search nominations.
     *
     * @return list<array{0: string, 1:string, 2: int}>
     */
    public function qualityNominationsDemotionSearchColumnsProvider(): array {
        return [
            ['problem_alias', 'problem_1', 1],
            ['author_username', 'user_test_author', 1],
            ['nominator_username', 'user_test_nominator',1],
            ['nominator_username', 'invalid_user_test_nominator', 0],
        ];
    }

    /**
     * Check that can search nominations.
     * @dataProvider qualityNominationsDemotionSearchColumnsProvider
     */
    public function testSearchNominations(
        string $column,
        string $query,
        int $valueExpected
    ) {
        ['identity' => $author] = \OmegaUp\Test\Factories\User::createUser(new \OmegaUp\Test\Factories\UserParams(
            [
                'username' => 'user_test_author'
            ]
        ));
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams(
            [
                'author' => $author,
                'title' => 'problem_1'
            ]
        ));
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(new \OmegaUp\Test\Factories\UserParams(
            [
                'username' => 'user_test_nominator'
            ]
        ));
        $login = self::login($identity);

        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
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

        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        $response = \OmegaUp\Controllers\QualityNomination::apiList(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
            ])
        );

        $this->assertCount(1, $response['nominations']);
        // Search for $column
        $response = \OmegaUp\Controllers\QualityNomination::apiList(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'query' => $query,
                'column' => $column
            ])
        );
        $this->assertCount($valueExpected, $response['nominations']);
    }

    /**
     * @dataProvider qualityNominationsDemotionStatusProvider
     * Check that a demotion can be banned and then reopned by a reviewer.
     */
    public function testDemotionCanBeResolvedAndThenReopenedByReviewer(
        $status,
        $visibility
    ) {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
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
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => $status,
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew plus something else'
        ]);
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertSame(
            $status,
            $details['nomination_status'],
            'qualitynomination should have been marked as banned'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertSame(
            $visibility,
            $problem['visibility'],
            "Problem should have been public {$status}"
        );

        // Reopen demotion request.
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'open',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew'
        ]);
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertSame(
            'open',
            $details['nomination_status'],
            'qualitynomination should have been re-opened'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertSame(
            $visibility,
            $problem['visibility'],
            "Problem should have remained public {$status}"
        );
    }

    /**
     * Check that a demotion of a private problem can be banned and
     * then resolved, and it keeps its original visibility
     * @dataProvider qualityNominationsDemotionStatusProvider
     */
    public function testDemotionOfPrivateProblemResolvedAndThenBannedKeepsItsOriginalVisibility(
        string $status,
        int $visibility
    ) {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'private'
        ]));
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
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
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => $status,
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew plus something else'
        ]);
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertSame(
            $status,
            $details['nomination_status'],
            "qualitynomination should have been marked as {$status}"
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertSame(
            // To transform from public to private (banned or warning).
            $visibility == \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED ? \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED : \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_WARNING,
            $problem['visibility'],
            'Problem should have been private resolved'
        );

        // Reopen demotion request.
        $request = new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'status' => 'resolved',
            'problem_alias' => $problemData['request']['problem_alias'],
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
            'rationale' => 'ew'
        ]);
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            $request
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails($request);
        $this->assertSame(
            'resolved',
            $details['nomination_status'],
            'qualitynomination should have been re-opened'
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails($request);
        $this->assertSame(
            \OmegaUp\ProblemParams::VISIBILITY_PRIVATE,
            $problem['visibility'],
            'Problem should have been private'
        );
    }

    /**
     * User who tried a problem but couldn't solve it yet (non AC, CE, JE verdicts),
     * should be able to send a dismissal or suggestion for it adding the before_ac
     * flag on nomination contents.
     */
    public function testBeforeAcNomination() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'nomination' => 'suggestion',
                'contents' => json_encode([
                    'quality' => 3,
                    'tags' => ['problemTopicGraphTheory', 'problemTopicGreedy', 'problemTopicBinarySearch'],
                    'before_ac' => true,
                ]),
            ]));
            $this->fail('Must have tried to solve the problem first.');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            $this->assertSame(
                $e->getMessage(),
                'qualityNominationMustHaveTriedToSolveProblem'
            );
        }

        // Now TRY to solve the problem
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'WA', 60);
        $login = self::login($identity);
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'suggestion',
            'contents' => json_encode([
                'quality' => 3,
                'tags' => ['problemTopicGraphTheory', 'problemTopicGreedy'],
                'before_ac' => true,
            ]),
        ]));

        // Dismissals could be sent before AC also
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'dismissal',
            'contents' => json_encode([
                'before_ac' => true
            ]),
        ]));

        // Now solve the problem, it must not be allowed to send a before AC
        // nomination, as the problem is already solved
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'nomination' => 'suggestion',
                'contents' => json_encode([
                    'quality' => 3,
                    'tags' => ['problemTopicGreedy', 'problemTopicMath'],
                    'before_ac' => true,
                ]),
            ]));
            $this->fail('Must not have solved the problem.');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            $this->assertSame(
                $e->getMessage(),
                'qualityNominationMustNotHaveSolvedProblem'
            );
        }
    }

    /**
     * Check that before a duplicate nomination needs to have a valid original problem.
     */
    public function testNominatingForDuplicate() {
        $originalProblemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

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
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $login = self::login($identity);
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
                'tags' => ['problemTopicSorting'],
            ]),
        ]));

        // Login as an arbitrary reviewer.
        $login = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        $response = \OmegaUp\Controllers\QualityNomination::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $nomination = $this->findByPredicate(
            $response['nominations'],
            fn ($nomination) => $nomination['problem']['alias'] == $problemData['request']['problem_alias']
        );
        $this->assertNotNull($nomination);
        $this->assertSame(
            \OmegaUp\Controllers\QualityNomination::REVIEWERS_PER_NOMINATION,
            count($nomination['votes'])
        );

        // Login as one of the reviewers of that nomination.
        $reviewer = $this->findByPredicate(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers,
            fn ($reviewer) => $reviewer->username == $nomination['votes'][0]['user']['username']
        );
        $this->assertNotNull($reviewer);
        $login = self::login($reviewer);
        $response = \OmegaUp\Controllers\QualityNomination::apiMyAssignedList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertArrayContainsWithPredicate(
            $response['nominations'],
            fn ($nomination) => $nomination['problem']['alias'] == $problemData['request']['problem_alias']
        );
    }

    /**
     * Duplicate tag test.
     */
    public function testTagsForDuplicate() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $login = self::login($identity);
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
                    'tags' => ['problemTopicSorting', 'problemTopicMath', 'problemTopicMath'],
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
                    'tags' => ['problemTopicSorting', 'problemTopicMath', 'problemTopicMath'],
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
                'tags' => ['problemTopicSorting', 'problemTopicMath'],
            ]),
        ]));

        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'suggestion',
            'contents' => json_encode([
                // No difficulty!
                'quality' => 3,
                'tags' => ['problemTopicSorting', 'problemTopicMath'],
            ]),
        ]));
    }

    public function testIncorrectTag() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $login = self::login($identity);
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
                    'tags' => ['ez', 'ez-pz'],
                ]),
            ]));
            $this->fail('Incorrect tags should be caught.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
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
                    'tags' => ['ez', 'ez-pz'],
                ]),
            ]));
            $this->fail('Incorrect tags should be caught.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // Expected.
        }
    }

    /**
     * Test that nomination list by default only shows promotions or demotions.
     * All other nomination types should not appear on this list.
     */
    public function testNominationListDoesntShowSuggestionsOrDismisssal() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Create promotion nomination.
        $login = self::login($identity);
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
                'tags' => ['problemTopicSorting'],
            ]),
        ]));

        // Create demotion nomination.
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
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
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identity,
            $problemData['request']['problem_alias'],
            null,
            1,
            ['problemTopicDynamicProgramming', 'problemTopicMath'],
            false
        );

        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        $list = \OmegaUp\Controllers\QualityNomination::apiList(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
        ]));
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
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        $login = self::login($identity);
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
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        try {
            $this->assertSame(
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
            \OmegaUp\Controllers\QualityNomination::apiCreate($r);
            $pd = \OmegaUp\DAO\QualityNominations::getByUserAndProblem(
                $r->user->user_id,
                $problem->problem_id,
                $r['nomination'],
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
        $problemData = [];
        $problemData[0] = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData[1] = \OmegaUp\Test\Factories\Problem::createProblem();
        self::setUpSyntheticSuggestions($problemData);

        $globalContents = \OmegaUp\DAO\QualityNominations::getAllNominations();
        $actualGlobals = \OmegaUp\DAO\QualityNominations::calculateGlobalDifficultyAndQuality(
            $globalContents
        );
        $expectedGlobals = [23 / 13 /*quality*/, 54 / 16 /*difficulty*/];

        $this->assertSame($expectedGlobals, $actualGlobals);
    }

    public function testGetSuggestionRowMap() {
        $problemData = [];
        $problemData[0] = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData[1] = \OmegaUp\Test\Factories\Problem::createProblem();
        self::setUpSyntheticSuggestions($problemData);
        $contents = [];
        $actualResult = [];
        $expectedResult = [];
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
                'problemTopicDynamicProgramming' => 6,
                'problemTopicMath' => 6,
                'problemTopicMatrices' => 1,
                'problemTopicGreedy' => 2,
                ]
            ];
        $expectedResult[1] = [
            'quality_sum' => 10,
            'quality_n' => 6,
            'difficulty_sum' => 29,
            'difficulty_n' => 9,
            'tags_n' => 15,
            'tags' => [
                'problemTopicSorting' => 6,
                'problemTopicGeometry' => 4,
                'problemTopicMatrices' => 1,
                'problemTopicMath' => 2,
                'problemTopicDynamicProgramming' => 2,
            ],
        ];
        $this->assertSame($expectedResult, $actualResult);
    }

    /*
        Creates 5 problems and 5 users.
         - The first time the cronjob is executed, the problems are voted by
           users as unranked users (with vote weight = 2)
         - The second time, the problems are voted by ranked users according to
           the number of problems they solved
    */
    public function testAggregateFeedback() {
        $problemData = [];
        $newProblem = [];
        for ($i = 0; $i < 5; $i++) {
            $problemData[$i] = \OmegaUp\Test\Factories\Problem::createProblem();
        }
        $identityData = [];
        for ($i = 0; $i < 5; $i++) {
            ['identity' => $identityData[$i]] = \OmegaUp\Test\Factories\User::createUser();
        }
        self::setUpRankForUsers($problemData, $identityData, true);

        // Create and extra user and send a before_ac nomination
        // that should not affect the current results.
        $beforeACUser = \OmegaUp\Test\Factories\User::createUser();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData[0],
            $beforeACUser['identity']
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'WA');
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $beforeACUser['identity'],
            $problemData[0]['request']['problem_alias'],
            4, /* difficulty */
            4, /* quality */
            ['problemTopicDynamicProgramming', 'problemTopicMath'],
            true
        );

        \OmegaUp\Test\Utils::runAggregateFeedback();

        $newProblem[0] = \OmegaUp\DAO\Problems::getByAlias(
            $problemData[0]['request']['problem_alias']
        );
        $this->assertEqualsWithDelta(
            2.971428571,
            $newProblem[0]->difficulty,
            0.001,
            'Wrong difficulty.'
        );
        $this->assertEqualsWithDelta(
            2.2,
            $newProblem[0]->quality,
            0.001,
            'Wrong quality.'
        );
        $this->assertSame(
            '[0, 0, 2, 2, 1]',
            $newProblem[0]->difficulty_histogram,
            'Wrong difficulty histogram'
        );
        $this->assertSame(
            '[1, 1, 0, 1, 2]',
            $newProblem[0]->quality_histogram,
            'Wrong quality histogram'
        );

        $newProblem[2] = \OmegaUp\DAO\Problems::getByAlias(
            $problemData[2]['request']['problem_alias']
        );
        $this->assertEqualsWithDelta(
            0,
            $newProblem[2]->difficulty,
            0.001,
            'Wrong difficulty'
        );
        $this->assertEqualsWithDelta(
            0,
            $newProblem[2]->quality,
            0.001,
            'Wrong quality'
        );

        $tagArrayForProblem1 = \OmegaUp\DAO\ProblemsTags::getProblemTags(
            $newProblem[0],
            false /* public_only */,
            true /* includeVoted */
        );

        $tagArrayForProblem3 = \OmegaUp\DAO\ProblemsTags::getProblemTags(
            $newProblem[2],
            false /* public_only */,
            true /* includeVoted */
        );

        $extractName = fn ($tag) => $tag['name'];

        $tags1 = array_map($extractName, $tagArrayForProblem1);
        $this->assertEqualsCanonicalizing(
            $tags1,
            [
                'problemLevelBasicIntroductionToProgramming',
                'problemRestrictedTagLanguage',
                'problemTopicDynamicProgramming',
                'problemTopicGreedy',
                'problemTopicMath',
                'problemTopicMatrices'
            ]
        );

        $tags3 = array_map($extractName, $tagArrayForProblem3);
        $this->assertEqualsCanonicalizing(
            $tags3,
            [
                'problemLevelBasicIntroductionToProgramming',
                'problemRestrictedTagLanguage',
                'problemTopicDynamicProgramming',
                'problemTopicGreedy',
                'problemTopicGeometry',
                'problemTopicSorting'
            ]
        );

        \OmegaUp\Test\Utils::runUpdateRanks();
        \OmegaUp\Test\Utils::runAggregateFeedback();

        $newProblem[0] = \OmegaUp\DAO\Problems::getByAlias(
            $problemData[0]['request']['problem_alias']
        );
        $this->assertEqualsWithDelta(
            2.895384615,
            $newProblem[0]->difficulty,
            0.001,
            'Wrong difficulty.'
        );
        $this->assertEqualsWithDelta(
            2.538378378,
            $newProblem[0]->quality,
            0.001,
            'Wrong quality.'
        );

        $newProblem[1] = \OmegaUp\DAO\Problems::getByAlias(
            $problemData[1]['request']['problem_alias']
        );
        $this->assertEqualsWithDelta(
            3.446886447,
            $newProblem[1]->difficulty,
            0.001,
            'Wrong difficulty.'
        );
        $this->assertEqualsWithDelta(
            0,
            $newProblem[1]->quality,
            0.001,
            'Wrong quality.'
        );

        $newProblem[2] = \OmegaUp\DAO\Problems::getByAlias(
            $problemData[2]['request']['problem_alias']
        );
        $this->assertEqualsWithDelta(
            2.684981685,
            $newProblem[2]->difficulty,
            0.001,
            'Wrong difficulty'
        );
        $this->assertEqualsWithDelta(
            1.736164736,
            $newProblem[2]->quality,
            0.001,
            'Wrong quality'
        );

        $tagArrayForProblem1 = \OmegaUp\DAO\ProblemsTags::getProblemTags(
            $newProblem[0],
            false /* public_only */,
            true /* includeVoted */
        );

        $tagArrayForProblem3 = \OmegaUp\DAO\ProblemsTags::getProblemTags(
            $newProblem[2],
            false /* public_only */,
            true /* includeVoted */
        );

        $tags1 = array_map($extractName, $tagArrayForProblem1);
        $this->assertEqualsCanonicalizing(
            $tags1,
            [
                'problemLevelBasicIntroductionToProgramming',
                'problemTopicDynamicProgramming',
                'problemTopicGreedy',
                'problemTopicMath',
                'problemRestrictedTagLanguage'
            ]
        );

        $tags3 = array_map($extractName, $tagArrayForProblem3);
        $this->assertEqualsCanonicalizing(
            $tags3,
            [
                'problemLevelBasicIntroductionToProgramming',
                'problemTopicDynamicProgramming',
                'problemTopicGreedy',
                'problemTopicGeometry',
                'problemTopicSorting',
                'problemRestrictedTagLanguage'
            ]
        );
    }

    /**
     * Test if the problem's quality_seal sets to true after receiving
     * the feedback of reviewers.
     */
    public function testReviewersFeedbackPostive() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode([
                'quality_seal' => true,
                'tag' => 'problemLevelBasicKarel',
                'tags' => ['problemTagBitManipulation', 'problemTagRecursion'],
            ]),
        ]));

        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[1]
        );
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode([
                'quality_seal' => true,
                'tag' => 'problemLevelBasicKarel',
                'tags' => ['problemTagBitManipulation', 'problemTagRecursion'],
            ]),
        ]));

        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[2]
        );
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode([
                'quality_seal' => false,
                'tag' => 'problemLevelAdvancedSpecializedTopics',
            ]),
        ]));

        \OmegaUp\Test\Utils::runAggregateFeedback();

        $problem = \OmegaUp\DAO\Problems::getByPK(
            $problemData['problem']->problem_id
        );
        $this->assertTrue($problem->quality_seal);
    }

    /**
     * Test if the problem's quality_seal remains as false after receiving
     * the feedback of reviewers.
     */
    public function testReviewersFeedbackNegative() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode([
                'quality_seal' => false,
                'tag' => 'problemLevelAdvancedSpecializedTopics',
            ]),
        ]));

        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[1]
        );
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode([
                'quality_seal' => false,
                'tag' => 'problemLevelBasicIntroductionToProgramming',
            ]),
        ]));

        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[2]
        );
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode([
                'quality_seal' => true,
                'tag' => 'problemLevelBasicIntroductionToProgramming',
                'tags' => ['problemTagQueues', 'problemTagRecursion'],
            ]),
        ]));

        \OmegaUp\Test\Utils::runAggregateFeedback();

        $problem = \OmegaUp\DAO\Problems::getByPK(
            $problemData['problem']->problem_id
        );
        $this->assertFalse($problem->quality_seal);
    }

    public function setUpRankForUsers(
        $problems,
        $users,
        $withSuggestions = false
    ) {
        for ($i = 0; $i < 5; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problems[$j],
                    $users[$i]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($runData);
            }
        }

        if ($withSuggestions) {
            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[0],
                $problems[0]['request']['problem_alias'],
                2, /* difficulty */
                1, /* quality */
                ['problemTopicDynamicProgramming', 'problemTopicMath'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[1],
                $problems[0]['request']['problem_alias'],
                3, /* difficulty */
                3, /* quality */
                ['problemTopicMatrices', 'problemTopicMath'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[2],
                $problems[0]['request']['problem_alias'],
                4, /* difficulty */
                0, /* quality */
                ['problemTopicMath', 'problemTopicDynamicProgramming'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[3],
                $problems[0]['request']['problem_alias'],
                2, /* difficulty */
                4, /* quality */
                ['problemTopicDynamicProgramming', 'problemTopicMath', 'problemTopicGreedy'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[4],
                $problems[0]['request']['problem_alias'],
                3, /* difficulty */
                4, /* quality */
                ['problemTopicGreedy', 'problemTopicDynamicProgramming'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[1],
                $problems[1]['request']['problem_alias'],
                3, /* difficulty */
                null, /* quality */
                ['problemTopicMatrices', 'problemTopicMath'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[2],
                $problems[1]['request']['problem_alias'],
                null, /* difficulty */
                1, /* quality */
                ['problemTopicMath', 'problemTopicDynamicProgramming'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[3],
                $problems[1]['request']['problem_alias'],
                4, /* difficulty */
                null, /* quality */
                ['problemTopicDynamicProgramming', 'problemTopicMath', 'problemTopicGreedy'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[4],
                $problems[1]['request']['problem_alias'],
                4, /* difficulty */
                0, /* quality */
                ['problemTopicGreedy', 'problemTopicDynamicProgramming'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[2],
                $problems[2]['request']['problem_alias'],
                4, /* difficulty */
                4, /* quality */
                ['problemTopicSorting', 'problemTopicDynamicProgramming', 'problemTopicGreedy'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[3],
                $problems[2]['request']['problem_alias'],
                4, /* difficulty */
                1, /* quality */
                ['problemTopicGeometry', 'problemTopicDynamicProgramming', 'problemTopicSorting', 'problemTopicGreedy'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[4],
                $problems[2]['request']['problem_alias'],
                1, /* difficulty */
                1, /* quality */
                ['problemTopicSorting', 'problemTopicGreedy'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[3],
                $problems[3]['request']['problem_alias'],
                4, /* difficulty */
                3, /* quality */
                ['problemTopicDynamicProgramming', 'problemTopicMath', 'problemTopicGreedy'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[4],
                $problems[3]['request']['problem_alias'],
                3, /* difficulty */
                null, /* quality */
                ['problemTopicGreedy', 'problemTopicDynamicProgramming'],
                false
            );

            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $users[4],
                $problems[4]['request']['problem_alias'],
                3, /* difficulty */
                null, /* quality */
                ['problemTopicGreedy', 'problemTopicDynamicProgramming'],
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
        \OmegaUp\Test\Utils::runAggregateFeedback();

        $problemOfTheWeek = \OmegaUp\DAO\ProblemOfTheWeek::getByDifficulty(
            'easy'
        );
        $this->assertCount(1, $problemOfTheWeek);
        $this->assertSame(
            $syntheticProblems[1]['problem']->problem_id,
            $problemOfTheWeek[0]->problem_id
        );
        // TODO(heduenas): Make assertion for hard problem of the week when that gets implemented.
    }

    public function setUpSyntheticSuggestionsForProblemOfTheWeek() {
        // Setup synthetic data.
        $numberOfProblems = 4;
        $problemData = [];
        $difficultyRatings = [];
        $qualityRatings = [];
        for ($i = 0; $i < $numberOfProblems; $i++) {
            $problemData[$i] = \OmegaUp\Test\Factories\Problem::createProblem();
        }
        $identities = [];
        for ($i = 0; $i < 10; $i++) {
            ['identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
            for ($j = 0; $j < $numberOfProblems; $j++) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData[$j],
                    $identities[$i]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($runData);
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
                \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                    $identities[$userIdx],
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
        $dateOneWeekAgo = (new DateTime())
            ->setTimestamp(\OmegaUp\Time::get())
            ->sub(new DateInterval('P7D'))
            ->format('Y-m-d H:i:s');
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            'UPDATE `QualityNominations` SET `time` = ?',
            [$dateOneWeekAgo]
        );

        return $problemData;
    }

    public function testAutogeneratedTagsWithConflicts() {
        $problemData = [];
        $problemData[0] = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData[1] = \OmegaUp\Test\Factories\Problem::createProblem();
        self::setUpSyntheticSuggestions($problemData);

        // Manually add one tag.
        \OmegaUp\Test\Factories\Problem::addTag(
            $problemData[0],
            'problemTopicDynamicProgramming',
            1 /* public */
        );
        $tags = array_map(
            fn ($tag) => $tag['name'],
            \OmegaUp\DAO\ProblemsTags::getProblemTags(
                $problemData[0]['problem'],
                false /* public_only */,
                true /* includeVoted */
            )
        );
        $this->assertEqualsCanonicalizing(
            $tags,
            [
                'problemLevelBasicIntroductionToProgramming',
                'problemTopicDynamicProgramming',
                'problemRestrictedTagLanguage'
            ]
        );

        \OmegaUp\Test\Utils::runAggregateFeedback();

        $tags = array_map(
            fn ($tag) => $tag['name'],
            \OmegaUp\DAO\ProblemsTags::getProblemTags(
                $problemData[0]['problem'],
                false /* public_only */,
                true /* includeVoted */
            )
        );
        $this->assertEqualsCanonicalizing(
            $tags,
            [
                'problemLevelBasicIntroductionToProgramming',
                'problemTopicDynamicProgramming',
                'problemTopicGreedy',
                'problemTopicMath',
                'problemRestrictedTagLanguage'
            ]
        );
    }

    public function setUpSyntheticSuggestions($problemData) {
        // Setup synthetic data.
        $identities = [];
        for ($i = 0; $i < 10; $i++) {
            ['identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
            for ($j = 0; $j < 2; $j++) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData[$j],
                    $identities[$i]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($runData);
            }
        }

        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[0],
            $problemData[0]['request']['problem_alias'],
            null,
            1,
            ['problemTopicDynamicProgramming', 'problemTopicMath'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[1],
            $problemData[0]['request']['problem_alias'],
            3,
            3,
            ['problemTopicMath', 'problemTopicDynamicProgramming'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[2],
            $problemData[0]['request']['problem_alias'],
            4,
            0,
            ['problemTopicMatrices', 'problemTopicMath'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[3],
            $problemData[0]['request']['problem_alias'],
            null,
            null,
            ['problemTopicMath'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[4],
            $problemData[0]['request']['problem_alias'],
            3,
            4,
            ['problemTopicDynamicProgramming', 'problemTopicMath', 'problemTopicGreedy'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[5],
            $problemData[0]['request']['problem_alias'],
            3,
            null,
            [],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[6],
            $problemData[0]['request']['problem_alias'],
            null,
            1,
            [],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[7],
            $problemData[0]['request']['problem_alias'],
            4,
            null,
            ['problemTopicGreedy', 'problemTopicDynamicProgramming'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[8],
            $problemData[0]['request']['problem_alias'],
            4,
            0,
            ['problemTopicDynamicProgramming'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[9],
            $problemData[0]['request']['problem_alias'],
            4,
            4,
            ['problemTopicDynamicProgramming', 'problemTopicMath'],
            false
        );

        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[0],
            $problemData[1]['request']['problem_alias'],
            4,
            1,
            ['problemTopicSorting', 'problemTopicGeometry'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[1],
            $problemData[1]['request']['problem_alias'],
            1,
            1,
            ['problemTopicSorting', 'problemTopicGeometry'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[2],
            $problemData[1]['request']['problem_alias'],
            4,
            3,
            ['problemTopicMatrices', 'problemTopicSorting'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[3],
            $problemData[1]['request']['problem_alias'],
            3,
            null,
            ['problemTopicSorting'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[4],
            $problemData[1]['request']['problem_alias'],
            3,
            null,
            ['problemTopicSorting', 'problemTopicMath', 'problemTopicGeometry'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[5],
            $problemData[1]['request']['problem_alias'],
            3,
            null,
            [],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[6],
            $problemData[1]['request']['problem_alias'],
            null,
            1,
            [],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[7],
            $problemData[1]['request']['problem_alias'],
            3,
            null,
            ['problemTopicSorting', 'problemTopicDynamicProgramming'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[8],
            $problemData[1]['request']['problem_alias'],
            4,
            1,
            ['problemTopicDynamicProgramming'],
            false
        );
        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[9],
            $problemData[1]['request']['problem_alias'],
            4,
            3,
            ['problemTopicGeometry', 'problemTopicMath'],
            false
        );
    }

    public function testMostVotedTags() {
        $tags = [
            'problemTopicDynamicProgramming' => 15,
            'problemTopicGraphTheory' => 10,
            'problemTopicBinarySearch' => 5,
            'problemTopicMath' => 2,
            'problemTopicGreedy' => 1,
        ];

        $this->assertSame(
            \OmegaUp\DAO\QualityNominations::mostVotedTags($tags, 0.25),
            ['problemTopicDynamicProgramming', 'problemTopicGraphTheory', 'problemTopicBinarySearch']
        );

        $this->assertSame(
            \OmegaUp\DAO\QualityNominations::mostVotedTags($tags, 0.5),
            ['problemTopicDynamicProgramming', 'problemTopicGraphTheory']
        );

        $this->assertSame(
            \OmegaUp\DAO\QualityNominations::mostVotedTags($tags, 0.9),
            ['problemTopicDynamicProgramming']
        );

        $this->assertSame(
            \OmegaUp\DAO\QualityNominations::mostVotedTags($tags, 0.9),
            ['problemTopicDynamicProgramming']
        );

        $this->assertSame(
            \OmegaUp\DAO\QualityNominations::mostVotedTags($tags, 0.01),
            ['problemTopicDynamicProgramming', 'problemTopicGraphTheory', 'problemTopicBinarySearch', 'problemTopicMath', 'problemTopicGreedy']
        );

        $tagsWithLittleVotes = [
            'problemTopicDynamicProgramming' => 2,
            'problemTopicGraphTheory' => 1,
        ];

        $this->assertSame(
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

        $this->assertSame(
            \OmegaUp\DAO\QualityNominations::mostVotedTags(
                $tooManyTagsWithMaxVotes,
                0.25
            ),
            [],
            'There must be a maximum number of tags to be assigned.'
        );
    }

    /**
     * A PHPUnit data provider for all the tests that can accept a status.
     *
     * @return list<array{0: string, 1: int, 2: string, 3:array<string:boolean>, 4: boolean }>
     */
    public function qualityNominationsDemotionStatusApiUpdateCaseProvider(): array {
        return [
            [
                'warning',
                \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING,
                'qualityNominationProblemHasWarning',
                [
                    'private_banned' => false,
                    'public_banned' => false,
                    'private' => false,
                    'private_warning' => true,
                    'public_warning' => true,
                    'public' => false,
                    'promoted' => false
                ],
                true
            ],
            [
                'warning',
                \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING,
                'qualityNominationProblemHasWarning',
                [
                    'private_banned' => true,
                    'public_banned' => true,
                    'private' => true,
                    'private_warning' => true,
                    'public_warning' => true,
                    'public' => true,
                    'promoted' => true
                ],
                false
            ],
            [
                'banned',
                \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED,
                'qualityNominationProblemHasBeenBanned',
                [
                    'private_banned' => false,
                    'public_banned' => true,
                    'private' => false,
                    'private_warning' => false,
                    'public_warning' => false,
                    'public' => false,
                    'promoted' => false
                ],
                true,
            ],
            [
                'banned',
                \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED,
                'qualityNominationProblemHasBeenBanned',
                [
                    'private_banned' => true,
                    'public_banned' => true,
                    'private' => true,
                    'private_warning' => true,
                    'public_warning' => true,
                    'public' => true,
                    'promoted' => true
                ],
                false,
            ]
        ];
    }

    /**
     * Check that a non-reviewer user cannot change the visibility that is not PRIVATE_WARNING
     * or PUBLIC_WARNING of his problems with a demotion qualitynomination but only reviewer
     * can change any visibility.
     * @dataProvider qualityNominationsDemotionStatusApiUpdateCaseProvider
     */
    public function testUserCannotUpdateProblemWithDemotionResolved(
        string $status,
        int $intVisibility,
        string $errorMessage,
        array $invalidVisibilities,
        bool $loginAsAuthor
    ) {
        ['identity' => $author] = \OmegaUp\Test\Factories\User::createUser(new \OmegaUp\Test\Factories\UserParams(
            [
                'username' => 'user_test_author'
            ]
        ));
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams(
            [
                'author' => $author,
                'title' => 'problem_1'
            ]
        ));
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
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
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        \OmegaUp\Controllers\QualityNomination::apiResolve(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'status' => $status,
                'problem_alias' => $problemData['request']['problem_alias'],
                'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
                'rationale' => 'ew plus something else',
            ])
        );

        $details = \OmegaUp\Controllers\QualityNomination::apiDetails(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'qualitynomination_id' => $qualitynomination['qualitynomination_id'],
        ]));
        $this->assertSame(
            $status,
            $details['nomination_status'],
            "qualitynomination should have been marked as {$status}"
        );

        $problem = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
        $this->assertSame(
            $intVisibility,
            $problem['visibility'],
            "Problem should have been public {$status}"
        );

        $login = self::login($author);
        foreach ($invalidVisibilities as $visibility => $shouldPass) {
            try {
                \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                    'auth_token' => ($loginAsAuthor ? $login->auth_token : $reviewerLogin->auth_token),
                    'problem_alias' => $problemData['request']['problem_alias'],
                    'visibility' => $visibility,
                    'message' => "public {$status} -> {$visibility}",
                ]));
                if (!$shouldPass) {
                    $this->fail(
                        "Normal user shouldn't be able to update to problem {$status} to other visibility that is not {$status}"
                    );
                }
            } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
                if ($shouldPass) {
                    $this->fail(
                        "Normal user should be able to update to problem warning to other visibility {$status}"
                    );
                }
                $this->assertSame(
                    $e->getMessage(),
                    $errorMessage
                );
            }
        }
    }

    /**
     * Test that if a quality nomination from a reviewer does not include a
     * level tag, the cronjob does not fail and simply ignores that nomination.
     */
    public function testNominationSkippedIfMissingLevelTag() {
        // Create two problems
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $karelProblemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        // Reviewer nominates both problems for quality review, the first one
        // has a level tag, the second one does not.
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $karelProblemData['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode([
                'quality_seal' => true,
                'tag' => 'problemLevelBasicKarel',
                'tags' => ['problemTagBitManipulation', 'problemTagRecursion'],
            ]),
        ]));
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode([
                'quality_seal' => true,
                'tags' => ['problemTagBitManipulation', 'problemTagRecursion'],
            ]),
        ]));

        // Run the cronjob to process nominations
        \OmegaUp\Test\Utils::runAggregateFeedback();

        // Check that the first problem has the selected level tag and the
        // second one has the default tag level, and the application does not
        // crash
        [
            'results' => $problems,
            'total' => $total,
        ] = \OmegaUp\Controllers\Problem::apiList(
            new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
            ])
        );
        $this->assertSame($total, 2);
        $this->assertSame(
            $problems[0]['tags'][0]['name'],
            'problemLevelBasicKarel'
        );
        $this->assertSame(
            $problems[1]['tags'][0]['name'],
            'problemLevelBasicIntroductionToProgramming'
        );
    }
}
