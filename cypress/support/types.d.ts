export interface LoginOptions {
  username: string;
  password: string;
}

export interface ProblemOptions {
  problemAlias: string;
  tag: string;
  autoCompleteTextTag: string;
  problemLevelIndex: number;
}

export interface CourseOptions {
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

export interface CaseOptions {
  caseName: string;
  groupName: string;
  points: number | null;
  autoPoints: boolean;
}

export type RequestParticipantInformation = 'no' | 'optional' | 'required';

export type ProblemLevel = 'introductory' | 'intermediate' | 'advanced';
