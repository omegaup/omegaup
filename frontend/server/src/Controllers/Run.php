<?php

 namespace OmegaUp\Controllers;

/**
 * RunController
 *
 * @psalm-type ProblemCasesContents=array<string, array{contestantOutput?: string, in: string, out: string}>
 * @psalm-type RunMetadata=array{memory: int, sys_time: int, time: float, verdict: string, wall_time: float}
 * @psalm-type CaseResult=array{contest_score: float, max_score: float, meta: RunMetadata, name: string, out_diff?: string, score: float, verdict: string}
 * @psalm-type RunDetailsGroup=array{cases: list<CaseResult>, contest_score: float, group: string, max_score: float, score: float, verdict?: string}
 * @psalm-type SubmissionFeedbackThread=array{author: string, authorClassname: string, submission_feedback_thread_id: int, text: string, timestamp: \OmegaUp\Timestamp}
 * @psalm-type SubmissionFeedback=array{author: string, author_classname: string, feedback: string, date: \OmegaUp\Timestamp, range_bytes_end: int|null, range_bytes_start: int|null, submission_feedback_id: int, feedback_thread?: list<SubmissionFeedbackThread>}
 * @psalm-type RunDetails=array{admin: bool, alias: string, cases: ProblemCasesContents, compile_error?: string, details?: array{compile_meta?: array<string, RunMetadata>, contest_score: float, groups?: list<RunDetailsGroup>, judged_by: string, max_score?: float, memory?: float, score: float, time?: float, verdict: string, wall_time?: float}, feedback?: string, guid: string, judged_by?: string, language: string, logs?: string, show_diff: string, source?: string, source_link?: bool, source_name?: string, source_url?: string, feedback: list<SubmissionFeedback>}
 * @psalm-type Run=array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, execution: null|string, guid: string, language: string, memory: int, output: null|string, penalty: int, runtime: int, score: float, score_by_group?: array<string, float|null>, status: string, status_memory: null|string, status_runtime: null|string, submit_delay: int, suggestions?: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}
 */
class Run extends \OmegaUp\Controllers\Controller {
    // this variable is not used anywhere
    public const LANGUAGE_ALIASES = [
        'c' => 'C11 (gcc 9.3)',
        'cpp' => 'C++03 (gcc 9.3)',
        'cpp11' => 'C++11 (gcc 9.3)',
        'py' => 'Python 2.7',
    ];
    /** @var int */
    public static $defaultSubmissionGap = 60; // seconds.
    /** @var int */
    public static $defaultExecutionGap = 30; // initial default execution gap.

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

    public const EXECUTION = [
        'EXECUTION_INTERRUPTED' => ['ML', 'MLE', 'TLE', 'OLE', 'TO', 'OL'],
        'EXECUTION_FINISHED' => ['AC', 'WA', 'PA'],
        'EXECUTION_RUNTIME_ERROR' => ['RE', 'RTE'],
        'EXECUTION_RUNTIME_FUNCTION_ERROR' => ['OF', 'RFE'],
        'EXECUTION_COMPILATION_ERROR' => ['CE'],
        'EXECUTION_VALIDATOR_ERROR' => ['VE'],
        'EXECUTION_JUDGE_ERROR' => ['JE'],
    ];

    public const OUTPUT = [
        'OUTPUT_INTERRUPTED' => ['JE', 'VE', 'CE', 'FO', 'RFE', 'RE', 'RTE', 'MLE', 'TLE'],
        'OUTPUT_CORRECT' => ['AC'],
        'OUTPUT_INCORRECT' => ['WA', 'PA'],
        'OUTPUT_EXCEEDED' => ['OLE', 'OL'],
    ];

    public const STATUS = ['new', 'waiting', 'compiling', 'running', 'ready'];

    /**
     *
     * @return array<string, string>
     */
    public static function SUPPORTED_LANGUAGES(): array {
        static $supportedLanguages = [];

        if (empty($supportedLanguages)) {
            $languagesFile = __DIR__ . '/../../../data/languages.json';
            $languagesJsonArray = json_decode(
                file_get_contents(
                    $languagesFile
                ),
                true
            );
            foreach ($languagesJsonArray as $key => $languageInfo) {
                $supportedLanguages[$key] = $languageInfo['name'];
            }
        }

        return $supportedLanguages;
    }

    /**
     *
     * @return list<string>
     */
    public static function DEFAULT_LANGUAGES(): array {
        static $defaultLanguages = [];

        if (empty($defaultLanguages)) {
            $supportedLanguages = self::SUPPORTED_LANGUAGES();
            $filterList = ['cat', 'kp', 'kj'];

            $defaultLanguages = array_keys(array_filter($supportedLanguages, function (
                $languageName,
                $key
            ) use ($filterList) {
                return !in_array($key, $filterList);
            }, ARRAY_FILTER_USE_BOTH));
        }

        return $defaultLanguages;
    }

    /**
     * Validates that a run is happening within the submission gap.
     *
     * This needs to be called within the transaction that creates the run to
     * avoid races where multiple concurrent requests can go in and multiple
     * runs be created.
     */
    private static function validateWithinSubmissionGap(
        \OmegaUp\DAO\VO\Submissions $submission,
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem,
        ?\OmegaUp\DAO\VO\Contests $contest
    ): void {
        $isInsideSubmissionGap = \OmegaUp\DAO\Submissions::isInsideSubmissionGap(
            $submission,
            intval($problem->problem_id),
            intval($identity->identity_id),
            $contest
        );
        if (is_null($contest)) {
            $isAdmin = \OmegaUp\Authorization::isSystemAdmin($identity);
        } else {
            $isAdmin = \OmegaUp\Authorization::isAdmin($identity, $contest);
        }

        if (!$isInsideSubmissionGap && !$isAdmin) {
            throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                'runWaitGap'
            );
        }
    }

    /**
     * Validates Create Run request
     *
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\NotAllowedToSubmitException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{isPractice: bool, problem: \OmegaUp\DAO\VO\Problems, contest: null|\OmegaUp\DAO\VO\Contests, problemsetContainer: null|\OmegaUp\DAO\VO\Contests|\OmegaUp\DAO\VO\Assignments, problemset: null|\OmegaUp\DAO\VO\Problemsets}
     *
     * @omegaup-request-param null|string $contest_alias
     * @omegaup-request-param string $language
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param int|null $problemset_id
     */
    private static function validateCreateRequest(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        // https://github.com/omegaup/omegaup/issues/739
        if ($r->identity->username == 'omi') {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        // Check that problem exists
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
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

        /** @var list<string> $allowedLanguages */
        $allowedLanguages = array_intersect(
            array_keys(self::SUPPORTED_LANGUAGES()),
            explode(',', $problem->languages)
        );
        $language = $r->ensureString('language');
        /** @psalm-suppress RedundantCondition don't know how to express "this is in the enum" */
        \OmegaUp\Validators::validateInEnum(
            $language,
            'language',
            $allowedLanguages
        );
        $problemsetId = $r->ensureOptionalInt('problemset_id');
        $contestAlias = $r->ensureOptionalString(
            'contest_alias',
            required: false,
            validator: fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        // Can't set both problemset_id and contest_alias at the same time.
        if (!is_null($problemsetId) && !is_null($contestAlias)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'incompatibleArgs',
                'problemset_id and contest_alias'
            );
        }

        /** @var null|\OmegaUp\DAO\VO\Contests|\OmegaUp\DAO\VO\Assignments */
        $problemsetContainer = null;
        /** @var null|\OmegaUp\DAO\VO\Contests */
        $contest = null;
        if (!is_null($problemsetId)) {
            $problemsetContainer = \OmegaUp\DAO\Problemsets::getProblemsetContainer(
                $problemsetId
            );
        } elseif (!is_null($contestAlias)) {
            // Got a contest alias, need to fetch the problemset id.
            // Validate contest
            $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
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
                /** @var list<string> $allowedLanguages */
                $allowedLanguages = array_intersect(
                    $allowedLanguages,
                    explode(',', $contest->languages)
                );
            }
        } else {
            // Check for practice or public problem, there is no contest info
            // in this scenario.
            $practiceDeadline = \OmegaUp\DAO\Problems::getPracticeDeadline(
                $problem->problem_id
            );
            if (
                !\OmegaUp\DAO\Problems::isVisible($problem) &&
                !\OmegaUp\Authorization::isProblemAdmin(
                    $r->identity,
                    $problem
                ) &&
                !(
                    is_null($practiceDeadline) ||
                    \OmegaUp\Time::get() > $practiceDeadline->time
                )
            ) {
                throw new \OmegaUp\Exceptions\NotAllowedToSubmitException(
                    'problemIsNotPublic'
                );
            }

            return [
                'isPractice' => true,
                'problem' => $problem,
                'contest' => null,
                'problemsetContainer' => null,
                'problemset' => null,
            ];
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
            /** @var list<string> $allowedLanguages */
            $allowedLanguages = array_intersect(
                $allowedLanguages,
                explode(',', $problemset->languages)
            );
        }
        \OmegaUp\Validators::validateInEnum(
            $r->ensureString('language'),
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
     * Get the next execution timestamp, no user session required, as the IDE
     * runs independently.
     *
     * @return array{nextExecutionTimestamp: \OmegaUp\Timestamp}
     */
    public static function apiExecuteForIDE(\OmegaUp\Request $r): array {
        return [
            'nextExecutionTimestamp' => \OmegaUp\DAO\Runs::nextExecutionTimestamp(
                lastExecutionTime: new \OmegaUp\Timestamp(\OmegaUp\Time::get()),
            )
        ];
    }

    /**
     * Get the next execution timestamp for a specific problemset:
     * - Contest
     * - Virtual contest
     * - Practice contest
     * - Course
     *
     * @return array{nextExecutionTimestamp: \OmegaUp\Timestamp}
     */
    public static function apiExecute(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        return [
            'nextExecutionTimestamp' => \OmegaUp\DAO\Runs::nextExecutionTimestamp(
                lastExecutionTime: new \OmegaUp\Timestamp(\OmegaUp\Time::get()),
            )
        ];
    }

    /**
     * Create a new run
     *
     * @throws \Exception
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{guid: string, submit_delay: int, submission_deadline: \OmegaUp\Timestamp, nextSubmissionTimestamp: \OmegaUp\Timestamp}
     *
     * @omegaup-request-param null|string $contest_alias
     * @omegaup-request-param string $language
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param int|null $problemset_id
     * @omegaup-request-param string $source
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        // Authenticate user
        $r->ensureIdentity();

        // Validate request
        $source = $r->ensureString('source');
        [
            'isPractice' => $isPractice,
            'problem' => $problem,
            'contest' => $contest,
            'problemsetContainer' => $problemsetContainer,
            'problemset' => $problemset,
        ] = self::validateCreateRequest($r);

        self::$log->info('New run being submitted!!');

        /** @var null|int */
        $problemsetId = null;
        if ($isPractice) {
            \OmegaUp\Controllers\Controller::ensureNotInLockdown();
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
                                'runNotEvenOpened'
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
                            'penalty_type for this contests is not a valid option, assuming `none`.'
                        );
                        $start = null;
                }
            }

            if (!is_null($start)) {
                // assuming submit_delay is in minutes.
                $submitDelay = intval(
                    (\OmegaUp\Time::get() - $start->time) / 60
                );
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
            'language' => $r->ensureString('language'),
            'time' => \OmegaUp\Time::get(),
            'status' => 'uploading',
            'verdict' => 'JE',
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
            'commit' => $problem->commit,
            'status' => 'uploading',
            'runtime' => 0,
            'penalty' => $submitDelay,
            'time' => $submission->time,
            'memory' => 0,
            'score' => 0,
            'contest_score' => !is_null($problemsetId) ? 0 : null,
            'verdict' => 'JE',
        ]);

        // Use TransactionHelper to handle potential deadlocks in submission creation
        \OmegaUp\TransactionHelper::executeWithRetry(function () use (
            $submission,
            $r,
            $problem,
            $contest,
            $run
        ) {
            // _Now_ that we are in a transaction, we can check whether the run
            // is within the submission gap.
            self::validateWithinSubmissionGap(
                $submission,
                $r->identity,
                $problem,
                $contest
            );

            // Push run into DB
            \OmegaUp\DAO\Submissions::create($submission);
            $run->submission_id = $submission->submission_id;
            \OmegaUp\DAO\Runs::create($run);
            $submission->current_run_id = $run->run_id;
            \OmegaUp\DAO\Submissions::update($submission);
        });

        // Call Grader
        try {
            \OmegaUp\Grader::getInstance()->grade($run, trim($source));
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
            self::$log->error(
                'Call to \OmegaUp\Grader::grade() failed',
                ['exception' => $e],
            );
            throw $e;
        }
        $userId = $r->identity->user_id;
        if (is_null($userId) && !is_null($r->loginIdentity)) {
            $userId = $r->loginIdentity->user_id;
        }

        \OmegaUp\DAO\SubmissionLog::create(new \OmegaUp\DAO\VO\SubmissionLog([
            'user_id' => $userId,
            'identity_id' => $r->identity->identity_id,
            'submission_id' => $submission->submission_id,
            'problemset_id' => $submission->problemset_id,
            'ip' => ip2long(
                \OmegaUp\Request::getServerVar('REMOTE_ADDR') ?? ''
            ),
        ]));

        $problem->submissions++;
        \OmegaUp\DAO\Problems::update($problem);

        $response = [
            'guid' => strval($submission->guid),
            'submit_delay' => $submitDelay,
        ];
        if ($isPractice) {
            $response['submission_deadline'] = new \OmegaUp\Timestamp(0);
        } else {
            // Add remaining time to the response
            $problemsetIdentity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
                $r->identity->identity_id,
                $problemsetId
            );
            if (
                !is_null($problemsetIdentity) &&
                !is_null($problemsetIdentity->end_time)
            ) {
                $response['submission_deadline'] = $problemsetIdentity->end_time;
            } elseif (isset($problemsetContainer->finish_time)) {
                /** @var \OmegaUp\Timestamp $problemsetContainer->finish_time */
                $response['submission_deadline'] = new \OmegaUp\Timestamp(
                    $problemsetContainer->finish_time
                );
            } else {
                $response['submission_deadline'] = new \OmegaUp\Timestamp(0);
            }
        }

        // Happy ending
        $response['nextSubmissionTimestamp'] = \OmegaUp\DAO\Runs::nextSubmissionTimestamp(
            $contest,
            lastSubmissionTime: $submission->time
        );

        if (is_null($submission->guid)) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }

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
     * @return Run
     *
     * @omegaup-request-param string $run_alias
     * @omegaup-request-param null|string $username
     */
    public static function apiStatus(\OmegaUp\Request $r): array {
        // Get the user who is calling this API
        $r->ensureIdentity();

        $runAlias = $r->ensureString(
            'run_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $username = $r->ensureOptionalString(
            'username',
            false
        );
        $submission = \OmegaUp\DAO\Submissions::getByGuid($runAlias);
        if (
            is_null(
                $submission
            ) || is_null(
                $submission->current_run_id
            ) || is_null(
                $submission->guid
            )
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }

        $run = \OmegaUp\DAO\Runs::getByGUID(
            $submission->guid
        );
        if (is_null($run)) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }
        $problem = \OmegaUp\DAO\Problems::getByPK(
            intval($submission->problem_id)
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problemset = null;
        if (!is_null($submission->problemset_id)) {
            $problemset = \OmegaUp\DAO\Problemsets::getByPK(
                $submission->problemset_id
            );
        }
        $contest = null;
        if (!is_null($problemset) && !is_null($problemset->contest_id)) {
            $contest = \OmegaUp\DAO\Contests::getByPK($problemset->contest_id);
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

        // Fill response
        $filtered = (
            $submission->asFilteredArray([
                'guid', 'language', 'time', 'submit_delay', 'type',
            ]) +
            $run
        );
        /** @var \OmegaUp\Timestamp $filtered['time'] */
        $result = [
            'guid' => strval($filtered['guid']),
            'alias' => strval($problem->alias),
            'contest_alias' => null,
            'time' => new \OmegaUp\Timestamp($filtered['time']),
            'contest_score' => null,
            'score' => round(floatval($filtered['score']), 4),
            'runtime' => intval($filtered['runtime']),
            'penalty' => intval($filtered['penalty']),
            'memory' => intval($filtered['memory']),
            'submit_delay' => intval($filtered['submit_delay']),
            'language' => strval($filtered['language']),
            'status' => strval($filtered['status']),
            'type' => strval($filtered['type']),
            'verdict' => strval($filtered['verdict']),
            'country' => 'xx',
            'username' => (
                ($submission->identity_id == $r->identity->identity_id) ?
                $r->identity->username
                : $username ?? ''
            ),
            'classname' => 'user-rank-unranked',
            'execution' => strval($filtered['execution']),
            'output' => strval($filtered['output']),
            'status_memory' => strval($filtered['status_memory']),
            'status_runtime' => strval($filtered['status_runtime']),
        ];
        if (!is_null($filtered['contest_score'])) {
            if (
                is_null($contest)
                || $contest->score_mode == 'partial'
                || $filtered['score'] == 1
            ) {
                $result['contest_score'] = round(
                    floatval($filtered['contest_score']),
                    2
                );
            } else {
                $result['contest_score'] = 0.0;
                $result['score'] = 0.0;
            }
        }
        return $result;
    }

    /**
     * Re-sends a problem to Grader.
     *
     * @return array{status: string}
     *
     * @omegaup-request-param bool|null $debug
     * @omegaup-request-param string $run_alias
     */
    public static function apiRejudge(\OmegaUp\Request $r): array {
        // Get the user who is calling this API
        $r->ensureIdentity();

        $runAlias = $r->ensureString(
            'run_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        [
            'run' => $run,
            'submission' => $submission,
        ] = self::validateDetailsRequest($runAlias);

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

        if ($run->status == 'new' || $run->status == 'waiting') {
            self::$log->info('Run already in the rejudge queue. Ignoring');
            return ['status' => 'ok'];
        }

        self::$log->info("Run {$run->run_id} being rejudged");

        // Reset fields.
        $run->status = 'new';
        try {
            \OmegaUp\DAO\DAO::transBegin();
            \OmegaUp\DAO\Runs::update($run);
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        try {
            \OmegaUp\Grader::getInstance()->rejudge(
                [$run],
                $r->ensureOptionalBool('debug') ?? false
            );
        } catch (\Exception $e) {
            self::$log->error(
                'Call to \OmegaUp\Grader::rejudge() failed',
                ['exception' => $e],
            );
        }

        self::invalidateCacheOnRejudge($run);

        // Expire ranks
        \OmegaUp\Controllers\User::deleteProblemsSolvedRankCacheList();

        return ['status' => 'ok'];
    }

    /**
     * Disqualify one or more submissions based on the received parameters:
     *
     * - When a run_alias is provided, it will only disqualify a single
     *   submission.
     * - When run_alias is not provided, both the username and the contest_alias
     *   are required.
     * - If a problem_alias is provided, all submissions belonging to the user
     *   for this problem and contest will be disqualified.
     * - If a problem_alias is not provided, all submissions belonging to the
     *   user in this contest will be disqualified.
     *
     * @return array{status: string, runs: list<array{guid: null|string, username: null|string}>}
     *
     * @omegaup-request-param null|string $run_alias
     * @omegaup-request-param null|string $problem_alias
     * @omegaup-request-param null|string $contest_alias
     * @omegaup-request-param null|string $username
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function apiDisqualify(\OmegaUp\Request $r): array {
        // Get the user who is calling this API
        $r->ensureMainUserIdentity();

        $runAlias = $r->ensureOptionalString(
            'run_alias',
            false,
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problemAlias = $r->ensureOptionalString(
            'problem_alias',
            false,
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contestAlias = $r->ensureOptionalString(
            'contest_alias',
            false,
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $username = $r->ensureOptionalString(
            'username',
            false,
            fn (string $user) => \OmegaUp\Validators::normalUsername(
                $user
            ) || \OmegaUp\Validators::identityUsername(
                $user
            ) || \OmegaUp\Validators::identityTeamUsername(
                $user
            )
        );
        $runs = [];
        if (!is_null($runAlias)) {
            [
                'submission' => $submission,
            ] = self::validateDetailsRequest($runAlias);

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

            if ($submission->type !== 'normal') {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'runCannotBeDisqualified'
                );
            }
            \OmegaUp\DAO\Submissions::disqualify($submission);
            $runs[] = ['guid' => $runAlias, 'username' => $username];
        } elseif (!is_null($username)) {
            if (is_null($contestAlias)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterNotFound',
                    'contest_alias'
                );
            }
            $submissions = \OmegaUp\DAO\Submissions::getAllSubmissionsByUserContest(
                $username,
                $contestAlias,
                $problemAlias
            );
            foreach ($submissions as $submission) {
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
                \OmegaUp\DAO\Submissions::disqualify($submission);
                $runs[] = ['guid' => $submission->guid, 'username' => $username];
            }
        }

        // Expire ranks
        \OmegaUp\Controllers\User::deleteProblemsSolvedRankCacheList();
        return [
            'status' => 'ok',
            'runs' => $runs,
        ];
    }

    /**
     * Requalify a submission previously disqualified
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $run_alias
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function apiRequalify(\OmegaUp\Request $r): array {
        // Get the user who is calling this API
        $r->ensureMainUserIdentity();

        $runAlias = $r->ensureString(
            'run_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        [
            'submission' => $submission,
        ] = self::validateDetailsRequest($runAlias);

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

        if ($submission->type !== 'disqualified') {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'runCannotBeRequalified'
            );
        }

        \OmegaUp\DAO\Submissions::requalify($submission);

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
            self::$log->warning(
                "Failed to invalidate cache on rejudge {$run->run_id}, skipping",
                ['exception' => $e],
            );
        }
    }

    /**
     * Get all the comments related to a submission feedback
     *
     * @return list<SubmissionFeedback>
     *
     * @omegaup-request-param string $run_alias
     */
    public static function apiGetSubmissionFeedback(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        [
            'submission' => $submission,
        ] = self::validateDetailsRequest(
            $r->ensureString(
                'run_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            )
        );

        return \OmegaUp\DAO\SubmissionFeedback::getSubmissionFeedback(
            $submission
        );
    }

    /**
     * Gets the details of a run. Includes admin details if admin.
     *
     * @return RunDetails
     *
     * @omegaup-request-param string $run_alias
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        [
            'run' => $run,
            'submission' => $submission,
        ] = self::validateDetailsRequest(
            $r->ensureString(
                'run_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            )
        );

        if (is_null($submission->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (is_null($run->commit) || is_null($run->version)) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }

        $problem = \OmegaUp\DAO\Problems::getByPK($submission->problem_id);
        if (is_null($problem) || is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $problemset = null;
        if (!is_null($submission->problemset_id)) {
            $problemset = \OmegaUp\DAO\Problemsets::getByPK(
                $submission->problemset_id
            );
        }

        $contest = null;
        if (!is_null($problemset) && !is_null($problemset->contest_id)) {
            $contest = \OmegaUp\DAO\Contests::getByPK($problemset->contest_id);
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
            'alias' => strval($problem->alias),
            'feedback' => \OmegaUp\DAO\SubmissionFeedback::getSubmissionFeedback(
                $submission
            ),
        ];

        $showRunDetails = !$response['admin'] ? self::shouldShowRunDetails(
            $r->identity->identity_id,
            $problem,
            $contest
        ) : 'detailed';

        // Get the details, compile error, logs, etc.
        $details = self::getOptionalRunDetails(
            $submission,
            $run,
            $contest,
            showDetails: $showRunDetails !== 'none'
        );

        $response['source'] = $details['source'];
        if (isset($details['compile_error'])) {
            $response['compile_error'] = $details['compile_error'];
        }
        if (isset($details['details'])) {
            if (
                !is_null($contest)
                && $contest->feedback === 'summary'
                && isset($details['details']['groups'])
            ) {
                $verdictIndexMap = array_flip(self::VERDICTS);
                foreach ($details['details']['groups'] as &$group) {
                    $worstVerdictIndex = 0;
                    foreach ($group['cases'] as $case) {
                        $worstVerdictIndex = max(
                            $worstVerdictIndex,
                            $verdictIndexMap[$case['verdict']]
                        );
                    }
                    $group['verdict'] = self::VERDICTS[$worstVerdictIndex];
                    unset($group['cases']);
                }
            }
            $response['details'] = $details['details'];
        }
        if (!OMEGAUP_LOCKDOWN && $response['admin']) {
            $gzippedLogs = self::getGraderResource($run, 'logs.txt.gz');
            if (is_string($gzippedLogs)) {
                $response['logs'] = strval(gzdecode($gzippedLogs));
            }

            $response['judged_by'] = strval($run->judged_by);
        }
        // Removing cases values until this PR be approved and merged to PR #3800
        $response['show_diff'] = 'examples';
        $response['cases'] = [];

        $cases = self::getProblemCasesMetadata(
            'cases',
            $problem->alias,
            $run->commit
        );
        /** @var int */
        $responseSize = array_reduce(
            $cases,
            /**
             * @param array{mode: int, type: string, id: string, size: int, path: string} $case
             */
            fn (int $sum, $case) => $sum + $case['size'],
            0
        );

        if (
            $problem->show_diff === \OmegaUp\ProblemParams::SHOW_DIFFS_NONE
            || $responseSize > 4096 // Forcing to hide diffs when inputs/outputs exceed 4kb
        ) {
            $response['show_diff'] = \OmegaUp\ProblemParams::SHOW_DIFFS_NONE;
            return $response;
        }
        if ($problem->show_diff === \OmegaUp\ProblemParams::SHOW_DIFFS_ALL) {
            $response['cases'] = self::getProblemCasesContents(
                'cases',
                $problem->alias,
                $run->commit
            );
            $response['show_diff'] = \OmegaUp\ProblemParams::SHOW_DIFFS_ALL;
            return $response;
        }
        $response['cases'] = self::getProblemCasesContents(
            'examples',
            $problem->alias,
            $run->commit
        );
        $response['show_diff'] = \OmegaUp\ProblemParams::SHOW_DIFFS_EXAMPLES;

        return $response;
    }

    /**
     * @return ProblemCasesContents
     */
    private static function getProblemCasesContents(
        string $directory,
        string $problemAlias,
        string $revision
    ): array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_CASES_CONTENTS,
            "{$problemAlias}-{$revision}-{$directory}",
            fn () => self::getProblemCasesContentsImpl(
                $directory,
                $problemAlias,
                $revision
            ),
            24 * 60 * 60 // expire in 1 day
        );
    }

    /**
     * @return ProblemCasesContents
     */
    private static function getProblemCasesContentsImpl(
        string $directory,
        string $problemAlias,
        string $revision
    ): array {
        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            $problemAlias,
            $revision
        );

        $existingCases = self::getProblemCasesMetadata(
            $directory,
            $problemAlias,
            $revision
        );
        $response = [];
        foreach ($existingCases as $file) {
            [$_, $filename] = explode("{$directory}/", $file['path']);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            \OmegaUp\Validators::validateInEnum(
                $extension,
                'extension',
                ['in', 'out']
            );
            $caseName = basename($filename, ".{$extension}");
            if (!isset($response[$caseName])) {
                $response[$caseName] = [
                    'in' => '',
                    'out' => '',
                ];
            }
            $caseContents = $problemArtifacts->get($file['path']);
            if ($extension === 'in') {
                $response[$caseName]['in'] = $caseContents;
            } else {
                $response[$caseName]['out'] = $caseContents;
            }
        }
        return $response;
    }

    /**
     * @return list<array{mode: int, type: string, id: string, size: int, path: string}>
     */
    public static function getProblemCasesMetadata(
        string $directory,
        string $problemAlias,
        string $revision
    ): array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::DATA_CASES_FILES,
            "{$problemAlias}-{$revision}-{$directory}",
            /** @return list<array{id: string, mode: int, path: string, size: int, type: string}> */
            function () use ($problemAlias, $revision, $directory) {
                $problemArtifacts = new \OmegaUp\ProblemArtifacts(
                    $problemAlias,
                    $revision
                );

                return $problemArtifacts->lsTreeRecursive($directory);
            },
            24 * 60 * 60 // expire in 1 day
        );
    }

    /**
     * Given the run alias, returns the source code and any compile errors if any
     * Used in the arena, any contestant can view its own codes and compile errors
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{compile_error?: string, details?: array{compile_meta?: array<string, RunMetadata>, contest_score: float, groups?: list<array{cases: list<CaseResult>, contest_score: float, group: string, max_score: float, score: float}>, judged_by: string, max_score?: float, memory?: float, score: float, time?: float, verdict: string, wall_time?: float}, source: string}
     *
     * @omegaup-request-param string $run_alias
     */
    public static function apiSource(\OmegaUp\Request $r): array {
        // Get the user who is calling this API
        $r->ensureIdentity();

        $runAlias = $r->ensureString(
            'run_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        [
            'run' => $run,
            'submission' => $submission,
        ] = self::validateDetailsRequest($runAlias);

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
            contest: null,
            showDetails: false
        );
    }

    private static function shouldShowRunDetails(
        int $identityId,
        \OmegaUp\DAO\VO\Problems $problem,
        ?\OmegaUp\DAO\VO\Contests $contest
    ): string {
        $isProblemSolved = \OmegaUp\DAO\Problems::isProblemSolved(
            $problem,
            $identityId
        );
        if (
            !is_null($contest) &&
            $contest->finish_time->time > \OmegaUp\Time::get()
        ) {
            return $contest->feedback;
        }
        if ($problem->show_diff !== 'none') {
            return 'detailed';
        }
        return $isProblemSolved ? 'detailed' : 'none';
    }

    /**
     * @return array{compile_error?: string, details?: array{compile_meta?: array<string, RunMetadata>, contest_score: float, groups?: list<array{cases: list<CaseResult>, contest_score: float, group: string, max_score: float, score: float}>, judged_by: string, max_score?: float, memory?: float, score: float, time?: float, verdict: string, wall_time?: float}, source: string}
     */
    private static function getOptionalRunDetails(
        \OmegaUp\DAO\VO\Submissions $submission,
        \OmegaUp\DAO\VO\Runs $run,
        ?\OmegaUp\DAO\VO\Contests $contest,
        bool $showDetails
    ): array {
        $response = [];

        /** @psalm-suppress TypeDoesNotContainType this can be defined to true sometimes. */
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
        /** @var array{compile_meta?: array<string, RunMetadata>, contest_score: float, groups?: list<array{cases: list<CaseResult>, contest_score: float, group: string, max_score: float, score: float}>, judged_by: string, max_score?: float, memory?: float, score: float, time?: float, verdict: string, wall_time?: float} */
        $details = json_decode($detailsJson, true);
        if (
            isset($details['compile_error']) &&
            is_string($details['compile_error'])
        ) {
            $response['compile_error'] = $details['compile_error'];
        }
        if (
            !is_null(
                $contest
            ) && $contest->score_mode == 'all_or_nothing' && $run->score < 1
        ) {
            $details['contest_score'] = 0.0;
            $details['score'] = 0.0;
        } else {
            $details['contest_score'] = floatval($details['contest_score']);
            $details['score'] = floatval($details['score']);
        }
        if (!OMEGAUP_LOCKDOWN && $showDetails) {
            $response['details'] = $details;
        }
        return $response;
    }

    /**
     * Given the run alias, returns a .zip file with all the .out files generated for a run.
     *
     * @omegaup-request-param string $run_alias
     * @omegaup-request-param bool $show_diff
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiDownload(\OmegaUp\Request $r): void {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        // Get the user who is calling this API
        $r->ensureIdentity();
        $showDiff = $r->ensureOptionalBool('show_diff') ?? false;

        $runAlias = $r->ensureString(
            'run_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        if (
            !self::downloadSubmission(
                $runAlias,
                $r->identity,
                passthru: true,
                skipAuthorization: $showDiff
            )
        ) {
            http_response_code(404);
        }

        // Since all the headers and response have been sent, make the API
        // caller to exit quietly.
        throw new \OmegaUp\Exceptions\ExitException();
    }

    /**
     * @return bool|null|string
     */
    public static function downloadSubmission(
        string $guid,
        \OmegaUp\DAO\VO\Identities $identity,
        bool $passthru,
        bool $skipAuthorization = false
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

        if (
            !$skipAuthorization &&
            !\OmegaUp\Authorization::isProblemAdmin($identity, $problem)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        if ($passthru) {
            $headers = [
                'Content-Type: application/zip',
                "Content-Disposition: attachment; filename={$submission->guid}.zip",
            ];
            return self::getGraderResourcePassthru($run, 'files.zip', $headers);
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
            missingOk: true
        );
        if (is_null($result)) {
            $result = self::downloadResourceFromS3(
                "{$run->run_id}/{$filename}",
                passthru: false
            );
        }
        return $result;
    }

    /**
     * @param list<string> $headers
     * @return bool|null|string
     */
    private static function getGraderResourcePassthru(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename,
        array $headers = []
    ) {
        $result = \OmegaUp\Grader::getInstance()->getGraderResourcePassthru(
            $run,
            $filename,
            missingOk: true,
            fileHeaders: $headers,
        );
        if (is_null($result)) {
            $result = self::downloadResourceFromS3(
                "{$run->run_id}/{$filename}",
                passthru: true,
                httpHeaders: $headers,
            );
        }
        return $result;
    }

    /**
     * Given the run resource path, fetches its contents from S3.
     *
     * @param  string       $resourcePath The run's resource path.
     * @param  bool         $passthru     Whether to output directly.
     * @param  list<string> $httpHeaders
     * @return ?string                    The contents of the resource (or an empty string) if successful. null otherwise.
     */
    private static function downloadResourceFromS3(
        string $resourcePath,
        bool $passthru,
        array $httpHeaders = []
    ): ?string {
        if (
            !defined('AWS_CLI_SECRET_ACCESS_KEY') ||
            !defined('AWS_CLI_ACCESS_KEY_ID') ||
            empty(AWS_CLI_SECRET_ACCESS_KEY) ||
            empty(AWS_CLI_ACCESS_KEY_ID) ||
            !is_string(AWS_CLI_SECRET_ACCESS_KEY) ||
            !is_string(AWS_CLI_ACCESS_KEY_ID)
        ) {
            return null;
        }

        if (strpos($resourcePath, '/') !== 0) {
            $resourcePath = "/{$resourcePath}";
        }
        $accessKeyId = AWS_CLI_ACCESS_KEY_ID;
        $secretAccessKey = AWS_CLI_SECRET_ACCESS_KEY;
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
                fn (string $key) => strtolower($key),
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
                        fn (string $key) => (
                            strtolower($key) . ":{$headers[$key]}\n"
                        ),
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
                    fn (string $key) => "{$key}: {$headers[$key]}",
                    array_keys($headers)
                ),
                CURLOPT_RETURNTRANSFER => intval(!$passthru),
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_TIMEOUT => 10,
            ]
        );

        $output = curl_exec($curl);
        if ($passthru) {
            foreach ($httpHeaders as $header) {
                header($header);
            }
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
            function () {
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
            24 * 60 * 60 // expire in 1 day
        );
    }

    /**
     * Validator for List API
     *
     * @return array{problem: null|\OmegaUp\DAO\VO\Problems, identity: null|\OmegaUp\DAO\VO\Identities}
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param string $username
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
        $problemAlias = $r->ensureOptionalString(
            'problem_alias',
            required: false,
            validator: fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        if (!is_null($problemAlias)) {
            $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
            if (is_null($problem)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }
        }

        // Get user if we have something in username
        $username = $r->ensureOptionalString(
            'username',
            required: false,
            validator: fn (string $username) => \OmegaUp\Validators::normalUsername(
                $username
            )
        );
        /** @var null|\OmegaUp\DAO\VO\Identities */
        $identity = null;
        if (!is_null($username)) {
            try {
                $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
                    $username
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
     * @return array{runs: list<Run>, totalRuns: int}
     *
     * @omegaup-request-param 'c11-clang'|'c11-gcc'|'cat'|'cpp11-clang'|'cpp11-gcc'|'cpp17-clang'|'cpp17-gcc'|'cpp20-clang'|'cpp20-gcc'|'cs'|'go'|'hs'|'java'|'js'|'kj'|'kp'|'kt'|'lua'|'pas'|'py2'|'py3'|'rb'|'rs'|null $language
     * @omegaup-request-param int $offset
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param int $rowcount
     * @omegaup-request-param 'compiling'|'new'|'ready'|'running'|'waiting'|null $status
     * @omegaup-request-param string $username
     * @omegaup-request-param 'AC'|'CE'|'JE'|'MLE'|'NO-AC'|'OLE'|'PA'|'RFE'|'RTE'|'TLE'|'VE'|'WA'|null $verdict
     */
    public static function apiList(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        [
            'problem' => $problem,
            'identity' => $identity,
        ] = self::validateList($r);

        $languagesKeys = array_keys(self::SUPPORTED_LANGUAGES());
        [
            'runs' => $runs,
            'totalRuns' => $totalRuns,
        ] = \OmegaUp\DAO\Runs::getAllRuns(
            null,
            $r->ensureOptionalEnum('status', self::STATUS),
            $r->ensureOptionalEnum('verdict', self::VERDICTS),
            !is_null($problem) ? $problem->problem_id : null,
            $r->ensureOptionalEnum('language', $languagesKeys),
            !is_null($identity) ? $identity->identity_id : null,
            max($r->ensureOptionalInt('offset') ?? 0, 0),
            $r->ensureOptionalInt('rowcount') ?? 100
        );

        $result = [];
        foreach ($runs as $run) {
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
            'totalRuns' => $totalRuns,
        ];
    }
}
