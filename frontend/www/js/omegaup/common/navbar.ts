import common_Navbar from '../components/common/Navbar.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import { types } from '../api_types';
import * as ui from '../ui';
import Vue from 'vue';
import T from '../lang';
import clarificationsStore from '../arena/clarificationsStore';
import mainStore from '../mainStore';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CommonPayload('header-payload');
  const fromLogin =
    new URL(document.location.toString()).searchParams.get('fromLogin') !==
    null;

  if (fromLogin) {
    const url = new URL(window.location.toString());
    url.searchParams.delete('fromLogin');
    window.history.replaceState({}, document.title, url.toString());
  }

  const commonNavbarExists = document.getElementById('common-navbar');
  if (!commonNavbarExists) {
    return;
  }
  const commonNavbar = new Vue({
    el: '#common-navbar',
    components: {
      'omegaup-common-navbar': common_Navbar,
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
          currentUsername: mainStore.state.username,
          isAdmin: payload.isAdmin,
          isMainUserIdentity: payload.isMainUserIdentity,
          lockDownImage: payload.lockDownImage,
          navbarSection: payload.navbarSection,
          profileProgress: payload.profileProgress,
          notifications: this.notifications,
          graderInfo: this.graderInfo,
          graderQueueLength: this.graderQueueLength,
          errorMessage: this.errorMessage,
          clarifications: clarificationsStore.state.clarifications,
          fromLogin: fromLogin,
          mentorCanChooseCoder: payload.mentorCanChooseCoder,
          userTypes: payload.userTypes,
          nextRegisteredContest: payload.nextRegisteredContestForUser,
          isUnder13User: payload.isUnder13User,
          userVerificationDeadline: payload.userVerificationDeadline,
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
          'update-user-objectives': ({
            hasCompetitiveObjective,
            hasLearningObjective,
            hasScholarObjective,
            hasTeachingObjective,
          }: {
            hasCompetitiveObjective: string;
            hasLearningObjective: string;
            hasScholarObjective: string;
            hasTeachingObjective: string;
          }) => {
            api.User.update({
              has_competitive_objective: hasCompetitiveObjective,
              has_learning_objective: hasLearningObjective,
              has_scholar_objective: hasScholarObjective,
              has_teaching_objective: hasTeachingObjective,
            })
              .then(() => {
                ui.success(T.userObjectivesUpdateSuccess);
              })
              .catch(ui.apiError);
          },
          'redirect-next-registered-contest': (alias: string) => {
            window.location.href = `/arena/${encodeURIComponent(alias)}/`;
          },
        },
      });
    },
  });

  if (payload.isLoggedIn) {
    mainStore.commit('updateUsername', payload.currentUsername);
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
