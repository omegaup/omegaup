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

    //TODO: apiReadNotifications
}
