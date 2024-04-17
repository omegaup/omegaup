<?php
/**
 * Testing delete problems feature
 */

class ProblemDeleteTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Tests problem with submissions in a contest or a course can't be deleted anymore
     */
    public function testProblemCanNotBeDeletedAfterSubmissionsInACourseOrContest() {
        // Get a user
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'author' => $identity,
            ])
        );

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Create a run
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        // Grade the run
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $login = self::login($problemData['author']);

        try {
            \OmegaUp\Controllers\Problem::apiDelete(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame(
                'problemHasBeenUsedInContestOrCourse',
                $e->getMessage()
            );
        }
    }

    /**
     * Tests anonymous user can't see deleted problems
     */
    public function testAnonymousUserCannotSeeDeletedProblems() {
        // Get a user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Get problems
        $deletedProblemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'author' => $identity,
            ])
        );

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'author' => $identity
            ])
        );

        $login = self::login($problemData['author']);

        // Call API to delete a problem
        \OmegaUp\Controllers\Problem::apiDelete(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $deletedProblemData['request']['problem_alias'],
        ]));

        // Get problems list
        $response = \OmegaUp\Controllers\Problem::apiList(
            new \OmegaUp\Request(
                []
            )
        );

        // Asserting deleted problem is not in the list
        foreach ($response['results'] as $problem) {
            $this->assertNotEquals(
                $deletedProblemData['request']['problem_alias'],
                $problem['alias']
            );
        }

        // Asserting not deleted problem is in the list
        $problemIsInTheList = false;
        foreach ($response['results'] as $problem) {
            if ($problemData['request']['problem_alias'] == $problem['alias']) {
                $problemIsInTheList = true;
                break;
            }
        }
        $this->assertTrue($problemIsInTheList);
    }

    /**
     * Tests logged user can't see deleted problems
     */
    public function testLoggedUserCannotSeeDeletedProblems() {
        // Get a user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Get problems
        $deletedProblemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'author' => $identity
            ])
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'author' => $identity
            ])
        );

        $login = self::login($problemData['author']);

        // Call API to delete a problem
        \OmegaUp\Controllers\Problem::apiDelete(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $deletedProblemData['request']['problem_alias'],
        ]));

        // Get problems list
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token
        ]));

        // Asserting deleted problem is not in the list
        foreach ($response['results'] as $problem) {
            $this->assertNotEquals(
                $deletedProblemData['request']['problem_alias'],
                $problem['alias']
            );
        }

        // Asserting not deleted problem is in the list
        $problemIsInTheList = false;
        foreach ($response['results'] as $problem) {
            if ($problemData['request']['problem_alias'] == $problem['alias']) {
                $problemIsInTheList = true;
                break;
            }
        }
        $this->assertTrue($problemIsInTheList);

        // Get My admin problems list
        $response = \OmegaUp\Controllers\Problem::apiAdminList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token
        ]));

        // Asserting deleted problem is not in the list
        foreach ($response['problems'] as $problem) {
            $this->assertNotEquals(
                $deletedProblemData['request']['problem_alias'],
                $problem['alias']
            );
        }

        // Asserting not deleted problem is in the list
        $problemIsInTheList = false;
        foreach ($response['problems'] as $problem) {
            if ($problemData['request']['problem_alias'] == $problem['alias']) {
                $problemIsInTheList = true;
                break;
            }
        }
        $this->assertTrue($problemIsInTheList);
    }

    /**
     * Tests Sysadmin can see deleted problems only in problems admin list
     */
    public function testSysadminCanSeeDeletedProblemsOnlyInAdminList() {
        // Get a user
        ['user' => $userLogin] = \OmegaUp\Test\Factories\User::createUser();

        // Get problems
        $deletedProblemData = \OmegaUp\Test\Factories\Problem::createProblem(
            null,
            null,
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
            $userLogin
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            null,
            null,
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
            $userLogin
        );

        // Get admin user
        ['identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();

        $login = self::login($identityAdmin);

        // Call API to delete a problem
        \OmegaUp\Controllers\Problem::apiDelete(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $deletedProblemData['request']['problem_alias'],
        ]));

        // Get problems list
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token
        ]));

        // Asserting deleted problem is not in the list
        foreach ($response['results'] as $problem) {
            $this->assertNotEquals(
                $deletedProblemData['request']['problem_alias'],
                $problem['alias']
            );
        }

        // Asserting not deleted problem is in the list
        $problemIsInTheList = false;
        foreach ($response['results'] as $problem) {
            if ($problemData['request']['problem_alias'] == $problem['alias']) {
                $problemIsInTheList = true;
                break;
            }
        }
        $this->assertTrue($problemIsInTheList);

        // Get My admin problems list
        $response = \OmegaUp\Controllers\Problem::apiAdminList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token
        ]));

        // Asserting deleted problem is in the list
        $deletedProblemIsInTheList = false;
        foreach ($response['problems'] as $problem) {
            if ($deletedProblemData['request']['problem_alias'] == $problem['alias']) {
                $deletedProblemIsInTheList = true;
                break;
            }
        }
        $this->assertTrue($deletedProblemIsInTheList);

        // Asserting not deleted problem is in the list
        $problemIsInTheList = false;
        foreach ($response['problems'] as $problem) {
            if ($problemData['request']['problem_alias'] == $problem['alias']) {
                $problemIsInTheList = true;
                break;
            }
        }
        $this->assertTrue($problemIsInTheList);
    }
}
