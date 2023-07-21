<?php

class AssignmentUpdateTest extends \OmegaUp\Test\ControllerTestCase {
    private static $login = null;
    private static $courseData = null;

    public function setUp(): void {
        parent::setUp();

        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        self::$login = self::login($admin);

        self::$courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin,
            self::$login
        );
    }

    public function testAssignmentUpdate() {
        $assignmentAlias = self::$courseData['assignment_alias'];
        $courseAlias = self::$courseData['course_alias'];

        $updatedStartTime = self::$courseData['request']['start_time']->time + 10;
        $updatedFinishTime = self::$courseData['request']['start_time']->time + 20;

        \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
            'auth_token' => self::$login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
            'start_time' => $updatedStartTime,
            'finish_time' => $updatedFinishTime,
            'name' => 'some new name',
            'description' => 'some meaningful description'
        ]));

        // Read the assignment again
        $response = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => self::$login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
        ]));

        $this->assertSame($updatedStartTime, $response['start_time']->time);
        $this->assertSame($updatedFinishTime, $response['finish_time']->time);

        $this->assertSame('some new name', $response['name']);
        $this->assertSame(
            'some meaningful description',
            $response['description']
        );
    }

    /**
     * Test if it's possible to set finish time as null on assignment update
     */
    public function testAssignmentUpdateUnlimitedDuration() {
        $assignmentAlias = self::$courseData['assignment_alias'];
        $courseAlias = self::$courseData['course_alias'];

        $updatedStartTime = self::$courseData['request']['start_time']->time + 10;

        try {
            // Try to set unlimited duration to assignment
            \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
                'auth_token' => self::$login->auth_token,
                'assignment' => $assignmentAlias,
                'course' => $courseAlias,
                'start_time' => $updatedStartTime,
                'unlimited_duration' => true,
                'name' => 'some new name',
                'description' => 'some meaningful description'
            ]));
            $this->fail('Should have thrown exception.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'courseDoesNotHaveUnlimitedDuration',
                $e->getMessage()
            );
        }

        // Now update the course in order to be of unlimited duration
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => self::$login->auth_token,
            'course_alias' => self::$courseData['course_alias'],
            'name' => self::$courseData['request']['course']->name,
            'description' => self::$courseData['request']['course']->description,
            'alias' => self::$courseData['request']['course']->alias,
            'show_scoreboard' => false,
            'unlimited_duration' => true
        ]));

        \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
            'auth_token' => self::$login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
            'start_time' => $updatedStartTime,
            'unlimited_duration' => true,
            'name' => 'some new name',
            'description' => 'some meaningful description'
        ]));

        // Read the assignment again
        $response = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => self::$login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
        ]));

        $this->assertSame($updatedStartTime, $response['start_time']->time);
        $this->assertNull($response['finish_time']);

        \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
            'auth_token' => self::$login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
            'unlimited_duration' => true,
            'description' => 'some new meaningful description'
        ]));

        $response = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => self::$login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
        ]));

        $this->assertSame(
            'some new meaningful description',
            $response['description']
        );
    }

    /**
     * When updating an assignment you need to supply both assignment
     * alias and course alias
     */
    public function testMissingDataOnAssignmentUpdate() {
        try {
            \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
                'auth_token' => self::$login->auth_token,
                'assignment' => self::$courseData['assignment_alias'],
                'name' => 'some new name'
            ]));
            $this->fail(
                'Updating assignment should have failed due to missing parameter'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterEmpty', $e->getMessage());
        }

        try {
            \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
                'auth_token' => self::$login->auth_token,
                'course' => self::$courseData['course_alias'],
                'name' => 'some new name'
            ]));
            $this->fail(
                'Updating assignment should have failed due to missing parameter'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterEmpty', $e->getMessage());
        }
    }

    /**
     * Can't update the start time to be after the finish time.
     */
    public function testAssignmentUpdateWithInvertedTimes() {
        try {
            \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
                'auth_token' => self::$login->auth_token,
                'assignment' => self::$courseData['assignment_alias'],
                'course' => self::$courseData['course_alias'],
                'start_time' => self::$courseData['request']['start_time']->time + 10,
                'finish_time' => self::$courseData['request']['start_time']->time + 9,
            ]));

            $this->fail(
                'Assignment should not have been updated because finish time is earlier than start time'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('courseInvalidStartTime', $e->getMessage());
        }
    }

    /**
     * Students should not be able to update the assignment.
     */
    public function testAssignmentUpdateByStudent() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => self::$login->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => self::$courseData['course_alias'],
        ]));

        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        try {
            \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'assignment' => self::$courseData['assignment_alias'],
                'course' => self::$courseData['course_alias'],
                'start_time' => self::$courseData['request']['start_time'],
                'finish_time' => self::$courseData['request']['finish_time'],
                'description' => 'pwnd',
            ]));
            $this->fail('Expected ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testAssignmentsOutOfDate() {
        $response = \OmegaUp\Controllers\Course::apiListAssignments(new \OmegaUp\Request([
            'auth_token' => self::$login->auth_token,
            'course_alias' => self::$courseData['course_alias']
        ]));

        // Updating start_time of assignment out of the date
        try {
            \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
                'auth_token' => self::$login->auth_token,
                'course' => self::$courseData['course_alias'],
                'name' => $response['assignments'][0]['name'],
                'assignment' => $response['assignments'][0]['alias'],
                'description' => $response['assignments'][0]['description'],
                'start_time' => $response['assignments'][0]['start_time']->time + 240,
                'finish_time' => $response['assignments'][0]['finish_time']->time + 240,
                'assignment_type' => $response['assignments'][0]['assignment_type'],
            ]));
            $this->fail(
                'Assignment should not have been updated because the date falls outside of valid range'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterDateTooLarge', $e->getMessage());

            $responseArray =  $e->asResponseArray();
            $this->assertSame(
                'parameterDateTooLarge',
                $responseArray['errorname']
            );
            $this->assertSame(
                self::$courseData['course']->finish_time->time,
                $responseArray['payload']['upper_bound']
            );
        }
    }

    public function testAssignmentUpdateWithRuns() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create a course with a different courseDuration of 180.
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            admissionMode: \OmegaUp\Controllers\Course::ADMISSION_MODE_PRIVATE,
            requestsUserInformation: 'no',
            showScoreboard: 'false',
            startTimeDelay: 0,
            courseDuration: 180,
        );
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
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Trying to create a run out of the time
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 140);

        try {
            // Create a run for assignment
            $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
                $problemData,
                $courseData,
                $participant
            );
            $this->fail('Should have thrown exception.');
        } catch (\OmegaUp\Exceptions\NotAllowedToSubmitException $e) {
            $this->assertSame(
                'runNotInsideContest',
                $e->getMessage()
            );
        }

        // Updating finish time to let participants create runs
        $updatedStartTime = $courseData['request']['start_time']->time;
        $updatedFinishTime = $courseData['request']['start_time']->time + 160;

        // Login
        $login = self::login($courseData['admin']);

        \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
            'start_time' => $updatedStartTime,
            'finish_time' => $updatedFinishTime,
        ]));

        // Create a successful run for assignment
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $participant
        );

        // Set the unlimited duration time of the course as true
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'name' => $courseData['request']['course']->name,
            'description' => $courseData['request']['course']->description,
            'alias' => $courseData['request']['course']->alias,
            'show_scoreboard' => false,
            'unlimited_duration' => true
        ]));
        $newCourse = \OmegaUp\DAO\Courses::getByPK(
            $courseData['request']['course']->course_id
        );
        $this->assertNull($newCourse->finish_time);

        // Set the unlimited duration time of the assignment as true
        \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
            'unlimited_duration' => true,
        ]));
        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            $courseData['request']['course']->course_id
        );
        $this->assertNull($assignment->finish_time);

        // Going forward in the time to test whether participant can create runs
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 280);

        // Create a run for assignment
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $participant
        );
    }

    public function testAssignmentStartTimeBeforeCourseStartTime() {
        $assignmentAlias = self::$courseData['assignment_alias'];
        $courseAlias = self::$courseData['course_alias'];
        $courseStartTime = self::$courseData['request']['start_time']->time;
        try {
            \OmegaUp\Controllers\Course::apiUpdateAssignment(
                new \OmegaUp\Request([
                    'auth_token' => self::$login->auth_token,
                    'assignment' => $assignmentAlias,
                    'course' => $courseAlias,
                    'start_time' => $courseStartTime - 10,
                    'finish_time' => $courseStartTime + 10,
                    'name' => 'some new name',
                    'description' => 'some meaningful description',
                ])
            );
            $this->fail('Should have thrown exception due invalid start time.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'courseAssignmentStartDateBeforeCourseStartDate',
                $e->getMessage()
            );
        }
    }

    public function testAssignmentFinishTimeBeforeCourseStartTime() {
        $assignmentAlias = self::$courseData['assignment_alias'];
        $courseAlias = self::$courseData['course_alias'];
        $courseStartTime = self::$courseData['request']['start_time']->time;
        try {
            \OmegaUp\Controllers\Course::apiUpdateAssignment(
                new \OmegaUp\Request([
                    'auth_token' => self::$login->auth_token,
                    'assignment' => $assignmentAlias,
                    'course' => $courseAlias,
                    'start_time' => $courseStartTime + 10,
                    'finish_time' => $courseStartTime - 10,
                    'name' => 'some new name',
                    'description' => 'some meaningful description',
                ])
            );
            $this->fail(
                'Updating assignment should have failed due assignment end date incorrect'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'courseAssignmentEndDateBeforeCourseStartDate',
                $e->getMessage()
            );
        }
    }

    public function testUpdateAssignmentAlreadyHasRuns() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        $login = self::login($courseData['admin']);

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problemData]
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $courseData['admin']
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Should not throw any exception
        $login = self::login($courseData['admin']);
        \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
            'start_time' => $courseData['request']['start_time']->time + 1,
            'finish_time' => $courseData['request']['finish_time'],
        ]));

        // Take back to normal date
        \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
            'start_time' => $courseData['request']['start_time']->time,
            'finish_time' => $courseData['request']['finish_time'],
        ]));

        // Create a participant and a run
        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $participant
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $participant
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Should throw a exception
        $login = self::login($courseData['admin']);
        try {
            \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'assignment' => $assignmentAlias,
                'course' => $courseAlias,
                'start_time' => $courseData['request']['start_time']->time + 2,
                'finish_time' => $courseData['request']['finish_time'],
            ]));
            $this->fail(
                'Updating assignment should have failed due to assignment already has student runs'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'courseUpdateAlreadyHasRuns',
                $e->getMessage()
            );
        }
    }

    public function testUpdateFinishTimeAssignmentAlreadyHasRuns() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        $login = self::login($courseData['admin']);

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problemData]
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $courseData['admin']
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Create a participant and a run
        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $participant
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $participant
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $updatedFinishTime = self::$courseData['request']['start_time']->time + 20;

        $login = self::login($courseData['admin']);
        \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
            'finish_time' => $updatedFinishTime,
        ]));

        $login = self::login($courseData['admin']);

        // Read the assignment again
        $response = \OmegaUp\Controllers\Course::apiAssignmentDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'assignment' => $assignmentAlias,
                'course' => $courseAlias,
            ])
        );

        $this->assertSame(
            self::$courseData['request']['start_time']->time,
            $response['start_time']->time
        );
        $this->assertSame($updatedFinishTime, $response['finish_time']->time);
    }
}
