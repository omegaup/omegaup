<?php

namespace OmegaUp\Controllers;

/**
 * ContestController
 *
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 * @psalm-type PrivacyStatement=array{markdown: string, statementType: string, gitObjectId?: string}
 * @psalm-type Contest=array{acl_id?: int, admission_mode: string, alias: string, contest_id: int, description: string, feedback?: string, finish_time: \OmegaUp\Timestamp, languages?: null|string, last_updated: \OmegaUp\Timestamp, original_finish_time?: \OmegaUp\Timestamp, score_mode: string, penalty?: int, penalty_calc_policy?: string, penalty_type?: string, points_decay_factor?: float, problemset_id: int, recommended: bool, rerun_id: int|null, scoreboard?: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after?: int, start_time: \OmegaUp\Timestamp, submissions_gap?: int, title: string, urgent?: int, window_length: int|null}
 * @psalm-type NavbarProblemsetProblem=array{acceptsSubmissions: bool, alias: string, bestScore: int, hasRuns: bool, maxScore: float|int, text: string, myBestScore?: float|null, hasMyRuns?: bool|null}
 * @psalm-type ContestUser=array{access_time: \OmegaUp\Timestamp|null, country_id: null|string, end_time: \OmegaUp\Timestamp|null, is_owner: int|null, username: string}
 * @psalm-type ContestGroup=array{alias: string, name: string}
 * @psalm-type ContestRequest=array{accepted: bool|null, admin?: array{username?: null|string}, country: null|string, last_update: \OmegaUp\Timestamp|null, request_time: \OmegaUp\Timestamp, username: string}
 * @psalm-type ContestAdmin=array{role: string, username: string}
 * @psalm-type ContestGroupAdmin=array{alias: string, name: string, role: string}
 * @psalm-type ConsentStatement=array{contest_alias: string, privacy_git_object_id?: string, share_user_information: bool|null, statement_type?: string}
 * @psalm-type Clarification=array{answer: null|string, assignment_alias?: null|string, author: string, clarification_id: int, contest_alias?: null|string, message: string, problem_alias: string, public: bool, receiver: null|string, time: \OmegaUp\Timestamp}
 * @psalm-type ProblemQualityPayload=array{canNominateProblem: bool, dismissed: bool, dismissedBeforeAc: bool, language?: string, nominated: bool, nominatedBeforeAc: bool, problemAlias: string, solved: bool, tried: bool}
 * @psalm-type ProblemsetProblem=array{accepted: int, accepts_submissions: bool, alias: string, commit: string, difficulty: float, has_submissions: bool, input_limit: int, is_extra_problem: bool, languages: string, letter?: string, order: int, points: float, problem_id?: int, quality_payload?: ProblemQualityPayload, quality_seal: bool, submissions: int, title: string, version: string, visibility: int, visits: int}
 * @psalm-type Signature=array{email: string, name: string, time: \OmegaUp\Timestamp}
 * @psalm-type ProblemVersion=array{author: Signature, commit: string, committer: Signature, message: string, parents: list<string>, tree: array<string, string>, version: string}
 * @psalm-type ProblemsetProblemWithVersions=array{accepted: int, accepts_submissions: bool, alias: string, commit: string, difficulty: float, has_submissions: bool, input_limit: int, languages: string, letter?: string, order: int, points: float, quality_payload?: ProblemQualityPayload, quality_seal: bool, submissions: int, title: string, version: string, versions: array{log: list<ProblemVersion>, published: string}, visibility: int, visits: int}
 * @psalm-type ContestListMinePayload=array{contests: list<Contest>, privateContestsAlert: bool}
 * @psalm-type ContestDetails=array{admin: bool, admission_mode: string, alias: string, archived: bool, contest_for_teams: bool, description: string, director: string, feedback: string, finish_time: \OmegaUp\Timestamp, has_submissions: bool, languages: list<string>, needs_basic_information: bool, opened: bool, original_contest_alias: null|string, original_problemset_id: int|null, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problems: list<ProblemsetProblem>, problemset_id: int, recommended: bool, requests_user_information: string, rerun_id?: int, score_mode: string, scoreboard: int, scoreboard_url?: string, scoreboard_url_admin?: string, show_penalty: bool, default_show_all_contestants_in_scoreboard: bool, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submission_deadline?: \OmegaUp\Timestamp|null, submissions_gap: int, title: string, window_length: int|null}
 * @psalm-type ArenaContest=array{alias: string, director: string, finish_time: \OmegaUp\Timestamp|null, start_time: \OmegaUp\Timestamp|null, rerun_id?: int, title: string, window_length?: int}
 * @psalm-type ContestAdminDetails=array{admin: bool, admission_mode: string, alias: string, archived: bool, available_languages: array<string, string>, canSetRecommended: bool, contest_for_teams: bool, description: string, director: string, feedback: string, finish_time: \OmegaUp\Timestamp, has_submissions: bool, languages: list<string>, needs_basic_information: bool, opened: bool, original_contest_alias: null|string, original_problemset_id: int|null, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problems?: list<ProblemsetProblem>, problemset_id: int, recommended?: bool, requests_user_information: string, rerun_id?: int, score_mode: string, scoreboard: int, scoreboard_url: null|string, scoreboard_url_admin: null|string, show_penalty: bool, default_show_all_contestants_in_scoreboard: bool, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submission_deadline?: \OmegaUp\Timestamp|null, submissions_gap: int, title: string, window_length: int|null}
 * @psalm-type StatsPayload=array{alias: string, entity_type: string, cases_stats?: array<string, int>, pending_runs: list<string>, total_runs: int, verdict_counts: array<string, int>, max_wait_time?: \OmegaUp\Timestamp|null, max_wait_time_guid?: null|string, distribution?: array<int, int>, size_of_bucket?: float, total_points?: float}
 * @psalm-type ContestPublicDetails=array{admission_mode: string, alias: string, description: string, director: string, extra_note?: string|null, feedback: string, finish_time: \OmegaUp\Timestamp, languages: string, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, rerun_id: int|null, score_mode: string, scoreboard: int, show_penalty: bool, default_show_all_contestants_in_scoreboard: bool, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, user_registration_requested?: bool, user_registration_answered?: bool, user_registration_accepted?: bool|null, window_length: int|null}
 * @psalm-type ContestVirtualDetailsPayload=array{contest: ContestPublicDetails}
 * @psalm-type ContestCertificatesAdminDetails=array{certificateCutoff: int|null, certificatesStatus: string, isCertificateGenerator: bool}
 * @psalm-type ContestEditPayload=array{details: ContestAdminDetails, problems: list<ProblemsetProblemWithVersions>, users: list<ContestUser>, groups: list<ContestGroup>, teams_group: ContestGroup|null, requests: list<ContestRequest>, admins: list<ContestAdmin>, group_admins: list<ContestGroupAdmin>, original_contest_admission_mode: null|string, certificatesDetails: ContestCertificatesAdminDetails}
 * @psalm-type UserBasicInformation=array{country: null|string, state: null|string, school: null|int}
 * @psalm-type ContestIntroPayload=array{contest: ContestPublicDetails, needsBasicInformation: bool, privacyStatement: PrivacyStatement, requestsUserInformation: string, shouldShowModalToLoginWithRegisteredIdentity: bool, userBasicInformation: UserBasicInformation}
 * @psalm-type ContestListItem=array{admission_mode: string, alias: string, contest_id: int, contestants: int, description: string, duration_minutes: int|null, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp, organizer: string, original_finish_time: \OmegaUp\Timestamp, participating: bool, problemset_id: int, recommended: bool, rerun_id: int|null, score_mode?: string, scoreboard_url?: string, scoreboard_url_admin?: string, start_time: \OmegaUp\Timestamp, title: string, window_length: int|null}
 * @psalm-type ContestList=array{current: list<ContestListItem>, future: list<ContestListItem>, past: list<ContestListItem>}
 * @psalm-type TimeTypeContests=array<string, list<ContestListItem>>
 * @psalm-type ContestListPayload=array{contests: list<ContestListItem>, countContests: int, query: string | null}
 * @psalm-type ContestListv2Payload=array{contests: ContestList, countContests: array{current: int, future: int, past: int}, pageSize: int, query: string | null}
 * @psalm-type ContestNewPayload=array{languages: array<string, string>, hasVisitedSection?: bool, canSetRecommended?: bool}
 * @psalm-type Run=array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, execution: null|string, guid: string, language: string, memory: int, output: null|string, penalty: int, runtime: int, score: float, score_by_group?: array<string, float|null>, status: string, status_memory: null|string, status_runtime: null|string, submit_delay: int, suggestions?: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}
 * @psalm-type RunMetadata=array{verdict: string, time: float, sys_time: int, wall_time: float, memory: int}
 * @psalm-type ScoreboardEvent=array{classname: string, country: string, delta: float, is_invited: bool, total: array{points: float, penalty: float}, name: null|string, username: string, problem: array{alias: string, points: float, penalty: float}}
 * @psalm-type CaseResult=array{contest_score: float, max_score: float, meta: RunMetadata, name: string, out_diff?: string, score: float, verdict: string}
 * @psalm-type ScoreboardRankingProblemDetailsGroup=array{cases: list<array{meta: RunMetadata}>}
 * @psalm-type ScoreboardRankingProblem=array{alias: string, penalty: float, percent: float, pending?: int, place?: int, points: float, run_details?: array{cases?: list<CaseResult>, details: array{groups: list<ScoreboardRankingProblemDetailsGroup>}}, runs: int}
 * @psalm-type ScoreboardRankingEntry=array{classname: string, country: string, is_invited: bool, name: null|string, place?: int, problems: list<ScoreboardRankingProblem>, total: array{penalty: float, points: float}, username: string}
 * @psalm-type Scoreboard=array{finish_time: \OmegaUp\Timestamp|null, problems: list<array{alias: string, order: int}>, ranking: list<ScoreboardRankingEntry>, start_time: \OmegaUp\Timestamp, time: \OmegaUp\Timestamp, title: string}
 * @psalm-type ContestScoreboardPayload=array{contest: ContestDetails, contestAdmin: bool, problems: list<NavbarProblemsetProblem>, scoreboard: Scoreboard, scoreboardEvents: list<ScoreboardEvent>, scoreboardToken:null|string}
 * @psalm-type ContestDetailsPayload=array{adminPayload?: array{allRuns: list<Run>, totalRuns: int, users: list<ContestUser>}, clarifications: list<Clarification>, contest: ContestPublicDetails, original?: array{contest: \OmegaUp\DAO\VO\Contests, scoreboard?: Scoreboard, scoreboardEvents?: list<ScoreboardEvent>}, problems: list<NavbarProblemsetProblem>, scoreboard: Scoreboard, scoreboardEvents: list<ScoreboardEvent>, shouldShowFirstAssociatedIdentityRunWarning: bool, submissionDeadline: \OmegaUp\Timestamp|null}
 * @psalm-type ContestPracticeDetailsPayload=array{adminPayload?: array{allRuns: list<Run>, users: list<ContestUser>}, clarifications: list<Clarification>, contest: ContestPublicDetails, contestAdmin: bool, original?: array{contest: \OmegaUp\DAO\VO\Contests, scoreboard?: Scoreboard, scoreboardEvents?: list<ScoreboardEvent>}, problems: list<NavbarProblemsetProblem>, shouldShowFirstAssociatedIdentityRunWarning: bool, submissionDeadline: \OmegaUp\Timestamp|null}
 * @psalm-type Event=array{courseAlias?: string, courseName?: string, name: string, problem?: string}
 * @psalm-type ActivityEvent=array{classname: string, event: Event, ip: int|null, time: \OmegaUp\Timestamp, username: string}
 * @psalm-type ActivityFeedPayload=array{alias: string, events: list<ActivityEvent>, type: string, page: int, length: int, pagerItems: list<PageItem>}
 * @psalm-type Contestant=array{name: null|string, username: string, email: null|string, gender: null|string, state: null|string, country: null|string, school: null|string}
 * @psalm-type ListItem=array{key: string, value: string}
 * @psalm-type ScoreboardMergePayload=array{contests: list<ContestListItem>}
 * @psalm-type MergedScoreboardEntry=array{name: null|string, username: string, contests: array<string, array{points: float, penalty: float}>, total: array{points: float, penalty: float}, place?: int}
 * @psalm-type ContestReport=array{country: null|string, is_invited: bool, name: null|string, place?: int, problems: list<ScoreboardRankingProblem>, total: array{penalty: float, points: float}, username: string}
 * @psalm-type ContestReportDetailsPayload=array{contestAlias: string, contestReport: list<ContestReport>}
 * @psalm-type SettingLimits=array{input_limit: string, memory_limit: string, overall_wall_time_limit: string, time_limit: string}
 * @psalm-type NominationStatus=array{alreadyReviewed: bool, canNominateProblem: bool, dismissed: bool, dismissedBeforeAc: bool, language: string, nominated: bool, nominatedBeforeAc: bool, solved: bool, tried: bool}
 * @psalm-type ProblemsetterInfo=array{classname: string, creation_date: \OmegaUp\Timestamp|null, name: string, username: string}
 * @psalm-type InteractiveSettingsDistrib=array{idl: string, module_name: string, language: string, main_source: string, templates: array<string, string>}
 * @psalm-type LimitsSettings=array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}
 * @psalm-type ProblemSettingsDistrib=array{cases: array<string, array{in: string, out: string, weight?: float}>, interactive?: InteractiveSettingsDistrib, limits: LimitsSettings, validator: array{custom_validator?: array{language: string, limits?: LimitsSettings, source: string}, group_score_policy?: string, name: string, tolerance?: float}}
 * @psalm-type BestSolvers=array{classname: string, language: string, memory: float, runtime: float, time: \OmegaUp\Timestamp, username: string}
 * @psalm-type ProblemStatement=array{images: array<string, string>, sources: array<string, string>, language: string, markdown: string}
 * @psalm-type ProblemCasesContents=array<string, array{contestantOutput?: string, in: string, out: string}>
 * @psalm-type RunDetailsGroup=array{cases: list<CaseResult>, contest_score: float, group: string, max_score: float, score: float, verdict?: string}
 * @psalm-type SubmissionFeedbackThread=array{author: string, authorClassname: string, submission_feedback_thread_id: int, text: string, timestamp: \OmegaUp\Timestamp}
 * @psalm-type SubmissionFeedback=array{author: string, author_classname: string, feedback: string, date: \OmegaUp\Timestamp, range_bytes_end: int|null, range_bytes_start: int|null, submission_feedback_id: int, feedback_thread?: list<SubmissionFeedbackThread>}
 * @psalm-type RunDetailsV2=array{admin: bool, cases: ProblemCasesContents, compile_error?: string, details?: array{compile_meta?: array<string, RunMetadata>, groups?: list<RunDetailsGroup>, judged_by: string, max_score?: float, memory?: float, score: float, time?: float, verdict: string, wall_time?: float}, feedback?: string, judged_by?: string, logs?: string, show_diff: string, source?: string, source_link?: bool, source_name?: string, source_url?: string, feedback: null|SubmissionFeedback}
 * @psalm-type RunWithDetails=array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, details: null|RunDetailsV2, execution: null|string, guid: string, language: string, memory: int, output: null|string, penalty: int, runtime: int, score: float, score_by_group?: array<string, float|null>, status: string, status_memory: null|string, status_runtime: null|string, submit_delay: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}
 * @psalm-type ProblemDetails=array{accepts_submissions: bool, accepted: int, admin?: bool, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, difficulty: float|null, email_clarifications: bool, input_limit: int, karel_problem: bool, languages: list<string>, letter?: string, limits: SettingLimits, nextExecutionTimestamp?: \OmegaUp\Timestamp, nextSubmissionTimestamp?: \OmegaUp\Timestamp, nominationStatus: NominationStatus, order: string, points: float, preferred_language?: string, problem_id: int, problemsetter?: ProblemsetterInfo, quality_seal: bool, runs?: list<RunWithDetails>, score: float, settings: ProblemSettingsDistrib, show_diff: string, solvers?: list<BestSolvers>, source?: string, statement: ProblemStatement, submissions: int, title: string, version: string, visibility: int, visits: int}
 * @psalm-type ContestPrintDetailsPayload=array{contestTitle: string, problems: array<int, null|ProblemDetails>}
 */

class Contest extends \OmegaUp\Controllers\Controller {
    const SHOW_INTRO = true;
    const MAX_CONTEST_LENGTH_SECONDS = 60 * 60 * 24 * 31; // 31 days
    const MAX_CONTEST_LENGTH_SYSADMIN_SECONDS = 60 * 60 * 24 * 60; // 60 days
    const CONTEST_LIST_PAGE_SIZE = 10;

    /**
     * Returns a list of contests
     *
     * @return array{number_of_results: int, results: list<ContestListItem>}
     *
     * @omegaup-request-param 'private'|'public'|'registration'|null $admission_mode
     * @omegaup-request-param 'all'|'recommended'|'signedup'|null $filter
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     * @omegaup-request-param int|null $participating
     * @omegaup-request-param string $query
     * @omegaup-request-param int|null $recommended
     * @omegaup-request-param null|string $sort_order
     * @omegaup-request-param string $tab_name
     */
    public static function apiList(\OmegaUp\Request $r): array {
        // Check who is visiting, but a not logged user can still view
        // the list of contests
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
            /** @var null $r->identity */
        }

        /** @var list<ContestListItem> */
        $contests = [];
        $recommended = $r->ensureOptionalInt(
            'recommended'
        ) ?? \OmegaUp\DAO\Enum\RecommendedStatus::ALL;
        $participating = $r->ensureOptionalInt(
            'participating'
        ) ?? \OmegaUp\DAO\Enum\ParticipatingStatus::NO;
        $filter = $r->ensureOptionalEnum(
            'filter',
            \OmegaUp\DAO\Enum\ContestFilterStatus::NAME_FOR_STATUS
        );
        $activeFilter = \OmegaUp\DAO\Enum\ContestFilterStatus::convertToInt(
            fieldName: 'filter',
            field: $filter,
            defaultValue: \OmegaUp\DAO\Enum\ContestFilterStatus::ALL
        );
        if ($activeFilter === \OmegaUp\DAO\Enum\ContestFilterStatus::ONLY_RECOMMENDED) {
            $recommended = \OmegaUp\DAO\Enum\RecommendedStatus::RECOMMENDED;
        } elseif ($activeFilter === \OmegaUp\DAO\Enum\ContestFilterStatus::SIGNED_UP) {
            $participating = \OmegaUp\DAO\Enum\ParticipatingStatus::YES;
        }
        $tabName = $r->ensureOptionalEnum(
            'tab_name',
            \OmegaUp\DAO\Enum\ContestTabStatus::NAME_FOR_STATUS
        );
        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt(
            key: 'page_size',
            lowerBound: 1,
            upperBound: 100
        ) ?? \OmegaUp\Controllers\Contest::CONTEST_LIST_PAGE_SIZE;
        $activeContests = \OmegaUp\DAO\Enum\ContestTabStatus::convertToInt(
            fieldName: 'tab_name',
            field: $tabName,
            defaultValue: \OmegaUp\DAO\Enum\ContestTabStatus::CURRENT
        );
        $recommended = \OmegaUp\DAO\Enum\RecommendedStatus::getIntValue(
            $recommended
        ) ?? \OmegaUp\DAO\Enum\RecommendedStatus::ALL;
        $participating = \OmegaUp\DAO\Enum\ParticipatingStatus::getIntValue(
            $participating
        );
        $admissionMode = $r->ensureOptionalEnum(
            'admission_mode',
            \OmegaUp\CourseParams::VALID_ADMISSION_MODES
        );

        // admission mode status in contest is public
        $public = (!is_null($admissionMode) && self::isPublic($admissionMode));

        if (is_null($participating)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'participating'
            );
        }
        $query = $r->ensureOptionalString(
            key: 'query',
            required: false,
            validator: fn(string $query) => \OmegaUp\Validators::stringOfLengthInRange(
                $query,
                0,
                250
            )
        );
        $order = $r->ensureOptionalEnum(
            'sort_order',
            \OmegaUp\DAO\Enum\ContestOrderStatus::NAME_FOR_STATUS,
            required: false
        );

        $orderBy = \OmegaUp\DAO\Enum\ContestOrderStatus::convertToInt(
            fieldName: 'order',
            field: $order,
            defaultValue: \OmegaUp\DAO\Enum\ContestOrderStatus::NONE
        );

        [
            'contests' => $contests,
            'count' => $count,
        ] = self::getContestList(
            $r->identity,
            $query,
            $page,
            $pageSize,
            $activeContests,
            $recommended,
            $public,
            $participating,
            $orderBy
        );

        return [
            'number_of_results' => $count,
            'results' => $contests,
        ];
    }

    /**
     * @return array{contests: list<ContestListItem>, count: int}
     */
    public static function getContestList(
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?string $query,
        int $page,
        int $pageSize,
        int $activeContests,
        int $recommended,
        bool $public = false,
        ?int $participating = null,
        int $orderBy = 0
    ) {
        $identityId = $identity?->identity_id ?? 0;
        $cacheKey = "01-{$identityId}-{$activeContests}-{$recommended}-{$participating}-{$page}-{$pageSize}";
        if (is_null($identity) || is_null($identity->identity_id)) {
            // Get all public contests
            $callback =
            /** @return array{contests: list<ContestListItem>, count: int} */
            fn() => \OmegaUp\DAO\Contests::getAllPublicContests(
                $page,
                $pageSize,
                $activeContests,
                $recommended,
                $query,
                $orderBy
            );
            if (empty($query)) {
                [
                    'contests' => $contests,
                    'count' => $count,
                ] = \OmegaUp\Cache::getFromCacheOrSet(
                    \OmegaUp\Cache::CONTESTS_LIST_PUBLIC,
                    $cacheKey,
                    $callback
                );
            } else {
                [
                    'contests' => $contests,
                    'count' => $count,
                ] = $callback();
            }
        } elseif ($participating === \OmegaUp\DAO\Enum\ParticipatingStatus::YES) {
            [
                'contests' => $contests,
                'count' => $count,
            ] = \OmegaUp\DAO\Contests::getContestsParticipating(
                $identity->identity_id,
                $page,
                $pageSize,
                $activeContests,
                $query,
                $orderBy
            );
        } elseif ($public) {
            [
                'contests' => $contests,
                'count' => $count,
            ] = \OmegaUp\DAO\Contests::getRecentPublicContests(
                $identity->identity_id,
                $page,
                $pageSize,
                $query,
                $orderBy
            );
        } elseif (\OmegaUp\Authorization::isSystemAdmin($identity)) {
            // Get all contests
            $callback =
            /** @return array{contests: list<ContestListItem>, count: int} */
            fn() => \OmegaUp\DAO\Contests::getAllContests(
                $page,
                $pageSize,
                $activeContests,
                $recommended,
                $query,
                $orderBy
            );
            if (empty($query)) {
                [
                    'contests' => $contests,
                    'count' => $count,
                ] = \OmegaUp\Cache::getFromCacheOrSet(
                    \OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN,
                    $cacheKey,
                    $callback
                );
            } else {
                [
                    'contests' => $contests,
                    'count' => $count,
                ] = $callback();
            }
        } else {
            // Get all public+private contests
            [
                'contests' => $contests,
                'count' => $count,
            ] = \OmegaUp\DAO\Contests::getAllContestsForIdentity(
                $identity->identity_id,
                $page,
                $pageSize,
                $activeContests,
                $recommended,
                $query,
                $orderBy
            );
        }
        $addedContests = [];
        foreach ($contests as $contestInfo) {
            $contestInfo['duration_minutes'] = (is_null(
                $contestInfo['window_length']
            ) ?
                $contestInfo['finish_time']->time - $contestInfo['start_time']->time : ($contestInfo['window_length'] * 60)
            );

            $addedContests[] = $contestInfo;
        }
        return [
            'contests' => $addedContests,
            'count' => $count,
        ];
    }

    /**
     * Returns a list of contests where current user has admin rights (or is
     * the director).
     *
     * @return array{contests: list<Contest>}
     *
     * @omegaup-request-param int|null $page
     * @omegaup-request-param int|null $page_size
     * @omegaup-request-param bool|null $show_archived
     */
    public static function apiAdminList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt('page_size') ?? 1000;
        $showArchived = $r->ensureOptionalBool('show_archived') ?? false;

        // Create array of relevant columns
        $contests = null;
        if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            $contests = \OmegaUp\DAO\Contests::getAllContestsWithScoreboard(
                $page,
                $pageSize,
                'contest_id',
                'DESC',
                $showArchived
            );
        } else {
            $contests = \OmegaUp\DAO\Contests::getAllContestsAdminedByIdentity(
                $r->identity->identity_id,
                $page,
                $pageSize
            );
        }

        return [
            'contests' => $contests,
        ];
    }

    /**
     * Callback to get contests list, depending on a given method
     *
     * @param \OmegaUp\Request $r
     * @param Closure(int, int, int, bool, null|string):array{contests: list<Contest>, count: int} $callbackUserFunction
     *
     * @return array{contests: list<Contest>, count: int}
     *
     * @omegaup-request-param int|null $page
     * @omegaup-request-param int|null $page_size
     * @omegaup-request-param null|string $query
     * @omegaup-request-param bool|null $show_archived
     */
    private static function getContestListInternal(
        \OmegaUp\Request $r,
        $callbackUserFunction
    ): array {
        $r->ensureIdentity();

        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt('page_size') ?? 1000;
        $query = $r->ensureOptionalString('query');
        $showArchived = $r->ensureOptionalBool('show_archived') ?? false;
        [
            'contests' => $contests,
            'count' => $count,
        ] = $callbackUserFunction(
            $r->identity->identity_id,
            $page,
            $pageSize,
            $showArchived,
            $query
        );

        // Expire contest-list cache
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_PUBLIC);
        \OmegaUp\Cache::invalidateAllKeys(
            \OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN
        );

        return [
            'contests' => $contests,
            'count' => $count,
        ];
    }

    /**
     * Returns a list of contests where current user is the director
     *
     * @return array{contests: list<Contest>, count: int}
     *
     * @omegaup-request-param int|null $page
     * @omegaup-request-param int|null $page_size
     * @omegaup-request-param null|string $query
     * @omegaup-request-param bool|null $show_archived
     */
    public static function apiMyList(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        return self::getContestListInternal(
            $r,
            fn(
                int $identityId,
                int $page,
                int $pageSize,
                bool $showArchived,
                ?string $query
            ) => \OmegaUp\DAO\Contests::getAllContestsOwnedByUser(
                $identityId,
                $page,
                $pageSize,
                $showArchived
            )
        );
    }

    /**
     * Returns a list of contests where current user is participating in
     *
     * @return array{contests: list<Contest>, count: int}
     *
     * @omegaup-request-param int|null $page
     * @omegaup-request-param int|null $page_size
     * @omegaup-request-param null|string $query
     * @omegaup-request-param bool|null $show_archived
     */
    public static function apiListParticipating(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        return self::getContestListInternal(
            $r,
            fn(
                int $identityId,
                int $page,
                int $pageSize,
                bool $showArchived,
                ?string $query
            ) => \OmegaUp\DAO\Contests::getContestsParticipating(
                $identityId,
                $page,
                $pageSize,
                \OmegaUp\DAO\Enum\ActiveStatus::ALL,
                $query
            )
        );
    }

    /**
     * Checks if user can access contests: If the contest is private then the user
     * must be added to the contest (an entry ProblemsetIdentities must exists) OR the user
     * should be a Contest Admin.
     *
     * Expects $contest to contain the contest to check against.
     *
     * In case of access check failed, an exception is thrown.
     *
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateAccessContest(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        if (self::canAccessContest($contest, $identity)) {
            return;
        }
        if ($contest->admission_mode === 'private') {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        throw new \OmegaUp\Exceptions\ForbiddenAccessException(
            'contestNotRegistered'
        );
    }

    /**
     * Checks if user can access contests: If the contest is private then the
     * user must be added to the contest (an entry ProblemsetIdentities must
     * exists) OR the user should be a Contest Admin.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function canAccessContest(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Identities $identity
    ): bool {
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        if ($contest->admission_mode === 'private') {
            if (
                \OmegaUp\DAO\ProblemsetIdentities::existsByPK(
                    $identity->identity_id,
                    $contest->problemset_id
                )
            ) {
                return true;
            }
            if (
                \OmegaUp\Authorization::canSubmitToProblemset(
                    $identity,
                    \OmegaUp\DAO\Problemsets::getByPK(
                        $contest->problemset_id
                    )
                )
            ) {
                return true;
            }
            return false;
        } elseif (
            $contest->admission_mode === 'registration' &&
            !\OmegaUp\Authorization::isContestAdmin($identity, $contest)
        ) {
            $req = \OmegaUp\DAO\ProblemsetIdentityRequest::getByPK(
                $identity->identity_id,
                $contest->problemset_id
            );
            if (is_null($req) || !$req->accepted) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate the basics of a contest request.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{contest: \OmegaUp\DAO\VO\Contests, problemset: \OmegaUp\DAO\VO\Problemsets}
     */
    private static function validateBasicDetails(string $contestAlias): array {
        // If the contest is private, verify that our user is invited
        $contestProblemset = \OmegaUp\DAO\Contests::getByAliasWithExtraInformation(
            $contestAlias
        );
        if (is_null($contestProblemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        return [
            'contest' => new \OmegaUp\DAO\VO\Contests(
                array_intersect_key(
                    $contestProblemset,
                    \OmegaUp\DAO\VO\Contests::FIELD_NAMES
                )
            ),
            'problemset' => new \OmegaUp\DAO\VO\Problemsets(
                array_intersect_key(
                    $contestProblemset,
                    \OmegaUp\DAO\VO\Problemsets::FIELD_NAMES
                )
            ),
        ];
    }

    /**
     * Validate a contest with contest alias
     *
     * @return \OmegaUp\DAO\VO\Contests $contest
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function validateContest(
        string $contestAlias
    ): \OmegaUp\DAO\VO\Contests {
        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        return $contest;
    }

    /**
     * Validate a contest with contest alias and add its director
     *
     * @return array{contest: \OmegaUp\DAO\VO\Contests, contestWithDirector: ContestPublicDetails}
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function validateContestWithDirector(string $contestAlias) {
        $contest = \OmegaUp\DAO\Contests::getByAliasWithDirector($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        return [
            'contest' => new \OmegaUp\DAO\VO\Contests(
                array_intersect_key(
                    $contest,
                    \OmegaUp\DAO\VO\Contests::FIELD_NAMES
                )
            ),
            'contestWithDirector' => $contest,
        ];
    }

    /**
     * Validate if a contestant has explicit access to a contest.
     */
    private static function isInvitedToContest(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Identities $identity
    ): bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isPublic($contest->admission_mode) ||
            \OmegaUp\DAO\ProblemsetIdentities::existsByPK(
                $identity->identity_id,
                $contest->problemset_id
            );
    }

    /**
     * @return array{clarifications: list<Clarification>, problems: list<NavbarProblemsetProblem>, submissionDeadline: \OmegaUp\Timestamp|null}
     */
    private static function getCommonDetails(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Problemsets $problemset,
        bool $contestAdmin,
        \OmegaUp\DAO\VO\Identities $identity,
        bool $isPracticeMode = false
    ): array {
        if (is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userNotExist'
            );
        }
        $contestDetails = self::getContestDetails(
            $contest,
            $contestAdmin,
            $identity,
            token: null,
            isPracticeMode: $isPracticeMode
        );

        if (is_null($problemset->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        [
            'runs' => $runs,
        ] = \OmegaUp\DAO\Runs::getAllRuns(
            $problemset->problemset_id,
            null,
            null,
            null,
            null,
            $identity->identity_id,
            null,
            null,
        );

        /** @var list<NavbarProblemsetProblem> */
        $problems = [];
        foreach ($contestDetails['problems'] as $problem) {
            $problemText = isset(
                $problem['letter']
            ) ? "{$problem['letter']}. {$problem['title']}" : $problem['title'];
            array_push(
                $problems,
                [
                    'alias' => strval($problem['alias']),
                    'text' => $problemText,
                    'acceptsSubmissions' => $problem['languages'] !== '',
                    'bestScore' => 0,
                    'maxScore' => floatval($problem['points']),
                    'hasRuns' => $problem['has_submissions'],
                    'myBestScore' => null,
                    'hasMyRuns' => false,
                ]
            );
        }

        $problemIndex = [];

        foreach ($problems as &$problem) {
            $problemIndex[$problem['alias']] = &$problem;
        }

        foreach ($runs as $run) {
            if (isset($problemIndex[$run['alias']])) {
                $alias = $run['alias'];
                if (isset($problemIndex[$alias]['myBestScore'])) {
                    $problemIndex[$alias]['myBestScore'] = max(
                        $problemIndex[$alias]['myBestScore'],
                        $run['contest_score']
                    );
                } else {
                    $problemIndex[$alias]['myBestScore'] = $run['contest_score'];
                }
                $problemIndex[$alias]['hasMyRuns'] = true;
            }
        }

        return [
            'clarifications' => \OmegaUp\DAO\Clarifications::getProblemsetClarifications(
                $contest,
                course: null,
                isAdmin: $contestAdmin,
                currentIdentity: $identity,
                offset: null,
                rowcount: 100,
            )['clarifications'],
            'problems' => $problems,
            'submissionDeadline' => $contestDetails['submission_deadline'] ?? $contest->finish_time,
        ];
    }

    /**
     * Get all the properties for TypeScript.
     *
     * @return array{entrypoint: string, templateProperties: array{hideFooterAndHeader: bool, payload: ContestPrintDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $alias
     * @omegaup-request-param null|string $lang
     */
    public static function getContestPrintDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();

        $contestAlias = $r->ensureString(
            'alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        [
            'contest' => $contest,
            'contest_admin' => $contestAdmin,
            'problemset' => $problemset,
        ] = self::validateDetails($contestAlias, $r->identity);

        if (!$contestAdmin) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $details = self::getContestDetails(
            $contest,
            $contestAdmin,
            $r->identity
        );

        // Bulk fetch all problems in a single query to avoid N+1
        $aliases = array_map(
            fn($problem) => $problem['alias'],
            $details['problems']
        );
        $problemsMap = \OmegaUp\DAO\Problems::getByAliases($aliases);

        $problems = [];
        foreach ($details['problems'] as $index => $problem) {
            $problemDetails = $problemsMap[$problem['alias']] ?? null;
            if (is_null($problemDetails)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }
            $problems[$index] = \OmegaUp\Controllers\Problem::getProblemDetails(
                $r->identity,
                $problemDetails,
                $problemset,
                \OmegaUp\Controllers\Identity::getPreferredLanguage(
                    $r->identity
                ),
                showSolvers: false,
                preventProblemsetOpen: false,
                contestAlias: $contestAlias,
            );
            $problems[$index]['letter'] = $problem['letter'] ?? '';
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'contestTitle' => $details['title'],
                    'problems' => $problems,
                ],
                'hideFooterAndHeader' => true,
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleContestPrint',
                    [
                        'contestTitle' => $details['title'],
                    ]
                ),
            ],
            'entrypoint' => 'contest_print',
        ];
    }

    /**
     * Get all the properties for TypeScript.
     *
     * @return array{entrypoint: string, templateProperties: array{fullWidth: bool, payload: ContestDetailsPayload, title: \OmegaUp\TranslationString}}|array{entrypoint: string, templateProperties: array{payload: ContestIntroPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param null|string $auth_token
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param bool|null $start_fresh
     */
    public static function getContestDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $startFresh = $r->ensureOptionalBool('start_fresh');
        [
            'contest' => $contest,
            'contestWithDirector' => $contestWithDirector,
        ] = self::validateContestWithDirector($contestAlias);
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            if ($contest->admission_mode === 'private') {
                throw $e;
            }
            $r->identity = null;
            // Request can proceed unauthenticated.
        }

        $result = [
            'templateProperties' => [
                'payload' => [
                    'contest' => self::getPublicDetails(
                        $contestWithDirector,
                        $r->identity
                    ),
                    'shouldShowModalToLoginWithRegisteredIdentity' => false,
                ],
                'title' => new \OmegaUp\TranslationString('enterContest'),
            ],
            'entrypoint' => 'contest_intro',
        ];

        if (
            $startFresh
            && !is_null($r->identity)
            && !self::canAccessContest($contest, $r->identity)
            && $contest->admission_mode === 'private'
        ) {
            $result['templateProperties']['payload']['shouldShowModalToLoginWithRegisteredIdentity'] = true;
            return $result;
        }

        $shouldShowIntro = \OmegaUp\Controllers\Contest::shouldShowIntro(
            $r->identity,
            $contest
        );

        // Half-authenticate, in case there is no session in place.
        \OmegaUp\Controllers\Session::getCurrentSession($r);

        if (!$shouldShowIntro) {
            [
                'contest' => $contest,
                'contest_admin' => $contestAdmin,
                'problemset' => $problemset,
            ] = self::validateDetails($contestAlias, $r->identity);
            if (is_null($contest->problemset_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'contestNotFound'
                );
            }
            $shouldShowFirstAssociatedIdentityRunWarning = (
                !is_null($r->identity) &&
                !is_null($r->user) &&
                !\OmegaUp\Controllers\User::isMainIdentity(
                    $r->user,
                    $r->identity
                ) &&
                \OmegaUp\DAO\Problemsets::shouldShowFirstAssociatedIdentityRunWarning(
                    $r->user
                )
            );
            if (is_null($r->identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'userNotExist'
                );
            }

            $result['templateProperties']['payload'] = array_merge(
                $result['templateProperties']['payload'],
                [
                    'shouldShowFirstAssociatedIdentityRunWarning' => $shouldShowFirstAssociatedIdentityRunWarning,
                    'scoreboard' => self::getScoreboard(
                        $contest,
                        $problemset,
                        $r->identity
                    ),
                    'scoreboardEvents' => self::getScoreboardEvents(
                        $contest,
                        $r->identity
                    ),
                ],
                self::getCommonDetails(
                    $contest,
                    $problemset,
                    $contestAdmin,
                    $r->identity
                ),
            );
            if ($contestAdmin) {
                // Get our runs
                [
                    'totalRuns' => $totalRuns,
                    'runs' => $runs,
                ] = self::getAllRuns(
                    $contest->problemset_id,
                    $contest->score_mode
                );

                $result['templateProperties']['payload']['adminPayload'] = [
                    'users' => \OmegaUp\DAO\ProblemsetIdentities::getWithExtraInformation(
                        intval($contest->problemset_id)
                    ),
                    'allRuns' => $runs,
                    'totalRuns' => $totalRuns,
                ];
            }

            $result['templateProperties']['fullWidth'] = true;
            $result['templateProperties']['title'] = new \OmegaUp\TranslationString(
                'omegaupTitleContest'
            );

            if (!empty($contest->rerun_id)) {
                $originalContest = \OmegaUp\DAO\Contests::getByPK(
                    $contest->rerun_id
                );
                if (
                    is_null(
                        $originalContest
                    ) || is_null(
                        $originalContest->problemset_id
                    )
                ) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'contestNotFound'
                    );
                }
                $originalProblemset = \OmegaUp\DAO\Problemsets::getByPK(
                    $contest->problemset_id
                );
                if (is_null($originalProblemset)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'contestNotFound'
                    );
                }
                $result['templateProperties']['payload']['original'] = [
                    'contest' => $originalContest,
                    'scoreboard' => self::getScoreboard(
                        $originalContest,
                        $originalProblemset,
                        $r->identity
                    ),
                    'scoreboardEvents' => self::getScoreboardEvents(
                        $originalContest,
                        $r->identity
                    ),
                ];
                $result['entrypoint'] = 'arena_contest_virtual';
                return $result;
            }

            $result['entrypoint'] = 'arena_contest_contestant';
            return $result;
        }

        if (is_null($r->identity)) {
            return $result;
        }

        [
            'needsBasicInformation' => $needsBasicInformation,
            'requestsUserInformation' => $requestsUserInformation,
        ] = \OmegaUp\DAO\Contests::getNeedsInformation($contest->problemset_id);

        $userBasicInformation = [
            'country' => $r->identity->country_id,
            'state' => $r->identity->state_id,
            'school' => $r->identity->current_identity_school_id,
        ];

        $needsBasicInformation = $needsBasicInformation && (
            is_null($userBasicInformation['country']) ||
            is_null($userBasicInformation['state']) ||
            is_null($userBasicInformation['school'])
        );

        $result['templateProperties']['payload']['requestsUserInformation'] = $requestsUserInformation;
        $result['templateProperties']['payload']['needsBasicInformation'] = $needsBasicInformation;
        $result['templateProperties']['payload']['userBasicInformation'] = $userBasicInformation;

        // Privacy Statement Information
        $privacyStatementMarkdown = \OmegaUp\PrivacyStatement::getForProblemset(
            $r->identity->language_id,
            'contest',
            $requestsUserInformation
        );
        if (!is_null($privacyStatementMarkdown)) {
            $statementType = "contest_{$requestsUserInformation}_consent";
            $result['templateProperties']['payload']['privacyStatement'] = [
                'markdown' => $privacyStatementMarkdown,
                'statementType' => $statementType
            ];
            $statement = \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement(
                $statementType
            );
            if (!is_null($statement)) {
                $result['templateProperties']['payload']['privacyStatement']['gitObjectId'] = $statement['git_object_id'];
            }
        }

        return $result;
    }

    /**
     * Get all the properties for TypeScript when user accesses a contest in
     * practice mode.
     *
     * @return array{entrypoint: string, templateProperties: array{fullWidth: bool, payload: ContestPracticeDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $contest_alias
     */
    public static function getContestPracticeDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        // Only logged users can access practice mode
        $r->ensureIdentity();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        [
            'contestWithDirector' => $contestWithDirector,
        ] = self::validateContestWithDirector($contestAlias);

        if ($contestWithDirector['finish_time']->time > \OmegaUp\Time::get()) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'originalContestHasNotEnded'
            );
        }

        [
            'contest' => $contest,
            'contest_admin' => $contestAdmin,
            'problemset' => $problemset,
        ] = self::validateDetails($contestAlias, $r->identity);
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        $commonDetails = self::getCommonDetails(
            $contest,
            $problemset,
            $contestAdmin,
            $r->identity,
            isPracticeMode: true
        );
        if (!$contestAdmin) {
            $commonDetails['users'] = [];
        }

        return [
            'templateProperties' => [
                'payload' => array_merge(
                    [
                        'shouldShowFirstAssociatedIdentityRunWarning' => (
                            !is_null($r->user) &&
                            !\OmegaUp\Controllers\User::isMainIdentity(
                                $r->user,
                                $r->identity
                            ) &&
                            \OmegaUp\DAO\Problemsets::shouldShowFirstAssociatedIdentityRunWarning(
                                $r->user
                            )
                        ),
                        'contest' => self::getPublicDetails(
                            $contestWithDirector,
                            $r->identity
                        ),
                        'contestAdmin' => $contestAdmin,
                    ],
                    $commonDetails,
                ),
                'fullWidth' => true,
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleContestPractice'
                ),
            ],
            'entrypoint' => 'arena_contest_practice',
        ];
    }

    /**
     * @return array{response: array<int, int>}
     *
     * @omegaup-request-param string $contest_ids
     */
    public static function apiGetNumberOfContestants(\OmegaUp\Request $r) {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
            $r->identity = null;
        }

        $contestIDsAsString = $r->ensureString('contest_ids');
        $contestIDStrings = explode(',', $contestIDsAsString);

        // Validate and convert all IDs upfront
        $validContestIds = [];
        foreach ($contestIDStrings as $contestIdString) {
            \OmegaUp\Validators::validateNumber($contestIdString, 'contest_id');
            $validContestIds[] = intval($contestIdString);
        }

        // Bulk fetch all contests in a single query to avoid N+1
        $contestsMap = \OmegaUp\DAO\Contests::getByPKs($validContestIds);

        $contestants = [];
        foreach ($validContestIds as $contestID) {
            try {
                $contest = $contestsMap[$contestID] ?? null;
                if (is_null($contest)) {
                    $contestants[$contestID] = 0;
                    continue;
                }

                // Only validate access when user is logged in and the admission
                // mode for the contest is not public
                if (
                    !is_null(
                        $r->identity
                    ) && $contest->admission_mode !== 'public'
                ) {
                    self::validateAccessContest($contest, $r->identity);
                }

                $callback =
                /** @return int */
                fn() => \OmegaUp\DAO\Contests::getNumberOfContestants(
                    $contestID
                );

                $contestants[$contestID] = \OmegaUp\Cache::getFromCacheOrSet(
                    \OmegaUp\Cache::CONTESTS_CONTESTANTS_LIST,
                    strval($contestID),
                    $callback
                );
            } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
                // For all the contests where user can not have access, we set
                // the number of contestants in 0
                $contestants[$contestID] = 0;
            }
        }

        return [
            'response' => $contestants,
        ];
    }

    /**
     * @return array{templateProperties: array{payload: ContestListv2Payload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param 'all'|'recommended'|'signedup'|null $filter
     * @omegaup-request-param int|null $page
     * @omegaup-request-param int|null $page_size
     * @omegaup-request-param int|null $participating
     * @omegaup-request-param null|string $query
     * @omegaup-request-param int|null $recommended
     * @omegaup-request-param null|string $sort_order
     * @omegaup-request-param string $tab_name
     */
    public static function getContestListDetailsv2ForTypeScript(
        \OmegaUp\Request $r
    ) {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
            $r->identity = null;
        }

        $recommended = $r->ensureOptionalInt(
            'recommended'
        ) ?? \OmegaUp\DAO\Enum\RecommendedStatus::ALL;
        $participating = $r->ensureOptionalInt(
            'participating'
        ) ?? \OmegaUp\DAO\Enum\ParticipatingStatus::NO;
        $filter = $r->ensureOptionalEnum(
            'filter',
            \OmegaUp\DAO\Enum\ContestFilterStatus::NAME_FOR_STATUS
        );
        $activeFilter = \OmegaUp\DAO\Enum\ContestFilterStatus::convertToInt(
            fieldName: 'filter',
            field: $filter,
            defaultValue: \OmegaUp\DAO\Enum\ContestFilterStatus::ALL
        );
        if ($activeFilter === \OmegaUp\DAO\Enum\ContestFilterStatus::ONLY_RECOMMENDED) {
            $recommended = \OmegaUp\DAO\Enum\RecommendedStatus::RECOMMENDED;
        } elseif ($activeFilter === \OmegaUp\DAO\Enum\ContestFilterStatus::SIGNED_UP) {
            $participating = \OmegaUp\DAO\Enum\ParticipatingStatus::YES;
        }
        $tabName = $r->ensureOptionalEnum(
            'tab_name',
            \OmegaUp\DAO\Enum\ContestTabStatus::NAME_FOR_STATUS
        ) ?? 'current';
        $activeContests = \OmegaUp\DAO\Enum\ContestTabStatus::convertToInt(
            fieldName: 'tab_name',
            field: $tabName,
            defaultValue: \OmegaUp\DAO\Enum\ContestTabStatus::CURRENT
        );
        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt(
            'page_size'
        ) ?? \OmegaUp\Controllers\Contest::CONTEST_LIST_PAGE_SIZE;
        $query = $r->ensureOptionalString(
            key: 'query',
            required: false,
            validator: fn(string $query) => \OmegaUp\Validators::stringOfLengthInRange(
                $query,
                0,
                250
            )
        );

        $order = $r->ensureOptionalEnum(
            'sort_order',
            \OmegaUp\DAO\Enum\ContestOrderStatus::NAME_FOR_STATUS,
            required: false
        );

        $orderBy = \OmegaUp\DAO\Enum\ContestOrderStatus::convertToInt(
            fieldName: 'order',
            field: $order,
            defaultValue: \OmegaUp\DAO\Enum\ContestOrderStatus::NONE
        );
        [
            'contests' => $contests,
            'count' => $countContests,
        ] = self::getContestList(
            $r->identity,
            $query,
            $page,
            $pageSize,
            $activeContests,
            $recommended,
            false,
            $participating,
            $orderBy
        );

        $contests = [
            $tabName => $contests,
        ];
        $countContests = [
            $tabName => $countContests,
        ];

        $contests = [
            'current' => $contests['current'] ?? [],
            'past' => $contests['past'] ?? [],
            'future' => $contests['future'] ?? []
        ];

        $countContests = [
            'current' => $countContests['current'] ?? 0,
            'past' => $countContests['past'] ?? 0,
            'future' => $countContests['future'] ?? 0
        ];

        return [
            'templateProperties' => [
                'payload' => [
                    'contests' => $contests,
                    'pageSize' => $pageSize,
                    'countContests' => $countContests,
                    'query' => $r->ensureOptionalString('query'),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleContestList'
                ),
            ],
            'entrypoint' => 'arena_contest_listv2',
        ];
    }

    /**
     * @return array{templateProperties: array{payload: ContestListMinePayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int|null $page
     * @omegaup-request-param int|null $page_size
     * @omegaup-request-param null|string $query
     * @omegaup-request-param bool|null $show_archived
     */
    public static function getContestListMineForTypeScript(
        \OmegaUp\Request $r
    ): array {
        // Authenticate user
        $r->ensureMainUserIdentity();

        // If the user have private material (contests/problems), an alert is issued
        // suggesting to contribute to the community by releasing the material to
        // the public. This flag ensures that this alert is shown only once per
        // session, the first time the user visits the "My contests" page.
        $scopedSession = \OmegaUp\Controllers\Session::getSessionManagerInstance()->sessionStart();
        $privateContestsAlert = (
            !isset($_SESSION['private_contests_alert']) &&
            \OmegaUp\DAO\Contests::getPrivateContestsCount($r->user) > 0
        );
        if ($privateContestsAlert) {
            $_SESSION['private_contests_alert'] = true;
        }
        unset($scopedSession);

        $contestsList = self::getContestListInternal(
            $r,
            fn(
                int $identityId,
                int $page,
                int $pageSize,
                bool $showArchived,
                ?string $query
            ) => \OmegaUp\DAO\Contests::getAllContestsOwnedByUser(
                $identityId,
                $page,
                $pageSize,
                $showArchived
            )
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'contests' => $contestsList['contests'],
                    'privateContestsAlert' => $privateContestsAlert,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleMyContests'
                ),
            ],
            'entrypoint' => 'contest_mine',
        ];
    }

    /**
     * @return array{templateProperties: array{payload: ContestNewPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getContestNewForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        if (\OmegaUp\Authorization::isUnderThirteenUser($r->user)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'under13UserException'
            );
        }
        return [
            'templateProperties' => [
                'payload' => [
                    'languages' => \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES(),
                    'hasVisitedSection' => \OmegaUp\UITools::hasVisitedSection(
                        'has-visited-create-contest'
                    ),
                    'canSetRecommended' => \OmegaUp\Authorization::isSupportTeamMember(
                        $r->identity
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleContestNew'
                )
            ],
            'entrypoint' => 'contest_new',
        ];
    }

    /**
     * Returns the details for the edition of a contest
     *
     * @return array{templateProperties: array{payload: ContestEditPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $contest_alias
     */
    public static function getContestEditForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        [
            'contest' => $contest,
            'contest_admin' => $contestAdmin,
        ] = self::validateDetails($contestAlias, $r->identity);
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'contestNotFound'
            );
        }
        if (!$contestAdmin) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        $originalContest = null;
        if (!is_null($contest->rerun_id)) {
            $originalContest = \OmegaUp\DAO\Contests::getByPK(
                $contest->rerun_id
            );
        }

        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $contest->problemset_id
        );
        if (is_null($problemset) || is_null($problemset->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        $problems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $problemset->problemset_id,
            needSubmissions: true
        );

        // Requests
        $resultAdmins = \OmegaUp\DAO\ProblemsetIdentityRequest::getFirstAdminForProblemsetRequest(
            $contest->problemset_id
        );
        $resultRequests = \OmegaUp\DAO\ProblemsetIdentityRequest::getRequestsForProblemset(
            $contest->problemset_id
        );

        $requestsAdmins = [];
        foreach ($resultAdmins as $result) {
            $requestsAdmins[$result['identity_id']] = [
                'username' => $result['username']
            ];
        }

        $usersRequests = array_map(function ($request) use ($requestsAdmins) {
            if (isset($requestsAdmins[$request['identity_id']])) {
                $request['admin'] = $requestsAdmins[$request['identity_id']];
            }
            unset($request['identity_id']);
            return $request;
        }, $resultRequests);

        return [
            'templateProperties' => [
                'payload' => [
                    'details' => self::getContestDetailsForAdmin(
                        $contest,
                        $r->identity,
                        $problemset
                    ),
                    'problems' => self::addVersionsToProblems(
                        $problems,
                        $r->identity,
                        $problemset->problemset_id
                    ),
                    'users' => \OmegaUp\DAO\ProblemsetIdentities::getWithExtraInformation(
                        intval($contest->problemset_id)
                    ),
                    'groups' => \OmegaUp\DAO\GroupRoles::getContestantGroups(
                        intval($contest->problemset_id)
                    ),
                    'teams_group' => \OmegaUp\DAO\TeamsGroupRoles::getTeamsGroup(
                        intval($contest->problemset_id)
                    ),
                    'requests' => $usersRequests,
                    'admins' => \OmegaUp\DAO\UserRoles::getContestAdmins(
                        $contest
                    ),
                    'group_admins' => \OmegaUp\DAO\GroupRoles::getContestAdmins(
                        $contest
                    ),
                    'original_contest_admission_mode' => $originalContest?->admission_mode,
                    'certificatesDetails' => [
                        'isCertificateGenerator' => \OmegaUp\Authorization::isCertificateGenerator(
                            $r->identity
                        ),
                        'certificatesStatus' => $contest->certificates_status,
                        'certificateCutoff' => $contest->certificate_cutoff,
                    ],
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleContestEdit'
                ),
            ],
            'entrypoint' => 'contest_edit',
        ];
    }

    /**
     * Adding the version log to every problem in a contest
     * @param list<ProblemsetProblem> $problems
     * @return list<ProblemsetProblemWithVersions> $problems
     */
    private static function addVersionsToProblems(
        array $problems,
        \OmegaUp\DAO\VO\Identities $identity,
        int $problemsetId
    ): array {
        $problemsWithVersions = [];
        foreach ($problems as $problem) {
            $problem['versions'] = \OmegaUp\Controllers\Problem::getVersions(
                new \OmegaUp\DAO\VO\Problems(
                    array_intersect_key(
                        $problem,
                        \OmegaUp\DAO\VO\Problems::FIELD_NAMES
                    )
                ),
                $identity,
                $problemsetId
            );
            $problemsWithVersions[] = $problem;
        }
        return $problemsWithVersions;
    }

    /**
     * Show the contest intro unless you are admin, or you already started this
     * contest.
     */
    public static function shouldShowIntro(
        ?\OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Contests $contest
    ): bool {
        try {
            if (is_null($identity)) {
                // No session, show the intro (if public), so that they can login.
                return self::isPublic($contest->admission_mode);
            }
            self::validateAccessContest($contest, $identity);
            $contestAdmin = \OmegaUp\Authorization::isContestAdmin(
                $identity,
                $contest
            );
            if ($contestAdmin) {
                return !\OmegaUp\Controllers\Contest::SHOW_INTRO;
            }
            if (
                !\OmegaUp\DAO\Contests::hasStarted($contest) &&
                self::isInvitedToContest($contest, $identity)
            ) {
                return \OmegaUp\Controllers\Contest::SHOW_INTRO;
            }
        } catch (\Exception $e) {
            // Could not access contest. Private contests must not be leaked, so
            // unless they were manually added beforehand, show them a 404 error.
            if (
                is_null($identity) ||
                !self::isInvitedToContest($contest, $identity)
            ) {
                throw $e;
            }
            self::$log->error('Exception while trying to verify access: ' . $e);
            return \OmegaUp\Controllers\Contest::SHOW_INTRO;
        }

        // You already started the contest.
        $contestOpened = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        if (!is_null($contestOpened) && !is_null($contestOpened->access_time)) {
            self::$log->debug(
                'No intro because you already started the contest'
            );
            return !\OmegaUp\Controllers\Contest::SHOW_INTRO;
        }
        return \OmegaUp\Controllers\Contest::SHOW_INTRO;
    }

    /**
     * Validate request of a details contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\PreconditionFailedException
     * @throws \OmegaUp\Exceptions\UnauthorizedException
     *
     * @return array{contest: \OmegaUp\DAO\VO\Contests, contest_admin: bool, contest_alias: string, problemset: \OmegaUp\DAO\VO\Problemsets}
     */
    public static function validateDetails(
        string $contestAlias,
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?string $token = null
    ): array {
        [
            'contest' => $contest,
            'problemset' => $problemset
        ] = self::validateBasicDetails($contestAlias);

        $contestAdmin = false;

        // If the contest has not started, user should not see it, unless it is
        // admin or has a token.
        if (is_null($token)) {
            // Crack the request to get the current user
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\UnauthorizedException();
            }
            self::validateAccessContest($contest, $identity);

            $contestAdmin = \OmegaUp\Authorization::isContestAdmin(
                $identity,
                $contest
            );
            if (
                !\OmegaUp\DAO\Contests::hasStarted($contest) &&
                !$contestAdmin
            ) {
                $exception = new \OmegaUp\Exceptions\PreconditionFailedException(
                    'contestNotStarted'
                );
                $exception->addCustomMessageToArray(
                    'start_time',
                    date('c', $contest->start_time->time)
                );

                throw $exception;
            }
        } else {
            if ($token === $problemset->scoreboard_url_admin) {
                $contestAdmin = true;
                /** @var string */
                $contestAlias = $contest->alias;
            } elseif ($token !== $problemset->scoreboard_url) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'invalidScoreboardUrl'
                );
            }
        }
        return [
            'contest' => $contest,
            'contest_admin' => $contestAdmin,
            'contest_alias' => $contestAlias,
            'problemset' => $problemset,
        ];
    }

    /**
     * @return ContestPublicDetails
     *
     * @omegaup-request-param string $contest_alias
     */
    public static function apiPublicDetails(\OmegaUp\Request $r): array {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
            $r->identity = null;
        }
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        [
            'contestWithDirector' => $contestWithDirector,
        ] = self::validateContestWithDirector($contestAlias);
        \OmegaUp\Controllers\Contest::validateContest($contestAlias);

        return self::getPublicDetails($contestWithDirector, $r->identity);
    }

    /**
     * @param ContestPublicDetails $contest
     * @return ContestPublicDetails
     */
    private static function getPublicDetails(
        $contest,
        ?\OmegaUp\DAO\VO\Identities $identity
    ): array {
        // Whether the contest is private, verify that our user is invited
        if (
            !is_null($identity) &&
            $contest['admission_mode'] === 'registration'
        ) {
            $registration = \OmegaUp\DAO\ProblemsetIdentityRequest::getByPK(
                $identity->identity_id,
                $contest['problemset_id']
            );

            $contest['user_registration_requested'] = !is_null($registration);

            if (is_null($registration)) {
                $contest['user_registration_answered'] = false;
            } else {
                $contest['user_registration_answered'] = !is_null(
                    $registration->accepted
                );
                $contest['user_registration_accepted'] = $registration->accepted;
                $contest['extra_note'] = $registration->extra_note;
            }
        }

        return $contest;
    }

    /**
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     */
    public static function apiRegisterForContest(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contest = self::validateContest($contestAlias);
        $admins = \OmegaUp\DAO\UserRoles::getContestAdmins($contest);
        $notificationContents = [
            'type' => \OmegaUp\DAO\Notifications::CONTEST_REGISTRATION_REQUEST,
            'body' => [
                'localizationString' => new \OmegaUp\TranslationString(
                    'notificationContestRegisterRequest'
                ),
                'localizationParams' => [
                    'username' => $r->identity->username,
                    'contestTitle' => $contest->title,
                ],
                'url' => "/contest/{$contest->alias}/edit/#contestants",
                'iconUrl' => '/media/info.png',
            ],
        ];

        try {
            \OmegaUp\DAO\DAO::transBegin();

            \OmegaUp\DAO\ProblemsetIdentityRequest::create(new \OmegaUp\DAO\VO\ProblemsetIdentityRequest([
                'identity_id' => $r->identity->identity_id,
                'problemset_id' => $contest->problemset_id,
                'request_time' => \OmegaUp\Time::get(),
            ]));

            $notifications = array_map(
                fn($admin) => new \OmegaUp\DAO\VO\Notifications([
                    'user_id' => $admin['user_id'],
                    'contents' => json_encode($notificationContents),
                ]),
                $admins
            );
            \OmegaUp\DAO\Notifications::createBulk($notifications);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return ['status' => 'ok'];
    }

    /**
     * Joins a contest - explicitly adds a identity to a contest.
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $privacy_git_object_id
     * @omegaup-request-param bool|null $share_user_information
     * @omegaup-request-param string $statement_type
     * @omegaup-request-param null|string $token
     */
    public static function apiOpen(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $token = $r->ensureOptionalString('token');

        $response = self::validateDetails($contestAlias, $r->identity, $token);
        if (is_null($response['contest']->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        [
            'needsBasicInformation' => $needsInformation,
            'requestsUserInformation' => $requestsUserInformation
        ] = \OmegaUp\DAO\Contests::getNeedsInformation(
            $response['contest']->problemset_id
        );

        if (
            $needsInformation
            && (is_null($r->identity->country_id)
                || is_null($r->identity->state_id)
                || is_null($r->identity->current_identity_school_id))
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'contestBasicInformationNeeded'
            );
        }

        \OmegaUp\DAO\DAO::transBegin();
        try {
            \OmegaUp\DAO\ProblemsetIdentities::checkAndSaveFirstTimeAccess(
                $r->identity,
                $response['contest'],
                grantAccess: true,
                shareUserInformation: $r->ensureOptionalBool(
                    'share_user_information'
                ) ?? false
            );

            // Insert into PrivacyStatement_Consent_Log whether request
            // user info is optional or required
            if ($requestsUserInformation !== 'no') {
                \OmegaUp\Validators::validateStringNonEmpty(
                    $r['privacy_git_object_id'],
                    'privacy_git_object_id'
                );
                \OmegaUp\Validators::validateStringNonEmpty(
                    $r['statement_type'],
                    'statement_type'
                );
                $privacyStatementId = \OmegaUp\DAO\PrivacyStatements::getId(
                    $r['privacy_git_object_id'],
                    $r['statement_type']
                );

                if (is_null($privacyStatementId)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'privacyStatementNotFound'
                    );
                }
                $privacyStatementConsentId = \OmegaUp\DAO\PrivacyStatementConsentLog::getId(
                    $r->identity->identity_id,
                    $privacyStatementId
                );
                if (is_null($privacyStatementConsentId)) {
                    $privacyStatementConsentId = \OmegaUp\DAO\PrivacyStatementConsentLog::saveLog(
                        $r->identity->identity_id,
                        $privacyStatementId
                    );
                }

                \OmegaUp\DAO\ProblemsetIdentities::updatePrivacyStatementConsent(new \OmegaUp\DAO\VO\ProblemsetIdentities([
                    'identity_id' => $r->identity->identity_id,
                    'problemset_id' => $response['contest']->problemset_id,
                    'privacystatement_consent_id' => $privacyStatementConsentId,
                ]));
            }

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        self::$log->info(
            "User '{$r->identity->username}' joined contest '{$response['contest']->alias}'"
        );

        // Invalidate user compare data cache since contest count changed
        \OmegaUp\Controllers\User::invalidateUserCompareDataCache(
            strval($r->identity->username)
        );

        return ['status' => 'ok'];
    }

    /**
     * Returns details of a Contest. This is shared between apiDetails and
     * apiAdminDetails.
     *
     * @return ContestDetails
     */
    private static function getCachedDetails(
        \OmegaUp\DAO\VO\Contests $contest
    ) {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::CONTEST_INFO,
            strval($contest->alias),
            /** @return ContestDetails */
            function () use ($contest) {
                // Initialize response to be the contest information
                /** @var array{admission_mode: string, alias: string, archived: bool, contest_for_teams: bool, description: string, feedback: string, finish_time: \OmegaUp\Timestamp, has_submissions: bool, languages: string, score_mode: string, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, recommended: bool, rerun_id: int|null, scoreboard: int, scoreboard_url: string, scoreboard_url_admin: string, default_show_all_contestants_in_scoreboard: bool, show_scoreboard_after: bool, start_time: \OmegaUp\Timestamp, submissions_gap: int, title: string, window_length: int|null} */
                $result = $contest->asFilteredArray([
                    'admission_mode',
                    'alias',
                    'archived',
                    'contest_for_teams',
                    'description',
                    'feedback',
                    'finish_time',
                    'has_submissions',
                    'languages',
                    'score_mode',
                    'penalty',
                    'penalty_calc_policy',
                    'penalty_type',
                    'points_decay_factor',
                    'problemset_id',
                    'recommended',
                    'rerun_id',
                    'scoreboard',
                    'scoreboard_url',
                    'scoreboard_url_admin',
                    'default_show_all_contestants_in_scoreboard',
                    'show_scoreboard_after',
                    'start_time',
                    'submissions_gap',
                    'title',
                    'window_length',
                    'check_plagiarism',
                ]);

                $result['original_contest_alias'] = null;
                $result['original_problemset_id'] = null;
                if (!is_null($result['rerun_id'])) {
                    $originalContest = \OmegaUp\DAO\Contests::getByPK(
                        $result['rerun_id']
                    );
                    if (!is_null($originalContest)) {
                        $result['original_contest_alias'] = $originalContest->alias;
                        $result['original_problemset_id'] = $originalContest->problemset_id;
                    }
                } else {
                    unset($result['rerun_id']);
                }

                if (
                    is_null($contest->acl_id) ||
                    is_null($contest->problemset_id)
                ) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'contestNotFound'
                    );
                }
                $director = \OmegaUp\DAO\UserRoles::getOwner(
                    $contest->acl_id
                );
                if (is_null($director)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'userNotExist'
                    );
                }
                $result['director'] = $director;

                $problemsInContest = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
                    $contest->problemset_id,
                    needSubmissions: true
                );

                // Add info of each problem to the contest
                $problemsResponseArray = [];

                $letter = 0;
                $hasSubmissions = false;

                foreach ($problemsInContest as $problem) {
                    // Add the 'points' value that is stored in the ContestProblem relationship
                    $problem['letter'] = \OmegaUp\Controllers\Contest::columnName(
                        $letter++
                    );
                    if (!empty($result['languages'])) {
                        $problem['languages'] = join(',', array_intersect(
                            explode(',', $result['languages']),
                            explode(',', $problem['languages'])
                        ));
                    }
                    if ($problem['has_submissions']) {
                        $hasSubmissions = true;
                    }

                    // Save our array into the response
                    $problemsResponseArray[] = $problem;
                }

                // Add problems to response
                $result['problems'] = $problemsResponseArray;
                $result['languages'] = explode(',', $result['languages']);
                [
                    'needsBasicInformation' => $needsBasicInformation,
                    'requestsUserInformation' => $requestsUserInformation,
                ] = \OmegaUp\DAO\Contests::getNeedsInformation(
                    $contest->problemset_id
                );
                $result['needs_basic_information'] = $needsBasicInformation;
                $result['requests_user_information'] = $requestsUserInformation;
                $result['show_penalty'] = (
                    $result['penalty'] !== 0 ||
                    $result['penalty_type'] !== 'none'
                );
                $result['admin'] = false;
                $result['opened'] = false;
                $result['has_submissions'] = $hasSubmissions;
                return $result;
            },
            APC_USER_CACHE_CONTEST_INFO_TIMEOUT
        );
    }

    /**
     * Returns details of a Contest. Requesting the details of a contest will
     * not start the current user into that contest. In order to participate
     * in the contest, \OmegaUp\Controllers\Contest::apiOpen() must be used.
     *
     * @return ContestDetails
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $token
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $r->identity = null;
        }
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $token = $r->ensureOptionalString('token');
        [
            'contest' => $contest,
            'contest_admin' => $contestAdmin,
        ] = self::validateDetails($contestAlias, $r->identity, $token);

        return self::getContestDetails(
            $contest,
            $contestAdmin,
            $r->identity,
            $token
        );
    }

    /**
     * @return ContestDetails
     */
    private static function getContestDetails(
        \OmegaUp\DAO\VO\Contests $contest,
        bool $contestAdmin,
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?string $token = null,
        bool $isPracticeMode = false
    ) {
        $result = self::getCachedDetails($contest);
        $result['opened'] = true;
        unset($result['scoreboard_url']);
        unset($result['scoreboard_url_admin']);
        unset($result['rerun_id']);
        if (!is_null($token)) {
            $result['admin'] = $contestAdmin;
            return $result;
        }
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\UnauthorizedException();
        }

        $result['admin'] = \OmegaUp\Authorization::isContestAdmin(
            $identity,
            $contest
        );

        // Log the operation.
        \OmegaUp\DAO\ProblemsetAccessLog::create(new \OmegaUp\DAO\VO\ProblemsetAccessLog([
            'identity_id' => $identity->identity_id,
            'problemset_id' => $contest->problemset_id,
            'ip' => ip2long(
                \OmegaUp\Request::getServerVar('REMOTE_ADDR') ?? ''
            ),
        ]));

        // When user is admin, support team member, or user joins contest in practice mode, saving
        // first access time is not necessary
        if (
            $isPracticeMode || $contestAdmin || \OmegaUp\Authorization::isSupportTeamMember(
                $identity
            )
        ) {
            return $result;
        }

        // Adding timer info separately as it depends on the current user and we
        // don't want this to get generally cached for everybody
        // Save the time of the first access
        $problemsetIdentity = \OmegaUp\DAO\ProblemsetIdentities::checkAndSaveFirstTimeAccess(
            $identity,
            $contest
        );
        $problemsetIdentity->access_time = $problemsetIdentity->access_time;

        // Add time left to response
        if (is_null($contest->window_length)) {
            $result['submission_deadline'] = $contest->finish_time;
        } elseif (!is_null($problemsetIdentity->access_time)) {
            $endTime = (
                !$problemsetIdentity->end_time ?
                $problemsetIdentity->access_time->time + $contest->window_length * 60 :
                $problemsetIdentity->end_time->time
            );
            $result['submission_deadline'] = new \OmegaUp\Timestamp(
                min($contest->finish_time->time, $endTime)
            );
        } else {
            $result['submission_deadline'] = $contest->finish_time;
        }

        return $result;
    }

    /**
     * Returns details of a Contest, for administrators. This differs from
     * common details in the sense that it does not attempt to calculate the
     * remaining time from the contest, or register the opened time.
     *
     * @return ContestAdminDetails
     */
    public static function getContestDetailsForAdmin(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Identities $adminIdentity,
        \OmegaUp\DAO\VO\Problemsets $problemset
    ): array {
        if (is_null($contest->alias) || is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        if (
            !\OmegaUp\Authorization::isContestAdmin(
                $adminIdentity,
                $contest
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $result = self::getCachedDetails($contest);
        $result['opened'] = \OmegaUp\DAO\ProblemsetIdentities::checkProblemsetOpened(
            intval($adminIdentity->identity_id),
            $contest->problemset_id
        );
        $result['available_languages'] = \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES();
        $result['admin'] = true;
        $result['scoreboard_url'] = $problemset->scoreboard_url;
        $result['scoreboard_url_admin'] = $problemset->scoreboard_url_admin;
        $result['recommended'] = $contest->recommended;
        $result['canSetRecommended'] = \OmegaUp\Authorization::isSupportTeamMember(
            $adminIdentity
        );
        return $result;
    }

    /**
     * Returns details of a Contest, for administrators. This differs from
     * apiDetails in the sense that it does not attempt to calculate the
     * remaining time from the contest, or register the opened time.
     *
     * @return ContestAdminDetails
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $token
     */
    public static function apiAdminDetails(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $token = $r->ensureOptionalString('token');
        [
            'contest' => $contest,
            'problemset' => $problemset,
        ] = self::validateDetails($contestAlias, $r->identity, $token);

        return self::getContestDetailsForAdmin(
            $contest,
            $r->identity,
            $problemset
        );
    }

    /**
     * Returns a report with all user activity for a contest.
     *
     * @return array{events: list<ActivityEvent>, pagerItems: list<PageItem>}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param int|null $length
     * @omegaup-request-param int|null $page
     * @omegaup-request-param null|string $token
     */
    public static function apiActivityReport(\OmegaUp\Request $r): array {
        try {
            $r->ensureidentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $r->identity = null;
        }

        $page = $r->ensureOptionalInt('page') ?? 1;
        $length = $r->ensureOptionalInt('length') ?? 100;
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $token = $r->ensureOptionalString('token');
        $response = self::validateDetails($contestAlias, $r->identity, $token);

        if (!$response['contest_admin']) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        if (is_null($response['contest']->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        $report = \OmegaUp\DAO\Contests::getActivityReport(
            $response['contest'],
            $page,
            $length
        );

        return [
            'events' => \OmegaUp\ActivityReport::getActivityReport(
                $report['activity']
            ),
            'pagerItems' => \OmegaUp\Pager::paginateWithUrl(
                $report['totalRows'],
                $length,
                $page,
                "/contest/{$contestAlias}/activity/",
                adjacent: 5,
                params: []
            ),
        ];
    }

    /**
     * @return array{templateProperties: array{payload: ActivityFeedPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $contest
     * @omegaup-request-param int|null $length
     * @omegaup-request-param int|null $page
     */
    public static function getActivityFeedDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        $page = $r->ensureOptionalInt('page') ?? 1;
        $length = $r->ensureOptionalInt('length') ?? 100;

        $alias = $r->ensureString(
            'contest',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        ['contest' => $contest] = self::validateBasicDetails($alias);

        if (is_null($contest->contest_id) || is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        if (!\OmegaUp\Authorization::isContestAdmin($r->identity, $contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        $report = \OmegaUp\DAO\Contests::getActivityReport(
            $contest,
            $page,
            $length
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'alias' => $alias,
                    'events' => \OmegaUp\ActivityReport::getActivityReport(
                        $report['activity']
                    ),
                    'type' => 'contest',
                    'pagerItems' => \OmegaUp\Pager::paginateWithUrl(
                        $report['totalRows'],
                        $length,
                        $page,
                        "/contest/{$alias}/activity/",
                        adjacent: 5,
                        params: []
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'activityReport'
                ),
            ],
            'entrypoint' => 'activity_feed',
        ];
    }

    /**
     * Returns a "column name" for the $idx (think Excel column names).
     */
    public static function columnName(int $idx): string {
        $name = chr(ord('A') + $idx % 26);
        while ($idx >= 26) {
            $idx /= 26;
            $idx--;
            $name = chr(ord('A') + $idx % 26) . $name;
        }
        return $name;
    }

    /**
     * Clone a contest
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{alias: string}
     *
     * @omegaup-request-param null|string $alias
     * @omegaup-request-param null|string $auth_token
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $description
     * @omegaup-request-param int $start_time
     * @omegaup-request-param string $title
     */
    public static function apiClone(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        // Authenticate user
        $r->ensureMainUserIdentityIsOver13();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['auth_token'],
            'auth_token'
        );
        $originalContest = self::validateContestAdmin(
            $contestAlias,
            $r->identity
        );
        if (is_null($originalContest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        // Validates form
        $alias = $r->ensureString(
            'alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['title'], 'title');
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['description'],
            'description'
        );
        $startTime = $r->ensureTimestamp('start_time');

        $length = (
            $originalContest->finish_time->time -
            $originalContest->start_time->time
        );

        $problemset = new \OmegaUp\DAO\VO\Problemsets([
            'needs_basic_information' => false,
            'requests_user_information' => 'no',
        ]);

        $contest = new \OmegaUp\DAO\VO\Contests([
            'title' => $r['title'],
            'description' => $r['description'],
            'alias' => $alias,
            'start_time' => $startTime->time,
            'finish_time' => $startTime->time + $length,
            'scoreboard' => $originalContest->scoreboard,
            'points_decay_factor' => $originalContest->points_decay_factor,
            'submissions_gap' => $originalContest->submissions_gap,
            'penalty_calc_policy' => $originalContest->penalty_calc_policy,
            'rerun_id' => $originalContest->rerun_id,
            'feedback' => $originalContest->feedback,
            'penalty_type' => $originalContest->penalty_type,
            'admission_mode' => 'private', // Cloned contests start in private
            // admission_mode
            'check_plagiarism' => $originalContest->check_plagiarism,
            'languages' => $originalContest->languages, // Cloned contests start with the same languages
        ]);

        \OmegaUp\DAO\DAO::transBegin();
        try {
            // Create the contest
            self::createContest($problemset, $contest, $r->user->user_id);
            if (is_null($contest->problemset_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'contestNotFound'
                );
            }

            $problemsetProblems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
                $originalContest->problemset_id,
                needSubmissions: false
            );
            foreach ($problemsetProblems as $problemsetProblem) {
                $problem = new \OmegaUp\DAO\VO\Problems([
                    'problem_id' => $problemsetProblem['problem_id'],
                    'alias' => $problemsetProblem['alias'],
                    'visibility' => $problemsetProblem['visibility'],
                ]);
                \OmegaUp\Controllers\Problemset::addProblem(
                    $contest->problemset_id,
                    $problem,
                    $problemsetProblem['commit'],
                    $problemsetProblem['version'],
                    $r->identity,
                    $problemsetProblem['points'],
                    $problemsetProblem['order'] ?: 1
                );
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'alias' => $alias,
        ];
    }

    /**
     * @return array{alias: string}
     *
     * @omegaup-request-param string $alias
     * @omegaup-request-param int $start_time
     */
    public static function apiCreateVirtual(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        // Authenticate user
        $r->ensureMainUserIdentity();

        $contestAlias = $r->ensureString(
            'alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $originalContest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($originalContest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        if ($originalContest->finish_time->time > \OmegaUp\Time::get()) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'originalContestHasNotEnded'
            );
        }

        $virtualContestAlias = \OmegaUp\DAO\Contests::generateAlias(
            $originalContest
        );

        $contestLength = (
            $originalContest->finish_time->time -
            $originalContest->start_time->time
        );

        $r->ensureOptionalInt('start_time');
        $startTime = (
            !is_null($r['start_time']) ?
            intval($r['start_time']) :
            \OmegaUp\Time::get()
        );

        // Initialize contest
        $contest = new \OmegaUp\DAO\VO\Contests([
            'title' => $originalContest->title,
            'description' => $originalContest->description,
            'window_length' => $originalContest->window_length,
            'start_time' => $startTime,
            'finish_time' => $startTime + $contestLength,
            'scoreboard' => 100, // Always show scoreboard in virtual contest
            'alias' => $virtualContestAlias,
            'points_decay_factor' => $originalContest->points_decay_factor,
            'submissions_gap' => $originalContest->submissions_gap,
            'score_mode' => $originalContest->score_mode,
            'feedback' => $originalContest->feedback,
            'penalty' => $originalContest->penalty,
            'penalty_type' => $originalContest->penalty_type,
            'penalty_calc_policy' => $originalContest->penalty_calc_policy,
            'show_scoreboard_after' => true,
            'languages' => $originalContest->languages,
            'rerun_id' => $originalContest->contest_id,
            'check_plagiarism' => $originalContest->check_plagiarism,
        ]);

        $problemset = new \OmegaUp\DAO\VO\Problemsets([
            'needs_basic_information' => false,
            'requests_user_information' => 'no',
            'access_mode' => 'private', // Virtual contest must be private
        ]);

        self::createContest(
            $problemset,
            $contest,
            $r->user->user_id,
            $originalContest->problemset_id
        );

        return [
            'alias' => strval($contest->alias),
        ];
    }

    /**
     * It retrieves a Problemset and a Contest objects to store them in the
     * database
     */
    private static function createContest(
        \OmegaUp\DAO\VO\Problemsets $problemset,
        \OmegaUp\DAO\VO\Contests $contest,
        int $currentUserId,
        ?int $originalProblemsetId = null,
        ?string $teamsGroupAlias = null
    ): void {
        $acl = new \OmegaUp\DAO\VO\ACLs();
        $acl->owner_id = $currentUserId;

        // Push changes
        try {
            // Begin a new transaction
            \OmegaUp\DAO\DAO::transBegin();

            \OmegaUp\DAO\ACLs::create($acl);
            $problemset->acl_id = $acl->acl_id;
            $problemset->type = 'Contest';
            $problemset->scoreboard_url = \OmegaUp\SecurityTools::randomString(
                30
            );
            $problemset->scoreboard_url_admin = \OmegaUp\SecurityTools::randomString(
                30
            );
            $contest->acl_id = $acl->acl_id;

            // Save the problemset object with data sent by user to the database
            \OmegaUp\DAO\Problemsets::create($problemset);

            $contest->problemset_id = $problemset->problemset_id;
            $contest->penalty_calc_policy = $contest->penalty_calc_policy ?: 'sum';
            $contest->rerun_id = $contest->rerun_id;
            if (!is_null($originalProblemsetId)) {
                if (is_null($contest->problemset_id)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'contestNotFound'
                    );
                }
                \OmegaUp\DAO\ProblemsetProblems::copyProblemset(
                    $contest->problemset_id,
                    $originalProblemsetId
                );
            }

            // Save the contest object with data sent by user to the database
            \OmegaUp\DAO\Contests::create($contest);

            // Update contest_id in problemset object
            $problemset->contest_id = $contest->contest_id;
            \OmegaUp\DAO\Problemsets::update($problemset);

            if (!is_null($teamsGroupAlias)) {
                $teamsGroup = \OmegaUp\DAO\TeamGroups::getByAlias(
                    $teamsGroupAlias
                );
                if (is_null($teamsGroup)) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'invalidParameters',
                        'teams_group'
                    );
                }

                \OmegaUp\DAO\TeamsGroupRoles::create(
                    new \OmegaUp\DAO\VO\TeamsGroupRoles([
                        'acl_id' => $problemset->acl_id,
                        'team_group_id' => $teamsGroup->team_group_id,
                        'role_id' => \OmegaUp\Authorization::CONTESTANT_ROLE,
                    ])
                );
            }

            // End transaction transaction
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'aliasInUse',
                    $e
                );
            }
            throw $e;
        }

        // Expire contest-list cache
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_PUBLIC);
        \OmegaUp\Cache::invalidateAllKeys(
            \OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN
        );

        self::$log->info("New Contest Created: {$contest->alias}");
    }

    /**
     * Creates a new contest
     *
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param 'private'|'public'|'registration'|null $admission_mode
     * @omegaup-request-param null|string $alias
     * @omegaup-request-param bool|null $check_plagiarism
     * @omegaup-request-param bool|null $contest_for_teams
     * @omegaup-request-param null|string $description
     * @omegaup-request-param 'detailed'|'none'|'summary'|null $feedback
     * @omegaup-request-param int $finish_time
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param int|null $penalty
     * @omegaup-request-param 'max'|'sum'|null $penalty_calc_policy
     * @omegaup-request-param 'contest_start'|'none'|'problem_open'|'runtime'|null $penalty_type
     * @omegaup-request-param float|null $points_decay_factor
     * @omegaup-request-param null|string $problems
     * @omegaup-request-param bool|null $requests_user_information
     * @omegaup-request-param 'all_or_nothing'|'max_per_group'|'partial'|null $score_mode
     * @omegaup-request-param float|null $scoreboard
     * @omegaup-request-param bool|null $show_scoreboard_after
     * @omegaup-request-param int $start_time
     * @omegaup-request-param int $submissions_gap
     * @omegaup-request-param null|string $teams_group_alias
     * @omegaup-request-param null|string $title
     * @omegaup-request-param int $window_length
     * @omegaup-request-param bool|null $recommended

     */
    public static function apiCreate(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        // Authenticate user
        $r->ensureMainUserIdentityIsOver13();

        // Validate request
        self::validateCreate($r, $r->identity);

        // Set private contest by default if is not sent in request
        if (
            !is_null($r['admission_mode']) &&
            $r['admission_mode'] !== 'private'
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'contestMustBeCreatedInPrivateMode'
            );
        }

        $problemset = new \OmegaUp\DAO\VO\Problemsets([
            'needs_basic_information' => $r->ensureOptionalBool(
                'needs_basic_information'
            ) ?? false,
            'requests_user_information' => $r['requests_user_information'],
        ]);

        /** @var null|list<string>|scalar $languages */
        $languages = $r['languages'];

        $forTeams = $r->ensureOptionalBool('contest_for_teams') ?? false;
        $teamsGroupsAlias = $forTeams ? $r->ensureString(
            'teams_group_alias',
            fn(string $alias) => \OmegaUp\Validators::alias(
                $alias
            )
        ) : null;

        $scoreMode = $r->ensureOptionalEnum(
            'score_mode',
            ['partial', 'all_or_nothing', 'max_per_group'],
        );
        $checkPlagiarism = $r->ensureOptionalBool('check_plagiarism') ?? false;

        // Handle recommended flag - only available for admins and support team
        $recommendedValue = $r->ensureOptionalBool('recommended');
        $contest = new \OmegaUp\DAO\VO\Contests([
            'admission_mode' => 'private',
            'title' => $r['title'],
            'description' => $r['description'],
            'start_time' => $r['start_time'],
            'finish_time' => $r['finish_time'],
            'window_length' => $r['window_length'] ?: null,
            'alias' => $r['alias'],
            'scoreboard' => $r['scoreboard'],
            'points_decay_factor' => $r['points_decay_factor'],
            'submissions_gap' => $r['submissions_gap'],
            'feedback' => $r['feedback'],
            'penalty_calc_policy' => $r['penalty_calc_policy'],
            'penalty' => max(0, intval($r['penalty'])),
            'penalty_type' => $r['penalty_type'],
            'languages' => $languages,
            'score_mode' => $scoreMode,
            'show_scoreboard_after' => $r['show_scoreboard_after'] ?? true,
            'contest_for_teams' => $forTeams,
            'check_plagiarism' => $checkPlagiarism ? true : false,
        ]);

        if (!is_null($recommendedValue)) {
            if (\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
                $contest->recommended = $recommendedValue;
            } else {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'userNotAllowed'
                );
            }
        }

        self::createContest(
            $problemset,
            $contest,
            $r->user->user_id,
            originalProblemsetId: null,
            teamsGroupAlias: $teamsGroupsAlias,
        );

        return ['status' => 'ok'];
    }

    /**
     * Validates that Request contains expected data to create or update a contest
     * In case of update, everything is optional except the contest_alias
     * In case of error, this function throws.
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @omegaup-request-param 'private'|'public'|'registration'|null $admission_mode
     * @omegaup-request-param null|string $alias
     * @omegaup-request-param bool|null $check_plagiarism
     * @omegaup-request-param bool|null $contest_for_teams
     * @omegaup-request-param null|string $description
     * @omegaup-request-param 'detailed'|'none'|'summary'|null $feedback
     * @omegaup-request-param int $finish_time
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param int|null $penalty
     * @omegaup-request-param 'max'|'sum'|null $penalty_calc_policy
     * @omegaup-request-param 'contest_start'|'none'|'problem_open'|'runtime'|null $penalty_type
     * @omegaup-request-param float|null $points_decay_factor
     * @omegaup-request-param null|string $problems
     * @omegaup-request-param 'all_or_nothing'|'max_per_group'|'partial'|null $score_mode
     * @omegaup-request-param float|null $scoreboard
     * @omegaup-request-param bool|null $show_scoreboard_after
     * @omegaup-request-param int $start_time
     * @omegaup-request-param int $submissions_gap
     * @omegaup-request-param null|string $teams_group_alias
     * @omegaup-request-param null|string $title
     * @omegaup-request-param int $window_length
     */
    private static function validateCommonCreateOrUpdate(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Contests $contest = null,
        bool $isRequired = true
    ): void {
        $r->ensureOptionalString('title', $isRequired);
        $r->ensureOptionalString('description', $isRequired);

        // Get the actual start and finish time of the contest, considering that
        // in case of update, parameters can be optional
        $startTime = $r->ensureOptionalTimestamp(
            'start_time',
            required: $isRequired
        ) ?? (
            is_null($contest)
            ? null
            : \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                $contest->start_time
            )
        );
        $finishTime = $r->ensureOptionalTimestamp(
            'finish_time',
            required: $isRequired
        ) ?? (
            is_null($contest)
            ? null
            : \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                $contest->finish_time
            )
        );

        // Calculate the actual contest length
        $contestLength = 0;
        if (!is_null($finishTime) && !is_null($startTime)) {
            // Validate start & finish time where finish_time must be strictly
            // greater than start_time
            if ($startTime->time >= $finishTime->time) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'contestNewInvalidStartTime',
                    'finish_time'
                );
            }
            $contestLength = $finishTime->time - $startTime->time;
        }

        // Validate max contest length (sys-admins get an extended limit)
        $isSystemAdmin = \OmegaUp\Authorization::isSystemAdmin($identity);
        $maxContestLength = $isSystemAdmin
            ? \OmegaUp\Controllers\Contest::MAX_CONTEST_LENGTH_SYSADMIN_SECONDS
            : \OmegaUp\Controllers\Contest::MAX_CONTEST_LENGTH_SECONDS;

        if ($contestLength > $maxContestLength) {
            $maxDays = intval($maxContestLength / (60 * 60 * 24)); // Convert seconds to days
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'contestLengthTooLong',
                'finish_time',
                ['max_days' => strval($maxDays)]
            );
        }

        // Window_length is optional
        if (!empty($r['window_length'])) {
            $r->ensureOptionalInt(
                'window_length',
                lowerBound: 0,
                upperBound: intval(floor($contestLength / 60)),
                required: false
            );
        }

        $r->ensureOptionalEnum(
            'admission_mode',
            ['public', 'private', 'registration']
        );
        $contestAlias = $r->ensureOptionalString(
            'alias',
            $isRequired,
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        if (
            !is_null($contestAlias) &&
            !is_null(\OmegaUp\DAO\Contests::getByAlias($contestAlias))
        ) {
            $exception = new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'aliasInUse'
            );
            $exception->addCustomMessageToArray('parameter', 'alias');
            throw $exception;
        }
        $r->ensureOptionalFloat('scoreboard', 0, 100, $isRequired);
        $r->ensureOptionalFloat('points_decay_factor', 0, 1, $isRequired);
        $r->ensureOptionalEnum(
            'score_mode',
            ['partial', 'all_or_nothing', 'max_per_group'],
        );
        $submissionsGap = $r->ensureOptionalInt(
            'submissions_gap',
            0,
            null,
            $isRequired
        );
        $r->ensureOptionalInt('penalty', 0, 10000, $isRequired);
        // Validate the submission_gap in minutes so that the error message
        // matches what is displayed in the UI.
        \OmegaUp\Validators::validateNumberInRange(
            (
                is_null($submissionsGap) ?
                null :
                floor(intval($submissionsGap) / 60)
            ),
            'submissions_gap',
            1,
            $contestLength === 0 ? null : floor($contestLength / 60),
            $isRequired
        );

        $r->ensureOptionalEnum(
            'feedback',
            ['none', 'summary', 'detailed'],
            $isRequired
        );
        $r->ensureOptionalEnum(
            'penalty_type',
            ['contest_start', 'problem_open', 'runtime', 'none'],
            $isRequired
        );
        $r->ensureOptionalEnum(
            'penalty_calc_policy',
            ['sum', 'max']
        );
        $problems = $r->ensureOptionalString('problems');

        // Problems is optional
        if (!is_null($problems)) {
            /** @var list<array{problem: string, points: int}>|null */
            $requestProblems = json_decode($problems, associative: true);
            if (!is_array($requestProblems)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'invalidParameters',
                    'problems'
                );
            }

            $problems = [];

            foreach ($requestProblems as $requestProblem) {
                $problem = \OmegaUp\DAO\Problems::getByAlias(
                    $requestProblem['problem']
                );
                if (is_null($problem)) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterNotFound',
                        'problems'
                    );
                }
                \OmegaUp\Controllers\Problemset::validateAddProblemToProblemset(
                    $problem,
                    $identity
                );
                array_push($problems, [
                    'id' => $problem->problem_id,
                    'alias' => $requestProblem['problem'],
                    'points' => $requestProblem['points']
                ]);
            }

            $r['problems'] = $problems;
        }

        // Show scoreboard is always optional
        $r->ensureOptionalBool('show_scoreboard_after');

        $forTeams = $r->ensureOptionalBool('contest_for_teams') ?? false;
        if ($forTeams) {
            $r->ensureString(
                'teams_group_alias',
                fn(string $alias) => \OmegaUp\Validators::alias($alias)
            );
        }
        $r->ensureOptionalBool('check_plagiarism') ?? false;

        // languages is required only when a contest is created
        $languagesAsString = $r->ensureOptionalString('languages', $isRequired);
        if (is_null($languagesAsString)) {
            return;
        }

        $languages = explode(',', $languagesAsString);

        $supportedLanguages = array_keys(
            \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES()
        );
        \OmegaUp\Validators::validateValidSubset(
            array_values(array_filter($languages, function ($language) {
                return !empty($language);
            })),
            'langauges',
            $supportedLanguages
        );
    }

    /**
     * Validates that Request contains expected data to create a contest
     * In case of error, this function throws.
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @omegaup-request-param 'private'|'public'|'registration'|null $admission_mode
     * @omegaup-request-param null|string $alias
     * @omegaup-request-param bool|null $check_plagiarism
     * @omegaup-request-param bool|null $contest_for_teams
     * @omegaup-request-param null|string $description
     * @omegaup-request-param 'detailed'|'none'|'summary'|null $feedback
     * @omegaup-request-param int $finish_time
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param int|null $penalty
     * @omegaup-request-param 'max'|'sum'|null $penalty_calc_policy
     * @omegaup-request-param 'contest_start'|'none'|'problem_open'|'runtime'|null $penalty_type
     * @omegaup-request-param float|null $points_decay_factor
     * @omegaup-request-param null|string $problems
     * @omegaup-request-param 'all_or_nothing'|'max_per_group'|'partial'|null $score_mode
     * @omegaup-request-param float|null $scoreboard
     * @omegaup-request-param bool|null $show_scoreboard_after
     * @omegaup-request-param int $start_time
     * @omegaup-request-param int $submissions_gap
     * @omegaup-request-param null|string $teams_group_alias
     * @omegaup-request-param null|string $title
     * @omegaup-request-param int $window_length
     */
    private static function validateCreate(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        self::validateCommonCreateOrUpdate($r, $identity);
    }

    /**
     * Validates that Request contains expected data to update a contest
     * everything is optional except the contest_alias
     * In case of error, this function throws.
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @return \OmegaUp\DAO\VO\Contests
     *
     * @omegaup-request-param 'private'|'public'|'registration'|null $admission_mode
     * @omegaup-request-param null|string $alias
     * @omegaup-request-param bool|null $check_plagiarism
     * @omegaup-request-param bool|null $contest_for_teams
     * @omegaup-request-param null|string $description
     * @omegaup-request-param 'detailed'|'none'|'summary'|null $feedback
     * @omegaup-request-param int $finish_time
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param int|null $penalty
     * @omegaup-request-param 'max'|'sum'|null $penalty_calc_policy
     * @omegaup-request-param 'contest_start'|'none'|'problem_open'|'runtime'|null $penalty_type
     * @omegaup-request-param float|null $points_decay_factor
     * @omegaup-request-param null|string $problems
     * @omegaup-request-param 'all_or_nothing'|'max_per_group'|'partial'|null $score_mode
     * @omegaup-request-param float|null $scoreboard
     * @omegaup-request-param bool|null $show_scoreboard_after
     * @omegaup-request-param int $start_time
     * @omegaup-request-param int $submissions_gap
     * @omegaup-request-param null|string $teams_group_alias
     * @omegaup-request-param null|string $title
     * @omegaup-request-param int $window_length
     */
    private static function validateUpdate(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Identities $identity,
        string $contestAlias
    ): \OmegaUp\DAO\VO\Contests {
        $contest = self::validateContestAdmin(
            $contestAlias,
            $identity
        );

        self::validateCommonCreateOrUpdate(
            $r,
            $identity,
            $contest,
            isRequired: false,
        );

        // Prevent date changes if a contest already has runs
        $startTime = $r->ensureOptionalTimestamp('start_time');
        if (
            !is_null($startTime) &&
            $startTime->time != $contest->start_time->time
        ) {
            $runCount = 0;

            $runCount = \OmegaUp\DAO\Submissions::countTotalSubmissionsOfProblemset(
                intval($contest->problemset_id)
            );

            if ($runCount > 0) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'contestUpdateAlreadyHasRuns'
                );
            }
        }
        return $contest;
    }

    /**
     * Function created to be called for all the API's that only can access
     * admins or contest organizers.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return \OmegaUp\DAO\VO\Contests
     */
    private static function validateContestAdmin(
        string $contestAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ): \OmegaUp\DAO\VO\Contests {
        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        if (!\OmegaUp\Authorization::isContestAdmin($identity, $contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        return $contest;
    }

    /**
     * This function is used to restrict API in virtual contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return void
     */
    private static function forbiddenInVirtual(\OmegaUp\DAO\VO\Contests $contest): void {
        if (\OmegaUp\DAO\Contests::isVirtual($contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'forbiddenInVirtualContest'
            );
        }
    }

    /**
     * Gets the problems from a contest
     *
     * @return array{problems: list<ProblemsetProblemWithVersions>}
     *
     * @omegaup-request-param string $contest_alias
     */
    public static function apiProblems(\OmegaUp\Request $r): array {
        // Authenticate user
        $r->ensureIdentity();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        // Only director is allowed to create problems in contest
        $contest = self::validateContestAdmin($contestAlias, $r->identity);
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'contestNotFound'
            );
        }

        $problemsetExists = \OmegaUp\DAO\Problemsets::existsByPK(
            $contest->problemset_id
        );
        if (!$problemsetExists) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        $problems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $contest->problemset_id,
            needSubmissions: true
        );

        return [
            'problems' => self::addVersionsToProblems(
                $problems,
                $r->identity,
                $contest->problemset_id
            ),
        ];
    }

    /**
     * Adds a problem to a contest
     *
     * @return array{status: string, solutionStatus: string}
     *
     * @omegaup-request-param null|string $commit
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param int $order_in_contest
     * @omegaup-request-param float $points
     * @omegaup-request-param string $problem_alias
     */
    public static function apiAddProblem(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        // Authenticate user
        $r->ensureMainUserIdentityIsOver13();
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $points = $r->ensureFloat('points', 0, INF);
        $orderInContest = $r->ensureOptionalInt(
            'order_in_contest',
            0,
            null
        ) ?? 1;

        $contest = self::validateContestAdmin($contestAlias, $r->identity);

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }
        // Validate the request
        \OmegaUp\Controllers\Problemset::validateAddProblemToProblemset(
            $problem,
            $r->identity,
            $contest->problemset_id
        );

        self::forbiddenInVirtual($contest);

        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            intval($contest->problemset_id)
        );
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        // Extract the problem from the problemset if exists
        $originalProblemsetProblem = \OmegaUp\DAO\Base\ProblemsetProblems::getByPK(
            $contest->problemset_id,
            $problem->problem_id
        );

        if (
            is_null($originalProblemsetProblem)
            &&
            \OmegaUp\DAO\ProblemsetProblems::countProblemsetProblems(
                $problemset
            )
            >= MAX_PROBLEMS_IN_CONTEST
        ) {
            throw new \OmegaUp\Exceptions\PreconditionFailedException(
                'contestAddproblemTooManyProblems'
            );
        }

        [
            $masterCommit,
            $currentVersion,
        ] = \OmegaUp\Controllers\Problem::resolveCommit(
            $problem,
            $r->ensureOptionalString(
                'commit',
                required: false,
                validator: fn(string $commit) => \OmegaUp\Validators::stringOfLengthInRange(
                    $commit,
                    1,
                    40,
                )
            ),
        );

        \OmegaUp\Controllers\Problemset::addProblem(
            intval($contest->problemset_id),
            $problem,
            $masterCommit,
            $currentVersion,
            $r->identity,
            $points,
            $orderInContest,
            $originalProblemsetProblem
        );

        // Invalidar cache
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::CONTEST_INFO,
            $contestAlias
        );
        \OmegaUp\Scoreboard::invalidateScoreboardCache(
            \OmegaUp\ScoreboardParams::fromContest(
                $contest
            )
        );

        $solutionStatus = \OmegaUp\Controllers\Problem::SOLUTION_NOT_FOUND;

        if (\OmegaUp\DAO\Problems::isVisible($problem)) {
            $solutionStatus = \OmegaUp\Controllers\Problem::getProblemSolutionStatus(
                $problem,
                $r->identity
            );
        }

        \OmegaUp\DAO\Runs::recalculatePenaltyForContest($contest);

        return [
            'status' => 'ok',
            'solutionStatus' => $solutionStatus,
        ];
    }

    /**
     * Removes a problem from a contest
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $problem_alias
     */
    public static function apiRemoveProblem(\OmegaUp\Request $r) {
        // Authenticate user
        $r->ensureMainUserIdentity();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        // Validate the request and get the problem and the contest in an array
        $params = self::validateRemoveFromContestRequest(
            $contestAlias,
            $problemAlias,
            $r->identity
        );

        self::forbiddenInVirtual($params['contest']);

        \OmegaUp\DAO\ProblemsetProblems::delete(new \OmegaUp\DAO\VO\ProblemsetProblems([
            'problemset_id' => $params['contest']->problemset_id,
            'problem_id' => $params['problem']->problem_id
        ]));

        // Invalidar cache
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::CONTEST_INFO,
            $contestAlias
        );
        \OmegaUp\Scoreboard::invalidateScoreboardCache(
            \OmegaUp\ScoreboardParams::fromContest(
                $params['contest']
            )
        );

        return ['status' => 'ok'];
    }

    /**
     * Validates the request for RemoveFromContest and returns an array with
     * the problem and contest DAOs
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{contest: \OmegaUp\DAO\VO\Contests, problem: \OmegaUp\DAO\VO\Problems}
     */
    private static function validateRemoveFromContestRequest(
        string $contestAlias,
        string $problemAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($contest) || is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'contest_alias'
            );
        }
        // Only contest admin is allowed to remove problems in contest
        if (!\OmegaUp\Authorization::isContestAdmin($identity, $contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'cannotRemoveProblem'
            );
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'problem_alias'
            );
        }

        // Disallow removing problem from contest if it already has runs within the contest
        if (
            \OmegaUp\DAO\Submissions::countTotalRunsOfProblemInProblemset(
                intval($problem->problem_id),
                intval($contest->problemset_id)
            ) > 0
            && !\OmegaUp\Authorization::isSystemAdmin($identity)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'cannotRemoveProblemWithSubmissions'
            );
        }

        if (self::isPublic($contest->admission_mode)) {
            // Check that contest has at least 2 problems
            $problemsetExists = \OmegaUp\DAO\Problemsets::existsByPK(
                $contest->problemset_id
            );
            if (!$problemsetExists) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemsetNotFound'
                );
            }
            $problemsInContest = \OmegaUp\DAO\ProblemsetProblems::GetRelevantProblems(
                $contest->problemset_id
            );
            if (count($problemsInContest) < 2) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'contestPublicRequiresProblem'
                );
            }
        }

        return [
            'contest' => $contest,
            'problem' => $problem,
        ];
    }

    /**
     * Return a report of which runs would change due to a version change.
     *
     * @return array{diff: list<array{guid: string, new_score: float|null, new_status: null|string, new_verdict: null|string, old_score: float|null, old_status: null|string, old_verdict: null|string, problemset_id: int|null, username: string}>}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $problem_alias
     * @omegaup-request-param string $version
     */
    public static function apiRunsDiff(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['version'], 'version');

        $contest = self::validateContestAdmin($contestAlias, $r->identity);

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $problemsetProblem = \OmegaUp\DAO\ProblemsetProblems::getByPK(
            intval($contest->problemset_id),
            intval($problem->problem_id)
        );
        if (
            is_null($problemsetProblem)
            || is_null($problemsetProblem->version)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }

        return [
            'diff' => \OmegaUp\DAO\Runs::getRunsDiffsForVersion(
                $problem,
                intval($contest->problemset_id),
                $problemsetProblem->version,
                $r['version']
            ),
        ];
    }

    /**
     * Validates add/remove user request
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{0: \OmegaUp\DAO\VO\Identities, 1: \OmegaUp\DAO\VO\Contests}
     */
    private static function validateAddRemoveUser(
        string $contestAlias,
        string $usernameOrEmail,
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        $identityToAddOrRemove = \OmegaUp\Controllers\Identity::resolveIdentity(
            $usernameOrEmail
        );
        $contest = self::validateContestAdmin($contestAlias, $identity);

        if (is_null($contest->rerun_id)) {
            return [$identityToAddOrRemove, $contest];
        }

        $originalContest = \OmegaUp\DAO\Contests::getByPK($contest->rerun_id);

        if (is_null($originalContest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        if ($originalContest->admission_mode === 'private') {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'usersCanNotBeAddedInVirtualContestWhenOriginalContestIsPrivate'
            );
        }

        return [$identityToAddOrRemove, $contest];
    }

    /**
     * Adds a user to a contest.
     * By default, any user can view details of public contests.
     * Only users added through this API can view private contests
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiAddUser(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        // Authenticate logged user
        $r->ensureMainUserIdentity();
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );
        [$identity, $contest] = self::validateAddRemoveUser(
            $contestAlias,
            $r['usernameOrEmail'],
            $r->identity
        );
        if ($contest->contest_for_teams) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'usersCanNotBeAddedInContestForTeams'
            );
        }
        if (is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userNotExist'
            );
        }

        try {
            // Begin a new transaction
            \OmegaUp\DAO\DAO::transBegin();

            // Save the contest to the DB
            \OmegaUp\DAO\ProblemsetIdentities::replace(
                new \OmegaUp\DAO\VO\ProblemsetIdentities([
                    'problemset_id' => $contest->problemset_id,
                    'identity_id' => $identity->identity_id,
                    'access_time' => null,
                    'end_time' => null,
                    'score' => 0,
                    'time' => 0,
                    'is_invited' => true,
                ])
            );

            if ($contest->admission_mode === 'registration') {
                // Pre-accept user
                self::preAcceptAccessRequest(
                    $contest,
                    [$identity->identity_id],
                    $r->user
                );
            }
            // End transaction
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();

            throw $e;
        }
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::CONTESTS_CONTESTANTS_LIST,
            strval($contest->contest_id)
        );

        return ['status' => 'ok'];
    }

    /**
     * @param list<int> $identitiesIDs
     */
    private static function preAcceptAccessRequest(
        \OmegaUp\DAO\VO\Contests $contest,
        array $identitiesIDs,
        \OmegaUp\DAO\VO\Users $admin
    ): void {
        $time = \OmegaUp\Time::get();
        $note = \OmegaUp\Translations::getInstance()->get(
            'contestRegistrationPreAcceptedDescription'
        );
        foreach ($identitiesIDs as $identityID) {
            if (
                \OmegaUp\DAO\ProblemsetIdentityRequest::replace(
                    new \OmegaUp\DAO\VO\ProblemsetIdentityRequest([
                        'identity_id' => $identityID,
                        'problemset_id' => $contest->problemset_id,
                        'request_time' => $time,
                        'last_update' => $time,
                        'accepted' => true,
                        'extra_note' => $note,
                    ])
                ) > 0
            ) {
                // Save this action in the history
                \OmegaUp\DAO\ProblemsetIdentityRequestHistory::create(
                    new \OmegaUp\DAO\VO\ProblemsetIdentityRequestHistory([
                        'identity_id' => $identityID,
                        'problemset_id' => $contest->problemset_id,
                        'time' => $time,
                        'admin_id' => $admin->user_id,
                        'accepted' => true,
                    ])
                );
            }
        }
    }

    /**
     * Remove a user from a private contest
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiRemoveUser(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureMainUserIdentity();
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );
        [$identity, $contest] = self::validateAddRemoveUser(
            $contestAlias,
            $r['usernameOrEmail'],
            $r->identity
        );

        \OmegaUp\DAO\ProblemsetIdentities::delete(new \OmegaUp\DAO\VO\ProblemsetIdentities([
            'problemset_id' => $contest->problemset_id,
            'identity_id' => $identity->identity_id,
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Replace the teams group assigned to a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias The alias of the contest
     * @omegaup-request-param string $teams_group_alias The alias of the teams group
     */
    public static function apiReplaceTeamsGroup(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        // Authenticate logged user
        $r->ensureIdentity();

        // Check contest_alias
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $teamsGroupAlias = $r->ensureString(
            'teams_group_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $teamsGroup = \OmegaUp\DAO\TeamGroups::getByAlias($teamsGroupAlias);
        if (is_null($teamsGroup)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $contest = self::validateContestAdmin(
            $contestAlias,
            $r->identity
        );
        if (!$contest->contest_for_teams) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'teamsGroupsCanNotBeAddedInNormalContest'
            );
        }
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            intval($contest->problemset_id)
        );
        if (is_null($problemset) || is_null($problemset->acl_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        if (\OmegaUp\DAO\Problemsets::hasSubmissions($problemset)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'contestEditCannotReplaceTeamsGroupWithSubmissions'
            );
        }

        try {
            // Begin a new transaction
            \OmegaUp\DAO\DAO::transBegin();

            \OmegaUp\DAO\TeamsGroupRoles::deleteAllTeamGroupsForAclId(
                $problemset->acl_id
            );

            \OmegaUp\DAO\TeamsGroupRoles::create(
                new \OmegaUp\DAO\VO\TeamsGroupRoles([
                    'acl_id' => $problemset->acl_id,
                    'team_group_id' => $teamsGroup->team_group_id,
                    'role_id' => \OmegaUp\Authorization::CONTESTANT_ROLE,
                ])
            );
            // End transaction
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();

            throw $e;
        }

        return ['status' => 'ok'];
    }

    /**
     * Adds a group to a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $group
     */
    public static function apiAddGroup(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        // Authenticate logged user
        $r->ensureIdentity();

        // Check contest_alias
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $groupAlias = $r->ensureString(
            'group',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $group = \OmegaUp\DAO\Groups::findByAlias($groupAlias);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $contest = self::validateContestAdmin(
            $contestAlias,
            $r->identity
        );
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            intval($contest->problemset_id)
        );
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        if ($contest->contest_for_teams) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'groupsCanNotBeAddedInContestForTeams'
            );
        }
        \OmegaUp\DAO\GroupRoles::create(
            new \OmegaUp\DAO\VO\GroupRoles([
                'acl_id' => $problemset->acl_id,
                'group_id' => $group->group_id,
                'role_id' => \OmegaUp\Authorization::CONTESTANT_ROLE,
            ])
        );

        return ['status' => 'ok'];
    }

    /**
     * Removes a group from a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $group
     */
    public static function apiRemoveGroup(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check contest_alias
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $groupAlias = $r->ensureString(
            'group',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $group = \OmegaUp\DAO\Groups::findByAlias($groupAlias);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $contest = self::validateContestAdmin($contestAlias, $r->identity);
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            intval($contest->problemset_id)
        );
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        \OmegaUp\DAO\GroupRoles::delete(
            new \OmegaUp\DAO\VO\GroupRoles([
                'acl_id' => $problemset->acl_id,
                'group_id' => $group->group_id,
                'role_id' => \OmegaUp\Authorization::CONTESTANT_ROLE,
            ])
        );

        return ['status' => 'ok'];
    }

    /**
     * Adds an admin to a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiAddAdmin(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        // Authenticate logged user
        $r->ensureMainUserIdentityIsOver13();

        // Check contest_alias
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        $user = \OmegaUp\Controllers\User::resolveUser($r['usernameOrEmail']);

        $contest = self::validateContestAdmin($contestAlias, $r->identity);
        if (is_null($contest->acl_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        \OmegaUp\Controllers\ACL::addUser(
            $contest->acl_id,
            intval($user->user_id)
        );

        return ['status' => 'ok'];
    }

    /**
     * Removes an admin from a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiRemoveAdmin(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureMainUserIdentity();

        // Check contest_alias
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );
        if (is_null($identity->user_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidUser'
            );
        }

        $contest = self::validateContestAdmin($contestAlias, $r->identity);

        // Check if admin to delete is actually an admin
        if (!\OmegaUp\Authorization::isContestAdmin($identity, $contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        \OmegaUp\Controllers\ACL::removeUser(
            intval($contest->acl_id),
            $identity->user_id
        );

        return ['status' => 'ok'];
    }

    /**
     * Adds a group admin to a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $group
     */
    public static function apiAddGroupAdmin(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        // Authenticate logged user
        $r->ensureMainUserIdentityIsOver13();

        // Check contest_alias
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $groupAlias = $r->ensureString(
            'group',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $group = \OmegaUp\DAO\Groups::findByAlias($groupAlias);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $contest = self::validateContestAdmin($contestAlias, $r->identity);

        \OmegaUp\Controllers\ACL::addGroup(
            intval($contest->acl_id),
            intval($group->group_id)
        );

        return ['status' => 'ok'];
    }

    /**
     * Removes a group admin from a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $group
     */
    public static function apiRemoveGroupAdmin(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureMainUserIdentity();

        // Check contest_alias
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $groupAlias = $r->ensureString(
            'group',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $group = \OmegaUp\DAO\Groups::findByAlias($groupAlias);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $contest = self::validateContestAdmin($contestAlias, $r->identity);

        \OmegaUp\Controllers\ACL::removeGroup(
            intval($contest->acl_id),
            intval($group->group_id)
        );

        return ['status' => 'ok'];
    }

    /**
     * Get clarifications of a contest
     *
     * @return array{clarifications: list<Clarification>}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param int $offset
     * @omegaup-request-param int $rowcount
     */
    public static function apiClarifications(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $offset = $r->ensureOptionalInt('offset');
        $rowcount = $r->ensureOptionalInt('rowcount') ?? 1000;
        if ($offset < 0) {
            $offset = 0;
        }

        $contest = self::validateContest(
            $r->ensureString(
                'contest_alias',
                fn(string $alias) => \OmegaUp\Validators::alias($alias)
            )
        );

        return [
            'clarifications' => \OmegaUp\DAO\Clarifications::getProblemsetClarifications(
                $contest,
                course: null,
                isAdmin: \OmegaUp\Authorization::isContestAdmin(
                    $r->identity,
                    $contest
                ),
                currentIdentity: $r->identity,
                offset: $offset,
                rowcount: $rowcount,
            )['clarifications'],
        ];
    }

    /**
     * Get clarifications of problem in a contest
     *
     * @return array{clarifications: list<Clarification>}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param int $offset
     * @omegaup-request-param int $rowcount
     */
    public static function apiProblemClarifications(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $offset = $r->ensureOptionalInt('offset');
        $rowcount = $r->ensureOptionalInt('rowcount') ?? 1000;
        if ($offset < 0) {
            $offset = 0;
        }

        $contest = self::validateContest(
            $r->ensureString(
                'contest_alias',
                fn(string $alias) => \OmegaUp\Validators::alias($alias)
            )
        );

        $problem = \OmegaUp\DAO\Problems::getByAliasAndProblemset(
            $r->ensureString(
                'problem_alias',
                fn(string $alias) => \OmegaUp\Validators::alias($alias)
            ),
            intval($contest->problemset_id)
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        return [
            'clarifications' => \OmegaUp\DAO\Clarifications::getProblemInProblemsetClarifications(
                $problem,
                intval($contest->problemset_id),
                \OmegaUp\Authorization::isContestAdmin(
                    $r->identity,
                    $contest
                ),
                currentIdentity: $r->identity,
                offset: $offset,
                rowcount: $rowcount,
            ),
        ];
    }

    /**
     * Returns the Scoreboard events
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{events: list<ScoreboardEvent>}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $token
     */
    public static function apiScoreboardEvents(\OmegaUp\Request $r): array {
        // Get the current user
        try {
            $r->ensureidentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
            $r->identity = null;
        }
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $token = $r->ensureOptionalString('token');
        [
            'contest' => $contest,
        ] = self::validateDetails($contestAlias, $r->identity, $token);

        return [
            'events' => self::getScoreboardEvents($contest, $r->identity),
        ];
    }

    /**
     * @return list<ScoreboardEvent>
     */
    private static function getScoreboardEvents(
        \OmegaUp\DAO\VO\Contests $contest,
        ?\OmegaUp\DAO\VO\Identities $identity
    ) {
        $params = \OmegaUp\ScoreboardParams::fromContest($contest);
        $params->admin = (
            !is_null($identity) &&
            \OmegaUp\Authorization::isContestAdmin($identity, $contest) &&
            !\OmegaUp\DAO\Contests::isVirtual($contest)
        );
        $params->show_all_runs = !\OmegaUp\DAO\Contests::isVirtual($contest);
        $scoreboard = new \OmegaUp\Scoreboard($params);

        // Push scoreboard data in response
        return $scoreboard->events();
    }

    /**
     * Returns the Scoreboard
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return Scoreboard
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $token
     */
    public static function apiScoreboard(\OmegaUp\Request $r): array {
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        [
            'contest' => $contest,
            'problemset' => $problemset
        ] = self::validateBasicDetails($contestAlias);

        $token = $r->ensureOptionalString(
            'token',
            required: false,
        );
        $identity = null;
        try {
            $r->ensureIdentity();
            $identity = $r->identity;
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
        }
        return self::getScoreboard($contest, $problemset, $identity, $token);
    }

    /**
     * @return null|Scoreboard
     */
    public static function getScoreboardForUserProfile(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Problemsets $problemset,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        // If true, will override Scoreboard Percentage to 100%
        $showAllRuns = \OmegaUp\Authorization::isContestAdmin(
            $identity,
            $contest
        );

        if (!self::canAccessContest($contest, $identity)) {
            return null;
        }

        // Create scoreboard
        $params = \OmegaUp\ScoreboardParams::fromContest($contest);
        $params->admin = $showAllRuns;
        $scoreboard = new \OmegaUp\Scoreboard($params);

        return $scoreboard->generate();
    }

    /**
     * @return Scoreboard
     */
    public static function getScoreboard(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Problemsets $problemset,
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?string $token = null
    ) {
        // If true, will override Scoreboard Percentage to 100%
        $showAllRuns = false;

        if (empty($token)) {
            // User should be logged
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\UnauthorizedException();
            }

            self::validateAccessContest($contest, $identity);

            if (
                \OmegaUp\Authorization::isContestAdmin(
                    $identity,
                    $contest
                )
            ) {
                $showAllRuns = true;
            }
        } else {
            if ($token === $problemset->scoreboard_url) {
                $showAllRuns = false;
            } elseif ($token === $problemset->scoreboard_url_admin) {
                $showAllRuns = true;
            } else {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'invalidScoreboardUrl'
                );
            }
        }

        // Create scoreboard
        $params = \OmegaUp\ScoreboardParams::fromContest($contest);
        $params->admin = $showAllRuns;
        $scoreboard = new \OmegaUp\Scoreboard($params);

        return $scoreboard->generate();
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: ContestScoreboardPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $scoreboard_token
     */
    public static function getContestScoreboardDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $identity = null;
        try {
            $r->ensureIdentity();
            $identity = $r->identity;
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
        }

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $scoreboardToken = $r->ensureOptionalString(
            'scoreboard_token',
            required: false,
            validator: fn(string $token) => \OmegaUp\Validators::token($token)
        );
        [
            'contest' => $contest,
            'contest_admin' => $contestAdmin,
            'problemset' => $problemset
        ] = self::validateDetails($contestAlias, $identity, $scoreboardToken);

        if (is_null($problemset->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        $problemsInContest = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $problemset->problemset_id,
            needSubmissions: false
        );

        $problemsResponseArray = [];
        $letter = 0;
        foreach ($problemsInContest as $problem) {
            $problem['letter'] = \OmegaUp\Controllers\Contest::columnName(
                $letter++
            );
            $problem['accepts_submissions'] = !empty($problem['languages']);
            $problemsResponseArray[] = [
                'acceptsSubmissions' => $problem['accepts_submissions'],
                'alias' => strval($problem['alias']),
                'text' => "{$problem['letter']}. {$problem['title']}",
                'bestScore' => 0,
                'maxScore' => floatval($problem['points']),
                'hasRuns' => false,
            ];
        }

        $contestDetails = self::getContestDetails(
            $contest,
            $contestAdmin,
            $identity,
            $scoreboardToken
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'contest' => $contestDetails,
                    'contestAdmin' => $contestAdmin,
                    'scoreboardToken' => $scoreboardToken,
                    'scoreboard' => self::getScoreboard(
                        $contest,
                        $problemset,
                        $identity,
                        $scoreboardToken
                    ),
                    'scoreboardEvents' => self::getScoreboardEvents(
                        $contest,
                        $identity
                    ),
                    'problems' => $problemsResponseArray,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleContestScoreboard'
                ),
            ],
            'entrypoint' => 'contest_scoreboard',
        ];
    }

    /**
     * Gets the accomulative scoreboard for an array of contests
     *
     * @return array{ranking: list<MergedScoreboardEntry>}
     *
     * @omegaup-request-param string $contest_aliases
     * @omegaup-request-param mixed $contest_params
     * @omegaup-request-param null|string $usernames_filter
     */
    public static function apiScoreboardMerge(\OmegaUp\Request $r): array {
        // Get the current user
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_aliases'],
            'contest_aliases'
        );
        $contestAliases = explode(',', $r['contest_aliases']);

        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['usernames_filter'],
            'usernames_filter'
        );
        /** @var list<string> */
        $usernamesFilter = [];
        if (isset($r['usernames_filter'])) {
            $usernamesFilter = explode(',', $r['usernames_filter']);
        }

        if (isset($r['contest_params']) && is_array($r['contest_params'])) {
            /** @var array<string, array{only_ac: bool, weight: float}> */
            $contestParams = $r['contest_params'];
        } else {
            /** @var array<string, array{only_ac: bool, weight: float}> */
            $contestParams = [];
        }

        return [
            'ranking' => self::getMergedScoreboard(
                $contestAliases,
                $usernamesFilter,
                $contestParams
            ),
        ];
    }

    /**
     * @param list<string> $contestAliases
     * @param list<string> $usernamesFilter
     * @param array<string, array{only_ac: bool, weight: float}> $contestParams
     *
     * @return list<MergedScoreboardEntry>
     */
    public static function getMergedScoreboard(
        array $contestAliases,
        array $usernamesFilter,
        array $contestParams
    ): array {
        // Validate all contest alias
        $contests = [];
        foreach ($contestAliases as $contestAlias) {
            $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
            if (is_null($contest)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'contestNotFound'
                );
            }

            $contests[] = $contest;
        }

        // Get all scoreboards
        $scoreboards = [];
        foreach ($contests as $contest) {
            if (is_null($contest->alias)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'contestNotFound'
                );
            }
            // Set defaults for contests params
            if (!isset($contestParams[$contest->alias])) {
                $contestParams[$contest->alias] = [
                    'only_ac' => false,
                    'weight' => 1.0,
                ];
            }
            $params = \OmegaUp\ScoreboardParams::fromContest($contest);
            $params->only_ac = $contestParams[strval(
                $contest->alias
            )]['only_ac'];
            $s = new \OmegaUp\Scoreboard($params);

            $scoreboards[strval($contest->alias)] = $s->generate();
        }

        /** @var array<string, MergedScoreboardEntry> */
        $mergedScoreboard = [];

        // Merge
        /** @var string $contestAlias */
        foreach ($scoreboards as $contestAlias => $scoreboard) {
            foreach ($scoreboard['ranking'] as $userResults) {
                $username = $userResults['username'];
                // If user haven't been added to the merged scoredboard, add them.
                if (!isset($mergedScoreboard[$username])) {
                    $mergedScoreboard[$username] = [
                        'name' => $userResults['name'],
                        'username' => $username,
                        'contests' => [],
                        'total' => [
                            'points' => 0.0,
                            'penalty' => 0.0,
                        ],
                    ];
                }

                $mergedScoreboard[$username]['contests'][$contestAlias] = [
                    'points' => ($userResults['total']['points'] * $contestParams[$contestAlias]['weight']),
                    'penalty' => $userResults['total']['penalty'],
                ];

                $mergedScoreboard[$username]['total']['points'] += (
                    $userResults['total']['points'] * $contestParams[$contestAlias]['weight']
                );
                $mergedScoreboard[$username]['total']['penalty'] += (
                    $userResults['total']['penalty']
                );
            }
        }

        // Remove users not in filter
        if (!empty($usernamesFilter)) {
            foreach ($mergedScoreboard as $username => $entry) {
                if (array_search($username, $usernamesFilter) === false) {
                    unset($mergedScoreboard[$username]);
                }
            }
        }

        // Normalize user["contests"] entries so all contain the same contests
        foreach ($mergedScoreboard as $username => $entry) {
            foreach ($contests as $contest) {
                if (isset($entry['contests'][$contest->alias]['points'])) {
                    continue;
                }
                /** @var string $contest->alias */
                $mergedScoreboard[$username]['contests'][$contest->alias] = [
                    'points' => 0.0,
                    'penalty' => 0.0,
                ];
            }
        }

        // Sort mergedScoreboard
        usort(
            $mergedScoreboard,
            /**
             * @param MergedScoreboardEntry $a
             * @param MergedScoreboardEntry $b
             */
            function ($a, $b): int {
                if ($a['total']['points'] == $b['total']['points']) {
                    if ($a['total']['penalty'] == $b['total']['penalty']) {
                        return 0;
                    }

                    return ($a['total']['penalty'] > $b['total']['penalty']) ? 1 : -1;
                }

                return ($a['total']['points'] < $b['total']['points']) ? 1 : -1;
            }
        );

        /** @var list<MergedScoreboardEntry> */
        return $mergedScoreboard;
    }

    /**
     * @return array{users: list<array{accepted: bool|null, admin?: array{username?: null|string}, country: null|string, last_update: \OmegaUp\Timestamp|null, request_time: \OmegaUp\Timestamp, username: string}>, contest_alias: string}
     *
     * @omegaup-request-param string $contest_alias
     */
    public static function apiRequests(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $contest = self::validateContestAdmin($contestAlias, $r->identity);

        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        $resultAdmins =
            \OmegaUp\DAO\ProblemsetIdentityRequest::getFirstAdminForProblemsetRequest(
                $contest->problemset_id
            );
        $resultRequests =
            \OmegaUp\DAO\ProblemsetIdentityRequest::getRequestsForProblemset(
                $contest->problemset_id
            );

        $admins = [];
        $requestsAdmins = [];
        $result = [
            'contest_alias' => $contestAlias,
            'users' => [],
        ];
        foreach ($resultAdmins as $result) {
            $adminId = $result['admin_id'];
            if (!empty($adminId) && !array_key_exists($adminId, $admins)) {
                $admin = [];
                $data = \OmegaUp\DAO\Identities::findByUserId($adminId);
                if (!is_null($data)) {
                    $admin = [
                        'username' => $data->username,
                    ];
                }
                $requestsAdmins[$result['identity_id']] = $admin;
            }
        }

        $usersRequests = array_map(function ($request) use ($requestsAdmins) {
            if (isset($requestsAdmins[$request['identity_id']])) {
                $request['admin'] = $requestsAdmins[$request['identity_id']];
            }
            unset($request['identity_id']);
            return $request;
        }, $resultRequests);

        return [
            'users' => $usersRequests,
            'contest_alias' => $contestAlias,
        ];
    }

    /**
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $note
     * @omegaup-request-param mixed $resolution
     * @omegaup-request-param string $username
     */
    public static function apiArbitrateRequest(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['username'],
            'username'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['note'],
            'note'
        );

        if (is_null($r['resolution'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $contest = self::validateContestAdmin($contestAlias, $r->identity);

        $targetIdentity = \OmegaUp\DAO\Identities::findByUsername(
            $r['username']
        );
        if (is_null($targetIdentity) || is_null($targetIdentity->username)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userNotExist'
            );
        }

        $request = \OmegaUp\DAO\ProblemsetIdentityRequest::getByPK(
            $targetIdentity->identity_id,
            $contest->problemset_id
        );

        if (is_null($request)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'userNotInListOfRequests'
            );
        }

        if (is_bool($r['resolution'])) {
            $resolution = $r['resolution'];
        } else {
            $resolution = $r['resolution'] === 'true';
        }

        $request->accepted = $resolution;
        $request->extra_note = $r['note'];
        $request->last_update = new \OmegaUp\Timestamp(\OmegaUp\Time::get());

        \OmegaUp\DAO\ProblemsetIdentityRequest::update($request);

        // Save this action in the history
        \OmegaUp\DAO\ProblemsetIdentityRequestHistory::create(new \OmegaUp\DAO\VO\ProblemsetIdentityRequestHistory([
            'identity_id' => $request->identity_id,
            'problemset_id' => $contest->problemset_id,
            'time' => $request->last_update,
            'admin_id' => intval($r->user->user_id),
            'accepted' => $request->accepted,
        ]));

        self::$log->info(
            'Arbitrated contest for user, new accepted username='
                . $targetIdentity->username . ', state=' . $resolution
        );

        $response = ['status' => 'ok'];

        if (is_null($targetIdentity->user_id)) {
            return $response;
        }

        \OmegaUp\Controllers\Notification::createForCourseAccessRequest(
            $contest,
            $targetIdentity->user_id,
            $request->accepted
        );

        return $response;
    }

    /**
     * Returns ALL identities participating in a contest
     *
     * @return array{users: list<ContestUser>, groups: list<array{alias: string, name: string}>}
     *
     * @omegaup-request-param string $contest_alias
     */
    public static function apiUsers(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contest = self::validateContestAdmin($contestAlias, $r->identity);

        return [
            'users' => \OmegaUp\DAO\ProblemsetIdentities::getWithExtraInformation(
                intval($contest->problemset_id)
            ),
            'groups' => \OmegaUp\DAO\GroupRoles::getContestantGroups(
                intval($contest->problemset_id)
            ),
        ];
    }

    /**
     * Search users in contest
     *
     * @return array{results: list<ListItem>}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $query
     */
    public static function apiSearchUsers(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $contest = self::validateContestAdmin($contestAlias, $r->identity);

        $users = \OmegaUp\DAO\ProblemsetIdentities::searchUsers(
            $r->ensureString('query'),
            intval($contest->problemset_id)
        );
        $response = [];
        foreach ($users as $user) {
            $response[] = [
                'key' => $user['username'],
                'value' => $user['name'] ?? $user['username'],
            ];
        }
        return [
            'results' => $response,
        ];
    }

    /**
     * Returns all contest administrators
     *
     * @return array{admins: list<array{role: string, username: string}>, group_admins: list<array{alias: string, name: string, role: string}>}
     *
     * @omegaup-request-param string $contest_alias
     */
    public static function apiAdmins(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $contest = self::validateContestAdmin($contestAlias, $r->identity);

        return [
            'admins' => \OmegaUp\DAO\UserRoles::getContestAdmins($contest),
            'group_admins' => \OmegaUp\DAO\GroupRoles::getContestAdmins(
                $contest
            )
        ];
    }

    /**
     * Enforces rules to avoid having invalid/unactionable public contests
     */
    private static function validateContestCanBePublic(\OmegaUp\DAO\VO\Contests $contest): void {
        $problemsetExists = \OmegaUp\DAO\Problemsets::existsByPK(
            intval($contest->problemset_id)
        );
        if (!$problemsetExists) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        if ($contest->contest_for_teams) {
            // TODO: Make contest for teams able to be public, define the rules
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'contestPublicForTeamsNotSupported'
            );
        }
        // Check that contest has some problems at least 1 problem
        $problemsInProblemset = \OmegaUp\DAO\ProblemsetProblems::getRelevantProblems(
            intval($contest->problemset_id)
        );
        if (count($problemsInProblemset) < 1) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'contestPublicRequiresProblem'
            );
        }
    }

    /**
     * Update a Contest
     *
     * @return array{status: string, teamsGroupName?: string, title: string}
     *
     * @omegaup-request-param null|string $admission_mode
     * @omegaup-request-param null|string $alias
     * @omegaup-request-param bool|null $check_plagiarism
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param bool|null $contest_for_teams
     * @omegaup-request-param bool|null $default_show_all_contestants_in_scoreboard
     * @omegaup-request-param null|string $description
     * @omegaup-request-param 'detailed'|'none'|'summary'|null $feedback
     * @omegaup-request-param int $finish_time
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param int|null $penalty
     * @omegaup-request-param 'max'|'sum'|null $penalty_calc_policy
     * @omegaup-request-param 'contest_start'|'none'|'problem_open'|'runtime'|null $penalty_type
     * @omegaup-request-param float|null $points_decay_factor
     * @omegaup-request-param null|string $problems
     * @omegaup-request-param bool|null $recommended
     * @omegaup-request-param 'no'|'optional'|'required'|null $requests_user_information
     * @omegaup-request-param 'all_or_nothing'|'max_per_group'|'partial'|null $score_mode
     * @omegaup-request-param float|null $scoreboard
     * @omegaup-request-param bool|null $show_scoreboard_after
     * @omegaup-request-param int $start_time
     * @omegaup-request-param int $submissions_gap
     * @omegaup-request-param null|string $teams_group_alias
     * @omegaup-request-param null|string $title
     * @omegaup-request-param int $window_length
     */
    public static function apiUpdate(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        // Authenticate request
        $r->ensureMainUserIdentity();

        // Validate request
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contest = self::validateUpdate($r, $r->identity, $contestAlias);
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'contestNotFound'
            );
        }

        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $contest->problemset_id
        );
        $originalNeedsBasicInformation = $problemset->needs_basic_information ?? false;
        $needsBasicInformation = $r->ensureOptionalBool(
            'needs_basic_information'
        ) ?? $originalNeedsBasicInformation;

        $originalRequestsUserInformation = $problemset->requests_user_information ?? 'no';
        $requestsUserInformation = $r->ensureOptionalEnum(
            'requests_user_information',
            ['no', 'optional', 'required']
        ) ?? $originalRequestsUserInformation;

        $contestForTeams = $r->ensureOptionalBool('contest_for_teams');
        if (
            !is_null(
                $contestForTeams
            ) && $contest->contest_for_teams !== $contestForTeams
        ) {
            if ($contest->contest_for_teams) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'contestForTeamsCanNotChangeToContest'
                );
            }
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'contestCanNotChangeToContestForTeams'
            );
        }

        self::forbiddenInVirtual($contest);

        $updateProblemset = true;
        $updateRequests = false;

        // Handle recommended flag - only available for admins and support team
        $recommendedValue = $r->ensureOptionalBool('recommended');
        if (!is_null($recommendedValue)) {
            if (\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
                $contest->recommended = $recommendedValue;
            } else {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'userNotAllowed'
                );
            }
        }

        // Update contest DAO
        if (!is_null($r['admission_mode'])) {
            \OmegaUp\Validators::validateOptionalInEnum(
                $r['admission_mode'],
                'admission_mode',
                [
                    'public',
                    'private',
                    'registration',
                ]
            );
            // If going public
            if (self::isPublic(strval($r['admission_mode']))) {
                self::validateContestCanBePublic($contest);
            }

            $contest->admission_mode = $r['admission_mode'];
            $updateRequests = $r['admission_mode'] === 'registration';

            $contest->default_show_all_contestants_in_scoreboard = $r->ensureOptionalBool(
                'default_show_all_contestants_in_scoreboard'
            ) ?? false;

            // Problemset does not update when admission mode change
            $updateProblemset = (
                $originalNeedsBasicInformation !== $needsBasicInformation ||
                $originalRequestsUserInformation !== $requestsUserInformation
            );
        }
        $valueProperties = [
            'title',
            'description',
            'start_time',
            'finish_time',
            'window_length' => [
                'transform' => fn(?int $value): ?int => empty(
                    $value
                ) ? null : $value,
            ],
            'scoreboard',
            'points_decay_factor',
            'score_mode',
            'submissions_gap',
            'feedback',
            'penalty' => ['transform' => fn(string $value): int => max(
                0,
                intval($value)
            )],
            'penalty_type',
            'penalty_calc_policy',
            'show_scoreboard_after' => [
                'transform' => fn(string $value): bool => boolval(
                    filter_var($value, FILTER_VALIDATE_BOOLEAN)
                ),
            ],
            'languages' => [
                'transform' =>
                /** @param list<string>|string $value */
                function ($value): ?string {
                    if (!is_array($value)) {
                        return $value ?: null;
                    }
                    return join(',', $value);
                }
            ],
            'admission_mode',
            'check_plagiarism',
        ];
        self::updateValueProperties($r, $contest, $valueProperties);

        $originalContest = \OmegaUp\DAO\Contests::getByPK(
            intval($contest->contest_id)
        );
        if (is_null($originalContest)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'contestNotFound'
            );
        }
        $result = [
            'status' => 'ok',
        ];

        // Push changes
        try {
            // Begin a new transaction
            \OmegaUp\DAO\DAO::transBegin();

            // Save the contest object with data sent by user to the database
            self::updateContest($contest, $originalContest, $r->identity);
            $result['title'] = strval($contest->title);

            if ($updateProblemset) {
                // Save the problemset object with data sent by user to the database
                $problemset = \OmegaUp\DAO\Problemsets::getByPK(
                    intval($contest->problemset_id)
                );
                if (is_null($problemset) || is_null($problemset->acl_id)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'problemsetNotFound'
                    );
                }
                $problemset->needs_basic_information = $needsBasicInformation;
                $problemset->requests_user_information = $requestsUserInformation;
                \OmegaUp\DAO\Problemsets::update($problemset);
                $teamsGroupAlias = $r->ensureOptionalString(
                    'teams_group_alias'
                );

                if ($originalContest->contest_for_teams) {
                    if (is_null($teamsGroupAlias)) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException(
                            'teamsGroupAliasMustBeRequired'
                        );
                    }
                    $teamsGroup = \OmegaUp\DAO\TeamGroups::getByAlias(
                        $teamsGroupAlias
                    );
                    if (is_null($teamsGroup)) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException(
                            'invalidParameters',
                            'teams_group'
                        );
                    }

                    \OmegaUp\DAO\TeamsGroupRoles::deleteAllTeamGroupsForAclId(
                        $problemset->acl_id
                    );

                    \OmegaUp\DAO\TeamsGroupRoles::create(
                        new \OmegaUp\DAO\VO\TeamsGroupRoles([
                            'acl_id' => $problemset->acl_id,
                            'team_group_id' => $teamsGroup->team_group_id,
                            'role_id' => \OmegaUp\Authorization::CONTESTANT_ROLE,
                        ])
                    );
                    if (!is_null($teamsGroup->name)) {
                        $result['teamsGroupName'] = $teamsGroup->name;
                    }
                }
            }

            if ($updateRequests) {
                // Save the problemset object with data sent by user to the database
                $problemset = \OmegaUp\DAO\Problemsets::getByPK(
                    intval($contest->problemset_id)
                );
                if (
                    is_null($problemset)
                    || is_null($problemset->problemset_id)
                ) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'problemsetNotFound'
                    );
                }
                // Get the list of contestants
                $identities = \OmegaUp\DAO\ProblemsetIdentities::getIdentitiesByProblemset(
                    $problemset->problemset_id
                );
                // Extract IDs
                $identitiesIDs = array_map(
                    /**
                     * @param array{access_time: \OmegaUp\Timestamp|null, country_id: null|string, email: null|string, end_time: \OmegaUp\Timestamp|null, identity_id: int, is_invited: bool, user_id: int|null, username: string} $identity
                     */
                    fn($identity) => $identity['identity_id'],
                    $identities
                );
                self::preAcceptAccessRequest(
                    $contest,
                    $identitiesIDs,
                    $r->user
                );
            }

            // End transaction
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();

            throw $e;
        }

        // Expire contest-info cache
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::CONTEST_INFO,
            $contestAlias
        );

        // Expire contest scoreboard cache
        \OmegaUp\Scoreboard::invalidateScoreboardCache(
            \OmegaUp\ScoreboardParams::fromContest(
                $contest
            )
        );

        // Expire contest-list cache
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_PUBLIC);
        \OmegaUp\Cache::invalidateAllKeys(
            \OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN
        );

        self::$log->info("Contest updated (alias): {$contestAlias}");

        return $result;
    }

    /**
     * Update Contest end time for an identity when window_length
     * option is turned on
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param \OmegaUp\Timestamp $end_time
     * @omegaup-request-param string $username
     */
    public static function apiUpdateEndTimeForIdentity(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contest = self::validateContestAdmin($contestAlias, $r->identity);

        \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['username']
        );

        $problemsetIdentity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        if (is_null($problemsetIdentity)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetIdentityNotFound'
            );
        }
        $problemsetIdentity->end_time = $r->ensureTimestamp('end_time');
        \OmegaUp\DAO\ProblemsetIdentities::update($problemsetIdentity);

        return [
            'status' => 'ok',
        ];
    }

    /**
     * This function reviews changes in penalty type, admission mode, finish
     * time and window length to recalculate information previously stored
     */
    private static function updateContest(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Contests $originalContest,
        \OmegaUp\DAO\VO\Identities $identity,
        ?string $teamsGroupAlias = null
    ): void {
        if ($originalContest->admission_mode !== $contest->admission_mode) {
            $timestamp = new \OmegaUp\Timestamp(\OmegaUp\Time::get());
            \OmegaUp\DAO\ContestLog::create(new \OmegaUp\DAO\VO\ContestLog([
                'contest_id' => $contest->contest_id,
                'user_id' => $identity->user_id,
                'from_admission_mode' => $originalContest->admission_mode,
                'to_admission_mode' => $contest->admission_mode,
                'time' => $timestamp,
            ]));
            $contest->last_updated = $timestamp;
        }
        if (
            ($originalContest->finish_time != $contest->finish_time) ||
            ($originalContest->window_length !== $contest->window_length)
        ) {
            if (!is_null($contest->window_length)) {
                // When window length is enabled, end time value is access time + window length
                \OmegaUp\DAO\ProblemsetIdentities::recalculateEndTimeForProblemsetIdentities(
                    $contest
                );
            } else {
                \OmegaUp\DAO\ProblemsetIdentities::recalculateEndTimeAsFinishTime(
                    $contest
                );
            }
        }

        \OmegaUp\DAO\Contests::update($contest);
        if ($originalContest->penalty_type == $contest->penalty_type) {
            return;
        }
        \OmegaUp\DAO\Runs::recalculatePenaltyForContest($contest);
    }

    /**
     * Returns all runs for a contest
     *
     * @return array{runs: list<Run>, totalRuns: int}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param 'c11-clang'|'c11-gcc'|'cat'|'cpp11-clang'|'cpp11-gcc'|'cpp17-clang'|'cpp17-gcc'|'cpp20-clang'|'cpp20-gcc'|'cs'|'go'|'hs'|'java'|'js'|'kj'|'kp'|'kt'|'lua'|'pas'|'py2'|'py3'|'rb'|'rs'|null $language
     * @omegaup-request-param int|null $offset
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param int|null $rowcount
     * @omegaup-request-param 'compiling'|'new'|'ready'|'running'|'waiting'|null $status
     * @omegaup-request-param null|string $username
     * @omegaup-request-param 'AC'|'CE'|'JE'|'MLE'|'NO-AC'|'OLE'|'PA'|'RFE'|'RTE'|'TLE'|'VE'|'WA'|null $verdict
     */
    public static function apiRuns(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        // Contest information
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contest = self::validateContestAdmin($contestAlias, $r->identity);
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        // Get user if we have something in username
        $username = $r->ensureOptionalString('username');

        $identity = null;
        if (!is_null($username)) {
            $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
                $username
            );
        }

        $problem = null;
        // Check filter by problem, is optional
        $problemAlias = $r->ensureOptionalString(
            'problem_alias',
            required: false,
            validator: fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        if (!is_null($problemAlias)) {
            $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
            if (is_null($problem)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }
        }

        $languages = array_keys(
            \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES()
        );

        // Get our runs
        return self::getAllRuns(
            $contest->problemset_id,
            $contest->score_mode,
            $r->ensureOptionalEnum('status', \OmegaUp\Controllers\Run::STATUS),
            $r->ensureOptionalEnum(
                'verdict',
                \OmegaUp\Controllers\Run::VERDICTS
            ),
            !is_null($problem) ? $problem->problem_id : null,
            $r->ensureOptionalEnum('language', $languages),
            !is_null($identity) ? $identity->identity_id : null,
            max($r->ensureOptionalInt('offset') ?? 0, 0),
            $r->ensureOptionalInt('rowcount') ?? 100
        );
    }

    /**
     * @return array{runs: list<Run>, totalRuns: int}
     */
    private static function getAllRuns(
        int $problemsetId,
        string $scoreMode,
        ?string $status = null,
        ?string $verdict = null,
        ?int $problemId = null,
        ?string $language = null,
        ?int $identityId = null,
        ?int $offset = 0,
        ?int $rowCount = 100
    ): array {
        // Get our runs
        [
            'runs' => $runs,
            'totalRuns' => $totalRuns,
        ] = \OmegaUp\DAO\Runs::getAllRuns(
            $problemsetId,
            $status,
            $verdict,
            $problemId,
            $language,
            $identityId,
            $offset,
            $rowCount
        );

        $allRuns = [];
        foreach ($runs as $run) {
            unset($run['run_id']);
            if ($scoreMode === 'partial' || $run['score'] == 1) {
                $run['contest_score'] = round(
                    floatval(
                        $run['contest_score']
                    ),
                    2
                );
                $run['score'] = round(floatval($run['score']), 4);
            } elseif ($scoreMode === 'all_or_nothing') {
                $run['contest_score'] = 0.0;
                $run['score'] = 0.0;
            }
            $allRuns[] = $run;
        }

        return [
            'runs' => $allRuns,
            'totalRuns' => $totalRuns,
        ];
    }

    /**
     * Stats of a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{total_runs: int, pending_runs: list<string>, max_wait_time: \OmegaUp\Timestamp|null, max_wait_time_guid: null|string, verdict_counts: array<string, int>, distribution: array<int, int>, size_of_bucket: float, total_points: float}
     *
     * @omegaup-request-param null|string $contest_alias
     */
    public static function apiStats(\OmegaUp\Request $r): array {
        // Get user
        $r->ensureIdentity();
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contest = self::validateContestAdmin($contestAlias, $r->identity);
        return self::getStats($contest, $r->identity);
    }

    /**
     * @return array{templateProperties: array{payload: StatsPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param null|string $contest_alias
     */
    public static function getStatsDataForTypeScript(\OmegaUp\Request $r) {
        // Get user
        $r->ensureIdentity();
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contest = self::validateContestAdmin($contestAlias, $r->identity);
        return [
            'templateProperties' => [
                'payload' => array_merge(
                    [
                        'alias' => $contestAlias,
                        'entity_type' => 'contest',
                    ],
                    self::getStats($contest, $r->identity)
                ),
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleContestStats'
                ),
            ],
            'entrypoint' => 'common_stats',
        ];
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: ScoreboardMergePayload, title: \OmegaUp\TranslationString}}
     */
    public static function getScoreboardMergeDetailsForTypeScript(
        \OmegaUp\Request $r
    ) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        // Get user
        $r->ensureIdentity();

        [
            'contests' => $contests,
        ] = self::getContestList(
            $r->identity,
            query: null,
            page: 1,
            pageSize: 100,
            activeContests: \OmegaUp\DAO\Enum\ActiveStatus::ALL,
            recommended: \OmegaUp\DAO\Enum\RecommendedStatus::ALL
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'contests' => $contests,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleScoreboardmerge'
                ),
            ],
            'entrypoint' => 'contest_scoreboardmerge',
        ];
    }

    /**
     * @return array{total_runs: int, pending_runs: list<string>, max_wait_time: \OmegaUp\Timestamp|null, max_wait_time_guid: null|string, verdict_counts: array<string, int>, distribution: array<int, int>, size_of_bucket: float, total_points: float}
     */
    private static function getStats(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        $pendingRunGuids = \OmegaUp\DAO\Runs::getPendingRunGuidsOfProblemset(
            intval(
                $contest->problemset_id
            )
        );

        // Count of pending runs (int)
        $totalRunsCount = \OmegaUp\DAO\Submissions::countTotalSubmissionsOfProblemset(
            intval($contest->problemset_id)
        );

        // Wait time
        $waitTimeArray = \OmegaUp\DAO\Runs::getLargestWaitTimeOfProblemset(
            intval(
                $contest->problemset_id
            )
        );

        // List of verdicts
        $verdictCounts = [];

        foreach (\OmegaUp\Controllers\Run::VERDICTS as $verdict) {
            $verdictCounts[$verdict] = \OmegaUp\DAO\Runs::countTotalRunsOfProblemsetByVerdict(
                intval($contest->problemset_id),
                $verdict
            );
        }

        // Get max points posible for contest
        $totalPoints = \OmegaUp\DAO\ProblemsetProblems::getMaxPointsByProblemset(
            intval($contest->problemset_id)
        );

        // Get scoreboard to calculate distribution
        $distribution = [];
        for ($i = 0; $i < 101; $i++) {
            $distribution[$i] = 0;
        }

        $sizeOfBucket = $totalPoints / 100;
        if ($sizeOfBucket > 0) {
            $problemset = \OmegaUp\DAO\Problemsets::getByPK(
                intval($contest->problemset_id)
            );
            if (is_null($problemset)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemsetNotFound'
                );
            }
            $scoreboardResponse = self::getScoreboard(
                $contest,
                $problemset,
                $identity
            );
            foreach ($scoreboardResponse['ranking'] as $results) {
                $distribution[intval(
                    $results['total']['points'] / $sizeOfBucket
                )]++;
            }
        }

        return [
            'total_runs' => $totalRunsCount,
            'pending_runs' => $pendingRunGuids,
            'max_wait_time' => empty(
                $waitTimeArray
            ) ? null : $waitTimeArray['time'],
            'max_wait_time_guid' => empty(
                $waitTimeArray
            ) ? null : $waitTimeArray['guid'],
            'verdict_counts' => $verdictCounts,
            'distribution' => $distribution,
            'size_of_bucket' => $sizeOfBucket,
            'total_points' => $totalPoints,
        ];
    }

    /**
     * Returns a detailed report of the contest
     *
     * @return array{finish_time: \OmegaUp\Timestamp|null, problems: list<array{alias: string, order: int}>, ranking: list<ContestReport>, start_time: \OmegaUp\Timestamp, time: \OmegaUp\Timestamp, title: string}
     *
     * @omegaup-request-param null|string $auth_token
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $filterBy
     */
    public static function apiReport(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $authToken = $r->ensureOptionalString('auth_token');

        // Check the filter if we have one
        $filterBy = $r->ensureOptionalString('filterBy');

        return self::getContestReportDetails(
            $r->identity,
            $contestAlias,
            $authToken,
            $filterBy
        )['scoreboard'];
    }

    /**
     * Returns a detailed report of the contest. Only Admins can get the report
     *
     * @return array{contest: \OmegaUp\DAO\VO\Contests,scoreboard: array{finish_time: \OmegaUp\Timestamp|null, problems: list<array{alias: string, order: int}>, ranking: list<array{country: null|string, is_invited: bool, name: string|null, place?: int, problems: list<ScoreboardRankingProblem>, total: array{penalty: float, points: float}, username: string}>, start_time: \OmegaUp\Timestamp, time: \OmegaUp\Timestamp, title: string}}
     */
    private static function getContestReportDetails(
        \OmegaUp\DAO\VO\Identities $identity,
        string $contestAlias,
        ?string $authToken,
        ?string $filterBy
    ): array {
        $contest = self::validateContestAdmin($contestAlias, $identity);

        $params = \OmegaUp\ScoreboardParams::fromContest($contest);
        $params->admin = true;
        $params->auth_token = $authToken;
        $scoreboard = new \OmegaUp\Scoreboard($params);

        return [
            'scoreboard' => $scoreboard->generate(
                withRunDetails: true,
                sortByName: true,
                filterUsersBy: $filterBy
            ),
            'contest' => $contest,
        ];
    }

    /**
     * Gets all details to show the report
     *
     * @return array{entrypoint: string, templateProperties: array{payload: ContestReportDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param null|string $auth_token
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $filterBy
     */
    public static function getContestReportDetailsForTypeScript(
        \OmegaUp\Request $r
    ) {
        $r->ensureIdentity();
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $authToken = $r->ensureOptionalString('auth_token');

        // Check the filter if we have one
        $filterBy = $r->ensureOptionalString('filterBy');

        $response = self::getContestReportDetails(
            $r->identity,
            $contestAlias,
            $authToken,
            $filterBy
        );
        $contest = $response['contest'];
        $contestReport = $response['scoreboard']['ranking'];

        foreach ($contestReport as &$user) {
            if (!isset($user['problems'])) {
                continue;
            }
            foreach ($user['problems'] as &$problem) {
                if (
                    !isset($problem['run_details']) ||
                    !isset($problem['run_details']['details']) ||
                    !isset($problem['run_details']['details']['groups'])
                ) {
                    continue;
                }
                foreach ($problem['run_details']['details']['groups'] as &$group) {
                    foreach ($group['cases'] as &$case) {
                        $case['meta']['memory'] =
                            floatval($case['meta']['memory']) / 1024.0 / 1024.0;
                    }
                }
            }
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'contestReport' => $contestReport,
                    'contestAlias' => $contestAlias,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleContestReport',
                    [
                        'contestName' => $contest->title,
                    ],
                ),
            ],
            'entrypoint' => 'contest_report',
        ];
    }

    /**
     * @return array{templateProperties: array{payload: ContestListPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int|null $page
     * @omegaup-request-param int|null $page_size
     * @omegaup-request-param null|string $sort_order
     * @omegaup-request-param null|string $filter
     * @omegaup-request-param null|string $tab_name
     * @omegaup-request-param null|string $query
     */
    public static function getContestListDetailsForTypeScript(
        \OmegaUp\Request $r
    ) {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
            $r->identity = null;
        }

        $page = $r->ensureOptionalInt('page') ?? 1; // The default page is 1 always
        $pageSize = $r->ensureOptionalInt(
            'page_size'
        ) ?? \OmegaUp\Controllers\Contest::CONTEST_LIST_PAGE_SIZE;
        $query = $r->ensureOptionalString(
            key: 'query',
            required: false,
            validator: fn(string $query) => \OmegaUp\Validators::stringOfLengthInRange(
                $query,
                0,
                250
            )
        );
        $order = $r->ensureOptionalEnum(
            'sort_order',
            \OmegaUp\DAO\Enum\ContestOrderStatus::NAME_FOR_STATUS,
            required: false
        );
        $orderBy = \OmegaUp\DAO\Enum\ContestOrderStatus::convertToInt(
            fieldName: 'order',
            field: $order,
            defaultValue: \OmegaUp\DAO\Enum\ContestOrderStatus::NONE
        );
        $filter = $r->ensureOptionalEnum(
            'filter',
            \OmegaUp\DAO\Enum\ContestFilterStatus::NAME_FOR_STATUS
        );
        $activeFilter = \OmegaUp\DAO\Enum\ContestFilterStatus::convertToInt(
            fieldName: 'filter',
            field: $filter,
            defaultValue: \OmegaUp\DAO\Enum\ContestFilterStatus::ALL
        );
        $tabName = $r->ensureOptionalEnum(
            'tab_name',
            \OmegaUp\DAO\Enum\ContestTabStatus::NAME_FOR_STATUS
        );
        $activeTab = \OmegaUp\DAO\Enum\ContestTabStatus::convertToInt(
            fieldName: 'tab_name',
            field: $tabName,
            defaultValue: \OmegaUp\DAO\Enum\ContestTabStatus::CURRENT
        );

        $recommended = \OmegaUp\DAO\Enum\RecommendedStatus::ALL;
        if ($activeFilter === \OmegaUp\DAO\Enum\ContestFilterStatus::ONLY_RECOMMENDED) {
            $recommended = \OmegaUp\DAO\Enum\RecommendedStatus::RECOMMENDED;
        }

        $participating = null;
        if ($activeFilter === \OmegaUp\DAO\Enum\ContestFilterStatus::SIGNED_UP) {
            $participating = \OmegaUp\DAO\Enum\ParticipatingStatus::YES;
        }

        [
            'contests' => $contests,
            'count' => $count,
        ] = self::getContestList(
            $r->identity,
            $query,
            $page,
            $pageSize,
            $activeTab,
            $recommended,
            public: false,
            participating: $participating,
            orderBy: $orderBy
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'contests' => $contests,
                    'countContests' => $count,
                    'query' => $query,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleContestList'
                ),
            ],
            'entrypoint' => 'arena_contest_list',
        ];
    }

    /**
     * @return array{templateProperties: array{payload: ContestVirtualDetailsPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param null|string $contest_alias
     */
    public static function getContestVirtualDetailsForTypeScript(\OmegaUp\Request $r) {
        // Authenticate user
        $r->ensureMainUserIdentity();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        [
            'contest' => $contest,
            'contestWithDirector' => $contestWithDirector,
        ] = self::validateContestWithDirector($contestAlias);
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'contest' => self::getPublicDetails(
                        $contestWithDirector,
                        $r->identity
                    ),
                ],
                'title' => new \OmegaUp\TranslationString('virtualContest'),
            ],
            'entrypoint' => 'contest_virtual',
        ];
    }

    /**
     * Generates a CSV for contest report
     *
     * @omegaup-request-param null|string $auth_token
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $filterBy
     */
    public static function apiCsvReport(\OmegaUp\Request $r): void {
        $r->ensureIdentity();
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $authToken = $r->ensureOptionalString('auth_token');

        // Check the filter if we have one
        $filterBy = $r->ensureOptionalString('filterBy');

        // Get full Report API of the contest
        $response = self::getContestReportDetails(
            $r->identity,
            $contestAlias,
            $authToken,
            $filterBy
        );
        $contestReport = $response['scoreboard'];
        $contest = $response['contest'];

        // Get problem stats for each contest problem so we can
        // have the full list of cases
        $problemStats = [];
        $problemAliasStats = [];
        $i = 0;
        foreach ($contestReport['problems'] as $entry) {
            $problemAlias = $entry['alias'];
            $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
            if (is_null($problem)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }

            $problemStats[$i] = \OmegaUp\Controllers\Problem::getStats(
                $problem,
                $r->identity
            );
            $problemAliasStats[$problemAlias] = $problemStats[$i];

            $i++;
        }

        // Build a csv
        $csvData = [];

        // Build titles
        $csvRow = [
            'username',
        ];
        foreach ($contestReport['problems'] as $entry) {
            foreach ($problemAliasStats[$entry['alias']]['cases_stats'] as $caseName => $counts) {
                $csvRow[] = strval($caseName);
            }
            $csvRow[] = "{$entry['alias']} total";
        }
        $csvRow[] = 'total';
        $csvData[] = $csvRow;

        foreach ($contestReport['ranking'] as $userData) {
            $csvRow = [
                $userData['username'],
            ];

            foreach ($userData['problems'] as $key => $problemData) {
                // If the user don't have these details then he didn't submit,
                // we need to fill the report with 0s for completeness
                if (
                    empty($problemData['run_details']['cases'])
                ) {
                    for (
                        $i = 0;
                        $i < count(
                            $problemStats[$key]['cases_stats']
                        );
                        $i++
                    ) {
                        $csvRow[] = '0';
                    }

                    // And adding the total for this problem
                    $csvRow[] = '0';
                } else {
                    // for each case
                    foreach ($problemData['run_details']['cases'] as $caseData) {
                        // If case is correct
                        if (
                            strcmp($caseData['meta']['verdict'], 'OK') === 0 &&
                            isset($caseData['out_diff']) &&
                            strcmp($caseData['out_diff'], '') === 0
                        ) {
                            $csvRow[] = '1';
                        } else {
                            $csvRow[] = '0';
                        }
                    }

                    $csvRow[] = $problemData['points'];
                }
            }
            $csvRow[] = $userData['total']['points'];
            $csvData[] = $csvRow;
        }

        // Set headers to auto-download file
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header(
            "Content-Disposition: attachment;filename={$contest->alias}_report.csv"
        );
        header('Content-Transfer-Encoding: binary');

        // Write contents to a csv raw string
        $out = fopen('php://output', 'w');
        foreach ($csvData as $csvRow) {
            fputcsv($out, \OmegaUp\Controllers\Contest::escapeCsv($csvRow));
        }
        fclose($out);

        // Since all the headers and response have been sent, make the API
        // caller to exit quietly.
        throw new \OmegaUp\Exceptions\ExitException();
    }

    /**
     * @param list<scalar> $csvRow
     *
     * @return list<string>
     */
    private static function escapeCsv($csvRow): array {
        $escapedRow = [];
        foreach ($csvRow as $field) {
            if (is_string($field) && $field[0] == '=') {
                $escapedRow[] = "'" . $field;
            } else {
                $escapedRow[] = strval($field);
            }
        }
        return $escapedRow;
    }

    /**
     * @omegaup-request-param string $contest_alias
     */
    public static function apiDownload(\OmegaUp\Request $r): void {
        $r->ensureIdentity();
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $contest = self::validateContestAdmin($contestAlias, $r->identity);
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        include_once __DIR__ . '/../../libs/third_party/ZipStream.php';
        $zip = new \ZipStream("{$contestAlias}.zip");
        \OmegaUp\Controllers\Problemset::downloadRuns(
            $contest->problemset_id,
            $zip
        );
        $zip->finish();

        // Since all the headers and response have been sent, make the API
        // caller to exit quietly.
        throw new \OmegaUp\Exceptions\ExitException();
    }

    /**
     * @throws \OmegaUp\Exceptions\UnauthorizedException
     *
     * Given a contest_alias and user_id, returns the role of the user within
     * the context of a contest.
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $token
     *
     * @return array{admin: bool}
     */
    public static function apiRole(\OmegaUp\Request $r): array {
        try {
            $r->ensureidentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $r->identity = null;
        }
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $token = $r->ensureOptionalString('token');
        if ($contestAlias === 'all-events') {
            if (is_null($r->identity)) {
                throw new \OmegaUp\Exceptions\UnauthorizedException();
            }
            if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
                return [
                    'status' => 'ok',
                    'admin' => true
                ];
            }
        }

        $response = self::validateDetails($contestAlias, $r->identity, $token);

        return [
            'admin' => $response['contest_admin']
        ];
    }

    /**
     * Given a contest_alias, sets the recommended flag on/off.
     * Only omegaUp admins and support team members can call this API.
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param bool|null $value
     */
    public static function apiSetRecommended(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        // Validate & get contest_alias
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        $contest->recommended = $r->ensureBool('value');
        \OmegaUp\DAO\Contests::update($contest);

        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::CONTEST_INFO,
            $contestAlias
        );

        return ['status' => 'ok'];
    }

    /**
     * Return users who participate in a contest, as long as contest admin
     * has chosen to ask for users information and contestants have
     * previously agreed to share their information.
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{contestants: list<Contestant>}
     *
     * @omegaup-request-param string $contest_alias
     */
    public static function apiContestants(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contest = self::validateContestAdmin($contestAlias, $r->identity);

        if (is_null($contest->contest_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        if (
            !\OmegaUp\DAO\Contests::requestsUserInformation(
                $contest->contest_id
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'contestInformationNotRequired'
            );
        }

        // Get contestants info
        /** @var int $contest->contest_id */
        $contestants = \OmegaUp\DAO\Contests::getContestantsInfo(
            $contest->contest_id
        );

        return [
            'status' => 'ok',
            'contestants' => $contestants,
        ];
    }

    /**
     * Archives or Unarchives a contest if user is the creator
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param bool|null $archive
     */
    public static function apiArchive(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check whether contest exists
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn(string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($contest) || is_null($contest->contest_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        if (!\OmegaUp\Authorization::canEditContest($r->identity, $contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $contest->archived = $r->ensureOptionalBool('archive') ?? true;
        \OmegaUp\DAO\Contests::update($contest);

        // Expire contest-list cache
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_PUBLIC);
        \OmegaUp\Cache::invalidateAllKeys(
            \OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN
        );

        return [
            'status' => 'ok',
        ];
    }

    public static function isPublic(string $admissionMode): bool {
        return $admissionMode !== \OmegaUp\CourseParams::COURSE_ADMISSION_MODE_PRIVATE;
    }
}
