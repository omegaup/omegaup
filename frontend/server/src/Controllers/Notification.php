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
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiMyList(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        return [
            'status' => 'ok',
            'notifications' => is_null($r->user) ?
                [] :
                \OmegaUp\DAO\Notifications::getUnreadNotifications($r->user),
        ];
    }

    /**
     * Updates notifications as read in database
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiReadNotifications(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        if (empty($r['notifications'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('notificationIdsNotProvided');
        }
        foreach ($r['notifications'] as $id) {
            $notification = \OmegaUp\DAO\Notifications::getByPK($id);
            if (is_null($notification)) {
                throw new \OmegaUp\Exceptions\NotFoundException('notificationDoesntExist');
            }
            if ($notification->user_id !== $r->user->user_id) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
            }
            $notification->read = 1;
            \OmegaUp\DAO\Notifications::update($notification);
        }
        return [
            'status' => 'ok',
        ];
    }
}
