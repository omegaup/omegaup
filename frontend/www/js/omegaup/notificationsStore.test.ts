import {
  createNotificationsStore,
  createNotificationsStoreConfig,
  MessageType,
  NotificationPosition,
  NotificationsState,
} from './notificationsStore';

describe('notificationsStore', () => {
  let state: NotificationsState;
  let storeConfig: ReturnType<typeof createNotificationsStoreConfig>;

  beforeEach(() => {
    // Create fresh state and config for each test to avoid shared state
    state = {
      message: null,
      type: null,
      position: NotificationPosition.Top,
      visible: false,
      counter: 0,
      autoHideTimeout: null,
    };
    storeConfig = createNotificationsStoreConfig();
  });

  describe('mutations', () => {
    describe('showNotification', () => {
      it('should set message, type, and visible state', () => {
        const payload = {
          message: 'Test error message',
          type: MessageType.Danger,
        };

        storeConfig.mutations.showNotification(state, payload);

        expect(state.message).toBe('Test error message');
        expect(state.type).toBe(MessageType.Danger);
        expect(state.visible).toBe(true);
        expect(state.counter).toBe(1);
      });

      it('should increment counter on each notification', () => {
        const payload = {
          message: 'Test message',
          type: MessageType.Success,
        };

        storeConfig.mutations.showNotification(state, payload);
        expect(state.counter).toBe(1);

        storeConfig.mutations.showNotification(state, payload);
        expect(state.counter).toBe(2);
      });
    });

    describe('hideNotification', () => {
      it('should clear message, type, and set visible to false', () => {
        // First show a notification
        state.message = 'Test message';
        state.type = MessageType.Info;
        state.visible = true;

        storeConfig.mutations.hideNotification(state);

        expect(state.message).toBeNull();
        expect(state.type).toBeNull();
        expect(state.visible).toBe(false);
      });
    });
  });

  describe('createNotificationsStore factory', () => {
    it('should create independent store instances', () => {
      const store1 = createNotificationsStore();
      const store2 = createNotificationsStore();

      // Modify store1
      store1.commit('showNotification', {
        message: 'Store 1 message',
        type: MessageType.Danger,
      });

      // store2 should be unaffected
      expect(store1.state.message).toBe('Store 1 message');
      expect(store2.state.message).toBeNull();
      expect(store1.state.visible).toBe(true);
      expect(store2.state.visible).toBe(false);
    });

    it('should create store with correct initial state', () => {
      const store = createNotificationsStore();

      expect(store.state.message).toBeNull();
      expect(store.state.type).toBeNull();
      expect(store.state.position).toBe(NotificationPosition.Top);
      expect(store.state.visible).toBe(false);
      expect(store.state.counter).toBe(0);
      expect(store.state.autoHideTimeout).toBeNull();
    });
  });

  describe('MessageType enum', () => {
    it('should have correct Bootstrap class mappings', () => {
      expect(MessageType.Danger).toBe('alert-danger');
      expect(MessageType.Info).toBe('alert-info');
      expect(MessageType.Success).toBe('alert-success');
      expect(MessageType.Warning).toBe('alert-warning');
    });
  });

  describe('displayStatus action', () => {
    it('should show notification with message and type', () => {
      const store = createNotificationsStore();

      store.dispatch('displayStatus', {
        message: 'Test message',
        type: MessageType.Info,
      });

      expect(store.state.visible).toBe(true);
      expect(store.state.message).toBe('Test message');
      expect(store.state.type).toBe(MessageType.Info);
    });

    it('should set position when provided', () => {
      const store = createNotificationsStore();

      store.dispatch('displayStatus', {
        message: 'Test message',
        type: MessageType.Info,
        position: NotificationPosition.TopRight,
      });

      expect(store.state.position).toBe(NotificationPosition.TopRight);
    });
  });
});
