<?php

 namespace OmegaUp\Controllers;

 /**
  * Admin Controller
  *
  * @psalm-type MaintenanceMessage=array{message: string, type: string}
  * @psalm-type MessageLanguages=array{es: string, en: string, pt: string}
  * @psalm-type PredefinedTemplate=array{id: string, title: MessageLanguages, message: MessageLanguages, type: string}
  * @psalm-type MaintenanceModeStatus=array{enabled: bool, message_es: null|string, message_en: null|string, message_pt: null|string, type: string}
  */
class Admin extends \OmegaUp\Controllers\Controller {
    const MAINTENANCE_MESSAGE_ES_KEY = 'maintenance_message_es';
    const MAINTENANCE_MESSAGE_EN_KEY = 'maintenance_message_en';
    const MAINTENANCE_MESSAGE_PT_KEY = 'maintenance_message_pt';
    const MAINTENANCE_ENABLED_KEY = 'maintenance_enabled';
    const MAINTENANCE_MESSAGE_TYPE_KEY = 'maintenance_message_type';

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

        try {
            \OmegaUp\DAO\DAO::transBegin();
            \OmegaUp\DAO\SystemSettings::setBooleanSetting(
                self::MAINTENANCE_ENABLED_KEY,
                $enabled
            );
            if ($enabled) {
                \OmegaUp\DAO\SystemSettings::setStringSetting(
                    self::MAINTENANCE_MESSAGE_ES_KEY,
                    $messageEs
                );
                \OmegaUp\DAO\SystemSettings::setStringSetting(
                    self::MAINTENANCE_MESSAGE_EN_KEY,
                    $messageEn
                );
                \OmegaUp\DAO\SystemSettings::setStringSetting(
                    self::MAINTENANCE_MESSAGE_PT_KEY,
                    $messagePt
                );
                \OmegaUp\DAO\SystemSettings::setStringSetting(
                    self::MAINTENANCE_MESSAGE_TYPE_KEY,
                    $type
                );
            } else {
                \OmegaUp\DAO\SystemSettings::setStringSetting(
                    self::MAINTENANCE_MESSAGE_ES_KEY,
                    ''
                );
                \OmegaUp\DAO\SystemSettings::setStringSetting(
                    self::MAINTENANCE_MESSAGE_EN_KEY,
                    ''
                );
                \OmegaUp\DAO\SystemSettings::setStringSetting(
                    self::MAINTENANCE_MESSAGE_PT_KEY,
                    ''
                );
                \OmegaUp\DAO\SystemSettings::setStringSetting(
                    self::MAINTENANCE_MESSAGE_TYPE_KEY,
                    self::MAINTENANCE_MESSAGE_TYPES[self::INFO]
                );
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
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
     * Get the message type from system settings
     *
     * @return string
     */
    private static function getMessageTypeFromSettings(): string {
        $messageType = \OmegaUp\DAO\SystemSettings::getStringSetting(
            self::MAINTENANCE_MESSAGE_TYPE_KEY,
            self::MAINTENANCE_MESSAGE_TYPES[self::INFO]
        );
        return in_array(
            $messageType,
            self::MAINTENANCE_MESSAGE_TYPES,
            strict: true
        ) ? $messageType : self::MAINTENANCE_MESSAGE_TYPES[self::INFO];
    }

    /**
     * Get maintenance mode status
     *
     * @return MaintenanceModeStatus
     */
    public static function getMaintenanceModeStatus(): array {
        $enabled = \OmegaUp\DAO\SystemSettings::getBooleanSetting(
            self::MAINTENANCE_ENABLED_KEY,
            false
        );
        $messageEs = \OmegaUp\DAO\SystemSettings::getStringSetting(
            self::MAINTENANCE_MESSAGE_ES_KEY
        );
        $messageEn = \OmegaUp\DAO\SystemSettings::getStringSetting(
            self::MAINTENANCE_MESSAGE_EN_KEY
        );
        $messagePt = \OmegaUp\DAO\SystemSettings::getStringSetting(
            self::MAINTENANCE_MESSAGE_PT_KEY
        );

        $type = self::getMessageTypeFromSettings();

        return [
            'enabled' => $enabled,
            'message_es' => $messageEs,
            'message_en' => $messageEn,
            'message_pt' => $messagePt,
            'type' => $type,
        ];
    }

    /**
     * Get predefined maintenance templates for public display
     *
     * @return list<PredefinedTemplate>
     */
    public static function getMaintenancePredefinedTemplates(): array {
        return [
            [
                'id' => 'scheduled_maintenance',
                'title' => [
                    'es' => 'Mantenimiento programado',
                    'en' => 'Scheduled Maintenance',
                    'pt' => 'Manutenção Programada',
                ],
                'message' => [
                    'es' => 'Estamos realizando mantenimiento programado. Esperamos volver a estar en línea pronto. Gracias por tu paciencia.',
                    'en' => 'We are performing scheduled maintenance. We expect to be back online soon. Thank you for your patience.',
                    'pt' => 'Estamos realizando manutenção programada. Esperamos voltar a ficar online em breve. Obrigado pela sua paciência.',
                ],
                'type' => self::MAINTENANCE_MESSAGE_TYPES[self::INFO],
            ],
            [
                'id' => 'grader_unavailable',
                'title' => [
                    'es' => 'Sistema de evaluación temporalmente no disponible',
                    'en' => 'Grader system temporarily unavailable',
                    'pt' => 'Sistema de avaliação temporariamente indisponível',
                ],
                'message' => [
                    'es' => 'Nuestro equipo técnico ha sido notificado.<br />Por favor, guarda tu código localmente y vuelve a intentarlo más tarde. Gracias por tu comprensión.',
                    'en' => 'Our technical team has been notified.<br />Please save your code locally and try again later. Thank you for your understanding.',
                    'pt' => 'Nossa equipe técnica foi notificada.<br />Por favor, salve seu código localmente e tente novamente mais tarde. Obrigado pela sua compreensão.',
                ],
                'type' => self::MAINTENANCE_MESSAGE_TYPES[self::WARNING],
            ],
            [
                'id' => 'service_unavailable',
                'title' => [
                    'es' => 'Servicio temporalmente no disponible',
                    'en' => 'Service temporarily unavailable',
                    'pt' => 'Serviço temporariamente indisponível',
                ],
                'message' => [
                    'es' => 'Estamos experimentando problemas técnicos con nuestros proveedores de infraestructura.',
                    'en' => 'We are experiencing technical issues with our infrastructure providers.',
                    'pt' => 'Estamos enfrentando problemas técnicos com nossos provedores de infraestrutura.',
                ],
                'type' => self::MAINTENANCE_MESSAGE_TYPES[self::ERROR],
            ]
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

    /**
     * Gets the current system settings. Only available to system admins.
     *
     * @return array{settings: array{ephemeralGraderEnabled: bool}, status: string}
     */
    public static function apiGetSystemSettings(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'status' => 'ok',
            'settings' => [
                'ephemeralGraderEnabled' => \OmegaUp\DAO\SystemSettings::getBooleanSetting(
                    'ephemeral_grader_enabled',
                    true
                ),
            ],
        ];
    }

    /**
     * Updates system settings. Only available to system admins.
     *
     * @omegaup-request-param null|bool $ephemeral_grader_enabled
     *
     * @return array{status: string}
     */
    public static function apiUpdateSystemSettings(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $ephemeralGraderEnabled = $r->ensureOptionalBool(
            'ephemeral_grader_enabled'
        );
        if (!is_null($ephemeralGraderEnabled)) {
            \OmegaUp\DAO\SystemSettings::setBooleanSetting(
                'ephemeral_grader_enabled',
                $ephemeralGraderEnabled
            );
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: array<empty, empty>, title: \OmegaUp\TranslationString}}
     */
    public static function getSettingsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'entrypoint' => 'admin_settings',
            'templateProperties' => [
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleAdminSettings'
                ),
                'payload' => [],
            ],
        ];
    }
}
