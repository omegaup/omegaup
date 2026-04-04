import * as api from '../api';
import * as time from '../time';
import { types } from '../api_types';
import clarificationStore from './clarificationsStore';

export interface CourseClarification {
  courseAlias: string;
  clarification: types.Clarification;
  courseClarificationRequest: CourseClarificationRequest;
}

export enum CourseClarificationType {
  WithProblem,
  AllProblems,
}

interface CourseClarificationWithProblem {
  type: CourseClarificationType.WithProblem;
  courseAlias: string;
  assignmentAlias: string;
  problemAlias: string;
}

interface CourseClarificationAllProblems {
  type: CourseClarificationType.AllProblems;
  courseAlias: string;
}

export type CourseClarificationRequest =
  | CourseClarificationWithProblem
  | CourseClarificationAllProblems;

export interface ContestClarification {
  contestAlias: string;
  clarification: types.Clarification;
  contestClarificationRequest: ContestClarificationRequest;
}

export enum ContestClarificationType {
  WithProblem,
  AllProblems,
}

interface ContestClarificationWithProblem {
  type: ContestClarificationType.WithProblem;
  contestAlias: string;
  problemAlias: string;
  offset: number;
  rowcount: number;
}

interface ContestClarificationAllProblems {
  type: ContestClarificationType.AllProblems;
  contestAlias: string;
  offset: number;
  rowcount: number;
}

export type ContestClarificationRequest =
  | ContestClarificationWithProblem
  | ContestClarificationAllProblems;

export function refreshContestClarifications(
  request: ContestClarificationRequest,
) {
  let problemAlias;
  if (request.type === ContestClarificationType.WithProblem) {
    problemAlias = request.problemAlias;
  }
  api.Contest.clarifications({
    contest_alias: request.contestAlias,
    problem_alias: problemAlias,
    rowcount: request.rowcount,
    offset: request.offset,
  })
    .then(time.remoteTimeAdapter)
    .then((data) => {
      trackClarifications(data.clarifications);
    });
}

export function refreshCourseClarifications(
  request: CourseClarificationRequest,
) {
  api.Course.clarifications({
    course_alias: request.courseAlias,
    rowcount: 100,
    offset: null,
  })
    .then(time.remoteTimeAdapter)
    .then((data) => {
      trackClarifications(data.clarifications);
    });
}

export function refreshProblemClarifications({
  problemAlias,
  offset,
  rowcount,
}: {
  problemAlias: string;
  offset: number;
  rowcount: number;
}) {
  api.Problem.clarifications({
    problem_alias: problemAlias,
    rowcount: rowcount,
    offset: offset,
  })
    .then(time.remoteTimeAdapter)
    .then((data) => {
      trackClarifications(data.clarifications);
    });
}

export function trackClarifications(clarifications: types.Clarification[]) {
  clarificationStore.commit('clear');
  for (const clarification of clarifications) {
    clarificationStore.commit('addClarification', clarification);
  }
}
