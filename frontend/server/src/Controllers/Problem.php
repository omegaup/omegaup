<?php

 namespace OmegaUp\Controllers;

/**
 * ProblemsController
 */
class Problem extends \OmegaUp\Controllers\Controller {
    // Constants for problem visibility.
    const VISIBILITY_DELETED = -10; // Problem that was logically deleted by its owner
    const VISIBILITY_PRIVATE_BANNED = -2; // Problem that was private before it was banned
    const VISIBILITY_PUBLIC_BANNED = -1; // Problem that was public before it was banned
    const VISIBILITY_PRIVATE = 0;
    const VISIBILITY_PUBLIC = 1;
    const VISIBILITY_PROMOTED = 2;

    // SOLUTION STATUS
    const SOLUTION_NOT_FOUND = 'not_found';
    const SOLUTION_UNLOCKED = 'unlocked';
    const SOLUTION_LOCKED = 'locked';

    const RESTRICTED_TAG_NAMES = ['karel', 'lenguaje', 'solo-salida', 'interactive'];
    const VALID_LANGUAGES = ['en', 'es', 'pt'];

    // Do not update the published branch.
    const UPDATE_PUBLISHED_NONE = 'none';
    // Update only non-problemset runs.
    const UPDATE_PUBLISHED_NON_PROBLEMSET = 'non-problemset';
    // Update non-problemset runs and running problemsets that are owned by the
    // author.
    const UPDATE_PUBLISHED_OWNED_PROBLEMSETS = 'owned-problemsets';
    // Update non-problemset runs and running problemsets that the author has
    // edit privileges.
    const UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS = 'editable-problemsets';

    // ISO 639-1 langs
    const ISO639_1 = ['ab', 'aa', 'af', 'ak', 'sq', 'am', 'ar', 'an', 'hy',
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

    /**
     * Validates a Create or Update Problem API request
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateCreateOrUpdate(\OmegaUp\Request $r, $is_update = false) {
        $is_required = true;
        // https://github.com/omegaup/omegaup/issues/739
        if ($r->user->username == 'omi') {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // In case of update, params are optional
        if ($is_update) {
            $is_required = false;

            // We need to check problem_alias
            \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');

            $r['problem'] = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
            if (is_null($r['problem'])) {
                throw new \OmegaUp\Exceptions\NotFoundException('Problem not found');
            }

            // We need to check that the user can actually edit the problem
            if (!\OmegaUp\Authorization::canEditProblem($r->identity, $r['problem'])) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            // Only reviewers can revert bans.
            if (($r['problem']->visibility == \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED ||
                  $r['problem']->visibility == \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE_BANNED)
                    && array_key_exists('visibility', $r)
                    && $r['problem']->visibility != $r['visibility']
                    && !\OmegaUp\Authorization::isQualityReviewer($r->identity)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('qualityNominationProblemHasBeenBanned', 'visibility');
            }

            if ($r['problem']->deprecated) {
                throw new \OmegaUp\Exceptions\PreconditionFailedException('problemDeprecated');
            }

            if (!is_null($r['visibility']) && $r['problem']->visibility != $r['visibility']) {
                if ($r['problem']->visibility == \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException('qualityNominationProblemHasBeenPromoted', 'visibility');
                } else {
                    \OmegaUp\Validators::validateInEnum(
                        $r['visibility'],
                        'visibility',
                        [
                            \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE,
                            \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC,
                            \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC_BANNED,
                            \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE_BANNED
                        ]
                    );
                }
            }
            \OmegaUp\Validators::validateInEnum(
                $r['update_published'],
                'update_published',
                [
                    \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NONE,
                    \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NON_PROBLEMSET,
                    \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_OWNED_PROBLEMSETS,
                    \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
                ],
                false
            );
        } else {
            \OmegaUp\Validators::validateValidAlias($r['problem_alias'], 'problem_alias');
            \OmegaUp\Validators::validateInEnum(
                $r['visibility'],
                'visibility',
                [\OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE, \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC]
            );
            $r['selected_tags'] = json_decode($r['selected_tags']);
            if (!empty($r['selected_tags'])) {
                foreach ($r['selected_tags'] as $tag) {
                    if (empty($tag->tagname)) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException('parameterEmpty', 'tagname');
                    }
                }
            }
        }

        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['title'], 'title', $is_required);
        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['source'], 'source', $is_required);
        \OmegaUp\Validators::validateInEnum(
            $r['validator'],
            'validator',
            ['token', 'token-caseless', 'token-numeric', 'custom', 'literal'],
            $is_required
        );
        $r->ensureInt('time_limit', 0, null, $is_required);
        $r->ensureInt('validator_time_limit', 0, null, $is_required);
        $r->ensureInt('overall_wall_time_limit', 0, 60000, $is_required);
        $r->ensureInt('extra_wall_time', 0, 5000, $is_required);
        $r->ensureInt('memory_limit', 0, null, $is_required);
        $r->ensureInt('output_limit', 0, null, $is_required);
        $r->ensureInt('input_limit', 0, null, $is_required);

        // HACK! I don't know why "languages" doesn't make it into $r, and I've spent far too much time
        // on it already, so I'll just leave this here for now...
        if (!isset($r['languages']) && isset($_REQUEST['languages'])) {
            $r['languages'] = implode(',', $_REQUEST['languages']);
        } elseif (isset($r['languages']) && is_array($r['languages'])) {
            $r['languages'] = implode(',', $r['languages']);
        }
        \OmegaUp\Validators::validateValidSubset(
            $r['languages'],
            'languages',
            array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES),
            $is_required
        );
    }

    /**
     * Create a new problem
     *
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();

        // Validates request
        self::validateCreateOrUpdate($r);

        // Populate a new Problem object
        $problem = new \OmegaUp\DAO\VO\Problems([
            'visibility' => $r['visibility'], /* private by default */
            'title' => $r['title'],
            'visits' => 0,
            'input_limit' => $r['input_limit'],
            'submissions' => 0,
            'accepted' => 0,
            'difficulty' => 0,
            'source' => $r['source'],
            'order' => 'normal', /* defaulting to normal */
            'alias' => $r['problem_alias'],
            'languages' => $r['languages'],
            'email_clarifications' => $r['email_clarifications'],
        ]);

        $problemSettings = self::getDefaultProblemSettings();
        self::updateProblemSettings($problemSettings, $r);
        $acceptsSubmissions = $r['languages'] !== '';

        $acl = new \OmegaUp\DAO\VO\ACLs();
        $acl->owner_id = $r->user->user_id;

        // Insert new problem
        try {
            \OmegaUp\DAO\DAO::transBegin();

            // Commit at the very end
            $problemDeployer = new \OmegaUp\ProblemDeployer(
                $r['problem_alias'],
                $acceptsSubmissions
            );
            $problemDeployer->commit(
                'Initial commit',
                $r->user,
                \OmegaUp\ProblemDeployer::CREATE,
                $problemSettings
            );
            $problem->commit = $problemDeployer->publishedCommit;
            $problem->current_version = $problemDeployer->privateTreeHash;

            // Save the contest object with data sent by user to the database
            \OmegaUp\DAO\ACLs::create($acl);
            $problem->acl_id = $acl->acl_id;
            \OmegaUp\DAO\Problems::create($problem);

            // Add tags
            if (!empty($r['selected_tags'])) {
                foreach ($r['selected_tags'] as $tag) {
                    $tagName = \OmegaUp\Controllers\Tag::normalize($tag->tagname);
                    if (in_array($tagName, self::RESTRICTED_TAG_NAMES)) {
                        continue;
                    }
                    self::addTag($tagName, $tag->public, $problem);
                }
            }
            \OmegaUp\Controllers\Problem::setRestrictedTags($problem);
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            // Operation failed in something we know it could fail, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();

            throw $e;
        } catch (\Exception $e) {
            self::$log->error("Failed to upload problem {$problem->alias}", $e);

            // Operation failed unexpectedly, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();

            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('problemTitleExists', $e);
            }
            throw $e;
        }

        self::updateLanguages($problem);

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Validates a Rejudge Problem API request
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateRejudge(\OmegaUp\Request $r) {
        // We need to check problem_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');

        $r['problem'] = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($r['problem'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if ($r['problem']->deprecated) {
            throw new \OmegaUp\Exceptions\PreconditionFailedException('problemDeprecated');
        }

        // We need to check that the user actually has admin privileges over
        // the problem.
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $r['problem'])) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
    }

    /**
     * Adds an admin to a problem
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiAddAdmin(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        $r->ensureIdentity();

        // Check problem_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');

        $user = \OmegaUp\Controllers\User::resolveUser($r['usernameOrEmail']);

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Only an admin can add other problem admins
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::addUser($problem->acl_id, $user->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Adds a group admin to a problem
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiAddGroupAdmin(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        $r->ensureIdentity();

        // Check problem_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);

        if ($group == null) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('invalidParameters');
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Only an admin can add other problem group admins
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::addGroup($problem->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Adds a tag to a problem
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiAddTag(\OmegaUp\Request $r) {
        // Check problem_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');
        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name');

        // Authenticate logged user
        $r->ensureIdentity();

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        self::addTag($r['name'], $r['public'] || false, $problem);

        return ['status' => 'ok', 'name' => $r['name']];
    }

    private static function addTag(
        string $tagName,
        bool $isPublic,
        \OmegaUp\DAO\VO\Problems $problem,
        bool $allowRestricted = false
    ) : void {
        // Normalize name.
        $tagName = \OmegaUp\Controllers\Tag::normalize($tagName);

        if (!$allowRestricted && in_array($tagName, self::RESTRICTED_TAG_NAMES)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('tagRestricted', 'name');
        }

        $tag = \OmegaUp\DAO\Tags::getByName($tagName);
        if (is_null($tag)) {
            $tag = new \OmegaUp\DAO\VO\Tags([
                'name' => $tagName,
            ]);
            \OmegaUp\DAO\Tags::create($tag);
        }

        \OmegaUp\DAO\ProblemsTags::create(new \OmegaUp\DAO\VO\ProblemsTags([
            'problem_id' => $problem->problem_id,
            'tag_id' => $tag->tag_id,
            'public' => filter_var($isPublic, FILTER_VALIDATE_BOOLEAN),
            'autogenerated' => 0,
        ]));
    }

    /**
     * Removes an admin from a problem
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiRemoveAdmin(\OmegaUp\Request $r) {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check problem_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($r['usernameOrEmail']);

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Only admin is alowed to make modifications
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Check if admin to delete is actually an admin
        if (!\OmegaUp\Authorization::isProblemAdmin($identity, $problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        \OmegaUp\Controllers\ACL::removeUser($problem->acl_id, $identity->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Removes a group admin from a problem
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiRemoveGroupAdmin(\OmegaUp\Request $r) {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check problem_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);

        if ($group == null) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('invalidParameters');
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Only admin is alowed to make modifications
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::removeGroup($problem->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Removes a tag from a contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiRemoveTag(\OmegaUp\Request $r) {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check whether problem exists
        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');
        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name');

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problem');
        }

        $tag = \OmegaUp\DAO\Tags::getByName($r['name']);
        if (is_null($tag)) {
            throw new \OmegaUp\Exceptions\NotFoundException('tag');
        }

        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if (in_array($tag->name, self::RESTRICTED_TAG_NAMES)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('tagRestricted', 'name');
        }

        \OmegaUp\DAO\ProblemsTags::delete(new \OmegaUp\DAO\VO\ProblemsTags([
            'problem_id' => $problem->problem_id,
            'tag_id' => $tag->tag_id,
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Removes a problem whether user is the creator
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiDelete(\OmegaUp\Request $r) {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check whether problem exists
        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if (\OmegaUp\DAO\Problems::hasBeenUsedInCoursesOrContests($problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('problemHasBeenUsedInContestOrCourse');
        }

        \OmegaUp\DAO\Problems::deleteProblem($problem->problem_id);

        return ['status' => 'ok'];
    }

    /**
     * Returns all problem administrators
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiAdmins(\OmegaUp\Request $r) {
        // Authenticate request
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'status' => 'ok',
            'admins' => \OmegaUp\DAO\UserRoles::getProblemAdmins($problem),
            'group_admins' => \OmegaUp\DAO\GroupRoles::getProblemAdmins($problem)
        ];
    }

    /**
     * Returns every tag associated to a given problem.
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiTags(\OmegaUp\Request $r) {
        // Authenticate request
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');
        $includeAutogenerated = ($r['include_autogenerated'] == 'true');
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $response = [];
        $response['tags'] = \OmegaUp\DAO\ProblemsTags::getProblemTags(
            $problem,
            !\OmegaUp\Authorization::canEditProblem($r->identity, $problem),
            $includeAutogenerated
        );

        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Rejudge problem
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\ApiException
     */
    public static function apiRejudge(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        self::validateRejudge($r);

        // Call Grader
        $runs = [];
        try {
            \OmegaUp\DAO\DAO::transBegin();
            $runs = \OmegaUp\DAO\Runs::getByProblem((int)$r['problem']->problem_id);

            foreach ($runs as $run) {
                $run->status = 'new';
                $run->version = $r['problem']->current_version;
                $run->verdict = 'JE';
                $run->score = 0;
                $run->contest_score = 0;
                \OmegaUp\DAO\Runs::update($run);

                // Expire details of the run
                \OmegaUp\Controllers\Run::invalidateCacheOnRejudge($run);
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }
        \OmegaUp\Grader::getInstance()->rejudge($runs, false);

        $response = [];

        // All clear
        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Update problem contents
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\ApiException
     */
    public static function apiUpdate(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        self::validateCreateOrUpdate($r, true /* is update */);

        // Validate commit message.
        \OmegaUp\Validators::validateStringNonEmpty($r['message'], 'message');

        // Update the Problem object
        $valueProperties = [
            'visibility',
            'title',
            'input_limit',
            'email_clarifications',
            'source',
            'order',
            'languages',
        ];
        $problem = $r['problem'];
        self::updateValueProperties($r, $problem, $valueProperties);

        $response = [
            'rejudged' => false
        ];

        $problemSettings = self::getProblemSettingsDistrib($problem, $problem->commit);
        unset($problemSettings['cases']);
        unset($problemSettings['slow']);
        self::updateProblemSettings($problemSettings, $r);
        $acceptsSubmissions = $problem->languages !== '';
        $updatePublished = \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS;
        if (!is_null($r['update_published'])) {
            $updatePublished = $r['update_published'];
        }
        $updatedStatementLanguages = [];

        try {
            //Begin transaction
            \OmegaUp\DAO\DAO::transBegin();

            $operation = \OmegaUp\ProblemDeployer::UPDATE_SETTINGS;
            if (isset($_FILES['problem_contents'])
                && \OmegaUp\FileHandler::getFileUploader()->isUploadedFile($_FILES['problem_contents']['tmp_name'])
            ) {
                $operation = \OmegaUp\ProblemDeployer::UPDATE_CASES;
            }
            $problemDeployer = new \OmegaUp\ProblemDeployer(
                $problem->alias,
                $acceptsSubmissions,
                $updatePublished != \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NONE
            );
            $problemDeployer->commit(
                $r['message'],
                $r->user,
                $operation,
                $problemSettings
            );

            $response['rejudged'] = false;
            $needsUpdate = false;
            if (!is_null($problemDeployer->publishedCommit)) {
                $oldCommit = $problem->commit;
                $oldVersion = $problem->current_version;
                [$problem->commit, $problem->current_version] = \OmegaUp\Controllers\Problem::resolveCommit(
                    $problem,
                    $problemDeployer->publishedCommit
                );
                $response['rejudged'] = ($oldVersion != $problem->current_version);
                $needsUpdate = $response['rejudged'] || ($oldCommit != $problem->commit);
            }

            if ($needsUpdate) {
                \OmegaUp\DAO\Runs::createRunsForVersion($problem);
                \OmegaUp\DAO\Runs::updateVersionToCurrent($problem);
                if ($updatePublished != \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NON_PROBLEMSET) {
                    \OmegaUp\DAO\ProblemsetProblems::updateVersionToCurrent(
                        $problem,
                        $r->user,
                        $updatePublished
                    );
                }
                $updatedStatementLanguages = $problemDeployer->getUpdatedLanguages();
            }

            // Save the contest object with data sent by user to the database
            \OmegaUp\DAO\Problems::update($problem);

            \OmegaUp\Controllers\Problem::setRestrictedTags($problem);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();

            throw $e;
        } catch (\Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();
            self::$log->error('Failed to update problem');
            self::$log->error($e);

            throw $e;
        }

        if ($response['rejudged'] && OMEGAUP_ENABLE_REJUDGE_ON_PROBLEM_UPDATE) {
            self::$log->info('Calling \OmegaUp\Controllers\Problem::apiRejudge');
            try {
                $runs = \OmegaUp\DAO\Runs::getNewRunsForVersion($problem);
                \OmegaUp\Grader::getInstance()->rejudge($runs, false);

                // Expire details of the runs
                foreach ($runs as $run) {
                    \OmegaUp\Cache::deleteFromCache(\OmegaUp\Cache::RUN_ADMIN_DETAILS, $run->run_id);
                }
                \OmegaUp\Cache::deleteFromCache(\OmegaUp\Cache::PROBLEM_STATS, $problem->alias);
            } catch (\Exception $e) {
                self::$log->error('Best effort \OmegaUp\Controllers\Problem::apiRejudge failed', $e);
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

    private static function setRestrictedTags(\OmegaUp\DAO\VO\Problems $problem) {
        \OmegaUp\DAO\ProblemsTags::clearRestrictedTags($problem);
        $languages = explode(',', $problem->languages);
        if (in_array('cat', $languages)) {
            \OmegaUp\Controllers\Problem::addTag('solo-salida', true, $problem, true);
        } elseif (!empty(array_intersect(['kp', 'kj'], $languages))) {
            \OmegaUp\Controllers\Problem::addTag('karel', true, $problem, true);
        } else {
            \OmegaUp\Controllers\Problem::addTag('lenguaje', true, $problem, true);
        }

        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);
        $distribSettings = json_decode(
            $problemArtifacts->get('settings.distrib.json'),
            JSON_OBJECT_AS_ARRAY
        );
        if (!empty($distribSettings['interactive'])) {
            \OmegaUp\Controllers\Problem::addTag('interactive', true, $problem, true);
        }
    }

    /**
     * Updates loose file
     *
     * @param \OmegaUp\Request $r
     * @return array The updated file languages
     * @throws \OmegaUp\Exceptions\ApiException
     */
    private static function updateLooseFile(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Problems $problem,
        string $directory,
        string $contents
    ): array {
        \OmegaUp\Validators::validateStringNonEmpty($r['message'], 'message');
        // Check that lang is in the ISO 639-1 code list, default is "es".
        \OmegaUp\Validators::validateInEnum($r['lang'], 'lang', \OmegaUp\Controllers\Problem::ISO639_1, false /* is_required */);
        if (is_null($r['lang'])) {
            $r['lang'] = \OmegaUp\Controllers\Identity::getPreferredLanguage($r);
        }
        $updatePublished = \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS;
        if (!is_null($r['update_published'])) {
            $updatePublished = $r['update_published'];
        }

        $updatedFileLanguages = [];
        try {
            $problemDeployer = new \OmegaUp\ProblemDeployer($r['problem_alias']);
            $problemDeployer->commitLooseFiles(
                "{$r['lang']}.markdown: {$r['message']}",
                $r->user,
                [
                    "{$directory}/{$r['lang']}.markdown" => $contents,
                ]
            );
            if ($updatePublished != \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NONE) {
                [$problem->commit, $problem->current_version] = \OmegaUp\Controllers\Problem::resolveCommit(
                    $problem,
                    $problemDeployer->publishedCommit
                );
                if ($updatePublished != \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NON_PROBLEMSET) {
                    \OmegaUp\DAO\ProblemsetProblems::updateVersionToCurrent(
                        $problem,
                        $r->user,
                        $updatePublished
                    );
                }
                \OmegaUp\DAO\Problems::update($problem);
            }
            $updatedFileLanguages = $problemDeployer->getUpdatedLanguages();
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            throw $e;
        }

        return $updatedFileLanguages;
    }

    /**
     * Updates problem statement only
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ApiException
     */
    public static function apiUpdateStatement(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        self::validateCreateOrUpdate($r, true);
        \OmegaUp\Validators::validateStringNonEmpty($r['statement'], 'statement');
        $updatedFileLanguages = self::updateLooseFile($r, $r['problem'], 'statements', $r['statement']);
        self::invalidateCache($r['problem'], $updatedFileLanguages);
        return [
            'status' => 'ok'
        ];
    }

    /**
     * Updates problem solution only
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ApiException
     */
    public static function apiUpdateSolution(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        self::validateCreateOrUpdate($r, true);
        \OmegaUp\Validators::validateStringNonEmpty($r['solution'], 'solution');
        $updatedFileLanguages = self::updateLooseFile($r, $r['problem'], 'solutions', $r['solution']);
        self::invalidateSolutionCache($r['problem'], $updatedFileLanguages);
        return [
            'status' => 'ok'
        ];
    }

    /**
     * Invalidates the various caches of the problem, as well as updating the
     * languages.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem the problem
     * @param array $updatedLanguages the array of updated statement file languages.
     *
     * @return void
     */
    private static function invalidateCache(
        \OmegaUp\DAO\VO\Problems $problem,
        array $updatedLanguages
    ): void {
        self::updateLanguages($problem);

        // Invalidate problem statement or solution cache
        foreach ($updatedLanguages as $lang) {
            \OmegaUp\Cache::deleteFromCache(
                \OmegaUp\Cache::PROBLEM_STATEMENT,
                "{$problem->alias}-{$problem->commit}-{$lang}-markdown"
            );
        }
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::PROBLEM_SETTINGS_DISTRIB,
            "{$problem->alias}-{$problem->commit}"
        );
    }

    /**
     * Invalidates the problem solution cache
     *
     * @param \OmegaUp\DAO\VO\Problems $problem the problem
     * @param array $updatedLanguages the array of updated loose file languages.
     *
     * @return void
     */
    private static function invalidateSolutionCache(
        \OmegaUp\DAO\VO\Problems $problem,
        array $updatedLanguages
    ): void {
        // Invalidate problem solution cache
        foreach ($updatedLanguages as $lang) {
            \OmegaUp\Cache::deleteFromCache(
                \OmegaUp\Cache::PROBLEM_SOLUTION,
                "{$problem->alias}-{$problem->commit}-{$lang}-markdown"
            );
        }
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::PROBLEM_SOLUTION_EXISTS,
            "{$problem->alias}-{$problem->commit}"
        );
    }

    /**
     * Validate problem Details API
     *
     * @param \OmegaUp\Request $r
     * @return Array
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateDetails(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['contest_alias'], 'contest_alias');
        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');

        // Lang is optional. Default is user's preferred.
        if (!is_null($r['lang'])) {
            \OmegaUp\Validators::validateStringOfLengthInRange($r['lang'], 'lang', 2, 2);
        } else {
            $r['lang'] = \OmegaUp\Controllers\Identity::getPreferredLanguage($r);
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            return [
                'status' => 'ok',
                'exists' => false,
                'problem' => null,
                'problemset' => null,
            ];
        }

        if (isset($r['statement_type']) && $r['statement_type'] != 'markdown') {
            throw new \OmegaUp\Exceptions\NotFoundException('invalidStatementType');
        }

        // If we request a problem inside a contest
        $problemset = self::validateProblemset($problem, $r['problemset_id'], $r['contest_alias']);
        if (!is_null($problemset) && isset($problemset['problemset'])) {
            if (!\OmegaUp\Authorization::isAdmin($r->identity, $problemset['problemset'])) {
                // If the contest is private, verify that our user is invited
                if (!empty($problemset['contest'])) {
                    if (!\OmegaUp\Controllers\Contest::isPublic($problemset['contest']->admission_mode)) {
                        if (is_null(\OmegaUp\DAO\ProblemsetIdentities::getByPK(
                            $r->identity->identity_id,
                            $problemset['problemset']->problemset_id
                        ))) {
                            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
                        }
                    }
                    // If the contest has not started, non-admin users should not see it
                    if (!\OmegaUp\DAO\Contests::hasStarted($problemset['contest'])) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException('contestNotStarted');
                    }
                } else {    // Not a contest, but we still have a problemset
                    if (!\OmegaUp\Authorization::canSubmitToProblemset(
                        $r->identity,
                        $problemset['problemset']
                    )) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException();
                    }
                    // TODO: Check start times.
                }
            }
        } else {
            if (is_null($r->identity)
                || !\OmegaUp\Authorization::canEditProblem($r->identity, $problem)
            ) {
                // If the problem is requested outside a contest, we need to
                // check that it is not private
                if (!\OmegaUp\DAO\Problems::isVisible($problem)) {
                    throw new \OmegaUp\Exceptions\ForbiddenAccessException('problemIsPrivate');
                }
            }
        }
        return [
            'exists' => true,
            'problem' => $problem,
            'problemset' => $problemset['problemset'],
        ];
    }

    /**
     * Gets the problem resource (statement/solution) from the gitserver.
     *
     * @param array $params The problem, commit, and language for the problem
     *                      statement.
     *
     * @return array The contents of the resource, plus some metadata.
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     */
    public static function getProblemResourceImpl(array $params) : array {
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($params['alias'], $params['commit']);
        $sourcePath = "{$params['directory']}/{$params['language']}.markdown";

        // Read the file that contains the source
        if (!$problemArtifacts->exists($sourcePath)) {
            // If there is no language file for the problem, return the Spanish
            // version.
            $params['language'] = 'es';
            $sourcePath = "{$params['directory']}/{$params['language']}.markdown";
        }

        $result = [
            'language' => $params['language'],
            'images' => [],
        ];
        try {
            $result['markdown'] = mb_convert_encoding($problemArtifacts->get($sourcePath), 'utf-8');
        } catch (\Exception $e) {
            throw new \OmegaUp\Exceptions\InvalidFilesystemOperationException('statementNotFound');
        }

        // Get all the images' mappings.
        $statementFiles = $problemArtifacts->lsTree($params['directory']);
        $imageExtensions = ['bmp', 'gif', 'ico', 'jpe', 'jpeg', 'jpg', 'png',
                            'svg', 'svgz', 'tif', 'tiff'];
        foreach ($statementFiles as $file) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (!in_array($extension, $imageExtensions)) {
                continue;
            }
            $result['images'][$file['name']] = (
                IMAGES_URL_PATH . "{$params['alias']}/{$file['id']}.{$extension}"
            );
            $imagePath = (
                IMAGES_PATH . "{$params['alias']}/{$file['id']}.{$extension}"
            );
            if (!@file_exists($imagePath)) {
                @mkdir(IMAGES_PATH . $params['alias'], 0755, true);
                file_put_contents($imagePath, $problemArtifacts->get(
                    "{$params['directory']}/{$file['name']}"
                ));
            }
        }

        return $result;
    }

    /**
     * Gets the problem statement from the gitserver.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem  The problem.
     * @param string   $commit   The git commit at which to get the statement.
     * @param string   $language The language of the problem. Will default to
     *                           Spanish if not found.
     *
     * @return array The contents of the file.
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     */
    public static function getProblemStatement(
        \OmegaUp\DAO\VO\Problems $problem,
        string $commit,
        string $language
    ) : array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_STATEMENT,
            "{$problem->alias}-{$commit}-{$language}-markdown",
            function () use ($problem, $commit, $language) {
                return \OmegaUp\Controllers\Problem::getProblemResourceImpl([
                    'directory' => 'statements',
                    'alias' => $problem->alias,
                    'commit' => $commit,
                    'language' => $language,
                ]);
            },
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );
    }

    /**
     * Gets the problem solution from the gitserver.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem  The problem.
     * @param string   $commit   The git commit at which to get the solution.
     * @param string   $language The language of the solution. Will default to
     *                           Spanish if not found.
     *
     * @return array The contents of the file.
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     */
    public static function getProblemSolution(
        \OmegaUp\DAO\VO\Problems $problem,
        string $commit,
        string $language
    ) : array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_SOLUTION,
            "{$problem->alias}-{$commit}-{$language}-markdown",
            function () use ($problem, $commit, $language) {
                return \OmegaUp\Controllers\Problem::getProblemResourceImpl([
                    'directory' => 'solutions',
                    'alias' => $problem->alias,
                    'commit' => $commit,
                    'language' => $language,
                ]);
            },
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );
    }

    /**
     * Gets the distributable problem settings for the problem, using the cache
     * if needed.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem the problem.
     * @return array the problem settings.
     */
    private static function getProblemSettingsDistrib(
        \OmegaUp\DAO\VO\Problems $problem,
        string $commit
    ) : array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_SETTINGS_DISTRIB,
            "{$problem->alias}-{$problem->commit}",
            function () use ($problem) {
                return \OmegaUp\Controllers\Problem::getProblemSettingsDistribImpl([
                    'alias' => $problem->alias,
                    'commit' => $problem->commit,
                ]);
            },
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );
    }

    /**
     * Gets the distributable problem settings for the problem.
     *
     * @param array $params the payload with the problem and commit.
     * @return array the problem settings.
     */
    public static function getProblemSettingsDistribImpl(array $params) : array {
        return json_decode(
            (new \OmegaUp\ProblemArtifacts($params['alias'], $params['commit']))->get('settings.distrib.json'),
            JSON_OBJECT_AS_ARRAY
        );
    }

    /**
     * Entry point for Problem Download API
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     */
    public static function apiDownload(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        // Validate request
        $problem = self::validateDownload($r);

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/zip');
        header("Content-Disposition: attachment;filename={$problem->alias}.zip");
        header('Content-Transfer-Encoding: binary');
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);
        $problemArtifacts->download();

        die();
    }

    /**
     * Validate problem Details API
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateDownload(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return $problem;
    }

    /**
     * Validate problemset Details API
     *
     * @param \OmegaUp\DAO\VO\Problems $problem
     * @param $problemsetId
     * @param $contestAlias
     * @return Array
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateProblemset(\OmegaUp\DAO\VO\Problems $problem, $problemsetId, $contestAlias = null) {
        $problemNotFound = null;
        $response = [];
        if (!empty($contestAlias)) {
            // Is it a valid contest_alias?
            $response['contest'] = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
            if (is_null($response['contest'])) {
                throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
            }
            $response['problemset'] = \OmegaUp\DAO\Problemsets::getByPK($response['contest']->problemset_id);
            if (is_null($response['problemset'])) {
                throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
            }
            $problemNotFound = 'problemNotFoundInContest';
        } elseif (!is_null($problemsetId)) {
            // Is it a valid problemset_id?
            $response['problemset'] = \OmegaUp\DAO\Problemsets::getByPK($problemsetId);
            if (is_null($response['problemset'])) {
                throw new \OmegaUp\Exceptions\NotFoundException('problemsetNotFound');
            }
            $problemNotFound = 'problemNotFoundInProblemset';
        } else {
            // Nothing to see here, move along.
            return null;
        }

        // Is the problem actually in the problemset?
        if (is_null(\OmegaUp\DAO\ProblemsetProblems::getByPK(
            $response['problemset']->problemset_id,
            $problem->problem_id
        ))
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException($problemNotFound);
        }

        return $response;
    }

    /**
     * Entry point for Problem Details API
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     */
    public static function apiDetails(\OmegaUp\Request $r) : array {
        $r->ensureBool('show_solvers', /*required=*/false);
        $result = self::getValidProblemAndProblemset($r);
        [
            'exists' => $problemExisits,
            'problem' => $problem,
            'problemset' => $problemset,
        ] = $result;
        if (!$problemExisits) {
            return $result;
        }
        return self::getProblemDetails(
            $r,
            $problem,
            $problemset,
            $r['show_solvers'] === true
        );
    }

    /**
     * Get user. Allow unauthenticated requests if we are not opening a problem
     * inside a contest
     *
     * @param \OmegaUp\Request $r
     * @return \OmegaUp\DAO\VO\Problems
     * @throws \OmegaUp\Exceptions\UnauthorizedException
     */
    private static function getValidProblemAndProblemset(\OmegaUp\Request $r) : array {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            if (!is_null($r['contest_alias']) || !is_null($r['problemset_id'])) {
                throw $e;
            }
        }

        // Validate request and return the object
        return self::validateDetails($r);
    }

    /**
     * Get the extra problem details with all the validations
     * @param \OmegaUp\Request $r
     * @param \OmegaUp\DAO\VO\Problems $problem
     * @param bool $showSolvers
     * @return array
     */
    private static function getProblemDetails(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Problems $problem,
        ?\OmegaUp\DAO\VO\Problemsets $problemset,
        bool $showSolvers
    ) : array {
        $response = [];

        // Get the expected commit version.
        $commit = $problem->commit;
        $version = $problem->current_version;
        if (!empty($problemset)) {
            $problemsetProblem = \OmegaUp\DAO\ProblemsetProblems::getByPK(
                $problemset->problemset_id,
                $problem->problem_id
            );
            if (is_null($problemsetProblem)) {
                return [
                    'status' => 'ok',
                    'exists' => false,
                ];
            }
            $commit = $problemsetProblem->commit;
            $version = $problemsetProblem->version;
        }

        $response['statement'] = \OmegaUp\Controllers\Problem::getProblemStatement(
            $problem,
            $commit,
            $r['lang']
        );
        $response['settings'] = \OmegaUp\Controllers\Problem::getProblemSettingsDistrib(
            $problem,
            $commit
        );

        // Add preferred language of the user.
        $request = new \OmegaUp\Request(['omit_rank' => true, 'auth_token' => $r['auth_token']]);
        if (!is_null($r->identity)) {
            $userData = \OmegaUp\Cache::getFromCacheOrSet(
                \OmegaUp\Cache::USER_PROFILE,
                $r->identity->username,
                function () use ($r) {
                    return \OmegaUp\Controllers\User::apiProfile($r);
                }
            );
            if (!empty($userData)) {
                $response['preferred_language'] = $userData['userinfo']['preferred_language'];
            }
        }

        // Add the problem the response
        $response = array_merge($response, $problem->asFilteredArray([
            'title', 'alias', 'input_limit', 'visits', 'submissions',
            'accepted', 'difficulty', 'creation_date', 'source', 'order',
            'points', 'visibility', 'languages', 'email_clarifications',
        ]));
        $response['version'] = $version;
        $response['commit'] = $commit;

        // If the problem is public or if the user has admin privileges, show the
        // problem source and alias of owner.
        if (\OmegaUp\DAO\Problems::isVisible($problem) ||
            \OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            $acl = \OmegaUp\DAO\ACLs::getByPK($problem->acl_id);
            $problemsetter = \OmegaUp\DAO\Identities::findByUserId($acl->owner_id);
            $response['problemsetter'] = [
                'username' => $problemsetter->username,
                'name' => is_null($problemsetter->name) ?
                          $problemsetter->username :
                          $problemsetter->name,
                'creation_date' => \OmegaUp\DAO\DAO::fromMySQLTimestamp($response['creation_date']),
            ];
        } else {
            unset($response['source']);
        }

        $problemsetId = !is_null($problemset) ? (int)$problemset->problemset_id : null;

        if (!is_null($r->identity)) {
            // Get all the available runs done by the current_user
            $runsArray = \OmegaUp\DAO\Runs::getForProblemDetails(
                (int)$problem->problem_id,
                $problemsetId,
                (int)$r->identity->identity_id
            );

            // Add each filtered run to an array
            $response['runs'] = [];
            foreach ($runsArray as $run) {
                $run['alias'] = $problem->alias;
                $run['username'] = $r->identity->username;
                $run['time'] = (int)$run['time'];
                $run['contest_score'] = (float)$run['contest_score'];
                array_push($response['runs'], $run);
            }
        }

        if (!is_null($problemset)) {
            $result['admin'] = \OmegaUp\Authorization::isAdmin($r->identity, $problemset);
            if (!$result['admin'] || $r['prevent_problemset_open'] !== 'true') {
                // At this point, contestant_user relationship should be established.
                $container = \OmegaUp\DAO\Problemsets::getProblemsetContainer(
                    $problemset->problemset_id
                );
                \OmegaUp\DAO\ProblemsetIdentities::checkAndSaveFirstTimeAccess(
                    $r->identity,
                    $container,
                    \OmegaUp\Authorization::canSubmitToProblemset(
                        $r->identity,
                        $problemset
                    )
                );
            }

            // As last step, register the problem as opened
            if (!\OmegaUp\DAO\ProblemsetProblemOpened::getByPK(
                $problemsetId,
                $problem->problem_id,
                $r->identity->identity_id
            )) {
                \OmegaUp\DAO\ProblemsetProblemOpened::create(new \OmegaUp\DAO\VO\ProblemsetProblemOpened([
                    'problemset_id' => $problemset->problemset_id,
                    'problem_id' => $problem->problem_id,
                    'open_time' => \OmegaUp\Time::get(),
                    'identity_id' => $r->identity->identity_id
                ]));
            }
        } elseif ($showSolvers) {
            $response['solvers'] = \OmegaUp\DAO\Runs::getBestSolvingRunsForProblem(
                (int)$problem->problem_id
            );
        }

        if (!is_null($r->identity)) {
            \OmegaUp\DAO\ProblemViewed::MarkProblemViewed(
                $r->identity->identity_id,
                $problem->problem_id
            );
        }

        // send the supported languages as a JSON array instead of csv
        // array_filter is needed to handle when $response['languages'] is empty
        $response['languages'] = array_filter(explode(',', $response['languages']));

        $response['points'] = round(100.0 / (log(max($response['accepted'], 1.0) + 1, 2)), 2);
        if (is_null($r->identity)) {
            $response['score'] = 0.0;
        } else {
            $response['score'] = self::bestScore(
                $problem,
                $problemsetId,
                $r['contest_alias'],
                $r->identity->identity_id
            );
        }
        $response['status'] = 'ok';
        $response['exists'] = true;
        return $response;
    }

    /**
     * Returns the solution for a problem if conditions are satisfied.
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     */
    public static function apiSolution(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        // Validate request
        $problem = self::validateDetails($r);
        if (is_null($problem)) {
            return [
                'status' => 'ok',
                'exists' => false,
            ];
        }
        $problemset = $problem['problemset'];
        $problem = $problem['problem'];

        // Get the expected commit version.
        $commit = $problem->commit;
        $version = $problem->current_version;
        if (!empty($problemset)) {
            $problemsetProblem = \OmegaUp\DAO\ProblemsetProblems::getByPK(
                $problemset->problemset_id,
                $problem->problem_id
            );
            if (is_null($problemsetProblem)) {
                return [
                    'status' => 'ok',
                    'exists' => false,
                ];
            }
            $commit = $problemsetProblem->commit;
            $version = $problemsetProblem->version;
        }

        if (!\OmegaUp\Authorization::canViewProblemSolution($r->identity, $problem)) {
            $r->ensureBool('forfeit_problem', false /*isRequired*/);
            if ($r['forfeit_problem'] !== true) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException('problemSolutionNotVisible');
            }
            $seenSolutions = \OmegaUp\DAO\ProblemsForfeited::getProblemsForfeitedCount($r->user);
            $allowedSolutions = intval(\OmegaUp\DAO\Problems::getProblemsSolvedCount($r->identity) /
                                \OmegaUp\Controllers\ProblemForfeited::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION);
            // Validate that the user will not exceed the number of allowed solutions.
            if ($seenSolutions >= $allowedSolutions) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException('allowedSolutionsLimitReached');
            }
            \OmegaUp\DAO\ProblemsForfeited::create(new \OmegaUp\DAO\VO\ProblemsForfeited([
                'user_id' => $r->user->user_id,
                'problem_id' => $problem->problem_id
            ]));
        }

        return [
            'status' => 'ok',
            'exists' => true,
            'solution' => \OmegaUp\Controllers\Problem::getProblemSolution(
                $problem,
                $commit,
                $r['lang']
            ),
        ];
    }

    /**
     * Entry point for Problem Versions API
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function apiVersions(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateValidAlias($r['problem_alias'], 'problem_alias');

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            return [
                'status' => 'ok',
                'published' => $problem->commit,
                'log' => [
                    [
                        'commit' => $problem->commit,
                        'tree' => null,
                        'author' => [
                            'time' => \OmegaUp\DAO\DAO::fromMySQLTimestamp($problem->creation_date),
                        ],
                        'committer' => [
                            'time' => \OmegaUp\DAO\DAO::fromMySQLTimestamp($problem->creation_date),
                        ],
                        'version' => $problem->current_version,
                    ],
                ],
            ];
        }

        $privateTreeMapping = [];
        foreach ((new \OmegaUp\ProblemArtifacts($problem->alias, 'private'))->log() as $logEntry) {
            $privateTreeMapping[$logEntry['commit']] = $logEntry['tree'];
        }

        $masterLog = [];
        foreach ((new \OmegaUp\ProblemArtifacts($problem->alias, 'master'))->log() as $logEntry) {
            if (count($logEntry['parents']) < 3) {
                // Master commits always have 3 or 4 parents. If they have
                // fewer, it's one of the commits in the merged branches.
                continue;
            }
            $logEntry['version'] = $privateTreeMapping[$logEntry['parents'][count($logEntry['parents']) - 1]];
            $logEntry['tree'] = [];
            foreach ((new \OmegaUp\ProblemArtifacts($problem->alias, $logEntry['commit']))->lsTreeRecursive() as $treeEntry) {
                $logEntry['tree'][$treeEntry['path']] = $treeEntry['id'];
            }
            array_push($masterLog, $logEntry);
        }

        return [
            'status' => 'ok',
            'published' => (new \OmegaUp\ProblemArtifacts($problem->alias, 'published'))->commit()['commit'],
            'log' => $masterLog,
        ];
    }

    /**
     * Change the version of the problem.
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function apiSelectVersion(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateValidAlias($r['problem_alias'], 'problem_alias');
        \OmegaUp\Validators::validateStringOfLengthInRange($r['commit'], 'commit', 1, 40, false);
        // \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NONE is not allowed here because
        // it would not make any sense!
        \OmegaUp\Validators::validateInEnum(
            $r['update_published'],
            'update_published',
            [
                \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NON_PROBLEMSET,
                \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_OWNED_PROBLEMSETS,
                \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
            ],
            false
        );

        $updatePublished = \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS;
        if (!is_null($r['update_published'])) {
            $updatePublished = $r['update_published'];
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $oldVersion = $problem->current_version;
        $oldCommit = $problem->commit;

        [$problem->commit, $problem->current_version] = \OmegaUp\Controllers\Problem::resolveCommit(
            $problem,
            $r['commit']
        );

        if ($oldCommit == $problem->commit && $oldVersion == $problem->current_version) {
            return [
                'status' => 'ok',
            ];
        }

        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias, $problem->commit);

        // Update problem fields.
        $problemSettings = json_decode(
            $problemArtifacts->get('settings.json'),
            JSON_OBJECT_AS_ARRAY
        );

        $problemDeployer = new \OmegaUp\ProblemDeployer($problem->alias);
        try {
            // Begin transaction
            \OmegaUp\DAO\DAO::transBegin();
            $problemDeployer->updatePublished(
                ((new \OmegaUp\ProblemArtifacts($problem->alias, 'published'))->commit())['commit'],
                $problem->commit,
                $r->user
            );

            \OmegaUp\DAO\Runs::createRunsForVersion($problem);
            \OmegaUp\DAO\Runs::updateVersionToCurrent($problem);
            if ($updatePublished != \OmegaUp\Controllers\Problem::UPDATE_PUBLISHED_NON_PROBLEMSET) {
                \OmegaUp\DAO\ProblemsetProblems::updateVersionToCurrent(
                    $problem,
                    $r->user,
                    $updatePublished
                );
            }

            \OmegaUp\DAO\Problems::update($problem);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();
            self::$log->error('Failed to update problem: ', $e);

            throw $e;
        }

        if (OMEGAUP_ENABLE_REJUDGE_ON_PROBLEM_UPDATE) {
            self::$log->info('Calling \OmegaUp\Controllers\Problem::apiRejudge');
            try {
                $runs = \OmegaUp\DAO\Runs::getNewRunsForVersion($problem);
                \OmegaUp\Grader::getInstance()->rejudge($runs, false);

                // Expire details of the runs
                foreach ($runs as $run) {
                    \OmegaUp\Cache::deleteFromCache(\OmegaUp\Cache::RUN_ADMIN_DETAILS, $run->run_id);
                }
                \OmegaUp\Cache::deleteFromCache(\OmegaUp\Cache::PROBLEM_STATS, $problem->alias);
            } catch (\Exception $e) {
                self::$log->error('Best effort \OmegaUp\Controllers\Problem::apiRejudge failed', $e);
            }
        }
        $updatedStatementLanguages = [];
        foreach ($problemArtifacts->lsTree('statements') as $file) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            if ($extension != 'markdown') {
                continue;
            }
            $updatedStatementLanguages[] = pathinfo($file['name'], PATHINFO_FILENAME);
        }
        self::invalidateCache(
            $problem,
            array_merge($updatedStatementLanguages, \OmegaUp\Controllers\Problem::VALID_LANGUAGES)
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Return a report of which runs would change due to a version change.
     */
    public static function apiRunsDiff(\OmegaUp\Request $r) : array {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateValidAlias($r['problem_alias'], 'problem_alias');
        \OmegaUp\Validators::validateStringNonEmpty($r['version'], 'version');

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'status' => 'ok',
            'diff' => \OmegaUp\DAO\Runs::getRunsDiffsForVersion(
                $problem,
                null,
                $problem->current_version,
                $r['version']
            ),
        ];
    }

    /**
     * Resolve a commit from the problem's master branch.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem the problem.
     * @param ?string  $commit  the optional explicit commit hash.
     *
     * @return the SHA1 of a commit in the problem's master branch, plus
     *         the SHA1 of the private branch tree associated with that commit.
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function resolveCommit(
        \OmegaUp\DAO\VO\Problems $problem,
        ?string $commit
    ) : array {
        $masterCommit = null;
        if (is_null($commit)) {
            $masterCommit = (new \OmegaUp\ProblemArtifacts($problem->alias, 'published'))->commit();
        } else {
            foreach ((new \OmegaUp\ProblemArtifacts($problem->alias, 'master'))->log() as $logEntry) {
                if (count($logEntry['parents']) < 3) {
                    // Master commits always have 3 or 4 parents. If they have
                    // fewer, it's one of the commits in the merged branches.
                    continue;
                }
                if ($logEntry['commit'] == $commit) {
                    $masterCommit = $logEntry;
                    break;
                }
            }
        }

        if ($masterCommit == null) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemVersionNotFound');
        }

        // The private branch is always the last parent.
        $privateCommitHash = $masterCommit['parents'][count($masterCommit['parents']) - 1];
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias, $privateCommitHash);
        $privateCommit = $problemArtifacts->commit();

        // Update problem fields.
        return [$masterCommit['commit'], $privateCommit['tree']];
    }

    /**
     * Validate problem Details API
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateRuns(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');

        // Is the problem valid?
        $r['problem'] = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($r['problem'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
    }

    /**
     * Entry point for Problem runs API
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     */
    public static function apiRuns(\OmegaUp\Request $r) {
        // Get user
        $r->ensureIdentity();

        // Validate request
        self::validateRuns($r);

        $response = [];

        if ($r['show_all']) {
            if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $r['problem'])) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            if (!is_null($r['username'])) {
                try {
                    $r['identity'] = \OmegaUp\DAO\Identities::findByUsername($r['username']);
                } catch (\Exception $e) {
                    throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
                }
            }
            $runs = \OmegaUp\DAO\Runs::getAllRuns(
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
        } else {
            // Get all the available runs
            $runsArray = \OmegaUp\DAO\Runs::getForProblemDetails(
                (int)$r['problem']->problem_id,
                null,
                (int)$r->identity->identity_id
            );

            // Add each filtered run to an array
            $response['runs'] = [];
            if (!empty($runsArray)) {
                foreach ($runsArray as $run) {
                    $run['time'] = (int)$run['time'];
                    $run['contest_score'] = (float)$run['contest_score'];
                    $run['username'] = $r->identity->username;
                    $run['alias'] = $r['problem']->alias;
                    array_push($response['runs'], $run);
                }
            }
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Entry point for Problem clarifications API
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     */
    public static function apiClarifications(\OmegaUp\Request $r) {
        // Get user
        $r->ensureIdentity();
        self::validateRuns($r);

        $is_problem_admin = \OmegaUp\Authorization::isProblemAdmin(
            $r->identity,
            $r['problem']
        );

        $clarifications = \OmegaUp\DAO\Clarifications::GetProblemClarifications(
            $r['problem']->problem_id,
            $is_problem_admin,
            $r->identity->identity_id,
            $r['offset'],
            $r['rowcount']
        );

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
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiStats(\OmegaUp\Request $r) {
        // Get user
        $r->ensureIdentity();

        // Validate request
        self::validateRuns($r);

        // We need to check that the user has priviledges on the problem
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $r['problem'])) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Array of GUIDs of pending runs
        $pendingRunsGuids = \OmegaUp\DAO\Runs::getPendingRunsOfProblem(
            (int)$r['problem']->problem_id
        );

        // Count of pending runs (int)
        $totalRunsCount = \OmegaUp\DAO\Submissions::countTotalSubmissionsOfProblem(
            (int)$r['problem']->problem_id
        );

        // List of verdicts
        $verdict_counts = [];

        foreach (self::$verdicts as $verdict) {
            $verdict_counts[$verdict] = \OmegaUp\DAO\Runs::countTotalRunsOfProblemByVerdict(
                (int)$r['problem']->problem_id,
                $verdict
            );
        }

        // Array to count AC stats per case.
        // Let's try to get the last snapshot from cache.
        $problemStatsCache = new \OmegaUp\Cache(\OmegaUp\Cache::PROBLEM_STATS, $r['problem']->alias);
        $casesStats = $problemStatsCache->get();
        if (is_null($casesStats)) {
            // Initialize the array at counts = 0
            $casesStats = [];
            $casesStats['counts'] = [];

            // We need to save the last_submission_id that we processed, so next time we do not repeat this
            $casesStats['last_submission_id'] = 0;
        }

        // Get all runs of this problem after the last id we had
        $runs = \OmegaUp\DAO\Runs::searchWithRunIdGreaterThan(
            (int)$r['problem']->problem_id,
            (int)$casesStats['last_submission_id']
        );

        // For each run we got
        foreach ($runs as $run) {
            // Skip it if it failed to compile.
            if ($run->verdict == 'CE') {
                continue;
            }

            // Try to open the details file. It's okay if the file is missing.
            $details = \OmegaUp\Grader::getInstance()->getGraderResource(
                $run,
                'details.json',
                /*missingOk=*/true
            );
            if (!is_null($details)) {
                $details = json_decode($details);
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

        // Save the last id we saw in case we saw something
        if (!empty($runs)) {
            $casesStats['last_submission_id'] = $runs[count($runs) - 1]->submission_id;
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
     * @param \OmegaUp\Request $r
     */
    private static function validateList(\OmegaUp\Request $r) {
        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);

        // Defaults for offset and rowcount
        if (!isset($r['page'])) {
            if (!isset($r['offset'])) {
                $r['offset'] = 0;
            }
            if (!isset($r['rowcount'])) {
                $r['rowcount'] = 1000;
            }
        }

        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['query'], 'query');
    }

    /**
     * List of public and user's private problems
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiList(\OmegaUp\Request $r) {
        // Authenticate request
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing, we allow unauthenticated users to use this API
        }

        self::validateList($r);

        // Filter results
        $language = null; // Filter by language, all by default.
        // "language" may be one of the allowed options, otherwise the default filter will be used.
        if (!is_null($r['language']) && in_array($r['language'], \OmegaUp\Controllers\Problem::VALID_LANGUAGES)) {
            $language = $r['language'];
        }

        // Sort results
        $orderBy = 'problem_id'; // Order by problem_id by default.
        $sorting_options = ['title', 'quality', 'difficulty', 'submissions', 'accepted', 'ratio', 'points', 'score', 'problem_id'];
        // "order_by" may be one of the allowed options, otherwise the default ordering will be used.
        if (!is_null($r['order_by']) && in_array($r['order_by'], $sorting_options)) {
            $orderBy = $r['order_by'];
        }

        // "mode" may be a valid one, for compatibility reasons 'descending' is the order by default.
        if (!is_null($r['mode']) && ($r['mode'] === 'asc' || $r['mode'] === 'desc')) {
            $order = $r['mode'];
        } else {
            $order = 'desc';
        }

        $response = [];
        $response['results'] = [];
        $authorIdentityId = null;
        $authorUserId = null;
        // There are basically three types of users:
        // - Non-logged in users: Anonymous
        // - Logged in users with normal permissions: Normal
        // - Logged in users with administrative rights: Admin
        $identityType = IDENTITY_ANONYMOUS;
        if (!is_null($r->identity)) {
            $authorIdentityId = intval($r->identity->identity_id);
            if (!is_null($r->user)) {
                $authorUserId = intval($r->user->user_id);
            }

            if (\OmegaUp\Authorization::isSystemAdmin($r->identity) ||
                \OmegaUp\Authorization::hasRole(
                    $r->identity,
                    \OmegaUp\Authorization::SYSTEM_ACL,
                    \OmegaUp\Authorization::REVIEWER_ROLE
                )
            ) {
                $identityType = IDENTITY_ADMIN;
            } else {
                $identityType = IDENTITY_NORMAL;
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
        $response['results'] = \OmegaUp\DAO\Problems::byIdentityType(
            $identityType,
            $language,
            $orderBy,
            $order,
            $offset,
            $rowcount,
            $query,
            $authorIdentityId,
            $authorUserId,
            $r['tag'],
            is_null($r['min_visibility']) ? \OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC : (int) $r['min_visibility'],
            is_null($r['require_all_tags']) ? true : !!$r['require_all_tags'],
            $r['programming_languages'],
            $r['difficulty_range'],
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
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiAdminList(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            $problems = \OmegaUp\DAO\Problems::getAll(
                $page,
                $pageSize,
                'problem_id',
                'DESC'
            );
        } else {
            $problems = \OmegaUp\DAO\Problems::getAllProblemsAdminedByIdentity(
                $r->identity->identity_id,
                $page,
                $pageSize
            );
        }

        $addedProblems = [];

        $hiddenTags = \OmegaUp\DAO\Users::getHideTags($r->identity->identity_id);
        foreach ($problems as $problem) {
            $problemArray = $problem->asArray();
            $problemArray['tags'] = $hiddenTags ? [] : \OmegaUp\DAO\Problems::getTagsForProblem($problem, false);
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
     * @param \OmegaUp\Request $r
     */
    public static function apiMyList(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        self::validateList($r);

        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        $problems = \OmegaUp\DAO\Problems::getAllProblemsOwnedByUser(
            $r->user->user_id,
            $page,
            $pageSize
        );

        $addedProblems = [];

        $hiddenTags = \OmegaUp\DAO\Users::getHideTags($r->identity->identity_id);
        foreach ($problems as $problem) {
            $problemArray = $problem->asArray();
            $problemArray['tags'] = $hiddenTags ? [] : \OmegaUp\DAO\Problems::getTagsForProblem($problem, false);
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
     * @param \OmegaUp\Request $r
     */
    public static function apiBestScore(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        // Uses same params as apiDetails, except for lang, which is optional
        $problem = self::validateDetails($r);

        // If username is set in the request, we use that identity as target.
        // else, we query using current_user
        $identity = self::resolveTargetIdentity($r);

        $response['score'] = self::bestScore(
            $problem['problem'],
            $r['problemset_id'],
            $r['contest_alias'],
            $r->identity->identity_id,
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
     * @param \OmegaUp\DAO\VO\Problems $problem
     * @param $problemsetId
     * @param $contestAlias
     * @param $currentLoggedIdentityId
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @return float
     */
    private static function bestScore(
        \OmegaUp\DAO\VO\Problems $problem,
        $problemsetId,
        $contestAlias,
        int $currentLoggedIdentityId,
        ?\OmegaUp\DAO\VO\Identities $identity = null
    ) : float {
        $currentIdentityId = (is_null($identity) ? $currentLoggedIdentityId : $identity->identity_id);

        $score = 0.0;
        // Add best score info
        $problemset = self::validateProblemset($problem, $problemsetId, $contestAlias);
        if (is_null($problemset['problemset'])) {
            $score = (float)\OmegaUp\DAO\Runs::getBestProblemScore(
                (int)$problem->problem_id,
                (int)$currentIdentityId
            );
        } else {
            $score = (float)\OmegaUp\DAO\Runs::getBestProblemScoreInProblemset(
                (int)$problemset['problemset']->problemset_id,
                (int)$problem->problem_id,
                (int)$currentIdentityId
            );
        }

        return round($score, 2);
    }

    /**
     * Save language data for a problem.
     * @param \OmegaUp\Request $r
     * @return Array
     */
    private static function updateLanguages(\OmegaUp\DAO\VO\Problems $problem) {
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);
        try {
            \OmegaUp\DAO\DAO::transBegin();

            // Removing existing data
            $deletedLanguages = \OmegaUp\DAO\ProblemsLanguages::deleteProblemLanguages(new \OmegaUp\DAO\VO\ProblemsLanguages([
                'problem_id' => $problem->problem_id,
            ]));

            foreach (\OmegaUp\DAO\Languages::getAll() as $lang) {
                if (!$problemArtifacts->exists("statements/{$lang->name}.markdown")) {
                    continue;
                }
                \OmegaUp\DAO\ProblemsLanguages::create(new \OmegaUp\DAO\VO\ProblemsLanguages([
                    'problem_id' => $problem->problem_id,
                    'language_id' => $lang->language_id,
                ]));
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            // Operation failed in something we know it could fail, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }
    }

    /**
     * Gets a Problem settings object with default values.
     *
     * @return array The Problem settings object.
     */
    private static function getDefaultProblemSettings() : array {
        return [
            'limits' => [
                'ExtraWallTime' => '0s',
                'MemoryLimit' => '64MiB',
                'OutputLimit' => '10240KiB',
                'OverallWallTimeLimit' => '30s',
                'TimeLimit' => '1s',
            ],
            'validator' => [
                'name' => 'token-caseless',
                'tolerance' => 1e-9,
                'limits' => [
                    'ExtraWallTime' => '0s',
                    'MemoryLimit' => '256MiB',
                    'OutputLimit' => '10KiB',
                    'OverallWallTimeLimit' => '5s',
                    'TimeLimit' => '30s',
                ],
            ],
        ];
    }

    /**
     * Updates the Problem's settings with the values from the request.
     *
     * @param array $problemSettings the original problem settings.
     * @param \OmegaUp\Request $r the request
     */
    private static function updateProblemSettings(array &$problemSettings, \OmegaUp\Request $r) : void {
        if (!is_null($r['extra_wall_time'])) {
            $problemSettings['limits']['ExtraWallTime'] = (int)$r['extra_wall_time'] . 'ms';
        }
        if (!is_null($r['memory_limit'])) {
            $problemSettings['limits']['MemoryLimit'] = (int)$r['memory_limit'] . 'KiB';
        }
        if (!is_null($r['output_limit'])) {
            $problemSettings['limits']['OutputLimit'] = (int)$r['output_limit'];
        }
        if (!is_null($r['overall_wall_time_limit'])) {
            $problemSettings['limits']['OverallWallTimeLimit'] = (
                (int)$r['overall_wall_time_limit'] . 'ms'
            );
        }
        if (!is_null($r['time_limit'])) {
            $problemSettings['limits']['TimeLimit'] = (int)$r['time_limit'] . 'ms';
        }
        if (!is_null($r['validator'])) {
            $problemSettings['validator']['name'] = $r['validator'];
        }
        if (!is_null($r['validator_time_limit'])) {
            if (empty($problemSettings['validator']['limits'])) {
                $problemSettings['validator']['limits'] = [
                    'ExtraWallTime' => '0s',
                    'MemoryLimit' => '256MiB',
                    'OutputLimit' => '10KiB',
                    'OverallWallTimeLimit' => '5s',
                    'TimeLimit' => '30s',
                ];
            }
            $problemSettings['validator']['limits']['TimeLimit'] = (
                (int)$r['validator_time_limit'] . 'ms'
            );
        }
    }

    public static function getProblemsMineInfoForSmarty(\OmegaUp\Request $r) : array {
        $r->ensureMainUserIdentity();

        return [
            'isSysadmin' => \OmegaUp\Authorization::isSystemAdmin($r->identity),
        ];
    }

    public static function getProblemDetailsForSmarty(
        \OmegaUp\Request $r
    ) : array {
        [
            'problem' => $problem,
            'problemset' => $problemset,
        ] = self::getValidProblemAndProblemset($r);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Get problem details from API
        $details = self::getProblemDetails($r, $problem, $problemset, /*showSolvers=*/true);

        $memoryLimit = (int) $details['settings']['limits']['MemoryLimit'] / 1024 / 1024;
        $result = [
            'problem_alias' => $details['alias'],
            'visibility' => $details['visibility'],
            'source' => $details['source'],
            'problemsetter' => $details['problemsetter'],
            'title' => $details['title'],
            'points' => $details['points'],
            'time_limit' => $details['settings']['limits']['TimeLimit'],
            'overall_wall_time_limit' =>
                $details['settings']['limits']['OverallWallTimeLimit'],
            'memory_limit' => "{$memoryLimit} MiB",
            'input_limit' => ($details['input_limit'] / 1024) . ' KiB',
            'solvers' => $details['solvers'],
            'quality_payload' => [
                'solved' => false,
                'nominated' => false,
                'dismissed' => false,
            ],
            'qualitynomination_reportproblem_payload' => [
                'problem_alias' => $details['alias'],
            ],
            'karel_problem' => count(array_intersect(
                $details['languages'],
                ['kp', 'kj']
            )) == 2,
            'problem_admin' => false,
        ];
        if (isset($details['settings']['cases']) &&
            isset($details['settings']['cases']['sample']) &&
            isset($result['settings']['cases']['sample']['in'])
        ) {
            $result['sample_input'] = $result['settings']['cases']['sample']['in'];
        }
        $details['histogram'] = [
            'difficulty_histogram' => $problem->difficulty_histogram,
            'quality_histogram' => $problem->quality_histogram,
            'quality' => floatval($problem->quality),
            'difficulty' => floatval($problem->difficulty),
        ];
        $details['user'] = ['logged_in' => false, 'admin' => false];
        $result['payload'] = $details;

        if (is_null($r->identity)) {
            return $result;
        }
        $nominationStatus = \OmegaUp\DAO\QualityNominations::getNominationStatusForProblem(
            $problem,
            $r->identity
        );
        $isProblemAdmin = \OmegaUp\Authorization::isProblemAdmin(
            $r->identity,
            $problem
        );
        $nominationStatus['problem_alias'] = $details['alias'];
        $nominationStatus['language'] = $details['statement']['language'];
        $nominationStatus['can_nominate_problem'] = !is_null($r->user);
        $user = [
            'logged_in' => true,
            'admin' => $isProblemAdmin
        ];
        $result['quality_payload'] = $nominationStatus;
        $result['problem_admin'] = $isProblemAdmin;
        $result['payload']['user'] = $user;
        $result['payload']['shouldShowFirstAssociatedIdentityRunWarning'] =
            !is_null($r->user) && !\OmegaUp\Controllers\User::isMainIdentity(
                $r->user,
                $r->identity
            ) && \OmegaUp\DAO\Problemsets::shouldShowFirstAssociatedIdentityRunWarning(
                $r->user
            );
        $result['payload']['solution_status'] = self::getProblemSolutionStatus($problem, $r->identity);
        return $result;
    }

    /**
     * Returns true if the problem's solution exists, otherwise returns false.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem The problem object.
     * @return bool The problem solution status.
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     */
    private static function getProblemSolutionExistenceImpl(
        \OmegaUp\DAO\VO\Problems $problem
    ): bool {
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias, $problem->commit);
        $existingFiles = $problemArtifacts->lsTree('solutions');
        foreach ($existingFiles as $file) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            if ($extension !== 'markdown') {
                continue;
            }

            $lang = pathinfo($file['name'], PATHINFO_FILENAME);
            if (in_array($lang, self::ISO639_1)) {
                return true;
            }
        }
        return false;
    }

    private static function getProblemSolutionExistence(
        \OmegaUp\DAO\VO\Problems $problem
    ): bool {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_SOLUTION_EXISTS,
            "{$problem->alias}-{$problem->commit}",
            function () use ($problem) {
                return \OmegaUp\Controllers\Problem::getProblemSolutionExistenceImpl(
                    $problem
                );
            },
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );
    }

    /**
     * Returns the status for a problem solution.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem
     * @param Identity $user
     * @return string The status for the problem solution.
     */
    public static function getProblemSolutionStatus(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity
    ) : string {
        $exists = self::getProblemSolutionExistence($problem);
        if (!$exists) {
            return self::SOLUTION_NOT_FOUND;
        }
        if (\OmegaUp\Authorization::canViewProblemSolution($identity, $problem)) {
            return self::SOLUTION_UNLOCKED;
        }
        return self::SOLUTION_LOCKED;
    }
}
