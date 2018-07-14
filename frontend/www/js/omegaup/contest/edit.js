import {OmegaUp, UI, API, T} from '../omegaup.js';
import Vue from 'vue';
import ContestEdit from '../components/contest/ContestEdit.vue';

OmegaUp.on('ready', function() {
  var contestAlias =
      /\/contest\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

  var trigger =
      {
        updateContest: (ev) => {
          API.Contest.update({
                       contest_alias: contestAlias,
                       title: ev.title,
                       description: ev.description,
                       start_time: (ev.startTime.getTime()) / 1000,
                       finish_time: (ev.finishTime.getTime()) / 1000,
                       window_length:
                           ev.windowLength == '' || !ev.windowLengthEnabled ?
                               0 :
                               ev.windowLength,
                       points_decay_factor: ev.pointsDecayFactor,
                       submissions_gap: ev.submissionsGap,
                       feedback: ev.feedback,
                       penalty: ev.penalty,
                       scoreboard: ev.scoreboard,
                       penalty_type: ev.penaltyType,
                       show_scoreboard_after: ev.showScoreboardAfter,
                       basic_information: ev.needsBasicInformation ? 1 : 0,
                       requests_user_information: ev.requestsUserInformation
                     })
              .then(UI.contestUpdated)
              .fail(UI.apiError);
        },
        refresh: (ev, api, param, key) => {
          key = key || param;
          console.log(key);
          api({contest_alias: contestAlias})
              .then((response) => {
                ev[param] = response[key] || response;
                ev.$parent[param] = response[key] || response;
              })
              .fail(UI.apiError);
        },
        addProblem: (ev) => {
          API.Contest.addProblem({
                       contest_alias: contestAlias,
                       order_in_contest: ev.order,
                       problem_alias: ev.alias,
                       points: ev.points,
                     })
              .then(function(response) {
                if (response.status != 'ok') {
                  UI.error(response.error || 'error');
                }
                UI.success(T.problemSuccessfullyAdded);
                trigger.refresh(ev, API.Contest.problems, 'problems');
              })
              .fail(UI.apiError);
        },
        removeProblem: (ev) => {
          API.Contest.removeProblem({
                       contest_alias: contestAlias,
                       problem_alias: ev.selected.alias
                     })
              .then(function(response) {
                if (response.status != 'ok') {
                  UI.error(response.error || 'error');
                }
                UI.success(T.problemSuccessfullyRemoved);
                trigger.refresh(ev, API.Contest.problems, 'problems');
              })
              .fail(UI.apiError);
        },
        updateAdmissionMode: (ev) => {
          API.Contest.update({
                       contest_alias: contestAlias,
                       admission_mode: ev.admissionMode
                     })
              .then(function(response) {
                UI.contestUpdated(response);
                trigger.refresh(ev, API.Contest.adminDetails, 'contest');
              })
              .fail(UI.apiError);
        },
        addUser: (ev) => {
          var contestants = [];
          if (ev.contestants !== '') contestants = ev.contestants.split(',');
          if (ev.contestant !== '') contestants.push(ev.contestant);
          var promises = contestants.map(function(contestant) {
            return API.Contest.addUser({
              contest_alias: contestAlias,
              usernameOrEmail: contestant.trim()
            });
          });
          $.when.apply($, promises)
              .then(function() {
                UI.success(T.bulkUserAddSuccess);
                trigger.refresh(ev, API.Contest.users, 'users');
              })
              .fail(function() { UI.error(T.bulkUserAddError); });
        },
        removeUser: (ev) => {
          API.Contest.removeUser({
                       contest_alias: contestAlias,
                       usernameOrEmail: ev.selected.username
                     })
              .then(function(response) {
                if (response.status != 'ok') {
                  UI.error(response.error || 'error');
                }
                UI.success(T.userRemoveSuccess);
                trigger.refresh(ev, API.Contest.users, 'users');
              })
              .fail(UI.apiError);
        },
        cloneContest: (ev) => {
          API.Contest.clone({
                       contest_alias: contestAlias,
                       title: ev.title,
                       alias: ev.alias,
                       description: ev.description,
                       start_time: ev.startTime.getTime() / 1000
                     })
              .then(function(response) {
                UI.success(T.contestEditContestClonedSuccessfully);
              })
              .fail(UI.apiError);
        },
        addAdmin: (ev) => {
          API.Contest
              .addAdmin({contest_alias: contestAlias, usernameOrEmail: ev.user})
              .then(function(response) {
                UI.success(T.adminAdded);
                trigger.refresh(ev, API.Contest.admins, 'admins');
              })
              .fail(UI.apiError);
        },
        removeAdmin: (ev) => {
          API.Contest.removeAdmin({
                       contest_alias: contestAlias,
                       usernameOrEmail: ev.selected.username
                     })
              .then(function(response) {
                if (response.status != 'ok') {
                  UI.error(response.error || 'error');
                }
                UI.success(T.adminRemoved);
                trigger.refresh(ev, API.Contest.admins, 'admins')
              })
              .fail(UI.apiError);
        },
        addGroupAdmin: (ev) => {
          API.Contest.addGroupAdmin(
                         {contest_alias: contestAlias, group: ev.groupName})
              .then(function(response) {
                UI.success(T.groupAdminAdded);
                trigger.refresh(ev, API.Contest.admins, 'groupAdmins',
                                'group_admins');
              })
              .fail(UI.apiError);
        },
        removeGroupAdmin: (ev) => {
          API.Contest.removeGroupAdminFromContest({
                       contest_alias: contestAlias,
                       group: ev.selected.alias
                     })
              .then(function(response) {
                UI.success(T.groupAdminRemoved);
                trigger.refresh(ev, API.Contest.admins, 'groupAdmins',
                                'group_admins');
              })
              .fail(UI.apiError);
        }
      }

      $.when(API.Contest.adminDetails({contest_alias: contestAlias}),
             API.Contest.problems({contest_alias: contestAlias}),
             API.Contest.users({contest_alias: contestAlias}),
             API.Contest.admins({contest_alias: contestAlias}), )
          .done((contest, problems, users, admins) => {
            problems = problems.problems;
            users = users.users;
            var groupAdmins = admins.group_admins;
            admins = admins.admins;
            let contest_edit = new Vue({
              el: '#contest-edit',
              render: function(createElement) {
                return createElement('contest-edit', {
                  props: {
                    data: {
                      contest: contest,
                      problems: problems,
                      users: users,
                      admins: admins,
                      groupAdmins: groupAdmins
                    }
                  },
                  on: {
                    'update-contest': trigger.updateContest,
                    'add-problem': trigger.addProblem,
                    'remove-problem': trigger.removeProblem,
                    'update-admission-mode': trigger.updateAdmissionMode,
                    'add-user': trigger.addUser,
                    'remove-user': trigger.removeUser,
                    'clone-contest': trigger.cloneContest,
                    'add-admin': trigger.addAdmin,
                    'remove-admin': trigger.removeAdmin,
                    'add-group-admin': trigger.addGroupAdmin,
                    'remove-group-admin': trigger.removeGroupAdmin,
                  }
                });
              },
              components: {'contest-edit': ContestEdit},
            });
          })
          .fail(UI.apiError);
});
