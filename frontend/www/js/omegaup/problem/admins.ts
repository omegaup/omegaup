import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';
import problem_Admins from '../components/common/Admins.vue';
import problem_GroupAdmins from '../components/common/GroupAdmins.vue';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemAdminsPayload(
    'problem-admins-payload',
  );
  const problemAdmins = new Vue({
    el: '#problem-admins .admins',
    render: function(createElement) {
      return createElement('omegaup-problem-admins', {
        props: { initialAdmins: this.initialAdmins },
        on: {
          'add-admin': (username: string): void => {
            api.Problem.addAdmin({
              problem_alias: payload.alias,
              usernameOrEmail: username,
            })
              .then(() => {
                ui.success(T.adminAdded);
                this.refreshProblemAdmins();
              })
              .catch(ui.apiError);
          },
          'remove-admin': (username: string): void => {
            api.Problem.removeAdmin({
              problem_alias: payload.alias,
              usernameOrEmail: username,
            })
              .then(() => {
                ui.success(T.adminRemoved);
                this.refreshProblemAdmins();
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    methods: {
      refreshProblemAdmins: (): void => {
        api.Problem.admins({ problem_alias: payload.alias })
          .then(data => {
            problemAdmins.initialAdmins = data.admins;
          })
          .catch(ui.apiError);
      },
    },
    data: { initialAdmins: payload.admins },
    components: {
      'omegaup-problem-admins': problem_Admins,
    },
  });

  const problemGroupAdmins = new Vue({
    el: '#problem-admins .groups',
    render: function(createElement) {
      return createElement('omegaup-problem-group-admins', {
        props: { initialGroups: this.initialGroups },
        on: {
          'add-group-admin': (groupAlias: string): void => {
            api.Problem.addGroupAdmin({
              problem_alias: payload.alias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.groupAdminAdded);
                this.refreshProblemAdmins();
              })
              .catch(ui.apiError);
          },
          'remove-group-admin': (groupAlias: string): void => {
            api.Problem.removeGroupAdmin({
              problem_alias: payload.alias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.groupAdminRemoved);
                this.refreshProblemAdmins();
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    methods: {
      refreshProblemAdmins: (): void => {
        api.Problem.admins({ problem_alias: payload.alias })
          .then(data => {
            problemGroupAdmins.initialGroups = data.group_admins;
          })
          .catch(ui.apiError);
      },
    },
    data: { initialGroups: payload.group_admins },
    components: {
      'omegaup-problem-group-admins': problem_GroupAdmins,
    },
  });
});
