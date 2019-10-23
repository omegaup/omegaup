import Vue from 'vue';
import problem_List from '../components/problem/List.vue';
import { OmegaUp, T, API } from '../omegaup.js';
import UI from '../ui.js';

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
          loggedIn: payload.logged_in,
          currentTags: payload.current_tags,
          wizardTags: {},
        },
        components: {
          'omegaup-problem-list': problem_List,
        },
      });
    })
    .fail(omegaup.UI.apiError);
});
