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
}
