<template>
  <transition
    name="notification-slide"
    @before-enter="beforeEnter"
    @enter="enter"
    @after-enter="afterEnter"
    @before-leave="beforeLeave"
    @leave="leave"
  >
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
import { Vue, Component } from 'vue-property-decorator';
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

  // Transition hooks for dynamic height animation
  beforeEnter(el: HTMLElement): void {
    el.style.height = '0';
    el.style.opacity = '0';
    el.style.overflow = 'hidden';
  }

  enter(el: HTMLElement, done: () => void): void {
    // Force reflow to ensure transition starts from initial state
    void el.offsetHeight;

    // Measure the natural height
    el.style.height = 'auto';
    const height = el.scrollHeight;
    el.style.height = '0';

    // Force reflow again
    void el.offsetHeight;

    // Animate to natural height
    el.style.transition = 'height 0.3s ease, opacity 0.3s ease';
    el.style.height = `${height}px`;
    el.style.opacity = '1';

    setTimeout(done, 300);
  }

  afterEnter(el: HTMLElement): void {
    // Reset to auto height so content can adjust if needed
    el.style.height = 'auto';
    el.style.overflow = '';
    el.style.transition = '';
  }

  beforeLeave(el: HTMLElement): void {
    // Set explicit height before animating out
    el.style.height = `${el.scrollHeight}px`;
    el.style.overflow = 'hidden';
    // Force reflow
    void el.offsetHeight;
  }

  leave(el: HTMLElement, done: () => void): void {
    el.style.transition = 'height 0.3s ease, opacity 0.3s ease';
    el.style.height = '0';
    el.style.opacity = '0';

    setTimeout(done, 300);
  }
}
</script>

<style lang="scss" scoped>
.alert {
  position: relative;
  margin-bottom: 0;
  border-radius: 0;

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
</style>
