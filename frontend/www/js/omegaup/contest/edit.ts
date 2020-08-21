import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import contest_Edit from '../components/contest/Editv2.vue';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestEditPayload();
  console.log(payload);

  const contestEdit = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-contest-edit', {
        props: {
          admins: payload.admins,
          details: payload.details,
          groups: payload.groups,
          groupAdmins: payload.group_admins,
          problems: payload.problems,
          requests: payload.requests,
          users: payload.users,
        },
      });
    },
    components: {
      'omegaup-contest-edit': contest_Edit,
    },
  });
});
