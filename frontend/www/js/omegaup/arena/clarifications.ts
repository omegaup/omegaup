import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';
import * as time from '../time';
import { types } from '../api_types';

export interface Clarification {
  target: Vue & {
    clearForm?: () => void;
    currentClarifications?: types.Clarification[];
  };
  contestAlias?: string;
  clarification?: types.Clarification;
}

export function newClarification({
  clarification,
  contestAlias,
  target,
}: Clarification): void {
  if (!clarification) {
    return;
  }
  api.Clarification.create({
    contest_alias: contestAlias,
    problem_alias: clarification.problem_alias,
    username: clarification.author,
    message: clarification.message,
  })
    .then(() => {
      if (target.clearForm) {
        target.clearForm();
      }
      if (contestAlias) {
        refreshClarifications({ clarification, contestAlias, target });
      }
    })
    .catch(ui.apiError);
}

export function clarificationResponse({
  clarification,
  contestAlias,
  target,
}: Clarification): void {
  api.Clarification.update(clarification)
    .then(() => {
      refreshClarifications({ clarification, contestAlias, target });
    })
    .catch(ui.apiError);
}

export function refreshClarifications({
  clarification,
  contestAlias,
  target,
}: Clarification) {
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
