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
      <img v-if="showIcon" class="d-block" width="80" v-bind:src="iconUrl" />
      <div v-if="htmlText" v-html="htmlText"></div>
      <div v-else-if="url">
        <a v-bind:href="url">
          {{ text }}
        </a>
      </div>
      <div v-else>
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

  get showIcon(): boolean {
    if (this.notification.contents.type === 'course-registration-request') {
      return false;
    }
    return true;
  }

  get iconUrl(): string {
    switch (this.notification.contents.type) {
      case 'badge':
        return `/media/dist/badges/${this.notification.contents.badge}.svg`;
      case 'demotion':
        if (this.notification.contents.status == 'banned') {
          return '/media/banned.svg';
        }
        return '/media/warning.svg';
      case 'general_notification':
        return '/media/email.svg';
      default:
        return 'media/info.png';
    }
  }

  get text(): string {
    switch (this.notification.contents.type) {
      case 'demotion':
        return this.notification.contents.message || '';
      case 'general_notification':
        return this.notification.contents.message || '';
      default:
        return '';
    }
  }

  get htmlText(): string {
    switch (this.notification.contents.type) {
      case 'badge':
        return ui.formatString(T.notificationNewBadge, {
          badgeLink: `/badge/${this.notification.contents.badge}/`,
          badgeName: T[`badge_${this.notification.contents.badge}_name`],
        });
      case 'course-registration-request':
        return ui.formatString(T.notificationCourseRegistrationRequest, {
          username: this.notification.contents.username || '',
          courseLink: `/course/${this.notification.contents.course?.alias ||
            ''}/edit/#students`,
          courseName: this.notification.contents.course?.name || '',
        });
      default:
        return '';
    }
  }

  get url(): string {
    switch (this.notification.contents.type) {
      case 'general_notification':
        return this.notification.contents.url || '';
      case 'demotion':
        // TODO: Add link to problem page.
        return '';
      default:
        return '';
    }
  }

  get date() {
    return time.formatDate(this.notification.timestamp);
  }
}
</script>
