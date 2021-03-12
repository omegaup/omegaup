<?php

class ClarificationUpdateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic test for answer
     */
    public function testUpdateAnswer() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant who will submit the clarification
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create clarification
        $this->detourBroadcasterCalls($this->exactly(2));
        $clarificationData = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemData,
            $contestData,
            $identity
        );

        // Update answer
        $newAnswer = 'new answer';
        $response = \OmegaUp\Test\Factories\Clarification::answer(
            $clarificationData,
            $contestData,
            $newAnswer
        );

        // Get clarification from DB
        $clarification = \OmegaUp\DAO\Clarifications::getByPK(
            $clarificationData['response']['clarification_id']
        );

        // Validate that clarification stays the same
        $this->assertEquals(
            $clarificationData['request']['message'],
            $clarification->message
        );
        $this->assertEquals(
            $clarificationData['request']['public'] == '1',
            $clarification->public
        );

        // Validate our update
        $this->assertEquals($newAnswer, $clarification->answer);
    }

    public function testUpdateForCourseClarification() {
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

        $message = 'Test message';
        $clarification = \OmegaUp\Controllers\Clarification::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => self::login($student['identity'])->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'problem_alias' => $problemData['problem']->alias,
                'message' => $message,
            ])
        );

        // Update answer
        $newAnswer = 'new answer';
        \OmegaUp\Controllers\Clarification::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => self::login($admin['identity'])->auth_token,
                'answer' => $newAnswer,
                'clarification_id' => $clarification['clarification_id'],
                'public' => true,
            ])
        );

        $updatedClarification = \OmegaUp\DAO\Clarifications::getByPK(
            $clarification['clarification_id']
        );

        $this->assertEquals(
            $newAnswer,
            $updatedClarification->answer
        );
        $this->assertEquals(
            $clarification['message'],
            $updatedClarification->message,
        );
        $this->assertTrue($updatedClarification->public);

        // Verify if notification has been created
        $author = \Omegaup\DAO\Users::FindByUsername($clarification['author']);
        if (is_null($author)) {
            return;
        }
        $notifications = \OmegaUp\DAO\Notifications::getUnreadNotifications(
            $author
        );
        $this->assertCount(1, $notifications);

        $contents = json_decode($notifications[0]['contents'], true);
        $this->assertEquals(
            \OmegaUp\DAO\Notifications::COURSE_CLARIFICATION_RESPONSE,
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
}
