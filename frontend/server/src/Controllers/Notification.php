<?php

 namespace OmegaUp\Controllers;

/**
 * BadgesController
 *
 * @author carlosabcs
 */
class Notification extends \OmegaUp\Controllers\Controller {
    /**
     * Returns a list of unread notifications for user
     *
     * @return array{notifications: list<array{contents: string, notification_id: int, timestamp: int}>}
     */
    public static function apiMyList(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        return [
            'notifications' => is_null($r->user) ?
                [] :
                \OmegaUp\DAO\Notifications::getUnreadNotifications($r->user),
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
