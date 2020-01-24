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
        },
      });
    },
    data: {
      courses: [],
    },
    components: {
      'omegaup-course-list': course_List,
    },
  });

  API.Course.listCourses()
    .then(function(data) {
      const timeTypes = {
        CURRENT: 0,
        PAST: 1,
      };
      const accessModes = {
        STUDENT: 0,
        ADMIN: 1,
      };
      const allCourses = [
        {
          accessMode: 'student',
          filteredCourses: [
            {
              timeType: 'current',
              courses: [],
              tabName: T.courseListCurrentCourses,
            },
            {
              timeType: 'past',
              courses: [],
              tabName: T.courseListPastCourses,
            },
          ],
          description: T.courseList,
          activeTab: '',
        },
        {
          accessMode: 'admin',
          filteredCourses: [
            {
              timeType: 'current',
              courses: [],
              tabName: T.courseListCurrentCourses,
            },
            {
              timeType: 'past',
              courses: [],
              tabName: T.courseListPastCourses,
            },
          ],
          description: T.courseListAdminCourses,
          activeTab: '',
        },
      ];

      const currentDate = Date.now();
      for (const [index, course] of allCourses.entries()) {
        let activeTab = '';
        for (const course of data[course.accessMode]) {
          if (
            !course.finish_time ||
            course.finish_time.getTime() > currentDate
          ) {
            allCourses[index].filteredCourses[timeTypes.CURRENT].courses.push(
              course,
            );
            continue;
          }
          allCourses[index].filteredCourses[timeTypes.PAST].courses.push(
            course,
          );
        }
        for (const filtered of course.filteredCourses) {
          if (filtered.courses.length > 0) {
            activeTab = filtered.timeType;
            break;
          }
        }
        allCourses[index].activeTab = activeTab;
      }
      courseList.courses = allCourses;
    })
    .fail(omegaup.UI.apiError);
});
