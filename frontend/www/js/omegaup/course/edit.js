import course_AddStudents from '../components/course/AddStudents.vue';
import course_Administrators from '../components/course/Administrators.vue';
import course_AssignmentDetails from '../components/course/AssignmentDetails.vue';
import course_AssignmentList from '../components/course/AssignmentList.vue';
import course_Details from '../components/course/Details.vue';
import course_ProblemList from '../components/course/ProblemList.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';
import Sortable from 'sortablejs';

Vue.directive('Sortable', {
  inserted: function(el, binding) { new Sortable(el, binding.value || {}); }
});

OmegaUp.on('ready', function() {
  var original_school = null;
  var original_school_id = null;
  let vuePath = [];
  if (window.location.hash) {
    vuePath = window.location.hash.split('/');
    $('#sections').find('a[href="' + vuePath[0] + '"]').tab('show');
  }

  $('#sections')
      .on('click', 'a', function(e) {
        e.preventDefault();
        // add this line
        window.location.hash = $(this).attr('href');
        $(this).tab('show');
      });

  var courseAlias =
      /\/course\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

  var defaultDate = Date.create(Date.now());
  defaultDate.set({seconds: 0});
  var defaultStartTime = Date.create(defaultDate);
  defaultDate.setHours(defaultDate.getHours() + 5);
  var defaultFinishTime = Date.create(defaultDate);

  function onNewAssignment(assignmentType) {
    assignmentDetails.show = true;
    assignmentDetails.update = false;
    assignmentDetails.assignment = {
      start_time: defaultStartTime,
      finish_time: defaultFinishTime,
      assignment_type: assignmentType,
    };
    // Vue lazily updates the DOM, so any interactions with `$el` need to
    // wait until the update is done.
    Vue.nextTick(function() { assignmentDetails.$el.scrollIntoView(); });
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
          'delete': function(assignment) {
            if (!window.confirm(
                    UI.formatString(T.courseAssignmentConfirmDelete, {
                      assignment: assignment.name,
                    }))) {
              return;
            }
            omegaup.API.Course.removeAssignment({
                                course_alias: courseAlias,
                                assignment_alias: assignment.alias,
                              })
                .then(function(data) {
                  omegaup.UI.success(omegaup.T.courseAssignmentDeleted);
                  refreshAssignmentsList();
                })
                .fail(omegaup.UI.apiError);
          },
          'new': function() {
            assignmentDetails.show = true;
            assignmentDetails.update = false;
            assignmentDetails.assignment = {
              start_time: defaultStartTime,
              finish_time: defaultFinishTime,
            };
          },
          cancel: function(ev) {
            window.location = '/course/' + courseAlias + '/';
          },
          'removeAdmin': function(admin) {
            API.Course.removeAdmin({
                        course_alias: courseAlias,
                        usernameOrEmail: admin.username
                      })
                .then(function(data) {
                  refreshCourseAdmins();
                  UI.success(T.adminRemoved);
                })
                .fail(UI.apiError);
          },
          'removeGroupAdmin': function(group) {
            API.Course.removeGroupAdmin(
                          {course_alias: courseAlias, group: group.alias})
                .then(function(data) {
                  refreshCourseAdmins();
                  UI.success(T.groupAdminRemoved);
                })
                .fail(UI.apiError);
          },
          'add-admin': function(useradmin) {
            omegaup.API.Course.addAdmin({
                                course_alias: courseAlias,
                                usernameOrEmail: useradmin,
                              })
                .then(function(data) {
                  omegaup.UI.success(omegaup.T.adminAdded);
                  refreshCourseAdmins();
                })
                .fail(omegaup.UI.apiError);
          },
          'add-group-admin': function(groupadmin) {
            omegaup.API.Course.addGroupAdmin({
                                course_alias: courseAlias,
                                group: groupadmin,
                              })
                .then(function(data) {
                  omegaup.UI.success(omegaup.T.groupAdminAdded);
                  refreshCourseAdmins();
                })
                .fail(omegaup.UI.apiError);
          }
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
        props: {T: T, assignments: this.assignments, courseAlias: courseAlias},
        on: {
          'edit': function(assignment) {
            assignmentDetails.show = true;
            assignmentDetails.update = true;
            assignmentDetails.assignment = assignment;
            assignmentDetails.$el.scrollIntoView();
          },
          'delete': function(assignment) {
            if (!window.confirm(
                    UI.formatString(T.courseAssignmentConfirmDelete, {
                      assignment: assignment.name,
                    }))) {
              return;
            }
            omegaup.API.Course.removeAssignment({
                                course_alias: courseAlias,
                                assignment_alias: assignment.alias,
                              })
                .then(function(data) {
                  omegaup.UI.success(omegaup.T.courseAssignmentDeleted);
                  refreshAssignmentsList();
                })
                .fail(omegaup.UI.apiError);
          },
          'new': onNewAssignment,
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

  var assignmentDetails = new Vue({
    el: '#assignments div.form',
    render: function(createElement) {
      return createElement('omegaup-course-assignmentdetails', {
        props: {
          T: T,
          show: this.show,
          update: this.update,
          assignment: this.assignment
        },
        on: {
          submit: function(ev) {
            if (ev.update) {
              omegaup.API.Course.updateAssignment({
                                  course: courseAlias,
                                  name: ev.name,
                                  description: ev.description,
                                  start_time: ev.startTime.getTime() / 1000,
                                  finish_time: ev.finishTime.getTime() / 1000,
                                  assignment: ev.alias,
                                  assignment_type: ev.assignmentType,
                                })
                  .then(function(data) {
                    omegaup.UI.success(omegaup.T.courseAssignmentUpdated);
                    refreshAssignmentsList();
                  })
                  .fail(function(error) {
                    omegaup.UI.apiError(error);
                    assignmentDetails.show = true;
                  });
            } else {
              omegaup.API.Course.createAssignment({
                                  course_alias: courseAlias,
                                  name: ev.name,
                                  description: ev.description,
                                  start_time: ev.startTime.getTime() / 1000,
                                  finish_time: ev.finishTime.getTime() / 1000,
                                  alias: ev.alias,
                                  assignment_type: ev.assignmentType,
                                })
                  .then(function(data) {
                    omegaup.UI.success(omegaup.T.courseAssignmentAdded);
                    refreshAssignmentsList();
                  })
                  .fail(function(error) {
                    omegaup.UI.apiError(error);
                    assignmentDetails.show = true;
                  });
            }
            assignmentDetails.show = false;
          },
          cancel: function() { assignmentDetails.show = false; },
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
        props: {T: T, update: true, course: this.course},
        on: {
          submit: function(ev) {
            if (ev.school_id == original_school_id &&
                ev.school_name != original_school) {
              ev.school_id = null;
            }
            API.Course.update({
                        course_alias: courseAlias,
                        name: ev.name,
                        description: ev.description,
                        start_time: ev.startTime.getTime() / 1000,
                        finish_time:
                            new Date(ev.finishTime).setHours(23, 59, 59, 999) /
                                1000,
                        alias: ev.alias,
                        show_scoreboard: ev.showScoreboard,
                        school_id: ev.school_id,
                        school_name: ev.school_name
                      })
                .then(function(data) {
                  UI.success(T.courseEditCourseEdited + ' <a href="/course/' +
                             ev.alias + '">' + T.courseEditGoToCourse + '</a>');
                  $('.course-header')
                      .text(ev.alias)
                      .attr('href', '/course/' + ev.alias + '/');
                  $('div.post.footer').show();
                  window.scrollTo(0, 0);
                })
                .fail(UI.apiError);
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
      'omegaup-course-details': course_Details,
    },
  });

  var problemList = new Vue({
    el: '#problems div',
    render: function(createElement) {
      return createElement('omegaup-course-problemlist', {
        props: {
          T: T,
          assignments: this.assignments,
          assignmentProblems: this.assignmentProblems,
          taggedProblems: this.taggedProblems
        },
        on: {
          'add-problem': function(assignment, problemAlias) {
            omegaup.API.Course.addProblem({
                                course_alias: courseAlias,
                                assignment_alias: assignment.alias,
                                problem_alias: problemAlias,
                              })
                .then(function(data) {
                  refreshProblemList(assignment);
                  problemList.$children[0].showForm = false;
                  omegaup.UI.success(T.courseAssignmentProblemAdded);
                })
                .fail(omegaup.UI.apiError);
          },
          assignment: function(assignment) { refreshProblemList(assignment); },
          remove: function(assignment, problem) {
            if (!window.confirm(
                    UI.formatString(T.courseAssignmentProblemConfirmRemove, {
                      problem: problem.title,
                    }))) {
              return;
            }
            omegaup.API.Course.removeProblem({
                                course_alias: courseAlias,
                                problem_alias: problem.alias,
                                assignment_alias: assignment.alias,
                              })
                .then(function(response) {
                  omegaup.UI.success(T.courseAssignmentProblemRemoved);
                  refreshProblemList(assignment);
                })
                .fail(omegaup.UI.apiError);
          },
          'sort': function(assignment, assignmentProblems) {
            let index = 1;
            for (let problem of assignmentProblems) {
              problem.order = index;
              index++;
            }
            omegaup.API.Course.updateProblemsOrder({
                                course_alias: courseAlias,
                                assignment_alias: assignment.alias,
                                problems: assignmentProblems,
                              })
                .then(function(response) {})
                .fail(omegaup.UI.apiError);
          },
          tags: function(tags) {
            omegaup.API.Problem.list({tag: tags})
                .then(function(data) {
                  problemList.taggedProblems = data.results;
                })
                .fail(omegaup.UI.apiError);
          },
        },
      });
    },
    data: {
      assignments: [],
      assignmentProblems: [],
      taggedProblems: [],
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
          T: T,
          students: this.students,
          courseAlias: courseAlias,
        },
        on: {
          'add-student': function(username) {
            API.Course.addStudent({
                        course_alias: courseAlias,
                        usernameOrEmail: username,
                      })
                .then(function(data) {
                  refreshStudentList();
                  UI.success(T.courseStudentAdded);
                  addStudents.$children[0].reset();
                })
                .fail(UI.apiError);
          },
          cancel: function(ev) {
            window.location = '/course/' + courseAlias + '/';
          },
          remove: function(student) {
            API.Course.removeStudent({
                        course_alias: courseAlias,
                        usernameOrEmail: student.username
                      })
                .then(function(data) {
                  refreshStudentList();
                  UI.success(T.courseStudentRemoved);
                })
                .fail(UI.apiError);
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

  let functionMap = {
    '#assignments': {
      'new': onNewAssignment,
    },
  };

  if (vuePath.length >= 2) {
    let section = functionMap[vuePath[0]];
    if (section) {
      let fn = section[vuePath[1]];
      if (fn) {
        Vue.nextTick(function() { fn.apply(this, vuePath.slice(2)); });
      }
    }
  }

  API.Course.adminDetails({alias: courseAlias})
      .then(function(course) {
        $('.course-header')
            .text(course.name)
            .attr('href', '/course/' + courseAlias + '/');
        details.course = course;
        original_school = course.school_name;
        original_school_id = course.school_id;
      })
      .fail(UI.apiError);

  function refreshStudentList() {
    API.Course.listStudents({course_alias: courseAlias})
        .then(function(data) { addStudents.students = data.students; })
        .fail(UI.apiError);
  }

  function refreshAssignmentsList() {
    API.Course.listAssignments({course_alias: courseAlias})
        .then(function(data) {
          problemList.assignments = data.assignments;
          assignmentList.assignments = data.assignments;
        })
        .fail(UI.apiError);
  }

  function refreshProblemList(assignment) {
    omegaup.API.Course.getAssignment(
                          {assignment: assignment.alias, course: courseAlias})
        .then(function(response) {
          problemList.assignmentProblems = response.problems;
        })
        .fail(omegaup.UI.apiError);
  }

  function refreshCourseAdmins() {
    omegaup.API.Course.admins({course_alias: courseAlias})
        .then(function(data) {
          administrators.admins = data.admins;
          administrators.groupadmins = data.group_admins;
        })
        .fail(UI.apiError);
  }

  refreshStudentList();
  refreshAssignmentsList();
  refreshCourseAdmins();
});
