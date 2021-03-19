import common_NavbarV2 from '../components/common/Navbarv2.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import { types } from '../api_types';
import * as ui from '../ui';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CommonPayload('header-payload');
  const localMetaData = JSON.parse(
    localStorage.getItem('toggle-bootstrap-theme') ?? '',
  );
  const isDark = localMetaData?.isDark;
  if (isDark) {
    document
      .getElementById('dark-theme-style')
      ?.setAttribute(
        'href',
        '/third_party/bootswatch-4.6.0/cyborg/bootstrap.min.css',
      );
  } else {
    document.getElementById('dark-theme-style')?.setAttribute('href', '');
  }
  const commonNavbar = new Vue({
    el: '#common-navbar',
    components: {
      'omegaup-common-navbar': common_NavbarV2,
    },
    data: () => ({
      notifications: [] as types.Notification[],
      graderInfo: null as types.GraderStatus | null,
      graderQueueLength: -1,
      errorMessage: null as string | null,
    }),
    render: function (createElement) {
      return createElement('omegaup-common-navbar', {
        props: {
          omegaUpLockDown: payload.omegaUpLockDown,
          inContest: payload.inContest,
          isLoggedIn: payload.isLoggedIn,
          isReviewer: payload.isReviewer,
          gravatarURL51: payload.gravatarURL51,
          gravatarURL128: payload.gravatarURL128,
          associatedIdentities: payload.associatedIdentities,
          currentEmail: payload.currentEmail,
          currentName: payload.currentName,
          currentUsername: payload.currentUsername,
          isAdmin: payload.isAdmin,
          isMainUserIdentity: payload.isMainUserIdentity,
          lockDownImage: payload.lockDownImage,
          navbarSection: payload.navbarSection,
          profileProgress: payload.profileProgress,
          notifications: this.notifications,
          graderInfo: this.graderInfo,
          graderQueueLength: this.graderQueueLength,
          errorMessage: this.errorMessage,
          initialClarifications: [],
          isDark,
        },
        on: {
          'read-notifications': (
            notifications: types.Notification[],
            redirectTo?: string,
          ) => {
            api.Notification.readNotifications({
              notifications: notifications.map(
                (notification) => notification.notification_id,
              ),
            })
              .then(() => api.Notification.myList())
              .then((data) => {
                commonNavbar.notifications = data.notifications;
                if (redirectTo) {
                  ui.navigateTo(redirectTo);
                }
              })
              .catch(ui.apiError);
          },
          'change-account': (usernameOrEmail: string) => {
            api.Identity.selectIdentity({
              usernameOrEmail: usernameOrEmail,
            })
              .then(() => {
                window.location.reload();
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
