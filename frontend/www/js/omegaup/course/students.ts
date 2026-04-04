import course_ViewProgress from '../components/course/ViewProgress.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import * as api from '../api';
import * as ui from '../ui';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.StudentsProgressPayload();

  const ViewProgress = new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-viewprogress': course_ViewProgress,
    },
    data: () => ({
      completeStudentsProgress: null as types.StudentProgressInCourse[] | null,
    }),
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

  const getCSV = async () => {
    let nextPage: undefined | number = 1;
    let completeStudentsProgress: types.StudentProgressInCourse[] = [];
    while (nextPage) {
      try {
        const response: {
          nextPage?: number;
          progress: types.StudentProgressInCourse[];
        } = await api.Course.studentsProgress({
          page: nextPage,
          length: 100,
          course: payload.course.alias,
        });
        completeStudentsProgress = completeStudentsProgress.concat(
          response.progress,
        );
        nextPage = response.nextPage;
      } catch (e: any) {
        ui.apiError(e);
        break;
      }
    }
    ViewProgress.completeStudentsProgress = completeStudentsProgress;
  };

  getCSV();
});
