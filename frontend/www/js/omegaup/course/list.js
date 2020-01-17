import course_List from '../components/course/List.vue';
import { API, UI, OmegaUp, T } from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let courseList = new Vue({
    el: '#course-list',
    render: function(createElement) {
      return createElement('omegaup-course-list', {
        props: {
          adminCoursesCurrent: this.adminCoursesCurrent,
          adminCoursesPast: this.adminCoursesPast,
          studentCoursesCurrent: this.studentCoursesCurrent,
          studentCoursesPast: this.studentCoursesPast,
          initialActiveTabStudent: this.initialActiveTabStudent,
          initialActiveTabAdmin: this.initialActiveTabAdmin,
        },
      });
    },
    data: {
      adminCoursesCurrent: [],
      adminCoursesPast: [],
      studentCoursesCurrent: [],
      studentCoursesPast: [],
      initialActiveTabStudent: '',
      initialActiveTabAdmin: '',
    },
    components: {
      'omegaup-course-list': course_List,
    },
  });

  API.Course.listCourses()
    .then(function(data) {
      let activeTabAdmin = '';
      for (let course of data.admin) {
        if (course.finish_time.getTime() > Date.now()) {
          courseList.adminCoursesCurrent.push(course);
        } else {
          courseList.adminCoursesPast.push(course);
        }
      }
      if (courseList.adminCoursesCurrent.length > 0) {
        activeTabAdmin = 'admin-courses-current';
      } else if (courseList.adminCoursesPast.length > 0) {
        activeTabAdmin = 'admin-courses-past';
      }
      courseList.initialActiveTabAdmin = activeTabAdmin;

      let activeTabStudent = '';
      for (let course of data.student) {
        if (course.finish_time.getTime() > Date.now()) {
          courseList.studentCoursesCurrent.push(course);
        } else {
          courseList.studentCoursesPast.push(course);
        }
      }
      if (courseList.studentCoursesCurrent.length > 0) {
        activeTabStudent = 'student-courses-current';
      } else if (courseList.studentCoursesPast.length > 0) {
        activeTabStudent = 'student-courses-past';
      }
      courseList.initialActiveTabStudent = activeTabStudent;
    })
    .fail(omegaup.UI.apiError);
});
