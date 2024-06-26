<?php

 namespace OmegaUp\Controllers;

/**
 * BadgesController
 *
 * @psalm-type NotificationContents=array{type: string, badge?: string, message?: string, status?: string, url?: string, body?: array{localizationString: string, localizationParams: list<string, string>, url: string, iconUrl: string}}
 * @psalm-type Notification=array{contents: NotificationContents, notification_id: int, timestamp: \OmegaUp\Timestamp}
 */
class Notification extends \OmegaUp\Controllers\Controller {
    /**
     * @param list<int> $usersIds
     * @param string $contestTitle
     * @param list<string> $verificationCodes
     */
    public static function createNotificationsForNewContestCertificates(
        array $usersIds,
        string $contestTitle,
        array $verificationCodes
    ): void {
        foreach ($usersIds as $index => $userId) {
            \OmegaUp\Controllers\Notification::setCommonNotification(
                [$userId],
                new \OmegaUp\TranslationString(
                    'notificationNewContestCertificate'
                ),
                \OmegaUp\DAO\Notifications::CERTIFICATE_AWARDED,
                "/certificates/mine/#{$verificationCodes[$index]}",
                [
                    'contest_title' => $contestTitle,
                ]
            );
        }
    }

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
                associative: true,
            );
            $notifications[] = $notification;
        }
        $notifications = array_reverse($notifications);
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
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'notifications'
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
                    'notificationNotFound'
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

    /**
     * This function creates a new notification
     *
     * @param list<int> $userIds
     */
    public static function setNotification(
        array $userIds,
        string $contents
    ): void {
        foreach ($userIds as $userId) {
            \OmegaUp\DAO\Notifications::create(
                new \OmegaUp\DAO\VO\Notifications([
                    'user_id' => $userId,
                    'contents' => $contents,
                ])
            );
        }
    }

    /**
     * This function helps to create a new notification for the clarifications
     *
     * @param list<int> $userIds
     * @param array{contestAlias?: null|string, courseName?: null|string, problemAlias?: null|string} $localizationParams
     */
    public static function setCommonNotification(
        array $userIds,
        \OmegaUp\TranslationString $localizationString,
        string $notificationType,
        string $url,
        array $localizationParams
    ): void {
        self::setNotification(
            userIds: $userIds,
            contents: json_encode(
                [
                    'type' => $notificationType,
                    'body' => [
                        'localizationString' => $localizationString,
                        'localizationParams' => $localizationParams,
                        'url' => $url,
                        'iconUrl' => '/media/info.png',
                    ]
                ]
            )
        );
    }
}
