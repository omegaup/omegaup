<template>
  <li class="dropdown hide">
    <a aria-expanded="true"
        aria-haspopup="true"
        class="notification-btn dropdown-toggle"
        data-toggle="dropdown"
        href="#"
        role="button"><span class="glyphicon glyphicon-bell"></span> <span class=
        "label label-danger count-label"
          v-show="!!notifications.length">{{ notifications.length }}</span></a>
    <ul class="dropdown-menu notification-dropdown">
      <li class="text-center"
          v-if="notifications.length === 0">{{ this.T.notificationsNoNewNotifications }}</li>
      <li v-else="">
        <a role="button"
            v-on:click="$emit('read', notifications)">{{ this.T.notificationsMarkAllAsRead }}
            ✔️</a>
      </li><transition-group name="list"><omegaup-notification v-bind:key=
      "notification.notification_id"
                            v-bind:notification="notification"
                            v-for="notification in notifications"
                            v-on:remove=
                            "$emit('read', [notification])"></omegaup-notification></transition-group>
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
}

.count-label {
  display: block;
}

.notification-dropdown {
  width: 500px;
  max-height: 600px;
  overflow-y: auto;
}

.dropdown-item {
  padding: 3px 20px;
}

/* Transitions */
.list-enter-active, .list-leave-active {
  transition: all .75s;
}

.list-enter, .list-leave-to {
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
}

</script>
