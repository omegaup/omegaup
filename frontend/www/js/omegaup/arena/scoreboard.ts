import arena_Scoreboard from '../components/arena/Scoreboard.vue';
import { OmegaUp } from '../omegaup';
import { EventsSocket } from './events_socket';
import socketStore from './socketStore';
import * as api from '../api';
import * as ui from '../ui';
import * as time from '../time';
import Vue from 'vue';
import { types } from '../api_types';
import { createChart, onRankingChanged, onRankingEvents } from './ranking';
import rankingStore from './rankingStore';
import { getScoreModeEnum } from './navigation';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestScoreboardPayload();
  const commonPayload = types.payloadParsers.CommonPayload();

  const getRankingByTokenRefresh = 5 * 60 * 1000; // 5 minutes

  let ranking: types.ScoreboardRankingEntry[] = [];
  let rankingChartOptions: Highcharts.Options | null = null;
  let lastTimeUpdated: null | Date = null;
  if (payload.scoreboard && payload.scoreboardEvents) {
    const rankingInfo = onRankingChanged({
      scoreboard: payload.scoreboard,
      currentUsername: commonPayload.currentUsername,
      navbarProblems: payload.problems,
      scoreMode: getScoreModeEnum(payload.contest.score_mode),
    });
    ranking = rankingInfo.ranking;
    lastTimeUpdated = rankingInfo.lastTimeUpdated;
    rankingStore.commit('updateRanking', ranking);
    rankingStore.commit('updateLastTimeUpdated', lastTimeUpdated);

    const startTimestamp = payload.contest.start_time.getTime();
    const finishTimestamp = Math.min(
      payload.contest.finish_time?.getTime() || Infinity,
      Date.now(),
    );
    const { series, navigatorData } = onRankingEvents({
      events: payload.scoreboardEvents,
      currentRanking: rankingInfo.currentRanking,
      startTimestamp,
      finishTimestamp,
    });
    if (series.length) {
      rankingChartOptions = createChart({
        series,
        navigatorData,
        startTimestamp,
        finishTimestamp,
        maxPoints: rankingInfo.maxPoints,
      });
      rankingStore.commit('updateRankingChartOptions', rankingChartOptions);
    }
  }

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-scoreboard': arena_Scoreboard,
    },
    data: () => ({
      problems: payload.problems,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-scoreboard', {
        props: {
          problems: this.problems,
          title: payload.contest.title,
          finishTime: payload.contest.finish_time,
          socketStatus: socketStore.state.socketStatus,
          ranking: rankingStore.state.ranking,
          rankingChartOptions: rankingStore.state.rankingChartOptions,
          lastUpdated: rankingStore.state.lastTimeUpdated,
          showInvitedUsersFilter: false,
        },
      });
    },
  });

  const socket = new EventsSocket({
    disableSockets: false,
    problemsetAlias: payload.contest.alias,
    isVirtual: false,
    startTime: payload.contest.start_time,
    finishTime: payload.contest.finish_time,
    locationProtocol: window.location.protocol,
    locationHost: window.location.host,
    problemsetId: payload.contest.problemset_id,
    scoreboardToken: null,
    clarificationsOffset: 1,
    clarificationsRowcount: 30,
    navbarProblems: payload.problems,
    currentUsername: commonPayload.currentUsername,
    intervalInMilliseconds: 5 * 60 * 1000,
    scoreMode: getScoreModeEnum(payload.contest.score_mode),
  });

  socket.connect();

  setInterval(() => {
    api.Problemset.scoreboard({
      problemset_id: payload.contest.problemset_id,
      token: payload.scoreboardToken,
    })
      .then(time.remoteTimeAdapter)
      .then((scoreboard) => {
        const rankingInfo = onRankingChanged({
          scoreboard,
          currentUsername: commonPayload.currentUsername,
          navbarProblems: payload.problems,
          scoreMode: getScoreModeEnum(payload.contest.score_mode),
        });
        ranking = rankingInfo.ranking;
        lastTimeUpdated = rankingInfo.lastTimeUpdated;
        rankingStore.commit('updateRanking', ranking);
        rankingStore.commit('updateLastTimeUpdated', lastTimeUpdated);
      })
      .catch(ui.ignoreError);
  }, getRankingByTokenRefresh);
});
