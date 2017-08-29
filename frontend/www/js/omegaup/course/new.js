import course_Details from '../components/course/Details.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var defaultDate = Date.create(Date.now());
  defaultDate.set({seconds: 0});
  var defaultStartTime = Date.create(defaultDate);
  defaultDate.setDate(defaultDate.getDate() + 30);
  var defaultFinishTime = Date.create(defaultDate);

  var details = new Vue({
    el: '#course-details',
    render: function(createElement) {
      return createElement('omegaup-course-details', {
        props: {T: T, update: false, course: this.course},
        on: {
          submit: function(ev) {
            API.Course.create({
                        alias: ev.alias,
                        name: ev.name,
                        description: ev.description,
                        start_time: ev.startTime.getTime() / 1000,
                        finish_time:
                            new Date(ev.finishTime).setHours(23, 59, 59, 999) /
                                1000,
                        alias: ev.alias,
                        show_scoreboard: ev.showScoreboard,
                      })
                .then(function(data) {
                  window.location.replace('/course/' + ev.alias +
                                          '/edit/#assignments');
                })
                .fail(UI.apiError);
          },
          cancel: function(ev) { window.location = '/course/'; },
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
      'omegaup-course-details': course_Details,
    },
  });
});
