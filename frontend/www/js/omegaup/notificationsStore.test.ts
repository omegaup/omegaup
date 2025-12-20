import {
    createNotificationsStore,
    notificationsStoreConfig,
    MessageType,
    NotificationsState,
} from './notificationsStore';
import { Store } from 'vuex';

describe('notificationsStore', () => {
    let state: NotificationsState;

    beforeEach(() => {
        // Create fresh state for each test
        state = {
            message: null,
            type: null,
            visible: false,
            counter: 0,
            uiReady: false,
            autoHideTimeout: null,
        };
    });

    describe('mutations', () => {
        describe('showNotification', () => {
            it('should set message, type, and visible state', () => {
                const payload = {
                    message: 'Test error message',
                    type: MessageType.Danger,
                };

                notificationsStoreConfig.mutations.showNotification(state, payload);

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

                notificationsStoreConfig.mutations.showNotification(state, payload);
                expect(state.counter).toBe(1);

                notificationsStoreConfig.mutations.showNotification(state, payload);
                expect(state.counter).toBe(2);
            });
        });

        describe('hideNotification', () => {
            it('should clear message, type, and set visible to false', () => {
                // First show a notification
                state.message = 'Test message';
                state.type = MessageType.Info;
                state.visible = true;

                notificationsStoreConfig.mutations.hideNotification(state);

                expect(state.message).toBeNull();
                expect(state.type).toBeNull();
                expect(state.visible).toBe(false);
            });
        });

        describe('setUiReady', () => {
            it('should set uiReady state', () => {
                expect(state.uiReady).toBe(false);

                notificationsStoreConfig.mutations.setUiReady(state, true);
                expect(state.uiReady).toBe(true);

                notificationsStoreConfig.mutations.setUiReady(state, false);
                expect(state.uiReady).toBe(false);
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
            expect(store.state.visible).toBe(false);
            expect(store.state.counter).toBe(0);
            expect(store.state.uiReady).toBe(false);
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
});
