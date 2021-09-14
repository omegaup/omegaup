import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import { trackRun } from './submissions';
import problemsStore from './problemStore';

export async function getRunDetails({
  guid,
  response,
}: {
  guid: string;
  response: { runDetails: null | types.RunDetails };
}): Promise<void> {
  return api.Run.details({ run_alias: guid })
    .then((runDetails) => {
      response.runDetails = runDetails;
    })
    .catch((error) => {
      ui.apiError(error);
    });
}

export async function getProblemDetails({
  problemAlias,
  contestAlias,
  problems,
  response,
}: {
  problemAlias: string;
  contestAlias?: string;
  problems: types.NavbarProblemsetProblem[];
  response: { problemInfo: null | types.ProblemInfo };
}): Promise<void> {
  return api.Problem.details({
    problem_alias: problemAlias,
    prevent_problemset_open: false,
    contest_alias: contestAlias,
  })
    .then((problemInfo) => {
      for (const run of problemInfo.runs ?? []) {
        trackRun({ run });
      }
      const currentProblem = problems?.find(
        ({ alias }: { alias: string }) => alias === problemInfo.alias,
      );
      problemInfo.title = currentProblem?.text ?? '';
      response.problemInfo = problemInfo;
      problemsStore.commit('addProblem', problemInfo);
    })
    .catch(() => {
      ui.dismissNotifications();
    });
}
