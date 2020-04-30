import * as types from './types.ts';
import { OmegaUp } from './omegaup';
import * as api from './api_transitional';

function _normalizeContestFields(contest) {
  OmegaUp.convertTimes(contest);
  contest.submissions_gap = parseInt(contest.submissions_gap);
  contest.show_penalty = contest.penalty != 0 || contest.penalty_type != 'none';
  return contest;
}

export default {
  Badge: api.Badge,

  Clarification: api.Clarification,

  Contest: {
    activityReport: api.Contest.activityReport,

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

    admins: api.Contest.admins,

    arbitrateRequest: api.Contest.arbitrateRequest,

    clone: api.Contest.clone,

    contestants: api.Contest.contestants,

    create: api.Contest.create,

    createVirtual: api.Contest.createVirtual,

    details: api.apiCall('/api/contest/details/', _normalizeContestFields),

    list: api.Contest.list,

    myList: api.Contest.myList,

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

    runsDiff: api.Contest.runsDiff,

    update: api.Contest.update,

    updateEndTimeForIdentity: api.Contest.updateEndTimeForIdentity,

    users: api.Contest.users,
  },

  Course: api.Course,

  Group: api.Group,

  GroupScoreboard: api.GroupScoreboard,

  Identity: api.Identity,

  Interview: api.Interview,

  Problem: api.Problem,

  Problemset: api.Problemset,

  QualityNomination: api.QualityNomination,

  Reset: api.Reset,

  Run: api.Run,

  School: {
    create: api.School.create,

    monthlySolvedProblemsCount: api.School.monthlySolvedProblemsCount,

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

  Submission: api.Submission,

  User: {
    acceptPrivacyPolicy: api.User.acceptPrivacyPolicy,

    addExperiment: api.User.addexperiment,

    addGroup: api.User.addgroup,

    addRole: api.User.addrole,

    associateIdentity: api.User.associateIdentity,

    changePassword: api.User.changePassword,

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

    create: api.User.create,

    extraInformation: api.User.extraInformation,

    interviewStats: api.User.interviewstats,

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

    problemsCreated: api.apiCall('/api/user/problemsCreated', function(data) {
      if (data.hasOwnProperty('problems')) {
        data.problems = data.problems.map(
          problem => new types.Problem(problem),
        );
      }
      return data;
    }),

    problemsSolved: api.apiCall('/api/user/problemsSolved/', function(data) {
      if (data.hasOwnProperty('problems')) {
        data.problems = data.problems.map(
          problem => new types.Problem(problem),
        );
      }
      return data;
    }),

    profile: api.User.profile,

    removeExperiment: api.User.removeExperiment,

    removeGroup: api.User.removeGroup,

    removeRole: api.User.removeRole,

    selectCoderOfTheMonth: api.User.selectCoderOfTheMonth,

    stats: api.User.stats,

    update: api.User.update,

    updateBasicInfo: api.User.updateBasicInfo,

    updateMainEmail: api.User.updateMainEmail,

    verifyEmail: api.User.verifyEmail,
  },
};
