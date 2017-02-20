var omegaup = typeof global === 'undefined' ?
                  (window.omegaup = window.omegaup || {}) :
                  (global.omegaup = global.omegaup || {});

omegaup.internal =
    typeof global === 'undefined' ?
        (window.omegaup.internal = window.omegaup.internal || {}) :
        (global.omegaup.internal = global.omegaup.internal || {});

omegaup.internal.API = {
  _call: function(url, transform, defaults) {
    return function(params) {
      var dfd = $.Deferred();
      if (defaults) {
        params = $.extend({}, defaults, params);
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
  },

  _convertRuntimes: function(data) {
    if (data.runs) {
      for (var i = 0; i < data.runs.length; i++) {
        data.runs[i].time = omegaup.OmegaUp.time(data.runs[i].time * 1000);
      }
    }
    return data;
  },

  _convertTimes: function(item) {
    if (item.hasOwnProperty('start_time')) {
      item.start_time = omegaup.OmegaUp.time(item.start_time * 1000);
    }
    if (item.hasOwnProperty('finish_time')) {
      item.finish_time = omegaup.OmegaUp.time(item.finish_time * 1000);
    }
    return item;
  },

  _normalizeContestFields: function(contest) {
    omegaup.internal.API._convertTimes(contest);
    contest.submission_deadline =
        omegaup.OmegaUp.time(contest.submission_deadline * 1000);
    contest.show_penalty =
        (contest.penalty != 0 || contest.penalty_type != 'none');
    return contest;
  },
};

omegaup.API = {
  Contest: {
    activityReport: omegaup.internal.API._call(
        '/api/contest/activityReport/',
        function(result) {
          for (var idx in result.events) {
            if (!result.events.hasOwnProperty(idx)) continue;
            var ev = result.events[idx];
            ev.time = omegaup.OmegaUp.time(ev.time * 1000);
          }
          return result;
        }),

    addAdmin: omegaup.internal.API._call('/api/contest/addAdmin/'),

    addGroupAdmin: omegaup.internal.API._call('/api/contest/addGroupAdmin/'),

    addProblem: omegaup.internal.API._call('/api/contest/addProblem/'),

    addUser: omegaup.internal.API._call('/api/contest/addUser/'),

    adminDetails: omegaup.internal.API._call(
        '/api/contest/admindetails/',
        function(contest) {
          // We cannot use |_normalizeContestFields| because admins need to be
          // able to get the unmodified times.
          contest.start_time = new Date(contest.start_time * 1000);
          contest.finish_time = new Date(contest.finish_time * 1000);
          contest.submission_deadline =
              omegaup.OmegaUp.time(contest.submission_deadline * 1000);
          contest.show_penalty =
              (contest.penalty != 0 || contest.penalty_type != 'none');
          return contest;
        }),

    adminList: omegaup.internal.API._call(
        '/api/contest/adminlist/',
        function(result) {
          for (var idx in result.contests) {
            var contest = result.contests[idx];
            omegaup.internal.API._convertTimes(contest);
          }
          return result;
        }),

    admins: omegaup.internal.API._call('/api/contest/admins/'),

    create: omegaup.internal.API._call('/api/contest/create/'),

    details: omegaup.internal.API._call(
        '/api/contest/details/', omegaup.internal.API._normalizeContestFields),

    list: omegaup.internal.API._call('/api/contest/list/',
                                     function(result) {
                                       for (var idx in result.results) {
                                         var contest = result.results[idx];
                                         omegaup.internal.API._convertTimes(
                                             contest);
                                       }
                                       return result;
                                     }),

    myList: omegaup.internal.API._call('/api/contest/mylist/',
                                       function(result) {
                                         for (var idx in result.contests) {
                                           var contest = result.contests[idx];
                                           omegaup.internal.API._convertTimes(
                                               contest);
                                         }
                                         return result;
                                       }),

    open: omegaup.internal.API._call('/api/contest/open/'),

    problems: omegaup.internal.API._call('/api/contest/problems/'),

    removeAdmin: omegaup.internal.API._call('/api/contest/removeAdmin/'),

    removeGroupAdminFromContest:
        omegaup.internal.API._call('/api/contest/removeGroupAdmin/'),

    removeProblem: omegaup.internal.API._call('/api/contest/removeProblem/'),

    removeUser: omegaup.internal.API._call('/api/contest/removeUser/'),

    runs: omegaup.internal.API._call('/api/contest/runs/',
                                     omegaup.internal.API._convertRuntimes),

    stats: omegaup.internal.API._call('/api/contest/stats/'),

    update: omegaup.internal.API._call('/api/contest/update/'),

    users: omegaup.internal.API._call('/api/contest/users/'),
  },

  Course: {
    addProblem: omegaup.internal.API._call('/api/course/addProblem/'),

    addStudent: omegaup.internal.API._call('/api/course/addStudent/'),

    adminDetails: omegaup.internal.API._call(
        '/api/course/adminDetails/', omegaup.internal.API._convertTimes),

    create: omegaup.internal.API._call('/api/course/create/'),

    details: omegaup.internal.API._call('/api/course/details/',
                                        omegaup.internal.API._convertTimes),

    createAssignment:
        omegaup.internal.API._call('/api/course/createAssignment/'),

    getAssignment: omegaup.internal.API._call(
        '/api/course/assignmentDetails', omegaup.internal.API._convertTimes),

    listAssignments: omegaup.internal.API._call('/api/course/listAssignments/'),

    listCourses: omegaup.internal.API
                     ._call('/api/course/listCourses/',
                            function(result) {
                              for (var i = 0; i < result.admin.length; ++i) {
                                result.admin[i].finish_time =
                                    omegaup.OmegaUp.time(
                                        result.admin[i].finish_time * 1000);
                              }
                              for (var i = 0; i < result.student.length; ++i) {
                                result.student[i].finish_time =
                                    omegaup.OmegaUp.time(
                                        result.student[i].finish_time * 1000);
                              }
                              return result;
                            }),

    listStudents: omegaup.internal.API._call('/api/course/listStudents/'),

    removeStudent: omegaup.internal.API._call('/api/course/removeStudent/'),

    update: omegaup.internal.API._call('/api/course/update/'),
  },

  Group: {
    /**
     * Adds a user to the group.
     * @param {string} group_alias - The alias of the group
     * @param {string} usernameOrEmail - The user's identification.
     * @return {Promise}
     */
    addUser: omegaup.internal.API._call('/api/group/addUser/'),

    /**
     * Creates a new group.
     * @param {string} alias - The group's alias.
     * @param {string} name - The group's name.
     * @param {string} description - The group's description.
     * @return {Promise}
     */
    create: omegaup.internal.API._call('/api/group/create/'),

    /**
     * Adds a scoreboard to the group.
     * @param {string} group_alias - The alias of the group.
     * @param {string} alias - The alias of the scoreboard.
     * @param {string} title - The title of the scoreboard.
     * @param {string} description - The description of the scoreboard.
     * @return {Promise}
     */
    createScoreboard:
        omegaup.internal.API._call('/api/group/createScoreboard/'),

    /**
     * Gets the group's details
     * @param {string} group_alias - The alias of the group.
     * @return {Promise}
     */
    details: omegaup.internal.API._call('/api/group/details/'),

    /**
     * Gets the groups owned by the user.
     * @return {Promise}
     */
    myList: omegaup.internal.API._call('/api/group/mylist/'),

    /**
     * Removes a user from the group.
     * @param {string} group_alias - The alias of the group
     * @param {string} usernameOrEmail - The user's identification.
     * @return {Promise}
     */
    removeUser: omegaup.internal.API._call('/api/group/removeUser/'),

    /**
     * Gets the list of members of a group.
     * @param {string} group_alias - The alias of the group
     * @return {Promise}
     */
    members: omegaup.internal.API._call('/api/group/members/'),
  },

  GroupScoreboard: {
    addContest: omegaup.internal.API._call('/api/groupScoreboard/addContest/'),

    details: omegaup.internal.API._call('/api/groupScoreboard/details/'),

    removeContest:
        omegaup.internal.API._call('/api/groupScoreboard/removeContest/'),
  },

  Problem: {
    addAdmin: omegaup.internal.API._call('/api/problem/addAdmin/'),

    addGroupAdmin: omegaup.internal.API._call('/api/problem/addGroupAdmin/'),

    addTag: omegaup.internal.API._call('/api/problem/addTag/'),

    adminList: omegaup.internal.API._call('/api/problem/adminlist/'),

    admins: omegaup.internal.API._call('/api/problem/admins/'),

    details: omegaup.internal.API._call('/api/problem/details/',
                                        omegaup.internal.API._convertRuntimes,
                                        {statement_type: 'html'}),

    list: omegaup.internal.API._call('/api/problem/list/'),

    myList: omegaup.internal.API._call('/api/problem/mylist/'),

    removeAdmin: omegaup.internal.API._call('/api/problem/removeAdmin/'),

    removeGroupAdmin:
        omegaup.internal.API._call('/api/problem/removeGroupAdmin/'),

    removeTag: omegaup.internal.API._call('/api/problem/removeTag/'),

    runs: omegaup.internal.API._call('/api/problem/runs/',
                                     omegaup.internal.API._convertRuntimes),

    stats: omegaup.internal.API._call('/api/problem/stats/'),

    tags: omegaup.internal.API._call('/api/problem/tags/'),
  },

  Run: {
    counts: omegaup.internal.API._call('/api/run/counts/'),

    create: omegaup.internal.API._call('/api/run/create/'),

    details: omegaup.internal.API._call('/api/run/details/'),
  },

  Session: {
    /**
     * Gets the current session.
     * @return {Promise}
     */
    currentSession: omegaup.internal.API._call('/api/session/currentsession/'),

    /**
     * Performs a login using Google OAuth.
     * @param {string} storeToken - The auth code.
     */
    googleLogin: omegaup.internal.API._call('/api/session/googlelogin/'),
  },

  Time: {
    /**
     * Gets the current time according to the server.
     * @return {Promise}
     */
    get: omegaup.internal.API._call('/api/time/get/'),
  },

  User: {
    /**
     * Creates a new user.
     * @param {string} email - The user's email address.
     * @param {string} username - The user's username.
     * @param {string} password - The user's password.
     * @param {string} recaptcha - The answer to the recaptcha challenge.
     * @return {Promise}
     */
    create: omegaup.internal.API._call('/api/user/create/'),

    /**
     * Updates the user's basic information.
     * @param {string} username - The user's new username.
     * @param {string} name - The user's new username.
     * @param {string} password - The use's new password.
     * @return {Promise}
     */
    updateBasicInfo: omegaup.internal.API._call('/api/user/updatebasicinfo/'),

    /**
     * Updates the user's mail email address.
     * @param {string} email - The user's new main email.
     * @return {Promise}
     */
    updateMainEmail: omegaup.internal.API._call('/api/user/updateMainEmail/'),
  },

  getUserStats: function(username, callback) {
    $.get(username == null ?
              '/api/user/stats/' :
              '/api/user/stats/username/' + encodeURIComponent(username),
          function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  getContest: function(alias, callback) {
    $.get('/api/contest/details/contest_alias/' + encodeURIComponent(alias) +
              '/',
          function(contest) {
            if (contest.status == 'ok') {
              omegaup.internal.API._normalizeContestFields(contest);
            }
            callback(contest);
          },
          'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  getContestPublicDetails: function(alias, callback) {
    $.get('/api/contest/publicdetails/contest_alias/' +
              encodeURIComponent(alias) + '/',
          function(contest) {
            if (contest.status == 'ok') {
              omegaup.internal.API._normalizeContestFields(contest);
            }
            callback(contest);
          },
          'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  getProfile: function(username, callback) {
    $.get(username == null ? '/api/user/profile/' :
                             '/api/user/profile/username/' +
                                 encodeURIComponent(username) + '/',
          function(data) {
            if (data.status == 'ok') {
              data.userinfo.birth_date =
                  omegaup.OmegaUp.time(data.userinfo.birth_date * 1000);
              data.userinfo.graduation_date =
                  omegaup.OmegaUp.time(data.userinfo.graduation_date * 1000);
            }

            callback(data);
          },
          'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  getCoderOfTheMonth: function(callback) {
    $.get('/api/user/coderofthemonth/',
          function(data) {
            if (data.status == 'ok') {
              data.userinfo.birth_date =
                  omegaup.OmegaUp.time(data.userinfo.birth_date * 1000);
              data.userinfo.graduation_date =
                  omegaup.OmegaUp.time(data.userinfo.graduation_date * 1000);
            }

            callback(data);
          },
          'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  arbitrateContestUserRequest: function(contest_alias, username, resolution,
                                        notes, callback) {
    $.post('/api/contest/arbitraterequest/',
           {
             contest_alias: contest_alias,
             username: username,
             resolution: resolution,
             note: notes
           },
           function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  registerForContest: function(contest_alias, callback) {
    $.post('/api/contest/registerforcontest/',
           {
             contest_alias: contest_alias,
           },
           function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  updateProblem: function(alias, public, callback) {
    $.post('/api/problem/update/',
           {
             problem_alias: alias,
             public: public,
             message: public ? 'private -> public' : 'public -> private'
           },
           function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  updateProfile: function(name, birth_date, country_id, state_id,
                          scholar_degree, graduation_date, school_id,
                          school_name, locale, recruitment_optin, callback) {
    $.post('/api/user/update/',
           {
             name: name,
             birth_date: birth_date,
             country_id: country_id,
             state_id: state_id,
             scholar_degree: scholar_degree,
             graduation_date: graduation_date,
             school_id: school_id,
             school_name: school_name,
             locale: locale,
             recruitment_optin: recruitment_optin
           },
           function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
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

  searchUsers: function(query, callback) {
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

  getProblemClarifications: function(problemAlias, offset, rowcount, callback) {
    $.get('/api/problem/clarifications/problem_alias/' +
              encodeURIComponent(problemAlias) + '/offset/' + offset +
              '/rowcount/' + rowcount + '/',
          function(data) {
            for (var idx in data.clarifications) {
              var clarification = data.clarifications[idx];
              clarification.time =
                  omegaup.OmegaUp.time(clarification.time * 1000);
            }
            callback(data);
          },
          'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  // TODO(pablo): The caller for this is providing page and row count.
  //              Is it used?
  getRankByProblemsSolved: function(rowcount, callback) {
    $.get('/api/user/rankbyproblemssolved/rowcount/' +
              encodeURIComponent(rowcount) + '/',
          function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  getContestStatsForUser: function(username, callback) {
    $.get(username == null ? '/api/user/conteststats/' :
                             '/api/user/conteststats/username/' +
                                 encodeURIComponent(username) + '/',
          function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  getProblemsSolved: function(username, callback) {
    $.get(username == null ? '/api/user/problemssolved/' :
                             '/api/user/problemssolved/username/' +
                                 encodeURIComponent(username) + '/',
          function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  getRuns: function(options, callback) {
    $.post('/api/run/list/', options,
           function(data) {
             omegaup.internal.API._convertRuntimes(data);
             callback(data);
           },
           'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  runStatus: function(guid, callback) {
    $.get('/api/run/status/run_alias/' + encodeURIComponent(guid) + '/',
          function(data) {
            data.time = omegaup.OmegaUp.time(data.time * 1000);
            callback(data);
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

  runRejudge: function(guid, debug, callback) {
    $.get('/api/run/rejudge/run_alias/' + encodeURIComponent(guid) + '/' +
              (debug ? 'debug/true/' : ''),
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

  rejudgeProblem: function(problemAlias, callback) {
    $.get('/api/problem/rejudge/problem_alias/' +
              encodeURIComponent(problemAlias) + '/',
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

  getRanking: function(contestAlias, callback) {
    $.get('/api/contest/scoreboard/contest_alias/' +
              encodeURIComponent(contestAlias) + '/',
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

  getRankingByToken: function(contestAlias, token, callback) {
    $.get('/api/contest/scoreboard/contest_alias/' +
              encodeURIComponent(contestAlias) + '/token/' +
              encodeURIComponent(token) + '/',
          function(data) {
            omegaup.internal.API._convertTimes(data);
            callback(data);
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

  getRankingEventsByToken: function(contestAlias, token, callback) {
    $.get('/api/contest/scoreboardevents/contest_alias/' +
              encodeURIComponent(contestAlias) + '/token/' +
              encodeURIComponent(token) + '/',
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

  getRankByProblemsSolved: function(offset, rowcount, callback) {
    $.get('/api/user/RankByProblemsSolved/offset/' +
              encodeURIComponent(offset) + '/rowcount/' +
              encodeURIComponent(rowcount) + '/',
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

  getRankingEvents: function(contestAlias, callback) {
    $.get('/api/contest/scoreboardevents/contest_alias/' +
              encodeURIComponent(contestAlias) + '/',
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

  getScoreboardMerge: function(contestAliases, callback) {
    $.get('/api/contest/scoreboardmerge/contest_aliases/' +
              contestAliases.map(encodeURIComponent).join(',') + '/',
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

  getGraderStats: function(callback) {
    $.get('/api/grader/status/', function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'ok', 'error': undefined});
          }
        });
  },

  getClarifications: function(contestAlias, offset, count, callback) {
    $.get('/api/contest/clarifications/contest_alias/' +
              encodeURIComponent(contestAlias) + '/offset/' +
              encodeURIComponent(offset) + '/rowcount/' +
              encodeURIComponent(count) + '/',
          function(data) {
            for (var idx in data.clarifications) {
              var clarification = data.clarifications[idx];
              clarification.time =
                  omegaup.OmegaUp.time(clarification.time * 1000);
            }
            callback(data);
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

  newClarification: function(contestAlias, problemAlias, message, callback) {
    $.post('/api/clarification/create/',
           {
             contest_alias: contestAlias,
             problem_alias: problemAlias,
             message: message
           },
           function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'ok', 'error': undefined});
          }
        });
  },

  updateClarification: function(clarificationId, answer, public, callback) {
    $.post('/api/clarification/update/',
           {
             clarification_id: clarificationId,
             answer: answer, public: public ? 1 : 0
           },
           function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  UserEdit: function(username, name, email, birthDate, school, password,
                     oldPassword, callback) {
    var toSend = {};

    if (username !== null) toSend.username = username;
    if (name !== null) toSend.name = name;
    if (email !== null) toSend.email = email;
    if (birthDate !== null) toSend.birthDate = birthDate;
    if (school !== null) toSend.school = school;
    if (password !== null) toSend.password = password;
    if (oldPassword !== null) toSend.oldPassword = oldPassword;

    $.post('/api/controllername/user/edit/', toSend,
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

  getInterviewStatsForUser: function(interviewAlias, username, callback) {
    $.get('/api/user/interviewstats/username/' + encodeURIComponent(username) +
              '/interview/' + encodeURIComponent(interviewAlias),
          function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
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
               omegaup.UI.error(data.error);
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

  forceVerifyEmail: function(username, callback) {
    $.post('/api/user/verifyemail/',
           {
             usernameOrEmail: username,
           },
           function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  forceChangePassword: function(username, newpassword, callback) {
    $.post('/api/user/changepassword/',
           {username: username, password: newpassword},
           function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  changePassword: function(oldPassword, newPassword, callback) {
    $.post('/api/user/changepassword/',
           {old_password: oldPassword, password: newPassword},
           function(data) { callback(data); }, 'json')
        .fail(function(j, status, errorThrown) {
          try {
            callback(JSON.parse(j.responseText));
          } catch (err) {
            callback({status: 'error', 'error': undefined});
          }
        });
  },

  resetCreate: function(email, callback) {
    omegaup.UI.dismissNotifications();
    $.post('/api/reset/create', {email: email},
           function(data) {
             omegaup.UI.success(data.message);
             callback();
           },
           'json')
        .fail(function(j, status, errorThrown) {
          omegaup.UI.error(JSON.parse(j.responseText).error);
          callback();
        });
  },

  resetUpdate: function(email, resetToken, password, passwordConfirmation,
                        callback) {
    omegaup.UI.dismissNotifications();
    $.post('/api/reset/update',
           {
             email: email,
             reset_token: resetToken,
             password: password,
             password_confirmation: passwordConfirmation
           },
           function(data) {
             omegaup.UI.success(data.message);
             callback();
           },
           'json')
        .fail(function(j, status, errorThrown) {
          omegaup.UI.error(JSON.parse(j.responseText).error);
          callback();
        });
  }
};
