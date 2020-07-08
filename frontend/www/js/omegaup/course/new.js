import course_Form from '../components/course/Form.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  var defaultStartTime = new Date();
  defaultStartTime.setSeconds(0);
  var defaultFinishTime = new Date(defaultStartTime);
  defaultFinishTime.setDate(defaultFinishTime.getDate() + 30);

  var details = new Vue({
    el: '#course-details',
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
            basic_information_required: false,
            requests_user_information: 'no',
          },
        },
        on: {
          submit: function (ev) {
            new Promise((accept, reject) => {
              if (ev.school_id) {
                accept(ev.school_id);
              } else if (ev.school_name) {
                api.School.create({ name: ev.school_name })
                  .then((data) => {
                    accept(data.school_id);
                  })
                  .catch(UI.apiError);
              } else {
                accept(null);
              }
            })
              .then(function (school_id) {
                const params = {
                  alias: ev.alias,
                  name: ev.name,
                  description: ev.description,
                  start_time: ev.startTime.getTime() / 1000,
                  show_scoreboard: ev.showScoreboard,
                  needs_basic_information: ev.basic_information_required,
                  requests_user_information: ev.requests_user_information,
                  school_id: school_id,
                };

                if (ev.unlimitedDuration) {
                  params.unlimited_duration = true;
                } else {
                  params.finish_time = ev.finishTime.getTime() / 1000;
                }

                api.Course.create(params)
                  .then(() => {
                    window.location.replace(
                      '/course/' + ev.alias + '/edit/#assignments',
                    );
                  })
                  .catch(UI.apiError);
              })
              .catch(UI.apiError);
          },
          cancel: function () {
            window.location = '/course/';
          },
        },
      });
    },
    components: {
      'omegaup-course-form': course_Form,
    },
  });
});
