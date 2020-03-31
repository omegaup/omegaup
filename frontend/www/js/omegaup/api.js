import * as types from './types.ts';
import { OmegaUp } from './omegaup';
import * as api from './api_transitional';

function _convertRuntimes(data) {
  if (data.runs) {
    for (var i = 0; i < data.runs.length; i++) {
      data.runs[i].time = OmegaUp.remoteTime(data.runs[i].time * 1000);
    }
  }
  return data;
}

function _normalizeContestFields(contest) {
  OmegaUp.convertTimes(contest);
  contest.submissions_gap = parseInt(contest.submissions_gap);
  contest.show_penalty = contest.penalty != 0 || contest.penalty_type != 'none';
  return contest;
}

export default {
  Badge: {
    badgeDetails: api.apiCall('/api/badge/badgeDetails/', function(result) {
      result.first_assignation = result.first_assignation
        ? new Date(result.first_assignation * 1000)
        : null;
      return result;
    }),

    list: api.Badge.list,

    myBadgeAssignationTime: api.apiCall(
      '/api/badge/myBadgeAssignationTime/',
      function(result) {
        result.assignation_time = result.assignation_time
          ? new Date(result.assignation_time * 1000)
          : null;
        return result;
      },
    ),

    myList: api.apiCall('/api/badge/myList/', function(result) {
      result.badges.forEach(badge => {
        badge.assignation_time = new Date(badge.assignation_time * 1000);
      });
      return result;
    }),

    userList: api.apiCall('/api/badge/userList/', function(result) {
      result.badges.forEach(badge => {
        badge.assignation_time = new Date(badge.assignation_time * 1000);
      });
      return result;
    }),
  },

  Clarification: api.Clarification,

  Contest: {
    activityReport: api.apiCall('/api/contest/activityReport/', function(
      result,
    ) {
      for (let ev of result.events) {
        ev.time = OmegaUp.remoteTime(ev.time * 1000);
      }
      return result;
    }),

    addAdmin: api.Contest.addAdmin,

    addGroup: api.Contest.addGroup,

    addGroupAdmin: api.Contest.addGroupAdmin,

    addProblem: api.Contest.addProblem,

    addUser: api.Contest.addUser,

    adminDetails: api.apiCall('/api/contest/adminDetails/', function(contest) {
      // We cannot use |_normalizeContestFields| because admins need to be
      // able to get the unmodified times.
      contest.start_time = new Date(contest.start_time * 1000);
      contest.finish_time = new Date(contest.finish_time * 1000);
      contest.submission_deadline = OmegaUp.remoteTime(
        contest.submission_deadline * 1000,
      );
      contest.show_penalty =
        contest.penalty != 0 || contest.penalty_type != 'none';
      return contest;
    }),

    adminList: api.apiCall('/api/contest/adminList/', function(result) {
      for (var idx in result.contests) {
        var contest = result.contests[idx];
        OmegaUp.convertTimes(contest);
      }
      return result;
    }),

    admins: api.Contest.admins,

    arbitrateRequest: api.Contest.arbitrateRequest,

    clarifications: api.apiCall('/api/contest/clarifications/', function(data) {
      for (var idx in data.clarifications) {
        var clarification = data.clarifications[idx];
        clarification.time = OmegaUp.remoteTime(clarification.time * 1000);
      }
      return data;
    }),

    contestants: api.Contest.contestants,

    create: api.Contest.create,

    createVirtual: api.Contest.createVirtual,

    clone: api.Contest.clone,

    details: api.apiCall('/api/contest/details/', _normalizeContestFields),

    list: api.apiCall('/api/contest/list/', function(result) {
      for (var idx in result.results) {
        var contest = result.results[idx];
        OmegaUp.convertTimes(contest);
      }
      return result;
    }),

    myList: api.apiCall('/api/contest/myList/', function(result) {
      for (var idx in result.contests) {
        var contest = result.contests[idx];
        OmegaUp.convertTimes(contest);
      }
      return result;
    }),

    open: api.Contest.open,

    problems: api.Contest.problems,

    publicDetails: api.apiCall(
      '/api/contest/publicDetails/',
      _normalizeContestFields,
    ),

    registerForContest: api.Contest.registerForContest,

    removeAdmin: api.Contest.removeAdmin,

    removeGroup: api.Contest.removeGroup,

    removeGroupAdmin: api.Contest.removeGroupAdmin,

    removeProblem: api.Contest.removeProblem,

    removeUser: api.Contest.removeUser,

    requests: api.Contest.requests,

    runs: api.apiCall('/api/contest/runs/', _convertRuntimes),

    runsDiff: api.Contest.runsDiff,

    scoreboard: api.Contest.scoreboard,

    scoreboardMerge: api.Contest.scoreboardMerge,

    stats: api.Contest.stats,

    update: api.Contest.update,

    updateEndTimeForIdentity: api.Contest.updateEndTimeForIdentity,

    users: api.apiCall('/api/contest/users/', function(result) {
      for (const user of result.users) {
        if (user.access_time !== null) {
          user.access_time = OmegaUp.remoteTime(user.access_time * 1000);
        }
        if (user.end_time !== null) {
          user.end_time = OmegaUp.remoteTime(user.end_time * 1000);
        }
      }
      return result;
    }),
  },

  Course: {
    activityReport: api.apiCall('/api/course/activityReport/', function(
      result,
    ) {
      for (let ev of result.events) {
        ev.time = OmegaUp.remoteTime(ev.time * 1000);
      }
      return result;
    }),

    addAdmin: api.Course.addAdmin,

    addGroupAdmin: api.Course.addGroupAdmin,

    addProblem: api.Course.addProblem,

    addStudent: api.Course.addStudent,

    adminDetails: api.apiCall('/api/course/adminDetails/', function(result) {
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

    admins: api.Course.admins,

    assignmentScoreboard: api.Course.assignmentScoreboard,

    clone: api.Course.clone,

    create: api.Course.create,

    details: api.apiCall('/api/course/details/', function(data) {
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

    myProgress: api.Course.myProgress,

    createAssignment: api.Course.createAssignment,

    getAssignment: api.apiCall('/api/course/assignmentDetails', function(data) {
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
    getProblemUsers: api.Course.getProblemUsers,

    listAssignments: api.apiCall('/api/course/listAssignments/', function(
      result,
    ) {
      // We cannot use OmegaUp.remoteTime() because admins need to
      // be able to get the unmodified times.
      result.assignments.forEach(assignment => {
        assignment.start_time = new Date(assignment.start_time * 1000);
        if (assignment.finish_time) {
          assignment.finish_time = new Date(assignment.finish_time * 1000);
        }
      });
      return result;
    }),

    listCourses: api.apiCall('/api/course/listCourses/', function(result) {
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
      result.public.forEach(res => {
        res.start_time = new Date(res.start_time * 1000);
        if (res.finish_time) {
          res.finish_time = new Date(res.finish_time * 1000);
        }
      });
      return result;
    }),

    listStudents: api.Course.listStudents,

    listSolvedProblems: api.Course.listSolvedProblems,

    listUnsolvedProblems: api.Course.listUnsolvedProblems,

    removeAdmin: api.Course.removeAdmin,

    removeAssignment: api.Course.removeAssignment,

    removeGroupAdmin: api.Course.removeGroupAdmin,

    removeProblem: api.Course.removeProblem,

    removeStudent: api.Course.removeStudent,

    runs: api.apiCall('/api/course/runs/', _convertRuntimes),

    studentProgress: api.apiCall('/api/course/studentProgress/', function(
      result,
    ) {
      for (var problem of result.problems) {
        for (var run of problem.runs) {
          run.time = OmegaUp.remoteTime(run.time * 1000);
        }
      }
      return result;
    }),

    update: api.Course.update,

    updateAssignment: api.Course.updateAssignment,

    updateProblemsOrder: api.Course.updateProblemsOrder,

    updateAssignmentsOrder: api.Course.updateAssignmentsOrder,
  },

  Grader: api.Grader,

  Group: api.Group,

  GroupScoreboard: api.GroupScoreboard,

  Identity: api.Identity,

  Interview: api.Interview,

  Notification: {
    myList: api.apiCall('/api/notification/myList/', function(result) {
      result.notifications.forEach(notification => {
        notification.timestamp = new Date(notification.timestamp * 1000);
        notification.contents = JSON.parse(notification.contents);
      });
      return result;
    }),

    readNotifications: api.Notification.readNotifications,
  },

  Problem: {
    addAdmin: api.Problem.addAdmin,

    addGroupAdmin: api.Problem.addGroupAdmin,

    addTag: api.Problem.addTag,

    adminList: api.Problem.adminList,

    admins: api.Problem.admins,

    clarifications: api.apiCall('/api/problem/clarifications/', function(data) {
      for (var idx in data.clarifications) {
        var clarification = data.clarifications[idx];
        clarification.time = OmegaUp.remoteTime(clarification.time * 1000);
      }
      return data;
    }),

    delete: api.Problem.delete,

    details: api.apiCall('/api/problem/details/', _convertRuntimes),

    list: api.Problem.list,

    myList: api.Problem.myList,

    rejudge: api.Problem.rejudge,

    removeAdmin: api.Problem.removeAdmin,

    removeGroupAdmin: api.Problem.removeGroupAdmin,

    removeTag: api.Problem.removeTag,

    runs: api.apiCall('/api/problem/runs/', _convertRuntimes),

    runsDiff: api.Problem.runsDiff,

    selectVersion: api.Problem.selectVersion,

    solution: api.Problem.solution,

    stats: api.Problem.stats,

    tags: api.Problem.tags,

    update: api.Problem.update,

    updateStatement: api.Problem.updateStatement,

    updateSolution: api.Problem.updateSolution,

    versions: api.Problem.versions,
  },

  ProblemForfeited: api.ProblemForfeited,

  Problemset: api.Problemset,

  QualityNomination: {
    create: api.QualityNomination.create,

    details: api.QualityNomination.details,

    list: api.apiCall('/api/qualityNomination/list/', function(data) {
      data.nominations.forEach(nomination => {
        nomination.time = OmegaUp.remoteTime(nomination.time * 1000);
      });
      return data;
    }),

    myList: api.apiCall('/api/qualityNomination/myList/', function(data) {
      data.nominations.forEach(nomination => {
        nomination.time = OmegaUp.remoteTime(nomination.time * 1000);
      });
      return data;
    }),

    resolve: api.QualityNomination.resolve,
  },

  Reset: api.Reset,

  Run: {
    counts: api.Run.counts,

    create: api.Run.create,

    details: api.Run.details,

    list: api.apiCall('/api/run/list/', _convertRuntimes),

    rejudge: api.Run.rejudge,

    disqualify: api.Run.disqualify,

    status: api.apiCall('/api/run/status/', function(data) {
      data.time = omegaup.OmegaUp.remoteTime(data.time * 1000);
      return data;
    }),
  },

  School: {
    create: api.School.create,

    list: api.School.list,

    monthlySolvedProblemsCount: api.apiCall(
      '/api/school/monthlySolvedProblemsCount',
    ),

    rank: api.School.rank,

    schoolsOfTheMonth: api.School.schoolsOfTheMonth,

    schoolCodersOfTheMonth: api.apiCall(
      '/api/school/schoolCodersOfTheMonth',
      function(data) {
        data.coders = data.coders.map(
          coderOfTheMonth => new types.SchoolCoderOfTheMonth(coderOfTheMonth),
        );
        return data;
      },
    ),

    selectSchoolOfTheMonth: api.School.selectSchoolOfTheMonth,

    users: api.apiCall('/api/school/users/', function(data) {
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

  Session: api.Session,

  Submission: {
    latestSubmissions: api.apiCall(
      '/api/submission/latestSubmissions/',
      function(data) {
        data.submissions.forEach(submission => {
          submission.time = new Date(submission.time * 1000);
        });
        return data;
      },
    ),
  },

  Time: api.Time,

  Tag: api.Tag,

  User: {
    acceptPrivacyPolicy: api.User.acceptPrivacyPolicy,

    addExperiment: api.User.addexperiment,

    addGroup: api.User.addgroup,

    associateIdentity: api.User.associateIdentity,

    addRole: api.User.addrole,

    changePassword: api.User.changepassword,

    contestStats: api.apiCall('/api/user/contestStats/', function(data) {
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

    coderOfTheMonthList: api.User.coderOfTheMonthList,

    /**
     * Creates a new user.
     * @param {string} email - The user's email address.
     * @param {string} username - The user's username.
     * @param {string} password - The user's password.
     * @param {string} recaptcha - The answer to the recaptcha challenge.
     * @return {Promise}
     */
    create: api.User.create,

    extraInformation: api.User.extraInformation,

    interviewStats: api.User.interviewstats,

    list: api.User.list,

    listAssociatedIdentities: api.User.listAssociatedIdentities,

    listUnsolvedProblems: api.apiCall(
      '/api/user/listUnsolvedProblems/',
      function(data) {
        if (data.hasOwnProperty('problems')) {
          data.problems = data.problems.map(
            problem => new types.Problem(problem),
          );
        }
        return data;
      },
    ),

    problemsSolved: api.apiCall('/api/user/problemsSolved/', function(data) {
      if (data.hasOwnProperty('problems')) {
        data.problems = data.problems.map(
          problem => new types.Problem(problem),
        );
      }
      return data;
    }),

    problemsCreated: api.apiCall('/api/user/problemsCreated', function(data) {
      if (data.hasOwnProperty('problems')) {
        data.problems = data.problems.map(
          problem => new types.Problem(problem),
        );
      }
      return data;
    }),

    profile: api.apiCall('/api/user/profile/', function(data) {
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

    rankByProblemsSolved: api.User.rankByProblemsSolved,

    removeExperiment: api.User.removeExperiment,

    removeGroup: api.User.removeGroup,

    removeRole: api.User.removeRole,

    stats: api.User.stats,

    selectCoderOfTheMonth: api.User.selectCoderOfTheMonth,

    update: api.User.update,

    updateBasicInfo: api.User.updateBasicInfo,

    updateMainEmail: api.User.updateMainEmail,

    verifyEmail: api.User.verifyEmail,
  },
};
