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

    /**
     * Get system settings for admins
     *
     * @return array{status: string, settings: array{ephemeralGraderEnabled: bool}}
     */
    public static function apiGetSystemSettings(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

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
     * Update system settings
     *
     * @return array{status: string}
     *
     * @omegaup-request-param bool|null $ephemeral_grader_enabled
     */
    public static function apiUpdateSystemSettings(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if (isset($r['ephemeral_grader_enabled'])) {
            $rawValue = $r['ephemeral_grader_enabled'];

            if (is_string($rawValue)) {
                $ephemeralGraderEnabled = in_array(
                    strtolower($rawValue),
                    ['1', 'true', 'yes', 'on'],
                    true
                );
            } elseif (is_numeric($rawValue)) {
                $ephemeralGraderEnabled = intval($rawValue) === 1;
            } else {
                $ephemeralGraderEnabled = boolval($rawValue);
            }

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
     * @return array{entrypoint: string, templateProperties: array{payload: AdminSettingsPayload, title: \OmegaUp\TranslationString}}
     *
     * @psalm-type AdminSettingsPayload=array{}
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
                    'omegaupTitleAdminUsers'
                ),
                'payload' => [],
            ],
        ];
    }
}
