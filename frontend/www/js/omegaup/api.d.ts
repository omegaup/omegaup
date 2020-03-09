declare namespace omegaup {
  export class Selectable<T> {
    value: T;
    selected: boolean;
  }

  enum AdmissionMode {
    Private = 'private',
    Registration = 'registration',
    Public = 'public',
  }

  enum RequestsUserInformation {
    No = 'no',
    Optional = 'optional',
    Required = 'required',
  }

  export interface ArenaContests {
    [timeType: string]: omegaup.Contest[];
  }

  export interface Assignment {
    alias: string;
    assignment_type: string,
    description: string;
    finish_time: Date;
    has_runs?: boolean;
    max_points?: number;
    name: string;
    order: number;
    publish_time_delay?: number;
    scoreboard_url: string;
    scoreboard_url_admin: string;
    start_time: Date;
  }

  export interface AssignmentProblem {
    alias: string;
    commit: string;
    languages: string;
    letter: string;
    order: number;
    points: number;
    title: string;
    version: string;
  }

  export interface Badge {
    badge_alias: string;
    assignation_time?: Date;
    unlocked?: boolean;
    first_assignation?: Date;
    total_users?: number;
    owners_count?: number;
  }

  interface Case {
    contest_score: number;
    max_score: number;
    meta: omegaup.Meta;
    name: string;
    score: number;
    verdict: string;
  }

  export interface CoderOfTheMonth extends Profile {
    date?: string;
    ProblemsSolved?: number;
    score?: number;
    country?: string;
    state?: string;
    school?: string;
  }

  export interface Commit {
    author: Signature;
    commit: string;
    commiter: Signature;
    message: string;
    parents: string[];
    tree: {
      [file: string]: string;
    }
    version: string;
  }

  export interface Contest {
    alias: string;
    title: string;
    window_length?: number;
    start_time?: Date;
    finish_time?: Date;
    admission_mode?: omegaup.AdmissionMode;
    contestant_must_register?: boolean;
    admin?: boolean;
    available_languages?: omegaup.Languages;
    description?: string;
    director?: string;
    feedback?: string;
    languages?: Array<string>;
    needs_basic_information?: boolean;
    opened?: boolean;
    original_contest_alias?: string;
    original_problemset_id?: string;
    partial_score?: boolean;
    penalty?: number;
    penalty_calc_policy?: string;
    penalty_type?: string;
    points_decay_factor?: number;
    problems?: omegaup.Problem[];
    problemset_id?: number;
    requests_user_information?: omegaup.RequestsUserInformation;
    rerun_id?: number;
    scoreboard?: number;
    scoreboard_url?: string;
    scoreboard_url_admin?: string;
    show_penalty?: boolean;
    show_scoreboard_after?: boolean;
    submission_deadline?: Date;
    submissions_gap?: number;
  }

  interface ContestAdmin {
    username: string;
    role: string;
  }

  export interface ContestGroup {
    alias: string;
    name: string;
  }

  export interface ContestGroupAdmin {
    role?: string;
    name?: string;
    alias?: string;
  }

  export interface ContestProblem {
    alias: string;
    text: string;
    bestScore: number;
    maxScore: number;
    active: boolean;
  }

  interface ContestResult {
    data: omegaup.Contest;
    length?: string;
    place: number;
  }

  export interface Country {
    id: string;
    name: string;
  }

  export interface Course {
    alias: string;
    assignments: Assignment[];
    basic_information_required: boolean;
    description: string;
    finish_time: Date;
    is_admin: boolean;
    name: string;
    public: boolean;
    requests_user_information: omegaup.RequestsUserInformation;
    school_id?: number;
    school_name: string;
    show_scoreboard: boolean;
    start_time: Date;
    student_count: boolean;
  }

  export interface CourseAdmin {
    username: string;
    role: string;
  }

  export interface CourseGroupAdmin {
    role: string;
    name: string;
    alias: string;
  }

  export interface CourseProblem extends Problem {
    commit: string;
    letter: string;
    order: number;
    runs: CourseProblemRun[];
    submissions: number;
    visits: number;
  }

  export interface CourseProblemRun {
    penalty: number;
    score: number;
    source: string;
    time: string;
    verdict: string;
  }

  interface CourseProgress {
    [assignment: string]: number;
  }

  export interface CourseStudent {
    name: string;
    username: string;
    progress: CourseProgress;
  }

  interface DetailsGroup {
    cases: omegaup.Case[];
    contest_score: number;
    group: string;
    max_score: number;
    score: number;
  }

  interface Details {
    group: omegaup.DetailsGroup[];
  }

  export interface Experiment {
    config: boolean;
    hash: string;
    name: string;
  }

  interface GraderQueue {
    running: string[];
    run_queue_length: number;
    runner_queue_length: number;
    runners: string[];
  }

  export interface Grader {
    status: string;
    broadcaster_sockets: number;
    embedded_runner: boolean;
    queue: omegaup.GraderQueue;
  }

  export interface Group {
    alias: string;
    create_time: Date;
    description: string;
    name: string;
  }

  export interface Identity extends User {
    name: string;
    username: string;
    school: string;
    school_name?: string;
    gender?: string;
    password?: string;
    school_id: number;
    country_id: string;
    state_id: string;
    classname: string;
  }

  export interface IdentityContest {
    username: string;
    end_time?: Date;
    access_time?: Date;
    country_id?: string;
  }

  export interface IdentityContestRequest {
    username: string;
    country: string;
    request_time: Date;
    last_update: Date;
    accepted: boolean;
    admin?: ContestAdmin;
  }

  interface Languages {
    [language: string]: string;
  }

  interface Meta {
    time: number;
    wall_time: number;
    memory: number;
  }

  export interface NavbarPayload {
    omegaUpLockDown: boolean;
    inContest: boolean;
    isLoggedIn: boolean;
    isReviewer: boolean;
    gravatarURL51: string;
    currentUsername: string;
    isAdmin: boolean;
    isMainUserIdentity: boolean;
    lockDownImage: string;
    navbarSection: string;
  }

  export interface NominationVote {
    time: number;
    vote: number;
    user: User;
  }

  export interface Nomination {
    author: User;
    author_name: string;
    author_username: string;
    nomination: string;
    nominator: User;
    nominator_name: string;
    nominator_username: string;
    problem: Problem;
    quality_nomination_id: number;
    status: string;
    time: string;
    votes: NominationVote[];
  }

  export interface Notification {
    notification_id: number;
    contents: NotificationContents;
    timestamp: Date;
  }

  export interface NotificationContents {
    type: string;
    badge?: string;
  }

  export interface Profile extends User {
    email: string;
    country_id: string;
    gravatar_92: string;
    rankinfo: RankInfo;
    classname: string;
  }

  export interface Problem {
    accepted?: number;
    alias: string;
    commit?: string;
    difficulty?: number;
    languages?: string;
    letter?: string;
    order: number;
    penalty?: number;
    percent?: number;
    points: number;
    quality?: number;
    quality_seal?: boolean;
    ratio?: number;
    run_details?: omegaup.RunDetails;
    runs?: CourseProblemRun[];
    score?: number;
    source?: string;
    statement?: Statement;
    submissions?: number;
    templates?: string;
    tags?: Tag[];
    title: string;
    version?: string;
    visibility?: number;
    visits?: number;
  }

  export interface QueryParameters {
    some_tags: boolean;
    min_difficulty: number;
    max_difficulty: number;
    order_by: string;
    mode: string;
    only_karel?: boolean;
    tag?: string[];
  }

  export interface RankInfo {
    rank: number;
    name?: string;
    problems_solved: number;
  }

  export interface Scoreboard {
    contests: omegaup.Contest[];
    name: string;
    place: number;
    totalPenalty: number;
    totalPoints: number;
  }

  export interface ScoreboardUser extends User {
    country?: string;
    is_invited: boolean;
    place: number;
    problems: ScoreboardUserProblem[];
    total: {
      penalty: number;
      points: number;
    }
  }

  export interface ScoreboardUserProblem {
    alias: string;
    penalty: number;
    pending?: boolean;
    percent: number;
    points: number;
    runs: number;
  }

  interface Statement {
    images: string[];
    language: string;
    markdown: string;
  }

  export interface Report {
    classname: string;
    event: {
      name: string;
      problem: string;
    }
    ip: string;
    time: string;
    username: string;
  }

  export interface Role {
    name: string;
  }

  export interface Run {
    [period: string]: number;
  }

  export interface RunInfo {
    date: string;
    verdict: string;
    runs: number;
    [period: string]: string;
  }

  export interface RunCounts {
    categories: string[];
    cumulative: omegaup.RunData[];
    delta: omegaup.RunData[];
  }

  interface RunData {
    data: number[];
    name: string;
  }

  interface RunDetails {
    admin?: boolean;
    compile_error?: string;
    details?: Details;
    groups?: DetailsGroup[];
    guid: string;
    judged_by: string;
    language: string;
    logs: string;
    problem_admin?: boolean;
    source?: string;
    source_link?: boolean;
    source_name?: string;
    source_url?: string;
  }

  export interface SchoolCoderOfTheMonth {
    classname: string;
    time: string;
    username: string;
  }

  export interface SchoolOfTheMonth extends SchoolsRank {
    time?: string;
  }

  export interface SchoolsRank {
    school_id: number;
    country_id: string;
    score?: number;
    name: string;
    rank?: number;
  }

  export interface SchoolRankTable {
    page: number;
    length: number;
    showHeader: boolean;
    totalRows: number;
    rank: omegaup.SchoolsRank[];
  }

  interface Signature {
    email: string;
    name: string;
    time: string;
  }

  export interface Solutions {
    [language: string]: string;
  }

  export interface Stats {
    total_runs: string;
    pending_runs: Array<string>;
    max_wait_time: number;
    max_wait_time_guid: number;
    verdict_runs: Verdict;
    distribution: Array<number>;
    size_of_bucket: number;
    total_points: number;
  }

  export interface Submission {
    time: Date;
    username: string;
    school_id: number;
    school_name: string;
    alias: string;
    title: string;
    language: string;
    verdict: string;
    runtime: number;
    memory: number;
    classname: string;
  }

  export interface Tag {
    source?: string;
    name: string;
  }

  export interface UserRole {
    role: string;
    username: string;
  }

  export interface UserRank {
    penalty?: number
    points?: number;
    position?: number;
    username: string;
    classname: string;
    country: string;
    name?: string;
    score?: number;
    problemsSolvedUser?: number;
  }

  export interface UserRankTable {
    page: number;
    length: number;
    isIndex: boolean;
    isLogged: boolean;
    availableFilters: { [key: string]: string };
    filter: string;
    ranking: omegaup.UserRank[];
    resultTotal: number;
  }

  export interface User {
    name?: string;
    username: string;
  }

  interface Verdict {
    [verdict: string]: number;
  }

  export interface VerdictByDate {
    [date: string]: Verdict;
  }
}

export default omegaup;
