import * as ui from './ui_transitional';
import * as api from './api_transitional';
import * as errors from './errors';

// This is the JavaScript version of the frontend's Experiments class.
export class Experiments {
  enabledExperiments: { [experiment: string]: boolean } = {};

  constructor(experimentList: Array<string>) {
    if (!experimentList) return;
    for (let experiment of experimentList)
      this.enabledExperiments[experiment] = true;
  }

  // Current frontend-available experiments:
  static get IDENTITIES(): string {
    return 'identities';
  }

  // The list of all enabled experiments for a particular request should have
  // been injected into the DOM by Smarty.
  static loadGlobal(): Experiments {
    const experimentsNode = document.getElementById(
      'omegaup-enabled-experiments',
    );
    let experimentsList: Array<string> = [];
    if (experimentsNode) experimentsList = experimentsNode.innerText.split(',');
    return new Experiments(experimentsList);
  }

  isEnabled(name: string): boolean {
    return this.enabledExperiments.hasOwnProperty(name);
  }
}

// Holds event listeners and notifies them exactly once. An event listener that
// is added after the .notify() method has been called will be notified
// immediately without adding it to the list.
export class EventListenerList {
  listenerList: Array<() => void> = [];
  ready: boolean = false;

  constructor(listenerList: Array<() => void>) {
    if (!listenerList) return;
    for (let listener of listenerList) this.listenerList.push(listener);
  }

  notify(): void {
    this.ready = true;
    for (let listener of this.listenerList) listener();
    this.listenerList = [];
  }

  add(listener: () => void): void {
    if (this.ready) {
      listener();
      return;
    }

    this.listenerList.push(listener);
  }
}

export namespace omegaup {
  export interface Selectable<T> {
    value: T;
    selected: boolean;
  }

  export enum AdmissionMode {
    Private = 'private',
    Registration = 'registration',
    Public = 'public',
  }

  export enum RequestsUserInformation {
    No = 'no',
    Optional = 'optional',
    Required = 'required',
  }

  export interface ArenaContests {
    [timeType: string]: omegaup.Contest[];
  }

  export interface Assignment {
    alias: string;
    assignment_type: string;
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

  export interface Case {
    contest_score: number;
    max_score: number;
    meta: Meta;
    name: string;
    score: number;
    verdict: string;
  }

  export interface Clarification {
    clarification_id: number;
    problem_alias: string;
    author: string;
    message: string;
    answer?: string;
    public: number;
    receiver?: string;
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
    };
    version: string;
  }

  export interface Contest {
    alias: string;
    title: string;
    window_length?: number;
    start_time?: Date;
    finish_time?: Date;
    admission_mode?: AdmissionMode;
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
    acceptsSubmissions: boolean;
    bestScore: number;
    maxScore: number;
    active: boolean;
  }

  export interface ContestResult {
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

  export interface DetailsGroup {
    cases: omegaup.Case[];
    contest_score: number;
    group: string;
    max_score: number;
    score: number;
  }

  export interface Details {
    group: omegaup.DetailsGroup[];
  }

  export interface Experiment {
    config: boolean;
    hash: string;
    name: string;
  }

  export interface GraderQueue {
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

  export interface IdentityRequest {
    username: string;
    country: string;
    request_time: Date;
    last_update: Date;
    accepted: boolean;
    admin?: UserRole;
  }

  export interface Languages {
    [language: string]: string;
  }

  export interface Meta {
    time: number;
    wall_time: number;
    memory: number;
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
    };
  }

  export interface ScoreboardUserProblem {
    alias: string;
    penalty: number;
    pending?: boolean;
    percent: number;
    points: number;
    runs: number;
  }

  export interface Statement {
    images: string[];
    language: string;
    markdown: string;
  }

  export interface StatementProblems {
    name: string;
  }

  export interface Report {
    classname: string;
    event: {
      name: string;
      problem: string;
    };
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

    day?: string;
    week?: string;
    month?: string;
    year?: string;
  }

  export interface RunCounts {
    categories: string[];
    cumulative: omegaup.RunData[];
    delta: omegaup.RunData[];
  }

  export interface RunData {
    data: number[];
    name: string;
  }

  export interface RunDetails {
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
    ranking?: number;
  }

  export interface SchoolRankTable {
    page: number;
    length: number;
    showHeader: boolean;
    totalRows: number;
    rank: omegaup.SchoolsRank[];
  }

  export interface Signature {
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
    verdict_runs: omegaup.Verdict;
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
    penalty?: number;
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

  export interface Verdict {
    [verdict: string]: number;
  }

  export interface VerdictByDate {
    [date: string]: Verdict;
  }

  export class OmegaUp {
    loggedIn: boolean = false;
    username: string | null = null;
    ready: boolean = false;
    experiments: Experiments | null = null;
    email?: string;
    identity?: omegaup.Identity;

    _documentReady: boolean = false;
    _initialized: boolean = false;
    _remoteDeltaTime?: number = undefined;
    _deltaTimeForTesting: number = 0;
    _listeners: { [name: string]: EventListenerList } = {
      ready: new EventListenerList([
        () => {
          this.experiments = Experiments.loadGlobal();
        },
      ]),
    };

    _onDocumentReady(): void {
      this._documentReady = true;
      if (this.ready) {
        this._notify('ready');
        return;
      }
      // TODO(lhchavez): Remove this.
      this._initialize();
    }

    _initialize(): void {
      if (this._initialized) {
        return;
      }
      this._initialized = true;
      const t0 = this._realTime();
      api.Session.currentSession()
        .then((data: { [name: string]: any }) => {
          if (data.session.valid) {
            this.loggedIn = true;
            this.username = data.session.identity.username;
            this.identity = data.session.identity;
            this.email = data.session.email;
          }
          this._remoteDeltaTime = t0 - data.time * 1000;

          this.ready = true;
          if (this._documentReady) {
            this._notify('ready');
          }
        })
        .catch(ui.apiError);
    }

    _notify(eventName: string): void {
      if (!this._listeners.hasOwnProperty(eventName)) return;
      this._listeners[eventName].notify();
    }

    on(events: string, handler: () => void): void {
      this._initialize();
      for (const eventName of events.split(' ')) {
        if (!this._listeners.hasOwnProperty(eventName)) continue;
        this._listeners[eventName].add(handler);
      }
    }

    _realTime(timestamp?: number): number {
      if (typeof timestamp !== 'undefined') {
        return timestamp + this._deltaTimeForTesting;
      }
      return Date.now() + this._deltaTimeForTesting;
    }

    remoteTime(
      timestamp: number,
      options: { server_sync?: boolean } = {},
    ): Date {
      options.server_sync =
        typeof options.server_sync === 'undefined' ? true : options.server_sync;
      return new Date(
        this._realTime(timestamp) +
          (options.server_sync ? this._remoteDeltaTime || 0 : 0),
      );
    }

    convertTimes(item: { [key: string]: any }): any {
      if (item.hasOwnProperty('time')) {
        item.time = this.remoteTime(item.time * 1000);
      }
      if (item.hasOwnProperty('end_time')) {
        item.end_time = this.remoteTime(item.end_time * 1000);
      }
      if (item.hasOwnProperty('start_time')) {
        item.start_time = this.remoteTime(item.start_time * 1000);
      }
      if (item.hasOwnProperty('finish_time')) {
        item.finish_time = this.remoteTime(item.finish_time * 1000);
      }
      if (item.hasOwnProperty('last_updated')) {
        item.last_updated = this.remoteTime(item.last_updated * 1000);
      }
      if (item.hasOwnProperty('submission_deadline')) {
        item.submission_deadline = this.remoteTime(
          item.submission_deadline * 1000,
        );
      }
      return item;
    }

    addError(error: any): void {
      errors.addError(error);
    }
  }
}

export const OmegaUp = new omegaup.OmegaUp();
