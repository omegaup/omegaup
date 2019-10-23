<?php

class AssignmentProblemsTest extends OmegaupTestCase {
    public function testAddProblemToAssignment() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'author' => $user
        ]), $login);
        $response = CoursesFactory::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        )[0];

        $this->assertEquals('ok', $response['status']);

        // Assert that the problem was correctly added
        $getAssignmentResponse = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course' => $courseAlias,
            'assignment' => $assignmentAlias,
        ]));
        $this->assertEquals(1, sizeof($getAssignmentResponse['problems']));
        $this->assertEquals(
            $problem['problem']->alias,
            $getAssignmentResponse['problems'][0]['alias']
        );
        $this->assertEquals(
            $problem['problem']->commit,
            $getAssignmentResponse['problems'][0]['commit']
        );
        $this->assertEquals(
            $problem['problem']->current_version,
            $getAssignmentResponse['problems'][0]['version']
        );
    }

    public function testDeleteProblemFromAssignment() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'author' => $user,
        ]), $login);
        CoursesFactory::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        );

        // Remove a problem from the assignment
        $removeProblemResponse = \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problem['problem']->alias,
        ]));
        $this->assertEquals('ok', $removeProblemResponse['status']);

        // Assert that the problem was correctly removed
        $getAssignmentResponse = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course' => $courseAlias,
            'assignment' => $assignmentAlias,
        ]));
        $this->assertEquals(0, sizeof($getAssignmentResponse['problems']));
    }

    public function testAddRemoveProblems() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add multiple problems to the assignment
        $problems = [
            ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => 1,
                'author' => $user
            ]), $login),
            ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => 1,
                'author' => $user
            ]), $login),
            ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => 1,
                'author' => $user
            ]), $login)
        ];
        $responses = CoursesFactory::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            $problems
        );
        $this->assertEquals('ok', $responses[0]['status']);
        $this->assertEquals('ok', $responses[1]['status']);
        $this->assertEquals('ok', $responses[2]['status']);

        // Assert that the problems were correctly added
        $getAssignmentResponse = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course' => $courseAlias,
            'assignment' => $assignmentAlias,
        ]));
        $this->assertEquals(3, sizeof($getAssignmentResponse['problems']));

        // Remove multiple problems from the assignment
        $removeProblemResponse = \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problems[0]['problem']->alias,
        ]));
        $this->assertEquals('ok', $removeProblemResponse['status']);
        $removeProblemResponse = \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problems[2]['problem']->alias,
        ]));
        $this->assertEquals('ok', $removeProblemResponse['status']);

        // Assert that the problems were correctly removed
        $getAssignmentResponse = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course' => $courseAlias,
            'assignment' => $assignmentAlias,
        ]));
        $this->assertEquals(1, sizeof($getAssignmentResponse['problems']));
        $this->assertEquals(
            $problems[1]['problem']->alias,
            $getAssignmentResponse['problems'][0]['alias']
        );
    }

    /**
     * Attempts to add a problem with a normal user.
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testAddProblemForbiddenAccess() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);
        $problem = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'author' => $user
        ]), $login);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment with a normal user
        ['user' => $forbiddenUser, 'identity' => $forbiddenIdentity] = UserFactory::createUser();
        $forbiddenUserLogin = self::login($forbiddenIdentity);
        CoursesFactory::addProblemsToAssignment(
            $forbiddenUserLogin,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        );
    }

    /**
     * Attempts to add a problem with a student.
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testAddProblemForbiddenAccessStudent() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);
        $problem = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'author' => $user
        ]), $login);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment with a student
        $forbiddenUser = CoursesFactory::addStudentToCourse($courseData);
        $forbiddenUserLogin = self::login($forbiddenUser);
        CoursesFactory::addProblemsToAssignment(
            $forbiddenUserLogin,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        );
    }

    /**
     * Attempts to remove a problem with a normal user.
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testDeleteProblemForbiddenAccess() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'author' => $user
        ]), $login);
        CoursesFactory::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        );

        // Remove a problem from the assignment with a normal user
        ['user' => $forbiddenUser, 'identity' => $forbiddenIdentity] = UserFactory::createUser();
        $forbiddenUserLogin = self::login($forbiddenIdentity);
        $removeProblemResponse = \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
            'auth_token' => $forbiddenUserLogin->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problem['problem']->alias,
        ]));
    }

    /**
     * Attempts to remove a problem with a student.
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testDeleteProblemForbiddenAccessStudent() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'author' => $user
        ]), $login);
        CoursesFactory::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        );

        // Remove a problem from the assignment with a student
        $forbiddenUser = CoursesFactory::addStudentToCourse($courseData);
        $forbiddenUserLogin = self::login($forbiddenUser);
        $removeProblemResponse = \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
            'auth_token' => $forbiddenUserLogin->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problem['problem']->alias,
        ]));
    }

    /**
     * Attempts to remove an invalid problem.
     *
     * @expectedException \OmegaUp\Exceptions\NotFoundException
     */
    public function testDeleteNonExistingProblem() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Remove an invalid problem from the assignment
        $removeProblemResponse = \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => 'noexiste',
        ]));
    }
}
