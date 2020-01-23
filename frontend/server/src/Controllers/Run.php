<?php

 namespace OmegaUp\Controllers;

/**
 * RunController
 *
 * @author joemmanuel
 */
class Run extends \OmegaUp\Controllers\Controller {
    // All languages that runs can have.
    public const SUPPORTED_LANGUAGES = [
        'kp' => 'Karel (Pascal)',
        'kj' => 'Karel (Java)',
        'c11-gcc' => 'C11 (gcc 7.4)',
        'c11-clang' => 'C11 (clang 6.0)',
        'cpp11' => 'C++11 (gcc 7.4)',
        'cpp11-gcc' => 'C++11 (g++ 7.4)',
        'cpp11-clang' => 'C++11 (clang++ 6.0)',
        'cpp17-gcc' => 'C++17 (g++ 7.4)',
        'cpp17-clang' => 'C++17 (clang++ 6.0)',
        'java' => 'Java (openjdk 11.0)',
        'py2' => 'Python 2.7',
        'py3' => 'Python 3.6',
        'rb' => 'Ruby (2.5)',
        'cs' => 'C# (dotnet 2.2)',
        'pas' => 'Pascal (fpc 3.0)',
        'cat' => 'Output Only',
        'hs' => 'Haskell (ghc 8.0)',
        'lua' => 'Lua (5.2)',
    ];

    // These languages are aliases. They can be shown to the user, but should
    // not appear as selectable mostly anywhere.
    public const LANGUAGE_ALIASES = [
        'c' => 'C11 (gcc 7.4)',
        'cpp' => 'C++03 (gcc 7.4)',
        'cpp11' => 'C++11 (gcc 7.4)',
        'py' => 'Python 2.7',
    ];

    public const DEFAULT_LANGUAGES = [
        'c11-gcc',
        'c11-clang',
        'cpp11-gcc',
        'cpp11-clang',
        'cpp17-gcc',
        'cpp17-clang',
        'cs',
        'hs',
        'java',
        'lua',
        'pas',
        'py2',
        'py3',
        'rb',
    ];

    /** @var int */
    public static $defaultSubmissionGap = 60; /*seconds*/

    public const VERDICTS = [
        'AC',
        'PA',
        'WA',
        'TLE',
        'OLE',
        'MLE',
        'RTE',
        'RFE',
        'CE',
        'JE',
        'VE',
        'NO-AC',
    ];

    /**
     *
     * Validates Create Run request
     *
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\NotAllowedToSubmitException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateCreateRequest(\OmegaUp\Request $r): bool {
        $r->ensureIdentity();
        // https://github.com/omegaup/omegaup/issues/739
        if ($r->identity->username == 'omi') {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $allowedLanguages = array_keys(self::SUPPORTED_LANGUAGES);
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );

        // Check that problem exists
        $r['problem'] = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);

        if ($r['problem']->deprecated) {
            throw new \OmegaUp\Exceptions\PreconditionFailedException(
                'problemDeprecated'
            );
        }
        // check that problem is not publicly or privately banned.
        if (
            $r['problem']->visibility === \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED ||
            $r['problem']->visibility === \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotfound');
        }

        $allowedLanguages = array_intersect(
            $allowedLanguages,
            explode(',', $r['problem']->languages)
        );
        \OmegaUp\Validators::validateInEnum(
            $r['language'],
            'language',
            $allowedLanguages
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['source'], 'source');

        // Can't set both problemset_id and contest_alias at the same time.
        if (!empty($r['problemset_id']) && !empty($r['contest_alias'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'incompatibleArgs',
                'problemset_id and contest_alias'
            );
        }

        $problemset_id = null;
        if (!empty($r['problemset_id'])) {
            // Got a problemset id directly.
            $problemset_id = intval($r['problemset_id']);
            $r['container'] = \OmegaUp\DAO\Problemsets::getProblemsetContainer(
                $problemset_id
            );
        } elseif (!empty($r['contest_alias'])) {
            // Got a contest alias, need to fetch the problemset id.
            // Validate contest
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['contest_alias'],
                'contest_alias'
            );
            $r['contest'] = \OmegaUp\DAO\Contests::getByAlias(
                $r['contest_alias']
            );
            if (is_null($r['contest'])) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterNotFound',
                    'contest_alias'
                );
            }

            $problemset_id = $r['contest']->problemset_id;
            $r['container'] = $r['contest'];

            // Update list of valid languages.
            if (!is_null($r['contest']->languages)) {
                $allowedLanguages = array_intersect(
                    $allowedLanguages,
                    explode(',', $r['contest']->languages)
                );
            }
        } else {
            // Check for practice or public problem, there is no contest info
            // in this scenario.
            if (
                \OmegaUp\DAO\Problems::isVisible($r['problem']) ||
                \OmegaUp\Authorization::isProblemAdmin(
                    $r->identity,
                    $r['problem']
                ) ||
                \OmegaUp\Time::get() > \OmegaUp\DAO\Problems::getPracticeDeadline(
                    $r['problem']->problem_id
                )
            ) {
                if (
                    !\OmegaUp\DAO\Runs::isRunInsideSubmissionGap(
                        null,
                        null,
                        intval($r['problem']->problem_id),
                        intval($r->identity->identity_id)
                    )
                        && !\OmegaUp\Authorization::isSystemAdmin($r->identity)
                ) {
                        throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                            'runWaitGap'
                        );
                }

                return true;
            } else {
                throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                    'problemIsNotPublic'
                );
            }
        }

        $r['problemset'] = \OmegaUp\DAO\Problemsets::getByPK($problemset_id);
        if (is_null($r['problemset'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'problemset_id'
            );
        }

        // Validate the language.
        if (!is_null($r['problemset']->languages)) {
            $allowedLanguages = array_intersect(
                $allowedLanguages,
                explode(',', $r['problemset']->languages)
            );
        }
        \OmegaUp\Validators::validateInEnum(
            $r['language'],
            'language',
            $allowedLanguages
        );

        // Validate that the combination problemset_id problem_id is valid
        if (
            !\OmegaUp\DAO\ProblemsetProblems::getByPK(
                $problemset_id,
                $r['problem']->problem_id
            )
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'problem_alias'
            );
        }

        $problemsetIdentity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $r->identity->identity_id,
            $problemset_id
        );

        // No one should submit after the deadline. Not even admins.
        if (
            \OmegaUp\DAO\Problemsets::isLateSubmission(
                $r['container'],
                $problemsetIdentity
            )
        ) {
            throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                'runNotInsideContest'
            );
        }

        // Contest admins can skip following checks
        if (!\OmegaUp\Authorization::isAdmin($r->identity, $r['problemset'])) {
            // Before submit something, user had to open the problem/problemset.
            if (
                is_null($problemsetIdentity) &&
                !\OmegaUp\Authorization::canSubmitToProblemset(
                    $r->identity,
                    $r['problemset']
                )
            ) {
                throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                    'runNotEvenOpened'
                );
            }

            // Validate that the run is timely inside contest
            if (
                !\OmegaUp\DAO\Problemsets::isSubmissionWindowOpen(
                    $r['container']
                )
            ) {
                throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                    'runNotInsideContest'
                );
            }

            // Validate if the user is allowed to submit given the submissions_gap
            if (
                !\OmegaUp\DAO\Runs::isRunInsideSubmissionGap(
                    intval($problemset_id),
                    $r['contest'],
                    intval($r['problem']->problem_id),
                    intval($r->identity->identity_id)
                )
            ) {
                throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                    'runWaitGap'
                );
            }
        }

        return false;
    }

    /**
     * Create a new run
     *
     * @throws \Exception
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{guid: string, submission_deadline: int, nextSubmissionTimestamp: int}
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        // Authenticate user
        $r->ensureIdentity();

        // Validate request
        $practice = self::validateCreateRequest($r);

        self::$log->info('New run being submitted!!');
        $response = [];

        if ($practice) {
            if (OMEGAUP_LOCKDOWN) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'lockdown'
                );
            }
            $submitDelay = 0;
            $problemsetId = null;
            $type = 'normal';
        } else {
            //check the kind of penalty_type for this contest
            $start = null;
            $problemsetId = intval($r['problemset']->problemset_id);
            if (isset($r['contest'])) {
                $penalty_type = $r['contest']->penalty_type;

                switch ($penalty_type) {
                    case 'contest_start':
                        // submit_delay is calculated from the start
                        // of the contest
                        $start = $r['contest']->start_time;
                        break;

                    case 'problem_open':
                        // submit delay is calculated from the
                        // time the user opened the problem
                        $opened = \OmegaUp\DAO\ProblemsetProblemOpened::getByPK(
                            $problemsetId,
                            intval($r['problem']->problem_id),
                            $r->identity->identity_id
                        );

                        if (is_null($opened)) {
                            //holy moly, he is submitting a run
                            //and he hasnt even opened the problem
                            //what should be done here?
                            throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                                'runEvenOpened'
                            );
                        }

                        $start = $opened->open_time;
                        break;

                    case 'none':
                    case 'runtime':
                        //we dont care
                        $start = null;
                        break;

                    default:
                        self::$log->error(
                            'penalty_type for this contests is not a valid option, asuming `none`.'
                        );
                        $start = null;
                }
            }

            if (!is_null($start)) {
                //asuming submit_delay is in minutes
                $submitDelay = intval((\OmegaUp\Time::get() - $start) / 60);
            } else {
                $submitDelay = 0;
            }

            // If user is admin and is in virtual contest, then admin will be treated as contestant

            $type = (\OmegaUp\Authorization::isAdmin(
                $r->identity,
                $r['problemset']
            ) &&
                !is_null($r['contest']) &&
                !\OmegaUp\DAO\Contests::isVirtual(
                    $r['contest']
                )) ? 'test' : 'normal';
        }

        // Populate new run+submission object
        $submission = new \OmegaUp\DAO\VO\Submissions([
            'identity_id' => $r->identity->identity_id,
            'problem_id' => $r['problem']->problem_id,
            'problemset_id' => $problemsetId,
            'guid' => md5(uniqid(rand(), true)),
            'language' => $r['language'],
            'time' => \OmegaUp\Time::get(),
            'submit_delay' => $submitDelay, /* based on penalty_type */
            'type' => $type,
        ]);

        if (!is_null($r->identity->current_identity_school_id)) {
            $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $r->identity->current_identity_school_id
            );
            if (!is_null($identitySchool)) {
                $submission->school_id = $identitySchool->school_id;
            }
        }

        $run = new \OmegaUp\DAO\VO\Runs([
            'version' => $r['problem']->current_version,
            'status' => 'new',
            'runtime' => 0,
            'penalty' => $submitDelay,
            'time' => \OmegaUp\Time::get(),
            'memory' => 0,
            'score' => 0,
            'contest_score' => !is_null($problemsetId) ? 0 : null,
            'verdict' => 'JE',
        ]);

        try {
            \OmegaUp\DAO\DAO::transBegin();
            // Push run into DB
            \OmegaUp\DAO\Submissions::create($submission);
            $run->submission_id = $submission->submission_id;
            \OmegaUp\DAO\Runs::create($run);
            $submission->current_run_id = $run->run_id;
            \OmegaUp\DAO\Submissions::update($submission);
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        // Call Grader
        try {
            \OmegaUp\Grader::getInstance()->grade($run, trim($r['source']));
        } catch (\Exception $e) {
            // Welp, it failed. We cannot make this a real transaction
            // because the Run row would not be visible from the Grader
            // process, so we attempt to roll it back by hand.
            // We need to unlink the current run and submission prior to
            // deleting the rows. Otherwise we would have a foreign key
            // violation.
            $submission->current_run_id = null;
            \OmegaUp\DAO\Submissions::update($submission);
            \OmegaUp\DAO\Runs::delete($run);
            \OmegaUp\DAO\Submissions::delete($submission);
            self::$log->error('Call to \OmegaUp\Grader::grade() failed', $e);
            throw $e;
        }

        \OmegaUp\DAO\SubmissionLog::create(new \OmegaUp\DAO\VO\SubmissionLog([
            'user_id' => $r->identity->user_id,
            'identity_id' => $r->identity->identity_id,
            'submission_id' => $submission->submission_id,
            'problemset_id' => $submission->problemset_id,
            'ip' => ip2long($_SERVER['REMOTE_ADDR'])
        ]));

        $r['problem']->submissions++;
        \OmegaUp\DAO\Problems::update($r['problem']);

        if ($practice) {
            $response['submission_deadline'] = 0;
        } else {
            // Add remaining time to the response
            $problemsetIdentity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
                $r->identity->identity_id,
                $problemsetId
            );
            if (
                !is_null($problemsetIdentity) &&
                !is_null(
                    $problemsetIdentity->end_time
                )
            ) {
                $response['submission_deadline'] =
                    intval($problemsetIdentity->end_time);
            } elseif (isset($r['container']->finish_time)) {
                $response['submission_deadline'] =
                    intval($r['container']->finish_time);
            } else {
                $response['submission_deadline'] = 0;
            }
        }

        /** @var null|\OmegaUp\DAO\VO\Contests */
        $contest = isset($r['contest']) ? $r['contest'] : null;

        // Happy ending
        $response['nextSubmissionTimestamp'] =
            \OmegaUp\DAO\Runs::nextSubmissionTimestamp($contest);
        if (is_null($submission->guid)) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }
        $response['guid'] = $submission->guid;

        // Expire rank cache
        \OmegaUp\Controllers\User::deleteProblemsSolvedRankCacheList();

        return $response;
    }

    /**
     * Validate request of details
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{submission: \OmegaUp\DAO\VO\Submissions, run: \OmegaUp\DAO\VO\Runs}
     */
    private static function validateDetailsRequest(string $runAlias): array {
        // If user is not judge, must be the run's owner.
        $submission = \OmegaUp\DAO\Submissions::getByGuid($runAlias);
        if (is_null($submission)) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }

        $run = \OmegaUp\DAO\Runs::getByPK(
            $submission->current_run_id
        );
        if (is_null($run)) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }

        return [
            'run' => $run,
            'submission' => $submission,
        ];
    }

    /**
     * Get basic details of a run
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{contest_score: float|null, memory: int, penalty: int, runtime: int, score: float, submit_delay: int, time: int}
     */
    public static function apiStatus(\OmegaUp\Request $r): array {
        // Get the user who is calling this API
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['run_alias'],
            'run_alias'
        );
        [
            'run' => $run,
            'submission' => $submission,
        ] = self::validateDetailsRequest($r['run_alias']);

        if (
            !\OmegaUp\Authorization::canViewSubmission(
                $r->identity,
                $submission
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        // Fill response
        $filtered = (
            $submission->asFilteredArray([
                'guid', 'language', 'time', 'submit_delay',
            ]) +
            $run->asFilteredArray([
                'status', 'verdict', 'runtime', 'penalty', 'memory', 'score', 'contest_score',
            ])
        );
        $filtered['time'] = intval($filtered['time']);
        $filtered['score'] = round(floatval($filtered['score']), 4);
        $filtered['runtime'] = intval($filtered['runtime']);
        $filtered['penalty'] = intval($filtered['penalty']);
        $filtered['memory'] = intval($filtered['memory']);
        $filtered['submit_delay'] = intval($filtered['submit_delay']);
        if (!is_null($filtered['contest_score'])) {
            $filtered['contest_score'] = round(
                floatval(
                    $filtered['contest_score']
                ),
                2
            );
        }
        if ($submission->identity_id == $r->identity->identity_id) {
            $filtered['username'] = $r->identity->username;
        }
        return $filtered;
    }

    /**
     * Re-sends a problem to Grader.
     *
     * @return array{status: string}
     */
    public static function apiRejudge(\OmegaUp\Request $r): array {
        // Get the user who is calling this API
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['run_alias'],
            'run_alias'
        );
        [
            'run' => $run,
            'submission' => $submission,
        ] = self::validateDetailsRequest($r['run_alias']);

        if (
            !\OmegaUp\Authorization::canEditSubmission(
                $r->identity,
                $submission
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        self::$log->info('Run being rejudged!!');

        // Reset fields.
        try {
            \OmegaUp\DAO\DAO::transBegin();
            $run->status = 'new';
            \OmegaUp\DAO\Runs::update($run);
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        try {
            \OmegaUp\Grader::getInstance()->rejudge(
                [$run],
                $r['debug'] || false
            );
        } catch (\Exception $e) {
            self::$log->error('Call to \OmegaUp\Grader::rejudge() failed', $e);
        }

        $response = [];
        $response['status'] = 'ok';

        self::invalidateCacheOnRejudge($run);

        // Expire ranks
        \OmegaUp\Controllers\User::deleteProblemsSolvedRankCacheList();

        return $response;
    }

    /**
     * Disqualify a submission
     *
     * @return array{status: string}
     */
    public static function apiDisqualify(\OmegaUp\Request $r): array {
        // Get the user who is calling this API
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['run_alias'],
            'run_alias'
        );
        [
            'submission' => $submission,
        ] = self::validateDetailsRequest($r['run_alias']);

        if (
            !\OmegaUp\Authorization::canEditSubmission(
                $r->identity,
                $submission
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        \OmegaUp\DAO\Submissions::disqualify($submission->guid);

        // Expire ranks
        \OmegaUp\Controllers\User::deleteProblemsSolvedRankCacheList();
        return [
            'status' => 'ok'
        ];
    }

    /**
     * Invalidates relevant caches on run rejudge
     */
    public static function invalidateCacheOnRejudge(\OmegaUp\DAO\VO\Runs $run): void {
        try {
            // Expire details of the run
            \OmegaUp\Cache::deleteFromCache(
                \OmegaUp\Cache::RUN_ADMIN_DETAILS,
                $run->run_id
            );

            $submission = \OmegaUp\DAO\Submissions::getByPK(
                $run->submission_id
            );
            if (is_null($submission)) {
                return;
            }

            // Now we need to invalidate problem stats
            $problem = \OmegaUp\DAO\Problems::getByPK($submission->problem_id);

            if (!is_null($problem)) {
                // Invalidar cache stats
                \OmegaUp\Cache::deleteFromCache(
                    \OmegaUp\Cache::PROBLEM_STATS,
                    $problem->alias
                );
            }
        } catch (\Exception $e) {
            // We did our best effort to invalidate the cache...
            self::$log->warn(
                'Failed to invalidate cache on Rejudge, skipping: '
            );
            self::$log->warn($e);
        }
    }

    /**
     * Gets the details of a run. Includes admin details if admin.
     *
     * @return array{admin: bool, compile_error?: string, details?: array{compile_meta?: array<string, array{memory: float, sys_time: float, time: float, verdict: string, wall_time: float}>, contest_score: float, groups?: array<array-key, array{cases: array<array-key, array{contest_score: float, max_score: float, meta: array<string, mixed>, name: string, score: float, verdict: string}>, contest_score: float, group: string, max_score: float, score: float}>, judged_by: string, max_score?: float, memory?: float, score: float, time?: float, verdict: string, wall_time?: float}, guid: string, judged_by?: string, language: string, logs?: string, source?: string}
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        // Get the user who is calling this API
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['run_alias'],
            'run_alias'
        );
        [
            'run' => $run,
            'submission' => $submission,
        ] = self::validateDetailsRequest($r['run_alias']);

        $r['problem'] = \OmegaUp\DAO\Problems::getByPK(
            $submission->problem_id
        );
        if (is_null($r['problem'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (
            !\OmegaUp\Authorization::canViewSubmission(
                $r->identity,
                $submission
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        // Get the source
        $response = [
            'admin' => \OmegaUp\Authorization::isProblemAdmin(
                $r->identity,
                $r['problem']
            ),
            'guid' => strval($submission->guid),
            'language' => strval($submission->language),
        ];

        // Get the details, compile error, logs, etc.
        $details = self::getOptionalRunDetails(
            $submission,
            $run,
            /*$showDetails=*/(
                $response['admin'] ||
                \OmegaUp\DAO\Problems::isProblemSolved(
                    $r['problem'],
                    intval(
                        $r->identity->identity_id
                    )
                )
            )
        );
        $response['source'] = $details['source'];
        if (isset($details['compile_error'])) {
            $response['compile_error'] = $details['compile_error'];
        }
        if (isset($details['details'])) {
            $response['details'] = $details['details'];
        }
        if (!OMEGAUP_LOCKDOWN && $response['admin']) {
            $gzippedLogs = self::getGraderResource($run, 'logs.txt.gz');
            if (is_string($gzippedLogs)) {
                $response['logs'] = strval(gzdecode($gzippedLogs));
            }

            $response['judged_by'] = strval($run->judged_by);
        }

        return $response;
    }

    /**
     * Given the run alias, returns the source code and any compile errors if any
     * Used in the arena, any contestant can view its own codes and compile errors
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{compile_error?: string, details?: array{compile_meta?: array<string, array{memory: float, sys_time: float, time: float, verdict: string, wall_time: float}>, contest_score: float, groups?: array<array-key, array{cases: array<array-key, array{contest_score: float, max_score: float, meta: array<string, mixed>, name: string, score: float, verdict: string}>, contest_score: float, group: string, max_score: float, score: float}>, judged_by: string, max_score?: float, memory?: float, score: float, time?: float, verdict: string, wall_time?: float}, source: string}
     */
    public static function apiSource(\OmegaUp\Request $r): array {
        // Get the user who is calling this API
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['run_alias'],
            'run_alias'
        );
        [
            'run' => $run,
            'submission' => $submission,
        ] = self::validateDetailsRequest($r['run_alias']);

        if (
            !\OmegaUp\Authorization::canViewSubmission(
                $r->identity,
                $submission
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        return self::getOptionalRunDetails(
            $submission,
            $run,
            false
        );
    }

    /**
     * @return array{compile_error?: string, details?: array{compile_meta?: array<string, array{memory: float, sys_time: float, time: float, verdict: string, wall_time: float}>, contest_score: float, groups?: array<array-key, array{cases: array<array-key, array{contest_score: float, max_score: float, meta: array<string, mixed>, name: string, score: float, verdict: string}>, contest_score: float, group: string, max_score: float, score: float}>, judged_by: string, max_score?: float, memory?: float, score: float, time?: float, verdict: string, wall_time?: float}, source: string}
     */
    private static function getOptionalRunDetails(
        \OmegaUp\DAO\VO\Submissions $submission,
        \OmegaUp\DAO\VO\Runs $run,
        bool $showDetails
    ): array {
        $response = [];
        if (OMEGAUP_LOCKDOWN) {
            $response['source'] = 'lockdownDetailsDisabled';
        } else {
            $response['source'] = \OmegaUp\Controllers\Submission::getSource(
                strval($submission->guid)
            );
        }
        if (!$showDetails && $run->verdict != 'CE') {
            return $response;
        }
        $detailsJson = self::getGraderResource($run, 'details.json');
        if (!is_string($detailsJson)) {
            return $response;
        }
        /** @var array{compile_meta?: array<string, array{memory: float, sys_time: float, time: float, verdict: string, wall_time: float}>, contest_score: float, groups?: array<array-key, array{cases: array<array-key, array{contest_score: float, max_score: float, meta: array<string, mixed>, name: string, score: float, verdict: string}>, contest_score: float, group: string, max_score: float, score: float}>, judged_by: string, max_score?: float, memory?: float, score: float, time?: float, verdict: string, wall_time?: float} */
        $details = json_decode($detailsJson, true);
        if (
            isset($details['compile_error']) &&
            is_string($details['compile_error'])
        ) {
            $response['compile_error'] = $details['compile_error'];
        }
        if (!OMEGAUP_LOCKDOWN && $showDetails) {
            $response['details'] = $details;
        }
        return $response;
    }

    /**
     * Given the run alias, returns a .zip file with all the .out files generated for a run.
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiDownload(\OmegaUp\Request $r): void {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }
        // Get the user who is calling this API
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['run_alias'],
            'run_alias'
        );
        if (
            !self::downloadSubmission(
                $r['run_alias'],
                $r->identity,
                /*passthru=*/true
            )
        ) {
            http_response_code(404);
        }
        exit;
    }

    /**
     * @return bool|null|string
     */
    public static function downloadSubmission(
        string $guid,
        \OmegaUp\DAO\VO\Identities $identity,
        bool $passthru
    ) {
        $submission = \OmegaUp\DAO\Submissions::getByGuid($guid);
        if (
            is_null($submission) ||
            is_null($submission->current_run_id) ||
            is_null($submission->problem_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }

        $run = \OmegaUp\DAO\Runs::getByPK($submission->current_run_id);
        if (is_null($run)) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }

        $problem = \OmegaUp\DAO\Problems::getByPK($submission->problem_id);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (!\OmegaUp\Authorization::isProblemAdmin($identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        if ($passthru) {
            header('Content-Type: application/zip');
            header(
                "Content-Disposition: attachment; filename={$submission->guid}.zip"
            );
            return self::getGraderResourcePassthru($run, 'files.zip');
        }
        return self::getGraderResource($run, 'files.zip');
    }

    private static function getGraderResource(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename
    ): ?string {
        $result = \OmegaUp\Grader::getInstance()->getGraderResource(
            $run,
            $filename,
            /*missingOk=*/true
        );
        if (is_null($result)) {
            $result = self::downloadResourceFromS3(
                "{$run->run_id}/{$filename}",
                /*passthru=*/false
            );
        }
        return $result;
    }

    /**
     * @return bool|null|string
     */
    private static function getGraderResourcePassthru(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename
    ) {
        $result = \OmegaUp\Grader::getInstance()->getGraderResourcePassthru(
            $run,
            $filename,
            /*missingOk=*/true
        );
        if (is_null($result)) {
            $result = self::downloadResourceFromS3(
                "{$run->run_id}/{$filename}",
                /*passthru=*/true
            );
        }
        return $result;
    }

    /**
     * Given the run resouce path, fetches its contents from S3.
     *
     * @param  string $resourcePath The run's resource path.
     * @param  bool   $passthru     Whether to output directly.
     * @return ?string              The contents of the resource (or an empty string) if successful. null otherwise.
     */
    private static function downloadResourceFromS3(
        string $resourcePath,
        bool $passthru
    ): ?string {
        if (
            !defined('AWS_CLI_SECRET_ACCESS_KEY') ||
            empty(AWS_CLI_SECRET_ACCESS_KEY)
        ) {
            return null;
        }

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $proc = proc_open(
            AWS_CLI_BINARY . " s3 cp s3://omegaup-runs/{$resourcePath} -",
            $descriptorspec,
            $pipes,
            '/tmp',
            [
                'AWS_ACCESS_KEY_ID' => AWS_CLI_ACCESS_KEY_ID,
                'AWS_SECRET_ACCESS_KEY' => AWS_CLI_SECRET_ACCESS_KEY,
            ]
        );

        if (!is_resource($proc)) {
            $errors = error_get_last();
            if (is_null($errors)) {
                self::$log->error("Getting {$resourcePath} failed");
            } else {
                self::$log->error(
                    "Getting {$resourcePath} failed: {$errors['type']} {$errors['message']}"
                );
            }
            return null;
        }

        fclose($pipes[0]);
        $err = trim(stream_get_contents($pipes[2]));
        fclose($pipes[2]);
        if ($passthru) {
            fpassthru($pipes[1]);
            $result = '';
        } else {
            $result = stream_get_contents($pipes[1]);
        }
        fclose($pipes[1]);

        $retval = proc_close($proc);

        if ($retval != 0) {
            self::$log->error("Getting {$resourcePath} failed: $retval $err");
            return null;
        }
        return $result;
    }

    /**
     * Get total of last 6 months
     *
     * @return array{total: array<string, int>, ac: array<string, int>}
     */
    public static function apiCounts(\OmegaUp\Request $r) {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::RUN_COUNTS,
            '',
            function () use ($r) {
                $totals = [];
                $totals['total'] = [];
                $totals['ac'] = [];
                $runCounts = \OmegaUp\DAO\RunCounts::getAll(
                    1,
                    90,
                    'date',
                    'DESC'
                );

                foreach ($runCounts as $runCount) {
                    $totals['total'][strval(
                        $runCount->date
                    )] = $runCount->total;
                    $totals['ac'][strval(
                        $runCount->date
                    )] = $runCount->ac_count;
                }

                return $totals;
            },
            24 * 60 * 60 /*expire in 1 day*/
        );
    }

    /**
     * Validator for List API
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateList(\OmegaUp\Request $r): void {
        // Defaults for offset and rowcount
        if (!isset($r['offset'])) {
            $r['offset'] = 0;
        }
        if (!isset($r['rowcount'])) {
            $r['rowcount'] = 100;
        }

        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);
        \OmegaUp\Validators::validateInEnum(
            $r['status'],
            'status',
            ['new', 'waiting', 'compiling', 'running', 'ready'],
            false
        );
        \OmegaUp\Validators::validateInEnum(
            $r['verdict'],
            'verdict',
            \OmegaUp\Controllers\Run::VERDICTS,
            false
        );

        // Check filter by problem, is optional
        if (!is_null($r['problem_alias'])) {
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['problem_alias'],
                'problem'
            );

            $r['problem'] = \OmegaUp\DAO\Problems::getByAlias(
                $r['problem_alias']
            );
            if (is_null($r['problem'])) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }
        }

        \OmegaUp\Validators::validateInEnum(
            $r['language'],
            'language',
            array_keys(self::SUPPORTED_LANGUAGES),
            false
        );

        // Get user if we have something in username
        if (!is_null($r['username'])) {
            try {
                $r['identity'] = \OmegaUp\Controllers\Identity::resolveIdentity(
                    $r['username']
                );
            } catch (\OmegaUp\Exceptions\NotFoundException $e) {
                // If not found, simply ignore it
                $r['username'] = null;
                $r['identity'] = null;
            }
        }
    }

    /**
     * Gets a list of latest runs overall
     *
     * @return array{runs: list<array{alias: string, contest_alias: null|string, contest_score: float|null, country_id: null|string, guid: string, judged_by: null|string, language: string, memory: int, penalty: int, run_id: int, runtime: int, score: float, status: string, submit_delay: int, time: int, type: null|string, username: string, verdict: string}>}
     */
    public static function apiList(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();
        self::validateList($r);

        $runs = \OmegaUp\DAO\Runs::getAllRuns(
            null,
            $r['status'],
            $r['verdict'],
            !is_null($r['problem']) ? $r['problem']->problem_id : null,
            $r['language'],
            !is_null($r['identity']) ? $r['identity']->identity_id : null,
            $r['offset'],
            $r['rowcount']
        );

        $result = [];
        foreach ($runs as $run) {
            $run['time'] = intval($run['time']);
            $run['score'] = round(floatval($run['score']), 4);
            if (!is_null($run['contest_score'])) {
                $run['contest_score'] = round(
                    floatval(
                        $run['contest_score']
                    ),
                    2
                );
            }
            $result[] = $run;
        }

        return [
            'runs' => $result,
        ];
    }
}
