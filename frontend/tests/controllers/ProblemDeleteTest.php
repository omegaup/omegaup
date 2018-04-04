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
     * @expectedException ForbiddenAccessException
     */
    public function testProblemCanNotBeDeletedAfterSubmissionsInACourseOrContest() {
        // Get a user
        $userLogin = UserFactory::createUser();

        // Get a problem
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => ProblemController::VISIBILITY_PUBLIC,
            'author' => $userLogin
        ]));

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        // Grade the run
        RunsFactory::gradeRun($runData);

        $login = self::login($problemData['author']);

        // Call API
        $response = ProblemController::apiDelete(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));
    }

    /**
     * Tests anonymous user can't see deleted problems
     */
    public function testAnonymousUserCannotSeeDeletedProblems() {
        // Get a user
        $userLogin = UserFactory::createUser();

        // Get problems
        $deletedProblemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => ProblemController::VISIBILITY_PUBLIC,
            'author' => $userLogin
        ]));

        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => ProblemController::VISIBILITY_PUBLIC,
            'author' => $userLogin
        ]));

        $login = self::login($problemData['author']);

        // Call API to delete a problem
        ProblemController::apiDelete(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $deletedProblemData['request']['problem_alias'],
        ]));

        // Get problems list
        $response = ProblemController::apiList(new Request([]));

        // Asserting deleted problem is not in the list
        foreach ($response['results'] as $key => $problem) {
            $this->assertNotEquals($deletedProblemData['request']['problem_alias'], $problem['alias']);
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
        $userLogin = UserFactory::createUser();

        // Get problems
        $deletedProblemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => ProblemController::VISIBILITY_PUBLIC,
            'author' => $userLogin
        ]));
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => ProblemController::VISIBILITY_PUBLIC,
            'author' => $userLogin
        ]));

        $login = self::login($problemData['author']);

        // Call API to delete a problem
        ProblemController::apiDelete(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $deletedProblemData['request']['problem_alias'],
        ]));

        // Get problems list
        $response = ProblemController::apiList(new Request([
            'auth_token' => $login->auth_token
        ]));

        // Asserting deleted problem is not in the list
        foreach ($response['results'] as $key => $problem) {
            $this->assertNotEquals($deletedProblemData['request']['problem_alias'], $problem['alias']);
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
        $response = ProblemController::apiAdminList(new Request([
            'auth_token' => $login->auth_token
        ]));

        // Asserting deleted problem is not in the list
        foreach ($response['problems'] as $key => $problem) {
            $this->assertNotEquals($deletedProblemData['request']['problem_alias'], $problem['alias']);
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
        $userLogin = UserFactory::createUser();

        // Get problems
        $deletedProblemData = ProblemsFactory::createProblem(
            null,
            null,
            ProblemController::VISIBILITY_PUBLIC,
            $userLogin
        );
        $problemData = ProblemsFactory::createProblem(
            null,
            null,
            ProblemController::VISIBILITY_PUBLIC,
            $userLogin
        );

        // Get admin user
        $adminLogin = UserFactory::createAdminUser();

        $login = self::login($adminLogin);

        // Call API to delete a problem
        ProblemController::apiDelete(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $deletedProblemData['request']['problem_alias'],
        ]));

        // Get problems list
        $response = ProblemController::apiList(new Request([
            'auth_token' => $login->auth_token
        ]));

        // Asserting deleted problem is not in the list
        foreach ($response['results'] as $key => $problem) {
            $this->assertNotEquals($deletedProblemData['request']['problem_alias'], $problem['alias']);
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
        $response = ProblemController::apiAdminList(new Request([
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
