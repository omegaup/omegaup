import Vue from 'vue';
import problem_List from '../components/problem/List.vue';
import { OmegaUp } from '../omegaup';
import T from '../lang';
import API from '../api.js';
import * as UI from '../ui';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  API.Tag.list({ query: '' })
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
              language: this.language,
              languages: this.languages,
              keyword: this.keyword,
              modes: this.modes,
              columns: this.columns,
              mode: this.mode,
              column: this.column,
              tags: this.tags,
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
          language: payload.language,
          languages: payload.languages,
          keyword: payload.keyword,
          modes: payload.modes,
          columns: payload.columns,
          mode: payload.mode,
          column: payload.column,
          tags: payload.tags,
        },
        components: {
          'omegaup-problem-list': problem_List,
        },
      });
    })
    .catch(UI.apiError);
});
