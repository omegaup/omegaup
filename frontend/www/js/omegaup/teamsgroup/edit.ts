import teamsgroup_Edit, {
  AvailableTabs,
} from '../components/teamsgroup/Edit.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.TeamGroupEditPayload();
  const teamsGroupEdit = new Vue({
    el: '#main-container',
    components: {
      'omegaup-teams-group-edit': teamsgroup_Edit,
    },
    data: () => ({
      searchResultUsers: [] as types.ListItem[],
      teamsMembers: payload.teamsMembers,
    }),
    methods: {
      refreshTeamsMembersList: (): void => {
        api.TeamsGroup.teamsMembers({
          team_group_alias: payload.teamGroup.alias,
        })
          .then((data) => {
            teamsGroupEdit.teamsMembers = data.teamsUsers;
          })
          .catch(ui.apiError);
      },
    },
    render: function (createElement) {
      return createElement('omegaup-teams-group-edit', {
        props: {
          alias: payload.teamGroup.alias,
          name: payload.teamGroup.name,
          description: payload.teamGroup.description,
          countries: payload.countries,
          isOrganizer: payload.isOrganizer,
          tab: window.location.hash
            ? window.location.hash.substr(1)
            : AvailableTabs.Teams,
          teamsIdentities: payload.identities,
          userErrorRow: null,
          searchResultUsers: this.searchResultUsers,
          teamsMembers: this.teamsMembers,
        },
        on: {
          'update-teams-group': (request: {
            name: string;
            description: string;
          }) => {
            api.TeamsGroup.update({
              alias: payload.teamGroup.alias,
              name: request.name,
              description: request.description,
            })
              .then(() => {
                ui.success(T.teamsGroupEditGroupUpdated);
              })
              .catch(ui.apiError);
          },
          'update-search-result-users': (query: string) => {
            api.User.list({ query })
              .then((data) => {
                // Users previously invited to any team in the current teams
                // group can not be added to another, so they should not be
                // shown in the dropdown
                const addedUsers = new Set(
                  this.teamsMembers.map((user) => user.username),
                );

                this.searchResultUsers = data
                  .filter((user) => !addedUsers.has(user.label))
                  .map((user) => ({
                    key: user.label,
                    value: `${ui.escape(user.label)} (<strong>${ui.escape(
                      user.value,
                    )}</strong>)`,
                  }));
              })
              .catch(ui.apiError);
          },
          'add-members': ({
            teamUsername,
            usersToAdd,
          }: {
            teamUsername: string;
            usersToAdd: string[];
          }) => {
            api.TeamsGroup.addMembers({
              team_group_alias: teamUsername,
              usernames: usersToAdd,
            })
              .then(() => {
                ui.success(T.groupEditMemberAdded);
                this.refreshTeamsMembersList();
              })
              .catch(ui.apiError);
          },
          'remove-member': ({
            teamUsername,
            username,
          }: {
            teamUsername: string;
            username: string;
          }) => {
            api.TeamsGroup.removeMember({
              team_group_alias: teamUsername,
              username,
            })
              .then(() => {
                ui.success(T.groupEditMemberRemoved);
                this.refreshTeamsMembersList();
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
