<template>
  <li class="nav-item dropdown d-lg-flex align-items-center">
    <a
      aria-expanded="false"
      aria-haspopup="true"
      class="nav-link dropdown-toggle px-2 notification-toggle"
      data-toggle="dropdown"
      href="#"
      role="button"
    >
      <font-awesome-icon :icon="['fas', 'bell']" />
      <span
        v-show="!!notifications.length"
        class="badge badge-danger count-badge"
        >{{ notifications.length }}</span
      ></a
    >
    <div class="dropdown-menu dropdown-menu-right notification-dropdown">
      <!--
        Trick to avoid closing on click
        The form element makes click events work inside dropdown on items that are not nav-link.
        TODO: Try another way to allow this behaviour.
      -->
      <form>
        <div v-if="notifications.length === 0" class="text-center">
          {{ T.notificationsNoNewNotifications }}
        </div>
        <a
          v-else
          class="dropdown-item"
          href="#"
          @click="$emit('read', notifications, null)"
        >
          {{ T.notificationsMarkAllAsRead }} ✔️
        </a>
        <transition-group name="list"
          ><omegaup-notification
            v-for="notification in notifications"
            :key="notification.notification_id"
            :notification="notification"
            @remove="readSingleNotification"
          ></omegaup-notification
        ></transition-group>
      </form>
    </div>
  </li>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import Notification from './Notification.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faBell } from '@fortawesome/free-solid-svg-icons';
library.add(faBell);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-notification': Notification,
  },
})
export default class NotificationList extends Vue {
  @Prop() notifications!: types.Notification[];
  T = T;

  readSingleNotification(notification: types.Notification, url?: string): void {
    this.$emit('read', [notification], url);
  }
}
</script>

<style>
.notification-toggle {
  font-size: 1.4rem;
  position: relative;
}

.count-badge {
  position: absolute;
  bottom: 0;
  right: 0.9rem;
  font-size: 0.75rem;
  display: block;
}

.notification-dropdown {
  max-width: 100vw;
  max-height: 600px;
  overflow-y: auto;
}

/* Transitions */
.list-enter-active,
.list-leave-active {
  transition: all 0.75s;
}

.list-enter,
.list-leave-to {
  opacity: 0;
}
</style>
