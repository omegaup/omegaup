import group_Edit, { AvailableTabs } from '../components/group/Edit.vue';
import group_Members from '../components/group/Members.vue';
import group_Scoreboards from '../components/group/Scoreboards.vue';
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
  const payload = types.payloadParsers.GroupEditPayload();
  const searchResultSchools: types.SchoolListItem[] = [];
  const groupEdit = new Vue({
    el: '#main-container',
    components: {
      'omegaup-group-edit': group_Edit,
    },
    data: () => ({
      tab: window.location.hash
        ? window.location.hash.substring(1)
        : AvailableTabs.Members,
      identities: payload.identities.filter(
        (identity) => !identity.username.includes(':'),
      ),
      identitiesCsv: payload.identities.filter((identity) =>
        identity.username.includes(':'),
      ),
      scoreboards: payload.scoreboards,
      userErrorRow: null,
      searchResultUsers: [] as types.ListItem[],
      searchResultSchools: searchResultSchools,
    }),
    methods: {
      refreshGroupScoreboards: (): void => {
        api.Group.details({ group_alias: payload.groupAlias })
          .then((group) => {
            groupEdit.scoreboards = group.scoreboards;
          })
          .catch(ui.apiError);
      },
      refreshMemberList: (): void => {
        api.Group.members({ group_alias: payload.groupAlias })
          .then((data) => {
            groupEdit.identities = data.identities.filter(
              (identity) => !identity.username.includes(':'),
            );
            groupEdit.identitiesCsv = data.identities.filter((identity) =>
              identity.username.includes(':'),
            );
          })
          .catch(ui.apiError);
      },
    },
    render: function (createElement) {
      return createElement('omegaup-group-edit', {
        props: {
          groupAlias: payload.groupAlias,
          groupName: payload.groupName,
          groupDescription: payload.groupDescription,
          countries: payload.countries,
          isOrganizer: payload.isOrganizer,
          tab: this.tab,
          identities: this.identities,
          identitiesCsv: this.identitiesCsv,
          scoreboards: this.scoreboards,
          userErrorRow: this.userErrorRow,
          searchResultUsers: this.searchResultUsers,
          searchResultSchools: this.searchResultSchools,
          hasVisitedSection: payload.hasVisitedSection,
        },
        on: {
          'update-group': (name: string, description: string) => {
            api.Group.update({
              alias: payload.groupAlias,
              name: name,
              description: description,
            })
              .then(() => {
                ui.success(T.groupEditGroupUpdated);
              })
              .catch(ui.apiError);
          },
          'create-scoreboard': (
            source: group_Scoreboards,
            scoreboardName: string,
            scoreboardAlias: string,
            scoreboardDescription: string,
          ) => {
            api.Group.createScoreboard({
              group_alias: payload.groupAlias,
              alias: scoreboardAlias,
              name: scoreboardName,
              description: scoreboardDescription,
            })
              .then(() => {
                ui.success(T.groupEditScoreboardsAdded);
                this.refreshGroupScoreboards();
                source.reset();
              })
              .catch(ui.apiError);
          },
          'add-member': (source: group_Members, username: string) => {
            api.Group.addUser({
              group_alias: payload.groupAlias,
              usernameOrEmail: username,
            })
              .then(() => {
                this.refreshMemberList();
                ui.success(T.groupEditMemberAdded);
                source.reset();
              })
              .catch(ui.apiError);
          },
          'edit-identity': (
            source: group_Members,
            identity: types.Identity,
          ) => {
            source.showEditForm = true;
            source.showChangePasswordForm = false;
            source.identity = identity;
            source.username = identity.username;
            if (identity.school && identity.school_id) {
              searchResultSchools.splice(0, searchResultSchools.length);
              searchResultSchools.push({
                key: identity.school_id,
                value: identity.school,
              });
            }
          },
          'edit-identity-member': (request: {
            originalUsername: string;
            identity: types.Identity;
            showEditForm: boolean;
          }) => {
            api.Identity.update({
              ...request.identity,
              original_username: request.originalUsername,
              group_alias: payload.groupAlias,
              school_name: request.identity.school,
            })
              .then(() => {
                ui.success(T.groupEditMemberUpdated);
                request.showEditForm = false;
                this.refreshMemberList();
              })
              .catch(ui.apiError);
          },
          'change-password-identity': (
            source: group_Members,
            username: string,
          ) => {
            source.showEditForm = false;
            source.showChangePasswordForm = true;
            source.username = username;
          },
          'change-password-identity-member': (
            source: group_Members,
            username: string,
            newPassword: string,
            newPasswordRepeat: string,
          ) => {
            if (newPassword !== newPasswordRepeat) {
              ui.error(T.userPasswordMustBeSame);
              return;
            }

            api.Identity.changePassword({
              group_alias: payload.groupAlias,
              password: newPassword,
              username: username,
            })
              .then(() => {
                this.refreshMemberList();
                ui.success(T.groupEditMemberPasswordUpdated);
                source.showChangePasswordForm = false;
                source.reset();
              })
              .catch(ui.apiError);
          },
          remove: (username: string) => {
            api.Group.removeUser({
              group_alias: payload.groupAlias,
              usernameOrEmail: username,
            })
              .then(() => {
                this.refreshMemberList();
                ui.success(T.groupEditMemberRemoved);
              })
              .catch(ui.apiError);
          },
          cancel: (source: group_Members) => {
            this.refreshMemberList();
            source.showEditForm = false;
            source.showChangePasswordForm = false;
            source.$el.scrollIntoView();
          },
          'bulk-identities': (identities: types.Identity[]) => {
            api.Identity.bulkCreate({
              identities: JSON.stringify(identities),
              group_alias: payload.groupAlias,
            })
              .then(() => {
                this.refreshMemberList();
                window.location.hash = `#${AvailableTabs.Members}`;
                this.tab = AvailableTabs.Members;
                ui.success(T.groupsIdentitiesSuccessfullyCreated);
              })
              .catch((data) => {
                ui.error(data.error);
                this.userErrorRow = data.parameter;
              });
          },
          'download-identities': (identities: types.Identity[]) => {
            downloadCsvFile({
              fileName: `identities_${payload.groupAlias}.csv`,
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
            identities,
            file,
            humanReadable,
          }: {
            identities: types.Identity[];
            file: File;
            humanReadable: boolean;
          }) => {
            CSV.fetch({ file })
              .done((dataset: CSV.Dataset) => {
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
                } of records) {
                  identities.push({
                    username: `${payload.groupAlias}:${username}`,
                    name,
                    password: humanReadable
                      ? generateHumanReadablePassword()
                      : generatePassword(),
                    country_id,
                    state_id,
                    gender,
                    school_name,
                  });
                }
                ui.dismissNotifications();
                this.userErrorRow = null;
              })
              .fail((data) => {
                ui.error(data.error);
              });
          },
          'invalid-file': () => {
            ui.error(T.groupsInvalidCsv);
          },
          'update-search-result-users': (query: string) => {
            api.User.list({ query })
              .then(({ results }) => {
                this.searchResultUsers = results.map(
                  ({ key, value }: types.ListItem) => ({
                    key,
                    value: `${ui.escape(key)} (<strong>${ui.escape(
                      value,
                    )}</strong>)`,
                  }),
                );
              })
              .catch(ui.apiError);
          },
          'update-search-result-schools': (query: string) => {
            api.School.list({ query })
              .then(({ results }) => {
                if (!results.length) {
                  this.searchResultSchools = [
                    {
                      key: 0,
                      value: query,
                    },
                  ];
                  return;
                }
                this.searchResultSchools = results.map(
                  ({ key, value }: types.SchoolListItem) => ({
                    key,
                    value,
                  }),
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
