import Vue from 'vue';
import notifications_List from '../components/notification/List.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  let notificationsList = new Vue({
    el: '#notifications-list',
    render: function(createElement) {
      return createElement('omegaup-notification-list', {
        props: {
          notifications: this.notifications,
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
