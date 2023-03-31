import arena_Scoreboard from '../components/arena/Scoreboard.vue';
import { OmegaUp } from '../omegaup';
import { EventsSocket } from '../arena/events_socket';
import socketStore from '../arena/socketStore';
import * as api from '../api';
import * as ui from '../ui';
import * as time from '../time';
import Vue from 'vue';
import { types } from '../api_types';
import { onRankingChanged } from '../arena/ranking';
import rankingStore from '../arena/rankingStore';
import { ScoreMode } from '../arena/navigation';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseScoreboardPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const getRankingByTokenRefresh = 5 * 60 * 1000; // 5 minutes

  let ranking: types.ScoreboardRankingEntry[] = [];
  let lastTimeUpdated: null | Date = null;
  if (payload.scoreboard) {
    const rankingInfo = onRankingChanged({
      scoreboard: payload.scoreboard,
      currentUsername: commonPayload.currentUsername,
      navbarProblems: payload.problems,
      scoreMode: ScoreMode.Partial,
    });
    ranking = rankingInfo.ranking;
    lastTimeUpdated = rankingInfo.lastTimeUpdated;
    rankingStore.commit('updateRanking', ranking);
    rankingStore.commit('updateLastTimeUpdated', lastTimeUpdated);
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
          title: payload.assignment.name,
          finishTime: payload.assignment.finishTime,
          socketStatus: socketStore.state.socketStatus,
          ranking: rankingStore.state.ranking,
          lastUpdated: rankingStore.state.lastTimeUpdated,
          showInvitedUsersFilter: false,
        },
      });
    },
  });

  const socket = new EventsSocket({
    disableSockets: false,
    problemsetAlias: payload.assignment.alias,
    isVirtual: false,
    startTime: payload.assignment.startTime,
    finishTime: payload.assignment.finishTime,
    locationProtocol: window.location.protocol,
    locationHost: window.location.host,
    problemsetId: payload.assignment.problemsetId,
    scoreboardToken: null,
    clarificationsOffset: 1,
    clarificationsRowcount: 30,
    navbarProblems: payload.problems,
    currentUsername: commonPayload.currentUsername,
    intervalInMilliseconds: 5 * 60 * 1000,
    scoreMode: ScoreMode.Partial,
  });

  socket.connect();

  setInterval(() => {
    api.Problemset.scoreboard({
      problemset_id: payload.assignment.problemsetId,
      token: payload.scoreboardToken,
    })
      .then(time.remoteTimeAdapter)
      .then((scoreboard) => {
        const rankingInfo = onRankingChanged({
          scoreboard,
          currentUsername: commonPayload.currentUsername,
          navbarProblems: payload.problems,
          scoreMode: ScoreMode.Partial,
        });
        ranking = rankingInfo.ranking;
        lastTimeUpdated = rankingInfo.lastTimeUpdated;
        rankingStore.commit('updateRanking', ranking);
        rankingStore.commit('updateLastTimeUpdated', lastTimeUpdated);
      })
      .catch(ui.ignoreError);
  }, getRankingByTokenRefresh);
});
