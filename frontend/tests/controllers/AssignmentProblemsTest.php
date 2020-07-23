<?php

class AssignmentProblemsTest extends \OmegaUp\Test\ControllerTestCase {
    public function testAddProblemToAssignment() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'author' => $identity
        ]), $login);
        $response = \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
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
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'author' => $identity,
        ]), $login);
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
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
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add multiple problems to the assignment
        $problems = [
            \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'author' => $identity
            ]), $login),
            \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'author' => $identity
            ]), $login),
            \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'author' => $identity
            ]), $login)
        ];
        $responses = \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
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
     */
    public function testAddProblemForbiddenAccess() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'author' => $identity
        ]), $login);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment with a normal user
        ['user' => $forbiddenUser, 'identity' => $forbiddenIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $forbiddenUserLogin = self::login($forbiddenIdentity);
        try {
            \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
                $forbiddenUserLogin,
                $courseAlias,
                $assignmentAlias,
                [$problem]
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Attempts to add a problem with a student.
     */
    public function testAddProblemForbiddenAccessStudent() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'author' => $identity
        ]), $login);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment with a student
        $forbiddenUser = \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData
        );
        $forbiddenUserLogin = self::login($forbiddenUser);
        try {
            \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
                $forbiddenUserLogin,
                $courseAlias,
                $assignmentAlias,
                [$problem]
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Attempts to remove a problem with a normal user.
     */
    public function testDeleteProblemForbiddenAccess() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'author' => $identity
        ]), $login);
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        );

        // Remove a problem from the assignment with a normal user
        ['user' => $forbiddenUser, 'identity' => $forbiddenIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $forbiddenUserLogin = self::login($forbiddenIdentity);
        try {
            \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
                'auth_token' => $forbiddenUserLogin->auth_token,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => $problem['problem']->alias,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Attempts to remove a problem with a student.
     */
    public function testDeleteProblemForbiddenAccessStudent() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'author' => $identity
        ]), $login);
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        );

        // Remove a problem from the assignment with a student
        $forbiddenUser = \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData
        );
        $forbiddenUserLogin = self::login($forbiddenUser);
        try {
            \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
                'auth_token' => $forbiddenUserLogin->auth_token,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => $problem['problem']->alias,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Attempts to remove an invalid problem.
     */
    public function testDeleteNonExistingProblem() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Remove an invalid problem from the assignment
        try {
            \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => 'noexiste',
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertEquals('problemNotFound', $e->getMessage());
        }
    }
}
