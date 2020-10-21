import Vue from 'vue';
import course_Details from '../components/course/Details.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseDetailsPayload();
  const headerPayload = types.payloadParsers.CommonPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-details': course_Details,
    },
    render: function (createElement) {
      return createElement('omegaup-course-details', {
        props: {
          course: payload.details,
          progress: payload.progress,
          loggedIn: headerPayload.isLoggedIn,
          currentUsername: headerPayload.currentUsername,
        },
        on: {
          clone: (alias: string, name: string, startTime: Date) => {
            api.Course.clone({
              course_alias: payload.details.alias,
              name: name,
              alias: alias,
              start_time: startTime.getTime() / 1000,
            })
              .then(() => {
                ui.success(
                  ui.formatString(T.courseEditCourseClonedSuccessfully, {
                    course_alias: alias,
                  }),
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
