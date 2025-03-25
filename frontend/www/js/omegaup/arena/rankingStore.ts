import Vue from 'vue';
import Vuex from 'vuex';
import { types } from '../api_types';
import { omegaup } from '../omegaup';

Vue.use(Vuex);

export interface RankingState {
  miniRankingUsers: omegaup.UserRank[];

  ranking: types.ScoreboardRankingEntry[];

  rankingChartOptions: Highcharts.Options;

  lastTimeUpdated: null | Date;
}

export const rankingStoreConfig = {
  state: {
    miniRankingUsers: [],
    ranking: [],
    rankingChartOptions: {},
    lastTimeUpdated: null,
  },
  mutations: {
    updateMiniRankingUsers(
      state: RankingState,
      miniRankingUsers: omegaup.UserRank[],
    ) {
      Vue.set(state, 'miniRankingUsers', miniRankingUsers);
    },
    updateRanking(
      state: RankingState,
      ranking: types.ScoreboardRankingEntry[],
    ) {
      Vue.set(state, 'ranking', ranking);
    },
    updateRankingChartOptions(
      state: RankingState,
      rankingChartOptions: Highcharts.Options,
    ) {
      Vue.set(state, 'rankingChartOptions', rankingChartOptions);
    },
    updateLastTimeUpdated(state: RankingState, lastTimeUpdated: Date) {
      Vue.set(state, 'lastTimeUpdated', lastTimeUpdated);
    },
  },
};

export default new Vuex.Store<RankingState>(rankingStoreConfig);
