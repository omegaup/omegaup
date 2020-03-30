import { OmegaUp } from '../omegaup';
import T from '../lang';
import API from '../api.js';
import * as UI from '../ui';
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
          allTokens: this.allTokens,
          availableTokens: this.availableTokens,
        },
        on: {
          'unlock-solution': function() {
            API.Problem.solution({
              problem_alias: payload['alias'],
              forfeit_problem: true,
            })
              .then(function(data) {
                if (!data.exists || !data.solution) {
                  UI.error(T.wordsProblemOrSolutionNotExist);
                  return;
                }
                problemSolution.solution = mdConverter.makeHtmlWithImages(
                  data.solution.markdown,
                  data.solution.images,
                );
                problemSolution.status = 'unlocked';
                UI.info(
                  UI.formatString(T.solutionTokens, {
                    available: problemSolution.availableTokens - 1,
                    total: problemSolution.allTokens,
                  }),
                );
              })
              .catch(UI.apiError);
          },
          'get-tokens': function() {
            API.ProblemForfeited.getCounts({})
              .then(function(data) {
                problemSolution.allTokens = data.allowed;
                problemSolution.availableTokens = data.allowed - data.seen;
                if (problemSolution.availableTokens <= 0) {
                  UI.warning(T.solutionNoTokens);
                }
              })
              .catch(UI.apiError);
          },
          'get-solution': function() {
            if (payload['solution_status'] === 'unlocked') {
              API.Problem.solution({ problem_alias: payload['alias'] })
                .then(function(data) {
                  if (!data.exists || !data.solution) {
                    return;
                  }
                  problemSolution.solution = mdConverter.makeHtmlWithImages(
                    data.solution.markdown,
                    data.solution.images,
                  );
                })
                .catch(UI.apiError);
            }
          },
        },
      });
    },
    data: {
      status: payload['solution_status'] || 'not_logged_in',
      solution: null,
      allTokens: null,
      availableTokens: null,
    },
    components: {
      'omegaup-problem-solution': problem_Solution,
    },
  });
});
