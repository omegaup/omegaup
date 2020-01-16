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
        },
      });
    },
    data: {
      adminCoursesCurrent: [],
      adminCoursesPast: [],
      studentCoursesCurrent: [],
      studentCoursesPast: [],
    },
    components: {
      'omegaup-course-list': course_List,
    },
  });

  API.Course.listCourses()
    .then(function(data) {
      for (let course of data.admin) {
        if (course.finish_time.getTime() > Date.now()) {
          courseList.adminCoursesCurrent.push(course);
        } else {
          courseList.adminCoursesPast.push(course);
        }
      }

      for (let course of data.student) {
        if (course.finish_time.getTime() > Date.now()) {
          courseList.studentCoursesCurrent.push(course);
        } else {
          courseList.studentCoursesPast.push(course);
        }
      }

      // Enable the first visible tab.
      let tabs = $('.nav-link');
      if (tabs.length > 0) {
        $(tabs[0]).trigger('click');
      }
      $('.tab-container').show();
    })
    .fail(omegaup.UI.apiError);
});
