import { createStore } from 'vuex';
import { types } from '../api_types';
import { omegaup } from '../omegaup';

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
      state.miniRankingUsers = miniRankingUsers;
    },
    updateRanking(
      state: RankingState,
      ranking: types.ScoreboardRankingEntry[],
    ) {
      state.ranking = ranking;
    },
    updateRankingChartOptions(
      state: RankingState,
      rankingChartOptions: Highcharts.Options,
    ) {
      state.rankingChartOptions = rankingChartOptions;
    },
    updateLastTimeUpdated(state: RankingState, lastTimeUpdated: Date) {
      state.lastTimeUpdated = lastTimeUpdated;
    },
  },
};

export default createStore<RankingState>(rankingStoreConfig);
