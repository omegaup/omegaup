export interface LoginOptions {
  username: string;
  password: string;
}

export interface Identities {
  usernames: string;
  passwords: string;
}

export interface GroupOptions {
  groupTitle: string;
  groupDescription: string;
}

export interface TeamGroupOptions {
  groupTitle: string;
  groupDescription: string;
  noOfContestants: string;
}

export interface ProblemOptions {
  problemAlias: string;
  tag: string;
  autoCompleteTextTag: string;
  problemLevelIndex: number;
}

export interface CourseOptions {
  courseAlias: string;
  startDate: Date;
  endDate?: Date;
  showScoreboard?: boolean;
  unlimitedDuration?: boolean;
  school?: string;
  basicInformation?: boolean;
  requestParticipantInformation?: RequestParticipantInformation;
  problemLevel?: ProblemLevel;
  objective?: string;
  description?: string;
}

declare enum ScoreMode {
  AllOrNothing = 'all_or_nothing',
  Partial = 'partial',
  MaxPerGroup = 'max_per_group',
}

export interface ContestOptions {
  contestAlias: string;
  description?: string;
  startDate: Date;
  endDate: Date;
  showScoreboard?: boolean;
  scoreBoardVisibleTime?: string;
  differentStart?: boolean;
  differentStartTime?: string;
  scoreMode: ScoreMode;
  basicInformation?: boolean;
  requestParticipantInformation?: RequestParticipantInformation;
  admissionMode: AdmissionModeOptions;
  problems: Array<ProblemOptions>;
  runs: Array<RunOptions>;
}

export interface RunOptions {
  problemAlias: string;
  fixturePath: string;
  language: Language;
  valid: boolean;
  status: Status;
}

export type AdmissionModeOptions = 'private' | 'registration' | 'public';
export type RequestParticipantInformation = 'no' | 'optional' | 'required';
export type ProblemLevel = 'introductory' | 'intermediate' | 'advanced';
export type Language =
  | 'c11-gcc'
  | 'c11-clang'
  | 'cpp11-gcc'
  | 'cpp11-clang'
  | 'cpp17-gcc'
  | 'cpp17-clang'
  | 'cpp20-gcc'
  | 'cpp20-clang'
  | 'java'
  | 'kt'
  | 'py2'
  | 'py3'
  | 'rb'
  | 'cs'
  | 'pas'
  | 'hs'
  | 'lua'
  | 'go'
  | 'rs'
  | 'js';
export type Status = 'AC' | 'TLE' | 'MLE' | 'PA' | 'WA';
