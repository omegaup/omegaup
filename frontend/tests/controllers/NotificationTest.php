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
}
