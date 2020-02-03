import { OmegaUp, UI, API, T } from '../omegaup.js';
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
      .fail(UI.apiError);
  }

  $.when(
    API.Contest.adminDetails({ contest_alias: contestAlias }),
    API.Contest.problems({ contest_alias: contestAlias }),
    API.Contest.users({ contest_alias: contestAlias }),
    API.Contest.requests({ contest_alias: contestAlias }),
    API.Contest.admins({ contest_alias: contestAlias }),
  )
    .done((contest, problems, users, requests, admins) => {
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
                  .then(data => UI.contestUpdated(data, contestAlias))
                  .fail(UI.apiError);
              },
              'add-problem': function(ev) {
                API.Contest.addProblem({
                  contest_alias: contestAlias,
                  order_in_contest: ev.order,
                  problem_alias: ev.alias,
                  points: ev.points,
                  commit: ev.selectedRevision
                    ? ev.selectedRevision.commit
                    : undefined,
                })
                  .then(function(response) {
                    if (response.status != 'ok') {
                      UI.error(response.error || 'error');
                      return;
                    }
                    UI.success(T.problemSuccessfullyAdded);
                    refresh(ev, API.Contest.problems, 'problems');
                  })
                  .fail(UI.apiError);
              },
              'remove-problem': function(ev) {
                API.Contest.removeProblem({
                  contest_alias: contestAlias,
                  problem_alias: ev.selected.alias,
                })
                  .then(function(response) {
                    if (response.status != 'ok') {
                      UI.error(response.error || 'error');
                      return;
                    }
                    UI.success(T.problemSuccessfullyRemoved);
                    refresh(ev, API.Contest.problems, 'problems');
                  })
                  .fail(UI.apiError);
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
                  .fail(UI.apiError);
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
                  .fail(UI.apiError);
              },
              'update-admission-mode': function(ev) {
                API.Contest.update({
                  contest_alias: contestAlias,
                  admission_mode: ev.admissionMode,
                })
                  .then(function(response) {
                    UI.contestUpdated(response, contestAlias);
                    refresh(ev, API.Contest.adminDetails, 'contest');
                  })
                  .fail(UI.apiError);
              },
              'add-user': function(ev) {
                let contestants = [];
                if (ev.contestants !== '')
                  contestants = ev.contestants.split(',');
                if (ev.contestant !== '') contestants.push(ev.contestant);
                let promises = contestants.map(function(contestant) {
                  return API.Contest.addUser({
                    contest_alias: contestAlias,
                    usernameOrEmail: contestant.trim(),
                  });
                });
                $.when
                  .apply($, promises)
                  .then(function() {
                    UI.success(T.bulkUserAddSuccess);
                    refresh(ev, API.Contest.users, 'users');
                  })
                  .fail(function() {
                    UI.error(T.bulkUserAddError);
                  });
              },
              'remove-user': function(ev) {
                API.Contest.removeUser({
                  contest_alias: contestAlias,
                  usernameOrEmail: ev.selected.username,
                })
                  .then(function(response) {
                    if (response.status != 'ok') {
                      UI.error(response.error || 'error');
                    }
                    UI.success(T.userRemoveSuccess);
                    refresh(ev, API.Contest.users, 'users');
                  })
                  .fail(UI.apiError);
              },
              'save-end-time': function(selected) {
                API.Contest.updateEndTimeForIdentity({
                  contest_alias: contestAlias,
                  username: selected.username,
                  end_time: selected.end_time.getTime() / 1000,
                })
                  .then(function(response) {
                    UI.success(T.userEndTimeUpdatedSuccessfully);
                  })
                  .fail(UI.apiError);
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
                    UI.success(T.contestEditContestClonedSuccessfully);
                  })
                  .fail(UI.apiError);
              },
              'add-group': function(ev) {
                API.Contest.addGroup({
                  contest_alias: contestAlias,
                  group: ev.groupName,
                })
                  .then(function(response) {
                    UI.success(T.contestGroupAdded);
                    refresh(ev, API.Contest.users, 'groups', 'groups');
                  })
                  .fail(UI.apiError);
              },
              'remove-group': function(ev) {
                API.Contest.removeGroup({
                  contest_alias: contestAlias,
                  group: ev.selected.alias,
                })
                  .then(function(response) {
                    UI.success(T.contestGroupRemoved);
                    refresh(ev, API.Contest.users, 'groups', 'groups');
                  })
                  .fail(UI.apiError);
              },
              'add-admin': function(ev) {
                API.Contest.addAdmin({
                  contest_alias: contestAlias,
                  usernameOrEmail: ev.user,
                })
                  .then(function(response) {
                    UI.success(T.adminAdded);
                    refresh(ev, API.Contest.admins, 'admins');
                  })
                  .fail(UI.apiError);
              },
              'remove-admin': function(ev) {
                API.Contest.removeAdmin({
                  contest_alias: contestAlias,
                  usernameOrEmail: ev.selected.username,
                })
                  .then(function(response) {
                    if (response.status != 'ok') {
                      UI.error(response.error || 'error');
                      return;
                    }
                    UI.success(T.adminRemoved);
                    refresh(ev, API.Contest.admins, 'admins');
                  })
                  .fail(UI.apiError);
              },
              'add-group-admin': function(ev) {
                API.Contest.addGroupAdmin({
                  contest_alias: contestAlias,
                  group: ev.groupName,
                })
                  .then(function(response) {
                    UI.success(T.groupAdminAdded);
                    refresh(
                      ev,
                      API.Contest.admins,
                      'groupAdmins',
                      'group_admins',
                    );
                  })
                  .fail(UI.apiError);
              },
              'remove-group-admin': function(ev) {
                API.Contest.removeGroupAdmin({
                  contest_alias: contestAlias,
                  group: ev.selected.alias,
                })
                  .then(function(response) {
                    UI.success(T.groupAdminRemoved);
                    refresh(
                      ev,
                      API.Contest.admins,
                      'groupAdmins',
                      'group_admins',
                    );
                  })
                  .fail(UI.apiError);
              },
            },
          });
        },
        methods: {
          arbitrateRequest: function(ev, username, resolution) {
            omegaup.API.Contest.arbitrateRequest({
              contest_alias: contestAlias,
              username: username,
              resolution: resolution,
              note: '',
            })
              .then(function(response) {
                UI.success(T.successfulOperation);
                refresh(ev, API.Contest.requests, 'requests', 'users');
              })
              .fail(UI.apiError);
          },
        },
        components: {
          'omegaup-contest-edit': contest_Edit,
        },
      });
    })
    .fail(UI.apiError);
});
