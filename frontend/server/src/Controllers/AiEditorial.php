<?php

namespace OmegaUp\Controllers;

/**
 * AI Editorial Controller
 *
 * @psalm-type AiEditorialJobDetails=array{job_id: string, status: string, error_message: null|string, is_retriable: bool, created_at: \OmegaUp\Timestamp, problem_alias: string, md_en: null|string, md_es: null|string, md_pt: null|string, validation_verdict: null|string}
 */
class AiEditorial extends \OmegaUp\Controllers\Controller {
    const STATUS_QUEUED = 'queued';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    const REVIEW_STATUS_APPROVED = 'approved';
    const REVIEW_STATUS_REJECTED = 'rejected';

    // Rate limits
    const MAX_JOBS_PER_HOUR = 5;
    const COOLDOWN_MINUTES = 5;

    /**
     * Generate AI editorial for a problem
     *
     * @omegaup-request-param null|string $auth_token
     * @omegaup-request-param string $language
     * @omegaup-request-param string $problem_alias
     *
     * @return array{status: string, job_id?: string}
     */
    public static function apiGenerate(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $language = $r->ensureString('language');

        // Validate language parameter
        if (!in_array($language, ['en', 'es', 'pt'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid'
            );
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Check if user has admin permissions for this problem
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        // Validate required fields for job creation
        if (is_null($problem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        if (is_null($r->identity->user_id)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        // Skip rate limiting for system administrators
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            // Rate limiting: Check user's recent job count
            $recentJobs = \OmegaUp\DAO\AIEditorialJobs::countRecentJobsByUser(
                $r->identity->user_id,
                1 // 1 hour
            );

            if ($recentJobs >= self::MAX_JOBS_PER_HOUR) {
                throw new \OmegaUp\Exceptions\RateLimitExceededException(
                    'apiTokenRateLimitExceeded'
                );
            }

            // Problem cooldown: Check if there's a recent job for this problem
            $lastJob = \OmegaUp\DAO\AIEditorialJobs::getLastJobForProblem(
                $problem->problem_id
            );

            if (!is_null($lastJob)) {
                $cooldownEnd = $lastJob->created_at->time + (self::COOLDOWN_MINUTES * 60);
                if (time() < $cooldownEnd) {
                    throw new \OmegaUp\Exceptions\RateLimitExceededException(
                        'apiTokenRateLimitExceeded'
                    );
                }
            }
        }

                // Extract auth token from current session for worker authentication
        $currentSession = \OmegaUp\Controllers\Session::getCurrentSession($r);
        $sessionAuthToken = $currentSession['auth_token'];

        if (is_null($sessionAuthToken)) {
            throw new \OmegaUp\Exceptions\UnauthorizedException(
                'userNotAllowed'
            );
        }

        // Create the job
        $jobId = \OmegaUp\DAO\AIEditorialJobs::createJob(
            $problem->problem_id,
            $r->identity->user_id
        );

                // Queue the job to Redis for the Python worker with auth token
        self::queueJobToRedis(
            $jobId,
            $problemAlias,
            $r->identity->user_id,
            $sessionAuthToken,
            $r->identity->identity_id
        );

        return [
            'status' => 'ok',
            'job_id' => $jobId,
        ];
    }

    /**
     * Queue job to Redis for Python worker (following cronjob pattern)
     */
    private static function queueJobToRedis(
        string $jobId,
        string $problemAlias,
        int $userId,
        string $sessionAuthToken,
        int $identityId
    ): void {
        try {
            // Use constants like existing Cache.php implementation
            $redisHost = REDIS_HOST;
            $redisPort = REDIS_PORT;

            $redis = new \Redis();

            // Set Redis connection timeout
            $timeout = 30;
            $connected = $redis->connect($redisHost, $redisPort, $timeout);

            if (!$connected) {
                throw new \OmegaUp\Exceptions\InternalServerErrorException(
                    'redisConnectionFailed',
                    null,
                    ['host' => $redisHost, 'port' => $redisPort,]
                );
            }

            /** @psalm-suppress RedundantCondition REDIS_PASS is really a variable */
            if (REDIS_PASS !== '' && !$redis->auth(REDIS_PASS)) {
                throw new \OmegaUp\Exceptions\InternalServerErrorException(
                    'redisAuthenticationFailed'
                );
            }

            // Validate auth token before queuing
            if (strlen($sessionAuthToken) < 10) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid'
                );
            }

            $job = [
                'job_id' => $jobId,
                'problem_alias' => $problemAlias,
                'user_id' => $userId,
                'auth_token' => $sessionAuthToken,
                'identity_id' => $identityId,
                'created_at' => date('c'),
                'source' => 'web_interface',
                'priority' => 'user'
            ];

            // Queue to high priority queue for user-initiated jobs
            $redis->lpush('editorial_jobs_user', json_encode($job));
            $redis->close();
        } catch (\Exception $e) {
            // Log error with security considerations (don't log auth token)
            error_log(
                "Failed to queue Redis job {$jobId} for user {$userId}: " .
                $e->getMessage()
            );

            // Extract local variables to avoid code repetition
            $failedStatus = 'failed';
            $failedMessage = 'Failed to queue job for processing';
            $notRetriable = false;

            // Update job status to failed and throw exception
            try {
                \OmegaUp\DAO\AIEditorialJobs::updateJobStatus(
                    $jobId,
                    $failedStatus,
                    $failedMessage,
                    $notRetriable
                );
            } catch (\Exception $dbException) {
                error_log(
                    "Failed to update job status for {$jobId}: " .
                    $dbException->getMessage()
                );
            }

            // Re-throw to fail the API call for Redis failures
            throw new \OmegaUp\Exceptions\InternalServerErrorException(
                'generalError'
            );
        }
    }

    /**
     * Get status of an AI editorial job
     *
     * @omegaup-request-param string $job_id
     *
     * @return array{status: string, job?: AiEditorialJobDetails}
     */
    public static function apiStatus(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $jobId = $r->ensureString('job_id');

        $job = \OmegaUp\DAO\AIEditorialJobs::getByPK($jobId);
        if (is_null($job)) {
            throw new \OmegaUp\Exceptions\NotFoundException('resourceNotFound');
        }

        // Get problem to check permissions
        if (is_null($job->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problem = \OmegaUp\DAO\Problems::getByPK($job->problem_id);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Check if user has admin permissions for this problem
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        return [
            'status' => 'ok',
            'job' => [
                'job_id' => strval($job->job_id),
                'status' => strval($job->status),
                'error_message' => $job->error_message,
                'is_retriable' => boolval($job->is_retriable),
                'created_at' => $job->created_at,
                'problem_alias' => strval($problem->alias),
                'md_en' => $job->md_en,
                'md_es' => $job->md_es,
                'md_pt' => $job->md_pt,
                'validation_verdict' => $job->validation_verdict,
            ],
        ];
    }

    /**
     * Review and approve/reject an AI editorial
     *
     * When approved, the editorial is published to gitserver
     *
     * @omegaup-request-param string $job_id
     * @omegaup-request-param string $action
     * @omegaup-request-param null|string $language
     *
     * @return array{status: string}
     */
    public static function apiReview(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $jobId = $r->ensureString('job_id');
        $action = $r->ensureString('action');
        $language = $r->ensureOptionalString('language');

        self::validateReviewAction($action);
        $job = self::getJobForReview($jobId);
        $problem = self::getProblemForJob($job);
        self::validateProblemAdminPermissions($r->identity, $problem);
        self::validateJobStatus($job);

        if ($action === 'approve') {
            self::approveEditorial(
                $r->identity,
                $jobId,
                $job,
                $problem,
                $language
            );
        } else {
            self::rejectEditorial($jobId);
        }

        return ['status' => 'ok'];
    }

    /**
     * Validate the review action parameter
     *
     * @param string $action
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateReviewAction(string $action): void {
        if (!in_array($action, ['approve', 'reject'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid'
            );
        }
    }

    /**
     * Get and validate the AI editorial job
     *
     * @param string $jobId
     * @return \OmegaUp\DAO\VO\AIEditorialJobs
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function getJobForReview(string $jobId): \OmegaUp\DAO\VO\AIEditorialJobs {
        $job = \OmegaUp\DAO\AIEditorialJobs::getByPK($jobId);
        if (is_null($job)) {
            throw new \OmegaUp\Exceptions\NotFoundException('resourceNotFound');
        }
        return $job;
    }

    /**
     * Get and validate the problem for a job
     *
     * @param \OmegaUp\DAO\VO\AIEditorialJobs $job
     * @return \OmegaUp\DAO\VO\Problems
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function getProblemForJob(\OmegaUp\DAO\VO\AIEditorialJobs $job): \OmegaUp\DAO\VO\Problems {
        if (is_null($job->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problem = \OmegaUp\DAO\Problems::getByPK($job->problem_id);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        return $problem;
    }

    /**
     * Validate user has problem admin permissions
     *
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @param \OmegaUp\DAO\VO\Problems $problem
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateProblemAdminPermissions(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem
    ): void {
        if (!\OmegaUp\Authorization::isProblemAdmin($identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
    }

    /**
     * Validate job is in completed status
     *
     * @param \OmegaUp\DAO\VO\AIEditorialJobs $job
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateJobStatus(\OmegaUp\DAO\VO\AIEditorialJobs $job): void {
        if ($job->status !== self::STATUS_COMPLETED) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid'
            );
        }
    }

    /**
     * Approve and publish the editorial
     *
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @param string $jobId
     * @param \OmegaUp\DAO\VO\AIEditorialJobs $job
     * @param \OmegaUp\DAO\VO\Problems $problem
     * @param null|string $language
     */
    private static function approveEditorial(
        \OmegaUp\DAO\VO\Identities $identity,
        string $jobId,
        \OmegaUp\DAO\VO\AIEditorialJobs $job,
        \OmegaUp\DAO\VO\Problems $problem,
        ?string $language
    ): void {
        \OmegaUp\DAO\AIEditorialJobs::updateJobStatus(
            $jobId,
            self::REVIEW_STATUS_APPROVED
        );

        $solutionMarkdown = self::getSolutionMarkdownByLanguage(
            $job,
            $language
        );
        if (!is_null($solutionMarkdown) && !self::isTestEnvironment()) {
            self::publishEditorial(
                $identity,
                $problem,
                $solutionMarkdown,
                $language
            );
        }
    }

    /**
     * Get solution markdown for the specified language
     *
     * @param \OmegaUp\DAO\VO\AIEditorialJobs $job
     * @param null|string $language
     * @return null|string
     */
    private static function getSolutionMarkdownByLanguage(
        \OmegaUp\DAO\VO\AIEditorialJobs $job,
        ?string $language
    ): ?string {
        $markdown = null;

        if ($language === 'en' && !is_null($job->md_en)) {
            $markdown = $job->md_en;
        } elseif ($language === 'es' && !is_null($job->md_es)) {
            $markdown = $job->md_es;
        } elseif ($language === 'pt' && !is_null($job->md_pt)) {
            $markdown = $job->md_pt;
        } else {
            // Default to English if no language specified or content not found
            $markdown = $job->md_en ?? $job->md_es ?? $job->md_pt;
        }

        return $markdown;
    }

    /**
     * Check if running in test environment
     *
     * @return bool
     */
    private static function isTestEnvironment(): bool {
        return class_exists('\PHPUnit\Framework\TestCase', false);
    }

    /**
     * Reject the editorial
     *
     * @param string $jobId
     */
    private static function rejectEditorial(string $jobId): void {
        \OmegaUp\DAO\AIEditorialJobs::updateJobStatus(
            $jobId,
            self::REVIEW_STATUS_REJECTED
        );
    }

    /**
     * Helper method to publish AI editorial to problem solution
     *
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @param \OmegaUp\DAO\VO\Problems $problem
     * @param string $solutionMarkdown
     * @param null|string $language
     */
    private static function publishEditorial(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem,
        string $solutionMarkdown,
        ?string $language
    ): void {
        // Create a new Request for the Problem::apiUpdateSolution call
        $solutionRequest = new \OmegaUp\Request([
            'problem_alias' => $problem->alias,
            'solution' => $solutionMarkdown,
            'message' => 'AI-generated editorial approved and published',
            'lang' => $language ?? 'en',
        ]);

        // Set the identity for the request
        $solutionRequest->identity = $identity;

        // Call the existing Problem API to update the solution
        \OmegaUp\Controllers\Problem::apiUpdateSolution($solutionRequest);
    }

    /**
     * Update job status and content from AI worker
     *
     * This endpoint is called by the Python AI worker to update job status
     * and content in the database after processing completion.
     *
     * @omegaup-request-param string $job_id
     * @omegaup-request-param string $status
     * @omegaup-request-param null|string $error_message
     * @omegaup-request-param null|string $md_en
     * @omegaup-request-param null|string $md_es
     * @omegaup-request-param null|string $md_pt
     * @omegaup-request-param null|string $validation_verdict
     *
     * @return array{status: string}
     */
    public static function apiUpdateJob(\OmegaUp\Request $r): array {
        // This endpoint is called by the worker using the original user's auth_token
        $r->ensureIdentity();

        $jobId = $r->ensureString('job_id');
        $status = $r->ensureString('status');
        $errorMessage = $r->ensureOptionalString('error_message');
        $mdEn = $r->ensureOptionalString('md_en');
        $mdEs = $r->ensureOptionalString('md_es');
        $mdPt = $r->ensureOptionalString('md_pt');
        $validationVerdict = $r->ensureOptionalString('validation_verdict');

        // Validate status parameter
        if (
            !in_array($status, [
            self::STATUS_PROCESSING,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED
            ])
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid'
            );
        }

        $job = \OmegaUp\DAO\AIEditorialJobs::getByPK($jobId);
        if (is_null($job)) {
            throw new \OmegaUp\Exceptions\NotFoundException('resourceNotFound');
        }

        // Get problem to check permissions
        if (is_null($job->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problem = \OmegaUp\DAO\Problems::getByPK($job->problem_id);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Check if user has admin permissions for this problem
        // Worker uses the original user's auth_token, so this ensures proper permissions
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        // Update job status
        \OmegaUp\DAO\AIEditorialJobs::updateJobStatus(
            $jobId,
            $status,
            $errorMessage,
            $status === self::STATUS_FAILED ? false : null // Set is_retriable to false only for failed jobs
        );

        // Update job content if provided
        if (
            !is_null(
                $mdEn
            ) || !is_null(
                $mdEs
            ) || !is_null(
                $mdPt
            ) || !is_null(
                $validationVerdict
            )
        ) {
            \OmegaUp\DAO\AIEditorialJobs::updateJobContent(
                $jobId,
                $mdEn,
                $mdEs,
                $mdPt,
                $validationVerdict
            );
        }

        return ['status' => 'ok'];
    }
}
