import {OmegaUp, UI, API, T} from '../omegaup.js';
import Vue from 'vue';
import ContestEdit from '../components/contest/ContestEdit.vue';

OmegaUp.on('ready', function() {
  var contestAlias =
      /\/contest\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

  API.Contest.adminDetails({contest_alias: contestAlias})
      .then(function(contest) {
        API.Contest.problems({contest_alias: contestAlias})
            .then(function(responseProblems) {
              API.Contest.users({contest_alias: contestAlias})
                  .then(function(responseUsers) {
                    API.Contest.admins({contest_alias: contestAlias})
                        .then(function(responseAdmins) {
                          var problems = responseProblems.problems;
                          var users = responseUsers.users;
                          var admins = responseAdmins.admins;
                          var groupAdmins = responseAdmins.group_admins;
                          let contest_edit = new Vue({
                            el: '#contest-edit',
                            render: function(createElement) {
                              return createElement('contest-edit', {
                                props: {
                                  contest: contest,
                                  problems: problems,
                                  users: users,
                                  admins: admins,
                                  groupAdmins: groupAdmins
                                },
                                on: {
                                  updateContest: function(ev) {
                                    API.Contest
                                        .update({
                                          contest_alias: contestAlias,
                                          title: ev.title,
                                          description: ev.description,
                                          start_time:
                                              (ev.start_time.val().getTime()) /
                                                  1000,
                                          finish_time:
                                              (ev.finish_time.val().getTime()) /
                                                  1000,
                                          window_length: ev.windowLength,
                                          alias: ev.alias,
                                          points_decay_factor:
                                              ev.pointsDecayFactor,
                                          submissions_gap: ev.submissionsGap,
                                          feedback: ev.feedback,
                                          penalty: ev.penalty,
                                          public: ev.public,
                                          scoreboard: ev.scoreboard,
                                          penalty_type: ev.penaltyType,
                                          show_scoreboard_after:
                                              ev.showScoreboardAfter,
                                          contestant_must_register:
                                              ev.contestantMustRegister,
                                          basic_information:
                                              ev.needsBasicInformation,
                                          requests_user_information:
                                              ev.requestsUserInformation
                                        })
                                        .then(UI.contestUpdated)
                                        .fail(UI.apiError);
                                  },
                                  addProblem: function(ev) {
                                    API.Contest.addProblem({
                                                 contest_alias: contestAlias,
                                                 order_in_contest: ev.order,
                                                 problem_alias: ev.alias,
                                                 points: ev.point,
                                               })
                                        .then(function(response) {
                                          UI.success(
                                              T.problemSuccessfullyAdded);
                                        })
                                        .fail(UI.apiError);
                                  },
                                  removeProblem: function(problem) {
                                    API.Contest.removeProblem({
                                                 contest_alias: contestAlias,
                                                 problem_alias: problem
                                               })
                                        .then(function(response) {
                                          UI.success(
                                              T.problemSuccessfullyRemoved);
                                        })
                                        .fail(UI.apiError);
                                  },
                                  updatePublic: function(ev) {
                                    API.Contest.update({
                                                 contest_alias: contestAlias,
                                                 public: ev.public
                                               })
                                        .then(UI.contestUpdated)
                                        .fail(UI.apiError);
                                  },
                                  addUser: function(ev) {
                                    var contestants = [];
                                    if (ev.contestants !== '')
                                      contestants = ev.contestants.split(',');
                                    if (ev.contestant !== '')
                                      contestants.push(ev.contestant);
                                    var promises =
                                        contestants.map(function(contestant) {
                                          return API.Contest.addUser({
                                            contest_alias: contestAlias,
                                            usernameOrEmail: contestant.trim()
                                          });
                                        });
                                    $.when.apply($, promises)
                                        .then(function() {
                                          UI.success(T.bulkUserAddSuccess);
                                        })
                                        .fail(function() {
                                          UI.error(T.bulkUserAddError);
                                        });
                                  },
                                  cloneContest: function(ev) {
                                    API.Contest.clone({
                                                 contest_alias: contestAlias,
                                                 title: ev.title,
                                                 alias: ev.alias,
                                                 description: ev.description,
                                                 start_time:
                                                     ev.startTime.getTime() /
                                                         1000
                                               })
                                        .then(function(response) {
                                          UI.success(
                                              T.contestEditContestClonedSuccessfully);
                                        })
                                        .fail(UI.apiError);
                                  },
                                  addAdmin: function(ev) {
                                    API.Contest.addAdmin({
                                                 contest_alias: contestAlias,
                                                 usernameOrEmail: ev.user
                                               })
                                        .then(function(response) {
                                          UI.success(T.adminAdded);
                                        })
                                        .fail(UI.apiError);
                                  },
                                  addGroupAdmin: function(ev) {
                                    API.Contest.addGroupAdmin({
                                                 contest_alias: contestAlias,
                                                 group: ev.groupName
                                               })
                                        .then(function(response) {
                                          UI.success(T.groupAdminAdded);
                                        })
                                        .fail(UI.apiError);
                                  }
                                }
                              });
                            },
                            components: {'contest-edit': ContestEdit},
                          });
                        })
                        .fail(UI.apiError);
                  })
                  .fail(UI.apiError);
            })
            .fail(UI.apiError);
      })
      .fail(UI.apiError);
});
