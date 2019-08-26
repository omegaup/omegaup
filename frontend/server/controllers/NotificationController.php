<?php

/**
 * BadgesController
 *
 * @author carlosabcs
 */
class NotificationController extends Controller {
    /**
     * Returns a list of unread notifications for user
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiMyList(\OmegaUp\Request $r) {
        self::authenticateRequest($r);
        return [
            'status' => 'ok',
            'notifications' => is_null($r->user) ?
                [] :
                NotificationsDAO::getUnreadNotifications($r->user),
        ];
    }

    /**
     * Updates notifications as read in database
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiReadNotifications(\OmegaUp\Request $r) {
        self::authenticateRequest($r, true /* requireMainUserIdentity */);
        if (empty($r['notifications'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('notificationIdsNotProvided');
        }
        foreach ($r['notifications'] as $id) {
            $notification = NotificationsDAO::getByPK($id);
            if (is_null($notification)) {
                throw new \OmegaUp\Exceptions\NotFoundException('notificationDoesntExist');
            }
            if ($notification->user_id !== $r->user->user_id) {
                throw new ForbiddenAccessException('userNotAllowed');
            }
            $notification->read = 1;
            NotificationsDAO::update($notification);
        }
        return [
            'status' => 'ok',
        ];
    }
}
