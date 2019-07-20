<?php

/**
 * Tests for ProblemForfeitedController
 *
 * @author carlosabcs
 */

class ProblemsForfeitedTest extends OmegaupTestCase {
    public function testGetCounts() {
        $user = UserFactory::createUser();
        $problemOne = ProblemsFactory::createProblem();
        $problemTwo = ProblemsFactory::createProblem();

        $login = self::login($user);
        $run = RunsFactory::createRunToProblem($problemOne, $user, $login);
        RunsFactory::gradeRun($run);

        ProblemsForfeitedDAO::create(new ProblemsForfeited([
            'user_id' => $user->user_id,
            'problem_id' => $problemTwo['problem']->problem_id,
        ]));

        $run = RunsFactory::createRunToProblem($problemOne, $user, $login);
        RunsFactory::gradeRun($run);

        $results = ProblemForfeitedController::apiGetCounts(new Request([
            'auth_token' => $login->auth_token,
        ]));

        $this->assertEquals(2, $results['solved']);
        $this->assertEquals(1, $results['forfeited']);
    }
}
