import course_AddStudents from '../components/course/AddStudents.vue';
import course_Admins from '../components/common/Admins.vue';
import course_AdmissionMode from '../components/course/AdmissionMode.vue';
import course_GroupAdmins from '../components/common/GroupAdmins.vue';
import course_AssignmentDetails from '../components/course/AssignmentDetails.vue';
import course_AssignmentList from '../components/course/AssignmentList.vue';
import course_Form from '../components/course/Form.vue';
import course_ProblemList from '../components/course/ProblemList.vue';
import common_Publish from '../components/common/Publish.vue';
import course_Clone from '../components/course/Clone.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import API from '../api';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';
import Sortable from 'sortablejs';
import Clipboard from 'v-clipboard';

Vue.directive('Sortable', {
  inserted: function(el, binding) {
    new Sortable(el, binding.value || {});
  },
});
Vue.use(Clipboard);

OmegaUp.on('ready', function() {
  let vuePath = [];
  if (window.location.hash) {
    vuePath = window.location.hash.split('/');
    $('#sections')
      .find('a[href="' + vuePath[0] + '"]')
      .tab('show');
  }

  $('#sections').on('click', 'a', function(e) {
    e.preventDefault();
    // add this line
    var tabName = $(this).attr('href');
    window.location.hash = tabName;
    if (tabName.split('#')[1] !== 'assignments') {
      assignmentDetails.show = false;
      updateNewAssignmentButtonVisibility(true);
    }
    $(this).tab('show');
  });

  var courseAlias = /\/course\/([^\/]+)\/edit\/?.*/.exec(
    window.location.pathname,
  )[1];

  var defaultDate = Date.create(Date.now());
  defaultDate.set({ seconds: 0 });
  var defaultStartTime = Date.create(defaultDate);
  defaultDate.setHours(defaultDate.getHours() + 5);
  var defaultFinishTime = Date.create(defaultDate);

  function updateNewAssignmentButtonVisibility(visible) {
    document.querySelector('form.new').style.display = visible
      ? 'initial'
      : 'none';
  }

  function onNewAssignment(assignmentType) {
    assignmentDetails.show = true;
    assignmentDetails.update = false;
    assignmentDetails.assignment = {
      start_time: defaultStartTime,
      finish_time: defaultFinishTime,
      assignment_type: assignmentType,
    };
    updateNewAssignmentButtonVisibility(false);

    // Vue lazily updates the DOM, so any interactions with `$el` need to
    // wait until the update is done.
    Vue.nextTick(function() {
      assignmentDetails.$el.scrollIntoView();
    });
  }

  const courseAdmins = new Vue({
    el: '#admins .admins',
    render: function(createElement) {
      return createElement('omegaup-course-admins', {
        props: { initialAdmins: this.initialAdmins },
        on: {
          'add-admin': useradmin => {
            api.Course.addAdmin({
              course_alias: courseAlias,
              usernameOrEmail: useradmin,
            })
              .then(data => {
                UI.success(T.adminAdded);
                refreshCourseAdmins();
              })
              .catch(UI.apiError);
          },
          'remove-admin': username => {
            api.Course.removeAdmin({
              course_alias: courseAlias,
              usernameOrEmail: username,
            })
              .then(data => {
                refreshCourseAdmins();
                UI.success(T.adminRemoved);
              })
              .catch(UI.apiError);
          },
        },
      });
    },
    data: { initialAdmins: [] },
    components: {
      'omegaup-course-admins': course_Admins,
    },
  });

  const courseGroupAdmins = new Vue({
    el: '#admins .groups',
    render: function(createElement) {
      return createElement('omegaup-course-group-admins', {
        props: { initialGroups: this.initialGroups },
        on: {
          'add-group-admin': groupAlias => {
            api.Course.addGroupAdmin({
              course_alias: courseAlias,
              group: groupAlias,
            })
              .then(data => {
                UI.success(T.groupAdminAdded);
                refreshCourseAdmins();
              })
              .catch(UI.apiError);
          },
          'remove-group-admin': groupAlias => {
            api.Course.removeGroupAdmin({
              course_alias: courseAlias,
              group: groupAlias,
            })
              .then(data => {
                refreshCourseAdmins();
                UI.success(T.groupAdminRemoved);
              })
              .catch(UI.apiError);
          },
        },
      });
    },
    data: { initialGroups: [] },
    components: {
      'omegaup-course-group-admins': course_GroupAdmins,
    },
  });

  var assignmentList = new Vue({
    el: '#assignments div.list',
    render: function(createElement) {
      return createElement('omegaup-course-assignmentlist', {
        props: { assignments: this.assignments, courseAlias: courseAlias },
        on: {
          edit: function(assignment) {
            assignmentDetails.show = true;
            assignmentDetails.update = true;
            assignmentDetails.assignment = assignment;
            assignmentDetails.$el.scrollIntoView();
            updateNewAssignmentButtonVisibility(true);
          },
          'add-problems': function(assignment) {
            window.location.hash = 'problems';
            assignmentDetails.show = false;
            problemList.selectedAssignment = assignment;
            updateNewAssignmentButtonVisibility(true);
            $('#sections')
              .find('a[href="#problems"]')
              .tab('show');
          },
          delete: function(assignment) {
            if (
              !window.confirm(
                UI.formatString(T.courseAssignmentConfirmDelete, {
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
              .then(function(data) {
                UI.success(T.courseAssignmentDeleted);
                refreshAssignmentsList();
              })
              .catch(UI.apiError);
          },
          new: onNewAssignment,
          'sort-homeworks': function(courseAlias, homeworksAliases) {
            api.Course.updateAssignmentsOrder({
              course_alias: courseAlias,
              assignments: JSON.stringify(homeworksAliases),
            }).catch(UI.apiError);
          },
          'sort-tests': function(courseAlias, testsAliases) {
            api.Course.updateAssignmentsOrder({
              course_alias: courseAlias,
              assignments: JSON.stringify(testsAliases),
            }).catch(UI.apiError);
          },
        },
      });
    },
    data: {
      assignments: [],
    },
    components: {
      'omegaup-course-assignmentlist': course_AssignmentList,
    },
  });

  const assignmentDetails = new Vue({
    el: '#assignments div.form',
    render: function(createElement) {
      return createElement('omegaup-course-assignmentdetails', {
        props: {
          show: this.show,
          update: this.update,
          assignment: this.assignment,
        },
        on: {
          submit: function(ev) {
            if (ev.update) {
              const params = {
                course: courseAlias,
                name: ev.name,
                description: ev.description,
                start_time: ev.startTime.getTime() / 1000,
                assignment: ev.alias,
                assignment_type: ev.assignmentType,
              };

              if (ev.unlimitedDuration) {
                params.unlimited_duration = true;
              } else {
                params.finish_time = ev.finishTime.getTime() / 1000;
              }

              api.Course.updateAssignment(params)
                .then(function() {
                  UI.success(T.courseAssignmentUpdated);
                  refreshAssignmentsList();
                })
                .catch(function(error) {
                  UI.apiError(error);
                  assignmentDetails.show = true;
                });
            } else {
              const params = {
                course_alias: courseAlias,
                name: ev.name,
                description: ev.description,
                start_time: ev.startTime.getTime() / 1000,
                alias: ev.alias,
                assignment_type: ev.assignmentType,
              };

              if (ev.unlimitedDuration) {
                params.unlimited_duration = true;
              } else {
                params.finish_time = ev.finishTime.getTime() / 1000;
              }

              api.Course.createAssignment(params)
                .then(function() {
                  UI.success(T.courseAssignmentAdded);
                  updateNewAssignmentButtonVisibility(true);
                  refreshAssignmentsList();
                })
                .catch(function(error) {
                  UI.apiError(error);
                  assignmentDetails.show = true;
                });
            }
            assignmentDetails.show = false;
          },
          cancel: function() {
            assignmentDetails.show = false;
            updateNewAssignmentButtonVisibility(true);
          },
        },
      });
    },
    data: {
      show: false,
      update: false,
      assignment: {
        start_time: defaultStartTime,
        finish_time: defaultFinishTime,
      },
    },
    components: {
      'omegaup-course-assignmentdetails': course_AssignmentDetails,
    },
  });

  var details = new Vue({
    el: '#edit div',
    render: function(createElement) {
      return createElement('omegaup-course-details', {
        props: { update: true, course: this.course },
        on: {
          submit: function(ev) {
            new Promise((accept, reject) => {
              if (ev.school_id !== undefined) {
                accept(ev.school_id);
              } else if (ev.school_name) {
                api.School.create({ name: ev.school_name })
                  .then(function(data) {
                    accept(data.school_id);
                  })
                  .catch(UI.apiError);
              } else {
                accept(null);
              }
            })
              .then(function(school_id) {
                const params = {
                  course_alias: courseAlias,
                  name: ev.name,
                  description: ev.description,
                  start_time: ev.startTime.getTime() / 1000,
                  alias: ev.alias,
                  show_scoreboard: ev.showScoreboard,
                  needs_basic_information: ev.basic_information_required,
                  requests_user_information: ev.requests_user_information,
                  school_id: school_id,
                };

                if (ev.unlimitedDuration) {
                  params.unlimited_duration = true;
                } else {
                  params.finish_time =
                    new Date(ev.finishTime).setHours(23, 59, 59, 999) / 1000;
                }

                api.Course.update(params)
                  .then(function() {
                    UI.success(
                      UI.formatString(T.courseEditCourseEditedAndGoToCourse, {
                        alias: ev.alias,
                      }),
                    );
                    $('.course-header')
                      .text(ev.alias)
                      .attr('href', '/course/' + ev.alias + '/');
                    $('div.post.footer').show();
                    window.scrollTo(0, 0);
                  })
                  .catch(UI.apiError);
              })
              .catch(UI.apiError);
          },
          cancel: function(ev) {
            window.location = '/course/' + courseAlias + '/';
          },
        },
      });
    },
    data: {
      course: {},
    },
    components: {
      'omegaup-course-details': course_Form,
    },
  });

  var problemList = new Vue({
    el: '#problems div',
    render: function(createElement) {
      return createElement('omegaup-course-problemlist', {
        props: {
          assignments: this.assignments,
          assignmentProblems: this.assignmentProblems,
          taggedProblems: this.taggedProblems,
          selectedAssignment: this.selectedAssignment,
        },
        on: {
          'add-problem': function(assignment, problemAlias) {
            api.Course.addProblem({
              course_alias: courseAlias,
              assignment_alias: assignment.alias,
              problem_alias: problemAlias,
            })
              .then(function(data) {
                refreshProblemList(assignment);
                problemList.$children[0].showForm = false;
                UI.success(T.courseAssignmentProblemAdded);
              })
              .catch(UI.apiError);
          },
          assignment: function(assignment) {
            refreshProblemList(assignment);
          },
          remove: function(assignment, problem) {
            if (
              !window.confirm(
                UI.formatString(T.courseAssignmentProblemConfirmRemove, {
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
              .then(function(response) {
                UI.success(T.courseAssignmentProblemRemoved);
                refreshProblemList(assignment);
              })
              .catch(UI.apiError);
          },
          sort: function(assignmentAlias, problemsAliases) {
            api.Course.updateProblemsOrder({
              course_alias: courseAlias,
              assignment_alias: assignmentAlias,
              problems: JSON.stringify(problemsAliases),
            })
              .then(function() {})
              .catch(UI.apiError);
          },
          tags: function(tags) {
            api.Problem.list({ tag: tags.join() })
              .then(function(data) {
                problemList.taggedProblems = data.results;
              })
              .catch(UI.apiError);
          },
        },
      });
    },
    data: {
      assignments: [],
      assignmentProblems: [],
      taggedProblems: [],
      selectedAssignment: null,
    },
    components: {
      'omegaup-course-problemlist': course_ProblemList,
    },
  });

  let editAdmissionMode = new Vue({
    el: '#admission-mode div',
    render: function(createElement) {
      return createElement('omegaup-course-admission-mode', {
        props: {
          initialAdmissionMode: this.admissionMode,
          shouldShowPublicOption: this.shouldShowPublicOption,
          admissionModeDescription: this.admissionModeDescription,
          courseAlias: courseAlias,
        },
        on: {
          'emit-update-admission-mode': function(admissionMode) {
            api.Course.update({
              course_alias: courseAlias,
              admission_mode: admissionMode,
            })
              .then(() => {
                UI.success(T.courseEditCourseEdited);
              })
              .catch(UI.apiError);
          },
        },
      });
    },
    data: {
      admissionMode: null,
      shouldShowPublicOption: false,
      admissionModeDescription: T.courseEditAdmissionModeDescription,
    },
    components: {
      'omegaup-course-admission-mode': course_AdmissionMode,
    },
  });

  let addStudents = new Vue({
    el: '#students div.list',
    render: function(createElement) {
      return createElement('omegaup-course-addstudents', {
        props: {
          students: this.students,
          courseAlias: courseAlias,
          data: this.data,
        },
        on: {
          'accept-request': (ev, username) =>
            this.arbitrateRequest(ev, username, true),
          'deny-request': (ev, username) =>
            this.arbitrateRequest(ev, username, false),
          'add-student': function(ev) {
            let participants = [];
            if (ev.participants !== '')
              participants = ev.participants.split(',');
            if (ev.participant !== '') participants.push(ev.participant);
            if (participants.length == 0) {
              UI.error(T.wordsEmptyAddStudentInput);
              return;
            }
            Promise.allSettled(
              participants.map(participant =>
                api.Course.addStudent({
                  course_alias: courseAlias,
                  usernameOrEmail: participant.trim(),
                }),
              ),
            )
              .then(results => {
                let participantsWithError = [];
                results.forEach(result => {
                  if (result.status === 'rejected') {
                    participantsWithError.push(result.reason.userEmail);
                  }
                });
                refreshStudentList();
                if (participantsWithError.length === 0) {
                  UI.success(T.courseStudentAdded);
                  return;
                }
                UI.error(
                  UI.formatString(T.bulkUserAddError, {
                    userEmail: participantsWithError.join('<br>'),
                  }),
                );
              })
              .catch(UI.ignoreError);
          },
          'remove-student': function(student) {
            api.Course.removeStudent({
              course_alias: courseAlias,
              usernameOrEmail: student.username,
            })
              .then(function(data) {
                refreshStudentList();
                UI.success(T.courseStudentRemoved);
              })
              .catch(UI.apiError);
          },
        },
      });
    },
    data: { students: [], data: [] },
    methods: {
      arbitrateRequest: (ev, username, resolution) => {
        api.Course.arbitrateRequest({
          course_alias: courseAlias,
          username: username,
          resolution: resolution,
          note: '',
        })
          .then(response => {
            UI.success(T.successfulOperation);
            refreshStudentList();
          })
          .catch(UI.apiError);
      },
    },
    components: {
      'omegaup-course-addstudents': course_AddStudents,
    },
  });

  var clone = new Vue({
    el: '#clone div',
    render: function(createElement) {
      return createElement('omegaup-course-clone', {
        props: { initialAlias: courseAlias, initialName: this.initialName },
        on: {
          clone: function(ev) {
            api.Course.clone({
              course_alias: courseAlias,
              name: ev.name,
              alias: ev.alias,
              start_time: ev.startTime.getTime() / 1000,
            })
              .then(function(data) {
                UI.success(
                  UI.formatString(T.courseEditCourseClonedSuccessfully, {
                    course_alias: ev.alias,
                  }),
                );
              })
              .catch(UI.apiError);
          },
          cancel: function(ev) {
            window.location = '/course/' + courseAlias + '/';
          },
        },
      });
    },
    data: {
      initialName: '',
    },
    components: {
      'omegaup-course-clone': course_Clone,
    },
  });

  let functionMap = {
    '#assignments': {
      new: onNewAssignment,
    },
  };

  if (vuePath.length >= 2) {
    let section = functionMap[vuePath[0]];
    if (section) {
      let fn = section[vuePath[1]];
      if (fn) {
        Vue.nextTick(function() {
          fn.apply(this, vuePath.slice(2));
        });
      }
    }
  }

  api.Course.adminDetails({ alias: courseAlias })
    .then(function(course) {
      $('.course-header')
        .text(course.name)
        .attr('href', '/course/' + courseAlias + '/');
      details.course = course;
      editAdmissionMode.admissionMode = course.admission_mode;
      editAdmissionMode.shouldShowPublicOption = course.isCurator;
      clone.initialName = course.name;
    })
    .catch(UI.apiError);

  function refreshStudentList() {
    api.Course.listStudents({ course_alias: courseAlias })
      .then(function(data) {
        addStudents.students = data.students;
      })
      .catch(UI.apiError);
    api.Course.requests({ course_alias: courseAlias })
      .then(function(data) {
        addStudents.data = data.users;
      })
      .catch(UI.apiError);
  }

  function refreshAssignmentsList() {
    api.Course.listAssignments({ course_alias: courseAlias })
      .then(function(data) {
        problemList.assignments = data.assignments;
        assignmentList.assignments = data.assignments;
      })
      .catch(UI.apiError);
  }

  function refreshProblemList(assignment) {
    api.Course.assignmentDetails({
      assignment: assignment.alias,
      course: courseAlias,
    })
      .then(function(response) {
        problemList.assignmentProblems = response.problems;
      })
      .catch(UI.apiError);
  }

  function refreshCourseAdmins() {
    api.Course.admins({ course_alias: courseAlias })
      .then(function(data) {
        courseAdmins.initialAdmins = data.admins;
        courseGroupAdmins.initialGroups = data.group_admins;
      })
      .catch(UI.apiError);
  }

  refreshStudentList();
  refreshAssignmentsList();
  refreshCourseAdmins();
});
