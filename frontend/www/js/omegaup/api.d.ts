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

  export interface Contest {
    alias: string;
    title: string;
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

  export interface Identity {
    name: string;
    username: string;
    school: string;
    school_id: number;
    country_id: string;
    state_id: string;
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

  export interface Problem {
    alias: string;
    title: string;
    accepted: number;
    submissions: number;
    penalty?: number;
    percent?: number;
    points?: number;
    run_details?: omegaup.RunDetails;
  }

  interface RunDetails {
    admin: boolean;
    details: omegaup.Details;
    guid: string;
    judged_by: string;
    language: string;
    logs: string;
    status: string;
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

  export interface RankInfo {
    rank: number;
    name?: string;
    problems_solved: number;
  }

  export interface Solutions {
    [language: string]: string;
  }
}

export default omegaup;