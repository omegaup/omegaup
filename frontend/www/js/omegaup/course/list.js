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
        PUBLIC: 2,
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
            {
              timeType: 'public',
              courses: [],
              tabName: T.courseListPublicCourses,
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
            {
              timeType: 'public',
              courses: [],
              tabName: T.courseListPublicCourses,
            },
          ],
          description: T.courseListAdminCourses,
          activeTab: '',
        },
      ];

      const currentDate = Date.now();
      for (const courseDescription of allCourses) {
        let activeTab = '';
        for (const course of data[courseDescription.accessMode]) {
          if (course.public) {
            courseDescription.filteredCourses[timeTypes.PUBLIC].courses.push(
              course,
            );
          } else if (
            !course.finish_time ||
            course.finish_time.getTime() > currentDate
          ) {
            courseDescription.filteredCourses[timeTypes.CURRENT].courses.push(
              course,
            );
          } else {
            courseDescription.filteredCourses[timeTypes.PAST].courses.push(
              course,
            );
          }
        }
        for (const filtered of courseDescription.filteredCourses) {
          if (filtered.courses.length > 0) {
            activeTab = filtered.timeType;
            break;
          }
        }
        courseDescription.activeTab = activeTab;
      }
      courseList.courses = allCourses;
    })
    .fail(omegaup.UI.apiError);
});
