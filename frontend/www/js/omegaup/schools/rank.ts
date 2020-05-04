import schools_Rank from '../components/schools/Rank.vue';
import { OmegaUp, omegaup } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as UI from '../ui';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.SchoolRankPayload();

  const schoolsRank = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-schools-rank', {
        props: {
          page: payload.page,
          length: payload.length,
          showHeader: payload.showHeader,
          rank: payload.rank,
          totalRows: payload.totalRows,
        },
      });
    },
    components: {
      'omegaup-schools-rank': schools_Rank,
    },
  });
});
