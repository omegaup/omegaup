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
     * Creates a new notification for an arbitrary request for access from a
     * contest user
     */
    public static function createForArbitrateRequest(
        \OmegaUp\DAO\VO\Contests $contest,
        int $userId,
        bool $isAccepted
    ) {
        \OmegaUp\DAO\Notifications::create(
            new \OmegaUp\DAO\VO\Notifications([
                'user_id' => $userId,
                'contents' =>  json_encode(
                    [
                        'type' => (
                            $isAccepted ?
                            \OmegaUp\DAO\Notifications::CONTEST_REGISTRATION_ACCEPTED :
                            \OmegaUp\DAO\Notifications::CONTEST_REGISTRATION_REJECTED
                        ),
                        'body' => [
                            'localizationString' => (
                                $isAccepted ?
                                new \OmegaUp\TranslationString(
                                    'notificationContestRegisterationAccepted'
                                ) :
                                new \OmegaUp\TranslationString(
                                    'notificationContestRegisterationRejected'
                                )
                            ),
                            'localizationParams' => [
                                'contestTitle' => $contest->title,
                            ],
                            'url' => "/arena/{$contest->alias}/",
                            'iconUrl' => '/media/info.png',
                        ],
                    ]
                ),
            ])
        );
    }
    /**
     * @param list<string> $usersIds
     * @param string $contestTitle
     * @param list<string> $verificationCodes
     */
    public static function createNotificationsForNewContestCertificates(
        array $usersIds,
        string $contestTitle,
        array $verificationCodes
    ): void {
        foreach ($usersIds as $index => $userId) {
            \OmegaUp\DAO\Base\Notifications::create(
                new \OmegaUp\DAO\VO\Notifications([
                    'user_id' => $userId,
                    'contents' =>  json_encode(
                        [
                            'type' => \OmegaUp\DAO\Notifications::CERTIFICATE_AWARDED,
                            'body' => [
                                'localizationString' => new \OmegaUp\TranslationString(
                                    'notificationNewContestCertificate'
                                ),
                                'localizationParams' => [
                                    'contest_title' => $contestTitle,
                                ],
                                'url' => "/certificates/mine/#{$verificationCodes[$index]}",
                                'iconUrl' => '/media/info.png',
                            ]
                        ]
                    ),
                ])
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
}
