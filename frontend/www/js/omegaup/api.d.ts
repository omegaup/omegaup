declare namespace omegaup {
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
  }

  interface ContestResult {
    data: omegaup.Contest;
    length?: string;
    place: number;
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

  export interface Identity extends User {
    school: string;
    school_id: number;
    country_id: string;
    state_id: string;
  }

  export interface IdentityContest {
    username: string;
    end_time: Date;
  }

  interface Meta {
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

  export interface Tag {
    autogenerated?: boolean;
    name: string;
  }

  export interface User {
    name: string;
    username: string;
  }
}

export default omegaup;