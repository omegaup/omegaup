import Vue from 'vue';
import course_Clone from '../components/course/CloneWithToken.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseCloneDetailsPayload();
  new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-course-clone', {
        props: {
          username: payload.creator.username,
          classname: payload.creator.classname,
          course: payload.details,
          token: payload.token,
        },
        on: {
          clone: (
            alias: string,
            name: string,
            token: string,
            startTime: Date,
          ) => {
            api.Course.clone({
              course_alias: payload.details.alias,
              name: name,
              alias: alias,
              token: token,
              start_time: startTime.getTime() / 1000,
            })
              .then((data) => {
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
    components: {
      'omegaup-course-clone': course_Clone,
    },
  });
});
