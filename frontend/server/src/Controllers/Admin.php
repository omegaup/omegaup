<?php

 namespace OmegaUp\Controllers;

 /**
  * Admin Controller
  *
  * @psalm-type MaintenanceMessage=array{message: string, type: string}
  * @psalm-type MaintenanceModeStatus=array{enabled: bool, message_es: null|string, message_en: null|string, message_pt: null|string, type: string}
  */
class Admin extends \OmegaUp\Controllers\Controller {
    const MAINTENANCE_MESSAGE_ES_KEY = 'system:maintenance_message_es';
    const MAINTENANCE_MESSAGE_EN_KEY = 'system:maintenance_message_en';
    const MAINTENANCE_MESSAGE_PT_KEY = 'system:maintenance_message_pt';
    const MAINTENANCE_ENABLED_KEY = 'system:maintenance_enabled';
    const MAINTENANCE_MESSAGE_TYPE_KEY = 'system:maintenance_message_type';

    const INFO = 0;
    const WARNING = 1;
    const ERROR = 2;

    const MAINTENANCE_MESSAGE_TYPES = [
        'info',
        'warning',
        'danger',
    ];

    /**
     * Get stats for an overall platform report.
     *
     * @return array{report: array{acceptedSubmissions: int, activeSchools: int, activeUsers: array<string, int>, courses: int, omiCourse: array{attemptedUsers: int, completedUsers: int, passedUsers: int}}}
     *
     * @omegaup-request-param int|null $end_time
     * @omegaup-request-param int|null $start_time
     */
    public static function apiPlatformReportStats(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Validators::validateOptionalNumber(
            $r['start_time'],
            'start_time'
        );
        \OmegaUp\Validators::validateOptionalNumber($r['end_time'], 'end_time');

        $startTime = empty($r['start_time']) ?
            strtotime('first day of this January') :
            intval($r['start_time']);
        $endTime = empty($r['end_time']) ?
            \OmegaUp\Time::get() :
            intval($r['end_time']);

        return [
            'report' => [
                'activeUsers' => array_merge(...array_map(
                    /**
                     * @param array{gender: string, users: int} $row
                     * @return array<string, int>
                     */
                    fn (array $row) => [$row['gender'] => $row['users']],
                    \OmegaUp\DAO\Identities::countActiveUsersByGender(
                        $startTime,
                        $endTime
                    )
                )),
                'acceptedSubmissions' => \OmegaUp\DAO\Submissions::countAcceptedSubmissions(
                    $startTime,
                    $endTime
                ),
                'activeSchools' => \OmegaUp\DAO\Schools::countActiveSchools(
                    $startTime,
                    $endTime
                ),
                'courses' => \OmegaUp\DAO\Courses::countCourses(
                    $startTime,
                    $endTime
                ),
                'omiCourse' => [
                    'attemptedUsers' => \OmegaUp\DAO\Courses::countAttemptedIdentities(
                        'Curso-OMI',
                        $startTime,
                        $endTime
                    ),
                    'passedUsers' => \OmegaUp\DAO\Courses::countCompletedIdentities(
                        'Curso-OMI',
                        0.7,
                        $startTime,
                        $endTime
                    ),
                    'completedUsers' => \OmegaUp\DAO\Courses::countCompletedIdentities(
                        'Curso-OMI',
                        1.0,
                        $startTime,
                        $endTime
                    ),
                ],
            ],
        ];
    }

    /**
     * Set maintenance mode
     *
     * @omegaup-request-param null|string $message_es
     * @omegaup-request-param null|string $message_en
     * @omegaup-request-param null|string $message_pt
     * @omegaup-request-param null|bool $enabled
     * @omegaup-request-param null|string $type
     *
     * @return array{status: string}
     */
    public static function apiSetMaintenanceMode(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $enabled = $r->ensureOptionalBool('enabled') ?? false;
        $messageEs = $r->ensureOptionalString('message_es') ?? '';
        $messageEn = $r->ensureOptionalString('message_en') ?? '';
        $messagePt = $r->ensureOptionalString('message_pt') ?? '';
        $type = $r->ensureOptionalEnum(
            'type',
            self::MAINTENANCE_MESSAGE_TYPES
        ) ?? self::MAINTENANCE_MESSAGE_TYPES[self::INFO];

        $cacheEnabled = new \OmegaUp\Cache(self::MAINTENANCE_ENABLED_KEY);
        $cacheMessageEs = new \OmegaUp\Cache(self::MAINTENANCE_MESSAGE_ES_KEY);
        $cacheMessageEn = new \OmegaUp\Cache(self::MAINTENANCE_MESSAGE_EN_KEY);
        $cacheMessagePt = new \OmegaUp\Cache(self::MAINTENANCE_MESSAGE_PT_KEY);
        $cacheMessageType = new \OmegaUp\Cache(
            self::MAINTENANCE_MESSAGE_TYPE_KEY
        );
        if ($enabled) {
            $cacheEnabled->set(value: true, timeout: 0); // No expiration
            $cacheMessageEs->set($messageEs, timeout: 0);
            $cacheMessageEn->set($messageEn, timeout: 0);
            $cacheMessagePt->set($messagePt, timeout: 0);

            // Store the index, not the string value
            $typeIndex = array_search(
                $type,
                self::MAINTENANCE_MESSAGE_TYPES,
                strict: true
            );
            $cacheMessageType->set(
                $typeIndex !== false ? $typeIndex : self::INFO,
                timeout: 0
            );
        } else {
            $cacheEnabled->delete();
            $cacheMessageEs->delete();
            $cacheMessageEn->delete();
            $cacheMessagePt->delete();
            $cacheMessageType->delete();
        }

        return ['status' => 'ok'];
    }

    /**
     * Get maintenance mode status
     *
     * @return MaintenanceModeStatus
     */
    public static function apiGetMaintenanceMode(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return self::getMaintenanceModeStatus();
    }

    /**
     * Get the message type from cache
     *
     * @return string
     */
    private static function getMessageTypeFromCache(): string {
        $cacheMessageType = new \OmegaUp\Cache(
            self::MAINTENANCE_MESSAGE_TYPE_KEY
        );
        $messageTypeIndex = $cacheMessageType->get();
        return (is_int(
            $messageTypeIndex
        ) && isset(
            self::MAINTENANCE_MESSAGE_TYPES[$messageTypeIndex]
        ))
            ? self::MAINTENANCE_MESSAGE_TYPES[$messageTypeIndex]
            : self::MAINTENANCE_MESSAGE_TYPES[self::INFO];
    }

    /**
     * Get maintenance mode status
     *
     * @return MaintenanceModeStatus
     */
    public static function getMaintenanceModeStatus(): array {
        $cacheEnabled = new \OmegaUp\Cache(self::MAINTENANCE_ENABLED_KEY);
        $enabled = boolval($cacheEnabled->get());

        $cacheMessageEs = new \OmegaUp\Cache(self::MAINTENANCE_MESSAGE_ES_KEY);
        $messageEs = is_null(
            $cacheMessageEs->get()
        ) ? null : strval(
            $cacheMessageEs->get()
        );

        $cacheMessageEn = new \OmegaUp\Cache(self::MAINTENANCE_MESSAGE_EN_KEY);
        $messageEn = is_null(
            $cacheMessageEn->get()
        ) ? null : strval(
            $cacheMessageEn->get()
        );

        $cacheMessagePt = new \OmegaUp\Cache(self::MAINTENANCE_MESSAGE_PT_KEY);
        $messagePt = is_null(
            $cacheMessagePt->get()
        ) ? null : strval(
            $cacheMessagePt->get()
        );

        $type = self::getMessageTypeFromCache();

        return [
            'enabled' => $enabled,
            'message_es' => $messageEs,
            'message_en' => $messageEn,
            'message_pt' => $messagePt,
            'type' => $type,
        ];
    }

    /**
     * Get maintenance message for public display in specific language
     *
     * @param null|string $lang Language code (es, en, pt). If null, defaults to Spanish.
     * @return null|MaintenanceMessage
     */
    public static function getMaintenanceMessage(?string $lang = null): ?array {
        $status = self::getMaintenanceModeStatus();

        if (!$status['enabled']) {
            return null;
        }

        // Select message based on language
        $message = '';
        if ($lang === 'en' || $lang === 'pseudo') {
            $message = $status['message_en'] ?? '';
        } elseif ($lang === 'pt') {
            $message = $status['message_pt'] ?? '';
        } else {
            // Default to Spanish
            $message = $status['message_es'] ?? '';
        }

        return [
            'message' => $message,
            'type' => $status['type'],
        ];
    }
}
