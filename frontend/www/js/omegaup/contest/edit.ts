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
          details: this.details,
          groups: payload.groups,
          groupAdmins: payload.group_admins,
          problems: this.problems,
          requests: this.requests,
          users: this.users,
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
          'update-admission-mode': (admissionMode: string) => {
            api.Contest.update({
              contest_alias: payload.details.alias,
              admission_mode: admissionMode,
            })
              .then(() => {
                ui.success(`
                  ${T.contestEditContestEdited} <a href="/arena/${payload.details.alias}/">${T.contestEditGoToContest}</a>
                `);
                this.refreshDetails();
                if (admissionMode === 'registration') {
                  this.refreshRequests();
                }
              })
              .catch(ui.apiError);
          },
          'add-user': (contestants: string[]) => {
            Promise.allSettled(
              contestants.map((user: string) =>
                api.Contest.addUser({
                  contest_alias: payload.details.alias,
                  usernameOrEmail: user,
                }),
              ),
            )
              .then((results) => {
                let contestantsWithError: string[] = [];
                results.forEach((result) => {
                  if (result.status === 'rejected') {
                    contestantsWithError.push(result.reason.userEmail);
                  }
                });
                this.refreshUsers();
                this.refreshRequests();
                if (!contestantsWithError.length) {
                  ui.success(T.bulkUserAddSuccess);
                } else {
                  ui.error(
                    ui.formatString(T.bulkUserAddError, {
                      userEmail: contestantsWithError.join('<br>'),
                    }),
                  );
                }
              })
              .catch(ui.ignoreError);
          },
          'remove-user': (contestant: types.ContestUser) => {
            api.Contest.removeUser({
              contest_alias: payload.details.alias,
              usernameOrEmail: contestant.username,
            })
              .then(() => {
                ui.success(T.userRemoveSuccess);
                this.refreshUsers();
              })
              .catch(ui.apiError);
          },
          'save-end-time': (user: types.ContestUser) => {
            if (user.end_time === undefined) {
              return;
            }
            api.Contest.updateEndTimeForIdentity({
              contest_alias: payload.details.alias,
              username: user.username,
              end_time: user.end_time,
            })
              .then(() => {
                ui.success(T.userEndTimeUpdatedSuccessfully);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    data: {
      details: payload.details,
      problems: payload.problems,
      requests: payload.requests,
      users: payload.users,
    },
    components: {
      'omegaup-contest-edit': contest_Edit,
    },
    methods: {
      refreshDetails: (): void => {
        api.Contest.adminDetails({
          contest: payload.details.alias,
        })
          .then((response) => {
            contestEdit.details = response;
          })
          .catch(ui.apiError);
      },
      refreshProblems: (): void => {
        api.Contest.problems({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.problems = response.problems;
          })
          .catch(ui.apiError);
      },
      refreshRequests: (): void => {
        api.Contest.requests({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.requests = response.users;
          })
          .catch(ui.apiError);
      },
      refreshUsers: (): void => {
        api.Contest.users({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.users = response.users;
          })
          .catch(ui.apiError);
      },
    },
  });
});
