<?php

 namespace OmegaUp\Controllers;

class Admin extends \OmegaUp\Controllers\Controller {
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

    const MAINTENANCE_MESSAGE_ES_KEY = 'system:maintenance_message_es';
    const MAINTENANCE_MESSAGE_EN_KEY = 'system:maintenance_message_en';
    const MAINTENANCE_MESSAGE_PT_KEY = 'system:maintenance_message_pt';
    const MAINTENANCE_ENABLED_KEY = 'system:maintenance_enabled';
    const MAINTENANCE_MESSAGE_TYPE_KEY = 'system:maintenance_message_type';

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
        $type = $r->ensureOptionalString('type') ?? 'info';

        if ($enabled) {
            $cacheEnabled = new \OmegaUp\Cache(self::MAINTENANCE_ENABLED_KEY);
            $cacheEnabled->set(true, 0); // No expiration

            $cacheMessageEs = new \OmegaUp\Cache(
                self::MAINTENANCE_MESSAGE_ES_KEY
            );
            $cacheMessageEs->set($messageEs, 0);

            $cacheMessageEn = new \OmegaUp\Cache(
                self::MAINTENANCE_MESSAGE_EN_KEY
            );
            $cacheMessageEn->set($messageEn, 0);

            $cacheMessagePt = new \OmegaUp\Cache(
                self::MAINTENANCE_MESSAGE_PT_KEY
            );
            $cacheMessagePt->set($messagePt, 0);

            $cacheMessageType = new \OmegaUp\Cache(
                self::MAINTENANCE_MESSAGE_TYPE_KEY
            );
            $cacheMessageType->set($type, 0);
        } else {
            $cacheEnabled = new \OmegaUp\Cache(self::MAINTENANCE_ENABLED_KEY);
            $cacheEnabled->delete();

            $cacheMessageEs = new \OmegaUp\Cache(
                self::MAINTENANCE_MESSAGE_ES_KEY
            );
            $cacheMessageEs->delete();

            $cacheMessageEn = new \OmegaUp\Cache(
                self::MAINTENANCE_MESSAGE_EN_KEY
            );
            $cacheMessageEn->delete();

            $cacheMessagePt = new \OmegaUp\Cache(
                self::MAINTENANCE_MESSAGE_PT_KEY
            );
            $cacheMessagePt->delete();

            $cacheMessageType = new \OmegaUp\Cache(
                self::MAINTENANCE_MESSAGE_TYPE_KEY
            );
            $cacheMessageType->delete();
        }

        return ['status' => 'ok'];
    }

    /**
     * Get maintenance mode status
     *
     * @return array{enabled: bool, message: null|string}
     */
    public static function apiGetMaintenanceMode(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return self::getMaintenanceModeStatus();
    }

    /**
     * Get maintenance mode status
     *
     * @return array{enabled: bool, message_es: null|string, message_en: null|string, message_pt: null|string, type: string}
     */
    public static function getMaintenanceModeStatus(): array {
        $cacheEnabled = new \OmegaUp\Cache(self::MAINTENANCE_ENABLED_KEY);
        $enabled = boolval($cacheEnabled->get());

        $cacheMessageEs = new \OmegaUp\Cache(self::MAINTENANCE_MESSAGE_ES_KEY);
        $messageEs = $cacheMessageEs->get();

        $cacheMessageEn = new \OmegaUp\Cache(self::MAINTENANCE_MESSAGE_EN_KEY);
        $messageEn = $cacheMessageEn->get();

        $cacheMessagePt = new \OmegaUp\Cache(self::MAINTENANCE_MESSAGE_PT_KEY);
        $messagePt = $cacheMessagePt->get();

        $cacheMessageType = new \OmegaUp\Cache(
            self::MAINTENANCE_MESSAGE_TYPE_KEY
        );
        $type = $cacheMessageType->get() ?? 'info';

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
     * @return null|array{message: string, type: string}
     */
    public static function getMaintenanceMessage(?string $lang = null): ?array {
        $cacheEnabled = new \OmegaUp\Cache(self::MAINTENANCE_ENABLED_KEY);
        $enabled = $cacheEnabled->get();

        if (!$enabled) {
            return null;
        }

        // Default to Spanish if no language specified
        if (is_null($lang) || $lang === 'es') {
            $cacheMessage = new \OmegaUp\Cache(
                self::MAINTENANCE_MESSAGE_ES_KEY
            );
        } elseif ($lang === 'en' || $lang === 'pseudo') {
            $cacheMessage = new \OmegaUp\Cache(
                self::MAINTENANCE_MESSAGE_EN_KEY
            );
        } elseif ($lang === 'pt') {
            $cacheMessage = new \OmegaUp\Cache(
                self::MAINTENANCE_MESSAGE_PT_KEY
            );
        } else {
            $cacheMessage = new \OmegaUp\Cache(
                self::MAINTENANCE_MESSAGE_ES_KEY
            );
        }

        $cacheMessageType = new \OmegaUp\Cache(
            self::MAINTENANCE_MESSAGE_TYPE_KEY
        );
        $type = $cacheMessageType->get() ?? 'info';

        // Map type to Bootstrap alert classes
        $alertClass = match ($type) {
            'error' => 'danger',
            'warning' => 'warning',
            'info', 'anuncio' => 'info',
            default => 'info',
        };

        return [
            'message' => $cacheMessage->get() ?? '',
            'type' => $alertClass,
        ];
    }
}
