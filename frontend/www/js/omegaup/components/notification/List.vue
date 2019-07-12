<template>
  <li class="dropdown">
    <a aria-expanded="true"
        aria-haspopup="true"
        class="notification-btn dropdown-toggle"
        data-toggle="dropdown"
        href="#"
        role="button"><span class="glyphicon glyphicon-bell"></span> <span class=
        "label label-danger count-label"
          v-show="!!notifications.length">{{ notifications.length }}</span></a>
    <ul class="dropdown-menu notification-dropdown">
      <li v-show="notifications.length === 0">{{ this.T.notificationsNoNewNotifications
      }}</li><omegaup-notification v-bind:key="index"
          v-bind:notification="notification"
          v-for="(notification, index) in notifications"></omegaup-notification>
    </ul>
  </li>
</template>

<style>
.nav>li>a.notification-btn {
  padding: 12px 4px 0 0;
}

.glyphicon-bell {
  font-size: 20px;
  display: block;
  text-align: center;
}

.count-label {
  display: block;
}

.notification-dropdown {
  width: 500px;
  padding: 5px 5px 0;
  max-height: 600px;
  overflow-y: auto;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import Notification from './Notification.vue';

@Component({
  components: {
    'omegaup-notification': Notification,
  },
})
export default class NotificationList extends Vue {
  @Prop() notifications!: omegaup.Notification[];
  T = T;
}

</script>
