import common_Navbar from '../components/common/Navbar.vue';
import { API, UI, OmegaUp, T } from '../omegaup.js';
import omegaup from '../api.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const headerPayload = JSON.parse(
    document.getElementById('header-payload').innerText,
  );
  let commonNavbar = new Vue({
    el: '#common-navbar',
    render: function(createElement) {
      return createElement('omegaup-common-navbar', {
        props: {
          header: this.header,
          status: this.status,
          graderInfo: this.graderInfo,
          graderQueueLength: this.graderQueueLength,
          inError: this.inError,
          errorMessage: this.errorMessage,
        },
      });
    },
    data: {
      header: headerPayload,
      status: 'ok',
      graderInfo: null,
      graderQueueLength: -1,
      inError: false,
      errorMessage: null,
    },
    components: {
      'omegaup-common-navbar': common_Navbar,
    },
  });

  if (headerPayload.isAdmin) {
    API.Notification.myList({})
      .then(function(data) {
        commonNavbar.notifications = data.notifications;
      })
      .fail(UI.apiError);
  }

  function updateGraderStatus() {
    API.Grader.status()
      .then(stats => {
        commonNavbar.graderInfo = stats.grader;
        if (stats.status !== 'ok') {
          commonNavbar.status = 'down';
          return;
        }
        if (stats.grader.queue) {
          commonNavbar.graderQueueLength =
            stats.grader.queue.run_queue_length +
            stats.grader.queue.running.length;
        }
        commonNavbar.status = 'ok';
        commonNavbar.errorMessage = null;
        commonNavbar.inError = false;
      })
      .fail(stats => {
        commonNavbar.status = 'down';
        commonNavbar.errorMessage = stats.error;
        commonNavbar.inError = true;
      });
  }

  updateGraderStatus();
  setInterval(updateGraderStatus, 30000);
});
