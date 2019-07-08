<?php

/**
 * Tests for NotificationController
 *
 * @author carlosabcs
 */

class NotificationTest extends OmegaupTestCase {
    public function testListUnreadNotifications() {
        $user = UserFactory::createUser();
        NotificationsDAO::create(new Notifications([
            'user_id' => $user->user_id,
            'read' => true,
            'contents' => json_encode(['type' => 'badge', 'badge' => 'testRead'])
        ]));
        NotificationsDAO::create(new Notifications([
            'user_id' => $user->user_id,
            'contents' => json_encode(['type' => 'badge', 'badge' => 'testUnread'])
        ]));

        // Get all unread notifications through API
        $login = self::login($user);
        $results = NotificationController::apiMyList(new Request([
            'auth_token' => $login->auth_token,
            'user' => $user,
        ]));
        $notifications = $results['notifications'];
        $this->assertEquals(1, sizeof($notifications));
        $this->assertEquals('testUnread', json_decode($notifications[0]['contents'])->badge);
    }

    public function testReadNotifications() {
        $user = UserFactory::createUser();
        NotificationsDAO::create(new Notifications([
            'user_id' => $user->user_id,
            'contents' => json_encode(['type' => 'badge', 'badge' => 'testUnread'])
        ]));
        NotificationsDAO::create(new Notifications([
            'user_id' => $user->user_id,
            'contents' => json_encode(['type' => 'badge', 'badge' => 'testUnread2'])
        ]));
        NotificationsDAO::create(new Notifications([
            'user_id' => $user->user_id,
            'contents' => json_encode(['type' => 'badge', 'badge' => 'testUnread3'])
        ]));

        // Get all unread notifications (3) for user
        $login = self::login($user);
        $results = NotificationController::apiMyList(new Request([
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
        $results = NotificationController::apiReadNotifications(new Request([
            'auth_token' => $login->auth_token,
            'user' => $user,
            'notifications' => $ids,
        ]));

        // Get all unread notifications (1) for user
        $results = NotificationController::apiMyList(new Request([
            'auth_token' => $login->auth_token,
            'user' => $user,
        ]));
        $notifications = $results['notifications'];
        $this->assertEquals(1, sizeof($notifications));
    }

    public function testReadNotificationsExceptions() {
        $user = UserFactory::createUser();
        $login = self::login($user);
        try {
            NotificationController::apiReadNotifications(new Request([
                'auth_token' => $login->auth_token,
                'user' => $user,
                'notifications' => [],
            ]));
            $this->fail('Should have thrown NotFoundException');
        } catch (NotFoundException $e) {
            $this->assertEquals($e->getMessage(), 'notificationIdsNotProvided');
        }
        try {
            NotificationController::apiReadNotifications(new Request([
                'auth_token' => $login->auth_token,
                'user' => $user,
                'notifications' => ['10'],
            ]));
            $this->fail('Should have thrown NotFoundException');
        } catch (NotFoundException $e) {
            $this->assertEquals($e->getMessage(), 'notificationDoesntExist');
        }
    }
}
