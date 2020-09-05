import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import T from '../lang';
import contest_Edit from '../components/contest/Editv2.vue';
import contest_AddProblem from '../components/contest/AddProblemv2.vue';
import problem_Versions from '../components/problem/Versions.vue';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestEditPayload();

  const contestEdit = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-contest-edit', {
        props: {
          admins: payload.admins,
          details: payload.details,
          groups: payload.groups,
          groupAdmins: payload.group_admins,
          problems: this.problems,
          requests: payload.requests,
          users: payload.users,
        },
        on: {
          'add-problem': (problem: types.ContestProblem) => {
            api.Contest.addProblem({
              contest_alias: payload.details.alias,
              order_in_contest: problem.order,
              problem_alias: problem.alias,
              points: problem.points,
              commit: problem.commit,
            })
              .then(() => {
                ui.success(T.problemSuccessfullyAdded);
                this.refreshProblems();
              })
              .catch(ui.apiError);
          },
          'get-versions': (
            problemAlias: string,
            addProblemComponent: contest_AddProblem,
          ) => {
            api.Problem.versions({
              problem_alias: problemAlias,
            })
              .then((result) => {
                addProblemComponent.versionLog = result.log;
                let currentProblem = null;
                for (const problem of addProblemComponent.problems) {
                  if (problem.alias === problemAlias) {
                    currentProblem = problem;
                    break;
                  }
                }
                let publishedCommitHash = result.published;
                if (currentProblem != null) {
                  publishedCommitHash = currentProblem.commit;
                }
                for (const revision of result.log) {
                  if (publishedCommitHash === revision.commit) {
                    addProblemComponent.selectedRevision = addProblemComponent.publishedRevision = revision;
                    break;
                  }
                }
              })
              .catch(ui.apiError);
          },
          'remove-problem': (problemAlias: string) => {
            api.Contest.removeProblem({
              contest_alias: payload.details.alias,
              problem_alias: problemAlias,
            })
              .then(() => {
                ui.success(T.problemSuccessfullyRemoved);
                this.refreshProblems();
              })
              .catch(ui.apiError);
          },
          'runs-diff': (
            problemAlias: string,
            versionsComponent: types.CommitRunsDiff,
            selectedCommit: omegaup.Commit,
          ) => {
            api.Contest.runsDiff({
              problem_alias: problemAlias,
              contest_alias: payload.details.alias,
              version: selectedCommit.version,
            })
              .then((response) => {
                Vue.set(
                  versionsComponent.runsDiff,
                  selectedCommit.version,
                  response.diff,
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    data: {
      problems: payload.problems,
    },
    components: {
      'omegaup-contest-edit': contest_Edit,
    },
    methods: {
      refreshProblems: (): void => {
        api.Contest.problems({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.problems = response.problems;
          })
          .catch(ui.apiError);
      },
    },
  });
});
