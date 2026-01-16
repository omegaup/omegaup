import { types } from '../api_types';
import { PopupDisplayed } from '../components/problem/Details.vue';
import clarificationsStore from './clarificationsStore';
import * as api from '../api';
import * as time from '../time';
import { trackRun } from './submissions';
import problemsStore from './problemStore';

export interface LocationOptions {
  problem: types.NavbarProblemsetProblem | null;
  popupDisplayed: PopupDisplayed;
  guid: null | string;
  problemAlias: null | string;
  showNewClarificationPopup: boolean;
}

export function getOptionsFromLocation(location: string): LocationOptions {
  const response: LocationOptions = {
    problem: null,
    popupDisplayed: PopupDisplayed.None,
    guid: null,
    problemAlias: null,
    showNewClarificationPopup: false,
  };

  // Location string is of the forms:
  // - `#problems/${alias}`
  // - `#problems/${alias}/new-run`
  // - `#problems/${alias}/show-run:${guid}`
  // - `#clarifications/${alias}/new`
  // - `#runs/${alias}/show-run:${guid}` the alias can be "all" when admin runs
  //   tab is shown.
  // and all the matching forms in the following regex
  const match = /#(?<tab>\w+)\/(?<alias>[^/]+)(?:\/(?<popup>[^/]+))?/g.exec(
    location,
  );
  switch (match?.groups?.tab) {
    case 'problems':
      response.problem = {
        alias: match?.groups?.alias,
        text: '',
        acceptsSubmissions: true,
        bestScore: 0,
        maxScore: 0,
        hasRuns: false,
      };
      response.problemAlias = match?.groups?.alias;
      if (match.groups.popup === 'new-run') {
        response.popupDisplayed = PopupDisplayed.RunSubmit;
      } else if (match.groups.popup?.startsWith('show-run')) {
        response.guid = match.groups.popup.split(':')[1];
        response.popupDisplayed = PopupDisplayed.RunDetails;
      }
      break;
    case 'clarifications':
      if (match.groups.popup === 'new') {
        response.showNewClarificationPopup = true;
      } else if (match.groups.alias?.startsWith('clarification-')) {
        clarificationsStore.commit(
          'selectClarificationId',
          parseInt(match.groups.alias.split('-')[1]),
        );
      }
      break;
    case 'runs':
      if (match.groups.popup?.startsWith('show-run')) {
        response.guid = match.groups.popup.split(':')[1];
        response.popupDisplayed = PopupDisplayed.RunDetails;
      }
      break;
    default:
      response.popupDisplayed = PopupDisplayed.None;
      response.showNewClarificationPopup = false;
  }

  return response;
}

export async function getProblemAndRunDetails({
  location,
  contestAlias,
  problems,
  problemsetId,
}: {
  location: string;
  contestAlias?: string;
  problems?: types.NavbarProblemsetProblem[];
  problemsetId?: number;
}): Promise<{
  runDetails: null | types.RunDetails;
  problemDetails: null | types.ProblemDetails;
}> {
  const { guid, problemAlias } = getOptionsFromLocation(location);
  let problemPromise: Promise<null | types.ProblemDetails> =
    Promise.resolve(null);
  let runPromise: Promise<null | types.RunDetails> = Promise.resolve(null);

  if (problemAlias) {
    problemPromise = api.Problem.details({
      problem_alias: problemAlias,
      prevent_problemset_open: false,
      contest_alias: contestAlias || undefined,
      problemset_id: problemsetId || undefined,
    });
  }
  if (guid) {
    runPromise = api.Run.details({ run_alias: guid });
  }

  const [problemDetails, runDetails] = await Promise.all([
    problemPromise.then(time.remoteTimeAdapter),
    runPromise.then(time.remoteTimeAdapter),
  ]);

  if (problemDetails != null) {
    for (const run of problemDetails.runs ?? []) {
      trackRun({ run });
    }
    const currentProblem = problems?.find(
      ({ alias }: { alias: string }) => alias === problemDetails?.alias,
    );
    problemDetails.title = currentProblem?.text ?? '';
    problemsStore.commit('addProblem', problemDetails);
  }

  return { problemDetails, runDetails };
}
