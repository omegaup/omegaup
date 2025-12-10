<?php
/**
 * Tests for NotificationController
 */

class NotificationTest extends \OmegaUp\Test\ControllerTestCase {
    public function testListUnreadNotifications() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\DAO\Notifications::create(new \OmegaUp\DAO\VO\Notifications([
            'user_id' => $user->user_id,
            'read' => true,
            'contents' => json_encode(
                ['type' => 'badge', 'badge' => 'testRead']
            )
        ]));
        \OmegaUp\DAO\Notifications::create(new \OmegaUp\DAO\VO\Notifications([
            'user_id' => $user->user_id,
            'contents' => json_encode(
                ['type' => 'badge', 'badge' => 'testUnread']
            )
        ]));

        // Get all unread notifications through API
        $login = self::login($identity);
        $results = \OmegaUp\Controllers\Notification::apiMyList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'user' => $user,
        ]));
        $notifications = $results['notifications'];
        $this->assertSame(1, sizeof($notifications));
        $this->assertSame(
            'testUnread',
            $notifications[0]['contents']['badge']
        );
    }

    public function testReadNotifications() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\DAO\Notifications::create(new \OmegaUp\DAO\VO\Notifications([
            'user_id' => $user->user_id,
            'contents' => json_encode(
                ['type' => 'badge', 'badge' => 'testUnread']
            )
        ]));
        \OmegaUp\DAO\Notifications::create(new \OmegaUp\DAO\VO\Notifications([
            'user_id' => $user->user_id,
            'contents' => json_encode(
                ['type' => 'badge', 'badge' => 'testUnread2']
            )
        ]));
        \OmegaUp\DAO\Notifications::create(new \OmegaUp\DAO\VO\Notifications([
            'user_id' => $user->user_id,
            'contents' => json_encode(
                ['type' => 'badge', 'badge' => 'testUnread3']
            )
        ]));

        // Get all unread notifications (3) for user
        $login = self::login($identity);
        $results = \OmegaUp\Controllers\Notification::apiMyList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'user' => $user,
        ]));
        $notifications = $results['notifications'];

        $ids = [];
        // Ignore the first unread notification
        for ($i = 1; $i < sizeof($notifications); $i++) {
            $ids[] = $notifications[$i]['notification_id'];
        }

        // Mark notifications as read
        $results = \OmegaUp\Controllers\Notification::apiReadNotifications(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'user' => $user,
            'notifications' => $ids,
        ]));

        // Get all unread notifications (1) for user
        $results = \OmegaUp\Controllers\Notification::apiMyList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'user' => $user,
        ]));
        $notifications = $results['notifications'];
        $this->assertCount(1, $notifications);
    }

    public function testReadNotificationsExceptions() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\Notification::apiReadNotifications(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'user' => $user,
                'notifications' => [],
            ]));
            $this->fail('Should have thrown NotFoundException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame($e->getMessage(), 'parameterEmpty');
            $this->assertSame($e->parameter, 'notifications');
        }
        try {
            \OmegaUp\Controllers\Notification::apiReadNotifications(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'user' => $user,
                'notifications' => ['10'],
            ]));
            $this->fail('Should have thrown NotFoundException');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame($e->getMessage(), 'notificationNotFound');
        }
    }

    public function testReadNotificationsForbbidenAccessException() {
        ['user' => $user] = \OmegaUp\Test\Factories\User::createUser();
        $notification = new \OmegaUp\DAO\VO\Notifications([
            'user_id' => $user->user_id,
            'contents' => json_encode(
                ['type' => 'badge', 'badge' => 'testUnread']
            )
        ]);
        \OmegaUp\DAO\Notifications::create($notification);

        ['user' => $maliciousUser, 'identity' => $maliciousIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($maliciousIdentity);

        try {
            \OmegaUp\Controllers\Notification::apiReadNotifications(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'user' => $maliciousUser,
                'notifications' => [$notification->notification_id],
            ]));
            $this->fail('Should have thrown ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'userNotAllowed');
        }
    }

    public function testCreateNotificationsForNewContestCertificates() {
        $n = 5;
        $users = [];
        $usersIds = [];
        for ($i = 0; $i < $n; $i++) {
            ['user' => $users[$i]] = \OmegaUp\Test\Factories\User::createUser();
            $usersIds[$i] = $users[$i]->user_id;
        }
        $contestTitle = 'Test';
        $verificationCodes = ['AG8XOPS89L', 'H5J8K9K8K2', 'PF2Y9SPE25', 'EOR5KF9F0L', 'FIR93E22E5'];

        $notifications = \OmegaUp\DAO\Notifications::getAll();
        $this->assertCount(0, $notifications);

        \OmegaUp\Controllers\Notification::createNotificationsForNewContestCertificates(
            $usersIds,
            $contestTitle,
            $verificationCodes
        );
        $notifications = \OmegaUp\DAO\Notifications::getAll();
        $this->assertCount($n, $notifications);
    }

    public function testNotificationsForContestClarification() {
        // Create a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get one problem into the contest
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        ['identity' => $contestant] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData,
            $contestant
        );

        // User creates a clarification
        $clarification = \OmegaUp\Test\Factories\Clarification::createClarification(
            $problemData,
            $contestData,
            $contestant
        )['response'];

        // Get all unread notifications through API
        $login = self::login($contestData['director']);
        $notifications = \OmegaUp\Controllers\Notification::apiMyList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['notifications'];

        $this->assertCount(1, $notifications);

        $this->assertSame(
            \OmegaUp\DAO\Notifications::CONTEST_CLARIFICATION_REQUEST,
            $notifications[0]['contents']['type']
        );
        $translation = new \OmegaUp\TranslationString(
            'notificationContestClarificationRequest'
        );
        $this->assertSame(
            $translation->message,
            $notifications[0]['contents']['body']['localizationString']
        );
        $this->assertSame(
            "/arena/{$contestData['contest']->alias}/#problems/{$problemData['problem']->alias}/",
            $notifications[0]['contents']['body']['url']
        );
        $this->assertSame(
            [
                'problemAlias' => $problemData['problem']->alias,
                'contestAlias' => $contestData['contest']->alias,
            ],
            $notifications[0]['contents']['body']['localizationParams']
        );

        // Admin replies the clarification
        \OmegaUp\Controllers\Clarification::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'answer' => 'The response',
                'clarification_id' => $clarification['clarification_id'],
                'public' => true,
            ])
        );

        // Get all unread notifications through API
        $login = self::login($contestant);

        $notifications = \OmegaUp\Controllers\Notification::apiMyList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['notifications'];

        $this->assertCount(1, $notifications);

        $this->assertSame(
            \OmegaUp\DAO\Notifications::CONTEST_CLARIFICATION_RESPONSE,
            $notifications[0]['contents']['type']
        );
        $translation = new \OmegaUp\TranslationString(
            'notificationContestClarificationResponse'
        );
        $this->assertSame(
            $translation->message,
            $notifications[0]['contents']['body']['localizationString']
        );
        $this->assertSame(
            "/arena/{$contestData['contest']->alias}/#problems/{$problemData['problem']->alias}/",
            $notifications[0]['contents']['body']['url']
        );
        $this->assertSame(
            [
                'problemAlias' => $problemData['problem']->alias,
                'contestAlias' => $contestData['contest']->alias,
            ],
            $notifications[0]['contents']['body']['localizationParams']
        );
    }

    public function testNotificationsForCourseClarification() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        $login = self::login($courseData['admin']);

        // Get one problem into the course
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [$problemData]
        )[0];

        $student = \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData
        );

        $studentLogin = self::login($student);
        [
            'clarification_id' => $clarificationId,
        ] = \OmegaUp\Controllers\Clarification::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'problem_alias' => $problemData['problem']->alias,
                'message' => 'Test message',
            ])
        );

        // Get all unread notifications through API
        $login = self::login($courseData['admin']);
        $notifications = \OmegaUp\Controllers\Notification::apiMyList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['notifications'];

        $this->assertCount(1, $notifications);

        $this->assertSame(
            \OmegaUp\DAO\Notifications::COURSE_CLARIFICATION_REQUEST,
            $notifications[0]['contents']['type']
        );
        $translation = new \OmegaUp\TranslationString(
            'notificationCourseClarificationRequest'
        );
        $this->assertSame(
            $translation->message,
            $notifications[0]['contents']['body']['localizationString']
        );
        $this->assertSame(
            "/course/{$courseData['course_alias']}/assignment/{$courseData['assignment_alias']}/#problems/{$problemData['problem']->alias}/",
            $notifications[0]['contents']['body']['url']
        );
        $this->assertSame(
            [
                'problemAlias' => $problemData['problem']->alias,
                'courseName' => $courseData['course']->name,
            ],
            $notifications[0]['contents']['body']['localizationParams']
        );

        // Admin replies the clarification
        \OmegaUp\Controllers\Clarification::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'answer' => 'The response',
                'clarification_id' => $clarificationId,
                'public' => true,
            ])
        );

        // Get all unread notifications through API
        //$login = self::login($contestant);

        $notifications = \OmegaUp\Controllers\Notification::apiMyList(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
            ])
        )['notifications'];

        $this->assertCount(1, $notifications);

        $this->assertSame(
            \OmegaUp\DAO\Notifications::COURSE_CLARIFICATION_RESPONSE,
            $notifications[0]['contents']['type']
        );
        $translation = new \OmegaUp\TranslationString(
            'notificationCourseClarificationResponse'
        );
        $this->assertSame(
            $translation->message,
            $notifications[0]['contents']['body']['localizationString']
        );
        $this->assertSame(
            "/course/{$courseData['course_alias']}/assignment/{$courseData['assignment_alias']}/#problems/{$problemData['problem']->alias}/",
            $notifications[0]['contents']['body']['url']
        );
        $this->assertSame(
            [
                'problemAlias' => $problemData['problem']->alias,
                'courseName' => $courseData['course']->name,
            ],
            $notifications[0]['contents']['body']['localizationParams']
        );
    }
}
