export interface LoginInfo {
  username: string;
  password: string;
}

export interface ProblemInfo {
  problemAlias: string;
  tag: string;
  autoCompleteTextTag: string;
  problemLevelIndex: number;
}

export interface CourseInfo {
  courseAlias: string;
  showScoreboard?: boolean;
  startDate?: Date;
  unlimitedDuration?: boolean;
  endDate?: Date;
  school?: string;
  basicInformation?: boolean;
  requestParticipantInformation?: RequestParticipantInformation;
  problemLevel?: ProblemLevel;
  objective?: string;
  description?: string;
}

export type RequestParticipantInformation = 'no' | 'optional' | 'required';

export type ProblemLevel = 'introductory' | 'intermediate' | 'advanced';
