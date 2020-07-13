import submissions_List from '../components/submissions/List.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.SubmissionsListPayload();

  const submissionsList = new Vue({
    el: '#main-container',
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
    components: {
      'omegaup-submissions-list': submissions_List,
    },
  });
});
