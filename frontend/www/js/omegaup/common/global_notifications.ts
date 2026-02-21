import Vue from 'vue';
import common_GlobalNotifications from '../components/common/GlobalNotifications.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import notificationsStore, { MessageType } from '../notificationsStore';

OmegaUp.on('ready', () => {
  const mountPoint = document.getElementById('global-notifications');
  if (!mountPoint) {
    return;
  }

  new Vue({
    el: '#global-notifications',
    components: {
      'omegaup-global-notifications': common_GlobalNotifications,
    },
    render: function (createElement) {
      return createElement('omegaup-global-notifications');
    },
  });

  // Display maintenance message if present
  const payload = types.payloadParsers.CommonPayload('header-payload');

  if (payload.maintenanceMessage) {
    const typeMap: Record<string, MessageType> = {
      info: MessageType.Info,
      warning: MessageType.Warning,
      danger: MessageType.Danger,
    };

    notificationsStore.dispatch('displayStatus', {
      message: payload.maintenanceMessage.message,
      type: typeMap[payload.maintenanceMessage.type] || MessageType.Info,
      autoHide: false, // Maintenance messages should stay visible
    });
  }
});
