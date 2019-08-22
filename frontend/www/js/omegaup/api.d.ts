declare namespace omegaup {
  export class Selectable<T> {
    value: T;
    selected: boolean;
  }

  export interface Assignment {
    alias: string;
    assignment_type: string,
    description: string;
    finish_time: Date;
    has_runs: boolean;
    name: string;
    order: number;
    scoreboard_url: string;
    scoreboard_url_admin: string;
    start_time: Date;
  }

  export interface Badge {
    badge_alias: string;
    assignation_time?: Date;
    unlocked?: boolean;
    first_assignation?: Date;
    owners_percentage?: number;
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
    admission_mode?: string;
  }

  interface ContestAdmin {
    username: string;
  }

  interface ContestResult {
    data: omegaup.Contest;
    length?: string;
    place: number;
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

  interface CourseProgress {
    [assignment: string]: number;
  }

  export interface CourseStudent {
    name?: string;
    username: string;
    progress: CourseProgress[];
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

  export interface Group {
    alias: string;
  }

  export interface Identity {
    name: string;
    username: string;
    school: string;
    school_id: number;
    country_id: string;
    state_id: string;
  }

  export interface IdentityContest {
    username: string;
    end_time: Date;
  }

  export interface IdentityContestRequest {
    username: string;
    country: string;
    request_time: Date;
    last_update: Date;
    accepted: boolean;
    admin?: ContestAdmin;

  }

  interface Meta {
    time: number;
    wall_time: number;
    memory: number;
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

  export interface Profile {
    username: string;
    name: string;
    email: string;
    country_id: string;
    gravatar_92: string;
    rankinfo: RankInfo;
    classname: string;
  }

  export interface Problem {
    accepted?: number;
    alias: string;
    difficulty?: number;
    penalty?: number;
    percent?: number;
    points?: number;
    quality?: number;
    ratio?: number;
    run_details?: omegaup.RunDetails;
    score?: number;
    submissions?: number;
    tags?: Tag[];
    title: string;
    visibility?: number;
  }

  export interface Profile {
    username: string;
    name: string;
    email: string;
    country_id: string;
    gravatar_92: string;
    rankinfo: RankInfo;
    classname: string;
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
    title: string;
  }

  interface RunDetails {
    admin: boolean;
    details: omegaup.Details;
    guid: string;
    judged_by: string;
    language: string;
    logs: string;
  }

  export interface SchoolsRank {
    country_id: string;
    distinct_problems: number;
    distinct_users: number;
    name: string;
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

  interface Verdict {
    [verdict: string]: number;
  }

  export interface Tag {
    autogenerated?: boolean;
    name: string;
  }
}

export default omegaup;