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
        },
        on: {
          'unlock-solution': function() {
            API.ProblemForfeited.getCounts({})
                .then(function(data) {
                  const allTokens = data.allowed;
                  const availableTokens = data.allowed - data.seen;
                  if (availableTokens <= 0) {
                    UI.warning(T.solutionNoTokens);
                    return;
                  }
                  API.Problem.solution({
                               'problem_alias': payload['alias'],
                               'forfeit_problem': true
                             })
                      .then(function(data) {
                        if (!data.exists || !data.solution) {
                          UI.error(T.wordsProblemOrSolutionNotExist);
                          return;
                        }
                        problemSolution.solution =
                            mdConverter.makeHtmlWithImages(
                                data.solution.markdown, data.solution.images);
                        problemSolution.status = 'unlocked';
                        UI.info(UI.formatString(T.solutionTokens, {
                          available: availableTokens - 1,
                          total: allTokens,
                        }));
                      })
                      .fail(omegaup.UI.apiError);
                })
                .fail(omegaup.UI.apiError);
          },
          'get-solution': function() {
            if (payload['solution_status'] === 'unlocked') {
              API.Problem.solution({'problem_alias': payload['alias']})
                  .then(function(data) {
                    if (!data.exists || !data.solution) {
                      return;
                    }
                    problemSolution.solution = mdConverter.makeHtmlWithImages(
                        data.solution.markdown, data.solution.images);
                  })
                  .fail(omegaup.UI.apiError);
            }
          }
        }
      });
    },
    data: {
      status: payload['solution_status'] ? payload['solution_status'] :
                                           'not_logged_in',
      solution: null,
    },
    components: {
      'omegaup-problem-solution': problem_Solution,
    }
  });
});
