import course_List from '../components/course/List.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import T from '../lang';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CourseListPayload();
  const headerPayload = types.payloadParsers.CommonPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-cards-list': course_List,
    },
    render: function (createElement) {
      return createElement('omegaup-course-cards-list', {
        props: {
          courses: payload.courses,
          loggedIn: headerPayload.isLoggedIn,
          currentUsername: headerPayload.currentUsername,
        },
        on: {
          clone: (
            originalCourseAlias: string,
            alias: string,
            name: string,
            startTime: Date,
          ) => {
            api.Course.clone({
              course_alias: originalCourseAlias,
              name: name,
              alias: alias,
              start_time: startTime.getTime() / 1000,
            })
              .then(() => {
                ui.success(
                  ui.formatString(T.courseEditCourseClonedSuccessfully, {
                    course_alias: alias,
                  }),
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
