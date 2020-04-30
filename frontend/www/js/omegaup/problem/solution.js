import { OmegaUp } from '../omegaup';
import T from '../lang';
import * as api from '../api_transitional';
import * as markdown from '../markdown';
import * as ui from '../ui';
import problem_Solution from '../components/problem/Solution.vue';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const mdConverter = markdown.markdownConverter();
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
            api.Problem.solution({
              problem_alias: payload['alias'],
              forfeit_problem: true,
            })
              .then(function(data) {
                if (!data.exists || !data.solution) {
                  ui.error(T.wordsProblemOrSolutionNotExist);
                  return;
                }
                problemSolution.solution = mdConverter.makeHtmlWithImages(
                  data.solution.markdown,
                  data.solution.images,
                );
                problemSolution.status = 'unlocked';
                ui.info(
                  ui.formatString(T.solutionTokens, {
                    available: problemSolution.availableTokens - 1,
                    total: problemSolution.allTokens,
                  }),
                );
              })
              .catch(ui.apiError);
          },
          'get-tokens': function() {
            api.ProblemForfeited.getCounts({})
              .then(function(data) {
                problemSolution.allTokens = data.allowed;
                problemSolution.availableTokens = data.allowed - data.seen;
                if (problemSolution.availableTokens <= 0) {
                  ui.warning(T.solutionNoTokens);
                }
              })
              .catch(ui.apiError);
          },
          'get-solution': function() {
            if (payload['solution_status'] === 'unlocked') {
              api.Problem.solution({ problem_alias: payload['alias'] })
                .then(function(data) {
                  if (!data.exists || !data.solution) {
                    return;
                  }
                  problemSolution.solution = mdConverter.makeHtmlWithImages(
                    data.solution.markdown,
                    data.solution.images,
                  );
                })
                .catch(ui.apiError);
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
