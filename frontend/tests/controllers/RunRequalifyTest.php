<?php
/**
 * Unittest for requalifying run
 */
class RunRequalifyTest extends \OmegaUp\Test\ControllerTestCase {
    public function proveRequalifyProvider(): array {
        return [
            'teaching assistant can requalify in public course' => [
                'public',
                'apiAddTeachingAssistant',
                'isTeachingAssistant',
            ],
            'teaching assistant can requalify in private course' => [
                'private',
                'apiAddTeachingAssistant',
                'isTeachingAssistant',
            ],
            'admin can disqualify in requalify course' => [
                'public',
                'apiAddAdmin',
                'isCourseAdmin',
            ],
            'admin can disqualify in requalify course' => [
                'private',
                'apiAddAdmin',
                'isCourseAdmin',
            ]
        ];
    }

    /**
     * @dataProvider proveRequalifyProvider
     */
    public function testRequalifyByAdminAndTeachingAssistant(
        string $admissionMode,
        string $nameApi,
        string $role
    ) {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            admissionMode: $admissionMode
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Login
        $adminLogin = self::login($courseData['admin']);

        // Add the problem to the assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $adminLogin,
            $courseAlias,
            $assignmentAlias,
            [$problemData]
        );

        // Create our student
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();

        // Add student to course
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student
        );

        // Create a run for assignment
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $student
        );

        // Grade the run
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Create user
        ['identity' => $user] = \OmegaUp\Test\Factories\User::createUser();

        // Login
        $adminLogin = self::login($courseData['admin']);

        // add user like teaching assistant
        \OmegaUp\Controllers\Course::$nameApi(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $user->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::$role(
                $user,
                $course
            )
        );

        // login teaching assistant
        $userLogin = self::login($user);

        $guid = $runData['response']['guid'];

        try {
            // Trying to requalify a normal run
            \OmegaUp\Controllers\Run::apiRequalify(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'run_alias' => $guid
                ])
            );
            $this->fail('A run cannot be requalified when it is normal.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('runCannotBeRequalified', $e->getMessage());
        }

        // Disqualify submission
        \OmegaUp\Controllers\Run::apiDisqualify(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'run_alias' => $guid
            ])
        );

        $this->assertEquals(
            'disqualified',
            \OmegaUp\DAO\Submissions::getByGuid($guid)->type
        );

        try {
            // Trying to disqualify a disqualified run
            \OmegaUp\Controllers\Run::apiDisqualify(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'run_alias' => $guid
                ])
            );
            $this->fail(
                'A run cannot be disqualified when it has been disqualfied before.'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('runCannotBeDisqualified', $e->getMessage());
        }

        // Requalify submission
        \OmegaUp\Controllers\Run::apiRequalify(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'run_alias' => $guid
            ])
        );

        $this->assertEquals(
            'normal',
            \OmegaUp\DAO\Submissions::getByGuid($guid)->type
        );
    }
}
