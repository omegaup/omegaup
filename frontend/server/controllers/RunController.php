<?php

/**
 * RunController
 *
 * @author joemmanuel
 */
class RunController extends Controller {
    public static $defaultSubmissionGap = 60; /*seconds*/
    public static $grader = null;
    private static $practice = false;

    public static function getGradePath($run) {
        return GRADE_PATH . '/' .
            substr($run->guid, 0, 2) . '/' .
            substr($run->guid, 2);
    }

    /**
     * Gets the path of the file that contains the submission.
     */
    public static function getSubmissionPath($run) {
        return RUNS_PATH .
            DIRECTORY_SEPARATOR . substr($run->guid, 0, 2) .
            DIRECTORY_SEPARATOR . substr($run->guid, 2);
    }

    /**
     * Creates an instance of Grader if not already created
     */
    private static function initializeGrader() {
        if (is_null(self::$grader)) {
            // Create new grader
            self::$grader = new Grader();
        }

        // Set practice mode OFF by default
        self::$practice = false;
    }

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
        if ($r['current_user']->username == 'omi') {
            throw new ForbiddenAccessException();
        }

        $allowedLanguages = ['kp', 'kj', 'c', 'cpp', 'cpp11', 'java', 'py', 'rb', 'pl', 'cs', 'pas', 'cat', 'hs'];
        try {
            Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

            // Check that problem exists
            $r['problem'] = ProblemsDAO::getByAlias($r['problem_alias']);

            if ($r['problem']->deprecated) {
                throw new PreconditionFailedException('problemDeprecated');
            }

            $allowedLanguages = array_intersect(
                $allowedLanguages,
                explode(',', $r['problem']->languages)
            );
            Validators::isInEnum(
                $r['language'],
                'language',
                $allowedLanguages
            );
            Validators::isStringNonEmpty($r['source'], 'source');

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
                Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');
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
                if ($r['problem']->public == true ||
                      Authorization::isProblemAdmin($r['current_user_id'], $r['problem']) ||
                      time() > ProblemsDAO::getPracticeDeadline($r['problem']->problem_id)) {
                    if (!RunsDAO::IsRunInsideSubmissionGap(
                        null,
                        null,
                        $r['problem']->problem_id,
                        $r['current_user_id']
                    )
                            && !Authorization::isSystemAdmin($r['current_user_id'])) {
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
            Validators::isInEnum(
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

            // Contest admins can skip following checks
            if (!Authorization::isAdmin($r['current_user_id'], $r['problemset'])) {
                // Before submit something, user had to open the problem/problemset.
                if (!ProblemsetUsersDAO::getByPK($r['current_user_id'], $problemset_id)) {
                    throw new NotAllowedToSubmitException('runNotEvenOpened');
                }

                // Validate that the run is timely inside contest
                if (!ProblemsetsDAO::insideSubmissionWindow($r['container'], $r['current_user_id'])) {
                    throw new NotAllowedToSubmitException('runNotInsideContest');
                }

                // Validate if the user is allowed to submit given the submissions_gap
                if (!RunsDAO::IsRunInsideSubmissionGap(
                    $problemset_id,
                    isset($r['contest']) ? $r['contest'] : null,
                    $r['problem']->problem_id,
                    $r['current_user_id']
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
        // Init
        self::initializeGrader();

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
            $test = 0;
        } else {
            //check the kind of penalty_type for this contest
            $start = null;
            $problemset_id = $r['problemset']->problemset_id;
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
                            $r['current_user_id']
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
                $c_time = time();
                $start = strtotime($start);

                //asuming submit_delay is in minutes
                $submit_delay = (int) (( $c_time - $start ) / 60);
            } else {
                $submit_delay = 0;
            }

            $test = Authorization::isAdmin($r['current_user_id'], $r['problemset']) ? 1 : 0;
        }

        // Populate new run object
        $run = new Runs([
                    'user_id' => $r['current_user_id'],
                    'problem_id' => $r['problem']->problem_id,
                    'problemset_id' => $problemset_id,
                    'language' => $r['language'],
                    'source' => $r['source'],
                    'status' => 'new',
                    'runtime' => 0,
                    'penalty' => $submit_delay,
                    'memory' => 0,
                    'score' => 0,
                    'contest_score' => $problemset_id != null ? 0 : null,
                    'submit_delay' => $submit_delay, /* based on penalty_type */
                    'guid' => md5(uniqid(rand(), true)),
                    'verdict' => 'JE',
                    'test' => $test
                ]);

        try {
            // Push run into DB
            RunsDAO::save($run);

            SubmissionLogDAO::save(new SubmissionLog([
                'user_id' => $run->user_id,
                'run_id' => $run->run_id,
                'problemset_id' => $run->problemset_id,
                'ip' => ip2long($_SERVER['REMOTE_ADDR'])
            ]));

            $r['problem']->submissions++;
            ProblemsDAO::save($r['problem']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        try {
            // Create file for the run
            $filepath = RunController::getSubmissionPath($run);
            FileHandler::CreateFile($filepath, trim($r['source']));
        } catch (Exception $e) {
            throw new InvalidFilesystemOperationException($e);
        }

        // Call Grader
        try {
            self::$grader->Grade([$run->guid], false, false);
        } catch (Exception $e) {
            self::$log->error('Call to Grader::grade() failed:');
            self::$log->error($e);
        }

        if (self::$practice) {
            $response['submission_deadline'] = 0;
        } else {
            // Add remaining time to the response
            try {
                $contest_user = ProblemsetUsersDAO::getByPK($r['current_user_id'], $problemset_id);

                $response['submission_deadline'] = strtotime($r['container']->finish_time);
                if (isset($r['container']->window_length)) {
                    $response['submission_deadline'] = min(
                        $response['submission_deadline'],
                        strtotime($contest_user->access_time) + $r['container']->window_length * 60
                    );
                }
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }
        }

        // Happy ending
        $response['guid'] = $run->guid;
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
        Validators::isStringNonEmpty($r['run_alias'], 'run_alias');

        try {
            // If user is not judge, must be the run's owner.
            $r['run'] = RunsDAO::getByAlias($r['run_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['run'])) {
            throw new NotFoundException('runNotFound');
        }
    }

    /**
     * Validate request of admin details
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     * @throws ForbiddenAccessException
     */
    private static function validateAdminDetailsRequest(Request $r) {
        Validators::isStringNonEmpty($r['run_alias'], 'run_alias');

        try {
            $r['run'] = RunsDAO::getByAlias($r['run_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['run'])) {
            throw new NotFoundException('runNotFound');
        }

        try {
            $r['problem'] = ProblemsDAO::getByPK($r['run']->problem_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['problem'])) {
            throw new NotFoundException('problemNotFound');
        }

        if (!(Authorization::isProblemAdmin($r['current_user_id'], $r['problem']))) {
            throw new ForbiddenAccessException('userNotAllowed');
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

        if (!(Authorization::canViewRun($r['current_user_id'], $r['run']))) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        // Fill response
        $relevant_columns = ['guid', 'language', 'status', 'verdict',
            'runtime', 'penalty', 'memory', 'score', 'contest_score', 'time',
            'submit_delay'];
        $filtered = $r['run']->asFilteredArray($relevant_columns);
        $filtered['time'] = strtotime($filtered['time']);
        $filtered['score'] = round((float) $filtered['score'], 4);
        $filtered['runtime'] = (int)$filtered['runtime'];
        $filtered['penalty'] = (int)$filtered['penalty'];
        $filtered['memory'] = (int)$filtered['memory'];
        $filtered['submit_delay'] = (int)$filtered['submit_delay'];
        if ($filtered['contest_score'] != null) {
            $filtered['contest_score'] = round((float) $filtered['contest_score'], 2);
        }
        if ($r['run']->user_id == $r['current_user_id']) {
            $filtered['username'] = $r['current_user']->username;
        }

        $response = $filtered;

        return $response;
    }

    /**
     * Re-sends a problem to Grader.
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     */
    public static function apiRejudge(Request $r) {
        // Init
        self::initializeGrader();

        // Get the user who is calling this API
        self::authenticateRequest($r);

        self::validateDetailsRequest($r);

        if (!(Authorization::canEditRun($r['current_user_id'], $r['run']))) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        self::$log->info('Run being rejudged!!');

        // Try to delete existing directory, if exists.
        try {
            $grade_dir = RunController::getGradePath($r['run']);
            FileHandler::DeleteDirRecursive($grade_dir);
        } catch (Exception $e) {
            // Soft error :P
            self::$log->warn($e);
        }

        try {
            self::$grader->Grade([$r['run']->guid], true, $r['debug'] || false);
        } catch (Exception $e) {
            self::$log->error('Call to Grader::grade() failed:');
            self::$log->error($e);
        }

        $response = [];
        $response['status'] = 'ok';

        self::invalidateCacheOnRejudge($r['run']);

        // Expire ranks
        UserController::deleteProblemsSolvedRankCacheList();

        return $response;
    }

    /**
     * Invalidates relevant caches on run rejudge
     *
     * @param RunsDAO $run
     */
    public static function invalidateCacheOnRejudge(Runs $run) {
        try {
            // Expire details of the run
            Cache::deleteFromCache(Cache::RUN_ADMIN_DETAILS, $run->run_id);

            $contest = ContestsDAO::getByPK($run->contest_id);

            // Now we need to invalidate problem stats
            $problem = ProblemsDAO::getByPK($run->problem_id);

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
            $r['problem'] = ProblemsDAO::getByPK($r['run']->problem_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['problem'])) {
            throw new NotFoundException('problemNotFound');
        }

        if (!(Authorization::canViewRun($r['current_user_id'], $r['run']))) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        $response = [];

        if (OMEGAUP_LOCKDOWN) {
            $response['source'] = 'lockdownDetailsDisabled';
            $response['status'] = 'ok';
            return $response;
        }

        // Get the source
        $response['source'] = file_get_contents(RunController::getSubmissionPath($r['run']));
        $response['admin'] = Authorization::isProblemAdmin($r['current_user_id'], $r['problem']);

        // Get the error
        $grade_dir = RunController::getGradePath($r['run']);
        $details = null;
        if (($response['admin'] || $r['run']->verdict == 'CE') &&
            file_exists("$grade_dir/details.json")) {
            $details = json_decode(file_get_contents("$grade_dir/details.json"), true);
        }
        if (!is_null($details) && isset($details['compile_error'])) {
            $response['compile_error'] = $details['compile_error'];
        } elseif (file_exists("$grade_dir/compile_error.log")) {
            $response['compile_error'] = file_get_contents("$grade_dir/compile_error.log");
        }

        if ($response['admin']) {
            if (!is_null($details)) {
                if (count(array_filter(array_keys($details), 'is_string')) > 0) {
                    $response['details'] = $details;
                } else {
                    // TODO(lhchavez): Remove this backwards-compatibility shim
                    // with backendv1.
                    $response['groups'] = $details;
                }
            }
            if (file_exists("$grade_dir/logs.txt.gz")) {
                $response['logs'] = file_get_contents("compress.zlib://$grade_dir/logs.txt.gz");
            } elseif (file_exists("$grade_dir/run.log")) {
                $response['logs'] = file_get_contents("$grade_dir/run.log");
            }

            $response['judged_by'] = $r['run']->judged_by;
        }
        $response['guid'] = $r['run']->guid;
        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Parses Run metadata
     *
     * @param string $meta
     * @return array
     */
    public static function ParseMeta($meta) {
        $ans = [];

        foreach (explode("\n", trim($meta)) as $line) {
            list($key, $value) = explode(':', trim($line));
            $ans[$key] = $value;
        }

        return $ans;
    }

    /**
     * Compare two Run metadata
     *
     * @param array $a
     * @param array $b
     * @return boolean
     */
    public static function MetaCompare($a, $b) {
        if ($a['group'] == $b['group']) {
            return 0;
        }

        return ($a['group'] < $b['group']) ? -1 : 1;
    }

    public static function CaseCompare($a, $b) {
        if ($a['name'] == $b['name']) {
            return 0;
        }

        return ($a['name'] < $b['name']) ? -1 : 1;
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

        if (!(Authorization::canViewRun($r['current_user_id'], $r['run']))) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        $response = [];

        if (OMEGAUP_LOCKDOWN) {
            // OMI hotfix
            // @TODO @joemmanuel, hay que localizar este msg :P
            $response['source'] = 'Ver el código ha sido temporalmente desactivado.';
        } else {
            // Get the source
            $response['source'] = file_get_contents(RunController::getSubmissionPath($r['run']));
        }

        // Get the error
        $grade_dir = RunController::getGradePath($r['run']);
        if (file_exists("$grade_dir/compile_error.log")) {
            $response['compile_error'] = file_get_contents("$grade_dir/compile_error.log");
        }

        $response['status'] = 'ok';
        return $response;
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

        self::validateAdminDetailsRequest($r);

        $grade_dir = RunController::getGradePath($r['run']);
        $results_zip = "$grade_dir/files.zip";
        if (!file_exists($results_zip)) {
            $results_zip = "$grade_dir/results.zip";
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $r['run']->guid . '.zip');
        header('Content-Length: ' . filesize($results_zip));
        readfile($results_zip);
        exit;
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

        if (!Authorization::isSystemAdmin($r['current_user_id'])) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        Validators::isNumber($r['offset'], 'offset', false);
        Validators::isNumber($r['rowcount'], 'rowcount', false);
        Validators::isInEnum($r['status'], 'status', ['new', 'waiting', 'compiling', 'running', 'ready'], false);
        Validators::isInEnum($r['verdict'], 'verdict', ['AC', 'PA', 'WA', 'TLE', 'MLE', 'OLE', 'RTE', 'RFE', 'CE', 'JE', 'NO-AC'], false);

        // Check filter by problem, is optional
        if (!is_null($r['problem_alias'])) {
            Validators::isStringNonEmpty($r['problem_alias'], 'problem');

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

        Validators::isInEnum($r['language'], 'language', ['c', 'cpp', 'cpp11', 'java', 'py', 'rb', 'pl', 'cs', 'pas', 'kp', 'kj', 'cat', 'hs'], false);

        // Get user if we have something in username
        if (!is_null($r['username'])) {
            try {
                $r['user'] = UserController::resolveUser($r['username']);
            } catch (NotFoundException $e) {
                // If not found, simply ignore it
                $r['username'] = null;
                $r['user'] = null;
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
            $runs = RunsDAO::GetAllRuns(
                null,
                $r['status'],
                $r['verdict'],
                !is_null($r['problem']) ? $r['problem']->problem_id : null,
                $r['language'],
                !is_null($r['user']) ? $r['user']->user_id : null,
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
