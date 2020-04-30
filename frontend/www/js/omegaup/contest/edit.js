import { OmegaUp } from '../omegaup';
import API from '../api.js';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import contest_Edit from '../components/contest/Edit.vue';

OmegaUp.on('ready', function() {
  const contestAlias = /\/contest\/([^\/]+)\/edit\/?.*/.exec(
    window.location.pathname,
  )[1];

  function refresh(ev, api, param, key) {
    key = key || param;
    api({ contest_alias: contestAlias })
      .then(response => {
        ev[param] = response[key] || response;
        ev.$parent[param] = response[key] || response;
      })
      .catch(ui.apiError);
  }

  Promise.all([
    API.Contest.adminDetails({ contest_alias: contestAlias }),
    API.Contest.problems({ contest_alias: contestAlias }),
    API.Contest.users({ contest_alias: contestAlias }),
    API.Contest.requests({ contest_alias: contestAlias }),
    API.Contest.admins({ contest_alias: contestAlias }),
  ])
    .then(([contest, problems, users, requests, admins]) => {
      problems = problems.problems;
      let groups = users.groups;
      users = users.users;
      let groupAdmins = admins.group_admins;
      requests = requests.users;
      admins = admins.admins;
      let contest_edit = new Vue({
        el: '#contest-edit',
        render: function(createElement) {
          return createElement('omegaup-contest-edit', {
            props: {
              data: {
                contest: contest,
                problems: problems,
                users: users,
                requests: requests,
                admins: admins,
                groups: groups,
                groupAdmins: groupAdmins,
              },
            },
            on: {
              'accept-request': (ev, username) =>
                this.arbitrateRequest(ev, username, true),
              'deny-request': (ev, username) =>
                this.arbitrateRequest(ev, username, false),
              'update-contest': function(ev) {
                API.Contest.update({
                  contest_alias: contestAlias,
                  title: ev.title,
                  description: ev.description,
                  start_time: ev.startTime.getTime() / 1000,
                  finish_time: ev.finishTime.getTime() / 1000,
                  window_length:
                    ev.windowLength == '' ||
                    ev.windowLength == null ||
                    !ev.windowLengthEnabled
                      ? 0
                      : ev.windowLength,
                  points_decay_factor: ev.pointsDecayFactor,
                  submissions_gap: ev.submissionsGap,
                  languages: ev.languages[0] == '' ? [] : ev.languages,
                  feedback: ev.feedback,
                  penalty: ev.penalty,
                  scoreboard: ev.scoreboard,
                  penalty_type: ev.penaltyType,
                  show_scoreboard_after: ev.showScoreboardAfter,
                  basic_information: ev.needsBasicInformation ? 1 : 0,
                  requests_user_information: ev.requestsUserInformation,
                })
                  .then(data => {
                    ui.success(
                      T.contestEditContestEdited +
                        ` <a href="/arena/${contestAlias}/">${T.contestEditGoToContest}</a>`,
                    );
                  })
                  .catch(ui.apiError);
              },
              'add-problem': function(ev) {
                API.Contest.addProblem({
                  contest_alias: contestAlias,
                  order_in_contest: ev.order,
                  problem_alias: ev.alias,
                  points: ev.points,
                  commit:
                    !ev.useLatestVersion && ev.selectedRevision
                      ? ev.selectedRevision.commit
                      : undefined,
                })
                  .then(function(response) {
                    if (response.status != 'ok') {
                      ui.error(response.error || 'error');
                      return;
                    }
                    ui.success(T.problemSuccessfullyAdded);
                    refresh(ev, API.Contest.problems, 'problems');
                  })
                  .catch(ui.apiError);
              },
              'remove-problem': function(ev) {
                API.Contest.removeProblem({
                  contest_alias: contestAlias,
                  problem_alias: ev.selected.alias,
                })
                  .then(function(response) {
                    if (response.status != 'ok') {
                      ui.error(response.error || 'error');
                      return;
                    }
                    ui.success(T.problemSuccessfullyRemoved);
                    refresh(ev, API.Contest.problems, 'problems');
                  })
                  .catch(ui.apiError);
              },
              'runs-diff': (ev, versions, selectedCommit) => {
                API.Contest.runsDiff({
                  problem_alias: ev.alias,
                  contest_alias: ev.contestAlias,
                  version: selectedCommit.version,
                })
                  .then(function(response) {
                    versions.$set(
                      versions.runsDiff,
                      selectedCommit.version,
                      response.diff,
                    );
                  })
                  .catch(ui.apiError);
              },
              'get-versions': (problemAlias, problemComponent) => {
                API.Problem.versions({ problem_alias: problemAlias })
                  .then(function(result) {
                    problemComponent.versionLog = result.log;
                    let currentProblem = null;
                    for (const problem of problemComponent.problems) {
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
                        problemComponent.selectedRevision = problemComponent.publishedRevision = revision;
                        break;
                      }
                    }
                  })
                  .catch(ui.apiError);
              },
              'update-admission-mode': function(ev) {
                API.Contest.update({
                  contest_alias: contestAlias,
                  admission_mode: ev.admissionMode,
                })
                  .then(data => {
                    ui.success(
                      T.contestEditContestEdited +
                        ` <a href="/arena/${contestAlias}/">${T.contestEditGoToContest}</a>`,
                    );
                    refresh(ev, API.Contest.adminDetails, 'contest');
                  })
                  .catch(ui.apiError);
              },
              'add-user': function(ev) {
                let contestants = [];
                if (ev.contestants !== '')
                  contestants = ev.contestants.split(',');
                if (ev.contestant !== '') contestants.push(ev.contestant);
                Promise.allSettled(
                  contestants.map(contestant =>
                    API.Contest.addUser({
                      contest_alias: contestAlias,
                      usernameOrEmail: contestant.trim(),
                    }),
                  ),
                )
                  .then(results => {
                    let contestantsWithError = [];
                    results.forEach(result => {
                      if (result.status === 'rejected') {
                        contestantsWithError.push(result.reason.userEmail);
                      }
                    });
                    refresh(ev, API.Contest.users, 'users');
                    if (contestantsWithError.length === 0) {
                      ui.success(T.bulkUserAddSuccess);
                      return;
                    }
                    ui.error(
                      ui.formatString(T.bulkUserAddError, {
                        userEmail: contestantsWithError.join('<br>'),
                      }),
                    );
                  })
                  .catch(ui.ignoreError);
              },
              'remove-user': function(ev) {
                API.Contest.removeUser({
                  contest_alias: contestAlias,
                  usernameOrEmail: ev.selected.username,
                })
                  .then(function(response) {
                    if (response.status != 'ok') {
                      ui.error(response.error || 'error');
                    }
                    ui.success(T.userRemoveSuccess);
                    refresh(ev, API.Contest.users, 'users');
                  })
                  .catch(ui.apiError);
              },
              'save-end-time': function(selected) {
                API.Contest.updateEndTimeForIdentity({
                  contest_alias: contestAlias,
                  username: selected.username,
                  end_time: selected.end_time.getTime() / 1000,
                })
                  .then(function(response) {
                    ui.success(T.userEndTimeUpdatedSuccessfully);
                  })
                  .catch(ui.apiError);
              },
              'clone-contest': function(ev) {
                API.Contest.clone({
                  contest_alias: contestAlias,
                  title: ev.title,
                  alias: ev.alias,
                  description: ev.description,
                  start_time: ev.startTime.getTime() / 1000,
                })
                  .then(function(response) {
                    ui.success(T.contestEditContestClonedSuccessfully);
                  })
                  .catch(ui.apiError);
              },
              'add-group': function(ev) {
                API.Contest.addGroup({
                  contest_alias: contestAlias,
                  group: ev.groupName,
                })
                  .then(function(response) {
                    ui.success(T.contestGroupAdded);
                    refresh(ev, API.Contest.users, 'groups', 'groups');
                  })
                  .catch(ui.apiError);
              },
              'remove-group': function(ev) {
                API.Contest.removeGroup({
                  contest_alias: contestAlias,
                  group: ev.selected.alias,
                })
                  .then(function(response) {
                    ui.success(T.contestGroupRemoved);
                    refresh(ev, API.Contest.users, 'groups', 'groups');
                  })
                  .catch(ui.apiError);
              },
              'add-admin': function(ev) {
                API.Contest.addAdmin({
                  contest_alias: contestAlias,
                  usernameOrEmail: ev.user,
                })
                  .then(function(response) {
                    ui.success(T.adminAdded);
                    refresh(ev, API.Contest.admins, 'admins');
                  })
                  .catch(ui.apiError);
              },
              'remove-admin': function(ev) {
                API.Contest.removeAdmin({
                  contest_alias: contestAlias,
                  usernameOrEmail: ev.selected.username,
                })
                  .then(function(response) {
                    if (response.status != 'ok') {
                      ui.error(response.error || 'error');
                      return;
                    }
                    ui.success(T.adminRemoved);
                    refresh(ev, API.Contest.admins, 'admins');
                  })
                  .catch(ui.apiError);
              },
              'add-group-admin': function(ev) {
                API.Contest.addGroupAdmin({
                  contest_alias: contestAlias,
                  group: ev.groupAlias,
                })
                  .then(function(response) {
                    ui.success(T.groupAdminAdded);
                    refresh(
                      ev,
                      API.Contest.admins,
                      'groupAdmins',
                      'group_admins',
                    );
                  })
                  .catch(ui.apiError);
              },
              'remove-group-admin': function(ev) {
                API.Contest.removeGroupAdmin({
                  contest_alias: contestAlias,
                  group: ev.selected.alias,
                })
                  .then(function(response) {
                    ui.success(T.groupAdminRemoved);
                    refresh(
                      ev,
                      API.Contest.admins,
                      'groupAdmins',
                      'group_admins',
                    );
                  })
                  .catch(ui.apiError);
              },
            },
          });
        },
        methods: {
          arbitrateRequest: function(ev, username, resolution) {
            const resolutionText = resolution ? T.wordAccepted : T.wordsDenied;
            API.Contest.arbitrateRequest({
              contest_alias: contestAlias,
              username: username,
              resolution: resolution,
              note: resolutionText,
            })
              .then(function(response) {
                ui.success(T.successfulOperation);
                refresh(ev, API.Contest.requests, 'requests', 'users');
              })
              .catch(ui.apiError);
          },
        },
        components: {
          'omegaup-contest-edit': contest_Edit,
        },
      });
    })
    .catch(ui.apiError);
});
