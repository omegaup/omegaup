import teamsgroup_Edit, {
  AvailableTabs,
} from '../components/teamsgroup/Edit.vue';
import teamsgroup_Teams from '../components/teamsgroup/Teams.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import * as CSV from '@/third_party/js/csv.js/csv.js';

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
      userErrorRow: null,
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
          teamsGroupAlias: payload.teamGroup.alias,
          teamsGroupName: payload.teamGroup.name,
          teamsGroupDescription: payload.teamGroup.description,
          countries: payload.countries,
          isOrganizer: payload.isOrganizer,
          tab: this.tab,
          teamsIdentities: this.teamsIdentities,
          userErrorRow: this.userErrorRow,
        },
        on: {
          'update-teams-group': ({
            name,
            description,
          }: {
            name: string;
            description: string;
          }) => {
            api.TeamsGroup.update({
              alias: payload.teamGroup.alias,
              name,
              description,
            })
              .then(() => {
                ui.success(T.teamsGroupEditGroupUpdated);
              })
              .catch(ui.apiError);
          },
          'edit-identity-member': (
            originalUsername: string,
            user: types.Identity,
          ) => {
            const request = Object.assign({}, user, {
              group_alias: payload.teamGroup.alias,
              original_username: originalUsername,
              school_name: user.school,
            });
            api.Identity.update(request)
              .then(() => {
                ui.success(T.groupEditMemberUpdated);
                this.refreshTeamsList();
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
                ui.success(T.groupEditMemberRemoved);
              })
              .catch(ui.apiError);
          },
          cancel: (source: teamsgroup_Teams) => {
            this.refreshTeamsList();
            source.$el.scrollIntoView();
          },
          'bulk-identities': (identities: types.Identity[]) => {
            console.log(identities);
            api.Identity.bulkCreateForTeams({
              team_identities: JSON.stringify(identities),
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
          'download-identities': (identities: types.Identity[]) => {
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
                if (!dataset.fields || dataset.fields.length != 5) {
                  ui.error(T.groupsInvalidCsv);
                  return;
                }
                for (const [
                  username,
                  name,
                  country_id,
                  state_id,
                  school_name,
                ] of cleanRecords(dataset.records)) {
                  identities.push({
                    username: `${payload.teamGroup.alias}:${username}`,
                    name,
                    password: humanReadable
                      ? generateHumanReadablePassword()
                      : generatePassword(),
                    country_id,
                    state_id,
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
        },
      });
    },
  });

  function cleanRecords(
    records: (null | number | string)[][],
  ): (undefined | string)[][] {
    return records.map((row) =>
      row.map((cell) => {
        if (cell === null) {
          return undefined;
        }
        if (typeof cell !== 'string') {
          return String(cell);
        }
        return cell;
      }),
    );
  }
});

export function generatePassword(): string {
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
}

export function generateHumanReadablePassword() {
  const words = {
    es: [
      'Loro',
      'Perro',
      'Pollo',
      'Lagarto',
      'Gato',
      'Toro',
      'Vaca',
      'Sapo',
      'Oso',
      'Zorro',
    ],
    en: [
      'Parrot',
      'Dog',
      'Chicken',
      'Lizard',
      'Cat',
      'Bull',
      'Cow',
      'Frog',
      'Bear',
      'Fox',
    ],
    pt: [
      'Papagaio',
      'Cachorro',
      'Frango',
      'Lagarto',
      'Gato',
      'Touro',
      'Vaca',
      'Sapo',
      'Urso',
      'Raposa',
    ],
  };
  const wordsNumber = 12;
  const totalNumbers = 6;

  let langWords: string[] = [];
  switch (T.locale) {
    case 'es':
      langWords = words.es;
      break;
    case 'pt':
      langWords = words.pt;
      break;
    default:
      langWords = words.en;
  }
  let password = '';
  for (let i = 0; i < wordsNumber; i++) {
    password += langWords[Math.floor(Math.random() * langWords.length)];
  }
  for (let i = 0; i < totalNumbers; i++) {
    password += Math.floor(Math.random() * 10); // random numbers
  }
  return password;
}
