import course_AddStudents from '../components/course/AddStudents.vue';
import course_Administrators from '../components/course/Administrators.vue';
import course_AssignmentDetails from '../components/course/AssignmentDetails.vue';
import course_AssignmentList from '../components/course/AssignmentList.vue';
import course_Form from '../components/course/Form.vue';
import course_ProblemList from '../components/course/ProblemList.vue';
import course_Clone from '../components/course/Clone.vue';
import { OmegaUp } from '../omegaup';
import API from '../api.js';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';
import Sortable from 'sortablejs';

Vue.directive('Sortable', {
  inserted: function(el, binding) {
    new Sortable(el, binding.value || {});
  },
});

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

  var administrators = new Vue({
    el: '#admins div',
    render: function(createElement) {
      return createElement('omegaup-course-administrators', {
        props: {
          admins: this.admins,
          groupadmins: this.groupadmins,
        },
        on: {
          edit: function(assignment) {
            assignmentDetails.show = true;
            assignmentDetails.update = true;
            assignmentDetails.assignment = assignment;
            assignmentDetails.$el.scrollIntoView();
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
            API.Course.removeAssignment({
              course_alias: courseAlias,
              assignment_alias: assignment.alias,
            })
              .then(function(data) {
                UI.success(T.courseAssignmentDeleted);
                refreshAssignmentsList();
              })
              .catch(UI.apiError);
          },
          new: function() {
            assignmentDetails.show = true;
            assignmentDetails.update = false;
            assignmentDetails.assignment = {
              start_time: defaultStartTime,
              finish_time: defaultFinishTime,
            };
          },
          removeAdmin: function(admin) {
            API.Course.removeAdmin({
              course_alias: courseAlias,
              usernameOrEmail: admin.username,
            })
              .then(function(data) {
                refreshCourseAdmins();
                UI.success(T.adminRemoved);
              })
              .catch(UI.apiError);
          },
          removeGroupAdmin: function(group) {
            API.Course.removeGroupAdmin({
              course_alias: courseAlias,
              group: group.alias,
            })
              .then(function(data) {
                refreshCourseAdmins();
                UI.success(T.groupAdminRemoved);
              })
              .catch(UI.apiError);
          },
          'add-admin': function(useradmin) {
            API.Course.addAdmin({
              course_alias: courseAlias,
              usernameOrEmail: useradmin,
            })
              .then(function(data) {
                UI.success(T.adminAdded);
                refreshCourseAdmins();
              })
              .catch(UI.apiError);
          },
          'add-group-admin': function(groupadmin) {
            API.Course.addGroupAdmin({
              course_alias: courseAlias,
              group: groupadmin,
            })
              .then(function(data) {
                UI.success(T.groupAdminAdded);
                refreshCourseAdmins();
              })
              .catch(UI.apiError);
          },
        },
      });
    },
    data: {
      admins: [],
      groupadmins: [],
    },
    components: {
      'omegaup-course-administrators': course_Administrators,
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
            API.Course.removeAssignment({
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
          'sort-homeworks': function(courseAlias, homeworks) {
            let index = 1;
            for (let homework of homeworks) {
              homework.order = index++;
            }
            API.Course.updateAssignmentsOrder({
              course_alias: courseAlias,
              assignments: homeworks,
            }).catch(UI.apiError);
          },
          'sort-tests': function(courseAlias, tests) {
            let index = 1;
            for (let test of tests) {
              test.order = index++;
            }
            API.Course.updateAssignmentsOrder({
              course_alias: courseAlias,
              assignments: tests,
            })
              .then(function(response) {})
              .catch(UI.apiError);
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

              API.Course.updateAssignment(params)
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

              API.Course.createAssignment(params)
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
                API.School.create({ name: ev.school_name })
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

                API.Course.update(params)
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
            API.Course.addProblem({
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
            API.Course.removeProblem({
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
          sort: function(assignment, assignmentProblems) {
            let index = 1;
            for (let problem of assignmentProblems) {
              problem.order = index;
              index++;
            }
            API.Course.updateProblemsOrder({
              course_alias: courseAlias,
              assignment_alias: assignment.alias,
              problems: assignmentProblems,
            })
              .then(function(response) {})
              .catch(UI.apiError);
          },
          tags: function(tags) {
            API.Problem.list({ tag: tags.join() })
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

  var addStudents = new Vue({
    el: '#students div',
    render: function(createElement) {
      return createElement('omegaup-course-addstudents', {
        props: {
          students: this.students,
          courseAlias: courseAlias,
        },
        on: {
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
                API.Course.addStudent({
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
            API.Course.removeStudent({
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
    data: {
      students: [],
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
            API.Course.clone({
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

  API.Course.adminDetails({ alias: courseAlias })
    .then(function(course) {
      $('.course-header')
        .text(course.name)
        .attr('href', '/course/' + courseAlias + '/');
      details.course = course;
      clone.initialName = course.name;
    })
    .catch(UI.apiError);

  function refreshStudentList() {
    API.Course.listStudents({ course_alias: courseAlias })
      .then(function(data) {
        addStudents.students = data.students;
      })
      .catch(UI.apiError);
  }

  function refreshAssignmentsList() {
    API.Course.listAssignments({ course_alias: courseAlias })
      .then(function(data) {
        problemList.assignments = data.assignments;
        assignmentList.assignments = data.assignments;
      })
      .catch(UI.apiError);
  }

  function refreshProblemList(assignment) {
    API.Course.getAssignment({
      assignment: assignment.alias,
      course: courseAlias,
    })
      .then(function(response) {
        problemList.assignmentProblems = response.problems;
      })
      .catch(UI.apiError);
  }

  function refreshCourseAdmins() {
    API.Course.admins({ course_alias: courseAlias })
      .then(function(data) {
        administrators.admins = data.admins;
        administrators.groupadmins = data.group_admins;
      })
      .catch(UI.apiError);
  }

  refreshStudentList();
  refreshAssignmentsList();
  refreshCourseAdmins();
});
