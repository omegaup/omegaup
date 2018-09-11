import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';
import arena_virtual from '../components/arena/Virtual.vue';

OmegaUp.on('ready', function() {
  let contestAlias =
      /\/arena\/([^\/]+)\/virtual/.exec(window.location.pathname)[1];
  let detail;
  API.Contest.publicDetails({contest_alias: contestAlias})
      .then(function(detail) {
        let virtual_ = new Vue({
          el: '#arena-virtual',
          render: function(createElement) {
            return createElement('omegaup-arena-virtual', {
              props: {
                title: detail.title,
                description: detail.description,
                startTime: detail.start_time,
                finishTime: detail.finish_time,
                scoreboard: detail.scoreboard,
                submissionGap: detail.submission_gap
              },
              on: {
                submit: function(ev) {
                  API.Contest.createVirtual({
                               alias: contestAlias,
                               start_time:
                                   ev.virtualContestStartTime.getTime() / 1000
                             })
                      .then(function(response) {
                        let virtualContestAlias = response.alias;
                        window.location =
                            '/contest/' + virtualContestAlias + '/edit/';
                      })
                      .fail(UI.apiError);
                }
              }
            });
          },
          components: {'omegaup-arena-virtual': arena_virtual}
        });
      })
      .fail(UI.apiError);
});
