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
          graderInfo: this.graderInfo,
          graderQueueLength: this.graderQueueLength,
          errorMessage: this.errorMessage,
        },
      });
    },
    data: {
      header: headerPayload,
      graderInfo: null,
      graderQueueLength: -1,
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

    function updateGraderStatus() {
      API.Grader.status()
        .then(stats => {
          commonNavbar.graderInfo = stats.grader;
          if (stats.status !== 'ok') {
            commonNavbar.errorMessage = T.generalError;
            return;
          }
          if (stats.grader.queue) {
            commonNavbar.graderQueueLength =
              stats.grader.queue.run_queue_length +
              stats.grader.queue.running.length;
          }
          commonNavbar.errorMessage = null;
        })
        .fail(stats => {
          commonNavbar.errorMessage = stats.error;
        });
    }

    updateGraderStatus();
    setInterval(updateGraderStatus, 30000);
  }
});
