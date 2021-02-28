<?php

/**
 * Description of Problem With Extra Information
 *
 * @author juan.pablo
 */

class ProblemExtraInformationTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test reviewers can do some problem-related tasks.
     */
    public function testProblemUpdateByReviewer() {
        // Create a private problem.
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip',
            ])
        );
        // Annonymus user is able to see the problem
        $r = new \OmegaUp\Request([
            'problem_alias' => $problemData['request']['problem_alias'],
        ]);
        $result = \OmegaUp\Controllers\Problem::getProblemDetailsForTypeScript(
            $r
        )['smartyProperties'];

        $this->assertFalse($result['payload']['user']['loggedIn']);
        $this->assertFalse($result['payload']['problem']['karel_problem']);
        $this->assertFalse($result['payload']['user']['admin']);

        // Normal user is able to see the problem.
        [
            'user' => $user,
            'identity' => $identity
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $r['auth_token'] = $login->auth_token;
        $result = \OmegaUp\Controllers\Problem::getProblemDetailsForTypeScript(
            $r
        )['smartyProperties'];

        $this->assertTrue($result['payload']['user']['loggedIn']);
        $this->assertFalse($result['payload']['problem']['karel_problem']);
        $this->assertFalse($result['payload']['user']['admin']);
    }

    public function testQualityPayload() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        [
            'user' => $user,
            'identity' => $identity
        ] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        $result = \OmegaUp\Controllers\Problem::getProblemDetailsForTypeScript(
            new \OmegaUp\Request([
                'problem_alias' => $problemData['request']['problem_alias'],
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties'];
        $payload = $result['payload']['nominationStatus'];
        $this->assertFalse($payload['nominated']);
        $this->assertFalse($payload['nominatedBeforeAc']);
        $this->assertFalse($payload['dismissed']);
        $this->assertFalse($payload['dismissedBeforeAc']);
        $this->assertFalse($payload['tried']);
        $this->assertFalse($payload['solved']);

        // Now try to solved the problem, tried must be true
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'WA', 60);
        $login = self::login($identity);
        $result = \OmegaUp\Controllers\Problem::getProblemDetailsForTypeScript(
            new \OmegaUp\Request([
                'problem_alias' => $problemData['request']['problem_alias'],
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties'];
        $payload = $result['payload']['nominationStatus'];
        $this->assertFalse($payload['nominated']);
        $this->assertFalse($payload['nominatedBeforeAc']);
        $this->assertFalse($payload['dismissed']);
        $this->assertFalse($payload['dismissedBeforeAc']);
        $this->assertTrue($payload['tried']);
        $this->assertFalse($payload['solved']);

        // Now send dismissal before solving the problem
        \OmegaUp\Controllers\QualityNomination::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'nomination' => 'dismissal',
                'contents' => json_encode(['before_ac' => true]),
            ])
        );
        $result = \OmegaUp\Controllers\Problem::getProblemDetailsForTypeScript(
            new \OmegaUp\Request([
                'problem_alias' => $problemData['request']['problem_alias'],
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties'];
        $payload = $result['payload']['nominationStatus'];
        $this->assertFalse($payload['nominated']);
        $this->assertFalse($payload['nominatedBeforeAc']);
        $this->assertFalse($payload['dismissed']);
        $this->assertTrue($payload['dismissedBeforeAc']);
        $this->assertTrue($payload['tried']);
        $this->assertFalse($payload['solved']);

        // Solve the problem and send dismissal, before AC information
        // is not necessary anymore.
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        $login = self::login($identity);
        \OmegaUp\Controllers\QualityNomination::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'nomination' => 'dismissal',
                'contents' => json_encode([]),
            ])
        );
        $result = \OmegaUp\Controllers\Problem::getProblemDetailsForTypeScript(
            new \OmegaUp\Request([
                'problem_alias' => $problemData['request']['problem_alias'],
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties'];
        $payload = $result['payload']['nominationStatus'];
        $this->assertFalse($payload['nominated']);
        $this->assertTrue($payload['dismissed']);
        $this->assertTrue($payload['tried']);
        $this->assertTrue($payload['solved']);

        // Send nomination
        \OmegaUp\Controllers\QualityNomination::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'nomination' => 'suggestion',
                'contents' => json_encode([
                    'quality' => 3,
                ]),
            ])
        );
        $result = \OmegaUp\Controllers\Problem::getProblemDetailsForTypeScript(
            new \OmegaUp\Request([
                'problem_alias' => $problemData['request']['problem_alias'],
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties'];
        $payload = $result['payload']['nominationStatus'];
        $this->assertTrue($payload['nominated']);
        $this->assertTrue($payload['dismissed']);
        $this->assertTrue($payload['tried']);
        $this->assertTrue($payload['solved']);
    }

    /**
     * Test getProblemSolutionStatus
     */
    public function testProblemSolutionStatus() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'triangulos.zip',
            ])
        );

        // Problem author should get the problem as unlocked
        $login = self::login($problemData['author']);
        $result = \OmegaUp\Controllers\Problem::getProblemDetailsForTypeScript(
            new \OmegaUp\Request([
                'problem_alias' => $problemData['request']['problem_alias'],
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties'];
        $this->assertEquals(
            \OmegaUp\Controllers\Problem::SOLUTION_UNLOCKED,
            $result['payload']['solutionStatus']
        );

        // Normal user should see the problem as locked
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $result = \OmegaUp\Controllers\Problem::getProblemDetailsForTypeScript(
            new \OmegaUp\Request([
                'problem_alias' => $problemData['request']['problem_alias'],
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties'];
        $this->assertEquals(
            \OmegaUp\Controllers\Problem::SOLUTION_LOCKED,
            $result['payload']['solutionStatus']
        );

        // Problem with no solutions should return NOT_FOUND
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'zipName' => OMEGAUP_TEST_RESOURCES_ROOT . 'imagetest.zip',
            ])
        );
        $result = \OmegaUp\Controllers\Problem::getProblemDetailsForTypeScript(
            new \OmegaUp\Request([
                'problem_alias' => $problemData['request']['problem_alias'],
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties'];
        $this->assertEquals(
            \OmegaUp\Controllers\Problem::SOLUTION_NOT_FOUND,
            $result['payload']['solutionStatus']
        );
    }
}
