<?php

class AssignmentProblemsTest extends OmegaupTestCase {
    public function testAddProblemToAssignment() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = ProblemsFactory::createProblem(null, null, 1, $user, null, $login);
        $response = CoursesFactory::addProblemsToAssignment($login, $courseAlias, $assignmentAlias, [$problem])[0];

        $this->assertEquals('ok', $response['status']);

        // Assert that the problem was correctly added
        $getAssignmentResponse = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $login->auth_token,
            'course' => $courseAlias,
            'assignment' => $assignmentAlias,
        ]));
        $this->assertEquals(1, sizeof($getAssignmentResponse['problems']));
        $this->assertEquals($problem['problem']->alias, $getAssignmentResponse['problems'][0]['alias']);
    }

    public function testDeleteProblemFromAssignment() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = ProblemsFactory::createProblem(null, null, 1, $user, null, $login);
        CoursesFactory::addProblemsToAssignment($login, $courseAlias, $assignmentAlias, [$problem]);

        // Remove a problem from the assignment
        $removeProblemResponse = CourseController::apiRemoveProblem(new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problem['problem']->alias,
        ]));
        $this->assertEquals('ok', $removeProblemResponse['status']);

        // Assert that the problem was correctly removed
        $getAssignmentResponse = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $login->auth_token,
            'course' => $courseAlias,
            'assignment' => $assignmentAlias,
        ]));
        $this->assertEquals(0, sizeof($getAssignmentResponse['problems']));
    }

    public function testAddRemoveProblems() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add multiple problems to the assignment
        $problems = [
            ProblemsFactory::createProblem(null, null, 1, $user, null, $login),
            ProblemsFactory::createProblem(null, null, 1, $user, null, $login),
            ProblemsFactory::createProblem(null, null, 1, $user, null, $login)];
        $responses = CoursesFactory::addProblemsToAssignment($login, $courseAlias, $assignmentAlias, $problems);
        $this->assertEquals('ok', $responses[0]['status']);
        $this->assertEquals('ok', $responses[1]['status']);
        $this->assertEquals('ok', $responses[2]['status']);

        // Assert that the problems were correctly added
        $getAssignmentResponse = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $login->auth_token,
            'course' => $courseAlias,
            'assignment' => $assignmentAlias,
        ]));
        $this->assertEquals(3, sizeof($getAssignmentResponse['problems']));

        // Remove multiple problems from the assignment
        $removeProblemResponse = CourseController::apiRemoveProblem(new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problems[0]['problem']->alias,
        ]));
        $this->assertEquals('ok', $removeProblemResponse['status']);
        $removeProblemResponse = CourseController::apiRemoveProblem(new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problems[2]['problem']->alias,
        ]));
        $this->assertEquals('ok', $removeProblemResponse['status']);

        // Assert that the problems were correctly removed
        $getAssignmentResponse = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $login->auth_token,
            'course' => $courseAlias,
            'assignment' => $assignmentAlias,
        ]));
        $this->assertEquals(1, sizeof($getAssignmentResponse['problems']));
        $this->assertEquals($problems[1]['problem']->alias, $getAssignmentResponse['problems'][0]['alias']);
    }

    /**
     * Attempts to add a problem with a normal user.
     *
     * @expectedException ForbiddenAccessException
     */
    public function testAddProblemForbiddenAccess() {
        $user = UserFactory::createUser();
        $login = self::login($user);
        $problem = ProblemsFactory::createProblem(null, null, 1, $user, null, $login);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment with a normal user
        $forbiddenUser = UserFactory::createUser();
        $forbiddenUserLogin = self::login($forbiddenUser);
        CoursesFactory::addProblemsToAssignment($login, $courseAlias, $assignmentAlias, [$problem]);
    }

    /**
     * Attempts to add a problem with a student.
     *
     * @expectedException ForbiddenAccessException
     */
    public function testAddProblemForbiddenAccessStudent() {
        $user = UserFactory::createUser();
        $login = self::login($user);
        $problem = ProblemsFactory::createProblem(null, null, 1, $user, null, $login);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment with a student
        $forbiddenUser = CoursesFactory::addStudentToCourse($courseData);
        $forbiddenUserLogin = self::login($forbiddenUser);
        CoursesFactory::addProblemsToAssignment($login, $courseAlias, $assignmentAlias, [$problem]);
    }

    /**
     * Attempts to remove a problem with a normal user.
     *
     * @expectedException ForbiddenAccessException
     */
    public function testDeleteProblemForbiddenAccess() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = ProblemsFactory::createProblem(null, null, 1, $user, null, $login);
        CoursesFactory::addProblemsToAssignment($login, $courseAlias, $assignmentAlias, [$problem]);

        // Remove a problem from the assignment with a normal user
        $forbiddenUser = UserFactory::createUser();
        $forbiddenUserLogin = self::login($forbiddenUser);
        $removeProblemResponse = CourseController::apiRemoveProblem(new Request([
            'auth_token' => $forbiddenUserLogin->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problem['problem']->alias,
        ]));
    }

    /**
     * Attempts to remove a problem with a student.
     *
     * @expectedException ForbiddenAccessException
     */
    public function testDeleteProblemForbiddenAccessStudent() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = ProblemsFactory::createProblem(null, null, 1, $user, null, $login);
        CoursesFactory::addProblemsToAssignment($login, $courseAlias, $assignmentAlias, [$problem]);

        // Remove a problem from the assignment with a student
        $forbiddenUser = CoursesFactory::addStudentToCourse($courseData);
        $forbiddenUserLogin = self::login($forbiddenUser);
        $removeProblemResponse = CourseController::apiRemoveProblem(new Request([
            'auth_token' => $forbiddenUserLogin->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problem['problem']->alias,
        ]));
    }

    /**
     * Attempts to remove an invalid problem.
     *
     * @expectedException NotFoundException
     */
    public function testDeleteNonExistingProblem() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Remove an invalid problem from the assignment
        $removeProblemResponse = CourseController::apiRemoveProblem(new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => 'noexiste',
        ]));
    }
}
