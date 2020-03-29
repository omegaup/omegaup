import Vue from 'vue';
import problem_List from '../components/problem/List.vue';
import { OmegaUp } from '../omegaup';
import T from '../lang';
import API from '../api.js';
import * as UI from '../ui';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  omegaup.API.Tag.list({ query: '' })
    .then(tagData => {
      let problemsList = new Vue({
        el: '#problem-list',
        render: function(createElement) {
          return createElement('omegaup-problem-list', {
            props: {
              problems: this.problems,
              loggedIn: this.loggedIn,
              currentTags: this.currentTags,
              pagerItems: this.pagerItems,
              wizardTags: tagData,
            },
            on: {
              'wizard-search': function(queryParameters) {
                window.location.search = UI.buildURLQuery(queryParameters);
              },
            },
          });
        },
        data: {
          problems: payload.problems,
          loggedIn: payload.loggedIn,
          currentTags: payload.currentTags,
          pagerItems: payload.pagerItems,
          wizardTags: {},
        },
        components: {
          'omegaup-problem-list': problem_List,
        },
      });
    })
    .catch(UI.apiError);
});
