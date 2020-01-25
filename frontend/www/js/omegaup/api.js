import UI from './ui.js';
import * as types from './types.ts';
import { OmegaUp } from './omegaup.js';

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
      dataType: 'json',
    })
      .done(function(data) {
        if (transform) {
          data = transform(data);
        }
        dfd.resolve(data);
      })
      .fail(function(jqXHR) {
        if (jqXHR.status == 499 || jqXHR.readyState != 4) {
          // If we cancel the connection, let's just swallow the error since
          // the user is not going to see it.
          return;
        }
        var errorData;
        try {
          if (jqXHR.responseText) {
            errorData = JSON.parse(jqXHR.responseText);
          } else {
            errorData = { status: 'error', error: null };
          }
        } catch (err) {
          errorData = { status: 'error', error: err };
        }
        omegaup.OmegaUp.addError(errorData);
        dfd.reject(errorData);
      });
    return dfd.promise();
  };
}

function _convertRuntimes(data) {
  if (data.runs) {
    for (var i = 0; i < data.runs.length; i++) {
      data.runs[i].time = omegaup.OmegaUp.remoteTime(data.runs[i].time * 1000);
    }
  }
  return data;
}

// This alias is needed because, without it, webpack cries when
// trying to export, e.g. Course.adminDetails, with the original
// name OmegaUp.convertTimes.
function _convertTimes(item) {
  return omegaup.OmegaUp.convertTimes(item);
}

function _normalizeContestFields(contest) {
  omegaup.OmegaUp.convertTimes(contest);
  contest.submissions_gap = parseInt(contest.submissions_gap);
  contest.show_penalty = contest.penalty != 0 || contest.penalty_type != 'none';
  return contest;
}

export default {
  Badge: {
    badgeDetails: _call('/api/badge/badgeDetails/', function(result) {
      result.first_assignation = result.first_assignation
        ? new Date(result.first_assignation * 1000)
        : null;
      return result;
    }),

    list: _call('/api/badge/list/'),

    myBadgeAssignationTime: _call(
      '/api/badge/myBadgeAssignationTime/',
      function(result) {
        result.assignation_time = result.assignation_time
          ? new Date(result.assignation_time * 1000)
          : null;
        return result;
      },
    ),

    myList: _call('/api/badge/myList/', function(result) {
      result.badges.forEach(badge => {
        badge.assignation_time = new Date(badge.assignation_time * 1000);
      });
      return result;
    }),

    userList: _call('/api/badge/userList/', function(result) {
      result.badges.forEach(badge => {
        badge.assignation_time = new Date(badge.assignation_time * 1000);
      });
      return result;
    }),
  },

  Clarification: {
    create: _call('/api/clarification/create/'),

    update: _call('/api/clarification/update/'),
  },

  Contest: {
    activityReport: _call('/api/contest/activityReport/', function(result) {
      for (let ev of result.events) {
        ev.time = omegaup.OmegaUp.remoteTime(ev.time * 1000);
      }
      return result;
    }),

    addAdmin: _call('/api/contest/addAdmin/'),

    addGroupAdmin: _call('/api/contest/addGroupAdmin/'),

    addProblem: _call('/api/contest/addProblem/'),

    addUser: _call('/api/contest/addUser/'),

    adminDetails: _call('/api/contest/admindetails/', function(contest) {
      // We cannot use |_normalizeContestFields| because admins need to be
      // able to get the unmodified times.
      contest.start_time = new Date(contest.start_time * 1000);
      contest.finish_time = new Date(contest.finish_time * 1000);
      contest.submission_deadline = omegaup.OmegaUp.remoteTime(
        contest.submission_deadline * 1000,
      );
      contest.show_penalty =
        contest.penalty != 0 || contest.penalty_type != 'none';
      return contest;
    }),

    adminList: _call('/api/contest/adminlist/', function(result) {
      for (var idx in result.contests) {
        var contest = result.contests[idx];
        omegaup.OmegaUp.convertTimes(contest);
      }
      return result;
    }),

    admins: _call('/api/contest/admins/'),

    arbitrateRequest: _call('/api/contest/arbitraterequest/'),

    clarifications: _call('/api/contest/clarifications/', function(data) {
      for (var idx in data.clarifications) {
        var clarification = data.clarifications[idx];
        clarification.time = omegaup.OmegaUp.remoteTime(
          clarification.time * 1000,
        );
      }
      return data;
    }),

    contestants: _call('/api/contest/contestants/'),

    create: _call('/api/contest/create/'),

    createVirtual: _call('/api/contest/createvirtual'),

    clone: _call('/api/contest/clone/'),

    details: _call('/api/contest/details/', _normalizeContestFields),

    list: _call('/api/contest/list/', function(result) {
      for (var idx in result.results) {
        var contest = result.results[idx];
        omegaup.OmegaUp.convertTimes(contest);
      }
      return result;
    }),

    myList: _call('/api/contest/mylist/', function(result) {
      for (var idx in result.contests) {
        var contest = result.contests[idx];
        omegaup.OmegaUp.convertTimes(contest);
      }
      return result;
    }),

    open: _call('/api/contest/open/'),

    problems: _call('/api/contest/problems/'),

    publicDetails: _call(
      '/api/contest/publicdetails/',
      _normalizeContestFields,
    ),

    registerForContest: _call('/api/contest/registerforcontest/'),

    removeAdmin: _call('/api/contest/removeAdmin/'),

    removeGroupAdminFromContest: _call('/api/contest/removeGroupAdmin/'),

    removeProblem: _call('/api/contest/removeProblem/'),

    removeUser: _call('/api/contest/removeUser/'),

    requests: _call('/api/contest/requests/'),

    runs: _call('/api/contest/runs/', _convertRuntimes),

    runsDiff: _call('/api/contest/runsDiff/'),

    scoreboard: _call('/api/contest/scoreboard/'),

    scoreboardMerge: _call('/api/contest/scoreboardmerge/'),

    stats: _call('/api/contest/stats/'),

    update: _call('/api/contest/update/'),

    updateEndTimeForIdentity: _call('/api/contest/updateEndTimeForIdentity/'),

    users: _call('/api/contest/users/', function(result) {
      for (const user of result.users) {
        if (user.access_time !== null) {
          user.access_time = omegaup.OmegaUp.remoteTime(
            user.access_time * 1000,
          );
        }
        if (user.end_time !== null) {
          user.end_time = omegaup.OmegaUp.remoteTime(user.end_time * 1000);
        }
      }
      return result;
    }),
  },

  Course: {
    activityReport: _call('/api/course/activityReport/', function(result) {
      for (let ev of result.events) {
        ev.time = omegaup.OmegaUp.remoteTime(ev.time * 1000);
      }
      return result;
    }),

    addAdmin: _call('/api/course/addAdmin/'),

    addGroupAdmin: _call('/api/course/addGroupAdmin/'),

    addProblem: _call('/api/course/addProblem/'),

    addStudent: _call('/api/course/addStudent/'),

    adminDetails: _call('/api/course/adminDetails/', function(result) {
      if (result.finish_time) {
        result.finish_time = new Date(result.finish_time * 1000);
      }
      result.start_time = new Date(result.start_time * 1000);
      result.assignments.forEach(assignment => {
        assignment.start_time = new Date(assignment.start_time * 1000);
        if (assignment.finish_time) {
          assignment.finish_time = new Date(assignment.finish_time * 1000);
        }
      });
      return result;
    }),

    admins: _call('/api/course/admins/'),

    assignmentScoreboard: _call('/api/course/assignmentScoreboard/'),

    clone: _call('/api/course/clone/'),

    create: _call('/api/course/create/'),

    details: _call('/api/course/details/', function(data) {
      if (data.finish_time) {
        data.finish_time = new Date(data.finish_time * 1000);
      }
      data.start_time = new Date(data.start_time * 1000);
      data.assignments.forEach(assignment => {
        assignment.start_time = new Date(assignment.start_time * 1000);
        if (assignment.finish_time) {
          assignment.finish_time = new Date(assignment.finish_time * 1000);
        }
      });
      return data;
    }),

    myProgress: _call('/api/course/myProgress/'),

    createAssignment: _call('/api/course/createAssignment/'),

    getAssignment: _call('/api/course/assignmentDetails', function(data) {
      data.start_time = new Date(data.start_time * 1000);
      if (data.finish_time) {
        data.finish_time = new Date(data.finish_time * 1000);
      }
      return data;
    }),

    /**
     * Returns the list of users signed up for the course that have
     * attempted the requested problem.
     *
     * @param {string} course_alias
     * @param {string} problem_alias
     * @return {Promise}
     */
    apiGetProblemUsers: _call('/api/course/getProblemUsers'),

    listAssignments: _call('/api/course/listAssignments/', function(result) {
      // We cannot use omegaup.OmegaUp.remoteTime() because admins need to
      // be able to get the unmodified times.
      result.assignments.forEach(assignment => {
        assignment.start_time = new Date(assignment.start_time * 1000);
        if (assignment.finish_time) {
          assignment.finish_time = new Date(assignment.finish_time * 1000);
        }
      });
      return result;
    }),

    listCourses: _call('/api/course/listCourses/', function(result) {
      result.admin.forEach(res => {
        res.start_time = new Date(res.start_time * 1000);
        if (res.finish_time) {
          res.finish_time = new Date(res.finish_time * 1000);
        }
      });
      result.student.forEach(res => {
        res.start_time = new Date(res.start_time * 1000);
        if (res.finish_time) {
          res.finish_time = new Date(res.finish_time * 1000);
        }
      });
      return result;
    }),

    listStudents: _call('/api/course/listStudents/'),

    listSolvedProblems: _call('/api/course/listSolvedProblems/'),

    listUnsolvedProblems: _call('/api/course/listUnsolvedProblems/'),

    removeAdmin: _call('/api/course/removeAdmin/'),

    removeAssignment: _call('/api/course/removeAssignment/'),

    removeGroupAdmin: _call('/api/course/removeGroupAdmin/'),

    removeProblem: _call('/api/course/removeProblem/'),

    removeStudent: _call('/api/course/removeStudent/'),

    runs: _call('/api/course/runs/', _convertRuntimes),

    studentProgress: _call('/api/course/studentProgress/', function(result) {
      for (var problem of result.problems) {
        for (var run of problem.runs) {
          run.time = omegaup.OmegaUp.remoteTime(run.time * 1000);
        }
      }
      return result;
    }),

    update: _call('/api/course/update/'),

    updateAssignment: _call('/api/course/updateAssignment/'),

    updateProblemsOrder: _call('/api/course/updateProblemsOrder/'),

    updateAssignmentsOrder: _call('/api/course/updateAssignmentsOrder/'),
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

    list: _call('/api/group/list/'),

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

  Identity: {
    changePassword: _call('/api/identity/changePassword/'),

    create: _call('/api/identity/create/'),

    bulkCreate: _call('/api/identity/bulkCreate/'),

    update: _call('/api/identity/update/'),
  },

  Interview: {
    addUsers: _call('/api/interview/addUsers/'),

    create: _call('/api/interview/create/'),

    details: _call('/api/interview/details/'),

    list: _call('/api/interview/list/'),
  },

  Notification: {
    myList: _call('/api/notification/myList/', function(result) {
      result.notifications.forEach(notification => {
        notification.timestamp = new Date(notification.timestamp * 1000);
        notification.contents = JSON.parse(notification.contents);
      });
      return result;
    }),

    readNotifications: _call('/api/notification/readNotifications/'),
  },

  Problem: {
    addAdmin: _call('/api/problem/addAdmin/'),

    addGroupAdmin: _call('/api/problem/addGroupAdmin/'),

    addTag: _call('/api/problem/addTag/'),

    adminList: _call('/api/problem/adminlist/'),

    admins: _call('/api/problem/admins/'),

    clarifications: _call('/api/problem/clarifications/', function(data) {
      for (var idx in data.clarifications) {
        var clarification = data.clarifications[idx];
        clarification.time = omegaup.OmegaUp.remoteTime(
          clarification.time * 1000,
        );
      }
      return data;
    }),

    delete: _call('/api/problem/delete/'),

    details: _call('/api/problem/details/', _convertRuntimes, {
      statement_type: 'markdown',
    }),

    list: _call('/api/problem/list/'),

    myList: _call('/api/problem/mylist/'),

    rejudge: _call('/api/problem/rejudge/'),

    removeAdmin: _call('/api/problem/removeAdmin/'),

    removeGroupAdmin: _call('/api/problem/removeGroupAdmin/'),

    removeTag: _call('/api/problem/removeTag/'),

    runs: _call('/api/problem/runs/', _convertRuntimes),

    runsDiff: _call('/api/problem/runsDiff/'),

    selectVersion: _call('/api/problem/selectVersion/'),

    solution: _call('/api/problem/solution/'),

    stats: _call('/api/problem/stats/'),

    tags: _call('/api/problem/tags/'),

    update: _call('/api/problem/update/'),

    updateStatement: _call('/api/problem/updateStatement/'),

    updateSolution: _call('/api/problem/updateSolution/'),

    versions: _call('/api/problem/versions/'),
  },

  ProblemForfeited: {
    getCounts: _call('/api/problemForfeited/getCounts/'),
  },

  Problemset: {
    scoreboard: _call('/api/problemset/scoreboard/'),

    scoreboardEvents: _call('/api/problemset/scoreboardevents/'),
  },

  QualityNomination: {
    create: _call('/api/qualityNomination/create/'),

    details: _call('/api/qualityNomination/details/'),

    list: _call('/api/qualityNomination/list/', function(data) {
      data.nominations.forEach(nomination => {
        nomination.time = OmegaUp.remoteTime(nomination.time * 1000);
      });
      return data;
    }),

    myList: _call('/api/qualityNomination/mylist/', function(data) {
      data.nominations.forEach(nomination => {
        nomination.time = OmegaUp.remoteTime(nomination.time * 1000);
      });
      return data;
    }),

    resolve: _call('/api/qualityNomination/resolve/'),
  },

  Reset: {
    create: _call('/api/reset/create/'),

    generateToken: _call('/api/reset/generateToken/'),

    update: _call('/api/reset/update/'),
  },

  Run: {
    counts: _call('/api/run/counts/'),

    create: _call('/api/run/create/'),

    details: _call('/api/run/details/'),

    list: _call('/api/run/list/', _convertRuntimes),

    rejudge: _call('/api/run/rejudge/'),

    disqualify: _call('/api/run/disqualify'),

    status: _call('/api/run/status/', function(data) {
      data.time = omegaup.OmegaUp.remoteTime(data.time * 1000);
      return data;
    }),
  },

  School: {
    create: _call('/api/school/create/'),

    list: _call('/api/school/list/'),

    monthlySolvedProblemsCount: _call('/api/school/monthlysolvedproblemscount'),

    rank: _call('/api/school/rank/'),

    schoolsOfTheMonth: _call('/api/school/schoolsofthemonth'),

    schoolCodersOfTheMonth: _call(
      '/api/school/schoolcodersofthemonth',
      function(data) {
        data.coders = data.coders.map(
          coderOfTheMonth => new types.SchoolCoderOfTheMonth(coderOfTheMonth),
        );
        return data;
      },
    ),

    selectSchoolOfTheMonth: _call('/api/school/selectschoolofthemonth/'),

    users: _call('/api/school/users/', function(data) {
      data.users = data.users.map(
        user =>
          new types.SchoolUser(
            user.classname,
            user.username,
            user.created_problems,
            user.solved_problems,
            user.organized_contests,
          ),
      );
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

  Submission: {
    latestSubmissions: _call('/api/submission/latestsubmissions/', function(
      data,
    ) {
      data.submissions.forEach(submission => {
        submission.time = new Date(submission.time * 1000);
      });
      return data;
    }),
  },

  Time: {
    /**
     * Gets the current time according to the server.
     * @return {Promise}
     */
    get: _call('/api/time/get/'),
  },

  Tag: {
    list: _call('/api/tag/list/'),
  },

  User: {
    acceptPrivacyPolicy: _call('/api/user/acceptPrivacyPolicy'),

    addExperiment: _call('/api/user/addexperiment/'),

    addGroup: _call('/api/user/addgroup/'),

    associateIdentity: _call('/api/user/associateIdentity/'),

    addRole: _call('/api/user/addrole/'),

    changePassword: _call('/api/user/changepassword/'),

    contestStats: _call('/api/user/conteststats/', function(data) {
      let contests = [];
      for (let contestAlias in data.contests) {
        const now = new Date();
        const currentTimestamp =
          data.contests[contestAlias].data.finish_time * 1000;
        const end = OmegaUp.remoteTime(currentTimestamp);
        if (data.contests[contestAlias].place !== null && now > end) {
          contests.push(new types.ContestResult(data.contests[contestAlias]));
        }
      }
      return contests;
    }),

    coderOfTheMonthList: _call('/api/user/coderofthemonthlist'),

    /**
     * Creates a new user.
     * @param {string} email - The user's email address.
     * @param {string} username - The user's username.
     * @param {string} password - The user's password.
     * @param {string} recaptcha - The answer to the recaptcha challenge.
     * @return {Promise}
     */
    create: _call('/api/user/create/'),

    extraInformation: _call('/api/user/extraInformation/'),

    interviewStats: _call('/api/user/interviewstats/'),

    list: _call('/api/user/list/'),

    listAssociatedIdentities: _call('/api/user/listAssociatedIdentities/'),

    listUnsolvedProblems: _call('/api/user/listUnsolvedProblems/', function(
      data,
    ) {
      if (data.hasOwnProperty('problems')) {
        data.problems = data.problems.map(
          problem => new types.Problem(problem),
        );
      }
      return data;
    }),

    problemsSolved: _call('/api/user/problemssolved/', function(data) {
      if (data.hasOwnProperty('problems')) {
        data.problems = data.problems.map(
          problem => new types.Problem(problem),
        );
      }
      return data;
    }),

    problemsCreated: _call('/api/user/problemscreated', function(data) {
      if (data.hasOwnProperty('problems')) {
        data.problems = data.problems.map(
          problem => new types.Problem(problem),
        );
      }
      return data;
    }),

    profile: _call('/api/user/profile/', function(data) {
      if (data.birth_date !== null) {
        data.birth_date = omegaup.OmegaUp.remoteTime(data.birth_date * 1000);
      }
      if (data.graduation_date !== null) {
        data.graduation_date = omegaup.OmegaUp.remoteTime(
          data.graduation_date * 1000,
        );
      }
      return data;
    }),

    rankByProblemsSolved: _call('/api/user/rankByProblemsSolved/'),

    removeExperiment: _call('/api/user/removeexperiment/'),

    removeGroup: _call('/api/user/removegroup/'),

    removeRole: _call('/api/user/removerole/'),

    stats: _call('/api/user/stats/'),

    selectCoderOfTheMonth: _call('/api/user/selectCoderOfTheMonth/'),

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
};
