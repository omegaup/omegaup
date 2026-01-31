<?php

namespace OmegaUp\Controllers;

/**
 * ProblemsController
 *
 * @psalm-type CDPLine=array{lineID: string,caseID: string, label: string, data: array{kind: 'line'|'multiline'|'array'|'matrix', value: string}}
 * @psalm-type CDPCase=array{caseID: string,groupID: string, lines: list<CDPLine>, points: int,autoPoints: bool,output: string,name: string}
 * @psalm-type CDPGroup=array{groupID: string,name: string,points: int,autoPoints: bool,ungroupedCase: bool,cases: list<CDPCase>}
 * @psalm-type CDPCasesStore=array{groups: list<CDPGroup>,selected: array{groupID: string|null, caseID: string|null},layouts: list<array<string, string>>,hide: bool}
 * @psalm-type CDP=array{problemName: string,problemMarkdown: string,problemCodeContent: string,problemCodeExtension: string, problemSolutionMarkdown: string, casesStore: CDPCasesStore}
 * @psalm-type Clarification=array{answer: null|string, assignment_alias?: null|string, author: string, clarification_id: int, contest_alias?: null|string, message: string, problem_alias: string, public: bool, receiver: null|string, time: \OmegaUp\Timestamp}
 * @psalm-type NominationStatus=array{alreadyReviewed: bool, canNominateProblem: bool, dismissed: bool, dismissedBeforeAc: bool, language: string, nominated: bool, nominatedBeforeAc: bool, solved: bool, tried: bool}
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 * @psalm-type LimitsSettings=array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}
 * @psalm-type InteractiveSettingsDistrib=array{idl: string, module_name: string, language: string, main_source: string, templates: array<string, string>}
 * @psalm-type ProblemStatement=array{images: array<string, string>, sources: array<string, string>, language: string, markdown: string}
 * @psalm-type InteractiveInterface=array{MakefileRules: list<array{Targets: list<string>, Requisites: list<string>, Compiler: string, Params: string, Debug: bool}>, ExecutableDescription: array{Args: list<string>, Env: array<string, string>}, Files: array<string, string>}
 * @psalm-type ProblemSettings=array{Cases: list<array{Cases: list<array{Name: string, Weight: float}>, Name: string}>, Limits: LimitsSettings, Slow: bool, Validator: array{GroupScorePolicy?: string, Lang?: string, Limits?: LimitsSettings, Name: string, Tolerance: float}, Interactive?: array{Interfaces: array<string, array<string, InteractiveInterface>>, Templates: array<string, string>, Main: string, ModuleName: string, ParentLang: string, LibinteractiveVersion: string}}
 * @psalm-type ProblemSettingsDistrib=array{cases: array<string, array{in: string, out: string, weight?: float}>, interactive?: InteractiveSettingsDistrib, limits: LimitsSettings, validator: array{custom_validator?: array{language: string, limits?: LimitsSettings, source: string}, group_score_policy?: string, name: string, tolerance?: float}}
 * @psalm-type ProblemsetterInfo=array{classname: string, creation_date: \OmegaUp\Timestamp|null, name: string, username: string}
 * @psalm-type SettingLimits=array{input_limit: string, memory_limit: string, overall_wall_time_limit: string, time_limit: string}
 * @psalm-type ProblemInfo=array{accepts_submissions: boolean, commit: string, alias: string, input_limit: int, karel_problem: bool, languages: list<string>, letter?: string, limits: SettingLimits, nextExecutionTimestamp?: \OmegaUp\Timestamp, nextSubmissionTimestamp?: \OmegaUp\Timestamp, points: float, preferred_language: null|string, problem_id: int, problemsetter: ProblemsetterInfo|null, quality_seal: bool, sample_input: null|string, settings: ProblemSettingsDistrib, source: null|string, statement: ProblemStatement, title: string, visibility: int}
 * @psalm-type UserInfoForProblem=array{loggedIn: bool, admin: bool, reviewer: bool}
 * @psalm-type RunMetadata=array{verdict: string, time: float, sys_time: int, wall_time: float, memory: int}
 * @psalm-type CaseResult=array{contest_score: float, max_score: float, meta: RunMetadata, name: string, out_diff?: string, score: float, verdict: string}
 * @psalm-type ListItem=array{key: string, value: string}
 * @psalm-type ProblemListItem=array{accepted: int, alias: string, can_be_removed?: bool, difficulty: float|null, difficulty_histogram: list<int>, points: float, problem_id: int, quality: float|null, quality_histogram: list<int>, quality_seal: bool, ratio: float, score: float, submissions: int, tags: list<array{name: string, source: string}>, title: string, visibility: int}
 * @psalm-type Statements=array<string, string>
 * @psalm-type Run=array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, execution: null|string, guid: string, language: string, memory: int, output: null|string, penalty: int, runtime: int, score: float, score_by_group?: array<string, float|null>, status: string, status_memory: null|string, status_runtime: null|string, submit_delay: int, suggestions?: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}
 * @psalm-type ArenaProblemDetails=array{accepts_submissions: bool, alias: string, commit: string, input_limit: int, languages: list<string>, letter?: string, points: float, problem_id?: int, problemsetter?: ProblemsetterInfo, quality_seal: bool, runs?: list<Run>,  settings?: ProblemSettingsDistrib, source?: string, statement?: ProblemStatement, title: string, visibility: int}
 * @psalm-type BestSolvers=array{classname: string, language: string, memory: float, runtime: float, time: \OmegaUp\Timestamp, username: string}
 * @psalm-type ProblemCasesContents=array<string, array{contestantOutput?: string, in: string, out: string}>
 * @psalm-type RunDetailsGroup=array{cases: list<CaseResult>, contest_score: float, group: string, max_score: float, score: float, verdict?: string}
 * @psalm-type SubmissionFeedbackThread=array{author: string, authorClassname: string, submission_feedback_thread_id: int, text: string, timestamp: \OmegaUp\Timestamp}
 * @psalm-type SubmissionFeedback=array{author: string, author_classname: string, feedback: string, date: \OmegaUp\Timestamp, range_bytes_end: int|null, range_bytes_start: int|null, submission_feedback_id: int, feedback_thread?: list<SubmissionFeedbackThread>}
 * @psalm-type RunDetailsV2=array{admin: bool, cases: ProblemCasesContents, compile_error?: string, details?: array{compile_meta?: array<string, RunMetadata>, groups?: list<RunDetailsGroup>, judged_by: string, max_score?: float, memory?: float, score: float, time?: float, verdict: string, wall_time?: float}, feedback?: string, judged_by?: string, logs?: string, show_diff: string, source?: string, source_link?: bool, source_name?: string, source_url?: string, feedback: null|SubmissionFeedback}
 * @psalm-type RunWithDetails=array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, details: null|RunDetailsV2, execution: null|string, guid: string, language: string, memory: int, output: null|string, penalty: int, runtime: int, score: float, score_by_group?: array<string, float|null>, status: string, status_memory: null|string, status_runtime: null|string, submit_delay: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}
 * @psalm-type ProblemDetails=array{accepts_submissions: bool, accepted: int, admin?: bool, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, difficulty: float|null, email_clarifications: bool, input_limit: int, karel_problem: bool, languages: list<string>, letter?: string, limits: SettingLimits, nextExecutionTimestamp?: \OmegaUp\Timestamp, nextSubmissionTimestamp?: \OmegaUp\Timestamp, nominationStatus: NominationStatus, order: string, points: float, preferred_language?: string, problem_id: int, problemsetter?: ProblemsetterInfo, quality_seal: bool, runs?: list<RunWithDetails>, score: float, settings: ProblemSettingsDistrib, show_diff: string, solvers?: list<BestSolvers>, source?: string, statement: ProblemStatement, submissions: int, title: string, version: string, visibility: int, visits: int}
 * @psalm-type StatsPayload=array{alias: string, entity_type: string, cases_stats?: array<string, int>, pending_runs: list<string>, total_runs: int, verdict_counts: array<string, int>, max_wait_time?: \OmegaUp\Timestamp|null, max_wait_time_guid?: null|string, distribution?: array<int, int>, size_of_bucket?: float, total_points?: float}
 * @psalm-type SelectedTag=array{public: bool, tagname: string}
 * @psalm-type ProblemAdmin=array{role: string, username: string}
 * @psalm-type ProblemGroupAdmin=array{alias: string, name: string, role: string}
 * @psalm-type Signature=array{email: string, name: string, time: \OmegaUp\Timestamp}
 * @psalm-type ProblemVersion=array{author: Signature, commit: string, committer: Signature, message: string, parents: list<string>, tree: array<string, string>, version: string}
 * @psalm-type ProblemEditPayload=array{admins: list<ProblemAdmin>, alias: string, allowUserAddTags: bool, emailClarifications: bool, extraWallTime: float, groupAdmins: list<ProblemGroupAdmin>, inputLimit: int, groupScorePolicy: null|string, languages: string, levelTags: list<string>, log: list<ProblemVersion>, memoryLimit: float, outputLimit: int, overallWallTimeLimit: float, problemLevel: null|string, problemsetter?: ProblemsetterInfo, publicTags: list<string>, publishedRevision: ProblemVersion|null, selectedPublicTags: list<string>, selectedPrivateTags: list<string>, showDiff: string, solution: ProblemStatement|null, source: string, statement: ProblemStatement, statusError?: string, statusSuccess: bool, timeLimit: float, title: string, validLanguages: array<string, string>, validator: string, validatorTimeLimit: float|int, validatorTypes: array<string, null|string>, visibility: int, visibilityStatuses: array<string, int>, cdp: CDP|null}
 * @psalm-type Histogram=array{difficulty: float, difficultyHistogram: null|string, quality: float, qualityHistogram: null|string}
 * @psalm-type ProblemDetailsPayload=array{allowUserAddTags?: bool, hasVisitedSection?: bool, allRuns?: list<Run>, totalRuns?: int, clarifications?: list<Clarification>, histogram: Histogram, isBookmarked?: bool, levelTags?: list<string>, nominationStatus?: NominationStatus, problem: ProblemInfo, problemLevel?: null|string, publicTags?: list<string>, reviewedProblemLevel?: null|string, reviewedPublicTags?: list<string>, reviewedQualitySeal?: bool, runs?: list<Run>, selectedPrivateTags?: list<string>, selectedPublicTags?: list<string>, solutionStatus: string, solvers: list<BestSolvers>, user: UserInfoForProblem, allowedSolutionsToSee: int}
 * @psalm-type ProblemFormPayload=array{alias: string, allowUserAddTags: true, hasVisitedSection?: bool, emailClarifications: bool, extraWallTime: int|string, groupScorePolicy: null|string, inputLimit: int|string, languages: string, levelTags: list<string>, memoryLimit: int|string, message?: string, outputLimit: int|string, overallWallTimeLimit: int|string, parameter: null|string, problem_level: string, publicTags: list<string>, selectedTags: list<SelectedTag>|null, showDiff: string, source: string, statusError: string, tags: list<array{name: null|string}>, timeLimit: int|string, title: string, validLanguages: array<string, string>, validator: string, validatorTimeLimit: int|string, validatorTypes: array<string, null|string>, visibility: int, visibilityStatuses: array<string, int>}
 * @psalm-type ProblemsMineInfoPayload=array{isSysadmin: bool, privateProblemsAlert: bool, visibilityStatuses: array<string, int>, query: string|null}
 * @psalm-type ProblemListPayload=array{selectedTags: list<string>, loggedIn: bool, pagerItems: list<PageItem>, problems: list<ProblemListItem>, keyword: string, language: string, mode: string, column: string, languages: list<string>, columns: list<string>, modes: list<string>, tagData: list<array{name: null|string}>, tags: list<string>}
 * @psalm-type RunsDiff=array{guid: string, new_score: float|null, new_status: null|string, new_verdict: null|string, old_score: float|null, old_status: null|string, old_verdict: null|string, problemset_id: int|null, username: string}
 * @psalm-type CommitRunsDiff=array<string, list<RunsDiff>>
 * @psalm-type AuthorsRank=array{ranking: list<array{author_ranking: int|null, author_score: float, classname: string, country_id: null|string, name: null|string, username: string}>, total: int}
 * @psalm-type TagWithProblemCount=array { name: string, problemCount: int }
 * @psalm-type CollectionDetailsByAuthorPayload=array{authorsRanking: AuthorsRank, selectedTags: list<string>, loggedIn: bool, pagerItems: list<PageItem>, problems: list<ProblemListItem>, keyword: string, language: string, mode: string, column: string, languages: list<string>, columns: list<string>, modes: list<string>, tagData: list<array{name: null|string}>, tags: list<string>, authors: list<string>}
 * @psalm-type CollectionDetailsByLevelPayload=array{frequentTags: list<TagWithProblemCount>, publicTags: list<TagWithProblemCount>, level: string, selectedTags: list<string>, loggedIn: bool, pagerItems: list<PageItem>, problems: list<ProblemListItem>, keyword: string, language: string, mode: string, column: string, languages: list<string>, columns: list<string>, modes: list<string>, tagData: list<array{name: null|string}>, tagsList: list<string>, difficulty: string}
 * @psalm-type Tag=array{name: string}
 * @psalm-type ProblemListCollectionPayload=array{levelTags: list<string>, problemCount: list<array{name: string, problems_per_tag: int}>, allTags: list<Tag>}
 * @psalm-type ProblemPrintDetailsPayload=array{details: ProblemDetails}
 * @psalm-type LibinteractiveError=array{description: string, field: string}
 * @psalm-type LibinteractiveGenPayload=array{error: LibinteractiveError|null, idl: null|string, language: null|string, name: null|string, os: null|string}
 * @psalm-type ProblemRequestData=array{preventProblemsetOpen: bool, contestAlias: null|string, problemAlias: string, statementType: string, problemsetId: int|null}
 */

class Problem extends \OmegaUp\Controllers\Controller {
    // SOLUTION STATUS
    const SOLUTION_NOT_FOUND = 'not_found';
    const SOLUTION_UNLOCKED = 'unlocked';
    const SOLUTION_LOCKED = 'locked';
    const SOLUTION_NOT_LOGGED_IN = 'not_logged_in';
    const RESTRICTED_TAG_NAMES = [
        'problemRestrictedTagKarel',
        'problemRestrictedTagLanguage',
        'problemRestrictedTagOnlyOutput',
        'problemRestrictedTagInteractive',
        'problemRestrictedTagNoSubmissions',
    ];
    const DEFAULT_LANGUAGE = 'es';
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

    const SOURCE_EXTENSIONS = [
        'py', 'cpp', 'c', 'java', 'kp', 'kj', 'in', 'out',
    ];

    // Number of rows shown in problems list
    const PAGE_SIZE = 100;

    // quality values
    const QUALITY_VALUES = ['onlyQualityProblems', 'all'];

    /**
     * Returns a ProblemParams instance from the Request values.
     *
     * @omegaup-request-param bool|null $allow_user_add_tags
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param int|null $extra_wall_time
     * @omegaup-request-param int|null $input_limit
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param int|null $memory_limit
     * @omegaup-request-param int|null $output_limit
     * @omegaup-request-param int|null $overall_wall_time_limit
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param null|string $problem_level
     * @omegaup-request-param null|string $selected_tags
     * @omegaup-request-param null|string $show_diff
     * @omegaup-request-param null|string $group_score_policy
     * @omegaup-request-param null|string $source
     * @omegaup-request-param int|null $time_limit
     * @omegaup-request-param null|string $title
     * @omegaup-request-param null|string $update_published
     * @omegaup-request-param null|string $validator
     * @omegaup-request-param int|null $validator_time_limit
     * @omegaup-request-param null|string $visibility
     */
    private static function convertRequestToProblemParams(
        \OmegaUp\Request $r,
        bool $isRequired = true
    ): \OmegaUp\ProblemParams {
        $params = [
            'problem_alias' => $r->ensureString(
                'problem_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            ),
        ];
        if (!is_null($r['email_clarifications'])) {
            $params['email_clarifications'] = $r->ensureOptionalBool(
                'email_clarifications'
            ) ?? false;
        }
        if (!is_null($r['extra_wall_time'])) {
            $params['extra_wall_time'] = $r->ensureOptionalInt(
                'extra_wall_time'
            ) ?? 0;
        }
        if (!is_null($r['input_limit'])) {
            $params['input_limit'] = $r->ensureOptionalInt('input_limit') ?? 0;
        }
        $languages = $r->ensureOptionalString('languages');
        if (!is_null($languages)) {
            $params['languages'] = $languages;
        }
        if (!is_null($r['memory_limit'])) {
            $params['memory_limit'] = $r->ensureOptionalInt('memory_limit');
        }
        if (!is_null($r['output_limit'])) {
            $params['output_limit'] = $r->ensureOptionalInt('output_limit');
        }
        if (!is_null($r['overall_wall_time_limit'])) {
            $params['overall_wall_time_limit'] = $r->ensureOptionalInt(
                'overall_wall_time_limit'
            );
        }
        $problemLevel = $r->ensureOptionalString('problem_level');
        if (!is_null($problemLevel)) {
            $params['problem_level'] = $problemLevel;
        }
        $selectedTags = $r->ensureOptionalString('selected_tags');
        if (!is_null($selectedTags)) {
            $params['selected_tags'] = $selectedTags;
        }
        $source = $r->ensureOptionalString('source');
        if (!is_null($source)) {
            $params['source'] = $source;
        }
        if (!is_null($r['time_limit'])) {
            $params['time_limit'] = $r->ensureOptionalInt('time_limit');
        }
        $title = $r->ensureOptionalString('title');
        if (!is_null($title)) {
            $params['title'] = $title;
        }
        $updatePublished = $r->ensureOptionalString('update_published');
        if (!is_null($updatePublished)) {
            $params['update_published'] = $updatePublished;
        }
        $validator = $r->ensureOptionalString('validator');
        if (!is_null($validator)) {
            $params['validator'] = $validator;
        }
        if (!is_null($r['validator_time_limit'])) {
            $params['validator_time_limit'] = $r->ensureOptionalInt(
                'validator_time_limit'
            );
        }
        $visibility = $r->ensureOptionalString('visibility');
        if (!is_null($visibility)) {
            $params['visibility'] = \OmegaUp\ProblemParams::stringVisibilityToNumeric(
                $visibility
            );
        }
        $showDiff = $r->ensureOptionalString('show_diff');
        if (!is_null($showDiff)) {
            $params['show_diff'] = $showDiff;
        }
        $groupScorePolicy = $r->ensureOptionalString('group_score_policy');
        if (!is_null($groupScorePolicy)) {
            $params['group_score_policy'] = $groupScorePolicy;
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
            if (!\OmegaUp\Validators::alias($params->problemAlias)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalidAlias',
                    'problem_alias'
                );
            }
            /** @var list<array{tagname: string, public: bool}>|null */
            $selectedTags = !is_null($params->selectedTagsAsJSON) ? json_decode(
                $params->selectedTagsAsJSON,
                associative: true
            ) : null;
            $isLecture = $params->languages === '';
            if (empty($selectedTags) && !$isLecture) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'problemEditTagPublicRequired',
                    'public_tags'
                );
            }
            $hasPublicTags = false;
            if (!is_null($selectedTags)) {
                foreach ($selectedTags as $tag) {
                    if (!$hasPublicTags) {
                        $hasPublicTags = boolval($tag['public']);
                    }
                    if (empty($tag['tagname'])) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException(
                            'parameterEmpty',
                            'tagname'
                        );
                    }
                }
            }
            if (!$hasPublicTags && !$isLecture) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'problemEditTagPublicRequired',
                    'public_tags'
                );
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
            $languages = explode(',', $params->languages);
            \OmegaUp\Validators::validateValidSubset(
                $languages,
                'languages',
                array_merge(
                    [''],
                    array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES())
                )
            );
        }

        return [
            'problem' => $problem,
            'selectedTags' => $selectedTags,
            'languages' => $params->languages,
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
     * @omegaup-request-param bool|null $allow_user_add_tags
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param int|null $extra_wall_time
     * @omegaup-request-param int|null $input_limit
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param int|null $memory_limit
     * @omegaup-request-param int|null $output_limit
     * @omegaup-request-param int|null $overall_wall_time_limit
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param null|string $problem_level
     * @omegaup-request-param null|string $selected_tags
     * @omegaup-request-param null|string $show_diff
     * @omegaup-request-param null|string $group_score_policy
     * @omegaup-request-param null|string $source
     * @omegaup-request-param int|null $time_limit
     * @omegaup-request-param null|string $title
     * @omegaup-request-param null|string $update_published
     * @omegaup-request-param null|string $validator
     * @omegaup-request-param int|null $validator_time_limit
     * @omegaup-request-param null|string $visibility
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentityIsOver13();

        self::createProblem(
            $r->user,
            $r->identity,
            self::convertRequestToProblemParams($r)
        );
        self::invalidateProblemsAclCacheForIdentity($r->identity);
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
        if (empty($params->problemLevel)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'level_tag',
            );
        }
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
        unset($problemSettings['Cases']);
        unset($problemSettings['Interactive']);
        self::updateProblemSettings($problemSettings, $params);
        $acceptsSubmissions = $languages !== '';

        $acl = new \OmegaUp\DAO\VO\ACLs();
        $acl->owner_id = $user->user_id;

        // Create the problem before attempting to communicate with the
        // database.
        $temporaryAlias = (
            "temp.{$params->problemAlias}." .
            intval(microtime(as_float: true) * 1000000)
        );
        $problemDeployer = new \OmegaUp\ProblemDeployer(
            $temporaryAlias,
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

        // Insert new problem
        try {
            \OmegaUp\DAO\DAO::transBegin();

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

            // Add problem level tag
            $tag = \OmegaUp\DAO\Tags::getByName($params->problemLevel);

            if (
                is_null($tag) ||
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

            \OmegaUp\DAO\ProblemsTags::updateProblemLevel(
                $problem,
                $tag
            );

            \OmegaUp\Controllers\Problem::setRestrictedTags(
                $problem,
                $temporaryAlias
            );

            // Once all the checks and validations have been performed, rename
            // the problem to its final name.
            $problemDeployer->renameRepository($params->problemAlias);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            self::$log->error(
                "Failed to create problem {$problem->alias}",
                ['exception' => $e],
            );

            try {
                // Operation failed in the data layer, try to rollback transaction
                \OmegaUp\DAO\DAO::transRollback();
            } catch (\Exception $rollbackException) {
                self::$log->error(
                    'Failed to roll back transaction: ',
                    ['exception' => $rollbackException],
                );
            }

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
     *
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiAddAdmin(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        // Authenticate logged user
        $r->ensureIdentityIsOver13();

        // Check problem_alias
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        $user = \OmegaUp\Controllers\User::resolveUser($r['usernameOrEmail']);
        if (is_null($user->user_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem) || is_null($problem->acl_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Only an admin can add other problem admins
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::addUser($problem->acl_id, $user->user_id);

        $adminIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );
        self::invalidateProblemsAclCacheForIdentity($adminIdentity);

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds a group admin to a problem
     *
     * @omegaup-request-param string $group
     * @omegaup-request-param string $problem_alias
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiAddGroupAdmin(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        // Authenticate logged user
        $r->ensureIdentityIsOver13();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem) || is_null($problem->acl_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $groupAlias = $r->ensureString(
            'group',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $group = \OmegaUp\DAO\Groups::findByAlias($groupAlias);
        if (is_null($group) || is_null($group->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('groupNotFound');
        }

        // Only an admin can add other problem group admins
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        \OmegaUp\Controllers\ACL::addGroup($problem->acl_id, $group->group_id);

        $groupIdentities = \OmegaUp\DAO\GroupsIdentities::getGroupIdentities(
            $group
        );
        foreach ($groupIdentities as $row) {
            $identity = \OmegaUp\DAO\Identities::getByPK(
                $row['identity_id']
            );
            if (is_null($identity)) {
                continue;
            }
            self::invalidateProblemsAclCacheForIdentity($identity);
        }

        return ['status' => 'ok'];
    }

    /**
     * Updates the problem level of a problem
     *
     * @return array{status: string}
     *
     * @omegaup-request-param null|string $level_tag
     * @omegaup-request-param string $problem_alias
     */
    public static function apiUpdateProblemLevel(\OmegaUp\Request $r): array {
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['level_tag'],
            'level_tag'
        );

        $r->ensureIdentity();

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $tag = null;
        if (!empty($r['level_tag'])) {
            $tag = \OmegaUp\DAO\Tags::getByName($r['level_tag']);

            if (
                is_null($tag) ||
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
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{name: string}
     *
     * @omegaup-request-param string $name
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param bool|null $public
     */
    public static function apiAddTag(\OmegaUp\Request $r): array {
        // Check problem_alias
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $tagName = $r->ensureString(
            'name',
            fn (string $name) => \OmegaUp\Validators::alias($name)
        );

        // Authenticate logged user
        $r->ensureIdentity();
        $isPublic = $r->ensureOptionalBool('public') ?? false;

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (!\OmegaUp\Authorization::canEditProblem($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        self::addTag($tagName, $isPublic, $problem);

        return [
            'name' => $tagName,
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
                    'public' => true,
                ]);
            } else {
                if ($isPublic) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'newPublicTagsNotAllowed',
                        'public'
                    );
                }

                // After normalization problemTag becomes problemtag
                if (strpos($tagName, 'problemtag') === 0) {
                    // Starts with 'problemtag'
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
            'source' => 'owner',
        ]));
    }

    /**
     * Removes an admin from a problem
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiRemoveAdmin(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check problem_alias
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );
        if (is_null($identity->user_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem) || is_null($problem->acl_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Only admin is allowed to make modifications
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Check if admin to delete is actually an admin
        if (!\OmegaUp\Authorization::isProblemAdmin($identity, $problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        $adminIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );
        self::invalidateProblemsAclCacheForIdentity($adminIdentity);

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
     *
     * @omegaup-request-param string $group
     * @omegaup-request-param string $problem_alias
     */
    public static function apiRemoveGroupAdmin(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem) || is_null($problem->acl_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $groupAlias = $r->ensureString(
            'group',
            fn (string $group) => \OmegaUp\Validators::alias($group)
        );
        $group = \OmegaUp\DAO\Groups::findByAlias($groupAlias);
        if (is_null($group) || is_null($group->group_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        // Only admin is allowed to make modifications
        if (!\OmegaUp\Authorization::isProblemAdmin($r->identity, $problem)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $groupIdentities = \OmegaUp\DAO\GroupsIdentities::getGroupIdentities(
            $group
        );
        foreach ($groupIdentities as $row) {
            $identity = \OmegaUp\DAO\Identities::getByPK(
                $row['identity_id']
            );
            if (is_null($identity)) {
                continue;
            }
            self::invalidateProblemsAclCacheForIdentity($identity);
        }

        \OmegaUp\Controllers\ACL::removeGroup(
            $problem->acl_id,
            $group->group_id
        );

        return ['status' => 'ok'];
    }

    /**
     * Removes a tag from a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $name
     * @omegaup-request-param string $problem_alias
     */
    public static function apiRemoveTag(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check whether problem exists
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $tagName = $r->ensureString(
            'name',
            fn (string $name) => \OmegaUp\Validators::alias($name)
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $tag = \OmegaUp\DAO\Tags::getByName($tagName);
        if (is_null($tag)) {
            throw new \OmegaUp\Exceptions\NotFoundException('tagNotFound');
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
     *
     * @omegaup-request-param string $problem_alias
     */
    public static function apiDelete(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check whether problem exists
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
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
     * @return array{admins: list<ProblemAdmin>, group_admins: list<ProblemGroupAdmin>}
     *
     * @omegaup-request-param string $problem_alias
     */
    public static function apiAdmins(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
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
     *
     * @omegaup-request-param mixed $include_voted
     * @omegaup-request-param string $problem_alias
     */
    public static function apiTags(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $includeVoted = ($r['include_voted'] == 'true');
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
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
     *
     * @omegaup-request-param string $problem_alias
     */
    public static function apiRejudge(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
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
            self::$log->error(
                "Failed to rejudge problem {$problem->alias}",
                ['exception' => $e],
            );
            try {
                // Operation failed in the data layer, try to rollback transaction
                \OmegaUp\DAO\DAO::transRollback();
            } catch (\Exception $rollbackException) {
                self::$log->error(
                    'Failed to roll back transaction',
                    ['exception' => $rollbackException],
                );
            }
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
     * @omegaup-request-param bool|null $allow_user_add_tags
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param int|null $extra_wall_time
     * @omegaup-request-param int|null $input_limit
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param int|null $memory_limit
     * @omegaup-request-param string $message
     * @omegaup-request-param int|null $output_limit
     * @omegaup-request-param int|null $overall_wall_time_limit
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param null|string $problem_level
     * @omegaup-request-param bool|string $redirect
     * @omegaup-request-param null|string $selected_tags
     * @omegaup-request-param null|string $show_diff
     * @omegaup-request-param null|string $group_score_policy
     * @omegaup-request-param null|string $source
     * @omegaup-request-param int|null $time_limit
     * @omegaup-request-param null|string $title
     * @omegaup-request-param null|string $update_published
     * @omegaup-request-param null|string $validator
     * @omegaup-request-param int|null $validator_time_limit
     * @omegaup-request-param null|string $visibility
     */
    public static function apiUpdate(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        $problemParams = self::convertRequestToProblemParams(
            $r,
            isRequired: false
        );

        [
            'rejudged' => $rejudged,
        ] = self::updateProblem(
            $r->identity,
            $r->user,
            $problemParams,
            $r->ensureString(
                'message',
                fn (string $message) => \OmegaUp\Validators::stringNonEmpty(
                    $message
                )
            ),
            $problemParams->updatePublished,
            $r->ensureOptionalBool('redirect') ?? false
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
    private static function diffLimitsSettings($a, $b): bool {
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
     * @param array{Limits: LimitsSettings, Slow: bool, Validator: array{GroupScorePolicy?: string, Lang?: string, Limits?: array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}, Name: string, Tolerance: float}} $a
     * @param array{Limits: LimitsSettings, Slow: bool, Validator: array{GroupScorePolicy?: string, Lang?: string, Limits?: array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}, Name: string, Tolerance: float}} $b
     */
    private static function diffProblemSettings($a, $b): bool {
        if (self::diffLimitsSettings($a['Limits'], $b['Limits'])) {
            return true;
        }
        if ($a['Validator']['Name'] !== $b['Validator']['Name']) {
            return true;
        }
        if (
            isset($a['Validator']['Tolerance']) !==
            isset($b['Validator']['Tolerance'])
        ) {
            return true;
        }
        if (
            isset($a['Validator']['Tolerance']) &&
            isset($b['Validator']['Tolerance']) &&
            $a['Validator']['Tolerance'] !== $b['Validator']['Tolerance']
        ) {
            return true;
        }
        if (
            isset($a['Validator']['GroupScorePolicy']) !==
            isset($b['Validator']['GroupScorePolicy'])
        ) {
            return true;
        }
        if (
            isset($a['Validator']['GroupScorePolicy']) &&
            isset($b['Validator']['GroupScorePolicy']) &&
            $a['Validator']['GroupScorePolicy'] !== $b['Validator']['GroupScorePolicy']
        ) {
            return true;
        }
        if (
            empty($a['Validator']['Limits']) !==
            empty($b['Validator']['Limits'])
        ) {
            return true;
        }
        if (
            empty($a['Validator']['Limits']) ||
            empty($b['Validator']['Limits'])
        ) {
            // No further checks are necessary.
            return false;
        }
        return self::diffLimitsSettings(
            $a['Validator']['Limits'],
            $b['Validator']['Limits']
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
            isRequired: false
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
            'groupScorePolicy' => [
                'alias' => 'group_score_policy',
            ],
            'allowUserAddTags' => [
                'alias' => 'allow_user_add_tags',
            ],
            'source',
            'order',
            'languages' => [
                'transform' =>
                /** @param list<string>|string $value */
                fn ($value): string => (is_array(
                    $value
                ) ? join(
                    ',',
                    $value
                ) : $value)
            ],
        ];
        $params->updateValueParams($problem, $valueProperties);
        $problem->languages = $languages ?: $problem->languages;

        $response = [
            'rejudged' => false,
        ];

        $problemSettings = self::getProblemSettings(
            $problem,
            $problem->commit
        );
        unset($problemSettings['Cases']);
        unset($problemSettings['Interactive']);
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
                && isset($_FILES['problem_contents']['tmp_name'])
                && \OmegaUp\FileHandler::getFileUploader()->isUploadedFile(
                    $_FILES['problem_contents']['tmp_name']
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
        } catch (\Exception $e) {
            self::$log->error(
                "Failed to update problem {$problem->alias}",
                ['exception' => $e],
            );
            try {
                // Operation failed in the data layer, try to rollback transaction
                \OmegaUp\DAO\DAO::transRollback();
            } catch (\Exception $rollbackException) {
                self::$log->error(
                    'Failed to roll back transaction',
                    ['exception' => $rollbackException],
                );
            }

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
                    ['exception' => $e],
                );
            }
        }

        if ($redirect === true) {
            header('Location: ' . (
                \OmegaUp\Request::getServerVar('HTTP_REFERER') ?? '/'
            ));
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

    private static function setRestrictedTags(
        \OmegaUp\DAO\VO\Problems $problem,
        ?string $temporaryAlias = null
    ): void {
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
        } elseif (in_array('', $languages)) {
            \OmegaUp\Controllers\Problem::addTag(
                'problemRestrictedTagNoSubmissions',
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
            $temporaryAlias ?? strval($problem->alias)
        );
        /** @var ProblemSettingsDistrib */
        $distribSettings = json_decode(
            $problemArtifacts->get('settings.distrib.json'),
            associative: true
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
     * @omegaup-request-param bool|null $allow_user_add_tags
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param int|null $extra_wall_time
     * @omegaup-request-param int|null $input_limit
     * @omegaup-request-param string $lang
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param int|null $memory_limit
     * @omegaup-request-param string $message
     * @omegaup-request-param int|null $output_limit
     * @omegaup-request-param int|null $overall_wall_time_limit
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param null|string $problem_level
     * @omegaup-request-param null|string $selected_tags
     * @omegaup-request-param null|string $show_diff
     * @omegaup-request-param null|string $group_score_policy
     * @omegaup-request-param null|string $source
     * @omegaup-request-param string $statement
     * @omegaup-request-param int|null $time_limit
     * @omegaup-request-param null|string $title
     * @omegaup-request-param null|string $update_published
     * @omegaup-request-param null|string $validator
     * @omegaup-request-param int|null $validator_time_limit
     * @omegaup-request-param null|string $visibility
     */
    public static function apiUpdateStatement(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        $problemParams = self::convertRequestToProblemParams(
            $r,
            isRequired: false
        );
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        $statement = $r->ensureString('statement');
        $message = $r->ensureString('message');
        $language = $r->ensureOptionalEnum(
            'lang',
            \OmegaUp\Controllers\Problem::ISO639_1
        );
        self::updateStatement(
            $r->identity,
            $r->user,
            $problem,
            directory: 'statements',
            statement: $statement,
            message: $message,
            lang: $language,
            updatePublished: $problemParams->updatePublished
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
     * @omegaup-request-param bool|null $allow_user_add_tags
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param int|null $extra_wall_time
     * @omegaup-request-param null|string $group_score_policy
     * @omegaup-request-param int|null $input_limit
     * @omegaup-request-param null|string $lang
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param int|null $memory_limit
     * @omegaup-request-param string $message
     * @omegaup-request-param int|null $output_limit
     * @omegaup-request-param int|null $overall_wall_time_limit
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param null|string $problem_level
     * @omegaup-request-param null|string $selected_tags
     * @omegaup-request-param null|string $show_diff
     * @omegaup-request-param string $solution
     * @omegaup-request-param null|string $source
     * @omegaup-request-param int|null $time_limit
     * @omegaup-request-param null|string $title
     * @omegaup-request-param null|string $update_published
     * @omegaup-request-param null|string $validator
     * @omegaup-request-param int|null $validator_time_limit
     * @omegaup-request-param null|string $visibility
     */
    public static function apiUpdateSolution(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        $problemParams = self::convertRequestToProblemParams(
            $r,
            isRequired: false
        );
        [
            'problem' => $problem,
        ] = self::validateCreateOrUpdate(
            $r->identity,
            $problemParams,
            isRequired: false
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        $solution = $r->ensureString('solution');
        $message = $r->ensureString('message');

        self::updateStatement(
            $r->identity,
            $r->user,
            $problem,
            directory: 'solutions',
            statement: $solution,
            message: $message,
            lang: \OmegaUp\Controllers\Identity::getPreferredLanguage(
                $r->identity
            ),
            updatePublished: $problemParams->updatePublished
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

    private static function invalidateProblemsAclCacheForIdentity(
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        $identityId = $identity->identity_id;
        $userId = $identity->user_id ?? 'null';
        $cacheKey = "{$identityId}-{$userId}";

        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::PROBLEM_IDENTITY_TYPE,
            $cacheKey
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
     * @return array{problem: null|\OmegaUp\DAO\VO\Problems, problemset: null|\OmegaUp\DAO\VO\Problemsets}
     */
    private static function validateDetails(
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?string $contestAlias,
        string $problemAlias,
        string $statementType,
        ?int $problemsetId
    ): array {
        $response = [
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

        $response['problem'] = $problem;

        if (!is_null($problemset) && isset($problemset['problemset'])) {
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
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
                            !\OmegaUp\DAO\ProblemsetIdentities::existsByPK(
                                $identity->identity_id,
                                $problemset['problemset']->problemset_id
                            )
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

                    $assignment = \OmegaUp\DAO\Assignments::getAssignmentForProblemset(
                        $problemsetId
                    );

                    if (
                        !is_null($assignment)
                        && $assignment->start_time->time > \OmegaUp\Time::get()
                    ) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                            'problemNotFound'
                        );
                    }
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
                // check that it is not private and the user is logged in
                if (!\OmegaUp\DAO\Problems::isVisible($problem)) {
                    if (is_null($identity)) {
                        throw new \OmegaUp\Exceptions\UnauthorizedException(
                            'userNotAllowed'
                        );
                    } else {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                            'problemIsPrivate'
                        );
                    }
                }
            }
        }
        return $response;
    }

    /**
     * Gets the problem resource (statement/solution) from the gitserver.
     *
     * @param array{directory: string, alias: null|string, commit: string, language: string} $params
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return ProblemStatement|null The contents of the resource, plus some metadata.
     */
    public static function getProblemResourceImpl(array $params): ?array {
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
            'sources' => [],
        ];
        try {
            $result['markdown'] = mb_convert_encoding(
                $problemArtifacts->get(
                    $sourcePath
                ),
                'utf-8'
            );
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            return null;
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \OmegaUp\Exceptions\InvalidFilesystemOperationException(
                'statementNotFound'
            );
        }

        // Get all the images' mappings.
        $statementFiles = $problemArtifacts->lsTree($params['directory']);
        foreach ($statementFiles as $file) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (in_array($extension, self::IMAGE_EXTENSIONS)) {
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
            } elseif (in_array($extension, self::SOURCE_EXTENSIONS)) {
                if ($file['size'] > 8192) {
                    // File is too big. Add an error message.
                    $result['sources'][$file['name']] = "File exceeded the 8192-byte limit: {$file['size']} bytes";
                    continue;
                }
                $result['sources'][$file['name']] = mb_convert_encoding(
                    $problemArtifacts->get(
                        "{$params['directory']}/{$file['name']}"
                    ),
                    'utf-8'
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
     * @return ProblemStatement|null The contents of the file.
     */
    public static function getProblemStatement(
        string $alias,
        string $commit,
        string $language
    ): ?array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_STATEMENT,
            "{$alias}-{$commit}-{$language}-markdown",
            fn () => \OmegaUp\Controllers\Problem::getProblemResourceImpl([
                'directory' => 'statements',
                'alias' => $alias,
                'commit' => $commit,
                'language' => $language,
            ]),
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
    ): ?array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_SOLUTION,
            "{$problem->alias}-{$commit}-{$language}-markdown",
            fn () => \OmegaUp\Controllers\Problem::getProblemResourceImpl([
                'directory' => 'solutions',
                'alias' => strval($problem->alias),
                'commit' => $commit,
                'language' => $language,
            ]),
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );
    }

    /**
     * Gets the problem settings for the problem, using the cache if needed.
     *
     * @return ProblemSettings
     */
    private static function getProblemSettings(
        \OmegaUp\DAO\VO\Problems $problem,
        string $commit
    ): array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_SETTINGS,
            "{$problem->alias}-{$problem->commit}",
            fn () => \OmegaUp\Controllers\Problem::getProblemSettingsImpl([
                'alias' => strval($problem->alias),
                'commit' => $problem->commit,
            ]),
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );
    }

    /**
     * Gets the problem settings for the problem.
     *
     * @param array{alias: string, commit: string} $params
     *
     * @return ProblemSettings
     */
    public static function getProblemSettingsImpl(array $params): array {
        /** @var ProblemSettings */
        return json_decode(
            (new \OmegaUp\ProblemArtifacts(
                $params['alias'],
                $params['commit']
            ))->get(
                'settings.json'
            ),
            associative: true
        );
    }

    /**
     * Gets the distributable problem settings for the problem, using the cache
     * if needed.
     *
     * @return ProblemSettingsDistrib
     */
    private static function getProblemSettingsDistrib(
        \OmegaUp\DAO\VO\Problems $problem,
        string $commit
    ) {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_SETTINGS_DISTRIB,
            "{$problem->alias}-{$problem->commit}",
            fn () => \OmegaUp\Controllers\Problem::getProblemSettingsDistribImpl([
                'alias' => strval($problem->alias),
                'commit' => $problem->commit,
            ]),
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );
    }

    /**
     * Gets the distributable problem settings for the problem.
     *
     * @param array{alias: string, commit: string} $params
     *
     * @return ProblemSettingsDistrib
     */
    public static function getProblemSettingsDistribImpl(array $params): array {
        /** @var array */
        $settings = json_decode(
            (new \OmegaUp\ProblemArtifacts(
                $params['alias'],
                $params['commit']
            ))->get(
                'settings.distrib.json'
            ),
            associative: true
        );
        if (empty($settings)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        /** @var ProblemSettingsDistrib */
        return $settings;
    }

    /**
     * Entry point for Problem Download API
     *
     * @param \OmegaUp\Request $r
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return void
     *
     * @omegaup-request-param string $problem_alias
     */
    public static function apiDownload(\OmegaUp\Request $r): void {
        $r->ensureIdentity();

        // Validate request
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problem = self::validateDownload($r->identity, $problemAlias);

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/zip');
        header(
            "Content-Disposition: attachment;filename={$problem->alias}.zip"
        );
        header('Content-Transfer-Encoding: binary');

        // Try to download from the published branch first. If it doesn't exist,
        // fallback to the current commit.
        $problemArtifacts = null;
        try {
            $problemArtifacts = new \OmegaUp\ProblemArtifacts(
                strval($problem->alias),
                'published'
            );
        } catch (\Exception $e) {
            self::$log->warning(
                "Failed to get published revision for {$problem->alias}, " .
                'falling back to current commit',
                ['exception' => $e],
            );
            try {
                $problemArtifacts = new \OmegaUp\ProblemArtifacts(
                    strval($problem->alias),
                    strval($problem->commit)
                );
            } catch (\Exception $e) {
                self::$log->error(
                    "Failed to get problem artifacts for {$problem->alias} " .
                    'using current commit',
                    ['exception' => $e],
                );
                throw new \OmegaUp\Exceptions\ServiceUnavailableException(
                    'gitServerError',
                    $e
                );
            }
        }

        try {
            $problemArtifacts->download();
        } catch (\Exception $e) {
            self::$log->error(
                "Failed to download problem {$problem->alias}",
                ['exception' => $e],
            );
            throw new \OmegaUp\Exceptions\ServiceUnavailableException(
                'downloadError',
                $e
            );
        }

        // Since all the headers and response have been sent, make the API
        // caller to exit quietly.
        throw new \OmegaUp\Exceptions\ExitException();
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
            // Is the problem actually in the problemset?
            if (
                !\OmegaUp\DAO\ProblemsetProblems::existsByPK(
                    $response['problemset']->problemset_id,
                    $problem->problem_id
                )
            ) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFoundInContest'
                );
            }
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
            // Is the problem actually in the problemset?
            if (
                !\OmegaUp\DAO\ProblemsetProblems::existsByPK(
                    $response['problemset']->problemset_id,
                    $problem->problem_id
                )
            ) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFoundInProblemset'
                );
            }
        } else {
            // Nothing to see here, move along.
            return null;
        }

        return $response;
    }

    /**
     * Entry point for Problem Details API
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return ProblemDetails
     *
     * @omegaup-request-param null|string $contest_alias
     * @omegaup-request-param null|string $lang
     * @omegaup-request-param bool|null $prevent_problemset_open
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param int|null $problemset_id
     * @omegaup-request-param bool|null $show_solvers
     * @omegaup-request-param null|string $statement_type
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        $showSolvers = $r->ensureOptionalBool('show_solvers') ?? false;
        $preventProblemsetOpen = $r->ensureOptionalBool(
            'prevent_problemset_open'
        ) ?? false;
        $contestAlias = $r->ensureOptionalString(
            'contest_alias',
            required: false,
            validator: fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
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
        [
            'problem' => $problem,
            'problemset' => $problemset,
        ] = self::getValidProblemAndProblemset(
            $r->identity,
            $contestAlias,
            $problemAlias,
            $r->ensureOptionalString('statement_type') ?? '',
            $r->ensureOptionalInt('problemset_id')
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        $details = self::getProblemDetails(
            $r->identity,
            $problem,
            $problemset,
            $lang,
            $showSolvers,
            $preventProblemsetOpen,
            $contestAlias
        );
        if (is_null($details)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        return $details;
    }

    /**
     * Get user. Allow unauthenticated requests if we are not opening a problem
     * inside a contest
     *
     * @throws \OmegaUp\Exceptions\UnauthorizedException
     *
     * @return array{problem: null|\OmegaUp\DAO\VO\Problems, problemset: null|\OmegaUp\DAO\VO\Problemsets}
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
     * @return ProblemDetails
     */
    public static function getProblemDetails(
        ?\OmegaUp\DAO\VO\Identities $loggedIdentity,
        \OmegaUp\DAO\VO\Problems $problem,
        ?\OmegaUp\DAO\VO\Problemsets $problemset,
        string $statementLanguage,
        bool $showSolvers,
        bool $preventProblemsetOpen,
        ?string $contestAlias = null
    ): ?array {
        if (is_null($problem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
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

        $response = [];
        $response['statement'] = \OmegaUp\Controllers\Problem::getProblemStatement(
            strval($problem->alias),
            $commit,
            $statementLanguage
        );
        if (is_null($response['statement'])) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'statementNotFound'
            );
        }
        $response['settings'] = \OmegaUp\Controllers\Problem::getProblemSettingsDistrib(
            $problem,
            $commit
        );

        // Add preferred language of the user.
        if (!is_null($loggedIdentity)) {
            $preferredLanguage = \OmegaUp\DAO\Users::getPreferredLanguage(
                $loggedIdentity->user_id
            );
            if (!empty($preferredLanguage)) {
                $response['preferred_language'] = strval(
                    $preferredLanguage
                );
            }
        }

        // Add the problem to the response
        $response['problem_id'] = $problem->problem_id;
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
        $response['show_diff'] = $problem->show_diff;
        $response['quality_seal'] = $problem->quality_seal;
        $response['version'] = $version;
        $response['commit'] = $commit;

        // Add the default problem's nomination status to response
        $response['nominationStatus'] = [
            'alreadyReviewed' => false,
            'dismissed' => false,
            'dismissedBeforeAc' => false,
            'nominated' => false,
            'nominatedBeforeAc' => false,
            'language' => '',
            'canNominateProblem' => false,
            'solved' => false,
            'tried' => false,
        ];

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
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            $problemsetter = \OmegaUp\DAO\Identities::findByUserId(
                $acl->owner_id
            );
            if (is_null($problemsetter) || is_null($problemsetter->username)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
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

        $problemsetId = $problemset?->problemset_id ?? null;

        $isPracticeMode = false;
        $container = null;
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
                $isPracticeMode = (
                    $container instanceof \OmegaUp\DAO\VO\Contests &&
                    $container->admission_mode !== 'private' &&
                    $container->finish_time->time < \OmegaUp\Time::get()
                );
                if (!$isPracticeMode) {
                    // Check and save first time access is not needed for
                    // contests in practice mode
                    \OmegaUp\DAO\ProblemsetIdentities::checkAndSaveFirstTimeAccess(
                        $loggedIdentity,
                        $container,
                        \OmegaUp\Authorization::canSubmitToProblemset(
                            $loggedIdentity,
                            $problemset
                        )
                    );
                }
            }

            // As last step, register the problem as opened
            if (
                !\OmegaUp\DAO\ProblemsetProblemOpened::getByPK(
                    $problemsetId,
                    $problem->problem_id,
                    $loggedIdentity->identity_id
                ) &&
                !$isPracticeMode
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
                $problem->problem_id
            );
        }

        if (
            !is_null(
                $loggedIdentity
            ) && !is_null(
                $loggedIdentity->identity_id
            )
        ) {
            // Get all the available runs done by the current_user
            $runsArray = \OmegaUp\DAO\Runs::getForProblemDetails(
                $problem->problem_id,
                $isPracticeMode ? null : $problemsetId,
                $loggedIdentity->identity_id
            );

            // Add each filtered run to an array
            $results = [];
            foreach ($runsArray as $run) {
                $run['alias'] = strval($problem->alias);
                $run['username'] = strval($loggedIdentity->username);
                $run['details'] = null;
                $results[] = $run;
            }
            $response['runs'] = $results;

            \OmegaUp\DAO\ProblemViewed::MarkProblemViewed(
                $loggedIdentity->identity_id,
                $problem->problem_id
            );
            if ($container instanceof \OmegaUp\DAO\VO\Contests) {
                $lastRunTime = null;

                if (count($runsArray) > 0) {
                    $lastRunTime = max(
                        array_map(
                            fn($run) => $run['time'],
                            $runsArray
                        )
                    );
                }
                $response['nextSubmissionTimestamp'] = \OmegaUp\DAO\Runs::nextSubmissionTimestamp(
                    $container,
                    $lastRunTime
                );
                $response['nextExecutionTimestamp'] = \OmegaUp\DAO\Runs::nextExecutionTimestamp(
                    $lastRunTime
                );
            }

            // Fill nomination status
            $nominationStatus = \OmegaUp\DAO\QualityNominations::getNominationStatusForProblem(
                $problem->problem_id,
                $loggedIdentity->user_id
            );
            $response['nominationStatus'] = [
                'alreadyReviewed' => \OmegaUp\DAO\QualityNominations::reviewerHasQualityTagNominatedProblem(
                    $loggedIdentity,
                    $problem
                ),
                'dismissed' => $nominationStatus['dismissed'],
                'dismissedBeforeAc' => $nominationStatus['dismissedBeforeAc'],
                'nominated' => $nominationStatus['nominated'],
                'nominatedBeforeAc' => $nominationStatus['nominatedBeforeAc'],
                'language' => $response['statement']['language'],
                'canNominateProblem' => !is_null($loggedIdentity->user_id),
                'solved' => false,
                'tried' => false,
            ];

            foreach ($runsArray as $run) {
                if ($run['verdict'] === 'AC') {
                    $response['nominationStatus']['solved'] = true;
                    break;
                } elseif ($run['verdict'] !== 'JE' && $run['verdict'] !== 'VE' && $run['verdict'] !== 'CE') {
                    $response['nominationStatus']['tried'] = true;
                }
            }
        }

        // send the supported languages as a JSON array instead of csv
        // array_filter is needed to handle when $response['languages'] is empty
        /** @var list<string> */
        $response['languages'] = array_filter(
            explode(',', $problem->languages)
        );
        $response['accepts_submissions'] = !empty($response['languages']);
        $response['karel_problem'] = count(
            array_intersect(
                $response['languages'],
                ['kp', 'kj']
            )
        ) === 2;
        $response['limits'] = [
            'input_limit' => ($response['input_limit'] / 1024) . ' KiB',
            'memory_limit' => (
                intval(
                    $response['settings']['limits']['MemoryLimit']
                ) / 1024 / 1024
            ) . ' MiB',
            'overall_wall_time_limit' => $response['settings']['limits']['OverallWallTimeLimit'],
            'time_limit' => $response['settings']['limits']['TimeLimit'],
        ];

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
     * @return array{solution: ProblemStatement|null}
     *
     * @omegaup-request-param null|string $contest_alias
     * @omegaup-request-param bool|null $forfeit_problem
     * @omegaup-request-param null|string $lang
     * @omegaup-request-param null|string $problem_alias
     * @omegaup-request-param mixed $problemset_id
     * @omegaup-request-param null|string $statement_type
     */
    public static function apiSolution(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contestAlias = $r->ensureOptionalString(
            'contest_alias',
            required: false,
            validator: fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        // Validate request
        $response = self::validateDetails(
            $r->identity,
            $contestAlias,
            $problemAlias,
            $r->ensureOptionalString('statement_type') ?? '',
            !is_null($r['problemset_id']) ? intval($r['problemset_id']) : null
        );
        if (is_null($response['problem'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problemset = $response['problemset'];
        $problem = $response['problem'];
        $lang = \OmegaUp\Controllers\Identity::getPreferredLanguage(
            $r->identity,
            $r
        );

        // Get the expected commit.
        $commit = $problem->commit;
        if (!empty($problemset)) {
            $problemsetProblem = \OmegaUp\DAO\ProblemsetProblems::getByPK(
                $problemset->problemset_id,
                $problem->problem_id
            );
            if (is_null($problemsetProblem)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }
            $commit = $problemsetProblem->commit;
        }

        $response = [
            'solution' => \OmegaUp\Controllers\Problem::getProblemSolution(
                $problem,
                $commit,
                $lang
            ),
        ];

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
            $seenSolutions = \OmegaUp\DAO\ProblemsForfeited::getProblemsForfeitedCountInDay(
                $r->user
            );
            $allowedSolutions = intval(
                \OmegaUp\Controllers\ProblemForfeited::SOLUTIONS_ALLOWED_TO_SEE_PER_DAY
            );
            // Validate that the user will not exceed the number of allowed solutions.
            if ($seenSolutions >= $allowedSolutions) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'allowedSolutionsLimitReached'
                );
            }
            if (!is_null($response['solution'])) {
                // We don't consume a token if there is no solution.
                \OmegaUp\DAO\ProblemsForfeited::create(new \OmegaUp\DAO\VO\ProblemsForfeited([
                    'user_id' => $r->user->user_id,
                    'problem_id' => $problem->problem_id
                ]));
            }
        }

        return $response;
    }

    /**
     * Entry point for Problem Versions API
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{published: string, log: list<ProblemVersion>}
     *
     * @omegaup-request-param null|string $problem_alias
     * @omegaup-request-param int|null $problemset_id
     */
    public static function apiVersions(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $problemsetId = $r->ensureOptionalInt('problemset_id');

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem) || is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        return self::getVersions($problem, $r->identity, $problemsetId);
    }

    /**
     * @return array{published: string, log: list<ProblemVersion>}
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function getVersions(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity,
        ?int $problemsetId = null
    ) {
        if (is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        if (
            !\OmegaUp\Authorization::canEditProblem($identity, $problem) &&
            (
                is_null($problemsetId) ||
                !\OmegaUp\Authorization::canEditProblemset(
                    $identity,
                    $problemsetId
                )
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
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

        if (is_null($commit)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        return [
            'published' => $commit['commit'],
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
     *
     * @omegaup-request-param null|string $commit
     * @omegaup-request-param null|string $problem_alias
     * @omegaup-request-param null|string $update_published
     */
    public static function apiSelectVersion(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
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

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
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
            self::$log->error(
                "Failed to update problem {$problem->alias}: ",
                ['exception' => $e],
            );
            try {
                // Operation failed in the data layer, try to rollback transaction
                \OmegaUp\DAO\DAO::transRollback();
            } catch (\Exception $rollbackException) {
                self::$log->error(
                    'Failed to roll back transaction: ',
                    ['exception' => $rollbackException],
                );
            }

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
                    ['exception' => $e],
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
     * @return array{diff: list<RunsDiff>}
     *
     * @omegaup-request-param null|string $problem_alias
     * @omegaup-request-param string $version
     */
    public static function apiRunsDiff(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['version'], 'version');

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
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
     * @return array{runs: list<Run>, totalRuns: int}
     */
    private static function getAllRuns(
        int $problemId,
        ?string $status = null,
        ?string $verdict = null,
        ?string $language = null,
        ?int $identityId = null,
        ?int $offset = 0,
        ?int $rowCount = 100,
        ?string $execution = null,
        ?string $output = null,
    ): array {
        // Get our runs
        [
            'runs' => $runs,
            'totalRuns' => $totalRuns,
        ] = \OmegaUp\DAO\Runs::getAllRuns(
            problemsetId: null,
            status: $status,
            verdict: $verdict,
            problemId: $problemId,
            language: $language,
            identityId: $identityId,
            offset: $offset,
            rowCount: $rowCount,
            execution: $execution,
            output: $output,
        );

        $allRuns = [];
        foreach ($runs as $run) {
            unset($run['run_id']);
            $run['contest_score'] = floatval($run['contest_score']);
            $allRuns[] = $run;
        }

        return [
            'runs' => $allRuns,
            'totalRuns' => $totalRuns,
        ];
    }

    /**
     * Entry point for Problem runs API
     *
     * @omegaup-request-param null|string $language
     * @omegaup-request-param int|null $offset
     * @omegaup-request-param null|string $problem_alias
     * @omegaup-request-param int|null $rowcount
     * @omegaup-request-param bool|null $show_all
     * @omegaup-request-param null|string $status
     * @omegaup-request-param null|string $username
     * @omegaup-request-param null|string $verdict
     * @omegaup-request-param null|string $execution
     * @omegaup-request-param null|string $output
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{runs: list<Run>, totalRuns: int}
     */
    public static function apiRuns(\OmegaUp\Request $r): array {
        // Get user
        $r->ensureIdentity();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        // Validate request
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem) || is_null($problem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if ($r->ensureOptionalBool('show_all') ?? false) {
            if (
                !\OmegaUp\Authorization::isProblemAdmin(
                    $r->identity,
                    $problem
                )
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $identity = null;
            $username = $r->ensureOptionalString('username');
            if (!is_null($username)) {
                try {
                    $identity = \OmegaUp\DAO\Identities::findByUsername(
                        $username
                    );
                } catch (\Exception $e) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'userNotExist'
                    );
                }
            }
            return self::getAllRuns(
                $problem->problem_id,
                $r->ensureOptionalString('status'),
                $r->ensureOptionalString('verdict'),
                $r->ensureOptionalString('language'),
                !is_null($identity) ? intval($identity->identity_id) : null,
                max($r->ensureOptionalInt('offset') ?? 0, 0),
                $r->ensureOptionalInt('rowcount') ?? 100,
                $r->ensureOptionalString('execution'),
                $r->ensureOptionalString('output')
            );
        }
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
        return [
            'runs' => $result,
            'totalRuns' => 0
        ];
    }

    /**
     * Entry point for Problem clarifications API
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return array{clarifications: list<Clarification>}
     *
     * @omegaup-request-param int|null $offset
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param int|null $rowcount
     */
    public static function apiClarifications(\OmegaUp\Request $r): array {
        // Get user
        $r->ensureIdentity();

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $offset = $r->ensureOptionalInt('offset');
        $rowcount = $r->ensureOptionalInt('rowcount') ?? 0;
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
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
            $offset,
            $rowcount
        );

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
     * @return array{cases_stats: array<string, int>, pending_runs: list<string>, total_runs: int, verdict_counts: array<string, int>}
     *
     * @omegaup-request-param string $problem_alias
     */
    public static function apiStats(\OmegaUp\Request $r): array {
        // Get user
        $r->ensureIdentity();

        // Validate request
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        return self::getStats($problem, $r->identity);
    }

    /**
     * @return array{templateProperties: array{payload: StatsPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $problem_alias
     */
    public static function getStatsDataForTypeScript(\OmegaUp\Request $r) {
        // Get user
        $r->ensureIdentity();

        // Validate request
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        return [
            'templateProperties' => [
                'payload' => array_merge(
                    [
                        'alias' => $problemAlias,
                        'entity_type' => 'problem',
                    ],
                    self::getStats($problem, $r->identity)
                ),
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleProblemStats'
                ),
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
                missingOk: true
            );
            if (!is_null($detailsJson)) {
                /** @var null|array{verdict: string, compile_meta: array{Main: RunMetadata}, score: int, contest_score: int, max_score: int, time: float, wall_time: float, memory: int, judged_by: string, groups: list<array{group: string, score: float, contest_score: int, max_score: int, cases: list<CaseResult>}>} */
                $details = json_decode($detailsJson, associative: true);
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
                                $casesStats['counts'][$caseName] = 0;
                            }
                            if ($case['score'] === 0.0) {
                                continue;
                            }
                            $casesStats['counts'][$caseName]++;
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
     * @return array{difficultyRange: array{0: int, 1: int}|null, keyword: string, language: string, minVisibility: int, sortOrder: string, orderBy: string, page: int, programmingLanguages: list<string>, requireAllTags: bool, tags: list<string>, authors: list<string>}
     *
     * @omegaup-request-param null|string $difficulty_range
     * @omegaup-request-param ''|'all'|'en'|'es'|'pt'|null $language
     * @omegaup-request-param int|null $max_difficulty
     * @omegaup-request-param int|null $min_difficulty
     * @omegaup-request-param int|null $min_visibility
     * @omegaup-request-param bool|null $only_karel
     * @omegaup-request-param ''|'accepted'|'creation_date'|'difficulty'|'points'|'problem_id'|'quality'|'ratio'|'score'|'submissions'|'title'|null $order_by
     * @omegaup-request-param int|null $page
     * @omegaup-request-param null|string $programming_languages
     * @omegaup-request-param null|string $query
     * @omegaup-request-param bool|null $require_all_tags
     * @omegaup-request-param bool|null $some_tags
     * @omegaup-request-param ''|'asc'|'desc'|null $sort_order
     */
    private static function validateListParams(\OmegaUp\Request $r) {
        $sortOrder = $r->ensureOptionalEnum(
            'sort_order',
            array_merge([''], \OmegaUp\Controllers\Problem::VALID_SORTING_MODES)
        ) ?? '';
        $orderBy = $r->ensureOptionalEnum(
            'order_by',
            array_merge(
                [''],
                \OmegaUp\Controllers\Problem::VALID_SORTING_COLUMNS
            )
        ) ?? '';
        $page = $r->ensureOptionalInt('page') ?? 0;
        $language = $r->ensureOptionalEnum(
            'language',
            array_merge(
                ['all', ''],
                \OmegaUp\Controllers\Problem::VALID_LANGUAGES
            )
        ) ?? '';

        $tags = $r->getStringList('tag', []);
        $authors = $r->getStringList('author', []);

        $keyword = substr($r->ensureOptionalString('query') ?? '', 0, 256);
        if (!$keyword) {
            $keyword = '';
        }
        $minDifficulty = $r->ensureOptionalInt('min_difficulty');
        $maxDifficulty = $r->ensureOptionalInt('max_difficulty');
        $minVisibility = $r->ensureOptionalInt(
            'min_visibility'
        ) ?? \OmegaUp\ProblemParams::VISIBILITY_PUBLIC;

        $difficultyRange = null;
        $difficultyRangeParam = $r->ensureOptionalString('difficulty_range');
        if (!empty($difficultyRangeParam)) {
            [$minDifficulty, $maxDifficulty] = explode(
                ',',
                $difficultyRangeParam
            );
            $difficultyRange = self::getDifficultyRange(
                intval($minDifficulty),
                intval($maxDifficulty)
            );
        }
        $programmingLanguageParam = $r->ensureOptionalString(
            'programming_languages'
        );
        $onlyKarel = $r->ensureOptionalBool('only_karel');
        if ($onlyKarel) {
            $programmingLanguages = ['kp', 'kj'];
        } elseif (!empty($programmingLanguageParam)) {
            $programmingLanguages = explode(
                ',',
                $programmingLanguageParam
            );
        } else {
            $programmingLanguages = [];
        }
        $someTags = $r->ensureOptionalBool('some_tags');
        $requireAllTags = $r->ensureOptionalBool('require_all_tags');

        return [
            'sortOrder' => $sortOrder,
            'page' => $page,
            'orderBy' => $orderBy,
            'language' => $language,
            'tags' => $tags,
            'keyword' => $keyword,
            'requireAllTags' => is_null(
                $requireAllTags
            ) ? is_null(
                $someTags
            ) : $requireAllTags,
            'programmingLanguages' => $programmingLanguages,
            'difficultyRange' => $difficultyRange,
            'minVisibility' => $minVisibility,
            'authors' => $authors,
        ];
    }

    /**
     * List of public problems shown in the typeahead component
     *
     * @return array{results: list<ListItem>}
     *
     * @omegaup-request-param int|null $offset
     * @omegaup-request-param string $query
     * @omegaup-request-param int|null $rowcount
     * @omegaup-request-param string $search_type
     */
    public static function apiListForTypeahead(\OmegaUp\Request $r) {
        // Authenticate request
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing, we allow unauthenticated users to use this API
        }

        // Default values for offset and rowcount
        $offset = $r->ensureOptionalInt('offset') ?? 0;
        $rowcount = $r->ensureOptionalInt(
            'rowcount'
        ) ?? \OmegaUp\Controllers\Problem::PAGE_SIZE;

        $searchType = $r->ensureEnum(
            'search_type',
            ['all', 'alias', 'title', 'problem_id']
        );
        $query = substr($r->ensureString('query'), 0, 256);

        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEMS_LIST,
            "{$query}-{$searchType}-{$offset}-{$rowcount}",
            fn () => \OmegaUp\DAO\Problems::byIdentityTypeForTypeahead(
                $offset,
                $rowcount,
                $query,
                $searchType
            ),
            APC_USER_CACHE_PROBLEM_LIST_TIMEOUT
        );
    }

    /**
     * List of public and user's private problems
     *
     * @return array{results: list<ProblemListItem>, total: int}
     *
     * @omegaup-request-param null|string $difficulty
     * @omegaup-request-param null|string $difficulty_range
     * @omegaup-request-param ''|'all'|'en'|'es'|'pt'|null $language
     * @omegaup-request-param null|string $level
     * @omegaup-request-param int|null $max_difficulty
     * @omegaup-request-param int|null $min_difficulty
     * @omegaup-request-param int|null $min_visibility
     * @omegaup-request-param int|null $offset
     * @omegaup-request-param bool|null $only_karel
     * @omegaup-request-param bool|null $only_quality_seal
     * @omegaup-request-param ''|'accepted'|'creation_date'|'difficulty'|'points'|'problem_id'|'quality'|'ratio'|'score'|'submissions'|'title'|null $order_by
     * @omegaup-request-param int|null $page
     * @omegaup-request-param null|string $programming_languages
     * @omegaup-request-param null|string $query
     * @omegaup-request-param bool|null $require_all_tags
     * @omegaup-request-param int|null $rowcount
     * @omegaup-request-param bool|null $some_tags
     * @omegaup-request-param ''|'asc'|'desc'|null $sort_order
     */
    public static function apiList(\OmegaUp\Request $r) {
        // Authenticate request
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing, we allow unauthenticated users to use this API
        }

        // Defaults for offset and rowcount
        $page = $r->ensureOptionalInt('page');
        $offset = null;
        if (is_null($page)) {
            $offset = $r->ensureOptionalInt('offset') ?? 0;
        }
        $rowcount = $r->ensureOptionalInt(
            'rowcount'
        ) ?? \OmegaUp\Controllers\Problem::PAGE_SIZE;

        $keyword = substr($r->ensureOptionalString('query') ?? '', 0, 256);

        $onlyQualitySeal = $r->ensureOptionalBool('only_quality_seal') ?? false;
        $difficulty = $r->ensureOptionalString('difficulty') ?? 'all';
        $level = $r->ensureOptionalString('level');

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
            'authors' => $authors,
        ] = self::validateListParams($r);

        return self::getListImpl(
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
            $r->user,
            $onlyQualitySeal,
            $level,
            $difficulty,
            $authors
        );
    }

    /**
     * @param list<string> $tags
     * @param list<string> $authors
     * @param array{0: int, 1: int}|null $difficultyRange
     * @param list<string> $programmingLanguages
     * @return array{results: list<ProblemListItem>, total: int}
     */
    private static function getListImpl(
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
        ?\OmegaUp\DAO\VO\Users $user,
        bool $onlyQualitySeal,
        ?string $level,
        string $difficulty,
        array $authors
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
            $difficultyRange,
            $onlyQualitySeal,
            $level,
            $difficulty,
            $authors
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
     * @omegaup-request-param null|string $query
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     */
    public static function apiAdminList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt(
            'page_size'
        ) ?? \OmegaUp\Controllers\Problem::PAGE_SIZE;

        $query = substr(
            $r->ensureOptionalString('query') ?? '',
            0,
            256
        );

        if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            [
                'problems' => $problems,
                'count' => $count,
            ] = \OmegaUp\DAO\Problems::getAllWithCount(
                $page,
                $pageSize,
                $query
            );
        } else {
            [
                'problems' => $problems,
                'count' => $count,
            ] = \OmegaUp\DAO\Problems::getAllProblemsAdminedByIdentity(
                $r->identity->identity_id,
                $page,
                $pageSize,
                $query
            );
        }

        $addedProblems = [];

        $hiddenTags = \OmegaUp\DAO\Users::getHideTags(
            $r->identity->identity_id
        );
        foreach ($problems as $problem) {
            /** @var ProblemListItem */
            $problemArray = $problem->asFilteredArray([
                'accepted',
                'alias',
                'difficulty',
                'difficulty_histogram',
                'points',
                'problem_id',
                'quality',
                'quality_histogram',
                'ratio',
                'score',
                'submissions',
                'tags',
                'title',
                'visibility',
                'quality_seal',
            ]);
            $problemArray['tags'] = $hiddenTags ? [] : \OmegaUp\DAO\Problems::getTagsForProblem(
                $problem,
                public: false,
                showUserTags: $problem->allow_user_add_tags
            );
            $problemArray['can_be_removed'] = !\OmegaUp\DAO\Problems::hasSubmissionsOrHasBeenUsedInCoursesOrContests(
                $problem
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
     * @omegaup-request-param null|string $query
     * @omegaup-request-param int $page
     * @omegaup-request-param int|null $rowcount
     */
    public static function apiMyList(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        // Defaults for offset and rowcount
        $pageSize = $r->ensureOptionalInt(
            'rowcount'
        ) ?? \OmegaUp\Controllers\Problem::PAGE_SIZE;
        $page = $r->ensureOptionalInt('page') ?? 1;

        $query = substr(
            $r->ensureOptionalString('query') ?? '',
            0,
            256
        );

        [
            'problems' => $problems,
            'count' => $count,
        ] = \OmegaUp\DAO\Problems::getAllProblemsOwnedByUser(
            $r->user->user_id,
            $page,
            $pageSize,
            $query
        );

        $addedProblems = [];

        $hiddenTags = \OmegaUp\DAO\Users::getHideTags(
            $r->identity->identity_id
        );
        foreach ($problems as $problem) {
            /** @var ProblemListItem */
            $problemArray = $problem->asFilteredArray([
                'accepted',
                'alias',
                'difficulty',
                'difficulty_histogram',
                'points',
                'problem_id',
                'quality',
                'quality_histogram',
                'ratio',
                'score',
                'submissions',
                'tags',
                'title',
                'visibility',
                'quality_seal',
            ]);
            $problemArray['tags'] = $hiddenTags ? [] : \OmegaUp\DAO\Problems::getTagsForProblem(
                $problem,
                public: false,
                showUserTags: $problem->allow_user_add_tags
            );
            $problemArray['can_be_removed'] = !\OmegaUp\DAO\Problems::hasSubmissionsOrHasBeenUsedInCoursesOrContests(
                $problem
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
     * @omegaup-request-param null|string $contest_alias
     * @omegaup-request-param null|string $problem_alias
     * @omegaup-request-param mixed $problemset_id
     * @omegaup-request-param null|string $statement_type
     * @omegaup-request-param null|string $username
     */
    public static function apiBestScore(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        // Uses same params as apiDetails, except for lang, which is optional
        $contestAlias = $r->ensureOptionalString(
            'contest_alias',
            required: false,
            validator: fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problem = self::validateDetails(
            $r->identity,
            $contestAlias,
            $problemAlias,
            $r->ensureOptionalString('statement_type') ?? '',
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
                $contestAlias,
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
            \OmegaUp\DAO\ProblemsLanguages::deleteProblemLanguages(new \OmegaUp\DAO\VO\ProblemsLanguages([
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
        } catch (\Exception $e) {
            self::$log->error(
                "Failed to update languages for problem {$problem->alias}: ",
                ['exception' => $e],
            );
            try {
                // Operation failed in the data layer, try to rollback transaction
                \OmegaUp\DAO\DAO::transRollback();
            } catch (\Exception $rollbackException) {
                self::$log->error(
                    'Failed to roll back transaction: ',
                    ['exception' => $rollbackException],
                );
            }
            throw $e;
        }
    }

    /**
     * Gets a Problem settings object with default values.
     *
     * @return ProblemSettings The Problem settings object.
     */
    private static function getDefaultProblemSettings(): array {
        return [
            'Cases' => [],
            'Limits' => [
                'ExtraWallTime' => '0s',
                'MemoryLimit' => '64MiB',
                'OutputLimit' => '10240KiB',
                'OverallWallTimeLimit' => '30s',
                'TimeLimit' => '1s',
            ],
            'Slow' => false,
            'Validator' => [
                'Name' => \OmegaUp\ProblemParams::VALIDATOR_TOKEN,
                'Tolerance' => 1e-9,
            ],
        ];
    }

    /**
     * Updates the Problem's settings with the values from the request.
     *
     * @param array{Limits: LimitsSettings, Slow: bool, Validator: array{GroupScorePolicy?: string, Lang?: string, Limits?: LimitsSettings, Name: string, Tolerance: float}} $problemSettings the original problem settings.
     * @param \OmegaUp\ProblemParams $params the params
     * @psalm-suppress ReferenceConstraintViolation for some reason, psalm cannot correctly infer the type for $problemSettings['Validator']['Limit']
     */
    private static function updateProblemSettings(
        &$problemSettings,
        \OmegaUp\ProblemParams $params
    ): void {
        if (!is_null($params->extraWallTime)) {
            $problemSettings['Limits']['ExtraWallTime'] = "{$params->extraWallTime}ms";
        }
        if (!is_null($params->memoryLimit)) {
            $problemSettings['Limits']['MemoryLimit'] = "{$params->memoryLimit}KiB";
        }
        if (!is_null($params->outputLimit)) {
            $problemSettings['Limits']['OutputLimit'] = "{$params->outputLimit}";
        }
        if (!is_null($params->memoryLimit)) {
            $problemSettings['Limits']['OverallWallTimeLimit'] = "{$params->overallWallTimeLimit}ms";
        }
        if (!is_null($params->timeLimit)) {
            $problemSettings['Limits']['TimeLimit'] = "{$params->timeLimit}ms";
        }
        if (!is_null($params->validator)) {
            $problemSettings['Validator']['Name'] = "{$params->validator}";
        }
        if (!is_null($params->groupScorePolicy)) {
            $problemSettings['Validator']['GroupScorePolicy'] = "{$params->groupScorePolicy}";
        }
        if ($problemSettings['Validator']['Name'] === 'custom') {
            if (
                empty(
                    $problemSettings['Validator']['Limits']
                )
            ) {
                $problemSettings['Validator']['Limits'] = [
                    'ExtraWallTime' => '0s',
                    'MemoryLimit' => '256MiB',
                    'OutputLimit' => '10KiB',
                    'OverallWallTimeLimit' => '5s',
                    'TimeLimit' => '30s',
                ];
            }
            if (!is_null($params->validatorTimeLimit)) {
                $problemSettings['Validator']['Limits']['TimeLimit'] = "{$params->validatorTimeLimit}ms";
            }
        } else {
            if (!empty($problemSettings['Validator']['Limits'])) {
                unset($problemSettings['Validator']['Limits']);
            }
        }
    }

    /**
     *
     * @omegaup-request-param null|string $query
     *
     * @return array{templateProperties: array{payload: ProblemsMineInfoPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getProblemsMineInfoForTypeScript(\OmegaUp\Request $r): array {
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
            unset($scopedSession);
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
            'templateProperties' => [
                'payload' => [
                    'isSysadmin' => \OmegaUp\Authorization::isSystemAdmin(
                        $r->identity
                    ),
                    'privateProblemsAlert' => $privateProblemsAlert,
                    'visibilityStatuses' => $visibilityStatuses,
                    'query' => $r->ensureOptionalString('query'),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleMyProblemsList'
                ),
            ],
            'entrypoint' => 'problem_mine',
        ];
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: ProblemDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param null|string $contest_alias
     * @omegaup-request-param bool|null $prevent_problemset_open
     * @omegaup-request-param null|string $problem_alias
     * @omegaup-request-param int|null $problemset_id
     * @omegaup-request-param null|string $statement_type
     */
    public static function getProblemDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing. Not logged user can access here
            $r->identity = null;
        }

        $requestData = self::extractRequestData($r);

        [$problem, $details] = self::getProblemBaseData(
            $r->identity,
            $requestData
        );

        $response = [
            'templateProperties' => [
                'payload' => [],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleProblem'
                ),
            ],
            'entrypoint' => 'problem_details',
        ];

        $basePayload = self::buildUnloggedPayload($problem, $details);
        $response['templateProperties']['payload'] = $basePayload;

        if (is_null($r->identity)) {
            return $response;
        }

        $isAdmin = \OmegaUp\Authorization::isProblemAdmin(
            $r->identity,
            $problem
        );

        $isQualityReviewer = \OmegaUp\Authorization::isQualityReviewer(
            $r->identity
        );

        if ($isQualityReviewer) {
            $qualityReviewerPayload = self::getQualityReviewerPayload(
                $r->identity,
                $problem
            );
            $response['templateProperties']['payload'] = array_merge(
                $response['templateProperties']['payload'],
                $qualityReviewerPayload
            );
        }

        $loggedUserPayload = self::getLoggedUserPayload(
            $r->identity,
            $r->user,
            $problem,
            $details,
            $isAdmin,
            $isQualityReviewer
        );

        $response['templateProperties']['payload'] = array_merge(
            $response['templateProperties']['payload'],
            $loggedUserPayload
        );

        $timestampsPayload = self::getSubmissionTimestampsPayload(
            $loggedUserPayload['runs']
        );
        $response['templateProperties']['payload']['problem'] = array_merge(
            $response['templateProperties']['payload']['problem'],
            $timestampsPayload
        );

        if ($isAdmin) {
            $adminPayload  = self::getAdminPayload(
                $r->identity,
                $problem
            );

            $response['templateProperties']['payload'] = array_merge(
                $response['templateProperties']['payload'],
                $adminPayload
            );
        }

        return $response;
    }

    /**
     *  Extracts and validates request parameters related to problem access.
     *
     * @return ProblemRequestData
     *
     * @omegaup-request-param null|string $contest_alias
     * @omegaup-request-param bool|null $prevent_problemset_open
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param int|null $problemset_id
     * @omegaup-request-param null|string $statement_type
     */
    private static function extractRequestData(\OmegaUp\Request $r) {
        return [
            'preventProblemsetOpen' => $r->ensureOptionalBool(
                'prevent_problemset_open'
            ) ?? false,
            'contestAlias' => $r->ensureOptionalString(
                'contest_alias',
                required: false,
                validator: fn (string $alias) => \OmegaUp\Validators::alias(
                    $alias
                )
            ),
            'problemAlias' => $r->ensureString(
                'problem_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            ),
            'statementType' => $r->ensureOptionalString('statement_type') ?? '',
            'problemsetId' => $r->ensureOptionalInt('problemset_id')
        ];
    }

    /**
     * Retrieves the base data for a problem, including the problem entity,
     *
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @param ProblemRequestData $requestData
     * @return array{\OmegaUp\DAO\VO\Problems, ProblemDetails}
     */
    private static function getProblemBaseData(
        ?\OmegaUp\DAO\VO\Identities $identity,
        $requestData
    ): array {
        [
            'problem' => $problem,
            'problemset' => $problemset,
        ] = self::getValidProblemAndProblemset(
            $identity,
            $requestData['contestAlias'],
            $requestData['problemAlias'],
            $requestData['statementType'],
            $requestData['problemsetId']
        );

        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        // Get problem details from API
        $details = self::getProblemDetails(
            $identity,
            $problem,
            $problemset,
            \OmegaUp\Controllers\Identity::getPreferredLanguage($identity),
            showSolvers: false,
            preventProblemsetOpen: $requestData['preventProblemsetOpen'],
            contestAlias: $requestData['contestAlias'],
        );

        if (is_null($details)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        return [$problem, $details];
    }

    /**
     * Builds the problem details response for users who are not logged in.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem
     * @param ProblemDetails $details
     * @return ProblemDetailsPayload
     */
    private static function buildUnloggedPayload($problem, $details): array {
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
        return [
            'solvers' => \OmegaUp\DAO\Runs::getBestSolvingRunsForProblem(
                intval($problem->problem_id)
            ),
            'histogram' => [
                'difficultyHistogram' => $problem->difficulty_histogram,
                'qualityHistogram' => $problem->quality_histogram,
                'quality' => floatval($problem->quality),
                'difficulty' => floatval($problem->difficulty),
            ],
            'problem' => [
                'alias' => $details['alias'],
                'karel_problem' => count(
                    array_intersect(
                        $details['languages'],
                        ['kp', 'kj']
                    )
                ) === 2,
                'commit' => $details['commit'],
                'languages' => $details['languages'],
                'preferred_language' => $details['preferred_language'] ?? null,
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
                'accepts_submissions' => $details['accepts_submissions'],
                'input_limit' => $details['input_limit'],
            ],
            'user' => [
                'loggedIn' => false,
                'admin' => false,
                'reviewer' => false,
            ],
            'allowedSolutionsToSee' => 0,
            'solutionStatus' => self::SOLUTION_NOT_LOGGED_IN,
        ];
    }

    /**
     * Enhances the problem details response for a quality reviewer by adding review-related information.
     *
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @param \OmegaUp\DAO\VO\Problems $problem
     * @return array{problemLevel: null|string, selectedPublicTags: list<string>, reviewedProblemLevel: null|string, reviewedQualitySeal: bool, reviewedPublicTags: list<string>, publicTags: list<string>}
     */
    private static function getQualityReviewerPayload(
        \OmegaUp\DAO\VO\Identities $identity,
        $problem
    ): array {
        $qualityNomination = \OmegaUp\DAO\QualityNominations::getQualityNominationContentsForProblemAndReviewer(
            $identity,
            $problem
        );

        $contents = [];
        if (!is_null($qualityNomination)) {
            /**
             * @var array{tags?: list<string>, quality_seal?: bool, level?: string}|null $contents
             */
            $contents = json_decode(
                $qualityNomination['contents'],
                associative: true
            );
            if (is_null($contents)) {
                $contents = [];
            }
        }

        return [
            'problemLevel' => \OmegaUp\DAO\ProblemsTags::getProblemLevel(
                $problem
            ),
            'selectedPublicTags' => \OmegaUp\DAO\ProblemsTags::getTagsForProblem(
                $problem,
                public: true
            ),
            'reviewedProblemLevel' => $contents['level'] ?? null,
            'reviewedQualitySeal' => $contents['quality_seal'] ?? false,
            'reviewedPublicTags' => $contents['tags'] ?? [],
            'publicTags' => \OmegaUp\Controllers\Tag::getPublicTags(),
        ];
    }

    /**
     * Enhances the problem details response for an admin by adding additional information.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem
     * @return array{allRuns: list<Run>, totalRuns: int, levelTags: list<string>, allowUserAddTags: bool, selectedPrivateTags: list<string>}
     */
    private static function getAdminPayload(
        \OmegaUp\DAO\VO\Identities $identity,
        $problem
    ): array {
        if (is_null($problem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        [
            'runs' => $runs,
            'totalRuns' => $totalRuns,
        ] = self::getAllRuns($problem->problem_id);

        return [
            'allRuns' => $runs,
            'totalRuns' => $totalRuns,
            'levelTags' => \OmegaUp\Controllers\Tag::getLevelTags(),
            'allowUserAddTags' => $problem->allow_user_add_tags,
            'selectedPrivateTags' => (\OmegaUp\Authorization::canEditProblem(
                $identity,
                $problem
            ) ?
            \OmegaUp\DAO\ProblemsTags::getTagsForProblem(
                $problem,
                public: false
            ) : []),
        ];
    }

    /**
     * Gets the submission timestamps payload for logged users.
     *
     * @param list<Run> $runs
     *
     * @return array{problem: array{nextSubmissionTimestamp?: \OmegaUp\Timestamp, nextExecutionTimestamp?: \OmegaUp\Timestamp}}
     */
    private static function getSubmissionTimestampsPayload(array $runs): array {
        $lastRunTime = null;

        if (count($runs) > 0) {
            $lastRunTime = max(
                array_map(
                    fn($run) => $run['time'],
                    $runs
                )
            );
        }

        $nextSubmissionTimestamp = \OmegaUp\DAO\Runs::nextSubmissionTimestamp(
            null,
            $lastRunTime
        );
        $nextExecutionTimestamp = \OmegaUp\DAO\Runs::nextExecutionTimestamp(
            $lastRunTime
        );

        return [
            'problem' => [
                'nextSubmissionTimestamp' => $nextSubmissionTimestamp,
                'nextExecutionTimestamp' => $nextExecutionTimestamp,
            ],
        ];
    }

    /**
     * Enhances the problem details response for a logged-in user by adding user-specific information and runs.
     *
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @param ?\OmegaUp\DAO\VO\Users $user
     * @param \OmegaUp\DAO\VO\Problems $problem
     * @param ProblemDetails $details
     * @param bool $isAdmin
     * @param bool $isQualityReviewer
     * @return array{user: UserInfoForProblem, nominationStatus: NominationStatus, runs: list<Run>, solutionStatus: string, clarifications: list<Clarification>, allowedSolutionsToSee: int}
     */
    private static function getLoggedUserPayload(
        \OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Users $user,
        $problem,
        $details,
        bool $isAdmin,
        bool $isQualityReviewer
    ): array {
        if (is_null($problem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $nominationStatus = \OmegaUp\DAO\QualityNominations::getNominationStatusForProblem(
            $problem->problem_id,
            $identity->user_id
        );

        $nominationPayload = [
            'alreadyReviewed' => \OmegaUp\DAO\QualityNominations::reviewerHasQualityTagNominatedProblem(
                $identity,
                $problem
            ),
            'dismissed' => $nominationStatus['dismissed'],
            'dismissedBeforeAc' => $nominationStatus['dismissedBeforeAc'],
            'nominated' => $nominationStatus['nominated'],
            'nominatedBeforeAc' => $nominationStatus['nominatedBeforeAc'],
            'language' => $details['statement']['language'],
            'canNominateProblem' => !is_null($identity->user_id),
            'solved' => false,
            'tried' => false,
        ];

        foreach ($details['runs'] ?? [] as $run) {
            if ($run['verdict'] === 'AC') {
                $nominationPayload['solved'] = true;
                $nominationPayload['tried'] = true;
                break;
            } elseif ($run['verdict'] !== 'JE' && $run['verdict'] !== 'VE' && $run['verdict'] !== 'CE') {
                $nominationPayload['tried'] = true;
            }
        }

        $runsPayload = \OmegaUp\DAO\Runs::getForProblemDetails(
            intval($problem->problem_id),
            problemsetId: null,
            identityId: intval($identity->identity_id)
        );

        $allowedSolutionsToSee = 0;

        if (!is_null($user)) {
            // Get the count of problems forfeited by the user on the current day.
            $problemsForfeitedCount = \OmegaUp\DAO\ProblemsForfeited::getProblemsForfeitedCountInDay(
                $user
            );
            // Calculate the remaining solutions the user can view by subtracting the number of solutions they have already seen from the daily allowed limit.
            $allowedSolutionsToSee = max(
                \OmegaUp\Controllers\ProblemForfeited::SOLUTIONS_ALLOWED_TO_SEE_PER_DAY - $problemsForfeitedCount,
                0
            );
        }

        // Check if problem is bookmarked by the user
        $isBookmarked = \OmegaUp\DAO\ProblemBookmarks::existsByPK(
            intval($identity->identity_id),
            intval($problem->problem_id)
        );

        return [
            'user' => [
                'loggedIn' => true,
                'admin' => $isAdmin,
                'reviewer' => $isQualityReviewer,
            ],
            'nominationStatus' => $nominationPayload,
            'runs' => $runsPayload,
            'solutionStatus' => self::getProblemSolutionStatus(
                $problem,
                $identity
            ),
            'clarifications' => \OmegaUp\DAO\Clarifications::getProblemClarifications(
                $problem->problem_id,
                $isAdmin,
                intval($identity->identity_id),
                offset: null,
                rowcount: 0,
            ),
            'allowedSolutionsToSee' => $allowedSolutionsToSee,
            'isBookmarked' => $isBookmarked,
        ];
    }

    /**
     * @return array{templateProperties: array{payload: ProblemListPayload, title: \OmegaUp\TranslationString, fullWidth: bool}, entrypoint: string}
     *
     * @omegaup-request-param null|string $difficulty_range
     * @omegaup-request-param ''|'all'|'en'|'es'|'pt'|null $language
     * @omegaup-request-param int|null $max_difficulty
     * @omegaup-request-param int|null $min_difficulty
     * @omegaup-request-param int|null $min_visibility
     * @omegaup-request-param int|null $offset
     * @omegaup-request-param bool|null $only_karel
     * @omegaup-request-param bool $only_quality_seal
     * @omegaup-request-param ''|'accepted'|'creation_date'|'difficulty'|'points'|'problem_id'|'quality'|'ratio'|'score'|'submissions'|'title'|null $order_by
     * @omegaup-request-param int|null $page
     * @omegaup-request-param null|string $programming_languages
     * @omegaup-request-param null|string $query
     * @omegaup-request-param bool|null $require_all_tags
     * @omegaup-request-param int|null $rowcount
     * @omegaup-request-param bool|null $some_tags
     * @omegaup-request-param ''|'asc'|'desc'|null $sort_order
     */
    public static function getProblemListForTypeScript(
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
        $offset = $r->ensureOptionalInt('offset');
        $pageSize = $r->ensureOptionalInt(
            'rowcount'
        ) ?? \OmegaUp\Controllers\Problem::PAGE_SIZE;
        if ($offset < 0) {
            $offset = 0;
        }

        $onlyQualitySeal = $r->ensureOptionalBool('only_quality_seal') ?? false;

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
            'authors' => $authors,
        ] = self::validateListParams($r);

        $result = self::getList(
            $page,
            $language,
            $orderBy,
            $sortOrder,
            $offset,
            $pageSize,
            $tags,
            $keyword,
            $requireAllTags,
            $programmingLanguages,
            $minVisibility,
            $difficultyRange,
            $r->identity,
            $r->user,
            $onlyQualitySeal,
            url: '/problem/list/',
            level: null,
            difficulty: 'all',
            authors: $authors
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'problems' => $result['problems'],
                    'loggedIn' => !is_null($r->identity),
                    'selectedTags' => $result['selectedTags'],
                    'pagerItems' => $result['pagerItems'],
                    'keyword' => $result['keyword'],
                    'mode' => $result['mode'],
                    'column' => $result['column'],
                    'language' => $result['language'],
                    'languages' => $result['languages'],
                    'modes' => $result['modes'],
                    'columns' => $result['columns'],
                    'tags' => $result['tags'],
                    'tagData' => $result['tagData'],
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleProblems'
                ),
                'fullWidth' => true,
            ],
            'entrypoint' => 'problem_list',
        ];
    }

    /**
     * @return array{templateProperties: array{payload: ProblemListCollectionPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getProblemCollectionDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $tags = [];
        $allTags = self::getAllTagsFromCache();

        foreach ($allTags as $tag) {
            if (is_null($tag->name)) {
                continue;
            }
            if (!$tag->public) {
                continue;
            }
            $tags[] = ['name' => $tag->name];
        }
        return [
            'templateProperties' => [
                'payload' => [
                    'levelTags' => \OmegaUp\Controllers\Tag::getLevelTags(),
                    'problemCount' => \OmegaUp\DAO\Problems::getQualityProblemsPerTagCount(),
                    'allTags' => $tags,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCollections'
                ),
            ],
            'entrypoint' => 'problem_collection',
        ];
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Tags>
     */
    private static function getAllTagsFromCache() {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::TAGS_LIST,
            'all',
            fn () => \OmegaUp\DAO\Tags::getAll(),
            APC_USER_CACHE_SESSION_TIMEOUT
        );
    }

    /**
     * @return array{validLanguages: array<string, string>, validatorTypes: array<string, null|string>, visibilityStatuses: array<string, int>}
     */
    public static function getCommonPayloadForTypeScript(
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        $validatorTypes = [
            \OmegaUp\ProblemParams::VALIDATOR_TOKEN_CASELESS => \OmegaUp\Translations::getInstance(
                $identity
            )->get(
                'problemEditFormTokenCaseless'
            ),
            \OmegaUp\ProblemParams::VALIDATOR_TOKEN_NUMERIC => \OmegaUp\Translations::getInstance(
                $identity
            )->get(
                'problemEditFormNumericTokensWithTolerance'
            ),
            \OmegaUp\ProblemParams::VALIDATOR_TOKEN => \OmegaUp\Translations::getInstance(
                $identity
            )->get(
                'problemEditFormTokenByToken'
            ),
            \OmegaUp\ProblemParams::VALIDATOR_LITERAL => \OmegaUp\Translations::getInstance(
                $identity
            )->get(
                'problemEditFormLiteral'
            ),
            \OmegaUp\ProblemParams::VALIDATOR_CUSTOM => \OmegaUp\Translations::getInstance(
                $identity
            )->get(
                'problemEditFormCustom'
            ),
        ];
        $sortedLanguages = \OmegaUp\Controllers\Run::DEFAULT_LANGUAGES();
        sort($sortedLanguages);
        $validLanguages = [
            join(
                ',',
                $sortedLanguages
            ) => 'C, C++, C#, Java, Kotlin, Python, Ruby, Pascal, Haskell, Lua, Go, Rust, JavaScript',
            'kj,kp' => 'Karel',
            'cat' => \OmegaUp\Translations::getInstance($identity)->get(
                'wordsJustOutput'
            ),
            '' => \OmegaUp\Translations::getInstance($identity)->get(
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
     * @return array{entrypoint: string, templateProperties: array{payload: ProblemEditPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param bool|null $allow_user_add_tags
     * @omegaup-request-param string $contents
     * @omegaup-request-param string $directory
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param int|null $extra_wall_time
     * @omegaup-request-param int|null $input_limit
     * @omegaup-request-param null|string $lang
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param int|null $memory_limit
     * @omegaup-request-param string $message
     * @omegaup-request-param int|null $output_limit
     * @omegaup-request-param int|null $overall_wall_time_limit
     * @omegaup-request-param null|string $problem
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param null|string $problem_level
     * @omegaup-request-param bool|null $redirect
     * @omegaup-request-param null|string $request
     * @omegaup-request-param null|string $selected_tags
     * @omegaup-request-param null|string $show_diff
     * @omegaup-request-param null|string $group_score_policy
     * @omegaup-request-param null|string $source
     * @omegaup-request-param int|null $time_limit
     * @omegaup-request-param null|string $title
     * @omegaup-request-param null|string $update_published
     * @omegaup-request-param null|string $validator
     * @omegaup-request-param int|null $validator_time_limit
     * @omegaup-request-param null|string $visibility
     */
    public static function getProblemEditDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        // HACK to prevent convertRequestToProblemParams from erroring out.
        if (!empty($r['problem']) && is_string($r['problem'])) {
            $r['problem_alias'] = $r['problem'];
        }

        $problemParams = self::convertRequestToProblemParams(
            $r,
            isRequired: false
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemParams->problemAlias
        );
        if (is_null($problem) || is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
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
        $cdp = \OmegaUp\Controllers\Problem::getProblemCDP(
            $problem,
            strval($problem->commit)
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
        $extraInfo = [
            'statusSuccess' => false,
            'admins' => $admins,
            'groupAdmins' => $groupAdmins,
            'problemLevel' => \OmegaUp\DAO\ProblemsTags::getProblemLevel(
                $problem
            ),
            'publicTags' => \OmegaUp\Controllers\Tag::getPublicTags(),
            'levelTags' => \OmegaUp\Controllers\Tag::getLevelTags(),
            'selectedPublicTags' => \OmegaUp\DAO\ProblemsTags::getTagsForProblem(
                $problem,
                public: true
            ),
            'selectedPrivateTags' => (
                \OmegaUp\Authorization::canEditProblem($r->identity, $problem) ?
                \OmegaUp\DAO\ProblemsTags::getTagsForProblem(
                    $problem,
                    public: false
                ) : []
            ),
            'log' => $versions['log'],
            'publishedRevision' => $publishedRevision,
            'solution' => $solution,
            'cdp' => $cdp,
        ];

        $result = [
            'templateProperties' => [
                'payload' => array_merge(
                    $details,
                    self::getCommonPayloadForTypeScript($r->identity),
                    $extraInfo
                ),
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleProblemEdit'
                ),
            ],
            'entrypoint' => 'problem_edit',
        ];
        if (isset($details['problemsetter'])) {
            $result['templateProperties']['payload']['problemsetter'] = $details['problemsetter'];
        }
        if (!isset($r['request'])) {
            return $result;
        }

        // Validate commit message.
        $message = $r->ensureString('message');
        $request = $r->ensureString('request');

        if ($request === 'submit') {
            $redirect = $r->ensureOptionalBool('redirect') ?? false;
            try {
                [
                    'problem' => $problem,
                ] = self::updateProblem(
                    $r->identity,
                    $r->user,
                    $problemParams,
                    $message,
                    $problemParams->updatePublished,
                    $redirect
                );
                $details = self::getProblemEditDetails($problem, $r->identity);
                $result['templateProperties']['payload'] = array_merge(
                    $details,
                    self::getCommonPayloadForTypeScript($r->identity)
                );
                $result['templateProperties']['payload'] = array_merge(
                    $extraInfo,
                    $result['templateProperties']['payload']
                );
            } catch (\OmegaUp\Exceptions\ApiException $e) {
                \OmegaUp\ApiCaller::logException($e);
                /** @var array{error?: string} */
                $response = $e->asResponseArray();
                if (empty($response['error'])) {
                    $statusError = '{error}';
                } else {
                    $statusError = $response['error'];
                }
                $result['templateProperties']['payload']['statusError'] = $statusError;
                return $result;
            }
        } elseif ($request === 'markdown') {
            $directory = $r->ensureEnum(
                'directory',
                ['statements', 'solutions']
            );

            $contents = $r->ensureString('contents');

            $statements = json_decode($contents, associative: true);
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
                self::updateStatement(
                    $r->identity,
                    $r->user,
                    $problem,
                    $directory,
                    $statement,
                    $message,
                    $language,
                    $problemParams->updatePublished
                );
            }
            $details = self::getProblemEditDetails($problem, $r->identity);
        } elseif ($request === 'cases') {
            $contents = $r->ensureString('contents');
            $data = json_decode($contents, true);
            if (!is_array($data)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'invalidJson'
                );
            }
            if (is_null($cdp)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'cdpNotFound'
                );
            }
            $isCase = isset($data['case']) && isset($data['group']);
            $isGroup = isset($data['group']) && !isset($data['case']);

            if (!$isCase && !$isGroup) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'missingCaseOrGroup'
                );
            }
            self::validateData($data, $isCase);
            if ($isCase) {
                $cdp = self::updateCase(
                    $r->identity,
                    $r->user,
                    $problem,
                    $data['case'],
                    $data['group'],
                    $cdp,
                    $message,
                    $problemParams->updatePublished
                );
            } else {
                $cdp = self::updateGroup(
                    $r->identity,
                    $r->user,
                    $problem,
                    $data['group'],
                    $cdp,
                    $message,
                    $problemParams->updatePublished
                );
            }

            $result['templateProperties']['payload']['cdp'] = $cdp;
        } elseif ($request === 'deleteGroupCase') {
            $contents = $r->ensureString('contents');
            $data = json_decode($contents, true);

            if (!is_array($data)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'invalidJson'
                );
            }

            if (is_null($cdp)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'cdpNotFound'
                );
            }

            if (!isset($data['id']) || empty($data['id'])) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'missingOrEmptyId'
                );
            }

            $cdp = self::deleteGroupOrCase(
                $r->identity,
                $r->user,
                $problem,
                $data['id'],
                $cdp,
                $message,
                $problemParams->updatePublished
            );
            $result['templateProperties']['payload']['cdp'] = $cdp;
        }
        $result['templateProperties']['payload']['statusSuccess'] = true;
        return $result;
    }

    /**
     * @return array{alias: string, allowUserAddTags: bool, emailClarifications: bool, extraWallTime: float, groupScorePolicy: null|string, inputLimit: int, languages: string, memoryLimit: float, outputLimit: int, overallWallTimeLimit: float, problemsetter?: ProblemsetterInfo, showDiff: string, source: string, statement: ProblemStatement, timeLimit: float, title: string, validator: string, validatorTimeLimit: float|int, visibility: int}
     */
    private static function getProblemEditDetails(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        $details = self::getProblemDetails(
            $identity,
            $problem,
            problemset: null,
            statementLanguage: '',
            showSolvers: false,
            preventProblemsetOpen: false,
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
            'showDiff' => $details['show_diff'],
            'groupScorePolicy' => $validatorSettings['group_score_policy'] ?? null,
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
    * @return array{templateProperties: array{payload: array<empty, empty>, title: \OmegaUp\TranslationString}, entrypoint: string}
    */
    public static function getCreatorForTypeScript(
        \OmegaUp\Request $r
    ): array {
        return [
            'templateProperties' => [
                'payload' => [],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleProblemCreator'
                )
            ],
            'entrypoint' => 'problem_creator'
        ];
    }
    /**
     * @return array{templateProperties: array{payload: ProblemFormPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param bool|null $allow_user_add_tags
     * @omegaup-request-param bool|null $email_clarifications
     * @omegaup-request-param int|null $extra_wall_time
     * @omegaup-request-param int|null $input_limit
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param int|null $memory_limit
     * @omegaup-request-param int|null $output_limit
     * @omegaup-request-param int|null $overall_wall_time_limit
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param null|string $problem_level
     * @omegaup-request-param null|string $request
     * @omegaup-request-param null|string $selected_tags
     * @omegaup-request-param null|string $show_diff
     * @omegaup-request-param null|string $group_score_policy
     * @omegaup-request-param null|string $source
     * @omegaup-request-param int|null $time_limit
     * @omegaup-request-param null|string $title
     * @omegaup-request-param null|string $update_published
     * @omegaup-request-param null|string $validator
     * @omegaup-request-param int|null $validator_time_limit
     * @omegaup-request-param null|string $visibility
     */
    public static function getProblemNewForTypeScript(
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
        $request = $r->ensureOptionalString('request');
        if (!is_null($request) && $request === 'submit') {
            $problemParams = null;
            try {
                $problemParams = self::convertRequestToProblemParams($r);
                self::createProblem($r->user, $r->identity, $problemParams);
                header("Location: /problem/{$r['problem_alias']}/edit/");

                // Since all the headers and response have been sent, make the API
                // caller to exit quietly.
                throw new \OmegaUp\Exceptions\ExitException();
            } catch (\OmegaUp\Exceptions\ExitException $e) {
                throw $e;
            } catch (\OmegaUp\Exceptions\ApiException $e) {
                \OmegaUp\ApiCaller::logException($e);
                /** @var array{error?: string, parameter?: string} */
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
                        associative: true
                    );
                }
                $problemAlias = $r->ensureOptionalString(
                    'problem_alias',
                    required: false,
                    validator: fn (string $alias) => \OmegaUp\Validators::alias(
                        $alias
                    )
                );
                return [
                    'templateProperties' => [
                        'payload' => array_merge(
                            [
                                'title' => $r->ensureOptionalString(
                                    'title'
                                ) ?? '',
                                'alias' => $problemAlias ?? '',
                                'emailClarifications' => $r->ensureOptionalBool(
                                    'email_clarifications'
                                ) ?? false,
                                'source' => $r->ensureOptionalString(
                                    'source'
                                ) ?? '',
                                'visibility' => intval($r['visibility']),
                                'statusError' => $statusError,
                                'parameter' => !empty($response['parameter'])
                                  ? strval($response['parameter'])
                                  : null,
                                'selectedTags' => $selectedTags,
                                'allowUserAddTags' => true,
                                'showDiff' => $r->ensureOptionalString(
                                    'show_diff'
                                ) ?? '',
                                'groupScorePolicy' => $r->ensureOptionalString(
                                    'group_score_policy'
                                ),
                                'timeLimit' => $r->ensureOptionalString(
                                    'time_limit'
                                ) ?? '',
                                'validatorTimeLimit' => $r->ensureOptionalString(
                                    'validator_time_limit'
                                ) ?? '',
                                'overallWallTimeLimit' => $r->ensureOptionalString(
                                    'overall_wall_time_limit'
                                ) ?? '',
                                'extraWallTime' => $r->ensureOptionalString(
                                    'extra_wall_time'
                                ) ?? '',
                                'outputLimit' => $r->ensureOptionalString(
                                    'output_limit'
                                ) ?? '',
                                'inputLimit' => $r->ensureOptionalString(
                                    'input_limit'
                                ) ?? '',
                                'memoryLimit' =>  $r->ensureOptionalString(
                                    'memory_limit'
                                ) ?? '',
                                'languages' => $r->ensureOptionalString(
                                    'languages'
                                ) ?? '',
                                'validator' => $r->ensureOptionalString(
                                    'validator'
                                ) ?? '',
                                'tags' => $tags,
                                'problem_level' => $r->ensureOptionalString(
                                    'problem_level'
                                ) ?? '',
                                'publicTags' => \OmegaUp\Controllers\Tag::getPublicTags(),
                                'levelTags' => \OmegaUp\Controllers\Tag::getLevelTags(),
                            ],
                            self::getCommonPayloadForTypeScript($r->identity)
                        ),
                        'title' => new \OmegaUp\TranslationString(
                            'omegaupTitleProblemNew'
                        ),
                    ],
                    'entrypoint' => 'problem_new',
                ];
            }
        }
        $sortedLanguages = \OmegaUp\Controllers\Run::DEFAULT_LANGUAGES();
        sort($sortedLanguages);

        return [
            'templateProperties' => [
                'payload' => array_merge(
                    [
                        'title' => '',
                        'alias' => '',
                        'validator' => \OmegaUp\ProblemParams::VALIDATOR_TOKEN,
                        'emailClarifications' => false,
                        'source' => '',
                        'visibility' => 0,
                        'statusError' => '',
                        'parameter' => null,
                        'selectedTags' => null,
                        'allowUserAddTags' => true,
                        'showDiff' => 'none',
                        'groupScorePolicy' => null,
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
                        'problem_level' => '',
                        'publicTags' => \OmegaUp\Controllers\Tag::getPublicTags(),
                        'levelTags' => \OmegaUp\Controllers\Tag::getLevelTags(),
                        'hasVisitedSection' => \OmegaUp\UITools::hasVisitedSection(
                            'has-visited-create-problem'
                        ),
                    ],
                    self::getCommonPayloadForTypeScript($r->identity)
                ),
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleProblemNew'
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
            strval($problem->alias),
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
            fn () => \OmegaUp\Controllers\Problem::getProblemSolutionExistenceImpl(
                $problem
            ),
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
     * @omegaup-request-param string $commit
     * @omegaup-request-param string $filename
     * @omegaup-request-param string $problem_alias
     */
    public static function getTemplate(\OmegaUp\Request $r): void {
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $commit = $r->ensureString(
            'commit',
            fn (string $commit) => \OmegaUp\Validators::objectId($commit)
        );
        $filename = $r->ensureString(
            'filename',
            fn (string $name) => \OmegaUp\Validators::filename($name)
        );

        $fileDirectory = TEMPLATES_PATH . "/{$problemAlias}/{$commit}";
        if (is_dir($fileDirectory)) {
            // The generation of templates has happened before. We're never
            // going to find this.
            throw new \OmegaUp\Exceptions\NotFoundException(
                'resourceNotFound'
            );
        }

        self::regenerateTemplates($problemAlias, $commit);
        $filePath = "{$fileDirectory}/{$filename}";
        $fileSize = @filesize($filePath);
        if ($fileSize === false) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'resourceNotFound'
            );
        }
        if (str_ends_with($filePath, '.tar.gz')) {
            header('Content-Type: application/tar+gzip');
        } elseif (str_ends_with($filePath, '.tar.bz2')) {
            header('Content-Type: application/tar+bzip2');
        } elseif (str_ends_with($filePath, '.zip')) {
            header('Content-Type: application/zip');
        } else {
            header('Content-Type: application/octet-stream');
        }
        header("Content-Length: {$fileSize}");
        readfile($filePath);

        // Since all the headers and response have been sent, make the API
        // caller to exit quietly.
        throw new \OmegaUp\Exceptions\ExitException();
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
     * @omegaup-request-param string $commit
     * @omegaup-request-param string $filename
     * @omegaup-request-param string $problem_alias
     */
    public static function getInput(\OmegaUp\Request $r): void {
        $commit = $r->ensureString(
            'commit',
            fn (string $commit) => preg_match(
                '/^[0-9a-f]{40}$/',
                $commit
            ) === 1
        );
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem) || is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        $filename = $r->ensureString('filename');
        $zipDirectory = INPUTS_PATH . "{$problem->alias}/{$commit}";
        if (is_dir($zipDirectory)) {
            // The generation of problems has happened before. We're never
            // going to find this.
            throw new \OmegaUp\Exceptions\NotFoundException(
                'resourceNotFound'
            );
        }

        self::generateInputZip($problem, $commit, $filename);

        $zipPath = "{$zipDirectory}/{$problem->alias}-input.zip";
        $fileSize = @filesize($zipPath);
        if ($fileSize === false) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'resourceNotFound'
            );
        }
        header('Content-Type: application/zip');
        header("Content-Length: {$fileSize}");
        readfile($zipPath);

        // Since all the headers and response have been sent, make the API
        // caller to exit quietly.
        throw new \OmegaUp\Exceptions\ExitException();
    }

    public static function generateInputZip(
        \OmegaUp\DAO\VO\Problems $problem,
        string $commit,
        string $filename
    ): void {
        if ($filename != "{$problem->alias}-input.zip") {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }
        if (!in_array('cat', explode(',', $problem->languages))) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }
        if (is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        $problemCases = \OmegaUp\Controllers\Run::getProblemCasesMetadata(
            'cases',
            $problem->alias,
            $commit
        );
        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            $problem->alias,
            $commit
        );

        $tmpDir = \OmegaUp\FileHandler::tempDir(
            INPUTS_PATH,
            'InputZip',
            0755
        );

        try {
            $tmpPath = "{$tmpDir}/{$problem->alias}-input.zip";
            $zipArchive = new \ZipArchive();
            /** @var true|int */
            $err = $zipArchive->open(
                $tmpPath,
                \ZipArchive::CREATE
            );
            if ($err !== true) {
                throw new \OmegaUp\Exceptions\ProblemDeploymentFailedException(
                    'problemDeployerInternalError',
                    $err
                );
            }

            foreach ($problemCases as $file) {
                if (pathinfo($file['path'], PATHINFO_EXTENSION) !== 'in') {
                    continue;
                }
                $zipArchive->addFromString(
                    basename($file['path']),
                    $problemArtifacts->get($file['path'])
                );
            }
            $zipArchive->close();

            $zipDir = INPUTS_PATH . "{$problem->alias}/{$commit}";
            @mkdir(dirname($zipDir), 0755, true);
            rename($tmpDir, $zipDir);
        } catch (\Exception $e) {
            self::$log->error(
                "Failed to create input .zip for {$problem->alias}",
                ['exception' => $e],
            );
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'problemDeployerLibinteractiveValidationError',
                $e->getMessage()
            );
        } finally {
            \OmegaUp\FileHandler::deleteDirRecursively($tmpDir);
        }
    }

    /**
     * @omegaup-request-param 'bmp'|'gif'|'ico'|'jpe'|'jpeg'|'jpg'|'png'|'svg'|'svgz'|'tif'|'tiff' $extension
     * @omegaup-request-param string $object_id
     * @omegaup-request-param string $problem_alias
     */
    public static function getImage(\OmegaUp\Request $r): void {
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $objectId = $r->ensureString(
            'object_id',
            fn (string $objectId) => \OmegaUp\Validators::objectId($objectId)
        );

        $extension = $r->ensureEnum('extension', self::IMAGE_EXTENSIONS);

        self::regenerateImage($problemAlias, $objectId, $extension);

        $imagePath = IMAGES_PATH . "{$problemAlias}/{$objectId}.{$extension}";
        $filesize = filesize($imagePath);
        header("Content-Type: image/{$extension}");
        header("Content-Length: $filesize");
        readfile($imagePath);

        // Since all the headers and response have been sent, make the API
        // caller to exit quietly.
        throw new \OmegaUp\Exceptions\ExitException();
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
        $imageContents = $problemArtifacts->getByRevision();
        file_put_contents($imagePath, $imageContents);
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: LibinteractiveGenPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $idl
     * @omegaup-request-param 'c'|'cpp'|'java' $language
     * @omegaup-request-param null|string $name
     * @omegaup-request-param 'unix'|'windows' $os
     */
    public static function getLibinteractiveGenForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $response = [
            'templateProperties' => [
                'payload' => [
                    'error' => null,
                    'language' => 'c',
                    'os' => 'unix',
                    'name' => null,
                    'idl' => null,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleLibInteractive'
                ),
            ],
            'entrypoint' => 'libinteractive_gen',
        ];
        if (count($r) === 0) {
            // \OmegaUp\Request does not support empty().
            return $response;
        }

        $language = $r->ensureOptionalEnum('language', ['c', 'cpp', 'java']);
        $os = $r->ensureOptionalEnum('os', ['unix', 'windows']);
        $name = $r->ensureOptionalString('name');
        $idl = $r->ensureOptionalString('idl');
        $response['templateProperties']['payload'] =  [
            'language' => $language,
            'os' => $os,
            'name' => $name,
            'idl' => $idl,
        ];
        if (empty($language)) {
            $response['templateProperties']['payload']['error'] = [
                'description' => \OmegaUp\Translations::getInstance()->get(
                    'parameterInvalid'
                ),
                'field' => 'language',
            ];
            return $response;
        }
        if (empty($os)) {
            $response['templateProperties']['payload']['error'] = [
                'description' => \OmegaUp\Translations::getInstance()->get(
                    'parameterInvalid'
                ),
                'field' => 'os',
            ];
            return $response;
        }
        if (empty($name)) {
            $response['templateProperties']['payload']['error'] = [
                'description' => \OmegaUp\Translations::getInstance()->get(
                    'parameterInvalid'
                ),
                'field' => 'name',
            ];
            return $response;
        }
        try {
            \OmegaUp\Validators::alias($name);
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $response['templateProperties']['payload']['error'] = [
                'description' => $e->getErrorMessage(),
                'field' => 'name',
            ];
            return $response;
        }
        if (empty($idl)) {
            $response['templateProperties']['payload']['error'] = [
                'description' => \OmegaUp\Translations::getInstance()->get(
                    'parameterInvalid'
                ),
                'field' => 'idl',
            ];
            return $response;
        }
        $dirname = \OmegaUp\FileHandler::TempDir(
            sys_get_temp_dir(),
            'libinteractive'
        );
        try {
            file_put_contents("{$dirname}/{$name}.idl", $idl);
            $args = [
                '/usr/bin/java',
                '-jar',
                '/usr/share/java/libinteractive.jar',
                'generate',
                "{$name}.idl",
                $language,
                $language,
                '--makefile',
                "--{$os}",
            ];
            $descriptorspec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ];
            $cmd = join(' ', array_map('escapeshellarg', $args));
            $pipes = [];
            $proc = proc_open(
                $cmd,
                $descriptorspec,
                $pipes,
                $dirname,
                ['LANG' => 'en_US.UTF-8']
            );
            if (!is_resource($proc)) {
                $lastError = error_get_last();
                $response['templateProperties']['payload']['error'] = [
                    'description' => $lastError['message'] ?? \OmegaUp\Translations::getInstance()->get(
                        'parameterInvalid'
                    ),
                    'field' => 'idl',
                ];
                return $response;
            }
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $err = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $retval = proc_close($proc);

            if ($retval != 0) {
                $response['templateProperties']['payload']['error'] = [
                    'description' => "{$output}{$err}",
                    'field' => 'idl',
                ];
                return $response;
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

            /**
             * @var string $name
             * @var \SplFileInfo $file
             */
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
                "Content-Disposition: attachment; filename={$name}.zip"
            );
            readfile("{$dirname}/interactive.zip");
            \OmegaUp\FileHandler::deleteDirRecursively($dirname);

            // Since all the headers and response have been sent, make the API
            // caller to exit quietly.
            throw new \OmegaUp\Exceptions\ExitException();
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            throw $e;
        } catch (\Exception $e) {
            $response['templateProperties']['payload']['error'] = [
                'description' => strval($e),
                'field' => 'idl',
            ];
            return $response;
        } finally {
            \OmegaUp\FileHandler::deleteDirRecursively($dirname);
        }
    }

    /**
     * @return array{templateProperties: array{payload: CollectionDetailsByLevelPayload, title: \OmegaUp\TranslationString, fullWidth: bool}, entrypoint: string}
     *
     * @omegaup-request-param null|string $difficulty
     * @omegaup-request-param null|string $difficulty_range
     * @omegaup-request-param ''|'all'|'en'|'es'|'pt'|null $language
     * @omegaup-request-param string $level
     * @omegaup-request-param int|null $max_difficulty
     * @omegaup-request-param int|null $min_difficulty
     * @omegaup-request-param int|null $min_visibility
     * @omegaup-request-param int|null $offset
     * @omegaup-request-param bool|null $only_karel
     * @omegaup-request-param ''|'accepted'|'creation_date'|'difficulty'|'points'|'problem_id'|'quality'|'ratio'|'score'|'submissions'|'title'|null $order_by
     * @omegaup-request-param int|null $page
     * @omegaup-request-param null|string $programming_languages
     * @omegaup-request-param 'all'|'onlyQualityProblems'|null $quality
     * @omegaup-request-param null|string $query
     * @omegaup-request-param bool|null $require_all_tags
     * @omegaup-request-param int|null $rowcount
     * @omegaup-request-param bool|null $some_tags
     * @omegaup-request-param ''|'asc'|'desc'|null $sort_order
     */
    public static function getCollectionsDetailsByLevelForTypeScript(\OmegaUp\Request $r): array {
        // Authenticate request
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing, we allow unauthenticated users to use this API
            $r->identity = null;
        }

        $collectionLevel = $r->ensureString('level');

        $frequentTags = [];

        $offset = $r->ensureOptionalInt('offset');
        $pageSize = $r->ensureOptionalInt(
            'rowcount'
        ) ?? \OmegaUp\Controllers\Problem::PAGE_SIZE;
        $difficulty = $r->ensureOptionalString('difficulty') ?? 'all';
        $quality = $r->ensureOptionalEnum(
            'quality',
            self::QUALITY_VALUES
        ) ?? 'onlyQualityProblems';
        if ($offset < 0) {
            $offset = 0;
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
            'authors' => $authors,
        ] = self::validateListParams($r);

        $result = self::getList(
            $page,
            $language,
            $orderBy,
            $sortOrder,
            $offset,
            $pageSize,
            $tags,
            $keyword,
            $requireAllTags,
            $programmingLanguages,
            $minVisibility,
            $difficultyRange,
            $r->identity,
            $r->user,
            onlyQualitySeal: ($quality === 'onlyQualityProblems'),
            url: "/problem/collection/{$collectionLevel}/",
            level: $collectionLevel,
            difficulty: $difficulty,
            authors: $authors
        );

        $frequentTags = \OmegaUp\Controllers\Tag::getFrequentQualityTagsByLevel(
            $collectionLevel,
            rows: 15
        );

        $publicTags = \OmegaUp\Controllers\Tag::getPublicQualityTagsByLevel(
            $collectionLevel
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'publicTags' => $publicTags,
                    'frequentTags' => $frequentTags,
                    'level' => $collectionLevel,
                    'problems' => $result['problems'],
                    'loggedIn' => !is_null($r->identity),
                    'selectedTags' => $result['selectedTags'],
                    'pagerItems' => $result['pagerItems'],
                    'keyword' => $result['keyword'],
                    'mode' => $result['mode'],
                    'column' => $result['column'],
                    'language' => $result['language'],
                    'languages' => $result['languages'],
                    'modes' => $result['modes'],
                    'columns' => $result['columns'],
                    'tagsList' => $result['tags'],
                    'tagData' => $result['tagData'],
                    'difficulty' => $result['difficulty'],
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCollectionsByLevel'
                ),
                'fullWidth' => true,
            ],
            'entrypoint' => 'problem_collection_list',
        ];
    }

    /**
     * @return array{alias: string}
     */
    public static function apiRandomLanguageProblem(\OmegaUp\Request $r) {
        return [
            'alias' => \OmegaUp\DAO\Problems::getRandomLanguageProblemAlias(),
        ];
    }

    /**
     * @return array{alias: string}
     */
    public static function apiRandomKarelProblem(\OmegaUp\Request $r) {
        return [
            'alias' => \OmegaUp\DAO\Problems::getRandomKarelProblemAlias(),
        ];
    }

    /**
     * @param list<string> $tags
     * @param list<string> $programmingLanguages
     * @param array{0: int, 1: int}|null $difficultyRange
     * @param list<string> $authors
     *
     * @return array{column: string, columns: list<string>, selectedTags: list<string>, keyword: string, language: string, languages: list<string>, mode: string, modes: list<string>, problems: list<ProblemListItem>, pagerItems: list<PageItem>, tagData: list<Tag>, tags: list<string>, difficulty: string, authors: list<string>}
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
        ?\OmegaUp\DAO\VO\Users $user,
        bool $onlyQualitySeal,
        string $url,
        ?string $level,
        string $difficulty,
        array $authors
    ) {
        $response = self::getListImpl(
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
            $identity,
            $user,
            $onlyQualitySeal,
            $level,
            $difficulty,
            $authors
        );

        $params = [
            'query' => $keyword,
            'language' => $language,
            'order_by' => $orderBy,
            'sort_order' => $sortOrder,
            'tag' => $tags,
            'author' => $authors,
            'difficulty' => $difficulty
        ];

        $pagerItems = \OmegaUp\Pager::paginateWithUrl(
            $response['total'],
            $rowcount,
            $page ?: 1,
            $url,
            5,
            $params
        );

        $tagData = [];
        $allTags = self::getAllTagsFromCache();

        foreach ($allTags as $tag) {
            if (is_null($tag->name)) {
                continue;
            }
            if (!$tag->public) {
                continue;
            }
            $tagData[] = ['name' => $tag->name];
        }

        return [
            'problems' => $response['results'],
            'selectedTags' => $tags,
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
            'difficulty' => $difficulty,
            'authors' => $authors
        ];
    }

    /**
     * @return array{templateProperties: array{payload: CollectionDetailsByAuthorPayload, title: \OmegaUp\TranslationString, fullWidth: bool}, entrypoint: string}
     *
     * @omegaup-request-param null|string $difficulty
     * @omegaup-request-param null|string $difficulty_range
     * @omegaup-request-param ''|'all'|'en'|'es'|'pt'|null $language
     * @omegaup-request-param int|null $max_difficulty
     * @omegaup-request-param int|null $min_difficulty
     * @omegaup-request-param int|null $min_visibility
     * @omegaup-request-param int|null $offset
     * @omegaup-request-param bool|null $only_karel
     * @omegaup-request-param ''|'accepted'|'creation_date'|'difficulty'|'points'|'problem_id'|'quality'|'ratio'|'score'|'submissions'|'title'|null $order_by
     * @omegaup-request-param int|null $page
     * @omegaup-request-param null|string $programming_languages
     * @omegaup-request-param 'all'|'onlyQualityProblems'|null $quality
     * @omegaup-request-param null|string $query
     * @omegaup-request-param bool|null $require_all_tags
     * @omegaup-request-param int|null $rowcount
     * @omegaup-request-param bool|null $some_tags
     * @omegaup-request-param ''|'asc'|'desc'|null $sort_order
     */
    public static function getCollectionsDetailsByAuthorForTypeScript(\OmegaUp\Request $r): array {
        // Authenticate request
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing, we allow unauthenticated users to use this API
            $r->identity = null;
        }

        $authorsRanking = [];

        $offset = $r->ensureOptionalInt('offset');
        $pageSize = $r->ensureOptionalInt(
            'rowcount'
        ) ?? \OmegaUp\Controllers\Problem::PAGE_SIZE;
        $difficulty = $r->ensureOptionalString('difficulty') ?? 'all';
        $quality = $r->ensureOptionalEnum(
            'quality',
            self::QUALITY_VALUES
        ) ?? 'onlyQualityProblems';
        if ($offset < 0) {
            $offset = 0;
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
            'authors' => $authors,
        ] = self::validateListParams($r);

        $result = self::getList(
            $page,
            $language,
            $orderBy,
            $sortOrder,
            $offset,
            $pageSize,
            $tags,
            $keyword,
            $requireAllTags,
            $programmingLanguages,
            $minVisibility,
            $difficultyRange,
            $r->identity,
            $r->user,
            onlyQualitySeal: ($quality === 'onlyQualityProblems'),
            url: '/problem/collection/author/',
            level: null,
            difficulty: $difficulty,
            authors: $authors
        );

        $authorsRanking = \OmegaUp\Controllers\User::getAuthorsRankWithQualityProblems(
            offset: 1,
            rowCount: 15
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'authorsRanking' => $authorsRanking,
                    'problems' => $result['problems'],
                    'loggedIn' => !is_null($r->identity),
                    'selectedTags' => $result['selectedTags'],
                    'pagerItems' => $result['pagerItems'],
                    'keyword' => $result['keyword'],
                    'mode' => $result['mode'],
                    'column' => $result['column'],
                    'language' => $result['language'],
                    'languages' => $result['languages'],
                    'modes' => $result['modes'],
                    'columns' => $result['columns'],
                    'tags' => $result['tags'],
                    'tagData' => $result['tagData'],
                    'difficulty' => $result['difficulty'],
                    'authors' => $result['authors'],
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCollectionsByAuthor'
                ),
                'fullWidth' => true,
            ],
            'entrypoint' => 'problem_collection_list_by_author',
        ];
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: ProblemPrintDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param null|string $lang
     */
    public static function getProblemPrintDetailsForTypeScript(\OmegaUp\Request $r) {
        // Get user
        $r->ensureIdentity();

        // Validate request
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $lang = \OmegaUp\Controllers\Identity::getPreferredLanguage(
            $r->identity,
            $r
        );
        [
            'problem' => $problem,
            'problemset' => $problemset,
        ] = self::getValidProblemAndProblemset(
            $r->identity,
            contestAlias: null,
            problemAlias: $problemAlias,
            statementType: 'markdown',
            problemsetId: null
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        $details = self::getProblemDetails(
            $r->identity,
            $problem,
            $problemset,
            $lang,
            showSolvers: true,
            preventProblemsetOpen: false,
        );

        if (is_null($details)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'details' => $details,
                ],
                'hideFooterAndHeader' => true,
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleProblemPrint',
                    [
                        'problemTitle' => $details['title'],
                    ]
                ),
            ],
            'entrypoint' => 'problem_print',
        ];
    }

    /**
     * Returns the CDP structure for the given problem
     *
     * @param \OmegaUp\DAO\VO\Problems $problem
     * @param string $commit
     *
     * @return CDP|null
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     */
    public static function getProblemCDP(
        \OmegaUp\DAO\VO\Problems $problem,
        string $commit
    ) {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEM_CDP_DATA,
            "{$problem->alias}-{$commit}",
            fn () => \OmegaUp\Controllers\Problem::getProblemCDPImpl([
                'alias' => strval($problem->alias),
                'commit' => $commit
            ]),
            APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT
        );
    }

    /**
     * Loads and decodes the CDP data from artifacts or ZIP.
     *
     * @param array{alias: string, commit: string} $params
     *
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     *
     * @return null|CDP
     */
    private static function getProblemCDPImpl(array $params) {
        if ($params['alias'] === '') {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $problemArtifacts = new \OmegaUp\ProblemArtifacts(
            $params['alias']
        );

        $sourcePath  = 'cdp.data';
        $zipFilePath = null;

        if (!$problemArtifacts->exists($sourcePath)) {
            $zipFilePath = $problemArtifacts->getZip();
        }

        try {
            if (!is_null($zipFilePath)) {
                $result = \OmegaUp\ZipToCdpConverter::convert(
                    $zipFilePath,
                    $params['alias']
                );
            } else {
                $jsonContent = mb_convert_encoding(
                    $problemArtifacts->get(
                        $sourcePath
                    ),
                    'utf-8'
                );
                $result = json_decode($jsonContent, associative: true);
            }
            if (!is_array($result)) {
                return null;
            }
            /** @var CDP $result */
            return $result;
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            return null;
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \OmegaUp\Exceptions\InvalidFilesystemOperationException(
                'cdpNotFound'
            );
        } finally {
            if (!is_null($zipFilePath) && file_exists($zipFilePath)) {
                unlink($zipFilePath);
            }
        }
    }

    /**
     * Validates the required fields for CDP group and case data.
     *
     * @param array $data Incoming data for groups or cases
     * @param bool $isCase Whether the validation applies to a case
     * @return void
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateData(array $data, bool $isCase): void {
        if (!isset($data['group']) || !is_array($data['group'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'missingGroup'
            );
        }

        $groupRequiredFields = ['groupID', 'name', 'points', 'autoPoints', 'ungroupedCase', 'cases'];
        foreach ($groupRequiredFields as $field) {
            if (!isset($data['group'][$field])) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'missingGroupField'
                );
            }
        }

        if ($isCase) {
            if (!isset($data['case']) || !is_array($data['case'])) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'missingCase'
                );
            }

            $caseRequiredFields = ['caseID', 'groupID', 'name', 'points', 'autoPoints', 'lines', 'output'];
            foreach ($caseRequiredFields as $field) {
                if (!isset($data['case'][$field])) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'missingCaseField'
                    );
                }
            }
        }
    }

    /**
     * Updates or creates a case in the CDP and commits the changes to the problem.
     *
     * @param \OmegaUp\DAO\VO\Identities $identity Identity performing the change
     * @param \OmegaUp\DAO\VO\Users $user User associated with the change
     * @param \OmegaUp\DAO\VO\Problems $problem Problem being edited
     * @param array $newCaseData New case data from the request
     * @param array $groupData Group data related to the case
     * @param CDP $cdp CDP data structure (modified in place)
     * @param string $message Commit message
     * @param string $updatePublished Update published problems/assignments mode
     *
     * @return CDP Modified CDP structure
     *
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function updateCase(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Problems $problem,
        array $newCaseData,
        array $groupData,
        array $cdp,
        string $message,
        string $updatePublished
    ) {
        if (is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);

        // Search to see if the case exists in the CDP
        $caseInfo = self::findCaseInCDP($cdp, $newCaseData['caseID']);

        $isEditOperation = !is_null($caseInfo);

        $result = $isEditOperation
            ? self::handleEditCase(
                $newCaseData,
                $groupData,
                $cdp,
                $problemArtifacts
            )
            : self::handleNewCase(
                $newCaseData,
                $groupData,
                $cdp,
                $problemArtifacts
            );

        self::commitChanges(
            $problemArtifacts,
            $problem,
            $identity,
            $user,
            $message,
            $updatePublished,
            $result['blobUpdate'],
            $result['pathsToExclude']
        );

        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::PROBLEM_CDP_DATA,
            "{$problem->alias}-{$problem->commit}"
        );
        /** @var CDP */
        return $result['cdp'];
    }

    /**
     * Applies changes to an existing case in the CDP and builds file operations.
     *
     * @param array $newCaseData New case data from the request
     * @param array $groupData Group data related to the case
     * @param CDP $cdp CDP data structure (modified in place)
     * @param \OmegaUp\ProblemArtifacts $problemArtifacts Problem artifacts helper
     *
     * @param-out CDP $cdp
     *
     * @return array{blobUpdate: array<string, string>, pathsToExclude: list<string>, cdp: CDP}
     *
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     */
    private static function handleEditCase(
        array $newCaseData,
        array $groupData,
        array $cdp,
        \OmegaUp\ProblemArtifacts $problemArtifacts
    ): array {
        $caseID = $newCaseData['caseID'];
        // Search for original case in CDP
        $caseInfo = self::findCaseInCDP($cdp, $caseID);
        if (is_null($caseInfo)) {
            throw new \OmegaUp\Exceptions\NotFoundException('missingCase');
        }

        $uploadedInput = null;
        $uploadedOutput = null;

        if (isset($_FILES['input_file'])) {
            $uploadedInput = self::processUploadedFile($_FILES['input_file']);
        }

        if (isset($_FILES['output_file'])) {
            $uploadedOutput = self::processUploadedFile($_FILES['output_file']);
        }

        $oldCase = $caseInfo['case'];
        $oldGroup = $caseInfo['group'];
        $caseIndex = $caseInfo['caseIndex'];
        $groupIndex = $caseInfo['groupIndex'];

        // Old data (from the request)
        $oldGroupID = $oldCase['groupID'];
        $oldCaseName = $oldCase['name'];
        $oldInput = self::getLineValues($oldCase);
        $oldOutput = $oldCase['output'] ?? '';
        $oldPoints = $oldCase['points'];

        // New data (from the CDP)
        $newGroupID = $newCaseData['groupID'];
        $newCaseName = $newCaseData['name'];
        $newInput = $uploadedInput['truncated'] ?? self::getLineValues(
            $newCaseData
        );
        $newOutput = $uploadedOutput['truncated'] ?? ($newCaseData['output'] ?? '');
        $newPoints = $newCaseData['points'];

        // Detect if it is ungrouped
        $oldGroupName = $oldGroup['name'];
        $isOldUngrouped = ($oldGroup['ungroupedCase'] ?? false);
        $isNewUngrouped = ($groupData['ungroupedCase'] ?? false);

        // Detect changes
        $nameChanged = ($newCaseName !== $oldCaseName);
        $groupChanged = ($newGroupID !== $oldGroupID);
        $inputChanged = !is_null($uploadedInput) || ($newInput !== $oldInput);
        $outputChanged = !is_null(
            $uploadedOutput
        ) || ($newOutput !== $oldOutput);
        $pointsChanged = ($newPoints !== $oldPoints);

        if (!$nameChanged && !$groupChanged && !$inputChanged && !$outputChanged && !$pointsChanged) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'noChangesDetected'
            );
        }

        $oldPathBase = $isOldUngrouped
        ? $oldCaseName
        : "{$oldGroupName}.{$oldCaseName}";

        $newGroupName = $groupData['name'];
        $newPathBase = $isNewUngrouped
            ? $newCaseName
            : "{$newGroupName}.{$newCaseName}";

        $oldInputPath = "cases/{$oldPathBase}.in";
        $oldOutputPath = "cases/{$oldPathBase}.out";
        $newInputPath = "cases/{$newPathBase}.in";
        $newOutputPath = "cases/{$newPathBase}.out";

        // Verify that the old files exist
        if (
            !$problemArtifacts->exists(
                $oldInputPath
            ) || !$problemArtifacts->exists(
                $oldOutputPath
            )
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('missingCaseFiles');
        }

        $blobUpdate = [];
        $pathsToExclude = [];

        // Managing file changes
        if ($nameChanged || $groupChanged) {
            //Verify that the new route does not exist
            if (
                $problemArtifacts->exists(
                    $newInputPath
                ) || $problemArtifacts->exists(
                    $newOutputPath
                )
            ) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'caseAlreadyExists'
                );
            }

            $blobUpdate[$newInputPath] = $uploadedInput['full'] ?? $newInput;
            $blobUpdate[$newOutputPath] = $uploadedOutput['full'] ?? $newOutput;

            $pathsToExclude[] = $oldInputPath;
            $pathsToExclude[] = $oldOutputPath;
        } else {
            if (!is_null($uploadedInput)) {
                $blobUpdate[$oldInputPath] = $uploadedInput['full'];
            } elseif ($inputChanged) {
                $blobUpdate[$oldInputPath] = $newInput;
            }

            if (!is_null($uploadedOutput)) {
                $blobUpdate[$oldOutputPath] = $uploadedOutput['full'];
            } elseif ($outputChanged) {
                $blobUpdate[$oldOutputPath] = $newOutput;
            }
        }

        $newLine = is_null($uploadedInput)
            ? $newCaseData['lines']
            : [[
                'lineID' => $newCaseData['lines'][0]['lineID'],
                'caseID' => $caseID,
                'label'  => '',
                'data'   => ['kind' => 'multiline', 'value' => $uploadedInput['truncated']],
            ]];

        // Update CDP
        if ($groupChanged) {
            // Move case to another group
            $targetGroupIndex = null;

            if ($isNewUngrouped) {
                // Search for or create an ungrouped group
                foreach ($cdp['casesStore']['groups'] as $idx => $grp) {
                    if ($grp['ungroupedCase'] ?? false) {
                        $targetGroupIndex = $idx;
                        break;
                    }
                }

                if (is_null($targetGroupIndex)) {
                    $cdp['casesStore']['groups'][] = [
                        'groupID' => $groupData['groupID'],
                        'name' => $groupData['name'],
                        'points' => $groupData['points'],
                        'autoPoints' => $groupData['autoPoints'],
                        'ungroupedCase' => true,
                        'cases' => []
                    ];
                    $targetGroupIndex = count($cdp['casesStore']['groups']) - 1;
                }
            } else {
                // Search for or create a normal group
                $newGroupInfo = self::findGroupInCDP($cdp, $newGroupID);

                if (is_null($newGroupInfo)) {
                    // Create new group
                    $cdp['casesStore']['groups'][] = [
                        'groupID' => $groupData['groupID'],
                        'name' => $groupData['name'],
                        'points' => $groupData['points'],
                        'autoPoints' => $groupData['autoPoints'],
                        'ungroupedCase' => false,
                        'cases' => []
                    ];
                    $targetGroupIndex = count($cdp['casesStore']['groups']) - 1;
                } else {
                    $targetGroupIndex = $newGroupInfo['groupIndex'];
                }
            }

            // Remove from the previous group
            array_splice(
                $cdp['casesStore']['groups'][$groupIndex]['cases'],
                $caseIndex,
                1
            );

            // Add to new group
            $cdp['casesStore']['groups'][$targetGroupIndex]['cases'][] = [
                'caseID' => $caseID,
                'groupID' => $newGroupID,
                'lines' => $newLine,
                'points' => $newCaseData['points'],
                'autoPoints' => $newCaseData['autoPoints'],
                'output' => $newOutput,
                'name' => $newCaseName
            ];
        } else {
            // Same group, update in-place
            $cdp['casesStore']['groups'][$groupIndex]['cases'][$caseIndex] = [
                'caseID' => $caseID,
                'groupID' => $newGroupID,
                'lines' => $newLine,
                'points' => $newCaseData['points'],
                'autoPoints' => $newCaseData['autoPoints'],
                'output' => $newOutput,
                'name' => $newCaseName
            ];
        }
        /** @var array<string, string> $blobUpdate*/
        $blobUpdate;
        /** @var list<string> $pathsToExclude*/
        $pathsToExclude;
        /** @var CDP $cdp*/
        $cdp;
        return [
            'blobUpdate' => $blobUpdate,
            'pathsToExclude' => $pathsToExclude,
            'cdp' => $cdp
        ];
    }

    /**
     * Creates a new case in the CDP and builds the corresponding file updates.
     *
     * @param array $newCaseData New case data from the request
     * @param array $groupData Group data related to the case
     * @param CDP $cdp CDP data structure (modified in place)
     * @param \OmegaUp\ProblemArtifacts $problemArtifacts Problem artifacts helper
     *
     * @return array{blobUpdate: array<string, string>, pathsToExclude: list<string>, cdp: CDP}
     *
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     */
    private static function handleNewCase(
        array $newCaseData,
        array $groupData,
        array $cdp,
        \OmegaUp\ProblemArtifacts $problemArtifacts
    ): array {
        $newGroupID = $newCaseData['groupID'];
        $newCaseName = $newCaseData['name'];
        $newInput = self::getLineValues($newCaseData);
        $newOutput = $newCaseData['output'] ?? '';

        // Determine if it is ungrouped using the group data
        $isUngrouped = ($groupData['ungroupedCase'] ?? false);

        // Search for or create the group
        $groupInfo = self::findGroupInCDP($cdp, $newGroupID);
        $targetGroupIndex = null;

        if ($isUngrouped) {
            // Search for ungrouped group
            foreach ($cdp['casesStore']['groups'] as $idx => $grp) {
                if ($grp['ungroupedCase'] ?? false) {
                    $targetGroupIndex = $idx;
                    break;
                }
            }

            if (is_null($targetGroupIndex)) {
                // Create ungrouped group with frontend data
                $cdp['casesStore']['groups'][] = [
                    'groupID' => $groupData['groupID'],
                    'name' => $groupData['name'],
                    'points' => $groupData['points'],
                    'autoPoints' => $groupData['autoPoints'],
                    'ungroupedCase' => true,
                    'cases' => []
                ];
                $targetGroupIndex = count($cdp['casesStore']['groups']) - 1;
            }
        } else {
            if (is_null($groupInfo)) {
                // The group does not exist; create it with the frontend data.
                $cdp['casesStore']['groups'][] = [
                    'groupID' => $groupData['groupID'],
                    'name' => $groupData['name'],
                    'points' => $groupData['points'],
                    'autoPoints' => $groupData['autoPoints'],
                    'ungroupedCase' => false,
                    'cases' => []
                ];
                $targetGroupIndex = count($cdp['casesStore']['groups']) - 1;
            } else {
                $targetGroupIndex = $groupInfo['groupIndex'];
            }
        }

        // Generate file path
        $newGroupName = $groupData['name'];
        $pathBase = $isUngrouped
            ? $newCaseName
            : "{$newGroupName}.{$newCaseName}";

        $newInputPath = "cases/{$pathBase}.in";
        $newOutputPath = "cases/{$pathBase}.out";

        // Verify that there are no files with that name.
        if (
            $problemArtifacts->exists(
                $newInputPath
            ) || $problemArtifacts->exists(
                $newOutputPath
            )
        ) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'caseAlreadyExists'
            );
        }

        $blobUpdate = [];
        $blobUpdate[$newInputPath] = $newInput;
        $blobUpdate[$newOutputPath] = $newOutput;

        // Add case to the CDP
        $cdp['casesStore']['groups'][$targetGroupIndex]['cases'][] = [
            'caseID' => $newCaseData['caseID'],
            'groupID' => $newGroupID,
            'name' => $newCaseName,
            'lines' => $newCaseData['lines'],
            'points' => $newCaseData['points'],
            'autoPoints' => $newCaseData['autoPoints'],
            'output' => $newOutput
        ];
        /** @var array<string, string> $blobUpdate */
        $blobUpdate;
        /** @var CDP $cdp */
        $cdp;
        return [
            'blobUpdate' => $blobUpdate,
            'pathsToExclude' => [],
            'cdp' => $cdp
        ];
    }

    /**
     * Updates a group in the CDP and commits the changes to the problem.
     *
     * @param \OmegaUp\DAO\VO\Identities $identity Identity performing the change
     * @param \OmegaUp\DAO\VO\Users $user User associated with the change
     * @param \OmegaUp\DAO\VO\Problems $problem Problem being edited
     * @param array $newGroupData New group data from the request
     * @param CDP $cdp CDP data structure (modified in place)
     * @param string $message Commit message
     * @param string $updatePublished Update published problems/assignments mode
     *
     * @return CDP Modified CDP structure
     *
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function updateGroup(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Problems $problem,
        array $newGroupData,
        array $cdp,
        string $message,
        string $updatePublished
    ) {
        if (is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);

        // Search to see if the group exists in the CDP
        $groupInfo = self::findGroupInCDP($cdp, $newGroupData['groupID']);

        if (is_null($groupInfo)) {
            throw new \OmegaUp\Exceptions\NotFoundException('groupNotFound');
        }

        $result = self::handleEditGroup($newGroupData, $cdp, $groupInfo);

        self::commitChanges(
            $problemArtifacts,
            $problem,
            $identity,
            $user,
            $message,
            $updatePublished,
            [], //blobUpdate
            [], // pathsToExclude
            $result['pathsToRename']
        );

        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::PROBLEM_CDP_DATA,
            "{$problem->alias}-{$problem->commit}"
        );
        return $result['cdp'];
    }

     /**
     * Applies changes to a group in the CDP and builds rename operations if needed.
     *
     * @param array $newGroupData New group data from the request
     * @param CDP $cdp CDP data structure (modified in place)
     * @param array $groupInfo Group metadata (group and index)
     *
     * @param-out CDP $cdp
     *
     * @return array{pathsToRename: array<string, string>, cdp: CDP}
     *
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     */
    private static function handleEditGroup(
        array $newGroupData,
        array $cdp,
        array $groupInfo
    ): array {
        $oldGroup = $groupInfo['group'];
        $groupIndex = $groupInfo['groupIndex'];

        if ($oldGroup['ungroupedCase'] ?? false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'cannotEditUngroupedGroup'
            );
        }

        $oldGroupName = $oldGroup['name'];
        $newGroupName = $newGroupData['name'];
        $nameChanged = ($newGroupName !== $oldGroupName);

        $pathsToRename = [];
        if ($nameChanged) {
            // Verify that there is no other group with that name.
            foreach ($cdp['casesStore']['groups'] as $idx => $grp) {
                if ($idx !== $groupIndex && $grp['name'] === $newGroupName) {
                    throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                        'groupAlreadyExists'
                    );
                }
            }
            // Rename ALL files in the group.
            $pathsToRename["cases/{$oldGroupName}."] = "cases/{$newGroupName}.";

            $cdp['casesStore']['groups'][$groupIndex]['name'] = $newGroupName;
        }

        $cdp['casesStore']['groups'][$groupIndex]['points'] = $newGroupData['points'];
        $cdp['casesStore']['groups'][$groupIndex]['autoPoints'] = $newGroupData['autoPoints'];
        /** @var CDP $cdp */
        $cdp;
        return [
            'pathsToRename' => $pathsToRename,
            'cdp' => $cdp
        ];
    }

    /**
     * Deletes a case or a group from the CDP and commits the changes.
     *
     * @param \OmegaUp\DAO\VO\Identities $identity Identity performing the change
     * @param \OmegaUp\DAO\VO\Users $user User associated with the change
     * @param \OmegaUp\DAO\VO\Problems $problem Problem being edited
     * @param string $id Case or group identifier
     * @param CDP $cdp CDP data structure (modified in place)
     * @param string $message Commit message
     * @param string $updatePublished Update published problems/assignments mode
     *
     * @return CDP Modified CDP structure
     *
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function deleteGroupOrCase(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Problems $problem,
        string $id,
        array $cdp,
        string $message,
        string $updatePublished
    ) {
        if (is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problemArtifacts = new \OmegaUp\ProblemArtifacts($problem->alias);

        // First try searching as a case
        $caseInfo = self::findCaseInCDP($cdp, $id);

        if (!is_null($caseInfo)) {
            $result = self::handleDeleteCase($caseInfo, $cdp);
        } else {
            $groupInfo = self::findGroupInCDP($cdp, $id);

            if (is_null($groupInfo)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'elementNotFound'
                );
            }
             $result = self::handleDeleteGroup($groupInfo, $cdp);
        }

        self::commitChanges(
            $problemArtifacts,
            $problem,
            $identity,
            $user,
            $message,
            $updatePublished,
            [], //blobUpdate
            $result['pathsToExclude'],
            [], //pathsToRename
            true //isDelete
        );

        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::PROBLEM_CDP_DATA,
            "{$problem->alias}-{$problem->commit}"
        );
        /** @var CDP $cdp */
        return $result['cdp'];
    }

    /**
     * Removes a case from the CDP and prepares paths to delete.
     *
     * @param array $caseInfo Case metadata (case, group and indexes)
     * @param CDP $cdp CDP data structure (modified in place)
     *
     * @return array{pathsToExclude: list<string>, cdp: CDP}
     */
    private static function handleDeleteCase(
        array $caseInfo,
        array $cdp,
    ): array {
        $case = $caseInfo['case'];
        $group = $caseInfo['group'];
        $caseIndex = $caseInfo['caseIndex'];
        $groupIndex = $caseInfo['groupIndex'];

        $caseName = $case['name'];
        $groupName = $group['name'];
        $isUngrouped = ($group['ungroupedCase'] ?? false);

        $pathBase = $isUngrouped
            ? $caseName
            : "{$groupName}.{$caseName}";

        $pathsToExclude = [
            "cases/{$pathBase}.in",
            "cases/{$pathBase}.out",
        ];
        // Remove case from the CDP
        array_splice(
            $cdp['casesStore']['groups'][$groupIndex]['cases'],
            $caseIndex,
            1
        );

        // If the group is empty, delete the group as well.
        if (empty($cdp['casesStore']['groups'][$groupIndex]['cases'])) {
            array_splice($cdp['casesStore']['groups'], $groupIndex, 1);
        }
        /** @var CDP $cdp */
        $cdp;
        return [
            'pathsToExclude' => $pathsToExclude,
            'cdp' => $cdp
        ];
    }

    /**
     * Removes a group from the CDP and prepares paths to delete.
     *
     * @param array $groupInfo Group metadata (group and index)
     * @param CDP $cdp CDP data structure (modified in place)
     *
     * @return array{blobUpdate: array<empty, empty>, pathsToExclude: list<string>, cdp: CDP}
     */
    private static function handleDeleteGroup(
        array $groupInfo,
        array $cdp,
    ): array {
        $group = $groupInfo['group'];
        $groupIndex = $groupInfo['groupIndex'];

        $groupName = $group['name'];
        $pathsToExclude = ['cases/' . $groupName];

        // Remove group from the CDP
        array_splice($cdp['casesStore']['groups'], $groupIndex, 1);
        /** @var CDP $cdp */
        $cdp;
        return [
            'blobUpdate' => [],
            'pathsToExclude' => $pathsToExclude,
            'cdp' => $cdp
        ];
    }

    /**
     * Commits changes to problem artifacts and updates the published version when required.
     *
     * @param \OmegaUp\ProblemArtifacts $problemArtifacts Problem artifacts helper
     * @param \OmegaUp\DAO\VO\Problems $problem Problem being edited
     * @param \OmegaUp\DAO\VO\Identities $identity Identity performing the change
     * @param \OmegaUp\DAO\VO\Users $user User associated with the change
     * @param string $message Commit message
     * @param string $updatePublished
     * @param array $blobUpdate Files to add or update in the ZIP
     * @param array $pathsToExclude Paths to remove from the ZIP
     * @param array $pathsToRename Paths to rename in the ZIP
     * @param bool $isDelete Whether this operation only deletes entries
     *
     * @return void
     *
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\InvalidFilesystemOperationException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function commitChanges(
        \OmegaUp\ProblemArtifacts $problemArtifacts,
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Users $user,
        string $message,
        string $updatePublished,
        array $blobUpdate,
        array $pathsToExclude,
        array $pathsToRename = [],
        bool $isDelete = false,
    ): void {
        if (empty($blobUpdate) && !$isDelete && empty($pathsToRename)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'noChangesDetected'
            );
        }
        if (is_null($problem->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }
        $problemDeployer = new \OmegaUp\ProblemDeployer($problem->alias);

        if (!empty($pathsToExclude) || !empty($pathsToRename)) {
            $zipFilePath = $problemArtifacts->getZip();
            $problemDeployer->commitModifiedZip(
                $message,
                $identity,
                $zipFilePath,
                $pathsToExclude,
                $blobUpdate,
                $pathsToRename
            );
        } else {
            $problemDeployer->commitLooseFiles(
                $message,
                $identity,
                $blobUpdate
            );
        }

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
    }
    /**
     * Validates and reads an uploaded file, returning full and truncated contents.
     *
     * @param array $fileInfo File information from $_FILES
     * @param int $limitBytes Maximum size before truncation
     *
     * @return array{full:string,truncated:string}|null  Array with full and truncated content, or null if no file
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function processUploadedFile(
        array $fileInfo,
        int $limitBytes = \OmegaUp\Validators::ZIP_CASE_SIZE_LIMIT_BYTES
    ): ?array {
        if (
            !isset(
                $fileInfo['error']
            ) || $fileInfo['error'] === UPLOAD_ERR_NO_FILE
        ) {
            return null;
        }

        if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'fileUploadError'
            );
        }

        $tmpName = $fileInfo['tmp_name'];
        if (!file_exists($tmpName)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'fileUploadError'
            );
        }

        $fullContent = file_get_contents($tmpName);
        if ($fullContent === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'fileUploadError'
            );
        }

        $fileSize = strlen($fullContent);
        if ($fileSize > $limitBytes) {
            $truncatedContent = substr(
                $fullContent,
                0,
                $limitBytes
            ) . ' ...[TRUNCATED]';
        } else {
            $truncatedContent = $fullContent;
        }

        return [
            'full' => $fullContent,
            'truncated' => $truncatedContent
        ];
    }
    /**
     * Finds a case in the CDP by its ID.
     *
     * @param CDP $cdp CDP structure
     * @param string $caseID Case identifier
     *
     * @return array{case:CDPCase,caseIndex:int,group:CDPGroup,groupIndex:int}|null Case and group metadata, or null if not found
     */
    private static function findCaseInCDP(array $cdp, string $caseID): ?array {
        if (
            !isset(
                $cdp['casesStore']
            )  || !isset(
                $cdp['casesStore']['groups']
            )
        ) {
            return null;
        }
        /** @var list<CDPGroup> $groups */
        $groups = $cdp['casesStore']['groups'];

        foreach ($groups as $groupIndex => $group) {
            if (!isset($group['cases'])) {
                continue;
            }
            /** @var CDPGroup $group */
            foreach ($group['cases'] as $caseIndex => $case) {
                /** @var CDPCase $case */
                if (($case['caseID'] ?? '') === $caseID) {
                    $group['cases'] = [];
                    return [
                        'case' => $case,
                        'caseIndex' => $caseIndex,
                        'group' => $group,
                        'groupIndex' => $groupIndex
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Finds a group in the CDP by its ID.
     *
     * @param CDP rray $cdp CDP structure
     * @param string $groupID Group identifier
     *
     * @return array{group:CDPGroup,groupIndex:int}|null Group metadata, or null if not found
     */
    private static function findGroupInCDP(
        array $cdp,
        string $groupID
    ): ?array {
        if (
            !isset(
                $cdp['casesStore']
            )  || !isset(
                $cdp['casesStore']['groups']
            )
        ) {
            return null;
        }
        /** @var list<CDPGroup> $groups */
        $groups = $cdp['casesStore']['groups'];

        foreach ($groups as $groupIndex => $group) {
            /** @var CDPGroup $group */
            if (($group['groupID'] ?? '') === $groupID) {
                $group['cases'] = [];
                return [
                    'group' => $group,
                    'groupIndex' => $groupIndex
                ];
            }
        }

        return null;
    }

    /**
     * Builds a multiline string from CDP line data.
     *
     * @param array $data Case or input data with a 'lines' list of CDPLine entries
     *
     * @return string Concatenated lines separated by newlines
     */
    private static function getLineValues(array $data): string {
        $allLinesString = '';

        if (!isset($data['lines']) || !is_array($data['lines'])) {
            return '';
        }

        foreach ($data['lines'] as $line) {
            if (isset($line['data']['value'])) {
                $allLinesString .= $line['data']['value'];
            }
            $allLinesString .= "\n";
        }

        return rtrim($allLinesString, "\n");
    }

    /**
     * Convert an uploaded ZIP file to CDP.
     *
     * @param \OmegaUp\Request $r
     * @return array{status: 'ok', cdp: CDP}
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException If the ZIP is invalid or a validation fails.
     */
    public static function apiConvertZipToCdp(\OmegaUp\Request $r): array {
        $fileInfo = \OmegaUp\Validators::validateZipUploadedFile();
        $tempFilePath = $fileInfo['tmpFilePath'];
        $problemName = $fileInfo['problemName'];

        // Convert ZIP to CDP
        $cdp = \OmegaUp\ZipToCdpConverter::convert(
            $tempFilePath,
            $problemName
        );

        return [
            'status' => 'ok',
            'cdp' => $cdp
        ];
    }
}
