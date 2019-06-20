<?php

require_once 'libs/FileHandler.php';

/**
 * RunController
 *
 * @author joemmanuel
 */
class RunController extends Controller {
    public static $kSupportedLanguages = [
        'kp' => 'Karel (Pascal)',
        'kj' => 'Karel (Java)',
        'c' => 'C',
        'cpp' => 'C++',
        'cpp11' => 'C++ 11',
        'java' => 'Java',
        'py' => 'Python',
        'rb' => 'Ruby',
        'pl' => 'Perl',
        'cs' => 'C#',
        'pas' => 'Pascal',
        'cat' => 'Output Only',
        'hs' => 'Haskell',
        'lua' => 'Lua',
    ];
    public static $defaultSubmissionGap = 60; /*seconds*/
    private static $practice = false;

    /**
     *
     * Validates Create Run request
     *
     * @param Request $r
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     * @throws NotAllowedToSubmitException
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    private static function validateCreateRequest(Request $r) {
        // https://github.com/omegaup/omegaup/issues/739
        if ($r->identity->username == 'omi') {
            throw new ForbiddenAccessException();
        }

        $allowedLanguages = array_keys(RunController::$kSupportedLanguages);
        try {
            Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');

            // Check that problem exists
            $r['problem'] = ProblemsDAO::getByAlias($r['problem_alias']);

            if ($r['problem']->deprecated) {
                throw new PreconditionFailedException('problemDeprecated');
            }
            // check that problem is not publicly or privately banned.
            if ($r['problem']->visibility == ProblemController::VISIBILITY_PUBLIC_BANNED || $r['problem']->visibility == ProblemController::VISIBILITY_PRIVATE_BANNED) {
                throw new NotFoundException('problemNotfound');
            }

            $allowedLanguages = array_intersect(
                $allowedLanguages,
                explode(',', $r['problem']->languages)
            );
            Validators::validateInEnum(
                $r['language'],
                'language',
                $allowedLanguages
            );
            Validators::validateStringNonEmpty($r['source'], 'source');

            // Can't set both problemset_id and contest_alias at the same time.
            if (!empty($r['problemset_id']) && !empty($r['contest_alias'])) {
                throw new InvalidParameterException(
                    'incompatibleArgs',
                    'problemset_id and contest_alias'
                );
            }

            $problemset_id = null;
            if (!empty($r['problemset_id'])) {
                // Got a problemset id directly.
                $problemset_id = intval($r['problemset_id']);
                $r['container'] = ProblemsetsDAO::getProblemsetContainer($problemset_id);
            } elseif (!empty($r['contest_alias'])) {
                // Got a contest alias, need to fetch the problemset id.
                // Validate contest
                Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');
                $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);

                if ($r['contest'] == null) {
                    throw new InvalidParameterException('parameterNotFound', 'contest_alias');
                }

                $problemset_id = $r['contest']->problemset_id;
                $r['container'] = $r['contest'];

                // Update list of valid languages.
                if ($r['contest']->languages !== null) {
                    $allowedLanguages = array_intersect(
                        $allowedLanguages,
                        explode(',', $r['contest']->languages)
                    );
                }
            } else {
                // Check for practice or public problem, there is no contest info
                // in this scenario.
                if (ProblemsDAO::isVisible($r['problem']) ||
                      Authorization::isProblemAdmin($r->identity->identity_id, $r['problem']) ||
                      Time::get() > ProblemsDAO::getPracticeDeadline($r['problem']->problem_id)) {
                    if (!RunsDAO::isRunInsideSubmissionGap(
                        null,
                        null,
                        (int)$r['problem']->problem_id,
                        (int)$r->identity->identity_id
                    )
                            && !Authorization::isSystemAdmin($r->identity->identity_id)) {
                            throw new NotAllowedToSubmitException('runWaitGap');
                    }

                    self::$practice = true;
                    return;
                } else {
                    throw new NotAllowedToSubmitException('problemIsNotPublic');
                }
            }

            $r['problemset'] = ProblemsetsDAO::getByPK($problemset_id);
            if ($r['problemset'] == null) {
                throw new InvalidParameterException('parameterNotFound', 'problemset_id');
            }

            // Validate the language.
            if ($r['problemset']->languages !== null) {
                $allowedLanguages = array_intersect(
                    $allowedLanguages,
                    explode(',', $r['problemset']->languages)
                );
            }
            Validators::validateInEnum(
                $r['language'],
                'language',
                $allowedLanguages
            );

            // Validate that the combination problemset_id problem_id is valid
            if (!ProblemsetProblemsDAO::getByPK(
                $problemset_id,
                $r['problem']->problem_id
            )) {
                throw new InvalidParameterException('parameterNotFound', 'problem_alias');
            }

            // No one should submit after the deadline. Not even admins.
            if (ProblemsetsDAO::isLateSubmission($r['container'])) {
                throw new NotAllowedToSubmitException('runNotInsideContest');
            }

            // Contest admins can skip following checks
            if (!Authorization::isAdmin($r->identity->identity_id, $r['problemset'])) {
                // Before submit something, user had to open the problem/problemset.
                if (!ProblemsetIdentitiesDAO::getByPK($r->identity->identity_id, $problemset_id) &&
                    !Authorization::canSubmitToProblemset(
                        $r->identity->identity_id,
                        $r['problemset']
                    )
                ) {
                    throw new NotAllowedToSubmitException('runNotEvenOpened');
                }

                // Validate that the run is timely inside contest
                if (!ProblemsetsDAO::insideSubmissionWindow($r['container'], $r->identity->identity_id)) {
                    throw new NotAllowedToSubmitException('runNotInsideContest');
                }

                // Validate if the user is allowed to submit given the submissions_gap
                if (!RunsDAO::IsRunInsideSubmissionGap(
                    (int)$problemset_id,
                    $r['contest'],
                    (int)$r['problem']->problem_id,
                    (int)$r->identity->identity_id
                )) {
                    throw new NotAllowedToSubmitException('runWaitGap');
                }
            }
        } catch (ApiException $apiException) {
            // Propagate ApiException
            throw $apiException;
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * Create a new run
     *
     * @param Request $r
     * @return array
     * @throws Exception
     * @throws InvalidDatabaseOperationException
     * @throws InvalidFilesystemOperationException
     */
    public static function apiCreate(Request $r) {
        self::$practice = false;

        // Authenticate user
        self::authenticateRequest($r);

        // Validate request
        self::validateCreateRequest($r);

        self::$log->info('New run being submitted!!');
        $response = [];

        if (self::$practice) {
            if (OMEGAUP_LOCKDOWN) {
                throw new ForbiddenAccessException('lockdown');
            }
            $submit_delay = 0;
            $problemset_id = null;
            $type = 'normal';
        } else {
            //check the kind of penalty_type for this contest
            $start = null;
            $problemset_id = (int)$r['problemset']->problemset_id;
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
                        $opened = ProblemsetProblemOpenedDAO::getByPK(
                            $problemset_id,
                            $r['problem']->problem_id,
                            $r->identity->identity_id
                        );

                        if (is_null($opened)) {
                            //holy moly, he is submitting a run
                            //and he hasnt even opened the problem
                            //what should be done here?
                            throw new NotAllowedToSubmitException('runEvenOpened');
                        }

                        $start = $opened->open_time;
                        break;

                    case 'none':
                    case 'runtime':
                        //we dont care
                        $start = null;
                        break;

                    default:
                        self::$log->error('penalty_type for this contests is not a valid option, asuming `none`.');
                        $start = null;
                }
            }

            if (!is_null($start)) {
                //ok, what time is it now?
                $c_time = Time::get();
                $start = strtotime($start);

                //asuming submit_delay is in minutes
                $submit_delay = (int) (( $c_time - $start ) / 60);
            } else {
                $submit_delay = 0;
            }

            // If user is admin and is in virtual contest, then admin will be treated as contestant

            $type = (Authorization::isAdmin($r->identity->identity_id, $r['problemset']) &&
                !is_null($r['contest']) &&
                !ContestsDAO::isVirtual($r['contest'])) ? 'test' : 'normal';
        }

        // Populate new run+submission object
        $submission = new Submissions([
            'identity_id' => $r->identity->identity_id,
            'problem_id' => $r['problem']->problem_id,
            'problemset_id' => $problemset_id,
            'guid' => md5(uniqid(rand(), true)),
            'language' => $r['language'],
            'penalty' => $submit_delay,
            'time' => gmdate('Y-m-d H:i:s', Time::get()),
            'submit_delay' => $submit_delay, /* based on penalty_type */
            'type' => $type
        ]);
        $run = new Runs([
            'version' => $r['problem']->current_version,
            'status' => 'new',
            'runtime' => 0,
            'penalty' => $submit_delay,
            'memory' => 0,
            'score' => 0,
            'contest_score' => $problemset_id != null ? 0 : null,
            'verdict' => 'JE',
            'type' => $type
        ]);

        try {
            // Push run into DB
            SubmissionsDAO::create($submission);
            $run->submission_id = $submission->submission_id;
            RunsDAO::create($run);
            $submission->current_run_id = $run->run_id;
            SubmissionsDAO::update($submission);

            // Call Grader
            try {
                Grader::getInstance()->grade($run, trim($r['source']));
            } catch (Exception $e) {
                // Welp, it failed. We cannot make this a real transaction
                // because the Run row would not be visible from the Grader
                // process, so we attempt to roll it back by hand.
                // We need to unlink the current run and submission prior to
                // deleting the rows. Otherwise we would have a foreign key
                // violation.
                $submission->current_run_id = null;
                SubmissionsDAO::update($submission);
                RunsDAO::delete($run);
                SubmissionsDAO::delete($submission);
                self::$log->error("Call to Grader::grade() failed: $e");
                throw $e;
            }

            SubmissionLogDAO::create(new SubmissionLog([
                'user_id' => $r->user->user_id,
                'identity_id' => $r->identity->identity_id,
                'submission_id' => $submission->submission_id,
                'problemset_id' => $submission->problemset_id,
                'ip' => ip2long($_SERVER['REMOTE_ADDR'])
            ]));

            $r['problem']->submissions++;
            ProblemsDAO::update($r['problem']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (self::$practice) {
            $response['submission_deadline'] = 0;
        } else {
            // Add remaining time to the response
            try {
                $contest_user = ProblemsetIdentitiesDAO::getByPK($r->identity->identity_id, $problemset_id);

                if (isset($r['container']->finish_time)) {
                    $response['submission_deadline'] = strtotime($r['container']->finish_time);
                    if (isset($r['container']->window_length)) {
                        $response['submission_deadline'] = min(
                            strtotime($r['container']->finish_time),
                            strtotime($contest_user->access_time) + $r['container']->window_length * 60
                        );
                    }
                } elseif (isset($r['container']->window_length)) {
                    $response['submission_deadline'] = strtotime($contest_user->access_time) + $r['container']->window_length * 60;
                }
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }
        }

        // Happy ending
        $response['nextSubmissionTimestamp'] = RunsDAO::nextSubmissionTimestamp(
            isset($r['contest']) ? $r['contest'] : null
        );
        $response['guid'] = $submission->guid;
        $response['status'] = 'ok';

        // Expire rank cache
        UserController::deleteProblemsSolvedRankCacheList();

        return $response;
    }

    /**
     * Validate request of details
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     * @throws ForbiddenAccessException
     */
    private static function validateDetailsRequest(Request $r) {
        Validators::validateStringNonEmpty($r['run_alias'], 'run_alias');

        // If user is not judge, must be the run's owner.
        try {
            $r['submission'] = SubmissionsDAO::getByGuid($r['run_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($r['submission'])) {
            throw new NotFoundException('runNotFound');
        }

        try {
            $r['run'] = RunsDAO::getByPK($r['submission']->current_run_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($r['run'])) {
            throw new NotFoundException('runNotFound');
        }
    }

    /**
     * Get basic details of a run
     *
     * @param Request $r
     * @return array
     * @throws InvalidFilesystemOperationException
     */
    public static function apiStatus(Request $r) {
        // Get the user who is calling this API
        self::authenticateRequest($r);

        self::validateDetailsRequest($r);

        if (!(Authorization::canViewSubmission($r->identity->identity_id, $r['submission']))) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        // Fill response
        $filtered = (
            $r['submission']->asFilteredArray([
                'guid', 'language', 'time', 'submit_delay',
            ]) +
            $r['run']->asFilteredArray([
                'status', 'verdict', 'runtime', 'penalty', 'memory', 'score', 'contest_score',
            ])
        );
        $filtered['time'] = strtotime($filtered['time']);
        $filtered['score'] = round((float) $filtered['score'], 4);
        $filtered['runtime'] = (int)$filtered['runtime'];
        $filtered['penalty'] = (int)$filtered['penalty'];
        $filtered['memory'] = (int)$filtered['memory'];
        $filtered['submit_delay'] = (int)$filtered['submit_delay'];
        if ($filtered['contest_score'] != null) {
            $filtered['contest_score'] = round((float) $filtered['contest_score'], 2);
        }
        if ($r['submission']->identity_id == $r->identity->identity_id) {
            $filtered['username'] = $r->identity->username;
        }
        return $filtered;
    }

    /**
     * Re-sends a problem to Grader.
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     */
    public static function apiRejudge(Request $r) {
        self::$practice = false;

        // Get the user who is calling this API
        self::authenticateRequest($r);

        self::validateDetailsRequest($r);

        if (!(Authorization::canEditSubmission($r->identity->identity_id, $r['submission']))) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        self::$log->info('Run being rejudged!!');

        // Reset fields.
        $r['run']->status = 'new';
        RunsDAO::save($r['run']);

        try {
            Grader::getInstance()->rejudge([$r['run']], $r['debug'] || false);
        } catch (Exception $e) {
            self::$log->error("Call to Grader::rejudge() failed: {$e}");
        }

        $response = [];
        $response['status'] = 'ok';

        self::invalidateCacheOnRejudge($r['run']);

        // Expire ranks
        UserController::deleteProblemsSolvedRankCacheList();

        return $response;
    }

    /**
     * Disqualify a submission
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     */
    public static function apiDisqualify(Request $r) {
        // Get the user who is calling this API
        self::authenticateRequest($r);

        self::validateDetailsRequest($r);

        if (!Authorization::canEditSubmission($r->identity->identity_id, $r['submission'])) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        SubmissionsDAO::disqualify($r['submission']->guid);

        // Expire ranks
        UserController::deleteProblemsSolvedRankCacheList();
        return [
            'status' => 'ok'
        ];
    }

    /**
     * Invalidates relevant caches on run rejudge
     *
     * @param Runs $run
     */
    public static function invalidateCacheOnRejudge(Runs $run) : void {
        try {
            // Expire details of the run
            Cache::deleteFromCache(Cache::RUN_ADMIN_DETAILS, $run->run_id);

            $submission = SubmissionsDAO::getByPK($run->submission_id);
            if (is_null($submission)) {
                return;
            }

            // Now we need to invalidate problem stats
            $problem = ProblemsDAO::getByPK($submission->problem_id);

            if (!is_null($problem)) {
                // Invalidar cache stats
                Cache::deleteFromCache(Cache::PROBLEM_STATS, $problem->alias);
            }
        } catch (Exception $e) {
            // We did our best effort to invalidate the cache...
            self::$log->warn('Failed to invalidate cache on Rejudge, skipping: ');
            self::$log->warn($e);
        }
    }

    /**
     * Gets the details of a run. Includes admin details if admin.
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     */
    public static function apiDetails(Request $r) {
        // Get the user who is calling this API
        self::authenticateRequest($r);

        self::validateDetailsRequest($r);

        try {
            $r['problem'] = ProblemsDAO::getByPK($r['submission']->problem_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['problem'])) {
            throw new NotFoundException('problemNotFound');
        }

        if (!(Authorization::canViewSubmission($r->identity->identity_id, $r['submission']))) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        // Get the source
        $response = [
            'status' => 'ok',
            'admin' => Authorization::isProblemAdmin($r->identity->identity_id, $r['problem']),
            'guid' => $r['submission']->guid,
            'language' => $r['submission']->language,
        ];
        $showDetails = $response['admin'] ||
            ProblemsDAO::isProblemSolved($r['problem'], (int)$r->identity->identity_id);

        // Get the details, compile error, logs, etc.
        RunController::populateRunDetails($r['submission'], $r['run'], $showDetails, $response);
        if (!OMEGAUP_LOCKDOWN && $response['admin']) {
            $gzippedLogs = self::getGraderResource($r['run'], 'logs.txt.gz');
            if (is_string($gzippedLogs)) {
                $response['logs'] = gzdecode($gzippedLogs);
            }

            $response['judged_by'] = $r['run']->judged_by;
        }

        return $response;
    }

    /**
     * Given the run alias, returns the source code and any compile errors if any
     * Used in the arena, any contestant can view its own codes and compile errors
     *
     * @param Request $r
     * @throws ForbiddenAccessException
     */
    public static function apiSource(Request $r) {
        // Get the user who is calling this API
        self::authenticateRequest($r);

        self::validateDetailsRequest($r);

        if (!(Authorization::canViewSubmission($r->identity->identity_id, $r['submission']))) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        $response = [
            'status' => 'ok',
        ];
        RunController::populateRunDetails($r['submission'], $r['run'], false, $response);
        return $response;
    }

    private static function populateRunDetails(
        Submissions $submission,
        Runs $run,
        bool $showDetails,
        &$response
    ) {
        if (OMEGAUP_LOCKDOWN) {
            $response['source'] = 'lockdownDetailsDisabled';
        } else {
            $response['source'] = SubmissionController::getSource($submission->guid);
        }
        if (!$showDetails && $run->verdict != 'CE') {
            return;
        }
        $detailsJson = self::getGraderResource($run, 'details.json');
        if (!is_string($detailsJson)) {
            return;
        }
        $details = json_decode($detailsJson, true);
        if (isset($details['compile_error'])) {
            $response['compile_error'] = $details['compile_error'];
        }
        if (!OMEGAUP_LOCKDOWN && $showDetails) {
            $response['details'] = $details;
        }
    }

    /**
     * Given the run alias, returns a .zip file with all the .out files generated for a run.
     *
     * @param Request $r
     * @throws ForbiddenAccessException
     */
    public static function apiDownload(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }
        // Get the user who is calling this API
        self::authenticateRequest($r);

        Validators::validateStringNonEmpty($r['run_alias'], 'run_alias');
        if (!RunController::downloadSubmission($r['run_alias'], $r->identity->identity_id, /*passthru=*/true)) {
            http_response_code(404);
        }
        exit;
    }

    public static function downloadSubmission(string $guid, int $identityId, bool $passthru) {
        try {
            $submission = SubmissionsDAO::getByGuid($guid);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($submission)) {
            throw new NotFoundException('runNotFound');
        }

        try {
            $run = RunsDAO::getByPK($submission->current_run_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($run)) {
            throw new NotFoundException('runNotFound');
        }

        try {
            $problem = ProblemsDAO::getByPK($submission->problem_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
        }

        if (!(Authorization::isProblemAdmin($identityId, $problem))) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        if ($passthru) {
            header('Content-Type: application/zip');
            header("Content-Disposition: attachment; filename={$submission->guid}.zip");
        }
        return self::getGraderResource($run, 'files.zip', $passthru);
    }

    private static function getGraderResource(
        Runs $run,
        string $filename,
        bool $passthru = false
    ) {
        $result = Grader::getInstance()->getGraderResource($run, $filename, $passthru, /*missingOk=*/true);
        if (is_null($result)) {
            $result = self::downloadResourceFromS3("{$run->run_id}/{$filename}", $passthru);
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
    private static function downloadResourceFromS3(string $resourcePath, bool $passthru) : ?string {
        if (is_null(AWS_CLI_SECRET_ACCESS_KEY)) {
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
            self::$log->error("Getting {$resourcePath} failed: {$errors['type']} {$errors['message']}");
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
     * @param Request $r
     * @return type
     * @throws InvalidDatabaseOperationException
     */
    public static function apiCounts(Request $r) {
        $totals = [];

        Cache::getFromCacheOrSet(Cache::RUN_COUNTS, '', $r, function (Request $r) {
            $totals = [];
            $totals['total'] = [];
            $totals['ac'] = [];
            try {
                $runCounts = RunCountsDAO::getAll(1, 90, 'date', 'DESC');

                foreach ($runCounts as $runCount) {
                    $totals['total'][$runCount->date] = $runCount->total;
                    $totals['ac'][$runCount->date] = $runCount->ac_count;
                }
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            return $totals;
        }, $totals, 24*60*60 /*expire in 1 day*/);

        return $totals;
    }

    /**
     * Validator for List API
     *
     * @param Request $r
     * @throws ForbiddenAccessException
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    private static function validateList(Request $r) {
        // Defaults for offset and rowcount
        if (!isset($r['offset'])) {
            $r['offset'] = 0;
        }
        if (!isset($r['rowcount'])) {
            $r['rowcount'] = 100;
        }

        if (!Authorization::isSystemAdmin($r->identity->identity_id)) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);
        Validators::validateInEnum($r['status'], 'status', ['new', 'waiting', 'compiling', 'running', 'ready'], false);
        Validators::validateInEnum($r['verdict'], 'verdict', ['AC', 'PA', 'WA', 'TLE', 'MLE', 'OLE', 'RTE', 'RFE', 'CE', 'JE', 'NO-AC'], false);

        // Check filter by problem, is optional
        if (!is_null($r['problem_alias'])) {
            Validators::validateStringNonEmpty($r['problem_alias'], 'problem');

            try {
                $r['problem'] = ProblemsDAO::getByAlias($r['problem_alias']);
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            if (is_null($r['problem'])) {
                throw new NotFoundException('problemNotFound');
            }
        }

        Validators::validateInEnum(
            $r['language'],
            'language',
            array_keys(RunController::$kSupportedLanguages),
            false
        );

        // Get user if we have something in username
        if (!is_null($r['username'])) {
            try {
                $r['identity'] = IdentityController::resolveIdentity($r['username']);
            } catch (NotFoundException $e) {
                // If not found, simply ignore it
                $r['username'] = null;
                $r['identity'] = null;
            }
        }
    }

    /**
     * Gets a list of latest runs overall
     *
     * @param Request $r
     * @return string
     * @throws InvalidDatabaseOperationException
     */
    public static function apiList(Request $r) {
        // Authenticate request
        self::authenticateRequest($r);
        self::validateList($r);

        try {
            $runs = RunsDAO::getAllRuns(
                null,
                $r['status'],
                $r['verdict'],
                !is_null($r['problem']) ? $r['problem']->problem_id : null,
                $r['language'],
                !is_null($r['identity']) ? $r['identity']->identity_id : null,
                $r['offset'],
                $r['rowcount']
            );
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        $result = [];

        foreach ($runs as $run) {
            $run['time'] = (int)$run['time'];
            $run['score'] = round((float)$run['score'], 4);
            if ($run['contest_score'] != null) {
                $run['contest_score'] = round((float)$run['contest_score'], 2);
            }
            array_push($result, $run);
        }

        $response = [];
        $response['runs'] = $result;
        $response['status'] = 'ok';

        return $response;
    }
}
