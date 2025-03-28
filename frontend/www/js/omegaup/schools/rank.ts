import schools_Rank from '../components/schools/Rank.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.SchoolRankPayload();
  let availableFilters: {
    country?: string;
    state?: string;
  } = {};
  let filter: null | string = null;
  if (payload.isLogged) {
    availableFilters = payload.availableFilters;
    filter = payload.filter;
  }

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-schools-rank': schools_Rank,
    },
    render: function (createElement) {
      return createElement('omegaup-schools-rank', {
        props: {
          page: payload.page,
          length: payload.length,
          showHeader: payload.showHeader,
          rank: payload.rank,
          totalRows: payload.totalRows,
          pagerItems: payload.pagerItems,
          isLogged: payload.isLogged,
          availableFilters,
          filter,
          profileComplete: payload.profileComplete, // Pass userProfile to the component
        },
      });
    },
  });
});
