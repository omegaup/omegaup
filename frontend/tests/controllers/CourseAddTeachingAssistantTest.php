<?php
/**
 * Test administrative tasks for teaching assistant team
 */
class CourseAddTeachingAssistantTest extends \OmegaUp\Test\ControllerTestCase {
    public function testCanAddTeachingAssistantAnotherTeachingAssistant() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        // create admin
        ['identity' => $adminUser] = \OmegaUp\Test\Factories\User::createAdminUser();

        // login admin
        $adminLogin = self::login($adminUser);

        // create normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // admin is able to add a teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $identity->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // login user
        $userLogin = self::login($identity);
        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $identity,
                $course
            )
        );

        // create another normal user
        ['identity' => $identityUser2] = \OmegaUp\Test\Factories\User::createUser();

        // teaching assistant can't add another teaching assistant
        try {
            \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'usernameOrEmail' => $identityUser2->username,
                    'course_alias' => $courseData['course_alias'],
                ])
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    public function testTeachingAssistantCanResolveClarifications() {
        // create admin
        [
            'identity' => $admin,
        ] = \OmegaUp\Test\Factories\User::createAdminUser();

        // login admin
        $adminLogin = self::login($admin);

        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin,
            $adminLogin
        );

        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'author' => $admin
            ]),
            $adminLogin
        );
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $adminLogin,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        );

        // create user with TA role
        [
            'identity' => $teachingAssistant,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // admin is able to add a teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $teachingAssistant->username,
                'course_alias' => $courseAlias,
            ])
        );

        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();

        // Student joins the course
        \OmegaUp\Controllers\Course::apiAddStudent(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $student->username,
                'course_alias' => $courseAlias,
            ])
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problem,
            $courseData,
            $student
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'CE');

        // Student logs in
        $studentLogin = self::login($student);

        // Student asks a clarification
        $clarification = \OmegaUp\Controllers\Clarification::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'message' => 'This is a clarification',
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => $problem['request']['problem_alias'],
                'username' => '',
            ])
        );

        // TA logs in
        $taLogin = self::login($teachingAssistant);

        // TA resolves the clarification
        \OmegaUp\Controllers\Clarification::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $taLogin->auth_token,
                'clarification_id' => $clarification['clarification_id'],
                'answer' => 'This is the answer for the clarification',
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => $problem['request']['problem_alias'],
                'username' => $student->username,
                'public' => '1',
            ])
        );

        // Retrieve the clarification
        [
            'clarifications' => $clarifications
        ] = \OmegaUp\Controllers\Course::apiClarifications(
            new \OmegaUp\Request([
                'auth_token' => $taLogin->auth_token,
                'course_alias' => $courseAlias,
            ])
        );

        $this->assertCount(1, $clarifications);
        $this->assertEquals(
            $clarifications[0]['message'],
            'This is a clarification'
        );
        $this->assertEquals(
            $clarifications[0]['answer'],
            'This is the answer for the clarification'
        );
    }
}
