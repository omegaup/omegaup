<?php
/**
 * Unittest for requalifying run
 */
class RunRequalifyTest extends \OmegaUp\Test\ControllerTestCase {
    public function testRequalifyByAdmin() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a new run
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $login = self::login($contestData['director']);

        $guid = $runData['response']['guid'];

        try {
            // Trying to requalify a normal run
            \OmegaUp\Controllers\Run::apiRequalify(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
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
                'auth_token' => $login->auth_token,
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
                    'auth_token' => $login->auth_token,
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
                'auth_token' => $login->auth_token,
                'run_alias' => $guid
            ])
        );

        $this->assertEquals(
            'normal',
            \OmegaUp\DAO\Submissions::getByGuid($guid)->type
        );
    }

    public function testRequalifyByTeachingAssistant() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
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
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Create user
        ['identity' => $teachingAssistantUser] = \OmegaUp\Test\Factories\User::createUser();

        // Login
        $adminLogin = self::login($courseData['admin']);

        // add user like teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $teachingAssistantUser->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $teachingAssistantUser,
                $course
            )
        );

        // login teaching assistant
        $teachingAssistantLogin = self::login($teachingAssistantUser);

        $guid = $runData['response']['guid'];

        try {
            // Trying to requalify a normal run
            \OmegaUp\Controllers\Run::apiRequalify(
                new \OmegaUp\Request([
                    'auth_token' => $teachingAssistantLogin->auth_token,
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
                'auth_token' => $teachingAssistantLogin->auth_token,
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
                    'auth_token' => $teachingAssistantLogin->auth_token,
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
                'auth_token' => $teachingAssistantLogin->auth_token,
                'run_alias' => $guid
            ])
        );

        $this->assertEquals(
            'normal',
            \OmegaUp\DAO\Submissions::getByGuid($guid)->type
        );
    }
}
