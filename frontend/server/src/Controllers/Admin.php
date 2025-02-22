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
     * Upload a file to the /docs directory (only for system admins).
     *
     * @return array{status: string, message: string, file: string}
     *
     * @omegaup-request-param string|null $file Base64 encoded file
     * @omegaup-request-param string|null $filename Original filename
     */
    public static function apiUploadFile(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if (empty($r['file']) || empty($r['filename'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'missingFile'
            );
        }

        $uploadDir = OMEGAUP_ROOT . '/www/docs/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            throw new \OmegaUp\Exceptions\InvalidFilesystemOperationException(
                'failedToCreateDirectory'
            );
        }

        // Extract Base64 data
        if (!preg_match('/^data:(.+);base64,(.+)$/', $r['file'], $matches)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidFileFormat'
            );
        }

        $fileData = base64_decode($matches[2]);
        if ($fileData === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidFileData'
            );
        }

        // Sanitize the filename to avoid security risks
        $originalFileName = basename($r['filename']);
        $originalFileName = preg_replace(
            '/[^A-Za-z0-9.\-_]/',
            '_',
            $originalFileName
        ); // Remove unsafe characters
        $filePath = $uploadDir . $originalFileName;

        // Prevent overwriting by appending a counter if the file exists
        $counter = 1;
        while (file_exists($filePath)) {
            $filePath = $uploadDir . pathinfo(
                $originalFileName,
                PATHINFO_FILENAME
            ) . "_{$counter}." . pathinfo(
                $originalFileName,
                PATHINFO_EXTENSION
            );
            $counter++;
        }

        if (file_put_contents($filePath, $fileData) === false) {
            throw new \OmegaUp\Exceptions\InvalidFilesystemOperationException(
                'fileUploadFailed'
            );
        }

        return ['status' => 'ok', 'message' => 'File uploaded successfully.', 'file' => basename(
            $filePath
        )];
    }

    /**
     * List all files in the /docs directory.
     *
     * @return array{status: string, message: string, files: array<string>}
     */
    public static function apiListFiles(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $uploadDir = OMEGAUP_ROOT . '/www/docs/';
        return [
            'status' => 'ok',
            'message' => 'File list retrieved successfully.',
            'files' => is_dir(
                $uploadDir
            ) ? array_values(
                array_diff(
                    scandir(
                        $uploadDir
                    ),
                    ['.', '..']
                )
            ) : []
        ];
    }

    /**
     * Delete a file from the /docs directory.
     *
     * @return array{status: string, message: string}
     *
     * @omegaup-request-param string $filename
     */
    public static function apiDeleteFile(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Validators::validateStringNonEmpty($r['filename'], 'filename');

        $filePath = OMEGAUP_ROOT . '/www/docs/' . basename($r['filename']);
        if (!file_exists($filePath)) {
            throw new \OmegaUp\Exceptions\NotFoundException('fileNotFound');
        }

        \OmegaUp\FileHandler::deleteFile($filePath);
        return ['status' => 'ok', 'message' => 'File deleted successfully.'];
    }

    /**
     * Download a file from the /docs directory.
     *
     * @omegaup-request-param string $filename
     */
    public static function apiDownloadFile(\OmegaUp\Request $r): void {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Validators::validateStringNonEmpty($r['filename'], 'filename');
        $filePath = OMEGAUP_ROOT . '/www/docs/' . basename($r['filename']);

        if (!file_exists($filePath)) {
            throw new \OmegaUp\Exceptions\NotFoundException('fileNotFound');
        }

        // Set headers for file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header(
            'Content-Disposition: attachment; filename="' . basename(
                $filePath
            ) . '"'
        );
        header('Content-Length: ' . filesize($filePath));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        // Read and output the file
        readfile($filePath);
        exit;
    }
}
