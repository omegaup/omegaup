<template>
  <div class="d-flex align-items-center flex-wrap px-4">
    <hr class="w-100 my-2" />
    <div class="w-100 d-flex justify-content-between">
      <div class="notification-date">
        {{ date }}
      </div>
      <button
        class="close"
        @click.prevent="$emit('remove', notification, null)"
      >
        ❌
      </button>
    </div>
    <div
      class="w-100 d-flex align-items-center pt-1"
      :class="{ 'notification-link': url }"
      @click="handleClick"
    >
      <img
        class="d-block"
        width="50"
        :src="iconUrl"
        alt=""
        aria-hidden="true"
      />
      <template v-if="notificationMarkdown">
        <omegaup-markdown :markdown="notificationMarkdown"></omegaup-markdown>
      </template>
      <div v-else-if="url">
        <a :href="url">
          {{ text }}
        </a>
      </div>
      <div v-else>
        {{ text }}
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';

import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class Notification extends Vue {
  @Prop() notification!: types.Notification;

  get iconUrl(): string {
    if (this.notification.contents.body) {
      return this.notification.contents.body.iconUrl;
    }

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
        return '/media/info.png';
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

  get notificationMarkdown(): string {
    if (this.notification.contents.body) {
      return ui.formatString(
        T[this.notification.contents.body.localizationString],
        this.notification.contents.body.localizationParams,
      );
    }
    switch (this.notification.contents.type) {
      case 'badge':
        return ui.formatString(T.notificationNewBadge, {
          badgeName: T[`badge_${this.notification.contents.badge}_name`],
        });
      default:
        return '';
    }
  }

  get url(): string {
    if (this.notification.contents.body) {
      return this.notification.contents.body.url;
    }

    switch (this.notification.contents.type) {
      case 'general_notification':
        return this.notification.contents.url || '';
      case 'badge':
        return `/badge/${this.notification.contents.badge}/`;
      case 'demotion':
        // TODO: Add link to problem page.
        return '';
      default:
        return '';
    }
  }

  get date() {
    return time.formatDateLocalHHMM(this.notification.timestamp);
  }

  handleClick(): void {
    if (this.url) {
      this.$emit('remove', this.notification, this.url);
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.close {
  font-size: inherit;
}

.notification-date {
  font-size: 0.8rem;
  color: var(--notifications-notification-date-font-color);
}

.notification-link {
  cursor: pointer;

  &:hover {
    background-color: rgba(
      var(--notifications-notification-link-background-color--hover),
      0.05
    );
  }
}
</style>
