import course_Statistics from '../components/course/Statistics.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import { numberFormat } from 'highcharts';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.CourseStatisticsPayload();

  const viewProgress = new Vue({
    el: '#main-container',
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
    components: {
      'omegaup-course-statistics': course_Statistics,
    },
  });
});
