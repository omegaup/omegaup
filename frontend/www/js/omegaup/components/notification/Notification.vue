<template>
  <div class="d-flex align-items-center flex-wrap px-4">
    <hr class="w-100 my-2" />
    <div class="w-100 d-flex justify-content-between">
      <div class="notification-date">
        {{ date }}
      </div>
      <button class="close" v-on:click.prevent="$emit('remove', notification)">
        ❌
      </button>
    </div>
    <div class="d-flex align-items-center pt-1">
      <img class="d-block" width="80" v-bind:src="iconUrl" />
      <div>
        {{ text }}
      </div>
    </div>
  </div>
</template>

<style scoped>
.close {
  font-size: inherit;
}

.notification-date {
  font-size: 0.8rem;
  color: #666;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';

@Component
export default class Notification extends Vue {
  @Prop() notification!: types.Notification;

  get iconUrl(): string {
    switch (this.notification.contents.type) {
      case 'badge':
        return `/media/dist/badges/${this.notification.contents.badge}.svg`;
      case 'demotion':
        if (this.notification.contents.status == 'banned') {
          return '/media/error.png';
        } else {
          return '/media/alert.png';
        }
      default:
        return 'media/info.png';
    }
  }

  get text(): string {
    switch (this.notification.contents.type) {
      case 'badge':
        return ui.formatString(T.notificationNewBadge, {
          badgeName: T[`badge_${this.notification.contents.badge}_name`],
        });
      case 'demotion':
        return !!this.notification.contents.message
          ? this.notification.contents.message
          : '';
      default:
        return '';
    }
  }

  get date() {
    return time.formatDate(this.notification.timestamp);
  }
}
</script>
