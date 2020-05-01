import * as types from './types.ts';
import { OmegaUp } from './omegaup';
import * as api from './api_transitional';

export default {
  Badge: api.Badge,

  Clarification: api.Clarification,

  Contest: api.Contest,

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
