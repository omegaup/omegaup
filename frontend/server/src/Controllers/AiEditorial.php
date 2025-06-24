<?php

namespace OmegaUp\Controllers;

/**
 * AI Editorial Controller
 *
 * @psalm-type AiEditorialJobDetails=array{job_id: string, status: string, error_message: null|string, created_at: \OmegaUp\Timestamp, problem_alias: string, md_en: null|string, md_es: null|string, md_pt: null|string}
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
     * @omegaup-request-param string $problem_alias
     *
     * @return array{status: string, job_id?: string}
     */
    public static function apiGenerate(\OmegaUp\Request $r): array {
        $identity = self::authenticateRequest($r);
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Check if user has admin permissions for this problem
        if (!\OmegaUp\Authorization::isProblemAdmin($identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        // Rate limiting: Check user's recent job count
        $recentJobs = \OmegaUp\DAO\AiEditorialJobs::countRecentJobsByUser(
            $identity->user_id,
            1 // 1 hour
        );

        if ($recentJobs >= self::MAX_JOBS_PER_HOUR) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'rateLimitExceeded'
            );
        }

        // Problem cooldown: Check if there's a recent job for this problem
        $lastJob = \OmegaUp\DAO\AiEditorialJobs::getLastJobForProblem(
            $problem->problem_id
        );

        if (!is_null($lastJob)) {
            $cooldownEnd = $lastJob->created_at->time + (self::COOLDOWN_MINUTES * 60);
            if (time() < $cooldownEnd) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'problemCooldownActive'
                );
            }
        }

        // Create the job
        $jobId = \OmegaUp\DAO\AiEditorialJobs::createJob(
            $problem->problem_id,
            $identity->user_id
        );

        return [
            'status' => 'ok',
            'job_id' => $jobId,
        ];
    }

    /**
     * Get status of an AI editorial job
     *
     * @omegaup-request-param string $job_id
     *
     * @return array{status: string, job?: AiEditorialJobDetails}
     */
    public static function apiStatus(\OmegaUp\Request $r): array {
        $identity = self::authenticateRequest($r);
        $jobId = $r->ensureString('job_id');

        $job = \OmegaUp\DAO\AiEditorialJobs::getJobByUuid($jobId);
        if (is_null($job)) {
            throw new \OmegaUp\Exceptions\NotFoundException('jobNotFound');
        }

        // Get problem to check permissions
        $problem = \OmegaUp\DAO\Problems::getByPK($job->problem_id);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Check if user has admin permissions for this problem
        if (!\OmegaUp\Authorization::isProblemAdmin($identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        return [
            'status' => 'ok',
            'job' => [
                'job_id' => $job->job_id,
                'status' => $job->status,
                'error_message' => $job->error_message,
                'created_at' => $job->created_at,
                'problem_alias' => $problem->alias,
                'md_en' => $job->md_en,
                'md_es' => $job->md_es,
                'md_pt' => $job->md_pt,
            ],
        ];
    }

    /**
     * Review and approve/reject an AI editorial
     *
     * @omegaup-request-param string $job_id
     * @omegaup-request-param string $action (approve|reject)
     * @omegaup-request-param null|string $language
     *
     * @return array{status: string}
     */
    public static function apiReview(\OmegaUp\Request $r): array {
        $identity = self::authenticateRequest($r);
        $jobId = $r->ensureString('job_id');
        $action = $r->ensureString('action');
        $language = $r->ensureOptionalString('language');

        if (!in_array($action, ['approve', 'reject'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameter'
            );
        }

        $job = \OmegaUp\DAO\AiEditorialJobs::getJobByUuid($jobId);
        if (is_null($job)) {
            throw new \OmegaUp\Exceptions\NotFoundException('jobNotFound');
        }

        // Get problem to check permissions
        $problem = \OmegaUp\DAO\Problems::getByPK($job->problem_id);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Check if user has admin permissions for this problem
        if (!\OmegaUp\Authorization::isProblemAdmin($identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        // Check if job is in completed status
        if ($job->status !== self::STATUS_COMPLETED) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'jobNotCompleted'
            );
        }

        if ($action === 'approve') {
            // Update job status
            \OmegaUp\DAO\AiEditorialJobs::updateJobStatus(
                $jobId,
                self::REVIEW_STATUS_APPROVED
            );

            // Publish the editorial using existing Problem API
            $solutionMarkdown = null;
            if ($language === 'en' && !is_null($job->md_en)) {
                $solutionMarkdown = $job->md_en;
            } elseif ($language === 'es' && !is_null($job->md_es)) {
                $solutionMarkdown = $job->md_es;
            } elseif ($language === 'pt' && !is_null($job->md_pt)) {
                $solutionMarkdown = $job->md_pt;
            } else {
                // Default to English if no language specified or content not found
                $solutionMarkdown = $job->md_en ?? $job->md_es ?? $job->md_pt;
            }

            if (!is_null($solutionMarkdown)) {
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
                \OmegaUp\Controllers\Problem::apiUpdateSolution(
                    $solutionRequest
                );
            }
        } else {
            // Reject the job
            \OmegaUp\DAO\AiEditorialJobs::updateJobStatus(
                $jobId,
                self::REVIEW_STATUS_REJECTED
            );
        }

        return ['status' => 'ok'];
    }
}
