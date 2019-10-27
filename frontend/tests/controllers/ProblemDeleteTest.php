<?php

/**
 * Testing delete problems feature
 *
 * @author juan.pablo@omegaup.com
 */

class ProblemDeleteTest extends OmegaupTestCase {
    /**
     * Tests problem with submissions in a contest or a course can't be deleted anymore
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testProblemCanNotBeDeletedAfterSubmissionsInACourseOrContest() {
        // Get a user
        ['user' => $userLogin, 'identity' => $identity] = UserFactory::createUser();

        // Get a problem
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC,
            'author' => $identity
        ]));

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun(
            $problemData,
            $contestData,
            $identity
        );

        // Grade the run
        RunsFactory::gradeRun($runData);

        $login = self::login($problemData['author']);

        // Call API
        $response = \OmegaUp\Controllers\Problem::apiDelete(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
    }

    /**
     * Tests anonymous user can't see deleted problems
     */
    public function testAnonymousUserCannotSeeDeletedProblems() {
        // Get a user
        ['user' => $userLogin, 'identity' => $identity] = UserFactory::createUser();

        // Get problems
        $deletedProblemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC,
            'author' => $identity
        ]));

        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC,
            'author' => $identity
        ]));

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
        foreach ($response['results'] as $key => $problem) {
            $this->assertNotEquals(
                $deletedProblemData['request']['problem_alias'],
                $problem['alias']
            );
        }

        // Asserting not deleted problem is in the list
        $problemIsInTheList = false;
        foreach ($response['results'] as $key => $problem) {
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
        ['user' => $userLogin, 'identity' => $identity] = UserFactory::createUser();

        // Get problems
        $deletedProblemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC,
            'author' => $identity
        ]));
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC,
            'author' => $identity
        ]));

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
        foreach ($response['results'] as $key => $problem) {
            $this->assertNotEquals(
                $deletedProblemData['request']['problem_alias'],
                $problem['alias']
            );
        }

        // Asserting not deleted problem is in the list
        $problemIsInTheList = false;
        foreach ($response['results'] as $key => $problem) {
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
        foreach ($response['problems'] as $key => $problem) {
            $this->assertNotEquals(
                $deletedProblemData['request']['problem_alias'],
                $problem['alias']
            );
        }

        // Asserting not deleted problem is in the list
        $problemIsInTheList = false;
        foreach ($response['problems'] as $key => $problem) {
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
        ['user' => $userLogin, 'identity' => $identity] = UserFactory::createUser();

        // Get problems
        $deletedProblemData = ProblemsFactory::createProblem(
            null,
            null,
            \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC,
            $userLogin
        );
        $problemData = ProblemsFactory::createProblem(
            null,
            null,
            \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC,
            $userLogin
        );

        // Get admin user
        ['user' => $admin, 'identity' => $identityAdmin] = UserFactory::createAdminUser();

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
        foreach ($response['results'] as $key => $problem) {
            $this->assertNotEquals(
                $deletedProblemData['request']['problem_alias'],
                $problem['alias']
            );
        }

        // Asserting not deleted problem is in the list
        $problemIsInTheList = false;
        foreach ($response['results'] as $key => $problem) {
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
        foreach ($response['problems'] as $key => $problem) {
            if ($deletedProblemData['request']['problem_alias'] == $problem['alias']) {
                $deletedProblemIsInTheList = true;
                break;
            }
        }
        $this->assertTrue($deletedProblemIsInTheList);

        // Asserting not deleted problem is in the list
        $problemIsInTheList = false;
        foreach ($response['problems'] as $key => $problem) {
            if ($problemData['request']['problem_alias'] == $problem['alias']) {
                $problemIsInTheList = true;
                break;
            }
        }
        $this->assertTrue($problemIsInTheList);
    }
}
