import course_Form from '../components/course/Form.vue';
import { omegaup, OmegaUp } from '../omegaup';
import { messages, types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const now = new Date();
  const finishTime = new Date();
  finishTime.setDate(finishTime.getDate() + 30);
  const defaultStartTime = now;
  const defaultFinishTime = finishTime;
  const payload = types.payloadParsers.CourseNewPayload();
  const details = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-course-form', {
        props: {
          course: {
            alias: '',
            description: '',
            start_time: defaultStartTime,
            finish_time: defaultFinishTime,
            show_scoreboard: false,
            name: '',
            school_name: '',
            needs_basic_information: false,
            requests_user_information: 'no',
            is_curator: payload.is_curator,
            is_admin: payload.is_admin,
          },
          invalidParameterName: this.invalidParameterName,
        },
        on: {
          submit: (source: course_Form) => {
            new Promise<number | null>((accept, reject) => {
              if (source.school_id !== undefined) {
                accept(source.school_id);
              } else if (source.school_name) {
                api.School.create({ name: source.school_name })
                  .then((data) => {
                    accept(data.school_id);
                  })
                  .catch(ui.apiError);
              } else {
                accept(null);
              }
            })
              .then((schoolId) => {
                const params: messages.CourseCreateRequest = {
                  alias: source.alias,
                  name: source.name,
                  description: source.description,
                  start_time: source.startTime,
                  show_scoreboard: source.showScoreboard,
                  needs_basic_information: source.needsBasicInformation,
                  requests_user_information: source.requests_user_information,
                  school_id: schoolId ?? undefined,
                  unlimited_duration: source.unlimitedDuration,
                  finish_time: !source.unlimitedDuration
                    ? source.finishTime
                    : null,
                };

                api.Course.create(params)
                  .then(() => {
                    this.invalidParameterName = '';
                    window.location.replace(
                      `/course/${source.alias}/edit/#content`,
                    );
                  })
                  .catch((error) => {
                    ui.apiError(error);
                    this.invalidParameterName = error.parameter || '';
                  });
              })
              .catch(ui.apiError);
          },
          cancel: () => {
            window.location.href = '/course/';
          },
        },
      });
    },
    data: () => ({
      invalidParameterName: '',
    }),
    components: {
      'omegaup-course-form': course_Form,
    },
  });
});
