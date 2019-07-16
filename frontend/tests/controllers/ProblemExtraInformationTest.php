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
            'zipName' => OMEGAUP_RESOURCES_ROOT . 'triangulos.zip'
        ]));
        // Annonymus user is able to see the problem
        $r = new Request([
            'problem_alias' => $problemData['request']['problem_alias'],
        ]);
        $result = ProblemController::getProblemDetailsForSmarty($r);

        $this->assertFalse($result['user']['logged_in']);
        $this->assertFalse($result['karel_problem']);
        $this->assertFalse($result['problem_admin']);

        // Normal user is able to see the problem.
        $user = UserFactory::createUser();
        $login = self::login($user);
        $r['auth_token'] = $login->auth_token;
        $result = ProblemController::getProblemDetailsForSmarty($r);

        $this->assertTrue($result['user']['logged_in']);
        $this->assertFalse($result['karel_problem']);
        $this->assertFalse($result['problem_admin']);
    }
}
