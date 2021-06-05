import * as api from '../api';
import { types } from '../api_types';
import { Arena, ArenaOptions } from './arena';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';
import * as time from '../time';

OmegaUp.on('ready', () => {
  const headerPayload = types.payloadParsers.CommonPayload('header-payload');
  const params = /\/arena\/([^/]+)\/scoreboard\/([^/]+)\/?/.exec(
    window.location.pathname,
  );
  const options: ArenaOptions = {
    // There is no UI to show clarifications with scoreboard-only views.
    disableClarifications: true,
    contestAlias: params?.[1] ?? null,
    scoreboardToken: params?.[2] ?? null,
    payload: headerPayload,

    assignmentAlias: null,
    courseAlias: null,
    courseName: null,
    disableSockets: false,
    isInterview: false,
    isLockdownMode: false,
    originalContestAlias: null,
    preferredLanguage: null,
    problemsetAdmin: false,
    problemsetId: null,
    shouldShowFirstAssociatedIdentityRunWarning: false,
    partialScore: true,
  };
  const arenaInstance = new Arena(options);
  const getRankingByTokenRefresh = 5 * 60 * 1000; // 5 minutes
  api.Contest.details({
    contest_alias: arenaInstance.options.contestAlias,
    token: arenaInstance.options.scoreboardToken,
  })
    .then(time.remoteTimeAdapter)
    .then((contest) => {
      arenaInstance.initProblemsetId(contest);
      arenaInstance.initProblems(contest);
      arenaInstance.initClock(contest.start_time, contest.finish_time, null);
      $('#title .contest-title').text(ui.contestTitle(contest));
      api.Problemset.scoreboard({
        problemset_id: arenaInstance.options.problemsetId,
        token: arenaInstance.options.scoreboardToken,
      })
        .then(arenaInstance.rankingChange.bind(arenaInstance))
        .catch(ui.ignoreError);
      if (new Date() < contest.finish_time && !arenaInstance.socket) {
        setInterval(() => {
          api.Problemset.scoreboard({
            problemset_id: arenaInstance.options.problemsetId,
            token: arenaInstance.options.scoreboardToken,
          })
            .then(arenaInstance.rankingChange.bind(arenaInstance))
            .catch(ui.ignoreError);
        }, getRankingByTokenRefresh);
      }

      $('#ranking').show();
      $('#root').fadeIn('slow');
      $('#loading').fadeOut('slow');
    })
    .catch(ui.apiError);
});
