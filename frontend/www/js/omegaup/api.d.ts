declare namespace omegaup {
  export interface Badge {
    badge_alias: string;
    assignation_time: string;
    unlocked?: boolean;
  }

  export interface Contest {
    alias: string;
    title: string;
  }

  export interface Identity {
    name: string;
    username: string;
    school: string;
    school_id: number;
    country_id: string;
    state_id: string;
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
}

export default omegaup;