import Vue from 'vue';
import problem_List from '../components/problem/List.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let problemsList = new Vue({
    el: '#problem-list',
    render: function(createElement) {
      return createElement('omegaup-problem-list', {
        props: {
          problems: this.problems,
          loggedIn: this.loggedIn,
          currentTags: this.currentTags,
          wizardTags: this.wizardTags,
        },
        on: {
          'wizard-search': function(queryParameters) {
            let url = UI.buildURLQuery(queryParameters);
            // Multiple tags could not be loaded on query parameters
            if (self.selectedTags !== undefined &&
                self.selectedTags.length > 0) {
              url += self.selectedTags.map((tag) => `&tag[]=${tag}`)
                         .reduce((query, tag) => query += tag);
            }
            window.location.search = url;
          },
        },
      });
    },
    mounted: function() {
      const self = this;
      omegaup.API.Tag.list({query: ''})
          .then(function(data) {
            data.forEach(tagObject => {self.wizardTags[tagObject.name] =
                                           tagObject.name});
          })
          .fail(omegaup.UI.apiError);
    },
    data: {
      problems: payload.problems,
      loggedIn: payload.logged_in,
      currentTags: payload.current_tags,
      wizardTags: {},
    },
    components: {
      'omegaup-problem-list': problem_List,
    }
  });
});
