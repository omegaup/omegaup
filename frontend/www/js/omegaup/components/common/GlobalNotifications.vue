<template>
  <transition name="notification-slide">
    <div
      v-if="visible"
      class="alert mt-0"
      :class="[alertClass, positionClass]"
      role="alert"
    >
      <button
        data-alert-close
        type="button"
        class="close"
        aria-label="Close"
        @click="dismiss"
      >
        &times;
      </button>
      <span v-if="message" class="message">
        <omegaup-markdown :markdown="message"></omegaup-markdown>
      </span>
    </div>
  </transition>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import notificationsStore from '../../notificationsStore';
import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class GlobalNotifications extends Vue {
  get visible(): boolean {
    return notificationsStore.getters.isVisible;
  }

  get message(): string | null {
    return notificationsStore.getters.message;
  }

  get alertClass(): string {
    return notificationsStore.getters.alertClass;
  }

  get positionClass(): string {
    return notificationsStore.getters.positionClass;
  }

  dismiss(): void {
    notificationsStore.dispatch('dismissNotifications');
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.alert {
  position: fixed;
  top: 56px; // Below navbar
  left: 0;
  right: 0;
  z-index: 1029;
  margin-bottom: 0;
  border-radius: 0;
  overflow: hidden;

  .close {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    padding: 0;
    background: transparent;
    border: none;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: inherit;
    opacity: 0.5;
    cursor: pointer;

    &:hover {
      opacity: 0.75;
    }
  }

  .message {
    display: block;
    padding-right: 2rem;

    // Override Markdown component styling for notifications
    /* stylelint-disable-next-line selector-pseudo-element-no-unknown */
    ::v-deep [data-markdown-statement] {
      display: inline;
      max-width: none;
      margin: 0;
      text-align: left;

      p {
        display: inline;
        margin: 0;
        text-align: left;
      }

      a {
        color: $omegaup-links;
        text-decoration: underline;
      }
    }
  }
}

// Vue transition classes for slide animation
.notification-slide-enter-active,
.notification-slide-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
  transform-origin: top;
  overflow: hidden;

  // Override transform-origin for bottom-positioned notifications
  &.notification-bottom,
  &.notification-bottom-right {
    transform-origin: bottom;
  }
}

.notification-slide-enter,
.notification-slide-leave-to {
  transform: scaleY(0);
  opacity: 0;
}

// Position variations
// .notification-top is the default (full-width banner at top), no additional styles needed

.notification-bottom {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1050;
}

.notification-top-right {
  position: fixed;
  top: 60px; // Below navbar
  right: 1rem;
  left: auto;
  max-width: 400px;
  border-radius: 0.375rem;
  z-index: 1050;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.notification-bottom-right {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  left: auto;
  max-width: 400px;
  border-radius: 0.375rem;
  z-index: 1050;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
