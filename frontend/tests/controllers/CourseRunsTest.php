<?php

/**
 * Description of CourseRunsTest
 */

class CourseRunsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Participant submits runs and admin is able to get them
     */
    public function testGetRunsForCourse() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Login
        $login = self::login($courseData['admin']);

        // Add the problem to the assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problemData]
        );

        // Create our participant
        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

        // Add student to course
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $participant
        );

        // Create a run for assignment
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $participant
        );

        // Grade the run
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 1, 'AC');

        // Create request
        $login = self::login($courseData['admin']);

        // Call API
        $response = \OmegaUp\Controllers\Course::apiRuns(new \OmegaUp\Request([
            'course_alias' => $courseData['request']['course_alias'],
            'assignment_alias' => $courseData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]));

        // Assert
        $this->assertSame(1, count($response['runs']));
        $this->assertSame(
            $runData['response']['guid'],
            $response['runs'][0]['guid']
        );
        $this->assertSame(
            $participant->username,
            $response['runs'][0]['username']
        );

        // Course admin should be able to view run, even if not problem admin.
        $adminIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $courseData['admin']->username
        );
        $this->assertFalse(\OmegaUp\Authorization::isProblemAdmin(
            $adminIdentity,
            $problemData['problem']
        ));
        $response = \OmegaUp\Controllers\Run::apiDetails(new \OmegaUp\Request([
            'problemset_id' => $courseData['assignment']->problemset_id,
            'run_alias' => $response['runs'][0]['guid'],
            'auth_token' => $login->auth_token,
        ]));

        $this->assertSame($runData['request']['source'], $response['source']);
        $this->assertEmpty($response['feedback']); // Feedback should be empty
    }

    /**
     * @dataProvider runExecutionFilterDataProvider
     */
    public function testRunExecutionFilter(
        $execution,
        $expectedCount,
        $verdicts
    ) {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Login
        $login = self::login($courseData['admin']);

        // Add the problem to the assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problemData]
        );

        // Create our participant
        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

        // Add student to course
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $participant
        );

        foreach ($verdicts as $verdictValue) {
            $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
                $problemData,
                $courseData,
                $participant
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData, 1, $verdictValue);
        }

        $login = self::login($courseData['admin']);
        // Test execution filter
        $response = \OmegaUp\Controllers\Course::apiRuns(new \OmegaUp\Request([
            'course_alias' => $courseData['request']['course_alias'],
            'assignment_alias' => $courseData['request']['alias'],
            'auth_token' => $login->auth_token,
            'execution' => $execution
        ]));

        $this->assertSame($expectedCount, count($response['runs']));
    }

    public function runExecutionFilterDataProvider() {
        return [
            ['EXECUTION_INTERRUPTED', 2, ['MLE','TLE','WA']],
            ['EXECUTION_FINISHED', 2, ['AC','CE','PA']],
            ['EXECUTION_RUNTIME_ERROR', 3, ['AC','CE','RTE','RTE','RTE']],
            ['EXECUTION_RUNTIME_FUNCTION_ERROR', 1, ['AC','RFE','AC']],
            ['EXECUTION_COMPILATION_ERROR', 3, ['CE','CE','CE','RFE']],
            ['EXECUTION_VALIDATOR_ERROR', 1, ['VE','CE','CE','RFE']],
            ['EXECUTION_JUDGE_ERROR', 2, ['JE','VE','JE','RFE']],
        ];
    }

    /**
     * @dataProvider runOutputFilterDataProvider
     */
    public function testRunOutputFilter($output, $expectedCount, $outputs) {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Login
        $login = self::login($courseData['admin']);

        // Add the problem to the assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problemData]
        );

        // Create our participant
        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

        // Add student to course
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $participant
        );

        foreach ($outputs as $outputValue) {
            $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
                $problemData,
                $courseData,
                $participant
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData, 1, $outputValue);
        }

        $login = self::login($courseData['admin']);
        // Test output filter
        $response = \OmegaUp\Controllers\Course::apiRuns(new \OmegaUp\Request([
            'course_alias' => $courseData['request']['course_alias'],
            'assignment_alias' => $courseData['request']['alias'],
            'auth_token' => $login->auth_token,
            'output' => $output
        ]));

        $this->assertSame($expectedCount, count($response['runs']));
    }

    public function runOutputFilterDataProvider() {
        return [
            ['OUTPUT_INTERRUPTED', 2, ['JE','TLE','WA']],
            ['OUTPUT_INCORRECT', 2, ['WA','PA','MLE']],
            ['OUTPUT_EXCEEDED', 3, ['OLE','OLE','RTE','OLE','JE']],
        ];
    }
}
