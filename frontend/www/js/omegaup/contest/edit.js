import {OmegaUp, UI, API, T} from '../omegaup.js';
import Vue from 'vue';
import ContestEdit from '../components/contest/ContestEdit.vue';


OmegaUp.on('ready', function() {
  var contestAlias =
      /\/contest\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

  function updateContest(ev) {
     API.Contest.update({
     	  contest_alias: contestAlias,
     	  title: ev.title,
     	  description: ev.description,
     	  start_time:
     		  (ev.startTime.getTime()) /
     			  1000,
     	  finish_time:
     		  (ev.finishTime.getTime()) /
     			  1000,
     	  window_length: ev.windowLength == "" || !ev.windowLengthEnabled? 0 : ev.windowLength,
     	  points_decay_factor: ev.pointsDecayFactor,
     	  submissions_gap: ev.submissionsGap,
     	  feedback: ev.feedback,
     	  penalty: ev.penalty,
     	  scoreboard: ev.scoreboard,
     	  penalty_type: ev.penaltyType,
     	  show_scoreboard_after:
     		  ev.showScoreboardAfter,
     	  basic_information:
     		  ev.needsBasicInformation ? 1 : 0,
     	  requests_user_information:
     		  ev.requestsUserInformation
	})
	.then(UI.contestUpdated)
	.fail(UI.apiError);
  }
  $.when(
	  API.Contest.adminDetails({contest_alias: contestAlias}),
      API.Contest.problems({contest_alias: contestAlias}),
	  API.Contest.users({contest_alias: contestAlias}),
	  API.Contest.admins({contest_alias: contestAlias}),
  ).done((contest, problems, users, admins) => {
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
			  'update-contest': updateContest,
			  'add-problem': function(ev) {
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
					  API.Contest.problems({contest_alias: contestAlias})
						  .then((response) => {
							  ev.problems = response.problems;
							  ev.$parent.problems = response.problems;
						  })
						  .fail(UI.apiError);
					})
					.fail(UI.apiError);
			  },
			  'remove-problem': function(problem) {
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
			  'update-admission-mode': function(ev) {
				API.Contest.update({
							 contest_alias: contestAlias,
							 admission_mode: ev.admissionMode
						   })
					.then(UI.contestUpdated)
					.fail(UI.apiError);
			  },
			  'add-user': function(ev) {
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
			  'clone-contest': function(ev) {
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
			  'add-admin': function(ev) {
				API.Contest.addAdmin({
							 contest_alias: contestAlias,
							 usernameOrEmail: ev.user
						   })
					.then(function(response) {
					  UI.success(T.adminAdded);
					})
					.fail(UI.apiError);
			  },
			  'add-group-admin': function(ev) {
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
	  }).fail(UI.apiError);
});
