import course_Form from '../components/course/Form.vue';
import { OmegaUp } from '../omegaup';
import API from '../api.js';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var defaultDate = Date.create(Date.now());
  defaultDate.set({ seconds: 0 });
  var defaultStartTime = Date.create(defaultDate);
  defaultDate.setDate(defaultDate.getDate() + 30);
  var defaultFinishTime = Date.create(defaultDate);

  var details = new Vue({
    el: '#course-details',
    render: function(createElement) {
      return createElement('omegaup-course-details', {
        props: { T: T, update: false, course: this.course },
        on: {
          submit: function(ev) {
            new Promise((accept, reject) => {
              if (ev.school_id) {
                accept(ev.school_id);
              } else if (ev.school_name) {
                API.School.create({ name: ev.school_name })
                  .then(data => {
                    accept(data.school_id);
                  })
                  .catch(UI.apiError);
              } else {
                accept(null);
              }
            })
              .then(function(school_id) {
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

                API.Course.create(params)
                  .then(() => {
                    window.location.replace(
                      '/course/' + ev.alias + '/edit/#assignments',
                    );
                  })
                  .catch(UI.apiError);
              })
              .catch(UI.apiError);
          },
          cancel: function() {
            window.location = '/course/';
          },
        },
      });
    },
    data: {
      course: {
        start_time: defaultStartTime,
        finish_time: defaultFinishTime,
      },
    },
    components: {
      'omegaup-course-details': course_Form,
    },
  });
});
