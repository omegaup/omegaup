<?php

/**
 * Test administrative tasks for teaching assistant team
 */
class CourseTeachingAssistantTest extends \OmegaUp\Test\ControllerTestCase {
    public function testTeachingAssistantCanViewCourse() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        // create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // create admin
        ['identity' => $adminUser] = \OmegaUp\Test\Factories\User::createAdminUser();

        // login admin
        $adminLogin = self::login($adminUser);

        // add problem to assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $adminLogin,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [ $problemData ]
        );

        // create normal user
        ['identity' => $teachingAssistant] = \OmegaUp\Test\Factories\User::createUser();
        // create normal user
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();

        // add user like teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $teachingAssistant->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // add user like student
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $student
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $run = \OmegaUp\DAO\Runs::getByGUID($runData['response']['guid']);
        if (is_null($run)) {
            return;
        }

        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $teachingAssistant,
                $course
            )
        );

        // teaching assistants are able to view the course
        $response = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'alias' => $courseData['course_alias']
        ]));

        $this->assertEquals($courseData['course_alias'], $response['alias']);

        $submission = \OmegaUp\DAO\Submissions::getByGuid(
            $runData['response']['guid']
        );

        //teaching assistant is able to view the submission
        $this->assertTrue(
            \OmegaUp\Authorization::canEditSubmission(
                $teachingAssistant,
                $submission
            )
        );
    }

    public function testTeachingAssistantCanViewSubmission() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        // create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // create admin
        ['identity' => $adminUser] = \OmegaUp\Test\Factories\User::createAdminUser();

        // login admin
        $adminLogin = self::login($adminUser);

        // add problem to assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $adminLogin,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [ $problemData ]
        );

        // create normal user
        ['identity' => $teachingAssistant] = \OmegaUp\Test\Factories\User::createUser();
        // create normal user
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();

        // add user like teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $teachingAssistant->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // add user like student
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $student
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $run = \OmegaUp\DAO\Runs::getByGUID($runData['response']['guid']);
        if (is_null($run)) {
            return;
        }

        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $teachingAssistant,
                $course
            )
        );

        $submission = \OmegaUp\DAO\Submissions::getByGuid(
            $runData['response']['guid']
        );

        //teaching assistant is able to view the submission
        $this->assertTrue(
            \OmegaUp\Authorization::canViewSubmission(
                $teachingAssistant,
                $submission
            )
        );
    }

    public function testTeachingAssistantCanEditSubmission() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        // create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // create admin
        ['identity' => $adminUser] = \OmegaUp\Test\Factories\User::createAdminUser();

        // login admin
        $adminLogin = self::login($adminUser);

        // add problem to assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $adminLogin,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [ $problemData ]
        );

        // create normal user
        ['identity' => $teachingAssistant] = \OmegaUp\Test\Factories\User::createUser();
        // create normal user
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();

        // add user like teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $teachingAssistant->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // add user like student
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $student
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $run = \OmegaUp\DAO\Runs::getByGUID($runData['response']['guid']);
        if (is_null($run)) {
            return;
        }

        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $teachingAssistant,
                $course
            )
        );

        $submission = \OmegaUp\DAO\Submissions::getByGuid(
            $runData['response']['guid']
        );

        //teaching assistant is able to view the submission
        $this->assertTrue(
            \OmegaUp\Authorization::canEditSubmission(
                $teachingAssistant,
                $submission
            )
        );
    }
}
