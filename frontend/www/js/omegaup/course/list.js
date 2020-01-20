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
      const courseTypes = {
        CURRENT: 0,
        PAST: 1,
      };
      const courseMode = {
        STUDENT: 0,
        ADMIN: 1,
      };
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
          if (
            !course.finish_time ||
            course.finish_time.getTime() > Date.now()
          ) {
            allCourses[index].filteredCourses[courseTypes.CURRENT].courses.push(
              course,
            );
            continue;
          }
          allCourses[index].filteredCourses[courseTypes.PAST].courses.push(
            course,
          );
        }
        for (const filtered of typed.filteredCourses) {
          if (filtered.courses.length > 0) {
            activeTab = `${typed.type}-courses-${filtered.type}`;
            break;
          }
        }
        allCourses[index].activeTab = activeTab;
        if (index === courseMode.STUDENT) {
          courseList.initialActiveTabStudent = activeTab;
        } else if (index === courseMode.ADMIN) {
          courseList.initialActiveTabAdmin = activeTab;
        }
      }
      courseList.courses = allCourses;
    })
    .fail(omegaup.UI.apiError);
});
