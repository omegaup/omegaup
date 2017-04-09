<?php

require_once 'libs/FileHandler.php';
require_once 'libs/ZipHandler.php';
require_once 'libs/third_party/Markdown/markdown.php';
/**
 * ProblemsController
 */
class ProblemController extends Controller {
    public static $grader = null;

    /**
     * Creates an instance of Grader if not already created
     */
    private static function initializeGrader() {
        if (is_null(self::$grader)) {
            // Create new grader
            self::$grader = new Grader();
        }
    }

    /**
     * Validates a Create or Update Problem API request
     *
     * @param Request $r
     * @throws NotFoundException
     */
    private static function validateCreateOrUpdate(Request $r, $is_update = false) {
        $is_required = true;
        // https://github.com/omegaup/omegaup/issues/739
        if ($r['current_user']->username == 'omi') {
            throw new ForbiddenAccessException();
        }

        // In case of update, params are optional
        if ($is_update) {
            $is_required = false;

            // We need to check problem_alias
            Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

            try {
                $r['problem'] = ProblemsDAO::getByAlias($r['problem_alias']);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            if (is_null($r['problem'])) {
                throw new NotFoundException('Problem not found');
            }

            // We need to check that the user can actually edit the problem
            if (!Authorization::canEditProblem($r['current_user_id'], $r['problem'])) {
                throw new ForbiddenAccessException();
            }

            if ($r['problem']->deprecated) {
                throw new PreconditionFailedException('problemDeprecated');
            }
        } else {
            Validators::isValidAlias($r['alias'], 'alias');
        }

        Validators::isStringNonEmpty($r['title'], 'title', $is_required);
        Validators::isStringNonEmpty($r['source'], 'source', $is_required);
        Validators::isInEnum($r['public'], 'public', ['0', '1'], $is_required);
        Validators::isInEnum(
            $r['validator'],
            'validator',
            ['token', 'token-caseless', 'token-numeric', 'custom', 'literal'],
            $is_required
        );
        Validators::isNumberInRange($r['time_limit'], 'time_limit', 0, INF, $is_required);
        Validators::isNumberInRange($r['validator_time_limit'], 'validator_time_limit', 0, INF, $is_required);
        Validators::isNumberInRange($r['overall_wall_time_limit'], 'overall_wall_time_limit', 0, 60000, $is_required);
        Validators::isNumberInRange($r['extra_wall_time'], 'extra_wall_time', 0, 5000, $is_required);
        Validators::isNumberInRange($r['memory_limit'], 'memory_limit', 0, INF, $is_required);
        Validators::isNumberInRange($r['output_limit'], 'output_limit', 0, INF, $is_required);

        // HACK! I don't know why "languages" doesn't make it into $r, and I've spent far too much time
        // on it already, so I'll just leave this here for now...
        if (!isset($r['languages']) && isset($_REQUEST['languages'])) {
            $r['languages'] = implode(',', $_REQUEST['languages']);
        } elseif (isset($r['languages']) && is_array($r['languages'])) {
            $r['languages'] = implode(',', $r['languages']);
        }
        Validators::isValidSubset(
            $r['languages'],
            'languages',
            RunController::$kSupportedLanguages,
            $is_required
        );
    }

    /**
     * Create a new problem
     *
     * @throws ApiException
     * @throws DuplicatedEntryInDatabaseException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiCreate(Request $r) {
        self::authenticateRequest($r);

        // Validates request
        self::validateCreateOrUpdate($r);

        // Populate a new Problem object
        $problem = new Problems();
        $problem->public = $r['public']; /* private by default */
        $problem->title = $r['title'];
        $problem->validator = $r['validator'];
        $problem->time_limit = $r['time_limit'];
        $problem->validator_time_limit = $r['validator_time_limit'];
        $problem->overall_wall_time_limit = $r['overall_wall_time_limit'];
        $problem->extra_wall_time = $r['extra_wall_time'];
        $problem->memory_limit = $r['memory_limit'];
        $problem->output_limit = $r['output_limit'];
        $problem->visits = 0;
        $problem->submissions = 0;
        $problem->accepted = 0;
        $problem->difficulty = 0;
        $problem->source = $r['source'];
        $problem->order = 'normal'; /* defaulting to normal */
        $problem->alias = $r['alias'];
        $problem->languages = $r['languages'];
        $problem->stack_limit = $r['stack_limit'];
        $problem->email_clarifications = $r['email_clarifications'];

        if (file_exists(PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r['alias'])) {
            throw new DuplicatedEntryInDatabaseException('problemExists');
        }

        $problemDeployer = new ProblemDeployer($r['alias'], ProblemDeployer::CREATE);

        $acl = new ACLs();
        $acl->owner_id = $r['current_user_id'];

        // Insert new problem
        try {
            ProblemsDAO::transBegin();

            // Create file after we know that alias is unique
            $problemDeployer->deploy();
            if ($problemDeployer->hasValidator) {
                $problem->validator = 'custom';
            } elseif ($problem->validator == 'custom') {
                throw new ProblemDeploymentFailedException('problemDeployerValidatorRequired');
            }
            $problem->slow = $problemDeployer->isSlow($problem);

            // Calculate output limit.
            $output_limit = $problemDeployer->getOutputLimit();

            if ($output_limit != -1) {
                $problem->output_limit = $output_limit;
            }

            // Save the contest object with data sent by user to the database
            ACLsDAO::save($acl);
            $problem->acl_id = $acl->acl_id;
            ProblemsDAO::save($problem);

            ProblemsDAO::transEnd();

            // Commit at the very end
            $problemDeployer->commit('Initial commit', $r['current_user']);
        } catch (ApiException $e) {
            // Operation failed in something we know it could fail, rollback transaction
            ProblemsDAO::transRollback();

            throw $e;
        } catch (Exception $e) {
            self::$log->error('Failed to upload problem');
            self::$log->error($e);

            // Operation failed unexpectedly, rollback transaction
            ProblemsDAO::transRollback();

            // Alias may be duplicated, 1062 error indicates that
            if (strpos($e->getMessage(), '1062') !== false) {
                throw new DuplicatedEntryInDatabaseException('problemTitleExists');
            } else {
                throw new InvalidDatabaseOperationException($e);
            }
        } finally {
            $problemDeployer->cleanup();
        }

        // Adding unzipped files to response
        $result['uploaded_files'] = $problemDeployer->filesToUnzip;
        $result['status'] = 'ok';
        $result['alias'] = $r['alias'];

        return $result;
    }

    /**
     * Validates a Rejudge Problem API request
     *
     * @param Request $r
     * @throws NotFoundException
     */
    private static function validateRejudge(Request $r) {
        // We need to check problem_alias
        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        try {
            $r['problem'] = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['problem'])) {
            throw new NotFoundException('problemNotFound');
        }

        if ($r['problem']->deprecated) {
            throw new PreconditionFailedException('problemDeprecated');
        }

        // We need to check that the user actually has admin privileges over
        // the problem.
        if (!Authorization::isProblemAdmin($r['current_user_id'], $r['problem'])) {
            throw new ForbiddenAccessException();
        }
    }

    /**
     * Adds an admin to a problem
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiAddAdmin(Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check problem_alias
        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        $user = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
        }

        if (!Authorization::isProblemAdmin($r['current_user_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        $user_role = new UserRoles();
        $user_role->acl_id = $problem->acl_id;
        $user_role->user_id = $user->user_id;
        $user_role->role_id = Authorization::ADMIN_ROLE;

        // Save the contest to the DB
        try {
            UserRolesDAO::save($user_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            self::$log->error('Failed to save user roles');
            self::$log->error($e);
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Adds a group admin to a problem
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiAddGroupAdmin(Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check problem_alias
        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        $group = GroupsDAO::FindByAlias($r['group']);

        if ($group == null) {
            throw new InvalidParameterException('invalidParameters');
        }

        try {
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (!Authorization::isProblemAdmin($r['current_user_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        $group_role = new GroupRoles();
        $group_role->acl_id = $problem->acl_id;
        $group_role->group_id = $group->group_id;
        $group_role->role_id = Authorization::ADMIN_ROLE;

        // Save the role
        try {
            GroupRolesDAO::save($group_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            self::$log->error('Failed to save user roles');
            self::$log->error($e);
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Adds a tag to a problem
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiAddTag(Request $r) {
        // Check problem_alias
        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        // Authenticate logged user
        self::authenticateRequest($r);

        $problem = ProblemsDAO::getByAlias($r['problem_alias']);

        if (!Authorization::canEditProblem($r['current_user_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        // Normalize name.
        $tag_name = $r['name'];
        Validators::isStringNonEmpty($tag_name, 'name');
        $tag_name = TagController::normalize($tag_name);

        try {
            $tag = TagsDAO::getByName($tag_name);
        } catch (Exception $e) {
            $this->log->info($e);
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if ($tag == null) {
            try {
                $tag = new Tags();
                $tag->name = $tag_name;
                TagsDAO::save($tag);
            } catch (Exception $inner) {
                $this->log->info($e);
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($inner);
            }
        }

        if (is_null($tag->tag_id)) {
            throw new InvalidDatabaseOperationException(new Exception('tag'));
        }

        $problem_tag = new ProblemsTags();
        $problem_tag->problem_id = $problem->problem_id;
        $problem_tag->tag_id = $tag->tag_id;
        $problem_tag->public = $r['public'] ? 1 : 0;

        // Save the tag to the DB
        try {
            ProblemsTagsDAO::save($problem_tag);
        } catch (Exception $e) {
            // Operation failed in the data layer
            self::$log->error('Failed to save tag', $e);
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok', 'name' => $tag_name];
    }

    /**
     * Removes an admin from a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiRemoveAdmin(Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check whether problem exists
        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        $user = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (!Authorization::isProblemAdmin($r['current_user_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        // Check if admin to delete is actually an admin
        if (!Authorization::isProblemAdmin($user->user_id, $problem)) {
            throw new NotFoundException();
        }

        $user_role = new UserRoles();
        $user_role->acl_id = $problem->acl_id;
        $user_role->user_id = $user->user_id;
        $user_role->role_id = Authorization::ADMIN_ROLE;

        // Delete the role
        try {
            UserRolesDAO::delete($user_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Removes a group admin from a problem
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiRemoveGroupAdmin(Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check whether problem exists
        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        $group = GroupsDAO::FindByAlias($r['group']);

        if ($group == null) {
            throw new InvalidParameterException('invalidParameters');
        }

        try {
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (!Authorization::isProblemAdmin($r['current_user_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        $group_role = new GroupRoles();
        $group_role->acl_id = $problem->acl_id;
        $group_role->group_id = $group->group_id;
        $group_role->role_id = Authorization::ADMIN_ROLE;

        // Delete the role
        try {
            GroupRolesDAO::delete($group_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Removes a tag from a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiRemoveTag(Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check whether problem exists
        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        try {
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
            $tag = TagsDAO::getByName($r['name']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($problem)) {
            throw new NotFoundException('problem');
        } elseif (is_null($tag)) {
            throw new NotFoundException('tag');
        }

        if (!Authorization::canEditProblem($r['current_user_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        $problem_tag = new ProblemsTags();
        $problem_tag->problem_id = $problem->problem_id;
        $problem_tag->tag_id = $tag->tag_id;

        // Delete the role
        try {
            ProblemsTagsDAO::delete($problem_tag);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Returns all problem administrators
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiAdmins(Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        try {
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (!Authorization::isProblemAdmin($r['current_user_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        $response = [];
        $response['admins'] = UserRolesDAO::getProblemAdmins($problem);
        $response['group_admins'] = GroupRolesDAO::getProblemAdmins($problem);
        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Returns all problem tags
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiTags(Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        try {
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response = [];
        $response['tags'] = ProblemsTagsDAO::getProblemTags(
            $problem,
            !Authorization::canEditProblem($r['current_user_id'], $problem)
        );

        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Rejudge problem
     *
     * @param Request $r
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiRejudge(Request $r) {
        self::authenticateRequest($r);

        self::validateRejudge($r);

        // We need to rejudge runs after an update, let's initialize the grader
        self::initializeGrader();

        // Call Grader
        $runs = [];
        try {
            $runs = RunsDAO::search(new Runs([
                                'problem_id' => $r['problem']->problem_id
                            ]));

            $guids = [];
            foreach ($runs as $run) {
                $guids[] = $run->guid;
                $run->status = 'new';
                $run->verdict = 'JE';
                $run->score = 0;
                $run->contest_score = 0;
                RunsDAO::save($run);

                // Expire details of the run
                RunController::invalidateCacheOnRejudge($run);
            }
            self::$grader->Grade($guids, true, false);
        } catch (Exception $e) {
            self::$log->error('Failed to rejudge runs after problem update');
            self::$log->error($e);
            throw new InvalidDatabaseOperationException($e);
        }

        $response = [];

        // All clear
        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Update problem contents
     *
     * @param Request $r
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiUpdate(Request $r) {
        self::authenticateRequest($r);

        self::validateCreateOrUpdate($r, true /* is update */);

        // Validate commit message.
        Validators::isStringNonEmpty($r['message'], 'message');

        // Update the Problem object
        $valueProperties = [
            'public',
            'title',
            'validator'     => ['important' => true], // requires rejudge
            'time_limit'    => ['important' => true], // requires rejudge
            'validator_time_limit'    => ['important' => true], // requires rejudge
            'overall_wall_time_limit' => ['important' => true], // requires rejudge
            'extra_wall_time' => ['important' => true], // requires rejudge
            'memory_limit'  => ['important' => true], // requires rejudge
            'output_limit'  => ['important' => true], // requires rejudge
            'stack_limit'   => ['important' => true], // requires rejudge
            'email_clarifications',
            'source',
            'order',
            'languages',
        ];
        $problem = $r['problem'];
        $requiresRejudge = self::updateValueProperties($r, $problem, $valueProperties);
        $r['problem'] = $problem;

        $response = [
            'rejudged' => false
        ];
        $problemDeployer = new ProblemDeployer($problem->alias, ProblemDeployer::UPDATE_CASES);

        // Insert new problem
        try {
            //Begin transaction
            ProblemsDAO::transBegin();

            if (isset($_FILES['problem_contents']) && FileHandler::GetFileUploader()->IsUploadedFile($_FILES['problem_contents']['tmp_name'])) {
                // DeployProblemZip requires alias => problem_alias
                $r['alias'] = $r['problem_alias'];

                $problemDeployer->deploy();
                if ($problemDeployer->hasValidator) {
                    $problem->validator = 'custom';
                } elseif ($problem->validator == 'custom') {
                    throw new ProblemDeploymentFailedException('problemDeployerValidatorRequired');
                }
                // This must come before the commit in case isSlow throws an exception.
                $problem->slow = $problemDeployer->isSlow($problem);

                // Calculate output limit.
                $output_limit = $problemDeployer->getOutputLimit();

                if ($output_limit != -1) {
                    $r['problem']->output_limit = $output_limit;
                }

                $response['uploaded_files'] = $problemDeployer->filesToUnzip;
                $problemDeployer->commit($r['message'], $r['current_user']);
                $requiresRejudge |= $problemDeployer->requiresRejudge;
            } else {
                $problem->slow = $problemDeployer->isSlow($problem);
            }

            // Save the contest object with data sent by user to the database
            ProblemsDAO::save($problem);

            //End transaction
            ProblemsDAO::transEnd();
        } catch (ApiException $e) {
            // Operation failed in the data layer, rollback transaction
            ProblemsDAO::transRollback();

            throw $e;
        } catch (Exception $e) {
            // Operation failed in the data layer, rollback transaction
            ProblemsDAO::transRollback();
            self::$log->error('Failed to update problem');
            self::$log->error($e);

            throw new InvalidDatabaseOperationException($e);
        } finally {
            $problemDeployer->cleanup();
        }

        if (($requiresRejudge == true) && (OMEGAUP_ENABLE_REJUDGE_ON_PROBLEM_UPDATE == true)) {
            self::$log->info('Calling ProblemController::apiRejudge');
            try {
                self::apiRejudge($r);
                $response['rejudged'] = true;
            } catch (Exception $e) {
                self::$log->error('Best efort ProblemController::apiRejudge failed', $e);
            }
        }

        if ($r['redirect'] === true) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }

        // All clear
        $response['status'] = 'ok';

        // Invalidar problem statement cache @todo invalidar todos los lenguajes
        foreach ($problemDeployer->getUpdatedLanguages() as $lang) {
            Cache::deleteFromCache(Cache::PROBLEM_STATEMENT, $r['problem']->alias . '-' . $lang . 'html');
            Cache::deleteFromCache(Cache::PROBLEM_STATEMENT, $r['problem']->alias . '-' . $lang . 'markdown');
        }
        Cache::deleteFromCache(Cache::PROBLEM_SAMPLE, $r['problem']->alias . '-sample.in');

        return $response;
    }

    /**
     * Updates problem statement only
     *
     * @param Request $r
     * @return array
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiUpdateStatement(Request $r) {
        self::authenticateRequest($r);

        self::validateCreateOrUpdate($r, true);

        // Validate statement
        Validators::isStringNonEmpty($r['statement'], 'statement');
        Validators::isStringNonEmpty($r['message'], 'message');

        // Check that lang is in the ISO 639-1 code list, default is "es".
        $iso639_1 = ['ab', 'aa', 'af', 'ak', 'sq', 'am', 'ar', 'an', 'hy',
            'as', 'av', 'ae', 'ay', 'az', 'bm', 'ba', 'eu', 'be', 'bn', 'bh', 'bi',
            'bs', 'br', 'bg', 'my', 'ca', 'ch', 'ce', 'ny', 'zh', 'cv', 'kw', 'co',
            'cr', 'hr', 'cs', 'da', 'dv', 'nl', 'dz', 'en', 'eo', 'et', 'ee', 'fo',
            'fj', 'fi', 'fr', 'ff', 'gl', 'ka', 'de', 'el', 'gn', 'gu', 'ht', 'ha',
            'he', 'hz', 'hi', 'ho', 'hu', 'ia', 'id', 'ie', 'ga', 'ig', 'ik', 'io',
            'is', 'it', 'iu', 'ja', 'jv', 'kl', 'kn', 'kr', 'ks', 'kk', 'km', 'ki',
            'rw', 'ky', 'kv', 'kg', 'ko', 'ku', 'kj', 'la', 'lb', 'lg', 'li', 'ln',
            'lo', 'lt', 'lu', 'lv', 'gv', 'mk', 'mg', 'ms', 'ml', 'mt', 'mi', 'mr',
            'mh', 'mn', 'na', 'nv', 'nd', 'ne', 'ng', 'nb', 'nn', 'no', 'ii', 'nr',
            'oc', 'oj', 'cu', 'om', 'or', 'os', 'pa', 'pi', 'fa', 'pl', 'ps', 'pt',
            'qu', 'rm', 'rn', 'ro', 'ru', 'sa', 'sc', 'sd', 'se', 'sm', 'sg', 'sr',
            'gd', 'sn', 'si', 'sk', 'sl', 'so', 'st', 'es', 'su', 'sw', 'ss', 'sv',
            'ta', 'te', 'tg', 'th', 'ti', 'bo', 'tk', 'tl', 'tn', 'to', 'tr', 'ts',
            'tt', 'tw', 'ty', 'ug', 'uk', 'ur', 'uz', 've', 'vi', 'vo', 'wa', 'cy',
            'wo', 'fy', 'xh', 'yi', 'yo', 'za', 'zu'];
        Validators::isInEnum($r['lang'], 'lang', $iso639_1, false /* is_required */);
        if (is_null($r['lang'])) {
            $r['lang'] = UserController::getPreferredLanguage($r);
        }

        $problemDeployer = new ProblemDeployer($r['problem_alias'], ProblemDeployer::UPDATE_STATEMENTS);
        try {
            $problemDeployer->updateStatement($r['lang'], $r['statement']);
            $problemDeployer->commit("{$r['lang']}.markdown: {$r['message']}", $r['current_user']);

            // Invalidar problem statement cache
            Cache::deleteFromCache(Cache::PROBLEM_STATEMENT, $r['problem']->alias . '-' . $r['lang'] . '-' . 'html');
            Cache::deleteFromCache(Cache::PROBLEM_STATEMENT, $r['problem']->alias . '-' . $r['lang'] . '-' . 'markdown');
            Cache::deleteFromCache(Cache::PROBLEM_SAMPLE, $r['problem']->alias . '-sample.in');
        } catch (ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        } finally {
            $problemDeployer->cleanup();
        }

        // All clear
        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Validate problem Details API
     *
     * @param Request $r
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     * @throws ForbiddenAccessException
     */
    private static function validateDetails(Request $r) {
        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias', false);
        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        // Lang is optional. Default is user's preferred.
        if (!is_null($r['lang'])) {
            Validators::isStringOfMaxLength($r['lang'], 'lang', 2);
        } else {
            $r['lang'] = UserController::getPreferredLanguage($r);
        }

        try {
            $r['problem'] = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['problem'])) {
            throw new NotFoundException('problemNotFound');
        }

        if (isset($r['statement_type']) && !in_array($r['statement_type'], ['html', 'markdown'])) {
            throw new NotFoundException('invalidStatementType');
        }

        // If we request a problem inside a contest
        if (self::validateProblemset($r)) {
            if (!Authorization::isAdmin($r['current_user_id'], $r['problemset'])) {
                // If the contest is private, verify that our user is invited
                if (isset($r['contest'])) {
                    if ($r['contest']->public != '1') {
                        if (is_null(ProblemsetUsersDAO::getByPK($r['current_user_id'], $r['problemset']->problemset_id))) {
                            throw new ForbiddenAccessException();
                        }
                    }
                    // If the contest has not started, non-admin users should not see it
                    if (!ContestsDAO::hasStarted($r['contest'])) {
                        throw new ForbiddenAccessException('contestNotStarted');
                    }
                } else {    // Not a contest, but we still have a problemset
                    if (!Authorization::canSubmitToProblemset($r['current_user_id'], $r['problemset'])) {
                        throw new ForbiddenAccessException();
                    }
                    // TODO: Check start times.
                }
            }
        } else {
            if (!Authorization::canEditProblem($r['current_user_id'], $r['problem'])) {
                // If the problem is requested outside a contest, we need to
                // check that it is not private
                if ($r['problem']->public != '1') {
                    throw new ForbiddenAccessException('problemIsPrivate');
                }
            }
        }
    }

    /**
     * Gets the problem statement from the filesystem.
     *
     * @param Request $r
     * @throws InvalidFilesystemOperationException
     */
    public static function getProblemStatement(Request $r) {
        $statement_type = ProblemController::getStatementType($r);
        $source_path = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r['problem']->alias . DIRECTORY_SEPARATOR . 'statements' . DIRECTORY_SEPARATOR . $r['lang'] . '.' . $statement_type;

        try {
            $file_content = FileHandler::ReadFile($source_path);
        } catch (Exception $e) {
            throw new InvalidFilesystemOperationException('statementNotFound');
        }

        return $file_content;
    }

    public static function isLanguageSupportedForProblem(Request $r) {
        $statement_type = ProblemController::getStatementType($r);
        $source_path = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r['problem']->alias . DIRECTORY_SEPARATOR . 'statements' . DIRECTORY_SEPARATOR . $r['lang'] . '.' . $statement_type;

        return file_exists($source_path);
    }

    /**
     * Gets the sample input from the filesystem.
     *
     * @param Request $r
     * @throws InvalidFilesystemOperationException
     */
    public static function getSampleInput(Request $r) {
        $source_path = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r['problem']->alias . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'sample.in';

        try {
            $file_content = FileHandler::ReadFile($source_path);
        } catch (Exception $e) {
            // Most problems won't have a sample input.
            $file_content = '';
        }

        return $file_content;
    }

    /**
     * Get the type of statement that was requested.
     * HTML is the default if statement_type unspecified in the request.
     *
     * @param Request $r
     */
    private static function getStatementType(Request $r) {
        $type = 'html';
        if (isset($r['statement_type'])) {
            $type = $r['statement_type'];
        }
        return $type;
    }

    /**
     * Entry point for Problem Download API
     *
     * @param Request $r
     * @throws InvalidFilesystemOperationException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiDownload(Request $r) {
        self::authenticateRequest($r);

        // Validate request
        self::validateDownload($r);

        // Get HEAD revision to avoid race conditions.
        $gitDir = PROBLEMS_GIT_PATH . DIRECTORY_SEPARATOR . $r['problem']->alias;
        $git = new Git($gitDir);
        $head = trim($git->get(['rev-parse', 'HEAD']));

        // Set headers to auto-download file
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment;filename=' . $r['problem']->alias . '_' . $head . '.zip');
        header('Content-Transfer-Encoding: binary');
        $git->exec(['archive', '--format=zip', $head]);

        die();
    }

    /**
     * Validate problem Details API
     *
     * @param Request $r
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     * @throws ForbiddenAccessException
     */
    private static function validateDownload(Request $r) {
        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        try {
            $r['problem'] = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['problem'])) {
            throw new NotFoundException('problemNotFound');
        }

        if (!Authorization::isProblemAdmin($r['current_user_id'], $r['problem'])) {
            throw new ForbiddenAccessException();
        }
    }

    private static function validateProblemset(Request $r) {
        $problemNotFound = null;
        if (!empty($r['contest_alias'])) {
            try {
                // Is it a valid contest_alias?
                $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
                if (is_null($r['contest'])) {
                    throw new NotFoundException('contestNotFound');
                }
                $r['problemset'] = ProblemsetsDAO::getByPK($r['contest']->problemset_id);
                if (is_null($r['problemset'])) {
                    throw new NotFoundException('contestNotFound');
                }
                $problemNotFound = 'problemNotFoundInContest';
            } catch (ApiException $apiException) {
                throw $apiException;
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }
        } elseif (!empty($r['problemset_id'])) {
            try {
                // Is it a valid problemset_id?
                $r['problemset'] = ProblemsetsDAO::getByPK($r['problemset_id']);
                if (is_null($r['problemset'])) {
                    throw new NotFoundException('problemsetNotFound');
                }
                $problemNotFound = 'problemNotFoundInProblemset';
            } catch (ApiException $apiException) {
                throw $apiException;
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }
        } else {
            // Nothing to see here, move along.
            return false;
        }

        // Is the problem actually in the problemset?
        if (is_null(ProblemsetProblemsDAO::getByPK(
            $r['problemset']->problemset_id,
            $r['problem']->problem_id
        ))
        ) {
            throw new NotFoundException($problemNotFound);
        }

        return true;
    }

    /**
     * Entry point for Problem Details API
     *
     * @param Request $r
     * @throws InvalidFilesystemOperationException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiDetails(Request $r) {
        // Get user.
        // Allow unauthenticated requests if we are not openning a problem
        // inside a contest.
        try {
            self::authenticateRequest($r);
        } catch (UnauthorizedException $e) {
            if (!is_null($r['contest_alias']) || !is_null($r['problemset_id'])) {
                throw $e;
            }
        }

        // Validate request
        self::validateDetails($r);

        $response = [];

        // Create array of relevant columns
        $relevant_columns = ['title', 'alias', 'validator', 'time_limit',
            'validator_time_limit', 'overall_wall_time_limit', 'extra_wall_time',
            'memory_limit', 'output_limit', 'visits', 'submissions', 'accepted',
            'difficulty', 'creation_date', 'source', 'order', 'points', 'public',
            'languages', 'slow', 'stack_limit', 'email_clarifications'];

        // Read the file that contains the source
        if (!ProblemController::isLanguageSupportedForProblem($r)) {
            // If there is no language file for the problem, return the spanish version.
            $r['lang'] = 'es';
        }
        $statement_type = ProblemController::getStatementType($r);
        Cache::getFromCacheOrSet(
            Cache::PROBLEM_STATEMENT,
            $r['problem']->alias . '-' . $r['lang'] . '-' . $statement_type,
            $r,
            'ProblemController::getProblemStatement',
            $file_content,
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );

        // Add problem statement to source
        $response['problem_statement'] = $file_content;
        $response['problem_statement_language'] = $r['lang'];

        // Add the example input.
        $sample_input = null;
        Cache::getFromCacheOrSet(
            Cache::PROBLEM_SAMPLE,
            $r['problem']->alias . '-sample.in',
            $r,
            'ProblemController::getSampleInput',
            $sample_input,
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );
        if (!empty($sample_input)) {
            $response['sample_input'] = $sample_input;
        }

        // Add the problem the response
        $response = array_merge($response, $r['problem']->asFilteredArray($relevant_columns));

        // If the problem is public or if the user has admin privileges, show the
        // problem source and alias of owner.
        if ($r['problem']->public ||
            Authorization::isProblemAdmin($r['current_user_id'], $r['problem'])) {
            $acl = ACLsDAO::getByPK($r['problem']->acl_id);
            $problemsetter = UsersDAO::getByPK($acl->owner_id);
            $response['problemsetter'] = [
                'username' => $problemsetter->username,
                'name' => is_null($problemsetter->name) ?
                          $problemsetter->username :
                          $problemsetter->name
            ];
        } else {
            unset($response['source']);
        }

        $problemset_id = isset($r['problemset']) ? $r['problemset']->problemset_id : null;

        if (!is_null($r['current_user_id'])) {
            // Create array of relevant columns for list of runs
            $relevant_columns = ['guid', 'language', 'status', 'verdict',
                'runtime', 'penalty', 'memory', 'score', 'contest_score', 'time',
                'submit_delay'];

            // Search the relevant runs from the DB
            $keyrun = new Runs([
                'user_id' => $r['current_user_id'],
                'problem_id' => $r['problem']->problem_id,
                'problemset_id' => $problemset_id
            ]);

            // Get all the available runs done by the current_user
            try {
                $runs_array = RunsDAO::search($keyrun);
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            // Add each filtered run to an array
            if (count($runs_array) >= 0) {
                $runs_filtered_array = [];
                foreach ($runs_array as $run) {
                    $filtered = $run->asFilteredArray($relevant_columns);
                    $filtered['alias'] = $r['problem']->alias;
                    $filtered['username'] = $r['current_user']->username;
                    $filtered['time'] = strtotime($filtered['time']);
                    array_push($runs_filtered_array, $filtered);
                }
            }

            $response['runs'] = $runs_filtered_array;
        }

        if (!is_null($problemset_id)) {
            // At this point, contestant_user relationship should be established.
            try {
                ProblemsetUsersDAO::CheckAndSaveFirstTimeAccess(
                    $r['current_user_id'],
                    $problemset_id,
                    Authorization::canSubmitToProblemset($r['current_user_id'], $r['problemset'])
                );
            } catch (ApiException $e) {
                throw $e;
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            // As last step, register the problem as opened
            if (!ProblemsetProblemOpenedDAO::getByPK(
                $problemset_id,
                $r['problem']->problem_id,
                $r['current_user_id']
            )) {
                try {
                    // Save object in the DB
                    ProblemsetProblemOpenedDAO::save(new ProblemsetProblemOpened([
                        'problemset_id' => $problemset_id,
                        'problem_id' => $r['problem']->problem_id,
                        'user_id' => $r['current_user_id']
                    ]));
                } catch (Exception $e) {
                    // Operation failed in the data layer
                    throw new InvalidDatabaseOperationException($e);
                }
            }
        } elseif (isset($r['show_solvers']) && $r['show_solvers']) {
            $response['solvers'] = RunsDAO::GetBestSolvingRunsForProblem($r['problem']->problem_id);
        }

        if (!is_null($r['current_user_id'])) {
            ProblemViewedDAO::MarkProblemViewed(
                $r['current_user_id'],
                $r['problem']->problem_id
            );
        }

        $response['score'] = self::bestScore($r);
        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Validate problem Details API
     *
     * @param Request $r
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     * @throws ForbiddenAccessException
     */
    private static function validateRuns(Request $r) {
        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        // Is the problem valid?
        try {
            $r['problem'] = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (ApiException $apiException) {
            throw $apiException;
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if ($r['problem'] == null) {
            throw new NotFoundException('problemNotFound');
        }
    }

    /**
     * Entry point for Problem runs API
     *
     * @param Request $r
     * @throws InvalidFilesystemOperationException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiRuns(Request $r) {
        // Get user
        self::authenticateRequest($r);

        // Validate request
        self::validateRuns($r);

        $response = [];

        if ($r['show_all']) {
            if (!Authorization::isProblemAdmin($r['current_user_id'], $r['problem'])) {
                throw new ForbiddenAccessException();
            }
            if (!is_null($r['username'])) {
                try {
                    $r['user'] = UsersDAO::FindByUsername($r['username']);
                } catch (Exception $e) {
                    throw new NotFoundException('userNotFound');
                }
            }
            try {
                $runs = RunsDAO::GetAllRuns(
                    null,
                    $r['status'],
                    $r['verdict'],
                    $r['problem']->problem_id,
                    $r['language'],
                    !is_null($r['user']) ? $r['user']->user_id : null,
                    $r['offset'],
                    $r['rowcount']
                );

                $result = [];

                foreach ($runs as $run) {
                    $run['time'] = (int)$run['time'];
                    $run['score'] = round((float)$run['score'], 4);
                    if ($run['contest_score'] != null) {
                        $run['contest_score'] = round((float)$run['contest_score'], 2);
                    }
                    array_push($result, $run);
                }

                $response['runs'] = $result;
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }
        } else {
            $keyrun = new Runs([
                'user_id' => $r['current_user_id'],
                'problem_id' => $r['problem']->problem_id
            ]);

            // Get all the available runs
            try {
                $runs_array = RunsDAO::search($keyrun);

                // Create array of relevant columns for list of runs
                $relevant_columns = ['guid', 'language', 'status', 'verdict',
                    'runtime', 'penalty', 'memory', 'score', 'contest_score', 'time',
                    'submit_delay'];

                // Add each filtered run to an array
                $response['runs'] = [];
                if (count($runs_array) >= 0) {
                    $runs_filtered_array = [];
                    foreach ($runs_array as $run) {
                        $filtered = $run->asFilteredArray($relevant_columns);
                        $filtered['time'] = strtotime($filtered['time']);
                        $filtered['username'] = $r['current_user']->username;
                        $filtered['alias'] = $r['problem']->alias;
                        array_push($response['runs'], $filtered);
                    }
                }
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Entry point for Problem clarifications API
     *
     * @param Request $r
     * @throws InvalidFilesystemOperationException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiClarifications(Request $r) {
        // Get user
        self::authenticateRequest($r);
        self::validateRuns($r);

        $is_problem_admin = Authorization::isProblemAdmin($r['current_user_id'], $r['problem']);

        try {
            $clarifications = ClarificationsDAO::GetProblemClarifications(
                $r['problem']->problem_id,
                $is_problem_admin,
                $r['current_user_id'],
                $r['offset'],
                $r['rowcount']
            );
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        foreach ($clarifications as &$clar) {
            $clar['time'] = (int)$clar['time'];
        }

        // Add response to array
        $response = [];
        $response['clarifications'] = $clarifications;
        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Stats of a problem
     *
     * @param Request $r
     * @return array
     * @throws ForbiddenAccessException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiStats(Request $r) {
        // Get user
        self::authenticateRequest($r);

        // Validate request
        self::validateRuns($r);

        // We need to check that the user has priviledges on the problem
        if (!Authorization::isProblemAdmin($r['current_user_id'], $r['problem'])) {
            throw new ForbiddenAccessException();
        }

        try {
            // Array of GUIDs of pending runs
            $pendingRunsGuids = RunsDAO::GetPendingRunsOfProblem($r['problem']->problem_id);

            // Count of pending runs (int)
            $totalRunsCount = RunsDAO::CountTotalRunsOfProblem($r['problem']->problem_id);

            // List of verdicts
            $verdict_counts = [];

            foreach (self::$verdicts as $verdict) {
                $verdict_counts[$verdict] = RunsDAO::CountTotalRunsOfProblemByVerdict($r['problem']->problem_id, $verdict);
            }

            // Array to count AC stats per case.
            // Let's try to get the last snapshot from cache.
            $problemStatsCache = new Cache(Cache::PROBLEM_STATS, $r['problem']->alias);
            $cases_stats = $problemStatsCache->get();
            if (is_null($cases_stats)) {
                // Initialize the array at counts = 0
                $cases_stats = [];
                $cases_stats['counts'] = [];

                // We need to save the last_id that we processed, so next time we do not repeat this
                $cases_stats['last_id'] = 0;

                // Build problem dir
                $problem_dir = PROBLEMS_PATH . '/' . $r['problem']->alias . '/cases/';

                // Get list of cases
                $dir = opendir($problem_dir);
                if (is_dir($problem_dir)) {
                    while (($file = readdir($dir)) !== false) {
                        // If we have an input
                        if (strstr($file, '.in')) {
                            // Initialize it to 0
                            $cases_stats['counts'][str_replace('.in', '', $file)] = 0;
                        }
                    }
                    closedir($dir);
                }
            }

            // Get all runs of this problem after the last id we had
            $runs = RunsDAO::searchRunIdGreaterThan(new Runs(['problem_id' => $r['problem']->problem_id]), $cases_stats['last_id'], 'run_id');

            // For each run we got
            foreach ($runs as $run) {
                // Build grade dir
                $grade_dir = RunController::getGradePath($run);

                // Skip it if it failed to compile.
                if (file_exists("$grade_dir/compile_error.log")) {
                    continue;
                }

                // Try to open the details file.
                if (file_exists("$grade_dir/details.json")) {
                    $details = json_decode(file_get_contents("$grade_dir/details.json"));
                    foreach ($details as $group) {
                        foreach ($group->cases as $case) {
                            if ($case->score > 0) {
                                $cases_stats['counts'][$case->name]++;
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        // Save the last id we saw in case we saw something
        if (!is_null($runs) && count($runs) > 0) {
            $cases_stats['last_id'] = $runs[count($runs) - 1]->run_id;
        }

        // Save in cache what we got
        $problemStatsCache->set($cases_stats, APC_USER_CACHE_PROBLEM_STATS_TIMEOUT);

        return [
            'total_runs' => $totalRunsCount,
            'pending_runs' => $pendingRunsGuids,
            'verdict_counts' => $verdict_counts,
            'cases_stats' => $cases_stats['counts'],
            'status' => 'ok'
        ];
    }

    /**
     * Validate list request
     *
     * @param Request $r
     */
    private static function validateList(Request $r) {
        Validators::isNumber($r['offset'], 'offset', false);
        Validators::isNumber($r['rowcount'], 'rowcount', false);

        // Defaults for offset and rowcount
        if (!isset($r['page'])) {
            if (!isset($r['offset'])) {
                $r['offset'] = 0;
            }
            if (!isset($r['rowcount'])) {
                $r['rowcount'] = 1000;
            }
        }

        Validators::isStringNonEmpty($r['query'], 'query', false);
    }

    /**
     * List of public and user's private problems
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     */
    public static function apiList(Request $r) {
        // Authenticate request
        try {
            self::authenticateRequest($r);
        } catch (UnauthorizedException $e) {
            // Do nothing, we allow unauthenticated users to use this API
        }

        self::validateList($r);

        // Sort results
        $order = 'problem_id'; // Order by problem_id by default.
        $sorting_options = ['title', 'submissions', 'accepted', 'ratio', 'points', 'score'];
        // "order_by" may be one of the allowed options, otherwise the default ordering will be used.
        if (!is_null($r['order_by']) && in_array($r['order_by'], $sorting_options)) {
            $order = $r['order_by'];
        }

        // "mode" may be a valid one, for compatibility reasons 'descending' is the mode by default.
        if (!is_null($r['mode']) && ($r['mode'] === 'asc' || $r['mode'] === 'desc')) {
            $mode = $r['mode'];
        } else {
            $mode = 'desc';
        }

        $response = [];
        $response['results'] = [];
        $author_id = null;
        // There are basically three types of users:
        // - Non-logged in users: Anonymous
        // - Logged in users with normal permissions: Normal
        // - Logged in users with administrative rights: Admin
        $user_type = USER_ANONYMOUS;
        if (!is_null($r['current_user_id'])) {
            $author_id = intval($r['current_user_id']);
            if (Authorization::isSystemAdmin($r['current_user_id']) ||
                Authorization::hasRole(
                    $r['current_user_id'],
                    Authorization::SYSTEM_ACL,
                    Authorization::REVIEWER_ROLE
                )
            ) {
                $user_type = USER_ADMIN;
            } else {
                $user_type = USER_NORMAL;
            }
        }

        // Search for problems whose title has $query as a substring.
        $query = is_null($r['query']) ? null : $r['query'];

        if (!is_null($r['offset']) && !is_null($r['rowcount'])) {
            // Skips the first $offset rows of the result.
            $offset = intval($r['offset']);

            // Specifies the maximum number of rows to return.
            $rowcount = intval($r['rowcount']);
        } else {
            $offset = (is_null($r['page']) ? 0 : intval($r['page']) - 1) *
                PROBLEMS_PER_PAGE;
            $rowcount = PROBLEMS_PER_PAGE;
        }

        $total = 0;
        $response['results'] = ProblemsDAO::byUserType(
            $user_type,
            $order,
            $mode,
            $offset,
            $rowcount,
            $query,
            $author_id,
            $r['tag'],
            $total
        );
        $response['total'] = $total;

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Returns a list of problems where current user has admin rights (or is
     * the owner).
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiAdminList(Request $r) {
        self::authenticateRequest($r);

        Validators::isNumber($r['page'], 'page', false);
        Validators::isNumber($r['page_size'], 'page_size', false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        try {
            if (Authorization::isSystemAdmin($r['current_user_id'])) {
                $problems = ProblemsDAO::getAll(
                    $page,
                    $pageSize,
                    'problem_id',
                    'DESC'
                );
            } else {
                $problems = ProblemsDAO::getAllProblemsAdminedByUser(
                    $r['current_user_id'],
                    $page,
                    $pageSize
                );
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $addedProblems = [];
        foreach ($problems as $problem) {
            $problemArray = $problem->asArray();
            $problemArray['tags'] = ProblemsDAO::getTagsForProblem($problem, false);
            $addedProblems[] = $problemArray;
        }

        return [
            'status' => 'ok',
            'problems' => $addedProblems,
        ];
    }

    /**
     * Gets a list of problems where current user is the owner
     *
     * @param Request $r
     */
    public static function apiMyList(Request $r) {
        self::authenticateRequest($r);
        self::validateList($r);

        Validators::isNumber($r['page'], 'page', false);
        Validators::isNumber($r['page_size'], 'page_size', false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        try {
            $problems = ProblemsDAO::getAllProblemsOwnedByUser(
                $r['current_user_id'],
                $page,
                $pageSize
            );
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $addedProblems = [];
        foreach ($problems as $problem) {
            $problemArray = $problem->asArray();
            $problemArray['tags'] = ProblemsDAO::getTagsForProblem($problem, false);
            $addedProblems[] = $problemArray;
        }

        return [
            'status' => 'ok',
            'problems' => $addedProblems,
        ];
    }

    /**
     * Returns the best score for a problem
     *
     * @param Request $r
     */
    public static function apiBestScore(Request $r) {
        self::authenticateRequest($r);

        // Uses same params as apiDetails, except for lang, which is optional
        self::validateDetails($r);

        // If username is set in the request, we use that user as target.
        // else, we query using current_user
        $user = self::resolveTargetUser($r);

        $response['score'] = self::bestScore($r, $user);
        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Returns the best score of a problem.
     * Problem must be loadad in $r["problem"]
     * Contest could be loadad in $r["contest"]. If set, will only look for
     * runs inside that contest.
     *
     * Authentication is expected to be performed earlier.
     *
     * @param Request $r
     * @return float
     * @throws InvalidDatabaseOperationException
     */
    private static function bestScore(Request $r, Users $user = null) {
        $current_user_id = (is_null($user) ? $r['current_user_id'] : $user->user_id);

        if (is_null($current_user_id)) {
            return 0;
        }

        $score = 0;
        try {
            // Add best score info
            if (!self::validateProblemset($r)) {
                $score = RunsDAO::GetBestScore($r['problem']->problem_id, $current_user_id);
            } else {
                $bestRun = RunsDAO::GetBestRun(
                    $r['problemset']->problemset_id,
                    $r['problem']->problem_id,
                    $current_user_id,
                    false /*showAllRuns*/
                );
                $score = is_null($bestRun->contest_score) ? 0 : $bestRun->contest_score;
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return $score;
    }
}
