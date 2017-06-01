<?php

class QualityNominationTest extends OmegaupTestCase {
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
                'statement' => 'a + b',
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
}
