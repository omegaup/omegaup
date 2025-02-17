<?php

namespace OmegaUp\Controllers;

class Admin extends \OmegaUp\Controllers\Controller {
    /**
     * Get stats for an overall platform report.
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
     */
    public static function apiUploadFile(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        

        if (empty($_FILES['file'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('missingFile');
        }

        $file = $_FILES['file'];
        $uploadDir = OMEGAUP_ROOT . '/www/docs/';
        $targetPath = $uploadDir . basename($file['name']);

        // Check if file with the same name already exists
        if (file_exists($targetPath)) {
            throw new \OmegaUp\Exceptions\InvalidFilesystemOperationException('fileAlreadyExists');
        }    
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            throw new \OmegaUp\Exceptions\InvalidFilesystemOperationException('failedToCreateDirectory');
        }

        $fileUploader = \OmegaUp\FileHandler::getFileUploader();
        if (!$fileUploader->isUploadedFile($file['tmp_name']) ||
            !$fileUploader->moveUploadedFile($file['tmp_name'], $targetPath)
        ) {
            throw new \OmegaUp\Exceptions\InvalidFilesystemOperationException('fileUploadFailed');
        }

        return ['status' => 'ok'];
    }

    /**
     * List all files in the /docs directory.
     */
    public static function apiListFiles(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $uploadDir = OMEGAUP_ROOT . '/www/docs/';
        
        self::$log->error(
            "This is the root directory (((((((((((((()))))))))))))))))))))))))): {$uploadDir}"
        );

        if (!is_dir($uploadDir)) {
            return ['files' => []];
        }

        $files = array_values(array_diff(scandir($uploadDir), ['.', '..']));
        return ['files' => $files];
    }

    /**
     * Delete a file from the /docs directory.
     */
    public static function apiDeleteFile(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Validators::validateStringNonEmpty($r['filename'], 'filename');

        $uploadDir = OMEGAUP_ROOT . '/www/docs/';
        $filePath = $uploadDir . basename($r['filename']);

        if (!file_exists($filePath)) {
            throw new \OmegaUp\Exceptions\NotFoundException('fileNotFound');
        }

        \OmegaUp\FileHandler::deleteFile($filePath);
        return ['status' => 'ok'];
    }

    /**
     * Download a file from the /docs directory.
     */
    public static function apiDownloadFile(\OmegaUp\Request $r): void {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Validators::validateStringNonEmpty($r['filename'], 'filename');

        $uploadDir = OMEGAUP_ROOT . '/www/docs/';
        $filePath = $uploadDir . basename($r['filename']);

        if (!file_exists($filePath)) {
            throw new \OmegaUp\Exceptions\NotFoundException('fileNotFound');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
