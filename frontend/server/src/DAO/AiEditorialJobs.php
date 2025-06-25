<?php

namespace OmegaUp\DAO;

/**
 * AiEditorialJobs Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\AiEditorialJobs}.
 *
 * @access public
 */
class AiEditorialJobs extends \OmegaUp\DAO\Base\AiEditorialJobs {
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

        $job = new \OmegaUp\DAO\VO\AiEditorialJobs([
            'job_id' => $jobId,
            'problem_id' => $problemId,
            'user_id' => $userId,
            'status' => 'queued',
            'attempts' => 0,
        ]);

        self::save($job);
        return $jobId;
    }

    /**
     * Gets a job by its UUID
     *
     * @return \OmegaUp\DAO\VO\AiEditorialJobs|null
     */
    public static function getJobByUuid(string $jobId): ?\OmegaUp\DAO\VO\AiEditorialJobs {
        return self::getByPK($jobId);
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
        if (is_null($job)) {
            throw new \OmegaUp\Exceptions\NotFoundException('resourceNotFound');
        }

        $job->status = $status;
        if (!is_null($errorMessage)) {
            $job->error_message = $errorMessage;
        }
        if (!is_null($isRetriable)) {
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
        if (is_null($job)) {
            throw new \OmegaUp\Exceptions\NotFoundException('resourceNotFound');
        }

        if (!is_null($mdEn)) {
            $job->md_en = $mdEn;
        }
        if (!is_null($mdEs)) {
            $job->md_es = $mdEs;
        }
        if (!is_null($mdPt)) {
            $job->md_pt = $mdPt;
        }
        if (!is_null($validationVerdict)) {
            $job->validation_verdict = $validationVerdict;
        }

        self::save($job);
    }

    /**
     * Counts recent jobs by user for rate limiting
     */
    public static function countRecentJobsByUser(
        int $userId,
        int $hours = 1
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
     * @return \OmegaUp\DAO\VO\AiEditorialJobs|null
     */
    public static function getLastJobForProblem(int $problemId): ?\OmegaUp\DAO\VO\AiEditorialJobs {
        $sql = '
            SELECT ' . \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\AiEditorialJobs::FIELD_NAMES,
            'AI_Editorial_Jobs'
        ) . '
            FROM AI_Editorial_Jobs
            WHERE problem_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        ';

        /** @var array{attempts: int, created_at: \OmegaUp\Timestamp, error_message: null|string, is_retriable: int, job_id: string, md_en: null|string, md_es: null|string, md_pt: null|string, problem_id: int, status: string, user_id: int, validation_verdict: null|string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$problemId]
        );
        if (is_null($rs)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\AiEditorialJobs($rs);
    }

    /**
     * Gets jobs by user with optional limit
     *
     * @return list<\OmegaUp\DAO\VO\AiEditorialJobs>
     */
    public static function getJobsByUser(
        int $userId,
        int $limit = 10
    ): array {
        $sql = '
            SELECT ' . \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\AiEditorialJobs::FIELD_NAMES,
            'AI_Editorial_Jobs'
        ) . '
            FROM AI_Editorial_Jobs
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ';

        /** @var list<array{attempts: int, created_at: \OmegaUp\Timestamp, error_message: null|string, is_retriable: int, job_id: string, md_en: null|string, md_es: null|string, md_pt: null|string, problem_id: int, status: string, user_id: int, validation_verdict: null|string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$userId, $limit]
        );

        $jobs = [];
        foreach ($rs as $row) {
            $jobs[] = new \OmegaUp\DAO\VO\AiEditorialJobs($row);
        }

        return $jobs;
    }

    /**
     * Gets jobs by problem
     *
     * @return list<\OmegaUp\DAO\VO\AiEditorialJobs>
     */
    public static function getJobsByProblem(int $problemId): array {
        $sql = '
            SELECT ' . \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\AiEditorialJobs::FIELD_NAMES,
            'AI_Editorial_Jobs'
        ) . '
            FROM AI_Editorial_Jobs
            WHERE problem_id = ?
            ORDER BY created_at DESC
        ';

        /** @var list<array{attempts: int, created_at: \OmegaUp\Timestamp, error_message: null|string, is_retriable: int, job_id: string, md_en: null|string, md_es: null|string, md_pt: null|string, problem_id: int, status: string, user_id: int, validation_verdict: null|string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemId]
        );

        $jobs = [];
        foreach ($rs as $row) {
            $jobs[] = new \OmegaUp\DAO\VO\AiEditorialJobs($row);
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
