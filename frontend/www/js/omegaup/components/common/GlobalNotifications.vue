<template>
  <transition name="notification-slide">
    <div v-if="visible" class="alert mt-0" :class="alertClass" role="alert">
      <button
        data-alert-close
        type="button"
        class="close"
        aria-label="Close"
        @click="dismiss"
      >
        &times;
      </button>
      <span class="message">{{ message }}</span>
    </div>
  </transition>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import notificationsStore from '../../notificationsStore';

@Component
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

  dismiss(): void {
    notificationsStore.dispatch('dismissNotifications');
  }
}
</script>

<style lang="scss" scoped>
.alert {
  position: relative;
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
  }
}

// Vue transition classes for slide animation
.notification-slide-enter-active,
.notification-slide-leave-active {
  transition: max-height 0.3s ease, opacity 0.3s ease;
  max-height: 100px; // Reasonable max for notifications
}

.notification-slide-enter,
.notification-slide-leave-to {
  max-height: 0;
  opacity: 0;
}
</style>

