<?php

/**
 * Tests for NotificationController
 *
 * @author carlosabcs
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
        $this->assertEquals(1, sizeof($notifications));
        $this->assertEquals(
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
            $this->assertEquals($e->getMessage(), 'parameterEmpty');
            $this->assertEquals($e->parameter, 'notifications');
        }
        try {
            \OmegaUp\Controllers\Notification::apiReadNotifications(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'user' => $user,
                'notifications' => ['10'],
            ]));
            $this->fail('Should have thrown NotFoundException');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertEquals($e->getMessage(), 'notificationNotFound');
        }
    }

    public function testReadNotificationsForbbidenAccessException() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
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
            $this->assertEquals($e->getMessage(), 'userNotAllowed');
        }
    }
}
