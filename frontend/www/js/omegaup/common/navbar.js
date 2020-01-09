import common_GraderStatus from '../components/common/GraderStatus.vue';
import common_GraderBadge from '../components/common/GraderBadge.vue';
import { API, UI, OmegaUp, T } from '../omegaup.js';
import omegaup from '../api.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let commonNavbarGraderStatus = new Vue({
    el: '#common-grader-status',
    render: function(createElement) {
      return createElement('omegaup-common-grader-status', {
        props: {
          status: this.status,
          error: this.error,
          graderInfo: this.graderInfo,
        },
      });
    },
    data: {
      status: 'ok',
      error: null,
      graderInfo: null,
    },
    components: {
      'omegaup-common-grader-status': common_GraderStatus,
    },
  });

  let commonNavbarGraderBadge = new Vue({
    el: '#common-grader-count-badge',
    render: function(createElement) {
      return createElement('omegaup-common-grader-count-badge', {
        props: {
          queueLength: this.queueLength,
          error: this.error,
        },
      });
    },
    data: {
      queueLength: -1,
      error: false,
    },
    components: {
      'omegaup-common-grader-count-badge': common_GraderBadge,
    },
  });

  function updateGraderStatus() {
    API.Grader.status()
      .then(function(stats) {
        commonNavbarGraderStatus.graderInfo = stats.grader;
        if (commonNavbarGraderStatus.graderInfo.status !== 'ok') {
          commonNavbarGraderStatus.status = 'down';
          return;
        }
        if (stats.grader.queue) {
          commonNavbarGraderBadge.queueLength =
            stats.grader.queue.run_queue_length +
            stats.grader.queue.running.length;
        }
        commonNavbarGraderStatus.status = 'ok';
        commonNavbarGraderStatus.error = null;
        commonNavbarGraderBadge.error = false;
      })
      .fail(function(stats) {
        commonNavbarGraderStatus.status = 'down';
        commonNavbarGraderStatus.error = stats.error;
        commonNavbarGraderBadge.error = true;
      });
  }

  updateGraderStatus();
  setInterval(updateGraderStatus, 30000);
});
