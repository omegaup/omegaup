import Vue from 'vue';
import problem_Details from '../components/problem/Details.vue';
import qualitynomination_Demotion from '../components/qualitynomination/DemotionPopup.vue';
import qualitynomination_Promotion from '../components/qualitynomination/Popup.vue';
import { Arena, GetOptionsFromLocation } from '../arena/arena';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemDetailsv2Payload();
  const problemDetails = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-problem-details', {
        props: {
          activeTab: this.activeTab,
          allRuns: payload.allRuns,
          problem: payload.problem,
          runs: payload.runs,
          solvers: payload.solvers,
          user: payload.user,
          nominationStatus: payload.nominationStatus,
          histogram: payload.histogram,
          initialClarifications: this.initialClarifications,
          solutionStatus: this.solutionStatus,
          solution: this.solution,
          availableTokens: this.availableTokens,
          allTokens: this.allTokens,
          showNewRunWindow: this.showNewRunWindow,
        },
        on: {
          'submit-reviewer': (tag: string, qualitySeal: boolean) => {
            const contents: { quality_seal?: boolean; tag?: string } = {};
            if (tag) {
              contents.tag = tag;
            }
            contents.quality_seal = qualitySeal;
            api.QualityNomination.create({
              problem_alias: payload.problem.alias,
              nomination: 'quality_tag',
              contents: JSON.stringify(contents),
            }).catch(ui.apiError);
          },
          'submit-demotion': (source: qualitynomination_Demotion) => {
            api.QualityNomination.create({
              problem_alias: payload.problem.alias,
              nomination: 'demotion',
              contents: JSON.stringify({
                rationale: source.rationale || 'N/A',
                reason: source.selectedReason,
                original: source.original,
              }),
            }).catch(ui.apiError);
          },
          'submit-promotion': (source: qualitynomination_Promotion) => {
            const contents: {
              before_ac?: boolean;
              difficulty?: number;
              quality?: number;
              tags?: string[];
            } = {};
            if (!source.solved && source.tried) {
              contents.before_ac = true;
            }
            if (source.difficulty !== '') {
              contents.difficulty = Number.parseInt(source.difficulty, 10);
            }
            if (source.tags.length > 0) {
              contents.tags = source.tags;
            }
            if (source.quality !== '') {
              contents.quality = Number.parseInt(source.quality, 10);
            }
            api.QualityNomination.create({
              problem_alias: payload.problem.alias,
              nomination: 'suggestion',
              contents: JSON.stringify(contents),
            }).catch(ui.apiError);
          },
          'dismiss-promotion': (source: qualitynomination_Promotion) => {
            const contents: { before_ac?: boolean } = {};
            if (!source.solved && source.tried) {
              contents.before_ac = true;
            }
            api.QualityNomination.create({
              problem_alias: payload.problem.alias,
              nomination: 'dismissal',
              contents: JSON.stringify(contents),
            })
              .then((data) => {
                ui.info(T.qualityNominationRateProblemDesc);
              })
              .catch(ui.apiError);
          },
          'unlock-solution': () => {
            api.Problem.solution(
              {
                problem_alias: payload.problem.alias,
                forfeit_problem: true,
              },
              { quiet: true },
            )
              .then((data) => {
                if (!data.solution) {
                  ui.error(T.wordsProblemOrSolutionNotExist);
                  return;
                }
                this.solutionStatus = 'unlocked';
                this.solution = data.solution;
                ui.info(
                  ui.formatString(T.solutionTokens, {
                    available: this.availableTokens - 1,
                    total: this.allTokens,
                  }),
                );
              })
              .catch((error) => {
                if (error.httpStatusCode == 404) {
                  ui.error(T.wordsProblemOrSolutionNotExist);
                  return;
                }
                ui.apiError(error);
              });
          },
          'get-tokens': () => {
            api.ProblemForfeited.getCounts()
              .then((data) => {
                this.allTokens = data.allowed;
                this.availableTokens = data.allowed - data.seen;
                if (this.availableTokens <= 0) {
                  ui.warning(T.solutionNoTokens);
                }
              })
              .catch(ui.apiError);
          },
          'get-solution': () => {
            if (payload.solutionStatus === 'unlocked') {
              api.Problem.solution(
                { problem_alias: payload.problem.alias },
                { quiet: true },
              )
                .then((data) => {
                  if (!data.solution) {
                    ui.error(T.wordsProblemOrSolutionNotExist);
                    return;
                  }
                  this.solution = data.solution;
                })
                .catch((error) => {
                  if (error.httpStatusCode == 404) {
                    ui.error(T.wordsProblemOrSolutionNotExist);
                    return;
                  }
                  ui.apiError(error);
                });
            }
          },
          'clarification-response': (
            id: number,
            responseText: string,
            isPublic: boolean,
          ) => {
            api.Clarification.update({
              clarification_id: id,
              answer: responseText,
              public: isPublic,
            })
              .then(() => {
                api.Problem.clarifications({
                  problem_alias: payload.problem.alias,
                })
                  .then(
                    (response) =>
                      (this.initialClarifications = response.clarifications),
                  )
                  .catch(ui.apiError);
              })
              .catch(ui.apiError);
          },
          'dismiss-popup': () => {
            window.location.replace(`#${arenaInstance.activeTab}`);
          },
          'update:activeTab': (tabName: string) => {
            window.location.replace(`#${tabName}`);
          },
        },
      });
    },
    data: {
      initialClarifications: payload.clarifications,
      solutionStatus: payload.solutionStatus,
      solution: <types.ProblemStatement | null>null,
      availableTokens: 0,
      allTokens: 0,
      showNewRunWindow: false,
      activeTab: window.location.hash
        ? window.location.hash.substr(1).split('/')[0]
        : 'problems',
    },
    components: {
      'omegaup-problem-details': problem_Details,
    },
  });

  const arenaInstance = new Arena(GetOptionsFromLocation(window.location));
  arenaInstance.renderProblem(payload.problem);

  const onlyProblemHashChanged = () => {
    if (arenaInstance.activeTab !== 'problems') {
      return;
    }
    detectNewRun();
  };

  const detectNewRun = () => {
    if (window.location.hash.indexOf('/new-run') === -1) return;
    if (!payload.user.loggedIn) {
      window.location.href = `/login/?redirect=${escape(
        window.location.pathname,
      )}`;
    }
    problemDetails.showNewRunWindow = true;
  };

  window.addEventListener('hashchange', () => {
    onlyProblemHashChanged();
  });

  // Everything is loaded
  onlyProblemHashChanged();
});
