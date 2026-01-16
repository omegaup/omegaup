import Vue from 'vue';
import Vuex, { ActionContext, Store } from 'vuex';

// Vuex plugin registration is required before creating store instances.
// Vue.use() is idempotent, so multiple registrations are safe.
Vue.use(Vuex);

/**
 * Message types for notifications, matching Bootstrap alert classes.
 */
export enum MessageType {
  Danger = 'alert-danger',
  Info = 'alert-info',
  Success = 'alert-success',
  Warning = 'alert-warning',
}

/**
 * Positions for notifications.
 * 'top' is the default full-width banner at the top of the page.
 */
export enum NotificationPosition {
  Top = 'top',
  Bottom = 'bottom',
  TopRight = 'top-right',
  BottomRight = 'bottom-right',
}

export interface NotificationsState {
  message: string | null;
  type: MessageType | null;
  position: NotificationPosition;
  visible: boolean;
  counter: number;
  autoHideTimeout: ReturnType<typeof setTimeout> | null;
}

/**
 * Creates fresh store configuration.
 * Each store instance has its own state including timeout management.
 */
function createStoreConfig() {
  return {
    state: {
      message: null,
      type: null,
      position: NotificationPosition.Top,
      visible: false,
      counter: 0,
      autoHideTimeout: null,
    } as NotificationsState,

    mutations: {
      showNotification(
        state: NotificationsState,
        payload: { message: string; type: MessageType },
      ) {
        Vue.set(state, 'message', payload.message);
        Vue.set(state, 'type', payload.type);
        Vue.set(state, 'visible', true);
        Vue.set(state, 'counter', state.counter + 1);
      },

      hideNotification(state: NotificationsState) {
        Vue.set(state, 'visible', false);
        Vue.set(state, 'message', null);
        Vue.set(state, 'type', null);
      },

      setPosition(state: NotificationsState, position: NotificationPosition) {
        Vue.set(state, 'position', position);
      },

      setAutoHideTimeout(
        state: NotificationsState,
        timeout: ReturnType<typeof setTimeout> | null,
      ) {
        Vue.set(state, 'autoHideTimeout', timeout);
      },

      clearAutoHideTimeout(state: NotificationsState) {
        if (state.autoHideTimeout) {
          clearTimeout(state.autoHideTimeout);
          Vue.set(state, 'autoHideTimeout', null);
        }
      },
    },

    actions: {
      displayStatus(
        {
          commit,
          state,
        }: ActionContext<NotificationsState, NotificationsState>,
        payload: {
          message: string;
          type: MessageType;
          autoHide?: boolean;
          position?: NotificationPosition;
        },
      ) {
        // Clear any existing auto-hide timeout
        commit('clearAutoHideTimeout');

        // Set position if provided
        if (payload.position) {
          commit('setPosition', payload.position);
        }

        // Show the notification
        commit('showNotification', {
          message: payload.message,
          type: payload.type,
        });

        // Auto-hide success messages after 5 seconds
        if (
          payload.type === MessageType.Success &&
          payload.autoHide !== false
        ) {
          const currentCounter = state.counter;
          const timeout = setTimeout(() => {
            // Only hide if no new notification has been shown
            if (state.counter === currentCounter && state.visible) {
              commit('hideNotification');
            }
          }, 5000);
          commit('setAutoHideTimeout', timeout);
        }
      },

      dismissNotifications({
        commit,
      }: ActionContext<NotificationsState, NotificationsState>) {
        commit('clearAutoHideTimeout');
        commit('hideNotification');
      },
    },

    getters: {
      isVisible: (state: NotificationsState) => state.visible,
      message: (state: NotificationsState) => state.message,
      type: (state: NotificationsState) => state.type,
      alertClass: (state: NotificationsState) => state.type || '',
      position: (state: NotificationsState) => state.position,
      positionClass: (state: NotificationsState) =>
        `notification-${state.position}`,
    },
  };
}

/**
 * Export factory function for testing purposes.
 * Creates fresh config on each call to avoid shared state.
 */
export const createNotificationsStoreConfig = createStoreConfig;

/**
 * Factory function to create a fresh notifications store instance.
 * Use this for SSR or when you need isolated store instances (e.g., tests).
 */
export function createNotificationsStore(): Store<NotificationsState> {
  return new Vuex.Store<NotificationsState>(createStoreConfig());
}

/**
 * Default singleton store for client-side usage.
 *
 * WARNING: This singleton should NOT be used in SSR environments.
 * If omegaUp ever adopts SSR, this pattern would need to change.
 * For isolated instances (tests, SSR), use createNotificationsStore() instead.
 */
export default createNotificationsStore();
