import schools_Rank from '../components/schools/Rankv2.vue';
import { OmegaUp, omegaup } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api_transitional';
import * as UI from '../ui';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.SchoolRankPayload('payload');

  const schoolsRank = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-schools-rank', {
        props: {
          page: payload.page,
          length: payload.length,
          showHeader: payload.showHeader,
          rank: this.rank,
          totalRows: this.totalRows,
        },
      });
    },
    data: {
      rank: <omegaup.SchoolsRank[]>[],
      totalRows: 0,
    },
    components: {
      'omegaup-schools-rank': schools_Rank,
    },
  });

  api.School.rank({
    offset: payload.page,
    rowcount: payload.length,
  })
    .then(data => {
      schoolsRank.totalRows = data.totalRows;
      schoolsRank.rank = data.rank;
    })
    .catch(UI.apiError);
});