<?php

/**
 * Description of Problem With Extra Information
 *
 * @author juan.pablo
 */

class ProblemExtraInformationTest extends OmegaupTestCase {
    /**
     * Test reviewers can do some problem-related tasks.
     */
    public function testProblemUpdateByReviewer() {
        // Create a private problem.
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip'
        ]));
        // Annonymus user is able to see the problem
        $r = new \OmegaUp\Request([
            'problem_alias' => $problemData['request']['problem_alias'],
        ]);
        $result = ProblemController::getProblemDetailsForSmarty($r);

        $this->assertFalse($result['payload']['user']['logged_in']);
        $this->assertFalse($result['karel_problem']);
        $this->assertFalse($result['problem_admin']);

        // Normal user is able to see the problem.
        $user = UserFactory::createUser();
        $login = self::login($user);
        $r['auth_token'] = $login->auth_token;
        $result = ProblemController::getProblemDetailsForSmarty($r);

        $this->assertTrue($result['payload']['user']['logged_in']);
        $this->assertFalse($result['karel_problem']);
        $this->assertFalse($result['problem_admin']);
    }

    /**
     * Test getProblemSolutionStatus
     */
    public function testProblemSolutionStatus() {
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip'
        ]));

        // Problem author should get the problem as unlocked
        $login = self::login($problemData['author']);
        $result = ProblemController::getProblemDetailsForSmarty(new \OmegaUp\Request([
            'problem_alias' => $problemData['request']['problem_alias'],
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals(ProblemController::SOLUTION_UNLOCKED, $result['payload']['solution_status']);

        // Normal user should see the problem as locked
        $user = UserFactory::createUser();
        $login = self::login($user);
        $result = ProblemController::getProblemDetailsForSmarty(new \OmegaUp\Request([
            'problem_alias' => $problemData['request']['problem_alias'],
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals(ProblemController::SOLUTION_LOCKED, $result['payload']['solution_status']);

        // Problem with no solutions should return NOT_FOUND
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'imagetest.zip'
        ]));
        $result = ProblemController::getProblemDetailsForSmarty(new \OmegaUp\Request([
            'problem_alias' => $problemData['request']['problem_alias'],
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals(ProblemController::SOLUTION_NOT_FOUND, $result['payload']['solution_status']);
    }
}
