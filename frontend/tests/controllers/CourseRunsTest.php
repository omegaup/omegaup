<?php

/**
 * Description of CourseRunsTest
 *
 * @author juan.pablo
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
        ['user' => $user, 'identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

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
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Create request
        $login = self::login($courseData['admin']);

        // Call API
        $response = \OmegaUp\Controllers\Course::apiRuns(new \OmegaUp\Request([
            'course_alias' => $courseData['request']['course_alias'],
            'assignment_alias' => $courseData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]));

        // Assert
        $this->assertEquals(1, count($response['runs']));
        $this->assertEquals(
            $runData['response']['guid'],
            $response['runs'][0]['guid']
        );
        $this->assertEquals(
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

        $this->assertEquals($runData['request']['source'], $response['source']);
        $this->assertNull($response['feedback']); // Feedback should be null
    }
}
