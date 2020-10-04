import submissions_List from '../components/submissions/List.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.SubmissionsListPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-submissions-list': submissions_List,
    },
    render: function (createElement) {
      return createElement('omegaup-submissions-list', {
        props: {
          page: payload.page,
          length: payload.length,
          pagerItems: payload.pagerItems,
          includeUser: payload.includeUser,
          submissions: payload.submissions,
          totalRows: payload.totalRows,
        },
      });
    },
  });
});
