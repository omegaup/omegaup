import { omegaup, OmegaUp } from '../omegaup';
import { messages, types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import course_AssignmentDetails from '../components/course/AssignmentDetails.vue';
import course_Edit from '../components/course/Edit.vue';
import course_Form from '../components/course/Form.vue';
import Sortable from 'sortablejs';

Vue.directive('Sortable', {
  inserted: (el: HTMLElement, binding) => {
    new Sortable(el, binding.value || {});
  },
});

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseEditPayload();
  const courseAlias = payload.course.alias;

  const courseEdit = new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-edit': course_Edit,
    },
    data: () => ({
      data: payload,
      initialTab: window.location.hash
        ? window.location.hash.substr(1)
        : 'course',
      invalidParameterName: '',
      token: '',
      searchResultUsers: [] as types.ListItem[],
    }),
    methods: {
      refreshCourseAdminDetails: (): void => {
        api.Course.adminDetails({ alias: courseAlias }).then((course) => {
          courseEdit.data.course = course;
        });
      },
      refreshStudentList: (): void => {
        api.Course.listStudents({ course_alias: courseAlias })
          .then((response) => {
            courseEdit.data.students = response.students;
          })
          .catch(ui.apiError);
        api.Course.requests({ course_alias: courseAlias })
          .then((response) => {
            courseEdit.data.identityRequests = response.users;
          })
          .catch(ui.apiError);
      },
      refreshAssignmentsList: (): void => {
        api.Course.listAssignments({ course_alias: courseAlias })
          .then((response) => {
            component.assignments = response.assignments;
            component.onResetAssignmentForm();
          })
          .catch(ui.apiError);
      },
      refreshProblemList: (assignment: types.CourseAssignment): void => {
        api.Course.assignmentDetails({
          assignment: assignment.alias,
          course: courseAlias,
        })
          .then((response) => {
            component.assignmentProblems = response.problems;
          })
          .catch(ui.apiError);
      },
      refreshCourseAdmins: (): void => {
        api.Course.admins({ course_alias: courseAlias })
          .then((response) => {
            courseEdit.data.admins = response.admins;
            courseEdit.data.groupsAdmins = response.group_admins;
          })
          .catch(ui.apiError);
      },
      arbitrateRequest: (username: string, resolution: boolean) => {
        api.Course.arbitrateRequest({
          course_alias: courseAlias,
          username: username,
          resolution: resolution,
          note: '',
        })
          .then(() => {
            if (resolution) {
              ui.success(T.arbitrateRequestAcceptSuccessfully);
            } else {
              ui.success(T.arbitrateRequestDenySuccessfully);
            }
            courseEdit.refreshStudentList();
          })
          .catch(ui.apiError);
      },
    },
    render: function (createElement) {
      return createElement('omegaup-course-edit', {
        props: {
          data: this.data,
          initialTab: this.initialTab,
          invalidParameterName: this.invalidParameterName,
          token: this.token,
          searchResultUsers: this.searchResultUsers,
        },
        on: {
          'submit-edit-course': (source: course_Form) => {
            new Promise<number | null>((accept) => {
              if (source.school_id !== undefined) {
                accept(source.school_id);
              } else if (source.school_name) {
                api.School.create({ name: source.school_name })
                  .then((response) => {
                    accept(response.school_id);
                  })
                  .catch(ui.apiError);
              } else {
                accept(null);
              }
            })
              .then((schoolId) => {
                const params: messages.CourseUpdateRequest = {
                  name: source.name,
                  description: source.description,
                  start_time: source.startTime,
                  alias: source.alias,
                  languages: source.selectedLanguages,
                  show_scoreboard: source.showScoreboard,
                  needs_basic_information: source.needsBasicInformation,
                  requests_user_information: source.requests_user_information,
                  school_id: schoolId ?? undefined,
                  unlimited_duration: source.unlimitedDuration,
                  finish_time: !source.unlimitedDuration
                    ? new Date(source.finishTime).setHours(23, 59, 59, 999) /
                      1000
                    : null,
                };

                api.Course.update(params)
                  .then(() => {
                    ui.success(
                      ui.formatString(T.courseEditCourseEditedAndGoToCourse, {
                        alias: source.alias,
                      }),
                    );
                    this.data.course.name = source.name;
                    window.scrollTo(0, 0);
                    this.refreshCourseAdminDetails();
                  })
                  .catch(ui.apiError);
              })
              .catch(ui.apiError);
          },
          'submit-new-assignment': (
            source: course_AssignmentDetails,
            problems: types.AddedProblem[],
          ) => {
            const params = {
              name: source.name,
              description: source.description,
              start_time: source.startTime.getTime() / 1000,
              assignment_type: source.assignmentType,
            };
            if (source.update) {
              Object.assign(params, {
                assignment: source.alias,
                course: courseAlias,
              });

              if (source.unlimitedDuration) {
                Object.assign(params, { unlimited_duration: true });
              } else {
                Object.assign(params, {
                  finish_time: source.finishTime.getTime() / 1000,
                });
              }

              api.Course.updateAssignment(params)
                .then(() => {
                  ui.success(T.courseAssignmentUpdated);
                  this.invalidParameterName = '';
                  this.refreshAssignmentsList();
                })
                .catch((error) => {
                  ui.apiError(error);
                  component.assignmentFormMode =
                    omegaup.AssignmentFormMode.Edit;
                  this.invalidParameterName = error.parameter || '';
                });
            } else {
              Object.assign(params, {
                alias: source.alias,
                course_alias: courseAlias,
                problems: JSON.stringify(problems),
              });

              if (source.unlimitedDuration) {
                Object.assign(params, { unlimited_duration: true });
              } else {
                Object.assign(params, {
                  finish_time: source.finishTime.getTime() / 1000,
                });
              }

              api.Course.createAssignment(params)
                .then(() => {
                  ui.success(T.courseAssignmentAdded);
                  this.invalidParameterName = '';
                  this.refreshAssignmentsList();
                })
                .catch((error) => {
                  ui.apiError(error);
                  component.assignmentFormMode = omegaup.AssignmentFormMode.New;
                  this.invalidParameterName = error.parameter || '';
                });
              window.scrollTo(0, 0);
            }
          },
          'delete-assignment': (assignment: types.CourseAssignment) => {
            if (
              !window.confirm(
                ui.formatString(T.courseAssignmentConfirmDelete, {
                  assignment: assignment.name,
                }),
              )
            ) {
              return;
            }
            api.Course.removeAssignment({
              course_alias: courseAlias,
              assignment_alias: assignment.alias,
            })
              .then(() => {
                ui.success(T.courseAssignmentDeleted);
                this.refreshAssignmentsList();
              })
              .catch(ui.apiError);
          },
          'sort-content': (courseAlias: string, contentAliases: string[]) => {
            api.Course.updateAssignmentsOrder({
              course_alias: courseAlias,
              assignments: JSON.stringify(contentAliases),
            })
              .then(() => {
                ui.success(T.contentOrderUpdated);
              })
              .catch(ui.apiError);
          },
          'get-versions': ({
            request,
            target,
          }: {
            request: { problemAlias: string; problemsetId: number };
            target: {
              versionLog: types.ProblemVersion[];
              problems: types.ProblemsetProblem[];
              selectedRevision: types.ProblemVersion;
              publishedRevision: types.ProblemVersion;
            };
          }) => {
            api.Problem.versions({
              problem_alias: request.problemAlias,
              problemset_id: request.problemsetId,
            })
              .then((result) => {
                target.versionLog = result.log;
                let publishedCommitHash = result.published;
                for (const problem of target.problems) {
                  if (problem.alias === request.problemAlias) {
                    publishedCommitHash = problem.commit;
                    break;
                  }
                }
                for (const revision of result.log) {
                  if (publishedCommitHash === revision.commit) {
                    target.selectedRevision = target.publishedRevision = revision;
                    break;
                  }
                }
              })
              .catch(ui.apiError);
          },
          'add-problem': (
            assignment: types.CourseAssignment,
            problem: types.AddedProblem,
          ) => {
            const problemParams: messages.CourseAddProblemRequest = {
              course_alias: courseAlias,
              assignment_alias: assignment.alias,
              problem_alias: problem.alias,
              points: problem.points,
              is_extra_problem: problem.is_extra_problem,
            };
            if (problem.commit) {
              problemParams.commit = problem.commit;
            }
            api.Course.addProblem(problemParams)
              .then(() => {
                if (assignment.assignment_type == 'lesson') {
                  ui.success(T.courseAssignmentLectureAdded);
                } else {
                  ui.success(T.courseAssignmentProblemAdded);
                }
                this.refreshProblemList(assignment);
              })
              .catch(ui.apiError);
          },
          'select-assignment': (assignment: types.CourseAssignment) => {
            this.refreshProblemList(assignment);
          },
          'remove-problem': (
            assignment: types.CourseAssignment,
            problem: types.ProblemsetProblem,
          ) => {
            if (
              !window.confirm(
                ui.formatString(T.courseAssignmentProblemConfirmRemove, {
                  problem: problem.title,
                }),
              )
            ) {
              return;
            }
            api.Course.removeProblem({
              course_alias: courseAlias,
              problem_alias: problem.alias,
              assignment_alias: assignment.alias,
            })
              .then(() => {
                if (assignment.assignment_type == 'lesson') {
                  ui.success(T.courseAssignmentLectureRemoved);
                } else {
                  ui.success(T.courseAssignmentProblemRemoved);
                }
                this.refreshProblemList(assignment);
              })
              .catch(ui.apiError);
          },
          'sort-problems': (
            assignmentAlias: string,
            problemsAliases: string[],
          ) => {
            api.Course.updateProblemsOrder({
              course_alias: courseAlias,
              assignment_alias: assignmentAlias,
              problems: JSON.stringify(problemsAliases),
            })
              .then(() => {
                ui.success(T.problemsOrderUpdated);
              })
              .catch(ui.apiError);
          },
          'tags-problems': (tags: string[]) => {
            api.Problem.list({ tag: tags.join() })
              .then(() => {
                //this.data.taggedProblems = data.results;
              })
              .catch(ui.apiError);
          },
          'update-admission-mode': (admissionMode: string) => {
            courseEdit.data.course.admission_mode = admissionMode;
            api.Course.update({
              alias: courseAlias,
              admission_mode: admissionMode,
            })
              .then(() => {
                ui.success(T.courseEditCourseEdited);
                if (admissionMode === 'registration') {
                  this.refreshStudentList();
                }
              })
              .catch(ui.apiError);
          },
          'add-student': (ev: {
            participant: string;
            participants: string;
          }) => {
            let participants: string[] = [];
            if (ev.participants) participants = ev.participants.split(/[\n,]/);
            if (ev.participant) participants.push(ev.participant);
            if (participants.length === 0) {
              ui.error(T.wordsEmptyAddStudentInput);
              return;
            }
            Promise.allSettled(
              participants.map((participant) =>
                api.Course.addStudent({
                  course_alias: courseAlias,
                  usernameOrEmail: participant.trim(),
                }),
              ),
            )
              .then((results) => {
                const participantsWithError: string[] = [];
                results.forEach((result) => {
                  if (result.status === 'rejected') {
                    participantsWithError.push(result.reason.userEmail);
                  }
                });
                this.refreshStudentList();
                if (participantsWithError.length === 0) {
                  ui.success(T.courseStudentAdded);
                  return;
                }
                ui.error(
                  ui.formatString(T.bulkUserAddError, {
                    userEmail: participantsWithError.join('<br>'),
                  }),
                );
              })
              .catch(ui.ignoreError);
          },
          'remove-student': (student: types.StudentProgress) => {
            api.Course.removeStudent({
              course_alias: courseAlias,
              usernameOrEmail: student.username,
            })
              .then(() => {
                this.refreshStudentList();
                ui.success(T.courseStudentRemoved);
              })
              .catch(ui.apiError);
          },
          'accept-request': ({ username }: { username: string }) =>
            this.arbitrateRequest(username, true),
          'deny-request': ({ username }: { username: string }) =>
            this.arbitrateRequest(username, false),
          'add-admin': (useradmin: string) => {
            api.Course.addAdmin({
              course_alias: courseAlias,
              usernameOrEmail: useradmin,
            })
              .then(() => {
                ui.success(T.adminAdded);
                this.refreshCourseAdmins();
              })
              .catch(ui.apiError);
          },
          'remove-admin': (username: string) => {
            api.Course.removeAdmin({
              course_alias: courseAlias,
              usernameOrEmail: username,
            })
              .then(() => {
                this.refreshCourseAdmins();
                ui.success(T.adminRemoved);
              })
              .catch(ui.apiError);
          },
          'add-group-admin': (groupAlias: string) => {
            api.Course.addGroupAdmin({
              course_alias: courseAlias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.groupAdminAdded);
                this.refreshCourseAdmins();
              })
              .catch(ui.apiError);
          },
          'remove-group-admin': (groupAlias: string) => {
            api.Course.removeGroupAdmin({
              course_alias: courseAlias,
              group: groupAlias,
            })
              .then(() => {
                this.refreshCourseAdmins();
                ui.success(T.groupAdminRemoved);
              })
              .catch(ui.apiError);
          },
          clone: (alias: string, name: string, startTime: Date) => {
            api.Course.clone({
              course_alias: courseAlias,
              name: name,
              alias: alias,
              start_time: startTime.getTime() / 1000,
            })
              .then(() => {
                ui.success(
                  ui.formatString(T.courseEditCourseClonedSuccessfully, {
                    course_alias: alias,
                  }),
                  /*autoHide=*/ false,
                );
              })
              .catch(ui.apiError);
          },
          'generate-link': (alias: string) => {
            api.Course.generateTokenForCloneCourse({
              course_alias: alias,
            })
              .then((data) => {
                ui.success(T.courseCloneGenerateLinkSuccess);
                component.token = data.token;
              })
              .catch(ui.apiError);
          },
          'archive-course': (alias: string, archive: boolean) => {
            api.Course.archive({
              course_alias: alias,
              archive,
            })
              .then(() => {
                if (archive) {
                  ui.success(T.courseArchivedSuccess);
                  return;
                }
                ui.success(T.courseUnarchivedSuccess);
              })
              .catch(ui.apiError);
          },
          'update-search-result-users': (query: string) => {
            api.User.list({ query })
              .then(({ results }) => {
                this.searchResultUsers = results.map(
                  ({ key, value }: types.ListItem) => ({
                    key,
                    value: `${ui.escape(key)} (<strong>${ui.escape(
                      value,
                    )}</strong>)`,
                  }),
                );
              })
              .catch(ui.apiError);
          },
        },
        ref: 'component',
      });
    },
  });
  const component = courseEdit.$refs.component as course_Edit;
});
