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
          graderStatus: this.graderStatus,
          graderBadge: this.graderBadge,
        },
      });
    },
    data: {
      header: headerPayload,
      graderStatus: {
        status: 'ok',
        error: null,
        graderInfo: null,
      },
      graderBadge: {
        queueLength: -1,
        error: false,
      },
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
        commonNavbar.graderStatus.graderInfo = stats.grader;
        if (commonNavbar.graderStatus.graderInfo.status !== 'ok') {
          commonNavbar.graderStatus.status = 'down';
          return;
        }
        if (stats.grader.queue) {
          commonNavbar.graderBadge.queueLength =
            stats.grader.queue.run_queue_length +
            stats.grader.queue.running.length;
        }
        commonNavbar.graderStatus.status = 'ok';
        commonNavbar.graderStatus.error = null;
        commonNavbar.graderBadge.error = false;
      })
      .fail(stats => {
        commonNavbar.graderStatus.status = 'down';
        commonNavbar.graderStatus.error = stats.error;
        commonNavbar.graderBadge.error = true;
      });
  }

  updateGraderStatus();
  setInterval(updateGraderStatus, 30000);
});
