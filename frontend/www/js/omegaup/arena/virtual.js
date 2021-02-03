import { OmegaUp } from '../omegaup-legacy';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import arena_virtual from '../components/arena/Virtual.vue';

OmegaUp.on('ready', function () {
  let contestAlias = /\/arena\/([^\/]+)\/virtual/.exec(
    window.location.pathname,
  )[1];
  let detail;
  api.Contest.publicDetails({ contest_alias: contestAlias })
    .then(function (detail) {
      let virtual_ = new Vue({
        el: '#arena-virtual',
        render: function (createElement) {
          return createElement('omegaup-arena-virtual', {
            props: {
              title: detail.title,
              description: detail.description,
              startTime: detail.start_time,
              finishTime: detail.finish_time,
              scoreboard: detail.scoreboard,
              submissionsGap: detail.submissions_gap,
            },
            on: {
              submit: function (ev) {
                api.Contest.createVirtual({
                  alias: contestAlias,
                  start_time: ev.virtualContestStartTime.getTime() / 1000,
                })
                  .then(function (response) {
                    let virtualContestAlias = response.alias;
                    window.location =
                      '/contest/' + virtualContestAlias + '/edit/';
                  })
                  .catch(ui.apiError);
              },
            },
          });
        },
        components: { 'omegaup-arena-virtual': arena_virtual },
      });
    })
    .catch(ui.apiError);
});
