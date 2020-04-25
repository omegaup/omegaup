<?php

 namespace OmegaUp\Controllers;

/**
 * BadgesController
 *
 * @psalm-type NotificationContents=array{type: string, badge?: string, message?: string, status?: string}
 * @psalm-type Notification=array{contents: NotificationContents, notification_id: int, timestamp: \OmegaUp\Timestamp}
 */
class Notification extends \OmegaUp\Controllers\Controller {
    /**
     * Returns a list of unread notifications for user
     *
     * @return array{notifications: list<Notification>}
     */
    public static function apiMyList(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        /** @var list<Notification> */
        $notifications = [];
        foreach (
            is_null($r->user) ?
            [] :
            \OmegaUp\DAO\Notifications::getUnreadNotifications($r->user) as $notification
        ) {
            /** @var NotificationContents */
            $notification['contents'] = json_decode(
                $notification['contents'],
                /*$assoc=*/true
            );
            $notifications[] = $notification;
        }
        return [
            'notifications' => $notifications,
        ];
    }

    /**
     * Updates notifications as read in database
     *
     * @omegaup-request-param mixed $notifications
     *
     * @return array{status: string}
     */
    public static function apiReadNotifications(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        if (empty($r['notifications'])) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'notificationIdsNotProvided'
            );
        }
        $notifications = [];
        if (is_string($r['notifications'])) {
            foreach (explode(',', $r['notifications']) as $id) {
                $notifications[] = intval($id);
            }
        } elseif (is_array($r['notifications'])) {
            /** @var string $id */
            foreach ($r['notifications'] as $id) {
                $notifications[] = intval($id);
            }
        }
        foreach ($notifications as $id) {
            $notification = \OmegaUp\DAO\Notifications::getByPK($id);
            if (is_null($notification)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'notificationDoesntExist'
                );
            }
            if ($notification->user_id !== $r->user->user_id) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'userNotAllowed'
                );
            }
            $notification->read = true;
            \OmegaUp\DAO\Notifications::update($notification);
        }
        return [
            'status' => 'ok',
        ];
    }
}
