<?php

/**
 * Description of SubmissionFeedbackTest
 */

class SubmissionFeedbackTest extends \OmegaUp\Test\ControllerTestCase {
    public function testSubmissionFeedbackForCourse() {
        $admin = \OmegaUp\Test\Factories\User::createUser();
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin['identity'],
            self::login($admin['identity']),
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );

        $login = self::login($admin['identity']);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [ $problemData ]
        );

        $student = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student['identity']
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $student['identity']
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $run = \OmegaUp\DAO\Runs::getByGUID($runData['response']['guid']);
        if (is_null($run)) {
            return;
        }

        $feedback = 'Test feedback';
        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => $feedback,
            ])
        );

        // Verify notification for admin
        $notifications = \OmegaUp\DAO\Notifications::getUnreadNotifications(
            $student['user']
        );
        $this->assertCount(1, $notifications);

        $contents = json_decode($notifications[0]['contents'], true);
        $this->assertEquals(
            \OmegaUp\DAO\Notifications::COURSE_SUBMISSION_FEEDBACK,
            $contents['type']
        );
        $this->assertEquals(
            $courseData['course']->name,
            $contents['body']['localizationParams']['courseName']
        );
        $this->assertEquals(
            $problemData['problem']->alias,
            $contents['body']['localizationParams']['problemAlias']
        );
    }

    public function testSubmissionFeedbackForCourseByTeachingAssistant() {
        // create admin
        $admin = \OmegaUp\Test\Factories\User::createUser();

        // create course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin['identity'],
            self::login($admin['identity']),
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );

        // login admin
        $loginAdmin = self::login($admin['identity']);

        // create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // add problem to assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $loginAdmin,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [ $problemData ]
        );

        // create a user
        $student = \OmegaUp\Test\Factories\User::createUser();

        // add user like a student
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student['identity']
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $student['identity']
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $run = \OmegaUp\DAO\Runs::getByGUID($runData['response']['guid']);
        if (is_null($run)) {
            return;
        }

        // create a user
        ['identity' => $teachigAssistantUser] = \OmegaUp\Test\Factories\User::createUser();

        // login admin
        $loginAdmin = self::login($admin['identity']);

        // add user like a teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $loginAdmin->auth_token,
                'usernameOrEmail' => $teachigAssistantUser->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // login teaching assistant
        $loginTeachingAssistant = self::login($teachigAssistantUser);

        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        // check if is a teaching assistant
        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $teachigAssistantUser,
                $course
            )
        );

        // give a feedback like teaching assistant
        $feedback = 'Test feedback!';
        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => $loginTeachingAssistant->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => $feedback,
                'range_bytes_start' => 1,
                'range_bytes_end' => 88,
            ])
        );

        // Verify notification for admin
        $notifications = \OmegaUp\DAO\Notifications::getUnreadNotifications(
            $student['user']
        );
        $this->assertCount(1, $notifications);

        $contents = json_decode($notifications[0]['contents'], true);
        $this->assertEquals(
            \OmegaUp\DAO\Notifications::COURSE_SUBMISSION_FEEDBACK,
            $contents['type']
        );
        $this->assertEquals(
            $courseData['course']->name,
            $contents['body']['localizationParams']['courseName']
        );
        $this->assertEquals(
            $problemData['problem']->alias,
            $contents['body']['localizationParams']['problemAlias']
        );
    }

    public function testGetCourseSubmissionFeedback() {
        $admin = \OmegaUp\Test\Factories\User::createUser();
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin['identity'],
            self::login($admin['identity']),
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );

        $login = self::login($admin['identity']);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [ $problemData ]
        );

        $student = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student['identity']
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $student['identity']
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $run = \OmegaUp\DAO\Runs::getByGUID($runData['response']['guid']);
        if (is_null($run)) {
            return;
        }

        // Without the feedback
        $response = \OmegaUp\Controllers\Run::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'problemset_id' => $courseData['assignment']->problemset_id,
                'run_alias' => $runData['response']['guid'],
            ])
        );
        $this->assertNull($response['feedback']);

        $feedback = 'Test feedback';
        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => $feedback,
            ])
        );

        // After adding the feedback
        $response = \OmegaUp\Controllers\Run::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'problemset_id' => $courseData['assignment']->problemset_id,
                'run_alias' => $runData['response']['guid'],
                'include_feedback' => true,
            ])
        );
        $this->assertNotNull($response['feedback']);
        $this->assertEquals($feedback, $response['feedback']['feedback']);
        $this->assertEquals(
            $admin['identity']->username,
            $response['feedback']['author']
        );
    }

    public function testEditSubmissionFeedbackForCourse() {
        $admin = \OmegaUp\Test\Factories\User::createUser();
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin['identity'],
            self::login($admin['identity']),
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );

        $login = self::login($admin['identity']);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [ $problemData ]
        );

        $student = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student['identity']
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $student['identity']
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $run = \OmegaUp\DAO\Runs::getByGUID($runData['response']['guid']);
        if (is_null($run)) {
            return;
        }

        $submission = \OmegaUp\DAO\Submissions::getByGuid(
            $runData['response']['guid']
        );
        if (is_null($submission)) {
            return;
        }

        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => 'Initial test feedback',
            ])
        );
        $initialFeedback = \OmegaUp\DAO\SubmissionFeedback::getFeedbackBySubmission(
            $submission
        );

        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => 'New feedback',
            ])
        );
        $newFeedback = \OmegaUp\DAO\SubmissionFeedback::getFeedbackBySubmission(
            $submission
        );

        $this->assertNotEquals(
            $initialFeedback->feedback,
            $newFeedback->feedback
        );
    }
}
