import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';
import problem_Solution from '../components/problem/Solution.vue';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const mdConverter = UI.markdownConverter();
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let problemSolution = new Vue({
    el: '#problem-solution',
    render: function(createElement) {
      return createElement('omegaup-problem-solution', {
        props: {
          status: this.status,
          solution: this.solution,
          availableTokens: this.availableTokens,
          allTokens: this.allTokens,
        },
        on: {
          'unlock-solution': function() {
            API.Problem.solution({
                         'problem_alias': payload['alias'],
                         'forfeit_problem': true
                       })
                .then(function(data) {
                  if (data.exists && data.solution) {
                    problemSolution.solution = mdConverter.makeHtmlWithImages(
                        data.solution.markdown, data.solution.images);
                    problemSolution.status = 'unlocked';
                  }
                })
                .fail(omegaup.UI.apiError);
          }
        }
      });
    },
    data: {
      status: payload['solution_status'] ? payload['solution_status'] :
                                           'not_logged_in',
      solution: null,
      availableTokens: 0,
      allTokens: 0,
    },
    components: {
      'omegaup-problem-solution': problem_Solution,
    }
  });

  if (payload['solution_status'] === 'unlocked') {
    API.Problem.solution({'problem_alias': payload['alias']})
        .then(function(data) {
          if (data.exists && data.solution) {
            problemSolution.solution = mdConverter.makeHtmlWithImages(
                data.solution.markdown, data.solution.images);
          }
        })
        .fail(omegaup.UI.apiError);
  }

  if (payload['solution_status'] === 'locked') {
    API.ProblemForfeited.getCounts({})
        .then(function(data) {
          problemSolution.allTokens = data.allowed;
          problemSolution.availableTokens = data.allowed - data.seen;
        })
        .fail(omegaup.UI.apiError);
  }
});
