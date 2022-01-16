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

export interface RunOptions {
  problemAlias: string;
  fixturePath: string;
  language: Language;
}

export type RequestParticipantInformation = 'no' | 'optional' | 'required';
export type ProblemLevel = 'introductory' | 'intermediate' | 'advanced';
export type Language =
  | 'c11-gcc'
  | 'c11-clang'
  | 'cpp11-gcc'
  | 'cpp11-clang'
  | 'cpp17-gcc'
  | 'cpp17-clang'
  | 'java'
  | 'py2'
  | 'py3'
  | 'rb'
  | 'cs'
  | 'pas'
  | 'hs'
  | 'lua';
export type Status = 'AC' | 'TLE' | 'MLE' | 'PA';
