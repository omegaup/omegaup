import course_List from '../components/course/List.vue';
import { API, UI, OmegaUp, T } from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let courseList = new Vue({
    el: '#course-list',
    render: function(createElement) {
      return createElement('omegaup-course-list', {
        props: {
          courses: this.courses,
          initialActiveTabStudent: this.initialActiveTabStudent,
          initialActiveTabAdmin: this.initialActiveTabAdmin,
        },
      });
    },
    data: {
      courses: [],
      initialActiveTabStudent: '',
      initialActiveTabAdmin: '',
    },
    components: {
      'omegaup-course-list': course_List,
    },
  });

  API.Course.listCourses()
    .then(function(data) {
      const allCourses = [
        {
          type: 'student',
          filteredCourses: [
            { type: 'current', courses: [] },
            { type: 'past', courses: [] },
          ],
          name: T.courseList,
          activeTab: '',
        },
        {
          type: 'admin',
          filteredCourses: [
            { type: 'current', courses: [] },
            { type: 'past', courses: [] },
          ],
          name: T.courseListAdminCourses,
          activeTab: '',
        },
      ];

      for (const [index, typed] of allCourses.entries()) {
        let activeTab = '';
        for (const course of data[typed.type]) {
          if (course.finish_time.getTime() > Date.now()) {
            allCourses[index].filteredCourses[0].courses.push(course);
            continue;
          }
          allCourses[index].filteredCourses[1].courses.push(course);
        }
        for (const filtered of typed.filteredCourses) {
          if (filtered.courses.length > 0) {
            activeTab = `${typed.type}-courses-${filtered.type}`;
            break;
          }
        }
        allCourses[index].activeTab = activeTab;
        if (index === 0) {
          // Student
          courseList.initialActiveTabStudent = activeTab;
        } else {
          // Admin
          courseList.initialActiveTabAdmin = activeTab;
        }
      }
      courseList.courses = allCourses;
    })
    .fail(omegaup.UI.apiError);
});
