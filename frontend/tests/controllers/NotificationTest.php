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
}
