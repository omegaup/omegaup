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
        $this->assertSame(
            \OmegaUp\DAO\Notifications::COURSE_SUBMISSION_FEEDBACK,
            $contents['type']
        );
        $this->assertSame(
            $courseData['course']->name,
            $contents['body']['localizationParams']['courseName']
        );
        $this->assertSame(
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

        // give a feedback as teaching assistant
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

    /**
     * A PHPUnit data provider for submission feedback.
     *
     * @return list<list<null|string>>
     */
    public function submissionFeedbackLineProvider(): array {
        return [
            [null], // A general feedback
            [1],
            [3],
            [5],
        ];
    }

    /**
     * @dataProvider submissionFeedbackLineProvider
     */
    public function testGetCourseSubmissionFeedback(?int $rangeBytesStart) {
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
        $this->assertEmpty($response['feedback']);

        $feedback = 'Test feedback';
        $submissionFeedback = [
            'auth_token' => self::login($admin['identity'])->auth_token,
            'guid' => $runData['response']['guid'],
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'feedback' => $feedback,
        ];

        if (!is_null($rangeBytesStart)) {
            $submissionFeedback['range_bytes_start'] = $rangeBytesStart;
        }

        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request($submissionFeedback)
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
        $foundKey = array_search(
            $rangeBytesStart,
            array_column(
                $response['feedback'],
                'range_bytes_start'
            )
        );
        $feedbackResponse = $response['feedback'][$foundKey];
        $this->assertNotEmpty($feedbackResponse);
        $this->assertSame($feedback, $feedbackResponse['feedback']);
        $this->assertSame(
            $admin['identity']->username,
            $feedbackResponse['author']
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
            $submission->guid,
            rangeBytesStart: null
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
            $submission->guid,
            rangeBytesStart: null
        );

        $this->assertNotEquals(
            $initialFeedback->feedback,
            $newFeedback->feedback
        );
    }

    public function testEditSubmissionFeedbackWithThreadForCourse() {
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

        // Creating and adding a new user as a course student
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

        // Admin creates a feedback in the line number 1
        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => 'Initial test feedback',
                'range_bytes_start' => 1,
            ])
        );

        // The student doesn't have the right access to create a feedback in any
        // line
        try {
            \OmegaUp\Controllers\Submission::apiSetFeedback(
                new \OmegaUp\Request([
                    'auth_token' => self::login(
                        $student['identity']
                    )->auth_token,
                    'guid' => $runData['response']['guid'],
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $courseData['assignment_alias'],
                    'feedback' => 'New comment from a student',
                    'range_bytes_start' => 2,
                ])
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }

        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => 'New feedback',
                'range_bytes_start' => 3,
            ])
        );
        $feedbackList = \OmegaUp\Controllers\Run::apiGetSubmissionFeedback(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'run_alias' => $runData['response']['guid'],
            ])
        );

        $expectedCommentsLines = [
            [
                'line' => 1,
                'author' => $admin['identity']->username,
                'feedback_thread' => ['Second feedback reply', 'First feedback reply'],
            ],
            [
                'line' => 3,
                'author' => $admin['identity']->username,
            ],
        ];

        // Comparing the created feedback comments versus expected comments
        foreach ($feedbackList as $index => $feedback) {
            $this->assertArrayContainsWithPredicate(
                $expectedCommentsLines,
                fn ($comment) => $comment['line'] == $feedback['range_bytes_start']
            );
            $this->assertArrayNotHasKey('feedback_thread', $feedback);
            $expectedCommentsLines[$index]['submission_feedback_id'] = $feedback['submission_feedback_id'];
        }

        $submissionFeedbackId = $expectedCommentsLines[0]['submission_feedback_id'];

        // Adding a feedback thread as admin
        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => 'First feedback reply',
                'submission_feedback_id' => $submissionFeedbackId,
            ])
        );

        $studentLogin = self::login($student['identity']);

        // Adding a feedback thread as student
        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => 'Second feedback reply',
                'submission_feedback_id' => $submissionFeedbackId,
            ])
        );

        // The participants in the thread should be the admin and the student
        $participants = \OmegaUp\DAO\SubmissionFeedbackThread::getSubmissionFeedbackThreadParticipants(
            $submissionFeedbackId
        );

        $expectedParticipants = [
            ['author_id' => $admin['identity']->user_id],
            ['author_id' => $student['identity']->user_id],
        ];

        // Comparing the participants versus expected participants
        foreach ($participants as $index => $participant) {
            $this->assertArrayContainsWithPredicate(
                $expectedParticipants,
                fn ($expectedParticipant) => $expectedParticipant['author_id'] == $participant['author_id']
            );
        }

        $feedbackList = \OmegaUp\Controllers\Run::apiGetSubmissionFeedback(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'run_alias' => $runData['response']['guid'],
            ])
        );

        // Comparing the new feedback threads versus expected comments
        foreach ($feedbackList as $index => $feedback) {
            $this->assertEquals(
                $feedback['author'],
                $expectedCommentsLines[$index]['author']
            );
            if (isset($expectedCommentsLines[$index]['feedback_thread'])) {
                $this->assertNotEmpty($feedback['feedback_thread']);

                $this->assertEquals(
                    $feedback['feedback_thread'][$index]['text'],
                    $expectedCommentsLines[$index]['feedback_thread'][0]
                );
            } else {
                $this->assertArrayNotHasKey('feedback_thread', $feedback);
            }
        }

        // Trying to add a comment with a user who does not belong to the course
        $user = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($user['identity']);

        try {
            \OmegaUp\Controllers\Submission::apiSetFeedback(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'guid' => $runData['response']['guid'],
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $courseData['assignment_alias'],
                    'feedback' => 'New feedback thread',
                    'submission_feedback_id' => $expectedCommentsLines[0]['submission_feedback_id'],
                ])
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }

        ['runs' => $runs] = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['problemset_id'],
                'prevent_problemset_open' => false,
                'problem_alias' => $problemData['request']['problem_alias'],
            ])
        );

        $this->assertSame(2, $runs[0]['suggestions']);
    }

    public function testSubmissionFeedbackGeneralAndByLine() {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($admin);
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin,
            $login,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [ $problemData ]
        );

        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();

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

        // student call api to send notifications to all administrators
        // members in the course
        $studentLogin = self::login($student);
        $response = \OmegaUp\Controllers\Course::apiRequestFeedback(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'assignment_alias' => $courseData['assignment_alias'],
                'course_alias' => $courseData['course_alias'],
                'guid' => $runData['response']['guid']
            ])
        );

        $this->assertSame('ok', $response['status']);

        $login = self::login($admin);

        $runs = \OmegaUp\Controllers\Course::getCourseDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'assignment_alias' => $courseData['assignment_alias'],
                'course_alias' => $courseData['course_alias'],
            ])
        )['templateProperties']['payload']['currentAssignment']['runs'];

        $this->assertSame(1, $runs[0]['suggestions']);

        $feedbackList = \OmegaUp\Controllers\Run::apiGetSubmissionFeedback(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'run_alias' => $runData['response']['guid'],
            ])
        );

        $this->assertCount(1, $feedbackList);
        $this->assertNull($feedbackList[0]['range_bytes_start']);

        $feedback = 'Test feedback!';
        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => $feedback,
                'range_bytes_start' => 1,
            ])
        );

        // Now the admin should get two feedbacks: The general one and the
        // feedback in the line 1
        $feedbackList = \OmegaUp\Controllers\Run::apiGetSubmissionFeedback(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'run_alias' => $runData['response']['guid'],
            ])
        );

        $this->assertCount(2, $feedbackList);
        $this->assertNull($feedbackList[0]['range_bytes_start']);
        $this->assertSame(1, $feedbackList[1]['range_bytes_start']);
        $this->assertSame($feedback, $feedbackList[1]['feedback']);

        // This should be reflected in the course details for the admin
        $runs = \OmegaUp\Controllers\Course::getCourseDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'assignment_alias' => $courseData['assignment_alias'],
                'course_alias' => $courseData['course_alias'],
            ])
        )['templateProperties']['payload']['currentAssignment']['runs'];

        $this->assertSame(2, $runs[0]['suggestions']);

        // Even when the feedback is updated, the feedback in the line 1 should
        // remain the same as originally set because updating the feedback in
        // the lines of code is not implemented yet.
        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => 'Updated Test feedback!',
                'range_bytes_start' => 1,
            ])
        );

        $feedbackList = \OmegaUp\Controllers\Run::apiGetSubmissionFeedback(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'run_alias' => $runData['response']['guid'],
            ])
        );

        $this->assertCount(2, $feedbackList);
        $this->assertNull($feedbackList[0]['range_bytes_start']);
        $this->assertSame(1, $feedbackList[1]['range_bytes_start']);
        $this->assertSame($feedback, $feedbackList[1]['feedback']);
    }

    public function testCountNumberOfSubmissionFeedbackSuggestions() {
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

        // Creating and adding a new user as a course student
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

        // Admin creates a feedback in the line number 1, 5 and 9
        $suggestionsLines = [
            ['line' => 1, 'feedback' => 'Initial test feedback'],
            ['line' => 5, 'feedback' => 'Second test feedback'],
            ['line' => 9, 'feedback' => 'Third test feedback'],
        ];
        $adminLogin = self::login($admin['identity'])->auth_token;

        foreach ($suggestionsLines as $suggestionLine) {
            \OmegaUp\Controllers\Submission::apiSetFeedback(
                new \OmegaUp\Request([
                    'auth_token' => $adminLogin,
                    'guid' => $runData['response']['guid'],
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $courseData['assignment_alias'],
                    'feedback' => $suggestionLine['feedback'],
                    'range_bytes_start' => $suggestionLine['line'],
                ])
            );
        }

        $studentLogin = self::login($student['identity']);
        ['runs' => $runs] = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['problemset_id'],
                'prevent_problemset_open' => false,
                'problem_alias' => $problemData['request']['problem_alias'],
            ])
        );

        $this->assertSame(3, $runs[0]['suggestions']);

        // Even if the student replies a feedback, the number of suggestions
        // should remain the same
        $feedbackList = \OmegaUp\Controllers\Run::apiGetSubmissionFeedback(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'run_alias' => $runData['response']['guid'],
            ])
        );

        $submissionFeedbackId = $feedbackList[0]['submission_feedback_id'];

        // Adding a feedback thread as student
        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => 'Feedback reply',
                'submission_feedback_id' => $submissionFeedbackId,
            ])
        );

        ['runs' => $runs] = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['problemset_id'],
                'prevent_problemset_open' => false,
                'problem_alias' => $problemData['request']['problem_alias'],
            ])
        );

        $this->assertSame(3, $runs[0]['suggestions']);
    }

    public function testSetFeedbackListWithInvalidJson(): void {
        $admin = \OmegaUp\Test\Factories\User::createUser();
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin['identity'],
            self::login($admin['identity']),
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );
        $loginAdmin = self::login($admin['identity']);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $loginAdmin,
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

        try {
            $loginAdmin = self::login($admin['identity']);
            \OmegaUp\Controllers\Submission::apiSetFeedbackList(
                new \OmegaUp\Request([
                    'auth_token' => $loginAdmin->auth_token,
                    'guid' => $runData['response']['guid'],
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $courseData['assignment_alias'],
                    'feedback_list' => 'not-valid-json',
                ])
            );
            $this->fail('Should have thrown an InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('invalidParameters', $e->getMessage());
            $this->assertSame('feedback_list', $e->parameter);
        }
    }

    /**
     * A PHPUnit data provider for malformed feedback lists.
     *
     * @return list<list<string>>
     */
    public function malformedFeedbackListProvider(): array {
        return [
            ['"a string"'],
            ['[1]'],
            ['[{"feedback":"ok"}]'],
            ['[{"lineNumber":5}]'],
            ['[{"lineNumber":"abc","feedback":"ok"}]'],
            ['[{"lineNumber":5,"feedback":10}]'],
        ];
    }

    /**
     * @dataProvider malformedFeedbackListProvider
     */
    public function testSetFeedbackListWithMalformedElements(
        string $malformedFeedbackList
    ) {
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

        try {
            \OmegaUp\Controllers\Submission::apiSetFeedbackList(
                new \OmegaUp\Request([
                    'auth_token' => self::login(
                        $admin['identity']
                    )->auth_token,
                    'guid' => $runData['response']['guid'],
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $courseData['assignment_alias'],
                    'feedback_list' => $malformedFeedbackList,
                ])
            );
            $this->fail(
                "Should have thrown an InvalidParameterException for {$malformedFeedbackList}"
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('invalidParameters', $e->getMessage());
            $this->assertSame('feedback_list', $e->parameter);
        }
    }

    public function testSetFeedbackListWithValidFeedbackList() {
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

        $login = self::login($admin['identity']);
        $response = \OmegaUp\Controllers\Submission::apiSetFeedbackList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'guid' => $runData['response']['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback_list' => json_encode([
                    ['lineNumber' => 1, 'feedback' => 'First line feedback'],
                    ['lineNumber' => 5, 'feedback' => 'Fifth line feedback'],
                ]),
            ])
        );

        $this->assertSame('ok', $response['status']);

        $feedbackList = \OmegaUp\Controllers\Run::apiGetSubmissionFeedback(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'run_alias' => $runData['response']['guid'],
            ])
        );

        $this->assertCount(2, $feedbackList);
        $feedbackByLine = [];
        foreach ($feedbackList as $entry) {
            $feedbackByLine[$entry['range_bytes_start']] = $entry['feedback'];
        }
        $this->assertSame('First line feedback', $feedbackByLine[1]);
        $this->assertSame('Fifth line feedback', $feedbackByLine[5]);
    }

    /**
     * Creates a course submission reviewed by a teaching assistant.
     *
     * @return array{assignmentAlias: string, courseAlias: string, guid: string, student: array{identity: \OmegaUp\DAO\VO\Identities, user: \OmegaUp\DAO\VO\Users}, teachingAssistant: \OmegaUp\DAO\VO\Identities, teachingAssistantLogin: \OmegaUp\Test\ScopedLoginToken, admin: \OmegaUp\DAO\VO\Identities}
     */
    private function createCourseSubmissionWithTeachingAssistant(): array {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = self::login($admin);
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin,
            $adminLogin,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $adminLogin,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [ $problemData ]
        );

        ['identity' => $studentIdentity, 'user' => $studentUser] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $studentIdentity
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $studentIdentity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        ['identity' => $teachingAssistant] = \OmegaUp\Test\Factories\User::createUser();
        // Earlier logins are logged out when their tokens leave scope, so the
        // admin has to sign in again before managing the course.
        $adminLogin = self::login($admin);
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $teachingAssistant->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );
        $teachingAssistantLogin = self::login($teachingAssistant);

        return [
            'assignmentAlias' => $courseData['assignment_alias'],
            'courseAlias' => $courseData['course_alias'],
            'guid' => $runData['response']['guid'],
            'student' => [
                'identity' => $studentIdentity,
                'user' => $studentUser,
            ],
            'teachingAssistant' => $teachingAssistant,
            'teachingAssistantLogin' => $teachingAssistantLogin,
            'admin' => $admin,
        ];
    }

    /**
     * Adds another teaching assistant to a course and returns their identity
     * and login token.
     *
     * @return array{identity: \OmegaUp\DAO\VO\Identities, login: \OmegaUp\Test\ScopedLoginToken}
     */
    private function addTeachingAssistantToCourse(
        \OmegaUp\DAO\VO\Identities $admin,
        string $courseAlias
    ): array {
        ['identity' => $teachingAssistant] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = self::login($admin);
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $teachingAssistant->username,
                'course_alias' => $courseAlias,
            ])
        );
        return [
            'identity' => $teachingAssistant,
            'login' => self::login($teachingAssistant),
        ];
    }

    /**
     * @param list<array{author: string, author_classname: string, date: \OmegaUp\Timestamp, feedback: string, feedback_thread?: list<array{author: string, authorClassname: string, submission_feedback_thread_id: int, text: string, timestamp: \OmegaUp\Timestamp}>, range_bytes_end: int|null, range_bytes_start: int|null, submission_feedback_id: int}> $feedbackList
     * @return list<array{author: string, author_classname: string, date: \OmegaUp\Timestamp, feedback: string, feedback_thread?: list<array{author: string, authorClassname: string, submission_feedback_thread_id: int, text: string, timestamp: \OmegaUp\Timestamp}>, range_bytes_end: int|null, range_bytes_start: int|null, submission_feedback_id: int}>
     */
    private function generalFeedbackRows(array $feedbackList): array {
        return array_values(array_filter(
            $feedbackList,
            fn (array $feedback) => is_null($feedback['range_bytes_start'])
        ));
    }

    public function testSetFeedbackListWithGeneralFeedback() {
        [
            'assignmentAlias' => $assignmentAlias,
            'courseAlias' => $courseAlias,
            'guid' => $guid,
            'student' => $student,
            'teachingAssistant' => $teachingAssistant,
            'teachingAssistantLogin' => $teachingAssistantLogin,
        ] = $this->createCourseSubmissionWithTeachingAssistant();

        $summary = 'Overall the solution works, but it can be simpler.';
        \OmegaUp\Controllers\Submission::apiSetFeedbackList(
            new \OmegaUp\Request([
                'auth_token' => $teachingAssistantLogin->auth_token,
                'guid' => $guid,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'feedback_list' => json_encode([
                    ['lineNumber' => 1, 'feedback' => 'Rename this variable'],
                    ['lineNumber' => 4, 'feedback' => 'This loop is unnecessary'],
                ]),
                'feedback' => $summary,
            ])
        );

        $feedbackList = \OmegaUp\Controllers\Run::apiGetSubmissionFeedback(
            new \OmegaUp\Request([
                'auth_token' => $teachingAssistantLogin->auth_token,
                'run_alias' => $guid,
            ])
        );

        $this->assertCount(3, $feedbackList);
        $generalFeedback = $this->generalFeedbackRows($feedbackList);
        $this->assertCount(1, $generalFeedback);
        $this->assertSame($summary, $generalFeedback[0]['feedback']);
        $this->assertSame(
            $teachingAssistant->username,
            $generalFeedback[0]['author']
        );

        $lineFeedback = array_values(array_filter(
            $feedbackList,
            fn (array $feedback) => !is_null($feedback['range_bytes_start'])
        ));
        $this->assertCount(2, $lineFeedback);

        $notifications = \OmegaUp\DAO\Notifications::getUnreadNotifications(
            $student['user']
        );
        $this->assertCount(1, $notifications);
    }

    public function testSetFeedbackListReplacesFeedbackRequest() {
        [
            'assignmentAlias' => $assignmentAlias,
            'courseAlias' => $courseAlias,
            'guid' => $guid,
            'student' => $student,
            'teachingAssistant' => $teachingAssistant,
            'teachingAssistantLogin' => $teachingAssistantLogin,
        ] = $this->createCourseSubmissionWithTeachingAssistant();

        $studentLogin = self::login($student['identity']);
        \OmegaUp\Controllers\Course::apiRequestFeedback(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'assignment_alias' => $assignmentAlias,
                'course_alias' => $courseAlias,
                'guid' => $guid,
            ])
        );

        $feedbackList = \OmegaUp\Controllers\Run::apiGetSubmissionFeedback(
            new \OmegaUp\Request([
                'auth_token' => $teachingAssistantLogin->auth_token,
                'run_alias' => $guid,
            ])
        );
        $requestMessage = \OmegaUp\Translations::getInstance()->get(
            'requestFeedbackMessage'
        );
        $generalFeedback = $this->generalFeedbackRows($feedbackList);
        $this->assertCount(1, $generalFeedback);
        $this->assertSame($requestMessage, $generalFeedback[0]['feedback']);

        $summary = 'Thanks for asking. Start by checking the loop bounds.';
        \OmegaUp\Controllers\Submission::apiSetFeedbackList(
            new \OmegaUp\Request([
                'auth_token' => $teachingAssistantLogin->auth_token,
                'guid' => $guid,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'feedback_list' => json_encode([
                    ['lineNumber' => 2, 'feedback' => 'Off by one here'],
                ]),
                'feedback' => $summary,
            ])
        );

        $feedbackList = \OmegaUp\Controllers\Run::apiGetSubmissionFeedback(
            new \OmegaUp\Request([
                'auth_token' => $teachingAssistantLogin->auth_token,
                'run_alias' => $guid,
            ])
        );
        $generalFeedback = $this->generalFeedbackRows($feedbackList);
        $this->assertCount(1, $generalFeedback);
        $this->assertSame($summary, $generalFeedback[0]['feedback']);
        $this->assertSame(
            $teachingAssistant->username,
            $generalFeedback[0]['author']
        );
        $this->assertNotSame(
            $requestMessage,
            $generalFeedback[0]['feedback']
        );
    }

    public function testSetFeedbackListOnlyGeneralFeedback() {
        [
            'assignmentAlias' => $assignmentAlias,
            'courseAlias' => $courseAlias,
            'guid' => $guid,
            'teachingAssistant' => $teachingAssistant,
            'teachingAssistantLogin' => $teachingAssistantLogin,
        ] = $this->createCourseSubmissionWithTeachingAssistant();

        $summary = 'Looks good overall.';
        $response = \OmegaUp\Controllers\Submission::apiSetFeedbackList(
            new \OmegaUp\Request([
                'auth_token' => $teachingAssistantLogin->auth_token,
                'guid' => $guid,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'feedback_list' => '[]',
                'feedback' => $summary,
            ])
        );
        $this->assertSame('ok', $response['status']);

        $feedbackList = \OmegaUp\Controllers\Run::apiGetSubmissionFeedback(
            new \OmegaUp\Request([
                'auth_token' => $teachingAssistantLogin->auth_token,
                'run_alias' => $guid,
            ])
        );
        $this->assertCount(1, $feedbackList);
        $this->assertNull($feedbackList[0]['range_bytes_start']);
        $this->assertSame($summary, $feedbackList[0]['feedback']);
        $this->assertSame(
            $teachingAssistant->username,
            $feedbackList[0]['author']
        );
    }

    public function testSetFeedbackListEmptyReviewFails() {
        [
            'assignmentAlias' => $assignmentAlias,
            'courseAlias' => $courseAlias,
            'guid' => $guid,
            'teachingAssistantLogin' => $teachingAssistantLogin,
        ] = $this->createCourseSubmissionWithTeachingAssistant();

        try {
            \OmegaUp\Controllers\Submission::apiSetFeedbackList(
                new \OmegaUp\Request([
                    'auth_token' => $teachingAssistantLogin->auth_token,
                    'guid' => $guid,
                    'course_alias' => $courseAlias,
                    'assignment_alias' => $assignmentAlias,
                    'feedback_list' => '[]',
                ])
            );
            $this->fail('Should have thrown an InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('invalidParameters', $e->getMessage());
            $this->assertSame('feedback_list', $e->parameter);
        }
    }

    public function testSetFeedbackListTwoConsecutiveReviews() {
        [
            'assignmentAlias' => $assignmentAlias,
            'courseAlias' => $courseAlias,
            'guid' => $guid,
            'teachingAssistant' => $teachingAssistant,
            'teachingAssistantLogin' => $teachingAssistantLogin,
            'admin' => $admin,
        ] = $this->createCourseSubmissionWithTeachingAssistant();

        \OmegaUp\Controllers\Submission::apiSetFeedbackList(
            new \OmegaUp\Request([
                'auth_token' => $teachingAssistantLogin->auth_token,
                'guid' => $guid,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'feedback_list' => json_encode([
                    ['lineNumber' => 1, 'feedback' => 'TA1 line 1'],
                    ['lineNumber' => 2, 'feedback' => 'TA1 line 2'],
                ]),
                'feedback' => 'TA1 summary',
            ])
        );

        [
            'identity' => $secondTeachingAssistant,
            'login' => $secondTeachingAssistantLogin,
        ] = $this->addTeachingAssistantToCourse($admin, $courseAlias);

        \OmegaUp\Controllers\Submission::apiSetFeedbackList(
            new \OmegaUp\Request([
                'auth_token' => $secondTeachingAssistantLogin->auth_token,
                'guid' => $guid,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'feedback_list' => json_encode([
                    ['lineNumber' => 2, 'feedback' => 'TA2 line 2'],
                    ['lineNumber' => 3, 'feedback' => 'TA2 line 3'],
                ]),
                'feedback' => 'TA2 summary',
            ])
        );

        $feedbackList = \OmegaUp\Controllers\Run::apiGetSubmissionFeedback(
            new \OmegaUp\Request([
                'auth_token' => $secondTeachingAssistantLogin->auth_token,
                'run_alias' => $guid,
            ])
        );

        $this->assertCount(4, $feedbackList);

        // The general summary is last-writer-wins.
        $generalFeedback = $this->generalFeedbackRows($feedbackList);
        $this->assertCount(1, $generalFeedback);
        $this->assertSame('TA2 summary', $generalFeedback[0]['feedback']);
        $this->assertSame(
            $secondTeachingAssistant->username,
            $generalFeedback[0]['author']
        );

        $lineFeedback = [];
        foreach ($feedbackList as $entry) {
            if (is_null($entry['range_bytes_start'])) {
                continue;
            }
            $lineFeedback[$entry['range_bytes_start']] = $entry;
        }
        $this->assertCount(3, $lineFeedback);
        $this->assertSame('TA1 line 1', $lineFeedback[1]['feedback']);
        $this->assertSame(
            $teachingAssistant->username,
            $lineFeedback[1]['author']
        );
        // A second review does not overwrite an existing line comment.
        $this->assertSame('TA1 line 2', $lineFeedback[2]['feedback']);
        $this->assertSame(
            $teachingAssistant->username,
            $lineFeedback[2]['author']
        );
        $this->assertSame('TA2 line 3', $lineFeedback[3]['feedback']);
        $this->assertSame(
            $secondTeachingAssistant->username,
            $lineFeedback[3]['author']
        );
    }

    public function testSetFeedbackListRollsBackOnFailure() {
        [
            'assignmentAlias' => $assignmentAlias,
            'courseAlias' => $courseAlias,
            'guid' => $guid,
            'student' => $student,
            'teachingAssistantLogin' => $teachingAssistantLogin,
        ] = $this->createCourseSubmissionWithTeachingAssistant();

        // range_bytes_start is a signed int column, so the second entry
        // overflows it and makes the insert fail after the first line
        // comment has already been written inside the transaction.
        try {
            \OmegaUp\Controllers\Submission::apiSetFeedbackList(
                new \OmegaUp\Request([
                    'auth_token' => $teachingAssistantLogin->auth_token,
                    'guid' => $guid,
                    'course_alias' => $courseAlias,
                    'assignment_alias' => $assignmentAlias,
                    'feedback_list' => json_encode([
                        ['lineNumber' => 1, 'feedback' => 'First line feedback'],
                        [
                            'lineNumber' => 3000000000,
                            'feedback' => 'Out of range line feedback',
                        ],
                    ]),
                    'feedback' => 'Summary',
                ])
            );
            $this->fail('Should have thrown a database exception');
        } catch (\Exception $e) {
            $this->assertInstanceOf(
                \OmegaUp\Exceptions\DatabaseOperationException::class,
                $e
            );
        }

        // Every test runs inside an outer transaction opened by
        // ControllerTestCase::setUp(), so rows written here are never
        // committed and stay visible to reads on the same connection.
        // What can be observed instead is that the failed call marked the
        // transaction as doomed and unwound its own transBegin, so the
        // outermost transEnd issues a ROLLBACK and nothing is persisted.
        $conn = \OmegaUp\MySQLConnection::getInstance();
        $reflection = new \ReflectionClass($conn);
        $transactionOk = $reflection->getProperty('_transactionOk');
        $transactionOk->setAccessible(true);
        $this->assertFalse($transactionOk->getValue($conn));
        $transactionCount = $reflection->getProperty('_transactionCount');
        $transactionCount->setAccessible(true);
        $this->assertSame(1, $transactionCount->getValue($conn));

        // Notifications are only sent after the transaction completes, so
        // a failed review must not notify the student.
        $notifications = \OmegaUp\DAO\Notifications::getUnreadNotifications(
            $student['user']
        );
        $this->assertCount(0, $notifications);
    }

    public function testSetFeedbackListWhitespaceOnlySummaryWithEmptyListFails() {
        [
            'assignmentAlias' => $assignmentAlias,
            'courseAlias' => $courseAlias,
            'guid' => $guid,
            'teachingAssistantLogin' => $teachingAssistantLogin,
        ] = $this->createCourseSubmissionWithTeachingAssistant();

        try {
            \OmegaUp\Controllers\Submission::apiSetFeedbackList(
                new \OmegaUp\Request([
                    'auth_token' => $teachingAssistantLogin->auth_token,
                    'guid' => $guid,
                    'course_alias' => $courseAlias,
                    'assignment_alias' => $assignmentAlias,
                    'feedback_list' => '[]',
                    'feedback' => '   ',
                ])
            );
            $this->fail('Should have thrown an InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('invalidParameters', $e->getMessage());
            $this->assertSame('feedback_list', $e->parameter);
        }
    }
}
