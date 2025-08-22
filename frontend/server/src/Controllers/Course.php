<?php

namespace OmegaUp\Controllers;

/**
 *  CourseController
 *
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 * @psalm-type Progress=array{score: float, max_score: float}
 * @psalm-type AssignmentProgress=array<string, Progress>
 * @psalm-type ProblemQualityPayload=array{canNominateProblem: bool, dismissed: bool, dismissedBeforeAc: bool, language?: string, nominated: bool, nominatedBeforeAc: bool, problemAlias: string, solved: bool, tried: bool}
 * @psalm-type ProblemsetProblem=array{accepted: int, accepts_submissions: bool, alias: string, commit: string, difficulty: float, has_submissions: bool, input_limit: int, is_extra_problem: bool, languages: string, letter?: string, order: int, points: float, problem_id?: int, quality_payload?: ProblemQualityPayload, quality_seal: bool, submissions: int, title: string, version: string, visibility: int, visits: int}
 * @psalm-type IdentityRequest=array{accepted: bool|null, admin?: array{name: null|string, username: string}, classname: string, country: null|string, country_id: null|string, last_update: \OmegaUp\Timestamp|null, name: null|string, request_time: \OmegaUp\Timestamp, username: string}
 * @psalm-type CourseAdmin=array{role: string, username: string}
 * @psalm-type Clarification=array{answer: null|string, assignment_alias?: null|string, author: string, clarification_id: int, contest_alias?: null|string, message: string, problem_alias: string, public: bool, receiver: null|string, time: \OmegaUp\Timestamp}
 * @psalm-type CourseGroupAdmin=array{alias: string, name: string, role: string}
 * @psalm-type CourseAssignment=array{alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, has_runs: bool, max_points: float, name: string, opened: bool, order: int, problemCount: int, problemset_id: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}
 * @psalm-type CourseDetails=array{admission_mode: string, alias: string, archived: boolean, assignments: list<CourseAssignment>, clarifications: list<Clarification>, description: string, objective: string|null, level: string|null, finish_time: \OmegaUp\Timestamp|null, is_admin: bool, is_curator: bool, is_teaching_assistant: bool, languages: list<string>|null, name: string, needs_basic_information: bool, recommended: bool, requests_user_information: string, school_id: int|null, school_name: null|string, show_scoreboard: bool, start_time: \OmegaUp\Timestamp, student_count?: int, teaching_assistant_enabled: bool, unlimited_duration: bool}
 * @psalm-type RunMetadata=array{verdict: string, time: float, sys_time: int, wall_time: float, memory: int}
 * @psalm-type Run=array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, execution: null|string, guid: string, language: string, memory: int, output: null|string, penalty: int, runtime: int, score: float, score_by_group?: array<string, float|null>, status: string, status_memory: null|string, status_runtime: null|string, submit_delay: int, suggestions?: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}
 * @psalm-type CaseResult=array{contest_score: float, max_score: float, meta: RunMetadata, name: string, out_diff?: string, score: float, verdict: string}
 * @psalm-type ScoreboardRankingProblemDetailsGroup=array{cases: list<array{meta: RunMetadata}>}
 * @psalm-type ScoreboardRankingProblem=array{alias: string, penalty: float, percent: float, pending?: int, place?: int, points: float, run_details?: array{cases?: list<CaseResult>, details: array{groups: list<ScoreboardRankingProblemDetailsGroup>}}, runs: int}
 * @psalm-type ScoreboardRankingEntry=array{classname: string, country: string, is_invited: bool, name: null|string, place?: int, problems: list<ScoreboardRankingProblem>, total: array{penalty: float, points: float}, username: string}
 * @psalm-type Scoreboard=array{finish_time: \OmegaUp\Timestamp|null, problems: list<array{alias: string, order: int}>, ranking: list<ScoreboardRankingEntry>, start_time: \OmegaUp\Timestamp, time: \OmegaUp\Timestamp, title: string}
 * @psalm-type ScoreboardEvent=array{classname: string, country: string, delta: float, is_invited: bool, total: array{points: float, penalty: float}, name: null|string, username: string, problem: array{alias: string, points: float, penalty: float}}
 * @psalm-type FilteredCourse=array{accept_teacher: bool|null, admission_mode: string, alias: string, assignments: list<CourseAssignment>, counts: array<string, int>, description: string, finish_time: \OmegaUp\Timestamp|null, is_open: bool, name: string, progress?: float, school_name: null|string, start_time: \OmegaUp\Timestamp}
 * @psalm-type CoursesList=array{admin: list<FilteredCourse>, public: list<FilteredCourse>, student: list<FilteredCourse>, archived: list<FilteredCourse>, teachingAssistant: list<FilteredCourse>}
 * @psalm-type CourseCloneDetailsPayload=array{creator: array{classname: string, username: string}, details: CourseDetails, token: null|string}
 * @psalm-type CoursesByTimeType=array{courses: list<FilteredCourse>, timeType: string}
 * @psalm-type CoursesByAccessMode=array{accessMode: string, activeTab: string, filteredCourses: array{current: CoursesByTimeType, past: CoursesByTimeType}}
 * @psalm-type CourseProblemTried=array{alias: string, title: string, username: string}
 * @psalm-type CourseSubmissionsListPayload=array{solvedProblems: array<string, list<CourseProblemTried>>, unsolvedProblems: array<string, list<CourseProblemTried>>}
 * @psalm-type AdminCourses=array{admin: array{accessMode: string, activeTab: string, filteredCourses: array{current: CoursesByTimeType, past: CoursesByTimeType, archived: CoursesByTimeType, teachingAssistant: CoursesByTimeType}}}
 * @psalm-type StudentCourses=array<string, CoursesByAccessMode>
 * @psalm-type CourseListMinePayload=array{courses: AdminCourses}
 * @psalm-type CourseProblemVerdict=array{assignment_alias: string, problem_alias: string, problem_id: int, runs: int, verdict: null|string}
 * @psalm-type CourseProblemStatistics=array{assignment_alias: string, average: float, avg_runs: float, completed_score_percentage: float, high_score_percentage: float, low_score_percentage: float, max_points: float, maximum: float, minimum: float, problem_alias: string, variance: float}
 * @psalm-type CourseStatisticsPayload=array{course: CourseDetails, problemStats: list<CourseProblemStatistics>, verdicts: list<CourseProblemVerdict>}
 * @psalm-type CourseStudent=array{name: null|string, username: string}
 * @psalm-type StudentProgress=array{classname: string, country_id: null|string, name: string|null, points: array<string, array<string, float>>, progress: array<string, array<string, float>>, score: array<string, array<string, float>>, username: string}
 * @psalm-type StudentProgressInCourse=array{assignments: array<string, array{problems: array<string, array{progress: float, score: float}>, progress: float, score: float}>, classname: string, country_id: null|string, courseProgress: float, courseScore: float, name: null|string, username: string}
 * @psalm-type AssignmentsProblemsPoints=array{alias: string, extraPoints: float, name: string, points: float, problems: list<array{alias: string, title: string, isExtraProblem: bool, order: int, points: float}>, order: int}
 * @psalm-type CourseNewPayload=array{is_admin: bool, hasVisitedSection: bool, is_curator: bool, languages: array<string, string>}
 * @psalm-type CourseEditPayload=array{admins: list<CourseAdmin>, teachingAssistants: list<CourseAdmin>, allLanguages: array<string, string>, assignmentProblems: list<ProblemsetProblem>, course: CourseDetails, groupsAdmins: list<CourseGroupAdmin>, groupsTeachingAssistants: list<CourseGroupAdmin>, identityRequests: list<IdentityRequest>, selectedAssignment: CourseAssignment|null, students: list<CourseStudent>, tags: list<string>}
 * @psalm-type StudentsProgressPayload=array{course: CourseDetails, assignmentsProblems: list<AssignmentsProblemsPoints>, students: list<StudentProgressInCourse>, totalRows: int, page: int, length: int, pagerItems: list<PageItem>}
 * @psalm-type SubmissionFeedbackThread=array{author: string, authorClassname: string, submission_feedback_thread_id: int, text: string, timestamp: \OmegaUp\Timestamp}
 * @psalm-type SubmissionFeedback=array{author: string, author_classname: string, feedback: string, date: \OmegaUp\Timestamp, range_bytes_end: int|null, range_bytes_start: int|null, submission_feedback_id: int, feedback_thread?: list<SubmissionFeedbackThread>}
 * @psalm-type CourseRun=array{feedback: null|array{author: string, author_classname: string, feedback: string, date: \OmegaUp\Timestamp, range_bytes_end: int|null, range_bytes_start: int|null}, guid: string, language: string, source?: string, status: string, verdict: string, runtime: int, penalty: int, memory: int, score: float, contest_score: float|null, time: \OmegaUp\Timestamp, submit_delay: int}
 * @psalm-type CourseProblem=array{accepted: int, alias: string, commit: string, difficulty: float, languages: string, letter: string, order: int, points: float, submissions: int, title: string, version: string, visibility: int, visits: int, runs: list<CourseRun>}
 * @psalm-type StudentProgressPayload=array{course: CourseDetails, students: list<StudentProgress>, student: string}
 * @psalm-type StudentProgressByAssignmentPayload=array{course: CourseDetails, students: list<StudentProgress>, student: string, problems: list<CourseProblem>, assignment: string}
 * @psalm-type CourseDetailsPayload=array{details: CourseDetails, progress?: AssignmentProgress}
 * @psalm-type PrivacyStatement=array{markdown: string, statementType: string, gitObjectId?: string}
 * @psalm-type IntroCourseDetails=array{details: CourseDetails, progress: array<string, array<string, float>>}
 * @psalm-type IntroDetailsPayload=array{course: CourseDetails, isFirstTimeAccess: bool, needsBasicInformation: bool, shouldShowAcceptTeacher: bool, shouldShowResults: bool, statements: array{acceptTeacher?: PrivacyStatement, privacy?: PrivacyStatement}, userRegistrationAccepted?: bool|null, userRegistrationAnswered?: bool, userRegistrationRequested?: bool}
 * @psalm-type NavbarProblemsetProblem=array{acceptsSubmissions: bool, alias: string, bestScore: int, hasRuns: bool, maxScore: float|int, text: string, myBestScore?: float|null, hasMyRuns?: bool|null}
 * @psalm-type ArenaAssignment=array{alias: string|null, assignment_type: string, description: null|string, director: string, finish_time: \OmegaUp\Timestamp|null, name: string|null, problems: list<NavbarProblemsetProblem>, problemset_id: int, runs: list<Run>, start_time: \OmegaUp\Timestamp, totalRuns: int|null}
 * @psalm-type AssignmentDetailsPayload=array{showRanking: bool, scoreboard?: Scoreboard, courseDetails: CourseDetails, currentAssignment: ArenaAssignment, shouldShowFirstAssociatedIdentityRunWarning: bool, isTeachingAssistant: bool}
 * @psalm-type AssignmentDetails=array{admin: bool, alias: string, assignmentType: string, courseAssignments: list<CourseAssignment>, description: string, director: string, finishTime: \OmegaUp\Timestamp|null, name: string, problems: list<ProblemsetProblem>, problemsetId: int, startTime: \OmegaUp\Timestamp}
 * @psalm-type CourseScoreboardPayload=array{assignment: AssignmentDetails, problems: list<NavbarProblemsetProblem>, scoreboard: Scoreboard, scoreboardToken:null|string}
 * @psalm-type AddedProblem=array{alias: string, commit?: string, points: float, is_extra_problem?: bool}
 * @psalm-type Event=array{courseAlias?: string, courseName?: string, name: string, problem?: string}
 * @psalm-type ActivityEvent=array{classname: string, event: Event, ip: int|null, time: \OmegaUp\Timestamp, username: string}
 * @psalm-type ActivityFeedPayload=array{alias: string, events: list<ActivityEvent>, type: string, page: int, length: int, pagerItems: list<PageItem>}
 * @psalm-type CourseClarificationsPayload=array{page: int, length: int, pagerItems: list<PageItem>, clarifications: list<Clarification>, is_admin: bool, is_teaching_assistant: bool}
 * @psalm-type CourseCardPublic=array{alias: string, alreadyStarted: bool, lessonCount: int, level: null|string, name: string, school_name: null|string, studentCount: int}
 * @psalm-type CourseCardEnrolled=array{alias: string, name: string, progress: float, school_name: null|string}
 * @psalm-type CourseCardFinished=array{alias: string, name: string}
 * @psalm-type CourseTabsPayload=array{courses: array{enrolled: list<CourseCardEnrolled>, finished: list<CourseCardFinished>, public: list<CourseCardPublic>}, hasVisitedSection: bool}
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
 * @psalm-type RunDetailsV2=array{admin: bool, cases: ProblemCasesContents, compile_error?: string, details?: array{compile_meta?: array<string, RunMetadata>, groups?: list<RunDetailsGroup>, judged_by: string, max_score?: float, memory?: float, score: float, time?: float, verdict: string, wall_time?: float}, feedback?: string, judged_by?: string, logs?: string, show_diff: string, source?: string, source_link?: bool, source_name?: string, source_url?: string, feedback: null|SubmissionFeedback}
 * @psalm-type RunWithDetails=array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, details: null|RunDetailsV2, execution: null|string, guid: string, language: string, memory: int, output: null|string, penalty: int, runtime: int, score: float, score_by_group?: array<string, float|null>, status: string, status_memory: null|string, status_runtime: null|string, submit_delay: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}
 * @psalm-type ProblemDetails=array{accepts_submissions: bool, accepted: int, admin?: bool, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, difficulty: float|null, email_clarifications: bool, input_limit: int, karel_problem: bool, languages: list<string>, letter?: string, limits: SettingLimits, nextExecutionTimestamp?: \OmegaUp\Timestamp, nextSubmissionTimestamp?: \OmegaUp\Timestamp, nominationStatus: NominationStatus, order: string, points: float, preferred_language?: string, problem_id: int, problemsetter?: ProblemsetterInfo, quality_seal: bool, runs?: list<RunWithDetails>, score: float, settings: ProblemSettingsDistrib, show_diff: string, solvers?: list<BestSolvers>, source?: string, statement: ProblemStatement, submissions: int, title: string, version: string, visibility: int, visits: int}
 * @psalm-type ArenaCourseDetails=array{alias: string, assignments: list<CourseAssignment>, name: string, languages: list<string>|null}
 * @psalm-type ArenaCourseAssignment=array{alias: string, name: string, description: string, problemset_id: int}
 * @psalm-type ArenaCourseProblem=array{alias: string, letter: string, title: string}
 * @psalm-type ArenaCoursePayload=array{course: ArenaCourseDetails, assignment: ArenaCourseAssignment, clarifications: list<Clarification>, problems: list<ArenaCourseProblem>, currentProblem: null|ProblemDetails, runs: list<Run>, scoreboard: null|Scoreboard}
 * @psalm-type ListItem=array{key: string, value: string}
 */
class Course extends \OmegaUp\Controllers\Controller {
    // Admision mode constants
    const ADMISSION_MODE_PUBLIC = 'public';
    const ADMISSION_MODE_PRIVATE = 'private';
    const ADMISSION_MODE_REGISTRATION = 'registration';

    // Assignment type constants
    const ASSIGNMENT_TYPE_HOMEWORK = 'homework';
    const ASSIGNMENT_TYPE_LESSON = 'lesson';
    const ASSIGNMENT_TYPE_TEST = 'test';

    // Course level constants
    const COURSE_LEVEL_INTRODUCTORY = 'introductory';
    const COURSE_LEVEL_INTERMEDIATE = 'intermediate';
    const COURSE_LEVEL_ADVANCED = 'advanced';

    // Number of rows shown in course list
    const PAGE_SIZE = 100;

    /**
     * Validate assignment_alias existis into the course and
     * return Assignments object
     *
     * @param \OmegaUp\DAO\VO\Courses $course
     * @param string $assignmentAlias
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateCourseAssignmentAlias(
        \OmegaUp\DAO\VO\Courses $course,
        string $assignmentAlias
    ): \OmegaUp\DAO\VO\Assignments {
        $assignment = \OmegaUp\DAO\Courses::getAssignmentByAlias(
            $course,
            $assignmentAlias
        );
        if (is_null($assignment)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        return $assignment;
    }

    /**
     * Validates request for creating a new Assignment
     *
     * @return array{addedProblems: list<AddedProblem>}
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @omegaup-request-param string $alias
     * @omegaup-request-param 'homework'|'lesson'|'test' $assignment_type
     * @omegaup-request-param string $description
     * @omegaup-request-param \OmegaUp\Timestamp|null $finish_time
     * @omegaup-request-param string $name
     * @omegaup-request-param null|string $problems
     * @omegaup-request-param \OmegaUp\Timestamp $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    private static function validateCreateAssignment(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Courses $course
    ): array {
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['name'],
            'name'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['description'],
            'description'
        );

        $unlimitedDuration = $r->ensureOptionalBool(
            'unlimited_duration'
        ) ?? false;
        $startTime = $r->ensureTimestamp(
            'start_time',
            lowerBound: null,
            upperBound: is_null(
                $course->finish_time
            ) ? null : $course->finish_time->time
        );
        if ($unlimitedDuration && !is_null($course->finish_time)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseDoesNotHaveUnlimitedDuration',
                'unlimited_duration'
            );
        }
        $finishTime = $r->ensureOptionalTimestamp(
            'finish_time',
            lowerBound: null,
            upperBound: is_null(
                $course->finish_time
            ) ? null : $course->finish_time->time,
            required: !is_null($course->finish_time) || !$unlimitedDuration,
        );

        if ($startTime->time < $course->start_time->time) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseAssignmentStartDateBeforeCourseStartDate',
                'start_time'
            );
        }

        if (
            !is_null($finishTime)
            && $finishTime->time < $course->start_time->time
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseAssignmentEndDateBeforeCourseStartDate',
                'finish_time'
            );
        }

        if (
            !is_null($finishTime) &&
            $startTime->time > $finishTime->time
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseInvalidStartTime',
                'finish_time'
            );
        }

        $r->ensureEnum(
            'assignment_type',
            ['test', 'lesson', 'homework']
        );
        $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $addedProblems = [];
        $problemsJson = $r->ensureOptionalString('problems');
        if (!empty($problemsJson)) {
          /** @var list<array{alias: string, commit?: string, points?: int|float|string, is_extra_problem?: bool}> */
            $problemsData = json_decode(
                $problemsJson,
                associative: true,
            );
            foreach ($problemsData as $problemData) {
                if (!isset($problemData['alias'])) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterEmpty',
                        'problems'
                    );
                }

                if (
                    is_null(
                        \OmegaUp\DAO\Problems::getByAlias(
                            $problemData['alias']
                        )
                    )
                ) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'problemNotFound'
                    );
                }
                if (!empty($problemData['points'])) {
                    \OmegaUp\Validators::validateNumber(
                        $problemData['points'],
                        'points'
                    );
                    $problemData['points'] = floatval($problemData['points']);
                } else {
                    $problemData['points'] = 100.0;
                }

                $addedProblems[] = $problemData;
            }
        }

        return ['addedProblems' => $addedProblems];
    }

    /**
     * Validates clone Courses
     *
     * @omegaup-request-param string $name
     * @omegaup-request-param int $start_time
     */
    private static function validateClone(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Courses $course
    ): void {
        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name');
        $r->ensureInt('start_time');
        if (
            is_null($r->identity)
            || (
                !\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)
                && $course->admission_mode !== self::ADMISSION_MODE_PUBLIC
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
    }

    /**
     * Validates update Courses
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @omegaup-request-param 'private'|'public'|'registration'|null $admission_mode
     * @omegaup-request-param null|string $alias
     * @omegaup-request-param null|string $description
     * @omegaup-request-param null|string $objective
     * @omegaup-request-param \OmegaUp\Timestamp|null $finish_time
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param null|string $level
     * @omegaup-request-param null|string $name
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param bool|null $recommended
     * @omegaup-request-param 'no'|'optional'|'required'|null $requests_user_information
     * @omegaup-request-param int $school_id
     * @omegaup-request-param bool|null $show_scoreboard
     * @omegaup-request-param \OmegaUp\Timestamp|null $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    private static function validateUpdate(
        \OmegaUp\Request $r,
        string $courseAlias
    ): \OmegaUp\DAO\VO\Courses {
        self::validateBasicCreateOrUpdate($r, isUpdate: true);

        // Get the actual start and finish time of the course, considering that
        // in case of update, parameters can be optional.
        $originalCourse = self::validateCourseExists($courseAlias);

        $startTime = $r->ensureOptionalTimestamp(
            'start_time'
        ) ?? $originalCourse->start_time;
        $finishTime = $r->ensureOptionalTimestamp('finish_time');
        $unlimitedDuration = $r->ensureOptionalBool(
            'unlimited_duration'
        ) ?? false;

        if (
            !$unlimitedDuration &&
            !is_null($finishTime) &&
            $startTime->time > $finishTime->time
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseInvalidStartTime',
                'finish_time'
            );
        }
        return $originalCourse;
    }

    /**
     * Returns a CourseParams instance from the Request values.
     *
     * @omegaup-request-param string $alias
     * @omegaup-request-param null|string $admission_mode
     * @omegaup-request-param bool|null $archived
     * @omegaup-request-param string $description
     * @omegaup-request-param null|int $finish_time
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param null|string $level
     * @omegaup-request-param int|null $minimum_progress_for_certificate
     * @omegaup-request-param string $name
     * @omegaup-request-param null|bool $needs_basic_information
     * @omegaup-request-param null|string $objective
     * @omegaup-request-param null|string $requests_user_information
     * @omegaup-request-param int|null $school_id
     * @omegaup-request-param null|bool $show_scoreboard
     * @omegaup-request-param int $start_time
     */
    private static function convertRequestToCourseParams(
        \OmegaUp\Request $r
    ): \OmegaUp\CourseParams {
        $params = [
            'alias' => $r->ensureString(
                'alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            ),
            'admission_mode' => $r->ensureOptionalString('admission_mode'),
            'archived' => $r->ensureOptionalBool('archived'),
            'description' => $r->ensureString('description'),
            'finish_time' => $r->ensureOptionalInt('finish_time'),
            'languages' => $r->ensureOptionalString('languages'),
            'level' => $r->ensureOptionalString('level'),
            'minimum_progress_for_certificate' => $r->ensureOptionalInt(
                'minimum_progress_for_certificate',
                lowerBound: 0,
                upperBound: 100
            ),
            'name' => $r->ensureString('name'),
            'needs_basic_information' => $r->ensureOptionalBool(
                'needs_basic_information'
            ) ?? false,
            'objective' => $r->ensureOptionalString('objective'),
            'requests_user_information' => $r->ensureOptionalString(
                'requests_user_information'
            ),
            'school_id' => $r->ensureOptionalInt('school_id'),
            'show_scoreboard' => $r->ensureOptionalBool(
                'show_scoreboard'
            ) ?? false,
            'start_time' => $r->ensureInt('start_time'),
        ];
        return new \OmegaUp\CourseParams($params);
    }

    /**
     * Validates basic information of a course
     *
     * @param \OmegaUp\Request $r
     * @param bool $isUpdate
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @omegaup-request-param 'private'|'public'|'registration'|null $admission_mode
     * @omegaup-request-param null|string $alias
     * @omegaup-request-param null|string $description
     * @omegaup-request-param null|string $level
     * @omegaup-request-param null|string $objective
     * @omegaup-request-param int|null $finish_time
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param null|string $name
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param bool|null $recommended
     * @omegaup-request-param 'no'|'optional'|'required'|null $requests_user_information
     * @omegaup-request-param int $school_id
     * @omegaup-request-param bool|null $show_scoreboard
     * @omegaup-request-param int $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    private static function validateBasicCreateOrUpdate(
        \OmegaUp\Request $r,
        bool $isUpdate = false
    ): void {
        $r->ensureMainUserIdentity();
        $isRequired = !$isUpdate;

        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['name'],
            'name',
            $isRequired
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['description'],
            'description',
            $isRequired
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['objective'],
            'objective',
            required: false // TODO: This should be $isRequired when the UI is ready
        );

        $r->ensureOptionalInt('start_time', null, null, !$isUpdate);
        $r->ensureOptionalInt(
            'finish_time',
            lowerBound: null,
            upperBound: null,
            required: (
                !$isUpdate &&
                !($r->ensureOptionalBool('unlimited_duration') ?? false)
            ),
        );

        $r->ensureOptionalString(
            'alias',
            $isRequired,
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        // Show scoreboard, needs basic information, recommended and request
        // user information are always optional
        $r->ensureOptionalBool('needs_basic_information');
        $r->ensureOptionalBool('show_scoreboard');
        $recommended = $r->ensureOptionalBool('recommended') ?? false;
        $r->ensureOptionalEnum(
            'requests_user_information',
            ['no', 'optional', 'required']
        );
        $r->ensureOptionalInt('school_id');

        if (is_null($r['school_id'])) {
            $school = null;
        } else {
            $school = \OmegaUp\DAO\Schools::getByPK(intval($r['school_id']));
            if (is_null($school)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'schoolNotFound'
                );
            }
        }
        $languages = $r->ensureOptionalString('languages');
        if (!is_null($languages)) {
            $languagesSet = explode(',', $languages);
            \OmegaUp\Validators::validateValidSubset(
                $languagesSet,
                'languages',
                array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES())
            );
        }

        $r->ensureOptionalEnum(
            'admission_mode',
            [
                self::ADMISSION_MODE_PUBLIC,
                self::ADMISSION_MODE_REGISTRATION,
                self::ADMISSION_MODE_PRIVATE,
            ]
        );

        if (
            is_null($r['admission_mode']) ||
            $r['admission_mode'] !== self::ADMISSION_MODE_PUBLIC
        ) {
            return;
        }

        $r->ensureOptionalEnum(
            'level',
            [
                self::COURSE_LEVEL_INTRODUCTORY,
                self::COURSE_LEVEL_INTERMEDIATE,
                self::COURSE_LEVEL_ADVANCED,
            ]
        );

        // Only curator can set public
        if (
            !\OmegaUp\Authorization::canCreatePublicCourse($r->identity)
            && $recommended
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
    }

    /**
     * Validates course exists. Expects course alias, returns
     * course. Throws if not found.
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateCourseExists(string $courseAlias): \OmegaUp\DAO\VO\Courses {
        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        return $course;
    }

    /**
     * Gets the Group assigned to the Course.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function resolveGroup(
        \OmegaUp\DAO\VO\Courses $course
    ): \OmegaUp\DAO\VO\Groups {
        if (is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        $group = \OmegaUp\DAO\Groups::getByPK($course->group_id);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseGroupNotFound'
            );
        }
        return $group;
    }

    /**
     * @return array{token: string}
     *
     * @omegaup-request-param string $course_alias
     */
    public static function apiGenerateTokenForCloneCourse(
        \OmegaUp\Request $r
    ): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $courseAlias) => \OmegaUp\Validators::alias($courseAlias)
        );
        $course = self::validateCourseExists($courseAlias);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if ($course->admission_mode === \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'unnecessaryTokenForPublicCourses'
            );
        }

        $claims = [
            'course' => strval($course->alias),
            'permissions' => 'clone',
        ];
        return [
            'token' => \OmegaUp\SecurityTools::getCourseCloneAuthorizationToken(
                $claims,
                issuer: $r->identity->username
            ),
        ];
    }

    /**
     * Clone a course
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{alias: string}
     *
     * @omegaup-request-param string $alias
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $name
     * @omegaup-request-param \OmegaUp\Timestamp $start_time
     * @omegaup-request-param null|string $token
     */
    public static function apiClone(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentityIsOver13();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::stringNonEmpty($alias)
        );
        $newAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $newName = $r->ensureString(
            'name',
            fn (string $name) => \OmegaUp\Validators::stringNonEmpty($name)
        );
        $token = $r->ensureOptionalString('token');
        $originalCourse = self::validateCourseExists($courseAlias);
        $decodedToken = null;
        if (!is_null($token)) {
            try {
                $decodedToken = \OmegaUp\SecurityTools::getDecodedCloneCourseToken(
                    $token,
                    $courseAlias
                );
            } catch (\OmegaUp\Exceptions\TokenValidateException $e) {
                self::$log->error(
                    "Error validating token for course {$courseAlias}: $e"
                );
                \OmegaUp\DAO\CourseCloneLog::create(
                    new \OmegaUp\DAO\VO\CourseCloneLog([
                        'ip' => (
                            \OmegaUp\Request::getServerVar('REMOTE_ADDR') ?? ''
                        ),
                        'course_id' => $originalCourse->course_id,
                        'new_course_id' => null,
                        'token_payload' => json_encode($e->claims),
                        'timestamp' => \OmegaUp\Time::get(),
                        'user_id' => $r->user->user_id,
                        'result' => $e->getMessage(),
                    ])
                );
                if ($e->getMessage() === 'token_invalid') {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'tokenDecodeInvalid',
                        'token'
                    );
                }
                if ($e->getMessage() === 'token_expired') {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'tokenDecodeExpired',
                        'token'
                    );
                }
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'tokenDecodeUnknown',
                    'token'
                );
            } catch (\Exception $e) {
                self::$log->error(
                    "Error decoding token for course {$courseAlias}",
                    ['exception' => $e],
                );

                \OmegaUp\DAO\CourseCloneLog::create(
                    new \OmegaUp\DAO\VO\CourseCloneLog([
                        'ip' => (
                            \OmegaUp\Request::getServerVar('REMOTE_ADDR') ?? ''
                        ),
                        'course_id' => $originalCourse->course_id,
                        'new_course_id' => null,
                        'token_payload' => '',
                        'timestamp' => \OmegaUp\Time::get(),
                        'user_id' => $r->user->user_id,
                        'result' => 'token_corrupted',
                    ])
                );
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'tokenDecodeCorrupted',
                    'token'
                );
            }
        } else {
            self::validateClone($r, $originalCourse);
        }

        $startTime = $r->ensureTimestamp('start_time');
        $offset = $startTime->time - $originalCourse->start_time->time;

        $cloneCourseFinishTime = null;
        if (!is_null($originalCourse->finish_time)) {
            $cloneCourseFinishTime = new \OmegaUp\Timestamp(
                $originalCourse->finish_time->time + $offset
            );
        }

        \OmegaUp\DAO\DAO::transBegin();
        $course = null;

        $result = 'unknown';
        try {
            // Create the course (and group)
            $course = \OmegaUp\Controllers\Course::createCourseAndGroup(
                new \OmegaUp\DAO\VO\Courses([
                    'name' => $newName,
                    'description' => $originalCourse->description,
                    'objective' => $originalCourse->objective,
                    'alias' => $newAlias,
                    'languages' => $originalCourse->languages,
                    'school_id' => $originalCourse->school_id,
                    'start_time' => $startTime,
                    'finish_time' => $cloneCourseFinishTime,
                    'admission_mode' => self::ADMISSION_MODE_PRIVATE,
                    'show_scoreboard' => $originalCourse->show_scoreboard,
                    'needs_basic_information' => $originalCourse->needs_basic_information,
                    'requests_user_information' => $originalCourse->requests_user_information
                ]),
                $r->user
            );

            $assignmentsProblems = \OmegaUp\DAO\ProblemsetProblems::getProblemsAssignmentByCourseAlias(
                $originalCourse
            );

            foreach ($assignmentsProblems as $_assignmentAlias => $assignmentProblems) {
                // Create and assign homeworks, lessons and tests to new course
                $problemset = self::createAssignment(
                    $originalCourse,
                    new \OmegaUp\DAO\VO\Assignments([
                        'course_id' => $course->course_id,
                        'acl_id' => $course->acl_id,
                        'name' => $assignmentProblems['name'],
                        'description' => $assignmentProblems['description'],
                        'alias' => $assignmentProblems['assignment_alias'],
                        'publish_time_delay' => $assignmentProblems['publish_time_delay'],
                        'assignment_type' => $assignmentProblems['assignment_type'],
                        'start_time' => new \OmegaUp\Timestamp(
                            $assignmentProblems['start_time']->time +
                            $offset
                        ),
                        'finish_time' => (
                            is_null($assignmentProblems['finish_time']) ?
                            null :
                            new \OmegaUp\Timestamp(
                                $assignmentProblems['finish_time']->time +
                                $offset
                            )
                        ),
                        'order' => $assignmentProblems['order'],
                        'max_points' => $assignmentProblems['max_points'],
                    ]),
                    $r->identity
                );
                if (is_null($problemset->problemset_id)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'problemsetNotFound'
                    );
                }

                foreach ($assignmentProblems['problems'] as $problem) {
                    // Create and assign problems to new course
                    self::addProblemToAssignment(
                        problemAlias: $problem['problem_alias'],
                        problemsetId: $problemset->problemset_id,
                        identity: $r->identity,
                        shouldValidateVisibility: false, // visbility mode validation no needed when it is a clone
                        isExtraProblem: $problem['is_extra_problem'],
                        points: 100,
                        commit: null,
                        order: $problem['order']
                    );
                }
            }
            \OmegaUp\DAO\DAO::transEnd();
            $result = 'success';
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            $result = 'unknown';
            throw $e;
        } finally {
            \OmegaUp\DAO\CourseCloneLog::create(
                new \OmegaUp\DAO\VO\CourseCloneLog([
                    'ip' => (
                        \OmegaUp\Request::getServerVar('REMOTE_ADDR') ?? ''
                    ),
                    'course_id' => $originalCourse->course_id,
                    'new_course_id' => !is_null(
                        $course
                    ) ? $course->course_id : null,
                    'token_payload' => json_encode($decodedToken),
                    'timestamp' => \OmegaUp\Time::get(),
                    'user_id' => $r->user->user_id,
                    'result' => $result,
                ])
            );
        }

        return [
            'alias' => $newAlias,
        ];
    }

    /**
     * Create new course API
     *
     * @return array{status: string}
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @omegaup-request-param null|string $admission_mode
     * @omegaup-request-param string $alias
     * @omegaup-request-param bool|null $archived
     * @omegaup-request-param string $description
     * @omegaup-request-param int|null $finish_time
     * @omegaup-request-param null|string $languages
     * @omegaup-request-param null|string $level
     * @omegaup-request-param int|null $minimum_progress_for_certificate
     * @omegaup-request-param string $name
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param null|string $objective
     * @omegaup-request-param null|string $requests_user_information
     * @omegaup-request-param int|null $school_id
     * @omegaup-request-param bool|null $show_scoreboard
     * @omegaup-request-param int $start_time
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentityIsOver13();
        $courseParams = self::convertRequestToCourseParams($r);

        if (is_null($courseParams->schoolId)) {
            $school = null;
        } else {
            $school = \OmegaUp\DAO\Schools::getByPK($courseParams->schoolId);
            if (is_null($school)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'schoolNotFound'
                );
            }
        }

        // Only curator can set public
        $admissionMode = $r->ensureOptionalString('admission_mode');
        if (
            !is_null($admissionMode) &&
            $admissionMode === self::ADMISSION_MODE_PUBLIC &&
            !\OmegaUp\Authorization::canCreatePublicCourse($r->identity)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        $course = new \OmegaUp\DAO\VO\Courses([
            'name' => $courseParams->name,
            'alias' => $courseParams->courseAlias,
            'level' => $courseParams->level,
            'description' => $courseParams->description,
            'objective' => $courseParams->objective,
            'school_id' => $courseParams->schoolId,
            'languages' => isset(
                $courseParams->languages
            ) ? implode(
                ',',
                $courseParams->languages
            ) : null,
            'start_time' => $courseParams->startTime,
            'finish_time' => $courseParams->finishTime,
            'admission_mode' => $courseParams->admissionMode,
            'show_scoreboard' => $courseParams->showScoreboard,
            'needs_basic_information' => $courseParams->needsBasicInformation,
            'requests_user_information' => $courseParams->requestsUserInformation,
          ]);

        if (\OmegaUp\Authorization::isCertificateGenerator($r->identity)) {
            $course->minimum_progress_for_certificate = $courseParams->minimumProgressForCertificate;
        }

        self::createCourseAndGroup($course, $r->user);

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Function to create a new course with its corresponding group
     */
    private static function createCourseAndGroup(
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Users $creator
    ): \OmegaUp\DAO\VO\Courses {
        if (is_null($course->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        if (is_null($creator->user_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        if (!is_null(\OmegaUp\DAO\Courses::getByAlias($course->alias))) {
                $exception = new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'aliasInUse'
                );
                $exception->addCustomMessageToArray('parameter', 'alias');
                throw $exception;
        }

        \OmegaUp\DAO\DAO::transBegin();

        $group = \OmegaUp\Controllers\Group::createGroup(
            $course->alias,
            "students-{$course->alias}",
            "students-{$course->alias}",
            $creator->user_id
        );

        try {
            $acl = new \OmegaUp\DAO\VO\ACLs(['owner_id' => $creator->user_id]);
            \OmegaUp\DAO\ACLs::create($acl);

            \OmegaUp\DAO\GroupRoles::create(new \OmegaUp\DAO\VO\GroupRoles([
                'group_id' => $group->group_id,
                'acl_id' => $acl->acl_id,
                'role_id' => \OmegaUp\Authorization::CONTESTANT_ROLE,
            ]));

            $course->group_id = $group->group_id;
            $course->acl_id = $acl->acl_id;

            \OmegaUp\DAO\Courses::create($course);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'titleInUse',
                    $e
                );
            }
            throw $e;
        }
        return $course;
    }

    /**
     * Function to create a new assignment
     *
     * @param list<AddedProblem> $addedProblems
     *
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    private static function createAssignment(
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Assignments $assignment,
        \OmegaUp\DAO\VO\Identities $identity,
        array $addedProblems = []
    ): \OmegaUp\DAO\VO\Problemsets {
        if (is_null($assignment->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }
        \OmegaUp\DAO\DAO::transBegin();
        try {
            // Create the backing problemset
            $problemset = new \OmegaUp\DAO\VO\Problemsets([
                'acl_id' => $assignment->acl_id,
                'type' => 'Assignment',
                'languages' => $course->languages,
                'scoreboard_url' => \OmegaUp\SecurityTools::randomString(30),
                'scoreboard_url_admin' => \OmegaUp\SecurityTools::randomString(
                    30
                ),
            ]);

            \OmegaUp\DAO\Problemsets::create($problemset);
            $assignment->problemset_id = $problemset->problemset_id;

            \OmegaUp\DAO\Assignments::create($assignment);

            // Update assignment_id in problemset object
            $problemset->assignment_id = $assignment->assignment_id;
            \OmegaUp\DAO\Problemsets::update($problemset);

            if (is_null($problemset->problemset_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }

            if (!empty($addedProblems)) {
                foreach ($addedProblems as $i => $addedProblem) {
                    // Create and assign problems to new course
                    self::addProblemToAssignment(
                        problemAlias: $addedProblem['alias'],
                        problemsetId: $problemset->problemset_id,
                        identity: $identity,
                        shouldValidateVisibility: true,
                        isExtraProblem: $addedProblem['is_extra_problem'] ?? false,
                        points: $addedProblem['points'],
                        commit: $addedProblem['commit'] ?? null,
                        order: $i + 1,
                    );
                }

                \OmegaUp\DAO\Courses::updateAssignmentMaxPoints(
                    $course,
                    $assignment->alias
                );
            }

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                $exception = new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'aliasInUse'
                );
                $exception->addCustomMessageToArray('parameter', 'alias');
                throw $exception;
            }
            throw $e;
        }
        return $problemset;
    }

    /**
     * Function to add problems to a specific assignment
     *
     * @param string $problemAlias
     * @param int $problemsetId
     * @param int $userId
     * @param bool $shouldValidateVisibility validations no needed when it is a clone
     * @param ?string $commit
     * @param ?int $order = 1
     */
    private static function addProblemToAssignment(
        string $problemAlias,
        int $problemsetId,
        \OmegaUp\DAO\VO\Identities $identity,
        bool $shouldValidateVisibility,
        bool $isExtraProblem = false,
        ?float $points = 100,
        ?string $commit = null,
        ?int $order = 1
    ): void {
        // Get this problem
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        [$masterCommit, $currentVersion] = \OmegaUp\Controllers\Problem::resolveCommit(
            $problem,
            $commit
        );

        $problemToAdd = \OmegaUp\DAO\Base\ProblemsetProblems::getByPK(
            $problemsetId,
            $problem->problem_id
        );

        $assignedPoints = $points ?? 100.0;
        \OmegaUp\Controllers\Problemset::addProblem(
            $problemsetId,
            $problem,
            $commit ?? $masterCommit,
            $currentVersion,
            $identity,
            $problem->languages === '' ? 0 : $assignedPoints,
            is_null($order) ? 1 : $order,
            $problemToAdd,
            $shouldValidateVisibility,
            $isExtraProblem
        );
    }

    /**
     * @param \OmegaUp\DAO\VO\Courses $course
     * @param string $assignmentAlias
     * @param string $notificationType
     */
    private static function sendNotificationToStudent(
        \OmegaUp\DAO\VO\Courses $course,
        string $assignmentAlias,
        string $notificationType
    ): void {
        if (is_null($course->group_id) || is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        $students = \OmegaUp\DAO\Courses::getStudentsInCourse(
            $course->course_id,
            $course->group_id
        );

        if ($notificationType == \OmegaUp\DAO\Notifications::COURSE_ASSIGNMENT_PROBLEM_ADDED) {
            $localizationString = new \OmegaUp\TranslationString(
                'notificationCourseAssignmentProblemAdded'
            );
        } else {
            $localizationString = new \OmegaUp\TranslationString(
                'notificationCourseAssignmentAdded'
            );
        }

        $userIds = array_map(
            fn (array $student) => $student['user_id'] ?? 0,
            $students
        );
        \OmegaUp\Controllers\Notification::setCommonNotification(
            $userIds,
            $localizationString,
            $notificationType,
            "/course/{$course->alias}/assignment/{$assignmentAlias}/",
            [
                'courseName' => $course->name,
            ]
        );
    }

    /**
     * API to Create an assignment
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $alias
     * @omegaup-request-param 'homework'|'lesson'|'test' $assignment_type
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $description
     * @omegaup-request-param \OmegaUp\Timestamp|null $finish_time
     * @omegaup-request-param string $name
     * @omegaup-request-param int|null $order
     * @omegaup-request-param null|string $problems
     * @omegaup-request-param int|null $publish_time_delay
     * @omegaup-request-param \OmegaUp\Timestamp $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    public static function apiCreateAssignment(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignmentAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias(
                $alias
            )
        );
        $assignmentType = $r->ensureEnum(
            'assignment_type',
            ['homework', 'test', 'lesson']
        );
        $name = $r->ensureString('name');
        $description = $r->ensureString('description');
        $order = $r->ensureOptionalInt('order');
        $course = self::validateCourseExists($courseAlias);
        [
            'addedProblems' => $addedProblems,
        ] = self::validateCreateAssignment($r, $course);
        $startTime = $r->ensureTimestamp(
            'start_time',
            lowerBound: null,
            upperBound: $course->finish_time?->time
        );
        $unlimitedDuration = $r->ensureOptionalBool(
            'unlimited_duration'
        ) ?? false;
        $finishTime = $r->ensureOptionalTimestamp(
            'finish_time',
            lowerBound: null,
            upperBound: $course->finish_time?->time,
            required: !is_null($course->finish_time) || !$unlimitedDuration,
        );
        $publishTimeDelay = $r->ensureOptionalInt('publish_time_delay');

        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        self::createAssignment(
            $course,
            new \OmegaUp\DAO\VO\Assignments([
                'course_id' => $course->course_id,
                'acl_id' => $course->acl_id,
                'name' => $name,
                'description' => $description,
                'alias' => $assignmentAlias,
                'publish_time_delay' => $publishTimeDelay,
                'assignment_type' => $assignmentType,
                'start_time' => $startTime,
                'finish_time' => $finishTime,
                'order' => $order ?? \OmegaUp\DAO\Assignments::getNextPositionOrder(
                    $course->course_id
                ),
            ]),
            $r->identity,
            $addedProblems
        );

        self::sendNotificationToStudent(
            $course,
            $assignmentAlias,
            \OmegaUp\DAO\Notifications::COURSE_ASSIGNMENT_ADDED
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Update an assignment
     *
     * @return array{status: 'ok'}
     *
     * @omegaup-request-param string $assignment
     * @omegaup-request-param string $course
     * @omegaup-request-param \OmegaUp\Timestamp|null $finish_time
     * @omegaup-request-param \OmegaUp\Timestamp|null $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    public static function apiUpdateAssignment(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignmentAlias = $r->ensureString(
            'assignment',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        [
            'course' => $course,
            'assignment' => $assignment
        ] = self::validateAssignmentDetails(
            $courseAlias,
            $assignmentAlias,
            $r->identity
        );
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $unlimitedDuration = $r->ensureOptionalBool(
            'unlimited_duration'
        ) ?? false;
        if ($unlimitedDuration && !is_null($course->finish_time)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseDoesNotHaveUnlimitedDuration',
                'unlimited_duration'
            );
        }
        $startTime = $r->ensureOptionalTimestamp(
            'start_time',
            lowerBound: null,
            upperBound: is_null(
                $course->finish_time
            ) ? null : $course->finish_time->time
        );
        $finishTime = $r->ensureOptionalTimestamp(
            'finish_time',
            lowerBound: null,
            upperBound: is_null(
                $course->finish_time
            ) ? null : $course->finish_time->time,
            required: !is_null($course->finish_time) || !$unlimitedDuration,
        );

        if (
            !is_null(
                $startTime
            ) && $startTime->time < $course->start_time->time
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseAssignmentStartDateBeforeCourseStartDate',
                'start_time'
            );
        }

        if (
            !is_null($finishTime)
            && $finishTime->time < $course->start_time->time
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseAssignmentEndDateBeforeCourseStartDate',
                'finish_time'
            );
        }

        if (
            !is_null(
                $startTime
            ) && !is_null(
                $finishTime
            ) && $startTime->time > $finishTime->time
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseInvalidStartTime',
                'finish_time'
            );
        }

        // Prevent date changes if a course already has runs from students
        if (
            !is_null(
                $startTime
            ) && $startTime->time !== $assignment->start_time->time
        ) {
            /** @var list<int> $adminsIds */
            $adminsIds = array_map(
                fn($admin) => $admin['user_id'],
                array_filter(
                    \OmegaUp\DAO\UserRoles::getCourseAdmins(
                        $course
                    ),
                    fn($admin) => !is_null($admin['user_id']),
                )
            );
            $runCount = \OmegaUp\DAO\Submissions::countTotalStudentsSubmissionsOfProblemset(
                intval($assignment->problemset_id),
                $adminsIds
            );
            if ($runCount > 0) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'courseUpdateAlreadyHasRuns'
                );
            }
        }

        $valueProperties = [
            'name',
            'description',
            'assignment_type',
        ];

        if (!is_null($startTime)) {
            array_push($valueProperties, 'start_time');
        }
        if (!is_null($finishTime)) {
            array_push($valueProperties, 'finish_time');
        }
        self::updateValueProperties($r, $assignment, $valueProperties);

        if (is_null($course->finish_time) && $unlimitedDuration) {
            $assignment->finish_time = null;
        }
        \OmegaUp\DAO\DAO::transBegin();
        try {
            \OmegaUp\DAO\Assignments::update($assignment);

            \OmegaUp\DAO\ProblemsetIdentities::recalculateEndTimeAsFinishTime(
                $assignment
            );

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds a problem to an assignment
     *
     * @return array{status: 'ok', solutionStatus: string}
     *
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param null|string $commit
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param float $points
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param null|bool $is_extra_problem
     */
    public static function apiAddProblem(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignmentAlias = $r->ensureString(
            'assignment_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $isExtraProblem = $r->ensureOptionalBool('is_extra_problem') ?? false;

        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemset = \OmegaUp\DAO\Assignments::getProblemset(
            $course->course_id,
            $assignmentAlias
        );
        if (is_null($problemset) || is_null($problemset->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        $countProblems = \OmegaUp\DAO\ProblemsetProblems::countProblemsetProblems(
            $problemset
        );
        $commit = $r->ensureOptionalString(
            'commit',
            required: false,
            validator: fn (string $commit) => \OmegaUp\Validators::objectId(
                $commit
            )
        );
        self::addProblemToAssignment(
            problemAlias: $problemAlias,
            problemsetId: $problemset->problemset_id,
            identity: $r->identity,
            shouldValidateVisibility: true,
            isExtraProblem: $isExtraProblem,
            points: $r->ensureOptionalFloat('points') ?? 100.0,
            commit: $commit,
            order: $countProblems + 1,
        );

        \OmegaUp\DAO\Courses::updateAssignmentMaxPoints(
            $course,
            $assignmentAlias
        );

        self::sendNotificationToStudent(
            $course,
            $assignmentAlias,
            \OmegaUp\DAO\Notifications::COURSE_ASSIGNMENT_PROBLEM_ADDED
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        $solutionStatus = \OmegaUp\Controllers\Problem::SOLUTION_NOT_FOUND;

        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        if (\OmegaUp\DAO\Problems::isVisible($problem)) {
            $solutionStatus = \OmegaUp\Controllers\Problem::getProblemSolutionStatus(
                $problem,
                $r->identity
            );
        }

        return [
            'status' => 'ok',
            'solutionStatus' => $solutionStatus,
        ];
    }

    /**
     * @return array{status: string}
     *
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $problems
     */
    public static function apiUpdateProblemsOrder(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignmentAlias = $r->ensureString(
            'assignment_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problems = $r->ensureString('problems');
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = \OmegaUp\DAO\Assignments::getProblemset(
            $course->course_id,
            $assignmentAlias
        );
        if (is_null($problemSet) || is_null($problemSet->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        /** @var list<string> */
        $aliases = json_decode($problems, true);

        \OmegaUp\DAO\DAO::transBegin();
        try {
            $order = 1;
            foreach ($aliases as $alias) {
                $currentProblem = \OmegaUp\DAO\Problems::getByAlias(
                    $alias
                );

                if (
                    is_null($currentProblem) ||
                    is_null($currentProblem->problem_id)
                ) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'problemNotFound'
                    );
                }

                \OmegaUp\DAO\ProblemsetProblems::updateProblemsOrder(
                    $problemSet->problemset_id,
                    $currentProblem->problem_id,
                    $order++
                );
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @return array{status: string}
     *
     * @omegaup-request-param string $assignments
     * @omegaup-request-param string $course_alias
     */
    public static function apiUpdateAssignmentsOrder(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignments = $r->ensureString('assignments');
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignments'],
            'assignments'
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        /** @var list<string> */
        $aliases = json_decode($assignments, true);

        \OmegaUp\DAO\DAO::transBegin();
        try {
            $order = 1;
            foreach ($aliases as $alias) {
                $currentAssignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
                    $alias,
                    $course->course_id
                );

                if (
                    empty($currentAssignment) ||
                    is_null($currentAssignment->assignment_id)
                ) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'assignmentNotFound'
                    );
                }

                \OmegaUp\DAO\Assignments::updateAssignmentsOrder(
                    $currentAssignment->assignment_id,
                    $order++
                );
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @return array{identities: list<string>}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $problem_alias
     */
    public static function apiGetProblemUsers(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get this problem
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem) || is_null($problem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $identities = \OmegaUp\DAO\Problems::getIdentitiesInGroupWhoAttemptedProblem(
            $course->group_id,
            $problem->problem_id
        );

        return [
            'identities' => $identities,
        ];
    }

    /**
     * Remove a problem from an assignment
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $problem_alias
     */
    public static function apiRemoveProblem(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignmentAlias = $r->ensureString(
            'assignment_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = \OmegaUp\DAO\Assignments::getProblemset(
            $course->course_id,
            $assignmentAlias
        );
        if (is_null($problemSet)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        // Get this problem
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Delete the entry from the database.
        $problemsetProblem = \OmegaUp\DAO\ProblemsetProblems::getByPK(
            $problemSet->problemset_id,
            $problem->problem_id
        );
        if (is_null($problemsetProblem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotPartOfAssignment'
            );
        }
        if (
            \OmegaUp\DAO\Submissions::countTotalRunsOfProblemInProblemset(
                intval($problem->problem_id),
                intval($problemSet->problemset_id)
            ) > 0 &&
            !\OmegaUp\Authorization::isSystemAdmin($r->identity)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'cannotRemoveProblemWithSubmissions'
            );
        }
        \OmegaUp\DAO\ProblemsetProblems::delete($problemsetProblem);

        \OmegaUp\DAO\Courses::updateAssignmentMaxPoints(
            $course,
            $assignmentAlias
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * List course assignments
     *
     * @return array{assignments: list<CourseAssignment>}
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @omegaup-request-param string $course_alias
     */
    public static function apiListAssignments(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        $group = self::resolveGroup($course);

        // Only Course Admins, Teaching Assistant or Group Members (students)
        // can see these results
        if (
            !\OmegaUp\Authorization::canViewCourse(
                $r->identity,
                $course,
                $group
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $assignments = \OmegaUp\DAO\Assignments::getSortedCourseAssignments(
            $course->course_id
        );

        $response = [
            'assignments' => [],
        ];
        $time = \OmegaUp\Time::get();
        foreach ($assignments as $assignment) {
            if (
                $assignment['start_time']->time > $time &&
                !\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)
            ) {
                // Non-admins should not be able to see the assignments ahead
                // of time.
                continue;
            }
            $response['assignments'][] = $assignment;
        }

        return $response;
    }

    /**
     * Remove an assignment from a course
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $course_alias
     */
    public static function apiRemoveAssignment(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignmentAlias = $r->ensureString(
            'assignment_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        [
            'course' => $course,
            'assignment' => $assignment,
        ] = self::validateAssignmentDetails(
            $courseAlias,
            $assignmentAlias,
            $r->identity
        );
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemset = \OmegaUp\DAO\Assignments::getProblemset(
            $course->course_id,
            $assignmentAlias
        );
        if (is_null($problemset) || is_null($problemset->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        /** @var list<int> $adminsIds */
        $adminsIds = array_map(
            fn($admin) => $admin['user_id'],
            array_filter(
                \OmegaUp\DAO\UserRoles::getCourseAdmins(
                    $course
                ),
                fn($admin) => !is_null($admin['user_id']),
            )
        );
        $runCount = \OmegaUp\DAO\Submissions::countTotalStudentsSubmissionsOfProblemset(
            intval($assignment->problemset_id),
            $adminsIds
        );
        if ($runCount > 0) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseUpdateAlreadyHasRuns'
            );
        }

        \OmegaUp\DAO\DAO::transBegin();

        try {
            \OmegaUp\DAO\Assignments::unlinkProblemset(
                $assignment,
                $problemset
            );

            \OmegaUp\DAO\ProblemsetAccessLog::removeAccessLogFromProblemset(
                $problemset->problemset_id
            );

            \OmegaUp\DAO\ProblemsetIdentities::removeIdentitiesFromProblemset(
                $problemset->problemset_id
            );

            \OmegaUp\DAO\ProblemsetProblemOpened::removeProblemOpenedFromProblemset(
                $problemset->problemset_id
            );

            \OmegaUp\DAO\ProblemsetProblems::removeProblemsFromProblemset(
                $problemset->problemset_id
            );

            \OmegaUp\DAO\Assignments::delete($assignment);

            \OmegaUp\DAO\Problemsets::delete($problemset);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Converts a Course object into an array
     *
     * @return FilteredCourse
     */
    public static function convertCourseToArray(\OmegaUp\DAO\VO\Courses $course): array {
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        $relevantColumns = [
            'admission_mode',
            'alias',
            'description',
            'finish_time',
            'name',
            'start_time',
        ];
        /** @var array{admission_mode: string, alias: string, assignments: list<CourseAssignment>, description: string, finish_time: \OmegaUp\Timestamp|null, name: string, start_time: \OmegaUp\Timestamp} */
        $arr = $course->asFilteredArray($relevantColumns);
        $arr['assignments'] = [];
        $arr['school_name'] = null;
        $arr['accept_teacher'] = null;
        $arr['is_open'] = false;
        $arr['finish_time'] = $course->finish_time;

        $arr['counts'] = \OmegaUp\DAO\Assignments::getAssignmentCountsForCourse(
            $course->course_id
        );
        return $arr;
    }

    /**
     * @param list<string> $courseTypes
     * @return CoursesList
     */
    private static function getCoursesList(
        \OmegaUp\DAO\VO\Identities $identity,
        int $page,
        int $pageSize,
        array $courseTypes = ['admin', 'student', 'public']
    ) {
        if (is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $response = [
            'admin' => [],
            'student' => [],
            'public' => [],
            'archived' => [],
            'teachingAssistant' => [],
        ];

        if (in_array('admin', $courseTypes)) {
            // TODO(pablo): Cache
            // Courses the user is an admin for.
            if (\OmegaUp\Authorization::isSystemAdmin($identity)) {
                $adminCourses = \OmegaUp\DAO\Courses::getAll(
                    $page,
                    $pageSize,
                    'course_id',
                    'DESC'
                );

                foreach ($adminCourses as $course) {
                    $response['admin'][] = \OmegaUp\Controllers\Course::convertCourseToArray(
                        $course
                    );
                }
            } else {
                $adminCoursesByIdentity = \OmegaUp\DAO\Courses::getAllCoursesAdminedByIdentity(
                    $identity->identity_id,
                    $page,
                    $pageSize
                );

                $response = array_merge($response, $adminCoursesByIdentity);
            }
        }

        if (in_array('student', $courseTypes)) {
            $response['student'] = \OmegaUp\DAO\Courses::getCoursesForStudent(
                $identity->identity_id
            );
        }

        if (in_array('public', $courseTypes)) {
            $response['public'] = \OmegaUp\DAO\Courses::getPublicCourses();
        }

        if (in_array('archived', $courseTypes)) {
            $response['archived'] = \OmegaUp\DAO\Courses::getArchivedCoursesAdminedByIdentity(
                $identity->identity_id
            );
        }
        return $response;
    }

    /**
     * Returns the list of requests made by participants who are interested to
     * join the course
     *
     * @omegaup-request-param string $course_alias
     *
     * @return array{users: list<IdentityRequest>}
     */
    public static function apiRequests(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureMainUserIdentity();

        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        if (
            !\OmegaUp\Authorization::isAdminOrTeachingAssistant(
                $r->identity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $usersRequests =
            \OmegaUp\DAO\CourseIdentityRequest::getRequestsForCourseWithFirstAdmin(
                $course->course_id
            );

        return ['users' => $usersRequests];
    }

    /**
     * Stores the resolution given to a certain request made by a contestant
     * interested to join the course.
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param bool $resolution
     * @omegaup-request-param string $username
     *
     * @return array{status: string}
     */
    public static function apiArbitrateRequest(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['username'],
            'username'
        );

        $course = self::validateCourseExists($courseAlias);
        if (
            !\OmegaUp\Authorization::isAdminOrTeachingAssistant(
                $r->identity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $targetIdentity = \OmegaUp\DAO\Identities::findByUsername(
            $r['username']
        );
        if (is_null($targetIdentity) || is_null($targetIdentity->username)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userNotExist'
            );
        }

        $request = \OmegaUp\DAO\CourseIdentityRequest::getByPK(
            $targetIdentity->identity_id,
            $course->course_id
        );

        if (is_null($request)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'userNotInListOfRequests'
            );
        }

        $request->accepted = $r->ensureBool('resolution');
        $request->last_update = new \OmegaUp\Timestamp(\OmegaUp\Time::get());

        \OmegaUp\DAO\CourseIdentityRequest::update($request);

        // Save this action in the history
        \OmegaUp\DAO\CourseIdentityRequestHistory::create(
            new \OmegaUp\DAO\VO\CourseIdentityRequestHistory([
                'identity_id' => $request->identity_id,
                'course_id' => $course->course_id,
                'time' => $request->last_update,
                'admin_id' => intval($r->user->user_id),
                'accepted' => $request->accepted,
            ])
        );

        if (!is_null($targetIdentity->user_id)) {
            if ($request->accepted) {
                $localizationString = new \OmegaUp\TranslationString(
                    'notificationCourseRegistrationAccepted'
                );
                $notificationType = \OmegaUp\DAO\Notifications::COURSE_REGISTRATION_ACCEPTED;
            } else {
                $localizationString = new \OmegaUp\TranslationString(
                    'notificationCourseRegistrationRejected'
                );
                $notificationType = \OmegaUp\DAO\Notifications::COURSE_REGISTRATION_REJECTED;
            }
            \OmegaUp\Controllers\Notification::setCommonNotification(
                [$targetIdentity->user_id],
                $localizationString,
                $notificationType,
                "/course/{$course->alias}/",
                [
                    'courseName' => $course->name,
                ]
            );
        }

        self::$log->info(
            "Arbitrated course for user, username={$targetIdentity->username}, state={$request->accepted}"
        );

        // If the request was accepted, we need to automatically add the student to the course
        if (!$request->accepted) {
            return ['status' => 'ok'];
        }

        $groupIdentity = new \OmegaUp\DAO\VO\GroupsIdentities([
            'group_id' => $course->group_id,
            'identity_id' => $targetIdentity->identity_id,
            'share_user_information' => $request->share_user_information,
        ]);
        if (!is_null($request->accept_teacher)) {
            $groupIdentity->accept_teacher = $request->accept_teacher;
        }
        \OmegaUp\DAO\GroupsIdentities::create($groupIdentity);

        return ['status' => 'ok'];
    }

    /**
     * List students in a course
     *
     * @return array{students: list<CourseStudent>}
     *
     * @omegaup-request-param string $course_alias
     */
    public static function apiListStudents(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (
            !\OmegaUp\Authorization::isAdminOrTeachingAssistant(
                $r->identity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'students' => \OmegaUp\DAO\Courses::getStudentsInCourse(
                $course->course_id,
                $course->group_id
            ),
        ];
    }

    /**
     * @return array{problems: list<CourseProblem>}
     *
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiStudentProgress(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );
        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );
        if (
            !\OmegaUp\DAO\GroupsIdentities::existsByPK(
                $course->group_id,
                $resolvedIdentity->identity_id
            )
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseStudentNotInCourse'
            );
        }

        $assignmentAlias = $r->ensureString(
            'assignment_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            $course->course_id
        );
        if (is_null($assignment) || is_null($assignment->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }
        return [
            'problems' => self::getProblemsBySelectedAssignment(
                $assignment,
                $resolvedIdentity
            ),
        ];
    }

    /**
     * @return list<CourseProblem>
    */
    private static function getProblemsBySelectedAssignment(
        \OmegaUp\DAO\VO\Assignments $assignment,
        \OmegaUp\DAO\VO\Identities $resolvedIdentity
    ) {
        if (is_null($assignment->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }
        $rawProblems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $assignment->problemset_id,
            needSubmissions: false
        );
        $letter = 0;
        $problems = [];
        foreach ($rawProblems as $problem) {
            $runsArray = \OmegaUp\DAO\Runs::getForCourseProblemDetails(
                intval($problem['problem_id']),
                intval($assignment->problemset_id),
                intval($resolvedIdentity->identity_id)
            );
            $problem['runs'] = [];
            foreach ($runsArray as $run) {
                $run['feedback'] = null;
                if (
                    !is_null($run['feedback_author']) &&
                    !is_null($run['feedback_content']) &&
                    !is_null($run['feedback_date'])
                ) {
                    $run['feedback'] = [
                        'author' => $run['feedback_author'],
                        'author_classname' => $run['feedback_author_classname'],
                        'feedback' => $run['feedback_content'],
                        'date' => $run['feedback_date'],
                        'range_bytes_end' => null,
                        'range_bytes_start' => null,
                    ];
                }
                unset($run['feedback_author']);
                unset($run['feedback_author_classname']);
                unset($run['feedback_content']);
                unset($run['feedback_date']);

                try {
                    $run['source'] = \OmegaUp\Controllers\Submission::getSource(
                        $run['guid']
                    );
                } catch (\Exception $e) {
                    self::$log->error(
                        "Error fetching source for {$run['guid']}",
                        ['exception' => $e],
                    );
                }
                $problem['runs'][] = $run;
            }
            unset($problem['problem_id']);
            $problem['letter'] = \OmegaUp\Controllers\Contest::columnName(
                $letter++
            );
            $problems[] = $problem;
        }
        return $problems;
    }

    /**
     * Returns details of a given course
     *
     * @return array{assignments: AssignmentProgress}
     *
     * @omegaup-request-param string $alias
     */
    public static function apiMyProgress(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        $group = self::resolveGroup($course);

        // Only Course Admins, Teaching Assistant or Group Members (students)
        // can see these results
        if (
            !\OmegaUp\Authorization::canViewCourse(
                $r->identity,
                $course,
                $group
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $assignments = \OmegaUp\DAO\Courses::getAssignmentsProgress(
            $course->course_id,
            $r->identity->identity_id
        );

        return [
            'assignments' => $assignments,
        ];
    }

    /**
     * Add Student to Course.
     *
     * @return array{status: string}
     *
     * @omegaup-request-param bool|null $accept_teacher
     * @omegaup-request-param string $accept_teacher_git_object_id
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $privacy_git_object_id
     * @omegaup-request-param bool $share_user_information
     * @omegaup-request-param string $statement_type
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiAddStudent(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();

        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );
        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );
        if (is_null($resolvedIdentity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $acceptTeacher = $r->ensureOptionalBool('accept_teacher');
        $shareUserInformation = $r->ensureOptionalBool(
            'share_user_information'
        );

        // Only course admins or users adding themselves when the course is public
        if (
            !\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)
            && ($course->admission_mode !== self::ADMISSION_MODE_PUBLIC
            || $resolvedIdentity->identity_id !== $r->identity->identity_id)
            && $course->requests_user_information == 'no'
            && is_null($acceptTeacher)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $groupIdentity = new \OmegaUp\DAO\VO\GroupsIdentities([
            'group_id' => $course->group_id,
            'identity_id' => $resolvedIdentity->identity_id,
            'share_user_information' => $shareUserInformation,
        ]);

        if (!is_null($acceptTeacher)) {
            $groupIdentity->accept_teacher = $acceptTeacher;
        }

        \OmegaUp\DAO\DAO::transBegin();

        try {
            // Only users adding themselves are saved in consent log
            if (
                $resolvedIdentity->identity_id === $r->identity->identity_id
                 && $course->requests_user_information !== 'no'
            ) {
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
                if (
                    !\OmegaUp\DAO\PrivacyStatementConsentLog::hasAcceptedPrivacyStatement(
                        $resolvedIdentity->identity_id,
                        $privacyStatementId
                    )
                ) {
                    $privacyStatementConsentId = \OmegaUp\DAO\PrivacyStatementConsentLog::saveLog(
                        $resolvedIdentity->identity_id,
                        $privacyStatementId
                    );
                } else {
                    $privacyStatementConsentId = \OmegaUp\DAO\PrivacyStatementConsentLog::getId(
                        $resolvedIdentity->identity_id,
                        $privacyStatementId
                    );
                }

                $groupIdentity->privacystatement_consent_id = $privacyStatementConsentId;
            }
            if (
                $resolvedIdentity->identity_id === $r->identity->identity_id
                 && !empty($r['accept_teacher'])
            ) {
                \OmegaUp\Validators::validateStringNonEmpty(
                    $r['accept_teacher_git_object_id'],
                    'accept_teacher_git_object_id'
                );
                $privacyStatementId = \OmegaUp\DAO\PrivacyStatements::getId(
                    $r['accept_teacher_git_object_id'],
                    'accept_teacher'
                );
                if (is_null($privacyStatementId)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'privacyStatementNotFound'
                    );
                }
                if (
                    !\OmegaUp\DAO\PrivacyStatementConsentLog::hasAcceptedPrivacyStatement(
                        $resolvedIdentity->identity_id,
                        $privacyStatementId
                    )
                ) {
                    \OmegaUp\DAO\PrivacyStatementConsentLog::saveLog(
                        $resolvedIdentity->identity_id,
                        $privacyStatementId
                    );
                }
            }
            \OmegaUp\DAO\GroupsIdentities::replace($groupIdentity);

            if (
                $course->admission_mode === self::ADMISSION_MODE_REGISTRATION
                && !is_null($r->user)
            ) {
                // Pre-accept user
                self::preAcceptAccessRequest(
                    $course,
                    [$resolvedIdentity->identity_id],
                    $r->user
                );
            }

            if (
                $resolvedIdentity->identity_id !== $r->identity->identity_id
                && !is_null($resolvedIdentity->user_id)
            ) {
                \OmegaUp\Controllers\Notification::setCommonNotification(
                    [$resolvedIdentity->user_id],
                    new \OmegaUp\TranslationString(
                        'notificationCourseRegistrationManual'
                    ),
                    \OmegaUp\DAO\Notifications::COURSE_REGISTRATION_MANUAL,
                    "/course/{$course->alias}/",
                    [
                        'courseName' => $course->name,
                    ]
                );
            }

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @param list<int> $identitiesIDs
     */
    private static function preAcceptAccessRequest(
        \OmegaUp\DAO\VO\Courses $course,
        array $identitiesIDs,
        \OmegaUp\DAO\VO\Users $admin
    ): void {
        $time = \OmegaUp\Time::get();
        $note = \OmegaUp\Translations::getInstance()->get(
            'courseRegistrationPreAcceptedDescription'
        );
        foreach ($identitiesIDs as $identityID) {
            if (
                \OmegaUp\DAO\CourseIdentityRequest::replace(
                    new \OmegaUp\DAO\VO\CourseIdentityRequest([
                        'identity_id' => $identityID,
                        'course_id' => $course->course_id,
                        'request_time' => $time,
                        'last_update' => $time,
                        'accepted' => true,
                        'extra_note' => $note,
                    ])
                ) > 0
            ) {
                // Save this action in the history
                \OmegaUp\DAO\CourseIdentityRequestHistory::create(
                    new \OmegaUp\DAO\VO\CourseIdentityRequestHistory([
                        'identity_id' => $identityID,
                        'course_id' => $course->course_id,
                        'time' => $time,
                        'admin_id' => $admin->user_id,
                        'accepted' => true,
                    ])
                );
            }
        }
    }

    /**
     * Remove Student from Course
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiRemoveStudent(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );

        if (
            !\OmegaUp\DAO\GroupsIdentities::existsByPK(
                $course->group_id,
                $resolvedIdentity->identity_id
            )
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseStudentNotInCourse'
            );
        }

        \OmegaUp\DAO\GroupsIdentities::delete(new \OmegaUp\DAO\VO\GroupsIdentities([
            'group_id' => $course->group_id,
            'identity_id' => $resolvedIdentity->identity_id,
        ]));

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Search users in course assignment
     *
     * @return array{results: list<ListItem>}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param null|string $query
     */
    public static function apiSearchUsers(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignmentAlias = $r->ensureString(
            'assignment_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        [
            'course' => $course,
            'assignment' => $assignment
        ] = self::validateAssignmentDetails(
            $courseAlias,
            $assignmentAlias,
            $r->identity
        );

        if (
            !\OmegaUp\Authorization::isAdminOrTeachingAssistant(
                $r->identity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $users = \OmegaUp\DAO\ProblemsetIdentities::searchUsers(
            $r->ensureString('query'),
            intval($assignment->problemset_id)
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
     * Returns all course administrators
     *
     * @return array{admins: list<array{role: string, username: string}>, group_admins: list<array{alias: string, name: string, role: string}>, teaching_assistants: list<array{role: string, username: string}>, group_teaching_assistants: list<array{alias: string, name: string, role: string}>}
     *
     * @omegaup-request-param string $course_alias
     */
    public static function apiAdmins(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        if (
            !\OmegaUp\Authorization::isAdminOrTeachingAssistant(
                $r->identity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'admins' => \OmegaUp\DAO\UserRoles::getCourseAdmins($course),
            'group_admins' => \OmegaUp\DAO\GroupRoles::getCourseAdmins($course),
            'teaching_assistants' => \OmegaUp\DAO\UserRoles::getCourseTeachingAssistants(
                $course
            ),
            'group_teaching_assistants' => \OmegaUp\DAO\GroupRoles::getCourseTeachingAssistants(
                $course
            )
        ];
    }

    /**
     * Adds an admin to a course
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiAddAdmin(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        // Authenticate logged user
        $r->ensureIdentityIsOver13();

        // Check course_alias
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        $resolvedUser = \OmegaUp\Controllers\User::resolveUser(
            $r['usernameOrEmail']
        );

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only director is allowed to make modifications
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::addUser(
            intval($course->acl_id),
            intval($resolvedUser->user_id)
        );

        if (
            $resolvedUser->user_id !== $r->identity->user_id
            && !is_null($resolvedUser->user_id)
        ) {
            \OmegaUp\Controllers\Notification::setCommonNotification(
                [$resolvedUser->user_id],
                new \OmegaUp\TranslationString('notificationCourseAddAdmin'),
                \OmegaUp\DAO\Notifications::COURSE_ADMINISTRATOR_ADDED,
                "/course/{$course->alias}/",
                [
                    'courseName' => $course->name,
                ]
            );
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes an admin from a course
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiRemoveAdmin(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );
        if (is_null($resolvedIdentity->user_id)) {
            // Unassociated identities can't be course admins
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        $resolvedUser = \OmegaUp\DAO\Users::getByPK($resolvedIdentity->user_id);
        if (is_null($resolvedUser)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admin is alowed to make modifications
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Check if admin to delete is actually an admin
        if (
            !\OmegaUp\Authorization::isCourseAdmin(
                $resolvedIdentity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        \OmegaUp\Controllers\ACL::removeUser(
            intval($course->acl_id),
            intval($resolvedUser->user_id)
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds an group admin to a course
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $group
     */
    public static function apiAddGroupAdmin(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        // Authenticate logged user
        $r->ensureIdentityIsOver13();

        // Check course_alias
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        try {
            $groupAlias = $r->ensureString(
                'group',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidGroupAlias'
            );
        }

        $group = \OmegaUp\DAO\Groups::findByAlias($groupAlias);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admins are allowed to modify course
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::addGroup(
            intval($course->acl_id),
            intval($group->group_id)
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes a group admin from a course
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $group
     */
    public static function apiRemoveGroupAdmin(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $groupAlias = $r->ensureString(
            'group',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $group = \OmegaUp\DAO\Groups::findByAlias($groupAlias);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admin is allowed to make modifications
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::removeGroup(
            intval($course->acl_id),
            intval($group->group_id)
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds a teaching assistant to a course
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiAddTeachingAssistant(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $username = $r->ensureString(
            'usernameOrEmail',
            fn (string $user) => \OmegaUp\Validators::usernameOrEmail(
                $user
            )
        );

        $resolvedUser = \OmegaUp\Controllers\User::resolveUser(
            $username
        );

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only director is allowed to make modifications
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::addUser(
            intval($course->acl_id),
            intval($resolvedUser->user_id),
            roleId: \OmegaUp\Authorization::TEACHING_ASSISTANT_ROLE
        );

        if (
            $resolvedUser->user_id !== $r->identity->user_id
            && !is_null($resolvedUser->user_id)
        ) {
            \OmegaUp\Controllers\Notification::setCommonNotification(
                [$resolvedUser->user_id],
                new \OmegaUp\TranslationString(
                    'notificationCourseAddTeachingAssistant'
                ),
                \OmegaUp\DAO\Notifications::COURSE_TEACHING_ASSISTANT_ADDED,
                "/course/{$course->alias}/",
                [
                    'courseName' => $course->name,
                ]
            );
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds an group teaching assistant to a course
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $group
     */
    public static function apiAddGroupTeachingAssistant(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        try {
            $groupAlias = $r->ensureString(
                'group',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidGroupAlias'
            );
        }

        $group = \OmegaUp\DAO\Groups::findByAlias($groupAlias);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admins are allowed to modify course
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::addGroup(
            intval($course->acl_id),
            intval($group->group_id),
            roleId: \OmegaUp\Authorization::TEACHING_ASSISTANT_ROLE
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes a group teaching assistant from a course
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $group
     */
    public static function apiRemoveGroupTeachingAssistant(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $groupAlias = $r->ensureString(
            'group',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $group = \OmegaUp\DAO\Groups::findByAlias($groupAlias);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admin is allowed to make modifications
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::removeGroup(
            intval($course->acl_id),
            intval($group->group_id),
            roleId: \OmegaUp\Authorization::TEACHING_ASSISTANT_ROLE
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes a teaching assistant from a course
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function apiRemoveTeachingAssistant(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $username = $r->ensureString(
            'usernameOrEmail',
            fn (string $user) => \OmegaUp\Validators::usernameOrEmail(
                $user
            )
        );

        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $username
        );

        if (is_null($resolvedIdentity->user_id)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $resolvedUser = \OmegaUp\DAO\Users::getByPK($resolvedIdentity->user_id);
        if (is_null($resolvedUser)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);

        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admin is allowed to make modifications
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Check if teaching assistant to delete is actually a teaching assistant
        if (
            !\OmegaUp\Authorization::isTeachingAssistant(
                $resolvedIdentity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        \OmegaUp\Controllers\ACL::removeUser(
            intval($course->acl_id),
            intval($resolvedUser->user_id),
            roleId:\OmegaUp\Authorization::TEACHING_ASSISTANT_ROLE
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Request feedback and its corresponding notification
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $guid
     */
    public static function apiRequestFeedback(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        $guid = $r->ensureString(
            'guid',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $assignmentAlias = $r->ensureString(
            'assignment_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            intval($course->course_id)
        );
        if (is_null($assignment) || is_null($assignment->acl_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        if (
            !\OmegaUp\DAO\GroupRoles::isContestant(
                intval($r->identity->identity_id),
                intval($assignment->acl_id)
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $submission = \OmegaUp\DAO\Submissions::getByGuid(
            $r->ensureString('guid')
        );

        if (is_null($submission)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        //save a feedback by default
        $feedback = \OmegaUp\Translations::getInstance()->get(
            'requestFeedbackMessage'
        );

        $courseSubmissionInfo = \OmegaUp\DAO\Submissions::getCourseSubmissionInfo(
            $submission,
            $assignmentAlias,
            $courseAlias
        );
        if (is_null($courseSubmissionInfo)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseSubmissionNotFound'
            );
        }

        \OmegaUp\Controllers\Submission::createOrUpdateFeedback(
            $r->identity,
            $submission,
            $feedback
        );

        $admins = \OmegaUp\DAO\UserRoles::getCourseAdministrators(
            $course
        );
        $userIds = array_map(
            fn (array $admin) => $admin['user_id'] ?? 0,
            $admins
        );
        \OmegaUp\Controllers\Notification::setCommonNotification(
            $userIds,
            new \OmegaUp\TranslationString('notificationCourseRequestFeedback'),
            \OmegaUp\DAO\Notifications::COURSE_REQUEST_FEEDBACK,
            "/course/{$course->alias}/assignment/{$assignmentAlias}/#runs/all/show-run:{$guid}",
            [
                'username' => $r->identity->username,
                'assignmentName' => $assignment->name,
                'courseName' => $course->name,
            ]
        );
        return [
            'status' => 'ok',
        ];
    }

    /**
     * Show course intro only on public courses when user is not yet registered
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Course not found or trying to directly access a private course.
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return IntroDetailsPayload
     *
     * @omegaup-request-param string $course_alias
     */
    public static function apiIntroDetails(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $courseAlias) => \OmegaUp\Validators::alias($courseAlias)
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        $group = self::resolveGroup($course);
        $shouldShowIntro = !\OmegaUp\Authorization::canViewCourse(
            $r->identity,
            $course,
            $group
        );

        $hasSharedUserInformation = true;
        $hasAcceptedTeacher = null;
        $registrationResponse = [];
        if (!\OmegaUp\Authorization::isGroupAdmin($r->identity, $group)) {
            [
              'share_user_information' => $hasSharedUserInformation,
              'accept_teacher' => $hasAcceptedTeacher,
            ] = \OmegaUp\DAO\Courses::getSharingInformation(
                $r->identity->identity_id,
                $course,
                $group
            );
        }

        if ($course->admission_mode === self::ADMISSION_MODE_REGISTRATION) {
            $registration = \OmegaUp\DAO\CourseIdentityRequest::getByPK(
                $r->identity->identity_id,
                $course->course_id
            );

            if (is_null($registration)) {
                $registrationResponse = [
                  'userRegistrationAnswered' => false,
                  'userRegistrationRequested' => false,
                ];
            } else {
                $registrationResponse = [
                  'userRegistrationAccepted' => $registration->accepted,
                'userRegistrationAnswered' => !is_null(
                    $registration->accepted
                ),
                  'userRegistrationRequested' => true,
                ];
            }
        }

        $introDetails = self::getIntroDetailsForCourse(
            $course,
            $r->identity,
            $shouldShowIntro,
            $hasAcceptedTeacher ?? false,
            $hasSharedUserInformation,
            $registrationResponse
        );

        if (!isset($introDetails['templateProperties']['coursePayload'])) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }
        return $introDetails['templateProperties']['coursePayload'];
    }

    /**
     * @return array{entrypoint: string, inContest?: bool, templateProperties: array{coursePayload?: IntroDetailsPayload, payload: CourseDetailsPayload|IntroDetailsPayload|AssignmentDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param null|string $assignment_alias
     * @omegaup-request-param string $course_alias
     */
    public static function getCourseDetailsForTypeScript(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Not logged user can still view the public course's contents,
            // including courses with registration mode
            $r->identity = null;
        }
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $courseAlias) => \OmegaUp\Validators::alias($courseAlias)
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        $group = self::resolveGroup($course);
        $assignmentAlias = $r->ensureOptionalString(
            'assignment_alias',
            required: false,
            validator: fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        if (is_null($r->identity)) {
            return self::getIntroDetailsForCourse($course);
        }

        if (is_null($assignmentAlias)) {
            return self::getCourseDetails($r, $course, $group, false);
        }

        return self::getAssignmentDetails(
            $r->identity,
            $r->user,
            $course,
            $group,
            $assignmentAlias
        );
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: CourseScoreboardPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param null|string $scoreboard_token
     */
    public static function getCourseScoreboardDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignmentAlias = $r->ensureString(
            'assignment_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $scoreboardToken = $r->ensureOptionalString(
            'scoreboard_token',
            required: false,
            validator: fn (string $token) => \OmegaUp\Validators::token($token)
        );

        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $courseAlias,
            $assignmentAlias,
            $scoreboardToken,
            $r
        );
        $group = self::resolveGroup($tokenAuthenticationResult['course']);

        // Log the operation only when there is not a token in request
        if (!$tokenAuthenticationResult['hasToken']) {
            // Authenticate request
            $r->ensureIdentity();

            if (
                !\OmegaUp\Authorization::canViewCourse(
                    $r->identity,
                    $tokenAuthenticationResult['course'],
                    $group
                )
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            \OmegaUp\DAO\ProblemsetAccessLog::create(new \OmegaUp\DAO\VO\ProblemsetAccessLog([
                'identity_id' => $r->identity->identity_id,
                'problemset_id' => $tokenAuthenticationResult['assignment']->problemset_id,
                'ip' => ip2long(
                    \OmegaUp\Request::getServerVar('REMOTE_ADDR') ?? ''
                ),
            ]));
        }

        if (
            is_null($tokenAuthenticationResult['course']->acl_id)
            || is_null($tokenAuthenticationResult['course']->alias)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        if (
            is_null($tokenAuthenticationResult['assignment']->problemset_id)
            || is_null($tokenAuthenticationResult['assignment']->alias)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        $director = \OmegaUp\DAO\UserRoles::getOwner(
            $tokenAuthenticationResult['course']->acl_id
        );
        if (is_null($director)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $scoreboard = new \OmegaUp\Scoreboard(
            new \OmegaUp\ScoreboardParams([
                'alias' => $tokenAuthenticationResult['assignment']->alias,
                'title' => $tokenAuthenticationResult['assignment']->name,
                'problemset_id' => $tokenAuthenticationResult['assignment']->problemset_id,
                'start_time' => $tokenAuthenticationResult['assignment']->start_time,
                'finish_time' => $tokenAuthenticationResult['assignment']->finish_time,
                'acl_id' => $tokenAuthenticationResult['assignment']->acl_id,
                'group_id' => $tokenAuthenticationResult['course']->group_id,
                'admin' => $tokenAuthenticationResult['courseAdmin'],
            ])
        );

        $problemsInAssignment = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $tokenAuthenticationResult['assignment']->problemset_id,
            needSubmissions: false
        );

        $problemsResponseArray = [];
        $letter = 0;
        foreach ($problemsInAssignment as $problem) {
            $problem['letter'] = \OmegaUp\Controllers\Contest::columnName(
                $letter++
            );
            $problem['accepts_submissions'] = !empty(
                $problem['languages']
            );
            $problemsResponseArray[] = [
                'acceptsSubmissions' => $problem['accepts_submissions'],
                'alias' => strval($problem['alias']),
                'text' => "{$problem['letter']}. {$problem['title']}",
                'bestScore' => 0,
                'maxScore' => floatval($problem['points']),
                'hasRuns' => false,
            ];
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'scoreboardToken' => $scoreboardToken,
                    'assignment' => [
                        'name' => strval(
                            $tokenAuthenticationResult['assignment']->name
                        ),
                        'alias' => strval(
                            $tokenAuthenticationResult['assignment']->alias
                        ),
                        'description' => $tokenAuthenticationResult['assignment']->description ?? '',
                        'assignmentType' => $tokenAuthenticationResult['assignment']->assignment_type,
                        'startTime' => $tokenAuthenticationResult['assignment']->start_time,
                        'finishTime' => $tokenAuthenticationResult['assignment']->finish_time,
                        'problems' => self::getProblemsByAssignment(
                            $tokenAuthenticationResult['assignment']->alias,
                            $tokenAuthenticationResult['course']->alias,
                            $r->identity,
                            $r->user
                        ),
                        'courseAssignments' => \OmegaUp\DAO\Courses::getAllAssignments(
                            $courseAlias,
                            $tokenAuthenticationResult['courseAdmin']
                        ),
                        'director' => $director,
                        'problemsetId' => $tokenAuthenticationResult['assignment']->problemset_id,
                        'admin' => $tokenAuthenticationResult['courseAdmin'],
                    ],
                    'scoreboard' => $scoreboard->generate(
                        withRunDetails: false,
                        sortByName: false
                    ),
                    'problems' => $problemsResponseArray,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCourseScoreboard'
                ),
            ],
            'entrypoint' => 'course_scoreboard',
        ];
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{title: \OmegaUp\TranslationString, payload: array<string, string|null>}}
     */
    public static function getCoursesHomepageForTypeScript(\OmegaUp\Request $r): array {
        return [
            'templateProperties' => [
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCourses'
                ),
                'payload' => [],
            ],
            'entrypoint' => 'course_homepage',
        ];
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: CourseCloneDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $token
     */
    public static function getCourseCloneDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        $alias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::stringNonEmpty($alias)
        );
        $course = self::validateCourseExists($alias);
        $token = $r->ensureOptionalString(
            'token',
            required: $course->admission_mode !== \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC,
            validator: fn (string $token) => \OmegaUp\Validators::stringNonEmpty(
                $token
            )
        );
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        $creator = \OmegaUp\DAO\Courses::getCreatorInformation($course);
        if (is_null($creator)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'creator' => $creator,
                    'details' => self::getCommonCourseDetails(
                        $course,
                        $r->identity
                    ),
                    'token' => $token,
                ],
                'title' => new \OmegaUp\TranslationString('wordsCloneCourse'),
            ],
            'entrypoint' => 'course_clone',
        ];
    }

    /**
     *
     * @return array{entrypoint: string, templateProperties: array{payload: CourseNewPayload, title:\OmegaUp\TranslationString}}
     */
    public static function getCourseNewDetailsForTypeScript(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        return [
            'templateProperties' => [
                'payload' => [
                    'is_curator' => \OmegaUp\Authorization::canCreatePublicCourse(
                        $r->identity
                    ),
                    'is_admin' => true,
                    'languages' => \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES(),
                    'hasVisitedSection' => \OmegaUp\UITools::hasVisitedSection(
                        'has-visited-create-course'
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCourseNew'
                ),
            ],
            'entrypoint' => 'course_new',
        ];
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: CourseEditPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $course
     */
    public static function getCourseEditDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        $courseAlias = $r->ensureString(
            'course',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $courseEditDetails = self::getCourseEditDetails(
            $courseAlias,
            $r->identity
        );

        if (!empty($courseEditDetails['course']['assignments'])) {
            $courseEditDetails['selectedAssignment'] = $courseEditDetails['course']['assignments'][0];
            $courseEditDetails['assignmentProblems'] = self::getProblemsByAssignment(
                $courseEditDetails['selectedAssignment']['alias'],
                $courseAlias,
                $r->identity,
                $r->user
            );
        }

        return [
            'templateProperties' => [
                'payload' => $courseEditDetails,
                'title' => new \OmegaUp\TranslationString('courseEdit'),
            ],
            'entrypoint' => 'course_edit',
        ];
    }

    /**
     * @return CourseEditPayload
     */
    private static function getCourseEditDetails(
        string $courseAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        if (
            !\OmegaUp\Authorization::isCourseAdmin($identity, $course) &&
            !self::isTeachingAssistant($course, $identity)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        $admins = \OmegaUp\DAO\UserRoles::getCourseAdmins($course);
        foreach ($admins as &$admin) {
            unset($admin['user_id']);
        }

        $teachingAssistants = \OmegaUp\DAO\UserRoles::getCourseTeachingAssistants(
            $course
        );
        $groupsTeachingAssistants = \OmegaUp\DAO\GroupRoles::getCourseTeachingAssistants(
            $course
        );
        foreach ($teachingAssistants as &$teachingAssistant) {
            unset($teachingAssistant['user_id']);
        }

        return [
            'course' => self::getCommonCourseDetails($course, $identity),
            'assignmentProblems' => [],
            'selectedAssignment' => null,
            'tags' => [],
            'students' => \OmegaUp\DAO\Courses::getStudentsInCourse(
                intval($course->course_id),
                intval($course->group_id)
            ),
            'allLanguages' => \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES(),
            'identityRequests' => \OmegaUp\DAO\CourseIdentityRequest::getRequestsForCourseWithFirstAdmin(
                intval($course->course_id)
            ),
            'admins' => $admins,
            'groupsAdmins' => \OmegaUp\DAO\GroupRoles::getCourseAdmins(
                $course
            ),
            'groupsTeachingAssistants' => $groupsTeachingAssistants,
            'teachingAssistants' => \OmegaUp\DAO\UserRoles::getCourseTeachingAssistants(
                $course
            ),
        ];
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: CourseSubmissionsListPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $course
     */
    public static function getCourseSubmissionsListForTypeScript(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        $courseAlias = $r->ensureString(
            'course',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        $usersProblems = \OmegaUp\DAO\Problems::getProblemsByUsersInACourse(
            $courseAlias
        );
        $userSolvedProblems = [];
        $userUnsolvedProblems = [];
        foreach ($usersProblems as $userProblem) {
            if ($userProblem['solved']) {
                $userSolvedProblems[$userProblem['username']][] = $userProblem;
            } else {
                $userUnsolvedProblems[$userProblem['username']][] = $userProblem;
            }
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'solvedProblems' => $userSolvedProblems,
                    'unsolvedProblems' => $userUnsolvedProblems,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'courseSubmissionsList'
                ),
            ],
            'entrypoint' => 'course_submissions_list',
        ];
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: StudentsProgressPayload, title: \OmegaUp\TranslationString, fullWidth: bool}}
     *
     * @omegaup-request-param int $length
     * @omegaup-request-param int $page
     * @omegaup-request-param string $course
     */
    public static function getStudentsProgressForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureIdentity();

        $page = $r->ensureOptionalInt('page') ?? 1;
        $length = $r->ensureOptionalInt('length') ?? 100;

        $courseAlias = $r->ensureString(
            'course',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $studentsProgress = \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::SCHOOL_STUDENTS_PROGRESS,
            "{$courseAlias}-{$page}-{$length}",
            function () use ($course, $page, $length) {
                if (is_null($course->course_id) || is_null($course->group_id)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'courseNotFound'
                    );
                }
                return \OmegaUp\DAO\Courses::getStudentsProgressPerAssignment(
                    $course->course_id,
                    $course->group_id,
                    $page,
                    $length
                );
            },
            60 * 60 * 12 // 12 hours
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'course' => self::getCommonCourseDetails(
                        $course,
                        $r->identity
                    ),
                    'assignmentsProblems' => $studentsProgress['assignmentsProblems'],
                    'students' => $studentsProgress['studentsProgress'],
                    'totalRows' => $studentsProgress['totalRows'],
                    'pagerItems' => \OmegaUp\Pager::paginateWithUrl(
                        $studentsProgress['totalRows'],
                        $length,
                        $page,
                        "/course/{$courseAlias}/students/",
                        adjacent: 5,
                        params: [],
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleStudentsProgress'
                ),
                'fullWidth' => true,
            ],
            'entrypoint' => 'course_students'
        ];
    }

    /**
     * @return array{progress: list<StudentProgressInCourse>, nextPage: null|int}
     *
     * @omegaup-request-param int $length
     * @omegaup-request-param int $page
     * @omegaup-request-param string $course
     */
    public static function apiStudentsProgress(
        \OmegaUp\Request $r
    ): array {
        $r->ensureIdentity();

        $page = $r->ensureOptionalInt('page') ?? 1;
        $length = $r->ensureOptionalInt('length') ?? 100;

        $courseAlias = $r->ensureString(
            'course',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $studentsProgress = \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::SCHOOL_STUDENTS_PROGRESS,
            "{$courseAlias}-{$page}-{$length}",
            function () use ($course, $page, $length) {
                if (is_null($course->course_id) || is_null($course->group_id)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'courseNotFound'
                    );
                }
                return \OmegaUp\DAO\Courses::getStudentsProgressPerAssignment(
                    $course->course_id,
                    $course->group_id,
                    $page,
                    $length
                );
            },
            60 * 60 * 12 // 12 hours
        );

        return [
            'progress' => $studentsProgress['studentsProgress'],
            'nextPage' => (
                $studentsProgress['totalRows'] > ($page * $length)
            ) ? $page + 1 : null,
        ];
    }

    /**
     * @return array{templateProperties: array{payload: StudentProgressPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $course
     * @omegaup-request-param string $student
     */
    public static function getStudentProgressForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['student'], 'student');

        $course = self::validateCourseExists($courseAlias);

        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        ['allProgress' => $studentsProgress] = \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::SCHOOL_STUDENTS_PROGRESS,
            $courseAlias,
            function () use ($course) {
                return \OmegaUp\DAO\Courses::getStudentsInCourseWithProgressPerAssignment(
                    $course->course_id,
                    $course->group_id
                );
            },
            60 * 60 * 12 // 12 hours
        );
        return [
            'templateProperties' => [
                'payload' => [
                    'course' => self::getCommonCourseDetails(
                        $course,
                        $r->identity
                    ),
                    // TODO: Get progress only for the given student, rather than every student.
                    'students' => $studentsProgress,
                    'student' => $r['student']
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleStudentsProgress'
                ),
            ],
            'entrypoint' => 'course_student'
        ];
    }

    /**
     * @return array{templateProperties: array{payload: StudentProgressByAssignmentPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $course
     * @omegaup-request-param string $student
     */
    public static function getStudentProgressByAssignmentForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $student = $r->ensureString('student');

        $course = self::validateCourseExists($courseAlias);

        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $student
        );

        if (
            !\OmegaUp\DAO\GroupsIdentities::existsByPK(
                $course->group_id,
                $resolvedIdentity->identity_id
            )
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseStudentNotInCourse'
            );
        }

        $assignmentAlias = $r->ensureString(
            'assignment_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            $course->course_id
        );
        if (is_null($assignment) || is_null($assignment->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }
        $problems = self::getProblemsBySelectedAssignment(
            $assignment,
            $resolvedIdentity
        );

        ['allProgress' => $studentsProgress] = \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::SCHOOL_STUDENTS_PROGRESS,
            $courseAlias,
            function () use ($course) {
                return \OmegaUp\DAO\Courses::getStudentsInCourseWithProgressPerAssignment(
                    $course->course_id,
                    $course->group_id
                );
            },
            60 * 60 * 12 // 12 hours
        );
        return [
            'templateProperties' => [
                'payload' => [
                    'course' => self::getCommonCourseDetails(
                        $course,
                        $r->identity
                    ),
                    // TODO: Get progress only for the given student, rather than every student.
                    'students' => $studentsProgress,
                    'student' => $student,
                    'problems' => $problems,
                    'assignment' => $assignmentAlias
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleStudentsProgress'
                ),
            ],
            'entrypoint' => 'course_student_with_assignment'
        ];
    }

     /**
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     *
     * @return array{entrypoint: string, templateProperties: array{payload: CourseListMinePayload, title: \OmegaUp\TranslationString}}
     */
    public static function getCourseMineDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureIdentity();
        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt('page_size') ?? 1000;

        $courses = self::getCoursesList(
            $r->identity,
            $page,
            $pageSize,
            courseTypes: ['admin', 'archived']
        );
        $filteredCourses = [
            'admin' => [
                'filteredCourses' => [
                    'current' => [
                        'courses' => [],
                        'timeType' => 'current',
                    ],
                    'past' => [
                        'courses' => [],
                        'timeType' => 'past',
                    ],
                    'archived' => [
                        'courses' => [],
                        'timeType' => 'archived',
                    ],
                    'teachingAssistant' => [
                        'courses' => [],
                        'timeType' => 'teachingAssistant',
                    ],
                ],
                'accessMode' => 'admin',
                'activeTab' => '',
            ]
        ];
        foreach ($courses['admin'] as $course) {
            if (
                is_null($course['finish_time'])
                || $course['finish_time']->time > \OmegaUp\Time::get()
            ) {
                $filteredCourses['admin']['filteredCourses']['current']['courses'][] = $course;
                $filteredCourses['admin']['activeTab'] = 'current';
            } else {
                $filteredCourses['admin']['filteredCourses']['past']['courses'][] = $course;
            }
        }

        if (array_key_exists('archived', $courses)) {
            $filteredCourses['admin']['filteredCourses']['archived']['courses'] = $courses['archived'];
        }
        if (array_key_exists('teachingAssistant', $courses)) {
            $filteredCourses['admin']['filteredCourses']['teachingAssistant']['courses'] = $courses['teachingAssistant'];
        }
        if (
            $filteredCourses['admin']['activeTab'] === ''
            && !empty(
                $filteredCourses['admin']['filteredCourses']['past']['courses']
            )
        ) {
            $filteredCourses['admin']['activeTab'] = 'past';
        } elseif (
            $filteredCourses['admin']['activeTab'] === ''
            && !empty(
                $filteredCourses['admin']['filteredCourses']['archived']['courses']
            )
        ) {
            $filteredCourses['admin']['activeTab'] = 'archived';
        } elseif (
            $filteredCourses['admin']['activeTab'] === ''
            && !empty(
                $filteredCourses['admin']['filteredCourses']['teachingAssistant']['courses']
            )
        ) {
            $filteredCourses['admin']['activeTab'] = 'teachingAssistant';
        }
        return [
            'templateProperties' => [
                'payload' => [
                    'courses' => $filteredCourses,
                ],
                'title' => new \OmegaUp\TranslationString('courseList'),
            ],
            'entrypoint' => 'course_mine',
        ];
    }

    /**
     *
     * @return array{entrypoint: string, templateProperties: array{payload: CourseTabsPayload, title: \OmegaUp\TranslationString, fullWidth: bool}}
     */
    public static function getCourseTabsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        /** @var array{enrolled: list<CourseCardEnrolled>, finished: list<CourseCardFinished>, public: list<CourseCardPublic>} $courses */
        $courses = [
            'enrolled' => [],
            'finished' => [],
            'public' => \OmegaUp\DAO\Courses::getPublicCoursesForTab(),
        ];

        // Check who is visiting, but a not logged user can still
        // view the list of public courses.
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            return [
                'templateProperties' => [
                    'payload' => [
                        'hasVisitedSection' => true,
                        'courses' => $courses,
                    ],
                    'title' => new \OmegaUp\TranslationString('courseList'),
                    'fullWidth' => true,
                ],
                'entrypoint' => 'course_tabs',
            ];
        }
        [
            'enrolled' => $courses['enrolled'],
            'finished' => $courses['finished'],
        ] = \OmegaUp\DAO\Courses::getEnrolledAndFinishedCoursesForTabs(
            $r->identity
        );
        /** @var array<string> */
        $startedCourses = [];
        foreach ($courses['enrolled'] as $studentCourse) {
            $startedCourses[] = $studentCourse['alias'];
        }
        foreach ($courses['finished'] as $studentCourse) {
            $startedCourses[] = $studentCourse['alias'];
        }
        $startedCourses = array_unique($startedCourses);
        foreach ($courses['public'] as &$course) {
            $course['alreadyStarted'] = in_array(
                $course['alias'],
                $startedCourses
            );
        }
        return [
            'templateProperties' => [
                'payload' => [
                    'courses' => $courses,
                    'hasVisitedSection' => \OmegaUp\UITools::hasVisitedSection(
                        'has-visited-join-course'
                    ),
                ],
                'title' => new \OmegaUp\TranslationString('courseList'),
                'fullWidth' => true,
            ],
            'entrypoint' => 'course_tabs',
        ];
    }

    /**
     * @return array{templateProperties: array{payload: CourseStatisticsPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $course
     */
    public static function getCourseStatisticsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $course = self::validateCourseExists($courseAlias);

        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'course' => self::getCommonCourseDetails(
                        $course,
                        $r->identity
                    ),
                    'problemStats' => \OmegaUp\DAO\Assignments::getAssignmentsProblemsStatistics(
                        $course->course_id,
                        $course->group_id
                    ),
                    'verdicts' => \OmegaUp\DAO\Assignments::getAssignmentVerdictDistribution(
                        $course->course_id,
                        $course->group_id
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCourseStatistics'
                ),
            ],
            'entrypoint' => 'course_statistics'
        ];
    }

    /**
     * @return array{templateProperties: array{payload: ActivityFeedPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $course
     * @omegaup-request-param int|null $length
     * @omegaup-request-param int|null $page
     */
    public static function getActivityFeedDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        $page = $r->ensureOptionalInt('page') ?? 1;
        $length = $r->ensureOptionalInt('length') ?? 100;

        $courseAlias = $r->ensureString(
            'course',
            fn (string $courseAlias) => \OmegaUp\Validators::alias($courseAlias)
        );
        $course = self::validateCourseExists($courseAlias);

        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $report = \OmegaUp\DAO\Courses::getActivityReport(
            $course,
            $page,
            $length
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'alias' => $courseAlias,
                    'events' => \OmegaUp\ActivityReport::getActivityReport(
                        $report['activity']
                    ),
                    'type' => 'course',
                    'pagerItems' => \OmegaUp\Pager::paginateWithUrl(
                        $report['totalRows'],
                        $length,
                        $page,
                        "/course/{$courseAlias}/activity/",
                        adjacent: 5,
                        params: [],
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
     * @param CoursesList $courses
     * @param list<string> $coursesTypes
     *
     * @return StudentCourses
     */
    private static function getFilteredCourses($courses, $coursesTypes) {
        $filteredCourses = [];
        foreach ($coursesTypes as $courseType) {
            $filteredCourses[$courseType] = [
                'filteredCourses' => [
                    'current' => [
                        'courses' => [],
                        'timeType' => 'current',
                    ],
                    'past' => [
                        'courses' => [],
                        'timeType' => 'past',
                    ],
                ],
                'accessMode' => $courseType,
                'activeTab' => '',
            ];
            foreach ($courses[$courseType] as $course) {
                if (
                    is_null($course['finish_time'])
                    || $course['finish_time']->time > \OmegaUp\Time::get()
                ) {
                    $filteredCourses[$courseType]['filteredCourses']['current']['courses'][] = $course;
                    $filteredCourses[$courseType]['activeTab'] = 'current';
                } else {
                    $filteredCourses[$courseType]['filteredCourses']['past']['courses'][] = $course;
                }
            }
            if (
                $filteredCourses[$courseType]['activeTab'] === ''
                && !empty(
                    $filteredCourses[$courseType]['filteredCourses']['past']['courses']
                )
            ) {
                $filteredCourses[$courseType]['activeTab'] = 'past';
            }
        }

        return $filteredCourses;
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{coursePayload?: IntroDetailsPayload, payload: IntroCourseDetails|IntroDetailsPayload, title: \OmegaUp\TranslationString}}
     */
    private static function getCourseDetails(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Groups $group,
        bool $showAssignment
    ): array {
        $r->ensureIdentity();

        if ($course->admission_mode === self::ADMISSION_MODE_PUBLIC) {
            return self::getCourseDetailsForLoggedUser($r, $course, $group);
        }
        if ($course->admission_mode === self::ADMISSION_MODE_PRIVATE) {
            return self::getCourseDetailsForLoggedUser(
                $r,
                $course,
                $group,
                registrationResponse: [],
                showAssignment: $showAssignment
            );
        }
        $registration = \OmegaUp\DAO\CourseIdentityRequest::getByPK(
            $r->identity->identity_id,
            $course->course_id
        );

        if (is_null($registration)) {
            $registrationResponse = [
                'userRegistrationAnswered' => false,
                'userRegistrationRequested' => false,
            ];
        } else {
            $registrationResponse = [
                'userRegistrationAccepted' => $registration->accepted,
                'userRegistrationAnswered' => !is_null($registration->accepted),
                'userRegistrationRequested' => true,
            ];
        }

        return self::getCourseDetailsForLoggedUser(
            $r,
            $course,
            $group,
            $registrationResponse,
            $showAssignment
        );
    }

    /**
     * @param array{userRegistrationAccepted?: bool|null, userRegistrationAnswered: bool, userRegistrationRequested: bool}|array<empty, empty> $registrationResponse
     *
     * @return array{entrypoint: string, templateProperties: array{coursePayload?: IntroDetailsPayload, payload: IntroCourseDetails|IntroDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     */
    private static function getCourseDetailsForLoggedUser(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Groups $group,
        array $registrationResponse = [],
        bool $showAssignment = false
    ): array {
        $r->ensureIdentity();

        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        $shouldShowIntro = !\OmegaUp\Authorization::canViewCourse(
            $r->identity,
            $course,
            $group
        );
        if (
            $shouldShowIntro &&
            $course->admission_mode === self::ADMISSION_MODE_PRIVATE
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        $hasSharedUserInformation = true;
        $hasAcceptedTeacher = null;
        if (!\OmegaUp\Authorization::isGroupAdmin($r->identity, $group)) {
            [
                'share_user_information' => $hasSharedUserInformation,
                'accept_teacher' => $hasAcceptedTeacher,
            ] = \OmegaUp\DAO\Courses::getSharingInformation(
                $r->identity->identity_id,
                $course,
                $group
            );
        }
        $detailsResponse = [
            'templateProperties' => [
                'payload' => [
                    'details' => self::getCommonCourseDetails(
                        $course,
                        $r->identity
                    ),
                    'progress' => \OmegaUp\DAO\Courses::getAssignmentsProgress(
                        $course->course_id,
                        $r->identity->identity_id
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCourseDetails'
                ),
            ],
            'entrypoint' => 'course_details',
        ];

        $isAdmin = \OmegaUp\Authorization::isCourseAdmin($r->identity, $course);
        $isTeachingAssistant = self::isTeachingAssistant(
            $course,
            $r->identity
        );

        if (($isAdmin || $isTeachingAssistant) && !$showAssignment) {
            return $detailsResponse;
        }
        if (
            $shouldShowIntro
            || is_null($hasAcceptedTeacher)
            || (
                !$hasSharedUserInformation
                && $course->requests_user_information !== 'no'
            )
        ) {
            return self::getIntroDetailsForCourse(
                $course,
                $r->identity,
                $shouldShowIntro,
                $hasAcceptedTeacher ?? false,
                $hasSharedUserInformation,
                $registrationResponse
            );
        }

        return $detailsResponse;
    }

    /**
     * Gets the course and specific assignment details
     *
     * @return array{templateProperties: array{payload: AssignmentDetailsPayload, fullWidth: bool, title: \OmegaUp\TranslationString}, inContest: bool, entrypoint: string}
     */
    public static function getAssignmentDetails(
        \OmegaUp\DAO\VO\Identities $currentIdentity,
        ?\OmegaUp\DAO\VO\Users $currentUser,
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Groups $group,
        string $assignmentAlias
    ): array {
        $assignment = self::validateCourseAssignmentAlias(
            $course,
            $assignmentAlias
        );
        if (is_null($assignment->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        $isAdmin = (
            \OmegaUp\Authorization::isCourseAdmin(
                $currentIdentity,
                $course
            ) ||
            \OmegaUp\Authorization::canCreatePublicCourse(
                $currentIdentity
            )
        );

        $isTeachingAssistant = self::isTeachingAssistant(
            $course,
            $currentIdentity
        );

        if (
            !$isAdmin &&
            !$isTeachingAssistant &&
            !\OmegaUp\DAO\GroupRoles::isContestant(
                intval($currentIdentity->identity_id),
                intval($assignment->acl_id)
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $problemsInAssignment = [];

        if (
            $isAdmin ||
            $assignment->start_time->time <= \OmegaUp\Time::get()
        ) {
            $problemsInAssignment = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
                intval($assignment->problemset_id),
                needSubmissions: false
            );
        }

        $problemsResponseArray = [];
        $letter = 0;
        foreach ($problemsInAssignment as $problem) {
            $problem['letter'] = \OmegaUp\Controllers\Contest::columnName(
                $letter++
            );
            $problem['accepts_submissions'] = !empty(
                $problem['languages']
            );
            $problemsResponseArray[] = [
                'acceptsSubmissions' => $problem['accepts_submissions'],
                'alias' => strval($problem['alias']),
                'text' => "{$problem['letter']}. {$problem['title']}",
                'bestScore' => 0,
                'maxScore' => floatval($problem['points']),
                'hasRuns' => false,
            ];
        }

        // Get the director
        $director = \OmegaUp\DAO\UserRoles::getOwner(
            intval($course->acl_id)
        );
        if (is_null($director)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        // Get scoreboard;
        $params = \OmegaUp\ScoreboardParams::fromAssignment(
            $assignment,
            intval($course->group_id),
            showAllRuns: false,
        );

        $params->admin = $isAdmin;
        $scoreboard = new \OmegaUp\Scoreboard($params);

        $runs = [];
        $totalRuns = null;
        if ($isAdmin || $isTeachingAssistant) {
            [
                'runs' => $runs,
                'totalRuns' => $totalRuns,
            ] = self::getAllRuns($assignment->problemset_id);
        }

        $response = [
            'templateProperties' => [
                'payload' => [
                    'isTeachingAssistant' => $isTeachingAssistant,
                    'shouldShowFirstAssociatedIdentityRunWarning' => (
                        !is_null($currentUser) &&
                        !\OmegaUp\Controllers\User::isMainIdentity(
                            $currentUser,
                            $currentIdentity
                        ) &&
                        \OmegaUp\DAO\Problemsets::shouldShowFirstAssociatedIdentityRunWarning(
                            $currentUser
                        )
                    ),
                    'showRanking' => \OmegaUp\Controllers\Course::shouldShowScoreboard(
                        $currentIdentity,
                        $course,
                        $group
                    ),
                    'courseDetails' => self::getCommonCourseDetails(
                        $course,
                        $currentIdentity
                    ),
                    'currentAssignment' => [
                        'name' => $assignment->name,
                        'description' => $assignment->description,
                        'alias' => $assignment->alias,
                        'director' => $director,
                        'assignment_type' => $assignment->assignment_type,
                        'start_time' => $assignment->start_time,
                        'finish_time' => $assignment->finish_time,
                        'problems' => $problemsResponseArray,
                        'problemset_id' => intval($assignment->problemset_id),
                        'runs' => $runs,
                        'totalRuns' => $totalRuns,
                    ],
                ],
                'fullWidth' => true,
                'title' => new \OmegaUp\TranslationString(
                    'courseAssignmentTitle',
                    [
                        'courseName' => $course->name,
                        'assignmentName' => $assignment->name,
                    ],
                ),
            ],
            // Navbar is only hidden during exams.
            'inContest' => $assignment->assignment_type === 'test',
            'entrypoint' => 'arena_course',
        ];
        if ($course->admission_mode !== self::ADMISSION_MODE_PUBLIC) {
            $response['templateProperties']['payload']['scoreboard'] = $scoreboard->generate(
                withRunDetails: false,
                sortByName: false
            );
        }

        if (!$isAdmin && $assignment->start_time->time > \OmegaUp\Time::get()) {
            $response['templateProperties']['payload']['currentAssignment']['name'] = null;
            $response['templateProperties']['payload']['currentAssignment']['description'] = null;
        }
        return $response;
    }

    /**
     * Gets the course assignment (and problem) details for ArenaCourse
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string|null $problem_alias
     * @omegaup-request-param null|string $lang
     *
     * @return array{templateProperties: array{payload: ArenaCoursePayload, fullWidth: bool, title: \OmegaUp\TranslationString}, inContest: bool, entrypoint: string}
     *
     */
    public static function getArenaCourseDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();

        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);

        $assignmentAlias = $r->ensureString(
            'assignment_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignment = self::validateCourseAssignmentAlias(
            $course,
            $assignmentAlias
        );
        if (is_null($assignment->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        $isAdmin = (
            \OmegaUp\Authorization::isCourseAdmin(
                $r->identity,
                $course
            ) ||
            \OmegaUp\Authorization::canCreatePublicCourse(
                $r->identity
            )
        );

        if (
            !$isAdmin &&
            !\OmegaUp\DAO\GroupRoles::isContestant(
                intval($r->identity->identity_id),
                intval($assignment->acl_id)
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if (
            $assignment->start_time->time > \OmegaUp\Time::get() &&
            !$isAdmin
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'assignmentNotStarted'
            );
        }

        $problemsInAssignment = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            intval($assignment->problemset_id),
            needSubmissions: false
        );

        $problemsResponseArray = [];
        $letter = 0;
        foreach ($problemsInAssignment as $problem) {
            $problem['letter'] = \OmegaUp\Controllers\Contest::columnName(
                $letter++
            );
            $problemsResponseArray[] = [
                'alias' => strval($problem['alias']),
                'letter' => $problem['letter'],
                'title' => strval($problem['title']),
            ];
        }

        $scoreboard = null;
        if (
            $assignment->assignment_type !== 'lesson' &&
            \OmegaUp\Controllers\Course::shouldShowScoreboard(
                $r->identity,
                $course,
                self::resolveGroup($course)
            )
        ) {
            // Get scoreboard;
            $params = \OmegaUp\ScoreboardParams::fromAssignment(
                $assignment,
                intval($course->group_id),
                showAllRuns: false,
            );
            $params->admin = $isAdmin;
            $scoreboard = new \OmegaUp\Scoreboard($params);
            $scoreboard = $scoreboard->generate(
                withRunDetails: false,
                sortByName: false,
            );
        }

        $response = [
            'templateProperties' => [
                'payload' => [
                    'course' => [
                        'alias' => strval($course->alias),
                        'name' => strval($course->name),
                        'languages' => !is_null(
                            $course->languages
                        ) ? explode(
                            ',',
                            $course->languages
                        ) : null,
                        'assignments' => \OmegaUp\DAO\Courses::getAllAssignments(
                            strval($course->alias),
                            $isAdmin,
                            $r->identity
                        ),
                    ],
                    'assignment' => [
                        'alias' => strval($assignment->alias),
                        'name' => strval($assignment->name),
                        'description' => strval($assignment->description),
                        'problemset_id' => intval($assignment->problemset_id),
                    ],
                    'clarifications' => \OmegaUp\DAO\Clarifications::getProblemsetClarifications(
                        contest: null,
                        course: $course,
                        isAdmin: $isAdmin,
                        currentIdentity: $r->identity,
                        offset: null,
                        rowcount: 100,
                    )['clarifications'],
                    'problems' => $problemsResponseArray,
                    'runs' => [],
                    'currentProblem' => null,
                    'scoreboard' => $scoreboard,
                ],
                'fullWidth' => true,
                'title' => new \OmegaUp\TranslationString(
                    'courseAssignmentTitle',
                    [
                        'courseName' => $course->name,
                        'assignmentName' => $assignment->name,
                    ],
                ),
            ],
            'inContest' => $assignment->assignment_type === 'test',
            'entrypoint' => 'arena_coursev2',
        ];

        $problemAlias = $r->ensureOptionalString(
            'problem_alias',
            required: false,
            validator: fn (string $alias) => \OmegaUp\Validators::alias($alias),
        );
        if (is_null($problemAlias)) {
            return $response;
        }

        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            intval($assignment->problemset_id)
        );
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        $problem = \OmegaUp\DAO\Problems::getByAliasAndProblemset(
            $problemAlias,
            intval($problemset->problemset_id)
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        $problemDetails = \OmegaUp\Controllers\Problem::getProblemDetails(
            $r->identity,
            $problem,
            $problemset,
            \OmegaUp\Controllers\Identity::getPreferredLanguage(
                $r->identity,
            ),
            showSolvers: false,
            preventProblemsetOpen: false,
            contestAlias: null,
        );
        if (is_null($problemDetails)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        $response['templateProperties']['payload']['currentProblem'] = $problemDetails;

        $identityId = null;
        if (!$isAdmin) {
            $identityId = $r->identity->identity_id;
        }

        $response['templateProperties']['payload']['runs'] = self::getAllRunsWithDetails(
            problemsetId: $assignment->problemset_id,
            status: null,
            verdict: null,
            problemId: intval($problem->problem_id),
            language: null,
            identityId: $identityId
        );

        return $response;
    }

    /**
     * @return array{runs: list<Run>, totalRuns: int}
     */
    private static function getAllRuns(
        int $problemsetId,
        ?string $status = null,
        ?string $verdict = null,
        ?int $problemId = null,
        ?string $language = null,
        ?int $identityId = null,
        ?int $offset = 0,
        ?int $rowCount = 100,
        ?string $execution = null,
        ?string $output = null
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
            $rowCount,
            $execution,
            $output,
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
     * @return list<RunWithDetails>
     */
    private static function getAllRunsWithDetails(
        int $problemsetId,
        ?string $status = null,
        ?string $verdict = null,
        ?int $problemId = null,
        ?string $language = null,
        ?int $identityId = null,
        ?int $offset = 0,
        ?int $rowCount = 100
    ): array {
        [
            'runs' => $runs,
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
            $run['details'] = null;
            $run['contest_score'] = floatval($run['contest_score']);
            $allRuns[] = $run;
        }

        return $allRuns;
    }

    /**
     * @param array{userRegistrationAccepted?: bool|null, userRegistrationAnswered: bool, userRegistrationRequested: bool}|array<empty, empty> $registrationResponse
     *
     * @return array{entrypoint: string, templateProperties: array{coursePayload: IntroDetailsPayload, payload: IntroDetailsPayload, title: \OmegaUp\TranslationString}}
     */
    private static function getIntroDetailsForCourse(
        \OmegaUp\DAO\VO\Courses $course,
        ?\OmegaUp\DAO\VO\Identities $identity = null,
        bool $shouldShowIntro = true,
        bool $hasAcceptedTeacher = true,
        bool $hasSharedUserInformation = false,
        array $registrationResponse = []
    ): array {
        if (
            is_null($identity) &&
            $shouldShowIntro &&
            $course->admission_mode === self::ADMISSION_MODE_PRIVATE
        ) {
            \OmegaUp\UITools::redirectToLoginIfNotLoggedIn();
        }

        $courseDetails = self::getCommonCourseDetails($course, $identity);

        $requestUserInformation = $courseDetails['requests_user_information'];
        $needsBasicInformation = false;
        $privacyStatementMarkdown = null;
        $statements = [];
        if (!is_null($identity)) {
            $markdown = \OmegaUp\PrivacyStatement::getForConsent(
                $identity->language_id,
                'accept_teacher'
            );
            $teacherStatement = \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement(
                'accept_teacher'
            );
            if (is_null($teacherStatement)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'courseNotFound'
                );
            }
            $statements['acceptTeacher'] = [
                'markdown' => $markdown,
                'statementType' => 'accept_teacher',
                'gitObjectId' => $teacherStatement['git_object_id'],
            ];
            $needsBasicInformation = (
                $courseDetails['needs_basic_information']
                && (
                    is_null($identity->country_id)
                    || is_null($identity->state_id)
                    || is_null($identity->current_identity_school_id)
                )
            );

            // Privacy Statement Information
            $privacyStatementMarkdown = \OmegaUp\PrivacyStatement::getForProblemset(
                $identity->language_id,
                'course',
                $requestUserInformation
            );
            if (!is_null($privacyStatementMarkdown)) {
                $statementType = "course_{$requestUserInformation}_consent";
                $statement =
                    \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement(
                        $statementType
                    );
                if (!is_null($statement)) {
                    $statements['privacy'] = [
                        'markdown' => $privacyStatementMarkdown,
                        'statementType' => $statementType,
                        'gitObjectId' => $statement['git_object_id'],
                    ];
                }
            }
        }

        $coursePayload = array_merge(
            $registrationResponse,
            [
                'course' => $courseDetails,
                'needsBasicInformation' => $needsBasicInformation,
                'shouldShowAcceptTeacher' => !$hasAcceptedTeacher,
                'statements' => $statements,
                'isFirstTimeAccess' => !$hasSharedUserInformation,
                'shouldShowResults' => $shouldShowIntro,
            ]
        );
        return [
            'templateProperties' => [
                'payload' => $coursePayload,
                'coursePayload' => $coursePayload,
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCourseIntro'
                ),
            ],
            'entrypoint' => 'course_intro',
        ];
    }

    /**
     * @return array{status: string}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param bool|null $accept_teacher
     * @omegaup-request-param bool|null $share_user_information
     */
    public static function apiRegisterForCourse(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $acceptTeacher = $r->ensureOptionalBool('accept_teacher');
        $shareUserInformation = $r->ensureOptionalBool(
            'share_user_information'
        );

        $course = self::validateCourseExists($courseAlias);

        if ($course->admission_mode !== self::ADMISSION_MODE_REGISTRATION) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'courseDoesNotAdmitRegistration'
            );
        }

        \OmegaUp\DAO\CourseIdentityRequest::create(
            new \OmegaUp\DAO\VO\CourseIdentityRequest([
                'identity_id' => $r->identity->identity_id,
                'course_id' => $course->course_id,
                'request_time' => \OmegaUp\Time::get(),
                'accept_teacher' => $acceptTeacher,
                'share_user_information' => $shareUserInformation,
            ])
        );

        /** @var array{user_id: int|null, role: 'admin'|'owner'|'site-admin', username: string} */
        foreach (
            \OmegaUp\DAO\UserRoles::getCourseAdmins(
                $course
            ) as $admin
        ) {
            if (empty($admin['user_id']) || $admin['role'] === 'site-admin') {
                continue;
            }
            \OmegaUp\Controllers\Notification::setCommonNotification(
                [$admin['user_id']],
                new \OmegaUp\TranslationString(
                    'notificationCourseRegistrationRequest'
                ),
                \OmegaUp\DAO\Notifications::COURSE_REGISTRATION_REQUEST,
                "/course/{$course->alias}/edit/#students",
                [
                    'username' => $r->identity->username,
                    'courseName' => $course->name,
                ]
            );
        }
        return ['status' => 'ok'];
    }

    /**
     * Returns course basic details
     *
     * @return array{alias: string, archived: boolean, needs_basic_information: bool, description: string, name: string, requests_user_information: string}
     */
    private static function getBasicCourseDetails(
        \OmegaUp\DAO\VO\Courses $course
    ) {
        return [
            'name' => strval($course->name),
            'description' => strval($course->description),
            'alias' => strval($course->alias),
            'archived' => $course->archived,
            'needs_basic_information' => $course->needs_basic_information,
            'requests_user_information' => $course->requests_user_information,
        ];
    }

    private static function isTeachingAssistant(
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Identities $identity
    ): bool {
        $groupsTeachingAssistants = \OmegaUp\DAO\Courses::getCourseTeachingAssistantGroups(
            $course,
        );
        return \OmegaUp\Authorization::isTeachingAssistant(
            $identity,
            $course
        ) || \OmegaUp\Authorization::isMemberOfAnyGroup(
            $identity,
            $groupsTeachingAssistants
        );
    }

    /**
     * Returns course details common between admin & non-admin
     *
     * @return CourseDetails
     */
    private static function getCommonCourseDetails(
        \OmegaUp\DAO\VO\Courses $course,
        ?\OmegaUp\DAO\VO\Identities $identity = null
    ): array {
        $isAdmin = false;
        $isCurator = false;
        $isTeachingAssistant = false;
        if (is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        $group = \OmegaUp\DAO\Groups::getByPK($course->group_id);
        if (is_null($group) || is_null($group->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseGroupNotFound'
            );
        }
        if (!is_null($identity)) {
            $isAdmin = \OmegaUp\Authorization::isCourseAdmin(
                $identity,
                $course
            );
            $isCurator = \OmegaUp\Authorization::canCreatePublicCourse(
                $identity
            );
            $isTeachingAssistant = self::isTeachingAssistant(
                $course,
                $identity
            );
        }

        $result = [
            'assignments' => \OmegaUp\DAO\Courses::getAllAssignments(
                strval($course->alias),
                $isAdmin,
                $identity
            ),
            'clarifications' => (
                is_null($identity)
                ? []
                : \OmegaUp\DAO\Clarifications::getProblemsetClarifications(
                    contest: null,
                    course: $course,
                    isAdmin: ($isAdmin || $isCurator || $isTeachingAssistant),
                    currentIdentity: $identity,
                    offset: null,
                    rowcount: 100,
                )['clarifications']
            ),
            'name' => strval($course->name),
            'description' => strval($course->description),
            'objective' => $course->objective,
            'level' => $course->level,
            'alias' => strval($course->alias),
            'archived' => $course->archived,
            'school_id' => $course->school_id,
            'school_name' => null,
            'start_time' => $course->start_time,
            'languages' => !is_null(
                $course->languages
            ) ? explode(
                ',',
                $course->languages
            ) : null,
            'finish_time' => $course->finish_time,
            'is_admin' => $isAdmin,
            'is_teaching_assistant' => $isTeachingAssistant,
            'is_curator' => $isCurator,
            'admission_mode' => $course->admission_mode,
            'needs_basic_information' => $course->needs_basic_information,
            'show_scoreboard' => boolval($course->show_scoreboard),
            'recommended' => boolval($course->recommended),
            'requests_user_information' => $course->requests_user_information,
            'teaching_assistant_enabled' => boolval(
                $course->teaching_assistant_enabled
            ),
            'unlimited_duration' => false,
        ];

        if ($isAdmin || $isTeachingAssistant) {
            $result['student_count'] =
                \OmegaUp\DAO\GroupsIdentities::GetMemberCountById(
                    $group->group_id
                );
        }
        if (!is_null($course->school_id)) {
            $school = \OmegaUp\DAO\Schools::getByPK($course->school_id);
            if (!is_null($school)) {
                $result['school_name'] = $school->name;
                $result['school_id'] = $school->school_id;
            }
        }

        return $result;
    }

    /**
     * Returns all details of a given Course
     *
     * @return CourseDetails
     *
     * @omegaup-request-param string $alias
     */
    public static function apiAdminDetails(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);
        self::resolveGroup($course);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($course, $r->identity);
    }

    /**
     * Returns a report with all user activity for a course.
     *
     * @return array{events: list<ActivityEvent>, pagerItems: list<PageItem>}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param int|null $length
     * @omegaup-request-param int|null $page
     */
    public static function apiActivityReport(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $page = $r->ensureOptionalInt('page') ?? 1;
        $length = $r->ensureOptionalInt('length') ?? 100;
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $report = \OmegaUp\DAO\Courses::getActivityReport(
            $course,
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
                "/course/{$courseAlias}/activity/",
                adjacent: 5,
                params: [],
            ),
        ];
    }

    /**
     * Archives or un-archives a course
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param boolean $archive
     */
    public static function apiArchive(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $archive = $r->ensureOptionalBool('archive') ?? true;
        $course->archived = $archive;
        \OmegaUp\DAO\Courses::update($course);

        return [ 'status' => 'ok' ];
    }

    /**
     * Validates and authenticate token for operations when user can be logged
     * in or not. This is the only private function that receives Request as a
     * parameter because it needs authenticate it wheter there is no token.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{assignment: \OmegaUp\DAO\VO\Assignments, course: \OmegaUp\DAO\VO\Courses, courseAdmin: bool, hasToken: bool}
     */
    private static function authenticateAndValidateToken(
        string $courseAlias,
        string $assignmentAlias,
        ?string $token,
        \OmegaUp\Request $r
    ): array {
        if (is_null($token)) {
            $r->ensureIdentity();
            [
                'course' => $course,
                'assignment' => $assignment
            ] = self::validateAssignmentDetails(
                $courseAlias,
                $assignmentAlias,
                $r->identity
            );
            $isAdmin = \OmegaUp\Authorization::isCourseAdmin(
                $r->identity,
                $course
            );

            return [
                'hasToken' => false,
                'courseAdmin' => $isAdmin,
                'assignment' => $assignment,
                'course' => $course,
            ];
        }

        $courseAdmin = false;

        $course = self::validateCourseExists($courseAlias);
        $assignment = self::validateCourseAssignmentAlias(
            $course,
            $assignmentAlias
        );
        if (is_null($assignment->assignment_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        $assignmentProblemset = \OmegaUp\DAO\Assignments::getByIdWithScoreboardUrls(
            $assignment->assignment_id
        );
        if (is_null($assignmentProblemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        if ($token === $assignmentProblemset['scoreboard_url_admin']) {
            $courseAdmin = true;
        } elseif ($token !== $assignmentProblemset['scoreboard_url']) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'invalidScoreboardUrl'
            );
        }

        // hasToken is true, it means we do not autenticate request user
        return [
            'courseAdmin' => $courseAdmin,
            'hasToken' => true,
            'assignment' => $assignment,
            'course' => $course,
        ];
    }

    /**
     * Validates assignment by course alias and assignment alias given
     *
     * @return array{course: \OmegaUp\DAO\VO\Courses, assignment: \OmegaUp\DAO\VO\Assignments}
     */
    private static function validateAssignmentDetails(
        string $courseAlias,
        string $assignmentAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course) || is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            $course->course_id
        );
        if (is_null($assignment) || is_null($assignment->acl_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        // Admins are almighty, no need to check anything else.
        if (\OmegaUp\Authorization::isCourseAdmin($identity, $course)) {
            return [
                'course' => $course,
                'assignment' => $assignment
            ];
        }

        if (
            $assignment->start_time->time > \OmegaUp\Time::get() ||
            !\OmegaUp\DAO\GroupRoles::isContestant(
                intval($identity->identity_id),
                $assignment->acl_id
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        return [
            'course' => $course,
            'assignment' => $assignment
        ];
    }

    /**
     * Returns details of a given assignment
     *
     * @return array{admin: bool, alias: string, assignment_type: null|string, courseAssignments: list<CourseAssignment>, description: null|string, director: string, finish_time: \OmegaUp\Timestamp|null, name: string, problems: list<ProblemsetProblem>, problemset_id: int, start_time: \OmegaUp\Timestamp}
     *
     * @omegaup-request-param string $assignment
     * @omegaup-request-param string $course
     * @omegaup-request-param null|string $token
     */
    public static function apiAssignmentDetails(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $courseAlias = $r->ensureString(
            'course',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignmentAlias = $r->ensureString(
            'assignment',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['token'],
            'token'
        );

        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $courseAlias,
            $assignmentAlias,
            $r['token'],
            $r
        );
        if (
            is_null($tokenAuthenticationResult['course']->acl_id)
            || is_null($tokenAuthenticationResult['course']->alias)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        if (
            is_null($tokenAuthenticationResult['assignment']->problemset_id)
            || is_null($tokenAuthenticationResult['assignment']->alias)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        $director = \OmegaUp\DAO\UserRoles::getOwner(
            $tokenAuthenticationResult['course']->acl_id
        );
        if (is_null($director)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        // Log the operation only when there is not a token in request
        if (!$tokenAuthenticationResult['hasToken']) {
            // Authenticate request
            $r->ensureIdentity();
            \OmegaUp\DAO\ProblemsetAccessLog::create(new \OmegaUp\DAO\VO\ProblemsetAccessLog([
                'identity_id' => $r->identity->identity_id,
                'problemset_id' => $tokenAuthenticationResult['assignment']->problemset_id,
                'ip' => ip2long(
                    \OmegaUp\Request::getServerVar('REMOTE_ADDR') ?? ''
                ),
            ]));
        }

        return [
            'name' => strval($tokenAuthenticationResult['assignment']->name),
            'alias' => strval($tokenAuthenticationResult['assignment']->alias),
            'description' => $tokenAuthenticationResult['assignment']->description,
            'assignment_type' => $tokenAuthenticationResult['assignment']->assignment_type,
            'start_time' => $tokenAuthenticationResult['assignment']->start_time,
            'finish_time' => $tokenAuthenticationResult['assignment']->finish_time,
            'problems' => self::getProblemsByAssignment(
                $tokenAuthenticationResult['assignment']->alias,
                $tokenAuthenticationResult['course']->alias,
                $r->identity,
                $r->user
            ),
            'courseAssignments' => \OmegaUp\DAO\Courses::getAllAssignments(
                $courseAlias,
                $tokenAuthenticationResult['courseAdmin']
            ),
            'director' => $director,
            'problemset_id' => $tokenAuthenticationResult['assignment']->problemset_id,
            'admin' => $tokenAuthenticationResult['courseAdmin'],
        ];
    }

    /**
     * @return list<ProblemsetProblem>
     */
    private static function getProblemsByAssignment(
        string $assignmentAlias,
        string $courseAlias,
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Users $user
    ): array {
        $problems = [];
        $problemIndex = 0;
        foreach (
            \OmegaUp\DAO\ProblemsetProblems::getProblemsByAssignmentAlias(
                $assignmentAlias,
                $courseAlias
            ) as $problem
        ) {
            $problem['letter'] = \OmegaUp\Controllers\Contest::columnName(
                $problemIndex++
            );
            $problem['accepts_submissions'] = !empty($problem['languages']);

            if (
                is_null($identity)
                || is_null($identity->identity_id)
            ) {
                $nominationStatus = [
                    'solved' => false,
                    'tried' => false,
                    'nominated' => false,
                    'dismissed' => false,
                    'nominatedBeforeAc' => false,
                    'dismissedBeforeAc' => false,
                ];
            } else {
                $nominationStatus = \OmegaUp\DAO\QualityNominations::getNominationStatusForProblem(
                    $problem['problem_id'],
                    $identity->user_id
                );

                [
                    'tried' => $tried,
                    'solved' => $solved,
                ] = \OmegaUp\DAO\Runs::getSolvedAndTriedProblemByIdentity(
                    $problem['problem_id'],
                    $identity->identity_id
                );

                $nominationStatus['tried'] = $tried;
                $nominationStatus['solved'] = $solved;
                $problemStatement = \OmegaUp\Controllers\Problem::getProblemStatement(
                    $problem['alias'],
                    $problem['commit'],
                    \OmegaUp\Controllers\Identity::getPreferredLanguage(
                        $identity
                    )
                );
                $nominationStatus['language'] = (
                    !is_null($problemStatement) ?
                    $problemStatement['language'] :
                    'es'
                );
            }
            $nominationStatus['canNominateProblem'] = !is_null($user);
            $nominationStatus['problemAlias'] = $problem['alias'];
            $problem['quality_payload'] = $nominationStatus;
            unset($problem['problem_id']);

            $problems[] = $problem;
        }
        return $problems;
    }

    /**
     * Returns all runs for a course
     *
     * @return array{runs: list<Run>, totalRuns: int}
     *
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param 'EXECUTION_COMPILATION_ERROR'|'EXECUTION_FINISHED'|'EXECUTION_INTERRUPTED'|'EXECUTION_JUDGE_ERROR'|'EXECUTION_RUNTIME_ERROR'|'EXECUTION_RUNTIME_FUNCTION_ERROR'|'EXECUTION_VALIDATOR_ERROR'|null $execution
     * @omegaup-request-param 'c11-clang'|'c11-gcc'|'cat'|'cpp11-clang'|'cpp11-gcc'|'cpp17-clang'|'cpp17-gcc'|'cpp20-clang'|'cpp20-gcc'|'cs'|'go'|'hs'|'java'|'js'|'kj'|'kp'|'kt'|'lua'|'pas'|'py2'|'py3'|'rb'|'rs'|null $language
     * @omegaup-request-param int|null $offset
     * @omegaup-request-param 'OUTPUT_CORRECT'|'OUTPUT_EXCEEDED'|'OUTPUT_INCORRECT'|'OUTPUT_INTERRUPTED'|null $output
     * @omegaup-request-param null|string $problem_alias
     * @omegaup-request-param int|null $rowcount
     * @omegaup-request-param 'compiling'|'new'|'ready'|'running'|'waiting'|null $status
     * @omegaup-request-param null|string $username
     * @omegaup-request-param 'AC'|'CE'|'JE'|'MLE'|'NO-AC'|'OLE'|'PA'|'RFE'|'RTE'|'TLE'|'VE'|'WA'|null $verdict
     */
    public static function apiRuns(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        $assignmentAlias = $r->ensureString(
            'assignment_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problemAlias = $r->ensureOptionalString(
            'problem_alias',
            required: false,
            validator: fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $username = $r->ensureOptionalString('username');

        // Validate request
        [
            'assignment' => $assignment,
            'problem' => $problem,
            'identity' => $identity,
        ] = self::validateRuns(
            $r->identity,
            $assignmentAlias,
            $courseAlias,
            $problemAlias,
            $username
        );

        if (is_null($assignment->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        $languages = array_keys(
            \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES()
        );
        // Get our runs
        return self::getAllRuns(
            $assignment->problemset_id,
            $r->ensureOptionalEnum('status', \OmegaUp\Controllers\Run::STATUS),
            $r->ensureOptionalEnum(
                'verdict',
                \OmegaUp\Controllers\Run::VERDICTS
            ),
            !is_null($problem) ? $problem->problem_id : null,
            $r->ensureOptionalEnum('language', $languages),
            !is_null($identity) ? $identity->identity_id : null,
            max($r->ensureOptionalInt('offset') ?? 0, 0),
            $r->ensureOptionalInt('rowcount') ?? 100,
            $r->ensureOptionalEnum(
                'execution',
                array_keys(\OmegaUp\Controllers\Run::EXECUTION)
            ),
            $r->ensureOptionalEnum(
                'output',
                array_keys(\OmegaUp\Controllers\Run::OUTPUT)
            ),
        );
    }

    /**
     * Validates runs API
     *
     * @return array{assignment: \OmegaUp\DAO\VO\Assignments, identity: \OmegaUp\DAO\VO\Identities|null, problem: \OmegaUp\DAO\VO\Problems|null}
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateRuns(
        \OmegaUp\DAO\VO\Identities $loggedIdentity,
        string $assignmentAlias,
        string $courseAlias,
        ?string $problemAlias,
        ?string $username
    ): array {
        $course = self::validateCourseExists($courseAlias);

        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            $course->course_id
        );
        if (is_null($assignment)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        if (
            !\OmegaUp\Authorization::isAdminOrTeachingAssistant(
                $loggedIdentity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        // Check filter by problem, is optional
        $problem = null;
        if (!is_null($problemAlias)) {
            $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
            if (is_null($problem)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }
        }

        // Get user if we have something in username
        $identity = null;
        if (!is_null($username)) {
            $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
                $username
            );
        }

        return [
            'assignment' => $assignment,
            'problem' => $problem,
            'identity' => $identity,
        ];
    }

    /**
     * Returns details of a given course
     *
     * @return CourseDetails
     *
     * @omegaup-request-param string $alias
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);
        $group = self::resolveGroup($course);

        // Only Course Admins or Group Members (students) can see these results
        if (
            !\OmegaUp\Authorization::canViewCourse(
                $r->identity,
                $course,
                $group
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($course, $r->identity);
    }

    /**
     * Edit Course contents
     *
     * @return array{status: string}
     *
     * @omegaup-request-param 'private'|'public'|'registration'|null $admission_mode
     * @omegaup-request-param string $alias
     * @omegaup-request-param null|string $description
     * @omegaup-request-param null|string $level
     * @omegaup-request-param null|string $objective
     * @omegaup-request-param \OmegaUp\Timestamp|null $finish_time
     * @omegaup-request-param string $languages
     * @omegaup-request-param null|string $name
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param bool|null $recommended
     * @omegaup-request-param 'no'|'optional'|'required'|null $requests_user_information
     * @omegaup-request-param int $school_id
     * @omegaup-request-param bool|null $show_scoreboard
     * @omegaup-request-param \OmegaUp\Timestamp|null $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    public static function apiUpdate(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        $courseAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $updateRequests = $r->ensureOptionalEnum(
            'admission_mode',
            [
                self::ADMISSION_MODE_PUBLIC,
                self::ADMISSION_MODE_REGISTRATION,
                self::ADMISSION_MODE_PRIVATE,
            ]
        ) === self::ADMISSION_MODE_REGISTRATION;

        $course = self::validateUpdate($r, $courseAlias);

        if (
            !\OmegaUp\Authorization::isCourseAdmin(
                $r->identity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $valueProperties = [
            'alias',
            'name',
            'description',
            'objective',
            'languages' => ['important' => true],
            'start_time',
            'finish_time',
            'school_id',
            'show_scoreboard' => [
                'transform' => fn (string $value): bool => boolval($value),
            ],
            'needs_basic_information' => [
                'transform' => fn (string $value): bool => boolval($value),
            ],
            'level',
            'recommended' => [
                'transform' => fn (string $value): bool => boolval($value),
            ],
            'requests_user_information',
            'admission_mode',
        ];

        if (\OmegaUp\Authorization::isCertificateGenerator($r->identity)) {
            array_push($valueProperties, 'minimum_progress_for_certificate');
        }

        $importantChange = self::updateValueProperties(
            $r,
            $course,
            $valueProperties
        );
        $unlimitedDuration = $r->ensureOptionalBool(
            'unlimited_duration'
        ) ?? false;

        // Set null finish time if required
        if ($unlimitedDuration) {
            $course->finish_time = null;
        }

        try {
            // Begin a new transaction
            \OmegaUp\DAO\DAO::transBegin();

            // Push changes
            \OmegaUp\DAO\Courses::update($course);

            // It means column languages has been modified
            if ($importantChange) {
                \OmegaUp\DAO\Courses::updateLanguagesToAssignments(
                    $course,
                    $r->ensureString('languages')
                );
            }

            if ($updateRequests) {
                // Get the list of contestants
                $identities = \OmegaUp\DAO\GroupsIdentities::getGroupIdentities(
                    new \OmegaUp\DAO\VO\Groups([
                        'group_id' => $course->group_id,
                    ])
                );
                // Extract IDs
                $identitiesIDs = array_map(
                    /**
                     * @param array{identity_id: int} $identity
                     */
                    function ($identity): int {
                        return $identity['identity_id'];
                    },
                    $identities
                );
                self::preAcceptAccessRequest(
                    $course,
                    $identitiesIDs,
                    $r->user
                );
            }
        } catch (\Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }
        // TODO: Expire cache

        self::$log->info("Course updated (alias): {$courseAlias}");
        return [
            'status' => 'ok',
        ];
    }

    /**
     * Toggles the AI Teaching Assistant feature for a course
     *
     * @return array{status: string, teaching_assistant_enabled: bool}
     *
     * @omegaup-request-param string $course_alias
     */
    public static function apiToggleTeachingAssistant(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $course = self::validateCourseExists($courseAlias);

        if (
            !\OmegaUp\Authorization::isCourseAdmin(
                $r->identity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $course->teaching_assistant_enabled = !$course->teaching_assistant_enabled;

        try {
            \OmegaUp\DAO\Courses::update($course);
        } catch (\Exception $e) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'unableToUpdateTAEnabledField',
            );
        }

        self::$log->info(
            "Course AI TA toggled (alias): {$courseAlias}, enabled: " . ($course->teaching_assistant_enabled ? 'true' : 'false')
        );

        return [
            'status' => 'ok',
            'teaching_assistant_enabled' => $course->teaching_assistant_enabled,
        ];
    }

    /**
     * Gets the clarifications of all assignments in a course
     *
     * @return array{clarifications: list<Clarification>}
     *
     * @omegaup-request-param string $course_alias
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

        $course = self::validateCourseExists(
            $r->ensureString(
                'course_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            )
        );
        $group = self::resolveGroup($course);

        if (
            !\OmegaUp\Authorization::canViewCourse(
                $r->identity,
                $course,
                $group
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        $isAdmin = \OmegaUp\Authorization::isCourseAdmin($r->identity, $course);
        $isTeachingAssistant = self::isTeachingAssistant(
            $course,
            $r->identity
        );

        return [
            'clarifications' => \OmegaUp\DAO\Clarifications::getProblemsetClarifications(
                contest: null,
                course: $course,
                isAdmin: $isAdmin || $isTeachingAssistant,
                currentIdentity: $r->identity,
                offset: $offset,
                rowcount: $rowcount
            )['clarifications'],
        ];
    }

    /**
     * Gets the latest clarifications for course with pagination
     *
     * @return array{templateProperties: array{payload: CourseClarificationsPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     */
    public static function getClarificationsForTypeScript(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt('page_size') ?? 100;

        $course = self::validateCourseExists(
            $r->ensureString(
                'course_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            )
        );
        $group = self::resolveGroup($course);
        $isAdmin = \OmegaUp\Authorization::isCourseAdmin($r->identity, $course);
        $isTeachingAssistant = self::isTeachingAssistant(
            $course,
            $r->identity
        );
        if (
            !\OmegaUp\Authorization::canViewCourse(
                $r->identity,
                $course,
                $group
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $list = \OmegaUp\DAO\Clarifications::getProblemsetClarifications(
            contest: null,
            course: $course,
            isAdmin: $isAdmin || $isTeachingAssistant,
            currentIdentity: $r->identity,
            offset: $page,
            rowcount: $pageSize
        );

        return [
            'templateProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $pageSize,
                    'clarifications' => $list['clarifications'],
                    'totalRows' => $list['totalRows'],
                    'pagerItems' => \OmegaUp\Pager::paginateWithUrl(
                        $list['totalRows'],
                        $pageSize,
                        $page,
                        "/course/{$course->alias}/clarifications/",
                        adjacent: 2,
                        params: [],
                    ),
                    'is_admin' => $isAdmin,
                    'is_teaching_assistant' => $isTeachingAssistant,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCourseClarifications'
                ),
            ],
            'entrypoint' => 'course_clarifications',
        ];
    }

    /**
     * Get clarifications of problem in a contest
     *
     * @return array{clarifications: list<Clarification>}
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $assignment_alias
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

        $course = self::validateCourseExists(
            $r->ensureString(
                'course_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            )
        );

        $assignment = self::validateCourseAssignmentAlias(
            $course,
            $r->ensureString(
                'assignment_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            )
        );

        $problem = \OmegaUp\DAO\Problems::getByAliasAndProblemset(
            $r->ensureString(
                'problem_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            ),
            intval($assignment->problemset_id)
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        if (
            !\OmegaUp\Authorization::canViewCourse(
                $r->identity,
                $course,
                self::resolveGroup($course)
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'clarifications' => \OmegaUp\DAO\Clarifications::getProblemInProblemsetClarifications(
                $problem,
                intval($assignment->problemset_id),
                \OmegaUp\Authorization::isCourseAdmin(
                    $r->identity,
                    $course
                ),
                currentIdentity: $r->identity,
                offset: $offset,
                rowcount: $rowcount,
            ),
        ];
    }

    /**
     * Gets Scoreboard for an assignment
     *
     * @return Scoreboard
     *
     * @omegaup-request-param string $assignment
     * @omegaup-request-param string $course
     * @omegaup-request-param null|string $token
     */
    public static function apiAssignmentScoreboard(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignmentAlias = $r->ensureString(
            'assignment',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['token'],
            'token'
        );
        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $courseAlias,
            $assignmentAlias,
            $r['token'],
            $r
        );
        $group = self::resolveGroup($tokenAuthenticationResult['course']);

        if (!$tokenAuthenticationResult['hasToken']) {
            if (
                !\OmegaUp\Authorization::canViewCourse(
                    $r->identity,
                    $tokenAuthenticationResult['course'],
                    $group
                )
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
        }

        $scoreboard = new \OmegaUp\Scoreboard(
            new \OmegaUp\ScoreboardParams([
                'alias' => $tokenAuthenticationResult['assignment']->alias,
                'title' => $tokenAuthenticationResult['assignment']->name,
                'problemset_id' => $tokenAuthenticationResult['assignment']->problemset_id,
                'start_time' => $tokenAuthenticationResult['assignment']->start_time,
                'finish_time' => $tokenAuthenticationResult['assignment']->finish_time,
                'acl_id' => $tokenAuthenticationResult['assignment']->acl_id,
                'group_id' => $tokenAuthenticationResult['course']->group_id,
                'admin' => $tokenAuthenticationResult['courseAdmin'],
            ])
        );

        return $scoreboard->generate(
            withRunDetails: false,
            sortByName: false
        );
    }

    /**
     * Returns the Scoreboard events
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{events: list<ScoreboardEvent>}
     *
     * @omegaup-request-param string $assignment
     * @omegaup-request-param string $course
     * @omegaup-request-param null|string $token
     */
    public static function apiAssignmentScoreboardEvents(\OmegaUp\Request $r): array {
        $courseAlias = $r->ensureString(
            'course',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $assignmentAlias = $r->ensureString(
            'assignment',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['token'],
            'token'
        );
        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $courseAlias,
            $assignmentAlias,
            $r['token'],
            $r
        );
        if (is_null($tokenAuthenticationResult['course']->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        $scoreboard = new \OmegaUp\Scoreboard(
            \OmegaUp\ScoreboardParams::fromAssignment(
                $tokenAuthenticationResult['assignment'],
                $tokenAuthenticationResult['course']->group_id,
                showAllRuns: $tokenAuthenticationResult['courseAdmin'],
            )
        );

        // Push scoreboard data in response
        return [
            'events' => $scoreboard->events()
        ];
    }

    /**
     * Get Problems solved by users of a course
     *
     * @return array{user_problems: array<string, list<array{alias: string, title: string, username: string}>>}
     *
     * @omegaup-request-param string $course_alias
     */
    public static function apiListSolvedProblems(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        $usersProblems = \OmegaUp\DAO\Problems::getProblemsByUsersInACourse(
            $courseAlias
        );
        $userProblems = [];
        foreach ($usersProblems as $userProblem) {
            if (!$userProblem['solved']) {
                continue;
            }
            $userProblems[$userProblem['username']][] = $userProblem;
        }
        return ['user_problems' => $userProblems];
    }

    /**
     * Get Problems unsolved by users of a course
     *
     * @return array{user_problems: array<string, list<array{alias: string, title: string, username: string}>>}
     *
     * @omegaup-request-param string $course_alias
     */
    public static function apiListUnsolvedProblems(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $course = self::validateCourseExists($courseAlias);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        $usersProblems = \OmegaUp\DAO\Problems::getProblemsByUsersInACourse(
            $courseAlias
        );
        $userProblems = [];
        foreach ($usersProblems as $userProblem) {
            if ($userProblem['solved']) {
                continue;
            }
            $userProblems[$userProblem['username']][] = $userProblem;
        }
        return ['user_problems' => $userProblems];
    }

    public static function shouldShowScoreboard(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Groups $group
    ): bool {
        return $course->show_scoreboard && \OmegaUp\Authorization::canViewCourse(
            $identity,
            $course,
            $group
        );
    }
}
