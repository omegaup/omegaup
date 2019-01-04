<?php

require_once 'libs/FileHandler.php';
require_once 'libs/ProblemArtifacts.php';
require_once 'libs/ProblemDeployer.php';
require_once 'libs/ZipHandler.php';

/**
 * ProblemsController
 */
class ProblemController extends Controller {
    public static $grader = null;

    // Constants for problem visibility.
    const VISIBILITY_DELETED = -10; // Problem that was logically deleted by its owner
    const VISIBILITY_PRIVATE_BANNED = -2; // Problem that was private before it was banned
    const VISIBILITY_PUBLIC_BANNED = -1; // Problem that was public before it was banned
    const VISIBILITY_PRIVATE = 0;
    const VISIBILITY_PUBLIC = 1;
    const VISIBILITY_PROMOTED = 2;

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
            if (!Authorization::canEditProblem($r['current_identity_id'], $r['problem'])) {
                throw new ForbiddenAccessException();
            }

            // Only reviewers can revert bans.
            if (($r['problem']->visibility == ProblemController::VISIBILITY_PUBLIC_BANNED ||
                  $r['problem']->visibility == ProblemController::VISIBILITY_PRIVATE_BANNED)
                    && array_key_exists('visibility', $r)
                    && $r['problem']->visibility != $r['visibility']
                    && !Authorization::isQualityReviewer($r['current_identity_id'])) {
                throw new InvalidParameterException('qualityNominationProblemHasBeenBanned', 'visibility');
            }

            if ($r['problem']->deprecated) {
                throw new PreconditionFailedException('problemDeprecated');
            }

            if (!is_null($r['visibility']) && $r['problem']->visibility != $r['visibility']) {
                if ($r['problem']->visibility == ProblemController::VISIBILITY_PROMOTED) {
                    throw new InvalidParameterException('qualityNominationProblemHasBeenPromoted', 'visibility');
                } else {
                    Validators::isInEnum(
                        $r['visibility'],
                        'visibility',
                        [
                            ProblemController::VISIBILITY_PRIVATE,
                            ProblemController::VISIBILITY_PUBLIC,
                            ProblemController::VISIBILITY_PUBLIC_BANNED,
                            ProblemController::VISIBILITY_PRIVATE_BANNED
                        ]
                    );
                }
            }
        } else {
            Validators::isValidAlias($r['problem_alias'], 'problem_alias');
            Validators::isInEnum(
                $r['visibility'],
                'visibility',
                [ProblemController::VISIBILITY_PRIVATE, ProblemController::VISIBILITY_PUBLIC]
            );
            $r['selected_tags'] = json_decode($r['selected_tags']);
            $tagsHaveErrors = false;
            if (!empty($r['selected_tags'])) {
                foreach ($r['selected_tags'] as $tag) {
                    if (!isset($tag->tagname)) {
                        throw new InvalidParameterException('parameterEmpty', 'tagname');
                    }
                    if (!Validators::isStringNonEmpty($tag->tagname, 'tagname', false)) {
                        $tagsHaveErrors = true;
                        break;
                    }
                }
            }
            if ($tagsHaveErrors) {
                throw new InvalidParameterException('parameterEmpty', 'tagname');
            }
        }

        Validators::isStringNonEmpty($r['title'], 'title', $is_required);
        Validators::isStringNonEmpty($r['source'], 'source', $is_required);
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
        Validators::isNumberInRange($r['input_limit'], 'input_limit', 0, INF, $is_required);

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
            array_keys(RunController::$kSupportedLanguages),
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
        $problem->visibility = $r['visibility']; /* private by default */
        $problem->title = $r['title'];
        $problem->validator = $r['validator'];
        $problem->time_limit = $r['time_limit'];
        $problem->validator_time_limit = $r['validator_time_limit'];
        $problem->overall_wall_time_limit = $r['overall_wall_time_limit'];
        $problem->extra_wall_time = $r['extra_wall_time'];
        $problem->memory_limit = $r['memory_limit'];
        $problem->output_limit = $r['output_limit'];
        $problem->input_limit = $r['input_limit'];
        $problem->visits = 0;
        $problem->submissions = 0;
        $problem->accepted = 0;
        $problem->difficulty = 0;
        $problem->source = $r['source'];
        $problem->order = 'normal'; /* defaulting to normal */
        $problem->alias = $r['problem_alias'];
        $problem->languages = $r['languages'];
        $problem->email_clarifications = $r['email_clarifications'];
        $problem->tolerance = 1e-9;

        $problemSettings = self::getProblemSettings($problem);
        $acceptsSubmissions = $r['languages'] !== '';

        $acl = new ACLs();
        $acl->owner_id = $r['current_user_id'];

        // Insert new problem
        try {
            ProblemsDAO::transBegin();

            // Commit at the very end
            $problemDeployer = new ProblemDeployer(
                $r['problem_alias'],
                ProblemDeployer::CREATE,
                $acceptsSubmissions
            );
            $problemDeployer->commit(
                'Initial commit',
                $r['current_user'],
                $problemSettings
            );

            // Save the contest object with data sent by user to the database
            ACLsDAO::save($acl);
            $problem->acl_id = $acl->acl_id;
            ProblemsDAO::save($problem);

            // Add tags
            if (!empty($r['selected_tags'])) {
                foreach ($r['selected_tags'] as $tag) {
                    self::addTag($tag->tagname, $tag->public, $problem->problem_id);
                }
            }
            ProblemsDAO::transEnd();
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
        }

        self::updateLanguages($problem);

        return [
            'status' => 'ok',
        ];
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
        if (!Authorization::isProblemAdmin($r['current_identity_id'], $r['problem'])) {
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
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

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

        // Only an admin can add other problem admins
        if (!Authorization::isProblemAdmin($r['current_identity_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        ACLController::addUser($problem->acl_id, $user->user_id);

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
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

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

        // Only an admin can add other problem group admins
        if (!Authorization::isProblemAdmin($r['current_identity_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        ACLController::addGroup($problem->acl_id, $group->group_id);

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
        Validators::isStringNonEmpty($r['name'], 'name');

        // Authenticate logged user
        self::authenticateRequest($r);

        $problem = ProblemsDAO::getByAlias($r['problem_alias']);

        if (!Authorization::canEditProblem($r['current_identity_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        self::addTag($r['name'], $r['public'], $problem->problem_id);

        return ['status' => 'ok', 'name' => $r['name']];
    }

    private static function addTag($tagName, $isPublic, $problemId) {
        // Normalize name.
        $tagName = TagController::normalize($tagName);

        try {
            $tag = TagsDAO::getByName($tagName);
        } catch (Exception $e) {
            $this->log->info($e);
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($tag)) {
            try {
                $tag = new Tags([
                    'name' => $tagName,
                ]);
                TagsDAO::save($tag);
            } catch (Exception $e) {
                $this->log->info($e);
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }
        }

        if (is_null($tag->tag_id)) {
            throw new InvalidDatabaseOperationException(new Exception('tag'));
        }

        $problemTag = new ProblemsTags([
            'problem_id' => $problemId,
            'tag_id' => $tag->tag_id,
            'public' => filter_var($isPublic, FILTER_VALIDATE_BOOLEAN),
            'autogenerated' => 0,
        ]);

        // Save the tag to the DB
        try {
            ProblemsTagsDAO::save($problemTag);
        } catch (Exception $e) {
            // Operation failed in the data layer
            self::$log->error('Failed to save tag', $e);
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * Removes an admin from a problem
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiRemoveAdmin(Request $r) {
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

        // Only admin is alowed to make modifications
        if (!Authorization::isProblemAdmin($r['current_identity_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        // Check if admin to delete is actually an admin
        if (!Authorization::isProblemAdmin($user->main_identity_id, $problem)) {
            throw new NotFoundException();
        }

        ACLController::removeUser($problem->acl_id, $user->user_id);

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

        // Only admin is alowed to make modifications
        if (!Authorization::isProblemAdmin($r['current_identity_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        ACLController::removeGroup($problem->acl_id, $group->group_id);

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

        if (!Authorization::canEditProblem($r['current_identity_id'], $problem)) {
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
     * Removes a problem whether user is the creator
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiDelete(Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check whether problem exists
        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        try {
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (!Authorization::canEditProblem($r['current_identity_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        if (ProblemsDAO::hasBeenUsedInCoursesOrContests($problem)) {
            throw new ForbiddenAccessException('problemHasBeenUsedInContestOrCourse');
        }

        try {
            ProblemsDAO::deleteProblem($problem->problem_id);
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

        if (!Authorization::isProblemAdmin($r['current_identity_id'], $problem)) {
            throw new ForbiddenAccessException();
        }

        return [
            'status' => 'ok',
            'admins' => UserRolesDAO::getProblemAdmins($problem),
            'group_admins' => GroupRolesDAO::getProblemAdmins($problem)
        ];
    }

    /**
     * Returns every tag associated to a given problem.
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiTags(Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');
        $includeAutogenerated = ($r['include_autogenerated'] == 'true');
        try {
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response = [];
        $response['tags'] = ProblemsTagsDAO::getProblemTags(
            $problem,
            !Authorization::canEditProblem($r['current_identity_id'], $problem),
            $includeAutogenerated
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
            $runs = RunsDAO::getByKeys($r['problem']->problem_id);

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
            'visibility',
            'title',
            'validator'     => ['important' => true], // requires rejudge
            'time_limit'    => ['important' => true], // requires rejudge
            'validator_time_limit'    => ['important' => true], // requires rejudge
            'overall_wall_time_limit' => ['important' => true], // requires rejudge
            'extra_wall_time' => ['important' => true], // requires rejudge
            'memory_limit'  => ['important' => true], // requires rejudge
            'output_limit'  => ['important' => true], // requires rejudge
            'input_limit'  => ['important' => true], // requires rejudge
            'email_clarifications',
            'source',
            'order',
            'languages',
        ];
        $problem = $r['problem'];
        self::updateValueProperties($r, $problem, $valueProperties);
        $r['problem'] = $problem;

        $response = [
            'rejudged' => false
        ];

        $problemSettings = self::getProblemSettings($problem);
        $acceptsSubmissions = $problem->languages !== '';
        $updatedStatementLanguages = [];

        // Insert new problem
        try {
            //Begin transaction
            ProblemsDAO::transBegin();

            $operation = ProblemDeployer::UPDATE_SETTINGS;
            if (isset($_FILES['problem_contents'])
                && FileHandler::GetFileUploader()->IsUploadedFile($_FILES['problem_contents']['tmp_name'])
            ) {
                $operation = ProblemDeployer::UPDATE_CASES;
            }
            $problemDeployer = new ProblemDeployer(
                $problem->alias,
                $operation,
                $acceptsSubmissions
            );
            $problemDeployer->commit(
                $r['message'],
                $r['current_user'],
                $problemSettings
            );
            $response['rejudged'] = $problemDeployer->requiresRejudge;
            $updatedStatementLanguages = $problemDeployer->getUpdatedStatementLanguages();

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
        }

        if ($response['rejudged'] && OMEGAUP_ENABLE_REJUDGE_ON_PROBLEM_UPDATE) {
            self::$log->info('Calling ProblemController::apiRejudge');
            try {
                self::apiRejudge($r);
            } catch (Exception $e) {
                self::$log->error('Best effort ProblemController::apiRejudge failed', $e);
            }
        }

        if ($r['redirect'] === true) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }

        self::invalidateCache($problem, $updatedStatementLanguages);

        // All clear
        $response['status'] = 'ok';
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

        $updatedStatementLanguages = [];
        $tmpfile = tmpfile();
        try {
            fwrite($tmpfile, $r['statement']);
            $path = stream_get_meta_data($tmpfile)['uri'];

            $problemDeployer = new ProblemDeployer(
                $r['problem_alias'],
                ProblemDeployer::UPDATE_STATEMENTS
            );
            $problemDeployer->commitStatements(
                "{$r['lang']}.markdown: {$r['message']}",
                $r['current_user'],
                [
                    [
                        'path' => "statements/{$r['lang']}.markdown",
                        'contents_path' => $path,
                    ],
                ]
            );
            $updatedStatementLanguages = $problemDeployer->getUpdatedStatementLanguages();
        } catch (ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        } finally {
            fclose($tmpfile);
        }

        self::invalidateCache($r['problem'], $updatedStatementLanguages);

        // All clear
        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Invalidates the various caches of the problem, as well as updating the
     * languages.
     *
     * @param Problems $problem                   the problem
     * @param array    $updatedStatementLanguages the array of updated
     *                                            statement languages.
     *
     * @return void
     */
    private static function invalidateCache(Problems $problem, array $updatedStatementLanguages) {
        self::updateLanguages($problem);

        // Invalidate problem statement cache
        foreach ($updatedStatementLanguages as $lang) {
            Cache::deleteFromCache(Cache::PROBLEM_STATEMENT, "{$problem->alias}-{$lang}-markdown");
        }
        Cache::deleteFromCache(Cache::PROBLEM_SETTINGS_DISTRIB, $problem->alias);
    }

    /**
     * Validate problem Details API
     *
     * @param Request $r
     * @return Array
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
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($problem)) {
            return null;
        }

        if (isset($r['statement_type']) && $r['statement_type'] != 'markdown') {
            throw new NotFoundException('invalidStatementType');
        }

        // If we request a problem inside a contest
        $problemset = self::validateProblemset($problem, $r['problemset_id'], $r['contest_alias']);
        if (!is_null($problemset) && isset($problemset['problemset'])) {
            if (!Authorization::isAdmin($r['current_identity_id'], $problemset['problemset'])) {
                // If the contest is private, verify that our user is invited
                if (!empty($problemset['contest'])) {
                    if (!ContestController::isPublic($problemset['contest']->admission_mode)) {
                        if (is_null(ProblemsetIdentitiesDAO::getByPK($r['current_identity_id'], $problemset['problemset']->problemset_id))) {
                            throw new ForbiddenAccessException();
                        }
                    }
                    // If the contest has not started, non-admin users should not see it
                    if (!ContestsDAO::hasStarted($problemset['contest'])) {
                        throw new ForbiddenAccessException('contestNotStarted');
                    }
                } else {    // Not a contest, but we still have a problemset
                    if (!Authorization::canSubmitToProblemset(
                        $r['current_identity_id'],
                        $problemset['problemset']
                    )
                    ) {
                        throw new ForbiddenAccessException();
                    }
                    // TODO: Check start times.
                }
            }
        } else {
            if (!Authorization::canEditProblem($r['current_identity_id'], $problem)) {
                // If the problem is requested outside a contest, we need to
                // check that it is not private
                if (!ProblemsDAO::isVisible($problem)) {
                    throw new ForbiddenAccessException('problemIsPrivate');
                }
            }
        }
        return [
            'problem' => $problem,
            'problemset' => $problemset['problemset'],
        ];
    }

    /**
     * Gets the problem statement from the filesystem.
     *
     * @param string $sourcePath The filesystem path for the problem statement.
     *
     * @return The contents of the file.
     * @throws InvalidFilesystemOperationException
     */
    public static function getProblemStatementImpl($params) {
        list($problemAlias, $language) = $params;
        $problemArtifacts = new ProblemArtifacts($problemAlias);
        $sourcePath = "statements/{$language}.markdown";

        // Read the file that contains the source
        if (!$problemArtifacts->exists($sourcePath)) {
            // If there is no language file for the problem, return the Spanish
            // version.
            $language = 'es';
            $sourcePath = "statements/{$language}.markdown";
        }

        $result = [
            'language' => $language,
            'images' => [],
        ];
        try {
            $result['markdown'] = mb_convert_encoding($problemArtifacts->get($sourcePath), 'utf-8');
        } catch (Exception $e) {
            throw new InvalidFilesystemOperationException('statementNotFound');
        }

        // Get all the images' mappings.
        $statementFiles = $problemArtifacts->lsTree('statements');
        $imageExtensions = ['bmp', 'gif', 'ico', 'jpe', 'jpeg', 'jpg', 'png',
                            'svg', 'svgz', 'tif', 'tiff'];
        foreach ($statementFiles as $file) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (!in_array($extension, $imageExtensions)) {
                continue;
            }
            $result['images'][$file['name']] = IMAGES_URL_PATH . "{$problemAlias}/{$file['id']}.{$extension}";
            $imagePath = IMAGES_PATH . "{$problemAlias}/{$file['id']}.{$extension}";
            if (!@file_exists($imagePath)) {
                @mkdir(IMAGES_PATH . $problemAlias, 0755, true);
                file_put_contents($imagePath, $problemArtifacts->get("statements/{$file['name']}"));
            }
        }

        return $result;
    }

    /**
     * Gets the problem statement from the filesystem.
     *
     * @param string $problemAlias    The alias of the problem.
     * @param string $language        The language of the problem. Will default
     *                                to Spanish if not found.
     *
     * @return The contents of the file.
     * @throws InvalidFilesystemOperationException
     */
    public static function getProblemStatement(
        $problemAlias,
        $language
    ) {
        $problemStatement = null;
        Cache::getFromCacheOrSet(
            Cache::PROBLEM_STATEMENT,
            "{$problemAlias}-{$language}-markdown",
            [$problemAlias, $language],
            'ProblemController::getProblemStatementImpl',
            $problemStatement,
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );

        return $problemStatement;
    }

    /**
     * Gets the distributable problem settings for the problem.
     *
     * @param Problems $problem
     * @throws InvalidFilesystemOperationException
     */
    public static function getProblemSettingsDistrib(Problems $problem) {
        $problemArtifacts = new ProblemArtifacts($problem->alias);
        return $problemArtifacts->get('settings.distrib.json');
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

        // TODO(lhchavez): Support this.
        throw new NotFoundException('problemNotFound');
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

        if (!Authorization::canEditProblem($r['current_identity_id'], $r['problem'])) {
            throw new ForbiddenAccessException();
        }
    }

    /**
     * Validate problemset Details API
     *
     * @param Problems $problem
     * @param $problemsetId
     * @param $contestAlias
     * @return Array
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    private static function validateProblemset(Problems $problem, $problemsetId, $contestAlias = null) {
        $problemNotFound = null;
        $response = [];
        if (!empty($contestAlias)) {
            try {
                // Is it a valid contest_alias?
                $response['contest'] = ContestsDAO::getByAlias($contestAlias);
                if (is_null($response['contest'])) {
                    throw new NotFoundException('contestNotFound');
                }
                $response['problemset'] = ProblemsetsDAO::getByPK($response['contest']->problemset_id);
                if (is_null($response['problemset'])) {
                    throw new NotFoundException('contestNotFound');
                }
                $problemNotFound = 'problemNotFoundInContest';
            } catch (ApiException $apiException) {
                throw $apiException;
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }
        } elseif (!is_null($problemsetId)) {
            try {
                // Is it a valid problemset_id?
                $response['problemset'] = ProblemsetsDAO::getByPK($problemsetId);
                if (is_null($response['problemset'])) {
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
            return null;
        }

        // Is the problem actually in the problemset?
        if (is_null(ProblemsetProblemsDAO::getByPK(
            $response['problemset']->problemset_id,
            $problem->problem_id
        ))
        ) {
            throw new NotFoundException($problemNotFound);
        }

        return $response;
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
        $problem = self::validateDetails($r);
        if (is_null($problem)) {
            return [
                'status' => 'ok',
                'exists' => false,
            ];
        }
        $response = [];

        // Create array of relevant columns
        $relevant_columns = ['title', 'alias', 'validator', 'time_limit',
            'validator_time_limit', 'overall_wall_time_limit', 'extra_wall_time',
            'memory_limit', 'output_limit', 'input_limit', 'visits', 'submissions',
            'accepted','difficulty', 'creation_date', 'source', 'order', 'points',
            'visibility','languages', 'slow', 'email_clarifications'];

        $response['statement'] = ProblemController::getProblemStatement(
            $problem['problem']->alias,
            $r['lang']
        );

        // Add the problem distributable settings.
        $problemSettingsDistrib = null;
        Cache::getFromCacheOrSet(
            Cache::PROBLEM_SETTINGS_DISTRIB,
            $problem['problem']->alias,
            $problem['problem'],
            'ProblemController::getProblemSettingsDistrib',
            $problemSettingsDistrib,
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );
        $response['settings'] = json_decode(
            $problemSettingsDistrib,
            JSON_OBJECT_AS_ARRAY
        );

        // Add preferred language of the user.
        $user_data = [];
        $request = new Request(['omit_rank' => true, 'auth_token' => $r['auth_token']]);
        if (!is_null($r['current_user'])) {
            Cache::getFromCacheOrSet(
                Cache::USER_PROFILE,
                $r['current_user']->username,
                $request,
                function (Request $request) {
                        return UserController::apiProfile($request);
                },
                $user_data
            );
        }
        if (!empty($user_data)) {
            $response['preferred_language'] = $user_data['userinfo']['preferred_language'];
        }

        // Add the problem the response
        $response = array_merge($response, $problem['problem']->asFilteredArray($relevant_columns));

        // If the problem is public or if the user has admin privileges, show the
        // problem source and alias of owner.
        if (ProblemsDAO::isVisible($problem['problem']) ||
            Authorization::isProblemAdmin($r['current_identity_id'], $problem['problem'])) {
            $acl = ACLsDAO::getByPK($problem['problem']->acl_id);
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

        $problemset_id = !is_null($problem['problemset']) ? $problem['problemset']->problemset_id : null;

        if (!is_null($r['current_user_id'])) {
            // Create array of relevant columns for list of runs
            $relevant_columns = ['guid', 'language', 'status', 'verdict',
                'runtime', 'penalty', 'memory', 'score', 'contest_score', 'time',
                'submit_delay'];

            // Search the relevant runs from the DB

            // Get all the available runs done by the current_user
            try {
                $runsArray = RunsDAO::getByKeys(
                    $problem['problem']->problem_id,
                    $problemset_id,
                    $r['current_identity_id']
                );
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            // Add each filtered run to an array
            $runs_filtered_array = [];
            foreach ($runsArray as $run) {
                $filtered = $run->asFilteredArray($relevant_columns);
                $filtered['alias'] = $problem['problem']->alias;
                $filtered['username'] = $r['current_user']->username;
                $filtered['time'] = strtotime($filtered['time']);
                $filtered['contest_score'] = (float)$filtered['contest_score'];
                array_push($runs_filtered_array, $filtered);
            }

            $response['runs'] = $runs_filtered_array;
        }

        if (!is_null($problemset_id)) {
            // At this point, contestant_user relationship should be established.
            try {
                ProblemsetIdentitiesDAO::CheckAndSaveFirstTimeAccess(
                    $r['current_identity_id'],
                    $problemset_id,
                    Authorization::canSubmitToProblemset(
                        $r['current_identity_id'],
                        $problem['problemset']
                    )
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
                $problem['problem']->problem_id,
                $r['current_identity_id']
            )) {
                try {
                    // Save object in the DB
                    ProblemsetProblemOpenedDAO::save(new ProblemsetProblemOpened([
                        'problemset_id' => $problemset_id,
                        'problem_id' => $problem['problem']->problem_id,
                        'open_time' => gmdate('Y-m-d H:i:s', Time::get()),
                        'identity_id' => $r['current_identity_id']
                    ]));
                } catch (Exception $e) {
                    // Operation failed in the data layer
                    throw new InvalidDatabaseOperationException($e);
                }
            }
        } elseif (isset($r['show_solvers']) && $r['show_solvers']) {
            $response['solvers'] = RunsDAO::GetBestSolvingRunsForProblem($problem['problem']->problem_id);
        }

        if (!is_null($r['current_identity_id'])) {
            ProblemViewedDAO::MarkProblemViewed(
                $r['current_identity_id'],
                $problem['problem']->problem_id
            );
        }

        // send the supported languages as a JSON array instead of csv
        // array_filter is needed to handle when $response['languages'] is empty
        $response['languages'] = array_filter(explode(',', $response['languages']));

        $response['points'] = round(100.0 / (log(max($response['accepted'], 1.0) + 1, 2)), 2);
        $response['score'] = self::bestScore($problem['problem'], $r['problemset_id'], $r['contest_alias'], $r['current_identity_id']);
        $response['status'] = 'ok';
        $response['exists'] = true;
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
            if (!Authorization::isProblemAdmin($r['current_identity_id'], $r['problem'])) {
                throw new ForbiddenAccessException();
            }
            if (!is_null($r['username'])) {
                try {
                    $r['identity'] = IdentitiesDAO::FindByUsername($r['username']);
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
                    !is_null($r['identity']) ? $r['identity']->identity_id : null,
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
            // Get all the available runs
            try {
                $runs_array = RunsDAO::getByKeys($r['problem']->problem_id, null, $r['current_identity_id']);

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

        $is_problem_admin = Authorization::isProblemAdmin($r['current_identity_id'], $r['problem']);

        try {
            $clarifications = ClarificationsDAO::GetProblemClarifications(
                $r['problem']->problem_id,
                $is_problem_admin,
                $r['current_identity_id'],
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
        if (!Authorization::isProblemAdmin($r['current_identity_id'], $r['problem'])) {
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
            $casesStats = $problemStatsCache->get();
            if (is_null($casesStats)) {
                // Initialize the array at counts = 0
                $casesStats = [];
                $casesStats['counts'] = [];

                // We need to save the last_id that we processed, so next time we do not repeat this
                $casesStats['last_id'] = 0;
            }

            // Get all runs of this problem after the last id we had
            $runs = RunsDAO::searchRunIdGreaterThan(new Runs([
                'problem_id' => $r['problem']->problem_id
            ]), $casesStats['last_id'], 'run_id');

            // For each run we got
            foreach ($runs as $run) {
                // Build grade dir
                $grade_dir = RunController::getGradePath($run->guid);

                // Skip it if it failed to compile.
                if ($run->verdict == 'CE') {
                    continue;
                }

                // Try to open the details file.
                if (file_exists("$grade_dir/details.json")) {
                    $details = json_decode(file_get_contents("$grade_dir/details.json"));
                    foreach ($details as $group) {
                        if (!isset($group->cases) || !is_array($group->cases)) {
                            continue;
                        }
                        foreach ($group->cases as $case) {
                            if (!array_key_exists($case->name, $casesStats['counts'])) {
                                $casesStats['counts'][$case->name] = 0;
                            }
                            if ($case->score == 0) {
                                continue;
                            }
                            $casesStats['counts'][$case->name]++;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        // Save the last id we saw in case we saw something
        if (!is_null($runs) && count($runs) > 0) {
            $casesStats['last_id'] = $runs[count($runs) - 1]->run_id;
        }

        // Save in cache what we got
        $problemStatsCache->set($casesStats, APC_USER_CACHE_PROBLEM_STATS_TIMEOUT);

        return [
            'total_runs' => $totalRunsCount,
            'pending_runs' => $pendingRunsGuids,
            'verdict_counts' => $verdict_counts,
            'cases_stats' => $casesStats['counts'],
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

        // Filter results
        $language = null; // Filter by language, all by default.
        $valid_languages = ['en', 'es', 'pt'];
        // "language" may be one of the allowed options, otherwise the default filter will be used.
        if (!is_null($r['language']) && in_array($r['language'], $valid_languages)) {
            $language = $r['language'];
        }

        // Sort results
        $order = 'problem_id'; // Order by problem_id by default.
        $sorting_options = ['title', 'quality', 'difficulty', 'submissions', 'accepted', 'ratio', 'points', 'score', 'problem_id'];
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
        $author_identity_id = null;
        $author_user_id = null;
        // There are basically three types of users:
        // - Non-logged in users: Anonymous
        // - Logged in users with normal permissions: Normal
        // - Logged in users with administrative rights: Admin
        $identity_type = IDENTITY_ANONYMOUS;
        if (!is_null($r['current_identity_id'])) {
            $author_identity_id = intval($r['current_identity_id']);
            $author_user_id = intval($r['current_user_id']);
            if (Authorization::isSystemAdmin($r['current_identity_id']) ||
                Authorization::hasRole(
                    $r['current_identity_id'],
                    Authorization::SYSTEM_ACL,
                    Authorization::REVIEWER_ROLE
                )
            ) {
                $identity_type = IDENTITY_ADMIN;
            } else {
                $identity_type = IDENTITY_NORMAL;
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
        $response['results'] = ProblemsDAO::byIdentityType(
            $identity_type,
            $language,
            $order,
            $mode,
            $offset,
            $rowcount,
            $query,
            $author_identity_id,
            $author_user_id,
            $r['tag'],
            is_null($r['min_visibility']) ? ProblemController::VISIBILITY_PUBLIC : (int) $r['min_visibility'],
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
            if (Authorization::isSystemAdmin($r['current_identity_id'])) {
                $problems = ProblemsDAO::getAll(
                    $page,
                    $pageSize,
                    'problem_id',
                    'DESC'
                );
            } else {
                $problems = ProblemsDAO::getAllProblemsAdminedByIdentity(
                    $r['current_identity_id'],
                    $page,
                    $pageSize
                );
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $addedProblems = [];

        $hiddenTags = UsersDao::getHideTags($r['current_identity_id']);
        foreach ($problems as $problem) {
            $problemArray = $problem->asArray();
            $problemArray['tags'] = $hiddenTags ? [] : ProblemsDAO::getTagsForProblem($problem, false);
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

        $hiddenTags = UsersDao::getHideTags($r['current_identity_id']);
        foreach ($problems as $problem) {
            $problemArray = $problem->asArray();
            $problemArray['tags'] = $hiddenTags ? [] : ProblemsDAO::getTagsForProblem($problem, false);
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
        $problem = self::validateDetails($r);

        // If username is set in the request, we use that identity as target.
        // else, we query using current_user
        $identity = self::resolveTargetIdentity($r);

        $response['score'] = self::bestScore(
            $problem['problem'],
            $r['problemset_id'],
            $r['contest_alias'],
            $r['current_identity_id'],
            $identity
        );
        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Returns the best score of a problem.
     * If problemset is set, will only look for
     * runs inside the contest.
     *
     * Authentication is expected to be performed earlier.
     *
     * @param Problems $problem
     * @param $problemsetId
     * @param $contestAlias
     * @param $currentLoggedIdentityId
     * @param Identities $identity
     * @return float
     * @throws InvalidDatabaseOperationException
     */
    private static function bestScore(
        Problems $problem,
        $problemsetId,
        $contestAlias,
        $currentLoggedIdentityId,
        Identities $identity = null
    ) {
        $currentIdentityId = (is_null($identity) ? $currentLoggedIdentityId : $identity->identity_id);

        if (is_null($currentIdentityId)) {
            return 0;
        }

        $score = 0;
        try {
            // Add best score info
            $problemset = self::validateProblemset($problem, $problemsetId, $contestAlias);
            if (is_null($problemset['problemset'])) {
                $score = RunsDAO::GetBestScore($problem->problem_id, $currentIdentityId);
            } else {
                $bestRun = RunsDAO::GetBestRun(
                    $problemset['problemset']->problemset_id,
                    $problem->problem_id,
                    $currentIdentityId,
                    false /*showAllRuns*/
                );
                $score = is_null($bestRun->contest_score) ? 0 : $bestRun->contest_score;
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return $score;
    }

    /**
     * Save language data for a problem.
     * @param Request $r
     * @return Array
     * @throws InvalidDatabaseOperationException
     */
    private static function updateLanguages(Problems $problem) {
        $problemArtifacts = new ProblemArtifacts($problem->alias);
        try {
            ProblemsLanguagesDAO::transBegin();

            // Removing existing data
            $deletedLanguages = ProblemsLanguagesDAO::deleteProblemLanguages(new ProblemsLanguages([
                'problem_id' => $problem->problem_id,
            ]));

            foreach (LanguagesDAO::getAll() as $lang) {
                if (!$problemArtifacts->exists("statements/{$lang->name}.markdown")) {
                    continue;
                }
                ProblemsLanguagesDAO::save(new ProblemsLanguages([
                    'problem_id' => $problem->problem_id,
                    'language_id' => $lang->language_id,
                ]));
            }
            ProblemsLanguagesDAO::transEnd();
        } catch (ApiException $e) {
            // Operation failed in something we know it could fail, rollback transaction
            ProblemsLanguagesDAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * Converts the Problem's settings into something that
     * omegaup-gitserver can use.
     *
     * @param Problems $problem the problem
     * @return Array an array that can be serialized into the JSON form of
     *               quark's common.ProblemSettings.
     */
    private static function getProblemSettings(Problems $problem) {
        $problemSettings = [
            'Limits' => [
                'ExtraWallTime' => (int)$problem->extra_wall_time . 'ms',
                'MemoryLimit' => (int)$problem->memory_limit,
                'OutputLimit' => (int)$problem->output_limit,
                'OverallWallTimeLimit' => (
                    (int)$problem->overall_wall_time_limit . 'ms'
                ),
                'TimeLimit' => (int)$problem->time_limit . 'ms',
            ],
            'Validator' => [
                'Name' => $problem->validator,
                'Tolerance' => (float)$problem->tolerance,
                'Limits' => [
                    'ExtraWallTime' => '0s',
                    'MemoryLimit' => 256 * 1024 * 1024,
                    'OutputLimit' => 10 * 1024,
                    'OverallWallTimeLimit' => '5s',
                    'TimeLimit' => (int)$problem->validator_time_limit . 'ms',
                ],
            ],
        ];

        return $problemSettings;
    }
}
