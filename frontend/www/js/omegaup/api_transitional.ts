// generated by frontend/server/cmd/APITool.php. DO NOT EDIT.
import { messages } from './api_types';
import { addError } from './errors';

export function apiCall<
  RequestType extends { [key: string]: any },
  ServerResponseType,
  ResponseType = ServerResponseType
>(
  url: string,
  transform?: (result: ServerResponseType) => ResponseType,
): (params?: RequestType) => Promise<ResponseType> {
  return (params?: RequestType) =>
    new Promise((accept, reject) => {
      let responseOk = true;
      fetch(
        url,
        params
          ? {
              method: 'POST',
              body: Object.keys(params)
                .filter(key => typeof params[key] !== 'undefined')
                .map(
                  key =>
                    `${encodeURIComponent(key)}=${encodeURIComponent(
                      params[key],
                    )}`,
                )
                .join('&'),
              headers: {
                'Content-Type':
                  'application/x-www-form-urlencoded;charset=UTF-8',
              },
            }
          : undefined,
      )
        .then(response => {
          if (response.status == 499) {
            // If we cancel the connection, let's just swallow the error since
            // the user is not going to see it.
            return;
          }
          responseOk = response.ok;
          return response.json();
        })
        .then(data => {
          if (!responseOk) {
            addError(data);
            console.error(data);
            reject(data);
            return;
          }
          if (transform) {
            accept(transform(data));
          } else {
            accept(data);
          }
        })
        .catch(err => {
          const errorData = { status: 'error', error: err };
          addError(errorData);
          console.error(errorData);
          reject(errorData);
        });
    });
}

export const Admin = {
  platformReportStats: apiCall<
    messages.AdminPlatformReportStatsRequest,
    messages.AdminPlatformReportStatsResponse
  >('/api/admin/platformReportStats/'),
};

export const Authorization = {
  problem: apiCall<
    messages.AuthorizationProblemRequest,
    messages.AuthorizationProblemResponse
  >('/api/authorization/problem/'),
};

export const Badge = {
  badgeDetails: apiCall<
    messages.BadgeBadgeDetailsRequest,
    messages._BadgeBadgeDetailsServerResponse,
    messages.BadgeBadgeDetailsResponse
  >('/api/badge/badgeDetails/', x => {
    x.assignation_time = ((x: number) => new Date(x * 1000))(
      x.assignation_time,
    );
    if (x.first_assignation)
      x.first_assignation = ((x: number) => new Date(x * 1000))(
        x.first_assignation,
      );
    return x;
  }),
  list: apiCall<messages.BadgeListRequest, messages.BadgeListResponse>(
    '/api/badge/list/',
  ),
  myBadgeAssignationTime: apiCall<
    messages.BadgeMyBadgeAssignationTimeRequest,
    messages._BadgeMyBadgeAssignationTimeServerResponse,
    messages.BadgeMyBadgeAssignationTimeResponse
  >('/api/badge/myBadgeAssignationTime/', x => {
    if (x.assignation_time)
      x.assignation_time = ((x: number) => new Date(x * 1000))(
        x.assignation_time,
      );
    return x;
  }),
  myList: apiCall<
    messages.BadgeMyListRequest,
    messages._BadgeMyListServerResponse,
    messages.BadgeMyListResponse
  >('/api/badge/myList/', x => {
    x.badges = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.assignation_time = ((x: number) => new Date(x * 1000))(
          x.assignation_time,
        );
        if (x.first_assignation)
          x.first_assignation = ((x: number) => new Date(x * 1000))(
            x.first_assignation,
          );
        return x;
      });
    })(x.badges);
    return x;
  }),
  userList: apiCall<
    messages.BadgeUserListRequest,
    messages._BadgeUserListServerResponse,
    messages.BadgeUserListResponse
  >('/api/badge/userList/', x => {
    x.badges = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.assignation_time = ((x: number) => new Date(x * 1000))(
          x.assignation_time,
        );
        if (x.first_assignation)
          x.first_assignation = ((x: number) => new Date(x * 1000))(
            x.first_assignation,
          );
        return x;
      });
    })(x.badges);
    return x;
  }),
};

export const Clarification = {
  create: apiCall<
    messages.ClarificationCreateRequest,
    messages.ClarificationCreateResponse
  >('/api/clarification/create/'),
  details: apiCall<
    messages.ClarificationDetailsRequest,
    messages.ClarificationDetailsResponse
  >('/api/clarification/details/'),
  update: apiCall<
    messages.ClarificationUpdateRequest,
    messages.ClarificationUpdateResponse
  >('/api/clarification/update/'),
};

export const Contest = {
  activityReport: apiCall<
    messages.ContestActivityReportRequest,
    messages.ContestActivityReportResponse
  >('/api/contest/activityReport/'),
  addAdmin: apiCall<
    messages.ContestAddAdminRequest,
    messages.ContestAddAdminResponse
  >('/api/contest/addAdmin/'),
  addGroup: apiCall<
    messages.ContestAddGroupRequest,
    messages.ContestAddGroupResponse
  >('/api/contest/addGroup/'),
  addGroupAdmin: apiCall<
    messages.ContestAddGroupAdminRequest,
    messages.ContestAddGroupAdminResponse
  >('/api/contest/addGroupAdmin/'),
  addProblem: apiCall<
    messages.ContestAddProblemRequest,
    messages.ContestAddProblemResponse
  >('/api/contest/addProblem/'),
  addUser: apiCall<
    messages.ContestAddUserRequest,
    messages.ContestAddUserResponse
  >('/api/contest/addUser/'),
  adminDetails: apiCall<
    messages.ContestAdminDetailsRequest,
    messages.ContestAdminDetailsResponse
  >('/api/contest/adminDetails/'),
  adminList: apiCall<
    messages.ContestAdminListRequest,
    messages._ContestAdminListServerResponse,
    messages.ContestAdminListResponse
  >('/api/contest/adminList/', x => {
    x.contests = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.finish_time = ((x: number) => new Date(x * 1000))(x.finish_time);
        x.start_time = ((x: number) => new Date(x * 1000))(x.start_time);
        return x;
      });
    })(x.contests);
    return x;
  }),
  admins: apiCall<
    messages.ContestAdminsRequest,
    messages.ContestAdminsResponse
  >('/api/contest/admins/'),
  arbitrateRequest: apiCall<
    messages.ContestArbitrateRequestRequest,
    messages.ContestArbitrateRequestResponse
  >('/api/contest/arbitrateRequest/'),
  clarifications: apiCall<
    messages.ContestClarificationsRequest,
    messages._ContestClarificationsServerResponse,
    messages.ContestClarificationsResponse
  >('/api/contest/clarifications/', x => {
    x.clarifications = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.time = ((x: number) => new Date(x * 1000))(x.time);
        return x;
      });
    })(x.clarifications);
    return x;
  }),
  clone: apiCall<messages.ContestCloneRequest, messages.ContestCloneResponse>(
    '/api/contest/clone/',
  ),
  contestants: apiCall<
    messages.ContestContestantsRequest,
    messages.ContestContestantsResponse
  >('/api/contest/contestants/'),
  create: apiCall<
    messages.ContestCreateRequest,
    messages.ContestCreateResponse
  >('/api/contest/create/'),
  createVirtual: apiCall<
    messages.ContestCreateVirtualRequest,
    messages.ContestCreateVirtualResponse
  >('/api/contest/createVirtual/'),
  details: apiCall<
    messages.ContestDetailsRequest,
    messages.ContestDetailsResponse
  >('/api/contest/details/'),
  list: apiCall<
    messages.ContestListRequest,
    messages._ContestListServerResponse,
    messages.ContestListResponse
  >('/api/contest/list/', x => {
    x.results = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.finish_time = ((x: number) => new Date(x * 1000))(x.finish_time);
        x.last_updated = ((x: number) => new Date(x * 1000))(x.last_updated);
        x.original_finish_time = ((x: number) => new Date(x * 1000))(
          x.original_finish_time,
        );
        x.start_time = ((x: number) => new Date(x * 1000))(x.start_time);
        return x;
      });
    })(x.results);
    return x;
  }),
  listParticipating: apiCall<
    messages.ContestListParticipatingRequest,
    messages._ContestListParticipatingServerResponse,
    messages.ContestListParticipatingResponse
  >('/api/contest/listParticipating/', x => {
    x.contests = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.finish_time = ((x: number) => new Date(x * 1000))(x.finish_time);
        x.last_updated = ((x: number) => new Date(x * 1000))(x.last_updated);
        x.original_finish_time = ((x: number) => new Date(x * 1000))(
          x.original_finish_time,
        );
        x.start_time = ((x: number) => new Date(x * 1000))(x.start_time);
        return x;
      });
    })(x.contests);
    return x;
  }),
  myList: apiCall<
    messages.ContestMyListRequest,
    messages._ContestMyListServerResponse,
    messages.ContestMyListResponse
  >('/api/contest/myList/', x => {
    x.contests = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.finish_time = ((x: number) => new Date(x * 1000))(x.finish_time);
        x.last_updated = ((x: number) => new Date(x * 1000))(x.last_updated);
        x.original_finish_time = ((x: number) => new Date(x * 1000))(
          x.original_finish_time,
        );
        x.start_time = ((x: number) => new Date(x * 1000))(x.start_time);
        return x;
      });
    })(x.contests);
    return x;
  }),
  open: apiCall<messages.ContestOpenRequest, messages.ContestOpenResponse>(
    '/api/contest/open/',
  ),
  problems: apiCall<
    messages.ContestProblemsRequest,
    messages.ContestProblemsResponse
  >('/api/contest/problems/'),
  publicDetails: apiCall<
    messages.ContestPublicDetailsRequest,
    messages.ContestPublicDetailsResponse
  >('/api/contest/publicDetails/'),
  registerForContest: apiCall<
    messages.ContestRegisterForContestRequest,
    messages.ContestRegisterForContestResponse
  >('/api/contest/registerForContest/'),
  removeAdmin: apiCall<
    messages.ContestRemoveAdminRequest,
    messages.ContestRemoveAdminResponse
  >('/api/contest/removeAdmin/'),
  removeGroup: apiCall<
    messages.ContestRemoveGroupRequest,
    messages.ContestRemoveGroupResponse
  >('/api/contest/removeGroup/'),
  removeGroupAdmin: apiCall<
    messages.ContestRemoveGroupAdminRequest,
    messages.ContestRemoveGroupAdminResponse
  >('/api/contest/removeGroupAdmin/'),
  removeProblem: apiCall<
    messages.ContestRemoveProblemRequest,
    messages.ContestRemoveProblemResponse
  >('/api/contest/removeProblem/'),
  removeUser: apiCall<
    messages.ContestRemoveUserRequest,
    messages.ContestRemoveUserResponse
  >('/api/contest/removeUser/'),
  report: apiCall<
    messages.ContestReportRequest,
    messages.ContestReportResponse
  >('/api/contest/report/'),
  requests: apiCall<
    messages.ContestRequestsRequest,
    messages._ContestRequestsServerResponse,
    messages.ContestRequestsResponse
  >('/api/contest/requests/', x => {
    x.users = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        if (x.last_update)
          x.last_update = ((x: number) => new Date(x * 1000))(x.last_update);
        x.request_time = ((x: number) => new Date(x * 1000))(x.request_time);
        return x;
      });
    })(x.users);
    return x;
  }),
  role: apiCall<messages.ContestRoleRequest, messages.ContestRoleResponse>(
    '/api/contest/role/',
  ),
  runs: apiCall<
    messages.ContestRunsRequest,
    messages._ContestRunsServerResponse,
    messages.ContestRunsResponse
  >('/api/contest/runs/', x => {
    x.runs = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.time = ((x: number) => new Date(x * 1000))(x.time);
        return x;
      });
    })(x.runs);
    return x;
  }),
  runsDiff: apiCall<
    messages.ContestRunsDiffRequest,
    messages.ContestRunsDiffResponse
  >('/api/contest/runsDiff/'),
  scoreboard: apiCall<
    messages.ContestScoreboardRequest,
    messages.ContestScoreboardResponse
  >('/api/contest/scoreboard/'),
  scoreboardEvents: apiCall<
    messages.ContestScoreboardEventsRequest,
    messages.ContestScoreboardEventsResponse
  >('/api/contest/scoreboardEvents/'),
  scoreboardMerge: apiCall<
    messages.ContestScoreboardMergeRequest,
    messages.ContestScoreboardMergeResponse
  >('/api/contest/scoreboardMerge/'),
  setRecommended: apiCall<
    messages.ContestSetRecommendedRequest,
    messages.ContestSetRecommendedResponse
  >('/api/contest/setRecommended/'),
  stats: apiCall<messages.ContestStatsRequest, messages.ContestStatsResponse>(
    '/api/contest/stats/',
  ),
  update: apiCall<
    messages.ContestUpdateRequest,
    messages.ContestUpdateResponse
  >('/api/contest/update/'),
  updateEndTimeForIdentity: apiCall<
    messages.ContestUpdateEndTimeForIdentityRequest,
    messages.ContestUpdateEndTimeForIdentityResponse
  >('/api/contest/updateEndTimeForIdentity/'),
  users: apiCall<messages.ContestUsersRequest, messages.ContestUsersResponse>(
    '/api/contest/users/',
  ),
};

export const Course = {
  activityReport: apiCall<
    messages.CourseActivityReportRequest,
    messages.CourseActivityReportResponse
  >('/api/course/activityReport/'),
  addAdmin: apiCall<
    messages.CourseAddAdminRequest,
    messages.CourseAddAdminResponse
  >('/api/course/addAdmin/'),
  addGroupAdmin: apiCall<
    messages.CourseAddGroupAdminRequest,
    messages.CourseAddGroupAdminResponse
  >('/api/course/addGroupAdmin/'),
  addProblem: apiCall<
    messages.CourseAddProblemRequest,
    messages.CourseAddProblemResponse
  >('/api/course/addProblem/'),
  addStudent: apiCall<
    messages.CourseAddStudentRequest,
    messages.CourseAddStudentResponse
  >('/api/course/addStudent/'),
  adminDetails: apiCall<
    messages.CourseAdminDetailsRequest,
    messages.CourseAdminDetailsResponse
  >('/api/course/adminDetails/'),
  admins: apiCall<messages.CourseAdminsRequest, messages.CourseAdminsResponse>(
    '/api/course/admins/',
  ),
  arbitrateRequest: apiCall<
    messages.CourseArbitrateRequestRequest,
    messages.CourseArbitrateRequestResponse
  >('/api/course/arbitrateRequest/'),
  assignmentDetails: apiCall<
    messages.CourseAssignmentDetailsRequest,
    messages.CourseAssignmentDetailsResponse
  >('/api/course/assignmentDetails/'),
  assignmentScoreboard: apiCall<
    messages.CourseAssignmentScoreboardRequest,
    messages.CourseAssignmentScoreboardResponse
  >('/api/course/assignmentScoreboard/'),
  assignmentScoreboardEvents: apiCall<
    messages.CourseAssignmentScoreboardEventsRequest,
    messages.CourseAssignmentScoreboardEventsResponse
  >('/api/course/assignmentScoreboardEvents/'),
  clone: apiCall<messages.CourseCloneRequest, messages.CourseCloneResponse>(
    '/api/course/clone/',
  ),
  create: apiCall<messages.CourseCreateRequest, messages.CourseCreateResponse>(
    '/api/course/create/',
  ),
  createAssignment: apiCall<
    messages.CourseCreateAssignmentRequest,
    messages.CourseCreateAssignmentResponse
  >('/api/course/createAssignment/'),
  details: apiCall<
    messages.CourseDetailsRequest,
    messages.CourseDetailsResponse
  >('/api/course/details/'),
  getProblemUsers: apiCall<
    messages.CourseGetProblemUsersRequest,
    messages.CourseGetProblemUsersResponse
  >('/api/course/getProblemUsers/'),
  introDetails: apiCall<
    messages.CourseIntroDetailsRequest,
    messages.CourseIntroDetailsResponse
  >('/api/course/introDetails/'),
  listAssignments: apiCall<
    messages.CourseListAssignmentsRequest,
    messages.CourseListAssignmentsResponse
  >('/api/course/listAssignments/'),
  listCourses: apiCall<
    messages.CourseListCoursesRequest,
    messages.CourseListCoursesResponse
  >('/api/course/listCourses/'),
  listSolvedProblems: apiCall<
    messages.CourseListSolvedProblemsRequest,
    messages.CourseListSolvedProblemsResponse
  >('/api/course/listSolvedProblems/'),
  listStudents: apiCall<
    messages.CourseListStudentsRequest,
    messages.CourseListStudentsResponse
  >('/api/course/listStudents/'),
  listUnsolvedProblems: apiCall<
    messages.CourseListUnsolvedProblemsRequest,
    messages.CourseListUnsolvedProblemsResponse
  >('/api/course/listUnsolvedProblems/'),
  myProgress: apiCall<
    messages.CourseMyProgressRequest,
    messages.CourseMyProgressResponse
  >('/api/course/myProgress/'),
  registerForCourse: apiCall<
    messages.CourseRegisterForCourseRequest,
    messages.CourseRegisterForCourseResponse
  >('/api/course/registerForCourse/'),
  removeAdmin: apiCall<
    messages.CourseRemoveAdminRequest,
    messages.CourseRemoveAdminResponse
  >('/api/course/removeAdmin/'),
  removeGroupAdmin: apiCall<
    messages.CourseRemoveGroupAdminRequest,
    messages.CourseRemoveGroupAdminResponse
  >('/api/course/removeGroupAdmin/'),
  removeProblem: apiCall<
    messages.CourseRemoveProblemRequest,
    messages.CourseRemoveProblemResponse
  >('/api/course/removeProblem/'),
  removeStudent: apiCall<
    messages.CourseRemoveStudentRequest,
    messages.CourseRemoveStudentResponse
  >('/api/course/removeStudent/'),
  requests: apiCall<
    messages.CourseRequestsRequest,
    messages._CourseRequestsServerResponse,
    messages.CourseRequestsResponse
  >('/api/course/requests/', x => {
    x.users = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        if (x.last_update)
          x.last_update = ((x: number) => new Date(x * 1000))(x.last_update);
        x.request_time = ((x: number) => new Date(x * 1000))(x.request_time);
        return x;
      });
    })(x.users);
    return x;
  }),
  runs: apiCall<
    messages.CourseRunsRequest,
    messages._CourseRunsServerResponse,
    messages.CourseRunsResponse
  >('/api/course/runs/', x => {
    x.runs = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.time = ((x: number) => new Date(x * 1000))(x.time);
        return x;
      });
    })(x.runs);
    return x;
  }),
  studentProgress: apiCall<
    messages.CourseStudentProgressRequest,
    messages._CourseStudentProgressServerResponse,
    messages.CourseStudentProgressResponse
  >('/api/course/studentProgress/', x => {
    x.problems = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.runs = (x => {
          if (!Array.isArray(x)) {
            return x;
          }
          return x.map(x => {
            x.time = ((x: number) => new Date(x * 1000))(x.time);
            return x;
          });
        })(x.runs);
        return x;
      });
    })(x.problems);
    return x;
  }),
  update: apiCall<messages.CourseUpdateRequest, messages.CourseUpdateResponse>(
    '/api/course/update/',
  ),
  updateAssignment: apiCall<
    messages.CourseUpdateAssignmentRequest,
    messages.CourseUpdateAssignmentResponse
  >('/api/course/updateAssignment/'),
  updateAssignmentsOrder: apiCall<
    messages.CourseUpdateAssignmentsOrderRequest,
    messages.CourseUpdateAssignmentsOrderResponse
  >('/api/course/updateAssignmentsOrder/'),
  updateProblemsOrder: apiCall<
    messages.CourseUpdateProblemsOrderRequest,
    messages.CourseUpdateProblemsOrderResponse
  >('/api/course/updateProblemsOrder/'),
};

export const Grader = {
  status: apiCall<messages.GraderStatusRequest, messages.GraderStatusResponse>(
    '/api/grader/status/',
  ),
};

export const Group = {
  addUser: apiCall<messages.GroupAddUserRequest, messages.GroupAddUserResponse>(
    '/api/group/addUser/',
  ),
  create: apiCall<messages.GroupCreateRequest, messages.GroupCreateResponse>(
    '/api/group/create/',
  ),
  createScoreboard: apiCall<
    messages.GroupCreateScoreboardRequest,
    messages.GroupCreateScoreboardResponse
  >('/api/group/createScoreboard/'),
  details: apiCall<messages.GroupDetailsRequest, messages.GroupDetailsResponse>(
    '/api/group/details/',
  ),
  list: apiCall<messages.GroupListRequest, messages.GroupListResponse>(
    '/api/group/list/',
  ),
  members: apiCall<messages.GroupMembersRequest, messages.GroupMembersResponse>(
    '/api/group/members/',
  ),
  myList: apiCall<messages.GroupMyListRequest, messages.GroupMyListResponse>(
    '/api/group/myList/',
  ),
  removeUser: apiCall<
    messages.GroupRemoveUserRequest,
    messages.GroupRemoveUserResponse
  >('/api/group/removeUser/'),
};

export const GroupScoreboard = {
  addContest: apiCall<
    messages.GroupScoreboardAddContestRequest,
    messages.GroupScoreboardAddContestResponse
  >('/api/groupScoreboard/addContest/'),
  details: apiCall<
    messages.GroupScoreboardDetailsRequest,
    messages.GroupScoreboardDetailsResponse
  >('/api/groupScoreboard/details/'),
  list: apiCall<
    messages.GroupScoreboardListRequest,
    messages.GroupScoreboardListResponse
  >('/api/groupScoreboard/list/'),
  removeContest: apiCall<
    messages.GroupScoreboardRemoveContestRequest,
    messages.GroupScoreboardRemoveContestResponse
  >('/api/groupScoreboard/removeContest/'),
};

export const Identity = {
  bulkCreate: apiCall<
    messages.IdentityBulkCreateRequest,
    messages.IdentityBulkCreateResponse
  >('/api/identity/bulkCreate/'),
  changePassword: apiCall<
    messages.IdentityChangePasswordRequest,
    messages.IdentityChangePasswordResponse
  >('/api/identity/changePassword/'),
  create: apiCall<
    messages.IdentityCreateRequest,
    messages.IdentityCreateResponse
  >('/api/identity/create/'),
  update: apiCall<
    messages.IdentityUpdateRequest,
    messages.IdentityUpdateResponse
  >('/api/identity/update/'),
};

export const Interview = {
  addUsers: apiCall<
    messages.InterviewAddUsersRequest,
    messages.InterviewAddUsersResponse
  >('/api/interview/addUsers/'),
  create: apiCall<
    messages.InterviewCreateRequest,
    messages.InterviewCreateResponse
  >('/api/interview/create/'),
  details: apiCall<
    messages.InterviewDetailsRequest,
    messages._InterviewDetailsServerResponse,
    messages.InterviewDetailsResponse
  >('/api/interview/details/', x => {
    x.users = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        if (x.access_time)
          x.access_time = ((x: number) => new Date(x * 1000))(x.access_time);
        return x;
      });
    })(x.users);
    return x;
  }),
  list: apiCall<messages.InterviewListRequest, messages.InterviewListResponse>(
    '/api/interview/list/',
  ),
};

export const Notification = {
  myList: apiCall<
    messages.NotificationMyListRequest,
    messages._NotificationMyListServerResponse,
    messages.NotificationMyListResponse
  >('/api/notification/myList/', x => {
    x.notifications = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.timestamp = ((x: number) => new Date(x * 1000))(x.timestamp);
        return x;
      });
    })(x.notifications);
    return x;
  }),
  readNotifications: apiCall<
    messages.NotificationReadNotificationsRequest,
    messages.NotificationReadNotificationsResponse
  >('/api/notification/readNotifications/'),
};

export const Problem = {
  addAdmin: apiCall<
    messages.ProblemAddAdminRequest,
    messages.ProblemAddAdminResponse
  >('/api/problem/addAdmin/'),
  addGroupAdmin: apiCall<
    messages.ProblemAddGroupAdminRequest,
    messages.ProblemAddGroupAdminResponse
  >('/api/problem/addGroupAdmin/'),
  addTag: apiCall<
    messages.ProblemAddTagRequest,
    messages.ProblemAddTagResponse
  >('/api/problem/addTag/'),
  adminList: apiCall<
    messages.ProblemAdminListRequest,
    messages.ProblemAdminListResponse
  >('/api/problem/adminList/'),
  admins: apiCall<
    messages.ProblemAdminsRequest,
    messages.ProblemAdminsResponse
  >('/api/problem/admins/'),
  bestScore: apiCall<
    messages.ProblemBestScoreRequest,
    messages.ProblemBestScoreResponse
  >('/api/problem/bestScore/'),
  clarifications: apiCall<
    messages.ProblemClarificationsRequest,
    messages._ProblemClarificationsServerResponse,
    messages.ProblemClarificationsResponse
  >('/api/problem/clarifications/', x => {
    x.clarifications = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.time = ((x: number) => new Date(x * 1000))(x.time);
        return x;
      });
    })(x.clarifications);
    return x;
  }),
  create: apiCall<
    messages.ProblemCreateRequest,
    messages.ProblemCreateResponse
  >('/api/problem/create/'),
  delete: apiCall<
    messages.ProblemDeleteRequest,
    messages.ProblemDeleteResponse
  >('/api/problem/delete/'),
  details: apiCall<
    messages.ProblemDetailsRequest,
    messages._ProblemDetailsServerResponse,
    messages.ProblemDetailsResponse
  >('/api/problem/details/', x => {
    x.runs = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.time = ((x: number) => new Date(x * 1000))(x.time);
        return x;
      });
    })(x.runs);
    return x;
  }),
  list: apiCall<messages.ProblemListRequest, messages.ProblemListResponse>(
    '/api/problem/list/',
  ),
  myList: apiCall<
    messages.ProblemMyListRequest,
    messages.ProblemMyListResponse
  >('/api/problem/myList/'),
  rejudge: apiCall<
    messages.ProblemRejudgeRequest,
    messages.ProblemRejudgeResponse
  >('/api/problem/rejudge/'),
  removeAdmin: apiCall<
    messages.ProblemRemoveAdminRequest,
    messages.ProblemRemoveAdminResponse
  >('/api/problem/removeAdmin/'),
  removeGroupAdmin: apiCall<
    messages.ProblemRemoveGroupAdminRequest,
    messages.ProblemRemoveGroupAdminResponse
  >('/api/problem/removeGroupAdmin/'),
  removeTag: apiCall<
    messages.ProblemRemoveTagRequest,
    messages.ProblemRemoveTagResponse
  >('/api/problem/removeTag/'),
  runs: apiCall<
    messages.ProblemRunsRequest,
    messages._ProblemRunsServerResponse,
    messages.ProblemRunsResponse
  >('/api/problem/runs/', x => {
    x.runs = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.time = ((x: number) => new Date(x * 1000))(x.time);
        return x;
      });
    })(x.runs);
    return x;
  }),
  runsDiff: apiCall<
    messages.ProblemRunsDiffRequest,
    messages.ProblemRunsDiffResponse
  >('/api/problem/runsDiff/'),
  selectVersion: apiCall<
    messages.ProblemSelectVersionRequest,
    messages.ProblemSelectVersionResponse
  >('/api/problem/selectVersion/'),
  solution: apiCall<
    messages.ProblemSolutionRequest,
    messages.ProblemSolutionResponse
  >('/api/problem/solution/'),
  stats: apiCall<messages.ProblemStatsRequest, messages.ProblemStatsResponse>(
    '/api/problem/stats/',
  ),
  tags: apiCall<messages.ProblemTagsRequest, messages.ProblemTagsResponse>(
    '/api/problem/tags/',
  ),
  update: apiCall<
    messages.ProblemUpdateRequest,
    messages.ProblemUpdateResponse
  >('/api/problem/update/'),
  updateSolution: apiCall<
    messages.ProblemUpdateSolutionRequest,
    messages.ProblemUpdateSolutionResponse
  >('/api/problem/updateSolution/'),
  updateStatement: apiCall<
    messages.ProblemUpdateStatementRequest,
    messages.ProblemUpdateStatementResponse
  >('/api/problem/updateStatement/'),
  versions: apiCall<
    messages.ProblemVersionsRequest,
    messages.ProblemVersionsResponse
  >('/api/problem/versions/'),
};

export const ProblemForfeited = {
  getCounts: apiCall<
    messages.ProblemForfeitedGetCountsRequest,
    messages.ProblemForfeitedGetCountsResponse
  >('/api/problemForfeited/getCounts/'),
};

export const Problemset = {
  details: apiCall<
    messages.ProblemsetDetailsRequest,
    messages._ProblemsetDetailsServerResponse,
    messages.ProblemsetDetailsResponse
  >('/api/problemset/details/', x => {
    x.users = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        if (x.access_time)
          x.access_time = ((x: number) => new Date(x * 1000))(x.access_time);
        return x;
      });
    })(x.users);
    return x;
  }),
  scoreboard: apiCall<
    messages.ProblemsetScoreboardRequest,
    messages.ProblemsetScoreboardResponse
  >('/api/problemset/scoreboard/'),
  scoreboardEvents: apiCall<
    messages.ProblemsetScoreboardEventsRequest,
    messages.ProblemsetScoreboardEventsResponse
  >('/api/problemset/scoreboardEvents/'),
};

export const QualityNomination = {
  create: apiCall<
    messages.QualityNominationCreateRequest,
    messages.QualityNominationCreateResponse
  >('/api/qualityNomination/create/'),
  details: apiCall<
    messages.QualityNominationDetailsRequest,
    messages.QualityNominationDetailsResponse
  >('/api/qualityNomination/details/'),
  list: apiCall<
    messages.QualityNominationListRequest,
    messages.QualityNominationListResponse
  >('/api/qualityNomination/list/'),
  myAssignedList: apiCall<
    messages.QualityNominationMyAssignedListRequest,
    messages.QualityNominationMyAssignedListResponse
  >('/api/qualityNomination/myAssignedList/'),
  myList: apiCall<
    messages.QualityNominationMyListRequest,
    messages.QualityNominationMyListResponse
  >('/api/qualityNomination/myList/'),
  resolve: apiCall<
    messages.QualityNominationResolveRequest,
    messages.QualityNominationResolveResponse
  >('/api/qualityNomination/resolve/'),
};

export const Reset = {
  create: apiCall<messages.ResetCreateRequest, messages.ResetCreateResponse>(
    '/api/reset/create/',
  ),
  generateToken: apiCall<
    messages.ResetGenerateTokenRequest,
    messages.ResetGenerateTokenResponse
  >('/api/reset/generateToken/'),
  update: apiCall<messages.ResetUpdateRequest, messages.ResetUpdateResponse>(
    '/api/reset/update/',
  ),
};

export const Run = {
  counts: apiCall<messages.RunCountsRequest, messages.RunCountsResponse>(
    '/api/run/counts/',
  ),
  create: apiCall<messages.RunCreateRequest, messages.RunCreateResponse>(
    '/api/run/create/',
  ),
  details: apiCall<messages.RunDetailsRequest, messages.RunDetailsResponse>(
    '/api/run/details/',
  ),
  disqualify: apiCall<
    messages.RunDisqualifyRequest,
    messages.RunDisqualifyResponse
  >('/api/run/disqualify/'),
  list: apiCall<
    messages.RunListRequest,
    messages._RunListServerResponse,
    messages.RunListResponse
  >('/api/run/list/', x => {
    x.runs = (x => {
      if (!Array.isArray(x)) {
        return x;
      }
      return x.map(x => {
        x.time = ((x: number) => new Date(x * 1000))(x.time);
        return x;
      });
    })(x.runs);
    return x;
  }),
  rejudge: apiCall<messages.RunRejudgeRequest, messages.RunRejudgeResponse>(
    '/api/run/rejudge/',
  ),
  source: apiCall<messages.RunSourceRequest, messages.RunSourceResponse>(
    '/api/run/source/',
  ),
  status: apiCall<
    messages.RunStatusRequest,
    messages._RunStatusServerResponse,
    messages.RunStatusResponse
  >('/api/run/status/', x => {
    x.time = ((x: number) => new Date(x * 1000))(x.time);
    return x;
  }),
};

export const School = {
  create: apiCall<messages.SchoolCreateRequest, messages.SchoolCreateResponse>(
    '/api/school/create/',
  ),
  list: apiCall<messages.SchoolListRequest, messages.SchoolListResponse>(
    '/api/school/list/',
  ),
  monthlySolvedProblemsCount: apiCall<
    messages.SchoolMonthlySolvedProblemsCountRequest,
    messages.SchoolMonthlySolvedProblemsCountResponse
  >('/api/school/monthlySolvedProblemsCount/'),
  rank: apiCall<messages.SchoolRankRequest, messages.SchoolRankResponse>(
    '/api/school/rank/',
  ),
  schoolCodersOfTheMonth: apiCall<
    messages.SchoolSchoolCodersOfTheMonthRequest,
    messages.SchoolSchoolCodersOfTheMonthResponse
  >('/api/school/schoolCodersOfTheMonth/'),
  selectSchoolOfTheMonth: apiCall<
    messages.SchoolSelectSchoolOfTheMonthRequest,
    messages.SchoolSelectSchoolOfTheMonthResponse
  >('/api/school/selectSchoolOfTheMonth/'),
  users: apiCall<messages.SchoolUsersRequest, messages.SchoolUsersResponse>(
    '/api/school/users/',
  ),
};

export const Scoreboard = {
  refresh: apiCall<
    messages.ScoreboardRefreshRequest,
    messages.ScoreboardRefreshResponse
  >('/api/scoreboard/refresh/'),
};

export const Session = {
  currentSession: apiCall<
    messages.SessionCurrentSessionRequest,
    messages.SessionCurrentSessionResponse
  >('/api/session/currentSession/'),
  googleLogin: apiCall<
    messages.SessionGoogleLoginRequest,
    messages.SessionGoogleLoginResponse
  >('/api/session/googleLogin/'),
};

export const Submission = {
  latestSubmissions: apiCall<
    messages.SubmissionLatestSubmissionsRequest,
    messages.SubmissionLatestSubmissionsResponse
  >('/api/submission/latestSubmissions/'),
};

export const Tag = {
  list: apiCall<messages.TagListRequest, messages.TagListResponse>(
    '/api/tag/list/',
  ),
};

export const Time = {
  get: apiCall<messages.TimeGetRequest, messages.TimeGetResponse>(
    '/api/time/get/',
  ),
};

export const User = {
  acceptPrivacyPolicy: apiCall<
    messages.UserAcceptPrivacyPolicyRequest,
    messages.UserAcceptPrivacyPolicyResponse
  >('/api/user/acceptPrivacyPolicy/'),
  addExperiment: apiCall<
    messages.UserAddExperimentRequest,
    messages.UserAddExperimentResponse
  >('/api/user/addExperiment/'),
  addGroup: apiCall<
    messages.UserAddGroupRequest,
    messages.UserAddGroupResponse
  >('/api/user/addGroup/'),
  addRole: apiCall<messages.UserAddRoleRequest, messages.UserAddRoleResponse>(
    '/api/user/addRole/',
  ),
  associateIdentity: apiCall<
    messages.UserAssociateIdentityRequest,
    messages.UserAssociateIdentityResponse
  >('/api/user/associateIdentity/'),
  changePassword: apiCall<
    messages.UserChangePasswordRequest,
    messages.UserChangePasswordResponse
  >('/api/user/changePassword/'),
  coderOfTheMonth: apiCall<
    messages.UserCoderOfTheMonthRequest,
    messages.UserCoderOfTheMonthResponse
  >('/api/user/coderOfTheMonth/'),
  coderOfTheMonthList: apiCall<
    messages.UserCoderOfTheMonthListRequest,
    messages.UserCoderOfTheMonthListResponse
  >('/api/user/coderOfTheMonthList/'),
  contestStats: apiCall<
    messages.UserContestStatsRequest,
    messages._UserContestStatsServerResponse,
    messages.UserContestStatsResponse
  >('/api/user/contestStats/', x => {
    x.contests = (x => {
      if (x instanceof Object) {
        Object.keys(x).forEach(
          y =>
            (x[y] = (x => {
              x.data = (x => {
                x.start_time = ((x: number) => new Date(x * 1000))(
                  x.start_time,
                );
                x.finish_time = ((x: number) => new Date(x * 1000))(
                  x.finish_time,
                );
                x.last_updated = ((x: number) => new Date(x * 1000))(
                  x.last_updated,
                );
                return x;
              })(x.data);
              return x;
            })(x[y])),
        );
      }
      return x;
    })(x.contests);
    return x;
  }),
  create: apiCall<messages.UserCreateRequest, messages.UserCreateResponse>(
    '/api/user/create/',
  ),
  extraInformation: apiCall<
    messages.UserExtraInformationRequest,
    messages.UserExtraInformationResponse
  >('/api/user/extraInformation/'),
  generateGitToken: apiCall<
    messages.UserGenerateGitTokenRequest,
    messages.UserGenerateGitTokenResponse
  >('/api/user/generateGitToken/'),
  generateOmiUsers: apiCall<
    messages.UserGenerateOmiUsersRequest,
    messages.UserGenerateOmiUsersResponse
  >('/api/user/generateOmiUsers/'),
  interviewStats: apiCall<
    messages.UserInterviewStatsRequest,
    messages.UserInterviewStatsResponse
  >('/api/user/interviewStats/'),
  lastPrivacyPolicyAccepted: apiCall<
    messages.UserLastPrivacyPolicyAcceptedRequest,
    messages.UserLastPrivacyPolicyAcceptedResponse
  >('/api/user/lastPrivacyPolicyAccepted/'),
  list: apiCall<messages.UserListRequest, messages.UserListResponse>(
    '/api/user/list/',
  ),
  listAssociatedIdentities: apiCall<
    messages.UserListAssociatedIdentitiesRequest,
    messages.UserListAssociatedIdentitiesResponse
  >('/api/user/listAssociatedIdentities/'),
  listUnsolvedProblems: apiCall<
    messages.UserListUnsolvedProblemsRequest,
    messages.UserListUnsolvedProblemsResponse
  >('/api/user/listUnsolvedProblems/'),
  login: apiCall<messages.UserLoginRequest, messages.UserLoginResponse>(
    '/api/user/login/',
  ),
  mailingListBackfill: apiCall<
    messages.UserMailingListBackfillRequest,
    messages.UserMailingListBackfillResponse
  >('/api/user/mailingListBackfill/'),
  problemsCreated: apiCall<
    messages.UserProblemsCreatedRequest,
    messages.UserProblemsCreatedResponse
  >('/api/user/problemsCreated/'),
  problemsSolved: apiCall<
    messages.UserProblemsSolvedRequest,
    messages.UserProblemsSolvedResponse
  >('/api/user/problemsSolved/'),
  profile: apiCall<messages.UserProfileRequest, messages.UserProfileResponse>(
    '/api/user/profile/',
  ),
  rankByProblemsSolved: apiCall<
    messages.UserRankByProblemsSolvedRequest,
    messages.UserRankByProblemsSolvedResponse
  >('/api/user/rankByProblemsSolved/'),
  removeExperiment: apiCall<
    messages.UserRemoveExperimentRequest,
    messages.UserRemoveExperimentResponse
  >('/api/user/removeExperiment/'),
  removeGroup: apiCall<
    messages.UserRemoveGroupRequest,
    messages.UserRemoveGroupResponse
  >('/api/user/removeGroup/'),
  removeRole: apiCall<
    messages.UserRemoveRoleRequest,
    messages.UserRemoveRoleResponse
  >('/api/user/removeRole/'),
  selectCoderOfTheMonth: apiCall<
    messages.UserSelectCoderOfTheMonthRequest,
    messages.UserSelectCoderOfTheMonthResponse
  >('/api/user/selectCoderOfTheMonth/'),
  stats: apiCall<messages.UserStatsRequest, messages.UserStatsResponse>(
    '/api/user/stats/',
  ),
  statusVerified: apiCall<
    messages.UserStatusVerifiedRequest,
    messages.UserStatusVerifiedResponse
  >('/api/user/statusVerified/'),
  update: apiCall<messages.UserUpdateRequest, messages.UserUpdateResponse>(
    '/api/user/update/',
  ),
  updateBasicInfo: apiCall<
    messages.UserUpdateBasicInfoRequest,
    messages.UserUpdateBasicInfoResponse
  >('/api/user/updateBasicInfo/'),
  updateMainEmail: apiCall<
    messages.UserUpdateMainEmailRequest,
    messages.UserUpdateMainEmailResponse
  >('/api/user/updateMainEmail/'),
  validateFilter: apiCall<
    messages.UserValidateFilterRequest,
    messages.UserValidateFilterResponse
  >('/api/user/validateFilter/'),
  verifyEmail: apiCall<
    messages.UserVerifyEmailRequest,
    messages.UserVerifyEmailResponse
  >('/api/user/verifyEmail/'),
};
