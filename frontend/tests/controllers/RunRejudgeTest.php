<?php
/**
 * Description of RunRejudgeTest
 */

class RunRejudgeTest extends \OmegaUp\Test\ControllerTestCase {
    public function proveRejudgeProvider(): array {
        return [
            'teaching assistant can rejudge in public course' => [
                'public',
                'apiAddTeachingAssistant',
                'isTeachingAssistant',
            ],
            'teaching assistant can rejudge in private course' => [
                'private',
                'apiAddTeachingAssistant',
                'isTeachingAssistant',
            ],
            'admin can disqualify in rejudge course' => [
                'public',
                'apiAddAdmin',
                'isCourseAdmin',
            ],
            'admin can disqualify in rejudge course' => [
                'private',
                'apiAddAdmin',
                'isCourseAdmin',
            ]
        ];
    }

    /**
     * @dataProvider proveRejudgeProvider
     */
    public function testRejudgeWithoutCompileErrorByAdminAndTeachingAssistant(
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

        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Call API
        $response = \OmegaUp\Controllers\Run::apiRejudge(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'run_alias' => $runData['response']['guid'],
        ]));

        $this->assertSame('ok', $response['status']);
        $this->assertSame(1, $detourGrader->getGraderCallCount());
    }
}
