import course_ViewProgress from '../components/course/ViewProgress.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.StudentsProgressPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-viewprogress': course_ViewProgress,
    },
    data: () => ({
      completeStudentsProgress: [] as types.StudentProgressInCourse[],
    }),
    async mounted() {
      let nextPage: undefined | number = 1;
      let completeStudentsProgress: types.StudentProgressInCourse[] = [];
      while (nextPage) {
        await api.Course.studentsProgress({
          page: nextPage,
          length: 1,
          course: payload.course.alias,
        })
          .then((response) => {
            completeStudentsProgress = completeStudentsProgress.concat(
              response.progress,
            );
            nextPage = response.nextPage;
          })
          .catch(ui.apiError);
      }
      this.completeStudentsProgress = completeStudentsProgress;
    },
    render: function (createElement) {
      return createElement('omegaup-course-viewprogress', {
        props: {
          course: payload.course,
          students: payload.students,
          assignmentsProblems: payload.assignmentsProblems,
          pagerItems: payload.pagerItems,
          totalRows: payload.totalRows,
          page: payload.page,
          length: payload.length,
          completeStudentsProgress: this.completeStudentsProgress,
        },
      });
    },
  });
});
