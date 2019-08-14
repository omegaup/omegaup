declare namespace omegaup {
  export interface Badge {
    badge_alias: string;
    assignation_time?: Date;
    unlocked?: boolean;
    first_assignation?: Date;
    owners_percentage?: number;
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

  export interface Identity {
    name: string;
    username: string;
    school: string;
    school_id: number;
    country_id: string;
    state_id: string;
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

  export interface SchoolsRank {
    country_id: string;
    distinct_problems: number;
    distinct_users: number;
    name: string;
  }

  export interface Solutions {
    [language: string]: string;
  }
}

export default omegaup;