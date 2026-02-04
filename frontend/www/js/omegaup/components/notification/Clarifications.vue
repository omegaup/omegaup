<template>
  <li class="nav-item dropdown d-none d-lg-flex align-items-center">
    <audio v-if="isAdmin" ref="notification-audio" data-notification-audio>
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
        v-if="unreadClarifications && unreadClarifications.length > 0"
        class="badge badge-danger count-badge"
        >{{ unreadClarifications.length }}</span
      ></a
    >
    <div class="dropdown-menu dropdown-menu-right notification-dropdown">
      <div v-if="unreadClarifications.length === 0" class="text-center">
        {{ T.notificationsNoNewNotifications }}
      </div>
      <transition-group name="list">
        <div
          v-for="clarification in unreadClarifications"
          :key="clarification.clarification_id"
          :data-clarification="clarification.clarification_id"
          class="d-flex align-items-center flex-wrap px-4"
        >
          <hr class="w-100 my-2" />
          <div class="w-100 justify-content-between">
            <button
              class="close"
              @click.prevent="onCloseClicked(clarification)"
            >
              ❌
            </button>
          </div>
          <div class="w-100 align-items-center pt-1 notification-link">
            <a :href="anchor(clarification)">
              <span>{{ clarification.problem_alias }}</span> —
              <span>{{ clarification.author }}</span>
              <pre>{{ clarification.message }}</pre>
              <template v-if="clarification.answer">
                <hr />
                <pre>{{ clarification.answer }}</pre>
              </template>
            </a>
          </div>
        </div>
      </transition-group>
      <template v-if="unreadClarifications && unreadClarifications.length > 1">
        <li class="divider" role="separator"></li>
        <li data-mark-all-as-read-button>
          <a href="#" @click.prevent="onMarkAllAsRead"
            ><font-awesome-icon :icon="['fas', 'align-right']" />
            {{ T.notificationsMarkAllAsRead }}</a
          >
        </li>
      </template>
    </div>
  </li>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import type { types } from '../../api_types';
import T from '../../lang';
import { SafeStorage } from '../../safe_storage';

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
  unreadClarifications = this.clarifications;

  @Watch('clarifications')
  onPropertyChanged(newValue: types.Clarification[]): void {
    this.unreadClarifications = newValue;
    const audio = this.$refs['notification-audio'] as HTMLMediaElement;
    if (!audio) return;
    audio.play();
  }

  @Watch('unreadClarifications')
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
    this.unreadClarifications = this.unreadClarifications.filter(
      (element) => element.clarification_id !== clarification.clarification_id,
    );
    if (!SafeStorage.setItem(id, Date.now().toString())) {
      console.warn('Could not persist clarification state');
    }
  }

  onMarkAllAsRead(): void {
    for (const clarification of this.unreadClarifications) {
      const id = `clarification-${clarification.clarification_id}`;
      SafeStorage.setItem(id, Date.now().toString());
    }
    this.unreadClarifications = [];
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.close {
  font-size: inherit;
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

pre {
  padding: 16px;
  background: var(--markdown-pre-background-color);
  margin: 1em 0;
  border-radius: 6px;
  display: block;
  line-height: 125%;
}
</style>
