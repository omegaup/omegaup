import Vue from 'vue';
import notifications_List from '../components/notification/List.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

let currentPage = window.location.pathname;
const section = currentPage.split('/')[1];
if (section === 'course') {
  currentPage = '/schools/';
} else if (section === 'arena') {
  currentPage = '/arena/';
}
$('.navbar-nav li').find(`[href='${currentPage}']`).parent().addClass('active');

OmegaUp.on('ready', function() {
  let notificationsList = new Vue({
    el: '#notifications-list',
    render: function(createElement) {
      return createElement('omegaup-notification-list', {
        props: {
          notifications: this.notifications,
        },
        on: {
          read: function(notifications) {
            API.Notification.readNotifications({
                              'notifications': notifications.map(
                                  notification => notification.notification_id),
                            })
                .then(function() { return API.Notification.myList({}); })
                .then(function(data) {
                  notificationsList.notifications = data.notifications;
                })
                .fail(UI.apiError);
          },
        }
      });
    },
    data: {
      notifications: [],
    },
    components: {
      'omegaup-notification-list': notifications_List,
    },
  });

  API.Notification.myList({})
      .then(function(data) {
        notificationsList.notifications = data.notifications;
      })
      .fail(UI.apiError);
});
