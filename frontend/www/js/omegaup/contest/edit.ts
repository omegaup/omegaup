import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import T from '../lang';
import contest_Edit from '../components/contest/Edit.vue';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestEditPayload();

  const contestEdit = new Vue({
    el: '#main-container',
    components: {
      'omegaup-contest-edit': contest_Edit,
    },
    data: () => ({
      admins: payload.admins,
      details: payload.details,
      groupAdmins: payload.group_admins,
      groups: payload.groups,
      problems: payload.problems,
      requests: payload.requests,
      users: payload.users,
      searchResultProblems: [] as types.ListItem[],
      searchResultUsers: [] as types.ListItem[],
    }),
    methods: {
      arbitrateRequest: (username: string, resolution: boolean): void => {
        const resolutionText = resolution ? T.wordAccepted : T.wordsDenied;
        api.Contest.arbitrateRequest({
          contest_alias: payload.details.alias,
          username,
          resolution,
          note: resolutionText,
        })
          .then(() => {
            if (resolution) {
              ui.success(T.arbitrateRequestAcceptSuccessfully);
            } else {
              ui.success(T.arbitrateRequestDenySuccessfully);
            }
            contestEdit.refreshRequests();
          })
          .catch(ui.apiError);
      },
      refreshDetails: (): void => {
        api.Contest.adminDetails({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.details = response;
          })
          .catch(ui.apiError);
      },
      refreshGroups: (): void => {
        api.Contest.users({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.groups = response.groups;
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
      refreshAdmins: (): void => {
        api.Contest.admins({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.admins = response.admins;
          })
          .catch(ui.apiError);
      },
      refreshGroupAdmins: (): void => {
        api.Contest.admins({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.groupAdmins = response.group_admins;
          })
          .catch(ui.apiError);
      },
    },
    render: function (createElement) {
      return createElement('omegaup-contest-edit', {
        props: {
          admins: this.admins,
          details: this.details,
          groupAdmins: this.groupAdmins,
          groups: this.groups,
          problems: this.problems,
          requests: this.requests,
          users: this.users,
          searchResultProblems: this.searchResultProblems,
          searchResultUsers: this.searchResultUsers,
        },
        on: {
          'update-search-result-problems': (query: string) => {
            api.Problem.list({
              query,
            })
              .then((data) => {
                // Problems previously added into the contest should not be
                // shown in the dropdown
                const addedProblems = new Set(
                  this.problems.map((problem) => problem.alias),
                );
                this.searchResultProblems = data.results
                  .filter((problem) => !addedProblems.has(problem.alias))
                  .map((problem) => ({
                    key: problem.alias,
                    value: `${ui.escape(problem.title)} (<strong>${ui.escape(
                      problem.alias,
                    )}</strong>)`,
                  }));
              })
              .catch(ui.apiError);
          },
          'update-search-result-users': (query: string) => {
            api.User.list({ query })
              .then((data) => {
                // Users previously invited to the contest should not be shown
                // in the dropdown
                const addedUsers = new Set(
                  this.users.map((user) => user.username),
                );

                this.searchResultUsers = data
                  .filter((user) => !addedUsers.has(user.label))
                  .map((user) => ({
                    key: user.label,
                    value: `${ui.escape(user.label)} (<strong>${ui.escape(
                      user.value,
                    )}</strong>)`,
                  }));
              })
              .catch(ui.apiError);
          },
          'update-contest': function (contest: omegaup.Contest) {
            api.Contest.update(
              Object.assign({}, contest, {
                contest_alias: contest.alias,
                alias: null,
              }),
            )
              .then(() => {
                ui.success(`
                  ${T.contestEditContestEdited} <a href="/arena/${contest.alias}/">${T.contestEditGoToContest}</a>
                `);
              })
              .catch(ui.apiError);
          },
          'add-problem': (problem: types.ProblemsetProblem) => {
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
          'get-versions': ({
            request,
            target,
          }: {
            request: { problemAlias: string };
            target: {
              versionLog: types.ProblemVersion[];
              problems: types.ProblemsetProblem[];
              selectedRevision: types.ProblemVersion;
              publishedRevision: types.ProblemVersion;
            };
          }) => {
            api.Problem.versions({
              problem_alias: request.problemAlias,
              problemset_id: payload.details.problemset_id,
            })
              .then((result) => {
                target.versionLog = result.log;
                let currentProblem = null;
                for (const problem of target.problems) {
                  if (problem.alias === request.problemAlias) {
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
                    target.selectedRevision = target.publishedRevision = revision;
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
                }).catch(() => user),
              ),
            )
              .then((results) => {
                const contestantsWithError: string[] = results
                  .filter(
                    (result): result is PromiseRejectedResult =>
                      result.status === 'rejected',
                  )
                  .map((result) => result.reason);
                this.refreshUsers();
                this.refreshRequests();
                if (!contestantsWithError.length) {
                  ui.success(
                    contestants.length === 1
                      ? T.singleUserAddSuccess
                      : T.bulkUserAddSuccess,
                  );
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
          'accept-request': ({ username }: { username: string }) => {
            this.arbitrateRequest(username, true);
          },
          'deny-request': ({ username }: { username: string }) => {
            this.arbitrateRequest(username, false);
          },
          'add-admin': (username: string) => {
            api.Contest.addAdmin({
              contest_alias: payload.details.alias,
              usernameOrEmail: username,
            })
              .then(() => {
                ui.success(T.adminAdded);
                this.refreshAdmins();
              })
              .catch(ui.apiError);
          },
          'remove-admin': (username: string) => {
            api.Contest.removeAdmin({
              contest_alias: payload.details.alias,
              usernameOrEmail: username,
            })
              .then(() => {
                ui.success(T.adminRemoved);
                this.refreshAdmins();
              })
              .catch(ui.apiError);
          },
          'add-group-admin': (groupAlias: string) => {
            api.Contest.addGroupAdmin({
              contest_alias: payload.details.alias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.groupAdminAdded);
                this.refreshGroupAdmins();
              })
              .catch(ui.apiError);
          },
          'remove-group-admin': (groupAlias: string) => {
            api.Contest.removeGroupAdmin({
              contest_alias: payload.details.alias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.groupAdminRemoved);
                this.refreshGroupAdmins();
              })
              .catch(ui.apiError);
          },
          'add-group': (groupAlias: string) => {
            api.Contest.addGroup({
              contest_alias: payload.details.alias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.contestGroupAdded);
                this.refreshGroups();
              })
              .catch(ui.apiError);
          },
          'remove-group': (groupAlias: string) => {
            api.Contest.removeGroup({
              contest_alias: payload.details.alias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.contestGroupRemoved);
                this.refreshGroups();
              })
              .catch(ui.apiError);
          },
          'clone-contest': (
            title: string,
            alias: string,
            description: string,
            startTime: Date,
          ) => {
            api.Contest.clone({
              contest_alias: payload.details.alias,
              title: title,
              alias: alias,
              description: description,
              start_time: startTime.getTime() / 1000,
            })
              .then(() => {
                ui.success(T.contestEditContestClonedSuccessfully);
              })
              .catch(ui.apiError);
          },
          'archive-contest': (contestAlias: string, archive: string) => {
            api.Contest.archive({ contest_alias: contestAlias, archive })
              .then(() => {
                if (archive) {
                  ui.success(T.contestEditArchivedSuccess);
                  return;
                }
                ui.success(T.contestEditUnarchivedSuccess);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
