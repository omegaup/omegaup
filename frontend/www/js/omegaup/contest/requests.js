import contest_Requests from '../components/contest/Requests.vue';
import {API, OmegaUp, UI, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let payload = JSON.parse(document.getElementById('payload').innerText);
  let contestAlias =
      /\/contest\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];
  let contestRequests = new Vue({
    el: '#contest-requests',
    render: function(createElement) {
      return createElement('omegaup-contest-requests', {
        props: {requests: this.requests},
        on: {
          'accept-request': (username) => this.arbitrateRequest(username, true),
          'deny-request': (username) => this.arbitrateRequest(username, false),
        }
      });
    },
    data: {requests: payload.users},
    components: {
      'omegaup-contest-requests': contest_Requests,
    },
    methods: {
      arbitrateRequest: function(username, resolution) {
        omegaup.API.Contest.arbitrateRequest({
                             contest_alias: contestAlias,
                             username: username,
                             resolution: resolution,
                             note: '',
                           })
            .then(function(response) { UI.success(T.successfulOperation); })
            .fail(UI.apiError);
      }
    }
  });
});
