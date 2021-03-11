import Vue from 'vue';
import * as api from '../api';
import * as time from '../time';
import { types } from '../api_types';

export interface ClarificationEvent {
  target: Vue & {
    clearForm?: () => void;
    currentClarifications?: types.Clarification[];
  };
  contestAlias?: string;
  clarification?: types.Clarification;
}

export function refreshClarifications({
  clarification,
  contestAlias,
  target,
}: ClarificationEvent) {
  const params = {
    contest_alias: contestAlias,
    problem_alias: clarification?.problem_alias,
    rowcount: 100,
    offset: null,
  };
  (contestAlias
    ? api.Contest.clarifications(params)
    : api.Problem.clarifications(params)
  )
    .then(time.remoteTimeAdapter)
    .then((data) => {
      target.currentClarifications = data.clarifications;
    });
}
