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
      <li v-if="notifications.length === 0">{{ this.T.notificationsNoNewNotifications }}</li>
      <li v-else="">
        <p class="read-all-notifications"
           v-on:click="$emit('read', notifications)">Marcar todas las notificaciones como leídas
           ✔️</p>
      </li><transition-group name="list"><omegaup-notification v-bind:key=
      "notification.notification_id"
                            v-bind:notification="notification"
                            v-for="notification in notifications"
                            v-on:remove=
                            "readNotification"></omegaup-notification></transition-group>
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

.read-all-notifications {
  display: inline-block;
  margin: 0;
  color: #337ab7;
  cursor: pointer;
  font-size: 14px;
  user-select: none;
}

.read-all-notifications:hover {
  color: #666;
  text-decoration: underline;
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
.list-enter-active, .list-leave-active {
  transition: all .75s;
}
.list-enter, .list-leave-to /* .list-leave-active below version 2.1.8 */ {
  opacity: 0;
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

  readNotification(notification: omegaup.Notification): void {
    this.$emit('read', [notification]);
  }
}

</script>
