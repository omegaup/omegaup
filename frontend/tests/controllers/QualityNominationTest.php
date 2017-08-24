<?php

class QualityNominationTest extends OmegaupTestCase {
    private static $reviewers = [];

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        $qualityReviewerGroup = GroupsDAO::FindByAlias(
            Authorization::QUALITY_REVIEWER_GROUP_ALIAS
        );
        for ($i = 0; $i < 5; $i++) {
            $reviewer = UserFactory::createUser();
            GroupsUsersDAO::save(new GroupsUsers([
                'group_id' => $qualityReviewerGroup->group_id,
                'user_id' => $reviewer->user_id,
                'role_id' => Authorization::ADMIN_ROLE,
            ]));
            self::$reviewers[] = $reviewer;
        }
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
                'rationale' => 'cool!',
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
                'rationale' => 'cool!',
                // No difficulty!
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
        $login = self::login(self::$reviewers[0]);
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
            self::$reviewers,
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
     * Check that before discard a problem, the user must
     * have solved it first.
     */
    public function testMustSolveBeforeDismissed() {
        // Create 2 problems and 10 problems. Both users solved all 10 problems.
        $problems = [];
        $users = [];
        for ($i = 0; $i < 2; $i ++) {
            $problems[] = ProblemsFactory::createProblem();
        }
        for ($i = 0; $i < 10; $i ++) {
            $users[] = UserFactory::createUser();
            foreach ($problems as $problem) {
                $runData = RunsFactory::createRunToProblem($problem, $users[$i]);
                RunsFactory::gradeRun($runData);
            }
        }

        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'nomination' => 'dismissal',
            'contents' => json_encode([
                'rationale' => 'dismiss', ]),
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
            'contents' => json_encode([
                'rationale' => 'dismiss' ]), // re-encoding it for normalization.
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
}
