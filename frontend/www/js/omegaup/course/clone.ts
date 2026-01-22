import Vue from 'vue';
import course_Clone from '../components/course/CloneWithToken.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseCloneDetailsPayload();
  const headerPayload = types.payloadParsers.CommonPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-clone': course_Clone,
    },
    render: function (createElement) {
      return createElement('omegaup-course-clone', {
        props: {
          username: payload.creator.username,
          classname: payload.creator.classname,
          course: payload.details,
          token: payload.token,
          currentUsername: headerPayload.currentUsername,
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
              .then(() => {
                ui.success(
                  `${T.courseEditCourseCloned} [${T.courseEdit}](/course/${alias}/edit/)`,
                  /*autoHide=*/ false,
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
