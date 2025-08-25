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
  let detailsComponent: any = null;

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-details': course_Details,
    },
    mounted() {
      detailsComponent = this.$refs.detailsComponent;
    },
    render: function (createElement) {
      return createElement('omegaup-course-details', {
        props: {
          course: payload.details,
          progress: payload.progress,
          currentUsername: headerPayload.currentUsername,
        },
        on: {
          'toggle-teaching-assistant': (request: { course_alias: string }) => {
            api.Course.toggleTeachingAssistant(request)
              .then((response) => {
                if (detailsComponent) {
                  detailsComponent.updateTeachingAssistantStatus(
                    response.teaching_assistant_enabled,
                  );
                }
                ui.success(
                  response.teaching_assistant_enabled
                    ? T.wordsEnableAITASuccess
                    : T.wordsDisableAITASuccess,
                );
              })
              .catch(() => {
                if (detailsComponent) {
                  detailsComponent.onToggleError();
                }
                ui.error(T.unableToUpdateTAEnabledField);
              });
          },
        },
        ref: 'detailsComponent',
      });
    },
  });
});
