import course_Form from '../components/course/Form.vue';
import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
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
          },
        },
        on: {
          submit: (ev: course_Form) => {
            new Promise((accept, reject) => {
              if (ev.school_id) {
                accept(ev.school_id);
              } else if (ev.school_name) {
                api.School.create({ name: ev.school_name })
                  .then((data) => {
                    accept(data.school_id);
                  })
                  .catch(ui.apiError);
              } else {
                accept(null);
              }
            })
              .then((schoolId: any) => {
                const params: types.CourseDetails = {
                  alias: ev.alias,
                  name: ev.name,
                  description: ev.description,
                  start_time: ev.startTime,
                  show_scoreboard: ev.showScoreboard,
                  needs_basic_information: ev.needs_basic_information,
                  requests_user_information: ev.requests_user_information,
                  admission_mode: omegaup.AdmissionMode.Private,
                  assignments: [],
                  school_id: schoolId,
                };

                if (ev.unlimitedDuration) {
                  params.unlimited_duration = true;
                } else {
                  params.finish_time = ev.finishTime;
                }

                api.Course.create(params)
                  .then(() => {
                    window.location.replace(
                      '/course/' + ev.alias + '/edit/#assignments',
                    );
                  })
                  .catch(ui.apiError);
              })
              .catch(ui.apiError);
          },
          cancel: () => {
            window.location.href = '/course/';
          },
        },
      });
    },
    components: {
      'omegaup-course-form': course_Form,
    },
  });
});
