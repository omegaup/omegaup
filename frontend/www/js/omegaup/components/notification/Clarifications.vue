<template>
  <li class="nav-item dropdown d-none d-lg-flex align-items-center">
    <audio ref="notification-audio" data-notification-audio>
      <source src="/media/notification.mp3" type="audio/mpeg" />
    </audio>
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
        v-if="clarifications && clarifications.length > 0"
        class="badge badge-danger count-badge"
        >{{ clarifications.length }}</span
      ></a
    >
    <ul class="dropdown-menu dropdown-menu-right notification-dropdown">
      <li v-if="!clarifications || clarifications.length === 0" class="empty">
        {{ T.notificationsNoNewNotifications }}
      </li>
      <li v-else>
        <ul class="notification-drawer">
          <li
            v-for="clarification in clarifications"
            :key="clarification.clarification_id"
          >
            <button
              :aria-label="T.wordsClose"
              class="close"
              type="button"
              @click.prevent="onCloseClicked(clarification)"
            >
              <span aria-hidden="true">×</span>
            </button>
            <a :href="anchor(clarification)"
              ><span>{{ clarification.problem_alias }}</span> —
              <span>{{ clarification.author }}</span>
              <pre>{{ clarification.message }}</pre>
              <template v-if="clarification.answer">
                <hr />
                <pre>{{ clarification.answer }}</pre>
              </template></a
            >
          </li>
        </ul>
      </li>
      <template v-if="clarifications && clarifications.length > 1">
        <li class="divider" role="separator"></li>
        <li>
          <a href="#" @click.prevent="onMarkAllAsRead"
            ><font-awesome-icon :icon="['fas', 'align-right']" />
            {{ T.notificationsMarkAllAsRead }}</a
          >
        </li>
      </template>
    </ul>
  </li>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import type { types } from '../../api_types';
import T from '../../lang';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faBell, faAlignRight } from '@fortawesome/free-solid-svg-icons';
library.add(faBell, faAlignRight);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class Clarifications extends Vue {
  @Prop({ default: () => [] }) clarifications!: types.Clarification[];

  @Prop() isAdmin!: boolean;
  T = T;

  flashInterval: number = 0;

  @Watch('clarifications')
  onPropertyChanged(newValue: types.Clarification[]): void {
    this.clarifications = newValue;
    const audio = this.$refs['notification-audio'] as HTMLMediaElement;
    if (!audio) return;
    audio.play();
  }

  @Watch('clarifications')
  onPropertyChange(newValue: types.Clarification[]): void {
    if (newValue.length > 0) {
      if (this.flashInterval) return;
      this.flashInterval = setInterval(this.flashTitle, 1000);
    } else {
      if (!this.flashInterval) return;
      clearInterval(this.flashInterval);
      this.flashInterval = 0;
      if (document.title.indexOf('!') === 0) {
        document.title = document.title.substring(2);
      }
    }
  }

  anchor(clarification: types.Clarification): string {
    return `#clarifications/clarification-${clarification.clarification_id}`;
  }

  flashTitle(reset: boolean): void {
    if (document.title.indexOf('!') === 0) {
      document.title = document.title.substring(2);
    } else if (!reset) {
      document.title = `! ${document.title}`;
    }
  }

  onCloseClicked(clarification: types.Clarification): void {
    const id = `clarification-${clarification.clarification_id}`;
    this.clarifications = this.clarifications.filter(
      (element) => element.clarification_id !== clarification.clarification_id,
    );
    localStorage.setItem(id, Date.now().toString());
  }

  onMarkAllAsRead(): void {
    for (const clarification of this.clarifications) {
      const id = `clarification-${clarification.clarification_id}`;
      localStorage.setItem(id, Date.now().toString());
    }
    this.clarifications = [];
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.notification-button {
  padding-top: 6px !important;
  padding-bottom: 20px !important;
  padding-right: 12px !important;
  padding-left: 12px !important;
  font-size: 22px;
}

.notification-counter {
  position: absolute;
  font-size: 16px;
  padding: 2px 4px;
  bottom: 4px;
  right: 0;
}

.notification-drawer::-webkit-scrollbar-track {
  border-radius: 10px;
  background-color: var(
    --notifications-clarifications-scrollbar-track-background-color
  );
}

.notification-drawer::-webkit-scrollbar {
  width: 8px;
  height: 8px;
  background-color: var(
    --notifications-clarifications-scrollbar-background-color
  );
}

.notification-drawer::-webkit-scrollbar-thumb {
  border-radius: 10px;
  background-color: var(
    --notifications-clarifications-scrollbar-thumb-background-color
  );
}

.notification-drawer {
  width: 320px;
  max-width: 320px;
  max-height: 380px;
  overflow-y: scroll;
}

.notification-drawer li {
  padding: 3px 20px;
  border-top: 1px solid
    var(--notifications-clarifications-drawer-li-border-top-color);
}

.notification-drawer li a {
  color: var(--notifications-clarifications-drawer-li-a-font-color);
  text-decoration: none;
}

.notification-drawer li a pre {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  width: 100%;
}

.notification-drawer li:hover,
.notification-drawer li:focus,
.notification-drawer li:active {
  cursor: pointer;
  background-color: var(
    --notifications-clarifications-drawer-li-background-color--active
  );
  text-decoration: none;
}

.notification-drawer li:hover > a,
.notification-drawer li:focus > a,
.notification-drawer li:active > a {
  color: var(--notifications-clarifications-drawer-li-font-color--active);
}

.notification-drawer li a > h4,
.notification-drawer li a > p {
  word-wrap: break-word;
}
</style>
