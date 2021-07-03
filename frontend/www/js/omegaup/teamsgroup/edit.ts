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
    },
    render: function (createElement) {
      return createElement('omegaup-teams-group-edit', {
        props: {
          alias: payload.teamGroup.alias,
          name: payload.teamGroup.name,
          description: payload.teamGroup.description,
          countries: payload.countries,
          isOrganizer: payload.isOrganizer,
          tab: this.tab,
          teamsIdentities: this.teamsIdentities,
          teamsMembers: this.teamsMembers,
          userErrorRow: this.userErrorRow,
          searchResultUsers: this.searchResultUsers,
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
                  ...{
                    usernames: identitiesTeams[identity.username].join(';'),
                  },
                })),
              ),
              team_group_alias: payload.teamGroup.alias,
            })
              .then(() => {
                this.refreshTeamsList();
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
                requiredFields: new Set([
                  'username',
                  'name',
                  'country_id',
                  'state_id',
                  'gender',
                  'school_name',
                ]),
              });
              for (const {
                username,
                name,
                country_id,
                state_id,
                gender,
                school_name,
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
                  gender: typeof gender === 'undefined' ? 'decline' : gender,
                });
                identitiesTeams[
                  `teams:${payload.teamGroup.alias}:${username}`
                ] = [];
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
