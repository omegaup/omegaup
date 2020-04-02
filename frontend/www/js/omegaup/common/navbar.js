import common_Navbar from '../components/common/Navbar.vue';
import common_NavbarV2 from '../components/common/Navbarv2.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api_transitional';
import API from '../api.js';
import T from '../lang';
import * as UI from '../ui';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(
    document.getElementById('header-payload').innerText,
  );
  let commonNavbar = new Vue({
    el: '#common-navbar',
    render: function(createElement) {
      return createElement('omegaup-common-navbar', {
        props: {
          omegaUpLockDown: this.omegaUpLockDown,
          inContest: this.inContest,
          isLoggedIn: this.isLoggedIn,
          isReviewer: this.isReviewer,
          gravatarURL51: this.gravatarURL51,
          currentUsername: this.currentUsername,
          isAdmin: this.isAdmin,
          isMainUserIdentity: this.isMainUserIdentity,
          lockDownImage: this.lockDownImage,
          navbarSection: this.navbarSection,
          graderInfo: this.graderInfo,
          graderQueueLength: this.graderQueueLength,
          errorMessage: this.errorMessage,
          initialClarifications: this.initialClarifications,
        },
      });
    },
    data: {
      omegaUpLockDown: payload.omegaUpLockDown,
      inContest: payload.inContest,
      isLoggedIn: payload.isLoggedIn,
      isReviewer: payload.isReviewer,
      gravatarURL51: payload.gravatarURL51,
      currentUsername: payload.currentUsername,
      isAdmin: payload.isAdmin,
      isMainUserIdentity: payload.isMainUserIdentity,
      lockDownImage: payload.lockDownImage,
      navbarSection: payload.navbarSection,
      graderInfo: null,
      graderQueueLength: -1,
      errorMessage: null,
      initialClarifications: [],
    },
    components: {
      'omegaup-common-navbar': payload.bootstrap4
        ? common_NavbarV2
        : common_Navbar,
    },
  });

  if (payload.isAdmin) {
    API.Notification.myList()
      .then(data => {
        commonNavbar.notifications = data.notifications;
      })
      .catch(UI.apiError);

    function updateGraderStatus() {
      api.Grader.status()
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
        .catch(stats => {
          commonNavbar.errorMessage = stats.error;
        });
    }

    updateGraderStatus();
    setInterval(updateGraderStatus, 30000);
  }
});
