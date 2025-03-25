import course_Statistics from '../components/course/Statistics.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.CourseStatisticsPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-statistics': course_Statistics,
    },
    render: function (createElement) {
      return createElement('omegaup-course-statistics', {
        props: {
          T: T,
          course: payload.course,
          problemStats: payload.problemStats,
          verdicts: payload.verdicts,
        },
      });
    },
  });
});
