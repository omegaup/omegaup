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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiMyList(Request $r) {
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiReadNotifications(Request $r) {
        self::authenticateRequest($r);
        if (empty($r['notifications'])) {
            throw new NotFoundException('notificationIdsNotProvided');
        }
        foreach ($r['notifications'] as $id) {
            $notification = NotificationsDAO::getByPK($id);
            if ($notification === null) {
                throw new NotFoundException('notificationDoesntExist');
            }
            $notification->read = 1;
            NotificationsDAO::update($notification);
        }
        return [
            'status' => 'ok',
        ];
    }
}
