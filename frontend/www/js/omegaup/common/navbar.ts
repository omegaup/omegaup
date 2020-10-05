import common_Navbar from '../components/common/Navbar.vue';
import common_NavbarV2 from '../components/common/Navbarv2.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import { types } from '../api_types';
import * as ui from '../ui';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CommonPayload('header-payload');
  const commonNavbar = new Vue({
    el: '#common-navbar',
    components: {
      'omegaup-common-navbar': payload.bootstrap4
        ? common_NavbarV2
        : common_Navbar,
    },
    data: () => ({
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
      notifications: <types.Notification[]>[],
      graderInfo: <types.GraderStatus | null>null,
      graderQueueLength: -1,
      errorMessage: <string | null>null,
      initialClarifications: [],
    }),
    render: function (createElement) {
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
          notifications: this.notifications,
          graderInfo: this.graderInfo,
          graderQueueLength: this.graderQueueLength,
          errorMessage: this.errorMessage,
          initialClarifications: this.initialClarifications,
        },
        on: {
          'read-notifications': (notifications: types.Notification[]) => {
            api.Notification.readNotifications({
              notifications: notifications.map(
                (notification) => notification.notification_id,
              ),
            })
              .then(() => api.Notification.myList())
              .then((data) => {
                commonNavbar.notifications = data.notifications;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  if (payload.isLoggedIn) {
    api.Notification.myList()
      .then((data) => {
        commonNavbar.notifications = data.notifications;
      })
      .catch(ui.apiError);
  }

  if (payload.isAdmin) {
    const updateGraderStatus = () => {
      api.Grader.status()
        .then((stats) => {
          commonNavbar.graderInfo = stats.grader;
          if (stats.grader.queue) {
            commonNavbar.graderQueueLength =
              stats.grader.queue.run_queue_length +
              stats.grader.queue.running.length;
          }
          commonNavbar.errorMessage = null;
        })
        .catch((stats) => {
          commonNavbar.errorMessage = stats.error;
        });
    };

    updateGraderStatus();
    setInterval(updateGraderStatus, 30000);
  }
});
