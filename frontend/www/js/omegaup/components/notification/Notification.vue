<template>
  <li class="notification">
    <p class="notification-date">{{ date }}</p><img class="notification-img"
        v-bind:src="iconUrl">
    <p class="notification-text">{{ text }}</p>
    <hr class="notification-separator">
  </li>
</template>

<style>
.notification {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
}

.notification-date,
.notification-text {
  margin: 0;
}

.notification-date {
  width: 100%;
  font-size: 12px;
  color: grey;
}

.notification-img {
  display: block;
  width: 15%;
  height: auto;
}

.notification-text {
  padding: 0 0 0 5px;
  width: 85%;
}

.notification-separator {
  width: 100%;
  margin: 5px 0;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';

@Component
export default class Notification extends Vue {
  @Prop() notification!: omegaup.Notification;

  T = T;
  UI = UI;

  get iconUrl(): string {
    switch (this.notification.contents.type) {
      case 'badge':
        return `/media/dist/badges/${this.notification.contents.badge}.svg`;
      default:
        return 'media/info.png';
    }
  }

  get text(): string {
    switch (this.notification.contents.type) {
      case 'badge':
        return this.UI.formatString(this.T.notificationNewBadge, {
          badgeName: this.T[`badge_${this.notification.contents.badge}_name`],
        });
      default:
        return '';
    }
  }

  get date() {
    return this.UI.formatDate(this.notification.timestamp);
  }
}

</script>
