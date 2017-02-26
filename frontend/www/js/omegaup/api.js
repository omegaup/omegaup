import UI from './ui.js';

function _call(url, transform, defaultParams) {
  return function(params) {
    var dfd = $.Deferred();
    if (defaultParams) {
      params = $.extend({}, defaultParams, params);
    }
    $.ajax({
       url: url,
       method: params ? 'POST' : 'GET',
       data: params,
       dataType: 'json'
     })
        .done(function(data) {
          if (transform) {
            data = transform(data);
          }
          dfd.resolve(data);
        })
        .fail(function(jqXHR) {
          var errorData;
          try {
            errorData = JSON.parse(jqXHR.responseText);
          } catch (err) {
            errorData = {status: 'error', error: err};
          }
          dfd.reject(errorData);
        });
    return dfd.promise();
  };
}

function _convertRuntimes(data) {
  if (data.runs) {
    for (var i = 0; i < data.runs.length; i++) {
      data.runs[i].time = omegaup.OmegaUp.time(data.runs[i].time * 1000);
    }
  }
  return data;
}

function _convertTimes(item) {
  if (item.hasOwnProperty('start_time')) {
    item.start_time = omegaup.OmegaUp.time(item.start_time * 1000);
  }
  if (item.hasOwnProperty('finish_time')) {
    item.finish_time = omegaup.OmegaUp.time(item.finish_time * 1000);
  }
  return item;
}

function _normalizeContestFields(contest) {
  _convertTimes(contest);
  contest.submission_deadline =
      omegaup.OmegaUp.time(contest.submission_deadline * 1000);
  contest.show_penalty =
      (contest.penalty != 0 || contest.penalty_type != 'none');
  return contest;
}

export default {
  Clarification: {
    create: _call('/api/clarification/create/'),

    update: _call('/api/clarification/update/'),
  },

  Contest: {
    activityReport: _call('/api/contest/activityReport/',
                          function(result) {
                            for (var idx in result.events) {
                              if (!result.events.hasOwnProperty(idx)) continue;
                              var ev = result.events[idx];
                              ev.time = omegaup.OmegaUp.time(ev.time * 1000);
                            }
                            return result;
                          }),

    addAdmin: _call('/api/contest/addAdmin/'),

    addGroupAdmin: _call('/api/contest/addGroupAdmin/'),

    addProblem: _call('/api/contest/addProblem/'),

    addUser: _call('/api/contest/addUser/'),

    adminDetails:
        _call('/api/contest/admindetails/',
              function(contest) {
                // We cannot use |_normalizeContestFields| because admins need
                // to be
                // able to get the unmodified times.
                contest.start_time = new Date(contest.start_time * 1000);
                contest.finish_time = new Date(contest.finish_time * 1000);
                contest.submission_deadline =
                    omegaup.OmegaUp.time(contest.submission_deadline * 1000);
                contest.show_penalty =
                    (contest.penalty != 0 || contest.penalty_type != 'none');
                return contest;
              }),

    adminList: _call('/api/contest/adminlist/',
                     function(result) {
                       for (var idx in result.contests) {
                         var contest = result.contests[idx];
                         _convertTimes(contest);
                       }
                       return result;
                     }),

    admins: _call('/api/contest/admins/'),

    arbitrateRequest: _call('/api/contest/arbitraterequest/'),

    clarifications: _call('/api/contest/clarifications/',
                          function(data) {
                            for (var idx in data.clarifications) {
                              var clarification = data.clarifications[idx];
                              clarification.time = omegaup.OmegaUp.time(
                                  clarification.time * 1000);
                            }
                            return data;
                          }),

    create: _call('/api/contest/create/'),

    details: _call('/api/contest/details/', _normalizeContestFields),

    list: _call('/api/contest/list/',
                function(result) {
                  for (var idx in result.results) {
                    var contest = result.results[idx];
                    _convertTimes(contest);
                  }
                  return result;
                }),

    myList: _call('/api/contest/mylist/',
                  function(result) {
                    for (var idx in result.contests) {
                      var contest = result.contests[idx];
                      _convertTimes(contest);
                    }
                    return result;
                  }),

    open: _call('/api/contest/open/'),

    problems: _call('/api/contest/problems/'),

    publicDetails:
        _call('/api/contest/publicdetails/', _normalizeContestFields),

    registerForContest: _call('/api/contest/registerforcontest/'),

    removeAdmin: _call('/api/contest/removeAdmin/'),

    removeGroupAdminFromContest: _call('/api/contest/removeGroupAdmin/'),

    removeProblem: _call('/api/contest/removeProblem/'),

    removeUser: _call('/api/contest/removeUser/'),

    runs: _call('/api/contest/runs/', _convertRuntimes),

    scoreboard: _call('/api/contest/scoreboard/'),

    scoreboardEvents: _call('/api/contest/scoreboardevents/'),

    scoreboardMerge: _call('/api/contest/scoreboardmerge/'),

    stats: _call('/api/contest/stats/'),

    update: _call('/api/contest/update/'),

    users: _call('/api/contest/users/'),
  },

  Course: {
    addProblem: _call('/api/course/addProblem/'),

    addStudent: _call('/api/course/addStudent/'),

    adminDetails: _call('/api/course/adminDetails/', _convertTimes),

    create: _call('/api/course/create/'),

    details: _call('/api/course/details/', _convertTimes),

    createAssignment: _call('/api/course/createAssignment/'),

    getAssignment: _call('/api/course/assignmentDetails', _convertTimes),

    listAssignments: _call('/api/course/listAssignments/'),

    listCourses: _call('/api/course/listCourses/',
                       function(result) {
                         for (var i = 0; i < result.admin.length; ++i) {
                           result.admin[i].finish_time = omegaup.OmegaUp.time(
                               result.admin[i].finish_time * 1000);
                         }
                         for (var i = 0; i < result.student.length; ++i) {
                           result.student[i].finish_time = omegaup.OmegaUp.time(
                               result.student[i].finish_time * 1000);
                         }
                         return result;
                       }),

    listStudents: _call('/api/course/listStudents/'),

    removeProblem: _call('/api/course/removeProblem/'),

    removeStudent: _call('/api/course/removeStudent/'),

    update: _call('/api/course/update/'),
  },

  Grader: {
    status: _call('/api/grader/status/'),
  },

  Group: {
    /**
     * Adds a user to the group.
     * @param {string} group_alias - The alias of the group
     * @param {string} usernameOrEmail - The user's identification.
     * @return {Promise}
     */
    addUser: _call('/api/group/addUser/'),

    /**
     * Creates a new group.
     * @param {string} alias - The group's alias.
     * @param {string} name - The group's name.
     * @param {string} description - The group's description.
     * @return {Promise}
     */
    create: _call('/api/group/create/'),

    /**
     * Adds a scoreboard to the group.
     * @param {string} group_alias - The alias of the group.
     * @param {string} alias - The alias of the scoreboard.
     * @param {string} title - The title of the scoreboard.
     * @param {string} description - The description of the scoreboard.
     * @return {Promise}
     */
    createScoreboard: _call('/api/group/createScoreboard/'),

    /**
     * Gets the group's details
     * @param {string} group_alias - The alias of the group.
     * @return {Promise}
     */
    details: _call('/api/group/details/'),

    /**
     * Gets the groups owned by the user.
     * @return {Promise}
     */
    myList: _call('/api/group/mylist/'),

    /**
     * Removes a user from the group.
     * @param {string} group_alias - The alias of the group
     * @param {string} usernameOrEmail - The user's identification.
     * @return {Promise}
     */
    removeUser: _call('/api/group/removeUser/'),

    /**
     * Gets the list of members of a group.
     * @param {string} group_alias - The alias of the group
     * @return {Promise}
     */
    members: _call('/api/group/members/'),
  },

  GroupScoreboard: {
    addContest: _call('/api/groupScoreboard/addContest/'),

    details: _call('/api/groupScoreboard/details/'),

    removeContest: _call('/api/groupScoreboard/removeContest/'),
  },

  Problem: {
    addAdmin: _call('/api/problem/addAdmin/'),

    addGroupAdmin: _call('/api/problem/addGroupAdmin/'),

    addTag: _call('/api/problem/addTag/'),

    adminList: _call('/api/problem/adminlist/'),

    admins: _call('/api/problem/admins/'),

    clarifications: _call('/api/problem/clarifications/',
                          function(data) {
                            for (var idx in data.clarifications) {
                              var clarification = data.clarifications[idx];
                              clarification.time = omegaup.OmegaUp.time(
                                  clarification.time * 1000);
                            }
                            return data;
                          }),

    details: _call('/api/problem/details/', _convertRuntimes,
                   {statement_type: 'html'}),

    list: _call('/api/problem/list/'),

    myList: _call('/api/problem/mylist/'),

    rejudge: _call('/api/problem/rejudge/'),

    removeAdmin: _call('/api/problem/removeAdmin/'),

    removeGroupAdmin: _call('/api/problem/removeGroupAdmin/'),

    removeTag: _call('/api/problem/removeTag/'),

    runs: _call('/api/problem/runs/', _convertRuntimes),

    stats: _call('/api/problem/stats/'),

    tags: _call('/api/problem/tags/'),

    update: _call('/api/problem/update/'),
  },

  Reset: {
    create: _call('/api/reset/create/'),

    update: _call('/api/reset/update/'),
  },

  Run: {
    counts: _call('/api/run/counts/'),

    create: _call('/api/run/create/'),

    details: _call('/api/run/details/'),

    list: _call('/api/run/list/', _convertRuntimes),

    rejudge: _call('/api/run/rejudge/'),

    status: _call('/api/run/status/',
                  function(data) {
                    data.time = omegaup.OmegaUp.time(data.time * 1000);
                    return data;
                  }),
  },

  Session: {
    /**
     * Gets the current session.
     * @return {Promise}
     */
    currentSession: _call('/api/session/currentsession/'),

    /**
     * Performs a login using Google OAuth.
     * @param {string} storeToken - The auth code.
     */
    googleLogin: _call('/api/session/googlelogin/'),
  },

  Time: {
    /**
     * Gets the current time according to the server.
     * @return {Promise}
     */
    get: _call('/api/time/get/'),
  },

  User: {
    changePassword: _call('/api/user/changepassword/'),

    contestStats: _call('/api/user/conteststats/'),

    /**
     * Creates a new user.
     * @param {string} email - The user's email address.
     * @param {string} username - The user's username.
     * @param {string} password - The user's password.
     * @param {string} recaptcha - The answer to the recaptcha challenge.
     * @return {Promise}
     */
    create: _call('/api/user/create/'),

    interviewStats: _call('/api/user/interviewstats/'),

    // TODO(lhchavez): Migrate once UI.typeahead only accepts promises.
    list: function(query, callback) {
      $.post('/api/user/list/', {query: query},
             function(data) { callback(data); }, 'json')
          .fail(function(j, status, errorThrown) {
            try {
              callback(JSON.parse(j.responseText));
            } catch (err) {
              callback({status: 'error', 'error': undefined});
            }
          });
    },

    problemsSolved: _call('/api/user/problemssolved/'),

    profile: _call('/api/user/profile/',
                   function(data) {
                     data.userinfo.birth_date =
                         omegaup.OmegaUp.time(data.userinfo.birth_date * 1000);
                     data.userinfo.graduation_date = omegaup.OmegaUp.time(
                         data.userinfo.graduation_date * 1000);
                     return data;
                   }),

    rankByProblemsSolved: _call('/api/user/rankByProblemsSolved/'),

    stats: _call('/api/user/stats/'),

    update: _call('/api/user/update/'),

    /**
     * Updates the user's basic information.
     * @param {string} username - The user's new username.
     * @param {string} name - The user's new username.
     * @param {string} password - The use's new password.
     * @return {Promise}
     */
    updateBasicInfo: _call('/api/user/updatebasicinfo/'),

    /**
     * Updates the user's mail email address.
     * @param {string} email - The user's new main email.
     * @return {Promise}
     */
    updateMainEmail: _call('/api/user/updateMainEmail/'),

    verifyEmail: _call('/api/user/verifyemail/'),
  },

  searchTags: function(query, callback) {
    $.post('/api/tag/list/', {query: query}, function(data) { callback(data); },
           'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  searchSchools: function(query, callback) {
    $.post('/api/school/list/', {query: query},
           function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  searchGroups: function(query, callback) {
    $.post('/api/group/list/', {query: query},
           function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  addUsersToInterview: function(interviewAlias, usernameOrEmailsCSV, callback) {
    $.post('/api/interview/addUsers/interview_alias/' +
               encodeURIComponent(interviewAlias) + '/',
           {usernameOrEmailsCSV: usernameOrEmailsCSV},
           function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  getInterview: function(alias, callback) {
    $.get('/api/interview/details/interview_alias/' +
              encodeURIComponent(alias) + '/',
          function(data) { callback(data); }, 'json')
        .fail(function(data) {
          if (callback !== undefined) {
            try {
              callback(JSON.parse(data.responseText));
            } catch (err) {
              callback({status: 'error', error: err});
            }
          }
        });
  },

  getInterviews: function(callback) {
    $.get('/api/interview/list/', function(data) { callback(data); }, 'json')
        .fail(function(data) {
          if (callback !== undefined) {
            try {
              callback(JSON.parse(data.responseText));
            } catch (err) {
              callback({status: 'error', error: err});
            }
          }
        });
  },

  createInterview: function(s_Alias, s_Title, s_Duration, callback) {
    $.post('/api/interview/create/',
           {alias: s_Alias, title: s_Title, duration: s_Duration},
           function(data) {
             if (data.status !== undefined && data.status == 'error') {
               UI.error(data.error);
             } else {
               if (callback !== undefined) {
                 callback(data);
               }
             }
           },
           'json')
        .fail(function(data) {
          if (callback !== undefined) {
            try {
              callback(JSON.parse(data.responseText));
            } catch (err) {
              callback({status: 'error', error: err});
            }
          }
        });
  },
}
