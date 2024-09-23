<?php

 namespace OmegaUp\Controllers;

 /**
 * Admin Controller
 *
 * @psalm-type ReportStatsPayload=array{report: array{acceptedSubmissions: int, activeSchools: int, activeUsers: array<string, int>, courses: int, omiCourse: array{attemptedUsers: int, completedUsers: int, passedUsers: int}}}
 */
class Admin extends \OmegaUp\Controllers\Controller {
    /**
     * Get stats for an overall platform report.
     *
     * If start_time and end_time are not provided, the report will cover the
     * current year.
     *
     * If start_time is not provided, it will be set to the first day of the
     * current year.
     *
     * If end_time is not provided, it will be set to the current time.
     *
     * If both start_time and end_time are provided, the report will cover the
     * range [start_time, end_time].
     *
     * For a full time range report, set start_time to 0 and end_time to the
     * current time. Alternative, end_time can be left unset.
     *
     * @return ReportStatsPayload
     *
     * @omegaup-request-param int|null $end_time
     * @omegaup-request-param int|null $start_time
     */
    public static function apiPlatformReportStats(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $startTime = $r->ensureOptionalInt('start_time');
        $endTime = $r->ensureOptionalInt('end_time');

        return self::getPlatformReportStats($startTime, $endTime);
    }

    /**
     * @return ReportStatsPayload
     */
    private static function getPlatformReportStats(
        ?int $startTime,
        ?int $endTime
    ) {
        if (is_null($startTime)) {
            $startTime = strtotime('first day of January ' . date('Y'));
        }
        if (is_null($endTime)) {
            $endTime = \OmegaUp\Time::get();
        }

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
     * Get stats for an overall platform report.
     *
     * @return array{templateProperties: array{payload: ReportStatsPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int|null $end_time
     * @omegaup-request-param int|null $start_time
     */
    public static function getPlatformReportStatsForTypeScript(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $startTime = $r->ensureOptionalInt('start_time');
        $endTime = $r->ensureOptionalInt('end_time');

        return [
            'templateProperties' => [
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleReportStats'
                ),
                'payload' => array_merge(
                    self::getPlatformReportStats($startTime, $endTime),
                    ['startTimestamp' => $startTime, 'endTimestamp' => $endTime],
                ),
            ],
            'entrypoint' => 'admin_report_stats',
        ];
    }
}
