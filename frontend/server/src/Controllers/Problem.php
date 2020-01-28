<?php

 namespace OmegaUp\Controllers;

/**
 * ProblemsController
 */
class Problem extends \OmegaUp\Controllers\Controller {
    // SOLUTION STATUS
    const SOLUTION_NOT_FOUND = 'not_found';
    const SOLUTION_UNLOCKED = 'unlocked';
    const SOLUTION_LOCKED = 'locked';

    const RESTRICTED_TAG_NAMES = ['karel', 'lenguaje', 'solo-salida', 'interactive'];
    const VALID_LANGUAGES = ['en', 'es', 'pt'];
    const VALID_SORTING_MODES = ['asc', 'desc'];
    const VALID_SORTING_COLUMNS = [
        'title',
        'quality',
        'difficulty',
        'submissions',
        'accepted',
        'ratio',
        'points',
        'score',
        'creation_date'
    ];

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

    const IMAGE_EXTENSIONS = [
        'bmp', 'gif', 'ico', 'jpe', 'jpeg', 'jpg', 'png', 'svg',
        'svgz', 'tif', 'tiff',
    ];

    // Number of rows shown in problems list
    const PAGE_SIZE = 1000;

    /**
     * Returns a ProblemParams instance from the Request values.
     *
     */
    private static function convertRequestToProblemParams(
        \OmegaUp\Request $r,
        bool $isRequired = true
    ): \OmegaUp\ProblemParams {
        // We need to check problem_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );

        $params = [
            'problem_alias' => $r['problem_alias'],
        ];
        if (!is_null($r['email_clarifications'])) {
            $params['email_clarifications'] = boolval(
                $r['email_clarifications']
            );
        }
        if (!is_null($r['extra_wall_time'])) {
            $params['extra_wall_time'] = intval($r['extra_wall_time']);
        }
        if (!is_null($r['input_limit'])) {
            $params['input_limit'] = intval($r['input_limit']);
        }
        if (!is_null($r['languages'])) {
            if (is_array($r['languages'])) {
                $params['languages'] = implode(',', $r['languages']);
            } else {
                $params['languages'] = strval($r['languages']);
            }
        }
        if (!is_null($r['memory_limit'])) {
            $params['memory_limit'] = intval($r['memory_limit']);
        }
        if (!is_null($r['output_limit'])) {
            $params['output_limit'] = intval($r['output_limit']);
        }
        if (!is_null($r['overall_wall_time_limit'])) {
            $params['overall_wall_time_limit'] = intval(
                $r['overall_wall_time_limit']
            );
        }
        if (!is_null($r['selected_tags'])) {
            $params['selected_tags'] = strval($r['selected_tags']);
        }
        if (!is_null($r['source'])) {
            $params['source'] = strval($r['source']);
        }
        if (!is_null($r['time_limit'])) {
            $params['time_limit'] = intval($r['time_limit']);
        }
        if (!is_null($r['title'])) {
            $params['title'] = strval($r['title']);
        }
        if (!is_null($r['update_published'])) {
            $params['update_published'] = strval($r['update_published']);
        }
        if (!is_null($r['validator'])) {
            $params['validator'] = strval($r['validator']);
        }
        if (!is_null($r['validator_time_limit'])) {
            $params['validator_time_limit'] = intval(
                $r['validator_time_limit']
            );
        }
        if (!is_null($r['visibility'])) {
            $params['visibility'] = intval($r['visibility']);
        }
        return new \OmegaUp\ProblemParams($params, $isRequired);
    }

    /**
     * Validates a Create or Update Problem API request
     *
     * @return array{languages: null|string, problem: \OmegaUp\DAO\VO\Problems|null, selectedTags: array{public: bool, tagname: string}[]|null}
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateCreateOrUpdate(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\ProblemParams $params,
        bool $isRequired = true
    ) {
        $isUpdate = !$isRequired;
        // https://github.com/omegaup/omegaup/issues/739
        if ($identity->username == 'omi') {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $problem = null;
        $selectedTags = [];

        // In case of update, params are optional
        if (!$isRequired) {
            $problem = \OmegaUp\DAO\Problems::getByAlias($params->problemAlias);
            if (is_null($problem)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }

            // We need to check that the user can actually edit the problem
            if (
                !\OmegaUp\Authorization::canEditProblem(
                    $identity,
                    $problem
                )
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            // Only reviewers can revert bans.
            if (
                ($problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED ||
                  $problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED) &&
                    !is_null($params->visibility) &&
                    $problem->visibility !== $params->visibility &&
                    !\OmegaUp\Authorization::isQualityReviewer($identity)
            ) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'qualityNominationProblemHasBeenBanned',
                    'visibility'
                );
            }

            if ($problem->deprecated) {
                throw new \OmegaUp\Exceptions\PreconditionFailedException(
                    'problemDeprecated'
                );
            }

            if (
                !is_null($params->visibility)
                && $problem->visibility !== $params->visibility
            ) {
                if ($problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PROMOTED) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'qualityNominationProblemHasBeenPromoted',
                        'visibility'
                    );
                }
            }
            \OmegaUp\Validators::validateInEnum(
                $params->updatePublished,
                'update_published',
                [
                    \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE,
                    \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET,
                    \OmegaUp\ProblemParams::UPDATE_PUBLISHED_OWNED_PROBLEMSETS,
                    \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
                ],
                false
            );
        } else {
            if (\OmegaUp\Validators::isRestrictedAlias($params->problemAlias)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'aliasInUse'
                );
            }
            if (!\OmegaUp\Validators::isValidAlias($params->problemAlias)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalidAlias',
                    'problem_alias'
                );
            }
            /** @var array{tagname: string, public: bool}[]|null */
            $selectedTags = json_decode(
                $params->selectedTagsAsJSON,
                /*$assoc=*/true
            );
            if (!empty($selectedTags)) {
                foreach ($selectedTags as $tag) {
                    if (empty($tag['tagname'])) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException(
                            'parameterEmpty',
                            'tagname'
                        );
                    }
                }
            }
        }

        if (empty($params->title) && $isRequired) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'title'
            );
        }
        if (empty($params->source) && $isRequired) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'source'
            );
        }
        \OmegaUp\Validators::validateNumberInRange(
            $params->validatorTimeLimit,
            'validator_time_limit',
            0,
            null,
            $isRequired
        );
        \OmegaUp\Validators::validateNumberInRange(
            $params->overallWallTimeLimit,
            'overall_wall_time_limit',
            0,
            60000,
            $isRequired
        );
        \OmegaUp\Validators::validateNumberInRange(
            $params->extraWallTime,
            'extra_wall_time',
            0,
            5000,
            $isRequired
        );
        \OmegaUp\Validators::validateNumberInRange(
            $params->outputLimit,
            'output_limit',
            0,
            null,
            $isRequired
        );
        \OmegaUp\Validators::validateNumberInRange(
            $params->inputLimit,
            'input_limit',
            0,
            null,
            $isRequired
        );
        if (!is_null($params->languages)) {
            \OmegaUp\Validators::validateValidSubset(
                $params->languages,
                'languages',
                array_merge(
                    [''],
                    array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES)
                )
            );
        }

        return [
            'problem' => $problem,
            'selectedTags' => $selectedTags,
            'languages' => is_array(
                $params->languages
            ) ? join(
                ',',
                $params->languages
            ) : $params->languages,
        ];
    }

    /**
     * Create a new problem
     *
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{status: string}
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        self::createProblem(
            $r->user,
            $r->identity,
            self::convertRequestToProblemParams($r)
        );
        return [
            'status' => 'ok',
        ];
    }

    private static function createProblem(
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\ProblemParams $params
    ): void {
        // Validates request
        [
            'selectedTags' => $selectedTags,
            'languages' => $languages,
        ] = self::validateCreateOrUpdate(
            $identity,
            $params
        );

        // Populate a new Problem object
        $problem = new \OmegaUp\DAO\VO\Problems([
            'visibility' => $params->visibility ?? \OmegaUp\ProblemParams::VISIBILITY_PRIVATE,
            'title' => $params->title,
            'visits' => 0,
            'input_limit' => $params->inputLimit,
            'submissions' => 0,
            'accepted' => 0,
            'source' => $params->source,
            'order' => 'normal', /* defaulting to normal */
            'alias' => $params->problemAlias,
            'languages' => $languages,
            'email_clarifications' => $params->emailClarifications,
        ]);

        $problemSettings = self::getDefaultProblemSettings();
        self::updateProblemSettings($problemSettings, $params);
        $acceptsSubmissions = $languages !== '';

        $acl = new \OmegaUp\DAO\VO\ACLs();
        $acl->owner_id = $user->user_id;

        // Insert new problem
        try {
            \OmegaUp\DAO\DAO::transBegin();

            // Commit at the very end
            $problemDeployer = new \OmegaUp\ProblemDeployer(
                $params->problemAlias,
                $acceptsSubmissions
            );
            $problemDeployer->commit(
                'Initial commit',
                $identity,
                \OmegaUp\ProblemDeployer::CREATE,
                $problemSettings
            );
            $problem->commit = $problemDeployer->publishedCommit ?: '';
            $problem->current_version = $problemDeployer->privateTreeHash;

            // Save the contest object with data sent by user to the database
            \OmegaUp\DAO\ACLs::create($acl);
            $problem->acl_id = $acl->acl_id;
            \OmegaUp\DAO\Problems::create($problem);

            // Add tags
            if (!is_null($selectedTags)) {
                foreach ($selectedTags as $tag) {
                    $tagName = \OmegaUp\Controllers\Tag::normalize(
                        $tag['tagname']
                    );
                    if (in_array($tagName, self::RESTRICTED_TAG_NAMES)) {
                        continue;
                    }
                    self::addTag($tagName, $tag['public'], $problem);
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
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'problemTitleExists',
                    $e
                );
            }
            throw $e;
        }

        self::updateLanguages($problem);
    }

    /**
     * Adds an admin to a problem
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiAddAdmin(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        $r->ensureIdentity();

        // Check problem_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );

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

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds a group admin to a problem
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiAddGroupAdmin(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        $r->ensureIdentity();

        // Check problem_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
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

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds a tag to a problem
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{name: string}
     */
    public static function apiAddTag(\OmegaUp\Request $r): array {
        // Check problem_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
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

        return [
            'name' => $r['name'],
        ];
    }

    private static function addTag(
        string $tagName,
        bool $isPublic,
        \OmegaUp\DAO\VO\Problems $problem,
        bool $allowRestricted = false
    ): void {
        // Normalize name.
        $tagName = \OmegaUp\Controllers\Tag::normalize($tagName);

        if (
            !$allowRestricted &&
            in_array($tagName, self::RESTRICTED_TAG_NAMES)
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'tagRestricted',
                'name'
            );
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
            'source' => 'voted',
        ]));
    }

    /**
     * Removes an admin from a problem
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiRemoveAdmin(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check problem_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );

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

        \OmegaUp\Controllers\ACL::removeUser(
            $problem->acl_id,
            $identity->user_id
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes a group admin from a problem
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiRemoveGroupAdmin(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check problem_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Only admin is alowed to make modifications
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::removeGroup(
            $problem->acl_id,
            $group->group_id
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes a tag from a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiRemoveTag(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check whether problem exists
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
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
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'tagRestricted',
                'name'
            );
        }

        \OmegaUp\DAO\ProblemsTags::delete(new \OmegaUp\DAO\VO\ProblemsTags([
            'problem_id' => $problem->problem_id,
            'tag_id' => $tag->tag_id,
        ]));

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes a problem whether user is the creator
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiDelete(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check whether problem exists
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if (\OmegaUp\DAO\Problems::hasBeenUsedInCoursesOrContests($problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'problemHasBeenUsedInContestOrCourse'
            );
        }

        \OmegaUp\DAO\Problems::deleteProblem($problem->problem_id);

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Returns all problem administrators
     *
     * @return array{admins: list<array{role: string, username: string}>, group_admins: list<array{alias: string, name: string, role: string}>}
     */
    public static function apiAdmins(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'admins' => \OmegaUp\DAO\UserRoles::getProblemAdmins($problem),
            'group_admins' => \OmegaUp\DAO\GroupRoles::getProblemAdmins(
                $problem
            )
        ];
    }

    /**
     * Returns every tag associated to a given problem.
     *
     * @return array{tags: list<array{name: string, public: bool}>}
     */
    public static function apiTags(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        $includeVoted = ($r['include_voted'] == 'true');
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        return [
            'tags' => \OmegaUp\DAO\ProblemsTags::getProblemTags(
                $problem,
                !\OmegaUp\Authorization::canEditProblem($r->identity, $problem),
                $includeVoted
            ),
        ];
    }

    /**
     * Rejudge problem
     *
     * @throws \OmegaUp\Exceptions\ApiException
     *
     * @return array{status: string}
     */
    public static function apiRejudge(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        if ($problem->deprecated) {
            throw new \OmegaUp\Exceptions\PreconditionFailedException(
                'problemDeprecated'
            );
        }

        // We need to check that the user actually has admin privileges over
        // the problem.
        if (
            !\OmegaUp\Authorization::isProblemAdmin(
                $r->identity,
                $problem
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Call Grader
        $runs = [];
        try {
            \OmegaUp\DAO\DAO::transBegin();
            $runs = \OmegaUp\DAO\Runs::getByProblem(
                intval(
                    $problem->problem_id
                )
            );

            foreach ($runs as $run) {
                $run->status = 'new';
                $run->version = $problem->current_version;
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

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Update problem contents
     *
     * @param \OmegaUp\Request $r
     * @return array{rejudged: bool}
     * @throws \OmegaUp\Exceptions\ApiException
     */
    public static function apiUpdate(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        // Validate commit message.
        \OmegaUp\Validators::validateStringNonEmpty($r['message'], 'message');
        return self::updateProblem(
            $r->identity,
            $r->user,
            self::convertRequestToProblemParams($r, /*$isRequired=*/ false),
            strval($r['message']),
            $r['update_published'] ?: \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
            boolval($r['redirect'])
        );
    }

    /**
     * @psalm-suppress MixedInferredReturnType Psalm cannot effectively analyze templated arrays this way
     * @psalm-suppress MismatchingDocblockReturnType Psalm cannot effectively analyze templated arrays this way
     * @template T
     * @param T $array
     * @return T
     */
    private static function arrayDeepCopy($array): array {
        $copy = [];
        /** @var string $key */
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $copy[$key] = self::arrayDeepCopy($value);
            } else {
                $copy[$key] = $value;
            }
        }
        /** @var T */
        return $copy;
    }

    /**
     * Converts a duration into milliseconds.
     */
    public static function parseDuration(string $duration): float {
        $milliseconds = 0.0;
        if (
            preg_match_all(
                '/([0-9]*(?:\\.[0-9]*)?)([a-zµ]+)/',
                $duration,
                $matches,
                PREG_SET_ORDER
            ) === false
        ) {
            return $milliseconds;
        }
        /** @var list<string> $match */
        foreach ($matches as $match) {
            if ($match[2] == 'h') {
                $milliseconds += intval($match[1]) * 3600 * 1000;
            } elseif ($match[2] == 'm') {
                $milliseconds += intval($match[1]) * 60 * 1000;
            } elseif ($match[2] == 's') {
                $milliseconds += intval($match[1]) * 1000;
            } elseif ($match[2] == 'ms') {
                $milliseconds += intval($match[1]);
            } elseif ($match[2] == 'us' || $match[2] == 'µs') {
                $milliseconds += intval($match[1]) / 1000.0;
            } elseif ($match[2] == 'ns') {
                $milliseconds += intval($match[1]) / (1000.0 * 1000.0);
            } else {
                throw new \Exception("Unrecognized suffix: {$match[2]}");
            }
        }
        return $milliseconds;
    }

    /**
     * Converts a size into bytes.
     * @param int|string $size
     */
    public static function parseSize($size): int {
        if (is_numeric($size)) {
            return intval($size);
        }
        $bytes = 0;
        if (
            preg_match_all(
                '/([0-9]+)([A-Za-z]+)/',
                $size,
                $matches,
                PREG_SET_ORDER
            ) === false
        ) {
            return $bytes;
        }
        /** @var list<string> $match */
        foreach ($matches as $match) {
            if ($match[2] == 'TiB') {
                $bytes += intval($match[1]) * 1024 * 1024 * 1024 * 1024;
            } elseif ($match[2] == 'GiB') {
                $bytes += intval($match[1]) * 1024 * 1024 * 1024;
            } elseif ($match[2] == 'MiB') {
                $bytes += intval($match[1]) * 1024 * 1024;
            } elseif ($match[2] == 'KiB') {
                $bytes += intval($match[1]) * 1024;
            } elseif ($match[2] == 'B') {
                $bytes += intval($match[1]);
            } else {
                throw new \Exception("Unrecognized suffix: {$match[2]}");
            }
        }
        return $bytes;
    }

    /**
     * @param array{limits: array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}, validator: array{name: string, tolerance: float, limits?: array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}}} $a
     * @param array{limits: array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}, validator: array{name: string, tolerance: float, limits?: array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}}} $b
     */
    private static function diffProblemSettings(array $a, array $b): bool {
        if (
            self::parseDuration($a['limits']['TimeLimit']) !=
            self::parseDuration($b['limits']['TimeLimit'])
        ) {
            return true;
        }
        if (
            self::parseDuration($a['limits']['ExtraWallTime']) !=
            self::parseDuration($b['limits']['ExtraWallTime'])
        ) {
            return true;
        }
        if (
            self::parseDuration($a['limits']['OverallWallTimeLimit']) !=
            self::parseDuration($b['limits']['OverallWallTimeLimit'])
        ) {
            return true;
        }
        if (
            self::parseSize($a['limits']['MemoryLimit']) !=
            self::parseSize($b['limits']['MemoryLimit'])
        ) {
            return true;
        }
        if (
            self::parseSize($a['limits']['OutputLimit']) !=
            self::parseSize($b['limits']['OutputLimit'])
        ) {
            return true;
        }
        if ($a['validator']['name'] != $b['validator']['name']) {
            return true;
        }
        if ($a['validator']['tolerance'] != $b['validator']['tolerance']) {
            return true;
        }
        if (
            empty($a['validator']['limits']) !=
            empty($b['validator']['limits'])
        ) {
            return true;
        }
        // No further checks are necessary.
        if (
            empty($a['validator']['limits']) ||
            empty($b['validator']['limits'])
        ) {
            return false;
        }

        if (
            self::parseDuration($a['validator']['limits']['TimeLimit']) !=
            self::parseDuration($b['validator']['limits']['TimeLimit'])
        ) {
            return true;
        }
        if (
            self::parseDuration($a['validator']['limits']['ExtraWallTime']) !=
            self::parseDuration($b['validator']['limits']['ExtraWallTime'])
        ) {
            return true;
        }
        if (
            self::parseDuration(
                $a['validator']['limits']['OverallWallTimeLimit']
            ) !=
            self::parseDuration(
                $b['validator']['limits']['OverallWallTimeLimit']
            )
        ) {
            return true;
        }
        if (
            self::parseSize($a['validator']['limits']['MemoryLimit']) !=
            self::parseSize($b['validator']['limits']['MemoryLimit'])
        ) {
            return true;
        }
        if (
            self::parseSize($a['validator']['limits']['OutputLimit']) !=
            self::parseSize($b['validator']['limits']['OutputLimit'])
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return array{rejudged: bool}
     */
    private static function updateProblem(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\ProblemParams $params,
        string $message,
        string $updatePublished,
        bool $redirect
    ) {
        [
            'problem' => $problem,
            'languages' => $languages,
        ] = self::validateCreateOrUpdate(
            $identity,
            $params,
            /*$isRequired=*/ false
        );
        if (is_null($problem) || is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        // Update the Problem object
        $valueProperties = [
            'visibility',
            'title',
            'inputLimit',
            'emailClarifications',
            'source',
            'order',
            'languages',
        ];
        $params->updateValueParams($problem, $valueProperties);
        $problem->languages = $languages ?: $problem->languages;

        $response = [
            'rejudged' => false,
        ];

        $problemSettings = self::getProblemSettingsDistrib(
            $problem,
            $problem->commit
        );
        unset($problemSettings['cases']);
        unset($problemSettings['slow']);
        $originalProblemSettings = self::arrayDeepCopy($problemSettings);
        self::updateProblemSettings($problemSettings, $params);
        $settingsUpdated = self::diffProblemSettings(
            $originalProblemSettings,
            $problemSettings
        );
        $acceptsSubmissions = $problem->languages !== '';
        $updatedStatementLanguages = [];

        try {
            //Begin transaction
            \OmegaUp\DAO\DAO::transBegin();

            $operation = \OmegaUp\ProblemDeployer::UPDATE_SETTINGS;
            if (
                isset($_FILES['problem_contents'])
                && is_array($_FILES['problem_contents'])
                && \OmegaUp\FileHandler::getFileUploader()->isUploadedFile(
                    $_FILES['problem_contents']['tmp_name']
                )
            ) {
                $operation = \OmegaUp\ProblemDeployer::UPDATE_CASES;
            }
            if ($operation != \OmegaUp\ProblemDeployer::UPDATE_SETTINGS || $settingsUpdated) {
                $problemDeployer = new \OmegaUp\ProblemDeployer(
                    $problem->alias,
                    $acceptsSubmissions,
                    $updatePublished != \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE
                );
                $problemDeployer->commit(
                    $message,
                    $identity,
                    $operation,
                    $problemSettings
                );

                $needsUpdate = false;
                if (!is_null($problemDeployer->publishedCommit)) {
                    $oldCommit = $problem->commit;
                    $oldVersion = $problem->current_version;
                    [
                        $problem->commit,
                        $problem->current_version,
                    ] = \OmegaUp\Controllers\Problem::resolveCommit(
                        $problem,
                        $problemDeployer->publishedCommit
                    );
                    $response['rejudged'] = ($oldVersion != $problem->current_version);
                    $needsUpdate = $response['rejudged'] || ($oldCommit != $problem->commit);
                }

                if ($needsUpdate) {
                    \OmegaUp\DAO\Runs::createRunsForVersion($problem);
                    \OmegaUp\DAO\Runs::updateVersionToCurrent($problem);
                    if ($updatePublished != \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET) {
                        \OmegaUp\DAO\ProblemsetProblems::updateVersionToCurrent(
                            $problem,
                            $user,
                            $updatePublished
                        );
                    }
                    $updatedStatementLanguages = $problemDeployer->getUpdatedLanguages();
                }
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
            self::$log->error('Failed to update problem', $e);

            throw $e;
        }

        if ($response['rejudged'] && OMEGAUP_ENABLE_REJUDGE_ON_PROBLEM_UPDATE) {
            self::$log->info(
                'Calling \OmegaUp\Controllers\Problem::apiRejudge'
            );
            try {
                $runs = \OmegaUp\DAO\Runs::getNewRunsForVersion($problem);
                \OmegaUp\Grader::getInstance()->rejudge($runs, false);

                // Expire details of the runs
                foreach ($runs as $run) {
                    \OmegaUp\Cache::deleteFromCache(
                        \OmegaUp\Cache::RUN_ADMIN_DETAILS,
                        strval($run->run_id)
                    );
                }
                \OmegaUp\Cache::deleteFromCache(
                    \OmegaUp\Cache::PROBLEM_STATS,
                    strval($problem->alias)
                );
            } catch (\Exception $e) {
                self::$log->error(
                    'Best effort \OmegaUp\Controllers\Problem::apiRejudge failed',
                    $e
                );
            }
        }

        if ($redirect === true) {
            header("Location: {$_SERVER['HTTP_REFERER']}");
        }

        self::invalidateCache($problem, $updatedStatementLanguages);

        return $response;
    }

    private static function setRestrictedTags(\OmegaUp\DAO\VO\Problems $problem): void {
        \OmegaUp\DAO\ProblemsTags::clearRestrictedTags($problem);
        $languages = explode(',', $problem->languages);
        if (in_array('cat', $languages)) {
            \OmegaUp\Controllers\Problem::addTag(
                'solo-salida',
                true,
                $problem,
                true
            );
        } elseif (!empty(array_intersect(['kp', 'kj'], $languages))) {
            \OmegaUp\Controllers\Problem::addTag('karel', true, $problem, true);
        } else {
            \OmegaUp\Controllers\Problem::addTag(
                'lenguaje',
                true,
                $problem,
                true
            );
        }

        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            strval($problem->alias)
        );
        $distribSettings = json_decode(
            $problemArtifacts->get('settings.distrib.json'),
            /*assoc=*/true
        );
        if (!empty($distribSettings['interactive'])) {
            \OmegaUp\Controllers\Problem::addTag(
                'interactive',
                true,
                $problem,
                true
            );
        }
    }

    /**
     * Updates loose file
     *
     * @throws \OmegaUp\Exceptions\ApiException
     *
     * @return list<string>
     */
    private static function updateLooseFile(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Problems $problem,
        string $directory,
        string $contents
    ): array {
        \OmegaUp\Validators::validateStringNonEmpty($r['message'], 'message');
        // Check that lang is in the ISO 639-1 code list, default is "es".
        \OmegaUp\Validators::validateInEnum(
            $r['lang'],
            'lang',
            \OmegaUp\Controllers\Problem::ISO639_1,
            false /* is_required */
        );
        if (is_null($r['lang'])) {
            $r['lang'] = \OmegaUp\Controllers\Identity::getPreferredLanguage(
                $r
            );
        }
        $updatePublished = \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS;
        if (!is_null($r['update_published'])) {
            $updatePublished = $r['update_published'];
        }

        $updatedFileLanguages = [];
        try {
            $problemDeployer = new \OmegaUp\ProblemDeployer(
                $r['problem_alias']
            );
            $problemDeployer->commitLooseFiles(
                "{$r['lang']}.markdown: {$r['message']}",
                $identity,
                [
                    "{$directory}/{$r['lang']}.markdown" => $contents,
                ]
            );
            if ($updatePublished != \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE) {
                [$problem->commit, $problem->current_version] = \OmegaUp\Controllers\Problem::resolveCommit(
                    $problem,
                    $problemDeployer->publishedCommit
                );
                if ($updatePublished != \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET) {
                    \OmegaUp\DAO\ProblemsetProblems::updateVersionToCurrent(
                        $problem,
                        $user,
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
     * @throws \OmegaUp\Exceptions\ApiException
     *
     * @return array{status: string}
     */
    public static function apiUpdateStatement(\OmegaUp\Request $r): array {
        self::updateStatement($r);
        return [
            'status' => 'ok'
        ];
    }

    private static function updateStatement(\OmegaUp\Request $r): void {
        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['statement'],
            'statement'
        );
        $updatedFileLanguages = self::updateLooseFile(
            $r,
            $r->identity,
            $r->user,
            $problem,
            'statements',
            $r['statement']
        );
        self::invalidateCache($problem, $updatedFileLanguages);
    }

    /**
     * Updates problem solution only
     *
     * @throws \OmegaUp\Exceptions\ApiException
     *
     * @return array{status: string}
     */
    public static function apiUpdateSolution(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        [
            'problem' => $problem,
        ] = self::validateCreateOrUpdate(
            $r->identity,
            self::convertRequestToProblemParams($r, /*$isRequired=*/ false),
            /*$isRequired=*/ false
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        \OmegaUp\Validators::validateStringNonEmpty($r['solution'], 'solution');
        $updatedFileLanguages = self::updateLooseFile(
            $r,
            $r->identity,
            $r->user,
            $problem,
            'solutions',
            $r['solution']
        );
        self::invalidateSolutionCache($problem, $updatedFileLanguages);
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
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{exists: bool, problem: null|\OmegaUp\DAO\VO\Problems, problemset: null|\OmegaUp\DAO\VO\Problemsets}
     */
    private static function validateDetails(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );

        // Lang is optional. Default is user's preferred.
        if (!is_null($r['lang'])) {
            \OmegaUp\Validators::validateStringOfLengthInRange(
                $r['lang'],
                'lang',
                2,
                2
            );
        } else {
            $r['lang'] = \OmegaUp\Controllers\Identity::getPreferredLanguage(
                $r
            );
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            return [
                'exists' => false,
                'problem' => null,
                'problemset' => null,
            ];
        }

        if (isset($r['statement_type']) && $r['statement_type'] != 'markdown') {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'invalidStatementType'
            );
        }

        // If we request a problem inside a contest
        $problemset = self::validateProblemset(
            $problem,
            !is_null(
                $r['problemset_id']
            ) ? intval(
                $r['problemset_id']
            ) : $r['problemset_id'],
            $r['contest_alias']
        );

        $response = [
            'exists' => true,
            'problem' => $problem,
            'problemset' => null,
        ];
        if (!is_null($problemset) && isset($problemset['problemset'])) {
            if (is_null($r->identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
            }
            if (
                !\OmegaUp\Authorization::isAdmin(
                    $r->identity,
                    $problemset['problemset']
                )
            ) {
                // If the contest is private, verify that our user is invited
                if (!empty($problemset['contest'])) {
                    if (
                        !\OmegaUp\Controllers\Contest::isPublic(
                            $problemset['contest']->admission_mode
                        )
                    ) {
                        if (
                            is_null(\OmegaUp\DAO\ProblemsetIdentities::getByPK(
                                $r->identity->identity_id,
                                $problemset['problemset']->problemset_id
                            ))
                        ) {
                            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
                        }
                    }
                    // If the contest has not started, non-admin users should not see it
                    if (
                        !\OmegaUp\DAO\Contests::hasStarted(
                            $problemset['contest']
                        )
                    ) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                            'contestNotStarted'
                        );
                    }
                } else {    // Not a contest, but we still have a problemset
                    if (
                        !\OmegaUp\Authorization::canSubmitToProblemset(
                            $r->identity,
                            $problemset['problemset']
                        )
                    ) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException();
                    }
                    // TODO: Check start times.
                }
            }
            $response['problemset'] = $problemset['problemset'];
        } else {
            if (
                is_null($r->identity)
                || !\OmegaUp\Authorization::canEditProblem(
                    $r->identity,
                    $problem
                )
            ) {
                // If the problem is requested outside a contest, we need to
                // check that it is not private
                if (!\OmegaUp\DAO\Problems::isVisible($problem)) {
                    throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                        'problemIsPrivate'
                    );
                }
            }
        }
        return $response;
    }

    /**
     * Gets the problem resource (statement/solution) from the gitserver.
     *
     * @param array{directory: string, alias: string|null, commit: string, language: string} $params
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{language: string, markdown: string, images: array<string, string>} The contents of the resource, plus some metadata.
     */
    public static function getProblemResourceImpl(array $params): array {
        if (is_null($params['alias'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            $params['alias'],
            $params['commit']
        );
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
            $result['markdown'] = mb_convert_encoding(
                $problemArtifacts->get(
                    $sourcePath
                ),
                'utf-8'
            );
        } catch (\Exception $e) {
            throw new \OmegaUp\Exceptions\InvalidFilesystemOperationException(
                'statementNotFound'
            );
        }

        // Get all the images' mappings.
        $statementFiles = $problemArtifacts->lsTree($params['directory']);
        foreach ($statementFiles as $file) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (!in_array($extension, self::IMAGE_EXTENSIONS)) {
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
                file_put_contents(
                    $imagePath,
                    $problemArtifacts->get(
                        "{$params['directory']}/{$file['name']}"
                    )
                );
            }
        }
        return $result;
    }

    /**
     * Gets the problem statement from the gitserver.
     *
     * @param string $alias    The problem alias.
     * @param string $commit   The git commit at which to get the statement.
     * @param string $language The language of the problem. Will default to
     *                           Spanish if not found.
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{language: string, markdown: string, images: array<string, string>} The contents of the file.
     */
    public static function getProblemStatement(
        string $alias,
        string $commit,
        string $language
    ): array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_STATEMENT,
            "{$alias}-{$commit}-{$language}-markdown",
            /** @return array{language: string, images: array<string, string>, markdown: string} */
            function () use ($alias, $commit, $language) {
                return \OmegaUp\Controllers\Problem::getProblemResourceImpl([
                    'directory' => 'statements',
                    'alias' => $alias,
                    'commit' => $commit,
                    'language' => $language,
                ]);
            },
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );
        return $response;
    }

    /**
     * Gets the problem solution from the gitserver.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem  The problem.
     * @param string   $commit   The git commit at which to get the solution.
     * @param string   $language The language of the solution. Will default to
     *                           Spanish if not found.
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{language: string, markdown: string, images: array<string, string>} The contents of the file.
     */
    public static function getProblemSolution(
        \OmegaUp\DAO\VO\Problems $problem,
        string $commit,
        string $language
    ): array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_SOLUTION,
            "{$problem->alias}-{$commit}-{$language}-markdown",
            /** @return array{language: string, markdown: string, images: array<string, string>} */
            function () use ($problem, $commit, $language): array {
                return \OmegaUp\Controllers\Problem::getProblemResourceImpl([
                    'directory' => 'solutions',
                    'alias' => strval($problem->alias),
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
     * @return array{cases: array<string, mixed>, limits: array{ExtraWallTime: string, TimeLimit: string, OverallWallTimeLimit: string, MemoryLimit: int|string, OutputLimit: int|string}, validator: array{limits?: array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}, name: string, tolerance: float}}
     */
    private static function getProblemSettingsDistrib(
        \OmegaUp\DAO\VO\Problems $problem,
        string $commit
    ): array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_SETTINGS_DISTRIB,
            "{$problem->alias}-{$problem->commit}",
            /** @return array{cases: array<string, mixed>, limits: array{ExtraWallTime: string, TimeLimit: string, OverallWallTimeLimit: string, MemoryLimit: int|int, OutputLimit: int|string}, validator: array{limits?: array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}, name: string, tolerance: float}} */
            function () use ($problem): array {
                return \OmegaUp\Controllers\Problem::getProblemSettingsDistribImpl([
                    'alias' => strval($problem->alias),
                    'commit' => $problem->commit,
                ]);
            },
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );
    }

    /**
     * Gets the distributable problem settings for the problem.
     *
     * @param array{alias: string, commit: string} $params
     *
     * @return array{cases: array<string, mixed>, limits: array{ExtraWallTime: string, TimeLimit: string, OverallWallTimeLimit: string, MemoryLimit: int|int, OutputLimit: int|string}, validator: array{limits?: array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}, name: string, tolerance: float}}
     */
    public static function getProblemSettingsDistribImpl(array $params): array {
        /** @var array{cases: array<string, mixed>, limits: array{ExtraWallTime: string, TimeLimit: string, OverallWallTimeLimit: string, MemoryLimit: int|int, OutputLimit: int|string}, validator: array{limits?: array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}, name: string, tolerance: float}} */
        return json_decode(
            (new \OmegaUp\ProblemArtifacts(
                $params['alias'],
                $params['commit']
            ))->get(
                'settings.distrib.json'
            ),
            /*assoc=*/true
        );
    }

    /**
     * Entry point for Problem Download API
     *
     * @param \OmegaUp\Request $r
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return void
     */
    public static function apiDownload(\OmegaUp\Request $r): void {
        $r->ensureIdentity();

        // Validate request
        $problem = self::validateDownload(
            $r->identity,
            strval($r['problem_alias'])
        );

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/zip');
        header(
            "Content-Disposition: attachment;filename={$problem->alias}.zip"
        );
        header('Content-Transfer-Encoding: binary');
        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            strval($problem->alias)
        );
        $problemArtifacts->download();

        die();
    }

    /**
     * Validate problem Details API
     *
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return \OmegaUp\DAO\VO\Problems
     */
    private static function validateDownload(
        \OmegaUp\DAO\VO\Identities $identity,
        string $problemAlias
    ): \OmegaUp\DAO\VO\Problems {
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (!\OmegaUp\Authorization::canEditProblem($identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return $problem;
    }

    /**
     * Validate problemset Details API
     *
     * @return null|array{contest?:\OmegaUp\DAO\VO\Contests, problemset: \OmegaUp\DAO\VO\Problemsets}
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateProblemset(
        \OmegaUp\DAO\VO\Problems $problem,
        ?int $problemsetId,
        ?string $contestAlias = null
    ) {
        $problemNotFound = null;
        $response = [];
        if (!empty($contestAlias)) {
            // Is it a valid contest_alias?
            $response['contest'] = \OmegaUp\DAO\Contests::getByAlias(
                $contestAlias
            );
            if (is_null($response['contest'])) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'contestNotFound'
                );
            }
            $response['problemset'] = \OmegaUp\DAO\Problemsets::getByPK(
                intval(
                    $response['contest']->problemset_id
                )
            );
            if (is_null($response['problemset'])) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'contestNotFound'
                );
            }
            $problemNotFound = 'problemNotFoundInContest';
        } elseif (!is_null($problemsetId)) {
            // Is it a valid problemset_id?
            $response['problemset'] = \OmegaUp\DAO\Problemsets::getByPK(
                $problemsetId
            );
            if (is_null($response['problemset'])) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemsetNotFound'
                );
            }
            $problemNotFound = 'problemNotFoundInProblemset';
        } else {
            // Nothing to see here, move along.
            return null;
        }

        // Is the problem actually in the problemset?
        if (
            is_null(\OmegaUp\DAO\ProblemsetProblems::getByPK(
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
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{accepted?: int, admin?: bool, alias?: string, commit?: string, creation_date?: int, difficulty?: float|null, email_clarifications?: bool, exists: bool, input_limit?: int, languages?: list<string>, order?: string, points?: float, preferred_language?: string, problemsetter?: array{creation_date: int, name: string, username: string}, runs?: list<array{alias: string, contest_score: float|null, guid: string, language: string, memory: int, penalty: int, runtime: int, score: float, status: string, submit_delay: int, time: int, username: string, verdict: string}>, score?: float, settings?: array{cases: array<string, mixed>, limits: array{MemoryLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}, validator: mixed}, solvers?: list<array{language: string, memory: float, runtime: float, time: int, username: string}>, source?: string, statement?: array{images: array<string, string>, language: string, markdown: string}, status?: string, submissions?: int, title?: string, version?: string, visibility?: int, visits?: int}
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        $r->ensureBool('show_solvers', /*required=*/false);
        $r->ensureBool('prevent_problemset_open', /*required=*/false);
        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['lang'], 'lang');
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['auth_token'],
            'auth_token'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        $result = self::getValidProblemAndProblemset($r);
        [
            'exists' => $problemExisits,
            'problem' => $problem,
            'problemset' => $problemset,
        ] = $result;
        if (!$problemExisits || is_null($problem)) {
            return $result;
        }
        $details = self::getProblemDetails(
            $r->identity,
            $problem,
            $problemset,
            strval($r['lang']),
            boolval($r['show_solvers']) === true,
            $r['auth_token'],
            boolval($r['prevent_problemset_open']) === true,
            $r['contest_alias']
        );
        if (is_null($details)) {
            return [
                'exists' => false,
            ];
        }
        $details['exists'] = true;
        return $details;
    }

    /**
     * Get user. Allow unauthenticated requests if we are not opening a problem
     * inside a contest
     *
     * @throws \OmegaUp\Exceptions\UnauthorizedException
     *
     * @return array{status?: string, exists: bool, problem: null|\OmegaUp\DAO\VO\Problems, problemset: null|\OmegaUp\DAO\VO\Problemsets}
     */
    private static function getValidProblemAndProblemset(\OmegaUp\Request $r): array {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            if (
                !is_null($r['contest_alias']) ||
                !is_null($r['problemset_id'])
            ) {
                throw $e;
            }
        }

        // Validate request and return the object
        return self::validateDetails($r);
    }

    /**
     * Get the extra problem details with all the validations
     * @return null|array{statement: array{language: string, images: array<string, string>, markdown: string}, settings: array{cases: array<string, mixed>, limits: array{TimeLimit: string, OverallWallTimeLimit: string, MemoryLimit: int|string}, validator: mixed}, preferred_language?: string, problemsetter?: array{username: string, name: string, creation_date: int}, version: string, commit: string, title: string, alias: string, input_limit: int, visits: int, submissions: int, accepted: int, difficulty: null|float, creation_date: int, source?: string, order: string, points: null|float, visibility: int, languages: list<string>, email_clarifications: bool, runs?: list<array{guid: string, language: string, status: string, verdict: string, runtime: int, penalty: int, memory: int, score: float, contest_score: float|null, time: int, submit_delay: int, alias: string, username: string}>, admin?: bool, solvers?: list<array{username: string, language: string, runtime: float, memory: float, time: int}>, points: float, score: float}
     */
    private static function getProblemDetails(
        ?\OmegaUp\DAO\VO\Identities $loggedIdentity,
        \OmegaUp\DAO\VO\Problems $problem,
        ?\OmegaUp\DAO\VO\Problemsets $problemset,
        string $statementLanguage,
        bool $showSolvers,
        ?string $authToken,
        bool $preventProblemsetOpen,
        ?string $contestAlias
    ): ?array {
        $response = [];

        // Get the expected commit version.
        $commit = $problem->commit;
        $version = strval($problem->current_version);
        if (!empty($problemset)) {
            $problemsetProblem = \OmegaUp\DAO\ProblemsetProblems::getByPK(
                $problemset->problemset_id,
                $problem->problem_id
            );
            if (is_null($problemsetProblem)) {
                return null;
            }
            $commit = $problemsetProblem->commit;
            $version = strval($problemsetProblem->version);
        }

        $response['statement'] = \OmegaUp\Controllers\Problem::getProblemStatement(
            strval($problem->alias),
            $commit,
            $statementLanguage
        );
        $response['settings'] = \OmegaUp\Controllers\Problem::getProblemSettingsDistrib(
            $problem,
            $commit
        );

        // Add preferred language of the user.
        $request = new \OmegaUp\Request(
            [
                'omit_rank' => true,
                'auth_token' => $authToken,
            ]
        );

        if (!is_null($loggedIdentity) && !is_null($loggedIdentity->username)) {
            self::authenticateOrAllowUnauthenticatedRequest($request);

            $identity = self::resolveTargetIdentity($request);
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterNotFound',
                    'Identity'
                );
            }
            $userData = \OmegaUp\Controllers\User::getUserProfile(
                $loggedIdentity,
                $identity,
                /**$omitRank=*/true
            );
            if (
                !empty($userData) &&
                !empty($userData['preferred_language'])
            ) {
                $response['preferred_language'] = strval(
                    $userData['preferred_language']
                );
            }
        }

        // Add the problem the response
        $response['title'] = strval($problem->title);
        $response['alias'] = strval($problem->alias);
        $response['input_limit'] = $problem->input_limit;
        $response['visits'] = $problem->visits;
        $response['submissions'] = $problem->submissions;
        $response['accepted'] = $problem->accepted;
        $response['difficulty'] = $problem->difficulty;
        $response['creation_date'] = $problem->creation_date;
        $response['source'] = strval($problem->source);
        $response['order'] = $problem->order;
        $response['visibility'] = $problem->visibility;
        $response['email_clarifications'] = $problem->email_clarifications;
        $response['version'] = $version;
        $response['commit'] = $commit;

        // If the problem is public or if the user has admin privileges, show the
        // problem source and alias of owner.
        if (
            \OmegaUp\DAO\Problems::isVisible($problem) ||
            (
                !is_null($loggedIdentity) &&
                \OmegaUp\Authorization::isProblemAdmin(
                    $loggedIdentity,
                    $problem
                )
            )
        ) {
            if (is_null($problem->acl_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }
            $acl = \OmegaUp\DAO\ACLs::getByPK($problem->acl_id);
            if (is_null($acl->owner_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
            }
            $problemsetter = \OmegaUp\DAO\Identities::findByUserId(
                $acl->owner_id
            );
            $response['problemsetter'] = [
                'username' => strval($problemsetter->username),
                'name' => is_null($problemsetter->name) ?
                          strval($problemsetter->username) :
                          $problemsetter->name,
                'creation_date' => intval(\OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $response['creation_date']
                )),
            ];
        } else {
            unset($response['source']);
        }

        $problemsetId = !is_null(
            $problemset
        ) ? intval(
            $problemset->problemset_id
        ) : null;

        if (!is_null($loggedIdentity)) {
            // Get all the available runs done by the current_user
            $runsArray = \OmegaUp\DAO\Runs::getForProblemDetails(
                intval($problem->problem_id),
                $problemsetId,
                intval($loggedIdentity->identity_id)
            );

            // Add each filtered run to an array
            $results = [];
            foreach ($runsArray as $run) {
                $run['alias'] = strval($problem->alias);
                $run['username'] = strval($loggedIdentity->username);
                $results[] = $run;
            }
            $response['runs'] = $results;
        }

        if (!is_null($problemset) && !is_null($loggedIdentity)) {
            $result['admin'] = \OmegaUp\Authorization::isAdmin(
                $loggedIdentity,
                $problemset
            );
            if (!$result['admin'] || $preventProblemsetOpen !== true) {
                if (is_null($problemset->problemset_id)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'problemsetNotFound'
                    );
                }
                // At this point, contestant_user relationship should be established.
                $container = \OmegaUp\DAO\Problemsets::getProblemsetContainer(
                    $problemset->problemset_id
                );
                if (is_null($container)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'problemsetNotFound'
                    );
                }
                \OmegaUp\DAO\ProblemsetIdentities::checkAndSaveFirstTimeAccess(
                    $loggedIdentity,
                    $container,
                    \OmegaUp\Authorization::canSubmitToProblemset(
                        $loggedIdentity,
                        $problemset
                    )
                );
            }

            // As last step, register the problem as opened
            if (
                !\OmegaUp\DAO\ProblemsetProblemOpened::getByPK(
                    $problemsetId,
                    $problem->problem_id,
                    $loggedIdentity->identity_id
                )
            ) {
                \OmegaUp\DAO\ProblemsetProblemOpened::create(new \OmegaUp\DAO\VO\ProblemsetProblemOpened([
                    'problemset_id' => $problemset->problemset_id,
                    'problem_id' => $problem->problem_id,
                    'open_time' => \OmegaUp\Time::get(),
                    'identity_id' => $loggedIdentity->identity_id
                ]));
            }
        } elseif ($showSolvers) {
            $response['solvers'] = \OmegaUp\DAO\Runs::getBestSolvingRunsForProblem(
                intval($problem->problem_id)
            );
        }

        if (!is_null($loggedIdentity)) {
            \OmegaUp\DAO\ProblemViewed::MarkProblemViewed(
                intval($loggedIdentity->identity_id),
                intval($problem->problem_id)
            );
        }

        // send the supported languages as a JSON array instead of csv
        // array_filter is needed to handle when $response['languages'] is empty
        /** @var list<string> */
        $response['languages'] = array_filter(
            explode(',', $problem->languages)
        );

        $response['points'] = round(
            100.0 / (log(
                max(
                    $response['accepted'],
                    1.0
                ) + 1,
                2
            )),
            2
        );
        if (is_null($loggedIdentity)) {
            $response['score'] = 0.0;
        } else {
            $response['score'] = self::bestScore(
                $problem,
                $problemsetId,
                $contestAlias,
                intval($loggedIdentity->identity_id)
            );
        }
        return $response;
    }

    /**
     * Returns the solution for a problem if conditions are satisfied.
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{exists: bool, solution?: array{language: string, markdown: string, images: array<string, string>}}
     */
    public static function apiSolution(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        // Validate request
        $problem = self::validateDetails($r);
        if (is_null($problem['problem'])) {
            return [
                'exists' => false,
            ];
        }
        $problemset = $problem['problemset'];
        $problem = $problem['problem'];

        // Get the expected commit version.
        $commit = $problem->commit;
        $version = strval($problem->current_version);
        if (!empty($problemset)) {
            $problemsetProblem = \OmegaUp\DAO\ProblemsetProblems::getByPK(
                $problemset->problemset_id,
                $problem->problem_id
            );
            if (is_null($problemsetProblem)) {
                return [
                    'exists' => false,
                ];
            }
            $commit = $problemsetProblem->commit;
            $version = strval($problemsetProblem->version);
        }

        if (
            !\OmegaUp\Authorization::canViewProblemSolution(
                $r->identity,
                $problem
            )
        ) {
            $r->ensureBool('forfeit_problem', false /*isRequired*/);
            if ($r['forfeit_problem'] !== true) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'problemSolutionNotVisible'
                );
            }
            $seenSolutions = \OmegaUp\DAO\ProblemsForfeited::getProblemsForfeitedCount(
                $r->user
            );
            $allowedSolutions = intval(
                \OmegaUp\DAO\Problems::getProblemsSolvedCount(
                    $r->identity
                ) /
                \OmegaUp\Controllers\ProblemForfeited::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION
            );
            // Validate that the user will not exceed the number of allowed solutions.
            if ($seenSolutions >= $allowedSolutions) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'allowedSolutionsLimitReached'
                );
            }
            \OmegaUp\DAO\ProblemsForfeited::create(new \OmegaUp\DAO\VO\ProblemsForfeited([
                'user_id' => $r->user->user_id,
                'problem_id' => $problem->problem_id
            ]));
        }

        return [
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
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{published: null|string, log: list<array{commit: string, tree: array<string, mixed>|null, parents?: array<array-key, string>, author: array{name?: string, email?: string, time: int|null|string}, committer: array{name?: string, email?: string, time: int|null|string}, message?: string, version: null|string}>}
     */
    public static function apiVersions(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateValidAlias(
            $r['problem_alias'],
            'problem_alias'
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem) || is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            return [
                'published' => $problem->commit,
                'log' => [
                    [
                        'commit' => $problem->commit,
                        'tree' => null,
                        'author' => [
                            'time' => \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                                $problem->creation_date
                            ),
                        ],
                        'committer' => [
                            'time' => \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                                $problem->creation_date
                            ),
                        ],
                        'version' => $problem->current_version,
                    ],
                ],
            ];
        }

        $privateTreeMapping = [];
        foreach (
            (new \OmegaUp\ProblemArtifacts(
                $problem->alias,
                'private'
            ))->log() as $logEntry
        ) {
            $privateTreeMapping[$logEntry['commit']] = $logEntry['tree'];
        }

        $masterLog = [];
        foreach (
            (new \OmegaUp\ProblemArtifacts(
                $problem->alias,
                'master'
            ))->log() as $logEntry
        ) {
            if (count($logEntry['parents']) < 3) {
                // Master commits always have 3 or 4 parents. If they have
                // fewer, it's one of the commits in the merged branches.
                continue;
            }
            $logEntry['version'] = $privateTreeMapping[$logEntry['parents'][count(
                $logEntry['parents']
            ) - 1]];
            $logEntry['tree'] = [];
            foreach (
                (new \OmegaUp\ProblemArtifacts(
                    $problem->alias,
                    $logEntry['commit']
                ))->lsTreeRecursive() as $treeEntry
            ) {
                $logEntry['tree'][$treeEntry['path']] = $treeEntry['id'];
            }
            $masterLog[] = $logEntry;
        }

        return [
            'published' => (new \OmegaUp\ProblemArtifacts(
                $problem->alias,
                'published'
            ))->commit()['commit'],
            'log' => $masterLog,
        ];
    }

    /**
     * Change the version of the problem.
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{status: string}
     */
    public static function apiSelectVersion(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        \OmegaUp\Validators::validateValidAlias(
            $r['problem_alias'],
            'problem_alias'
        );
        \OmegaUp\Validators::validateStringOfLengthInRange(
            $r['commit'],
            'commit',
            1,
            40,
            false
        );
        // \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE is not allowed here because
        // it would not make any sense!
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['update_published'],
            'update_published'
        );
        \OmegaUp\Validators::validateInEnum(
            $r['update_published'],
            'update_published',
            [
                \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET,
                \OmegaUp\ProblemParams::UPDATE_PUBLISHED_OWNED_PROBLEMSETS,
                \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
            ],
            false
        );

        $updatePublished = \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS;
        if (!is_null($r['update_published'])) {
            $updatePublished = $r['update_published'];
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem) || is_null($problem->alias)) {
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

        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            $problem->alias,
            $problem->commit
        );

        // Update problem fields.
        /** @var array{Cases: list<array{Cases: list<array{Name: string, Weight: int}>, Name: string}>, Limits: array{ExtraWallTime: string, MemoryLimit: int, OutputLimit: int, OverallWallTimeLimit: string, TimeLimit: string}, Slow: null, Validator: array{Name: string, Tolerance: string, Limits: array{ExtraWallTime: string, MemoryLimit: int, OutputLimit: int, OverallWallTimeLimit: string, TimeLimit: string}}}*/
        $problemSettings = json_decode(
            $problemArtifacts->get('settings.json'),
            /*assoc=*/true
        );

        $problemDeployer = new \OmegaUp\ProblemDeployer($problem->alias);
        try {
            // Begin transaction
            \OmegaUp\DAO\DAO::transBegin();
            $commit = ((new \OmegaUp\ProblemArtifacts(
                $problem->alias,
                'published'
            ))->commit())['commit'];
            if (is_null($commit)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemVersionNotFound'
                );
            }
            $problemDeployer->updatePublished(
                $commit,
                $problem->commit,
                $r->identity
            );

            \OmegaUp\DAO\Runs::createRunsForVersion($problem);
            \OmegaUp\DAO\Runs::updateVersionToCurrent($problem);
            if ($updatePublished != \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET) {
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
            self::$log->info(
                'Calling \OmegaUp\Controllers\Problem::apiRejudge'
            );
            try {
                $runs = \OmegaUp\DAO\Runs::getNewRunsForVersion($problem);
                \OmegaUp\Grader::getInstance()->rejudge($runs, false);

                // Expire details of the runs
                foreach ($runs as $run) {
                    \OmegaUp\Cache::deleteFromCache(
                        \OmegaUp\Cache::RUN_ADMIN_DETAILS,
                        strval($run->run_id)
                    );
                }
                \OmegaUp\Cache::deleteFromCache(
                    \OmegaUp\Cache::PROBLEM_STATS,
                    $problem->alias
                );
            } catch (\Exception $e) {
                self::$log->error(
                    'Best effort \OmegaUp\Controllers\Problem::apiRejudge failed',
                    $e
                );
            }
        }
        $updatedStatementLanguages = [];
        foreach ($problemArtifacts->lsTree('statements') as $file) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            if ($extension != 'markdown') {
                continue;
            }
            $updatedStatementLanguages[] = pathinfo(
                $file['name'],
                PATHINFO_FILENAME
            );
        }
        self::invalidateCache(
            $problem,
            array_merge(
                $updatedStatementLanguages,
                \OmegaUp\Controllers\Problem::VALID_LANGUAGES
            )
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Return a report of which runs would change due to a version change.
     *
     * @return array{diff: list<array{username: string, guid: string, problemset_id: ?int, old_status: ?string, old_verdict: ?string, old_score: ?float, new_status: ?string, new_verdict: ?string, new_score: ?float}>}
     */
    public static function apiRunsDiff(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateValidAlias(
            $r['problem_alias'],
            'problem_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['version'], 'version');

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'diff' => \OmegaUp\DAO\Runs::getRunsDiffsForVersion(
                $problem,
                null,
                strval($problem->current_version),
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
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{string, string} the SHA1 of a commit in the problem's
     *                               master branch, plus the SHA1 of the
     *                               private branch tree associated with that
     *                               commit.
     */
    public static function resolveCommit(
        \OmegaUp\DAO\VO\Problems $problem,
        ?string $commit
    ): array {
        /** @var null|array{commit: string, tree: string, parents: string[], author: array{name: string, email: string, time: string}, committer: array{name: string, email: string, time: string}, message: string} */
        $masterCommit = null;
        if (is_null($commit)) {
            $masterCommit = (new \OmegaUp\ProblemArtifacts(
                strval($problem->alias),
                'published'
            ))->commit();
        } else {
            foreach (
                (new \OmegaUp\ProblemArtifacts(
                    strval($problem->alias),
                    'master'
                ))->log() as $logEntry
            ) {
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
        if (is_null($masterCommit)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemVersionNotFound'
            );
        }

        // The private branch is always the last parent.
        $privateCommitHash = $masterCommit['parents'][count(
            $masterCommit['parents']
        ) - 1];
        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            strval($problem->alias),
            $privateCommitHash
        );
        $privateCommit = $problemArtifacts->commit();
        if (is_null($privateCommit)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemVersionNotFound'
            );
        }

        // Update problem fields.
        return [$masterCommit['commit'], $privateCommit['tree']];
    }

    /**
     * Entry point for Problem runs API
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{runs: list<array{guid: string, language: string, status: string, verdict: string, runtime: int, penalty: int, memory: int, score: float, contest_score: float|null, time: int, submit_delay: int, alias: mixed|string, username: string, run_id?: int, judged_by?: null|string, type?: null|string, country_id?: null|string, contest_alias?: null|string}>}
     */
    public static function apiRuns(\OmegaUp\Request $r): array {
        // Get user
        $r->ensureIdentity();

        // Validate request
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $response = [];

        if ($r['show_all']) {
            if (
                !\OmegaUp\Authorization::isProblemAdmin(
                    $r->identity,
                    $problem
                )
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $identity = null;
            if (!is_null($r['username'])) {
                try {
                    $identity = \OmegaUp\DAO\Identities::findByUsername(
                        strval($r['username'])
                    );
                } catch (\Exception $e) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'userNotFound'
                    );
                }
            }
            $response['runs'] = \OmegaUp\DAO\Runs::getAllRuns(
                null,
                !is_null($r['status']) ? strval($r['status']) : null,
                !is_null($r['verdict']) ? strval($r['verdict']) : null,
                $problem->problem_id,
                !is_null($r['language']) ? strval($r['language']) : null,
                !is_null($identity) ? intval($identity->identity_id) : null,
                !is_null($r['offset']) ? intval($r['offset']) : null,
                !is_null($r['rowcount']) ? intval($r['rowcount']) : null
            );
        } else {
            // Get all the available runs
            $runsArray = \OmegaUp\DAO\Runs::getForProblemDetails(
                intval($problem->problem_id),
                null,
                intval($r->identity->identity_id)
            );

            // Add each filtered run to an array
            $result = [];
            foreach ($runsArray as $run) {
                $run['alias'] = $problem->alias;
                $run['username'] = $r->identity->username;
                $result[] = $run;
            }
            $response['runs'] = $result;
        }

        return $response;
    }

    /**
     * Entry point for Problem clarifications API
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{clarifications: list<array{clarification_id: int, contest_alias: string, author: null|string, message: string, time: int, answer: null|string, public: bool}>}
     */
    public static function apiClarifications(\OmegaUp\Request $r): array {
        // Get user
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem) || is_null($problem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $isProblemAdmin = \OmegaUp\Authorization::isProblemAdmin(
            $r->identity,
            $problem
        );

        $clarifications = \OmegaUp\DAO\Clarifications::GetProblemClarifications(
            $problem->problem_id,
            $isProblemAdmin,
            $r->identity->identity_id,
            $r['offset'],
            $r['rowcount']
        );

        foreach ($clarifications as &$clar) {
            $clar['time'] = intval($clar['time']);
        }

        // Add response to array
        return [
            'clarifications' => $clarifications,
        ];
    }

    /**
     * Stats of a problem
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{cases_stats: array<string, int>, pending_runs: list<array{guid: string}>, total_runs: int, verdict_counts: array<string, int>}
     */
    public static function apiStats(\OmegaUp\Request $r): array {
        // Get user
        $r->ensureIdentity();

        // Validate request
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // We need to check that the user has privileges on the problem
        if (
            !\OmegaUp\Authorization::isProblemAdmin(
                $r->identity,
                $problem
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Array of GUIDs of pending runs
        $pendingRunsGuids = \OmegaUp\DAO\Runs::getPendingRunsOfProblem(
            intval($problem->problem_id)
        );

        // Count of pending runs (int)
        $totalRunsCount = \OmegaUp\DAO\Submissions::countTotalSubmissionsOfProblem(
            intval($problem->problem_id)
        );

        // List of verdicts
        $verdictCounts = [];

        foreach (\OmegaUp\Controllers\Run::VERDICTS as $verdict) {
            $verdictCounts[$verdict] = \OmegaUp\DAO\Runs::countTotalRunsOfProblemByVerdict(
                intval($problem->problem_id),
                $verdict
            );
        }

        // Array to count AC stats per case.
        // Let's try to get the last snapshot from cache.
        $problemStatsCache = new \OmegaUp\Cache(
            \OmegaUp\Cache::PROBLEM_STATS,
            strval($problem->alias)
        );
        /** @var array{counts: array<string, int>, last_submission_id: int}|null */
        $casesStats = $problemStatsCache->get();
        if (is_null($casesStats)) {
            // Initialize the array at counts = 0
            $casesStats = [
                'counts' => [],
                'last_submission_id' => 0,
            ];
        }

        // Get all runs of this problem after the last id we had
        $runs = \OmegaUp\DAO\Runs::searchWithRunIdGreaterThan(
            intval($problem->problem_id),
            intval($casesStats['last_submission_id'])
        );

        // For each run we got
        foreach ($runs as $run) {
            // Skip it if it failed to compile.
            if ($run->verdict == 'CE') {
                continue;
            }

            // Try to open the details file. It's okay if the file is missing.
            $detailsJson = \OmegaUp\Grader::getInstance()->getGraderResource(
                $run,
                'details.json',
                /*missingOk=*/true
            );
            if (!is_null($detailsJson)) {
                /** @var null|array{verdict: string, compile_meta: array{Main: array{verdict: string, time: float, sys_time: float, wall_time: float, memory: int}}, score: int, contest_score: int, max_score: int, time: float, wall_time: float, memory: int, judged_by: string, groups: list<array{group: string, score: float, contest_score: int, max_score: int, cases: list<array{verdict: string, name: string, score: int, contest_score: int, max_score: int, meta: array{verdict: string, time: float, sys_time: int, wall_time: float, memory: int}}>}>} */
                $details = json_decode($detailsJson, /*associative=*/true);
                if (!is_array($details)) {
                    self::$log->error(
                        "Failed to interpret run details: {$detailsJson}"
                    );
                    continue;
                }
                foreach ($details as $key => $item) {
                    if ($key !== 'groups' || !is_array($item)) {
                        continue;
                    }
                    foreach ($item as $group) {
                        if (!isset($group['cases'])) {
                            continue;
                        }
                        foreach ($group['cases'] as $case) {
                            $caseName = strval($case['name']);
                            if (
                                !array_key_exists(
                                    $caseName,
                                    $casesStats['counts']
                                )
                            ) {
                                $casesStats['counts'][$case['name']] = 0;
                            }
                            if ($case['score'] === 0) {
                                continue;
                            }
                            $casesStats['counts'][$case['name']]++;
                        }
                    }
                }
            }
        }

        // Save the last id we saw in case we saw something
        if (!empty($runs)) {
            $casesStats['last_submission_id'] = $runs[count(
                $runs
            ) - 1]->submission_id;
        }

        // Save in cache what we got
        $problemStatsCache->set(
            $casesStats,
            APC_USER_CACHE_PROBLEM_STATS_TIMEOUT
        );

        return [
            'total_runs' => $totalRunsCount,
            'pending_runs' => $pendingRunsGuids,
            'verdict_counts' => $verdictCounts,
            'cases_stats' => $casesStats['counts'],
        ];
    }

    /**
     * Validate list request
     *
     * @return array{offset: null|int, rowcount: null|int}
     */
    private static function validateList(
        ?int $offset,
        ?int $rowcount,
        ?int $page
    ) {
        // Defaults for offset and rowcount
        $newOffset = null;
        $newRowcount = null;
        if (is_null($page)) {
            $newOffset = is_null($offset) ? 0 : $offset;
            $newRowcount = is_null(
                $rowcount
            ) ? \OmegaUp\Controllers\Problem::PAGE_SIZE : $rowcount;
        }

        return [
            'offset' => $newOffset,
            'rowcount' => $newRowcount,
        ];
    }

    /**
     * @return array{difficultyRange: array{0: int, 1: int}|null, keyword: string, language: string, minVisibility: int, mode: string, orderBy: string, page: int, programmingLanguages: list<string>, requireAllTags: bool, tags: list<string>}
     */
    private static function validateListParams(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateInEnum(
            $r['mode'],
            'mode',
            array_merge(
                [''],
                \OmegaUp\Controllers\Problem::VALID_SORTING_MODES
            ),
            false
        );
        \OmegaUp\Validators::validateOptionalNumber($r['page'], 'page');
        \OmegaUp\Validators::validateInEnum(
            $r['order_by'],
            'order_by',
            array_merge(
                [''],
                \OmegaUp\Controllers\Problem::VALID_SORTING_COLUMNS
            ),
            false
        );
        \OmegaUp\Validators::validateInEnum(
            $r['language'],
            'language',
            array_merge(
                ['all', ''],
                \OmegaUp\Controllers\Problem::VALID_LANGUAGES
            ),
            false
        );

        $tags = $r->getStringList('tag', []);

        $keyword = substr(strval($r['query']), 0, 256);
        if (!$keyword) {
            $keyword = '';
        }
        \OmegaUp\Validators::validateOptionalNumber(
            $r['min_difficulty'],
            'min_difficulty'
        );
        \OmegaUp\Validators::validateOptionalNumber(
            $r['max_difficulty'],
            'max_difficulty'
        );
        \OmegaUp\Validators::validateOptionalNumber(
            $r['min_visibility'],
            'min_visibility'
        );
        $minVisibility = empty(
            $r['min_visibility']
        ) ? \OmegaUp\ProblemParams::VISIBILITY_PUBLIC : intval(
            $r['min_visibility']
        );
        $difficultyRange = null;
        if (isset($r['difficulty_range'])) {
            [$minDifficulty, $maxDifficulty] = explode(
                ',',
                strval(
                    $r['difficulty_range']
                )
            );
            $difficultyRange = self::getDifficultyRange(
                intval($minDifficulty),
                intval($maxDifficulty)
            );
        }
        if (isset($r['only_karel'])) {
            $programmingLanguages = ['kp', 'kj'];
        } elseif (isset($r['programming_languages'])) {
            $programmingLanguages = explode(
                ',',
                strval(
                    $r['programming_languages']
                )
            );
        } else {
            $programmingLanguages = [];
        }

        return [
            'mode' => strval($r['mode']),
            'page' => $r['page'],
            'orderBy' => strval($r['order_by']),
            'language' => strval($r['language']),
            'tags' => $tags,
            'keyword' => $keyword,
            'requireAllTags' => !isset(
                $r['require_all_tags']
            ) ? !isset(
                $r['some_tags']
            ) : boolval($r['require_all_tags']),
            'programmingLanguages' => $programmingLanguages,
            'difficultyRange' => $difficultyRange,
            'minVisibility' => $minVisibility,
        ];
    }

    /**
     * List of public and user's private problems
     *
     * @param \OmegaUp\Request $r
     * @return array{results: array{alias: string, difficulty: float|null, difficulty_histogram: list<int>, points: float, quality: float|null, quality_histogram: list<int>, ratio: float, score: float, tags: array{source: string, name: string}[], title: string, visibility: int}[], total: int}
     */
    public static function apiList(\OmegaUp\Request $r) {
        // Authenticate request
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing, we allow unauthenticated users to use this API
        }
        [
            'offset' => $offset,
            'rowcount' => $rowcount,
        ] = self::validateList(
            isset($r['offset']) ? intval($r['offset']) : null,
            isset($r['rowcount']) ? intval($r['rowcount']) : null,
            isset($r['page']) ? intval($r['page']) : null
        );
        [
            'mode' => $mode,
            'page' => $page,
            'orderBy' => $orderBy,
            'language' => $language,
            'tags' => $tags,
            'keyword' => $keyword,
            'requireAllTags' => $requireAllTags,
            'programmingLanguages' => $programmingLanguages,
            'difficultyRange' => $difficultyRange,
            'minVisibility' => $minVisibility,
        ] = self::validateListParams($r);

        return self::getList(
            $page ?: 1,
            $language ?: 'all',
            $orderBy ?: 'problem_id',
            $mode ?: 'desc',
            $offset,
            $rowcount,
            $tags,
            $keyword,
            $requireAllTags,
            $programmingLanguages,
            $minVisibility,
            $difficultyRange,
            $r->identity,
            $r->user
        );
    }

    /**
     * @param list<string> $tags
     * @param array{0: int, 1: int}|null $difficultyRange
     * @param list<string> $programmingLanguages
     * @return array{results: array{alias: string, difficulty: float|null, difficulty_histogram: list<int>, points: float, quality: float|null, quality_histogram: list<int>, ratio: float, score: float, tags: array{source: string, name: string}[], title: string, visibility: int}[], total: int}
     */
    private static function getList(
        int $page,
        string $language,
        string $orderBy,
        string $mode,
        ?int $offset,
        ?int $rowcount,
        array $tags,
        string $keyword,
        bool $requireAllTags,
        array $programmingLanguages,
        int $minVisibility,
        ?array $difficultyRange,
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Users $user
    ) {
        $authorIdentityId = null;
        $authorUserId = null;
        // There are basically three types of users:
        // - Non-logged in users: Anonymous
        // - Logged in users with normal permissions: Normal
        // - Logged in users with administrative rights: Admin
        $identityType = IDENTITY_ANONYMOUS;
        if (!is_null($identity)) {
            $authorIdentityId = intval($identity->identity_id);
            if (!is_null($user)) {
                $authorUserId = intval($user->user_id);
            }

            if (
                \OmegaUp\Authorization::isSystemAdmin($identity) ||
                \OmegaUp\Authorization::hasRole(
                    $identity,
                    \OmegaUp\Authorization::SYSTEM_ACL,
                    \OmegaUp\Authorization::REVIEWER_ROLE
                )
            ) {
                $identityType = IDENTITY_ADMIN;
            } else {
                $identityType = IDENTITY_NORMAL;
            }
        }

        if (is_null($offset) || is_null($rowcount)) {
            $offset = ($page - 1) * PROBLEMS_PER_PAGE;
            $rowcount = PROBLEMS_PER_PAGE;
        }

        $total = 0;
        $problems = \OmegaUp\DAO\Problems::byIdentityType(
            $identityType,
            $language,
            $orderBy,
            $mode,
            $offset,
            $rowcount,
            $keyword,
            $authorIdentityId,
            $authorUserId,
            $tags,
            $minVisibility,
            $requireAllTags,
            $programmingLanguages,
            $difficultyRange,
            $total
        );
        return [
            'total' => $total,
            'results' => $problems,
        ];
    }

    /**
     * Returns a list of problems where current user has admin rights (or is
     * the owner).
     *
     * @return array{problems: list<array{tags: list<array{name: string, source: string}>}>}
     */
    public static function apiAdminList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset(
            $r['page_size']
        ) ? intval(
            $r['page_size']
        ) : \OmegaUp\Controllers\Problem::PAGE_SIZE);

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

        $hiddenTags = \OmegaUp\DAO\Users::getHideTags(
            $r->identity->identity_id
        );
        foreach ($problems as $problem) {
            $problemArray = $problem->asArray();
            $problemArray['tags'] = $hiddenTags ? [] : \OmegaUp\DAO\Problems::getTagsForProblem(
                $problem,
                false
            );
            $addedProblems[] = $problemArray;
        }

        return [
            'problems' => $addedProblems,
        ];
    }

    /**
     * Gets a list of problems where current user is the owner
     *
     * @return array{problems: list<array{tags: array<array-key, array{name: string, source: string}>}>}
     */
    public static function apiMyList(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        [
            'offset' => $offset,
            'rowcount' => $pageSize,
        ] = self::validateList(
            isset($r['offset']) ? intval($r['offset']) : null,
            isset($r['rowcount']) ? intval($r['rowcount']) : null,
            isset($r['page']) ? intval($r['page']) : null
        );

        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = isset($r['page']) ? intval($r['page']) : 1;

        $problems = \OmegaUp\DAO\Problems::getAllProblemsOwnedByUser(
            $r->user->user_id,
            $page,
            $pageSize ?? 1000
        );

        $addedProblems = [];

        $hiddenTags = \OmegaUp\DAO\Users::getHideTags(
            $r->identity->identity_id
        );
        foreach ($problems as $problem) {
            $problemArray = $problem->asArray();
            $problemArray['tags'] = $hiddenTags ? [] : \OmegaUp\DAO\Problems::getTagsForProblem(
                $problem,
                false
            );
            $addedProblems[] = $problemArray;
        }

        return [
            'problems' => $addedProblems,
        ];
    }

    /**
     * Returns the best score for a problem
     *
     * @return array{score: float}
     */
    public static function apiBestScore(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        // Uses same params as apiDetails, except for lang, which is optional
        $problem = self::validateDetails($r);

        // If username is set in the request, we use that identity as target.
        // else, we query using current_user
        $identity = self::resolveTargetIdentity($r);

        if (is_null($problem['problem'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        return [
            'score' => self::bestScore(
                $problem['problem'],
                !is_null(
                    $r['problemset_id']
                ) ? intval(
                    $r['problemset_id']
                ) : $r['problemset_id'],
                strval($r['contest_alias']),
                $r->identity->identity_id,
                $identity
            ),
        ];
    }

    /**
     * Returns the best score of a problem.
     * If problemset is set, will only look for
     * runs inside the contest.
     *
     * Authentication is expected to be performed earlier.
     * @return float
     */
    private static function bestScore(
        \OmegaUp\DAO\VO\Problems $problem,
        ?int $problemsetId,
        ?string $contestAlias,
        int $currentLoggedIdentityId,
        ?\OmegaUp\DAO\VO\Identities $identity = null
    ): float {
        $currentIdentityId = (is_null(
            $identity
        ) ? $currentLoggedIdentityId : $identity->identity_id);

        $score = 0.0;
        // Add best score info
        $problemset = self::validateProblemset(
            $problem,
            $problemsetId,
            $contestAlias
        );

        if (is_null($problemset)) {
            $score = floatval(\OmegaUp\DAO\Runs::getBestProblemScore(
                intval($problem->problem_id),
                intval($currentIdentityId)
            ));
        } else {
            $score = floatval(\OmegaUp\DAO\Runs::getBestProblemScoreInProblemset(
                intval($problemset['problemset']->problemset_id),
                intval($problem->problem_id),
                intval($currentIdentityId)
            ));
        }
        return round($score, 2);
    }

    /**
     * Save language data for a problem.
     *
     * @return void
     */
    private static function updateLanguages(\OmegaUp\DAO\VO\Problems $problem): void {
        if (is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);
        try {
            \OmegaUp\DAO\DAO::transBegin();

            // Removing existing data
            $deletedLanguages = \OmegaUp\DAO\ProblemsLanguages::deleteProblemLanguages(new \OmegaUp\DAO\VO\ProblemsLanguages([
                'problem_id' => $problem->problem_id,
            ]));

            foreach (\OmegaUp\DAO\Languages::getAll() as $lang) {
                if (
                    !$problemArtifacts->exists(
                        "statements/{$lang->name}.markdown"
                    )
                ) {
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
     * @return array{limits: array{ExtraWallTime: string, MemoryLimit: string, OutputLimit: string, OverallWallTimeLimit: string, TimeLimit: string}, validator: array{name: string, tolerance: float}} The Problem settings object.
     */
    private static function getDefaultProblemSettings(): array {
        return [
            'limits' => [
                'ExtraWallTime' => '0s',
                'MemoryLimit' => '64MiB',
                'OutputLimit' => '10240KiB',
                'OverallWallTimeLimit' => '30s',
                'TimeLimit' => '1s',
            ],
            'validator' => [
                'name' => \OmegaUp\ProblemParams::VALIDATOR_TOKEN,
                'tolerance' => 1e-9,
            ],
        ];
    }

    /**
     * Updates the Problem's settings with the values from the request.
     *
     * @param array{limits: array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}, validator: array{name: string, tolerance: float, limits?: array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}}} $problemSettings the original problem settings.
     * @param \OmegaUp\ProblemParams $params the params
     * @psalm-suppress ReferenceConstraintViolation for some reason, psalm cannot correctly infer the type for $problemSettings['validator']['limit']
     */
    private static function updateProblemSettings(
        array &$problemSettings,
        \OmegaUp\ProblemParams $params
    ): void {
        $problemSettings['limits']['ExtraWallTime'] = "{$params->extraWallTime}ms";
        if (!is_null($params->memoryLimit)) {
            $problemSettings['limits']['MemoryLimit'] = "{$params->memoryLimit}KiB";
        }
        $problemSettings['limits']['OutputLimit'] = "{$params->outputLimit}";
        $problemSettings['limits']['OverallWallTimeLimit'] = "{$params->overallWallTimeLimit}ms";
        if (!is_null($params->timeLimit)) {
            $problemSettings['limits']['TimeLimit'] = "{$params->timeLimit}ms";
        }
        if (!is_null($params->validator)) {
            $problemSettings['validator']['name'] = "{$params->validator}";
        }
        if ($problemSettings['validator']['name'] == 'custom') {
            if (empty($problemSettings['validator']['limits'])) {
                $problemSettings['validator']['limits'] = [
                    'ExtraWallTime' => '0s',
                    'MemoryLimit' => '256MiB',
                    'OutputLimit' => '10KiB',
                    'OverallWallTimeLimit' => '5s',
                    'TimeLimit' => '30s',
                ];
            }
            $problemSettings['validator']['limits']['TimeLimit'] = "{$params->validatorTimeLimit}ms";
        } else {
            if (!empty($problemSettings['validator']['limits'])) {
                unset($problemSettings['validator']['limits']);
            }
        }
    }

    /**
     * @return array{isSysadmin: bool, privateProblemsAlert: bool}
     */
    public static function getProblemsMineInfoForSmarty(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        $privateProblemsAlert = false;
        {
            $scopedSession = \OmegaUp\Controllers\Session::getSessionManagerInstance()->sessionStart();
            $privateProblemsAlert = (
                !isset($_SESSION['private_problems_alert']) &&
                \OmegaUp\DAO\Problems::getPrivateCount($r->user) > 0
            );
        if ($privateProblemsAlert) {
            $_SESSION['private_problems_alert'] = true;
        }
        }
        return [
            'isSysadmin' => \OmegaUp\Authorization::isSystemAdmin($r->identity),
            'privateProblemsAlert' => $privateProblemsAlert,
        ];
    }

    /**
     * @return array{input_limit: string, karel_problem: bool, memory_limit: string, overall_wall_time_limit: string, payload: array{accepted: int, admin?: bool, alias: string, commit: string, creation_date: int, difficulty: float|null, email_clarifications: bool, histogram: array{difficulty: float, difficulty_histogram: null|string, quality: float, quality_histogram: null|string}, input_limit: int, languages: list<string>, order: string, points: float, preferred_language?: string, problemsetter?: array{creation_date: int, name: string, username: string}, runs?: list<array{alias: string, contest_score: float|null, guid: string, language: string, memory: int, penalty: int, runtime: int, score: float, status: string, submit_delay: int, time: int, username: string, verdict: string}>, score: float, settings: array{cases: array<string, mixed>, limits: array{MemoryLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}, validator: mixed}, shouldShowFirstAssociatedIdentityRunWarning?: bool, solution_status?: string, solvers?: list<array{language: string, memory: float, runtime: float, time: int, username: string}>, source?: string, statement: array{images: array<string, string>, language: string, markdown: string}, submissions: int, title: string, user: array{admin: bool, logged_in: bool}, version: string, visibility: int, visits: int}, points: float, problem_admin: bool, problem_alias: string, problemsetter: array{creation_date: int, name: string, username: string}|null, quality_payload: array{can_nominate_problem?: bool, dismissed: bool, dismissedBeforeAC?: bool, language?: string, nominated: bool, nominatedBeforeAC?: bool, problem_alias?: string, solved: bool, tried: bool}, qualitynomination_reportproblem_payload: array{problem_alias: string}, solvers: list<array{language: string, memory: float, runtime: float, time: int, username: string}>, source: null|string, time_limit: string, title: string, visibility: int}
     */
    public static function getProblemDetailsForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureBool('prevent_problemset_open', /*required=*/false);
        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['lang'], 'lang');
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['auth_token'],
            'auth_token'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        [
            'problem' => $problem,
            'problemset' => $problemset,
        ] = self::getValidProblemAndProblemset($r);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Get problem details from API
        $details = self::getProblemDetails(
            $r->identity,
            $problem,
            $problemset,
            strval($r['lang']),
            /*showSolvers=*/true,
            $r['auth_token'],
            boolval($r['prevent_problemset_open']) === true,
            $r['contest_alias']
        );
        if (is_null($details)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $memoryLimit = intval(
            $details['settings']['limits']['MemoryLimit']
        ) / 1024 / 1024;
        $result = [
            'problem_alias' => $details['alias'],
            'visibility' => $details['visibility'],
            'source' => (
                isset($details['source']) ?
                strval($details['source']) :
                null
            ),
            'problemsetter' => $details['problemsetter'] ?? null,
            'title' => $details['title'],
            'points' => $details['points'],
            'time_limit' => $details['settings']['limits']['TimeLimit'],
            'overall_wall_time_limit' => $details['settings']['limits']['OverallWallTimeLimit'],
            'memory_limit' => "{$memoryLimit} MiB",
            'input_limit' => ($details['input_limit'] / 1024) . ' KiB',
            'solvers' => isset($details['solvers']) ? $details['solvers'] : [],
            'quality_payload' => [
                'solved' => false,
                'tried' => false,
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
        if (
            isset($details['settings']['cases']) &&
            isset($details['settings']['cases']['sample']) &&
            isset($result['settings']['cases']['sample']['in'])
        ) {
            $result['sample_input'] = strval(
                $result['settings']['cases']['sample']['in']
            );
        }
        $details['histogram'] = [
            'difficulty_histogram' => $problem->difficulty_histogram,
            'quality_histogram' => $problem->quality_histogram,
            'quality' => floatval($problem->quality),
            'difficulty' => floatval($problem->difficulty),
        ];
        $details['user'] = ['logged_in' => false, 'admin' => false];
        $result['payload'] = $details;

        if (
            is_null($r->identity)
            || is_null($r->identity->user_id)
            || is_null($problem->problem_id)
        ) {
            return $result;
        }
        $nominationStatus = \OmegaUp\DAO\QualityNominations::getNominationStatusForProblem(
            $problem->problem_id,
            $r->identity->user_id
        );
        $isProblemAdmin = \OmegaUp\Authorization::isProblemAdmin(
            $r->identity,
            $problem
        );

        $nominationStatus['tried'] = false;
        $nominationStatus['solved'] = false;

        foreach ($details['runs'] ?? [] as $run) {
            if ($run['verdict'] === 'AC') {
                $nominationStatus['solved'] = true;
                break;
            } elseif ($run['verdict'] !== 'JE' && $run['verdict'] !== 'VE' && $run['verdict'] !== 'CE') {
                $nominationStatus['tried'] = true;
            }
        }
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
        $result['payload']['solution_status'] = self::getProblemSolutionStatus(
            $problem,
            $r->identity
        );
        return $result;
    }

    /**
     * @return array{KEYWORD: string, LANGUAGE: string, MODE: string, ORDER_BY: string, current_tags: string[]|string, pager_items: array{class: string, label: string, url: string}[], problems: array{alias: string, difficulty: float|null, difficulty_histogram: list<int>, points: float, quality: float|null, quality_histogram: list<int>, ratio: float, score: float, tags: array{source: string, name: string}[], title: string, visibility: int}[]}
     */
    public static function getProblemListForSmarty(
        \OmegaUp\Request $r
    ): array {
        // Authenticate request
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing, we allow unauthenticated users to use this API
        }
        [
            'offset' => $offset,
            'rowcount' => $rowcount,
        ] = self::validateList(
            isset($r['offset']) ? intval($r['offset']) : null,
            isset($r['rowcount']) ? intval($r['rowcount']) : null,
            isset($r['page']) ? intval($r['page']) : null
        );
        [
            'mode' => $mode,
            'page' => $page,
            'orderBy' => $orderBy,
            'language' => $language,
            'tags' => $tags,
            'keyword' => $keyword,
            'requireAllTags' => $requireAllTags,
            'programmingLanguages' => $programmingLanguages,
            'difficultyRange' => $difficultyRange,
            'minVisibility' => $minVisibility,
        ] = self::validateListParams($r);

        $response = self::getList(
            $page ?: 1,
            $language ?: 'all',
            $orderBy ?: 'problem_id',
            $mode ?: 'desc',
            $offset,
            $rowcount,
            $tags,
            $keyword,
            $requireAllTags,
            $programmingLanguages,
            $minVisibility,
            $difficultyRange,
            $r->identity,
            $r->user
        );

        $params = [
            'query' => $keyword,
            'language' => $language,
            'order_by' => $orderBy,
            'mode' => $mode,
            'tag' => $tags
        ];

        $pagerItems = \OmegaUp\Pager::paginate(
            $response['total'],
            $page ?: 1,
            '/problem/list/',
            5,
            $params
        );

        return [
            'KEYWORD' => $keyword,
            'MODE' => $mode,
            'ORDER_BY' => $orderBy,
            'LANGUAGE' => $language,
            'problems' => $response['results'],
            'current_tags' => $tags,
            'pager_items' => $pagerItems,
        ];
    }

    /**
     * @return array{IS_UPDATE: bool, LOAD_MATHJAX: bool, LOAD_PAGEDOWN: bool, STATUS_SUCCESS: null|string}
     */
    public static function getProblemEditDetailsForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();

        if (!isset($r['request'])) {
            return [
                'IS_UPDATE' => true,
                'LOAD_MATHJAX' => true,
                'LOAD_PAGEDOWN' => true,
                'STATUS_SUCCESS' => '',
            ];
        }
        if ($r['request'] === 'submit') {
            // Validate commit message.
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['message'],
                'message'
            );
            self::updateProblem(
                $r->identity,
                $r->user,
                self::convertRequestToProblemParams($r, /*$isRequired=*/ false),
                strval($r['message']),
                !is_null(
                    $r['update_published']
                ) ? strval(
                    $r['update_published']
                ) : \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
                boolval($r['redirect'])
            );
        } elseif ($r['request'] === 'markdown') {
            self::updateStatement(new \OmegaUp\Request([
                'problem_alias' => $r['problem_alias'] ?? null,
                'statement' => $r['wmd-input-statement'] ?? null,
                'message' => $r['message'] ?? null,
                'lang' => $r['statement-language'] ?? null,
            ]));
        }

        return [
            'IS_UPDATE' => true,
            'LOAD_MATHJAX' => true,
            'LOAD_PAGEDOWN' => true,
            'STATUS_SUCCESS' => \OmegaUp\Translations::getInstance()->get(
                'problemEditUpdatedSuccessfully'
            ),
        ];
    }

    /**
     * @return array{ALIAS: string, EMAIL_CLARIFICATIONS: string, EXTRA_WALL_TIME: string, INPUT_LIMIT: string, LANGUAGES: string, MEMORY_LIMIT: string, OUTPUT_LIMIT: string, OVERALL_WALL_TIME_LIMIT: string, SELECTED_TAGS: string, SOURCE: string, TIME_LIMIT: string, TITLE: string, VALIDATOR: string, VALIDATOR_TIME_LIMIT: string, VISIBILITY: string}
     */
    public static function getProblemNewForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();

        if (isset($r['request']) && ($r['request'] === 'submit')) {
            // HACK to prevent fails in validateCreateOrUpdate
            $r['problem_alias'] = strval($r['alias']);

            try {
                self::createProblem(
                    $r->user,
                    $r->identity,
                    self::convertRequestToProblemParams($r)
                );
                header("Location: /problem/{$r['problem_alias']}/edit/");
                die();
            } catch (\OmegaUp\Exceptions\ApiException $e) {
                /** @var array{error?: string} */
                $response = $e->asResponseArray();
                if (empty($response['error'])) {
                    $statusError = '{error}';
                } else {
                    $statusError = $response['error'];
                }
                return [
                    'TITLE' => strval($r['title']),
                    'ALIAS' => strval($r['problem_alias']),
                    'VALIDATOR' => strval($r['validator']),
                    'TIME_LIMIT' => strval($r['time_limit']),
                    'VALIDATOR_TIME_LIMIT' => strval(
                        $r['validator_time_limit']
                    ),
                    'OVERALL_WALL_TIME_LIMIT' => strval(
                        $r['overall_wall_time_limit']
                    ),
                    'EXTRA_WALL_TIME' => strval($r['extra_wall_time']),
                    'OUTPUT_LIMIT' => strval($r['output_limit']),
                    'INPUT_LIMIT' => strval($r['input_limit']),
                    'MEMORY_LIMIT' => strval($r['memory_limit']),
                    'EMAIL_CLARIFICATIONS' => strval(
                        $r['email_clarifications']
                    ),
                    'SOURCE' => strval($r['source']),
                    'VISIBILITY' => strval($r['visibility']),
                    'LANGUAGES' => strval($r['languages']),
                    'SELECTED_TAGS' => strval($r['selected_tags']),
                    'STATUS_ERROR' => $statusError,
                ];
            }
        }
        return [
            'TITLE' => '',
            'ALIAS' => '',
            'VALIDATOR' => \OmegaUp\ProblemParams::VALIDATOR_TOKEN,
            'TIME_LIMIT' => '1000',
            'VALIDATOR_TIME_LIMIT' => '1000',
            'OVERALL_WALL_TIME_LIMIT' => '60000',
            'EXTRA_WALL_TIME' => '0',
            'OUTPUT_LIMIT' => '10240',
            'INPUT_LIMIT' => '10240',
            'MEMORY_LIMIT' => '32768',
            'EMAIL_CLARIFICATIONS' => '0',
            'SOURCE' => '',
            'VISIBILITY' => '0',
            'LANGUAGES' => join(
                ',',
                \OmegaUp\Controllers\Run::DEFAULT_LANGUAGES
            ),
            'SELECTED_TAGS' => '',
            'IS_UPDATE' => false,
        ];
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
        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            strval(
                $problem->alias
            ),
            $problem->commit
        );
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
            function () use ($problem): bool {
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
    ): string {
        $exists = self::getProblemSolutionExistence($problem);
        if (!$exists) {
            return self::SOLUTION_NOT_FOUND;
        }
        if (
            \OmegaUp\Authorization::canViewProblemSolution(
                $identity,
                $problem
            )
        ) {
            return self::SOLUTION_UNLOCKED;
        }
        return self::SOLUTION_LOCKED;
    }

    /**
     * @return null|array{0: int, 1: int}
     */
    private static function getDifficultyRange(
        ?int $minDifficulty,
        ?int $maxDifficulty
    ) {
        if (
            is_null($minDifficulty) ||
            is_null($maxDifficulty) ||
            $minDifficulty > $maxDifficulty ||
            $minDifficulty < 0 ||
            $minDifficulty > 4 ||
            $maxDifficulty < 0 ||
            $maxDifficulty > 4
        ) {
            return null;
        }
        return [$minDifficulty, $maxDifficulty];
    }

    public static function apiTemplate(\OmegaUp\Request $r): void {
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        \OmegaUp\Validators::validateStringOfLengthInRange(
            $r['commit'],
            'commit',
            40,
            40
        );
        if (
            preg_match(
                '/^[0-9a-f]{40}$/',
                $r['commit']
            ) !== 1
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'commit'
            );
        }
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['filename'],
            'filename'
        );
        if (
            preg_match(
                '/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_.-]+$/',
                $r['filename']
            ) !== 1
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'filename'
            );
        }

        self::regenerateTemplates($r['problem_alias'], $r['commit']);

        //The noredirect=1 part lets nginx know to not call us again if the file is not found.
        header(
            'Location: ' . TEMPLATES_URL_PATH . "{$r['problem_alias']}/{$r['commit']}/{$r['filename']}?noredirect=1"
        );
        header('HTTP/1.1 303 See Other');
        die();
    }

    public static function regenerateTemplates(
        string $problemAlias,
        string $commit
    ): void {
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemAlias
        );
        if (is_null($problem) || is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        $problemDeployer = new \OmegaUp\ProblemDeployer($problem->alias);
        $problemDeployer->generateLibinteractiveTemplates($commit);
    }

    public static function apiImage(\OmegaUp\Request $r): void {
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        \OmegaUp\Validators::validateStringOfLengthInRange(
            $r['object_id'],
            'object_id',
            40,
            40
        );
        if (
            preg_match(
                '/^[0-9a-f]{40}$/',
                $r['object_id']
            ) !== 1
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'object_id'
            );
        }
        \OmegaUp\Validators::validateInEnum(
            $r['extension'],
            'extension',
            self::IMAGE_EXTENSIONS
        );

        self::regenerateImage(
            $r['problem_alias'],
            $r['object_id'],
            strval($r['extension'])
        );

        //The noredirect=1 part lets nginx know to not call us again if the file is not found.
        header(
            'Location: ' . IMAGES_URL_PATH . "{$r['problem_alias']}/{$r['object_id']}.{$r['extension']}?noredirect=1"
        );
        header('HTTP/1.1 303 See Other');
        die();
    }

    public static function regenerateImage(
        string $problemAlias,
        string $objectId,
        string $extension
    ): void {
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemAlias
        );
        if (is_null($problem) || is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            $problem->alias,
            $objectId
        );
        $imagePath = (
            IMAGES_PATH . "{$problem->alias}/{$objectId}.{$extension}"
        );
        @mkdir(IMAGES_PATH . $problem->alias, 0755, true);
        file_put_contents(
            $imagePath,
            $problemArtifacts->getByRevision()
        );
    }

    /**
     * @return array{smartyProperties: array{error?: string, error_field?: string}, template: string}
     */
    public static function getLibinteractiveGenForSmarty(\OmegaUp\Request $r): array {
        if (count($r) === 0) {
            // \OmegaUp\Request does not support empty().
            return [
                'smartyProperties' => [],
                'template' => 'libinteractive.gen.tpl',
            ];
        }
        try {
            \OmegaUp\Validators::validateInEnum(
                $r['language'],
                'language',
                ['c', 'cpp', 'java']
            );
            \OmegaUp\Validators::validateInEnum(
                $r['os'],
                'os',
                ['unix', 'windows']
            );
            \OmegaUp\Validators::validateValidAlias(
                $r['name'],
                'name'
            );
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['idl'],
                'idl'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            return [
                'smartyProperties' => [
                    'error' => \OmegaUp\Translations::getInstance()->get(
                        'parameterInvalid'
                    ) ?? 'parameterInvalid',
                    'error_field' => strval($e->parameter),
                ],
                'template' => 'libinteractive.gen.tpl',
            ];
        }
        $dirname = \OmegaUp\FileHandler::TempDir(
            sys_get_temp_dir(),
            'libinteractive'
        );
        try {
            file_put_contents("{$dirname}/{$r['name']}.idl", $r['idl']);
            $args = [
                '/usr/bin/java',
                '-jar',
                '/usr/share/java/libinteractive.jar',
                'generate',
                "{$r['name']}.idl",
                $r['language'],
                $r['language'],
                '--makefile',
                "--{$r['os']}",
            ];
            $descriptorspec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ];
            $cmd = join(' ', array_map('escapeshellarg', $args));
            $proc = proc_open(
                $cmd,
                $descriptorspec,
                $pipes,
                $dirname,
                ['LANG' => 'en_US.UTF-8']
            );
            if (!is_resource($proc)) {
                return [
                    'smartyProperties' => [
                        'error' => strval(error_get_last()),
                    ],
                    'template' => 'libinteractive.gen.tpl',
                ];
            }
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $err = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $retval = proc_close($proc);

            if ($retval != 0) {
                return [
                    'smartyProperties' => [
                        'error' => "{$output}{$err}",
                    ],
                    'template' => 'libinteractive.gen.tpl',
                ];
            }
            $zip = new \ZipArchive();
            $zip->open(
                "{$dirname}/interactive.zip",
                \ZipArchive::CREATE | \ZipArchive::OVERWRITE
            );

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dirname),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            /** @var \SplFileInfo $file */
            foreach ($files as $name => $file) {
                if ($file->isDir()) {
                    continue;
                }
                if ($file->getFilename() == 'interactive.zip') {
                    continue;
                }

                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($dirname) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }

            $zip->close();

            header('Content-Type: application/zip');
            header(
                "Content-Disposition: attachment; filename={$r['name']}.zip"
            );
            readfile("{$dirname}/interactive.zip");
            \OmegaUp\FileHandler::deleteDirRecursively($dirname);
            die();
        } catch (\Exception $e) {
            return [
                'smartyProperties' => [
                    'error' => strval($e),
                ],
                'template' => 'libinteractive.gen.tpl',
            ];
        } finally {
            \OmegaUp\FileHandler::deleteDirRecursively($dirname);
        }
    }
}
