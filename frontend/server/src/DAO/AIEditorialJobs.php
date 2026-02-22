<?php

namespace OmegaUp\DAO;

/**
 * AIEditorialJobs Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\AIEditorialJobs}.
 *
 * @access public
 */
class AIEditorialJobs extends \OmegaUp\DAO\Base\AIEditorialJobs {
    /** @var int Hours that define whether a job is considered recent */
    const RECENT_JOB_HOURS = 1;

    /**
     * Saves the current state of the given
     * {@link \OmegaUp\DAO\VO\AIEditorialJobs} object to the database.
     *
     * The primary key will determine which instance will be updated in the
     * database.
     * If the primary key or combination of primary keys describing a row is not
     * found in the database, then save() will create a new row and insert the
     * newly generated ID into the object.
     *
     * @param \OmegaUp\DAO\VO\AIEditorialJobs $AI_Editorial_Jobs The object of
     * type {@link \OmegaUp\DAO\VO\AIEditorialJobs} to be saved.
     * @return int An integer greater than or equal to zero identifying the
     * number of affected rows.
     */
    final public static function save(
        \OmegaUp\DAO\VO\AIEditorialJobs $AI_Editorial_Jobs
    ): int {
        if (
            $AI_Editorial_Jobs->job_id === null || self::getByPK(
                    $AI_Editorial_Jobs->job_id
                ) === null
        ) {
            return AIEditorialJobs::create($AI_Editorial_Jobs);
        }
        return AIEditorialJobs::update($AI_Editorial_Jobs);
    }

    /**
     * Creates a new AI editorial job
     *
     * @return string The generated job ID (UUID)
     */
    public static function createJob(
        int $problemId,
        int $userId
    ): string {
        $jobId = self::generateUuid();

        $job = new \OmegaUp\DAO\VO\AIEditorialJobs([
            'job_id' => $jobId,
            'problem_id' => $problemId,
            'user_id' => $userId,
            'status' => 'queued',
            'attempts' => 0,
            'is_retriable' => true,
        ]);

        self::save($job);
        return $jobId;
    }

    /**
     * Updates job status and optional error message
     */
    public static function updateJobStatus(
        string $jobId,
        string $status,
        ?string $errorMessage = null,
        ?bool $isRetriable = null
    ): void {
        $job = self::getByPK($jobId);
        if ($job === null) {
            throw new \OmegaUp\Exceptions\NotFoundException('resourceNotFound');
        }

        $job->status = $status;
        if ($errorMessage !== null) {
            $job->error_message = $errorMessage;
        }
        if ($isRetriable !== null) {
            $job->is_retriable = $isRetriable;
        }

        self::save($job);
    }

    /**
     * Updates job content (editorials in multiple languages)
     */
    public static function updateJobContent(
        string $jobId,
        ?string $mdEn = null,
        ?string $mdEs = null,
        ?string $mdPt = null,
        ?string $validationVerdict = null
    ): void {
        $job = self::getByPK($jobId);
        if ($job === null) {
            throw new \OmegaUp\Exceptions\NotFoundException('resourceNotFound');
        }

        if ($mdEn !== null) {
            $job->md_en = $mdEn;
        }
        if ($mdEs !== null) {
            $job->md_es = $mdEs;
        }
        if ($mdPt !== null) {
            $job->md_pt = $mdPt;
        }
        if ($validationVerdict !== null) {
            $job->validation_verdict = $validationVerdict;
        }

        self::save($job);
    }

    /**
     * Counts recent jobs by user for rate limiting
     */
    public static function countRecentJobsByUser(
        int $userId,
        int $hours = self::RECENT_JOB_HOURS
    ): int {
        $sql = '
            SELECT COUNT(*)
            FROM AI_Editorial_Jobs
            WHERE user_id = ?
              AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
        ';

        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$userId, $hours]
        );

        return $count;
    }

    /**
     * Gets the last job for a problem (for cooldown check)
     *
     * @return \OmegaUp\DAO\VO\AIEditorialJobs|null
     */
    public static function getLastJobForProblem(int $problemId): ?\OmegaUp\DAO\VO\AIEditorialJobs {
        $sql = '
            SELECT ' . \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\AIEditorialJobs::FIELD_NAMES,
            'AI_Editorial_Jobs'
        ) . '
            FROM AI_Editorial_Jobs
            WHERE problem_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        ';

        /** @var array{attempts: int, created_at: \OmegaUp\Timestamp, error_message: null|string, is_retriable: bool, job_id: string, md_en: null|string, md_es: null|string, md_pt: null|string, problem_id: int, status: string, user_id: int, validation_verdict: null|string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$problemId]
        );
        if ($rs === null) {
            return null;
        }

        return new \OmegaUp\DAO\VO\AIEditorialJobs($rs);
    }

    /**
     * Gets jobs by user with optional limit
     *
     * @return list<\OmegaUp\DAO\VO\AIEditorialJobs>
     */
    public static function getJobsByUser(
        int $userId,
        int $limit = 10
    ): array {
        $sql = '
            SELECT ' . \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\AIEditorialJobs::FIELD_NAMES,
            'AI_Editorial_Jobs'
        ) . '
            FROM AI_Editorial_Jobs
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ';

        /** @var list<array{attempts: int, created_at: \OmegaUp\Timestamp, error_message: null|string, is_retriable: bool, job_id: string, md_en: null|string, md_es: null|string, md_pt: null|string, problem_id: int, status: string, user_id: int, validation_verdict: null|string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$userId, $limit]
        );

        $jobs = [];
        foreach ($rs as $row) {
            $jobs[] = new \OmegaUp\DAO\VO\AIEditorialJobs($row);
        }

        return $jobs;
    }

    /**
     * Gets jobs by problem
     *
     * @return list<\OmegaUp\DAO\VO\AIEditorialJobs>
     */
    public static function getJobsByProblem(int $problemId): array {
        $sql = '
            SELECT ' . \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\AIEditorialJobs::FIELD_NAMES,
            'AI_Editorial_Jobs'
        ) . '
            FROM AI_Editorial_Jobs
            WHERE problem_id = ?
            ORDER BY created_at DESC
        ';

        /** @var list<array{attempts: int, created_at: \OmegaUp\Timestamp, error_message: null|string, is_retriable: bool, job_id: string, md_en: null|string, md_es: null|string, md_pt: null|string, problem_id: int, status: string, user_id: int, validation_verdict: null|string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemId]
        );

        $jobs = [];
        foreach ($rs as $row) {
            $jobs[] = new \OmegaUp\DAO\VO\AIEditorialJobs($row);
        }

        return $jobs;
    }

    /**
     * Generates a UUID v4
     *
     * @return string
     */
    private static function generateUuid(): string {
        $data = random_bytes(16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
