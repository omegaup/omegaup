<?php

 namespace OmegaUp\Controllers;

/**
 * ProblemsController
 *
 * @psalm-type Clarification=array{answer: null|string, author: null|string, clarification_id: int, contest_alias: null|string, message: string, problem_alias: string, public: bool, receiver: null|string, time: \OmegaUp\Timestamp}
 * @psalm-type NominationStatus=array{alreadyReviewed: bool, dismissed: bool, dismissedBeforeAC: bool, nominated: bool, nominatedBeforeAC: bool, solved: bool, tried: bool}
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 * @psalm-type LimitsSettings=array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}
 * @psalm-type InteractiveSettings=array{idl: string, module_name: string, language: string, main_source: string, templates: array<string, string>}
 * @psalm-type ProblemSettings=array{cases: array<string, array{in: string, out: string, weight?: float}>, interactive?: InteractiveSettings, limits: LimitsSettings, validator: array{custom_validator?: array{language: string, limits?: LimitsSettings, source: string}, name: string, tolerance?: float}}
 * @psalm-type ProblemStatement=array{images: array<string, string>, language: string, markdown: string}
 * @psalm-type ProblemsetterInfo=array{classname: string, creation_date: \OmegaUp\Timestamp|null, name: string, username: string}
 * @psalm-type ProblemInfo=array{alias: string, karel_problem: bool, limits: array{input_limit: string, memory_limit: string, overall_wall_time_limit: string, time_limit: string}, points: float, problem_id: int, problemsetter: ProblemsetterInfo|null, quality_seal: bool, sample_input: null|string, settings: ProblemSettings, source: null|string, statement: ProblemStatement, title: string, visibility: int}
 * @psalm-type UserInfoForProblem=array{loggedIn: bool, admin: bool, reviewer: bool}
 * @psalm-type RunMetadata=array{verdict: string, time: float, sys_time: int, wall_time: float, memory: int}
 * @psalm-type ProblemListItem=array{alias: string, difficulty: float|null, difficulty_histogram: list<int>, points: float, problem_id: int, quality: float|null, quality_histogram: list<int>, quality_seal: bool, ratio: float, score: float, tags: list<array{name: string, source: string}>, title: string, visibility: int}
 * @psalm-type Statements=array<string, string>
 * @psalm-type Run=array{guid: string, language: string, status: string, verdict: string, runtime: int, penalty: int, memory: int, score: float, contest_score: float|null, time: \OmegaUp\Timestamp, submit_delay: int, type: null|string, username: string, classname: string, alias: string, country: string, contest_alias: null|string}
 * @psalm-type ProblemDetails=array{accepted: int, admin?: bool, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, difficulty: float|null, email_clarifications: bool, input_limit: int, languages: list<string>, order: string, points: float, preferred_language?: string, problem_id: int, problemsetter?: ProblemsetterInfo, quality_seal: bool, runs?: list<Run>, score: float, settings: ProblemSettings, solvers?: list<array{language: string, memory: float, runtime: float, time: \OmegaUp\Timestamp, username: string}>, source?: string, statement: ProblemStatement, submissions: int, title: string, version: string, visibility: int, visits: int}
 * @psalm-type StatsPayload=array{alias: string, entity_type: string, cases_stats?: array<string, int>, pending_runs: list<string>, total_runs: int, verdict_counts: array<string, int>, max_wait_time?: \OmegaUp\Timestamp|null, max_wait_time_guid?: null|string, distribution?: array<int, int>, size_of_bucket?: float, total_points?: float}
 * @psalm-type SelectedTag=array{public: bool, tagname: string}
 * @psalm-type ProblemAdmin=array{role: string, username: string}
 * @psalm-type ProblemGroupAdmin=array{alias: string, name: string, role: string}
 * @psalm-type ProblemVersion=array{author: array{email?: string, name?: string, time: \OmegaUp\Timestamp|null}, commit: string, committer: array{email?: string, name?: string, time: \OmegaUp\Timestamp|null}, message?: string, parents?: list<string>, tree: array<string, string>|null, version: null|string}
 * @psalm-type ProblemEditPayload=array{admins: list<ProblemAdmin>, alias: string, allowUserAddTags: bool, emailClarifications: bool, extraWallTime: float, groupAdmins: list<ProblemGroupAdmin>, inputLimit: int, languages: string, levelTags?: list<string>, loadMathjax: true, log: list<ProblemVersion>, memoryLimit: float, outputLimit: int, overallWallTimeLimit: float, problemLevel?: null|string, problemsetter?: ProblemsetterInfo, publicTags?: list<string>, publishedRevision: ProblemVersion|null, selectedTags: list<array{public: bool, tagname: string}>, solution: ProblemStatement, source: string, statement: ProblemStatement, statusError?: string, statusSuccess: bool, tags: list<array{name: null|string}>, timeLimit: float, title: string, validLanguages: array<string, string>, validator: string, validatorTimeLimit: float|int, validatorTypes: array<string, null|string>, visibility: int, visibilityStatuses: array<string, int>}
 * @psalm-type ProblemDetailsPayload=array{accepted: int, admin?: bool, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, difficulty: float|null, email_clarifications: bool, histogram: array{difficulty: float, difficulty_histogram: null|string, quality: float, quality_histogram: null|string}, input_limit: int, languages: list<string>, order: string, points: float, preferred_language?: string, problem_id: int, problemsetter?: ProblemsetterInfo, quality_seal: bool, runs?: list<Run>, score: float, settings: ProblemSettings, shouldShowFirstAssociatedIdentityRunWarning?: bool, solution_status?: string, solvers?: list<array{language: string, memory: float, runtime: float, time: \OmegaUp\Timestamp, username: string}>, source?: string, statement: ProblemStatement, submissions: int, title: string, user: array{admin: bool, logged_in: bool, reviewer: bool}, version: string, visibility: int, visits: int}
 * @psalm-type ProblemDetailsv2Payload=array{nominationStatus?: NominationStatus, problem: ProblemInfo, user: UserInfoForProblem}
 * @psalm-type ProblemFormPayload=array{alias: string, allowUserAddTags: true, emailClarifications: bool, extraWallTime: int|string, inputLimit: int|string, languages: string, memoryLimit: int|string, message?: string, outputLimit: int|string, overallWallTimeLimit: int|string, selectedTags: list<SelectedTag>|null, source: string, statusError: string, tags: list<array{name: null|string}>, timeLimit: int|string, title: string, validLanguages: array<string, string>, validator: string, validatorTimeLimit: int|string, validatorTypes: array<string, null|string>, visibility: int, visibilityStatuses: array<string, int>}
 * @psalm-type ProblemsMineInfoPayload=array{isSysadmin: bool, privateProblemsAlert: bool, visibilityStatuses: array<string, int>}
 * @psalm-type ProblemListPayload=array{currentTags: list<string>, loggedIn: bool, pagerItems: list<PageItem>, problems: list<ProblemListItem>, keyword: string, language: string, mode: string, column: string, languages: list<string>, columns: list<string>, modes: list<string>, tagData: list<array{name: null|string}>, tags: list<string>}
 * @psalm-type RunsDiff=array{guid: string, new_score: float|null, new_status: null|string, new_verdict: null|string, old_score: float|null, old_status: null|string, old_verdict: null|string, problemset_id: int|null, username: string}
 * @psalm-type CommitRunsDiff=array<string, list<RunsDiff>>
 */
class Problem extends \OmegaUp\Controllers\Controller {
    // SOLUTION STATUS
    const SOLUTION_NOT_FOUND = 'not_found';
    const SOLUTION_UNLOCKED = 'unlocked';
    const SOLUTION_LOCKED = 'locked';

    const RESTRICTED_TAG_NAMES = [
        'problemRestrictedTagKarel',
        'problemRestrictedTagLanguage',
        'problemRestrictedTagOnlyOutput',
        'problemRestrictedTagInteractive'
    ];
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
        'creation_date',
        'problem_id',
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
    const PAGE_SIZE = 100;

    /**
     * Returns a ProblemParams instance from the Request values.
     *
     * @omegaup-request-param bool $allow_user_add_tags
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param mixed $extra_wall_time
     * @omegaup-request-param mixed $input_limit
     * @omegaup-request-param mixed $languages
     * @omegaup-request-param mixed $memory_limit
     * @omegaup-request-param mixed $output_limit
     * @omegaup-request-param mixed $overall_wall_time_limit
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $selected_tags
     * @omegaup-request-param string $show_diff
     * @omegaup-request-param mixed $source
     * @omegaup-request-param mixed $time_limit
     * @omegaup-request-param mixed $title
     * @omegaup-request-param mixed $update_published
     * @omegaup-request-param mixed $validator
     * @omegaup-request-param mixed $validator_time_limit
     * @omegaup-request-param mixed $visibility
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
            $params['email_clarifications'] = $r->ensureOptionalBool(
                'email_clarifications'
            ) ?? false;
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
        if (!is_null($r['show_diff'])) {
            $params['show_diff'] = strval($r['show_diff']);
        }
        if (!is_null($r['allow_user_add_tags'])) {
            $params['allow_user_add_tags'] = $r->ensureOptionalBool(
                'allow_user_add_tags'
            ) ?? false;
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
            if (
                ($problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING ||
                  $problem->visibility === \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_WARNING) &&
                    !is_null($params->visibility) &&
                    $problem->visibility !== $params->visibility &&
                    !\OmegaUp\Authorization::isQualityReviewer($identity)
            ) {
                if (
                    intval(
                        $params->visibility
                    ) !== \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_WARNING &&
                    intval(
                        $params->visibility
                    ) !== \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING
                ) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'qualityNominationProblemHasWarning',
                        'visibility'
                    );
                }
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
                ]
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
            /** @var list<array{tagname: string, public: bool}>|null */
            $selectedTags = !is_null($params->selectedTagsAsJSON) ? json_decode(
                $params->selectedTagsAsJSON,
                /*$assoc=*/true
            ) : null;
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
     *
     * @omegaup-request-param bool $allow_user_add_tags
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param mixed $extra_wall_time
     * @omegaup-request-param mixed $input_limit
     * @omegaup-request-param mixed $languages
     * @omegaup-request-param mixed $memory_limit
     * @omegaup-request-param mixed $output_limit
     * @omegaup-request-param mixed $overall_wall_time_limit
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $selected_tags
     * @omegaup-request-param string $show_diff
     * @omegaup-request-param mixed $source
     * @omegaup-request-param mixed $time_limit
     * @omegaup-request-param mixed $title
     * @omegaup-request-param mixed $update_published
     * @omegaup-request-param mixed $validator
     * @omegaup-request-param mixed $validator_time_limit
     * @omegaup-request-param mixed $visibility
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
            'show_diff' => $params->showDiff,
            'allow_user_add_tags' => $params->allowUserAddTags,
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
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $usernameOrEmail
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
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        $user = \OmegaUp\Controllers\User::resolveUser($r['usernameOrEmail']);
        if (is_null($user->user_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem) || is_null($problem->acl_id)) {
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
     * @omegaup-request-param mixed $group
     * @omegaup-request-param mixed $problem_alias
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
        \OmegaUp\Validators::validateValidAlias($r['group'], 'group');

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);
        if (is_null($group) || is_null($group->group_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem) || is_null($problem->acl_id)) {
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
     * Updates the problem level of a problem
     *
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $level_tag
     *
     * @return array{status: string}
     */
    public static function apiUpdateProblemLevel(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['level_tag'],
            'level_tag'
        );

        $r->ensureIdentity();

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $tag = null;
        if (!empty($r['level_tag'])) {
            $tag = \OmegaUp\DAO\Tags::getByName($r['level_tag']);

            if (is_null($tag)) {
                throw new \OmegaUp\Exceptions\NotFoundException('tag');
            }

            if (
                !in_array(
                    $tag->name,
                    \OmegaUp\Controllers\Tag::getLevelTags()
                )
            ) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'notProblemLevelTag',
                    'level_tag'
                );
            }
        }

        \OmegaUp\DAO\ProblemsTags::updateProblemLevel(
            $problem,
            $tag
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds a tag to a problem
     *
     * @omegaup-request-param mixed $name
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $public
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
        if (!$isPublic) {
            $tagName = \OmegaUp\Controllers\Tag::normalize($tagName);
        }

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
            if (in_array($tagName, self::RESTRICTED_TAG_NAMES)) {
                $tag = new \OmegaUp\DAO\VO\Tags([
                    'name' => $tagName,
                    'public' => false,
                ]);
            } else {
                if ($isPublic) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'newPublicTagsNotAllowed',
                        'public'
                    );
                }
                if (strpos($tagName, 'problemTag') === 0) {
                    // Starts with 'problemTag'
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'tagPrefixRestricted',
                        'name'
                    );
                }
                $tag = new \OmegaUp\DAO\VO\Tags([
                    'name' => $tagName,
                    'public' => false,
                ]);
            }
            \OmegaUp\DAO\Tags::create($tag);
        }

        \OmegaUp\DAO\ProblemsTags::replace(new \OmegaUp\DAO\VO\ProblemsTags([
            'problem_id' => $problem->problem_id,
            'tag_id' => $tag->tag_id,
            'public' => filter_var($isPublic, FILTER_VALIDATE_BOOLEAN),
            'source' => 'owner',
        ]));
    }

    /**
     * Removes an admin from a problem
     *
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $usernameOrEmail
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
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );
        if (is_null($identity->user_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem) || is_null($problem->acl_id)) {
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
     * @omegaup-request-param mixed $group
     * @omegaup-request-param mixed $problem_alias
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
        \OmegaUp\Validators::validateValidAlias($r['group'], 'group');

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);
        if (is_null($group) || is_null($group->group_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem) || is_null($problem->acl_id)) {
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
     * @omegaup-request-param mixed $name
     * @omegaup-request-param mixed $problem_alias
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
     * @omegaup-request-param mixed $problem_alias
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
        if (is_null($problem) || is_null($problem->problem_id)) {
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
     * @omegaup-request-param mixed $problem_alias
     *
     * @return array{admins: list<ProblemAdmin>, group_admins: list<ProblemGroupAdmin>}
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
     * @omegaup-request-param mixed $include_voted
     * @omegaup-request-param mixed $problem_alias
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
     * @omegaup-request-param mixed $problem_alias
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
     *
     * @return array{rejudged: bool}
     *
     * @throws \OmegaUp\Exceptions\ApiException
     *
     * @omegaup-request-param bool $allow_user_add_tags
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param mixed $extra_wall_time
     * @omegaup-request-param mixed $input_limit
     * @omegaup-request-param mixed $languages
     * @omegaup-request-param mixed $memory_limit
     * @omegaup-request-param mixed $message
     * @omegaup-request-param mixed $output_limit
     * @omegaup-request-param mixed $overall_wall_time_limit
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $redirect
     * @omegaup-request-param mixed $selected_tags
     * @omegaup-request-param string $show_diff
     * @omegaup-request-param mixed $source
     * @omegaup-request-param mixed $time_limit
     * @omegaup-request-param mixed $title
     * @omegaup-request-param mixed $update_published
     * @omegaup-request-param mixed $validator
     * @omegaup-request-param mixed $validator_time_limit
     * @omegaup-request-param mixed $visibility
     */
    public static function apiUpdate(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        $problemParams = self::convertRequestToProblemParams(
            $r,
            /*$isRequired=*/ false
        );

        // Validate commit message.
        \OmegaUp\Validators::validateStringNonEmpty($r['message'], 'message');
        [
            'rejudged' => $rejudged,
        ] = self::updateProblem(
            $r->identity,
            $r->user,
            $problemParams,
            $r['message'],
            $problemParams->updatePublished,
            boolval($r['redirect'])
        );
        return [
            'rejudged' => $rejudged,
        ];
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
        /**
         * @var string $key
         * @var mixed $value
         */
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $copy[$key] = self::arrayDeepCopy($value);
            } else {
                /** @psalm-suppress MixedAssignment Psalm cannot effectively allow assigning mixed this way */
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
                $milliseconds += floatval($match[1]) * 3600 * 1000;
            } elseif ($match[2] == 'm') {
                $milliseconds += floatval($match[1]) * 60 * 1000;
            } elseif ($match[2] == 's') {
                $milliseconds += floatval($match[1]) * 1000;
            } elseif ($match[2] == 'ms') {
                $milliseconds += floatval($match[1]);
            } elseif ($match[2] == 'us' || $match[2] == 'µs') {
                $milliseconds += floatval($match[1]) / 1000.0;
            } elseif ($match[2] == 'ns') {
                $milliseconds += floatval($match[1]) / (1000.0 * 1000.0);
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
     * Returns whether $a and $b are different.
     *
     * @param LimitsSettings $a
     * @param LimitsSettings $b
     */
    private static function diffLimitsSettings(array $a, array $b): bool {
        if (
            self::parseDuration($a['TimeLimit']) !==
            self::parseDuration($b['TimeLimit'])
        ) {
            return true;
        }
        if (
            self::parseDuration($a['ExtraWallTime']) !==
            self::parseDuration($b['ExtraWallTime'])
        ) {
            return true;
        }
        if (
            self::parseDuration($a['OverallWallTimeLimit']) !==
            self::parseDuration($b['OverallWallTimeLimit'])
        ) {
            return true;
        }
        if (
            self::parseSize($a['MemoryLimit']) !==
            self::parseSize($b['MemoryLimit'])
        ) {
            return true;
        }
        if (
            self::parseSize($a['OutputLimit']) !==
            self::parseSize($b['OutputLimit'])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Returns whether $a and $b are different.
     *
     * @param array{limits: LimitsSettings, validator: array{name: string, tolerance?: float, custom_validator?: array{source: string, language: string, limits?: LimitsSettings}}} $a
     * @param array{limits: LimitsSettings, validator: array{name: string, tolerance?: float, custom_validator?: array{source: string, language: string, limits?: LimitsSettings}}} $b
     */
    private static function diffProblemSettings(array $a, array $b): bool {
        if (self::diffLimitsSettings($a['limits'], $b['limits'])) {
            return true;
        }
        if ($a['validator']['name'] !== $b['validator']['name']) {
            return true;
        }
        if (
            isset($a['validator']['tolerance']) !==
            isset($b['validator']['tolerance'])
        ) {
            return true;
        }
        if (
            isset($a['validator']['tolerance']) &&
            isset($b['validator']['tolerance']) &&
            $a['validator']['tolerance'] !== $b['validator']['tolerance']
        ) {
            return true;
        }
        if (
            empty($a['validator']['custom_validator']) !==
            empty($b['validator']['custom_validator'])
        ) {
            return true;
        }
        if (
            empty($a['validator']['custom_validator']['limits']) !==
            empty($b['validator']['custom_validator']['limits'])
        ) {
            return true;
        }
        if (
            empty($a['validator']['custom_validator']['limits']) ||
            empty($b['validator']['custom_validator']['limits'])
        ) {
            // No further checks are necessary.
            return false;
        }
        return self::diffLimitsSettings(
            $a['validator']['custom_validator']['limits'],
            $b['validator']['custom_validator']['limits']
        );
    }

    /**
     * @return array{problem: \OmegaUp\DAO\VO\Problems, rejudged: bool}
     */
    public static function updateProblem(
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
            'inputLimit' => [
                'alias' => 'input_limit',
            ],
            'emailClarifications' => [
                'alias' => 'email_clarifications',
            ],
            'showDiff' => [
                'alias' => 'show_diff',
            ],
            'allowUserAddTags' => [
                'alias' => 'allow_user_add_tags',
            ],
            'source',
            'order',
            'languages' => [
                'transform' =>
                /**
                 * @param list<string>|string $value
                 */
                function ($value): string {
                    return is_array($value) ? join(',', $value) : $value;
                }
            ],
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
        $originalProblemSettings = self::arrayDeepCopy($problemSettings);
        self::updateProblemSettings($problemSettings, $params);
        $settingsUpdated = self::diffProblemSettings(
            $originalProblemSettings,
            $problemSettings
        );
        $acceptsSubmissions = $problem->languages !== '';
        $updatedStatementLanguages = [];
        $response['rejudged'] = false;

        try {
            //Begin transaction
            \OmegaUp\DAO\DAO::transBegin();

            $operation = \OmegaUp\ProblemDeployer::UPDATE_SETTINGS;
            if (
                isset($_FILES['problem_contents'])
                && is_array($_FILES['problem_contents'])
                && \OmegaUp\FileHandler::getFileUploader()->isUploadedFile(
                    strval($_FILES['problem_contents']['tmp_name'])
                )
            ) {
                $operation = \OmegaUp\ProblemDeployer::UPDATE_CASES;
            }
            if (
                $operation !== \OmegaUp\ProblemDeployer::UPDATE_SETTINGS ||
                $settingsUpdated
            ) {
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

        $response['problem'] = \OmegaUp\DAO\Problems::getByAlias(
            $params->problemAlias
        );
        if (is_null($response['problem'])) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        return $response;
    }

    private static function setRestrictedTags(\OmegaUp\DAO\VO\Problems $problem): void {
        \OmegaUp\DAO\ProblemsTags::clearRestrictedTags($problem);
        $languages = explode(',', $problem->languages);
        if (in_array('cat', $languages)) {
            \OmegaUp\Controllers\Problem::addTag(
                'problemRestrictedTagOnlyOutput',
                true,
                $problem,
                true
            );
        } elseif (!empty(array_intersect(['kp', 'kj'], $languages))) {
            \OmegaUp\Controllers\Problem::addTag(
                'problemRestrictedTagKarel',
                true,
                $problem,
                true
            );
        } else {
            \OmegaUp\Controllers\Problem::addTag(
                'problemRestrictedTagLanguage',
                true,
                $problem,
                true
            );
        }

        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            strval($problem->alias)
        );
        /** @var ProblemSettings */
        $distribSettings = json_decode(
            $problemArtifacts->get('settings.distrib.json'),
            /*assoc=*/true
        );
        if (!empty($distribSettings['interactive'])) {
            \OmegaUp\Controllers\Problem::addTag(
                'problemRestrictedTagInteractive',
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
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Problems $problem,
        string $directory,
        string $contents,
        string $message,
        ?string $lang,
        string $updatePublished
    ): array {
        // Check that lang is in the ISO 639-1 code list, default is "es".
        \OmegaUp\Validators::validateOptionalInEnum(
            $lang,
            'lang',
            \OmegaUp\Controllers\Problem::ISO639_1
        );
        if (is_null($lang)) {
            $lang = \OmegaUp\Controllers\Identity::getPreferredLanguage(
                $identity
            );
        }
        if (is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        $updatedFileLanguages = [];
        try {
            $problemDeployer = new \OmegaUp\ProblemDeployer($problem->alias);
            $problemDeployer->commitLooseFiles(
                "{$lang}.markdown: {$message}",
                $identity,
                [
                    "{$directory}/{$lang}.markdown" => $contents,
                ]
            );
            if ($updatePublished !== \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NONE) {
                [
                    $problem->commit,
                    $problem->current_version
                ] = \OmegaUp\Controllers\Problem::resolveCommit(
                    $problem,
                    $problemDeployer->publishedCommit
                );
                if ($updatePublished !== \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET) {
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
     *
     * @omegaup-request-param bool $allow_user_add_tags
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param mixed $extra_wall_time
     * @omegaup-request-param mixed $input_limit
     * @omegaup-request-param mixed $lang
     * @omegaup-request-param mixed $languages
     * @omegaup-request-param mixed $memory_limit
     * @omegaup-request-param mixed $message
     * @omegaup-request-param mixed $output_limit
     * @omegaup-request-param mixed $overall_wall_time_limit
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $selected_tags
     * @omegaup-request-param string $show_diff
     * @omegaup-request-param mixed $source
     * @omegaup-request-param mixed $statement
     * @omegaup-request-param mixed $time_limit
     * @omegaup-request-param mixed $title
     * @omegaup-request-param mixed $update_published
     * @omegaup-request-param mixed $validator
     * @omegaup-request-param mixed $validator_time_limit
     * @omegaup-request-param mixed $visibility
     */
    public static function apiUpdateStatement(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        $problemParams = self::convertRequestToProblemParams(
            $r,
            /*$isRequired=*/ false
        );
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
        \OmegaUp\Validators::validateStringNonEmpty($r['message'], 'message');
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['lang'],
            'lang',
            \OmegaUp\Controllers\Problem::ISO639_1
        );
        self::updateStatement(
            $r->identity,
            $r->user,
            $problem,
            /*$directory=*/ 'statements',
            $r['statement'],
            $r['message'],
            $r['lang'],
            $problemParams->updatePublished
        );
        return [
            'status' => 'ok'
        ];
    }

    private static function updateStatement(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Problems $problem,
        string $directory,
        string $statement,
        string $message,
        ?string $lang,
        string $updatePublished
    ): void {
        $updatedFileLanguages = self::updateLooseFile(
            $identity,
            $user,
            $problem,
            $directory,
            $statement,
            $message,
            $lang,
            $updatePublished
        );
        self::invalidateCache($problem, $updatedFileLanguages);
    }

    /**
     * Updates problem solution only
     *
     * @throws \OmegaUp\Exceptions\ApiException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param bool $allow_user_add_tags
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param mixed $extra_wall_time
     * @omegaup-request-param mixed $input_limit
     * @omegaup-request-param mixed $lang
     * @omegaup-request-param mixed $languages
     * @omegaup-request-param mixed $memory_limit
     * @omegaup-request-param mixed $message
     * @omegaup-request-param mixed $output_limit
     * @omegaup-request-param mixed $overall_wall_time_limit
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $selected_tags
     * @omegaup-request-param string $show_diff
     * @omegaup-request-param mixed $solution
     * @omegaup-request-param mixed $source
     * @omegaup-request-param mixed $time_limit
     * @omegaup-request-param mixed $title
     * @omegaup-request-param mixed $update_published
     * @omegaup-request-param mixed $validator
     * @omegaup-request-param mixed $validator_time_limit
     * @omegaup-request-param mixed $visibility
     */
    public static function apiUpdateSolution(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        $problemParams = self::convertRequestToProblemParams(
            $r,
            /*$isRequired=*/ false
        );
        [
            'problem' => $problem,
        ] = self::validateCreateOrUpdate(
            $r->identity,
            $problemParams,
            /*$isRequired=*/ false
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        \OmegaUp\Validators::validateStringNonEmpty($r['solution'], 'solution');
        \OmegaUp\Validators::validateStringNonEmpty($r['message'], 'message');

        self::updateStatement(
            $r->identity,
            $r->user,
            $problem,
            /*$directory=*/ 'solutions',
            $r['solution'],
            $r['message'],
            \OmegaUp\Controllers\Identity::getPreferredLanguage($r->identity),
            $problemParams->updatePublished
        );
        return [
            'status' => 'ok'
        ];
    }

    /**
     * Invalidates the various caches of the problem, as well as updating the
     * languages.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem the problem
     * @param list<string> $updatedLanguages the list of updated statement file
     *        languages.
     */
    private static function invalidateCache(
        \OmegaUp\DAO\VO\Problems $problem,
        $updatedLanguages
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
     * @param list<string> $updatedLanguages the list of updated loose file
     *        languages.
     */
    private static function invalidateSolutionCache(
        \OmegaUp\DAO\VO\Problems $problem,
        $updatedLanguages
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
    private static function validateDetails(
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?string $contestAlias,
        string $problemAlias,
        string $statementType,
        ?int $problemsetId
    ): array {
        $response = [
            'exists' => false,
            'problem' => null,
            'problemset' => null,
        ];

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            return $response;
        }

        if ($statementType !== 'markdown' && $statementType !== '') {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'invalidStatementType'
            );
        }

        // If we request a problem inside a contest
        $problemset = self::validateProblemset(
            $problem,
            $problemsetId,
            $contestAlias
        );

        $response['exists'] = true;
        $response['problem'] = $problem;

        if (!is_null($problemset) && isset($problemset['problemset'])) {
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
            }
            if (
                !\OmegaUp\Authorization::isAdmin(
                    $identity,
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
                                $identity->identity_id,
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
                            $identity,
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
                is_null($identity)
                || !\OmegaUp\Authorization::canEditProblem(
                    $identity,
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
     * @return ProblemStatement The contents of the file.
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
     * @return ProblemSettings
     */
    private static function getProblemSettingsDistrib(
        \OmegaUp\DAO\VO\Problems $problem,
        string $commit
    ): array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_SETTINGS_DISTRIB,
            "{$problem->alias}-{$problem->commit}",
            /** @return ProblemSettings */
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
     * @return ProblemSettings
     */
    public static function getProblemSettingsDistribImpl(array $params): array {
        /** @var ProblemSettings */
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
     * @omegaup-request-param mixed $problem_alias
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
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        $problem = self::validateDownload(
            $r->identity,
            $r['problem_alias']
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
     * Validate problem Download API
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
     * @return array{accepted: int, admin?: bool, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, difficulty: float|null, email_clarifications: bool, exists: true, input_limit: int, languages: list<string>, order: string, points: float, preferred_language?: string, problemsetter?: ProblemsetterInfo, quality_seal: bool, runs?: list<Run>, score: float, settings: ProblemSettings, solvers?: list<array{language: string, memory: float, runtime: float, time: \OmegaUp\Timestamp, username: string}>, source?: string, statement: ProblemStatement, submissions: int, title: string, version: string, visibility: int, visits: int}|array{exists: false}
     *
     * @omegaup-request-param mixed $contest_alias
     * @omegaup-request-param mixed $lang
     * @omegaup-request-param bool|null $prevent_problemset_open
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $problemset_id
     * @omegaup-request-param bool|null $show_solvers
     * @omegaup-request-param mixed $statement_type
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        $showSolvers = $r->ensureOptionalBool('show_solvers') ?? false;
        $preventProblemsetOptin = $r->ensureOptionalBool(
            'prevent_problemset_open'
        ) ?? false;
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateValidAlias(
            $r['problem_alias'],
            'problem_alias'
        );
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing. Not logged user can access here
        }
        $lang = \OmegaUp\Controllers\Identity::getPreferredLanguage(
            $r->identity,
            $r
        );
        $result = self::getValidProblemAndProblemset(
            $r->identity,
            $r['contest_alias'],
            $r['problem_alias'],
            !is_null($r['statement_type']) ? strval($r['statement_type']) : '',
            !is_null($r['problemset_id']) ? intval($r['problemset_id']) : null
        );
        [
            'exists' => $problemExists,
            'problem' => $problem,
            'problemset' => $problemset,
        ] = $result;
        if (!$problemExists || is_null($problem)) {
            return $result;
        }
        $details = self::getProblemDetails(
            $r->identity,
            $problem,
            $problemset,
            $lang,
            $showSolvers,
            boolval($r['prevent_problemset_open']),
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
     * @return array{exists: bool, problem: null|\OmegaUp\DAO\VO\Problems, problemset: null|\OmegaUp\DAO\VO\Problemsets}
     */
    private static function getValidProblemAndProblemset(
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?string $contestAlias,
        string $problemAlias,
        string $statementType,
        ?int $problemsetId
    ): array {
        if (is_null($identity) && !is_null($contestAlias)) {
            throw new \OmegaUp\Exceptions\UnauthorizedException(
                'userNotAllowed'
            );
        }

        // Validate request and return the object
        return self::validateDetails(
            $identity,
            $contestAlias,
            $problemAlias,
            $statementType,
            $problemsetId
        );
    }

    /**
     * Get the extra problem details with all the validations
     *
     * @return ProblemDetails|null
     */
    private static function getProblemDetails(
        ?\OmegaUp\DAO\VO\Identities $loggedIdentity,
        \OmegaUp\DAO\VO\Problems $problem,
        ?\OmegaUp\DAO\VO\Problemsets $problemset,
        string $statementLanguage,
        bool $showSolvers,
        bool $preventProblemsetOpen,
        ?string $contestAlias = null
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
        if (!is_null($loggedIdentity) && !is_null($loggedIdentity->username)) {
            $userData = \OmegaUp\Controllers\User::getUserProfile(
                $loggedIdentity,
                $loggedIdentity,
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
        $response['problem_id'] = intval($problem->problem_id);
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
        $response['allow_user_add_tags'] = $problem->allow_user_add_tags;
        $response['quality_seal'] = $problem->quality_seal;
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
            if (is_null($acl) || is_null($acl->owner_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
            }
            $problemsetter = \OmegaUp\DAO\Identities::findByUserId(
                $acl->owner_id
            );
            if (is_null($problemsetter) || is_null($problemsetter->username)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
            }
            $response['problemsetter'] = [
                'username' => $problemsetter->username,
                'classname' => \OmegaUp\DAO\Users::getRankingClassName(
                    $problemsetter->user_id
                ),
                'name' => $problemsetter->name ?? $problemsetter->username,
                'creation_date' => \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $response['creation_date']
                ),
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
            $response['admin'] = \OmegaUp\Authorization::isAdmin(
                $loggedIdentity,
                $problemset
            );
            if (!$response['admin'] || $preventProblemsetOpen !== true) {
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
                    'identity_id' => $loggedIdentity->identity_id,
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
     * @return array{exists: bool, solution?: ProblemStatement}
     *
     * @omegaup-request-param mixed $contest_alias
     * @omegaup-request-param bool|null $forfeit_problem
     * @omegaup-request-param mixed $lang
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $problemset_id
     * @omegaup-request-param mixed $statement_type
     */
    public static function apiSolution(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateValidAlias(
            $r['problem_alias'],
            'problem_alias'
        );

        // Validate request
        $response = self::validateDetails(
            $r->identity,
            !is_null($r['contest_alias']) ? strval($r['contest_alias']) : null,
            $r['problem_alias'],
            !is_null($r['statement_type']) ? strval($r['statement_type']) : '',
            !is_null($r['problemset_id']) ? intval($r['problemset_id']) : null
        );
        if (is_null($response['problem'])) {
            return [
                'exists' => false,
            ];
        }
        $problemset = $response['problemset'];
        $problem = $response['problem'];
        $lang = \OmegaUp\Controllers\Identity::getPreferredLanguage(
            $r->identity,
            $r
        );

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
            $forfeitProblem = $r->ensureOptionalBool('forfeit_problem');
            if ($forfeitProblem !== true) {
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
                $lang
            ),
        ];
    }

    /**
     * Entry point for Problem Versions API
     *
     * @omegaup-request-param mixed $problem_alias
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{published: null|string, log: list<ProblemVersion>}
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

        return self::getVersions($problem, $r->identity);
    }

    /**
     * @return array{published: null|string, log: list<ProblemVersion>}
     */
    private static function getVersions(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        if (is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        if (!\OmegaUp\Authorization::canEditProblem($identity, $problem)) {
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
            $logEntry['author']['time'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                $logEntry['author']['time']
            );
            $logEntry['committer']['time'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                $logEntry['committer']['time']
            );
            $masterLog[] = $logEntry;
        }
        $commit = (
            new \OmegaUp\ProblemArtifacts($problem->alias, 'published')
        )->commit();
        return [
            'published' => !is_null($commit) ? $commit['commit'] : null,
            'log' => $masterLog,
        ];
    }

    /**
     * Change the version of the problem.
     *
     * @omegaup-request-param mixed $commit
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $update_published
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
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['update_published'],
            'update_published',
            [
                \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET,
                \OmegaUp\ProblemParams::UPDATE_PUBLISHED_OWNED_PROBLEMSETS,
                \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
            ]
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

        $problemDeployer = new \OmegaUp\ProblemDeployer($problem->alias);
        try {
            // Begin transaction
            \OmegaUp\DAO\DAO::transBegin();
            $commit = (new \OmegaUp\ProblemArtifacts(
                $problem->alias,
                'published'
            ))->commit();
            if (is_null($commit)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemVersionNotFound'
                );
            }
            $problemDeployer->updatePublished(
                $commit['commit'],
                $problem->commit,
                $r->identity
            );

            \OmegaUp\DAO\Runs::createRunsForVersion($problem);
            \OmegaUp\DAO\Runs::updateVersionToCurrent($problem);
            if (
                strval(
                    $updatePublished
                ) != \OmegaUp\ProblemParams::UPDATE_PUBLISHED_NON_PROBLEMSET
            ) {
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

        /** @psalm-suppress RedundantCondition OMEGAUP_ENABLE_REJUDGE_ON_PROBLEM_UPDATE may be defined as true in tests. */
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
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $version
     *
     * @return array{diff: list<RunsDiff>}
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
     * @omegaup-request-param mixed $language
     * @omegaup-request-param mixed $offset
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $rowcount
     * @omegaup-request-param mixed $show_all
     * @omegaup-request-param mixed $status
     * @omegaup-request-param mixed $username
     * @omegaup-request-param mixed $verdict
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{runs: list<Run>}
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
            $response['runs'] = [];
            foreach (
                \OmegaUp\DAO\Runs::getAllRuns(
                    null,
                    !is_null($r['status']) ? strval($r['status']) : null,
                    !is_null($r['verdict']) ? strval($r['verdict']) : null,
                    $problem->problem_id,
                    !is_null($r['language']) ? strval($r['language']) : null,
                    !is_null($identity) ? intval($identity->identity_id) : null,
                    !is_null($r['offset']) ? intval($r['offset']) : null,
                    !is_null($r['rowcount']) ? intval($r['rowcount']) : null
                ) as $run
            ) {
                unset($run['run_id']);
                $response['runs'][] = $run;
            }
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
                $run['alias'] = strval($problem->alias);
                $run['country'] = 'xx';
                $result[] = $run;
            }
            $response['runs'] = $result;
        }

        return $response;
    }

    /**
     * Entry point for Problem clarifications API
     *
     * @omegaup-request-param mixed $offset
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $rowcount
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{clarifications: list<Clarification>}
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
            !empty($r['offset']) ? intval($r['offset']) : null,
            intval($r['rowcount'])
        );

        // Add response to array
        return [
            'clarifications' => $clarifications,
        ];
    }

    /**
     * Stats of a problem
     *
     * @omegaup-request-param mixed $problem_alias
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{cases_stats: array<string, int>, pending_runs: list<string>, total_runs: int, verdict_counts: array<string, int>}
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
        return self::getStats($problem, $r->identity);
    }

    /**
     * @omegaup-request-param mixed $problem_alias
     *
     * @return array{smartyProperties: array{payload: StatsPayload, title: string}, entrypoint: string}
     */
    public static function getStatsDataForSmarty(\OmegaUp\Request $r) {
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
        return [
            'smartyProperties' => [
                'payload' => array_merge(
                    [
                        'alias' => $r['problem_alias'],
                        'entity_type' => 'problem',
                    ],
                    self::getStats($problem, $r->identity)
                ),
                'title' => 'omegaupTitleProblemStats',
            ],
            'entrypoint' => 'common_stats',
        ];
    }

    /**
     * @return array{cases_stats: array<string, int>, pending_runs: list<string>, total_runs: int, verdict_counts: array<string, int>}
     */
    public static function getStats(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        // We need to check that the user has privileges on the problem
        if (
            !\OmegaUp\Authorization::isProblemAdmin(
                $identity,
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
                /** @var null|array{verdict: string, compile_meta: array{Main: RunMetadata}, score: int, contest_score: int, max_score: int, time: float, wall_time: float, memory: int, judged_by: string, groups: list<array{group: string, score: float, contest_score: int, max_score: int, cases: list<array{verdict: string, name: string, score: int, contest_score: int, max_score: int, meta: RunMetadata}>}>} */
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
     * @omegaup-request-param mixed $difficulty_range
     * @omegaup-request-param mixed $language
     * @omegaup-request-param mixed $max_difficulty
     * @omegaup-request-param mixed $min_difficulty
     * @omegaup-request-param mixed $min_visibility
     * @omegaup-request-param mixed $sort_order
     * @omegaup-request-param mixed $only_karel
     * @omegaup-request-param mixed $order_by
     * @omegaup-request-param mixed $page
     * @omegaup-request-param mixed $programming_languages
     * @omegaup-request-param mixed $query
     * @omegaup-request-param mixed $require_all_tags
     * @omegaup-request-param mixed $some_tags
     *
     * @return array{difficultyRange: array{0: int, 1: int}|null, keyword: string, language: string, minVisibility: int, sortOrder: string, orderBy: string, page: int, programmingLanguages: list<string>, requireAllTags: bool, tags: list<string>}
     */
    private static function validateListParams(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['sort_order'],
            'sort_order',
            array_merge(
                [''],
                \OmegaUp\Controllers\Problem::VALID_SORTING_MODES
            )
        );
        \OmegaUp\Validators::validateOptionalNumber($r['page'], 'page');
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['order_by'],
            'order_by',
            array_merge(
                [''],
                \OmegaUp\Controllers\Problem::VALID_SORTING_COLUMNS
            )
        );
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['language'],
            'language',
            array_merge(
                ['all', ''],
                \OmegaUp\Controllers\Problem::VALID_LANGUAGES
            )
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
            'sortOrder' => strval($r['sort_order']),
            'page' => intval($r['page']),
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
     * @omegaup-request-param mixed $difficulty_range
     * @omegaup-request-param mixed $language
     * @omegaup-request-param mixed $max_difficulty
     * @omegaup-request-param mixed $min_difficulty
     * @omegaup-request-param mixed $min_visibility
     * @omegaup-request-param mixed $sort_order
     * @omegaup-request-param mixed $offset
     * @omegaup-request-param mixed $only_karel
     * @omegaup-request-param mixed $order_by
     * @omegaup-request-param mixed $page
     * @omegaup-request-param mixed $programming_languages
     * @omegaup-request-param mixed $query
     * @omegaup-request-param mixed $require_all_tags
     * @omegaup-request-param mixed $rowcount
     * @omegaup-request-param mixed $some_tags
     *
     * @return array{results: list<ProblemListItem>, total: int}
     */
    public static function apiList(\OmegaUp\Request $r) {
        // Authenticate request
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing, we allow unauthenticated users to use this API
        }

        // Defaults for offset and rowcount
        $offset = null;
        $rowcount = \OmegaUp\Controllers\Problem::PAGE_SIZE;

        if (is_null($r['page'])) {
            $offset = is_null($r['offset']) ? 0 : intval($r['offset']);
        }
        if (!is_null($r['rowcount'])) {
            $rowcount = intval($r['rowcount']);
        }

        [
            'sortOrder' => $sortOrder,
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
            $sortOrder ?: 'desc',
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
     * @return array{results: list<ProblemListItem>, total: int}
     */
    private static function getList(
        int $page,
        string $language,
        string $orderBy,
        string $sortOrder,
        ?int $offset,
        int $rowcount,
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

        if (is_null($offset)) {
            $offset = ($page - 1) * $rowcount;
        }

        [
            'problems' => $problems,
            'count' => $count,
        ] = \OmegaUp\DAO\Problems::byIdentityType(
            $identityType,
            $language,
            $orderBy,
            $sortOrder,
            $offset,
            $rowcount,
            $keyword,
            $authorIdentityId,
            $authorUserId,
            $tags,
            $minVisibility,
            $requireAllTags,
            $programmingLanguages,
            $difficultyRange
        );
        return [
            'total' => $count,
            'results' => $problems,
        ];
    }

    /**
     * Returns a list of problems where current user has admin rights (or is
     * the owner).
     *
     * @return array{pagerItems: list<PageItem>, problems: list<ProblemListItem>}
     *
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     */
    public static function apiAdminList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('page_size');

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset(
            $r['page_size']
        ) ? intval(
            $r['page_size']
        ) : \OmegaUp\Controllers\Problem::PAGE_SIZE);

        if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            [
                'problems' => $problems,
                'count' => $count,
            ] = \OmegaUp\DAO\Problems::getAllWithCount(
                $page,
                $pageSize
            );
        } else {
            [
                'problems' => $problems,
                'count' => $count,
            ] = \OmegaUp\DAO\Problems::getAllProblemsAdminedByIdentity(
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
            /** @var ProblemListItem */
            $problemArray = $problem->asFilteredArray([
                'alias',
                'difficulty',
                'difficulty_histogram',
                'problem_id',
                'points',
                'quality',
                'quality_histogram',
                'ratio',
                'score',
                'tags',
                'title',
                'visibility',
                'quality_seal',
            ]);
            $problemArray['tags'] = $hiddenTags ? []  : \OmegaUp\DAO\Problems::getTagsForProblem(
                $problem,
                /*$public=*/false,
                $problem->allow_user_add_tags
            );
            $addedProblems[] = $problemArray;
        }

        $pagerItems = \OmegaUp\Pager::paginate(
            $count,
            $pageSize,
            $page ?: 1,
            5,
            []
        );

        return [
            'problems' => $addedProblems,
            'pagerItems' => $pagerItems,
        ];
    }

    /**
     * Gets a list of problems where current user is the owner
     *
     * @return array{pagerItems: list<PageItem>, problems: list<ProblemListItem>}
     *
     * @omegaup-request-param mixed $offset
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     * @omegaup-request-param mixed $rowcount
     */
    public static function apiMyList(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        // Defaults for offset and rowcount
        $offset = null;
        $pageSize = \OmegaUp\Controllers\Problem::PAGE_SIZE;

        if (is_null($r['page'])) {
            $offset = is_null($r['offset']) ? 0 : intval($r['offset']);
        }
        if (!is_null($r['rowcount'])) {
            $pageSize = intval($r['rowcount']);
        }

        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('page_size');

        $page = isset($r['page']) ? intval($r['page']) : 1;

        [
            'problems' => $problems,
            'count' => $count,
        ] = \OmegaUp\DAO\Problems::getAllProblemsOwnedByUser(
            $r->user->user_id,
            $page,
            $pageSize
        );

        $addedProblems = [];

        $hiddenTags = \OmegaUp\DAO\Users::getHideTags(
            $r->identity->identity_id
        );
        foreach ($problems as $problem) {
            /** @var ProblemListItem */
            $problemArray = $problem->asFilteredArray([
                'alias',
                'difficulty',
                'difficulty_histogram',
                'points',
                'problem_id',
                'quality',
                'quality_histogram',
                'ratio',
                'score',
                'tags',
                'title',
                'visibility',
                'quality_seal',
            ]);
            $problemArray['tags'] = $hiddenTags ? [] : \OmegaUp\DAO\Problems::getTagsForProblem(
                $problem,
                /*$public=*/false,
                $problem->allow_user_add_tags
            );
            $addedProblems[] = $problemArray;
        }

        $pagerItems = \OmegaUp\Pager::paginate(
            $count,
            $pageSize,
            $page ?: 1,
            5,
            []
        );

        return [
            'problems' => $addedProblems,
            'pagerItems' => $pagerItems,
        ];
    }

    /**
     * Returns the best score for a problem
     *
     * @return array{score: float}
     *
     * @omegaup-request-param mixed $contest_alias
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $problemset_id
     * @omegaup-request-param mixed $statement_type
     * @omegaup-request-param mixed $username
     */
    public static function apiBestScore(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateValidAlias(
            $r['problem_alias'],
            'problem_alias'
        );

        // Uses same params as apiDetails, except for lang, which is optional
        $problem = self::validateDetails(
            $r->identity,
            !is_null($r['contest_alias']) ? strval($r['contest_alias']) : null,
            $r['problem_alias'],
            !is_null($r['statement_type']) ? strval($r['statement_type']) : '',
            !is_null($r['problemset_id']) ? intval($r['problemset_id']) : null
        );

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
     * @return array{limits: LimitsSettings, validator: array{name: string, tolerance: float}} The Problem settings object.
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
     * @param array{limits: LimitsSettings, validator: array{name: string, tolerance?: float, custom_validator?: array{source: string, language: string, limits?: LimitsSettings}}} $problemSettings the original problem settings.
     * @param \OmegaUp\ProblemParams $params the params
     * @psalm-suppress ReferenceConstraintViolation for some reason, psalm cannot correctly infer the type for $problemSettings['validator']['limit']
     */
    private static function updateProblemSettings(
        array &$problemSettings,
        \OmegaUp\ProblemParams $params
    ): void {
        if (!is_null($params->extraWallTime)) {
            $problemSettings['limits']['ExtraWallTime'] = "{$params->extraWallTime}ms";
        }
        if (!is_null($params->memoryLimit)) {
            $problemSettings['limits']['MemoryLimit'] = "{$params->memoryLimit}KiB";
        }
        if (!is_null($params->outputLimit)) {
            $problemSettings['limits']['OutputLimit'] = "{$params->outputLimit}";
        }
        if (!is_null($params->memoryLimit)) {
            $problemSettings['limits']['OverallWallTimeLimit'] = "{$params->overallWallTimeLimit}ms";
        }
        if (!is_null($params->timeLimit)) {
            $problemSettings['limits']['TimeLimit'] = "{$params->timeLimit}ms";
        }
        if (!is_null($params->validator)) {
            $problemSettings['validator']['name'] = "{$params->validator}";
        }
        if ($problemSettings['validator']['name'] === 'custom') {
            if (empty($problemSettings['validator']['custom_validator'])) {
                $problemSettings['validator']['custom_validator'] = [];
            }
            if (
                empty(
                    $problemSettings['validator']['custom_validator']['limits']
                )
            ) {
                $problemSettings['validator']['custom_validator']['limits'] = [
                    'ExtraWallTime' => '0s',
                    'MemoryLimit' => '256MiB',
                    'OutputLimit' => '10KiB',
                    'OverallWallTimeLimit' => '5s',
                    'TimeLimit' => '30s',
                ];
            }
            $problemSettings['validator']['custom_validator']['limits']['TimeLimit'] = "{$params->validatorTimeLimit}ms";
        } else {
            if (!empty($problemSettings['validator']['custom_validator'])) {
                unset($problemSettings['validator']['custom_validator']);
            }
        }
    }

    /**
     * @return array{smartyProperties: array{payload: ProblemsMineInfoPayload, title: string}, entrypoint: string}
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
        $visibilityStatuses = [
            'deleted' => \OmegaUp\ProblemParams::VISIBILITY_DELETED,
            'privateBanned' => \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED,
            'publicBanned' => \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED,
            'privateWarning' => \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_WARNING,
            'private' => \OmegaUp\ProblemParams::VISIBILITY_PRIVATE,
            'publicWarning' => \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING,
            'public' => \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
            'promoted' => \OmegaUp\ProblemParams::VISIBILITY_PROMOTED,
        ];
        return [
            'smartyProperties' => [
                'payload' => [
                    'isSysadmin' => \OmegaUp\Authorization::isSystemAdmin(
                        $r->identity
                    ),
                    'privateProblemsAlert' => $privateProblemsAlert,
                    'visibilityStatuses' => $visibilityStatuses,
                ],
                'title' => 'omegaupTitleMyProblemsList',
            ],
            'entrypoint' => 'problem_mine',
        ];
    }

    /**
     * @return array{smartyProperties: array{input_limit: string, karel_problem: bool, memory_limit: string, nomination_payload: array{already_reviewed: bool, problem_alias: string, reviewer: bool}, overall_wall_time_limit: string, payload: ProblemDetailsPayload, points: float, problem_admin: bool, problem_alias: string, problem_id: int, problemsetter: ProblemsetterInfo|null, quality_payload: array{can_nominate_problem?: bool, dismissed: bool, dismissedBeforeAC?: bool, language?: string, nominated: bool, nominatedBeforeAC?: bool, problem_alias?: string, solved: bool, tried: bool}, quality_seal: bool, sample_input?: string, solvers: list<array{language: string, memory: float, runtime: float, time: \OmegaUp\Timestamp, username: string}>, source: null|string, time_limit: string, title: string, visibility: int}, template: string}
     * @omegaup-request-param mixed $contest_alias
     * @omegaup-request-param mixed $lang
     * @omegaup-request-param bool|null $prevent_problemset_open
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $problemset_id
     * @omegaup-request-param mixed $statement_type
     */
    public static function getProblemDetailsForSmarty(
        \OmegaUp\Request $r
    ): array {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing. Not logged user can access here
            $r->identity = null;
        }
        $preventProblemsetOpen = $r->ensureOptionalBool(
            'prevent_problemset_open'
        ) ?? false;
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateValidAlias(
            $r['problem_alias'],
            'problem_alias'
        );
        [
            'problem' => $problem,
            'problemset' => $problemset,
        ] = self::getValidProblemAndProblemset(
            $r->identity,
            !is_null($r['contest_alias']) ? strval($r['contest_alias']) : null,
            $r['problem_alias'],
            !is_null($r['statement_type']) ? strval($r['statement_type']) : '',
            !is_null($r['problemset_id']) ? intval($r['problemset_id']) : null
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Get problem details from API
        $details = self::getProblemDetails(
            $r->identity,
            $problem,
            $problemset,
            \OmegaUp\Controllers\Identity::getPreferredLanguage($r->identity),
            /*showSolvers=*/true,
            $preventProblemsetOpen,
            $r['contest_alias']
        );
        if (is_null($details)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $memoryLimit = intval(
            $details['settings']['limits']['MemoryLimit']
        ) / 1024 / 1024;
        $result = [
            'problem_id' => intval($details['problem_id']),
            'problem_alias' => $details['alias'],
            'visibility' => $details['visibility'],
            'quality_seal' => $details['quality_seal'],
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
            'nomination_payload' => [
                'problem_alias' => $details['alias'],
                'reviewer' => false,
                'already_reviewed' => false,
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
            isset($details['settings']['cases']['sample']['in'])
        ) {
            $result['sample_input'] = strval(
                $details['settings']['cases']['sample']['in']
            );
        }
        $details['histogram'] = [
            'difficulty_histogram' => $problem->difficulty_histogram,
            'quality_histogram' => $problem->quality_histogram,
            'quality' => floatval($problem->quality),
            'difficulty' => floatval($problem->difficulty),
        ];
        $details['user'] = ['logged_in' => false, 'admin' => false, 'reviewer' => false];
        $result['payload'] = $details;

        if (
            is_null($r->identity)
            || is_null($r->identity->user_id)
            || is_null($problem->problem_id)
        ) {
            return [
                'smartyProperties' => $result,
                'template' => 'arena.problem.tpl',
            ];
        }
        $nominationStatus = \OmegaUp\DAO\QualityNominations::getNominationStatusForProblem(
            $problem->problem_id,
            $r->identity->user_id
        );
        $isProblemAdmin = \OmegaUp\Authorization::isProblemAdmin(
            $r->identity,
            $problem
        );
        $isQualityReviewer = \OmegaUp\Authorization::isQualityReviewer(
            $r->identity
        );

        $result['nomination_payload']['reviewer'] = $isQualityReviewer;
        $result['nomination_payload']['already_reviewed'] = \OmegaUp\DAO\QualityNominations::reviewerHasQualityTagNominatedProblem(
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
            'admin' => $isProblemAdmin,
            'reviewer' => $isQualityReviewer,
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
        return [
            'smartyProperties' => $result,
            'template' => 'arena.problem.tpl',
        ];
    }

    /**
     * @return array{entrypoint: string, smartyProperties: array{LOAD_MATHJAX: bool, payload: ProblemDetailsv2Payload, title: string}}
     *
     * @omegaup-request-param mixed $contest_alias
     * @omegaup-request-param mixed $lang
     * @omegaup-request-param bool|null $prevent_problemset_open
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $problemset_id
     * @omegaup-request-param mixed $statement_type
     */
    public static function getProblemDetailsForSmartyV2(
        \OmegaUp\Request $r
    ): array {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing. Not logged user can access here
            $r->identity = null;
        }
        $preventProblemsetOpen = $r->ensureOptionalBool(
            'prevent_problemset_open'
        ) ?? false;
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateValidAlias(
            $r['problem_alias'],
            'problem_alias'
        );
        [
            'problem' => $problem,
            'problemset' => $problemset,
        ] = self::getValidProblemAndProblemset(
            $r->identity,
            !is_null($r['contest_alias']) ? strval($r['contest_alias']) : null,
            $r['problem_alias'],
            !is_null($r['statement_type']) ? strval($r['statement_type']) : '',
            !is_null($r['problemset_id']) ? intval($r['problemset_id']) : null
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Get problem details from API
        $details = self::getProblemDetails(
            $r->identity,
            $problem,
            $problemset,
            \OmegaUp\Controllers\Identity::getPreferredLanguage($r->identity),
            /*showSolvers=*/true,
            $preventProblemsetOpen,
            $r['contest_alias']
        );
        if (is_null($details)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $sampleInput = null;
        if (
            isset($details['settings']['cases']) &&
            isset($details['settings']['cases']['sample']) &&
            isset($details['settings']['cases']['sample']['in'])
        ) {
            $sampleInput = strval(
                $details['settings']['cases']['sample']['in']
            );
        }

        $response = [
            'smartyProperties' => [
                'LOAD_MATHJAX' => true,
                'payload' => [
                    'problem' => [
                        'alias' => $details['alias'],
                        'karel_problem' => count(
                            array_intersect(
                                $details['languages'],
                                ['kp', 'kj']
                            )
                        ) === 2,
                        'limits' => [
                            'input_limit' => (
                                $details['input_limit'] / 1024
                            ) . ' KiB',
                            'memory_limit' => (
                                intval(
                                    $details['settings']['limits']['MemoryLimit']
                                ) / 1024 / 1024
                            ) . ' MiB',
                            'overall_wall_time_limit' => $details['settings']['limits']['OverallWallTimeLimit'],
                            'time_limit' => $details['settings']['limits']['TimeLimit'],
                        ],
                        'points' => $details['points'],
                        'problem_id' => intval($details['problem_id']),
                        'problemsetter' => $details['problemsetter'] ?? null,
                        'quality_seal' => $details['quality_seal'],
                        'sample_input' => $sampleInput,
                        'settings' => $details['settings'],
                        'statement' => $details['statement'],
                        'source' => (
                            isset($details['source']) ?
                            strval($details['source']) :
                            null
                        ),
                        'title' => $details['title'],
                        'visibility' => $details['visibility'],
                    ],
                    'user' => [
                        'loggedIn' => false,
                        'admin' => false,
                        'reviewer' => false,
                    ],
                ],
                'title' => 'omegaupTitleProblem',
            ],
            'entrypoint' => 'problem_details',
        ];

        if (
            is_null($r->identity)
            || is_null($r->identity->user_id)
            || is_null($problem->problem_id)
        ) {
            return $response;
        }

        $isAdmin = \OmegaUp\Authorization::isProblemAdmin(
            $r->identity,
            $problem
        );

        $isQualityReviewer = \OmegaUp\Authorization::isQualityReviewer(
            $r->identity
        );

        $response['smartyProperties']['payload']['user'] = [
            'loggedIn' => true,
            'admin' => $isAdmin,
            'reviewer' => $isQualityReviewer,
        ];

        $nominationStatus = \OmegaUp\DAO\QualityNominations::getNominationStatusForProblem(
            $problem->problem_id,
            $r->identity->user_id
        );

        $nominationPayload = [
            'alreadyReviewed' => \OmegaUp\DAO\QualityNominations::reviewerHasQualityTagNominatedProblem(
                $r->identity,
                $problem
            ),
            'dismissed' => $nominationStatus['dismissed'],
            'dismissedBeforeAC' => $nominationStatus['dismissedBeforeAC'],
            'nominated' => $nominationStatus['nominated'],
            'nominatedBeforeAC' => $nominationStatus['nominatedBeforeAC'],
            'solved' => false,
            'tried' => false,
        ];

        foreach ($details['runs'] ?? [] as $run) {
            if ($run['verdict'] === 'AC') {
                $nominationPayload['solved'] = true;
                break;
            } elseif ($run['verdict'] !== 'JE' && $run['verdict'] !== 'VE' && $run['verdict'] !== 'CE') {
                $nominationPayload['tried'] = true;
            }
        }

        $response['smartyProperties']['payload']['nominationStatus'] = $nominationPayload;

        return $response;
    }

    /**
     * @omegaup-request-param mixed $difficulty_range
     * @omegaup-request-param mixed $language
     * @omegaup-request-param mixed $max_difficulty
     * @omegaup-request-param mixed $min_difficulty
     * @omegaup-request-param mixed $min_visibility
     * @omegaup-request-param mixed $sort_order
     * @omegaup-request-param mixed $offset
     * @omegaup-request-param mixed $only_karel
     * @omegaup-request-param mixed $order_by
     * @omegaup-request-param mixed $page
     * @omegaup-request-param mixed $programming_languages
     * @omegaup-request-param mixed $query
     * @omegaup-request-param mixed $require_all_tags
     * @omegaup-request-param mixed $rowcount
     * @omegaup-request-param mixed $some_tags
     *
     * @return array{smartyProperties: array{payload: ProblemListPayload, title: string}, entrypoint: string}
     */
    public static function getProblemListForSmarty(
        \OmegaUp\Request $r
    ): array {
        // Authenticate request
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing, we allow unauthenticated users to use this API
            $r->identity = null;
        }

        // Defaults for offset and pageSize
        $offset = null;
        $pageSize = \OmegaUp\Controllers\Problem::PAGE_SIZE;

        if (is_null($r['page'])) {
            $offset = is_null($r['offset']) ? 0 : intval($r['offset']);
        }
        if (!is_null($r['rowcount'])) {
            $pageSize = intval($r['rowcount']);
        }

        [
            'sortOrder' => $sortOrder,
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
            $sortOrder ?: 'desc',
            $offset,
            $pageSize,
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
            'sort_order' => $sortOrder,
            'tag' => $tags
        ];

        $pagerItems = \OmegaUp\Pager::paginateWithUrl(
            $response['total'],
            $pageSize,
            $page ?: 1,
            '/problem/list/',
            5,
            $params
        );

        $tagData = [];
        $allTags = self::getAllTagsFromCache();

        foreach ($allTags as $tag) {
            $tagData[] = ['name' => $tag->name];
        }

        return [
            'smartyProperties' => [
                'payload' => [
                    'problems' => $response['results'],
                    'loggedIn' => !is_null($r->identity),
                    'currentTags' => $tags,
                    'pagerItems' => $pagerItems,
                    'keyword' => $keyword,
                    'mode' => $sortOrder,
                    'column' => $orderBy,
                    'language' => $language,
                    'languages' => array_merge(
                        ['all'],
                        \OmegaUp\Controllers\Problem::VALID_LANGUAGES
                    ),
                    'modes' => \OmegaUp\Controllers\Problem::VALID_SORTING_MODES,
                    'columns' => \OmegaUp\Controllers\Problem::VALID_SORTING_COLUMNS,
                    'tags' => $tags,
                    'tagData' => $tagData,
                ],
                'title' => 'omegaupTitleProblems',
            ],
            'entrypoint' => 'problem_list',
        ];
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Tags>
     */
    private static function getAllTagsFromCache() {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::TAGS_LIST,
            'all',
            /** @return list<\OmegaUp\DAO\VO\Tags> */
            function () {
                return \OmegaUp\DAO\Tags::getAll();
            },
            APC_USER_CACHE_SESSION_TIMEOUT
        );
    }

    /**
     * @return array{validLanguages: array<string, string>, validatorTypes: array<string, null|string>, visibilityStatuses: array<string, int>}
     */
    public static function getCommonPayloadForSmarty(): array {
        $validatorTypes = [
            \OmegaUp\ProblemParams::VALIDATOR_TOKEN_CASELESS => \OmegaUp\Translations::getInstance()->get(
                'problemEditFormTokenCaseless'
            ),
            \OmegaUp\ProblemParams::VALIDATOR_TOKEN_NUMERIC => \OmegaUp\Translations::getInstance()->get(
                'problemEditFormNumericTokensWithTolerance'
            ),
            \OmegaUp\ProblemParams::VALIDATOR_TOKEN => \OmegaUp\Translations::getInstance()->get(
                'problemEditFormTokenByToken'
            ),
            \OmegaUp\ProblemParams::VALIDATOR_LITERAL => \OmegaUp\Translations::getInstance()->get(
                'problemEditFormLiteral'
            ),
            \OmegaUp\ProblemParams::VALIDATOR_CUSTOM => \OmegaUp\Translations::getInstance()->get(
                'problemEditFormCustom'
            ),
        ];
        $sortedLanguages = \OmegaUp\Controllers\Run::DEFAULT_LANGUAGES;
        sort($sortedLanguages);
        $validLanguages = [
            join(
                ',',
                $sortedLanguages
            ) => 'C, C++, C++11, C#, Haskell, Java, Pascal, Python, Ruby, Lua',
            'kj,kp' => 'Karel',
            'cat' => \OmegaUp\Translations::getInstance()->get(
                'wordsJustOutput'
            ),
            '' => \OmegaUp\Translations::getInstance()->get(
                'wordsNoSubmissions'
            ),
        ];
        $visibilityStatuses = [
            'deleted' => \OmegaUp\ProblemParams::VISIBILITY_DELETED,
            'privateBanned' => \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED,
            'publicBanned' => \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED,
            'privateWarning' => \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_WARNING,
            'private' => \OmegaUp\ProblemParams::VISIBILITY_PRIVATE,
            'publicWarning' => \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING,
            'public' => \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
            'promoted' => \OmegaUp\ProblemParams::VISIBILITY_PROMOTED,
        ];
        return [
          'validatorTypes' => $validatorTypes,
          'validLanguages' => $validLanguages,
          'visibilityStatuses' => $visibilityStatuses,
        ];
    }

    /**
     * @return array{entrypoint: string, smartyProperties: array{LOAD_MATHJAX: bool, payload: ProblemEditPayload, title: string}}
     *
     * @omegaup-request-param string $contents
     * @omegaup-request-param string $directory
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param bool $allow_user_add_tags
     * @omegaup-request-param mixed $extra_wall_time
     * @omegaup-request-param mixed $input_limit
     * @omegaup-request-param mixed $lang
     * @omegaup-request-param mixed $languages
     * @omegaup-request-param mixed $memory_limit
     * @omegaup-request-param mixed $message
     * @omegaup-request-param mixed $output_limit
     * @omegaup-request-param mixed $overall_wall_time_limit
     * @omegaup-request-param mixed $problem
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $redirect
     * @omegaup-request-param mixed $request
     * @omegaup-request-param mixed $selected_tags
     * @omegaup-request-param string $show_diff
     * @omegaup-request-param mixed $source
     * @omegaup-request-param mixed $time_limit
     * @omegaup-request-param mixed $title
     * @omegaup-request-param mixed $update_published
     * @omegaup-request-param mixed $validator
     * @omegaup-request-param mixed $validator_time_limit
     * @omegaup-request-param mixed $visibility
     * @omegaup-request-param string $contents
     *
     */
    public static function getProblemEditDetailsForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        // HACK to prevent fails in validateCreateOrUpdate
        $r['problem_alias'] = strval($r['problem']);
        \OmegaUp\Validators::validateValidAlias(
            $r['problem_alias'],
            'problem_alias'
        );

        $problemParams = self::convertRequestToProblemParams(
            $r,
            /*$isRequired=*/ false
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem) || is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        $tags = [];
        $selectedTags = [];
        $allTags = self::getAllTagsFromCache();
        // TODO: Change this list when the final list be defined
        $filteredTags = array_slice($allTags, 0, 100);
        $tagnames = array_column($filteredTags, 'name');

        $problemSelectedTags = \OmegaUp\DAO\ProblemsTags::getProblemTags(
            $problem,
            !\OmegaUp\Authorization::canEditProblem($r->identity, $problem)
        );
        foreach ($problemSelectedTags as $selectedTag) {
            $key = array_search($selectedTag['name'], $tagnames);
            unset($filteredTags[$key]);
            $selectedTags[] = [
                'tagname' => $selectedTag['name'],
                'public' => $selectedTag['public'],
            ];
        }
        foreach ($filteredTags as $tag) {
            $tags[] = ['name' => $tag->name];
        }
        $versions = self::getVersions($problem, $r->identity);
        $lang = \OmegaUp\Controllers\Identity::getPreferredLanguage(
            $r->identity,
            $r
        );
        $solution = \OmegaUp\Controllers\Problem::getProblemSolution(
            $problem,
            $problem->commit,
            $lang
        );
        $publishedRevision = null;
        foreach ($versions['log'] as $revision) {
            if ($versions['published'] === $revision['commit']) {
                $publishedRevision = $revision;
                break;
            }
        }
        $admins = \OmegaUp\DAO\UserRoles::getProblemAdmins($problem);
        $groupAdmins = \OmegaUp\DAO\GroupRoles::getProblemAdmins($problem);

        $details = self::getProblemEditDetails($problem, $r->identity);
        $result = [
            'smartyProperties' => [
                'LOAD_MATHJAX' => true,
                'payload' => array_merge(
                    $details,
                    self::getCommonPayloadForSmarty()
                ),
                'title' => 'problemEditEditProblem',
            ],
            'entrypoint' => 'problem_edit',
        ];
        $result['smartyProperties']['payload'] = array_merge(
            [
                'loadMathjax' => true,
                'statusSuccess' => false,
                'admins' => $admins,
                'groupAdmins' => $groupAdmins,
                'tags' => $tags,
                'problemLevel' => \OmegaUp\DAO\ProblemsTags::getProblemLevel(
                    $problem
                ),
                'publicTags' => \OmegaUp\Controllers\Tag::getPublicTags(),
                'levelTags' => \OmegaUp\Controllers\Tag::getLevelTags(),
                'selectedTags' => $selectedTags,
                'log' => $versions['log'],
                'publishedRevision' => $publishedRevision,
                'solution' => $solution,
            ],
            $result['smartyProperties']['payload']
        );
        if (isset($details['problemsetter'])) {
            $result['smartyProperties']['payload']['problemsetter'] = $details['problemsetter'];
        }
        if (!isset($r['request'])) {
            return $result;
        }

        // Validate commit message.
        \OmegaUp\Validators::validateStringNonEmpty($r['message'], 'message');
        if ($r['request'] === 'submit') {
            try {
                $originalVisibility = $problem->visibility;
                [
                    'problem' => $problem,
                ] = self::updateProblem(
                    $r->identity,
                    $r->user,
                    $problemParams,
                    $r['message'],
                    $problemParams->updatePublished,
                    boolval($r['redirect'])
                );
                $details = self::getProblemEditDetails($problem, $r->identity);
                $result['smartyProperties']['payload'] = array_merge(
                    $details,
                    self::getCommonPayloadForSmarty()
                );
                $result['smartyProperties']['payload'] = array_merge(
                    [
                        'loadMathjax' => true,
                        'statusSuccess' => false,
                        'admins' => $admins,
                        'groupAdmins' => $groupAdmins,
                        'tags' => $tags,
                        'selectedTags' => $selectedTags,
                        'log' => $versions['log'],
                        'publishedRevision' => $publishedRevision,
                        'solution' => $solution,
                    ],
                    $result['smartyProperties']['payload']
                );
            } catch (\OmegaUp\Exceptions\ApiException $e) {
                /** @var array{error?: string} */
                $response = $e->asResponseArray();
                if (empty($response['error'])) {
                    $statusError = '{error}';
                } else {
                    $statusError = $response['error'];
                }
                $result['smartyProperties']['payload']['statusError'] = $statusError;
                return $result;
            }
        } elseif ($r['request'] === 'markdown') {
            \OmegaUp\Validators::validateInEnum(
                $r['directory'],
                'directory',
                ['statements', 'solutions']
            );

            \OmegaUp\Validators::validateStringNonEmpty(
                $r['contents'],
                'contents'
            );
            /** @var mixed $statements */
            $statements = json_decode($r['contents'], /*$assoc=*/true);
            if (!is_array($statements)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'statementNotFound'
                );
            }
            foreach ($statements as $language => $statement) {
                \OmegaUp\Validators::validateInEnum(
                    $language,
                    'lang',
                    \OmegaUp\Controllers\Problem::ISO639_1
                );
                \OmegaUp\Validators::validateStringNonEmpty(
                    $statement,
                    'statement'
                );
                self::updateStatement(
                    $r->identity,
                    $r->user,
                    $problem,
                    strval($r['directory']),
                    $statement,
                    $r['message'],
                    strval($language),
                    $problemParams->updatePublished
                );
            }
            $details = self::getProblemEditDetails($problem, $r->identity);
            $result['smartyProperties']['payload']['statement'] = $details['statement'];
        }
        $result['smartyProperties']['payload']['statusSuccess'] = true;
        return $result;
    }

    /**
     * @return array{alias: string, allowUserAddTags: bool, emailClarifications: bool, extraWallTime: float, inputLimit: int, languages: string, memoryLimit: float, outputLimit: int, overallWallTimeLimit: float, problemsetter?: ProblemsetterInfo, source: string, statement: ProblemStatement, timeLimit: float, title: string, validator: string, validatorTimeLimit: float|int, visibility: int}
     */
    private static function getProblemEditDetails(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        $details = self::getProblemDetails(
            $identity,
            $problem,
            /*$problemset*/null,
            /*$statementLanguage*/'',
            /*$showSolvers*/false,
            /*$preventProblemsetOpen*/false
        );
        if (is_null($details)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        sort($details['languages']);
        $limitsSettings = $details['settings']['limits'];
        $validatorSettings = $details['settings']['validator'];

        $result = [
            'title' => $details['title'],
            'alias' => $details['alias'],
            'source' => $details['source'] ?? '',
            'visibility' => $details['visibility'],
            'emailClarifications' => $details['email_clarifications'],
            'allowUserAddTags' => $details['allow_user_add_tags'],
            'validator' => $validatorSettings['name'],
            'languages' => join(',', $details['languages']),
            'extraWallTime' => self::parseDuration(
                $limitsSettings['ExtraWallTime']
            ),
            'overallWallTimeLimit' => self::parseDuration(
                $limitsSettings['OverallWallTimeLimit']
            ),
            'timeLimit' => self::parseDuration($limitsSettings['TimeLimit']),
            'memoryLimit' => intval($limitsSettings['MemoryLimit']) / 1024,
            'inputLimit' => $details['input_limit'],
            'outputLimit' => intval($limitsSettings['OutputLimit']),
            'validatorTimeLimit' => 0,
            'statement' => $details['statement'],
        ];
        if (
            isset($validatorSettings['custom_validator']) &&
            isset($validatorSettings['custom_validator']['limits'])
        ) {
            $result['validatorTimeLimit'] = self::parseDuration(
                strval(
                    $validatorSettings['custom_validator']['limits']['TimeLimit']
                )
            );
        }
        if (isset($details['problemsetter'])) {
            $result['problemsetter'] = $details['problemsetter'];
        }
        return $result;
    }

    /**
     * @omegaup-request-param bool $allow_user_add_tags
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $email_clarifications
     * @omegaup-request-param mixed $extra_wall_time
     * @omegaup-request-param mixed $input_limit
     * @omegaup-request-param mixed $languages
     * @omegaup-request-param mixed $memory_limit
     * @omegaup-request-param mixed $output_limit
     * @omegaup-request-param mixed $overall_wall_time_limit
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $request
     * @omegaup-request-param mixed $selected_tags
     * @omegaup-request-param string $show_diff
     * @omegaup-request-param mixed $source
     * @omegaup-request-param mixed $time_limit
     * @omegaup-request-param mixed $title
     * @omegaup-request-param mixed $update_published
     * @omegaup-request-param mixed $validator
     * @omegaup-request-param mixed $validator_time_limit
     * @omegaup-request-param mixed $visibility
     *
     * @return array{smartyProperties: array{payload: ProblemFormPayload}, entrypoint: string}
     */
    public static function getProblemNewForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        $tags = [];
        $selectedTags = null;

        $allTags = self::getAllTagsFromCache();
        // TODO: Change this list when the final list be defined
        $filteredTags = array_slice($allTags, 0, 100);
        foreach ($filteredTags as $tag) {
            $tags[] = ['name' => $tag->name];
        }
        if (isset($r['request']) && ($r['request'] === 'submit')) {
            // HACK to prevent fails in validateCreateOrUpdate
            $r['problem_alias'] = strval($r['alias']);
            $problemParams = null;
            try {
                $problemParams = self::convertRequestToProblemParams($r);
                self::createProblem($r->user, $r->identity, $problemParams);
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
                if (
                    !is_null($problemParams)
                    && !is_null($problemParams->selectedTagsAsJSON)
                ) {
                    /** @var list<array{tagname: string, public: bool}> */
                    $selectedTags = json_decode(
                        $problemParams->selectedTagsAsJSON,
                        /*$assoc=*/true
                    );
                }
                return [
                    'smartyProperties' => [
                        'payload' => array_merge(
                            [
                                'title' => strval($r['title']),
                                'alias' => strval($r['problem_alias']),
                                'emailClarifications' => boolval(
                                    $r['email_clarifications']
                                ),
                                'source' => strval($r['source']),
                                'visibility' => intval($r['visibility']),
                                'statusError' => $statusError,
                                'selectedTags' => $selectedTags,
                                'allowUserAddTags' => true,
                                'timeLimit' => strval($r['time_limit']),
                                'validatorTimeLimit' => strval(
                                    $r['validator_time_limit']
                                ),
                                'overallWallTimeLimit' => strval(
                                    $r['overall_wall_time_limit']
                                ),
                                'extraWallTime' => strval(
                                    $r['extra_wall_time']
                                ),
                                'outputLimit' => strval($r['output_limit']),
                                'inputLimit' => strval($r['input_limit']),
                                'memoryLimit' =>  strval($r['memory_limit']),
                                'languages' => strval($r['languages']),
                                'validator' => strval($r['validator']),
                                'tags' => $tags,
                            ],
                            self::getCommonPayloadForSmarty()
                        ),
                    ],
                    'entrypoint' => 'problem_new',
                ];
            }
        }
        $sortedLanguages = \OmegaUp\Controllers\Run::DEFAULT_LANGUAGES;
        sort($sortedLanguages);

        return [
            'smartyProperties' => [
                'payload' => array_merge(
                    [
                        'title' => '',
                        'alias' => '',
                        'validator' => \OmegaUp\ProblemParams::VALIDATOR_TOKEN,
                        'emailClarifications' => false,
                        'source' => '',
                        'visibility' => 0,
                        'statusError' => '',
                        'selectedTags' => null,
                        'allowUserAddTags' => true,
                        'timeLimit' => 1000,
                        'validatorTimeLimit' => 1000,
                        'overallWallTimeLimit' => 60000,
                        'extraWallTime' => 0,
                        'outputLimit' => 10240,
                        'inputLimit' => 10240,
                        'memoryLimit' => 32768,
                        'languages' => join(
                            ',',
                            $sortedLanguages
                        ),
                        'tags' => $tags,
                    ],
                    self::getCommonPayloadForSmarty()
                ),
            ],
            'entrypoint' => 'problem_new',
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

    /**
     * @omegaup-request-param mixed $commit
     * @omegaup-request-param mixed $filename
     * @omegaup-request-param mixed $problem_alias
     */
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

    /**
     * @omegaup-request-param mixed $extension
     * @omegaup-request-param mixed $object_id
     * @omegaup-request-param mixed $problem_alias
     */
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
     * @omegaup-request-param mixed $idl
     * @omegaup-request-param mixed $language
     * @omegaup-request-param mixed $name
     * @omegaup-request-param mixed $os
     *
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
