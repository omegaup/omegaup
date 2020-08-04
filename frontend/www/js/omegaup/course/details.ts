import Vue from 'vue';
import course_Details from '../components/course/Details.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseDetailsPayload();
  const courseDetails = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-course-details', {
        props: {
          course: payload.details,
          progress: payload.progress,
        },
        on: {
          clone: (alias: string, name: string, startTime: Date) => {
            api.Course.clone({
              course_alias: payload.details.alias,
              name: name,
              alias: alias,
              start_time: startTime.getTime() / 1000,
            })
              .then((data) => {
                ui.success(
                  ui.formatString(T.courseEditCourseClonedSuccessfully, {
                    course_alias: alias,
                  }),
                );
                component.showCloneForm = false;
              })
              .catch(ui.apiError);
          },
        },
        ref: 'component',
      });
    },
    components: {
      'omegaup-course-details': course_Details,
    },
  });
  const component = <course_Details>courseDetails.$refs.component;
});
