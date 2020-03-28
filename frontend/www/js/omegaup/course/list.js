import course_List from '../components/course/List.vue';
import { OmegaUp } from '../omegaup';
import API from '../api.js';
import UI from '../ui.js';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const headerPayload = JSON.parse(
    document.getElementById('header-payload').innerText,
  );
  let courseList = new Vue({
    el: '#course-list',
    render: function(createElement) {
      return createElement('omegaup-course-list', {
        props: {
          courses: this.courses,
          isMainUserIdentity: headerPayload && headerPayload.isMainUserIdentity,
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
        PUBLIC: 0,
        STUDENT: 1,
        ADMIN: 2,
      };
      const allCourses = [
        {
          accessMode: 'public',
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
          description: T.courseListPublicCourses,
          activeTab: '',
        },
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
          description: T.courseListIStudy,
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
      for (const courseDescription of allCourses) {
        let activeTab = '';
        for (const course of data[courseDescription.accessMode]) {
          if (
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
    .catch(omegaup.UI.apiError);
});
