import course_ViewProgress from '../components/course/ViewProgress.vue';
import course_StudentProgress from '../components/course/StudentProgress.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import { data } from 'jquery';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.StudentsProgressPayload();

  //what to do for student progress component?
  const viewProgress = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-course-viewprogress', {
        props: {
          T: T,
          course: payload.course,
          students: payload.students,
          assignments: payload.course.assignments,
        },
      });
    },
    components: {
      'omegaup-course-viewprogress': course_ViewProgress,
    }
  });
});
