var omegaup = typeof global === 'undefined' ?
                  (window.omegaup = window.omegaup || {}) :
                  (global.omegaup = global.omegaup || {});

omegaup.API = {
  _wrapDeferred: function(jqXHR, transform) {
    var dfd = $.Deferred();
    jqXHR.done(function(data) {
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
  },

  currentSession: function() {
    return omegaup.API._wrapDeferred(
        $.ajax({url: '/api/session/currentsession/', dataType: 'json'}));
  },

  time: function() {
    return omegaup.API._wrapDeferred(
        $.ajax({url: '/api/time/get/', dataType: 'json'}));
  },

  createUser: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/user/create/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  createGroup: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/group/create/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  createCourse: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/course/create/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  updateCourse: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/course/update/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  createCourseAssignment: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/course/createAssignment/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  addCourseAssignmentProblem: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/course/addProblem/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  getCourseList: function() {
    return omegaup.API._wrapDeferred(
        $.ajax({
          url: '/api/course/listCourses/',
          dataType: 'json',
        }),
        function(result) {
          for (var i = 0; i < result.admin.length; ++i) {
            result.admin[i].finish_time =
                omegaup.OmegaUp.time(result.admin[i].finish_time * 1000);
          }
          for (var i = 0; i < result.student.length; ++i) {
            result.student[i].finish_time =
                omegaup.OmegaUp.time(result.student[i].finish_time * 1000);
          }
          return result;
        });
  },

  getCourseStudentList: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/course/listStudents/',
      data: params,
      dataType: 'json',
    }));
  },

  getCourseAssignments: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/course/listAssignments/',
      method: 'POST',
      data: params,
      dataType: 'json',
    }));
  },

  getAssignment: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/course/getAssignment',
      method: 'POST',
      data: params,
      dataType: 'json',
    }),
                                     omegaup.API._convertTimes);
  },

  getCourseAdminDetails: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/course/adminDetails',
      method: 'POST',
      data: params,
      dataType: 'json',
    }),
                                     omegaup.API._convertTimes);
  },

  getCourseDetails: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/course/details',
      method: 'POST',
      data: params,
      dataType: 'json',
    }),
                                     omegaup.API._convertTimes);
  },

  addStudentToCourse: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/course/addStudent/',
      method: 'POST',
      data: params,
      dataType: 'json',
    }));
  },

  removeStudentFromCourse: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/course/removeStudent/',
      data: params,
      method: 'POST',
      dataType: 'json',
    }));
  },

  createContest: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/create/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  updateContest: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/update/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  googleLogin: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/session/googlelogin/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
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

  getMyGroups: function() {
    return omegaup.API._wrapDeferred(
        $.ajax({url: '/api/group/mylist/', dataType: 'json'}));
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

  getContests: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/list/',
      data: params,
      dataType: 'json',
    }),
                                     function(result) {
                                       for (var idx in result.results) {
                                         var contest = result.results[idx];
                                         omegaup.API._convertTimes(contest);
                                       }
                                       return result;
                                     });
  },

  openContest: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/open/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  _normalizeContestFields: function(contest) {
    omegaup.API._convertTimes(contest);
    contest.submission_deadline =
        omegaup.OmegaUp.time(contest.submission_deadline * 1000);
    contest.show_penalty =
        (contest.penalty != 0 || contest.penalty_type != 'none');
    return contest;
  },

  getContest: function(alias, callback) {
    $.get('/api/contest/details/contest_alias/' + encodeURIComponent(alias) +
              '/',
          function(contest) {
            if (contest.status == 'ok') {
              omegaup.API._normalizeContestFields(contest);
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

  getContestAdminDetails: function(params) {
    return omegaup.API._wrapDeferred(
        $.ajax({
          url: '/api/contest/admindetails/',
          method: 'POST',
          data: params,
          dataType: 'json',
        }),
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
        });
  },

  getContestPublicDetails: function(alias, callback) {
    $.get('/api/contest/publicdetails/contest_alias/' +
              encodeURIComponent(alias) + '/',
          function(contest) {
            if (contest.status == 'ok') {
              omegaup.API._normalizeContestFields(contest);
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

  getContestActivityReport: function(params) {
    return omegaup.API._wrapDeferred(
        $.ajax({
          url: '/api/contest/activityReport/',
          data: params,
          dataType: 'json',
        }),
        function(result) {
          for (var idx in result.events) {
            if (!result.events.hasOwnProperty(idx)) continue;
            var ev = result.events[idx];
            ev.time = omegaup.OmegaUp.time(ev.time * 1000);
          }
          return result;
        });
  },

  getContestByToken: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/details/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }),
                                     omegaup.API._normalizeContestFields);
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

  updateBasicProfile: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/user/updatebasicinfo/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  updateMainEmail: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/user/updateMainEmail/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  addProblemToContest: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/addProblem/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  removeProblemFromContest: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/removeProblem/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  contestProblems: function(params) {
    return omegaup.API._wrapDeferred($.ajax(
        {url: '/api/contest/problems/', data: params, dataType: 'json'}));
  },

  addAdminToContest: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/addAdmin/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  removeAdminFromContest: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/removeAdmin/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  addAdminToProblem: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/problem/addAdmin/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  removeAdminFromProblem: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/problem/removeAdmin/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  addGroupAdminToContest: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/addGroupAdmin/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  removeGroupAdminFromContest: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/removeGroupAdmin/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  addGroupAdminToProblem: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/problem/addGroupAdmin/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  removeGroupAdminFromProblem: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/problem/removeGroupAdmin/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  addTagToProblem: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/problem/addTag/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  removeTagFromProblem: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/problem/removeTag/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  addUserToGroup: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/group/addUser/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  removeUserFromGroup: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/group/removeUser/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  addScoreboardToGroup: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/group/createScoreboard/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  addContestToScoreboard: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/groupScoreboard/addContest/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  removeContestFromScoreboard: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/groupScoreboard/removeContest/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  addUserToContest: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/addUser/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  removeUserFromContest: function(contestAlias, username, callback) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/removeUser/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  getProblems: function() {
    return omegaup.API._wrapDeferred(
        $.ajax({url: '/api/problem/list/', dataType: 'json'}));
  },

  getProblemsWithTags: function(tags) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/problem/list/',
      data: {tag: tags},
      dataType: 'json',
    }));
  },

  searchProblems: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/problem/list/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
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

  getMyProblems: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/problem/mylist/',
      data: params,
      dataType: 'json',
    }));
  },

  getMyContests: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/mylist/',
      data: params,
      dataType: 'json',
    }),
                                     function(result) {
                                       for (var idx in result.contests) {
                                         var contest = result.contests[idx];
                                         omegaup.API._convertTimes(contest);
                                       }
                                       return result;
                                     });
  },

  getAdminProblems: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/problem/adminlist/',
      data: params,
      dataType: 'json',
    }));
  },

  getAdminContests: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/adminlist/',
      data: params,
      dataType: 'json',
    }),
                                     function(result) {
                                       for (var idx in result.contests) {
                                         var contest = result.contests[idx];
                                         omegaup.API._convertTimes(contest);
                                       }
                                       return result;
                                     });
  },

  _convertRuntimes: function(data) {
    if (data.runs) {
      for (var i = 0; i < data.runs.length; i++) {
        data.runs[i].time = omegaup.OmegaUp.time(data.runs[i].time * 1000);
      }
    }
    return data;
  },

  getProblem: function(contestAlias, problemAlias, callback, statement_type,
                       show_solvers, language) {
    if (statement_type === undefined) {
      statement_type = 'html';
    }
    var params = {statement_type: statement_type, show_solvers: !!show_solvers};
    if (language) {
      params.lang = language;
    }
    $.post(contestAlias === null ?
               '/api/problem/details/problem_alias/' +
                   encodeURIComponent(problemAlias) + '/' :
               '/api/problem/details/contest_alias/' +
                   encodeURIComponent(contestAlias) + '/problem_alias/' +
                   encodeURIComponent(problemAlias) + '/',
           params,
           function(problem) {
             omegaup.API._convertRuntimes(problem);
             callback(problem);
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

  getGroupMembers: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/group/members/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  getGroup: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/group/details/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  getGroupScoreboard: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/groupScoreboard/details/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }));
  },

  getProblemRuns: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/problem/runs/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }),
                                     omegaup.API._convertRuntimes);
  },

  getContestRuns: function(params) {
    return omegaup.API._wrapDeferred($.ajax({
      url: '/api/contest/runs/',
      method: 'POST',
      data: params,
      dataType: 'json'
    }),
                                     omegaup.API._convertRuntimes);
  },

  getContestStats: function(params) {
    return omegaup.API._wrapDeferred(
        $.ajax({url: '/api/contest/stats/', data: params, dataType: 'json'}));
  },

  getContestUsers: function(params) {
    return omegaup.API._wrapDeferred(
        $.ajax({url: '/api/contest/users/', data: params, dataType: 'json'}));
  },

  getContestAdmins: function(params) {
    return omegaup.API._wrapDeferred(
        $.ajax({url: '/api/contest/admins/', data: params, dataType: 'json'}));
  },

  getProblemAdmins: function(params) {
    return omegaup.API._wrapDeferred(
        $.ajax({url: '/api/problem/admins/', data: params, dataType: 'json'}));
  },

  getProblemTags: function(params) {
    return omegaup.API._wrapDeferred(
        $.ajax({url: '/api/problem/tags/', data: params, dataType: 'json'}));
  },

  getProblemStats: function(problemAlias, callback) {
    return omegaup.API._wrapDeferred(
        $.ajax({url: '/api/problem/stats/', data: params, dataType: 'json'}));
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
             omegaup.API._convertRuntimes(data);
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

  submit: function(contestAlias, problemAlias, language, code, callback) {
    $.post('/api/run/create/',
           {
             contest_alias: contestAlias,
             problem_alias: problemAlias,
             language: language,
             source: code
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

  getRunDetails: function(params) {
    return omegaup.API._wrapDeferred(
        $.ajax({url: '/api/run/details/', data: params, dataType: 'json'}));
  },

  getRunCounts: function(params) {
    return omegaup.API._wrapDeferred(
        $.ajax({url: '/api/run/counts/', data: params, dataType: 'json'}));
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
            omegaup.API._convertTimes(data);
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
