import teamsgroup_Edit, {
  AvailableTabs,
} from '../components/teamsgroup/Edit.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import * as CSV from '@/third_party/js/csv.js/csv.js';
import {
  downloadCsvFile,
  generateHumanReadablePassword,
  generatePassword,
  getCSVRecords,
  identityOptionalFields,
  identityRequiredFields,
} from '../groups';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.TeamGroupEditPayload();
  const teamsGroupEdit = new Vue({
    el: '#main-container',
    components: {
      'omegaup-teams-group-edit': teamsgroup_Edit,
    },
    data: () => ({
      tab: window.location.hash
        ? window.location.hash.substr(1)
        : AvailableTabs.Teams,
      teamsIdentities: payload.identities,
      teamsMembers: payload.teamsMembers,
      userErrorRow: null,
      searchResultUsers: [] as types.ListItem[],
    }),
    methods: {
      refreshTeamsList: (): void => {
        api.TeamsGroup.teams({ team_group_alias: payload.teamGroup.alias })
          .then((data) => {
            teamsGroupEdit.teamsIdentities = data.identities;
          })
          .catch(ui.apiError);
      },
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
          numberOfContestants: payload.teamGroup.numberOfContestants,
          countries: payload.countries,
          isOrganizer: payload.isOrganizer,
          tab: this.tab,
          teamsIdentities: this.teamsIdentities,
          teamsMembers: this.teamsMembers,
          userErrorRow: this.userErrorRow,
          searchResultUsers: this.searchResultUsers,
        },
        on: {
          'update-teams-group': ({
            name,
            description,
            numberOfContestants,
          }: {
            name: string;
            description: string;
            numberOfContestants: number;
          }) => {
            api.TeamsGroup.update({
              alias: payload.teamGroup.alias,
              name,
              description,
              numberOfContestants,
            })
              .then(() => {
                ui.success(T.teamsGroupEditGroupUpdated);
              })
              .catch(ui.apiError);
          },
          'edit-identity-team': ({
            originalUsername,
            identity,
          }: {
            originalUsername: string;
            identity: types.Identity;
          }) => {
            api.Identity.updateIdentityTeam({
              ...identity,
              group_alias: payload.teamGroup.alias,
              original_username: originalUsername,
              school_name: identity.school,
            })
              .then(() => {
                ui.success(T.teamsGroupEditTeamsUpdated);
                this.refreshTeamsList();
              })
              .catch(ui.apiError);
          },
          'change-password-identity-team': ({
            username,
            newPassword,
            newPasswordRepeat,
          }: {
            username: string;
            newPassword: string;
            newPasswordRepeat: string;
          }) => {
            if (newPassword !== newPasswordRepeat) {
              ui.error(T.userPasswordMustBeSame);
              return;
            }

            api.Identity.changePassword({
              group_alias: payload.teamGroup.alias,
              password: newPassword,
              username: username,
            })
              .then(() => {
                this.refreshTeamsList();
                ui.success(T.teamsGroupEditTeamsPasswordUpdated);
              })
              .catch(ui.apiError);
          },
          remove: (username: string) => {
            api.Group.removeUser({
              group_alias: payload.teamGroup.alias,
              usernameOrEmail: username,
            })
              .then(() => {
                this.refreshTeamsList();
                ui.success(T.teamsGroupEditTeamsRemoved);
              })
              .catch(ui.apiError);
          },
          'update-search-result-users': (query: string) => {
            api.User.list({ query })
              .then(({ results }) => {
                // Users previously invited to any team in the current teams
                // group can not be added to another, so they should not be
                // shown in the dropdown
                const addedUsers = new Set(
                  this.teamsMembers.map((user) => user.username),
                );

                this.searchResultUsers = results
                  .filter((user) => !addedUsers.has(user.key))
                  .map(({ key, value }: types.ListItem) => ({
                    key,
                    value: `${ui.escape(key)} (<strong>${ui.escape(
                      value,
                    )}</strong>)`,
                  }));
              })
              .catch(ui.apiError);
          },
          'bulk-identities': ({
            identities,
            identitiesTeams,
          }: {
            identities: types.Identity[];
            identitiesTeams: { [team: string]: string[] };
          }) => {
            api.Identity.bulkCreateForTeams({
              team_identities: JSON.stringify(
                identities.map((identity) => ({
                  ...identity,
                  usernames: identitiesTeams[identity.username].join(';'),
                })),
              ),
              team_group_alias: payload.teamGroup.alias,
            })
              .then(() => {
                this.refreshTeamsList();
                this.refreshTeamsMembersList();
                window.location.hash = `#${AvailableTabs.Teams}`;
                this.tab = AvailableTabs.Teams;
                ui.success(T.groupsIdentitiesSuccessfullyCreated);
              })
              .catch((data) => {
                ui.error(data.error);
                this.userErrorRow = data.parameter;
              });
          },
          'invalid-file': () => {
            ui.error(T.groupsInvalidCsv);
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
          'download-identities': (identities: types.Identity[]) => {
            downloadCsvFile({
              fileName: `identities_${payload.teamGroup.alias}.csv`,
              columns: [
                'username',
                'name',
                'password',
                'country_id',
                'state_id',
                'gender',
                'school_name',
                'usernames',
              ],
              records: identities,
            });
          },
          'read-csv': ({
            identitiesTeams,
            identities,
            file,
            humanReadable,
          }: {
            identitiesTeams: { [team: string]: string[] };
            identities: types.Identity[];
            file: File;
            humanReadable: boolean;
          }) => {
            CSV.fetch({ file }).done((dataset: CSV.Dataset) => {
              if (!dataset.fields) {
                ui.error(T.groupsInvalidCsv);
                return;
              }
              const records = getCSVRecords<types.Identity>({
                fields: dataset.fields,
                records: dataset.records,
                requiredFields: identityRequiredFields,
                optionalFields: identityOptionalFields,
              });
              for (const {
                username,
                name,
                country_id,
                state_id,
                gender,
                school_name,
                usernames,
              } of records) {
                identities.push({
                  username: `teams:${payload.teamGroup.alias}:${username}`,
                  name,
                  password: humanReadable
                    ? generateHumanReadablePassword()
                    : generatePassword(),
                  country_id,
                  state_id,
                  school_name,
                  gender: gender ?? 'decline',
                  usernames,
                });
                identitiesTeams[
                  `teams:${payload.teamGroup.alias}:${username}`
                ] = usernames?.split(';') ?? [];
              }
              ui.dismissNotifications();
              this.userErrorRow = null;
            });
          },
        },
      });
    },
  });
});
