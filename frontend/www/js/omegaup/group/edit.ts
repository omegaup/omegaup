import group_Edit, { AvailableTabs } from '../components/group/Edit.vue';
import group_Members from '../components/group/Members.vue';
import group_Scoreboards from '../components/group/Scoreboards.vue';
import group_Identities from '../components/group/Identities.vue';
import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import * as CSV from '@/third_party/js/csv.js/csv.js';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.GroupEditPayload();
  const groupEdit = new Vue({
    el: '#main-container',
    components: {
      'omegaup-group-edit': group_Edit,
    },
    data: () => ({
      tab: window.location.hash
        ? window.location.hash.substr(1)
        : AvailableTabs.Members,
      identities: payload.identities.filter(
        (identity) => identity.username.split(':').length === 1,
      ),
      identitiesCsv: payload.identities.filter(
        (identity) => identity.username.split(':').length !== 1,
      ),
      scoreboards: payload.scoreboards,
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
              (identity) => identity.username.split(':').length === 1,
            );
            groupEdit.identitiesCsv = data.identities.filter(
              (identity) => identity.username.split(':').length !== 1,
            );
          })
          .catch(ui.apiError);
      },
      generatePassword: (): string => {
        const validChars = 'acdefhjkmnpqruvwxyACDEFHJKLMNPQRUVWXY346';
        const len = 8;
        // Browser supports window.crypto
        if (typeof window.crypto == 'object') {
          const arr = new Uint8Array(2 * len);
          window.crypto.getRandomValues(arr);
          return Array.from(
            arr.filter((value) => value <= 255 - (255 % validChars.length)),
            (value) => validChars[value % validChars.length],
          )
            .join('')
            .substr(0, len);
        }

        // Browser does not support window.crypto
        let password = '';
        for (let i = 0; i < len; i++) {
          password += validChars.charAt(
            Math.floor(Math.random() * validChars.length),
          );
        }
        return password;
      },
    },
    render: function (createElement) {
      return createElement('omegaup-group-edit', {
        props: {
          groupAlias: payload.groupAlias,
          groupName: payload.groupName,
          countries: payload.countries,
          isOrganizer: payload.isOrganizer,
          tab: this.tab,
          identities: this.identities,
          identitiesCsv: this.identitiesCsv,
          scoreboards: this.scoreboards,
        },
        on: {
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
            identity: omegaup.Identity,
          ) => {
            source.showEditForm = true;
            source.showChangePasswordForm = false;
            source.identity = identity;
            source.username = identity.username;
          },
          'edit-identity-member': (
            membersSource: group_Members,
            originalUsername: string,
            username: string,
            name: string,
            gender: string,
            countryId: string,
            stateId: string,
            school: string,
            schoolId: string,
          ) => {
            api.Identity.update({
              username: username,
              name: name,
              gender: gender,
              country_id: countryId,
              state_id: stateId,
              school_name: school,
              school_id: schoolId,
              group_alias: payload.groupAlias,
              original_username: originalUsername,
            })
              .then(() => {
                ui.success(T.groupEditMemberUpdated);
                membersSource.showEditForm = false;
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
          'bulk-identities': (
            source: group_Identities,
            identities: omegaup.Identity[],
          ) => {
            api.Identity.bulkCreate({
              identities: JSON.stringify(identities),
              group_alias: payload.groupAlias,
            })
              .then(() => {
                this.refreshMemberList();
                this.tab = AvailableTabs.Members;
                ui.success(T.groupsIdentitiesSuccessfullyCreated);
              })
              .catch(function (data) {
                ui.error(data.error);
                source.userErrorRow = data.parameter;
              });
          },
          'download-identities': (identities: omegaup.Identity[]) => {
            const dialect = {
              dialect: {
                csvddfVersion: 1.2,
                delimiter: ',',
                doubleQuote: true,
                lineTerminator: '\r\n',
                quoteChar: '"',
                skipInitialSpace: true,
                header: true,
                commentChar: '#',
              },
            };
            const csv = CSV.serialize(
              {
                fields: [
                  { id: 'username' },
                  { id: 'name' },
                  { id: 'password' },
                  { id: 'country_id' },
                  { id: 'state_id' },
                  { id: 'gender' },
                  { id: 'school_name' },
                ],
                records: identities,
              },
              dialect,
            );
            const hiddenElement = document.createElement('a');
            hiddenElement.href = `data:text/csv;charset=utf-8,${window.encodeURIComponent(
              csv,
            )}`;
            hiddenElement.target = '_blank';
            hiddenElement.download = 'identities.csv';
            hiddenElement.click();
          },
          'read-csv': (
            source: group_Identities,
            fileUpload: HTMLInputElement,
          ) => {
            source.identities = [];
            CSV.fetch({
              file: fileUpload.files ? fileUpload.files[0] : null,
            }).done((dataset: any) => {
              if (dataset.fields.length != 6) {
                ui.error(T.groupsInvalidCsv);
                return;
              }
              for (const cells of dataset.records) {
                source.identities.push({
                  username: `${payload.groupAlias}:${cells[0]}`,
                  name: cells[1],
                  password: this.generatePassword(),
                  country_id: cells[2],
                  state_id: cells[3],
                  gender: cells[4],
                  school_name: cells[5],
                });
              }
              source.userErrorRow = null;
            });
          },
        },
      });
    },
  });
});
