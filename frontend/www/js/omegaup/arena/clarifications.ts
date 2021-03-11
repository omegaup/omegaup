import Vue from 'vue';
import * as api from '../api';
import * as time from '../time';
import { types } from '../api_types';

export interface ClarificationEvent {
  target: Vue & {
    clearForm?: () => void;
    currentClarifications?: types.Clarification[];
    clarifications?: types.Clarification[];
  };
  contestAlias?: string;
  problemAlias?: string;
  clarification?: types.Clarification;
}

export function refreshClarifications({
  contestAlias,
  problemAlias,
  target,
}: ClarificationEvent) {
  const params = {
    contest_alias: contestAlias,
    problem_alias: problemAlias,
    rowcount: 100,
    offset: null,
  };
  (contestAlias
    ? api.Contest.clarifications(params)
    : api.Problem.clarifications(params)
  )
    .then(time.remoteTimeAdapter)
    .then((data) => {
      if (!contestAlias) {
        target.clarifications = data.clarifications;
      }
      target.currentClarifications = data.clarifications;
    });
}
