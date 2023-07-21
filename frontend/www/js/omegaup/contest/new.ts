import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import contest_NewForm from '../components/contest/NewForm.vue';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestNewPayload();
  const startTime = new Date();
  const finishTime = new Date(startTime.getTime() + 5 * 60 * 60 * 1000);
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-contest-new': contest_NewForm,
    },
    data: () => ({
      invalidParameterName: null as null | string,
      searchResultTeamsGroups: [] as types.ListItem[],
    }),
    render: function (createElement) {
      return createElement('omegaup-contest-new', {
        props: {
          allLanguages: payload.languages,
          initialLanguages: Object.keys(payload.languages),
          update: false,
          initialStartTime: startTime,
          initialFinishTime: finishTime,
          invalidParameterName: this.invalidParameterName,
          searchResultTeamsGroups: this.searchResultTeamsGroups,
        },
        on: {
          'create-contest': ({
            contest,
            teamsGroupAlias,
          }: {
            contest: types.ContestAdminDetails;
            teamsGroupAlias?: types.ListItem;
          }): void => {
            api.Contest.create({
              ...contest,
              teams_group_alias: teamsGroupAlias?.key,
            })
              .then(() => {
                this.invalidParameterName = null;
                window.location.replace(
                  `/contest/${contest.alias}/edit/#problems`,
                );
              })
              .catch((error) => {
                ui.apiError(error);
                this.invalidParameterName = error.parameter || null;
              });
          },
          'update-search-result-teams-groups': (query: string) => {
            api.TeamsGroup.list({
              query,
            })
              .then((data) => {
                this.searchResultTeamsGroups = data.map(
                  ({ key, value }: { key: string; value: string }) => ({
                    key,
                    value: `${ui.escape(value)} (<strong>${ui.escape(
                      key,
                    )}</strong>)`,
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
