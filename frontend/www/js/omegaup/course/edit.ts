import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import course_AssignmentDetails from '../components/course/AssignmentDetails.vue';
import course_Edit from '../components/course/Edit.vue';
import course_Form from '../components/course/Form.vue';
import Sortable from 'sortablejs';
import Clipboard from 'v-clipboard';

Vue.directive('Sortable', {
  inserted: (el: HTMLElement, binding) => {
    new Sortable(el, binding.value || {});
  },
});
Vue.use(Clipboard);

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseEditPayload();
  const courseAlias = payload.course.alias;
  const courseEdit = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-course-edit', {
        props: {
          data: this.data,
          initialTab: this.initialTab,
          invalidParameterName: this.invalidParameterName,
        },
        on: {
          'submit-edit-course': (source: course_Form) => {
            new Promise((accept, reject) => {
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
                const params = {
                  course_alias: courseAlias,
                  name: source.name,
                  description: source.description,
                  start_time: source.startTime.getTime() / 1000,
                  alias: source.alias,
                  show_scoreboard: source.showScoreboard,
                  needs_basic_information: source.basic_information_required,
                  requests_user_information: source.requests_user_information,
                  school_id: schoolId,
                };

                if (source.unlimitedDuration) {
                  Object.assign(params, { unlimited_duration: true });
                } else {
                  Object.assign(params, {
                    finish_time:
                      new Date(source.finishTime).setHours(23, 59, 59, 999) /
                      1000,
                  });
                }

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
          'submit-new-assignment': (source: course_AssignmentDetails) => {
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
                  component.visibilityMode = omegaup.VisibilityMode.Edit;
                  this.invalidParameterName = error.parameter || '';
                });
            } else {
              Object.assign(params, {
                alias: source.alias,
                course_alias: courseAlias,
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
                  component.visibilityMode = omegaup.VisibilityMode.New;
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
              .then((data) => {
                ui.success(T.courseAssignmentDeleted);
                this.refreshAssignmentsList();
              })
              .catch(ui.apiError);
          },
          'sort-homeworks': (
            courseAlias: string,
            homeworksAliases: string[],
          ) => {
            api.Course.updateAssignmentsOrder({
              course_alias: courseAlias,
              assignments: JSON.stringify(homeworksAliases),
            })
              .then(() => {
                ui.success(T.homeworksOrderUpdated);
              })
              .catch(ui.apiError);
          },
          'sort-tests': (courseAlias: string, testsAliases: string[]) => {
            api.Course.updateAssignmentsOrder({
              course_alias: courseAlias,
              assignments: JSON.stringify(testsAliases),
            })
              .then(() => {
                ui.success(T.testsOrderUpdated);
              })
              .catch(ui.apiError);
          },
          'add-problem': (
            assignment: types.CourseAssignment,
            problemAlias: string,
          ) => {
            api.Course.addProblem({
              course_alias: courseAlias,
              assignment_alias: assignment.alias,
              problem_alias: problemAlias,
            })
              .then(() => {
                ui.success(T.courseAssignmentProblemAdded);
                this.refreshProblemList(assignment);
                component.visibilityMode = omegaup.VisibilityMode.Default;
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
              .then((response) => {
                ui.success(T.courseAssignmentProblemRemoved);
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
              .then((data) => {
                //this.data.taggedProblems = data.results;
              })
              .catch(ui.apiError);
          },
          'update-admission-mode': (admissionMode: string) => {
            api.Course.update({
              course_alias: courseAlias,
              admission_mode: admissionMode,
            })
              .then(() => {
                ui.success(T.courseEditCourseEdited);
              })
              .catch(ui.apiError);
          },
          'add-student': (ev: {
            participant: string;
            participants: string;
          }) => {
            let participants: string[] = [];
            if (ev.participants !== '')
              participants = ev.participants.split(',');
            if (ev.participant !== '') participants.push(ev.participant);
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
                let participantsWithError: string[] = [];
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
          'remove-student': (student: types.CourseStudent) => {
            api.Course.removeStudent({
              course_alias: courseAlias,
              usernameOrEmail: student.username,
            })
              .then((data) => {
                this.refreshStudentList();
                ui.success(T.courseStudentRemoved);
              })
              .catch(ui.apiError);
          },
          'accept-request': (username: string) =>
            this.arbitrateRequest(username, true),
          'deny-request': (username: string) =>
            this.arbitrateRequest(username, false),
          'add-admin': (useradmin: string) => {
            api.Course.addAdmin({
              course_alias: courseAlias,
              usernameOrEmail: useradmin,
            })
              .then((data) => {
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
              .then((data) => {
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
              .then((data) => {
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
              .then((data) => {
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
              .then((data) => {
                ui.success(
                  ui.formatString(T.courseEditCourseClonedSuccessfully, {
                    course_alias: alias,
                  }),
                );
              })
              .catch(ui.apiError);
          },
        },
        ref: 'component',
      });
    },
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
            courseEdit.data.course.assignments = response.assignments;
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
            courseEdit.data.assignmentProblems = response.problems;
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
          .then((response) => {
            ui.success(T.successfulOperation);
            courseEdit.refreshStudentList();
          })
          .catch(ui.apiError);
      },
    },
    data: {
      data: payload,
      initialTab: window.location.hash
        ? window.location.hash.substr(1)
        : 'course',
      invalidParameterName: '',
    },
    components: {
      'omegaup-course-edit': course_Edit,
    },
  });
  const component = <course_Edit>courseEdit.$refs.component;
});
