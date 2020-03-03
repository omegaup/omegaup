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
     *
     * @return array{isPractice: bool, problem: \OmegaUp\DAO\VO\Problems, contest: null|\OmegaUp\DAO\VO\Contests, problemsetContainer: null|\OmegaUp\DAO\VO\Contests|\OmegaUp\DAO\VO\Assignments|\OmegaUp\DAO\VO\Interviews, problemset: null|\OmegaUp\DAO\VO\Problemsets}
     */
    private static function validateCreateRequest(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        // https://github.com/omegaup/omegaup/issues/739
        if ($r->identity->username == 'omi') {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );

        // Check that problem exists
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem) || is_null($problem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if ($problem->deprecated) {
            throw new \OmegaUp\Exceptions\PreconditionFailedException(
                'problemDeprecated'
            );
        }
        // check that problem is not publicly or privately banned.
        if (
            $problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED ||
            $problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $allowedLanguages = array_intersect(
            array_keys(self::SUPPORTED_LANGUAGES),
            explode(',', $problem->languages)
        );
        \OmegaUp\Validators::validateInEnum(
            $r['language'],
            'language',
            $allowedLanguages
        );

        // Can't set both problemset_id and contest_alias at the same time.
        if (!empty($r['problemset_id']) && !empty($r['contest_alias'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'incompatibleArgs',
                'problemset_id and contest_alias'
            );
        }

        /** @var null|int */
        $problemsetId = null;
        /** @var null|\OmegaUp\DAO\VO\Contests|\OmegaUp\DAO\VO\Assignments|\OmegaUp\DAO\VO\Interviews */
        $problemsetContainer = null;
        /** @var null|\OmegaUp\DAO\VO\Contests */
        $contest = null;
        if (!empty($r['problemset_id'])) {
            // Got a problemset id directly.
            $problemsetId = intval($r['problemset_id']);
            $problemsetContainer = \OmegaUp\DAO\Problemsets::getProblemsetContainer(
                $problemsetId
            );
        } elseif (!empty($r['contest_alias'])) {
            // Got a contest alias, need to fetch the problemset id.
            // Validate contest
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['contest_alias'],
                'contest_alias'
            );
            $contest = \OmegaUp\DAO\Contests::getByAlias(
                $r['contest_alias']
            );
            if (is_null($contest)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterNotFound',
                    'contest_alias'
                );
            }

            $problemsetId = intval($contest->problemset_id);
            $problemsetContainer = $contest;

            // Update list of valid languages.
            if (!is_null($contest->languages)) {
                $allowedLanguages = array_intersect(
                    $allowedLanguages,
                    explode(',', $contest->languages)
                );
            }
        } else {
            // Check for practice or public problem, there is no contest info
            // in this scenario.
            if (
                \OmegaUp\DAO\Problems::isVisible($problem) ||
                \OmegaUp\Authorization::isProblemAdmin(
                    $r->identity,
                    $problem
                ) ||
                \OmegaUp\Time::get() > \OmegaUp\DAO\Problems::getPracticeDeadline(
                    $problem->problem_id
                )
            ) {
                if (
                    !\OmegaUp\DAO\Runs::isRunInsideSubmissionGap(
                        null,
                        null,
                        intval($problem->problem_id),
                        intval($r->identity->identity_id)
                    )
                        && !\OmegaUp\Authorization::isSystemAdmin($r->identity)
                ) {
                        throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                            'runWaitGap'
                        );
                }

                return [
                    'isPractice' => true,
                    'problem' => $problem,
                    'contest' => null,
                    'problemsetContainer' => null,
                    'problemset' => null,
                ];
            } else {
                throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                    'problemIsNotPublic'
                );
            }
        }
        if (is_null($problemsetContainer)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        $problemset = \OmegaUp\DAO\Problemsets::getByPK($problemsetId);
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'problemset_id'
            );
        }

        // Validate the language.
        if (!is_null($problemset->languages)) {
            $allowedLanguages = array_intersect(
                $allowedLanguages,
                explode(',', $problemset->languages)
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
                $problemsetId,
                $problem->problem_id
            )
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'problem_alias'
            );
        }

        $problemsetIdentity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $r->identity->identity_id,
            $problemsetId
        );

        // No one should submit after the deadline. Not even admins.
        if (
            \OmegaUp\DAO\Problemsets::isLateSubmission(
                $problemsetContainer,
                $problemsetIdentity
            )
        ) {
            throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                'runNotInsideContest'
            );
        }

        // Contest admins can skip following checks
        if (!\OmegaUp\Authorization::isAdmin($r->identity, $problemset)) {
            // Before submit something, user had to open the problem/problemset.
            if (
                is_null($problemsetIdentity) &&
                !\OmegaUp\Authorization::canSubmitToProblemset(
                    $r->identity,
                    $problemset
                )
            ) {
                throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                    'runNotEvenOpened'
                );
            }

            // Validate that the run is timely inside contest
            if (
                !\OmegaUp\DAO\Problemsets::isSubmissionWindowOpen(
                    $problemsetContainer
                )
            ) {
                throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                    'runNotInsideContest'
                );
            }

            // Validate if the user is allowed to submit given the submissions_gap
            if (
                !\OmegaUp\DAO\Runs::isRunInsideSubmissionGap(
                    intval($problemsetId),
                    $contest,
                    intval($problem->problem_id),
                    intval($r->identity->identity_id)
                )
            ) {
                throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                    'runWaitGap'
                );
            }
        }

        return [
            'isPractice' => false,
            'problem' => $problem,
            'contest' => $contest,
            'problemset' => $problemset,
            'problemsetContainer' => $problemsetContainer,
        ];
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
        \OmegaUp\Validators::validateStringNonEmpty($r['source'], 'source');
        [
            'isPractice' => $isPractice,
            'problem' => $problem,
            'contest' => $contest,
            'problemsetContainer' => $problemsetContainer,
            'problemset' => $problemset,
        ] = self::validateCreateRequest($r);

        self::$log->info('New run being submitted!!');
        $response = [];

        /** @var null|int */
        $problemsetId = null;
        if ($isPractice) {
            if (OMEGAUP_LOCKDOWN) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'lockdown'
                );
            }
            $submitDelay = 0;
            $type = 'normal';
        } else {
            $problemsetId = !is_null(
                $problemset
            ) ? intval(
                $problemset->problemset_id
            ) : null;
            //check the kind of penalty_type for this contest
            $start = null;
            if (!is_null($contest) && !is_null($problemsetId)) {
                switch ($contest->penalty_type) {
                    case 'contest_start':
                        // submit_delay is calculated from the start
                        // of the contest
                        $start = $contest->start_time;
                        break;

                    case 'problem_open':
                        // submit delay is calculated from the
                        // time the user opened the problem
                        $opened = \OmegaUp\DAO\ProblemsetProblemOpened::getByPK(
                            $problemsetId,
                            intval($problem->problem_id),
                            $r->identity->identity_id
                        );

                        if (is_null($opened)) {
                            // welp, the user is submitting a run before even
                            // opening the problem!
                            throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                                'runEvenOpened'
                            );
                        }

                        $start = $opened->open_time;
                        break;

                    case 'none':
                    case 'runtime':
                        // we don't care about problemset start.
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
                // assuming submit_delay is in minutes.
                $submitDelay = intval((\OmegaUp\Time::get() - $start) / 60);
            } else {
                $submitDelay = 0;
            }

            // If user is admin and is in virtual contest, then admin will be treated as contestant
            $type = (
                !is_null($problemset) &&
                !is_null($contest) &&
                \OmegaUp\Authorization::isAdmin(
                    $r->identity,
                    $problemset
                ) &&
                !\OmegaUp\DAO\Contests::isVirtual(
                    $contest
                )
            ) ? 'test' : 'normal';
        }

        // Populate new run+submission object
        $submission = new \OmegaUp\DAO\VO\Submissions([
            'identity_id' => $r->identity->identity_id,
            'problem_id' => $problem->problem_id,
            'problemset_id' => $problemsetId,
            'guid' => md5(uniqid(strval(rand()), true)),
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
            'version' => $problem->current_version,
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
            'ip' => ip2long(strval($_SERVER['REMOTE_ADDR']))
        ]));

        $problem->submissions++;
        \OmegaUp\DAO\Problems::update($problem);

        if ($isPractice) {
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
            } elseif (isset($problemsetContainer->finish_time)) {
                $response['submission_deadline'] =
                    intval($problemsetContainer->finish_time);
            } else {
                $response['submission_deadline'] = 0;
            }
        }

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
        if (is_null($submission) || is_null($submission->current_run_id)) {
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

        \OmegaUp\DAO\Submissions::disqualify(strval($submission->guid));

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
                strval($run->run_id)
            );

            $submission = \OmegaUp\DAO\Submissions::getByPK(
                intval($run->submission_id)
            );
            if (is_null($submission) || is_null($submission->problem_id)) {
                return;
            }

            // Now we need to invalidate problem stats
            $problem = \OmegaUp\DAO\Problems::getByPK($submission->problem_id);

            if (!is_null($problem) && !is_null($problem->alias)) {
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

        $problem = \OmegaUp\DAO\Problems::getByPK(
            intval($submission->problem_id)
        );
        if (is_null($problem)) {
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
                $problem
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
                    $problem,
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

        if (strpos($resourcePath, '/') !== 0) {
            $resourcePath = "/{$resourcePath}";
        }
        $accessKeyId = strval(AWS_CLI_ACCESS_KEY_ID);
        $secretAccessKey = strval(AWS_CLI_SECRET_ACCESS_KEY);
        $regionName = 'us-east-1';
        $bucketName = 'omegaup-runs';
        $serviceName = 's3';
        $signingAlgorithm = 'AWS4-HMAC-SHA256';

        $now = \OmegaUp\Time::get();
        $datestamp = gmstrftime('%Y%m%d', $now);
        $timestamp = gmstrftime('%Y%m%dT%H%M%SZ', $now);
        $emptySHA256 = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';
        $headers = [
            'Host' => "{$bucketName}.{$serviceName}.amazonaws.com",
            'X-Amz-Content-Sha256' => $emptySHA256,
            'X-Amz-Date' => $timestamp,
        ];
        $signedHeaders = join(
            ';',
            array_map(
                function (string $key): string {
                    return strtolower($key);
                },
                array_keys($headers)
            )
        );
        $canonicalRequest = join(
            "\n",
            [
                'GET',
                $resourcePath,
                '',
                join(
                    '',
                    array_map(
                        function (string $key) use ($headers): string {
                            return strtolower($key) . ":{$headers[$key]}\n";
                        },
                        array_keys($headers)
                    )
                ),
                $signedHeaders,
                $emptySHA256,
            ]
        );

        $scope = "{$datestamp}/{$regionName}/{$serviceName}/aws4_request";
        $stringToSign = join(
            "\n",
            [
                $signingAlgorithm,
                $timestamp,
                $scope,
                hash('sha256', $canonicalRequest),
            ]
        );

        $dateSignature = hash_hmac(
            'sha256',
            $datestamp,
            "AWS4{$secretAccessKey}",
            true
        );
        $regionSignature = hash_hmac(
            'sha256',
            $regionName,
            $dateSignature,
            true
        );
        $serviceSignature = hash_hmac(
            'sha256',
            $serviceName,
            $regionSignature,
            true
        );
        $signingKey = hash_hmac(
            'sha256',
            'aws4_request',
            $serviceSignature,
            true
        );
        $signature = hash_hmac(
            'sha256',
            $stringToSign,
            $signingKey,
            false
        );
        $headers['Authorization'] = "{$signingAlgorithm} Credential={$accessKeyId}/{$scope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => "https://{$headers['Host']}{$resourcePath}",
                CURLOPT_HTTPHEADER => array_map(
                    function (string $key) use ($headers): string {
                        return "{$key}: {$headers[$key]}";
                    },
                    array_keys($headers)
                ),
                CURLOPT_RETURNTRANSFER => intval(!$passthru),
            ]
        );

        $output = curl_exec($curl);
        if ($passthru) {
            $result = '';
        } else {
            $result = strval($output);
        }
        /** @var int */
        $retval = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($output === false || $retval != 200) {
            self::$log->error("Getting {$resourcePath} failed: {$output}");
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
     * Get total of runs have been submitted last 3 months.
     *
     * @return array{date: list<string>, total: list<int>}
     */
    public static function getCounts() {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::RUN_TOTAL_COUNTS,
            '',
            function () {
                $result = [];
                $result['date'] = [];
                $result['total'] = [];
                $runCounts = \OmegaUp\DAO\RunCounts::getAll(
                    1,
                    90,
                    'date',
                    'DESC'
                );

                foreach ($runCounts as $runCount) {
                    $result['date'][] = strval($runCount->date);
                    $result['total'][] = intval($runCount->total);
                }

                return $result;
            },
            24 * 60 * 60 /*expire in 1 day*/
        );
    }

    /**
     * Validator for List API
     *
     * @return array{problem: null|\OmegaUp\DAO\VO\Problems, identity: null|\OmegaUp\DAO\VO\Identities}
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        // Check filter by problem, is optional
        /** @var null|\OmegaUp\DAO\VO\Problems */
        $problem = null;
        if (!is_null($r['problem_alias'])) {
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['problem_alias'],
                'problem'
            );

            $problem = \OmegaUp\DAO\Problems::getByAlias(
                $r['problem_alias']
            );
            if (is_null($problem)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }
        }

        // Get user if we have something in username
        /** @var null|\OmegaUp\DAO\VO\Identities */
        $identity = null;
        if (!is_null($r['username'])) {
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['username'],
                'username'
            );
            try {
                $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
                    $r['username']
                );
            } catch (\OmegaUp\Exceptions\NotFoundException $e) {
                // If not found, simply ignore it
            }
        }

        return [
            'problem' => $problem,
            'identity' => $identity,
        ];
    }

    /**
     * Gets a list of latest runs overall
     *
     * @return array{runs: list<array{alias: string, contest_alias: null|string, contest_score: float|null, country_id: null|string, guid: string, judged_by: null|string, language: string, memory: int, penalty: int, run_id: int, runtime: int, score: float, status: string, submit_delay: int, time: int, type: null|string, username: string, verdict: string}>}
     */
    public static function apiList(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        // Defaults for offset and rowcount
        $r->ensureInt('offset', null, null, false);
        if (!isset($r['offset'])) {
            $r['offset'] = 0;
        }
        $r->ensureInt('rowcount', null, null, false);
        if (!isset($r['rowcount'])) {
            $r['rowcount'] = 100;
        }

        \OmegaUp\Validators::validateOptionalInEnum(
            $r['status'],
            'status',
            ['new', 'waiting', 'compiling', 'running', 'ready']
        );
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['verdict'],
            'verdict',
            \OmegaUp\Controllers\Run::VERDICTS
        );

        \OmegaUp\Validators::validateOptionalInEnum(
            $r['language'],
            'language',
            array_keys(self::SUPPORTED_LANGUAGES)
        );

        [
            'problem' => $problem,
            'identity' => $identity,
        ] = self::validateList($r);

        $runs = \OmegaUp\DAO\Runs::getAllRuns(
            null,
            $r['status'],
            $r['verdict'],
            !is_null($problem) ? $problem->problem_id : null,
            $r['language'],
            !is_null($identity) ? $identity->identity_id : null,
            intval($r['offset']),
            intval($r['rowcount'])
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
