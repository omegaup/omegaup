import * as api from '../api';
import * as time from '../time';
import { types } from '../api_types';
import clarificationStore from './clarificationsStore';

export interface ClarificationEvent {
  contestAlias: string;
  clarification: types.Clarification;
}

export function refreshContestClarifications({
  contestAlias,
  problemAlias,
}: {
  contestAlias: string;
  problemAlias?: string;
}) {
  api.Contest.clarifications({
    contest_alias: contestAlias,
    problem_alias: problemAlias,
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
}: {
  problemAlias: string;
}) {
  api.Problem.clarifications({
    problem_alias: problemAlias,
    rowcount: 100,
    offset: null,
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
