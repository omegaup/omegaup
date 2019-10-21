import course_Details from '../components/course/Details.vue';
import { API, UI, OmegaUp, T } from '../omegaup.js';
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
            var schoolIdDeferred = $.Deferred();
            if (ev.school_id) {
              schoolIdDeferred.resolve(ev.school_id);
            } else if (ev.school_name) {
              API.School.create({ name: ev.school_name })
                .then(function(data) {
                  schoolIdDeferred.resolve(data.school_id);
                })
                .fail(UI.apiError);
            } else {
              schoolIdDeferred.resolve(null);
            }
            schoolIdDeferred
              .then(function(school_id) {
                API.Course.create({
                  alias: ev.alias,
                  name: ev.name,
                  description: ev.description,
                  start_time: ev.startTime.getTime() / 1000,
                  finish_time:
                    new Date(ev.finishTime).setHours(23, 59, 59, 999) / 1000,
                  show_scoreboard: ev.showScoreboard,
                  needs_basic_information: ev.basic_information_required,
                  requests_user_information: ev.requests_user_information,
                  school_id: school_id,
                })
                  .then(function(data) {
                    window.location.replace(
                      '/course/' + ev.alias + '/edit/#assignments',
                    );
                  })
                  .fail(UI.apiError);
              })
              .fail(UI.apiError);
          },
          cancel: function(ev) {
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
      'omegaup-course-details': course_Details,
    },
  });
});
