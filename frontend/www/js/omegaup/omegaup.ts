import * as ui from './ui';
import * as api from './api';
import { types } from './api_types';
import * as errors from './errors';
import * as time from './time';

// This is the JavaScript version of the frontend's Experiments class.
export class Experiments {
  enabledExperiments: { [experiment: string]: boolean } = {};

  constructor(experimentList: Array<string>) {
    if (!experimentList) return;
    for (const experiment of experimentList)
      this.enabledExperiments[experiment] = true;
  }

  // Current frontend-available experiments:

  // The list of all enabled experiments for a particular request should have
  // been injected into the DOM through the template.
  static loadGlobal(): Experiments {
    const experimentsNode = document?.getElementById(
      'omegaup-enabled-experiments',
    );
    let experimentsList: Array<string> = [];
    if (experimentsNode) experimentsList = experimentsNode.innerText.split(',');
    return new Experiments(experimentsList);
  }

  isEnabled(name: string): boolean {
    return Object.prototype.hasOwnProperty.call(this.enabledExperiments, name);
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
    for (const listener of listenerList) this.listenerList.push(listener);
  }

  notify(): void {
    this.ready = true;
    for (const listener of this.listenerList) listener();
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

  export enum AssignmentFormMode {
    Default,
    New,
    Edit,
    AddProblem,
  }

  export enum ColumnType {
    Number = 'number',
    String = 'string',
  }

  export enum CountdownFormat {
    AssignmentHasNotStarted,
    ContestHasNotStarted,
    EventCountdown,
    WaitBetweenUploadsSeconds,
  }

  export enum SortOrder {
    Ascending = 'asc',
    Descending = 'desc',
  }

  export enum RequestsUserInformation {
    No = 'no',
    Optional = 'optional',
    Required = 'required',
  }

  export enum SubmissionFeedback {
    None = 'none',
    Summary = 'summary',
    Detailed = 'detailed',
  }

  export interface Assignment {
    alias: string;
    assignment_type: string;
    description: string;
    finish_time: Date | null;
    has_runs?: boolean;
    max_points?: number;
    name: string;
    order: number;
    publish_time_delay?: number;
    scoreboard_url: string;
    scoreboard_url_admin: string;
    start_time: Date;
  }

  export interface Case {
    contest_score: number;
    max_score: number;
    meta: Meta;
    name: string;
    score: number;
    verdict: string;
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

  export interface Country {
    id: string;
    name: string;
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
    time: Date;
    verdict: string;
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

  export interface IdentityContest {
    username: string;
    end_time?: Date;
    access_time?: Date;
    country_id?: string;
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
    input_limit: number;
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
    min_difficulty?: number;
    max_difficulty?: number;
    difficulty_range: string;
    order_by: string;
    sort_order: string;
    only_karel?: boolean;
    tag?: string[];
  }

  export interface RankInfo {
    rank: number;
    name?: string;
    problems_solved: number;
    author_ranking: number | null;
  }

  export interface Statement {
    images: string[];
    language: string;
    markdown: string;
  }

  export interface StatementProblems {
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
    feedback?: SubmissionFeedback;
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

  export interface SchoolOfTheMonth extends SchoolsRank {
    time?: string;
    country?: string;
    state?: string;
  }

  export interface SchoolsRank {
    school_id: number;
    country_id?: string;
    score: number;
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
    identity?: types.Identity;

    _documentReady: boolean = false;
    _initialized: boolean = false;
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
      const t0 = Date.now();
      api.Session.currentSession()
        .then((data: { [name: string]: any }) => {
          if (data.session.valid) {
            this.loggedIn = true;
            this.username = data.session.identity.username;
            this.identity = data.session.identity;
            this.email = data.session.email;
          }
          time._setRemoteDeltaTime(t0 - data.time * 1000);

          this.ready = true;
          if (this._documentReady) {
            this._notify('ready');
          }
        })
        .catch(ui.apiError);
    }

    _notify(eventName: string): void {
      if (!Object.prototype.hasOwnProperty.call(this._listeners, eventName))
        return;
      this._listeners[eventName].notify();
    }

    on(events: string, handler: () => void): void {
      this._initialize();
      for (const eventName of events.split(' ')) {
        if (!Object.prototype.hasOwnProperty.call(this._listeners, eventName))
          continue;
        this._listeners[eventName].add(handler);
      }
    }

    remoteTime(timestamp: number | Date): Date {
      if (timestamp instanceof Date) {
        return time.remoteDate(timestamp);
      }
      return time.remoteTime(timestamp);
    }

    convertTimes(item: { [key: string]: any }): any {
      if (Object.prototype.hasOwnProperty.call(item, 'time')) {
        item.time = time.remoteTime(item.time * 1000);
      }
      if (Object.prototype.hasOwnProperty.call(item, 'end_time')) {
        item.end_time = time.remoteTime(item.end_time * 1000);
      }
      if (Object.prototype.hasOwnProperty.call(item, 'start_time')) {
        item.start_time = time.remoteTime(item.start_time * 1000);
      }
      if (Object.prototype.hasOwnProperty.call(item, 'finish_time')) {
        item.finish_time = time.remoteTime(item.finish_time * 1000);
      }
      if (Object.prototype.hasOwnProperty.call(item, 'last_updated')) {
        item.last_updated = time.remoteTime(item.last_updated * 1000);
      }
      if (Object.prototype.hasOwnProperty.call(item, 'submission_deadline')) {
        item.submission_deadline = time.remoteTime(
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
// Prevent image dragging for better UX
document.addEventListener('dragstart', (event) => {
  if (event.target instanceof HTMLImageElement) {
    event.preventDefault();
  }
});